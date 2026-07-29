<?php

namespace Tests\Unit\Watchlist;

use App\Application\Watchlist\Services\WeeklySwingTailRiskS01DiagnosticService;
use TestCase;

class WeeklySwingTailRiskS01DiagnosticTest extends TestCase
{
    public function test_it_preregisters_exactly_three_bounded_tail_risk_hypotheses(): void
    {
        $rows = [];
        for ($index = 0; $index < 150; $index++) {
            $weak = $index >= 120;
            $tail = $index % 10 === 0;
            $rows[] = [
                'trade_date' => (new \DateTimeImmutable('2024-01-02'))
                    ->modify('+'.$index.' days')
                    ->format('Y-m-d'),
                'ret_net' => $tail ? -0.10 : ($weak ? -0.02 : 0.01),
                'market_regime' => $weak ? 'WEAK' : 'STRONG',
                'signal_tick_risk_expansion_pct' => $tail ? 0.02 : 0.01,
            ];
        }

        $analysis = (new WeeklySwingTailRiskS01DiagnosticService())
            ->analyzeEvidence($rows, 490);

        $this->assertCount(3, $analysis['pre_registered_hypotheses']);
        $this->assertSame([
            'S01_H1_IHSG_NON_WEAK_GUARD',
            'S01_H2_TICK_RISK_LT_1P5_GUARD',
            'S01_H3_DAILY_CLOSE_LOSS_CONTAINMENT',
        ], array_column($analysis['pre_registered_hypotheses'], 'hypothesis_code'));
        foreach ($analysis['pre_registered_hypotheses'] as $hypothesis) {
            $this->assertTrue($hypothesis['one_primary_idea']);
            $this->assertFalse($hypothesis['oos_used']);
            $this->assertFalse($hypothesis['canonical_gates_changed']);
            $this->assertFalse($hypothesis['future_return_as_runtime_input']);
        }
        $this->assertSame(490, $analysis['overall_metrics']['days_covered']);
    }

    public function test_service_never_routes_oos_or_reopens_r02(): void
    {
        $service = (string) file_get_contents(base_path(
            'app/Application/Watchlist/Services/WeeklySwingTailRiskS01DiagnosticService.php'
        ));
        $command = (string) file_get_contents(base_path(
            'app/Console/Commands/Watchlist/RunWeeklySwingTailRiskS01DiagnosticCommand.php'
        ));

        $this->assertStringContainsString('WS_TAIL_RISK_S01', $service);
        $this->assertStringContainsString("'r02_reopened' => false", $service);
        $this->assertStringContainsString("'oos_table_read' => false", $service);
        $this->assertStringContainsString("'official_is_runtime_invoked' => false", $service);
        $this->assertStringNotContainsString('watchlist_bt_oos_eval_ws', $service);
        $this->assertStringNotContainsString('WatchlistBacktestOos', $service);
        $this->assertStringContainsString(
            'watchlist:weekly-swing-tail-risk-s01-diagnostic',
            $command
        );
    }
}
