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
            'prior_run_id' => 5001,
            'new_publication_id' => 1201,
            'new_run_id' => 5009,
            'status' => 'PUBLISHED',
            'final_outcome_note' => 'Historical correction published safely via new sealed current publication.',
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
        $evidence->shouldReceive('resolvePublicationForEvidenceAudit')->once()->with([
            'type' => 'publication_id',
            'publication_id' => 1188,
            'run_id' => 5001,
        ])->andReturn((object) [
            'publication_id' => 1188,
            'run_id' => 5001,
            'publication_version' => 1,
            'is_current' => 0,
            'seal_state' => 'SEALED',
            'evidence_resolution_mode' => 'HISTORICAL_PUBLICATION_AUDIT',
            'evidence_publication_scope' => 'HISTORICAL_SEALED_PUBLICATION',
            'current_pointer_required' => false,
            'current_pointer_status' => 'NOT_CURRENT_POINTER',
            'historical_publication_allowed' => true,
            'lineage_verification_status' => 'LINEAGE_VERIFIED',
            'artifact_scope' => 'PUBLICATION_SCOPED',
            'coverage_basis_publication_id' => 1188,
            'coverage_basis_run_id' => 5001,
            'evidence_reason_code' => 'HISTORICAL_SEALED_PUBLICATION_RESOLVED',
        ]);
        $evidence->shouldReceive('resolvePublicationForEvidenceAudit')->once()->with([
            'type' => 'publication_id',
            'publication_id' => 1201,
            'run_id' => 5009,
        ])->andReturn((object) [
            'publication_id' => 1201,
            'run_id' => 5009,
            'publication_version' => 2,
            'is_current' => 1,
            'seal_state' => 'SEALED',
            'evidence_resolution_mode' => 'CURRENT_READABLE_PUBLICATION_AUDIT',
            'evidence_publication_scope' => 'CURRENT_POINTER_PUBLICATION',
            'current_pointer_required' => true,
            'current_pointer_status' => 'RESOLVED_READABLE_CURRENT',
            'historical_publication_allowed' => false,
            'lineage_verification_status' => 'LINEAGE_VERIFIED',
            'artifact_scope' => 'PUBLICATION_SCOPED',
            'coverage_basis_publication_id' => 1201,
            'coverage_basis_run_id' => 5009,
            'evidence_reason_code' => 'CURRENT_READABLE_PUBLICATION_RESOLVED',
        ]);

        $service = new MarketDataEvidenceExportService($evidence, $publications, $corrections);
        $dir = sys_get_temp_dir().'/market_data_evidence_correction_'.uniqid();
        $result = $service->exportCorrectionEvidence(9001, $dir);

        $this->assertSame('correction', $result['selector']['type']);
        $this->assertSame(9001, $result['selector']['id']);
        $this->assertSame('PUBLISHED', $result['summary']['status']);
        $this->assertTrue($result['summary']['publication_switch']);
        $this->assertSame(2, $result['file_count']);
        $this->assertSame($dir, $result['output_dir']);
        $this->assertFileExists($dir.'/correction_evidence.json');
        $this->assertFileExists($dir.'/evidence_admission.json');

        $admission = json_decode(file_get_contents($dir.'/evidence_admission.json'), true);
        $this->assertSame('correction', $admission['selector_type']);
        $this->assertSame('ADMITTED_COMPLETE', $admission['evidence_admission_state']);

        $payload = json_decode(file_get_contents($dir.'/correction_evidence.json'), true);
        $this->assertSame('ADMITTED_COMPLETE', $payload['evidence_admission']['evidence_admission_state']);
        $this->assertSame(9001, $payload['correction_id']);
        $this->assertSame('H1B', $payload['old_hashes']['bars_batch_hash']);
        $this->assertSame('H2B', $payload['new_hashes']['bars_batch_hash']);
        $this->assertTrue($payload['publication_switch']);
        $this->assertSame('HISTORICAL_PUBLICATION_AUDIT', $payload['baseline_historical_publication_proof']['evidence_resolution_mode']);
        $this->assertSame('HISTORICAL_SEALED_PUBLICATION_RESOLVED', $payload['baseline_historical_publication_proof']['evidence_reason_code']);
        $this->assertSame('CURRENT_READABLE_PUBLICATION_AUDIT', $payload['candidate_historical_publication_proof']['evidence_resolution_mode']);
        $this->assertSame('PUBLICATION_SCOPED', $payload['correction_lifecycle']['historical_lineage_proof']['baseline_publication_proof']['artifact_scope']);
        $this->assertStringContainsString('Historical correction published safely', $payload['final_outcome_note']);
    }
}
