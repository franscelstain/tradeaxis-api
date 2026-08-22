<?php

use PHPUnit\Framework\TestCase;

/**
 * MD-B01 — the numbered boundary invariants of `Domain_Boundary_Invariants_LOCKED.md`.
 *
 * Fourteen invariants are listed. Four state their prohibition with a modal and were required from
 * the start; the other ten state the same class of prohibition in the copula form — "X is not Y" —
 * and carried no proof obligation until `MD-B01-A014` promoted them. Nine of those ten are proven
 * here. The tenth, `MD-S020-R0172`, constrains how guards may be written and is proven in
 * `DownstreamConceptSurfaceBoundaryTest`, which owns the forbidden-terms machinery it governs.
 *
 * Each invariant is proven two-sided. Absence alone would be vacuous: a surface that never mentions
 * eligibility cannot be shown to keep eligibility away from ranking. So every rule names a positive
 * locator that must match — the upstream concept genuinely lives here — and a set of downstream
 * senses that must not. A rename that made both searches find nothing fails on the locator.
 *
 * Comments are stripped before scanning. A docblock explaining why a surface is *not* a signal is
 * the contract being honoured, and reading it as a violation is a mistake this stage has already
 * made four times.
 */
class BoundaryInvariantSemanticsTest extends TestCase
{
    private function root(): string
    {
        return dirname(__DIR__, 3);
    }

    private function stripPhpComments(string $source): string
    {
        $out = '';
        foreach (token_get_all($source) as $token) {
            if (is_array($token)) {
                if ($token[0] === T_COMMENT || $token[0] === T_DOC_COMMENT) {
                    continue;
                }
                $out .= $token[1];

                continue;
            }
            $out .= $token;
        }

        return $out;
    }

    private function read(string $relative): string
    {
        $path = $this->root().'/'.$relative;
        $body = @file_get_contents($path);
        if ($body === false) {
            $this->fail('Surface under proof is unreadable: '.$relative);
        }

        return $this->stripPhpComments((string) $body);
    }

    /**
     * rule id => [
     *   statement,
     *   surfaces      => relative paths that carry the upstream concept,
     *   locator       => regex proving the upstream concept is really on those surfaces,
     *   forbidden     => downstream sense => regex that must not appear,
     * ]
     *
     * @return array<string,array{statement:string,surfaces:array<int,string>,locator:string,forbidden:array<string,string>}>
     */
    private function invariants(): array
    {
        return [
            'MD-S020-R0160' => [
                'statement' => 'Market-data readiness is judged from data evidence, not watchlist outcomes.',
                'surfaces' => ['app/Application/MarketData/Services/MarketDataReadinessService.php'],
                'locator' => '/[\'"]is_ready[\'"]\s*=>/',
                'forbidden' => [
                    'watchlist outcome' => '/watchlist|ranked_picks|selection_result/i',
                    'strategy outcome' => '/win_rate|expectancy|drawdown|profit|pnl|equity_curve/i',
                    'signal outcome' => '/buy_signal|sell_signal|trade_signal|conviction/i',
                ],
            ],
            'MD-S020-R0162' => [
                'statement' => 'Upstream readability and data usability are not downstream desirability.',
                'surfaces' => [
                    'app/Infrastructure/Persistence/MarketData/MarketDataReadProductRepository.php',
                    'app/Application/MarketData/Services/EligibilityDecisionService.php',
                ],
                'locator' => '/data_usable|DATA_USABLE/',
                'forbidden' => [
                    'desirability' => '/desirab|attractive|preferred_instrument|preference_score/i',
                    'ranking' => '/(^|_|\$|\')(rank|ranking|ranked)(_|\b)/i',
                    'approval' => '/tradability_approv|alpha_approv|strategy_approv/i',
                ],
            ],
            'MD-S020-R0163' => [
                'statement' => 'Canonicalization and quarantine are not strategy filtering.',
                'surfaces' => [
                    'app/Application/MarketData/Services/EodBarsIngestService.php',
                ],
                'locator' => '/eod_invalid_bars|invalid_reason_code/',
                'forbidden' => [
                    'strategy filtering' => '/strategy_filter|screen_result|screener|watchlist/i',
                    'preference exclusion' => '/exclude_unattractive|low_conviction|reject_by_score/i',
                ],
            ],
            'MD-S020-R0164' => [
                'statement' => 'Indicator derivation is not signal generation.',
                'surfaces' => [
                    'app/Application/MarketData/Services/IndicatorVectorService.php',
                    'app/Application/MarketData/Services/EodIndicatorsComputeService.php',
                ],
                'locator' => '/formula_version|indicator_set_version/',
                'forbidden' => [
                    'signal generation' => '/buy_signal|sell_signal|trade_signal|entry_signal|exit_signal|generate_signal/i',
                    'trade instruction' => '/entry_price|exit_price|stop_loss|take_profit|position_size/i',
                ],
            ],
            'MD-S020-R0165' => [
                'statement' => 'Eligibility is not ranking, selection, tradability approval, or alpha approval.',
                'surfaces' => [
                    'app/Application/MarketData/Services/EligibilityDecisionService.php',
                    'app/Application/MarketData/Services/EodEligibilityBuildService.php',
                ],
                'locator' => '/[\'"]eligible[\'"]\s*=>/',
                'forbidden' => [
                    'ranking' => '/(^|_|\$|\')(rank|ranking|ranked)(_|\b)/i',
                    'selection' => '/select_candidates|candidate_selection|shortlist/i',
                    'tradability approval' => '/tradab|trade_permitted|permitted_to_trade/i',
                    'alpha approval' => '/alpha_approv|alpha_score|(^|_)alpha(_|\b)/i',
                ],
            ],
            'MD-S020-R0166' => [
                'statement' => 'Liquidity facts are not candidate ordering.',
                'surfaces' => [
                    'app/Application/MarketData/Services/IndicatorVectorService.php',
                    'app/Infrastructure/Persistence/MarketData/MarketDataReadProductRepository.php',
                ],
                'locator' => '/adv20_traded_value_idr_actual|adv20_close_volume_proxy_idr|dv20_idr/',
                'forbidden' => [
                    'ordering by a liquidity measure' => '/orderBy\w*\(\s*[\'"][^\'"]*(adv20|dv20|volume|turnover|liquidity)[^\'"]*[\'"]/i',
                    'candidate ordering' => '/candidate_rank|liquidity_rank|ordering_score/i',
                ],
            ],
            'MD-S020-R0167' => [
                'statement' => 'Session snapshots are not real-time signal infrastructure.',
                'surfaces' => ['app/Application/MarketData/Services/SessionSnapshotService.php'],
                'locator' => '/md_session_snapshots|snapshot_slot/',
                'forbidden' => [
                    'streaming transport' => '/websocket|streaming|stream_subscribe|push_feed|sse_/i',
                    'tick infrastructure' => '/tick_by_tick|order_book|market_depth|level2|realtime_feed/i',
                    'signal emission' => '/emit_signal|buy_signal|sell_signal|trade_signal/i',
                ],
            ],
            'MD-S020-R0168' => [
                'statement' => 'Publication currentness and effective-date fallback are not trading recency recommendations.',
                'surfaces' => [
                    'app/Application/MarketData/Services/MarketDataReadinessService.php',
                    'app/Infrastructure/Persistence/MarketData/EodPublicationRepository.php',
                ],
                'locator' => '/is_current|trade_date_effective/',
                'forbidden' => [
                    'recommendation' => '/recommend|recommended_action|advice/i',
                    'recency signal' => '/recency_signal|freshness_signal|timing_signal|entry_timing/i',
                ],
            ],
            'MD-S020-R0169' => [
                'statement' => 'Market-data replay is not strategy backtesting or profitability proof.',
                'surfaces' => [
                    'app/Application/MarketData/Services/ReplayVerificationService.php',
                    'app/Application/MarketData/Services/ReplayBackfillService.php',
                ],
                'locator' => '/divergen|mismatch|hash/i',
                'forbidden' => [
                    'backtesting' => '/backtest|strategy_run|simulate_trades/i',
                    'profitability' => '/pnl|profit|return_pct|equity_curve|sharpe|win_rate|expectancy/i',
                ],
            ],
        ];
    }

    public function invariantProvider(): array
    {
        $out = [];
        foreach ((new self('x'))->invariants() as $ruleId => $spec) {
            $out[$ruleId] = [$ruleId, $spec];
        }

        return $out;
    }

    /**
     * @dataProvider invariantProvider
     *
     * @param  array{statement:string,surfaces:array<int,string>,locator:string,forbidden:array<string,string>}  $spec
     */
    public function test_the_upstream_concept_lives_here_and_carries_no_downstream_sense(string $ruleId, array $spec): void
    {
        $located = 0;
        $violations = [];

        foreach ($spec['surfaces'] as $relative) {
            $source = $this->read($relative);
            $this->assertNotSame('', trim($source), $ruleId.': '.$relative.' is empty after comment stripping');

            if (preg_match($spec['locator'], $source)) {
                $located++;
            }

            foreach ($spec['forbidden'] as $sense => $pattern) {
                if (preg_match($pattern, $source, $match)) {
                    $violations[] = $relative.' :: '.$sense.' :: '.trim($match[0]);
                }
            }
        }

        $this->assertGreaterThan(
            0,
            $located,
            $ruleId.': the upstream concept was not found on any named surface, so its absence of the downstream sense proves nothing'
        );
        $this->assertSame([], $violations, $ruleId.': '.$spec['statement']);
    }

    /**
     * Every forbidden pattern must be able to fire. A pattern too narrow to match anything would
     * report the surface clean and be indistinguishable from a surface that genuinely is.
     */
    public function test_every_forbidden_pattern_matches_the_sense_it_forbids(): void
    {
        $fixtures = [
            'watchlist outcome' => '$watchlist_membership = true;',
            'strategy outcome' => '$expectancy = 0.4;',
            'signal outcome' => '$buy_signal = true;',
            'desirability' => '$preference_score = 3;',
            'ranking' => '$rank = 1;',
            'approval' => '$tradability_approved = true;',
            'strategy filtering' => '$screener = new Screener();',
            'preference exclusion' => '$reject_by_score = true;',
            'signal generation' => '$entry_signal = 1;',
            'trade instruction' => '$stop_loss = 100;',
            'selection' => '$candidate_selection = [];',
            'tradability approval' => '$trade_permitted = true;',
            'alpha approval' => '$alpha_score = 2;',
            'ordering by a liquidity measure' => "orderBy('adv20_traded_value_idr_actual')",
            'candidate ordering' => '$candidate_rank = 2;',
            'streaming transport' => '$websocket = null;',
            'tick infrastructure' => '$order_book = [];',
            'signal emission' => '$emit_signal = true;',
            'recommendation' => '$recommended_action = "BUY";',
            'recency signal' => '$entry_timing = "NOW";',
            'backtesting' => '$backtest_result = [];',
            'profitability' => '$equity_curve = [];',
        ];

        $checked = 0;
        foreach ($this->invariants() as $ruleId => $spec) {
            foreach ($spec['forbidden'] as $sense => $pattern) {
                $this->assertArrayHasKey($sense, $fixtures, $ruleId.': '.$sense.' has no adequacy fixture');
                $this->assertSame(
                    1,
                    preg_match($pattern, $fixtures[$sense]),
                    $ruleId.': the '.$sense.' pattern cannot match the sense it forbids'
                );
                $checked++;
            }
        }

        $this->assertGreaterThan(20, $checked, 'the adequacy sweep must reach every forbidden pattern');
    }

    /**
     * The other direction, and the one `MD-S020-R0172` demands: a pattern must not fire on the
     * legitimate upstream identifiers this domain genuinely uses. `candidate_publication_id` is the
     * boundary contract's own example of a word that carries both senses.
     */
    public function test_no_forbidden_pattern_fires_on_a_legitimate_upstream_identifier(): void
    {
        $legitimate = [
            '$candidate_publication_id = 4;',
            '$expected_candidate_publication_id = 4;',
            '$coverage_policy = "STRICT";',
            '$expected_bar_policy = "EXPECTED";',
            '$target_date_count = 12;',
            '$publish_target = "CURRENT";',
            '$range_position_20_pct = 0.5;',
            '$trade_date = "2026-03-09";',
            '$adv20_close_volume_proxy_idr = 1000;',
            "orderBy('elig.ticker_id')",
            "orderBy('trade_date')",
            '$coverage_edge_cases = [];',
        ];

        $flagged = [];
        foreach ($this->invariants() as $ruleId => $spec) {
            foreach ($spec['forbidden'] as $sense => $pattern) {
                foreach ($legitimate as $identifier) {
                    if (preg_match($pattern, $identifier)) {
                        $flagged[] = $ruleId.' :: '.$sense.' :: '.$identifier;
                    }
                }
            }
        }

        $this->assertSame([], $flagged, 'a guard may not flag a legitimate upstream identifier on the word alone');
    }
}
