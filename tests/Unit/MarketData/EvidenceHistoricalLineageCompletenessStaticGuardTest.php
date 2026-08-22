<?php

use PHPUnit\Framework\TestCase;

class EvidenceHistoricalLineageCompletenessStaticGuardTest extends TestCase
{
    private function projectPath(string $path): string
    {
        return dirname(__DIR__, 3).DIRECTORY_SEPARATOR.str_replace('/', DIRECTORY_SEPARATOR, $path);
    }

    public function test_evidence_audit_resolver_is_separate_from_consumer_current_pointer_resolver(): void
    {
        $evidenceRepository = file_get_contents($this->projectPath('app/Infrastructure/Persistence/MarketData/EodEvidenceRepository.php'));
        $publicationRepository = file_get_contents($this->projectPath('app/Infrastructure/Persistence/MarketData/EodPublicationRepository.php'));
        $service = file_get_contents($this->projectPath('app/Application/MarketData/Services/MarketDataEvidenceExportService.php'));

        $this->assertStringContainsString('resolvePublicationForEvidenceAudit', $evidenceRepository);
        $this->assertStringContainsString('Historical publication audit resolution requires explicit run_id or publication_id', $evidenceRepository);
        $this->assertStringContainsString('resolveCurrentReadablePublicationForTradeDate', $publicationRepository);
        $this->assertStringContainsString('Official read-side gateway for consumer paths', $publicationRepository);
        $this->assertStringContainsString('resolvePublicationForEvidenceAudit', $service);
        $this->assertStringNotContainsString('findReadableCurrentPublicationForRun($run->run_id, $run->trade_date_requested)', $service);
    }

    public function test_historical_publication_resolver_is_selector_scoped_and_lineage_validated(): void
    {
        $evidenceRepository = file_get_contents($this->projectPath('app/Infrastructure/Persistence/MarketData/EodEvidenceRepository.php'));

        foreach ([
            'EVIDENCE_SELECTOR_MISSING',
            'EVIDENCE_PUBLICATION_NOT_FOUND',
            'EVIDENCE_PUBLICATION_RUN_MISMATCH',
            'EVIDENCE_RUN_PUBLICATION_MIRROR_MISMATCH',
            'EVIDENCE_PUBLICATION_TRADE_DATE_MISMATCH',
            'EVIDENCE_HISTORICAL_PUBLICATION_UNSEALED',
            'EVIDENCE_RUN_SEAL_MISSING',
            'EVIDENCE_COVERAGE_CONTEXT_MISSING',
            'EVIDENCE_PUBLICATION_ARTIFACT_HASH_MISSING',
            'HISTORICAL_PUBLICATION_AUDIT',
            'HISTORICAL_SEALED_PUBLICATION_RESOLVED',
            'PUBLICATION_SCOPED',
            'LINEAGE_VERIFIED',
        ] as $needle) {
            $this->assertStringContainsString($needle, $evidenceRepository, 'Historical evidence resolver must keep '.$needle);
        }
    }

    public function test_historical_artifact_export_uses_publication_scoped_sources_without_current_pointer_fallback(): void
    {
        $evidenceRepository = file_get_contents($this->projectPath('app/Infrastructure/Persistence/MarketData/EodEvidenceRepository.php'));
        $service = file_get_contents($this->projectPath('app/Application/MarketData/Services/MarketDataEvidenceExportService.php'));

        foreach ([
            'dominantReasonCodesForEvidencePublication',
            'exportEligibilityRowsForEvidencePublication',
            'evidenceEligibilityQuery',
            'eod_eligibility_history',
            'where(\'elig.publication_id\', $publicationId)',
            'artifact_scope',
            'coverage_basis_publication_id',
            'coverage_basis_run_id',
        ] as $needle) {
            $this->assertStringContainsString($needle, $evidenceRepository.$service, 'Historical artifact scope must keep '.$needle);
        }

        $this->assertStringNotContainsString('readablePublicationContextExists($tradeDate, $publicationId, $runId)', $service);
        $this->assertStringNotContainsString('findReadableCurrentPublicationForRun($run->run_id', $service);
    }

    public function test_correction_and_replay_evidence_include_historical_lineage_fields(): void
    {
        $service = file_get_contents($this->projectPath('app/Application/MarketData/Services/MarketDataEvidenceExportService.php'));

        foreach ([
            'baseline_historical_publication_proof',
            'candidate_historical_publication_proof',
            'historical_lineage_proof',
            'buildHistoricalPublicationAuditProof',
            'buildReplayPublicationAuditContext',
            'evidence_resolution_mode',
            'evidence_publication_scope',
            'current_pointer_required',
            'historical_publication_allowed',
            'lineage_verification_status',
            'evidence_reason_code',
        ] as $needle) {
            $this->assertStringContainsString($needle, $service, 'Correction/replay evidence must keep '.$needle);
        }
    }

}
