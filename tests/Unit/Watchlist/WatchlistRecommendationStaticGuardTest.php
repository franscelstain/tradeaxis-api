<?php

use PHPUnit\Framework\TestCase;

class WatchlistRecommendationStaticGuardTest extends TestCase
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

    public function test_watchlist_recommendation_service_exists_and_consumes_plan_grouping_service(): void
    {
        $source = $this->readProjectFile('app/Application/Watchlist/Services/WatchlistRecommendationService.php');

        $this->assertStringContainsString('class WatchlistRecommendationService', $source);
        $this->assertStringContainsString('WatchlistPlanGroupingService', $source);
        $this->assertStringContainsString('groupForTradeDate', $source);
        $this->assertStringContainsString('recommendFromPlanOutput', $source);
    }

    public function test_watchlist_recommendation_service_does_not_consume_upstream_plan_builders_or_market_data_directly(): void
    {
        $source = $this->readProjectFile('app/Application/Watchlist/Services/WatchlistRecommendationService.php');

        $this->assertStringNotContainsString('WatchlistScoringService', $source);
        $this->assertStringNotContainsString('WatchlistCandidateUniverseService', $source);
        $this->assertStringNotContainsString('WatchlistMarketDataConsumerReadService', $source);
        $this->assertStringNotContainsString('MarketDataWatchlistReadService', $source);
    }

    public function test_watchlist_recommendation_service_does_not_use_raw_market_data_tables_or_latest_shortcuts(): void
    {
        $source = $this->readProjectFile('app/Application/Watchlist/Services/WatchlistRecommendationService.php');

        $this->assertDoesNotMatchRegularExpression('/DB::table\s*\(/i', $source);
        $this->assertDoesNotMatchRegularExpression('/\b(eod_bars|eod_indicators|eod_eligibility|eod_current_publication_pointer)\b/i', $source);
        $this->assertDoesNotMatchRegularExpression('/\bMAX\s*\(\s*trade_date\s*\)/i', $source);
        $this->assertDoesNotMatchRegularExpression('/->\s*max\s*\(\s*[\'\"]trade_date[\'\"]\s*\)/i', $source);
        $this->assertDoesNotMatchRegularExpression('/->\s*latest\s*\(/i', $source);
        $this->assertDoesNotMatchRegularExpression('/->\s*orderByDesc\s*\(\s*[\'\"]trade_date[\'\"]\s*\)/i', $source);
        $this->assertDoesNotMatchRegularExpression('/->\s*orderBy\s*\(\s*[\'\"]trade_date[\'\"]\s*,\s*[\'\"]desc[\'\"]\s*\)/i', $source);
    }

    public function test_watchlist_recommendation_service_does_not_create_confirm_execution_portfolio_or_backtest_logic(): void
    {
        $source = $this->readProjectFile('app/Application/Watchlist/Services/WatchlistRecommendationService.php');

        foreach ([
            'confirm_state',
            'confirm_status',
            'intraday_snapshot',
            'order_instruction',
            'execution_action',
            'portfolio_allocation',
            'holding_state',
            'suggested_lots',
            'entry_price_instruction',
            'take_profit_instruction',
            'stop_loss_instruction',
            'buy_signal',
            'sell_signal',
            'backtest_metric',
        ] as $forbidden) {
            $this->assertStringNotContainsString($forbidden, $source);
        }
    }

    public function test_watchlist_recommendation_contract_is_plan_derived_and_deterministic(): void
    {
        $source = $this->readProjectFile('app/Application/Watchlist/Services/WatchlistRecommendationService.php');

        foreach ([
            'PLAN_DERIVED_DETERMINISTIC',
            'THRESHOLD_AND_CAP',
            'CAPITAL_FREE',
            'CAPITAL_AWARE',
            'TOP_PICKS',
            'SECONDARY',
            'recommendation_score_desc',
            'plan_rank_asc',
            'plan_group_priority_asc',
            'ticker_id_asc',
            'WS_REC_SELECTED',
            'WS_REC_NOT_SELECTED',
            'WS_REC_BORDERLINE',
            'WS_REC_EMPTY_SET',
            'WS_REC_RANK_OUTSIDE_DYNAMIC_TARGET',
            'WS_REC_CAPITAL_AWARE',
            'WS_REC_CAPITAL_INSUFFICIENT',
            'WS_REC_MIN_LOT_NOT_AFFORDABLE',
            "'available_without_confirm' => true",
            "'can_be_empty' => true",
        ] as $needle) {
            $this->assertStringContainsString($needle, $source);
        }

        $this->assertDoesNotMatchRegularExpression('/random_int\s*\(|mt_rand\s*\(|shuffle\s*\(/i', $source);
        $this->assertStringNotContainsString('microtime(', $source);
    }

    public function test_recommendation_reason_codes_exist_in_weekly_swing_owner_docs_or_support_seed(): void
    {
        $owner = $this->readProjectFile('docs/watchlist/system/policies/weekly_swing/07_WS_REASON_CODES_AND_HASH.md');
        $recommendation = $this->readProjectFile('docs/watchlist/system/policies/weekly_swing/25_WS_RECOMMENDATION_REASON_CODES_AND_TESTS.md');
        $seed = $this->readProjectFile('docs/watchlist/system/policies/weekly_swing/db/REASON_CODES_SEED.sql');
        $combined = $owner.$recommendation.$seed;

        foreach ([
            'WS_REC_SELECTED',
            'WS_REC_NOT_SELECTED',
            'WS_REC_BORDERLINE',
            'WS_REC_EMPTY_SET',
            'WS_REC_RANK_OUTSIDE_DYNAMIC_TARGET',
            'WS_REC_CAPITAL_AWARE',
            'WS_REC_CAPITAL_INSUFFICIENT',
            'WS_REC_MIN_LOT_NOT_AFFORDABLE',
        ] as $reason) {
            $this->assertStringContainsString($reason, $combined);
        }
    }

    public function test_lumen_audit_docs_are_synchronized_for_recommendation_session(): void
    {
        $status = $this->readProjectFile('docs/watchlist/audit/LUMEN_IMPLEMENTATION_STATUS.md');
        $tracker = $this->readProjectFile('docs/watchlist/audit/LUMEN_CONTRACT_TRACKER.md');
        $combined = $status.$tracker;

        $this->assertStringContainsString('FINAL RECOMMENDATION LAYER FOUNDATION EXECUTION SESSION', $combined);
        $this->assertStringContainsString('WatchlistRecommendationService.php', $combined);
        $this->assertStringContainsString('WatchlistRecommendationServiceTest.php', $combined);
        $this->assertStringContainsString('WatchlistRecommendationStaticGuardTest.php', $combined);
        $this->assertStringContainsString('WL-CONTRACT-006', $combined);
        $this->assertStringContainsString('WL-CONTRACT-007', $combined);
        $this->assertStringContainsString('WL-CONTRACT-008', $combined);
        $this->assertStringContainsString('WL-CONTRACT-014', $combined);
        $this->assertStringContainsString('WL-CONTRACT-015', $combined);
        $this->assertStringContainsString('WL-CONTRACT-016', $combined);
        $this->assertStringContainsString('WL-CONTRACT-017', $combined);
        $this->assertStringContainsString('WL-CONTRACT-018', $combined);
        $this->assertStringContainsString('WL-CONTRACT-019', $combined);
        $this->assertStringContainsString('Phase 5', $combined);
        $this->assertStringContainsString('Final Recommendation Layer Foundation', $combined);
        $this->assertStringContainsString('Watchlist Production Ready', $combined);
        $this->assertStringContainsString('NO', $combined);
    }
}
