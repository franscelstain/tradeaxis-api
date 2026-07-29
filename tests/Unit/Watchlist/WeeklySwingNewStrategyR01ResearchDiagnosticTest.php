<?php

namespace Tests\Unit\Watchlist;

use App\Application\Watchlist\Services\WeeklySwingNewStrategyR01ResearchDiagnosticService;
use TestCase;

class WeeklySwingNewStrategyR01ResearchDiagnosticTest extends TestCase
{
    public function testAnalysisPreRegistersAtMostThreeSupportedDecisionTimeHypotheses(): void
    {
        $rows = [];
        for ($index = 0; $index < 120; $index++) {
            $strong = $index < 60;
            $rows[] = [
                'trade_date' => (new \DateTimeImmutable('2024-01-02'))->modify('+'.$index.' days')->format('Y-m-d'),
                'ticker_id' => $index + 1,
                'ticker_code' => 'T'.($index + 1),
                'ret_net' => $strong ? 0.04 : -0.06,
                'score_total' => $strong ? 0.9 : 0.7,
                'signal_close_price' => $strong ? 1000 : 150,
                'signal_tick_risk_expansion_pct' => $strong ? 0.002 : 0.02,
                'dv20_idr' => 5000000000,
                'atr14_pct' => $strong ? 0.03 : 0.07,
                'vol_ratio' => $strong ? 1.7 : 4.0,
                'roc5' => $strong ? 0.03 : -0.02,
                'roc10' => $strong ? 0.05 : -0.01,
                'roc20' => $strong ? 0.08 : 0.02,
                'close_to_hh20_pct' => $strong ? 0.01 : 0.07,
                'range_position_20_pct' => $strong ? 0.9 : 0.5,
                'ma20_slope_pct' => $strong ? 0.02 : -0.02,
                'rs_20_vs_ihsg' => $strong ? 0.05 : -0.05,
                'market_index_roc20' => $strong ? 0.06 : -0.06,
                'market_index_ma20_slope_pct' => $strong ? 0.02 : -0.02,
                'entry_gap_pct' => $strong ? 0.005 : -0.03,
                'entry_price' => $strong ? 1000 : 145,
                'market_regime' => $strong ? 'STRONG' : 'WEAK',
                'momentum_persistence' => $strong ? 'PERSISTENT_POSITIVE' : 'MOMENTUM_COOLING',
                'exit_reason_code' => $strong
                    ? 'WATCHLIST_BACKTEST_EXIT_TARGET'
                    : 'WATCHLIST_BACKTEST_EXIT_STOP',
                'gap_detected' => ! $strong,
                'sector_code' => $strong ? 'I' : 'A',
            ];
        }

        $result = (new WeeklySwingNewStrategyR01ResearchDiagnosticService())->analyzeEvidence($rows);

        $this->assertSame('WS_NEW_STRATEGY_R01_SUPPORTED_HYPOTHESES_FOUND', $result['diagnostic_reason_code']);
        $this->assertCount(3, $result['research_hypotheses']);
        $this->assertCount(3, $result['candidate_design_allowed_hypotheses']);
        $this->assertSame([
            'H1_BREAKOUT_QUALITY_CONFIRMATION',
            'H2_MOMENTUM_PERSISTENCE',
            'H3_MARKET_REGIME_COMPATIBILITY',
        ], array_column($result['research_hypotheses'], 'hypothesis_code'));
        foreach ($result['candidate_design_allowed_hypotheses'] as $hypothesis) {
            $this->assertSame('SUPPORTED_FOR_MINIMAL_CANDIDATE_DESIGN', $hypothesis['diagnostic_status']);
            $this->assertFalse($hypothesis['future_return_as_selection_input']);
            $this->assertFalse($hypothesis['oos_used']);
            $this->assertFalse($hypothesis['canonical_gates_changed']);
        }
    }

    public function testAnalysisKeepsCanonicalQualityGatesUnchanged(): void
    {
        $row = [
            'trade_date' => '2024-01-02',
            'ticker_id' => 1,
            'ticker_code' => 'TEST',
            'ret_net' => -0.04,
            'vol_ratio' => 1.5,
            'roc20' => 0.05,
            'close_to_hh20_pct' => 0.01,
            'market_index_roc20' => -0.01,
            'market_regime' => 'WEAK',
            'momentum_persistence' => 'MOMENTUM_COOLING',
            'exit_reason_code' => 'WATCHLIST_BACKTEST_EXIT_STOP',
            'gap_detected' => false,
            'sector_code' => 'A',
        ];
        $result = (new WeeklySwingNewStrategyR01ResearchDiagnosticService())->analyzeEvidence([$row]);

        $this->assertFalse($result['canonical_gate_snapshot']['pass']);
        $this->assertSame(-0.03, $result['canonical_gate_snapshot']['thresholds']['min_p25_ret_net']);
        $this->assertSame(0.45, $result['canonical_gate_snapshot']['thresholds']['min_month_win_rate']);
        $this->assertSame(-0.01, $result['canonical_gate_snapshot']['thresholds']['min_month_avg_ret_net']);
        $this->assertCount(0, $result['candidate_design_allowed_hypotheses']);
    }

    public function testCommandAndServiceKeepR01SeparateFromC171RemediationAndOos(): void
    {
        $kernel = (string) file_get_contents(base_path('app/Console/Kernel.php'));
        $command = (string) file_get_contents(base_path('app/Console/Commands/Watchlist/RunWeeklySwingNewStrategyR01ResearchDiagnosticCommand.php'));
        $service = (string) file_get_contents(base_path('app/Application/Watchlist/Services/WeeklySwingNewStrategyR01ResearchDiagnosticService.php'));

        $this->assertStringContainsString('RunWeeklySwingNewStrategyR01ResearchDiagnosticCommand::class', $kernel);
        $this->assertStringContainsString('watchlist:weekly-swing-new-strategy-r01-diagnostic', $command);
        $this->assertStringContainsString('WS_NEW_STRATEGY_R01', $service);
        $this->assertStringContainsString("'c171_status' => 'CLOSED'", $service);
        $this->assertStringContainsString("'draft_paramset_created' => false", $service);
        $this->assertStringContainsString("'official_is_runtime_invoked' => false", $service);
        $this->assertStringContainsString("'oos_runtime_invoked' => false", $service);
        $this->assertStringContainsString("'paramset_promoted' => false", $service);
        $this->assertStringContainsString("'plan_run_created' => false", $service);
        $this->assertStringNotContainsString('watchlist_bt_oos_eval_ws', $service);
        $this->assertStringNotContainsString('WatchlistBacktestOosProofService', $service);
        $this->assertStringNotContainsString('WeeklySwingParamsetPromotionService', $service);
        $this->assertStringNotContainsString('persistDraft(', $service);
        $this->assertStringNotContainsString('WatchlistBacktestIsCalibrationService', $service);
    }
}
