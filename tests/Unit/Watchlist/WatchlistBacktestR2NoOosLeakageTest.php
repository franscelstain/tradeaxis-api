<?php

use App\Application\Watchlist\Services\WatchlistBacktestIsCalibrationService;
use App\Application\Watchlist\Services\WatchlistBacktestPublishedPriceRuntimeService;
use App\Application\Watchlist\Services\WatchlistBacktestR2ParamGridCatalog;
use App\Infrastructure\Persistence\Watchlist\WatchlistBacktestEvaluationRepository;
use App\Infrastructure\Persistence\Watchlist\WatchlistBacktestParamGridRepository;

class WatchlistBacktestR2NoOosLeakageTest extends TestCase
{
    public function test_r2_calibration_passes_hard_is_boundary_and_post_is_mutation_cannot_change_result(): void
    {
        $dates = ['2025-05-19', '2025-05-20', '2025-05-21'];
        $row = WatchlistBacktestR2ParamGridCatalog::rows()[0];
        $row['param_id'] = 101;

        $firstRuntime = $this->runtime($dates, ['2025-05-22' => 100.0]);
        $secondRuntime = $this->runtime($dates, ['2025-05-22' => 999999.0, '2026-05-29' => -1.0]);
        $first = $this->service($firstRuntime, $row)->calibrate($dates, [
            'catalog_code' => WatchlistBacktestR2ParamGridCatalog::CATALOG_CODE,
            'executed_at' => '2025-05-21T23:59:59+07:00',
        ]);
        $second = $this->service($secondRuntime, $row)->calibrate($dates, [
            'catalog_code' => WatchlistBacktestR2ParamGridCatalog::CATALOG_CODE,
            'executed_at' => '2025-05-21T23:59:59+07:00',
        ]);

        $this->assertSame('2025-05-21', $firstRuntime->receivedOptions['hard_market_data_to_date']);
        $this->assertSame('2025-05-21', $secondRuntime->receivedOptions['hard_market_data_to_date']);
        $this->assertSame($first['is_trading_date_hash'], $second['is_trading_date_hash']);
        $this->assertSame($first['ordered_param_hashes'], $second['ordered_param_hashes']);
        $this->assertSame($first['evaluations'][0]['metrics'], $second['evaluations'][0]['metrics']);
        $this->assertSame($first['best_is_binding']['binding_hash'], $second['best_is_binding']['binding_hash']);
        $this->assertSame('2025-05-21', $first['evaluations'][0]['max_requested_market_data_date']);
        $this->assertTrue($first['evaluations'][0]['strict_is_boundary']);
    }

    public function test_r2_calibration_never_selects_best_of_failed(): void
    {
        $dates = ['2025-05-19', '2025-05-20', '2025-05-21'];
        $row = WatchlistBacktestR2ParamGridCatalog::rows()[0];
        $row['param_id'] = 101;
        $runtime = $this->runtime($dates, [], false);

        $result = $this->service($runtime, $row)->calibrate($dates, [
            'catalog_code' => WatchlistBacktestR2ParamGridCatalog::CATALOG_CODE,
        ]);

        $this->assertFalse($result['is_ready']);
        $this->assertSame('WS_BT_R2_NO_VALID_IS_CANDIDATE', $result['reason_code']);
        $this->assertSame(0, $result['is_valid_param_count']);
        $this->assertNull($result['best_is_binding']);
    }

    private function service(
        WatchlistBacktestPublishedPriceRuntimeService $runtime,
        array $row
    ): WatchlistBacktestIsCalibrationService {
        $grid = new class([$row]) extends WatchlistBacktestParamGridRepository {
            private array $rows;
            public function __construct(array $rows) { $this->rows = $rows; }
            public function allForCatalog(string $catalogCode, string $policyCode = 'WS'): array { return $this->rows; }
        };
        $evaluations = new class extends WatchlistBacktestEvaluationRepository {
            public function persist(array $row): array
            {
                return ['status' => 'IDEMPOTENT', 'eval_id' => 9001, 'row' => $row];
            }
        };

        return new WatchlistBacktestIsCalibrationService($runtime, $grid, $evaluations);
    }

    private function runtime(array $dates, array $postBoundaryPayload, bool $valid = true): WatchlistBacktestPublishedPriceRuntimeService
    {
        return new class($dates, $postBoundaryPayload, $valid) extends WatchlistBacktestPublishedPriceRuntimeService {
            private array $dates;
            private array $postBoundaryPayload;
            private bool $valid;
            public array $receivedOptions = [];

            public function __construct(array $dates, array $postBoundaryPayload, bool $valid)
            {
                $this->dates = $dates;
                $this->postBoundaryPayload = $postBoundaryPayload;
                $this->valid = $valid;
            }

            public function evaluateWindow(string $fromDate, string $toDate, array $options = []): array
            {
                $this->receivedOptions = $options;
                if (($options['hard_market_data_to_date'] ?? null) !== $toDate) {
                    throw new RuntimeException('strict boundary missing');
                }

                $metrics = [
                    'picks_count' => 130,
                    'days_covered' => 3,
                    'avg_ret_net_top' => 0.01,
                    'win_rate_top' => 0.55,
                    'median_ret_net_top' => 0.005,
                    'p25_ret_net_top' => -0.02,
                    'p75_ret_net_top' => 0.03,
                    'min_ret_net_top' => -0.05,
                    'max_ret_net_top' => 0.08,
                    'periods_count' => 1,
                    'period_fail_count' => $this->valid ? 0 : 1,
                    'month_win_rate_min' => $this->valid ? 0.50 : 0.10,
                    'month_avg_ret_net_min' => $this->valid ? 0.001 : -0.10,
                ];

                return [
                    'is_ready' => true,
                    'artifact_hash' => sha1('strict-is-artifact'),
                    'calendar' => [
                        'trade_dates' => $this->dates,
                        'calendar_hash' => sha1(json_encode($this->dates)),
                    ],
                    'price_read' => [
                        'price_series_manifest' => [
                            'source_payload_hash' => sha1('strict-is-prices'),
                            'read_mode' => 'TARGETED_DATE_TICKER_MAP',
                            'requested_ticker_date_pair_count' => 12,
                        ],
                        'publication_manifest' => [['publication_id' => 1]],
                    ],
                    'artifact' => [
                        'runtime_execution' => [
                            'strict_is_boundary' => true,
                            'hard_market_data_to_date' => $toDate,
                            'max_requested_market_data_date' => $toDate,
                            'strategy_trade_date_count' => 0,
                            'boundary_censored_trade_date_count' => 3,
                        ],
                        'metrics' => [
                            'canonical_eval_metrics' => $metrics,
                            'metric_sufficiency' => [
                                'required_fields_available' => true,
                                'thresholds_resolved' => true,
                                'calibration_valid' => $this->valid,
                                'gates' => [
                                    'minimum_trade_count' => true,
                                    'minimum_coverage' => true,
                                    'average_return_positive' => true,
                                    'median_return_non_negative' => true,
                                    'p25_downside_bound' => true,
                                    'monthly_win_rate_floor' => $this->valid,
                                    'monthly_average_floor' => $this->valid,
                                ],
                                'effective_thresholds' => ['min_trades' => 120],
                            ],
                            'evaluated_trades' => [],
                        ],
                    ],
                ];
            }
        };
    }
}
