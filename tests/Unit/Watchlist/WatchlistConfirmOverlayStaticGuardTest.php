<?php

use PHPUnit\Framework\TestCase;

class WatchlistConfirmOverlayStaticGuardTest extends TestCase
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

    public function test_watchlist_confirm_overlay_service_exists_and_consumes_immutable_plan_binding(): void
    {
        $source = $this->readProjectFile('app/Application/Watchlist/Services/WatchlistConfirmOverlayService.php');

        $this->assertStringContainsString('class WatchlistConfirmOverlayService', $source);
        $this->assertStringContainsString('WatchlistPlanGroupingService', $source);
        $this->assertStringContainsString('WatchlistRecommendationService', $source);
        $this->assertStringContainsString('groupForTradeDate', $source);
        $this->assertStringContainsString('recommendFromPlanOutput', $source);
        $this->assertStringContainsString('confirmFromPlanAndRecommendationOutput', $source);
        $this->assertStringContainsString('candidate PLAN membership', $source);
    }

    public function test_watchlist_confirm_overlay_service_does_not_consume_raw_builders_or_market_data_directly(): void
    {
        $source = $this->readProjectFile('app/Application/Watchlist/Services/WatchlistConfirmOverlayService.php');

        $this->assertStringNotContainsString('WatchlistScoringService', $source);
        $this->assertStringNotContainsString('WatchlistCandidateUniverseService', $source);
        $this->assertStringNotContainsString('WatchlistMarketDataConsumerReadService', $source);
        $this->assertStringNotContainsString('MarketDataWatchlistReadService', $source);
    }

    public function test_watchlist_confirm_overlay_service_does_not_use_raw_market_data_tables_or_latest_shortcuts(): void
    {
        $source = $this->readProjectFile('app/Application/Watchlist/Services/WatchlistConfirmOverlayService.php');

        $this->assertDoesNotMatchRegularExpression('/DB::table\s*\(/i', $source);
        $this->assertDoesNotMatchRegularExpression('/\b(eod_bars|eod_indicators|eod_eligibility|eod_current_publication_pointer)\b/i', $source);
        $this->assertDoesNotMatchRegularExpression('/\bMAX\s*\(\s*trade_date\s*\)/i', $source);
        $this->assertDoesNotMatchRegularExpression('/->\s*max\s*\(\s*[\'\"]trade_date[\'\"]\s*\)/i', $source);
        $this->assertDoesNotMatchRegularExpression('/->\s*latest\s*\(/i', $source);
        $this->assertDoesNotMatchRegularExpression('/->\s*orderByDesc\s*\(\s*[\'\"]trade_date[\'\"]\s*\)/i', $source);
        $this->assertDoesNotMatchRegularExpression('/->\s*orderBy\s*\(\s*[\'\"]trade_date[\'\"]\s*,\s*[\'\"]desc[\'\"]\s*\)/i', $source);
    }

    public function test_watchlist_confirm_overlay_service_preserves_boundary_without_runtime_or_allocation_logic(): void
    {
        $source = $this->readProjectFile('app/Application/Watchlist/Services/WatchlistConfirmOverlayService.php');

        foreach ([
            'portfolio_allocation',
            'capital_allocation',
            'suggested_lots',
            'order_instruction',
            'execution_action',
            'broker_instruction',
            'entry_price_instruction',
            'take_profit_instruction',
            'stop_loss_instruction',
            'buy_signal',
            'sell_signal',
            'backtest_metric',
            'Controller',
            'Route::',
            'Artisan::',
            'Command extends',
        ] as $forbidden) {
            $this->assertStringNotContainsString($forbidden, $source, $forbidden);
        }
    }

    public function test_watchlist_confirm_overlay_contract_contains_overlay_and_immutability_terms(): void
    {
        $source = $this->readProjectFile('app/Application/Watchlist/Services/WatchlistConfirmOverlayService.php');

        foreach ([
            'WATCHLIST_CONFIRM_OVERLAY_READY',
            'WATCHLIST_CONFIRM_SOURCE_PLAN_NOT_READY',
            'WATCHLIST_CONFIRM_SOURCE_RECOMMENDATION_NOT_READY',
            'WS_CONFIRM_ELIGIBLE_RECOMMENDED',
            'WS_CONFIRM_ELIGIBLE_NON_RECOMMENDED',
            'WS_CONFIRM_APPLIED',
            'WS_CONFIRM_NOT_APPLIED',
            'WS_CONFIRM_REJECTED_UNKNOWN_CANDIDATE',
            'WS_CONFIRM_NO_DATA',
            'recommended_plan_candidate_can_confirm',
            'non_recommended_plan_candidate_can_confirm',
            'confirm_does_not_create_recommendation_membership',
            'confirm_does_not_remove_recommendation_membership',
            'no_recommendation_mutation',
            'no_score_mutation',
            'no_rank_mutation',
            'no_label_mutation',
            'no_hash_mutation',
        ] as $needle) {
            $this->assertStringContainsString($needle, $source);
        }
    }

    public function test_confirm_reason_codes_exist_in_weekly_swing_owner_docs_or_support_seed(): void
    {
        $owner = $this->readProjectFile('docs/watchlist/system/policies/weekly_swing/07_WS_REASON_CODES_AND_HASH.md');
        $confirm = $this->readProjectFile('docs/watchlist/system/policies/weekly_swing/10_WS_CONFIRM_OVERLAY.md');
        $recommendation = $this->readProjectFile('docs/watchlist/system/policies/weekly_swing/25_WS_RECOMMENDATION_REASON_CODES_AND_TESTS.md');
        $seed = $this->readProjectFile('docs/watchlist/system/policies/weekly_swing/db/REASON_CODES_SEED.sql');
        $combined = $owner.$confirm.$recommendation.$seed;

        foreach ([
            'WS_CONFIRM_ELIGIBLE_RECOMMENDED',
            'WS_CONFIRM_ELIGIBLE_NON_RECOMMENDED',
            'WS_CONFIRM_APPLIED',
            'WS_CONFIRM_NOT_APPLIED',
            'WS_CONFIRM_REJECTED_UNKNOWN_CANDIDATE',
            'WS_CONFIRM_REJECTED_NOT_PLAN_CANDIDATE',
            'WS_CONFIRM_NO_DATA',
        ] as $reason) {
            $this->assertStringContainsString($reason, $combined);
        }
    }

    public function test_lumen_audit_docs_are_synchronized_for_confirm_overlay_session(): void
    {
        $status = $this->readProjectFile('docs/watchlist/audit/LUMEN_IMPLEMENTATION_STATUS.md');
        $tracker = $this->readProjectFile('docs/watchlist/audit/LUMEN_CONTRACT_TRACKER.md');
        $combined = $status.$tracker;

        $this->assertStringContainsString('CONFIRM OVERLAY FOUNDATION EXECUTION SESSION', $combined);
        $this->assertStringContainsString('WatchlistConfirmOverlayService.php', $combined);
        $this->assertStringContainsString('WatchlistConfirmOverlayServiceTest.php', $combined);
        $this->assertStringContainsString('WatchlistConfirmOverlayStaticGuardTest.php', $combined);
        $this->assertStringContainsString('WL-CONTRACT-017', $combined);
        $this->assertStringContainsString('WL-CONTRACT-018', $combined);
        $this->assertStringContainsString('WL-CONTRACT-019', $combined);
        $this->assertStringContainsString('WL-CONTRACT-008', $combined);
        $this->assertStringContainsString('WL-CONTRACT-014', $combined);
        $this->assertStringContainsString('WL-CONTRACT-015', $combined);
        $this->assertStringContainsString('Phase 6', $combined);
        $this->assertStringContainsString('Confirm Overlay Foundation', $combined);
        $this->assertStringContainsString('Watchlist Production Ready', $combined);
        $this->assertStringContainsString('NO', $combined);
    }
}
