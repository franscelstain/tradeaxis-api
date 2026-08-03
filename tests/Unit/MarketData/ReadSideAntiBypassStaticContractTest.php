<?php

/**
 * Three tests were removed. All three asserted query fragments for behaviour that is now driven.
 *
 * - The gateway's pointer-only conditions were asserted as ten source strings, plus two regexes
 *   checking that findCurrentPublicationForTradeDate and findPointerResolvedPublicationForTradeDate
 *   delegate to it. CorrectionBaselineResolutionTest now drives all four publication entry points
 *   over thirteen broken states and asserts they never disagree, on rejection or acceptance —
 *   which is the property the delegation regexes were approximating.
 * - The consumer repositories' latest-date prohibition is now applied to every file under app/
 *   by ReadPathShortcutProhibitionTest, rather than to nine paths by name.
 * - Coverage-pass and run-mirror conditions in the scope and evidence repositories were asserted
 *   as three strings each. ReadablePublicationReadContractIntegrationTest drives both
 *   repositories through ten broken publication states and asserts no rows leak from any of them.
 */
class ReadSideAntiBypassStaticContractTest extends TestCase
{
    /**
     * The read-side contract is a LOCKED document under audit governance. LOCKED does not mean
     * unchangeable — it means a change must be argued and recorded rather than made in passing.
     * This checks the governance markers are still in place, which no runtime behaviour can show.
     */
    public function test_read_side_contract_document_is_locked_and_audit_governed(): void
    {
        $path = dirname(__DIR__, 3).DIRECTORY_SEPARATOR
            .str_replace('/', DIRECTORY_SEPARATOR, 'docs/market_data/book/Read_Side_Enforcement_Anti_Bypass_Contract_LOCKED.md');

        $this->assertFileExists($path);

        $contract = file_get_contents($path);

        $this->assertStringContainsString('Status: LOCKED', $contract);
        $this->assertStringContainsString('resolveCurrentReadablePublicationForTradeDate', $contract);
        $this->assertStringContainsString('Forbidden Bypass Rule', $contract);
        $this->assertStringContainsString('Fail-Safe Rule', $contract);
        $this->assertStringContainsString('AUDIT_UPDATE_GOVERNANCE.md', $contract);
    }
}
