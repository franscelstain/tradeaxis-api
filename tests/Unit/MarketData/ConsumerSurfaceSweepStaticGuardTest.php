<?php

use PHPUnit\Framework\TestCase;

class ConsumerSurfaceSweepStaticGuardTest extends TestCase
{
    public function test_evidence_repository_replay_lookup_has_no_latest_row_fallback()
    {
        $source = $this->readProjectFile('app/Infrastructure/Persistence/MarketData/EodEvidenceRepository.php');

        $this->assertStringContainsString('Replay metric lookup requires explicit trade_date', $source);
        $this->assertStringNotContainsString("->orderByDesc('trade_date')->first()", $source);
    }

    public function test_evidence_repository_publication_lookup_is_pointer_resolved()
    {
        $source = $this->readProjectFile('app/Infrastructure/Persistence/MarketData/EodEvidenceRepository.php');
        $method = $this->extractMethod($source, 'findPublicationForRun');

        $this->assertStringContainsString('eod_current_publication_pointer as ptr', $method);
        $this->assertStringContainsString("run.terminal_status', 'SUCCESS'", $method);
        $this->assertStringContainsString("run.publishability_state', 'READABLE'", $method);
        $this->assertStringContainsString("run.coverage_gate_state', 'PASS'", $method);
        $this->assertStringContainsString("pub.seal_state', 'SEALED'", $method);
        $this->assertStringContainsString("whereColumn('run.publication_id', 'ptr.publication_id')", $method);
        $this->assertStringContainsString("whereColumn('run.publication_version', 'ptr.publication_version')", $method);
        $this->assertStringNotContainsString('orderByDesc', $method);
    }

    public function test_readable_eligibility_queries_require_coverage_pass_and_run_publication_mirror()
    {
        $scopeSource = $this->readProjectFile('app/Infrastructure/Persistence/MarketData/EligibilitySnapshotScopeRepository.php');
        $evidenceSource = $this->readProjectFile('app/Infrastructure/Persistence/MarketData/EodEvidenceRepository.php');

        foreach ([$scopeSource, $evidenceSource] as $source) {
            $this->assertStringContainsString("run.coverage_gate_state', 'PASS'", $source);
            $this->assertStringContainsString("whereColumn('run.publication_id', 'ptr.publication_id')", $source);
            $this->assertStringContainsString("whereColumn('run.publication_version', 'ptr.publication_version')", $source);
        }
    }

    public function test_replay_evidence_command_requires_explicit_trade_date()
    {
        $source = $this->readProjectFile('app/Console/Commands/MarketData/ExportEvidenceCommand.php');

        $this->assertStringContainsString('Replay evidence export requires --trade_date; latest-row resolution is not allowed.', $source);
    }

    private function readProjectFile($relativePath)
    {
        $path = dirname(__DIR__, 3).DIRECTORY_SEPARATOR.str_replace('/', DIRECTORY_SEPARATOR, $relativePath);
        $this->assertFileExists($path);

        return file_get_contents($path);
    }

    private function extractMethod($source, $methodName)
    {
        $pattern = '/public function '.preg_quote($methodName, '/').'\([^)]*\)\s*\{(?P<body>.*?)\n    \}/s';
        $this->assertSame(1, preg_match($pattern, $source, $matches), 'Method not found: '.$methodName);

        return $matches[0];
    }
}
