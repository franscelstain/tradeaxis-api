<?php

use PHPUnit\Framework\TestCase;

class MarketDataOrdersOneToFourArchitectureTest extends TestCase
{
    private function root(): string
    {
        return dirname(__DIR__, 3);
    }

    public function test_application_services_depend_on_provider_neutral_source_ports(): void
    {
        foreach ([
            'app/Application/MarketData/Services/EodBarsIngestService.php',
            'app/Application/MarketData/Services/ApiBackfillRangeAcquisitionService.php',
            'app/Application/MarketData/Services/BenchmarkBarsIngestService.php',
            'app/Application/MarketData/Services/SectorIndexApiIngestService.php',
        ] as $relative) {
            $source = file_get_contents($this->root().'/'.$relative);
            $this->assertStringNotContainsString('use App\\Infrastructure\\MarketData\\Source\\PublicApiEodBarsAdapter', $source, $relative);
            $this->assertStringContainsString('ApiEodBarsSource', $source, $relative);
        }
    }

    public function test_market_data_application_has_no_watchlist_or_portfolio_named_artifact(): void
    {
        $violations = [];
        $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($this->root().'/app'));
        foreach ($iterator as $file) {
            if (! $file->isFile() || $file->getExtension() !== 'php') continue;
            $normalized = str_replace('\\', '/', $file->getPathname());
            if (strpos($normalized, '/MarketData/') === false) continue;
            if (preg_match('/(Watchlist|Portfolio|Ranking|Candidate|EntrySignal)/i', $file->getFilename())) {
                $violations[] = $normalized;
            }
        }

        $this->assertSame([], $violations);
    }

    public function test_read_product_does_not_apply_strategy_screening_or_current_active_state(): void
    {
        $source = file_get_contents($this->root().'/app/Infrastructure/Persistence/MarketData/MarketDataReadProductRepository.php');

        $this->assertStringNotContainsString("where('elig.eligible', 1)", $source);
        $this->assertStringNotContainsString('active_column', $source);
        $this->assertStringNotContainsString('REQUIRED_WATCHLIST', $source);
        $this->assertStringContainsString("'data_usable'", $source);
    }

    public function test_yahoo_capability_boundary_is_executable_and_adjusted_close_has_no_close_fallback(): void
    {
        $source = file_get_contents($this->root().'/app/Infrastructure/MarketData/Source/PublicApiEodBarsAdapter.php');

        $this->assertStringContainsString('provides_actual_traded_value', $source);
        $this->assertStringContainsString('provides_official_board_or_trading_status', $source);
        $this->assertStringContainsString("'adj_close' => \$adjclose[\$position] ?? null", $source);
        $this->assertStringNotContainsString("\$adjclose[\$position] ?? (\$quote['close'][\$position]", $source);
    }
}
