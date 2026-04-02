<?php

use App\Application\MarketData\Services\MarketDataBackfillService;
use App\Application\MarketData\Services\MarketDataEvidenceExportService;
use App\Application\MarketData\Services\ReplayBackfillService;
use App\Application\MarketData\Services\ReplaySmokeSuiteService;
use App\Application\MarketData\Services\ReplayVerificationService;
use App\Application\MarketData\Services\SessionSnapshotService;
use App\Console\Commands\MarketData\BackfillMarketDataCommand;
use App\Console\Commands\MarketData\ExportEvidenceCommand;
use App\Console\Commands\MarketData\CaptureSessionSnapshotCommand;
use App\Console\Commands\MarketData\PurgeSessionSnapshotCommand;
use App\Console\Commands\MarketData\ReplayBackfillCommand;
use App\Console\Commands\MarketData\ReplaySmokeSuiteCommand;
use App\Console\Commands\MarketData\VerifyReplayCommand;
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


    public function test_evidence_export_command_requires_exactly_one_selector(): void
    {
        $command = new ExportEvidenceCommand();
        $command->setLaravel($this->app);
        $tester = new CommandTester($command);

        $exitCode = $tester->execute([]);

        $this->assertSame(1, $exitCode);
        $this->assertStringContainsString('Exactly one of --run_id, --correction_id, or --replay_id must be provided.', $tester->getDisplay());
    }

    public function test_evidence_export_command_rejects_ambiguous_selector_input(): void
    {
        $service = m::mock(MarketDataEvidenceExportService::class);
        $service->shouldNotReceive('exportRunEvidence');
        $service->shouldNotReceive('exportCorrectionEvidence');
        $service->shouldNotReceive('exportReplayEvidence');

        $this->app->instance(MarketDataEvidenceExportService::class, $service);

        $command = new ExportEvidenceCommand();
        $command->setLaravel($this->app);
        $tester = new CommandTester($command);

        $exitCode = $tester->execute([
            '--run_id' => 41,
            '--replay_id' => 3001,
        ]);

        $this->assertSame(1, $exitCode);
        $this->assertStringContainsString('Evidence export selector is ambiguous. Provide exactly one of --run_id, --correction_id, or --replay_id.', $tester->getDisplay());
    }

    public function test_evidence_export_command_exports_run_evidence(): void
    {
        $service = m::mock(MarketDataEvidenceExportService::class);
        $service->shouldReceive('exportRunEvidence')
            ->once()
            ->with(41, '/tmp/run-evidence')
            ->andReturn([
                'output_dir' => '/tmp/run-evidence',
                'files' => ['run_summary.json', 'evidence_pack.json'],
            ]);

        $this->app->instance(MarketDataEvidenceExportService::class, $service);

        $command = new ExportEvidenceCommand();
        $command->setLaravel($this->app);
        $tester = new CommandTester($command);

        $exitCode = $tester->execute([
            '--run_id' => 41,
            '--output_dir' => '/tmp/run-evidence',
        ]);

        $this->assertSame(0, $exitCode);
        $this->assertStringContainsString('output_dir=/tmp/run-evidence', $tester->getDisplay());
        $this->assertStringContainsString('files=run_summary.json,evidence_pack.json', $tester->getDisplay());
    }

    public function test_evidence_export_command_exports_correction_evidence(): void
    {
        $service = m::mock(MarketDataEvidenceExportService::class);
        $service->shouldReceive('exportCorrectionEvidence')
            ->once()
            ->with(25, '/tmp/correction-evidence')
            ->andReturn([
                'output_dir' => '/tmp/correction-evidence',
                'files' => ['correction_evidence.json'],
            ]);

        $this->app->instance(MarketDataEvidenceExportService::class, $service);

        $command = new ExportEvidenceCommand();
        $command->setLaravel($this->app);
        $tester = new CommandTester($command);

        $exitCode = $tester->execute([
            '--correction_id' => 25,
            '--output_dir' => '/tmp/correction-evidence',
        ]);

        $this->assertSame(0, $exitCode);
        $this->assertStringContainsString('output_dir=/tmp/correction-evidence', $tester->getDisplay());
        $this->assertStringContainsString('files=correction_evidence.json', $tester->getDisplay());
    }

    public function test_evidence_export_command_exports_replay_evidence(): void
    {
        $service = m::mock(MarketDataEvidenceExportService::class);
        $service->shouldReceive('exportReplayEvidence')
            ->once()
            ->with(3001, '2026-03-17', '/tmp/replay-evidence')
            ->andReturn([
                'output_dir' => '/tmp/replay-evidence',
                'files' => ['replay_result.json', 'replay_evidence_pack.json'],
            ]);

        $this->app->instance(MarketDataEvidenceExportService::class, $service);

        $command = new ExportEvidenceCommand();
        $command->setLaravel($this->app);
        $tester = new CommandTester($command);

        $exitCode = $tester->execute([
            '--replay_id' => 3001,
            '--trade_date' => '2026-03-17',
            '--output_dir' => '/tmp/replay-evidence',
        ]);

        $this->assertSame(0, $exitCode);
        $this->assertStringContainsString('output_dir=/tmp/replay-evidence', $tester->getDisplay());
        $this->assertStringContainsString('files=replay_result.json,replay_evidence_pack.json', $tester->getDisplay());
    }


    public function test_replay_verify_command_renders_result_and_exports_evidence_when_output_dir_requested(): void
    {
        $verification = m::mock(ReplayVerificationService::class);
        $verification->shouldReceive('verifyRunAgainstFixture')
            ->once()
            ->with(41, 'storage/app/market_data/replay-fixtures/valid_case', 3001)
            ->andReturn([
                'replay_id' => 3001,
                'trade_date' => '2026-03-17',
                'comparison_result' => 'MATCH',
                'comparison_note' => 'Replay verification matched fixture expectation.',
                'artifact_changed_scope' => 'none',
                'fixture_family' => 'market_data_replay_minimum',
            ]);

        $evidence = m::mock(MarketDataEvidenceExportService::class);
        $evidence->shouldReceive('exportReplayEvidence')
            ->once()
            ->with(3001, '2026-03-17', '/tmp/replay-verify')
            ->andReturn([
                'output_dir' => '/tmp/replay-verify',
                'files' => ['replay_result.json', 'replay_evidence_pack.json'],
            ]);

        $this->app->instance(ReplayVerificationService::class, $verification);
        $this->app->instance(MarketDataEvidenceExportService::class, $evidence);

        $command = new VerifyReplayCommand();
        $command->setLaravel($this->app);
        $tester = new CommandTester($command);

        $exitCode = $tester->execute([
            'run_id' => 41,
            'fixture_path' => 'storage/app/market_data/replay-fixtures/valid_case',
            '--replay_id' => 3001,
            '--output_dir' => '/tmp/replay-verify',
        ]);

        $display = $tester->getDisplay();

        $this->assertSame(0, $exitCode);
        $this->assertStringContainsString('replay_id=3001', $display);
        $this->assertStringContainsString('trade_date=2026-03-17', $display);
        $this->assertStringContainsString('comparison_result=MATCH', $display);
        $this->assertStringContainsString('artifact_changed_scope=none', $display);
        $this->assertStringContainsString('fixture_family=market_data_replay_minimum', $display);
        $this->assertStringContainsString('evidence_output_dir=/tmp/replay-verify', $display);
    }

    public function test_replay_verify_command_returns_failure_on_mismatch_without_forcing_evidence_export(): void
    {
        $verification = m::mock(ReplayVerificationService::class);
        $verification->shouldReceive('verifyRunAgainstFixture')
            ->once()
            ->with(41, 'storage/app/market_data/replay-fixtures/reason_code_mismatch_case', null)
            ->andReturn([
                'replay_id' => 3002,
                'trade_date' => '2026-03-17',
                'comparison_result' => 'MISMATCH',
                'comparison_note' => 'Replay verification diverged from fixture expectation.',
                'artifact_changed_scope' => 'bars_only',
                'fixture_family' => 'market_data_replay_minimum',
            ]);

        $evidence = m::mock(MarketDataEvidenceExportService::class);
        $evidence->shouldNotReceive('exportReplayEvidence');

        $this->app->instance(ReplayVerificationService::class, $verification);
        $this->app->instance(MarketDataEvidenceExportService::class, $evidence);

        $command = new VerifyReplayCommand();
        $command->setLaravel($this->app);
        $tester = new CommandTester($command);

        $exitCode = $tester->execute([
            'run_id' => 41,
            'fixture_path' => 'storage/app/market_data/replay-fixtures/reason_code_mismatch_case',
        ]);

        $display = $tester->getDisplay();

        $this->assertSame(1, $exitCode);
        $this->assertStringContainsString('replay_id=3002', $display);
        $this->assertStringContainsString('comparison_result=MISMATCH', $display);
        $this->assertStringContainsString('comparison_note=Replay verification diverged from fixture expectation.', $display);
        $this->assertStringContainsString('artifact_changed_scope=bars_only', $display);
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
