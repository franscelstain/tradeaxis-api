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
            'lifecycle_state' => 'FINALIZED',
            'terminal_status' => 'SUCCESS',
            'quality_gate_state' => 'PASS',
            'publishability_state' => 'READABLE',
            'stage' => 'FINALIZE',
            'source' => 'manual_file',
            'coverage_ratio' => 1.0,
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
        ];
        $publication = (object) ['publication_id' => 1201, 'run_id' => 8124, 'publication_version' => 1, 'is_current' => 1, 'seal_state' => 'SEALED'];
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
        $evidence->shouldReceive('findPublicationForRun')->once()->with(8124)->andReturn($publication);
        $publications->shouldReceive('findCurrentPublicationForTradeDate')->once()->with('2026-04-21')->andReturn($publication);
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
        $evidence->shouldReceive('dominantReasonCodes')->once()->andReturn([]);
        $evidence->shouldReceive('exportEligibilityRows')->once()->andReturn([
            ['trade_date' => '2026-04-21', 'ticker_id' => 101, 'eligible' => 1, 'reason_code' => null],
        ]);
        $evidence->shouldReceive('exportInvalidBarsRows')->once()->andReturn([
            ['trade_date' => '2026-04-21', 'ticker_id' => 999, 'source' => 'LOCAL_FILE', 'source_row_ref' => 'r1', 'invalid_reason_code' => 'BAR_NON_POSITIVE_PRICE'],
        ]);

        $service = new MarketDataEvidenceExportService($evidence, $publications, $corrections);
        $dir = sys_get_temp_dir().'/market_data_evidence_run_'.uniqid();
        $result = $service->exportRunEvidence(8124, $dir);

        $this->assertSame($dir, $result['output_dir']);
        $this->assertFileExists($dir.'/run_summary.json');
        $this->assertFileExists($dir.'/publication_manifest.json');
        $this->assertFileExists($dir.'/run_event_summary.json');
        $this->assertFileExists($dir.'/eligibility_export.csv');
        $this->assertFileExists($dir.'/invalid_bars_export.csv');
        $this->assertFileExists($dir.'/anomaly_report.md');
        $this->assertFileExists($dir.'/evidence_pack.json');

        $summary = json_decode(file_get_contents($dir.'/run_summary.json'), true);
        $this->assertSame(8124, $summary['run_id']);
        $this->assertSame('SUCCESS', $summary['terminal_status']);
        $this->assertTrue($summary['is_current_publication']);
    }
}
