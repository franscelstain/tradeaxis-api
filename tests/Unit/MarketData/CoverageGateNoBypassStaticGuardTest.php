<?php

use PHPUnit\Framework\TestCase;

class CoverageGateNoBypassStaticGuardTest extends TestCase
{
    private function projectPath(string $path): string
    {
        return dirname(__DIR__, 3).DIRECTORY_SEPARATOR.str_replace('/', DIRECTORY_SEPARATOR, $path);
    }

    public function test_finalize_and_pointer_paths_require_complete_coverage_context_before_readable_or_current(): void
    {
        $guard = file_get_contents($this->projectPath('app/Application/MarketData/Services/MarketDataInvariantGuard.php'));
        $finalize = file_get_contents($this->projectPath('app/Application/MarketData/Services/FinalizeDecisionService.php'));
        $outcome = file_get_contents($this->projectPath('app/Application/MarketData/Services/PublicationFinalizeOutcomeService.php'));
        $pipeline = file_get_contents($this->projectPath('app/Application/MarketData/Services/MarketDataPipelineService.php'));
        $publicationRepository = file_get_contents($this->projectPath('app/Infrastructure/Persistence/MarketData/EodPublicationRepository.php'));

        foreach ([
            'expected_universe_count',
            'available_eod_count',
            'missing_eod_count',
            'coverage_ratio',
            'coverage_threshold_value',
            'coverage_threshold_mode',
            'coverage_universe_basis',
            'coverage_contract_version',
        ] as $field) {
            $this->assertStringContainsString($field, $guard);
            $this->assertStringContainsString($field, $finalize);
            $this->assertStringContainsString($field, $pipeline);
        }

        $this->assertStringContainsString('assertCoverageTelemetryCompleteForReadable', $guard);
        $this->assertStringContainsString('isReadableCoverageSummary', $finalize);
        $this->assertStringContainsString("'coverage_summary' =>", $outcome);
        $this->assertStringContainsString("whereNotNull('run.coverage_universe_count')", $publicationRepository);
        $this->assertStringContainsString("whereNotNull('run.coverage_available_count')", $publicationRepository);
        $this->assertStringContainsString("whereNotNull('run.coverage_missing_count')", $publicationRepository);
        $this->assertStringContainsString("whereNotNull('run.coverage_ratio')", $publicationRepository);
        $this->assertStringContainsString("whereNotNull('run.coverage_min_threshold')", $publicationRepository);
        $this->assertStringContainsString("whereNotNull('run.coverage_threshold_mode')", $publicationRepository);
        $this->assertStringContainsString("whereNotNull('run.coverage_universe_basis')", $publicationRepository);
        $this->assertStringContainsString("whereNotNull('run.coverage_contract_version')", $publicationRepository);
    }

    public function test_coverage_evaluator_never_defaults_empty_or_duplicate_universe_to_pass(): void
    {
        $source = file_get_contents($this->projectPath('app/Application/MarketData/Services/CoverageGateEvaluator.php'));

        $this->assertStringContainsString('$expectedUniverseCount = count($universeByTickerId);', $source);
        $this->assertStringContainsString("'coverage_gate_status' => 'NOT_EVALUABLE'", $source);
        $this->assertStringContainsString("'coverage_reason_code' => 'RUN_COVERAGE_NOT_EVALUABLE'", $source);
        $this->assertStringContainsString('\'coverage_universe_basis\' => $universeBasis', $source);
        $this->assertStringContainsString('\'coverage_contract_version\' => $contractVersion', $source);
        $this->assertStringNotContainsString("'coverage_gate_status' => 'PASS',\n                'coverage_ratio' => 1", $source);
    }

    public function test_replay_evidence_and_command_surfaces_keep_coverage_context_visible(): void
    {
        $evidence = file_get_contents($this->projectPath('app/Application/MarketData/Services/MarketDataEvidenceExportService.php'));
        $replay = file_get_contents($this->projectPath('app/Application/MarketData/Services/ReplayVerificationService.php'));
        $command = file_get_contents($this->projectPath('app/Console/Commands/MarketData/AbstractMarketDataCommand.php'));

        foreach ([
            'coverage_universe_count',
            'coverage_available_count',
            'coverage_missing_count',
            'coverage_ratio',
            'coverage_threshold_mode',
            'coverage_universe_basis',
            'coverage_contract_version',
            'coverage_reason_code',
        ] as $field) {
            $this->assertStringContainsString($field, $evidence);
            $this->assertStringContainsString($field, $replay);
            $this->assertStringContainsString($field, $command);
        }

        $this->assertStringContainsString('COVERAGE_FIELD_MISMATCH', $replay);
        $this->assertStringContainsString('publishability_state', $evidence);
        $this->assertStringContainsString('publishability_state', $replay);
        $this->assertStringContainsString('publication_id', $replay);
        $this->assertStringContainsString('is_current_publication', $replay);
    }

    public function test_publication_outcome_requires_explicit_current_publication_identity_for_readable_state(): void
    {
        $source = file_get_contents($this->projectPath('app/Application/MarketData/Services/PublicationFinalizeOutcomeService.php'));

        $this->assertStringContainsString('hasPublicationIdentity', $source);
        $this->assertStringContainsString('samePublicationIdentity', $source);
        $this->assertStringContainsString('READABLE requires resolved current publication identity', $source);
        $this->assertStringNotContainsString('(string) $resolvedCurrentPublicationId === (string) $candidatePublicationId', $source);
    }

    public function test_no_forbidden_coverage_latest_trade_date_shortcuts_in_runtime_paths(): void
    {
        $paths = [
            'app/Application/MarketData/Services/CoverageGateEvaluator.php',
            'app/Application/MarketData/Services/FinalizeDecisionService.php',
            'app/Application/MarketData/Services/PublicationFinalizeOutcomeService.php',
            'app/Application/MarketData/Services/MarketDataPipelineService.php',
            'app/Application/MarketData/Services/MarketDataEvidenceExportService.php',
            'app/Application/MarketData/Services/ReplayVerificationService.php',
        ];

        foreach ($paths as $path) {
            $source = file_get_contents($this->projectPath($path));
            foreach (["MAX(trade_date)", "max('trade_date')", "latest('trade_date')", "orderByDesc('trade_date')", 'ORDER BY trade_date DESC'] as $forbidden) {
                $this->assertStringNotContainsString($forbidden, $source, $path.' contains forbidden latest-date shortcut '.$forbidden);
            }
        }
    }
}
