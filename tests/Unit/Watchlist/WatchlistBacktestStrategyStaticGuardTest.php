<?php

use PHPUnit\Framework\TestCase;

class WatchlistBacktestStrategyStaticGuardTest extends TestCase
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

    public function test_backtest_strategy_service_exists_and_consumes_only_existing_watchlist_outputs(): void
    {
        $source = $this->readProjectFile('app/Application/Watchlist/Services/WatchlistBacktestStrategyService.php');

        $this->assertStringContainsString('class WatchlistBacktestStrategyService', $source);
        $this->assertStringContainsString('WatchlistPlanGroupingService', $source);
        $this->assertStringContainsString('WatchlistRecommendationService', $source);
        $this->assertStringContainsString('WatchlistConfirmOverlayService', $source);
        $this->assertStringContainsString('groupForTradeDate', $source);
        $this->assertStringContainsString('recommendFromPlanOutput', $source);
        $this->assertStringContainsString('confirmFromPlanAndRecommendationOutput', $source);
        $this->assertStringContainsString('backtestForReplayWindow', $source);
    }

    public function test_backtest_strategy_service_does_not_consume_raw_builders_or_market_data_directly(): void
    {
        $source = $this->readProjectFile('app/Application/Watchlist/Services/WatchlistBacktestStrategyService.php');

        $this->assertStringNotContainsString('WatchlistScoringService', $source);
        $this->assertStringNotContainsString('WatchlistCandidateUniverseService', $source);
        $this->assertStringNotContainsString('WatchlistMarketDataConsumerReadService', $source);
        $this->assertStringNotContainsString('MarketDataWatchlistReadService', $source);
    }

    public function test_backtest_strategy_service_does_not_use_raw_market_data_tables_or_latest_shortcuts(): void
    {
        $source = $this->readProjectFile('app/Application/Watchlist/Services/WatchlistBacktestStrategyService.php');

        $this->assertDoesNotMatchRegularExpression('/DB::table\s*\(/i', $source);
        $this->assertDoesNotMatchRegularExpression('/\b(eod_bars|eod_indicators|eod_eligibility|eod_current_publication_pointer)\b/i', $source);
        $this->assertDoesNotMatchRegularExpression('/\bMAX\s*\(\s*trade_date\s*\)/i', $source);
        $this->assertDoesNotMatchRegularExpression('/->\s*max\s*\(\s*[\'\"]trade_date[\'\"]\s*\)/i', $source);
        $this->assertDoesNotMatchRegularExpression('/->\s*latest\s*\(/i', $source);
        $this->assertDoesNotMatchRegularExpression('/->\s*orderByDesc\s*\(\s*[\'\"]trade_date[\'\"]\s*\)/i', $source);
        $this->assertDoesNotMatchRegularExpression('/->\s*orderBy\s*\(\s*[\'\"]trade_date[\'\"]\s*,\s*[\'\"]desc[\'\"]\s*\)/i', $source);
    }

    public function test_backtest_strategy_service_preserves_no_allocation_order_runtime_surface_boundary(): void
    {
        $source = $this->readProjectFile('app/Application/Watchlist/Services/WatchlistBacktestStrategyService.php');

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
            'Controller',
            'Route::',
            'Artisan::',
            'Command extends',
        ] as $forbidden) {
            $this->assertStringNotContainsString($forbidden, $source, $forbidden);
        }
    }

    public function test_backtest_strategy_contract_contains_foundation_no_lookahead_and_artifact_terms(): void
    {
        $source = $this->readProjectFile('app/Application/Watchlist/Services/WatchlistBacktestStrategyService.php');

        foreach ([
            'WATCHLIST_BACKTEST_FOUNDATION_READY',
            'WATCHLIST_BACKTEST_EMPTY_RECOMMENDATION',
            'WATCHLIST_BACKTEST_NO_LOOKAHEAD_VIOLATION',
            'PLAN_RECOMMENDATION_REPLAY_FOUNDATION',
            'EXPLICIT_TRADE_DATE_WINDOW',
            'FOUNDATION_REPLAY_ONLY',
            'WS_BT_EVAL_METRICS_MISSING',
            'WS_BT_ARTIFACT_MISSING',
            'WS_REC_EMPTY_SET',
            'no_lookahead',
            'deterministic_replay',
            'publication_aware_replay',
            'confirm_overlay_diagnostic_only',
            'watchlist_bt_eval',
            'watchlist_bt_picks_ws',
            'watchlist_bt_universe_ws',
            'watchlist_bt_oos_eval_ws',
        ] as $needle) {
            $this->assertStringContainsString($needle, $source);
        }
    }

    public function test_backtest_reason_codes_exist_in_weekly_swing_owner_docs_or_support_seed(): void
    {
        $owner = $this->readProjectFile('docs/watchlist/system/policies/weekly_swing/07_WS_REASON_CODES_AND_HASH.md');
        $schema = $this->readProjectFile('docs/watchlist/system/policies/weekly_swing/12_WS_BACKTEST_SCHEMA_AND_CALIBRATION.md');
        $metrics = $this->readProjectFile('docs/watchlist/system/policies/weekly_swing/16_WS_EVAL_METRICS_SUFFICIENCY_LOCKED.md');
        $manifest = $this->readProjectFile('docs/watchlist/system/policies/weekly_swing/18_WS_BACKTEST_ARTIFACT_MANIFEST_LOCKED.md');
        $seed = $this->readProjectFile('docs/watchlist/system/policies/weekly_swing/db/REASON_CODES_SEED.sql');
        $combined = $owner.$schema.$metrics.$manifest.$seed;

        foreach ([
            'WS_BT_EVAL_METRICS_MISSING',
            'WS_BT_ARTIFACT_MISSING',
            'WS_BT_OOS_PROOF_MISSING',
            'WS_REC_EMPTY_SET',
            'WS_CONFIRM_APPLIED',
        ] as $reason) {
            $this->assertStringContainsString($reason, $combined);
        }
    }

    public function test_lumen_audit_docs_are_synchronized_for_backtest_strategy_session(): void
    {
        $status = $this->readProjectFile('docs/watchlist/audit/LUMEN_IMPLEMENTATION_STATUS.md');
        $tracker = $this->readProjectFile('docs/watchlist/audit/LUMEN_CONTRACT_TRACKER.md');
        $combined = $status.$tracker;

        $this->assertStringContainsString('BACKTEST STRATEGY ENGINE FOUNDATION EXECUTION SESSION', $combined);
        $this->assertStringContainsString('WatchlistBacktestStrategyService.php', $combined);
        $this->assertStringContainsString('WatchlistBacktestStrategyServiceTest.php', $combined);
        $this->assertStringContainsString('WatchlistBacktestStrategyStaticGuardTest.php', $combined);
        $this->assertStringContainsString('WL-CONTRACT-009', $combined);
        $this->assertStringContainsString('WL-CONTRACT-010', $combined);
        $this->assertStringContainsString('WL-CONTRACT-013', $combined);
        $this->assertStringContainsString('WL-CONTRACT-008', $combined);
        $this->assertStringContainsString('WL-CONTRACT-014', $combined);
        $this->assertStringContainsString('WL-CONTRACT-015', $combined);
        $this->assertStringContainsString('Phase 7', $combined);
        $this->assertStringContainsString('Backtest Strategy Engine', $combined);
        $this->assertStringContainsString('Watchlist Production Ready', $combined);
        $this->assertStringContainsString('NO', $combined);
    }
}
