<?php

use App\Application\Watchlist\Services\WatchlistBacktestIsFailureDrilldownService;
use App\Application\Watchlist\Services\WatchlistBacktestParamGridParamsetFactory;
use App\Application\Watchlist\Services\WatchlistBacktestPublishedPriceRuntimeService;
use App\Infrastructure\Persistence\Watchlist\WatchlistBacktestParamGridRepository;

class WatchlistBacktestIsFailureDrilldownServiceTest extends TestCase
{
    public function test_it_builds_deterministic_is_only_drilldown_artifact_without_oos_claim(): void
    {
        $output = sys_get_temp_dir().DIRECTORY_SEPARATOR.'watchlist-c01-drilldown-test.json';
        @unlink($output);

        $runtime = $this->runtime();
        $service = new WatchlistBacktestIsFailureDrilldownService(
            $runtime,
            $this->gridRepository(),
            $this->paramsetFactory()
        );

        $result1 = $service->execute(
            'WS_BT_GRID_DOWNSIDE_STABILITY_C01_2026_06',
            '2023-01-02',
            '2025-05-21',
            $output,
            ['overwrite' => true]
        );
        $sha1Run1 = sha1_file($output);
        $hash1 = $result1['artifact_hash'];

        $result2 = $service->execute(
            'WS_BT_GRID_DOWNSIDE_STABILITY_C01_2026_06',
            '2023-01-02',
            '2025-05-21',
            $output,
            ['overwrite' => true]
        );
        $sha1Run2 = sha1_file($output);

        $this->assertTrue($result1['is_ready']);
        $this->assertTrue($result2['is_ready']);
        $this->assertSame($hash1, $result2['artifact_hash']);
        $this->assertSame($sha1Run1, $sha1Run2);
        $this->assertSame($result1['artifact']['artifact_hash'], $result1['artifact']['canonical_artifact_hash']);
        $this->assertNotEmpty($result1['artifact']['is_trading_date_hash']);
        $this->assertSame('2025-05-21', $result1['artifact']['no_oos_leakage_summary']['max_allowed_market_data_date']);
        $this->assertSame('2025-05-21', $result1['artifact']['no_oos_leakage_summary']['max_requested_market_data_date']);
        $this->assertFalse($result1['artifact']['no_oos_leakage_summary']['oos_service_invoked']);
        $this->assertFalse($result1['artifact']['no_oos_leakage_summary']['oos_repository_invoked']);
        $this->assertFalse($result1['artifact']['meta']['production_ready']);
        $this->assertSame('NEXT_CATALOG_NOT_DESIGNED', $result1['artifact']['next_focus_recommendation']['decision']);
        $this->assertSame('AAA', $result1['artifact']['ticker_loss_cluster_summary'][0]['bucket']);
        $this->assertSame('2024-02', $result1['artifact']['month_failure_cluster_summary'][0]['bucket']);
        $this->assertSame('FIELD_NOT_AVAILABLE_IN_RUNTIME_EVIDENCE', $result1['artifact']['volume_ratio_bucket_summary']['status']);
        $this->assertSame('FIELD_NOT_AVAILABLE_IN_RUNTIME_EVIDENCE', $result1['artifact']['sector_bucket_summary']['status']);
        $this->assertSame('FIELD_NOT_AVAILABLE_IN_RUNTIME_EVIDENCE', $result1['artifact']['score_component_effectiveness_summary']['status']);
        $this->assertArrayHasKey('runtime_consumed_parameter_summary', $result1['artifact']);
        $this->assertSame(
            'RUNTIME_CONSUMED_BY_PARAMSET_FACTORY_AND_WS_SERVICES',
            $result1['artifact']['runtime_consumed_parameter_summary']['parameters']['setup.bo_max_ext_pct']['status']
        );

        @unlink($output);
    }

    public function test_it_derives_feature_buckets_when_runtime_evidence_exports_fields(): void
    {
        $output = sys_get_temp_dir().DIRECTORY_SEPARATOR.'watchlist-c01-drilldown-feature-test.json';
        @unlink($output);

        $service = new WatchlistBacktestIsFailureDrilldownService(
            $this->runtime(true),
            $this->gridRepository(),
            $this->paramsetFactory()
        );

        $result = $service->execute(
            'WS_BT_GRID_DOWNSIDE_STABILITY_C01_2026_06',
            '2023-01-02',
            '2025-05-21',
            $output,
            ['overwrite' => true]
        );

        $artifact = $result['artifact'];
        $this->assertTrue($result['is_ready']);
        $this->assertSame('DERIVED_FROM_RUNTIME_EVIDENCE', $artifact['breakout_extension_bucket_summary']['status']);
        $this->assertSame('DERIVED_FROM_RUNTIME_EVIDENCE', $artifact['momentum_roc_bucket_summary']['status']);
        $this->assertSame('DERIVED_FROM_RUNTIME_EVIDENCE', $artifact['volume_ratio_bucket_summary']['status']);
        $this->assertSame('DERIVED_FROM_RUNTIME_EVIDENCE', $artifact['liquidity_dv20_bucket_summary']['status']);
        $this->assertSame('DERIVED_FROM_RUNTIME_EVIDENCE', $artifact['sector_bucket_summary']['status']);
        $this->assertSame('DERIVED_FROM_RUNTIME_EVIDENCE', $artifact['score_component_effectiveness_summary']['status']);
        $this->assertSame('AVAILABLE_IN_RUNTIME_EVIDENCE', $artifact['runtime_field_availability_summary']['score_components']['status']);
        $this->assertArrayHasKey('bo_near_below_pct_hit_miss', $artifact['breakout_extension_bucket_summary']);
        $this->assertArrayHasKey('directional_finding', $artifact['score_component_effectiveness_summary']['components']['score_momentum']);

        @unlink($output);
    }

    public function test_it_blocks_non_is_window_before_runtime_execution(): void
    {
        $runtime = $this->runtime();
        $service = new WatchlistBacktestIsFailureDrilldownService(
            $runtime,
            $this->gridRepository(),
            $this->paramsetFactory()
        );

        $result = $service->execute(
            'WS_BT_GRID_DOWNSIDE_STABILITY_C01_2026_06',
            '2023-01-02',
            '2025-05-22',
            sys_get_temp_dir().DIRECTORY_SEPARATOR.'watchlist-c01-drilldown-blocked.json',
            ['overwrite' => true]
        );

        $this->assertFalse($result['is_ready']);
        $this->assertSame('WS_BT_C01_IS_BOUNDARY_VIOLATION', $result['reason_code']);
        $this->assertSame([], $runtime->calls);
    }

    private function runtime(bool $includeFeatureFields = false): WatchlistBacktestPublishedPriceRuntimeService
    {
        return new class($includeFeatureFields) extends WatchlistBacktestPublishedPriceRuntimeService {
            public array $calls = [];
            private bool $includeFeatureFields;

            public function __construct(bool $includeFeatureFields)
            {
                $this->includeFeatureFields = $includeFeatureFields;
            }

            public function evaluateWindow(string $fromDate, string $toDate, array $options = []): array
            {
                $paramId = (int) ($options['paramset']['bt_grid']['param_id'] ?? 0);
                $this->calls[] = [$fromDate, $toDate, $paramId, $options['hard_market_data_to_date'] ?? null];

                return [
                    'is_ready' => true,
                    'artifact_hash' => sha1('artifact-'.$paramId),
                    'calendar' => ['trade_dates' => ['2023-01-02', '2023-01-03']],
                    'price_read' => ['price_series_manifest' => ['requested_to_date' => '2025-05-21']],
                    'artifact' => [
                        'runtime_execution' => [
                            'strict_is_boundary' => true,
                            'max_requested_market_data_date' => '2025-05-21',
                        ],
                        'metrics' => [
                            'canonical_eval_metrics' => [
                                'picks_count' => 130 + $paramId,
                                'days_covered' => 508,
                                'avg_ret_net_top' => -0.002 * $paramId,
                                'median_ret_net_top' => -0.01,
                                'p25_ret_net_top' => -0.04,
                                'month_win_rate_min' => 0.20,
                                'month_avg_ret_net_min' => -0.02,
                                'period_fail_count' => 20,
                            ],
                            'metric_sufficiency' => [
                                'calibration_valid' => false,
                                'gates' => [
                                    'average_return_positive' => false,
                                    'median_return_non_negative' => false,
                                    'p25_downside_bound' => false,
                                    'monthly_win_rate_floor' => false,
                                    'monthly_average_floor' => false,
                                ],
                                'effective_thresholds' => [
                                    'min_p25_ret_net_top' => -0.03,
                                    'min_month_win_rate_min' => 0.45,
                                    'min_month_avg_ret_net_min' => -0.01,
                                ],
                            ],
                            'evaluated_trades' => [
                                array_merge([
                                    'metrics_ready' => true,
                                    'trade_date' => '2024-02-01',
                                    'ticker_id' => 1,
                                    'ticker' => 'AAA',
                                    'bucket_code' => 'TOP',
                                    'atr14_pct' => 0.031,
                                    'ret_net' => -0.05,
                                ], $this->includeFeatureFields ? [
                                    'close_to_hh20_pct' => -0.006,
                                    'roc20' => 0.041,
                                    'vol_ratio' => 1.7,
                                    'dv20_idr' => 8000000000,
                                    'sector_code' => 'FIN',
                                    'score_components' => [
                                        'score_momentum' => 0.72,
                                        'score_breakout' => 0.83,
                                        'score_volume' => 0.65,
                                        'score_risk' => 0.55,
                                    ],
                                ] : []),
                                array_merge([
                                    'metrics_ready' => true,
                                    'trade_date' => '2024-03-01',
                                    'ticker_id' => 2,
                                    'ticker' => 'BBB',
                                    'bucket_code' => 'SECONDARY',
                                    'atr14_pct' => 0.052,
                                    'ret_net' => 0.04,
                                ], $this->includeFeatureFields ? [
                                    'close_to_hh20_pct' => 0.031,
                                    'roc20' => 0.085,
                                    'vol_ratio' => 2.4,
                                    'dv20_idr' => 12000000000,
                                    'sector_code' => 'IDXTECH',
                                    'score_components' => [
                                        'score_momentum' => 0.91,
                                        'score_breakout' => 0.74,
                                        'score_volume' => 0.88,
                                        'score_risk' => 0.49,
                                    ],
                                ] : []),
                            ],
                        ],
                        'trades' => [
                            array_merge([
                                'trade_date' => '2024-02-01',
                                'ticker' => 'AAA',
                                'recommendation_score' => 0.91,
                            ], $this->includeFeatureFields ? [
                                'score_metrics' => [
                                    'close_to_hh20_pct' => -0.006,
                                    'roc20' => 0.041,
                                    'vol_ratio' => 1.7,
                                    'dv20_idr' => 8000000000,
                                    'sector_code' => 'FIN',
                                ],
                            ] : []),
                            array_merge([
                                'trade_date' => '2024-03-01',
                                'ticker' => 'BBB',
                                'recommendation_score' => 0.72,
                            ], $this->includeFeatureFields ? [
                                'score_metrics' => [
                                    'close_to_hh20_pct' => 0.031,
                                    'roc20' => 0.085,
                                    'vol_ratio' => 2.4,
                                    'dv20_idr' => 12000000000,
                                    'sector_code' => 'IDXTECH',
                                ],
                            ] : []),
                        ],
                    ],
                ];
            }
        };
    }

    private function gridRepository(): WatchlistBacktestParamGridRepository
    {
        return new class extends WatchlistBacktestParamGridRepository {
            public function allForCatalog(string $catalogCode, string $policyCode = 'WS'): array
            {
                return [
                    [
                        'param_id' => 1,
                        'policy_code' => 'WS',
                        'catalog_code' => $catalogCode,
                        'catalog_version' => 'C01',
                        'catalog_hash' => '604ac98f6f193a4c317d4f25582deada84682846',
                        'row_code' => '00_TEST',
                        'row_hash' => sha1($catalogCode.'|00_TEST'),
                        'min_dv20_idr' => 2500000000,
                        'dv20_strong_idr' => 5000000000,
                        'min_vol_ratio' => 1.5,
                        'strong_vol_ratio' => 2.0,
                        'min_atr14_pct' => 0.02,
                        'max_atr14_pct' => 0.05,
                        'atr_ideal_low' => 0.025,
                        'atr_ideal_high' => 0.04,
                        'roc_lo' => 0.02,
                        'roc_hi' => 0.15,
                        'mom_roc20_soft_min' => 0.0,
                        'bo_near_below_pct' => 0.02,
                        'bo_max_ext_pct' => 0.05,
                        'w_momentum' => 0.25,
                        'w_volume' => 0.25,
                        'w_breakout' => 0.25,
                        'w_risk' => 0.25,
                        'top_min_score_q' => 0.80,
                        'secondary_min_score_q' => 0.65,
                    ],
                ];
            }
        };
    }

    private function paramsetFactory(): WatchlistBacktestParamGridParamsetFactory
    {
        return new class extends WatchlistBacktestParamGridParamsetFactory {
            public function make(array $row): array
            {
                return [
                    'policy_code' => 'WS',
                    'policy_version' => 'WS_EOD_RUNTIME',
                    'paramset_code' => 'TEST',
                    'bt_grid' => $row,
                    'risk' => ['stop_atr_mult' => 1.5, 'min_rr' => 1.5],
                    'backtest' => ['holding_days' => 5],
                ];
            }
        };
    }
}
