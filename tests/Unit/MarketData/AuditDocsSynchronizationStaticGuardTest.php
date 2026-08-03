<?php

use PHPUnit\Framework\TestCase;

/**
 * Six tests were removed from this file, which held 143 string assertions.
 *
 * - The cross-reference roster was about a hundred assertions of the shape "status says X -> DONE"
 *   / "[RELATED_CONTRACT] Y" / "tracker says Y -> LOCKED" / "[RELATED_IMPLEMENTATION] X", written
 *   out by hand for roughly a dozen of the fifty entries. AuditCrossReferenceIntegrityTest derives
 *   the same rule and applies it to every entry in both documents, including entries not written
 *   yet.
 * - Registry-and-seed synchronization was checked by parsing both files with regexes. That is the
 *   exact check that passed for years while the seed carried a trailing comma and inserted
 *   nothing. ReasonCodeSeedExecutionTest runs the statement and compares the registry against the
 *   rows that actually landed in eod_reason_codes.
 * - Three tests asserted frozen historical tallies and runtime-proof identifiers — "OK (511 tests,
 *   7871 assertions)", run_id=33, replay_id=15, benchmark_rows_written=1, storage paths from a
 *   past operator run. They record what happened once. They cannot fail unless someone edits the
 *   audit history, and if someone does, the tallies are not what protects it.
 *
 * What remains are the rules that hold regardless of which entries exist.
 */
class AuditDocsSynchronizationStaticGuardTest extends TestCase
{
    private function read(string $path): string
    {
        $fullPath = dirname(__DIR__, 3).DIRECTORY_SEPARATOR.str_replace('/', DIRECTORY_SEPARATOR, $path);
        $this->assertFileExists($fullPath);

        return file_get_contents($fullPath);
    }

    /**
     * A LOCKED contract is one nothing may quietly change. That status is only meaningful if the
     * document says what evidence locked it, so every LOCKED block must carry its validation
     * sections, cite operator-local validation, cite a passing result, and name the test scope.
     *
     * Derived over whichever contracts are LOCKED, so a contract locked tomorrow is held to the
     * same standard without editing this test.
     */
    public function test_locked_contracts_have_concrete_validation_evidence(): void
    {
        $tracker = $this->read('docs/market_data/audit/LUMEN_CONTRACT_TRACKER.md');

        preg_match_all(
            '/^- ([A-Z0-9_]+_CONTRACT) -> LOCKED\R(?P<body>.*?)(?=^- [A-Z0-9_]+_CONTRACT (?:->|→)|\z)/msu',
            $tracker,
            $matches,
            PREG_SET_ORDER
        );

        $this->assertNotEmpty($matches, 'The tracker must contain LOCKED contracts.');

        foreach ($matches as $match) {
            [$block, $contractName] = [$match[0], $match[1]];

            $this->assertStringContainsString('[VALIDATED]', $block, $contractName.' must have a VALIDATED section.');
            $this->assertStringContainsString('[FINAL_RULE]', $block, $contractName.' must have a FINAL_RULE section.');
            $this->assertMatchesRegularExpression(
                '/(Operator-local|Operator local|Local PHPUnit|PHPUnit\/artisan validation was supplied by operator|local validation)/i',
                $block,
                $contractName.' must cite local/operator validation.'
            );
            $this->assertMatchesRegularExpression('/(OK \(|PASS|passed)/i', $block, $contractName.' must cite a passing validation result.');
            $this->assertStringContainsString('tests/Unit/MarketData', $block, $contractName.' must cite MarketData validation scope.');
        }
    }

    /**
     * The working entry at the top of each document must belong to the session both documents
     * declare active. Otherwise the first thing a reader sees is work from a different session.
     */
    public function test_current_working_sections_start_with_active_session(): void
    {
        $status = $this->read('docs/market_data/audit/LUMEN_IMPLEMENTATION_STATUS.md');
        $tracker = $this->read('docs/market_data/audit/LUMEN_CONTRACT_TRACKER.md');

        preg_match('/ACTIVE SESSION:\R- (?P<session>[^\r\n]+)/', $status, $sessionMatch);
        $activeSession = trim($sessionMatch['session']);

        $this->assertStringContainsString($activeSession, $this->firstNonEmptyLineAfter($status, '## CURRENT WORKING ENTRY'));
        $this->assertStringContainsString('[SESSION] '.$activeSession, $status);

        // The working contract line must be a well-formed canonical entry. The contract it names
        // is not asserted: pinning it freezes the documents to one session.
        $this->assertMatchesRegularExpression(
            '/^- [A-Z0-9_]+_CONTRACT (?:->|→) (DONE|LOCKED|ENFORCED|PARTIAL|BLOCKED|REVIEW_REQUIRED)$/',
            $this->firstNonEmptyLineAfter($tracker, '## CURRENT WORKING CONTRACT')
        );
    }

    /**
     * The governance document is what makes the audit trail trustworthy: append-only, no
     * duplicate canonical entries, evidence recorded against a named environment. These markers
     * are its structure, and nothing else asserts they survive.
     */
    public function test_audit_governance_enforces_append_only_anti_duplication_and_static_guard(): void
    {
        $documents = $this->read('docs/market_data/audit/AUDIT_UPDATE_GOVERNANCE.md')
            .$this->read('docs/market_data/audit/AUDIT_DOCS_SYNCHRONIZATION_INVENTORY.md')
            .$this->read('docs/market_data/audit/AUDIT_DOCS_SYNCHRONIZATION_POST_SESSION_1_8_INVENTORY.md');

        foreach ([
            'AUDIT DOCS SYNCHRONIZATION HARD RULE',
            'append-only',
            'anti-duplication',
            'ACTIVE SESSION',
            'targeted and full local PHPUnit evidence',
            'LOCKED_LOCAL_PHPUNIT_PASS',
            'AUDIT_DOCS_SYNCHRONIZATION_CONTRACT',
            'RUNTIME ENVIRONMENT BASELINE HARD RULE',
            'operator-local PHP version',
            'operator-local PHPUnit version',
        ] as $needle) {
            $this->assertStringContainsString($needle, $documents);
        }
    }

    /**
     * The production-ready claim is a decision, not a measurement, and the proof pack must state
     * the decision it actually reached. The rejected outcomes are asserted absent so a downgrade
     * cannot be left sitting next to the claim it contradicts.
     */
    public function test_production_ready_claim_states_a_single_settled_decision(): void
    {
        $proofPack = $this->read('docs/market_data/audit/MARKET_DATA_PRODUCTION_PROOF_PACK.md');
        $tracker = $this->read('docs/market_data/audit/LUMEN_CONTRACT_TRACKER.md');
        $inventory = $this->read('docs/market_data/audit/FULL_MARKET_DATA_PRODUCTION_READY_INVENTORY.md');

        $this->assertStringContainsString('Decision: `OPS_RUNTIME_PARITY_PASSED`', $proofPack);
        $this->assertStringNotContainsString('Decision: `OPS_RUNTIME_PARITY_PARTIAL_PROVIDER_RATE_LIMITED`', $proofPack);
        $this->assertStringContainsString('FINAL_PROVIDER_SMOKE=PASSED', $proofPack);
        $this->assertStringContainsString('Source-state decision: `MARKET_DATA_PRODUCTION_READY_LOCKED`', $proofPack);
        $this->assertStringContainsString('Final source-state lock status: `LOCKED`', $proofPack);
        $this->assertStringContainsString('- FULL_MARKET_DATA_PRODUCTION_READY_CONTRACT -> LOCKED', $tracker);
        $this->assertStringContainsString('FINAL_AUDIT_DOCS_SYNCHRONIZED', $tracker.$proofPack);

        // A provisional claim scoped to one delivered archive must not survive the final lock.
        $this->assertStringNotContainsString('Full market-data production-ready: `CLAIMED_FOR_THIS_SOURCE_ZIP`', $inventory.$proofPack);
    }

    private function firstNonEmptyLineAfter(string $document, string $heading): string
    {
        $position = strpos($document, $heading);
        $this->assertNotFalse($position, $heading.' heading must exist.');

        foreach (preg_split('/\R/', substr($document, $position + strlen($heading))) as $line) {
            if (trim($line) !== '') {
                return trim($line);
            }
        }

        $this->fail('No non-empty line found after '.$heading.'.');
    }
}
