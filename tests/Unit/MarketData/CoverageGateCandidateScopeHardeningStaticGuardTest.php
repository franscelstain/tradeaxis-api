<?php

use PHPUnit\Framework\TestCase;

class CoverageGateCandidateScopeHardeningStaticGuardTest extends TestCase
{
    private function projectPath(string $path): string
    {
        return dirname(__DIR__, 3).DIRECTORY_SEPARATOR.str_replace('/', DIRECTORY_SEPARATOR, $path);
    }

    public function test_promote_and_correction_coverage_resolves_candidate_publication_scope_before_evaluation(): void
    {
        $pipeline = file_get_contents($this->projectPath('app/Application/MarketData/Services/MarketDataPipelineService.php'));

        $this->assertStringContainsString('resolveCandidateCoveragePublicationId', $pipeline);
        $this->assertStringContainsString('$coverageBasisPublicationId = $this->resolveCandidateCoveragePublicationId($run, $input, $priorCurrent);', $pipeline);
        $this->assertStringContainsString('$this->coverageGateEvaluator->evaluate($input->requestedDate, $coverageBasisPublicationId)', $pipeline);
        $this->assertStringContainsString("\$result['publication_id']", $pipeline);
        $this->assertStringContainsString("'candidate_publication_id='", $pipeline);
        $this->assertStringContainsString("'baseline_publication_id='", $pipeline);
        $this->assertStringContainsString('coverageBasisNoteSegments', $pipeline);
        $this->assertStringContainsString('noteCandidateSupersedesPriorCurrent', $pipeline);
        $this->assertStringContainsString('replaceBarsHistoryFromPublication', $pipeline);
        $this->assertStringContainsString('ensureBarsHistoryFromCurrentTradeDate', $pipeline);
    }

    public function test_artifact_repository_filters_candidate_coverage_by_publication_id_not_live_current_fallback(): void
    {
        $repository = file_get_contents($this->projectPath('app/Infrastructure/Persistence/MarketData/EodArtifactRepository.php'));

        $this->assertStringContainsString('loadCandidateScopedBarTickerIdsForTradeDate', $repository);
        $this->assertStringContainsString('eod_bars_history', $repository);
        $this->assertStringContainsString('eod_bars', $repository);
        $this->assertStringContainsString("where('publication_id', \$publicationId)", $repository);
        $this->assertStringNotContainsString('findCurrentPublicationForTradeDate($tradeDate)', $repository);
        $this->assertStringNotContainsString('findLatestReadablePublicationBefore($tradeDate)', $repository);
        $this->assertStringNotContainsString('orderByDesc(\'trade_date\')', $repository);
    }

    public function test_coverage_evaluator_outputs_candidate_coverage_basis_context(): void
    {
        $evaluator = file_get_contents($this->projectPath('app/Application/MarketData/Services/CoverageGateEvaluator.php'));

        foreach ([
            'coverage_basis',
            'coverage_basis_publication_id',
            'coverage_basis_artifact_scope',
            'candidate_publication_id',
            'candidate_available_count',
            'candidate_missing_count',
            'candidate_coverage_ratio',
            'CandidatePublication',
        ] as $needle) {
            $this->assertStringContainsString($needle, $evaluator);
        }
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
