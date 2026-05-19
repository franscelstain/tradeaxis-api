<?php

use PHPUnit\Framework\TestCase;

class AuditDocsSynchronizationStaticGuardTest extends TestCase
{
    private function projectPath(string $path): string
    {
        return dirname(__DIR__, 3).DIRECTORY_SEPARATOR.str_replace('/', DIRECTORY_SEPARATOR, $path);
    }

    private function readProjectFile(string $path): string
    {
        $fullPath = $this->projectPath($path);
        $this->assertFileExists($fullPath);

        return file_get_contents($fullPath);
    }

    public function test_audit_docs_have_active_session_and_contract_tracker_alignment(): void
    {
        $status = $this->readProjectFile('docs/market_data/audit/LUMEN_IMPLEMENTATION_STATUS.md');
        $tracker = $this->readProjectFile('docs/market_data/audit/LUMEN_CONTRACT_TRACKER.md');

        $statusActiveSession = $this->activeSessionName($status);
        $trackerActiveSession = $this->activeSessionName($tracker);

        $this->assertSame($statusActiveSession, $trackerActiveSession, 'Implementation status and contract tracker must name the same active session.');
        $this->assertSame('Replay Determinism Runtime Proof', $statusActiveSession);
        $this->assertStringContainsString("ACTIVE SESSION:\n- ".$statusActiveSession, $status);
        $this->assertStringContainsString("ACTIVE SESSION:\n- ".$trackerActiveSession, $tracker);

        foreach ([$status, $tracker] as $document) {
            $this->assertStringContainsString('AUDIT_DOCS_SYNCHRONIZATION_CONTRACT', $document);
            $this->assertStringContainsString('POST_SESSION_1_8_LOCKED_LOCAL_PHPUNIT_PASS', $document);
            $this->assertStringContainsString('OPERATIONAL_READINESS_CONTRACT', $document);
            $this->assertStringContainsString('PRODUCTION_VALIDATION_CONTRACT', $document);
            $this->assertStringContainsString('OPS_ENVIRONMENT_BASELINE_CONTRACT', $document);
            $this->assertStringContainsString('COVERAGE_POLICY_RECONCILIATION_CONTRACT', $document);
            $this->assertStringContainsString('DB_SCHEMA_AND_MIGRATION_SYNC_CONTRACT', $document);
            $this->assertStringContainsString('READ_SIDE_POINTER_ENFORCEMENT_CONTRACT', $document);
            $this->assertStringContainsString('EVIDENCE_EXPORT_RUNTIME_PROOF_CONTRACT', $document);
            $this->assertStringContainsString('REPLAY_DETERMINISM_RUNTIME_PROOF_CONTRACT', $document);
        }

        $this->assertStringContainsString('- Replay Determinism Runtime Proof / PASS-FAIL-BLOCKED Evidence Linkage -> DONE', $status);
        $this->assertStringContainsString('[RELATED_CONTRACT] REPLAY_DETERMINISM_RUNTIME_PROOF_CONTRACT', $status);
        $this->assertStringContainsString('- REPLAY_DETERMINISM_RUNTIME_PROOF_CONTRACT -> LOCKED', $tracker);
        $this->assertStringContainsString('[RELATED_IMPLEMENTATION] Replay Determinism Runtime Proof / PASS-FAIL-BLOCKED Evidence Linkage', $tracker);
        $this->assertStringContainsString('replay_status=PASS', $status.$tracker);
        $this->assertStringContainsString('replay_status=FAIL', $status.$tracker);
        $this->assertStringContainsString('replay_status=BLOCKED', $status.$tracker);
        $this->assertStringContainsString('REPLAY_FINAL_REASON_CODE_MISMATCH', $status.$tracker);
        $this->assertStringContainsString('storage/app/market-data/replay-determinism-runtime-proof/verify-pass/replay_result.json', $status.$tracker);

        $this->assertStringContainsString('- Evidence Export Runtime Proof / Admission Artifact Hardening -> DONE', $status);
        $this->assertStringContainsString('Full evidence export runtime proof across run + correction + replay is LOCKED', $status);
        $this->assertStringContainsString('[RELATED_CONTRACT] EVIDENCE_EXPORT_RUNTIME_PROOF_CONTRACT', $status);
        $this->assertStringContainsString('- EVIDENCE_EXPORT_RUNTIME_PROOF_CONTRACT -> LOCKED', $tracker);
        $this->assertStringContainsString('full run+correction+replay runtime artifact scope', $tracker);
        $this->assertStringContainsString('UNCHANGED_CORRECTION_CANDIDATE_DISCARDED', $status.$tracker);
        $this->assertStringContainsString('Operator-local post-patch correction re-export for `correction_id=1`', $status);
        $this->assertStringContainsString('Replay runtime proof has been supplied for `replay_id=1`', $status);
        $this->assertStringContainsString('[RELATED_IMPLEMENTATION] Evidence Export Runtime Proof / Admission Artifact Hardening', $tracker);
        $this->assertStringContainsString('- Read-Side Consumer Surface Completion / Final Sweep Revalidation -> DONE', $status);
        $this->assertStringContainsString('[RELATED_CONTRACT] READ_SIDE_POINTER_ENFORCEMENT_CONTRACT', $status);
        $this->assertStringContainsString('- READ_SIDE_POINTER_ENFORCEMENT_CONTRACT -> LOCKED', $tracker);
        $this->assertStringContainsString('[RELATED_IMPLEMENTATION] Read-Side Consumer Surface Completion / Final Sweep Revalidation', $tracker);
        $this->assertStringContainsString('- DB Schema & Migration Sync / Runtime Schema Four-Way Synchronization ->', $status);
        $this->assertStringContainsString('[RELATED_CONTRACT] DB_SCHEMA_AND_MIGRATION_SYNC_CONTRACT', $status);
        $this->assertStringContainsString('- DB_SCHEMA_AND_MIGRATION_SYNC_CONTRACT ->', $tracker);
        $this->assertStringContainsString('[RELATED_IMPLEMENTATION] DB Schema & Migration Sync / Runtime Schema Four-Way Synchronization', $tracker);
        $this->assertStringContainsString('- Coverage Policy Reconciliation -> DONE', $status);
        $this->assertStringContainsString('[RELATED_CONTRACT] COVERAGE_POLICY_RECONCILIATION_CONTRACT', $status);
        $this->assertStringContainsString('- COVERAGE_POLICY_RECONCILIATION_CONTRACT -> LOCKED', $tracker);
        $this->assertStringContainsString('[RELATED_IMPLEMENTATION] Coverage Policy Reconciliation', $tracker);
        $this->assertStringContainsString('- Audit Docs Synchronization -> DONE', $status);
        $this->assertStringContainsString('[RELATED_CONTRACT] AUDIT_DOCS_SYNCHRONIZATION_CONTRACT', $status);
        $this->assertStringContainsString('- AUDIT_DOCS_SYNCHRONIZATION_CONTRACT -> LOCKED', $tracker);
        $this->assertStringContainsString('[RELATED_IMPLEMENTATION] Audit Docs Synchronization', $tracker);
        $this->assertStringContainsString('- Ops Environment Baseline -> DONE', $status);
        $this->assertStringContainsString('[RELATED_CONTRACT] OPS_ENVIRONMENT_BASELINE_CONTRACT', $status);
        $this->assertStringContainsString('- OPS_ENVIRONMENT_BASELINE_CONTRACT -> LOCKED', $tracker);
        $this->assertStringContainsString('[RELATED_IMPLEMENTATION] Ops Environment Baseline', $tracker);
    }

    public function test_current_working_sections_start_with_active_session(): void
    {
        $status = $this->readProjectFile('docs/market_data/audit/LUMEN_IMPLEMENTATION_STATUS.md');
        $tracker = $this->readProjectFile('docs/market_data/audit/LUMEN_CONTRACT_TRACKER.md');

        $implementationEntry = $this->firstNonEmptyLineAfter($status, '## CURRENT WORKING ENTRY');
        $contractEntry = $this->firstNonEmptyLineAfter($tracker, '## CURRENT WORKING CONTRACT');

        $activeSession = $this->activeSessionName($status);
        $this->assertStringContainsString($activeSession, $implementationEntry);
        $this->assertStringContainsString('REPLAY_DETERMINISM_RUNTIME_PROOF_CONTRACT', $contractEntry);

        $implementationContracts = $this->relatedContractsFromImplementationStatus($status);
        $trackerContracts = $this->canonicalContractsFromTracker($tracker);

        foreach ($implementationContracts as $contractName) {
            $this->assertContains($contractName, $trackerContracts, $contractName.' is referenced by implementation status but missing from contract tracker.');
        }

        $this->assertMatchesRegularExpression('/^- [A-Z0-9_]+_CONTRACT (?:->|→) (LOCKED|ENFORCED|PARTIAL|BLOCKED|REVIEW_REQUIRED)$/', $contractEntry);
    }

    public function test_locked_contracts_have_concrete_validation_evidence(): void
    {
        $tracker = $this->readProjectFile('docs/market_data/audit/LUMEN_CONTRACT_TRACKER.md');
        $lockedBlocks = $this->contractBlocksByStatus($tracker, 'LOCKED');

        $this->assertNotEmpty($lockedBlocks);

        foreach ($lockedBlocks as $contractName => $block) {
            $this->assertStringContainsString('[VALIDATED]', $block, $contractName.' must have a VALIDATED section.');
            $this->assertStringContainsString('[FINAL_RULE]', $block, $contractName.' must have a FINAL_RULE section.');
            $this->assertMatchesRegularExpression('/(Operator-local|Operator local|Local PHPUnit|PHPUnit\/artisan validation was supplied by operator|local validation)/i', $block, $contractName.' must cite local/operator validation.');
            $this->assertMatchesRegularExpression('/(OK \(|PASS|passed)/i', $block, $contractName.' must cite a passing validation result.');
            $this->assertStringContainsString('tests/Unit/MarketData', $block, $contractName.' must cite MarketData validation scope.');
        }
    }

    public function test_audit_docs_do_not_duplicate_canonical_contracts(): void
    {
        $tracker = $this->readProjectFile('docs/market_data/audit/LUMEN_CONTRACT_TRACKER.md');
        preg_match_all('/^- ([A-Z0-9_]+_CONTRACT) (?:->|→)/mu', $tracker, $matches);

        $contracts = $matches[1];
        $this->assertNotEmpty($contracts);
        $this->assertCount(count(array_unique($contracts)), $contracts, 'Canonical contract names must not be duplicated.');
        $this->assertSame(1, substr_count($tracker, '- AUDIT_DOCS_SYNCHRONIZATION_CONTRACT ->'));
    }

    public function test_implementation_status_and_contract_tracker_are_synchronized(): void
    {
        $status = $this->readProjectFile('docs/market_data/audit/LUMEN_IMPLEMENTATION_STATUS.md');
        $tracker = $this->readProjectFile('docs/market_data/audit/LUMEN_CONTRACT_TRACKER.md');

        $implementationContracts = $this->relatedContractsFromImplementationStatus($status);
        $trackerContracts = $this->canonicalContractsFromTracker($tracker);

        foreach ($implementationContracts as $contractName) {
            $this->assertContains($contractName, $trackerContracts, $contractName.' is referenced by implementation status but missing from contract tracker.');
        }

        $this->assertContains('AUDIT_DOCS_SYNCHRONIZATION_CONTRACT', $implementationContracts);
        $this->assertContains('AUDIT_DOCS_SYNCHRONIZATION_CONTRACT', $trackerContracts);
        $this->assertContains('EVIDENCE_EXPORT_RUNTIME_PROOF_CONTRACT', $implementationContracts);
        $this->assertContains('EVIDENCE_EXPORT_RUNTIME_PROOF_CONTRACT', $trackerContracts);
        $this->assertContains('REPLAY_DETERMINISM_RUNTIME_PROOF_CONTRACT', $implementationContracts);
        $this->assertContains('REPLAY_DETERMINISM_RUNTIME_PROOF_CONTRACT', $trackerContracts);
    }

    public function test_audit_governance_enforces_append_only_anti_duplication_and_static_guard(): void
    {
        $governance = $this->readProjectFile('docs/market_data/audit/AUDIT_UPDATE_GOVERNANCE.md');
        $inventory = $this->readProjectFile('docs/market_data/audit/AUDIT_DOCS_SYNCHRONIZATION_INVENTORY.md');
        $postSessionInventory = $this->readProjectFile('docs/market_data/audit/AUDIT_DOCS_SYNCHRONIZATION_POST_SESSION_1_8_INVENTORY.md');

        foreach ([
            'AUDIT DOCS SYNCHRONIZATION HARD RULE',
            'append-only',
            'anti-duplication',
            'ACTIVE SESSION',
            'targeted and full local PHPUnit evidence',
            'LOCKED_LOCAL_PHPUNIT_PASS',
            'AuditDocsSynchronizationStaticGuardTest.php',
            'AUDIT_DOCS_SYNCHRONIZATION_CONTRACT',
            'RUNTIME ENVIRONMENT BASELINE HARD RULE',
            'operator-local PHP version',
            'operator-local PHPUnit version',
            'POST_SESSION_1_8_LOCKED_LOCAL_PHPUNIT_PASS',
        ] as $needle) {
            $this->assertStringContainsString($needle, $governance.$inventory.$postSessionInventory);
        }
    }

    public function test_latest_market_data_full_suite_evidence_is_recorded(): void
    {
        $status = $this->readProjectFile('docs/market_data/audit/LUMEN_IMPLEMENTATION_STATUS.md');
        $tracker = $this->readProjectFile('docs/market_data/audit/LUMEN_CONTRACT_TRACKER.md');
        $inventory = $this->readProjectFile('docs/market_data/audit/AUDIT_DOCS_SYNCHRONIZATION_POST_SESSION_1_8_INVENTORY.md');

        foreach ([$status, $tracker, $inventory] as $document) {
            $this->assertStringContainsString('349 tests, 4558 assertions', $document);
            $this->assertStringContainsString('358 tests, 4711 assertions', $document);
            $this->assertStringContainsString('368 tests, 4927 assertions', $document);
            $this->assertStringContainsString('435 tests, 6299 assertions', $document);
            $this->assertStringContainsString('435 tests, 6318 assertions', $document);
            $this->assertStringContainsString('164 tests, 3702 assertions', $document);
            $this->assertStringContainsString('164 tests, 3721 assertions', $document);
            $this->assertStringContainsString('vendor/bin/phpunit tests/Unit/MarketData', $document);
            $this->assertStringContainsString('not a new container PHPUnit run', $document);
        }
    }

    public function test_reason_code_registry_and_seed_are_synchronized(): void
    {
        $registry = $this->readProjectFile('docs/market_data/registry/Reason_Codes_Registry.md');
        $seed = $this->readProjectFile('docs/market_data/registry/Reason_Codes_Seed.sql');

        preg_match_all('/^\| `([A-Z0-9_]+)` \|/m', $registry, $registryMatches);
        preg_match_all("/\('([A-Z0-9_]+)'\s*,/", $seed, $seedMatches);

        $registryCodes = array_values(array_unique($registryMatches[1]));
        $seedCodes = array_values(array_unique($seedMatches[1]));
        sort($registryCodes);
        sort($seedCodes);

        $this->assertCount(325, $registryCodes);
        $this->assertSame($registryCodes, $seedCodes, 'Reason code registry and seed must stay synchronized.');
    }

    public function test_post_session_audit_docs_scope_is_locked_after_final_local_proof(): void
    {
        $status = $this->readProjectFile('docs/market_data/audit/LUMEN_IMPLEMENTATION_STATUS.md');
        $tracker = $this->readProjectFile('docs/market_data/audit/LUMEN_CONTRACT_TRACKER.md');
        $inventory = $this->readProjectFile('docs/market_data/audit/AUDIT_DOCS_SYNCHRONIZATION_POST_SESSION_1_8_INVENTORY.md');

        $implementationBlock = $this->implementationBlock($status, 'Audit Docs Synchronization');
        $contractBlock = $this->contractBlock($tracker, 'AUDIT_DOCS_SYNCHRONIZATION_CONTRACT');
        $combined = $implementationBlock.$contractBlock.$inventory;

        $this->assertStringContainsString('[NEXT_ACTION]', $implementationBlock);
        $this->assertStringContainsString('[LOCK_CONDITION]', $contractBlock);
        $this->assertStringContainsString('POST_SESSION_1_8_LOCKED_LOCAL_PHPUNIT_PASS', $combined);
        $this->assertStringContainsString('BLOCKED_CONTAINER_RUNTIME_ENV', $combined);
        $this->assertStringContainsString('ENV_UNSUPPORTED_PHP_VERSION', $combined);
        $this->assertStringContainsString('164 tests, 3721 assertions', $combined);
        $this->assertStringContainsString('435 tests, 6318 assertions', $combined);
        $this->assertStringContainsString('- Audit Docs Synchronization -> DONE', $status);
        $this->assertStringContainsString('- AUDIT_DOCS_SYNCHRONIZATION_CONTRACT -> LOCKED', $tracker);
        $this->assertStringNotContainsString('PENDING_OPERATOR_LOCAL_PHPUNIT_RERUN', $combined);
    }

    private function activeSessionName(string $document): string
    {
        $this->assertMatchesRegularExpression('/ACTIVE SESSION:\R- (?P<session>[^\r\n]+)/', $document);
        preg_match('/ACTIVE SESSION:\R- (?P<session>[^\r\n]+)/', $document, $match);

        return trim($match['session']);
    }

    private function firstNonEmptyLineAfter(string $document, string $heading): string
    {
        $position = strpos($document, $heading);
        $this->assertNotFalse($position, $heading.' heading must exist.');

        $afterHeading = substr($document, $position + strlen($heading));
        foreach (preg_split('/\R/', $afterHeading) as $line) {
            if (trim($line) !== '') {
                return trim($line);
            }
        }

        $this->fail('No non-empty line found after '.$heading.'.');
    }

    private function contractBlocksByStatus(string $tracker, string $status): array
    {
        preg_match_all('/^- ([A-Z0-9_]+_CONTRACT) -> '.preg_quote($status, '/').'\R(?P<body>.*?)(?=^- [A-Z0-9_]+_CONTRACT (?:->|→)|\z)/msu', $tracker, $matches, PREG_SET_ORDER);

        $blocks = [];
        foreach ($matches as $match) {
            $blocks[$match[1]] = $match[0];
        }

        return $blocks;
    }

    private function relatedContractsFromImplementationStatus(string $status): array
    {
        preg_match_all('/\[RELATED_CONTRACT\] ([A-Z0-9_]+_CONTRACT)/', $status, $matches);

        return array_values(array_unique($matches[1]));
    }

    private function canonicalContractsFromTracker(string $tracker): array
    {
        preg_match_all('/^- ([A-Z0-9_]+_CONTRACT) (?:->|→)/mu', $tracker, $matches);

        return array_values(array_unique($matches[1]));
    }

    private function implementationBlock(string $status, string $name): string
    {
        $pattern = '/^- '.preg_quote($name, '/').' -> [A-Z_]+\R.*?(?=^- .+ (?:->|→) [A-Z_]+\R|\z)/msu';
        $this->assertMatchesRegularExpression($pattern, $status);
        preg_match($pattern, $status, $match);

        return $match[0];
    }

    private function contractBlock(string $tracker, string $name): string
    {
        $pattern = '/^- '.preg_quote($name, '/').' (?:->|→) [A-Z_]+\R.*?(?=^- [A-Z0-9_]+_CONTRACT (?:->|→) [A-Z_]+\R|\z)/msu';
        $this->assertMatchesRegularExpression($pattern, $tracker);
        preg_match($pattern, $tracker, $match);

        return $match[0];
    }
}
