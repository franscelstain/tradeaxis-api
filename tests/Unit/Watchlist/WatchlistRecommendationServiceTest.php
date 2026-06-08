<?php

use App\Application\Watchlist\Services\WatchlistPlanGroupingService;
use App\Application\Watchlist\Services\WatchlistRecommendationService;

class WatchlistRecommendationServiceTest extends TestCase
{
    public function test_recommendation_creates_plan_derived_items_from_top_picks_and_secondary_only(): void
    {
        $service = new WatchlistRecommendationService($this->fakePlanGrouping($this->planOutput([
            'TOP_PICKS' => [
                $this->planItem(1, 'TOPA', 'TOP_PICKS', 0.92, 1),
            ],
            'SECONDARY' => [
                $this->planItem(2, 'SECA', 'SECONDARY', 0.72, 2),
            ],
            'WATCH_ONLY' => [
                $this->planItem(3, 'WCHA', 'WATCH_ONLY', 0.60, 3),
            ],
            'AVOID' => [
                $this->planItem(4, 'AVDA', 'AVOID', 0.30, null),
            ],
        ])));

        $result = $service->recommendForTradeDate('2026-05-19');

        $this->assertTrue($result['ready']);
        $this->assertSame('WATCHLIST_RECOMMENDATION_READY', $result['reason_code']);
        $this->assertSame(['meta', 'items', 'summary'], array_values(array_intersect(['meta', 'items', 'summary'], array_keys($result))));
        $this->assertSame(['TOPA', 'SECA'], array_column($result['items'], 'ticker'));
        $this->assertSame([true, true], array_column($result['items'], 'recommended_flag'));
        $this->assertSame(['TOPA', 'SECA'], $result['summary']['recommended_tickers']);
        $this->assertSame(2, $result['summary']['recommended_count']);
        $this->assertFalse($result['summary']['empty_recommendation_flag']);
    }

    public function test_recommendation_fails_closed_when_plan_source_is_not_ready(): void
    {
        $service = new WatchlistRecommendationService($this->fakePlanGrouping([
            'ready' => false,
            'is_ready' => false,
            'reason_code' => 'WATCHLIST_PLAN_GROUPING_SOURCE_NOT_READY',
            'trade_date' => '2026-05-19',
            'groups' => [
                'TOP_PICKS' => [],
                'SECONDARY' => [],
                'WATCH_ONLY' => [],
                'AVOID' => [],
            ],
        ]));

        $result = $service->recommendForTradeDate('2026-05-19');

        $this->assertFalse($result['ready']);
        $this->assertFalse($result['is_ready']);
        $this->assertSame('WATCHLIST_RECOMMENDATION_SOURCE_NOT_READY', $result['reason_code']);
        $this->assertSame([], $result['items']);
        $this->assertSame(0, $result['summary']['recommended_count']);
        $this->assertTrue($result['summary']['empty_recommendation_flag']);
    }

    public function test_recommendation_is_available_without_confirm_and_records_plan_only_source_contract(): void
    {
        $service = new WatchlistRecommendationService($this->fakePlanGrouping($this->planOutput([
            'TOP_PICKS' => [
                $this->planItem(1, 'TOPA', 'TOP_PICKS', 0.82, 1),
            ],
        ])));

        $result = $service->recommendForTradeDate('2026-05-19');

        $this->assertSame('WatchlistRecommendationService', $result['meta']['source_contract']['consumer']);
        $this->assertSame('WatchlistPlanGroupingService', $result['meta']['source_contract']['upstream']);
        $this->assertTrue($result['meta']['source_contract']['plan_only']);
        $this->assertTrue($result['meta']['source_contract']['available_without_confirm']);
        $this->assertTrue($result['meta']['source_contract']['no_confirm']);
        $this->assertTrue($result['meta']['recommendation_contract']['confirm_does_not_mutate']);
    }

    public function test_recommendation_can_be_empty_even_when_prioritized_plan_groups_are_not_empty(): void
    {
        $service = new WatchlistRecommendationService($this->fakePlanGrouping($this->planOutput([
            'TOP_PICKS' => [
                $this->planItem(1, 'TOPA', 'TOP_PICKS', 0.80, 1),
            ],
            'SECONDARY' => [
                $this->planItem(2, 'SECA', 'SECONDARY', 0.78, 2),
            ],
        ])));

        $result = $service->recommendForTradeDate('2026-05-19', [
            'recommendation' => [
                'min_recommendation_score' => 0.95,
                'borderline_min_score' => 0.70,
            ],
        ]);

        $this->assertTrue($result['ready']);
        $this->assertSame('WATCHLIST_RECOMMENDATION_EMPTY', $result['reason_code']);
        $this->assertSame(2, $result['summary']['source_plan_item_count']);
        $this->assertSame(0, $result['summary']['recommended_count']);
        $this->assertTrue($result['summary']['empty_recommendation_flag']);
        $this->assertSame(['WS_REC_EMPTY_SET'], $result['summary']['reason_codes']);
        $this->assertSame(['BORDERLINE', 'BORDERLINE'], array_column($result['items'], 'recommendation_label'));
        $this->assertSame([false, false], array_column($result['items'], 'recommended_flag'));
    }

    public function test_recommendation_dynamic_target_caps_selected_items_and_marks_rank_outside_target(): void
    {
        $service = new WatchlistRecommendationService($this->fakePlanGrouping($this->planOutput([
            'TOP_PICKS' => [
                $this->planItem(1, 'AAA', 'TOP_PICKS', 0.92, 1),
                $this->planItem(2, 'BBB', 'TOP_PICKS', 0.86, 2),
                $this->planItem(3, 'CCC', 'TOP_PICKS', 0.83, 3),
            ],
        ])));

        $result = $service->recommendForTradeDate('2026-05-19', [
            'recommendation' => [
                'max_recommended_items' => 2,
            ],
        ]);

        $this->assertSame(['AAA', 'BBB'], $result['summary']['recommended_tickers']);
        $this->assertSame([true, true, false], array_column($result['items'], 'recommended_flag'));
        $this->assertContains('WS_REC_RANK_OUTSIDE_DYNAMIC_TARGET', $result['items'][2]['reason_codes']);
        $this->assertContains('WS_REC_NOT_SELECTED', $result['items'][2]['reason_codes']);
    }

    public function test_recommendation_runs_in_capital_free_mode_without_capital_input(): void
    {
        $service = new WatchlistRecommendationService($this->fakePlanGrouping($this->planOutput([
            'TOP_PICKS' => [
                $this->planItem(1, 'FREE', 'TOP_PICKS', 0.82, 1),
            ],
        ])));

        $result = $service->recommendForTradeDate('2026-05-19');

        $this->assertSame('CAPITAL_FREE', $result['meta']['capital_mode']);
        $this->assertNull($result['meta']['input_capital']);
        $this->assertSame('CAPITAL_FREE', $result['items'][0]['capital_mode']);
        $this->assertNotContains('WS_REC_CAPITAL_AWARE', $result['items'][0]['reason_codes']);
    }

    public function test_recommendation_runs_in_capital_aware_mode_without_portfolio_allocation(): void
    {
        $service = new WatchlistRecommendationService($this->fakePlanGrouping($this->planOutput([
            'TOP_PICKS' => [
                $this->planItem(1, 'AFFD', 'TOP_PICKS', 0.88, 1),
                $this->planItem(2, 'NAFF', 'TOP_PICKS', 0.87, 2),
            ],
        ])));

        $result = $service->recommendForTradeDate(
            '2026-05-19',
            [],
            [
                'input_capital' => 500000.0,
                'minimum_lot_values_idr' => [
                    1 => 100000.0,
                    2 => 750000.0,
                ],
            ]
        );

        $this->assertSame('CAPITAL_AWARE', $result['meta']['capital_mode']);
        $this->assertSame(500000.0, $result['meta']['input_capital']);
        $this->assertSame(['AFFD'], $result['summary']['recommended_tickers']);
        $this->assertTrue($result['items'][0]['recommended_flag']);
        $this->assertFalse($result['items'][1]['recommended_flag']);
        $this->assertContains('WS_REC_CAPITAL_AWARE', $result['items'][0]['reason_codes']);
        $this->assertContains('WS_REC_MIN_LOT_NOT_AFFORDABLE', $result['items'][1]['reason_codes']);
        $this->assertFalse($this->payloadHasExactKey($result, 'portfolio_allocation'));
        $this->assertFalse($this->payloadHasExactKey($result, 'suggested_lots'));
    }

    public function test_recommendation_applies_deterministic_tie_break_order_and_does_not_depend_on_plan_array_order(): void
    {
        $items = [
            $this->planItem(30, 'T30', 'TOP_PICKS', 0.80, 3),
            $this->planItem(20, 'T20', 'TOP_PICKS', 0.80, 2),
            $this->planItem(10, 'T10', 'TOP_PICKS', 0.80, 1),
        ];

        $first = (new WatchlistRecommendationService($this->fakePlanGrouping($this->planOutput([
            'TOP_PICKS' => $items,
        ]))))->recommendForTradeDate('2026-05-19');
        $second = (new WatchlistRecommendationService($this->fakePlanGrouping($this->planOutput([
            'TOP_PICKS' => array_reverse($items),
        ]))))->recommendForTradeDate('2026-05-19');

        $this->assertSame(['T10', 'T20', 'T30'], array_column($first['items'], 'ticker'));
        $this->assertSame(['T10', 'T20', 'T30'], array_column($second['items'], 'ticker'));
        $this->assertSame($first['items'], $second['items']);
    }

    public function test_recommendation_preserves_policy_publication_run_and_trade_date_metadata(): void
    {
        $service = new WatchlistRecommendationService($this->fakePlanGrouping($this->planOutput([
            'TOP_PICKS' => [
                $this->planItem(1, 'META', 'TOP_PICKS', 0.82, 1),
            ],
        ])));

        $result = $service->recommendForTradeDate('2026-05-19');

        $this->assertSame('2026-05-19', $result['meta']['trade_date']);
        $this->assertSame('2026-05-19', $result['meta']['trade_date_effective']);
        $this->assertSame(2, $result['meta']['publication_id']);
        $this->assertSame(1, $result['meta']['publication_version']);
        $this->assertSame(3, $result['meta']['run_id']);
        $this->assertSame('WS', $result['meta']['policy_code']);
        $this->assertSame('WS_EOD_RUNTIME', $result['meta']['policy_version']);
        $this->assertSame('WS_ACTIVE_BOOTSTRAP', $result['meta']['paramset_code']);
        $this->assertSame('WATCHLIST_PLAN_GROUPING_READY', $result['meta']['source_plan_reference']['reason_code']);
    }

    public function test_recommendation_output_contains_required_contracts_and_summary(): void
    {
        $service = new WatchlistRecommendationService($this->fakePlanGrouping($this->planOutput([
            'TOP_PICKS' => [
                $this->planItem(1, 'TOPA', 'TOP_PICKS', 0.82, 1),
            ],
        ])));

        $result = $service->recommendForTradeDate('2026-05-19');

        $this->assertArrayHasKey('meta', $result);
        $this->assertArrayHasKey('items', $result);
        $this->assertArrayHasKey('summary', $result);
        $this->assertArrayHasKey('source_contract', $result['meta']);
        $this->assertArrayHasKey('recommendation_contract', $result['meta']);
        $this->assertArrayHasKey('source_plan_reference', $result['meta']);
        $this->assertArrayHasKey('paramset_snapshot', $result['meta']);
        $this->assertSame('PLAN_DERIVED_DETERMINISTIC', $result['meta']['recommendation_contract']['recommendation_mode']);
        $this->assertTrue($result['meta']['recommendation_contract']['can_be_empty']);
        $this->assertSame('THRESHOLD_AND_CAP', $result['meta']['recommendation_contract']['dynamic_count_mode']);
        $this->assertSame(1, $result['summary']['recommended_count']);
    }

    public function test_recommendation_rejects_invalid_recommendation_paramset_and_capital_mode(): void
    {
        $service = new WatchlistRecommendationService($this->fakePlanGrouping($this->planOutput([
            'TOP_PICKS' => [
                $this->planItem(1, 'BADP', 'TOP_PICKS', 0.82, 1),
            ],
        ])));

        $result = $service->recommendForTradeDate(
            '2026-05-19',
            [
                'recommendation' => [
                    'min_recommendation_score' => 0.50,
                    'borderline_min_score' => 0.60,
                    'max_recommended_items' => 0,
                ],
            ],
            [
                'capital_mode' => 'BROKEN',
            ]
        );

        $this->assertFalse($result['ready']);
        $this->assertSame('WATCHLIST_RECOMMENDATION_PARAMSET_INVALID', $result['reason_code']);
        $this->assertNotEmpty($result['meta']['paramset_errors']);
        $this->assertSame([], $result['items']);
    }

    public function test_recommendation_output_is_not_changed_by_confirm_like_fields_on_plan_payload(): void
    {
        $plan = $this->planOutput([
            'TOP_PICKS' => [
                $this->planItem(1, 'SAFE', 'TOP_PICKS', 0.82, 1),
            ],
        ]);
        $planWithConfirmLikeNoise = $plan;
        $planWithConfirmLikeNoise['confirm_state'] = 'CONFIRMED';
        $planWithConfirmLikeNoise['groups']['TOP_PICKS'][0]['confirm_state'] = 'CONFIRMED';

        $first = (new WatchlistRecommendationService($this->fakePlanGrouping($plan)))
            ->recommendForTradeDate('2026-05-19');
        $second = (new WatchlistRecommendationService($this->fakePlanGrouping($planWithConfirmLikeNoise)))
            ->recommendForTradeDate('2026-05-19');

        $this->assertSame($first, $second);
        $this->assertFalse($this->payloadHasExactKey($first, 'confirm_state'));
        $this->assertFalse($this->payloadHasExactKey($first, 'confirm_status'));
    }

    public function test_recommendation_does_not_produce_execution_portfolio_order_or_backtest_fields(): void
    {
        $service = new WatchlistRecommendationService($this->fakePlanGrouping($this->planOutput([
            'TOP_PICKS' => [
                $this->planItem(1, 'SAFE', 'TOP_PICKS', 0.82, 1),
            ],
        ])));

        $result = $service->recommendForTradeDate('2026-05-19');

        foreach ([
            'confirm_state',
            'confirm_status',
            'intraday_snapshot',
            'entry_price_instruction',
            'take_profit_instruction',
            'stop_loss_instruction',
            'portfolio_allocation',
            'holding_state',
            'order_instruction',
            'execution_action',
            'backtest_metric',
            'buy_signal',
            'sell_signal',
        ] as $forbiddenKey) {
            $this->assertFalse($this->payloadHasExactKey($result, $forbiddenKey), $forbiddenKey);
        }
    }

    private function fakePlanGrouping(array $payload): WatchlistPlanGroupingService
    {
        return new class($payload) extends WatchlistPlanGroupingService {
            private array $payload;

            public function __construct(array $payload)
            {
                $this->payload = $payload;
            }

            public function groupForTradeDate(string $tradeDate, array $paramset = []): array
            {
                return $this->payload;
            }
        };
    }

    private function planOutput(array $groups, array $overrides = []): array
    {
        return array_merge([
            'ready' => true,
            'is_ready' => true,
            'reason_code' => 'WATCHLIST_PLAN_GROUPING_READY',
            'trade_date' => '2026-05-19',
            'trade_date_effective' => '2026-05-19',
            'publication_id' => 2,
            'publication_version' => 1,
            'run_id' => 3,
            'policy_code' => 'WS',
            'policy_version' => 'WS_EOD_RUNTIME',
            'paramset_code' => 'WS_ACTIVE_BOOTSTRAP',
            'source_contract' => [
                'consumer' => 'WatchlistPlanGroupingService',
                'upstream' => 'WatchlistScoringService',
            ],
            'group_contract' => [
                'not_final_recommendation' => true,
                'groups' => ['TOP_PICKS', 'SECONDARY', 'WATCH_ONLY', 'AVOID'],
            ],
            'paramset_snapshot' => [
                'policy_code' => 'WS',
                'policy_version' => 'WS_EOD_RUNTIME',
                'paramset_code' => 'WS_ACTIVE_BOOTSTRAP',
                'grouping' => [],
            ],
            'groups' => array_merge([
                'TOP_PICKS' => [],
                'SECONDARY' => [],
                'WATCH_ONLY' => [],
                'AVOID' => [],
            ], $groups),
            'excluded' => [],
            'summary' => [
                'top_picks_count' => count($groups['TOP_PICKS'] ?? []),
                'secondary_count' => count($groups['SECONDARY'] ?? []),
                'watch_only_count' => count($groups['WATCH_ONLY'] ?? []),
                'avoid_count' => count($groups['AVOID'] ?? []),
            ],
        ], $overrides);
    }

    private function planItem(int $tickerId, string $tickerCode, string $group, float $scoreTotal, ?int $planRank): array
    {
        return [
            'ticker_id' => $tickerId,
            'ticker_code' => $tickerCode,
            'trade_date' => '2026-05-19',
            'trade_date_effective' => '2026-05-19',
            'publication_id' => 2,
            'publication_version' => 1,
            'run_id' => 3,
            'eligible_score' => true,
            'score_total' => $scoreTotal,
            'score_components' => [
                'score_momentum' => 0.80,
                'score_breakout' => 0.80,
                'score_volume' => 0.80,
                'score_risk' => 0.80,
            ],
            'reason_codes' => ['WS_PLAN_TOP_PICK'],
            'ranking_keys' => [
                'score_total_desc' => $scoreTotal,
                'ticker_id_asc' => $tickerId,
            ],
            'score_metrics' => [
                'dv20_idr' => 7000000000.0,
                'atr14_pct' => 0.0500,
                'vol_ratio' => 1.50,
            ],
            'plan_group' => $group,
            'group_semantic' => $group,
            'group_reason_code' => $group === 'SECONDARY' ? 'WS_PLAN_SECONDARY' : 'WS_PLAN_TOP_PICK',
            'group_rank' => $planRank,
            'plan_rank' => $planRank,
        ];
    }

    private function payloadHasExactKey(array $payload, string $key): bool
    {
        foreach ($payload as $payloadKey => $value) {
            if ($payloadKey === $key) {
                return true;
            }

            if (is_array($value) && $this->payloadHasExactKey($value, $key)) {
                return true;
            }
        }

        return false;
    }
}
