<?php

use App\Application\Watchlist\Services\WatchlistPlanGroupingService;
use App\Application\Watchlist\Services\WatchlistScoringService;

class WatchlistPlanGroupingServiceTest extends TestCase
{
    public function test_grouping_creates_deterministic_top_secondary_watch_and_avoid_groups_from_scored_output(): void
    {
        $service = new WatchlistPlanGroupingService($this->fakeScoring($this->scoredOutput([
            $this->scoredItem(1, 'TOPA', 0.82),
            $this->scoredItem(2, 'SECA', 0.61),
            $this->scoredItem(3, 'WCHA', 0.45),
            $this->scoredItem(4, 'AVDA', 0.30),
        ])));

        $result = $service->groupForTradeDate('2026-05-19');

        $this->assertTrue($result['ready']);
        $this->assertSame('WATCHLIST_PLAN_GROUPING_READY', $result['reason_code']);
        $this->assertSame(['TOPA'], array_column($result['groups']['TOP_PICKS'], 'ticker_code'));
        $this->assertSame(['SECA'], array_column($result['groups']['SECONDARY'], 'ticker_code'));
        $this->assertSame(['WCHA'], array_column($result['groups']['WATCH_ONLY'], 'ticker_code'));
        $this->assertSame(['AVDA'], array_column($result['groups']['AVOID'], 'ticker_code'));
        $this->assertContains('WS_PLAN_TOP_PICK', $result['groups']['TOP_PICKS'][0]['reason_codes']);
        $this->assertContains('WS_PLAN_SECONDARY', $result['groups']['SECONDARY'][0]['reason_codes']);
        $this->assertContains('WS_PLAN_WATCH_ONLY', $result['groups']['WATCH_ONLY'][0]['reason_codes']);
        $this->assertContains('WS_PLAN_AVOID_LOW_SCORE', $result['groups']['AVOID'][0]['reason_codes']);
    }

    public function test_grouping_fails_closed_when_scoring_source_is_not_ready(): void
    {
        $service = new WatchlistPlanGroupingService($this->fakeScoring([
            'ready' => false,
            'is_ready' => false,
            'reason_code' => 'WATCHLIST_SCORING_SOURCE_NOT_READY',
            'trade_date' => '2026-05-19',
            'items' => [],
            'excluded' => [],
        ]));

        $result = $service->groupForTradeDate('2026-05-19');

        $this->assertFalse($result['ready']);
        $this->assertFalse($result['is_ready']);
        $this->assertSame('WATCHLIST_PLAN_GROUPING_SOURCE_NOT_READY', $result['reason_code']);
        $this->assertSame([], $result['groups']['TOP_PICKS']);
        $this->assertSame([], $result['groups']['SECONDARY']);
        $this->assertSame([], $result['groups']['WATCH_ONLY']);
        $this->assertSame([], $result['groups']['AVOID']);
    }

    public function test_grouping_excludes_invalid_scored_item_where_eligible_score_is_false(): void
    {
        $service = new WatchlistPlanGroupingService($this->fakeScoring($this->scoredOutput([
            array_merge($this->scoredItem(1, 'BAD1', 0.92), ['eligible_score' => false]),
        ])));

        $result = $service->groupForTradeDate('2026-05-19');

        $this->assertSame([], $result['groups']['TOP_PICKS']);
        $this->assertSame([], $result['groups']['SECONDARY']);
        $this->assertSame([], $result['groups']['WATCH_ONLY']);
        $this->assertSame(['BAD1'], array_column($result['excluded'], 'ticker_code'));
        $this->assertSame(['BAD1'], array_column($result['groups']['AVOID'], 'ticker_code'));
        $this->assertContains('WS_PLAN_AVOID_EXCLUDED', $result['groups']['AVOID'][0]['reason_codes']);
    }

    public function test_grouping_keeps_top_picks_limited_by_max_items_and_overflows_valid_candidates_into_secondary(): void
    {
        $service = new WatchlistPlanGroupingService($this->fakeScoring($this->scoredOutput([
            $this->scoredItem(1, 'TOP1', 0.95),
            $this->scoredItem(2, 'TOP2', 0.91),
            $this->scoredItem(3, 'TOP3', 0.88),
        ])));

        $result = $service->groupForTradeDate('2026-05-19', [
            'grouping' => [
                'top_picks' => ['max_items' => 1],
            ],
        ]);

        $this->assertSame(['TOP1'], array_column($result['groups']['TOP_PICKS'], 'ticker_code'));
        $this->assertSame(['TOP2', 'TOP3'], array_column($result['groups']['SECONDARY'], 'ticker_code'));
        $this->assertSame(1, $result['summary']['top_picks_count']);
        $this->assertSame(2, $result['summary']['secondary_count']);
    }

    public function test_grouping_keeps_secondary_limited_by_max_items_and_overflows_valid_candidates_into_watch_only(): void
    {
        $service = new WatchlistPlanGroupingService($this->fakeScoring($this->scoredOutput([
            $this->scoredItem(1, 'TOP1', 0.95),
            $this->scoredItem(2, 'SEC1', 0.68),
            $this->scoredItem(3, 'SEC2', 0.66),
        ])));

        $result = $service->groupForTradeDate('2026-05-19', [
            'grouping' => [
                'top_picks' => ['max_items' => 1],
                'secondary' => ['max_items' => 1],
            ],
        ]);

        $this->assertSame(['TOP1'], array_column($result['groups']['TOP_PICKS'], 'ticker_code'));
        $this->assertSame(['SEC1'], array_column($result['groups']['SECONDARY'], 'ticker_code'));
        $this->assertSame(['SEC2'], array_column($result['groups']['WATCH_ONLY'], 'ticker_code'));
    }

    public function test_grouping_places_low_score_candidates_into_avoid(): void
    {
        $service = new WatchlistPlanGroupingService($this->fakeScoring($this->scoredOutput([
            $this->scoredItem(1, 'LOW1', 0.39),
        ])));

        $result = $service->groupForTradeDate('2026-05-19');

        $this->assertSame([], $result['groups']['TOP_PICKS']);
        $this->assertSame([], $result['groups']['SECONDARY']);
        $this->assertSame([], $result['groups']['WATCH_ONLY']);
        $this->assertSame(['LOW1'], array_column($result['groups']['AVOID'], 'ticker_code'));
        $this->assertSame('WS_PLAN_AVOID_LOW_SCORE', $result['groups']['AVOID'][0]['group_reason_code']);
    }

    public function test_grouping_preserves_policy_publication_run_and_trade_date_metadata(): void
    {
        $service = new WatchlistPlanGroupingService($this->fakeScoring($this->scoredOutput([
            $this->scoredItem(1, 'META', 0.80),
        ])));

        $result = $service->groupForTradeDate('2026-05-19');

        $this->assertSame('2026-05-19', $result['trade_date']);
        $this->assertSame('2026-05-19', $result['trade_date_effective']);
        $this->assertSame(2, $result['publication_id']);
        $this->assertSame(1, $result['publication_version']);
        $this->assertSame(3, $result['run_id']);
        $this->assertSame('WS', $result['policy_code']);
        $this->assertSame('WS_EOD_RUNTIME', $result['policy_version']);
        $this->assertSame('WS_ACTIVE_BOOTSTRAP', $result['paramset_code']);
    }

    public function test_grouping_output_contains_source_contract_group_contract_paramset_snapshot_groups_excluded_and_summary(): void
    {
        $service = new WatchlistPlanGroupingService($this->fakeScoring($this->scoredOutput([
            $this->scoredItem(1, 'TOPA', 0.80),
        ], [
            [
                'ticker_id' => 2,
                'ticker_code' => 'EXCL',
                'eligible_score' => false,
                'reason_codes' => ['WS_DATA_MISSING'],
            ],
        ])));

        $result = $service->groupForTradeDate('2026-05-19');

        $this->assertSame('WatchlistPlanGroupingService', $result['source_contract']['consumer']);
        $this->assertSame('WatchlistScoringService', $result['source_contract']['upstream']);
        $this->assertTrue($result['source_contract']['no_raw_market_data']);
        $this->assertTrue($result['source_contract']['no_latest_shortcut']);
        $this->assertTrue($result['source_contract']['no_recommendation']);
        $this->assertTrue($result['source_contract']['no_confirm']);
        $this->assertTrue($result['source_contract']['no_execution']);
        $this->assertTrue($result['source_contract']['no_backtest']);
        $this->assertSame('PLAN_GROUPING_DETERMINISTIC', $result['group_contract']['grouping_mode']);
        $this->assertSame(['TOP_PICKS', 'SECONDARY', 'WATCH_ONLY', 'AVOID'], $result['group_contract']['groups']);
        $this->assertSame('ticker_id', $result['group_contract']['dedupe_key']);
        $this->assertArrayHasKey('grouping', $result['paramset_snapshot']);
        $this->assertArrayHasKey('TOP_PICKS', $result['groups']);
        $this->assertArrayHasKey('SECONDARY', $result['groups']);
        $this->assertArrayHasKey('WATCH_ONLY', $result['groups']);
        $this->assertArrayHasKey('AVOID', $result['groups']);
        $this->assertSame(['EXCL'], array_column($result['excluded'], 'ticker_code'));
        $this->assertSame(2, $result['summary']['input_count']);
    }

    public function test_grouping_applies_deterministic_tie_break_order_and_does_not_depend_on_input_array_order(): void
    {
        $items = [
            $this->scoredItem(30, 'T30', 0.80, ['dv20_idr' => 7000000000.0, 'atr14_pct' => 0.0500]),
            $this->scoredItem(20, 'T20', 0.80, ['dv20_idr' => 9000000000.0, 'atr14_pct' => 0.0500]),
            $this->scoredItem(10, 'T10', 0.80, ['dv20_idr' => 9000000000.0, 'atr14_pct' => 0.0400]),
        ];

        $first = (new WatchlistPlanGroupingService($this->fakeScoring($this->scoredOutput($items))))
            ->groupForTradeDate('2026-05-19');
        $second = (new WatchlistPlanGroupingService($this->fakeScoring($this->scoredOutput(array_reverse($items)))))
            ->groupForTradeDate('2026-05-19');

        $this->assertSame([10, 20, 30], array_column($first['groups']['TOP_PICKS'], 'ticker_id'));
        $this->assertSame([10, 20, 30], array_column($second['groups']['TOP_PICKS'], 'ticker_id'));
        $this->assertSame(
            $first['groups']['TOP_PICKS'],
            $second['groups']['TOP_PICKS']
        );
    }

    public function test_grouping_deduplicates_ticker_id_deterministically(): void
    {
        $service = new WatchlistPlanGroupingService($this->fakeScoring($this->scoredOutput([
            $this->scoredItem(10, 'DUPL', 0.51),
            $this->scoredItem(10, 'DUPH', 0.92),
            $this->scoredItem(20, 'NEXT', 0.80),
        ])));

        $result = $service->groupForTradeDate('2026-05-19');
        $active = array_merge(
            $result['groups']['TOP_PICKS'],
            $result['groups']['SECONDARY'],
            $result['groups']['WATCH_ONLY']
        );

        $this->assertSame([10, 20], array_column($active, 'ticker_id'));
        $this->assertSame(0.92, $active[0]['score_total']);
        $this->assertSame('DUPH', $active[0]['ticker_code']);
    }

    public function test_grouping_does_not_produce_recommendation_confirm_execution_or_backtest_fields(): void
    {
        $service = new WatchlistPlanGroupingService($this->fakeScoring($this->scoredOutput([
            $this->scoredItem(1, 'SAFE', 0.80),
        ])));

        $result = $service->groupForTradeDate('2026-05-19');

        foreach ([
            'recommendation_label',
            'final_recommendation',
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
        ] as $forbiddenKey) {
            $this->assertFalse($this->payloadHasExactKey($result, $forbiddenKey), $forbiddenKey);
        }
    }

    public function test_grouping_rejects_invalid_grouping_paramset_thresholds(): void
    {
        $service = new WatchlistPlanGroupingService($this->fakeScoring($this->scoredOutput([
            $this->scoredItem(1, 'BADP', 0.80),
        ])));

        $result = $service->groupForTradeDate('2026-05-19', [
            'grouping' => [
                'top_picks' => ['min_score_total' => 0.50],
                'secondary' => ['min_score_total' => 0.60],
            ],
        ]);

        $this->assertFalse($result['ready']);
        $this->assertSame('WATCHLIST_PLAN_GROUPING_PARAMSET_INVALID', $result['reason_code']);
        $this->assertNotEmpty($result['paramset_errors']);
        $this->assertSame([], $result['groups']['TOP_PICKS']);
        $this->assertSame([], $result['groups']['SECONDARY']);
        $this->assertSame([], $result['groups']['WATCH_ONLY']);
    }

    private function fakeScoring(array $payload): WatchlistScoringService
    {
        return new class($payload) extends WatchlistScoringService {
            private array $payload;

            public function __construct(array $payload)
            {
                $this->payload = $payload;
            }

            public function scoreForTradeDate(string $tradeDate, array $paramset = []): array
            {
                return $this->payload;
            }
        };
    }

    private function scoredOutput(array $items, array $excluded = [], array $overrides = []): array
    {
        return array_merge([
            'ready' => true,
            'is_ready' => true,
            'reason_code' => 'WATCHLIST_SCORING_READY',
            'trade_date' => '2026-05-19',
            'trade_date_effective' => '2026-05-19',
            'publication_id' => 2,
            'publication_version' => 1,
            'run_id' => 3,
            'policy_code' => 'WS',
            'policy_version' => 'WS_EOD_RUNTIME',
            'paramset_code' => 'WS_ACTIVE_BOOTSTRAP',
            'source_contract' => [
                'consumer' => 'WatchlistScoringService',
                'upstream' => 'WatchlistCandidateUniverseService',
            ],
            'score_contract' => [
                'sort_keys' => [
                    'score_total_desc',
                    'score_breakout_desc',
                    'score_momentum_desc',
                    'dv20_idr_desc',
                    'atr14_pct_asc',
                    'ticker_id_asc',
                ],
            ],
            'paramset_snapshot' => [
                'policy_code' => 'WS',
                'policy_version' => 'WS_EOD_RUNTIME',
                'paramset_code' => 'WS_ACTIVE_BOOTSTRAP',
            ],
            'items' => $items,
            'excluded' => $excluded,
            'summary' => [
                'input_count' => count($items) + count($excluded),
                'scored_count' => count($items),
                'excluded_count' => count($excluded),
            ],
        ], $overrides);
    }

    private function scoredItem(int $tickerId, string $tickerCode, float $scoreTotal, array $metricOverrides = []): array
    {
        $metrics = array_merge([
            'dv20_idr' => 7000000000.0,
            'atr14_pct' => 0.0500,
            'vol_ratio' => 1.50,
        ], $metricOverrides);

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
                'score_momentum' => $metricOverrides['score_momentum'] ?? 0.80,
                'score_breakout' => $metricOverrides['score_breakout'] ?? 0.80,
                'score_volume' => $metricOverrides['score_volume'] ?? 0.80,
                'score_risk' => $metricOverrides['score_risk'] ?? 0.80,
            ],
            'score_weights' => [
                'momentum' => 0.30,
                'breakout' => 0.30,
                'volume' => 0.20,
                'risk' => 0.20,
            ],
            'factor_breakdown' => [],
            'reason_codes' => ['WS_MOM_STRONG'],
            'ranking_keys' => [
                'score_total_desc' => $scoreTotal,
                'score_breakout_desc' => $metricOverrides['score_breakout'] ?? 0.80,
                'score_momentum_desc' => $metricOverrides['score_momentum'] ?? 0.80,
                'dv20_idr_desc' => $metrics['dv20_idr'],
                'atr14_pct_asc' => $metrics['atr14_pct'],
                'ticker_id_asc' => $tickerId,
            ],
            'score_metrics' => $metrics,
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
