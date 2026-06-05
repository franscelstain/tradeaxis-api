<?php

use PHPUnit\Framework\TestCase;

class WatchlistCandidateUniverseStaticGuardTest extends TestCase
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

    public function test_candidate_universe_service_exists_and_consumes_read_model_only(): void
    {
        $source = $this->readProjectFile('app/Application/Watchlist/Services/WatchlistCandidateUniverseService.php');

        $this->assertStringContainsString('WatchlistMarketDataConsumerReadService', $source);
        $this->assertStringContainsString('getCandidateUniverseForTradeDate', $source);
        $this->assertStringContainsString('current_readable_publication_pointer', $source);
        $this->assertStringContainsString('forbids_raw_staging_latest_max_date_bypass', $source);
        $this->assertStringContainsString('does_not_score', $source);
        $this->assertStringContainsString('does_not_recommend', $source);
        $this->assertStringContainsString('does_not_backtest', $source);
    }

    public function test_watchlist_application_code_still_forbids_raw_market_data_and_latest_shortcuts(): void
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

    public function test_candidate_universe_contains_canonical_liquidity_risk_and_volume_reason_codes(): void
    {
        $source = $this->readProjectFile('app/Application/Watchlist/Services/WatchlistCandidateUniverseService.php');

        foreach ([
            'WS_DATA_MISSING',
            'WS_LIQ_FAIL',
            'WS_ATR_LOW',
            'WS_ATR_HIGH',
            'WS_VOLR_FAIL',
            'WS_LIQ_STRONG',
            'WS_LIQ_BORDER',
            'WS_RISK_IDEAL',
            'WS_RISK_HIGH',
            'WS_RISK_LOW',
        ] as $needle) {
            $this->assertStringContainsString($needle, $source);
        }
    }

    public function test_candidate_universe_default_paramset_matches_weekly_swing_policy_baseline(): void
    {
        $source = $this->readProjectFile('app/Application/Watchlist/Services/WatchlistCandidateUniverseService.php');

        $this->assertStringContainsString("'min_dv20_idr' => 1000000000.0", $source);
        $this->assertStringContainsString("'dv20_strong_idr' => 5000000000.0", $source);
        $this->assertStringContainsString("'min_vol_ratio' => 1.2", $source);
        $this->assertStringContainsString("'min_atr14_pct' => 0.02", $source);
        $this->assertStringContainsString("'max_atr14_pct' => 0.12", $source);
        $this->assertStringContainsString("'atr_ideal_low' => 0.035", $source);
        $this->assertStringContainsString("'atr_ideal_high' => 0.075", $source);
        $this->assertStringContainsString('must be a fraction between 0 and 1, not percent-points', $source);
    }

    public function test_watchlist_audit_docs_are_synchronized_for_candidate_universe_session(): void
    {
        $status = $this->readProjectFile('docs/watchlist/audit/LUMEN_IMPLEMENTATION_STATUS.md');
        $tracker = $this->readProjectFile('docs/watchlist/audit/LUMEN_CONTRACT_TRACKER.md');
        $combined = $status.$tracker;

        $this->assertStringContainsString('WATCHLIST — CANDIDATE UNIVERSE + LIQUIDITY/RISK FILTER EXECUTION SESSION', $combined);
        $this->assertStringContainsString('WatchlistCandidateUniverseService.php', $combined);
        $this->assertStringContainsString('WatchlistCandidateUniverseServiceTest.php', $combined);
        $this->assertStringContainsString('WatchlistCandidateUniverseStaticGuardTest.php', $combined);
        $this->assertStringContainsString('WL-CONTRACT-011', $combined);
        $this->assertStringContainsString('WS_LIQ_FAIL', $combined);
        $this->assertStringContainsString('WS_ATR_HIGH', $combined);
        $this->assertStringContainsString('WS_VOLR_FAIL', $combined);
    }

    private function watchlistApplicationFiles(): array
    {
        return [
            'app/Application/Watchlist/Services/WatchlistMarketDataConsumerReadService.php',
            'app/Application/Watchlist/Services/WatchlistCandidateUniverseService.php',
        ];
    }
}
