<?php

use App\Application\Watchlist\Services\WatchlistBacktestOosProofService;
use App\Console\Commands\Watchlist\RunBacktestOosProofCommand;
use Mockery as m;
use Symfony\Component\Console\Tester\CommandTester;

class WatchlistBacktestOosProofCommandTest extends TestCase
{
    protected function tearDown(): void
    {
        m::close();
        parent::tearDown();
    }

    public function test_command_requires_explicit_range_and_output(): void
    {
        $tester = $this->executeCommand([]);

        $this->assertSame(1, $tester->getStatusCode());
        $this->assertStringContainsString('status=BLOCKED', $tester->getDisplay());
        $this->assertStringContainsString('production_ready=0', $tester->getDisplay());
    }

    public function test_command_returns_zero_only_for_oos_acceptance_pass(): void
    {
        $output = sys_get_temp_dir().DIRECTORY_SEPARATOR.'wl-oos-command-pass-'.uniqid('', true).'.json';
        $service = m::mock(WatchlistBacktestOosProofService::class);
        $service->shouldReceive('execute')->once()->with(
            '2023-01-02',
            '2026-05-29',
            $output,
            ['overwrite' => true]
        )->andReturn($this->result(true, $output));
        $this->app->instance(WatchlistBacktestOosProofService::class, $service);

        $tester = $this->executeCommand([
            '--from' => '2023-01-02',
            '--to' => '2026-05-29',
            '--output' => $output,
            '--overwrite' => true,
        ]);

        $this->assertSame(0, $tester->getStatusCode());
        $display = $tester->getDisplay();
        $this->assertStringContainsString('status=PASS', $display);
        $this->assertStringContainsString('split_rule=FLOOR_70_PERCENT_IS_REMAINDER_OOS', $display);
        $this->assertStringContainsString('param_id_best_is=7', $display);
        $this->assertStringContainsString('oos_acceptance_pass=1', $display);
        $this->assertStringContainsString('production_ready=0', $display);
    }

    public function test_command_returns_non_zero_but_renders_evidence_for_oos_acceptance_fail(): void
    {
        $output = sys_get_temp_dir().DIRECTORY_SEPARATOR.'wl-oos-command-fail-'.uniqid('', true).'.json';
        $service = m::mock(WatchlistBacktestOosProofService::class);
        $service->shouldReceive('execute')->once()->andReturn($this->result(false, $output));
        $this->app->instance(WatchlistBacktestOosProofService::class, $service);

        $tester = $this->executeCommand([
            '--from' => '2023-01-02',
            '--to' => '2026-05-29',
            '--output' => $output,
        ]);

        $this->assertSame(1, $tester->getStatusCode());
        $display = $tester->getDisplay();
        $this->assertStringContainsString('status=OOS_ACCEPTANCE_FAIL', $display);
        $this->assertStringContainsString('failed_oos_gates=minimum_oos_trades', $display);
        $this->assertStringContainsString('artifact_hash='.str_repeat('a', 40), $display);
        $this->assertStringContainsString('production_ready=0', $display);
    }

    private function executeCommand(array $input): CommandTester
    {
        $command = new RunBacktestOosProofCommand();
        $command->setLaravel($this->app);
        $tester = new CommandTester($command);
        $tester->execute($input);

        return $tester;
    }

    private function result(bool $pass, string $output): array
    {
        return [
            'ready' => true,
            'is_ready' => true,
            'reason_code' => $pass ? 'LOCAL_OOS_PROOF_PASS' : 'WS_BT_OOS_WINDOW_INSUFFICIENT',
            'split' => [
                'split_rule' => 'FLOOR_70_PERCENT_IS_REMAINDER_OOS',
                'is_from' => '2023-01-02',
                'is_to' => '2025-05-01',
                'is_trading_date_count' => 580,
                'oos_from' => '2025-05-02',
                'oos_to' => '2026-05-29',
                'oos_trading_date_count' => 249,
            ],
            'calibration' => [
                'param_grid_count' => 5,
                'is_valid_param_count' => 2,
            ],
            'best_is_binding' => [
                'param_id_best_is' => 7,
                'is_eval_id' => 101,
                'is_metrics' => ['picks_count' => 140, 'days_covered' => 500],
            ],
            'oos_runtime' => [
                'artifact' => [
                    'metrics' => [
                        'canonical_eval_metrics' => ['picks_count' => $pass ? 50 : 39, 'days_covered' => 200],
                    ],
                ],
            ],
            'oos_acceptance' => [
                'pass' => $pass,
                'failed_gates' => $pass ? [] : ['minimum_oos_trades'],
            ],
            'persistence' => ['oos_id' => 201],
            'artifact_hash' => str_repeat('a', 40),
            'write' => ['path' => $output],
            'production_ready' => false,
        ];
    }
}
