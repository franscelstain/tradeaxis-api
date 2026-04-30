<?php

use PHPUnit\Framework\TestCase;

class PublicationCurrentPointerReadinessStaticGuardTest extends TestCase
{
    public function test_current_pointer_resolution_requires_run_publication_mirror_match()
    {
        $source = $this->readProjectFile('app/Infrastructure/Persistence/MarketData/EodPublicationRepository.php');
        $method = $this->extractMethod($source, 'resolveCurrentReadablePublicationForTradeDate');

        $this->assertStringContainsString("eod_current_publication_pointer as ptr", $method);
        $this->assertStringContainsString("->whereColumn('run.publication_id', 'ptr.publication_id')", $method);
        $this->assertStringContainsString("->whereColumn('run.publication_version', 'ptr.publication_version')", $method);
        $this->assertStringContainsString("run.terminal_status', 'SUCCESS'", $method);
        $this->assertStringContainsString("run.publishability_state', 'READABLE'", $method);
        $this->assertStringContainsString("pub.seal_state', 'SEALED'", $method);
    }

    public function test_invalid_current_state_scan_detects_ghost_pointer_and_run_mirror_mismatch()
    {
        $source = $this->readProjectFile('app/Infrastructure/Persistence/MarketData/EodPublicationRepository.php');
        $scanMethod = $this->extractMethod($source, 'findInvalidCurrentPublicationStates');
        $reasonMethod = $this->extractMethod($source, 'determineCurrentIntegrityViolationReasons', 'protected');

        $this->assertStringContainsString("->leftJoin('eod_publications as pub'", $scanMethod);
        $this->assertStringContainsString('PUBLICATION_ROW_MISSING', $reasonMethod);
        $this->assertStringContainsString('RUN_PUBLICATION_ID_MISMATCH', $reasonMethod);
        $this->assertStringContainsString('RUN_PUBLICATION_VERSION_MISMATCH', $reasonMethod);
    }

    public function test_pointer_switch_paths_verify_pointer_resolution_after_mutation()
    {
        $source = $this->readProjectFile('app/Infrastructure/Persistence/MarketData/EodPublicationRepository.php');
        $promote = $this->extractMethod($source, 'promoteCandidateToCurrent');
        $restore = $this->extractMethod($source, 'restorePriorCurrentPublication');
        $postSwitch = $this->extractMethod($source, 'assertCurrentPointerResolvedAfterSwitch', 'private');

        $this->assertStringContainsString('assertCurrentPointerResolvedAfterSwitch', $promote);
        $this->assertStringContainsString('assertCurrentPointerResolvedAfterSwitch', $restore);
        $this->assertStringContainsString('resolveCurrentReadablePublicationForTradeDate', $postSwitch);
        $this->assertStringContainsString('current pointer did not resolve to a readable publication after switch', $postSwitch);
    }

    private function readProjectFile($relativePath)
    {
        $path = dirname(__DIR__, 3).DIRECTORY_SEPARATOR.str_replace('/', DIRECTORY_SEPARATOR, $relativePath);
        $this->assertFileExists($path);

        return file_get_contents($path);
    }

    private function extractMethod($source, $methodName, $visibility = 'public')
    {
        $pattern = '/'.$visibility.' function '.preg_quote($methodName, '/').'\([^)]*\)\s*(?::\s*[^\s{]+)?\s*\{(?P<body>.*?)\n    \}/s';
        $this->assertSame(1, preg_match($pattern, $source, $matches), 'Method not found: '.$methodName);

        return $matches[0];
    }
}
