<?php

use App\Application\MarketData\Services\MarketDataBackfillService;
use App\Application\MarketData\Services\ReplayBackfillService;
use App\Application\MarketData\Services\ReplaySmokeSuiteService;
use App\Application\MarketData\Services\SessionSnapshotService;
use App\Console\Commands\MarketData\BackfillMarketDataCommand;
use App\Console\Commands\MarketData\CaptureSessionSnapshotCommand;
use App\Console\Commands\MarketData\PurgeSessionSnapshotCommand;
use App\Console\Commands\MarketData\ReplayBackfillCommand;
use App\Console\Commands\MarketData\ReplaySmokeSuiteCommand;
use Mockery as m;
use Symfony\Component\Console\Tester\CommandTester;

class OpsCommandSurfaceTest extends TestCase
{
    protected function tearDown(): void
    {
        m::close();
        parent::tearDown();
    }

    public function test_backfill_command_renders_summary_and_returns_success_when_all_cases_pass(): void
    {
        $service = m::mock(MarketDataBackfillService::class);
        $service->shouldReceive('execute')
            ->once()
            ->with('2026-03-17', '2026-03-18', 'manual_file', null, false)
            ->andReturn([
                'suite' => 'market_data_backfill_minimum',
                'range' => [
                    'start_date' => '2026-03-17',
                    'end_date' => '2026-03-18',
                ],
                'all_passed' => true,
                'output_dir' => '/tmp/backfill',
                'cases' => [
                    [
                        'requested_date' => '2026-03-17',
                        'status' => 'PASS',
                        'run_id' => 41,
                        'terminal_status' => 'SUCCESS',
                    ],
                    [
                        'requested_date' => '2026-03-18',
                        'status' => 'PASS',
                        'run_id' => 42,
                        'terminal_status' => 'SUCCESS',
                    ],
                ],
            ]);

        $this->app->instance(MarketDataBackfillService::class, $service);

        $command = new BackfillMarketDataCommand();
        $command->setLaravel($this->app);
        $tester = new CommandTester($command);

        $exitCode = $tester->execute([
            'start_date' => '2026-03-17',
            'end_date' => '2026-03-18',
            '--source_mode' => 'manual_file',
        ]);

        $display = $tester->getDisplay();

        $this->assertSame(0, $exitCode);
        $this->assertStringContainsString('suite=market_data_backfill_minimum', $display);
        $this->assertStringContainsString('start_date=2026-03-17', $display);
        $this->assertStringContainsString('end_date=2026-03-18', $display);
        $this->assertStringContainsString('all_passed=1', $display);
        $this->assertStringContainsString('requested_date=2026-03-17 | status=PASS | run_id=41 | terminal_status=SUCCESS', $display);
    }

    public function test_session_snapshot_capture_command_renders_summary(): void
    {
        $service = m::mock(SessionSnapshotService::class);
        $service->shouldReceive('capture')
            ->once()
            ->with('2026-03-17', 'PREOPEN', 'manual_file', 'storage/app/manual.csv', null)
            ->andReturn([
                'trade_date' => '2026-03-17',
                'snapshot_slot' => 'PREOPEN',
                'run_id' => 41,
                'scope_count' => 901,
                'captured_count' => 901,
                'skipped_count' => 0,
                'output_dir' => '/tmp/session-snapshot',
            ]);

        $this->app->instance(SessionSnapshotService::class, $service);

        $command = new CaptureSessionSnapshotCommand();
        $command->setLaravel($this->app);
        $tester = new CommandTester($command);

        $exitCode = $tester->execute([
            'trade_date' => '2026-03-17',
            'snapshot_slot' => 'PREOPEN',
            '--source_mode' => 'manual_file',
            '--input_file' => 'storage/app/manual.csv',
        ]);

        $display = $tester->getDisplay();

        $this->assertSame(0, $exitCode);
        $this->assertStringContainsString('trade_date=2026-03-17', $display);
        $this->assertStringContainsString('snapshot_slot=PREOPEN', $display);
        $this->assertStringContainsString('run_id=41', $display);
        $this->assertStringContainsString('scope_count=901', $display);
        $this->assertStringContainsString('captured_count=901', $display);
        $this->assertStringContainsString('skipped_count=0', $display);
    }

    public function test_session_snapshot_purge_command_renders_summary(): void
    {
        $service = m::mock(SessionSnapshotService::class);
        $service->shouldReceive('purge')
            ->once()
            ->with('2026-03-01', null)
            ->andReturn([
                'cutoff_timestamp' => '2026-03-01 00:00:00',
                'deleted_rows' => 250,
                'output_dir' => '/tmp/session-purge',
            ]);

        $this->app->instance(SessionSnapshotService::class, $service);

        $command = new PurgeSessionSnapshotCommand();
        $command->setLaravel($this->app);
        $tester = new CommandTester($command);

        $exitCode = $tester->execute([
            '--before_date' => '2026-03-01',
        ]);

        $display = $tester->getDisplay();

        $this->assertSame(0, $exitCode);
        $this->assertStringContainsString('cutoff_timestamp=2026-03-01 00:00:00', $display);
        $this->assertStringContainsString('deleted_rows=250', $display);
    }

    public function test_replay_smoke_command_renders_suite_summary_and_returns_success_when_all_cases_pass(): void
    {
        $service = m::mock(ReplaySmokeSuiteService::class);
        $service->shouldReceive('execute')
            ->once()
            ->with(41, null, null)
            ->andReturn([
                'suite' => 'replay_smoke_minimum',
                'run_id' => 41,
                'all_passed' => true,
                'output_dir' => '/tmp/replay-smoke',
                'cases' => [
                    [
                        'fixture_case' => 'valid_case',
                        'expected_outcome' => 'MATCH',
                        'observed_outcome' => 'MATCH',
                        'passed' => true,
                    ],
                    [
                        'fixture_case' => 'missing_file_case',
                        'expected_outcome' => 'ERROR',
                        'observed_outcome' => 'ERROR',
                        'passed' => true,
                    ],
                ],
            ]);

        $this->app->instance(ReplaySmokeSuiteService::class, $service);

        $command = new ReplaySmokeSuiteCommand();
        $command->setLaravel($this->app);
        $tester = new CommandTester($command);

        $exitCode = $tester->execute([
            'run_id' => 41,
        ]);

        $display = $tester->getDisplay();

        $this->assertSame(0, $exitCode);
        $this->assertStringContainsString('suite=replay_smoke_minimum', $display);
        $this->assertStringContainsString('run_id=41', $display);
        $this->assertStringContainsString('all_passed=1', $display);
        $this->assertStringContainsString('fixture_case=valid_case | expected=MATCH | observed=MATCH | passed=1', $display);
    }

    public function test_replay_backfill_command_returns_failure_and_renders_case_lines_when_any_case_fails(): void
    {
        $service = m::mock(ReplayBackfillService::class);
        $service->shouldReceive('execute')
            ->once()
            ->with('2026-03-17', '2026-03-18', 'valid_case', null, null, false)
            ->andReturn([
                'suite' => 'market_data_replay_backfill_minimum',
                'range' => [
                    'start_date' => '2026-03-17',
                    'end_date' => '2026-03-18',
                ],
                'fixture_case' => 'valid_case',
                'all_passed' => false,
                'output_dir' => '/tmp/replay-backfill',
                'cases' => [
                    [
                        'trade_date' => '2026-03-17',
                        'expected_outcome' => 'MATCH',
                        'observed_outcome' => 'MATCH',
                        'passed' => true,
                    ],
                    [
                        'trade_date' => '2026-03-18',
                        'expected_outcome' => 'MATCH',
                        'observed_outcome' => 'MISMATCH',
                        'passed' => false,
                    ],
                ],
            ]);

        $this->app->instance(ReplayBackfillService::class, $service);

        $command = new ReplayBackfillCommand();
        $command->setLaravel($this->app);
        $tester = new CommandTester($command);

        $exitCode = $tester->execute([
            'start_date' => '2026-03-17',
            'end_date' => '2026-03-18',
            '--fixture_case' => 'valid_case',
        ]);

        $display = $tester->getDisplay();

        $this->assertSame(1, $exitCode);
        $this->assertStringContainsString('suite=market_data_replay_backfill_minimum', $display);
        $this->assertStringContainsString('fixture_case=valid_case', $display);
        $this->assertStringContainsString('all_passed=0', $display);
        $this->assertStringContainsString('trade_date=2026-03-18 | expected=MATCH | observed=MISMATCH | passed=0', $display);
    }
}
