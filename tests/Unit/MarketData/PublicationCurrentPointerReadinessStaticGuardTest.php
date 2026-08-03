<?php

use PHPUnit\Framework\TestCase;

class PublicationCurrentPointerReadinessStaticGuardTest extends TestCase
{
    // Two tests were removed here.
    //
    // The first asserted seven query fragments inside resolveCurrentReadablePublicationForTradeDate.
    // PublicationRepositoryIntegrationTest already drives that method over twelve broken pointer
    // states and CorrectionBaselineResolutionTest over thirteen more, all by execution.
    //
    // The second asserted that three reason-code strings appear inside the reason derivation.
    // They did appear, and two further static guards confirmed the same strings independently,
    // yet the operator never saw them: the repair command kept a private copy of the derivation
    // that was missing five reasons, so a pointer broken only by a coverage or run-mirror fault
    // was reported as invalid with an empty integrity_reasons list. Presence of a string in a
    // file says nothing about which code path reaches the operator.
    //
    // CurrentPointerIntegrityScanTest replaces both. It drives seventeen broken states through
    // the scan, the consumer read, and the real repair command, and asserts the three agree.

    /**
     * The post-switch assertion must throw, never return a boolean an indifferent caller could
     * discard. That is an absence, so it stays a source check: execution can show that a
     * particular call throws, but not that no silent-failure path was ever added.
     */
    public function test_post_switch_pointer_assertion_cannot_fail_silently()
    {
        $source = $this->readProjectFile('app/Infrastructure/Persistence/MarketData/EodPublicationRepository.php');
        $postSwitch = $this->extractMethod($source, 'assertCurrentPointerResolvedAfterSwitch', 'private');

        $this->assertStringContainsString('throw new \\RuntimeException', $postSwitch);
        $this->assertStringNotContainsString('return false', $postSwitch);
    }

    /**
     * Pointer timestamps must be taken in the exchange timezone. `now()` would silently use the
     * application default, and the resulting sealed_at would disagree with the trading day it
     * claims to belong to. Only the prohibition is asserted; the positive case is covered by the
     * promote and finalize integration tests.
     */
    public function test_pointer_switch_timestamps_are_not_taken_in_the_default_timezone()
    {
        $source = $this->readProjectFile('app/Application/MarketData/Services/MarketDataPipelineService.php');
        $prepare = $this->extractMethod($source, 'prepareRunForPointerSwitch', 'private');

        $this->assertStringContainsString("Carbon::now(config('market_data.platform.timezone'))", $prepare);
        $this->assertStringNotContainsString('now()', $prepare);
    }

    // The finalize-idempotency fail-safe was asserted here as nine wiring strings. It is driven
    // end to end by MarketDataPipelineIntegrationTest: a completed run whose pointer version is
    // corrupted mid-flight is re-finalized, held with RUN_LOCK_CONFLICT, the invalid pointer is
    // cleared, no publication is duplicated, and RUN_FINALIZE_IDEMPOTENCY_POINTER_INVALID is
    // recorded against the run. Every string checked here is an observable outcome there.

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
