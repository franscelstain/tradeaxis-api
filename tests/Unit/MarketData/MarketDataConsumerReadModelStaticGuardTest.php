<?php

use PHPUnit\Framework\TestCase;

class MarketDataConsumerReadModelStaticGuardTest extends TestCase
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

    public function test_consumer_read_model_classes_use_official_current_readable_publication_gateway(): void
    {
        foreach ($this->gatewayOwnerFiles() as $file) {
            $source = $this->read($file);

            $this->assertStringContainsString('resolveCurrentReadablePublicationForTradeDate', $source, $file);
        }

        $benchmarkService = $this->read('app/Application/MarketData/Services/MarketBenchmarkReadService.php');
        $this->assertStringContainsString('readinessForTradeDate', $benchmarkService);

        foreach ($this->publicationScopedRepositoryFiles() as $file) {
            $source = $this->read($file);

            $this->assertStringContainsString('publication_id', $source, $file);
            $this->assertStringContainsString('trade_date', $source, $file);
        }
    }

    public function test_consumer_read_model_classes_forbid_latest_raw_staging_shortcuts(): void
    {
        foreach ($this->readModelFiles() as $file) {
            $source = $this->read($file);

            $this->assertDoesNotMatchRegularExpression('/\bMAX\s*\(\s*trade_date\s*\)/i', $source, $file);
            $this->assertDoesNotMatchRegularExpression('/->\s*max\s*\(\s*[\'"]trade_date[\'"]\s*\)/i', $source, $file);
            $this->assertDoesNotMatchRegularExpression('/->\s*latest\s*\(\s*[\'"]trade_date[\'"]\s*\)/i', $source, $file);
            $this->assertDoesNotMatchRegularExpression('/->\s*orderByDesc\s*\(\s*[\'"]trade_date[\'"]\s*\)/i', $source, $file);
            $this->assertDoesNotMatchRegularExpression('/->\s*orderBy\s*\(\s*[\'"]trade_date[\'"]\s*,\s*[\'"]desc[\'"]\s*\)/i', $source, $file);
            $this->assertDoesNotMatchRegularExpression('/DB::table\s*\(\s*[\'"][^\'"]*(raw|staging)[^\'"]*[\'"]\s*\)/i', $source, $file);
            $this->assertStringNotContainsString('findLatestReadablePublicationBefore', $source, $file);
            $this->assertStringNotContainsString('resolvePublicationForEvidenceAudit', $source, $file);
        }
    }

    public function test_readiness_service_is_reason_coded_and_fail_closed(): void
    {
        $source = $this->read('app/Application/MarketData/Services/MarketDataReadinessService.php');

        foreach ([
            'READABLE_PUBLICATION_RESOLVED',
            'NO_READABLE_PUBLICATION',
            'PUBLICATION_NOT_SEALED',
            'RUN_TERMINAL_STATUS_NOT_SUCCESS',
            'RUN_PUBLISHABILITY_NOT_READABLE',
            'RUN_COVERAGE_GATE_NOT_PASS',
            'RESOLVED_READABLE_CURRENT',
            'NOT_RESOLVED_READABLE_CURRENT',
        ] as $needle) {
            $this->assertStringContainsString($needle, $source);
        }
    }

    public function test_benchmark_read_model_keeps_ihsg_outside_equity_ticker_universe(): void
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

    public function test_docs_lock_consumer_read_model_scope_without_strategy_claims(): void
    {
        $inventory = $this->read('docs/market_data/audit/MARKET_DATA_CONSUMER_READ_MODEL_INVENTORY.md');
        $status = $this->read('docs/market_data/audit/LUMEN_IMPLEMENTATION_STATUS.md');
        $tracker = $this->read('docs/market_data/audit/LUMEN_CONTRACT_TRACKER.md');

        foreach ([
            'MARKET_DATA_CONSUMER_READ_MODEL_STATUS',
            'WATCHLIST_READ_SURFACE',
            'PORTFOLIO_PRICE_SURFACE',
            'BENCHMARK_READ_SURFACE',
            'READINESS_SURFACE',
            'current readable publication only',
            'no raw/staging/latest/MAX(date)',
            'MARKET_DATA_CONSUMER_READ_MODEL_CONTRACT',
        ] as $needle) {
            $this->assertStringContainsString($needle, $inventory.$status.$tracker);
        }

        foreach (['buy/sell decision implemented', 'watchlist ranking implemented', 'portfolio P/L final implemented'] as $forbidden) {
            $this->assertStringNotContainsString($forbidden, strtolower($inventory.$status.$tracker));
        }
    }

    private function readModelFiles(): array
    {
        return [
            'app/Application/MarketData/Services/MarketDataReadinessService.php',
            'app/Application/MarketData/Services/MarketDataWatchlistReadService.php',
            'app/Infrastructure/Persistence/MarketData/MarketDataWatchlistReadRepository.php',
            'app/Application/MarketData/Services/MarketDataPortfolioPriceService.php',
            'app/Infrastructure/Persistence/MarketData/MarketDataPortfolioPriceRepository.php',
            'app/Application/MarketData/Services/MarketBenchmarkReadService.php',
            'app/Infrastructure/Persistence/MarketData/MarketBenchmarkReadRepository.php',
        ];
    }

    private function gatewayOwnerFiles(): array
    {
        return [
            'app/Application/MarketData/Services/MarketDataReadinessService.php',
            'app/Application/MarketData/Services/MarketDataWatchlistReadService.php',
            'app/Application/MarketData/Services/MarketDataPortfolioPriceService.php',
            'app/Infrastructure/Persistence/MarketData/MarketDataPortfolioPriceRepository.php',
        ];
    }

    private function publicationScopedRepositoryFiles(): array
    {
        return [
            'app/Infrastructure/Persistence/MarketData/MarketDataWatchlistReadRepository.php',
            'app/Infrastructure/Persistence/MarketData/MarketDataPortfolioPriceRepository.php',
        ];
    }
}
