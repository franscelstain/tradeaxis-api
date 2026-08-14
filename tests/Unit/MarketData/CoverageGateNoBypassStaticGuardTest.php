<?php

use PHPUnit\Framework\TestCase;

/**
 * What remains here are prohibitions: rules of the form "this must never appear", which
 * execution cannot demonstrate. A test can show that a given call refuses a given input; it
 * cannot show that no bypass was ever written anywhere.
 *
 * Everything that was a positive claim has moved to tests that run the code:
 *
 * - Coverage telemetry completeness was asserted as eight field names present in three service
 *   files. CoverageTelemetryBypassTest now drives the guard over fourteen fabricated states, one
 *   per way of claiming coverage a run cannot prove, and over the four differently shaped rows
 *   its callers actually pass in.
 * - The eight whereNotNull clauses on the read path were asserted as source strings.
 *   CurrentPointerIntegrityScanTest now nulls each coverage column in turn and proves both the
 *   consumer read and the integrity scan reject it.
 * - The empty-universe rule was asserted as a NOT_EVALUABLE string. CoverageGateEvaluatorTest
 *   evaluates a trade date with an empty universe and asserts the verdict, the null ratio, and
 *   the COVERAGE_UNIVERSE_EMPTY reason code.
 * - Coverage context in evidence, replay and command output was asserted as field names present
 *   in three files. MarketDataEvidenceExportServiceTest, ReplayEvidenceExportServiceTest,
 *   ReplayVerificationServiceTest and OpsCommandSurfaceTest assert them in produced output, and
 *   ReplayVerificationServiceTest raises the three coverage mismatch reason codes for real.
 */
class CoverageGateNoBypassStaticGuardTest extends TestCase
{
    private function projectPath(string $path): string
    {
        return dirname(__DIR__, 3).DIRECTORY_SEPARATOR.str_replace('/', DIRECTORY_SEPARATOR, $path);
    }

    /**
     * BLOCKED was a legacy coverage verdict. It must never be emitted again: it was ambiguous
     * about whether the gate had failed or had never run, and the two demand opposite responses.
     * A run that could not be evaluated is NOT_EVALUABLE.
     */
    public function test_evaluator_never_emits_the_legacy_blocked_verdict(): void
    {
        $source = file_get_contents($this->projectPath('app/Application/MarketData/Services/CoverageGateEvaluator.php'));

        $this->assertStringNotContainsString("'coverage_gate_status' => 'BLOCKED'", $source);
    }

    /**
     * Readable state requires a resolved current publication identity, compared through the
     * named helpers. The prohibited inline comparison is what the helpers replaced: it treated
     * two absent identities as a match, so a run with no publication at all compared equal to
     * itself and passed.
     */
    public function test_publication_identity_is_not_compared_inline(): void
    {
        $source = file_get_contents($this->projectPath('app/Application/MarketData/Services/PublicationFinalizeOutcomeService.php'));

        $this->assertStringContainsString('hasPublicationIdentity', $source);
        $this->assertStringContainsString('samePublicationIdentity', $source);
        $this->assertStringNotContainsString('(string) $resolvedCurrentPublicationId === (string) $candidatePublicationId', $source);
    }

    // The latest-trade-date prohibition was checked here against six named paths. Eleven other
    // guard files carried the same check against their own lists. ReadPathShortcutProhibitionTest
    // now applies it to every file under app/, so it covers these six and everything nobody
    // thought to list.
}
