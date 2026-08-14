<?php

use PHPUnit\Framework\TestCase;

/**
 * Market data has no HTTP surface, and the boundary is derived rather than listed.
 *
 * `ReadSideConsumerSurfaceFinalSweepStaticGuardTest` checked two files by name — routes/web.php
 * and ExampleController.php — for market-data symbols. That covered the HTTP surface as it stood,
 * and a controller added tomorrow would be governed by nothing.
 *
 * The rule is READ_SIDE_SCOPE = INTERNAL_ONLY: every consumer resolves a dataset through
 * EodPublicationRepository, which only ever returns a publication that is sealed, current, and
 * produced by a run that succeeded with coverage passing. A controller reaching into eod_bars or
 * eod_indicators directly bypasses that in one line, and what it returns looks exactly like data.
 *
 * This matters most right now because it is the next thing to be built on. The watchlist surface
 * will be the first real consumer, and the moment it acquires HTTP endpoints this guard decides
 * whether they can quietly read around the pointer.
 */
class ReadSideHasNoHttpSurfaceTest extends TestCase
{
    /**
     * Artifact and publication tables. Reading any of these from an HTTP path means the pointer
     * was not consulted.
     */
    private const ARTIFACT_TABLES = [
        'eod_bars',
        'eod_bars_history',
        'eod_indicators',
        'eod_indicators_history',
        'eod_eligibility',
        'eod_eligibility_history',
        'eod_publications',
        'eod_current_publication_pointer',
        'eod_runs',
        'market_benchmark_bars',
        'market_benchmark_indicators',
    ];

    /**
     * @return array<string, string> relative path => source
     */
    private function httpSurfaceSources(): array
    {
        $root = dirname(__DIR__, 3);
        $sources = [];

        foreach (['routes', 'app'.DIRECTORY_SEPARATOR.'Http'] as $relativeRoot) {
            $directory = $root.DIRECTORY_SEPARATOR.$relativeRoot;

            if (! is_dir($directory)) {
                continue;
            }

            $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($directory));

            foreach ($iterator as $file) {
                if ($file->isFile() && $file->getExtension() === 'php') {
                    $sources[str_replace($root.DIRECTORY_SEPARATOR, '', $file->getPathname())] = file_get_contents($file->getPathname());
                }
            }
        }

        ksort($sources);

        return $sources;
    }

    public function test_the_scan_finds_the_http_surface(): void
    {
        // Guards the guard: a path change returning nothing would make every assertion below
        // vacuous.
        $sources = $this->httpSurfaceSources();

        $this->assertNotEmpty($sources);
        $this->assertArrayHasKey('routes'.DIRECTORY_SEPARATOR.'web.php', $sources);
    }

    /**
     * No HTTP file may name an artifact table. A route or controller that does has stopped being
     * a caller of the read model and become a second, ungoverned one.
     */
    public function test_no_http_file_reads_an_artifact_table_directly(): void
    {
        $violations = [];

        foreach ($this->httpSurfaceSources() as $path => $source) {
            foreach (self::ARTIFACT_TABLES as $table) {
                if (preg_match('/[\'"]'.preg_quote($table, '/').'[\'"]/', $source)) {
                    $violations[] = $path.' names '.$table;
                }
            }
        }

        $this->assertSame([], $violations, 'HTTP surface must not read artifact tables directly.');
    }

    /**
     * Nor may it build queries at all. A raw builder call in a controller is how the table names
     * above end up being reached indirectly.
     */
    public function test_no_http_file_builds_a_database_query(): void
    {
        $violations = [];

        foreach ($this->httpSurfaceSources() as $path => $source) {
            foreach (['DB::table', 'DB::select', 'DB::statement', 'DB::raw'] as $forbidden) {
                if (strpos($source, $forbidden) !== false) {
                    $violations[] = $path.' uses '.$forbidden;
                }
            }
        }

        $this->assertSame([], $violations, 'HTTP surface must not query the database directly.');
    }

    /**
     * The market-data namespace itself must not be reachable from HTTP.
     *
     * This is the wider rule and the reason the two above are not enough: a controller could call
     * MarketDataWatchlistReadService, which resolves the pointer correctly, and still be wrong —
     * the read side is internal, and exposing it over HTTP is a scope decision that has not been
     * made. When it is made, this test is where the decision gets recorded rather than discovered.
     */
    public function test_no_http_file_reaches_into_the_market_data_namespace(): void
    {
        $violations = [];

        foreach ($this->httpSurfaceSources() as $path => $source) {
            if (preg_match('/App\\\\(Application|Infrastructure)\\\\MarketData/', $source)
                || preg_match('/\bMarketData[A-Za-z]*(Service|Repository)\b/', $source)) {
                $violations[] = $path;
            }
        }

        $this->assertSame(
            [],
            $violations,
            'Market data is internal-only. Exposing it over HTTP is a scope change, not an implementation detail.'
        );
    }
}
