<?php

use App\Application\Watchlist\Services\WatchlistBacktestIsCalibrationService;
use App\Application\Watchlist\Services\WatchlistBacktestPublishedPriceRuntimeService;
use App\Infrastructure\Persistence\Watchlist\WatchlistBacktestEvaluationRepository;
use App\Infrastructure\Persistence\Watchlist\WatchlistBacktestParamGridRepository;

class WatchlistBacktestIsCalibrationServiceTest extends TestCase
{
    public function test_calibration_uses_only_is_window_and_canonical_ranking_with_param_id_final_tie_break(): void
    {
        $dates = ['2026-01-01', '2026-01-02', '2026-01-03'];
        $runtime = $this->runtime([
            2 => $this->metrics(0.02, 0.01, 0.50, -0.02),
            1 => $this->metrics(0.02, 0.01, 0.50, -0.02),
        ], $dates);
        $service = new WatchlistBacktestIsCalibrationService(
            $runtime,
            $this->gridRepository([$this->gridRow(2), $this->gridRow(1)]),
            $this->evaluationRepository()
        );

        $result = $service->calibrate($dates);

        $this->assertTrue($result['is_ready']);
        $this->assertSame(2, $result['param_grid_count']);
        $this->assertSame(2, $result['is_valid_param_count']);
        $this->assertSame(1, $result['best_is_binding']['param_id_best_is']);
        $this->assertSame($dates, $runtime->receivedDatesByParam[1]);
        $this->assertSame($dates, $runtime->receivedDatesByParam[2]);
        $this->assertSame([
            'avg_ret_net_top_desc',
            'median_ret_net_top_desc',
            'month_win_rate_min_desc',
            'p25_ret_net_top_desc',
            'param_id_asc',
        ], $result['best_is_binding']['ranking_policy']);
    }

    public function test_calibration_does_not_select_best_of_failed_candidates(): void
    {
        $dates = ['2026-01-01', '2026-01-02'];
        $runtime = $this->runtime([
            1 => $this->metrics(-0.01, -0.01, 0.40, -0.04, false),
        ], $dates);
        $service = new WatchlistBacktestIsCalibrationService(
            $runtime,
            $this->gridRepository([$this->gridRow(1)]),
            $this->evaluationRepository()
        );

        $result = $service->calibrate($dates);

        $this->assertFalse($result['is_ready']);
        $this->assertSame('WS_BT_OOS_PROOF_MISSING', $result['reason_code']);
        $this->assertSame(0, $result['is_valid_param_count']);
        $this->assertSame(1, $result['is_failed_param_count']);
        $this->assertSame(130, $result['is_max_picks_count']);
        $this->assertSame(3, $result['is_max_days_covered']);
        $this->assertContains('WS_BT_EVAL_ROBUST_RETURN_FAIL', $result['is_failure_reason_codes']);
        $this->assertSame(2, $result['evaluations'][0]['trade_evidence']['evaluated_trade_count']);
        $this->assertSame(-0.05, $result['evaluations'][0]['trade_evidence']['worst_trades'][0]['ret_net']);
        $this->assertSame(0.10, $result['evaluations'][0]['trade_evidence']['best_trades'][0]['ret_net']);
        $this->assertNull($result['best_is_binding']);
    }

    public function test_grid_row_is_bound_to_runtime_paramset_without_fixture_source(): void
    {
        $dates = ['2026-01-01', '2026-01-02'];
        $runtime = $this->runtime([1 => $this->metrics(0.02, 0.01, 0.50, -0.02)], $dates);
        $service = new WatchlistBacktestIsCalibrationService(
            $runtime,
            $this->gridRepository([$this->gridRow(1)]),
            $this->evaluationRepository()
        );

        $result = $service->calibrate($dates);
        $snapshot = $result['best_is_binding']['paramset_snapshot'];

        $this->assertSame(1, $snapshot['bt_grid']['param_id']);
        $this->assertSame(1500000000.0, $snapshot['liquidity']['min_dv20_idr']);
        $this->assertSame(0.11, $snapshot['risk']['max_atr14_pct']);
        $this->assertSame(1.25, $snapshot['risk']['stop_atr_mult']);
        $this->assertSame(2.0, $snapshot['risk']['min_rr']);
        $this->assertSame(0.28, $snapshot['scoring']['weights']['momentum']);
        $this->assertSame(0.80, $snapshot['grouping']['top_min_score_q']);
        $this->assertSame(4, $snapshot['grouping']['top_picks']['max_items']);
    }

    private function runtime(array $metricsByParam, array $dates): WatchlistBacktestPublishedPriceRuntimeService
    {
        return new class($metricsByParam, $dates) extends WatchlistBacktestPublishedPriceRuntimeService {
            private array $metricsByParam;
            private array $dates;
            public array $receivedDatesByParam = [];

            public function __construct(array $metricsByParam, array $dates)
            {
                $this->metricsByParam = $metricsByParam;
                $this->dates = $dates;
            }

            public function evaluateWindow(string $fromDate, string $toDate, array $options = []): array
            {
                $paramset = $options['paramset'];
                $paramId = (int) $paramset['bt_grid']['param_id'];
                $this->receivedDatesByParam[$paramId] = $this->dates;
                $metrics = $this->metricsByParam[$paramId];

                return [
                    'is_ready' => true,
                    'artifact_hash' => sha1('artifact-'.$paramId),
                    'calendar' => [
                        'trade_dates' => $this->dates,
                        'calendar_hash' => sha1(json_encode($this->dates)),
                    ],
                    'price_read' => [
                        'price_series_manifest' => [
                            'source_payload_hash' => sha1('prices-'.$paramId),
                            'read_mode' => 'TARGETED_DATE_TICKER_MAP',
                            'requested_ticker_date_pair_count' => 10,
                        ],
                        'publication_manifest' => [['param_id' => $paramId]],
                    ],
                    'artifact' => [
                        'metrics' => [
                            'canonical_eval_metrics' => $metrics['values'],
                            'metric_sufficiency' => [
                                'required_fields_available' => true,
                                'thresholds_resolved' => true,
                                'calibration_valid' => $metrics['valid'],
                                'gates' => $metrics['gates'],
                                'effective_thresholds' => ['min_trades' => 120],
                            ],
                            'evaluated_trades' => [
                                [
                                    'metrics_ready' => true,
                                    'trade_date' => '2026-01-01',
                                    'ticker_id' => 1,
                                    'ticker' => 'AAA',
                                    'ret_net' => -0.05,
                                    'is_win' => false,
                                ],
                                [
                                    'metrics_ready' => true,
                                    'trade_date' => '2026-01-02',
                                    'ticker_id' => 2,
                                    'ticker' => 'BBB',
                                    'ret_net' => 0.10,
                                    'is_win' => true,
                                ],
                            ],
                        ],
                    ],
                ];
            }
        };
    }

    private function gridRepository(array $rows): WatchlistBacktestParamGridRepository
    {
        return new class($rows) extends WatchlistBacktestParamGridRepository {
            private array $rows;
            public function __construct(array $rows) { $this->rows = $rows; }
            public function allForPolicy(string $policyCode = 'WS'): array { return $this->rows; }
        };
    }

    private function evaluationRepository(): WatchlistBacktestEvaluationRepository
    {
        return new class extends WatchlistBacktestEvaluationRepository {
            private int $nextId = 100;
            public function persist(array $row): array
            {
                return ['status' => 'INSERTED', 'eval_id' => $this->nextId++, 'row' => $row];
            }
        };
    }

    private function gridRow(int $paramId): array
    {
        return [
            'param_id' => $paramId,
            'policy_code' => 'WS',
            'min_dv20_idr' => 1500000000,
            'max_atr14_pct' => 0.11,
            'min_vol_ratio' => 1.3,
            'w_momentum' => 0.28,
            'w_volume' => 0.22,
            'w_breakout' => 0.30,
            'w_risk' => 0.20,
            'stop_atr_mult' => 1.25,
            'min_rr' => 2.0,
            'top_picks_target' => 4,
            'secondary_target' => 8,
            'top_min_score_q' => 0.80,
            'secondary_min_score_q' => 0.60,
            'notes' => null,
        ];
    }

    private function metrics(float $avg, float $median, float $monthWin, float $p25, bool $valid = true): array
    {
        return [
            'valid' => $valid,
            'gates' => [
                'minimum_trade_count' => true,
                'minimum_coverage' => true,
                'average_return_positive' => $avg > 0,
                'median_return_non_negative' => $median >= 0,
                'p25_downside_bound' => $p25 >= -0.03,
                'monthly_win_rate_floor' => $monthWin >= 0.45,
                'monthly_average_floor' => $valid,
            ],
            'values' => [
                'picks_count' => 130,
                'days_covered' => 3,
                'avg_ret_net_top' => $avg,
                'win_rate_top' => 0.55,
                'median_ret_net_top' => $median,
                'p25_ret_net_top' => $p25,
                'p75_ret_net_top' => 0.04,
                'min_ret_net_top' => -0.05,
                'max_ret_net_top' => 0.10,
                'month_win_rate_min' => $monthWin,
                'month_avg_ret_net_min' => 0.001,
                'periods_count' => 2,
                'period_fail_count' => 0,
            ],
        ];
    }
}
