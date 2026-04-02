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
            'status' => 'HELD',
            'comparison_result' => 'EXPECTED_DEGRADE',
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
        $this->assertSame('HELD', $result['summary']['status']);
        $this->assertSame(5, $result['file_count']);
        $this->assertSame($dir, $result['output_dir']);
        $this->assertFileExists($dir.'/replay_result.json');
        $this->assertFileExists($dir.'/replay_expected_state.json');
        $this->assertFileExists($dir.'/replay_actual_state.json');
        $this->assertFileExists($dir.'/replay_reason_code_counts.json');
        $this->assertFileExists($dir.'/replay_evidence_pack.json');

        $replayResult = json_decode(file_get_contents($dir.'/replay_result.json'), true);
        $this->assertSame(3001, $replayResult['replay_id']);
        $this->assertSame('EXPECTED_DEGRADE', $replayResult['comparison_result']);
        $this->assertSame('cfg_2025_12_v2', $replayResult['config_identity']);
        $this->assertSame('FAIL', $replayResult['coverage']['coverage_gate_state']);
        $this->assertSame('FAIL', $replayResult['expected_coverage']['coverage_gate_state']);
        $this->assertSame(['BBCA', 'TLKM'], $replayResult['coverage']['coverage_missing_sample']);

        $expectedState = json_decode(file_get_contents($dir.'/replay_expected_state.json'), true);
        $this->assertSame('HELD', $expectedState['status']);
        $this->assertSame('A1', $expectedState['bars_batch_hash']);
        $this->assertCount(2, $expectedState['reason_code_counts']);
        $this->assertSame(1000, $expectedState['coverage']['coverage_universe_count']);

        $actualState = json_decode(file_get_contents($dir.'/replay_actual_state.json'), true);
        $this->assertSame('HELD', $actualState['status']);
        $this->assertSame('B1', $actualState['indicators_batch_hash']);
        $this->assertCount(2, $actualState['reason_code_counts']);
        $this->assertSame(['BBCA', 'TLKM'], $actualState['coverage']['coverage_missing_sample']);

        $payload = json_decode(file_get_contents($dir.'/replay_evidence_pack.json'), true);
        $this->assertSame('HELD', $payload['replay_result']['status']);
        $this->assertSame('cfg_2025_12_v2', $payload['expected_state']['config_identity']);
        $this->assertCount(2, $payload['reason_code_counts']);
    }
}
