<?php

use PHPUnit\Framework\TestCase;

class WatchlistPlanGroupingStaticGuardTest extends TestCase
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

    public function test_watchlist_plan_grouping_service_exists_and_consumes_scoring_service(): void
    {
        $source = $this->readProjectFile('app/Application/Watchlist/Services/WatchlistPlanGroupingService.php');

        $this->assertStringContainsString('class WatchlistPlanGroupingService', $source);
        $this->assertStringContainsString('WatchlistScoringService', $source);
        $this->assertStringContainsString('scoreForTradeDate', $source);
        $this->assertStringContainsString('groupScoredOutput', $source);
    }

    public function test_watchlist_plan_grouping_service_does_not_consume_candidate_universe_or_market_data_read_model_directly(): void
    {
        $source = $this->readProjectFile('app/Application/Watchlist/Services/WatchlistPlanGroupingService.php');

        $this->assertStringNotContainsString('WatchlistCandidateUniverseService', $source);
        $this->assertStringNotContainsString('WatchlistMarketDataConsumerReadService', $source);
        $this->assertStringNotContainsString('MarketDataWatchlistReadService', $source);
    }

    public function test_watchlist_plan_grouping_service_does_not_use_raw_market_data_tables_or_latest_shortcuts(): void
    {
        $source = $this->readProjectFile('app/Application/Watchlist/Services/WatchlistPlanGroupingService.php');

        $this->assertDoesNotMatchRegularExpression('/DB::table\s*\(/i', $source);
        $this->assertDoesNotMatchRegularExpression('/\b(eod_bars|eod_indicators|eod_eligibility|eod_current_publication_pointer)\b/i', $source);
        $this->assertDoesNotMatchRegularExpression('/\bMAX\s*\(\s*trade_date\s*\)/i', $source);
        $this->assertDoesNotMatchRegularExpression('/->\s*max\s*\(\s*[\'\"]trade_date[\'\"]\s*\)/i', $source);
        $this->assertDoesNotMatchRegularExpression('/->\s*latest\s*\(/i', $source);
        $this->assertDoesNotMatchRegularExpression('/->\s*orderByDesc\s*\(\s*[\'\"]trade_date[\'\"]\s*\)/i', $source);
        $this->assertDoesNotMatchRegularExpression('/->\s*orderBy\s*\(\s*[\'\"]trade_date[\'\"]\s*,\s*[\'\"]desc[\'\"]\s*\)/i', $source);
    }

    public function test_watchlist_plan_grouping_service_does_not_create_recommendation_confirm_execution_or_backtest_logic(): void
    {
        $source = $this->readProjectFile('app/Application/Watchlist/Services/WatchlistPlanGroupingService.php');

        foreach ([
            'recommendation_label',
            'recommended_flag',
            'recommendation_score',
            'capital_mode',
            'confirm_state',
            'entry_price_instruction',
            'take_profit_instruction',
            'stop_loss_instruction',
            'order_instruction',
            'execution_action',
            'backtest_metric',
            'buy_signal',
            'sell_signal',
        ] as $forbidden) {
            $this->assertStringNotContainsString($forbidden, $source);
        }
    }

    public function test_watchlist_plan_grouping_contract_is_static_and_deterministic(): void
    {
        $source = $this->readProjectFile('app/Application/Watchlist/Services/WatchlistPlanGroupingService.php');

        foreach ([
            'PLAN_GROUPING_DETERMINISTIC',
            'TOP_PICKS',
            'SECONDARY',
            'WATCH_ONLY',
            'AVOID',
            'WS_PLAN_TOP_PICK',
            'WS_PLAN_SECONDARY',
            'WS_PLAN_WATCH_ONLY',
            'WS_PLAN_AVOID_LOW_SCORE',
            'WS_PLAN_AVOID_EXCLUDED',
            'score_total_desc',
            'score_breakout_desc',
            'score_momentum_desc',
            'dv20_idr_desc',
            'atr14_pct_asc',
            'ticker_id_asc',
            "'dedupe_key' => 'ticker_id'",
            "'not_final_recommendation' => true",
        ] as $needle) {
            $this->assertStringContainsString($needle, $source);
        }

        $this->assertStringContainsString("'min_score_total' => 0.70", $source);
        $this->assertStringContainsString("'min_score_total' => 0.55", $source);
        $this->assertStringContainsString("'min_score_total' => 0.40", $source);
        $this->assertStringContainsString("'max_items' => 5", $source);
        $this->assertStringContainsString("'max_items' => 10", $source);
        $this->assertStringContainsString("'max_items' => 20", $source);
        $this->assertDoesNotMatchRegularExpression('/random_int\s*\(|mt_rand\s*\(|shuffle\s*\(/i', $source);
        $this->assertStringNotContainsString('microtime(', $source);
    }

    public function test_plan_group_reason_codes_exist_in_weekly_swing_owner_docs_or_support_seed(): void
    {
        $owner = $this->readProjectFile('docs/watchlist/system/policies/weekly_swing/07_WS_REASON_CODES_AND_HASH.md');
        $recommendation = $this->readProjectFile('docs/watchlist/system/policies/weekly_swing/25_WS_RECOMMENDATION_REASON_CODES_AND_TESTS.md');
        $seed = $this->readProjectFile('docs/watchlist/system/policies/weekly_swing/db/REASON_CODES_SEED.sql');
        $combined = $owner.$recommendation.$seed;

        foreach ([
            'WS_PLAN_TOP_PICK',
            'WS_PLAN_SECONDARY',
            'WS_PLAN_WATCH_ONLY',
            'WS_PLAN_AVOID_LOW_SCORE',
            'WS_PLAN_AVOID_EXCLUDED',
        ] as $reason) {
            $this->assertStringContainsString($reason, $combined);
        }
    }

    public function test_lumen_audit_docs_are_synchronized_for_plan_grouping_session(): void
    {
        $status = $this->readProjectFile('docs/watchlist/audit/LUMEN_IMPLEMENTATION_STATUS.md');
        $tracker = $this->readProjectFile('docs/watchlist/audit/LUMEN_CONTRACT_TRACKER.md');
        $combined = $status.$tracker;

        $this->assertStringContainsString('WATCHLIST — PLAN GROUPING + TOP_PICKS / SECONDARY SELECTION EXECUTION SESSION', $combined);
        $this->assertStringContainsString('WatchlistPlanGroupingService.php', $combined);
        $this->assertStringContainsString('WatchlistPlanGroupingServiceTest.php', $combined);
        $this->assertStringContainsString('WatchlistPlanGroupingStaticGuardTest.php', $combined);
        $this->assertStringContainsString('WL-CONTRACT-006', $combined);
        $this->assertStringContainsString('WL-CONTRACT-007', $combined);
        $this->assertStringContainsString('WL-CONTRACT-008', $combined);
        $this->assertStringContainsString('WL-CONTRACT-011', $combined);
        $this->assertStringContainsString('WL-CONTRACT-014', $combined);
        $this->assertStringContainsString('WL-CONTRACT-015', $combined);
        $this->assertStringContainsString('WL-CONTRACT-016', $combined);
        $this->assertStringContainsString('WL-CONTRACT-017', $combined);
        $this->assertStringContainsString('Phase 4 — PLAN Grouping + TOP_PICKS/SECONDARY', $combined);
        $this->assertStringContainsString('Watchlist Production Ready', $combined);
        $this->assertStringContainsString('NO', $combined);
    }
}
