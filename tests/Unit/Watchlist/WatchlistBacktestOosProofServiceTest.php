<?php

use App\Application\MarketData\Services\MarketDataTradingCalendarReadService;
use App\Application\Watchlist\Services\WatchlistBacktestIsCalibrationService;
use App\Application\Watchlist\Services\WatchlistBacktestOosProofService;
use App\Application\Watchlist\Services\WatchlistBacktestOosSplitService;
use App\Application\Watchlist\Services\WatchlistBacktestPublishedPriceRuntimeService;
use App\Application\Watchlist\Services\WatchlistBacktestRuntimeArtifactService;
use App\Infrastructure\Persistence\Watchlist\WatchlistBacktestOosEvaluationRepository;

class WatchlistBacktestOosProofServiceTest extends TestCase
{
    public function test_oos_proof_uses_frozen_best_is_binding_and_persists_official_record(): void
    {
        $path = sys_get_temp_dir().DIRECTORY_SEPARATOR.'wl-oos-proof-'.uniqid('', true).'.json';
        $dates = $this->dates();
        $binding = $this->binding();
        $service = $this->service($dates, $binding, $this->oosMetrics(50, 0.02, 0.01, 0.50, -0.02));

        $result = $service->execute('2026-01-01', '2026-01-10', $path);

        $this->assertTrue($result['is_ready']);
        $this->assertSame('LOCAL_OOS_PROOF_PASS', $result['reason_code']);
        $this->assertTrue($result['oos_acceptance']['pass']);
        $this->assertSame(7, $result['split']['is_trading_date_count']);
        $this->assertSame(3, $result['split']['oos_trading_date_count']);
        $this->assertSame(7, $result['best_is_binding']['param_id_best_is']);
        $this->assertSame(701, $result['persistence']['oos_id']);
        $this->assertTrue($result['artifact']['oos_evaluation']['binding_immutable']);
        $this->assertFalse($result['artifact']['oos_evaluation']['retuning_performed']);
        $this->assertTrue($result['artifact']['boundary']['no_promotion']);
        $this->assertFileExists($path);
        $this->assertSame($result['artifact_hash'], $result['artifact']['validation']['artifact_hash']);

        @unlink($path);
    }

    public function test_oos_acceptance_fail_exports_honest_evidence_and_remains_non_production_ready(): void
    {
        $path = sys_get_temp_dir().DIRECTORY_SEPARATOR.'wl-oos-fail-'.uniqid('', true).'.json';
        $service = $this->service($this->dates(), $this->binding(), $this->oosMetrics(39, 0.02, 0.01, 0.50, -0.02));

        $result = $service->execute('2026-01-01', '2026-01-10', $path);

        $this->assertTrue($result['is_ready']);
        $this->assertFalse($result['oos_acceptance']['pass']);
        $this->assertSame('WS_BT_OOS_WINDOW_INSUFFICIENT', $result['reason_code']);
        $this->assertContains('minimum_oos_trades', $result['oos_acceptance']['failed_gates']);
        $this->assertFileExists($path);
        $this->assertFalse($result['production_ready']);
        $this->assertTrue($result['artifact']['boundary']['no_paramset_status_mutation']);

        @unlink($path);
    }

    public function test_changing_only_oos_metrics_cannot_change_best_is_binding_or_is_hash(): void
    {
        $firstPath = sys_get_temp_dir().DIRECTORY_SEPARATOR.'wl-oos-leak-a-'.uniqid('', true).'.json';
        $secondPath = sys_get_temp_dir().DIRECTORY_SEPARATOR.'wl-oos-leak-b-'.uniqid('', true).'.json';
        $binding = $this->binding();

        $first = $this->service($this->dates(), $binding, $this->oosMetrics(50, 0.02, 0.01, 0.50, -0.02))
            ->execute('2026-01-01', '2026-01-10', $firstPath);
        $second = $this->service($this->dates(), $binding, $this->oosMetrics(50, -0.01, -0.01, 0.30, -0.05))
            ->execute('2026-01-01', '2026-01-10', $secondPath);

        $this->assertSame($first['best_is_binding']['param_id_best_is'], $second['best_is_binding']['param_id_best_is']);
        $this->assertSame($first['best_is_binding']['binding_hash'], $second['best_is_binding']['binding_hash']);
        $this->assertSame($first['best_is_binding']['is_metrics_hash'], $second['best_is_binding']['is_metrics_hash']);
        $this->assertSame(
            $first['artifact']['is_calibration']['param_grid_hash'],
            $second['artifact']['is_calibration']['param_grid_hash']
        );
        $this->assertNotSame($first['artifact_hash'], $second['artifact_hash']);
        $this->assertNotSame(
            $first['artifact']['oos_evaluation']['metrics'],
            $second['artifact']['oos_evaluation']['metrics']
        );

        @unlink($firstPath);
        @unlink($secondPath);
    }


    public function test_identical_rerun_keeps_canonical_hash_when_persistence_operation_becomes_idempotent(): void
    {
        $firstPath = sys_get_temp_dir().DIRECTORY_SEPARATOR.'wl-oos-rerun-a-'.uniqid('', true).'.json';
        $secondPath = sys_get_temp_dir().DIRECTORY_SEPARATOR.'wl-oos-rerun-b-'.uniqid('', true).'.json';
        $service = $this->service($this->dates(), $this->binding(), $this->oosMetrics(50, 0.02, 0.01, 0.50, -0.02));

        $first = $service->execute('2026-01-01', '2026-01-10', $firstPath, ['executed_at' => '2026-06-09T00:00:00+07:00']);
        $second = $service->execute('2026-01-01', '2026-01-10', $secondPath, ['executed_at' => '2026-06-09T00:00:00+07:00']);

        $this->assertSame('INSERTED', $first['persistence']['status']);
        $this->assertSame('IDEMPOTENT', $second['persistence']['status']);
        $this->assertSame($first['artifact_hash'], $second['artifact_hash']);
        $this->assertSame('PERSISTED', $first['artifact']['persistence_manifest']['oos_persistence_status']);
        $this->assertSame('PERSISTED', $second['artifact']['persistence_manifest']['oos_persistence_status']);

        @unlink($firstPath);
        @unlink($secondPath);
    }

    public function test_missing_required_oos_metric_fails_closed_without_persisting_zero_payload(): void
    {
        $path = sys_get_temp_dir().DIRECTORY_SEPARATOR.'wl-oos-missing-metric-'.uniqid('', true).'.json';
        $metrics = $this->oosMetrics(50, 0.02, 0.01, 0.50, -0.02);
        unset($metrics['win_rate_top']);
        $repository = null;
        $service = $this->service($this->dates(), $this->binding(), $metrics, $repository);

        $result = $service->execute('2026-01-01', '2026-01-10', $path);

        $this->assertFalse($result['is_ready']);
        $this->assertSame('WS_BT_OOS_PROOF_MISSING', $result['reason_code']);
        $this->assertCount(0, $repository->rows);
        $this->assertFileExists($path);
        $this->assertStringContainsString('win_rate_top', json_encode($result['diagnostics']));

        @unlink($path);
    }

    private function service(array $dates, array $binding, array $oosMetrics, ?WatchlistBacktestOosEvaluationRepository &$repositoryOut = null): WatchlistBacktestOosProofService
    {
        $oosDates = array_slice($dates, 7);
        $calendar = new class($dates) extends MarketDataTradingCalendarReadService {
            private array $dates;
            public function __construct(array $dates) { $this->dates = $dates; }
            public function resolveReplayWindow(string $fromDate, string $toDate, int $forwardTradingDays = 5): array
            {
                return [
                    'ready' => true,
                    'is_ready' => true,
                    'reason_code' => 'TRADING_CALENDAR_REPLAY_WINDOW_RESOLVED',
                    'trade_dates' => $this->dates,
                    'calendar_dates' => $this->dates,
                    'calendar_hash' => sha1(json_encode($this->dates)),
                    'diagnostics' => [],
                ];
            }
        };
        $calibration = new class($binding) extends WatchlistBacktestIsCalibrationService {
            private array $binding;
            public function __construct(array $binding) { $this->binding = $binding; }
            public function calibrate(array $isDates, array $options = []): array
            {
                return [
                    'ready' => true,
                    'is_ready' => true,
                    'status' => 'READY',
                    'reason_code' => null,
                    'param_grid_count' => 2,
                    'param_grid_hash' => sha1('grid'),
                    'is_valid_param_count' => 1,
                    'evaluations' => [[
                        'param_id' => $this->binding['param_id_best_is'],
                        'eval_id' => $this->binding['is_eval_id'],
                        'calibration_valid' => true,
                        'artifact_hash' => $this->binding['is_artifact_hash'],
                    ]],
                    'best_is_binding' => $this->binding,
                    'diagnostics' => [],
                ];
            }
        };
        $runtime = new class($oosMetrics, $oosDates) extends WatchlistBacktestPublishedPriceRuntimeService {
            private array $metrics;
            private array $dates;
            public array $receivedParamsets = [];
            public function __construct(array $metrics, array $dates) { $this->metrics = $metrics; $this->dates = $dates; }
            public function evaluateWindow(string $fromDate, string $toDate, array $options = []): array
            {
                $this->receivedParamsets[] = $options['paramset'];
                return [
                    'ready' => true,
                    'is_ready' => true,
                    'reason_code' => 'WATCHLIST_BACKTEST_RUNTIME_ARTIFACT_READY',
                    'artifact_hash' => sha1(json_encode($this->metrics)),
                    'calendar' => [
                        'trade_dates' => $this->dates,
                        'calendar_hash' => sha1(json_encode($this->dates)),
                    ],
                    'price_read' => [
                        'price_series_manifest' => ['source_payload_hash' => sha1('oos-prices'.json_encode($this->metrics))],
                        'publication_manifest' => [['publication_id' => 99]],
                    ],
                    'artifact' => [
                        'metrics' => ['canonical_eval_metrics' => $this->metrics],
                    ],
                    'diagnostics' => [],
                ];
            }
        };
        $repository = new class extends WatchlistBacktestOosEvaluationRepository {
            public array $rows = [];
            private int $calls = 0;
            public function persist(array $row): array
            {
                $this->rows[] = $row;
                $this->calls++;
                return [
                    'status' => $this->calls === 1 ? 'INSERTED' : 'IDEMPOTENT',
                    'oos_id' => 701,
                    'row' => $row,
                ];
            }
        };
        $repositoryOut = $repository;

        return new WatchlistBacktestOosProofService(
            $calendar,
            new WatchlistBacktestOosSplitService(),
            $calibration,
            $runtime,
            $repository,
            new WatchlistBacktestRuntimeArtifactService()
        );
    }

    private function binding(): array
    {
        $binding = [
            'param_id_best_is' => 7,
            'is_eval_id' => 501,
            'paramset_snapshot' => [
                'policy_code' => 'WS',
                'policy_version' => 'WS_EOD_RUNTIME',
                'paramset_code' => 'WS_BT_PARAM_7',
                'eval' => ['min_trades_oos' => ['value' => 40]],
                'backtest' => [
                    'holding_days' => 5,
                    'fee_model' => 'IDR_FIXED',
                    'slippage_entry_pct' => 0,
                ],
            ],
            'paramset_hash' => sha1('paramset-7'),
            'is_metrics' => ['picks_count' => 140, 'avg_ret_net_top' => 0.02],
            'is_metrics_hash' => sha1('is-metrics'),
            'is_artifact_hash' => sha1('is-artifact'),
            'eval_model' => 'ENTRY=NEXT_OPEN;EXIT=STOP_TP_OR_TIME;HOLD=5;FEE=IDR_FIXED;SLIP=0;GAP=OPEN;PX=IDX_BANDS',
            'is_from' => '2026-01-01',
            'is_to' => '2026-01-07',
            'is_trading_date_hash' => sha1('is-dates'),
            'calendar_hash' => sha1('is-calendar'),
            'price_payload_hash' => sha1('is-prices'),
            'publication_manifest_hash' => sha1('is-publications'),
            'ranking_policy' => ['avg_ret_net_top_desc', 'param_id_asc'],
        ];
        $binding['binding_hash'] = $this->stableHash($binding);

        return $binding;
    }

    private function dates(): array
    {
        return [
            '2026-01-01', '2026-01-02', '2026-01-03', '2026-01-04', '2026-01-05',
            '2026-01-06', '2026-01-07', '2026-01-08', '2026-01-09', '2026-01-10',
        ];
    }

    private function oosMetrics(int $count, float $avg, float $median, float $monthWin, float $p25): array
    {
        return [
            'picks_count' => $count,
            'days_covered' => 3,
            'avg_ret_net_top' => $avg,
            'win_rate_top' => 0.55,
            'median_ret_net_top' => $median,
            'p25_ret_net_top' => $p25,
            'month_win_rate_min' => $monthWin,
        ];
    }

    private function stableHash(array $payload): string
    {
        return sha1(json_encode($this->normalize($payload), JSON_UNESCAPED_SLASHES));
    }

    private function normalize($value)
    {
        if (! is_array($value)) {
            return $value;
        }
        if ($value === [] || array_keys($value) === range(0, count($value) - 1)) {
            return array_map(function ($item) { return $this->normalize($item); }, $value);
        }
        ksort($value, SORT_STRING);
        foreach ($value as $key => $item) {
            $value[$key] = $this->normalize($item);
        }
        return $value;
    }
}
