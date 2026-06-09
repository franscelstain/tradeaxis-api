<?php

use App\Application\Watchlist\Services\WatchlistBacktestPublishedPriceRuntimeService;
use App\Console\Commands\Watchlist\RunBacktestPublishedPriceProofCommand;
use Mockery as m;
use Symfony\Component\Console\Tester\CommandTester;

class WatchlistBacktestPublishedPriceProofCommandTest extends TestCase
{
    protected function tearDown(): void
    {
        m::close();
        parent::tearDown();
    }

    public function test_command_requires_explicit_valid_range_and_output_path(): void
    {
        $tester = $this->executeCommand([]);

        $this->assertSame(1, $tester->getStatusCode());
        $this->assertStringContainsString('status=BLOCKED', $tester->getDisplay());
        $this->assertStringContainsString('WATCHLIST_BACKTEST_CALENDAR_UNAVAILABLE', $tester->getDisplay());
    }

    public function test_command_rejects_directory_output_path_without_running_runtime(): void
    {
        $tester = $this->executeCommand([
            '--from' => '2026-05-19',
            '--to' => '2026-05-19',
            '--output' => sys_get_temp_dir(),
        ]);

        $this->assertSame(1, $tester->getStatusCode());
        $this->assertStringContainsString('WATCHLIST_BACKTEST_ARTIFACT_WRITE_PATH_INVALID', $tester->getDisplay());
    }

    public function test_command_renders_runtime_proof_summary_and_hash(): void
    {
        $output = sys_get_temp_dir().DIRECTORY_SEPARATOR.'wl-command-proof-'.uniqid('', true).'.json';
        $service = m::mock(WatchlistBacktestPublishedPriceRuntimeService::class);
        $service->shouldReceive('execute')->once()->with(
            '2026-05-19',
            '2026-05-19',
            $output,
            ['overwrite' => true]
        )->andReturn([
            'ready' => true,
            'is_ready' => true,
            'reason_code' => 'WATCHLIST_BACKTEST_RUNTIME_ARTIFACT_READY',
            'artifact' => [
                'metrics' => ['ready' => true],
                'validation' => ['artifact_hash' => str_repeat('a', 40)],
            ],
            'calendar' => [
                'coverage' => [
                    'replay_date_count' => 1,
                    'calendar_date_count' => 6,
                ],
            ],
            'price_read' => [
                'price_series_manifest' => [
                    'required_price_date_count' => 5,
                    'resolved_price_date_count' => 5,
                ],
            ],
            'write' => [
                'path' => $output,
                'sha1' => str_repeat('b', 40),
            ],
            'artifact_hash' => str_repeat('a', 40),
            'metrics_ready' => true,
            'metric_sufficiency_available' => true,
            'metric_thresholds_resolved' => true,
            'metric_calibration_valid' => false,
            'metric_gating_thresholds' => [
                'min_trades' => 120,
                'min_days_covered' => 1,
            ],
            'metric_coverage_threshold_rule' => 'CEIL_70_PERCENT_OF_TOTAL_TRADING_DAYS',
            'evaluated_trade_count' => 1,
            'diagnostic_count' => 0,
        ]);
        $this->app->instance(WatchlistBacktestPublishedPriceRuntimeService::class, $service);

        $tester = $this->executeCommand([
            '--from' => '2026-05-19',
            '--to' => '2026-05-19',
            '--output' => $output,
            '--overwrite' => true,
        ]);

        $this->assertSame(0, $tester->getStatusCode());
        $display = $tester->getDisplay();
        $this->assertStringContainsString('status=PASS', $display);
        $this->assertStringContainsString('artifact_hash='.str_repeat('a', 40), $display);
        $this->assertStringContainsString('evaluated_trade_count=1', $display);
        $this->assertStringContainsString('metrics_ready=1', $display);
        $this->assertStringContainsString('metric_thresholds_resolved=1', $display);
        $this->assertStringContainsString('metric_calibration_valid=0', $display);
        $this->assertStringContainsString('metric_min_trades=120', $display);
        $this->assertStringContainsString('metric_min_days_covered=1', $display);
        $this->assertStringContainsString('metric_coverage_threshold_rule=CEIL_70_PERCENT_OF_TOTAL_TRADING_DAYS', $display);
        $this->assertStringContainsString('production_ready=0', $display);
    }

    public function test_command_returns_non_zero_for_fatal_runtime_failure(): void
    {
        $output = sys_get_temp_dir().DIRECTORY_SEPARATOR.'wl-command-blocked-'.uniqid('', true).'.json';
        $service = m::mock(WatchlistBacktestPublishedPriceRuntimeService::class);
        $service->shouldReceive('execute')->once()->andReturn([
            'ready' => false,
            'is_ready' => false,
            'reason_code' => 'WATCHLIST_BACKTEST_PRICE_SERIES_UNAVAILABLE',
            'diagnostics' => [[
                'message' => 'Exact trade date has no official readable publication.',
            ]],
        ]);
        $this->app->instance(WatchlistBacktestPublishedPriceRuntimeService::class, $service);

        $tester = $this->executeCommand([
            '--from' => '2026-05-19',
            '--to' => '2026-05-19',
            '--output' => $output,
        ]);

        $this->assertSame(1, $tester->getStatusCode());
        $this->assertStringContainsString('status=BLOCKED', $tester->getDisplay());
        $this->assertStringContainsString('WATCHLIST_BACKTEST_PRICE_SERIES_UNAVAILABLE', $tester->getDisplay());
    }

    private function executeCommand(array $input): CommandTester
    {
        $command = new RunBacktestPublishedPriceProofCommand();
        $command->setLaravel($this->app);
        $tester = new CommandTester($command);
        $tester->execute($input);

        return $tester;
    }
}
