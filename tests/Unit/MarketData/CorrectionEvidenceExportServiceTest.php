<?php

use App\Application\MarketData\Services\MarketDataEvidenceExportService;
use App\Infrastructure\Persistence\MarketData\EodCorrectionRepository;
use App\Infrastructure\Persistence\MarketData\EodEvidenceRepository;
use App\Infrastructure\Persistence\MarketData\EodPublicationRepository;
use Mockery as m;
use PHPUnit\Framework\TestCase;

class CorrectionEvidenceExportServiceTest extends TestCase
{
    protected function tearDown(): void
    {
        m::close();
    }

    public function test_export_correction_evidence_writes_before_after_hash_proof()
    {
        $correction = (object) [
            'correction_id' => 9001,
            'trade_date' => '2026-03-05',
            'approved_by' => 'ops_lead',
            'approved_at' => '2026-03-06T09:00:00+07:00',
            'prior_publication_id' => 1188,
            'new_publication_id' => 1201,
            'status' => 'PUBLISHED',
        ];
        $priorPublication = (object) [
            'publication_id' => 1188,
            'run_id' => 5001,
            'publication_version' => 1,
            'is_current' => 0,
            'bars_batch_hash' => 'H1B',
            'indicators_batch_hash' => 'H1I',
            'eligibility_batch_hash' => 'H1E',
        ];
        $newPublication = (object) [
            'publication_id' => 1201,
            'run_id' => 5009,
            'publication_version' => 2,
            'is_current' => 1,
            'bars_batch_hash' => 'H2B',
            'indicators_batch_hash' => 'H2I',
            'eligibility_batch_hash' => 'H2E',
        ];

        $evidence = m::mock(EodEvidenceRepository::class);
        $publications = m::mock(EodPublicationRepository::class);
        $corrections = m::mock(EodCorrectionRepository::class);

        $evidence->shouldReceive('findCorrectionById')->once()->with(9001)->andReturn($correction);
        $evidence->shouldReceive('findPublicationById')->once()->with(1188)->andReturn($priorPublication);
        $evidence->shouldReceive('findPublicationById')->once()->with(1201)->andReturn($newPublication);

        $service = new MarketDataEvidenceExportService($evidence, $publications, $corrections);
        $dir = sys_get_temp_dir().'/market_data_evidence_correction_'.uniqid();
        $result = $service->exportCorrectionEvidence(9001, $dir);

        $this->assertSame($dir, $result['output_dir']);
        $this->assertFileExists($dir.'/correction_evidence.json');

        $payload = json_decode(file_get_contents($dir.'/correction_evidence.json'), true);
        $this->assertSame(9001, $payload['correction_id']);
        $this->assertSame('H1B', $payload['old_hashes']['bars_batch_hash']);
        $this->assertSame('H2B', $payload['new_hashes']['bars_batch_hash']);
        $this->assertTrue($payload['publication_switch']);
    }
}
