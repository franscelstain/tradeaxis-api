<?php

use App\Application\MarketData\Services\FullRangeCurrentEvidenceReplayService;
use App\Console\Commands\MarketData\FullRangeCurrentEvidenceReplayCommand;
use Mockery as m;
use Symfony\Component\Console\Tester\CommandTester;

class FullRangeCurrentEvidenceReplayCommandTest extends TestCase
{
    protected function tearDown(): void
    {
        m::close();

        parent::tearDown();
    }

    public function test_command_renders_full_range_current_summary(): void
    {
        $service = m::mock(FullRangeCurrentEvidenceReplayService::class);
        $service->shouldReceive('execute')->once()->with('2026-03-18', '2026-03-19', m::on(function ($options) {
            return $options['fixture_case'] === 'valid_case'
                && $options['output_dir'] === 'C:\\tmp\\full-range'
                && $options['continue_on_error'] === true
                && $options['max_dates'] === '2';
        }))->andReturn([
            'suite' => 'market_data_full_range_current_evidence_replay',
            'start_date' => '2026-03-18',
            'end_date' => '2026-03-19',
            'fixture_case' => 'valid_case',
            'trading_date_count' => 2,
            'processed_count' => 2,
            'success_count' => 2,
            'failed_count' => 0,
            'error_count' => 0,
            'all_passed' => true,
            'output_dir' => 'C:\\tmp\\full-range',
            'cases' => [
                [
                    'trade_date' => '2026-03-18',
                    'status' => 'SUCCESS',
                    'passed' => true,
                    'run_id' => 123,
                    'publication_id' => 23,
                    'replay_id' => 1123,
                    'comparison_result' => 'MATCH',
                    'replay_status' => 'PASS',
                    'mismatch_count' => 0,
                    'run_evidence_admission_state' => 'ADMITTED_COMPLETE',
                    'run_evidence_completeness_state' => 'COMPLETE',
                    'replay_evidence_admission_state' => 'ADMITTED_COMPLETE',
                ],
            ],
        ]);

        $this->app->instance(FullRangeCurrentEvidenceReplayService::class, $service);

        $command = new FullRangeCurrentEvidenceReplayCommand();
        $command->setLaravel($this->app);
        $tester = new CommandTester($command);

        $exitCode = $tester->execute([
            'start_date' => '2026-03-18',
            'end_date' => '2026-03-19',
            '--output_dir' => 'C:\\tmp\\full-range',
            '--continue_on_error' => true,
            '--max_dates' => '2',
        ]);

        $display = $tester->getDisplay();

        $this->assertSame(0, $exitCode);
        $this->assertStringContainsString('suite=market_data_full_range_current_evidence_replay', $display);
        $this->assertStringContainsString('all_passed=1', $display);
        $this->assertStringContainsString('trade_date=2026-03-18 | status=SUCCESS | passed=1 | run_id=123 | publication_id=23 | replay_id=1123 | comparison_result=MATCH | replay_status=PASS | mismatch_count=0', $display);
        $this->assertStringContainsString('run_evidence=ADMITTED_COMPLETE/COMPLETE', $display);
        $this->assertStringContainsString('replay_evidence=ADMITTED_COMPLETE', $display);
    }
}
