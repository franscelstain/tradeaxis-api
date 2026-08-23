<?php

use App\Application\MarketData\Services\MarketDataEvidenceExportService;
use App\Infrastructure\Persistence\MarketData\EodCorrectionRepository;
use App\Infrastructure\Persistence\MarketData\EodEvidenceRepository;
use App\Infrastructure\Persistence\MarketData\EodPublicationRepository;
use Mockery as m;
use PHPUnit\Framework\TestCase;

class MarketDataEvidenceExportServiceTest extends TestCase
{
    protected function tearDown(): void
    {
        m::close();
    }

    public function test_export_run_evidence_writes_minimum_required_files()
    {
        $run = (object) [
            'run_id' => 8124,
            'trade_date_requested' => '2026-04-21',
            'trade_date_effective' => '2026-04-21',
            'lifecycle_state' => 'COMPLETED',
            'terminal_status' => 'SUCCESS',
            'quality_gate_state' => 'PASS',
            'publishability_state' => 'READABLE',
            'stage' => 'FINALIZE',
            'source' => 'manual_file',
            'coverage_universe_count' => 2,
            'coverage_available_count' => 2,
            'coverage_missing_count' => 0,
            'coverage_expected_count' => 101,
            'coverage_delivered_count' => 102,
            'coverage_delivered_valid_count' => 103,
            'coverage_ratio' => 1.0,
            'coverage_min_threshold' => 0.98,
            'coverage_gate_state' => 'PASS',
            'coverage_threshold_mode' => 'MIN_RATIO',
            'coverage_universe_basis' => 'active_equity_universe_asof_trade_date',
            'coverage_contract_version' => 'coverage_gate_v1',
            'coverage_missing_sample_json' => json_encode([]),
            'coverage_bar_not_expected_count' => 104,
            'coverage_expectation_unknown_count' => 105,
            'coverage_universe_hash' => str_repeat('a', 64),
            'coverage_excluded_sample_json' => json_encode([
                ['ticker_id' => 202, 'ticker_code' => 'EXCL'],
            ]),
            'final_reason_code' => 'COVERAGE_THRESHOLD_MET',
            'final_outcome_note' => 'Run finalized as readable publication.',
            'publication_id' => 1201,
            'bars_rows_written' => 2,
            'indicators_rows_written' => 2,
            'eligibility_rows_written' => 2,
            'invalid_bar_count' => 1,
            'invalid_indicator_count' => 0,
            'warning_count' => 0,
            'hard_reject_count' => 0,
            'bars_batch_hash' => 'HB',
            'indicators_batch_hash' => 'HI',
            'eligibility_batch_hash' => 'HE',
            'sealed_at' => '2026-04-21T17:20:00+07:00',
            'config_version' => 'cfg_v1',
            'config_hash' => 'cfg_hash',
            'config_snapshot_ref' => 'configs/x.json',
            'publication_version' => 1,
            'is_current_publication' => 1,
            'supersedes_run_id' => null,
            'started_at' => '2026-04-21T17:00:00+07:00',
            'finished_at' => '2026-04-21T17:21:00+07:00',
            'notes' => 'candidate_publication_id=1201; source_name=API_FREE; source_provider=generic; source_timeout_seconds=15; source_retry_max=3; source_attempt_count=2; source_priority=PRIMARY; active_source_decision=api_free; source_retry_attempt_count=1; source_failure_class_summary_json={"TRANSIENT":1}; source_success_after_retry=yes; source_final_http_status=200; source_final_reason_code=RUN_SOURCE_TIMEOUT; publication_reprocess_state=REPUBLISHED; publication_reprocess_republished_trade_date_count=1; publication_reprocess_republished_trade_dates=2026-05-09; publication_reprocess_candidate_trade_dates=2026-05-09; publication_reprocess_republication_mode=AUTOMATED_READABLE_CORRECTION; publication_reprocess_correction_ids=51; publication_reprocess_correction_id=51',
        ];
        $publication = (object) [
            'publication_id' => 1201,
            'run_id' => 8124,
            'publication_version' => 1,
            'is_current' => 1,
            'seal_state' => 'SEALED',
            'evidence_resolution_mode' => 'CURRENT_READABLE_PUBLICATION_AUDIT',
            'evidence_publication_scope' => 'CURRENT_POINTER_PUBLICATION',
            'evidence_selector_type' => 'run_id',
            'evidence_selector_id' => 8124,
            'current_pointer_required' => true,
            'current_pointer_status' => 'RESOLVED_READABLE_CURRENT',
            'historical_publication_allowed' => false,
            'artifact_scope' => 'PUBLICATION_SCOPED',
            'coverage_basis_publication_id' => 1201,
            'coverage_basis_run_id' => 8124,
            'lineage_verification_status' => 'LINEAGE_VERIFIED',
            'evidence_reason_code' => 'CURRENT_READABLE_PUBLICATION_RESOLVED',
        ];
        $manifest = (object) [
            'publication_id' => 1201,
            'trade_date' => '2026-04-21',
            'run_id' => 8124,
            'publication_version' => 1,
            'is_current' => 1,
            'supersedes_publication_id' => null,
            'seal_state' => 'SEALED',
            'sealed_at' => '2026-04-21T17:20:00+07:00',
            'config_identity' => 'cfg_v1',
            'bars_batch_hash' => 'HB',
            'indicators_batch_hash' => 'HI',
            'eligibility_batch_hash' => 'HE',
            'bars_rows_written' => 2,
            'indicators_rows_written' => 2,
            'eligibility_rows_written' => 2,
            'trade_date_effective' => '2026-04-21',
        ];

        $evidence = m::mock(EodEvidenceRepository::class);
        $publications = m::mock(EodPublicationRepository::class);
        $corrections = m::mock(EodCorrectionRepository::class);

        $evidence->shouldReceive('findRunById')->once()->with(8124)->andReturn($run);
        $evidence->shouldReceive('resolvePublicationForEvidenceAudit')->once()->with([
            'type' => 'run_id',
            'run_id' => 8124,
            'trade_date' => '2026-04-21',
        ])->andReturn($publication);
        $publications->shouldReceive('buildManifestByPublicationId')->once()->with(1201)->andReturn($manifest);
        $evidence->shouldReceive('summarizeRunEvents')->once()->with(8124)->andReturn([
            'event_count' => 3,
            'first_event_time' => '2026-04-21T17:00:00+07:00',
            'last_event_time' => '2026-04-21T17:21:00+07:00',
            'first_event_type' => 'RUN_CREATED',
            'last_event_type' => 'RUN_FINALIZED',
            'highest_severity' => 'INFO',
            'stage_counts' => ['FINALIZE' => 1, 'HASH' => 1, 'SEAL' => 1],
            'reason_code_counts' => [],
        ]);
        $evidence->shouldReceive('dominantReasonCodesForEvidencePublication')->once()->with(8124, '2026-04-21', 1201, true)->andReturn([]);
        $evidence->shouldReceive('exportRunSourceAttemptTelemetry')->once()->with(8124)->andReturn([
            'event_id' => 991,
            'event_time' => '2026-04-21T17:04:00+07:00',
            'event_type' => 'STAGE_COMPLETED',
            'provider' => 'generic',
            'source_name' => 'API_FREE',
            'source_priority' => 'PRIMARY',
            'active_source_decision' => 'api_free',
            'retry_attempt_count' => 1,
            'failure_class_summary' => ['TRANSIENT' => 1],
            'timeout_seconds' => 15,
            'retry_max' => 3,
            'attempt_count' => 2,
            'success_after_retry' => 'yes',
            'final_http_status' => 200,
            'final_reason_code' => 'RUN_SOURCE_TIMEOUT',
            'circuit_breaker_open' => true,
            'source_protection_state' => 'CIRCUIT_OPEN',
            'circuit_breaker_threshold' => 0.5,
            'circuit_breaker_failure_count' => 5,
            'circuit_breaker_success_count' => 0,
            'attempted_acquisition_unit_count' => 5,
            'unattempted_acquisition_unit_count' => 95,
            'circuit_breaker_trigger_reason_code' => 'RUN_SOURCE_TIMEOUT',
            'captured_at' => '2026-04-21T17:04:00+07:00',
            'source_observation_audit' => [
                'source_observation_count' => 2,
                'source_observation_reference_manifest_hash' => str_repeat('c', 64),
                'source_observation_reference_sample' => [[
                    'source_observation_id' => 901,
                    'payload_hash' => str_repeat('d', 64),
                    'schema_fingerprint' => str_repeat('e', 64),
                    'validation_state' => 'PASSED',
                    'outcome_state' => 'ACCEPTED',
                ]],
                'source_observation_outcome_state_summary' => ['ACCEPTED' => 1, 'CAPTURED' => 1],
                'schema_validation_state_summary' => ['PASSED' => 1, 'PENDING' => 1],
                'source_observation_rejected_row_count' => 1,
                'source_observation_rejection_reason_summary' => ['BAR_NON_POSITIVE_PRICE' => 1],
            ],
            'attempts' => [
                [
                    'attempt_number' => 1,
                    'reason_code' => 'RUN_SOURCE_TIMEOUT',
                    'http_status' => 504,
                    'throttle_delay_ms' => 1000,
                    'backoff_delay_ms' => 250,
                    'will_retry' => true,
                ],
                [
                    'attempt_number' => 2,
                    'reason_code' => null,
                    'http_status' => 200,
                    'throttle_delay_ms' => 1000,
                    'backoff_delay_ms' => 0,
                    'will_retry' => false,
                ],
            ],
        ]);
        $evidence->shouldReceive('exportEligibilityRowsForEvidencePublication')->once()->with('2026-04-21', 1201, true)->andReturn([
            ['trade_date' => '2026-04-21', 'ticker_id' => 101, 'eligible' => 1, 'reason_code' => null],
        ]);
        $evidence->shouldReceive('exportInvalidBarsRows')->once()->with('2026-04-21', 8124)->andReturn([
            ['trade_date' => '2026-04-21', 'ticker_id' => 999, 'source' => 'LOCAL_FILE', 'source_row_ref' => 'r1', 'invalid_reason_code' => 'BAR_NON_POSITIVE_PRICE'],
        ]);

        $service = new MarketDataEvidenceExportService($evidence, $publications, $corrections);
        $dir = sys_get_temp_dir().'/market_data_evidence_run_'.uniqid();
        $result = $service->exportRunEvidence(8124, $dir);

        $this->assertSame('run', $result['selector']['type']);
        $this->assertSame(8124, $result['selector']['id']);
        $this->assertSame('SUCCESS', $result['summary']['terminal_status']);
        $this->assertSame('READABLE', $result['summary']['publishability_state']);
        $this->assertSame(11, $result['file_count']);
        $this->assertSame($dir, $result['output_dir']);
        $this->assertFileExists($dir.'/run_summary.json');
        $this->assertFileExists($dir.'/publication_manifest.json');
        $this->assertFileExists($dir.'/run_event_summary.json');
        $this->assertFileExists($dir.'/source_attempt_telemetry.json');
        $this->assertFileExists($dir.'/eligibility_export.csv');
        $this->assertFileExists($dir.'/invalid_bars_export.csv');
        $this->assertFileExists($dir.'/anomaly_report.md');
        $this->assertFileExists($dir.'/lineage.json');
        $this->assertFileExists($dir.'/evidence_admission.json');
        $this->assertFileExists($dir.'/evidence_completeness.json');
        $this->assertFileExists($dir.'/evidence_pack.json');

        $summary = json_decode(file_get_contents($dir.'/run_summary.json'), true);
        $this->assertSame(8124, $summary['run_id']);
        $this->assertSame('SUCCESS', $summary['terminal_status']);
        $this->assertTrue($summary['is_current_publication']);
        $this->assertSame('ADMITTED_COMPLETE', $summary['evidence_admission_state']);
        $this->assertSame('PASS', $summary['coverage']['coverage_gate_state']);
        $this->assertSame('COVERAGE_THRESHOLD_MET', $summary['coverage']['coverage_reason_code']);
        $this->assertTrue($summary['coverage']['coverage_passed']);
        $this->assertSame(2, $summary['coverage']['coverage_universe_count']);
        $this->assertSame(101, $summary['coverage']['coverage_expected_count']);
        $this->assertSame(102, $summary['coverage']['coverage_delivered_count']);
        $this->assertSame(103, $summary['coverage']['coverage_delivered_valid_count']);
        $this->assertSame(104, $summary['coverage']['coverage_bar_not_expected_count']);
        $this->assertSame(105, $summary['coverage']['coverage_expectation_unknown_count']);
        $this->assertSame(str_repeat('a', 64), $summary['coverage']['coverage_universe_hash']);
        $this->assertSame(
            [['ticker_id' => 202, 'ticker_code' => 'EXCL']],
            $summary['coverage']['coverage_excluded_sample_json']
        );
        $this->assertSame(0.98, $summary['coverage']['coverage_min_threshold']);
        $this->assertSame([], $summary['coverage']['coverage_missing_sample']);
        $this->assertSame('API_FREE', $summary['source_context']['source_name']);
        $this->assertSame('PRIMARY', $summary['source_context']['source_priority']);
        $this->assertSame('api_free', $summary['source_context']['active_source_decision']);
        $this->assertSame(1, $summary['source_context']['retry_attempt_count']);
        $this->assertSame(['TRANSIENT' => 1], $summary['source_context']['failure_class_summary']);
        $this->assertSame(2, $summary['source_context']['source_observation_count']);
        $this->assertSame(str_repeat('c', 64), $summary['source_context']['source_observation_reference_manifest_hash']);
        $this->assertSame(['PASSED' => 1, 'PENDING' => 1], $summary['source_context']['schema_validation_state_summary']);
        $this->assertSame(['BAR_NON_POSITIVE_PRICE' => 1], $summary['source_context']['source_observation_rejection_reason_summary']);
        $this->assertSame(2, $summary['source_context']['attempt_count']);
        $this->assertSame('yes', $summary['source_context']['success_after_retry']);
        $this->assertSame(200, $summary['source_context']['final_http_status']);
        $this->assertSame('RUN_SOURCE_TIMEOUT', $summary['source_context']['final_reason_code']);
        $this->assertTrue($summary['source_context']['circuit_breaker_open']);
        $this->assertSame('CIRCUIT_OPEN', $summary['source_context']['source_protection_state']);
        $this->assertSame(95, $summary['source_context']['unattempted_acquisition_unit_count']);
        $this->assertSame('RUN_SOURCE_TIMEOUT', $summary['source_context']['circuit_breaker_trigger_reason_code']);
        $this->assertStringContainsString('source_priority=PRIMARY', $summary['source_context']['source_summary']);
        $this->assertStringContainsString('active_source_decision=api_free', $summary['source_context']['source_summary']);
        $this->assertStringContainsString('retry_attempt_count=1', $summary['source_context']['source_summary']);
        $this->assertStringContainsString('failure_class_summary={"TRANSIENT":1}', $summary['source_context']['source_summary']);
        $this->assertStringContainsString('source_protection_state=CIRCUIT_OPEN', $summary['source_context']['source_summary']);

        $attemptTelemetry = json_decode(file_get_contents($dir.'/source_attempt_telemetry.json'), true);
        $this->assertSame('STAGE_COMPLETED', $attemptTelemetry['event_type']);
        $this->assertSame('API_FREE', $attemptTelemetry['source_name']);
        $this->assertSame('PRIMARY', $attemptTelemetry['source_priority']);
        $this->assertSame('api_free', $attemptTelemetry['active_source_decision']);
        $this->assertSame(1, $attemptTelemetry['retry_attempt_count']);
        $this->assertSame(['TRANSIENT' => 1], $attemptTelemetry['failure_class_summary']);
        $this->assertTrue($attemptTelemetry['circuit_breaker_open']);
        $this->assertSame('CIRCUIT_OPEN', $attemptTelemetry['source_protection_state']);
        $this->assertSame(95, $attemptTelemetry['unattempted_acquisition_unit_count']);
        $this->assertSame('RUN_SOURCE_TIMEOUT', $attemptTelemetry['circuit_breaker_trigger_reason_code']);
        $this->assertCount(2, $attemptTelemetry['attempts']);
        $this->assertTrue($attemptTelemetry['attempts'][0]['will_retry']);
        $this->assertFalse($attemptTelemetry['attempts'][1]['will_retry']);

        $admission = json_decode(file_get_contents($dir.'/evidence_admission.json'), true);
        $this->assertSame('run', $admission['selector_type']);
        $this->assertSame(8124, $admission['selector_id']);
        $this->assertSame('ADMITTED_COMPLETE', $admission['evidence_admission_state']);
        $this->assertFalse($admission['database_lookup_required_after_export']);
        $this->assertFalse($admission['silent_missing_metadata_allowed']);

        $payload = json_decode(file_get_contents($dir.'/evidence_pack.json'), true);
        $this->assertSame(
            array_keys(MarketDataEvidenceExportService::RUN_COVERAGE_STORAGE_EXPORT_PATHS),
            array_values(MarketDataEvidenceExportService::RUN_COVERAGE_STORAGE_EXPORT_PATHS),
            'Stored coverage evidence must use its own exact payload key, never an alias for another field.'
        );
        foreach (MarketDataEvidenceExportService::RUN_COVERAGE_STORAGE_EXPORT_PATHS as $payloadField) {
            $this->assertArrayHasKey($payloadField, $payload['coverage_context'], 'Missing coverage export path '.$payloadField);
        }
        $this->assertSame(104, $payload['coverage_context']['coverage_bar_not_expected_count']);
        $this->assertSame(105, $payload['coverage_context']['coverage_expectation_unknown_count']);
        $this->assertSame(str_repeat('a', 64), $payload['coverage_context']['coverage_universe_hash']);
        $this->assertSame(
            [['ticker_id' => 202, 'ticker_code' => 'EXCL']],
            $payload['coverage_context']['coverage_excluded_sample_json']
        );
        $this->assertSame('ADMITTED_COMPLETE', $payload['evidence_admission']['evidence_admission_state']);
        $this->assertSame('coverage_gate_v1', $payload['run_summary']['coverage']['coverage_contract_version']);
        $this->assertSame('active_equity_universe_asof_trade_date', $payload['run_summary']['coverage']['coverage_universe_basis']);
        $this->assertSame('REPUBLISHED', $payload['run_summary']['publication_reprocess_summary']['execution_state']);
        $this->assertSame('AUTOMATED_READABLE_CORRECTION', $payload['run_summary']['publication_reprocess_summary']['republication_mode']);
        $this->assertSame([51], $payload['run_summary']['publication_reprocess_summary']['correction_ids']);
        $this->assertSame(51, $payload['run_summary']['publication_reprocess_summary']['correction_id']);
        $this->assertSame('API_FREE', $payload['run_summary']['source_context']['source_name']);
        $this->assertSame('RUN_SOURCE_TIMEOUT', $payload['run_summary']['source_context']['final_reason_code']);
        $this->assertSame('STAGE_COMPLETED', $payload['source_attempt_telemetry']['event_type']);
        $this->assertCount(2, $payload['source_attempt_telemetry']['attempts']);
        $this->assertSame(2, $payload['source_observation_audit']['source_observation_count']);
        $this->assertSame(str_repeat('c', 64), $payload['source_observation_audit']['source_observation_reference_manifest_hash']);
        $this->assertSame('COMPLETE', $payload['evidence_completeness']['evidence_completeness_state']);
        $this->assertFalse($payload['evidence_completeness']['database_lookup_required_after_export']);
        $this->assertSame(1201, $payload['publication_context']['publication_id']);
        $this->assertSame('RESOLVED_READABLE_CURRENT', $payload['pointer_context']['pointer_resolve_status']);
        $this->assertFalse($payload['fallback_context']['fallback_used']);
        $this->assertSame(8124, $payload['lineage']['run_to_publication']['run_id']);
        $this->assertSame(1201, $payload['lineage']['run_to_publication']['publication_id']);
        $this->assertSame('SUCCESS', $payload['publication_resolution']['terminal_status']);
        $this->assertSame('READABLE', $payload['publication_resolution']['publishability_state']);
        $this->assertSame('PASS', $payload['publication_resolution']['coverage_gate_state']);
        $this->assertSame(1201, $payload['publication_resolution']['publication_id']);
        $this->assertSame(8124, $payload['publication_resolution']['publication_run_id']);
        $this->assertSame('SEALED', $payload['publication_resolution']['publication_seal_state']);
        $this->assertTrue($payload['publication_resolution']['is_current_publication']);
        $this->assertTrue($payload['publication_resolution']['pointer_context']['readable_pointer_validated']);
        $this->assertSame('CURRENT_READABLE_PUBLICATION_AUDIT', $payload['publication_resolution']['evidence_resolution_mode']);
        $this->assertSame('CURRENT_POINTER_PUBLICATION', $payload['publication_resolution']['evidence_publication_scope']);
        $this->assertTrue($payload['publication_resolution']['current_pointer_required']);
        $this->assertSame('PUBLICATION_SCOPED', $payload['publication_resolution']['artifact_scope']);
        $this->assertSame('LINEAGE_VERIFIED', $payload['publication_resolution']['lineage_verification_status']);
        $this->assertSame('provider=generic | source_priority=PRIMARY | active_source_decision=api_free | retry_attempt_count=1 | timeout_seconds=15 | retry_max=3 | attempt_count=2 | success_after_retry=yes | final_http_status=200 | final_reason_code=RUN_SOURCE_TIMEOUT | failure_class_summary={"TRANSIENT":1} | source_protection_state=CIRCUIT_OPEN | circuit_breaker_open=yes | unattempted_acquisition_unit_count=95 | circuit_breaker_trigger_reason_code=RUN_SOURCE_TIMEOUT', $result['summary']['source_summary']);
        $this->assertSame('STAGE_COMPLETED', $result['summary']['source_attempt_event_type']);
        $this->assertSame(2, $result['summary']['source_attempt_count']);
    }


    public function test_export_replay_evidence_requires_explicit_trade_date()
    {
        $evidence = m::mock(EodEvidenceRepository::class);
        $publications = m::mock(EodPublicationRepository::class);
        $corrections = m::mock(EodCorrectionRepository::class);

        $evidence->shouldNotReceive('findReplayMetric');

        $service = new MarketDataEvidenceExportService($evidence, $publications, $corrections);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Replay evidence export requires explicit trade_date; latest-row resolution is not allowed on consumer read path.');

        $service->exportReplayEvidence(3001, null, sys_get_temp_dir().'/market_data_evidence_replay_'.uniqid());
    }

    public function test_export_run_evidence_exports_not_readable_failure_proof_without_read_path()
    {
        $run = (object) [
            'run_id' => 8125,
            'trade_date_requested' => '2026-04-22',
            'trade_date_effective' => '2026-04-21',
            'lifecycle_state' => 'COMPLETED',
            'terminal_status' => 'HELD',
            'quality_gate_state' => 'FAIL',
            'publishability_state' => 'NOT_READABLE',
            'stage' => 'FINALIZE',
            'source' => 'manual_file',
            'source_name' => 'LOCAL_FILE',
            'source_input_file' => 'storage/app/market-data/manual/partial.csv',
            'source_file_hash' => 'FILE_HASH',
            'source_file_hash_algorithm' => 'SHA-256',
            'source_file_size_bytes' => 4096,
            'source_file_row_count' => 5,
            'bars_rows_written' => 5,
            'invalid_bar_count' => 1,
            'coverage_universe_count' => 901,
            'coverage_available_count' => 5,
            'coverage_missing_count' => 896,
            'coverage_ratio' => 0.0055,
            'coverage_min_threshold' => 0.98,
            'coverage_gate_state' => 'FAIL',
            'coverage_threshold_mode' => 'MIN_RATIO',
            'coverage_universe_basis' => 'active_equity_universe_asof_trade_date',
            'coverage_contract_version' => 'coverage_gate_v1',
            'coverage_missing_sample_json' => json_encode(['BBCA', 'TLKM']),
            'final_reason_code' => 'COVERAGE_BELOW_THRESHOLD',
            'final_outcome_note' => 'Manual file imported but not promoted to readable publication.',
            'started_at' => '2026-04-22T17:00:00+07:00',
            'finished_at' => '2026-04-22T17:03:00+07:00',
            'created_at' => '2026-04-22T17:00:00+07:00',
            'updated_at' => '2026-04-22T17:03:00+07:00',
        ];

        $evidence = m::mock(EodEvidenceRepository::class);
        $publications = m::mock(EodPublicationRepository::class);
        $corrections = m::mock(EodCorrectionRepository::class);

        $evidence->shouldReceive('findRunById')->once()->with(8125)->andReturn($run);
        $evidence->shouldNotReceive('resolvePublicationForEvidenceAudit');
        $publications->shouldNotReceive('buildManifestByPublicationId');
        $publications->shouldReceive('findRawCurrentPublicationStateForTradeDate')->once()->with('2026-04-22')->andReturn(null);
        $evidence->shouldReceive('summarizeRunEvents')->once()->with(8125)->andReturn([
            'event_count' => 2,
            'first_event_time' => '2026-04-22T17:00:00+07:00',
            'last_event_time' => '2026-04-22T17:03:00+07:00',
            'first_event_type' => 'RUN_CREATED',
            'last_event_type' => 'RUN_HELD',
            'highest_severity' => 'WARN',
            'stage_counts' => ['INGEST' => 1, 'FINALIZE' => 1],
            'reason_code_counts' => ['COVERAGE_BELOW_THRESHOLD' => 1],
        ]);
        $evidence->shouldNotReceive('dominantReasonCodes');
        $evidence->shouldNotReceive('exportEligibilityRows');
        $evidence->shouldReceive('exportRunSourceAttemptTelemetry')->once()->with(8125)->andReturn([]);
        $evidence->shouldReceive('exportInvalidBarsRows')->once()->with('2026-04-22', 8125)->andReturn([]);

        $service = new MarketDataEvidenceExportService($evidence, $publications, $corrections);
        $dir = sys_get_temp_dir().'/market_data_evidence_run_'.uniqid();
        $result = $service->exportRunEvidence(8125, $dir);

        $this->assertSame('run', $result['selector']['type']);
        $this->assertSame(8125, $result['selector']['id']);
        $this->assertSame('HELD', $result['summary']['terminal_status']);
        $this->assertSame('NOT_READABLE', $result['summary']['publishability_state']);
        $this->assertSame('COVERAGE_BELOW_THRESHOLD', $result['summary']['final_reason_code']);
        $this->assertSame('INCOMPLETE', $result['summary']['evidence_completeness_state']);
        $this->assertSame('MISSING', $result['summary']['pointer_resolve_status']);
        $this->assertTrue($result['summary']['fallback_used']);
        $this->assertFileExists($dir.'/evidence_pack.json');
        $this->assertFileExists($dir.'/lineage.json');
        $this->assertFileExists($dir.'/evidence_admission.json');
        $this->assertFileExists($dir.'/evidence_completeness.json');
        $this->assertFileDoesNotExist($dir.'/publication_manifest.json');

        $admission = json_decode(file_get_contents($dir.'/evidence_admission.json'), true);
        $this->assertSame('ADMITTED_INCOMPLETE', $admission['evidence_admission_state']);
        $this->assertContains('artifact_hash_context', $admission['critical_missing_sections']);

        $payload = json_decode(file_get_contents($dir.'/evidence_pack.json'), true);
        $this->assertSame('ADMITTED_INCOMPLETE', $payload['evidence_admission']['evidence_admission_state']);
        $this->assertSame('INCOMPLETE', $payload['evidence_completeness']['evidence_completeness_state']);
        $this->assertContains('artifact_hash_context', $payload['evidence_completeness']['missing_sections']);
        $this->assertSame('FAIL', $payload['coverage_context']['coverage_gate_state']);
        $this->assertNull($payload['coverage_context']['coverage_expected_count']);
        $this->assertNull($payload['coverage_context']['coverage_delivered_count']);
        $this->assertNull($payload['coverage_context']['coverage_delivered_valid_count']);
        $this->assertNull($payload['coverage_context']['coverage_bar_not_expected_count']);
        $this->assertNull($payload['coverage_context']['coverage_expectation_unknown_count']);
        $this->assertNull($payload['coverage_context']['coverage_universe_hash']);
        $this->assertNull($payload['coverage_context']['coverage_excluded_sample_json']);
        $this->assertSame(5, $payload['coverage_context']['coverage_available_count']);
        $this->assertSame(['BBCA', 'TLKM'], $payload['coverage_context']['coverage_missing_sample_json']);
        $this->assertNull($payload['coverage_context']['expected_bar_count']);
        $this->assertSame(5, $payload['coverage_context']['available_bar_count']);
        $this->assertSame(896, $payload['coverage_context']['missing_bar_count']);
        $this->assertSame('manual_file', $payload['source_context']['source_mode']);
        $this->assertSame('LOCAL_FILE', $payload['source_context']['source_name']);
        $this->assertSame('FILE_HASH', $payload['source_context']['source_file_hash']);
        $this->assertSame('NOT_CREATED_OR_NOT_READABLE', $payload['publication_context']['publication_state']);
        $this->assertSame('MISSING', $payload['pointer_context']['pointer_resolve_status']);
        $this->assertSame('CURRENT_POINTER_ROW_MISSING', $payload['pointer_context']['pointer_mismatch_reason']);
        $this->assertTrue($payload['fallback_context']['fallback_used']);
        $this->assertSame('COVERAGE_BELOW_THRESHOLD', $payload['fallback_context']['fallback_reason_code']);
        $this->assertSame(8125, $payload['lineage']['run_to_finalize_decision']['run_id']);
    }


    public function test_export_run_evidence_resolves_historical_sealed_publication_without_current_pointer_dependency()
    {
        $run = (object) [
            'run_id' => 8126,
            'trade_date_requested' => '2026-04-20',
            'trade_date_effective' => '2026-04-20',
            'lifecycle_state' => 'COMPLETED',
            'terminal_status' => 'SUCCESS',
            'quality_gate_state' => 'PASS',
            'publishability_state' => 'READABLE',
            'stage' => 'FINALIZE',
            'source' => 'manual_file',
            'coverage_universe_count' => 2,
            'coverage_available_count' => 2,
            'coverage_missing_count' => 0,
            'coverage_ratio' => 1.0,
            'coverage_min_threshold' => 0.98,
            'coverage_gate_state' => 'PASS',
            'coverage_threshold_mode' => 'MIN_RATIO',
            'coverage_universe_basis' => 'active_equity_universe_asof_trade_date',
            'coverage_contract_version' => 'coverage_gate_v1',
            'coverage_missing_sample_json' => json_encode([]),
            'final_reason_code' => 'COVERAGE_THRESHOLD_MET',
            'final_outcome_note' => 'Historical publication remains sealed and evidenceable.',
            'publication_id' => 1200,
            'publication_version' => 1,
            'is_current_publication' => 0,
            'bars_rows_written' => 2,
            'indicators_rows_written' => 2,
            'eligibility_rows_written' => 2,
            'bars_batch_hash' => 'HB_OLD',
            'indicators_batch_hash' => 'HI_OLD',
            'eligibility_batch_hash' => 'HE_OLD',
            'sealed_at' => '2026-04-20T17:20:00+07:00',
            'config_version' => 'cfg_v1',
            'created_at' => '2026-04-20T17:00:00+07:00',
            'updated_at' => '2026-04-20T17:21:00+07:00',
        ];
        $publication = (object) [
            'publication_id' => 1200,
            'run_id' => 8126,
            'publication_version' => 1,
            'is_current' => 0,
            'seal_state' => 'SEALED',
            'sealed_at' => '2026-04-20T17:20:00+07:00',
            'pointer_publication_id' => 1201,
            'pointer_run_id' => 8127,
            'pointer_publication_version' => 2,
            'evidence_resolution_mode' => 'HISTORICAL_PUBLICATION_AUDIT',
            'evidence_publication_scope' => 'HISTORICAL_SEALED_PUBLICATION',
            'evidence_selector_type' => 'run_id',
            'evidence_selector_id' => 8126,
            'current_pointer_required' => false,
            'current_pointer_status' => 'NOT_CURRENT_POINTER',
            'historical_publication_allowed' => true,
            'artifact_scope' => 'PUBLICATION_SCOPED',
            'coverage_basis_publication_id' => 1200,
            'coverage_basis_run_id' => 8126,
            'lineage_verification_status' => 'LINEAGE_VERIFIED',
            'evidence_reason_code' => 'HISTORICAL_SEALED_PUBLICATION_RESOLVED',
        ];
        $manifest = (object) [
            'publication_id' => 1200,
            'trade_date' => '2026-04-20',
            'run_id' => 8126,
            'publication_version' => 1,
            'is_current' => 0,
            'seal_state' => 'SEALED',
            'sealed_at' => '2026-04-20T17:20:00+07:00',
            'bars_batch_hash' => 'HB_OLD',
            'indicators_batch_hash' => 'HI_OLD',
            'eligibility_batch_hash' => 'HE_OLD',
            'bars_rows_written' => 2,
            'indicators_rows_written' => 2,
            'eligibility_rows_written' => 2,
            'trade_date_effective' => '2026-04-20',
        ];

        $evidence = m::mock(EodEvidenceRepository::class);
        $publications = m::mock(EodPublicationRepository::class);
        $corrections = m::mock(EodCorrectionRepository::class);

        $evidence->shouldReceive('findRunById')->once()->with(8126)->andReturn($run);
        $evidence->shouldReceive('resolvePublicationForEvidenceAudit')->once()->with([
            'type' => 'run_id',
            'run_id' => 8126,
            'trade_date' => '2026-04-20',
        ])->andReturn($publication);
        $publications->shouldNotReceive('findReadableCurrentPublicationForRun');
        $publications->shouldReceive('buildManifestByPublicationId')->once()->with(1200)->andReturn($manifest);
        $evidence->shouldReceive('summarizeRunEvents')->once()->with(8126)->andReturn([
            'event_count' => 1,
            'first_event_time' => '2026-04-20T17:21:00+07:00',
            'last_event_time' => '2026-04-20T17:21:00+07:00',
            'first_event_type' => 'RUN_FINALIZED',
            'last_event_type' => 'RUN_FINALIZED',
            'highest_severity' => 'INFO',
            'stage_counts' => ['FINALIZE' => 1],
            'reason_code_counts' => [],
        ]);
        $evidence->shouldReceive('dominantReasonCodesForEvidencePublication')->once()->with(8126, '2026-04-20', 1200, false)->andReturn([]);
        $evidence->shouldReceive('exportEligibilityRowsForEvidencePublication')->once()->with('2026-04-20', 1200, false)->andReturn([
            ['trade_date' => '2026-04-20', 'ticker_id' => 101, 'eligible' => 1, 'reason_code' => null],
        ]);
        $evidence->shouldReceive('exportRunSourceAttemptTelemetry')->once()->with(8126)->andReturn([]);
        $evidence->shouldReceive('exportInvalidBarsRows')->once()->with('2026-04-20', 8126)->andReturn([]);

        $service = new MarketDataEvidenceExportService($evidence, $publications, $corrections);
        $dir = sys_get_temp_dir().'/market_data_evidence_historical_run_'.uniqid();
        $result = $service->exportRunEvidence(8126, $dir);

        $this->assertSame('run', $result['selector']['type']);
        $this->assertSame('COMPLETE', $result['summary']['evidence_completeness_state']);
        $this->assertSame('HISTORICAL_SEALED_PUBLICATION_RESOLVED', $result['summary']['pointer_resolve_status']);

        $payload = json_decode(file_get_contents($dir.'/evidence_pack.json'), true);
        $this->assertSame('HISTORICAL_PUBLICATION_AUDIT', $payload['publication_context']['evidence_resolution_mode']);
        $this->assertSame('HISTORICAL_SEALED_PUBLICATION', $payload['publication_context']['evidence_publication_scope']);
        $this->assertFalse($payload['publication_context']['current_pointer_required']);
        $this->assertTrue($payload['publication_context']['historical_publication_allowed']);
        $this->assertSame('PUBLICATION_SCOPED', $payload['publication_context']['artifact_scope']);
        $this->assertSame('LINEAGE_VERIFIED', $payload['publication_context']['lineage_verification_status']);
        $this->assertSame('HISTORICAL_PUBLICATION_NOT_CURRENT_POINTER', $payload['pointer_context']['pointer_mismatch_reason']);
        $this->assertSame('HISTORICAL_SEALED_PUBLICATION_RESOLVED', $payload['publication_context']['evidence_reason_code']);
        $this->assertSame(1200, $payload['lineage']['publication_to_pointer']['publication_id']);
        $this->assertFalse($payload['lineage']['publication_to_pointer']['current_pointer_required']);
        $this->assertTrue($payload['lineage']['publication_to_pointer']['historical_publication_allowed']);
    }

    public function test_export_run_evidence_normalizes_legacy_blocked_coverage_state_and_preserves_raw_trace(): void
    {
        $run = (object) [
            'run_id' => 8130,
            'trade_date_requested' => '2026-04-23',
            'trade_date_effective' => '2026-04-23',
            'lifecycle_state' => 'COMPLETED',
            'terminal_status' => 'FAILED',
            'quality_gate_state' => 'BLOCKED',
            'publishability_state' => 'NOT_READABLE',
            'stage' => 'FINALIZE',
            'source' => 'manual_file',
            'coverage_universe_count' => 0,
            'coverage_available_count' => 0,
            'coverage_missing_count' => 0,
            'coverage_ratio' => null,
            'coverage_min_threshold' => 0.98,
            'coverage_gate_state' => 'BLOCKED',
            'coverage_threshold_mode' => 'MIN_RATIO',
            'coverage_universe_basis' => 'active_equity_universe_asof_trade_date',
            'coverage_contract_version' => 'coverage_gate_v1',
            'coverage_missing_sample_json' => json_encode([]),
            'final_reason_code' => null,
            'final_outcome_note' => 'Legacy coverage state was normalized during evidence export.',
            'started_at' => '2026-04-23T17:00:00+07:00',
            'finished_at' => '2026-04-23T17:01:00+07:00',
        ];

        $evidence = m::mock(EodEvidenceRepository::class);
        $publications = m::mock(EodPublicationRepository::class);
        $corrections = m::mock(EodCorrectionRepository::class);

        $evidence->shouldReceive('findRunById')->once()->with(8130)->andReturn($run);
        $evidence->shouldNotReceive('resolvePublicationForEvidenceAudit');
        $publications->shouldNotReceive('buildManifestByPublicationId');
        $publications->shouldReceive('findRawCurrentPublicationStateForTradeDate')->once()->with('2026-04-23')->andReturn(null);
        $evidence->shouldReceive('summarizeRunEvents')->once()->with(8130)->andReturn([
            'event_count' => 1,
            'first_event_time' => '2026-04-23T17:01:00+07:00',
            'last_event_time' => '2026-04-23T17:01:00+07:00',
            'first_event_type' => 'RUN_FINALIZED',
            'last_event_type' => 'RUN_FINALIZED',
            'highest_severity' => 'WARN',
            'stage_counts' => ['FINALIZE' => 1],
            'reason_code_counts' => ['RUN_COVERAGE_NOT_EVALUABLE' => 1],
        ]);
        $evidence->shouldNotReceive('dominantReasonCodes');
        $evidence->shouldNotReceive('exportEligibilityRows');
        $evidence->shouldReceive('exportRunSourceAttemptTelemetry')->once()->with(8130)->andReturn([]);
        $evidence->shouldReceive('exportInvalidBarsRows')->once()->with('2026-04-23', 8130)->andReturn([]);

        $service = new MarketDataEvidenceExportService($evidence, $publications, $corrections);
        $dir = sys_get_temp_dir().'/market_data_evidence_run_'.uniqid();
        $result = $service->exportRunEvidence(8130, $dir);

        $payload = json_decode(file_get_contents($dir.'/evidence_pack.json'), true);
        $summary = json_decode(file_get_contents($dir.'/run_summary.json'), true);

        $this->assertSame('NOT_EVALUABLE', $result['summary']['coverage_gate_state']);
        $this->assertSame('BLOCKED', $summary['quality_gate_state']);
        $this->assertSame('NOT_EVALUABLE', $summary['coverage']['coverage_gate_state']);
        $this->assertSame('BLOCKED', $summary['coverage']['legacy_coverage_gate_state_raw']);
        $this->assertSame('RUN_COVERAGE_NOT_EVALUABLE', $summary['coverage']['coverage_reason_code']);
        $this->assertSame('NOT_EVALUABLE', $payload['coverage_context']['coverage_gate_state']);
        $this->assertSame('BLOCKED', $payload['coverage_context']['legacy_coverage_gate_state_raw']);
        $this->assertSame('RUN_COVERAGE_NOT_EVALUABLE', $payload['coverage_context']['coverage_reason_code']);
    }


}
