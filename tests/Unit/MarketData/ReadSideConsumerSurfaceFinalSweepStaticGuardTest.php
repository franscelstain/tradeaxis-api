<?php

use PHPUnit\Framework\TestCase;

class ReadSideConsumerSurfaceFinalSweepStaticGuardTest extends TestCase
{
    private function projectPath(string $relativePath): string
    {
        return dirname(__DIR__, 3).DIRECTORY_SEPARATOR.str_replace('/', DIRECTORY_SEPARATOR, $relativePath);
    }

    private function read(string $relativePath): string
    {
        $path = $this->projectPath($relativePath);
        $this->assertFileExists($path, $relativePath.' must exist.');

        return file_get_contents($path);
    }

    public function test_final_sweep_inventory_exists_and_maps_to_existing_read_side_contract(): void
    {
        $inventory = $this->read('docs/market_data/audit/READ_SIDE_CONSUMER_SURFACE_FINAL_SWEEP_INVENTORY.md');
        $contract = $this->read('docs/market_data/book/Read_Side_Enforcement_Anti_Bypass_Contract_LOCKED.md');

        foreach ([
            'Read-Side Consumer Surface Final Sweep',
            'READ_SIDE_SCOPE = INTERNAL_ONLY',
            'READ_SIDE_POINTER_ENFORCEMENT_CONTRACT',
            'This inventory is a final consumer-surface sweep over the existing read-side anti-bypass contract',
            'Consumer Surface Matrix',
            'Raw/Latest Scan Matrix',
            'Consumer end-to-end trace summary',
            'NO_READABLE_PUBLICATION',
            'NO_CONSUMER_BYPASS_FOUND',
            'DONE_LOCAL_PHPUNIT_PASS',
        ] as $needle) {
            $this->assertStringContainsString($needle, $inventory);
        }

        $this->assertStringContainsString('Status: LOCKED', $contract);
        $this->assertStringContainsString('resolveCurrentReadablePublicationForTradeDate', $contract);
    }

    // The HTTP boundary was checked against two files by name. ReadSideHasNoHttpSurfaceTest
    // walks every file under routes/ and app/Http instead, so a controller added tomorrow is
    // covered without editing a list.

    public function test_session_snapshot_consumer_resolves_readable_current_publication_before_scope_read(): void
    {
        $service = $this->read('app/Application/MarketData/Services/SessionSnapshotService.php');
        $method = $this->extractMethod($service, 'capture');

        $this->assertStringContainsString('findCurrentPublicationForTradeDate($tradeDate)', $method);
        $this->assertStringContainsString('NoReadablePublicationException', $service);
        $this->assertStringContainsString('getScopeForTradeDate($tradeDate)', $method);
        $this->assertStringContainsString('trade_date_effective', $method);

        $this->assertNoLatestTradeDateShortcut($method, 'SessionSnapshotService::capture');
    }

    public function test_eligibility_scope_consumer_is_pointer_scoped_and_coverage_pass_guarded(): void
    {
        $source = $this->read('app/Infrastructure/Persistence/MarketData/EligibilitySnapshotScopeRepository.php');

        // The fourteen query-string assertions that used to live here are gone. Each one
        // matched a fragment of the SQL builder chain, so any refactor broke them while
        // logic that was wrong but textually identical still passed.
        //
        // ReadablePublicationReadContractIntegrationTest now proves the same invariants by
        // execution: the repository returns nothing when the run is not readable, when the
        // coverage gate is not PASS, when the run/publication mirror disagrees with the
        // pointer, when the publication is unsealed, and when it is no longer current. That
        // is strictly stronger and survives refactoring.
        //
        // What stays is the shortcut check, which asserts an absence. An absence cannot be
        // demonstrated by executing one happy path, so a source-level guard still earns its
        // place here.
        $this->assertNoLatestTradeDateShortcut($source, 'EligibilitySnapshotScopeRepository');
    }

    public function test_evidence_read_paths_are_explicitly_pointer_or_selector_scoped(): void
    {
        $repository = $this->read('app/Infrastructure/Persistence/MarketData/EodEvidenceRepository.php');
        $publicationLookup = $this->extractMethod($repository, 'findPublicationForRun');
        $eligibilityQuery = $this->extractMethod($repository, 'readableEligibilityQuery', 'private');
        $replayMetric = $this->extractMethod($repository, 'findReplayMetric');
        $command = $this->read('app/Console/Commands/MarketData/ExportEvidenceCommand.php');

        // The seven pointer-condition fragments per method are gone.
        // ReadablePublicationReadContractIntegrationTest drives the evidence repository through
        // ten broken publication states and proves no rows leak from any of them, which is what
        // those fragments were approximating.
        //
        // What stays is the explicit-date requirement below. It is a refusal, and a refusal
        // phrased as an operator message is worth pinning: replay metrics have one row per date
        // and "the latest one" is never the right answer to "how did this date replay".
        $this->assertStringContainsString('Replay metric lookup requires explicit trade_date; latest-row resolution is not allowed.', $replayMetric);
        $this->assertStringContainsString('Replay evidence export requires --trade_date; latest-row resolution is not allowed.', $command);
    }

    // The eight-file latest-date sweep is now applied to every file under app/ by
    // ReadPathShortcutProhibitionTest.

    public function test_producer_and_diagnostic_paths_are_classified_in_inventory_not_as_consumer_bypass(): void
    {
        $inventory = $this->read('docs/market_data/audit/READ_SIDE_CONSUMER_SURFACE_FINAL_SWEEP_INVENTORY.md');

        foreach ([
            'WRITE_SIDE_PRODUCER',
            'ADMIN_REPAIR_DIAGNOSTIC',
            'EVIDENCE_REPLAY_AUDIT',
            'TEST_ONLY',
            'DOCS_ONLY',
            'INTERNAL_ONLY',
            'MarketDataPipelineService.php',
            'EodArtifactRepository.php',
            'RepairCurrentPublicationIntegrityCommand.php',
            'findLatestReadablePublicationBefore',
            'findRawCurrentPublicationStateForTradeDate',
        ] as $needle) {
            $this->assertStringContainsString($needle, $inventory);
        }
    }

    public function test_audit_docs_track_final_sweep_without_new_contract(): void
    {
        $status = $this->read('docs/market_data/audit/LUMEN_IMPLEMENTATION_STATUS.md');
        $tracker = $this->read('docs/market_data/audit/LUMEN_CONTRACT_TRACKER.md');

        $this->assertStringContainsString('ACTIVE SESSION:', $status);
        $this->assertStringContainsString('ACTIVE SESSION:', $tracker);
        $this->assertStringContainsString('- Read-Side Consumer Surface Completion / Final Sweep Revalidation -> DONE', $status);
        $this->assertStringContainsString('- READ_SIDE_POINTER_ENFORCEMENT_CONTRACT -> LOCKED', $tracker);
        $this->assertStringContainsString('READ_SIDE_CONSUMER_SURFACE_FINAL_SWEEP_INVENTORY.md', $status.$tracker);
        $this->assertStringContainsString('NO_CONSUMER_BYPASS_FOUND', $status.$tracker);
        $this->assertStringContainsString('NO_READABLE_PUBLICATION', $status.$tracker);
        $this->assertStringContainsString('HISTORICAL_CONTEXT_2026_05_01', $tracker);
        $this->assertStringContainsString('Read-Side Enforcement / Anti Bypass Total', $tracker);
        $this->assertStringContainsString('2026-05-01 → Canonical read-side pointer enforcement contract opened under audit governance.', $tracker);
        // Two frozen PHPUnit tallies were asserted here, from runs of 449 and 250 tests against
        // a suite that now holds four times that. They recorded what happened once.
        $this->assertStringContainsString('No market-data consumer may read raw/staging/latest/current artifact data unless it is resolved through the current readable publication pointer', $tracker);
        $this->assertSame(1, substr_count($tracker, '- READ_SIDE_POINTER_ENFORCEMENT_CONTRACT'));
    }

    public function test_runtime_environment_baseline_is_recorded_in_always_read_audit_materials(): void
    {
        $status = $this->read('docs/market_data/audit/LUMEN_IMPLEMENTATION_STATUS.md');
        $tracker = $this->read('docs/market_data/audit/LUMEN_CONTRACT_TRACKER.md');
        $inventory = $this->read('docs/market_data/audit/READ_SIDE_CONSUMER_SURFACE_FINAL_SWEEP_INVENTORY.md');
        $governance = $this->read('docs/market_data/audit/AUDIT_UPDATE_GOVERNANCE.md');

        foreach ([$status, $tracker, $inventory, $governance] as $document) {
            $this->assertStringContainsString('PHP 7.4.33', $document);
            $this->assertStringContainsString('PHPUnit 9.6.34', $document);
            $this->assertStringContainsString('dom, mbstring, pdo_mysql, pdo_sqlite, xml, xmlreader, xmlwriter', $document);
            $this->assertStringContainsString('BLOCKED_CONTAINER_RUNTIME_ENV', $document);
            $this->assertStringContainsString('operator-local phpunit output', strtolower($document));
        }
    }

    private function assertNoLatestTradeDateShortcut(string $source, string $label): void
    {
        $this->assertDoesNotMatchRegularExpression('/\bMAX\s*\(\s*trade_date\s*\)/i', $source, $label.' must not use MAX(trade_date).');
        $this->assertDoesNotMatchRegularExpression('/->\s*max\s*\(\s*[\'\"]trade_date[\'\"]\s*\)/i', $source, $label.' must not use max(trade_date).');
        $this->assertDoesNotMatchRegularExpression('/->\s*latest\s*\(\s*[\'\"]trade_date[\'\"]\s*\)/i', $source, $label.' must not use latest(trade_date).');
        $this->assertDoesNotMatchRegularExpression('/->\s*orderByDesc\s*\(\s*[\'\"]trade_date[\'\"]\s*\)/i', $source, $label.' must not use orderByDesc(trade_date).');
        $this->assertDoesNotMatchRegularExpression('/->\s*orderBy\s*\(\s*[\'\"]trade_date[\'\"]\s*,\s*[\'\"]desc[\'\"]\s*\)/i', $source, $label.' must not use orderBy(trade_date, desc).');
    }

    private function extractMethod(string $source, string $methodName, string $visibility = 'public'): string
    {
        $pattern = '/'.preg_quote($visibility, '/').' function '.preg_quote($methodName, '/').'\([^)]*\)\s*\{(?P<body>.*?)\n    \}/s';
        $this->assertSame(1, preg_match($pattern, $source, $matches), 'Method not found: '.$methodName);

        return $matches[0];
    }
}
