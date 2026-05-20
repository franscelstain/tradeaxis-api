<?php

use App\Application\MarketData\Services\MarketDataEvidenceExportService;
use App\Infrastructure\Persistence\MarketData\EodCorrectionRepository;
use App\Infrastructure\Persistence\MarketData\EodEvidenceRepository;
use App\Infrastructure\Persistence\MarketData\EodPublicationRepository;
use Mockery as m;
use PHPUnit\Framework\TestCase;

class ReplayEvidenceExportServiceTest extends TestCase
{
    protected function tearDown(): void
    {
        m::close();
    }

    public function test_export_replay_evidence_writes_replay_result_and_reason_code_summary()
    {
        $metric = (object) [
            'replay_id' => 3001,
            'trade_date' => '2025-12-10',
            'trade_date_effective' => '2025-12-09',
            'source' => 'manual_file',
            'source_mode' => 'manual_file',
            'source_name' => 'LOCAL_FILE',
            'source_provider' => 'manual_import',
            'source_input_file' => 'storage/app/market-data/manual/degraded.csv',
            'source_file_hash' => 'FILE_HASH_ACTUAL',
            'source_file_hash_algorithm' => 'SHA-256',
            'source_file_size_bytes' => 2048,
            'source_file_row_count' => 842,
            'status' => 'HELD',
            'publishability_state' => 'NOT_READABLE',
            'publication_id' => null,
            'publication_run_id' => null,
            'is_current_publication' => 0,
            'comparison_result' => 'EXPECTED_DEGRADE',
            'replay_status' => 'PASS',
            'comparison_note' => 'coverage intentionally degraded',
            'artifact_changed_scope' => 'bars_indicators_eligibility',
            'config_identity' => 'cfg_2025_12_v2',
            'publication_version' => null,
            'coverage_universe_count' => 1000,
            'coverage_available_count' => 842,
            'coverage_missing_count' => 158,
            'coverage_ratio' => '0.8420',
            'coverage_min_threshold' => '0.9800',
            'coverage_gate_state' => 'FAIL',
            'coverage_threshold_mode' => 'MIN_RATIO',
            'coverage_universe_basis' => 'active_equity_universe_asof_trade_date',
            'coverage_contract_version' => 'coverage_gate_v1',
            'coverage_missing_sample_json' => json_encode(['BBCA', 'TLKM']),
            'bars_rows_written' => 842,
            'indicators_rows_written' => 830,
            'eligibility_rows_written' => 1000,
            'eligible_count' => 650,
            'invalid_bar_count' => 18,
            'invalid_indicator_count' => 170,
            'warning_count' => 50,
            'hard_reject_count' => 12,
            'bars_batch_hash' => 'A1',
            'indicators_batch_hash' => 'B1',
            'eligibility_batch_hash' => 'C1',
            'seal_state' => 'UNSEALED',
            'sealed_at' => null,
            'expected_status' => 'HELD',
            'expected_terminal_status' => 'HELD',
            'expected_publishability_state' => 'NOT_READABLE',
            'expected_source_mode' => 'manual_file',
            'expected_source_name' => 'LOCAL_FILE',
            'expected_source_provider' => 'manual_import',
            'expected_source_input_file' => 'storage/app/market-data/manual/degraded.csv',
            'expected_source_file_hash' => 'FILE_HASH_ACTUAL',
            'expected_source_file_hash_algorithm' => 'SHA-256',
            'expected_source_file_size_bytes' => 2048,
            'expected_source_file_row_count' => 842,
            'expected_publication_id' => null,
            'expected_publication_run_id' => null,
            'expected_is_current_publication' => 0,
            'expected_trade_date_effective' => '2025-12-09',
            'expected_seal_state' => 'UNSEALED',
            'expected_config_identity' => 'cfg_2025_12_v2',
            'expected_publication_version' => 7,
            'expected_coverage_universe_count' => 1000,
            'expected_coverage_available_count' => 842,
            'expected_coverage_missing_count' => 158,
            'expected_coverage_ratio' => '0.8420',
            'expected_coverage_min_threshold' => '0.9800',
            'expected_coverage_gate_state' => 'FAIL',
            'expected_coverage_threshold_mode' => 'MIN_RATIO',
            'expected_coverage_universe_basis' => 'active_equity_universe_asof_trade_date',
            'expected_coverage_contract_version' => 'coverage_gate_v1',
            'expected_coverage_missing_sample_json' => json_encode(['BBCA', 'TLKM']),
            'expected_bars_batch_hash' => 'A1',
            'expected_indicators_batch_hash' => 'B1',
            'expected_eligibility_batch_hash' => 'C1',
            'expected_reason_code_counts_json' => json_encode([
                'ELIG_MISSING_BAR' => 120,
                'IND_INSUFFICIENT_HISTORY' => 80,
            ]),
            'mismatch_summary' => null,
            'created_at' => '2025-12-10T17:15:00+07:00',
        ];

        $evidence = m::mock(EodEvidenceRepository::class);
        $publications = m::mock(EodPublicationRepository::class);
        $corrections = m::mock(EodCorrectionRepository::class);

        $evidence->shouldReceive('findReplayMetric')->once()->with(3001, '2025-12-10')->andReturn($metric);
        $evidence->shouldReceive('replayReasonCodeCounts')->once()->with(3001, '2025-12-10')->andReturn([
            ['reason_code' => 'ELIG_MISSING_BAR', 'reason_count' => 120],
            ['reason_code' => 'IND_INSUFFICIENT_HISTORY', 'reason_count' => 80],
        ]);

        $service = new MarketDataEvidenceExportService($evidence, $publications, $corrections);
        $dir = sys_get_temp_dir().'/market_data_evidence_replay_'.uniqid();
        $result = $service->exportReplayEvidence(3001, '2025-12-10', $dir);

        $this->assertSame('replay', $result['selector']['type']);
        $this->assertSame(3001, $result['selector']['id']);
        $this->assertSame('EXPECTED_DEGRADE', $result['summary']['comparison_result']);
        $this->assertSame('PASS', $result['summary']['replay_status']);
        $this->assertSame('HELD', $result['summary']['status']);
        $this->assertSame(6, $result['file_count']);
        $this->assertSame($dir, $result['output_dir']);
        $this->assertFileExists($dir.'/replay_result.json');
        $this->assertFileExists($dir.'/replay_expected_state.json');
        $this->assertFileExists($dir.'/replay_actual_state.json');
        $this->assertFileExists($dir.'/replay_reason_code_counts.json');
        $this->assertFileExists($dir.'/evidence_admission.json');
        $this->assertFileExists($dir.'/replay_evidence_pack.json');

        $replayResult = json_decode(file_get_contents($dir.'/replay_result.json'), true);
        $this->assertSame(3001, $replayResult['replay_id']);
        $this->assertSame('EXPECTED_DEGRADE', $replayResult['comparison_result']);
        $this->assertSame('PASS', $replayResult['replay_status']);
        $this->assertSame('cfg_2025_12_v2', $replayResult['config_identity']);
        $this->assertSame('FAIL', $replayResult['coverage']['coverage_gate_state']);
        $this->assertSame('FAIL', $replayResult['expected_coverage']['coverage_gate_state']);
        $this->assertSame(['BBCA', 'TLKM'], $replayResult['coverage']['coverage_missing_sample']);

        $expectedState = json_decode(file_get_contents($dir.'/replay_expected_state.json'), true);
        $this->assertSame('HELD', $expectedState['status']);
        $this->assertSame('A1', $expectedState['bars_batch_hash']);
        $this->assertSame('manual_file', $expectedState['source_context']['source_mode']);
        $this->assertSame('FILE_HASH_ACTUAL', $expectedState['source_context']['source_file_hash']);
        $this->assertSame('NOT_READABLE', $expectedState['publication_context']['publication_publishability_state']);
        $this->assertSame('NOT_RESOLVED_READABLE_CURRENT', $expectedState['pointer_context']['pointer_resolve_status']);
        $this->assertCount(2, $expectedState['reason_code_counts']);
        $this->assertSame(1000, $expectedState['coverage']['coverage_universe_count']);

        $actualState = json_decode(file_get_contents($dir.'/replay_actual_state.json'), true);
        $this->assertSame('HELD', $actualState['status']);
        $this->assertSame('B1', $actualState['indicators_batch_hash']);
        $this->assertSame('manual_file', $actualState['source_context']['source_mode']);
        $this->assertSame('FILE_HASH_ACTUAL', $actualState['source_context']['source_file_hash']);
        $this->assertSame(842, $actualState['source_context']['accepted_row_count']);
        $this->assertSame(18, $actualState['source_context']['invalid_row_count']);
        $this->assertSame('NOT_READABLE', $actualState['publication_context']['publication_publishability_state']);
        $this->assertSame('NOT_RESOLVED_READABLE_CURRENT', $actualState['pointer_context']['pointer_resolve_status']);
        $this->assertCount(2, $actualState['reason_code_counts']);
        $this->assertSame(['BBCA', 'TLKM'], $actualState['coverage']['coverage_missing_sample']);

        $admission = json_decode(file_get_contents($dir.'/evidence_admission.json'), true);
        $this->assertSame('replay', $admission['selector_type']);
        $this->assertSame(3001, $admission['selector_id']);
        $this->assertSame('ADMITTED_COMPLETE', $admission['evidence_admission_state']);

        $payload = json_decode(file_get_contents($dir.'/replay_evidence_pack.json'), true);
        $this->assertSame('ADMITTED_COMPLETE', $payload['evidence_admission']['evidence_admission_state']);
        $this->assertSame('PASS', $payload['summary']['replay_status']);
        $this->assertSame('PASS', $payload['replay_result']['replay_status']);
        $this->assertSame('HELD', $payload['replay_result']['status']);
        $this->assertSame('cfg_2025_12_v2', $payload['expected_state']['config_identity']);
        $this->assertSame('manual_file', $payload['replay_result']['source_context']['source_mode']);
        $this->assertSame('manual_file', $payload['replay_result']['expected_source_context']['source_mode']);
        $this->assertSame('NOT_RESOLVED_READABLE_CURRENT', $payload['replay_result']['pointer_context']['pointer_resolve_status']);
        $this->assertSame('NOT_RESOLVED_READABLE_CURRENT', $payload['replay_result']['expected_pointer_context']['pointer_resolve_status']);
        $this->assertCount(2, $payload['reason_code_counts']);
    }

    public function test_export_replay_evidence_preserves_context_pointer_switch_flag_for_unchanged_correction()
    {
        $actualContext = [
            'actual_pointer_context' => [
                'pointer_publication_id' => 5,
                'pointer_run_id' => 6,
                'pointer_publication_version' => 4,
                'pointer_resolve_status' => 'RESOLVED_READABLE_CURRENT',
                'pointer_switched' => false,
            ],
        ];
        $expectedContext = [
            'expected_pointer_context' => [
                'pointer_publication_id' => 5,
                'pointer_run_id' => 6,
                'pointer_publication_version' => 4,
                'pointer_resolve_status' => 'RESOLVED_READABLE_CURRENT',
                'pointer_switched' => false,
            ],
        ];
        $metric = (object) [
            'replay_id' => 3002,
            'replay_suite' => 'runtime_generated_valid_case',
            'replay_case' => 'correction-unchanged-run-8',
            'fixture_id' => 'correction-unchanged-run-8',
            'fixture_version' => 'generated-v1',
            'fixture_schema_version' => 'replay_fixture_v2',
            'fixture_source' => 'unit_test',
            'fixture_created_at' => '2026-05-20T00:00:00+07:00',
            'trade_date' => '2026-02-18',
            'trade_date_effective' => '2026-02-18',
            'source' => 'manual_file',
            'source_mode' => 'manual_file',
            'source_name' => 'LOCAL_FILE',
            'source_provider' => null,
            'source_input_file' => '2026-02-18.csv',
            'source_file_hash' => 'FILE_HASH',
            'source_file_hash_algorithm' => 'SHA-256',
            'source_file_size_bytes' => 1234,
            'source_file_row_count' => 901,
            'status' => 'SUCCESS',
            'publishability_state' => 'READABLE',
            'publication_id' => 5,
            'publication_run_id' => 6,
            'publication_version' => 4,
            'is_current_publication' => 1,
            'comparison_result' => 'MATCH',
            'replay_status' => 'PASS',
            'comparison_note' => 'unchanged correction preserved baseline',
            'artifact_changed_scope' => 'none',
            'config_identity' => 'v1',
            'coverage_universe_count' => 913,
            'coverage_available_count' => 901,
            'coverage_missing_count' => 12,
            'coverage_ratio' => '0.986857',
            'coverage_min_threshold' => '0.9800',
            'coverage_gate_state' => 'PASS',
            'coverage_threshold_mode' => 'MIN_RATIO',
            'coverage_universe_basis' => 'ACTIVE_LISTED_EQUITY_AS_OF_DATE',
            'coverage_contract_version' => 'coverage_gate_v1',
            'coverage_missing_sample_json' => json_encode([]),
            'bars_rows_written' => 901,
            'indicators_rows_written' => 901,
            'eligibility_rows_written' => 913,
            'eligible_count' => 0,
            'invalid_bar_count' => 0,
            'invalid_indicator_count' => 901,
            'warning_count' => null,
            'hard_reject_count' => 913,
            'bars_batch_hash' => 'A1',
            'indicators_batch_hash' => 'B1',
            'eligibility_batch_hash' => 'C1',
            'seal_state' => 'SEALED',
            'sealed_at' => '2026-05-20 00:00:00',
            'expected_status' => 'SUCCESS',
            'expected_terminal_status' => 'SUCCESS',
            'expected_publishability_state' => 'READABLE',
            'expected_source_mode' => 'manual_file',
            'expected_source_name' => 'LOCAL_FILE',
            'expected_source_provider' => null,
            'expected_source_input_file' => '2026-02-18.csv',
            'expected_source_file_hash' => 'FILE_HASH',
            'expected_source_file_hash_algorithm' => 'SHA-256',
            'expected_source_file_size_bytes' => 1234,
            'expected_source_file_row_count' => 901,
            'expected_publication_id' => 5,
            'expected_publication_run_id' => 6,
            'expected_publication_version' => 4,
            'expected_is_current_publication' => 1,
            'expected_trade_date_effective' => '2026-02-18',
            'expected_seal_state' => 'SEALED',
            'expected_config_identity' => 'v1',
            'expected_coverage_universe_count' => 913,
            'expected_coverage_available_count' => 901,
            'expected_coverage_missing_count' => 12,
            'expected_coverage_ratio' => '0.986857',
            'expected_coverage_min_threshold' => '0.9800',
            'expected_coverage_gate_state' => 'PASS',
            'expected_coverage_threshold_mode' => 'MIN_RATIO',
            'expected_coverage_universe_basis' => 'ACTIVE_LISTED_EQUITY_AS_OF_DATE',
            'expected_coverage_contract_version' => 'coverage_gate_v1',
            'expected_coverage_missing_sample_json' => json_encode([]),
            'expected_bars_batch_hash' => 'A1',
            'expected_indicators_batch_hash' => 'B1',
            'expected_eligibility_batch_hash' => 'C1',
            'correction_id' => 3,
            'correction_status' => 'CONSUMED_CURRENT',
            'correction_outcome' => 'UNCHANGED',
            'correction_reseal_status' => 'NOT_RESEALED_UNCHANGED',
            'correction_publication_switch' => 0,
            'baseline_publication_id' => 5,
            'candidate_publication_id' => 7,
            'expected_correction_id' => 3,
            'expected_correction_status' => 'CONSUMED_CURRENT',
            'expected_correction_outcome' => 'UNCHANGED',
            'expected_correction_reseal_status' => 'NOT_RESEALED_UNCHANGED',
            'expected_correction_publication_switch' => 0,
            'expected_baseline_publication_id' => 5,
            'expected_candidate_publication_id' => 7,
            'mismatch_summary' => null,
            'mismatch_count' => 0,
            'mismatch_reason_codes_json' => json_encode([]),
            'mismatches_json' => json_encode([]),
            'actual_context_json' => json_encode($actualContext),
            'expected_context_json' => json_encode($expectedContext),
            'ignored_volatile_fields_json' => json_encode([]),
            'deterministic_fields_checked_json' => json_encode(['pointer_switched']),
            'final_reason_code' => 'COVERAGE_THRESHOLD_MET',
            'created_at' => '2026-05-20T00:00:00+07:00',
        ];

        $evidence = m::mock(EodEvidenceRepository::class);
        $publications = m::mock(EodPublicationRepository::class);
        $corrections = m::mock(EodCorrectionRepository::class);

        $evidence->shouldReceive('findReplayMetric')->once()->with(3002, '2026-02-18')->andReturn($metric);
        $evidence->shouldReceive('replayReasonCodeCounts')->once()->with(3002, '2026-02-18')->andReturn([]);

        $service = new MarketDataEvidenceExportService($evidence, $publications, $corrections);
        $dir = sys_get_temp_dir().'/market_data_evidence_replay_pointer_'.uniqid();
        $service->exportReplayEvidence(3002, '2026-02-18', $dir);

        $replayResult = json_decode(file_get_contents($dir.'/replay_result.json'), true);
        $actualState = json_decode(file_get_contents($dir.'/replay_actual_state.json'), true);
        $expectedState = json_decode(file_get_contents($dir.'/replay_expected_state.json'), true);

        $this->assertFalse($replayResult['pointer_context']['pointer_switched']);
        $this->assertFalse($replayResult['expected_pointer_context']['pointer_switched']);
        $this->assertFalse($actualState['pointer_context']['pointer_switched']);
        $this->assertFalse($expectedState['pointer_context']['pointer_switched']);
    }
}
