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
        $this->assertSame('DERIVED_FROM_RUNTIME_EVIDENCE', $artifact['short_term_momentum_roc5_bucket_summary']['status']);
        $this->assertSame('DERIVED_FROM_RUNTIME_EVIDENCE', $artifact['short_term_momentum_roc10_bucket_summary']['status']);
        $this->assertSame('DERIVED_FROM_RUNTIME_EVIDENCE', $artifact['range_position_20_bucket_summary']['status']);
        $this->assertSame('DERIVED_FROM_RUNTIME_EVIDENCE', $artifact['sector_roc20_bucket_summary']['status']);
        $this->assertSame('DERIVED_FROM_RUNTIME_EVIDENCE', $artifact['event_risk_flag_summary']['status']);
        $this->assertSame('AVAILABLE_IN_RUNTIME_EVIDENCE', $artifact['runtime_field_availability_summary']['score_components']['status']);
        $this->assertSame('AVAILABLE_IN_RUNTIME_EVIDENCE', $artifact['runtime_field_availability_summary']['roc5']['status']);
        $this->assertSame('AVAILABLE_IN_RUNTIME_EVIDENCE', $artifact['runtime_field_availability_summary']['corporate_action_flag']['status']);
        $this->assertSame('AVAILABLE_IN_RUNTIME_EVIDENCE', $artifact['runtime_field_availability_summary']['corporate_action_types']['status']);
        $this->assertSame('AVAILABLE_IN_RUNTIME_EVIDENCE', $artifact['runtime_field_availability_summary']['trading_status_code']['status']);
        $this->assertSame('AVAILABLE_IN_RUNTIME_EVIDENCE', $artifact['runtime_field_availability_summary']['event_risk_reasons']['status']);
        $this->assertSame('DERIVED_FROM_RUNTIME_EVIDENCE', $artifact['event_risk_flag_summary']['fields']['corporate_action_types']['status']);
        $this->assertArrayHasKey('bo_near_below_pct_hit_miss', $artifact['breakout_extension_bucket_summary']);
        $this->assertArrayHasKey('directional_finding', $artifact['score_component_effectiveness_summary']['components']['score_momentum']);

        @unlink($output);
    }

    public function test_it_supports_param_id_scoped_non_c01_drilldown_without_oos_claim(): void
    {
        $output = sys_get_temp_dir().DIRECTORY_SEPARATOR.'watchlist-c07-drilldown-param-filter-test.json';
        @unlink($output);

        $service = new WatchlistBacktestIsFailureDrilldownService(
            $this->runtime(true),
            $this->gridRepository([
                $this->gridRow(1, '00_TEST_A', 'C07', 'hash-c07'),
                $this->gridRow(2, '01_TEST_B', 'C07', 'hash-c07'),
            ]),
            $this->paramsetFactory()
        );

        $result = $service->execute(
            'WS_BT_GRID_DOWNSIDE_STABILITY_C07_2026_06',
            '2023-01-02',
            '2025-05-21',
            $output,
            ['overwrite' => true, 'param_id' => 2]
        );

        $this->assertTrue($result['is_ready']);
        $this->assertSame('WS_BT_IS_FAILURE_DRILLDOWN_READY', $result['reason_code']);
        $this->assertSame('IS_ONLY_CATALOG_FAILURE_DRILLDOWN', $result['artifact']['meta']['scope']);
        $this->assertSame(1, $result['artifact']['catalog_count']);
        $this->assertSame(2, $result['artifact']['row_filter']['param_id']);
        $this->assertSame(2, $result['artifact']['per_param_status'][0]['param_id']);
        $this->assertSame('01_TEST_B', $result['artifact']['per_param_status'][0]['row_code']);
        $this->assertTrue($result['artifact']['validation']['row_filter_applied']);
        $this->assertFalse($result['artifact']['no_oos_leakage_summary']['oos_executed']);
        $this->assertFalse($result['artifact']['meta']['production_ready']);

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
                                    'roc5' => 0.025,
                                    'roc10' => 0.035,
                                    'close_to_ll20_pct' => 0.08,
                                    'range_20_pct' => 0.12,
                                    'range_position_20_pct' => 0.68,
                                    'sector_roc20' => 0.03,
                                    'rs_20_vs_sector' => 0.02,
                                    'sector_rs_20_vs_ihsg' => 0.01,
                                    'corporate_action_types' => 'DIVIDEND',
                                    'trading_status_code' => 'ACTIVE',
                                    'event_risk_flag' => 0,
                                    'event_risk_reasons' => 'CORPORATE_ACTION:DIVIDEND',
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
                                    'roc5' => -0.005,
                                    'roc10' => 0.015,
                                    'close_to_ll20_pct' => 0.21,
                                    'range_20_pct' => 0.18,
                                    'range_position_20_pct' => 0.91,
                                    'sector_roc20' => -0.01,
                                    'rs_20_vs_sector' => -0.02,
                                    'sector_rs_20_vs_ihsg' => 0.04,
                                    'corporate_action_types' => null,
                                    'trading_status_code' => 'ACTIVE',
                                    'event_risk_flag' => 0,
                                    'event_risk_reasons' => null,
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
                                    'roc5' => 0.025,
                                    'roc10' => 0.035,
                                    'close_to_ll20_pct' => 0.08,
                                    'range_20_pct' => 0.12,
                                    'range_position_20_pct' => 0.68,
                                    'sector_roc20' => 0.03,
                                    'rs_20_vs_sector' => 0.02,
                                    'sector_rs_20_vs_ihsg' => 0.01,
                                    'corporate_action_types' => 'DIVIDEND',
                                    'trading_status_code' => 'ACTIVE',
                                    'event_risk_flag' => 0,
                                    'event_risk_reasons' => 'CORPORATE_ACTION:DIVIDEND',
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
                                    'roc5' => -0.005,
                                    'roc10' => 0.015,
                                    'close_to_ll20_pct' => 0.21,
                                    'range_20_pct' => 0.18,
                                    'range_position_20_pct' => 0.91,
                                    'sector_roc20' => -0.01,
                                    'rs_20_vs_sector' => -0.02,
                                    'sector_rs_20_vs_ihsg' => 0.04,
                                    'corporate_action_types' => null,
                                    'trading_status_code' => 'ACTIVE',
                                    'event_risk_flag' => 0,
                                    'event_risk_reasons' => null,
                                ],
                            ] : []),
                        ],
                    ],
                ];
            }
        };
    }

    private function gridRepository(array $rows = null): WatchlistBacktestParamGridRepository
    {
        if ($rows === null) {
            return $this->defaultGridRepository();
        }

        return new class($rows) extends WatchlistBacktestParamGridRepository {
            private array $rows;

            public function __construct(array $rows)
            {
                $this->rows = $rows;
            }

            public function allForCatalog(string $catalogCode, string $policyCode = 'WS'): array
            {
                return array_map(function (array $row) use ($catalogCode): array {
                    $row['catalog_code'] = $catalogCode;
                    $row['row_hash'] = sha1($catalogCode.'|'.$row['row_code']);

                    return $row;
                }, $this->rows);
            }
        };
    }

    private function defaultGridRepository(): WatchlistBacktestParamGridRepository
    {
        return new class extends WatchlistBacktestParamGridRepository {
            public function allForCatalog(string $catalogCode, string $policyCode = 'WS'): array
            {
                return [
                    WatchlistBacktestIsFailureDrilldownServiceTest::gridRow(1, '00_TEST', 'C01', '604ac98f6f193a4c317d4f25582deada84682846', $catalogCode),
                ];
            }
        };
    }

    public static function gridRow(int $paramId, string $rowCode, string $catalogVersion, string $catalogHash, string $catalogCode = 'WS_BT_GRID_TEST'): array
    {
        return [
            'param_id' => $paramId,
            'policy_code' => 'WS',
            'catalog_code' => $catalogCode,
            'catalog_version' => $catalogVersion,
            'catalog_hash' => $catalogHash,
            'row_code' => $rowCode,
            'row_hash' => sha1($catalogCode.'|'.$rowCode),
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
        ];
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
