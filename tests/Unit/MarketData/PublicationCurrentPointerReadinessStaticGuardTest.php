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
        $this->assertStringContainsString("ptr.publication_id as pointer_publication_id", $method);
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

        $this->assertStringContainsString('Current publication promotion requires pre-approved SUCCESS + READABLE run before pointer switch', $promote);
        $this->assertStringContainsString('assertCurrentPointerResolvedAfterSwitch', $promote);
        $this->assertStringContainsString('assertCurrentPointerResolvedAfterSwitch', $restore);
        $this->assertStringContainsString('findRawCurrentPublicationStateForTradeDate', $postSwitch);
        $this->assertStringContainsString('determineCurrentIntegrityViolationReasons', $postSwitch);
        $this->assertStringContainsString('resolveCurrentReadablePublicationForTradeDate', $postSwitch);
        $this->assertStringContainsString('pointer_publication_id', $postSwitch);
        $this->assertStringContainsString('throw new \\RuntimeException', $postSwitch);
        $this->assertStringContainsString('current pointer did not resolve to a readable publication after switch', $postSwitch);
        $this->assertStringNotContainsString('return false', $postSwitch);
    }



    public function test_pipeline_primes_run_with_carbon_timestamp_and_authoritative_pointer_resolver_before_readable_outcome()
    {
        $source = $this->readProjectFile('app/Application/MarketData/Services/MarketDataPipelineService.php');
        $prepare = $this->extractMethod($source, 'prepareRunForPointerSwitch', 'private');
        $finalize = $this->extractMethod($source, 'completeFinalize');

        $this->assertStringContainsString("Carbon::now(config('market_data.platform.timezone'))", $prepare);
        $this->assertStringNotContainsString('now()', $prepare);
        $this->assertStringContainsString('resolveCurrentReadablePublicationForTradeDate($input->requestedDate)', $finalize);
        $this->assertStringContainsString('Current publication pointer resolution mismatch after finalize.', $finalize);
        $this->assertStringContainsString('Treat the pointer resolver as the authoritative post-switch', $finalize);
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
