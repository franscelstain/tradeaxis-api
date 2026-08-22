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
