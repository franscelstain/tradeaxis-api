<?php

use PHPUnit\Framework\TestCase;

class WatchlistMarketDataConsumerReadModelStaticGuardTest extends TestCase
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

    public function test_watchlist_market_data_consumer_read_service_exists_and_uses_market_data_gateway(): void
    {
        $source = $this->readProjectFile('app/Application/Watchlist/Services/WatchlistMarketDataConsumerReadService.php');

        $this->assertStringContainsString('MarketDataWatchlistReadService', $source);
        $this->assertStringContainsString('getWatchlistMarketDataForTradeDate', $source);
        $this->assertStringContainsString('current_readable_publication_pointer', $source);
        $this->assertStringContainsString('forbids_raw_staging_latest_max_date_bypass', $source);
        $this->assertStringContainsString('WATCHLIST_MARKET_DATA_READY', $source);
        $this->assertStringContainsString('WATCHLIST_MARKET_DATA_NO_VALID_CANDIDATES', $source);
    }

    public function test_watchlist_application_code_forbids_raw_market_data_and_latest_shortcuts(): void
    {
        foreach ($this->watchlistApplicationFiles() as $file) {
            $source = $this->readProjectFile($file);

            $this->assertDoesNotMatchRegularExpression('/DB::table\s*\(/i', $source, $file);
            $this->assertDoesNotMatchRegularExpression('/\b(eod_bars|eod_indicators|eod_eligibility|eod_current_publication_pointer)\b/i', $source, $file);
            $this->assertDoesNotMatchRegularExpression('/\bMAX\s*\(\s*trade_date\s*\)/i', $source, $file);
            $this->assertDoesNotMatchRegularExpression('/->\s*max\s*\(\s*[\'\"]trade_date[\'\"]\s*\)/i', $source, $file);
            $this->assertDoesNotMatchRegularExpression('/->\s*latest\s*\(/i', $source, $file);
            $this->assertDoesNotMatchRegularExpression('/->\s*orderByDesc\s*\(\s*[\'\"]trade_date[\'\"]\s*\)/i', $source, $file);
            $this->assertDoesNotMatchRegularExpression('/->\s*orderBy\s*\(\s*[\'\"]trade_date[\'\"]\s*,\s*[\'\"]desc[\'\"]\s*\)/i', $source, $file);
        }
    }

    public function test_required_indicator_and_eligibility_guards_are_present(): void
    {
        $source = $this->readProjectFile('app/Application/Watchlist/Services/WatchlistMarketDataConsumerReadService.php');

        foreach ([
            'dv20idr',
            'atr14_pct',
            'vol_ratio',
            'roc_20',
            'hh20',
            'ma20',
            'ma50',
            'close_to_hh20_pct',
            'close_vs_ma20_pct',
            'close_vs_ma50_pct',
            'ma20_slope_pct',
            'rs_20_vs_ihsg',
            'indicator_set_version',
            'WATCHLIST_ELIGIBILITY_NOT_ELIGIBLE',
            'WATCHLIST_INDICATOR_INVALID',
            'WATCHLIST_REQUIRED_FIELD_MISSING',
        ] as $needle) {
            $this->assertStringContainsString($needle, $source);
        }
    }

    public function test_market_data_watchlist_repository_is_publication_run_scoped_and_filters_invalid_indicators(): void
    {
        $source = $this->readProjectFile('app/Infrastructure/Persistence/MarketData/MarketDataWatchlistReadRepository.php');

        $this->assertStringContainsString("->where('elig.publication_id', \$publication->publication_id)", $source);
        $this->assertStringContainsString("->where('elig.run_id', \$publication->run_id)", $source);
        $this->assertStringContainsString("->where('ind.is_valid', 1)", $source);
        $this->assertStringContainsString("->whereNull('ind.invalid_reason_code')", $source);
        $this->assertStringContainsString("->whereNotNull('ind.indicator_set_version')", $source);
        $this->assertStringContainsString('REQUIRED_WATCHLIST_INDICATOR_COLUMNS', $source);
    }

    public function test_watchlist_audit_docs_are_synchronized_for_consumer_read_model_session(): void
    {
        $status = $this->readProjectFile('docs/watchlist/audit/LUMEN_IMPLEMENTATION_STATUS.md');
        $tracker = $this->readProjectFile('docs/watchlist/audit/LUMEN_CONTRACT_TRACKER.md');
        $combined = $status.$tracker;

        $this->assertStringContainsString('WATCHLIST — MARKET-DATA CONSUMER READ MODEL EXECUTION SESSION', $combined);
        $this->assertStringContainsString('WatchlistMarketDataConsumerReadService.php', $combined);
        $this->assertStringContainsString('WatchlistMarketDataConsumerReadServiceTest.php', $combined);
        $this->assertStringContainsString('WatchlistMarketDataConsumerReadModelStaticGuardTest.php', $combined);
        $this->assertStringContainsString('WL-CONTRACT-001', $combined);
        $this->assertStringContainsString('WL-CONTRACT-002', $combined);
        $this->assertStringContainsString('WL-CONTRACT-003', $combined);
        $this->assertStringContainsString('WL-CONTRACT-004', $combined);
        $this->assertStringContainsString('WL-CONTRACT-005', $combined);
    }

    private function watchlistApplicationFiles(): array
    {
        return [
            'app/Application/Watchlist/Services/WatchlistMarketDataConsumerReadService.php',
        ];
    }
}
