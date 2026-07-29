<?php

namespace Tests\Unit\Watchlist;

use App\Application\Watchlist\Services\WeeklySwingC171TradeEvidenceDiagnosticService;
use TestCase;

class WeeklySwingC171TradeEvidenceDiagnosticTest extends TestCase
{
    public function testDiagnosticReproducesOfficialPickAndClassifiesStrategyFailureWithoutMutation(): void
    {
        $service = new WeeklySwingC171TradeEvidenceDiagnosticService();
        $eval = $this->failedEval();
        $official = [[
            'asof_eod_date' => '2024-01-02',
            'ticker_id' => 7,
            'ticker_code' => 'TEST',
            'ret_net' => -0.05,
            'score_total' => 0.91,
            'source_publication_id' => 10,
            'source_publication_version' => 2,
            'source_run_id' => 9,
        ]];
        $trades = [$this->trade()];
        $evaluations = [$this->evaluation(-0.05, 950.0)];

        $result = $service->analyzeTradeEvidence($eval, $official, $trades, $evaluations, $this->runtimeParamset());

        $this->assertTrue($result['official_pick_parity']['pass']);
        $this->assertSame('STRATEGY_QUALITY_FAILURE_CONFIRMED', $result['remediation_classification']);
        $this->assertSame(1, $result['reproduced_metrics']['trade_count']);
        $this->assertFalse($result['canonical_gates']['pass']);
        $this->assertCount(0, $result['anomaly_rows']);
    }

    public function testDiagnosticFlagsPriceDiscontinuityForMarketDataReview(): void
    {
        $service = new WeeklySwingC171TradeEvidenceDiagnosticService();
        $official = [[
            'asof_eod_date' => '2024-01-02', 'ticker_id' => 7, 'ticker_code' => 'TEST',
            'ret_net' => -0.8, 'score_total' => 0.91,
            'source_publication_id' => 10, 'source_publication_version' => 2, 'source_run_id' => 9,
        ]];
        $result = $service->analyzeTradeEvidence(
            $this->failedEval(),
            $official,
            [$this->trade()],
            [$this->evaluation(-0.8, 190.0)],
            $this->runtimeParamset()
        );

        $this->assertTrue($result['official_pick_parity']['pass']);
        $this->assertCount(1, $result['anomaly_rows']);
        $this->assertTrue($result['anomaly_rows'][0]['extreme_loss_flag']);
        $this->assertTrue($result['anomaly_rows'][0]['price_discontinuity_flag']);
        $this->assertTrue($result['anomaly_rows'][0]['requires_market_data_review']);
    }

    public function testDiagnosticBlocksParityWhenOfficialReturnDiffers(): void
    {
        $service = new WeeklySwingC171TradeEvidenceDiagnosticService();
        $official = [[
            'asof_eod_date' => '2024-01-02', 'ticker_id' => 7, 'ticker_code' => 'TEST',
            'ret_net' => -0.04, 'score_total' => 0.91,
            'source_publication_id' => 10, 'source_publication_version' => 2, 'source_run_id' => 9,
        ]];
        $result = $service->analyzeTradeEvidence(
            $this->failedEval(),
            $official,
            [$this->trade()],
            [$this->evaluation(-0.05, 950.0)],
            $this->runtimeParamset()
        );

        $this->assertFalse($result['official_pick_parity']['pass']);
        $this->assertGreaterThan(0, $result['official_pick_parity']['mismatch_count']);
    }

    public function testDiagnosticSegmentAxesCoverPlannedDecisionTimeFields(): void
    {
        $service = new WeeklySwingC171TradeEvidenceDiagnosticService();
        $official = [[
            'asof_eod_date' => '2024-01-02', 'ticker_id' => 7, 'ticker_code' => 'TEST',
            'ret_net' => 0.03, 'score_total' => 0.91,
            'source_publication_id' => 10, 'source_publication_version' => 2, 'source_run_id' => 9,
        ]];
        $result = $service->analyzeTradeEvidence(
            $this->failedEval(),
            $official,
            [$this->trade()],
            [$this->evaluation(0.03, 1030.0)],
            $this->runtimeParamset()
        );
        $axes = array_values(array_unique(array_column($result['segment_rows'], 'axis')));
        foreach (['month','ticker','exit_reason','gap_detected','entry_price_band','score_decile','dv20_band','atr14_band','vol_ratio_band','roc20_band','close_to_hh20_band','sector'] as $axis) {
            $this->assertContains($axis, $axes);
        }
    }

    private function failedEval(): array
    {
        return [
            'picks_count' => 1425,
            'days_covered' => 506,
            'avg_ret_net_top' => -0.002539,
            'win_rate_top' => 0.399298,
            'median_ret_net_top' => -0.049270,
            'p25_ret_net_top' => -0.082112,
            'p75_ret_net_top' => 0.092810,
            'min_ret_net_top' => -0.953989,
            'max_ret_net_top' => 0.342871,
            'month_win_rate_min' => 0.241379,
            'month_avg_ret_net_min' => -0.034529,
            'periods_count' => 27,
            'period_fail_count' => 20,
        ];
    }

    private function trade(): array
    {
        return [
            'trade_date' => '2024-01-02',
            'ticker_id' => 7,
            'ticker' => 'TEST',
            'bucket_code' => 'TOP_PICKS',
            'score_total' => 0.91,
            'score_momentum' => 0.8,
            'score_volume' => 0.7,
            'score_breakout' => 0.9,
            'score_risk' => 0.6,
            'dv20_idr' => 5000000000,
            'atr14_pct' => 0.05,
            'vol_ratio' => 1.8,
            'roc20' => 0.07,
            'close_to_hh20_pct' => -0.01,
            'sector_code' => 'TECH',
            'source_reference' => ['publication_id' => 10, 'publication_version' => 2, 'run_id' => 9],
        ];
    }

    private function evaluation(float $retNet, float $exitPrice): array
    {
        return [
            'trade_date' => '2024-01-02',
            'entry_trade_date' => '2024-01-03',
            'exit_trade_date' => '2024-01-08',
            'ticker_id' => 7,
            'ticker' => 'TEST',
            'bucket_code' => 'TOP_PICKS',
            'metrics_ready' => true,
            'exit_reason_code' => 'WATCHLIST_BACKTEST_EXIT_HOLD_EXPIRED',
            'fill_rule' => 'TIME_EXIT_AT_CLOSE',
            'gap_detected' => false,
            'entry_price' => 1000,
            'exit_price' => $exitPrice,
            'entry_volume' => 10000,
            'exit_volume' => 9000,
            'ret_net' => $retNet,
            'net_pnl_idr' => $retNet * 10000000,
            'entry_publication_id' => 10,
            'entry_publication_version' => 2,
            'entry_run_id' => 9,
        ];
    }

    private function runtimeParamset(): array
    {
        return ['eval' => [
            'min_trades' => 120,
            'min_days_covered' => 390,
            'min_p25_ret_net_top' => -0.03,
            'min_month_win_rate_min' => 0.45,
            'min_month_avg_ret_net_min' => -0.01,
        ]];
    }
}
