<?php

use PHPUnit\Framework\TestCase;

class CoverageGateCandidateScopeHardeningStaticGuardTest extends TestCase
{
    private function projectPath(string $path): string
    {
        return dirname(__DIR__, 3).DIRECTORY_SEPARATOR.str_replace('/', DIRECTORY_SEPARATOR, $path);
    }

    // The pipeline wiring and the evaluator's output fields were asserted here as source
    // strings. What the scoping query actually returns is now proven by
    // CandidateCoverageScopeTest, which builds a live publication holding the full universe and
    // an incomplete candidate beside it, and shows the candidate is counted by its own bars.

    /**
     * The artifact repository must never resolve a coverage scope for itself.
     *
     * These are the three fallbacks that would silently restore the defect the candidate scope
     * exists to prevent: reading the live current publication, reaching back to the last readable
     * one, or taking whatever is newest. Each would make an incomplete candidate report the
     * coverage of a dataset it did not produce.
     *
     * This stays a source check because it asserts an absence. The positive behaviour — that the
     * scoped query counts only the named publication's bars, unions history and current without
     * double counting, and treats an empty publication as empty — is driven by
     * CandidateCoverageScopeTest.
     */
    public function test_artifact_repository_never_falls_back_to_a_live_or_latest_publication(): void
    {
        $repository = file_get_contents($this->projectPath('app/Infrastructure/Persistence/MarketData/EodArtifactRepository.php'));

        $this->assertStringNotContainsString('findCurrentPublicationForTradeDate($tradeDate)', $repository);
        $this->assertStringNotContainsString('findLatestReadablePublicationBefore($tradeDate)', $repository);
        $this->assertStringNotContainsString('orderByDesc(\'trade_date\')', $repository);
    }

    public function test_command_evidence_and_replay_expose_candidate_coverage_basis(): void
    {
        $command = file_get_contents($this->projectPath('app/Console/Commands/MarketData/AbstractMarketDataCommand.php'));
        $evidence = file_get_contents($this->projectPath('app/Application/MarketData/Services/MarketDataEvidenceExportService.php'));
        $replay = file_get_contents($this->projectPath('app/Application/MarketData/Services/ReplayVerificationService.php'));

        foreach (['coverage_basis', 'coverage_basis_publication_id', 'coverage_basis_artifact_scope', 'candidate_publication_id', 'baseline_publication_id'] as $field) {
            $this->assertStringContainsString($field, $command);
            $this->assertStringContainsString($field, $evidence);
            $this->assertStringContainsString($field, $replay);
        }
    }

    public function test_audit_docs_record_candidate_scope_hardening_without_replacing_existing_coverage_contract(): void
    {
        $inventory = file_get_contents($this->projectPath('docs/market_data/audit/COVERAGE_GATE_CANDIDATE_SCOPE_HARDENING_INVENTORY.md'));
        $status = file_get_contents($this->projectPath('docs/market_data/audit/LUMEN_IMPLEMENTATION_STATUS.md'));
        $tracker = file_get_contents($this->projectPath('docs/market_data/audit/LUMEN_CONTRACT_TRACKER.md'));

        foreach (['Coverage Gate Candidate Scope Hardening', 'candidate publication', 'manual promote', 'correction candidate', 'not coverage gate enforcement ulang'] as $needle) {
            $this->assertStringContainsString($needle, $inventory);
            $this->assertStringContainsString($needle, $status);
            $this->assertStringContainsString($needle, $tracker);
        }
    }
}
