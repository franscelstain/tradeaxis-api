<?php

use PHPUnit\Framework\TestCase;

/**
 * Three tests were removed. Each asserted the presence of strings for behaviour now driven.
 *
 * - Gateway usage was asserted as the method name appearing in four files, which a file that
 *   names it and ignores it satisfies. CorrectionBaselineResolutionTest proves all four
 *   publication entry points agree on thirteen broken states, and
 *   ReadablePublicationReadContractIntegrationTest proves the consumer repositories leak no rows
 *   from any of them.
 * - The readiness reason codes were asserted as eight strings present in the service.
 *   MarketDataReadinessServiceTest produces each of them from a real fixture, and
 *   ReadinessDiagnosisAgreementTest holds the reason the consumer is given against the platform's
 *   own diagnosis over every blocked state — which is how a drift was found that string presence
 *   could not see: every one of those eight strings was present while the service still reported
 *   a faulty publication as though nothing had been published.
 * - The latest-date prohibition is now applied to every file under app/ by
 *   ReadPathShortcutProhibitionTest.
 *
 * What remains are prohibitions and a domain rule that execution does not reach.
 */
class MarketDataConsumerReadModelStaticGuardTest extends TestCase
{
    private function read(string $relativePath): string
    {
        $path = dirname(__DIR__, 3).DIRECTORY_SEPARATOR.str_replace('/', DIRECTORY_SEPARATOR, $relativePath);
        $this->assertFileExists($path);

        return file_get_contents($path);
    }

    /**
     * @return string[]
     */
    private function readModelFiles(): array
    {
        return [
            'app/Application/MarketData/Services/MarketDataReadinessService.php',
            'app/Application/MarketData/Services/MarketDataReadProductService.php',
            'app/Infrastructure/Persistence/MarketData/MarketDataReadProductRepository.php',
            'app/Application/MarketData/Services/MarketDataPriceReadService.php',
            'app/Infrastructure/Persistence/MarketData/MarketDataPriceReadRepository.php',
            'app/Application/MarketData/Services/MarketBenchmarkReadService.php',
            'app/Infrastructure/Persistence/MarketData/MarketBenchmarkReadRepository.php',
        ];
    }

    /**
     * Two resolvers exist that are correct in their own context and wrong in this one.
     *
     * findLatestReadablePublicationBefore is the pipeline's fallback when source acquisition
     * fails: it deliberately reaches back to an earlier day so the platform keeps serving
     * something. resolvePublicationForEvidenceAudit resolves a historical publication for
     * evidence and replay, where the point is to read a superseded dataset.
     *
     * A consumer read path calling either would return data under the wrong trade date — an
     * earlier day's bars labelled as today, or a superseded publication presented as current.
     * Both are silent, and both have names that read as reasonable at the call site, which is
     * exactly why the prohibition is worth stating rather than trusting to review.
     */
    public function test_consumer_read_paths_never_use_the_fallback_or_historical_resolvers(): void
    {
        $violations = [];

        foreach ($this->readModelFiles() as $file) {
            $source = $this->read($file);

            foreach (['findLatestReadablePublicationBefore', 'resolvePublicationForEvidenceAudit'] as $forbidden) {
                if (strpos($source, $forbidden) !== false) {
                    $violations[] = $file.' calls '.$forbidden;
                }
            }
        }

        $this->assertSame([], $violations);
    }

    /**
     * Consumers read published artifacts, never the tables data passes through on its way in.
     */
    public function test_consumer_read_paths_never_query_raw_or_staging_tables(): void
    {
        foreach ($this->readModelFiles() as $file) {
            $this->assertDoesNotMatchRegularExpression(
                '/DB::table\s*\(\s*[\'"][^\'"]*(raw|staging)[^\'"]*[\'"]\s*\)/i',
                $this->read($file),
                $file
            );
        }
    }

    /**
     * IHSG is the benchmark the platform measures equities against — rs_20_vs_ihsg compares a
     * ticker's 20-day move to it. It is not itself tradable, so it lives in market_benchmarks
     * rather than tickers. If the benchmark read model reached into the equity ticker universe,
     * or resolved IHSG through an equity provider symbol, the index would become a ticker: it
     * would count toward the coverage universe it is supposed to be measured against, and could
     * be ranked as a candidate.
     */
    public function test_the_benchmark_read_model_keeps_ihsg_outside_the_equity_ticker_universe(): void
    {
        $source = $this->read('app/Infrastructure/Persistence/MarketData/MarketBenchmarkReadRepository.php')
            .$this->read('app/Application/MarketData/Services/MarketBenchmarkReadService.php');

        $this->assertStringContainsString('market_benchmarks as bench', $source);
        $this->assertStringContainsString('market_benchmark_bars as bar', $source);
        $this->assertStringContainsString('market_benchmark_indicators as ind', $source);

        $this->assertStringNotContainsString('tickers as', $source);
        $this->assertStringNotContainsString('^JKSE.JK', $source);
        $this->assertStringNotContainsString('IHSG.JK', $source);
    }

}
