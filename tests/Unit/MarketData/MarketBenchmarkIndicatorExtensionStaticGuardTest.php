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

    public function test_benchmark_provider_symbol_is_never_suffixed_as_jk_equity_symbol(): void
    {
        $adapter = $this->read('app/Infrastructure/MarketData/Source/PublicApiEodBarsAdapter.php');
        $benchmarkResolver = $this->read('app/Infrastructure/MarketData/Source/BenchmarkProviderSymbolResolver.php');
        $equityResolver = $this->read('app/Infrastructure/MarketData/Source/EquityProviderSymbolResolver.php');

        $this->assertStringContainsString('fetchOrLoadBenchmarkBars', $adapter);
        $this->assertStringContainsString('BenchmarkProviderSymbolResolver', $adapter);
        $this->assertStringContainsString('EquityProviderSymbolResolver', $adapter);
        $this->assertStringContainsString('return $providerSymbol;', $benchmarkResolver);
        $this->assertStringContainsString('$symbol.$suffix', $equityResolver);
        $this->assertStringNotContainsString('^JKSE.JK', $adapter.$benchmarkResolver.$equityResolver);
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

    public function test_indicator_extension_has_null_safe_denominator_and_lookback_guards(): void
    {
        $vector = $this->read('app/Application/MarketData/Services/IndicatorVectorService.php');
        $benchmarkVector = $this->read('app/Application/MarketData/Services/BenchmarkIndicatorVectorService.php');

        foreach (['pctDifference', '$base === null', '(float) $base <= 0', 'return null'] as $needle) {
            $this->assertStringContainsString($needle, $vector);
        }

        foreach (['IND_INSUFFICIENT_HISTORY', 'IND_INVALID_BAR_INPUT', 'hasInvalidCloseInput', 'return null'] as $needle) {
            $this->assertStringContainsString($needle, $benchmarkVector);
        }
    }

    public function test_benchmark_repository_uses_explicit_trade_date_no_latest_shortcut(): void
    {
        $repository = $this->read('app/Infrastructure/Persistence/MarketData/MarketBenchmarkRepository.php');

        $this->assertStringContainsString("where('trade_date', \$tradeDate)", $repository);
        $this->assertDoesNotMatchRegularExpression('/\bMAX\s*\(\s*trade_date\s*\)/i', $repository);
        $this->assertDoesNotMatchRegularExpression('/->\s*max\s*\(\s*[\'"]trade_date[\'"]\s*\)/i', $repository);
        $this->assertDoesNotMatchRegularExpression('/orderByDesc\s*\(\s*[\'"]trade_date[\'"]\s*\)/i', $repository);
    }
public function test_benchmark_indicator_extension_production_ready_docs_are_locked(): void
{
    $status = $this->read('docs/market_data/audit/LUMEN_IMPLEMENTATION_STATUS.md');
    $tracker = $this->read('docs/market_data/audit/LUMEN_CONTRACT_TRACKER.md');
    $inventory = $this->read('docs/market_data/audit/MARKET_BENCHMARK_INDICATOR_EXTENSION_INVENTORY.md');
    $proofLock = $this->read('storage/app/market_data/evidence/2026-05-19/benchmark-extension/FINAL_MARKET_DATA_PRODUCTION_READY_LOCK.txt');

    foreach ([$status, $tracker, $inventory, $proofLock] as $source) {
        $this->assertStringContainsString('MARKET_BENCHMARK_INDICATOR_EXTENSION_STATUS', $source);
        $this->assertStringContainsString('PASS', $source);
        $this->assertStringContainsString('FULL_MARKET_DATA_PHPUNIT', $source);
        $this->assertStringContainsString('511 tests, 7871 assertions', $source);
        $this->assertStringContainsString('RUNTIME_VALIDATION', $source);
        $this->assertStringContainsString('EVIDENCE_EXPORT', $source);
        $this->assertStringContainsString('REPLAY_VERIFY', $source);
    }

    $this->assertStringContainsString('^JKSE', $status.$tracker.$inventory);
    $this->assertStringContainsString('IND_INSUFFICIENT_HISTORY', $status.$tracker.$inventory);
}

}
