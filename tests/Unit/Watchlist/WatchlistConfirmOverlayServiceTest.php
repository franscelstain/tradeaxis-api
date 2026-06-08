<?php

use App\Application\Watchlist\Services\WatchlistConfirmOverlayService;
use App\Application\Watchlist\Services\WatchlistPlanGroupingService;
use App\Application\Watchlist\Services\WatchlistRecommendationService;

class WatchlistConfirmOverlayServiceTest extends TestCase
{
    public function test_confirm_overlay_consumes_immutable_plan_candidate_binding(): void
    {
        $planOutput = $this->planOutput([
            'TOP_PICKS' => [$this->planItem(1, 'TOPA', 'TOP_PICKS', 0.92, 1)],
            'SECONDARY' => [$this->planItem(2, 'SECA', 'SECONDARY', 0.68, 2)],
            'WATCH_ONLY' => [$this->planItem(3, 'WCHA', 'WATCH_ONLY', 0.52, 3)],
        ]);
        $service = $this->serviceForPlan($planOutput);

        $result = $service->confirmForTradeDate('2026-05-19', [
            ['ticker_code' => 'WCHA', 'confirm_state' => 'CONFIRMED', 'confirm_source' => 'manual_check'],
        ]);

        $this->assertTrue($result['ready']);
        $this->assertSame('WATCHLIST_CONFIRM_OVERLAY_READY', $result['reason_code']);
        $this->assertSame('WatchlistConfirmOverlayService', $result['source_contract']['consumer']);
        $this->assertSame('candidate PLAN membership', $result['confirm_contract']['eligibility_source']);
        $this->assertTrue($result['confirm_contract']['recommended_plan_candidate_can_confirm']);
        $this->assertTrue($result['confirm_contract']['non_recommended_plan_candidate_can_confirm']);
        $this->assertSame(['TOPA', 'SECA', 'WCHA'], array_column($result['items'], 'ticker'));
        $this->assertSame(3, $result['summary']['plan_candidate_count']);
    }

    public function test_recommended_plan_candidate_can_be_confirmed_without_changing_recommendation_membership(): void
    {
        $planOutput = $this->planOutput([
            'TOP_PICKS' => [$this->planItem(1, 'TOPA', 'TOP_PICKS', 0.92, 1)],
        ]);
        $service = $this->serviceForPlan($planOutput);

        $before = (new WatchlistRecommendationService($this->fakePlanGrouping($planOutput)))
            ->recommendForTradeDate('2026-05-19');
        $result = $service->confirmForTradeDate('2026-05-19', [
            ['ticker_id' => 1, 'confirm_state' => 'CONFIRMED', 'confirm_reason_codes' => ['WS_CONFIRM_OK']],
        ]);

        $this->assertSame($before['items'][0]['recommended_flag'], $result['items'][0]['recommended_flag']);
        $this->assertSame($before['items'][0]['recommendation_rank'], $result['items'][0]['recommendation_rank']);
        $this->assertSame($before['items'][0]['recommendation_score'], $result['items'][0]['recommendation_score']);
        $this->assertSame($before['items'][0]['recommendation_label'], $result['items'][0]['recommendation_label']);
        $this->assertSame('CONFIRMED', $result['items'][0]['confirm_state']);
        $this->assertContains('WS_CONFIRM_ELIGIBLE_RECOMMENDED', $result['items'][0]['confirm_reason_codes']);
        $this->assertContains('WS_CONFIRM_APPLIED', $result['items'][0]['confirm_reason_codes']);
        $this->assertContains('WS_CONFIRM_OK', $result['items'][0]['confirm_reason_codes']);
        $this->assertFalse($result['summary']['recommendation_mutated']);
    }

    public function test_non_recommended_plan_candidate_can_be_confirmed_without_becoming_recommended(): void
    {
        $planOutput = $this->planOutput([
            'TOP_PICKS' => [$this->planItem(1, 'TOPA', 'TOP_PICKS', 0.90, 1)],
            'WATCH_ONLY' => [$this->planItem(3, 'WCHA', 'WATCH_ONLY', 0.52, 3)],
        ]);
        $service = $this->serviceForPlan($planOutput);

        $result = $service->confirmForTradeDate('2026-05-19', [
            ['ticker_code' => 'WCHA', 'confirm_state' => 'CONFIRMED', 'confirm_reason_codes' => ['WS_CONFIRM_NEUTRAL']],
        ]);
        $watchOnly = $this->findTicker($result['items'], 'WCHA');

        $this->assertSame('WATCH_ONLY', $watchOnly['plan_group']);
        $this->assertFalse($watchOnly['recommended_flag']);
        $this->assertSame('NOT_RECOMMENDED_PLAN_CANDIDATE', $watchOnly['recommendation_label']);
        $this->assertSame('CONFIRMED', $watchOnly['confirm_state']);
        $this->assertContains('WS_CONFIRM_ELIGIBLE_NON_RECOMMENDED', $watchOnly['confirm_reason_codes']);
        $this->assertContains('WS_CONFIRM_APPLIED', $watchOnly['confirm_reason_codes']);
    }

    public function test_confirm_overlay_rejects_unknown_candidate_into_diagnostics(): void
    {
        $planOutput = $this->planOutput([
            'TOP_PICKS' => [$this->planItem(1, 'TOPA', 'TOP_PICKS', 0.90, 1)],
        ]);
        $service = $this->serviceForPlan($planOutput);

        $result = $service->confirmForTradeDate('2026-05-19', [
            ['ticker_code' => 'MISS', 'confirm_state' => 'CONFIRMED'],
        ]);

        $this->assertSame(1, $result['summary']['excluded_count']);
        $this->assertSame('MISS', $result['excluded'][0]['ticker_code']);
        $this->assertFalse($result['excluded'][0]['confirm_eligible']);
        $this->assertSame(['WS_CONFIRM_REJECTED_UNKNOWN_CANDIDATE'], $result['excluded'][0]['confirm_reason_codes']);
        $this->assertSame(['TOPA'], array_column($result['items'], 'ticker'));
    }

    public function test_excluded_or_avoid_candidate_cannot_become_active_confirm_candidate(): void
    {
        $planOutput = $this->planOutput([
            'TOP_PICKS' => [$this->planItem(1, 'TOPA', 'TOP_PICKS', 0.90, 1)],
            'AVOID' => [$this->planItem(4, 'AVDA', 'AVOID', 0.30, null)],
        ], [
            'excluded' => [$this->planItem(5, 'EXCL', 'AVOID', 0.20, null)],
        ]);
        $service = $this->serviceForPlan($planOutput);

        $result = $service->confirmForTradeDate('2026-05-19', [
            ['ticker_code' => 'AVDA', 'confirm_state' => 'CONFIRMED'],
            ['ticker_code' => 'EXCL', 'confirm_state' => 'CONFIRMED'],
        ]);

        $this->assertSame(['TOPA'], array_column($result['items'], 'ticker'));
        $this->assertSame(2, $result['summary']['excluded_count']);
        $this->assertSame(['AVDA', 'EXCL'], array_column($result['excluded'], 'ticker_code'));
    }

    public function test_confirm_overlay_does_not_mutate_rank_score_label_or_hash_when_fields_exist(): void
    {
        $planOutput = $this->planOutput([
            'TOP_PICKS' => [$this->planItem(1, 'TOPA', 'TOP_PICKS', 0.90, 1, [
                'hash' => 'plan-item-hash-1',
                'plan_hash' => 'plan-hash-1',
            ])],
        ], [
            'plan_hash' => 'plan-hash-1',
        ]);
        $recommendationOutput = (new WatchlistRecommendationService($this->fakePlanGrouping($planOutput)))
            ->recommendForTradeDate('2026-05-19');
        $recommendationOutput['items'][0]['recommendation_hash'] = 'rec-hash-1';
        $service = new WatchlistConfirmOverlayService();

        $result = $service->confirmFromPlanAndRecommendationOutput($planOutput, $recommendationOutput, [
            ['ticker_id' => 1, 'confirm_state' => 'CONFIRMED'],
        ]);

        $this->assertSame(1, $result['items'][0]['plan_rank']);
        $this->assertSame(0.90, $result['items'][0]['score_total']);
        $this->assertSame('RECOMMENDED', $result['items'][0]['recommendation_label']);
        $this->assertSame('plan-item-hash-1', $result['items'][0]['hash']);
        $this->assertSame('plan-hash-1', $result['items'][0]['plan_hash']);
        $this->assertSame('rec-hash-1', $result['items'][0]['recommendation_hash']);
        $this->assertFalse($result['summary']['score_mutated']);
        $this->assertFalse($result['summary']['rank_mutated']);
        $this->assertFalse($result['summary']['label_mutated']);
        $this->assertFalse($result['summary']['hash_mutated']);
    }

    public function test_confirm_overlay_output_contains_required_contracts_and_preserves_metadata(): void
    {
        $planOutput = $this->planOutput([
            'TOP_PICKS' => [$this->planItem(1, 'TOPA', 'TOP_PICKS', 0.90, 1)],
        ]);
        $service = $this->serviceForPlan($planOutput);

        $result = $service->confirmForTradeDate('2026-05-19');

        foreach (['source_contract', 'confirm_contract', 'immutability_contract', 'items', 'excluded', 'summary'] as $key) {
            $this->assertArrayHasKey($key, $result);
        }
        $this->assertSame('WS', $result['policy_code']);
        $this->assertSame('WS_EOD_RUNTIME', $result['policy_version']);
        $this->assertSame('WS_ACTIVE_BOOTSTRAP', $result['paramset_code']);
        $this->assertSame('2026-05-19', $result['trade_date']);
        $this->assertSame('2026-05-19', $result['trade_date_effective']);
        $this->assertSame(2, $result['publication_id']);
        $this->assertSame(1, $result['publication_version']);
        $this->assertSame(3, $result['run_id']);
        $this->assertArrayHasKey('plan_hash', $result);
        $this->assertArrayHasKey('recommendation_hash', $result);
        $this->assertTrue($result['source_contract']['no_raw_market_data']);
        $this->assertTrue($result['source_contract']['no_latest_shortcut']);
        $this->assertTrue($result['source_contract']['no_recommendation_mutation']);
        $this->assertTrue($result['source_contract']['no_score_mutation']);
        $this->assertTrue($result['source_contract']['no_rank_mutation']);
        $this->assertTrue($result['source_contract']['no_label_mutation']);
        $this->assertTrue($result['source_contract']['no_hash_mutation']);
    }

    public function test_confirm_overlay_fails_closed_when_plan_or_recommendation_source_is_not_ready(): void
    {
        $service = new WatchlistConfirmOverlayService();
        $notReadyPlan = $this->planOutput([], [
            'ready' => false,
            'is_ready' => false,
            'reason_code' => 'WATCHLIST_PLAN_GROUPING_SOURCE_NOT_READY',
        ]);
        $readyRecommendation = $this->recommendationOutput([]);

        $result = $service->confirmFromPlanAndRecommendationOutput($notReadyPlan, $readyRecommendation);

        $this->assertFalse($result['ready']);
        $this->assertSame('WATCHLIST_CONFIRM_SOURCE_PLAN_NOT_READY', $result['reason_code']);
    }

    public function test_confirm_overlay_does_not_produce_portfolio_execution_backtest_or_command_fields(): void
    {
        $planOutput = $this->planOutput([
            'TOP_PICKS' => [$this->planItem(1, 'TOPA', 'TOP_PICKS', 0.90, 1)],
        ]);
        $service = $this->serviceForPlan($planOutput);

        $result = $service->confirmForTradeDate('2026-05-19', [
            ['ticker_code' => 'TOPA', 'confirm_state' => 'CONFIRMED'],
        ]);

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
            'api_endpoint',
            'artisan_command',
        ] as $forbiddenKey) {
            $this->assertFalse($this->payloadHasExactKey($result, $forbiddenKey), $forbiddenKey);
        }
    }

    private function serviceForPlan(array $planOutput): WatchlistConfirmOverlayService
    {
        $planGrouping = $this->fakePlanGrouping($planOutput);

        return new WatchlistConfirmOverlayService(
            $planGrouping,
            new WatchlistRecommendationService($planGrouping)
        );
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
            'groups' => array_merge([
                'TOP_PICKS' => [],
                'SECONDARY' => [],
                'WATCH_ONLY' => [],
                'AVOID' => [],
            ], $groups),
            'excluded' => [],
            'summary' => [],
        ], $overrides);
    }

    private function recommendationOutput(array $items, array $overrides = []): array
    {
        return array_merge([
            'ready' => true,
            'is_ready' => true,
            'reason_code' => 'WATCHLIST_RECOMMENDATION_READY',
            'meta' => [
                'policy_code' => 'WS',
                'policy_version' => 'WS_EOD_RUNTIME',
                'paramset_code' => 'WS_ACTIVE_BOOTSTRAP',
                'trade_date' => '2026-05-19',
                'trade_date_effective' => '2026-05-19',
                'publication_id' => 2,
                'publication_version' => 1,
                'run_id' => 3,
            ],
            'items' => $items,
            'summary' => [],
        ], $overrides);
    }

    private function planItem(
        int $tickerId,
        string $tickerCode,
        string $group,
        float $scoreTotal,
        ?int $planRank,
        array $overrides = []
    ): array {
        $reasonCode = 'WS_PLAN_TOP_PICK';
        if ($group === 'SECONDARY') {
            $reasonCode = 'WS_PLAN_SECONDARY';
        } elseif ($group === 'WATCH_ONLY') {
            $reasonCode = 'WS_PLAN_WATCH_ONLY';
        } elseif ($group === 'AVOID') {
            $reasonCode = 'WS_PLAN_AVOID_LOW_SCORE';
        }

        return array_merge([
            'ticker_id' => $tickerId,
            'ticker_code' => $tickerCode,
            'eligible_score' => true,
            'score_total' => $scoreTotal,
            'score_components' => [
                'score_momentum' => 0.80,
                'score_breakout' => 0.80,
                'score_volume' => 0.80,
                'score_risk' => 0.80,
            ],
            'reason_codes' => [$reasonCode],
            'plan_group' => $group,
            'group_semantic' => $group,
            'group_reason_code' => $reasonCode,
            'group_rank' => $planRank,
            'plan_rank' => $planRank,
        ], $overrides);
    }

    private function findTicker(array $items, string $ticker): array
    {
        foreach ($items as $item) {
            if (($item['ticker'] ?? null) === $ticker) {
                return $item;
            }
        }

        $this->fail('Ticker '.$ticker.' was not found.');
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
