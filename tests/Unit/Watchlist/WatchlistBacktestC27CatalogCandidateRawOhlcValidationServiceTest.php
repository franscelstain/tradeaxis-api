<?php

use App\Application\MarketData\Services\MarketDataPublishedEodSeriesReadService;
use App\Application\MarketData\Services\MarketDataTradingCalendarReadService;
use App\Application\Watchlist\Services\WatchlistBacktestC27CatalogCandidateRawOhlcValidationService;
use App\Application\Watchlist\Services\WatchlistBacktestParamGridParamsetFactory;
use App\Infrastructure\Persistence\Watchlist\WatchlistBacktestParamGridRepository;

class WatchlistBacktestC27CatalogCandidateRawOhlcValidationServiceTest extends TestCase
{
    public function test_it_generates_C27_raw_ohlc_validated_candidate_artifact_without_catalog_or_oos(): void
    {
        $c26Path = sys_get_temp_dir().'/c27-c26-source-test.json';
        $c21Path = sys_get_temp_dir().'/c27-c21-source-test.json';
        $outputPath = sys_get_temp_dir().'/c27-raw-ohlc-output-test.json';
        foreach ([$c26Path, $c21Path, $outputPath] as $file) {
            @unlink($file);
        }
        file_put_contents($c26Path, json_encode($this->c26Artifact(), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES)."\n");
        file_put_contents($c21Path, json_encode($this->c21Artifact(), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES)."\n");

        $service = new WatchlistBacktestC27CatalogCandidateRawOhlcValidationService(
            $this->calendar(),
            $this->priceSeries(),
            $this->paramGrid(),
            $this->paramsetFactory()
        );

        $result = $service->execute($c26Path, $outputPath, [
            'overwrite' => true,
            'input_c21_artifact' => $c21Path,
            'executed_at' => '2025-05-21T23:59:59+07:00',
        ]);

        $this->assertSame('PASS', $result['status']);
        $this->assertSame('WS_BT_C27_RAW_OHLC_VALIDATION_READY', $result['reason_code']);
        $this->assertSame('IS_ONLY_CATALOG_CANDIDATE_RAW_OHLC_VALIDATION', $result['scope']);
        $this->assertSame(2, $result['evaluated_picks_count']);
        $this->assertSame(2, $result['raw_ohlc_validated_count']);
        $this->assertSame(0, $result['raw_ohlc_missing_count']);
        $this->assertSame(1, $result['raw_ohlc_validation_pass']);
        $this->assertSame(1, $result['g21_raw_beats_r09']);
        $this->assertSame(1, $result['g21_raw_catalog_candidate_ready']);
        $this->assertSame(1, $result['c28_oos_proof_recommended']);
        $this->assertSame(0, $result['derived_mfe_mae_used_for_execution']);
        $this->assertSame(0, $result['oos_executed']);
        $this->assertSame(0, $result['production_ready']);

        $artifact = json_decode((string) file_get_contents($outputPath), true);
        $this->assertSame('C27_CATALOG_CANDIDATE_RAW_OHLC_VALIDATION', $artifact['artifact_type']);
        $this->assertSame('PASS', $artifact['status']);
        $this->assertSame('C27_RAW_OHLC_VALIDATED_CATALOG_CANDIDATE_READY_FOR_C28_OOS_PROOF', $artifact['decision']['decision_status']);
        $this->assertTrue($artifact['raw_ohlc_validation_summary']['raw_ohlc_validation_pass']);
        $this->assertSame(2, $artifact['raw_ohlc_validation_summary']['raw_ohlc_validated_count']);
        $this->assertFalse($artifact['raw_ohlc_validation_summary']['derived_mfe_mae_used_for_execution']);
        $this->assertTrue($artifact['candidate_readiness_summary']['derived_mfe_mae_dependency_removed']);
        $this->assertTrue($artifact['candidate_readiness_summary']['g21_raw_catalog_candidate_ready']);
        $this->assertTrue($artifact['candidate_readiness_summary']['c28_oos_proof_recommended']);
        $this->assertFalse($artifact['decision']['catalog_allowed']);
        $this->assertFalse($artifact['decision']['oos_allowed']);
        $this->assertSame('NOT_CREATED', $artifact['safety_boundaries']['C27_CATALOG_CODE']);
        $this->assertTrue($artifact['safety_boundaries']['NO_C01_TO_C26_MUTATION']);
        $this->assertTrue($artifact['safety_boundaries']['raw_ohlc_used_for_execution']);
        $this->assertFalse($artifact['safety_boundaries']['derived_mfe_mae_used_for_execution']);
        $this->assertFalse($artifact['safety_boundaries']['future_path_price_used_for_selection']);
        $this->assertSame('NEXT_OPEN', $artifact['price_evaluation_model']['ENTRY']);
        $this->assertSame('STOP_TP_OR_TIME', $artifact['price_evaluation_model']['EXIT']);

        $g21Rows = array_values(array_filter($artifact['pick_validation_rows'], function (array $row): bool {
            return ($row['profile_code'] ?? null) === 'C27_G05_RAW_C25_G21_PRIMARY_COMBO';
        }));
        $this->assertCount(2, $g21Rows);
        $this->assertTrue($g21Rows[0]['raw_ohlc_validated_flag']);
        $this->assertFalse($g21Rows[0]['derived_mfe_mae_used_for_execution']);
        $this->assertFalse($g21Rows[0]['future_path_price_used_for_selection']);
        $this->assertFalse($g21Rows[0]['profile_ret_net_used_for_selection']);

        foreach ([$c26Path, $c21Path, $outputPath] as $file) {
            @unlink($file);
        }
    }

    public function test_it_writes_blocked_artifact_when_C26_input_is_missing(): void
    {
        $outputPath = sys_get_temp_dir().'/c27-blocked-output.json';
        @unlink($outputPath);

        $result = (new WatchlistBacktestC27CatalogCandidateRawOhlcValidationService(
            $this->calendar(),
            $this->priceSeries(),
            $this->paramGrid(),
            $this->paramsetFactory()
        ))->execute(sys_get_temp_dir().'/missing-c27-c26.json', $outputPath, [
            'overwrite' => true,
        ]);

        $this->assertSame('BLOCKED', $result['status']);
        $this->assertSame('WS_BT_C27_C26_ARTIFACT_UNREADABLE', $result['reason_code']);
        $this->assertSame(1, $result['c27_catalog_implementation_deferred']);
        $this->assertSame('NOT_CREATED', $result['c27_catalog_code']);
        $this->assertSame(0, $result['oos_executed']);
        $this->assertSame(0, $result['production_ready']);

        $artifact = json_decode((string) file_get_contents($outputPath), true);
        $this->assertSame('C27_CATALOG_CANDIDATE_RAW_OHLC_VALIDATION', $artifact['artifact_type']);
        $this->assertSame('BLOCKED', $artifact['status']);
        $this->assertSame('C27_RAW_OHLC_VALIDATION_BLOCKED', $artifact['decision']['decision_status']);
        $this->assertFalse($artifact['decision']['catalog_allowed']);
        $this->assertFalse($artifact['decision']['oos_allowed']);

        @unlink($outputPath);
    }

    private function c26Artifact(): array
    {
        return [
            'artifact_type' => 'C26_CATALOG_CANDIDATE_DIAGNOSTIC',
            'status' => 'PASS',
            'artifact_hash' => 'abc-c26',
            'candidate_readiness_summary' => [
                'c27_catalog_candidate_implementation_recommended' => true,
            ],
            'pick_diagnostic_rows' => [
                $this->c26Row('2023-01-02', '2023-01-03', 'AAA', 101, 'next_open_delay_after_close_signal', 0.0040, 0.0060, 0.0095),
                $this->c26Row('2023-01-09', '2023-01-10', 'BBB', 102, 'no_rule_profit_signal_before_fallback', -0.0400, -0.0200, -0.0100),
            ],
        ];
    }

    private function c26Row(string $tradeDate, string $entryDate, string $ticker, int $paramId, string $bucket, float $r09, float $g13, float $g21): array
    {
        return [
            'trade_date' => $tradeDate,
            'ticker_id' => crc32($ticker) % 1000,
            'ticker' => $ticker,
            'param_id' => $paramId,
            'row_code' => 'TEST_'.$paramId,
            'entry_date' => $entryDate,
            'entry_price' => 100.0,
            'bucket_code' => $bucket,
            'bucket_reason' => 'fixture bucket',
            'profile_code' => WatchlistBacktestC27CatalogCandidateRawOhlcValidationService::C26_G21,
            'c23_r09_ret_net' => $r09,
            'c25_g13_ret_net' => $g13,
            'c25_g16_ret_net' => 0.0050,
            'c25_g21_ret_net' => $g21,
            'canonical_ret_net' => -0.0300,
            'missing_path_data_flag' => false,
        ];
    }

    private function c21Artifact(): array
    {
        return [
            'artifact_type' => 'C21_ENTRY_EXIT_BEHAVIOR_DIAGNOSTIC',
            'status' => 'PASS',
            'artifact_hash' => 'abc-c21',
            'pick_path_rows' => [
                [
                    'diagnostic_profile_code' => 'C21_P00_CANONICAL_PATH_BASELINE',
                    'trade_date' => '2023-01-02',
                    'ticker' => 'AAA',
                    'param_id' => 101,
                    'stop_trigger_price' => 95.0,
                    'target_trigger_price' => 110.0,
                ],
                [
                    'diagnostic_profile_code' => 'C21_P00_CANONICAL_PATH_BASELINE',
                    'trade_date' => '2023-01-09',
                    'ticker' => 'BBB',
                    'param_id' => 102,
                    'stop_trigger_price' => 95.0,
                    'target_trigger_price' => 110.0,
                ],
            ],
        ];
    }

    private function calendar(): MarketDataTradingCalendarReadService
    {
        return new class extends MarketDataTradingCalendarReadService {
            public function resolveTradingDates(string $fromDate, string $toDate): array
            {
                $dates = [
                    '2023-01-02', '2023-01-03', '2023-01-04', '2023-01-05', '2023-01-06', '2023-01-09',
                    '2023-01-10', '2023-01-11', '2023-01-12', '2023-01-13', '2023-01-16',
                ];
                return [
                    'is_ready' => true,
                    'trade_dates' => $dates,
                    'calendar_dates' => $dates,
                ];
            }
        };
    }

    private function priceSeries(): MarketDataPublishedEodSeriesReadService
    {
        return new class extends MarketDataPublishedEodSeriesReadService {
            public function readPublishedSeriesForDateTickerMap(string $fromDate, string $toDate, array $tickerCodesByTradeDate): array
            {
                $series = [
                    'AAA' => [
                        '2023-01-02' => $this->bar(100, 101, 99, 100),
                        '2023-01-03' => $this->bar(100, 101, 99, 100.5),
                        '2023-01-04' => $this->bar(101, 102, 100, 101),
                        '2023-01-05' => $this->bar(101, 102, 100, 101),
                        '2023-01-06' => $this->bar(101, 102, 100, 101),
                        '2023-01-09' => $this->bar(101, 102, 100, 102),
                    ],
                    'BBB' => [
                        '2023-01-09' => $this->bar(100, 101, 99, 100),
                        '2023-01-10' => $this->bar(100, 100, 98, 99),
                        '2023-01-11' => $this->bar(99, 100, 97, 98),
                        '2023-01-12' => $this->bar(99, 100, 97, 98.5),
                        '2023-01-13' => $this->bar(97, 99, 96, 96),
                        '2023-01-16' => $this->bar(96, 98, 95.5, 96),
                    ],
                ];
                return [
                    'is_ready' => true,
                    'reason_code' => 'READABLE_PUBLISHED_EOD_SERIES_RESOLVED',
                    'series_by_ticker' => $series,
                    'price_series_manifest' => [
                        'required_price_date_count' => count($tickerCodesByTradeDate),
                        'requested_ticker_date_pair_count' => array_sum(array_map('count', $tickerCodesByTradeDate)),
                        'missing_price_dates' => [],
                        'missing_price_rows' => [],
                        'exact_date_resolution_only' => true,
                        'no_latest_fallback' => true,
                    ],
                ];
            }

            private function bar(float $open, float $high, float $low, float $close): array
            {
                return [
                    'open' => $open,
                    'high' => $high,
                    'low' => $low,
                    'close' => $close,
                    'volume' => 1000000,
                    'published' => true,
                    'readable' => true,
                ];
            }
        };
    }

    private function paramGrid(): WatchlistBacktestParamGridRepository
    {
        return new class extends WatchlistBacktestParamGridRepository {
            public function allForCatalog(string $catalogCode, string $policyCode = 'WS'): array
            {
                return [
                    ['param_id' => 101, 'row_code' => 'TEST_101'],
                    ['param_id' => 102, 'row_code' => 'TEST_102'],
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
                    'backtest' => [
                        'notional_idr' => 10000000,
                        'lot_size' => 100,
                        'fee_buy_idr' => 2500,
                        'fee_sell_idr' => 2500,
                        'min_tradable_volume' => 1,
                    ],
                ];
            }
        };
    }
}
