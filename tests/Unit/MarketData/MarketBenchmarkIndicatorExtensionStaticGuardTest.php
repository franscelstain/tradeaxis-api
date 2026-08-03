<?php

class MarketBenchmarkIndicatorExtensionStaticGuardTest extends TestCase
{
    private function projectPath(string $relativePath): string
    {
        return dirname(__DIR__, 3).DIRECTORY_SEPARATOR.str_replace('/', DIRECTORY_SEPARATOR, $relativePath);
    }

    private function read(string $relativePath): string
    {
        $path = $this->projectPath($relativePath);
        $this->assertFileExists($path);

        return file_get_contents($path);
    }

    public function test_benchmark_schema_keeps_ihsg_outside_equity_tickers_boundary(): void
    {
        $schema = $this->read('docs/market_data/db/Database_Schema_MariaDB.sql');
        $migration = $this->read('database/migrations/2026_05_24_000001_add_market_benchmark_indicator_extension.php');

        foreach ([$schema, $migration] as $source) {
            $this->assertStringContainsString('market_benchmarks', $source);
            $this->assertStringContainsString('market_benchmark_bars', $source);
            $this->assertStringContainsString('market_benchmark_indicators', $source);
            $this->assertStringContainsString('IHSG', $source);
            $this->assertStringContainsString('^JKSE', $source);
            $this->assertStringContainsString('INDEX', $source);
        }

        $this->assertStringNotContainsString("ticker_code' => 'IHSG'", $migration);
        $this->assertStringNotContainsString("'ticker_code', 'IHSG'", $schema);
    }

    /**
     * Only the routing remains asserted here: the adapter must reach both resolvers, so an
     * index and an equity cannot share one path.
     *
     * What each resolver produces is now proven by ProviderSymbolResolverTest, which calls
     * them. The old check searched for the literal "^JKSE.JK" and so ruled out exactly one
     * known-bad string while missing the same defect for any other index symbol.
     */
    public function test_adapter_routes_benchmarks_and_equities_through_separate_resolvers(): void
    {
        $adapter = $this->read('app/Infrastructure/MarketData/Source/PublicApiEodBarsAdapter.php');

        $this->assertStringContainsString('fetchOrLoadBenchmarkBars', $adapter);
        $this->assertStringContainsString('BenchmarkProviderSymbolResolver', $adapter);
        $this->assertStringContainsString('EquityProviderSymbolResolver', $adapter);
    }

    public function test_rs_20_vs_ihsg_depends_on_benchmark_roc20_not_hardcoded_value(): void
    {
        $indicatorCompute = $this->read('app/Application/MarketData/Services/EodIndicatorsComputeService.php');
        $vector = $this->read('app/Application/MarketData/Services/IndicatorVectorService.php');

        $this->assertStringContainsString("roc20('IHSG', \$requestedDate)", $indicatorCompute);
        $this->assertStringContainsString('benchmark_roc20_pct', $vector);
        $this->assertStringContainsString('rs_20_vs_ihsg', $vector);
        $this->assertDoesNotMatchRegularExpression('/rs_20_vs_ihsg[^\r\n]+(=|=>)\s*[0-9]+(?:\.[0-9]+)?/', $vector);
    }

    // Three tests were removed.
    //
    // The null-safe denominator and lookback guards were asserted as source strings.
    // IndicatorVectorServiceTest already drives them: a null benchmark leaves rs_20_vs_ihsg null
    // rather than falling back to the raw equity return, and a zero denominator produces null
    // without error.
    //
    // The benchmark repository's latest-date prohibition is now applied to every file under app/
    // by ReadPathShortcutProhibitionTest. What the repository actually resolves is proven by
    // BenchmarkRoc20ResolutionTest, which had no predecessor of any kind — the repository that
    // supplies the denominator of relative strength was untested.
    //
    // The production-ready doc test asserted a frozen tally, "511 tests, 7871 assertions",
    // against a suite that now holds many times that. It recorded one past run and could only
    // ever be archaeology or churn.
}
