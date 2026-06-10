<?php

use App\Application\Watchlist\Services\WatchlistPlanGroupingService;

class WatchlistPlanGroupingQuantileCutoffTest extends TestCase
{
    public function test_bt_grid_quantiles_resolve_deterministic_daily_score_cutoffs(): void
    {
        $service = new WatchlistPlanGroupingService();
        $scored = [
            'ready' => true,
            'is_ready' => true,
            'trade_date' => '2026-01-02',
            'items' => [
                $this->item(1, 0.90),
                $this->item(2, 0.80),
                $this->item(3, 0.60),
                $this->item(4, 0.40),
            ],
            'excluded' => [],
        ];

        $result = $service->groupScoredOutput($scored, [
            'grouping' => [
                'top_min_score_q' => 0.75,
                'secondary_min_score_q' => 0.50,
                'top_picks' => ['max_items' => 5],
                'secondary' => ['max_items' => 10],
            ],
        ]);

        $this->assertTrue($result['is_ready']);
        $this->assertSame('DAILY_SCORE_QUANTILE', $result['cutoff_manifest']['mode']);
        $this->assertEqualsWithDelta(0.825, $result['cutoff_manifest']['top_picks_min_score_total'], 0.000001);
        $this->assertEqualsWithDelta(0.70, $result['cutoff_manifest']['secondary_min_score_total'], 0.000001);
        $this->assertSame([1], array_column($result['groups']['TOP_PICKS'], 'ticker_id'));
        $this->assertSame([2], array_column($result['groups']['SECONDARY'], 'ticker_id'));
    }

    private function item(int $tickerId, float $score): array
    {
        return [
            'ticker_id' => $tickerId,
            'ticker_code' => 'T'.$tickerId,
            'eligible_score' => true,
            'score_total' => $score,
            'score_components' => ['score_breakout' => $score, 'score_momentum' => $score],
            'score_metrics' => ['dv20_idr' => 10000000000, 'atr14_pct' => 0.05],
            'reason_codes' => [],
        ];
    }
}
