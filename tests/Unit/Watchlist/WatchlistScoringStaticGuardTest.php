<?php

use PHPUnit\Framework\TestCase;

class WatchlistScoringStaticGuardTest extends TestCase
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

    public function test_watchlist_scoring_service_exists_and_consumes_candidate_universe(): void
    {
        $source = $this->readProjectFile('app/Application/Watchlist/Services/WatchlistScoringService.php');

        $this->assertStringContainsString('class WatchlistScoringService', $source);
        $this->assertStringContainsString('WatchlistCandidateUniverseService', $source);
        $this->assertStringContainsString('buildCandidateUniverseForTradeDate', $source);
        $this->assertStringContainsString('WatchlistScoringService', $source);
        $this->assertStringContainsString('WatchlistCandidateUniverseService', $source);
    }

    public function test_watchlist_scoring_service_does_not_consume_market_data_read_model_or_raw_tables(): void
    {
        $source = $this->readProjectFile('app/Application/Watchlist/Services/WatchlistScoringService.php');

        $this->assertStringNotContainsString('WatchlistMarketDataConsumerReadService', $source);
        $this->assertStringNotContainsString('MarketDataWatchlistReadService', $source);
        $this->assertDoesNotMatchRegularExpression('/DB::table\s*\(/i', $source);
        $this->assertDoesNotMatchRegularExpression('/\b(eod_bars|eod_indicators|eod_eligibility|eod_current_publication_pointer)\b/i', $source);
        $this->assertDoesNotMatchRegularExpression('/\bMAX\s*\(\s*trade_date\s*\)/i', $source);
        $this->assertDoesNotMatchRegularExpression('/->\s*max\s*\(\s*[\'\"]trade_date[\'\"]\s*\)/i', $source);
        $this->assertDoesNotMatchRegularExpression('/->\s*latest\s*\(/i', $source);
        $this->assertDoesNotMatchRegularExpression('/->\s*orderByDesc\s*\(\s*[\'\"]trade_date[\'\"]\s*\)/i', $source);
        $this->assertDoesNotMatchRegularExpression('/->\s*orderBy\s*\(\s*[\'\"]trade_date[\'\"]\s*,\s*[\'\"]desc[\'\"]\s*\)/i', $source);
    }

    public function test_watchlist_scoring_service_does_not_create_recommendation_confirm_order_or_backtest_fields(): void
    {
        $source = $this->readProjectFile('app/Application/Watchlist/Services/WatchlistScoringService.php');

        foreach (['recommendation_label', 'confirm_state', 'portfolio_allocation', 'order_instruction', 'execution_action', 'buy_signal', 'sell_signal', 'backtest_metric'] as $forbidden) {
            $this->assertStringNotContainsString($forbidden, $source);
        }
    }

    public function test_watchlist_scoring_reason_codes_are_defined_in_weekly_swing_owner_docs_or_seed(): void
    {
        $owner = $this->readProjectFile('docs/watchlist/system/policies/weekly_swing/07_WS_REASON_CODES_AND_HASH.md');
        $seed = $this->readProjectFile('docs/watchlist/system/policies/weekly_swing/db/REASON_CODES_SEED.sql');
        $combined = $owner.$seed;

        foreach ([
            'WS_MOM_STRONG',
            'WS_MOM_WEAK',
            'WS_BO_NEAR',
            'WS_BO_BREAK',
            'WS_BO_FAR',
            'WS_BO_EXT',
            'WS_LIQ_STRONG',
            'WS_LIQ_BORDER',
            'WS_VOLR_FAIL',
            'WS_RISK_IDEAL',
            'WS_RISK_HIGH',
            'WS_RISK_LOW',
        ] as $reason) {
            $this->assertStringContainsString($reason, $combined);
        }
    }

    public function test_watchlist_scoring_contract_and_sort_keys_are_static_and_deterministic(): void
    {
        $source = $this->readProjectFile('app/Application/Watchlist/Services/WatchlistScoringService.php');

        foreach ([
            'WEIGHTED_MEAN',
            'score_total_desc',
            'score_breakout_desc',
            'score_momentum_desc',
            'dv20_idr_desc',
            'atr14_pct_asc',
            'ticker_id_asc',
            'momentum' => 'momentum',
            'breakout' => 'breakout',
            'volume' => 'volume',
            'risk' => 'risk',
        ] as $needle) {
            $this->assertStringContainsString($needle, $source);
        }

        $this->assertDoesNotMatchRegularExpression('/random_int\s*\(|mt_rand\s*\(|shuffle\s*\(/i', $source);
        $this->assertStringNotContainsString('microtime(', $source);
    }

    public function test_watchlist_scoring_docs_are_synchronized_for_scoring_session(): void
    {
        $status = $this->readProjectFile('docs/watchlist/audit/LUMEN_IMPLEMENTATION_STATUS.md');
        $tracker = $this->readProjectFile('docs/watchlist/audit/LUMEN_CONTRACT_TRACKER.md');
        $combined = $status.$tracker;

        $this->assertStringContainsString('WATCHLIST — SCORING ENGINE FOUNDATION EXECUTION SESSION', $combined);
        $this->assertStringContainsString('WatchlistScoringService.php', $combined);
        $this->assertStringContainsString('WatchlistScoringServiceTest.php', $combined);
        $this->assertStringContainsString('WatchlistScoringStaticGuardTest.php', $combined);
        $this->assertStringContainsString('WL-CONTRACT-006', $combined);
        $this->assertStringContainsString('WL-CONTRACT-007', $combined);
        $this->assertStringContainsString('WL-CONTRACT-008', $combined);
        $this->assertStringContainsString('WL-CONTRACT-011', $combined);
        $this->assertStringContainsString('WL-CONTRACT-014', $combined);
        $this->assertStringContainsString('WL-CONTRACT-015', $combined);
        $this->assertStringContainsString('Phase 3 — Scoring Engine Foundation', $combined);
        $this->assertStringContainsString('Watchlist Production Ready', $combined);
        $this->assertStringContainsString('NO', $combined);
    }
}
