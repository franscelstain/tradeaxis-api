<?php

use App\Application\MarketData\DTOs\MarketDataStageInput;
use App\Application\MarketData\Services\MarketDataBackfillService;
use App\Application\MarketData\Services\MarketDataEvidenceExportService;
use App\Application\MarketData\Services\MarketDataPipelineService;
use App\Application\MarketData\Services\ReplayBackfillService;
use App\Application\MarketData\Services\ReplaySmokeSuiteService;
use App\Application\MarketData\Services\ReplayVerificationService;
use App\Application\MarketData\Services\SessionSnapshotService;
use App\Infrastructure\Persistence\MarketData\EodEvidenceRepository;
use App\Infrastructure\Persistence\MarketData\EodRunRepository;
use App\Console\Commands\MarketData\BackfillMarketDataCommand;
use App\Console\Commands\MarketData\ExportEvidenceCommand;
use App\Console\Commands\MarketData\CaptureSessionSnapshotCommand;
use App\Console\Commands\MarketData\PurgeSessionSnapshotCommand;
use App\Console\Commands\MarketData\ReplayBackfillCommand;
use App\Console\Commands\MarketData\ReplaySmokeSuiteCommand;
use App\Console\Commands\MarketData\VerifyReplayCommand;
use Mockery as m;
use Symfony\Component\Console\Tester\CommandTester;
use Illuminate\Support\Facades\File;

class OpsCommandSurfaceTest extends TestCase
{
    protected function tearDown(): void
    {
        m::close();
        parent::tearDown();
    }

    public function test_backfill_command_propagates_operator_options_and_renders_import_context(): void
    {
        $service = m::mock(MarketDataBackfillService::class);
        $service->shouldReceive('execute')
            ->once()
            ->with('2026-03-17', '2026-03-18', 'manual_file', 'C:\\tmp\\backfill', true)
            ->andReturn([
                'suite' => 'market_data_backfill_minimum',
                'range' => [
                    'start_date' => '2026-03-17',
                    'end_date' => '2026-03-18',
                ],
                'source_mode' => 'manual_file',
                'all_imported' => true,
                'all_passed' => true,
                'output_dir' => 'C:\\tmp\\backfill',
                'source_attempt_telemetry_artifact' => 'C:\\tmp\\backfill\\source_attempt_telemetry.json',
                'cases' => [
                    [
                        'requested_date' => '2026-03-17',
                        'status' => 'IMPORTED',
                        'import_status' => 'IMPORTED',
                        'run_id' => 41,
                        'import_stage_reached' => 'INGEST_BARS',
                        'import_bars_rows_written' => 901,
                        'import_invalid_bar_count' => 0,
                        'source_name' => 'API_FREE',
                        'source_attempt_event_type' => 'STAGE_COMPLETED',
                        'source_attempt_count' => 2,
                        'source_summary' => 'provider=generic | timeout_seconds=15 | retry_max=3 | attempt_count=2 | success_after_retry=yes | final_http_status=200 | final_reason_code=RUN_SOURCE_TIMEOUT',
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
            '--output_dir' => 'C:\\tmp\\backfill',
            '--continue_on_error' => true,
        ]);

        $display = $tester->getDisplay();

        $this->assertSame(0, $exitCode);
        $this->assertStringContainsString('suite=market_data_backfill_minimum', $display);
        $this->assertStringContainsString('start_date=2026-03-17', $display);
        $this->assertStringContainsString('end_date=2026-03-18', $display);
        $this->assertStringContainsString('source_mode=manual_file', $display);
        $this->assertStringContainsString('all_imported=1', $display);
        $this->assertStringContainsString('all_passed=1', $display);
        $this->assertStringContainsString('output_dir=C:/tmp/backfill', $display);
        $this->assertStringContainsString('source_attempt_telemetry_artifact=C:/tmp/backfill/source_attempt_telemetry.json', $display);
        $this->assertStringContainsString('requested_date=2026-03-17 | status=IMPORTED | import_status=IMPORTED | run_id=41 | import_stage_reached=INGEST_BARS | import_bars_rows_written=901 | import_invalid_bar_count=0 | source_name=API_FREE | source_attempt_event_type=STAGE_COMPLETED | source_attempt_count=2 | source_summary=provider=generic | timeout_seconds=15 | retry_max=3 | attempt_count=2 | success_after_retry=yes | final_http_status=200 | final_reason_code=RUN_SOURCE_TIMEOUT', $display);
    }

    public function test_backfill_command_returns_failure_and_renders_error_case_lines(): void
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
                'source_mode' => 'manual_file',
                'all_imported' => false,
                'all_passed' => false,
                'output_dir' => '/tmp/backfill',
                'cases' => [
                    [
                        'requested_date' => '2026-03-17',
                        'status' => 'IMPORTED',
                        'import_status' => 'IMPORTED',
                        'run_id' => 41,
                        'import_stage_reached' => 'INGEST_BARS',
                        'import_bars_rows_written' => 5,
                        'import_invalid_bar_count' => 0,
                        'source_name' => 'LOCAL_FILE',
                        'source_input_file' => 'C:\\ops\\manual-2026-03-17.csv',
                    ],
                    [
                        'requested_date' => '2026-03-18',
                        'status' => 'ERROR',
                        'error_class' => 'RuntimeException',
                        'error_message' => 'Backfill requires at least one trading date in market_calendar for the requested range.',
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

        $this->assertSame(1, $exitCode);
        $this->assertStringContainsString('source_mode=manual_file', $display);
        $this->assertStringContainsString('all_imported=0', $display);
        $this->assertStringContainsString('all_passed=0', $display);
        $this->assertStringContainsString('requested_date=2026-03-17 | status=IMPORTED | import_status=IMPORTED | run_id=41 | import_stage_reached=INGEST_BARS | import_bars_rows_written=5 | import_invalid_bar_count=0 | source_name=LOCAL_FILE | source_input_file=C:/ops/manual-2026-03-17.csv', $display);
        $this->assertStringContainsString('requested_date=2026-03-18 | status=ERROR | error=Backfill requires at least one trading date in market_calendar for the requested range.', $display);
    }


    public function test_backfill_command_propagates_manual_input_file_override_without_leaking_config(): void
    {
        config()->set('market_data.source.local_input_file', null);

        $service = m::mock(MarketDataBackfillService::class);
        $service->shouldReceive('execute')
            ->once()
            ->with('2026-04-14', '2026-04-14', 'manual_file', 'C:\tmp\backfill', false)
            ->andReturn([
                'suite' => 'market_data_backfill_minimum',
                'range' => [
                    'start_date' => '2026-04-14',
                    'end_date' => '2026-04-14',
                ],
                'source_mode' => 'manual_file',
                'request_mode' => 'single_day',
                'all_passed' => false,
                'output_dir' => 'C:\tmp\backfill',
                'cases' => [
                    [
                        'requested_date' => '2026-04-14',
                        'status' => 'FAIL',
                        'import_status' => 'IMPORT_FAILED',
                        'run_id' => 76,
                        'source_name' => 'LOCAL_FILE',
                        'source_input_file' => 'C:\ops\manual-2026-04-14.csv',
                        'source_summary' => 'final_reason_code=RUN_SOURCE_MALFORMED_PAYLOAD',
                    ],
                ],
            ]);

        $this->app->instance(MarketDataBackfillService::class, $service);

        $command = new BackfillMarketDataCommand();
        $command->setLaravel($this->app);
        $tester = new CommandTester($command);

        $exitCode = $tester->execute([
            'start_date' => '2026-04-14',
            'end_date' => '2026-04-14',
            '--source_mode' => 'manual_file',
            '--input_file' => 'storage\app\market_data\operator\manual-2026-04-14.csv',
            '--output_dir' => 'C:\tmp\backfill',
        ]);

        $display = $tester->getDisplay();

        $this->assertSame(1, $exitCode);
        $this->assertNull(config('market_data.source.local_input_file'));
        $this->assertStringContainsString('input_file=storage/app/market_data/operator/manual-2026-04-14.csv', $display);
        $this->assertStringContainsString('source_name=LOCAL_FILE', $display);
        $this->assertStringContainsString('source_input_file=C:/ops/manual-2026-04-14.csv', $display);
    }

    public function test_session_snapshot_capture_command_renders_summary(): void
    {
        $service = m::mock(SessionSnapshotService::class);
        $service->shouldReceive('capture')
            ->once()
            ->with('2026-03-17', 'PREOPEN', 'manual_file', 'storage/app/manual.csv', null)
            ->andReturn([
                'trade_date' => '2026-03-17',
                'trade_date_effective' => '2026-03-17',
                'snapshot_slot' => 'PREOPEN',
                'publication_id' => 55,
                'run_id' => 41,
                'scope_count' => 901,
                'captured_count' => 901,
                'skipped_count' => 0,
                'slot_anchor_time' => '09:10:00',
                'slot_tolerance_minutes' => 3,
                'slot_miss_count' => 0,
                'output_dir' => 'C:\\tmp\\session-snapshot',
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
        $this->assertStringContainsString('trade_date_effective=2026-03-17', $display);
        $this->assertStringContainsString('publication_id=55', $display);
        $this->assertStringContainsString('run_id=41', $display);
        $this->assertStringContainsString('scope_count=901', $display);
        $this->assertStringContainsString('captured_count=901', $display);
        $this->assertStringContainsString('skipped_count=0', $display);
        $this->assertStringContainsString('slot_anchor_time=09:10:00', $display);
        $this->assertStringContainsString('slot_tolerance_minutes=3', $display);
        $this->assertStringContainsString('slot_miss_count=0', $display);
        $this->assertStringContainsString('output_dir=C:/tmp/session-snapshot', $display);
    }

    public function test_session_snapshot_purge_command_renders_summary(): void
    {
        $service = m::mock(SessionSnapshotService::class);
        $service->shouldReceive('purge')
            ->once()
            ->with('2026-03-01', null)
            ->andReturn([
                'cutoff_timestamp' => '2026-03-01 23:59:59',
                'cutoff_source' => 'explicit_before_date',
                'before_date' => '2026-03-01',
                'retention_days' => null,
                'deleted_rows' => 250,
                'output_dir' => 'C:\\tmp\\session-purge',
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
        $this->assertStringContainsString('cutoff_timestamp=2026-03-01 23:59:59', $display);
        $this->assertStringContainsString('cutoff_source=explicit_before_date', $display);
        $this->assertStringContainsString('before_date=2026-03-01', $display);
        $this->assertStringContainsString('deleted_rows=250', $display);
        $this->assertStringContainsString('output_dir=C:/tmp/session-purge', $display);
    }

    public function test_session_snapshot_purge_command_renders_default_retention_context(): void
    {
        $service = m::mock(SessionSnapshotService::class);
        $service->shouldReceive('purge')
            ->once()
            ->with(null, '/tmp/session-purge-default')
            ->andReturn([
                'cutoff_timestamp' => '2026-03-24 12:00:00',
                'cutoff_source' => 'default_retention_days',
                'before_date' => null,
                'retention_days' => 30,
                'deleted_rows' => 25,
                'output_dir' => '/tmp/session-purge-default',
            ]);

        $this->app->instance(SessionSnapshotService::class, $service);

        $command = new PurgeSessionSnapshotCommand();
        $command->setLaravel($this->app);
        $tester = new CommandTester($command);

        $exitCode = $tester->execute([
            '--output_dir' => '/tmp/session-purge-default',
        ]);

        $display = $tester->getDisplay();

        $this->assertSame(0, $exitCode);
        $this->assertStringContainsString('cutoff_timestamp=2026-03-24 12:00:00', $display);
        $this->assertStringContainsString('cutoff_source=default_retention_days', $display);
        $this->assertStringContainsString('retention_days=30', $display);
        $this->assertStringNotContainsString('before_date=', $display);
        $this->assertStringContainsString('deleted_rows=25', $display);
        $this->assertStringContainsString('output_dir=/tmp/session-purge-default', $display);
    }

    public function test_replay_smoke_command_propagates_operator_options_and_renders_case_identifiers(): void
    {
        $service = m::mock(ReplaySmokeSuiteService::class);
        $service->shouldReceive('execute')
            ->once()
            ->with(41, '/tmp/fixtures', 'C:\\tmp\\replay-smoke')
            ->andReturn([
                'suite' => 'replay_smoke_minimum',
                'run_id' => 41,
                'fixture_root' => 'C:\\tmp\\fixtures',
                'all_passed' => true,
                'output_dir' => 'C:\\tmp\\replay-smoke',
                'cases' => [
                    [
                        'fixture_case' => 'valid_case',
                        'expected_outcome' => 'MATCH',
                        'observed_outcome' => 'MATCH',
                        'passed' => true,
                        'trade_date' => '2026-03-17',
                        'replay_id' => 3001,
                        'evidence_output_dir' => 'C:\\tmp\\replay-smoke\\valid_case',
                    ],
                    [
                        'fixture_case' => 'missing_file_case',
                        'expected_outcome' => 'ERROR',
                        'observed_outcome' => 'ERROR',
                        'passed' => true,
                        'error' => 'Fixture file declared in manifest is missing.',
                    ],
                ],
            ]);

        $this->app->instance(ReplaySmokeSuiteService::class, $service);

        $command = new ReplaySmokeSuiteCommand();
        $command->setLaravel($this->app);
        $tester = new CommandTester($command);

        $exitCode = $tester->execute([
            'run_id' => 41,
            '--fixture_root' => '/tmp/fixtures',
            '--output_dir' => 'C:\\tmp\\replay-smoke',
        ]);

        $display = $tester->getDisplay();

        $this->assertSame(0, $exitCode);
        $this->assertStringContainsString('suite=replay_smoke_minimum', $display);
        $this->assertStringContainsString('run_id=41', $display);
        $this->assertStringContainsString('fixture_root=C:/tmp/fixtures', $display);
        $this->assertStringContainsString('all_passed=1', $display);
        $this->assertStringContainsString('fixture_case=valid_case | expected=MATCH | observed=MATCH | passed=1 | fixture_path=C:/tmp/fixtures/valid_case | trade_date=2026-03-17 | replay_id=3001 | evidence_output_dir=C:/tmp/replay-smoke/valid_case', $display);
        $this->assertStringContainsString('fixture_case=missing_file_case | expected=ERROR | observed=ERROR | passed=1 | error=Fixture file declared in manifest is missing.', $display);
    }

    public function test_replay_smoke_command_returns_failure_when_any_case_deviates_from_expected_outcome(): void
    {
        $service = m::mock(ReplaySmokeSuiteService::class);
        $service->shouldReceive('execute')
            ->once()
            ->with(41, null, null)
            ->andReturn([
                'suite' => 'replay_smoke_minimum',
                'run_id' => 41,
                'fixture_root' => '/tmp/default-fixtures',
                'all_passed' => false,
                'output_dir' => '/tmp/replay-smoke',
                'cases' => [
                    [
                        'fixture_case' => 'valid_case',
                        'expected_outcome' => 'MATCH',
                        'observed_outcome' => 'MISMATCH',
                        'passed' => false,
                        'trade_date' => '2026-03-17',
                        'replay_id' => 3001,
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

        $this->assertSame(1, $exitCode);
        $this->assertStringContainsString('fixture_root=/tmp/default-fixtures', $display);
        $this->assertStringContainsString('all_passed=0', $display);
        $this->assertStringContainsString('fixture_case=valid_case | expected=MATCH | observed=MISMATCH | passed=0 | trade_date=2026-03-17 | replay_id=3001', $display);
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
            ->with(41, 'C:\\tmp\\run-evidence')
            ->andReturn([
                'selector' => ['type' => 'run', 'id' => 41],
                'summary' => [
                    'run_id' => 41,
                    'trade_date_requested' => '2026-03-17',
                    'trade_date_effective' => '2026-03-17',
                    'terminal_status' => null,
                    'publishability_state' => 'NOT_READABLE',
                ],
                'output_dir' => 'C:\\tmp\\run-evidence',
                'file_count' => 2,
                'files' => ['run_summary.json', 'evidence_pack.json'],
            ]);

        $this->app->instance(MarketDataEvidenceExportService::class, $service);

        $command = new ExportEvidenceCommand();
        $command->setLaravel($this->app);
        $tester = new CommandTester($command);

        $exitCode = $tester->execute([
            '--run_id' => 41,
            '--output_dir' => 'C:\\tmp\\run-evidence',
        ]);

        $display = $tester->getDisplay();

        $this->assertSame(0, $exitCode);
        $this->assertStringContainsString('selector=run', $display);
        $this->assertStringContainsString('selector_id=41', $display);
        $this->assertStringContainsString('trade_date_requested=2026-03-17', $display);
        $this->assertStringContainsString('terminal_status=SUCCESS', $display);
        $this->assertStringContainsString('publishability_state=READABLE', $display);
        $this->assertStringContainsString('output_dir=C:/tmp/run-evidence', $display);
        $this->assertStringContainsString('file_count=2', $display);
        $this->assertStringContainsString('files=run_summary.json,evidence_pack.json', $display);
    }

    public function test_evidence_export_command_exports_run_evidence_with_source_context_summary(): void
    {
        $service = m::mock(MarketDataEvidenceExportService::class);
        $service->shouldReceive('exportRunEvidence')
            ->once()
            ->with(42, '/tmp/run-evidence-source')
            ->andReturn([
                'selector' => ['type' => 'run', 'id' => 42],
                'summary' => [
                    'run_id' => 42,
                    'trade_date_requested' => '2026-03-18',
                    'trade_date_effective' => '2026-03-18',
                    'terminal_status' => null,
                    'publishability_state' => 'NOT_READABLE',
                    'source_name' => 'API_FREE',
                    'source_input_file' => null,
                    'source_summary' => 'provider=generic | timeout_seconds=15 | retry_max=3 | attempt_count=2 | success_after_retry=yes | final_http_status=200 | final_reason_code=RUN_SOURCE_TIMEOUT',
                ],
                'output_dir' => '/tmp/run-evidence-source',
                'file_count' => 2,
                'files' => ['run_summary.json', 'evidence_pack.json'],
            ]);

        $this->app->instance(MarketDataEvidenceExportService::class, $service);

        $command = new ExportEvidenceCommand();
        $command->setLaravel($this->app);
        $tester = new CommandTester($command);

        $exitCode = $tester->execute([
            '--run_id' => 42,
            '--output_dir' => '/tmp/run-evidence-source',
        ]);

        $display = $tester->getDisplay();

        $this->assertSame(0, $exitCode);
        $this->assertStringContainsString('selector=run', $display);
        $this->assertStringContainsString('selector_id=42', $display);
        $this->assertStringContainsString('source_name=API_FREE', $display);
        $this->assertStringContainsString('source_summary=provider=generic | timeout_seconds=15 | retry_max=3 | attempt_count=2 | success_after_retry=yes | final_http_status=200 | final_reason_code=RUN_SOURCE_TIMEOUT', $display);
        $this->assertStringNotContainsString('source_input_file=', $display);
    }


    public function test_evidence_export_command_normalizes_manual_source_input_file_in_summary_output(): void
    {
        $service = m::mock(MarketDataEvidenceExportService::class);
        $service->shouldReceive('exportRunEvidence')
            ->once()
            ->with(43, 'C:\tmp\run-evidence-manual')
            ->andReturn([
                'selector' => ['type' => 'run', 'id' => 43],
                'summary' => [
                    'run_id' => 43,
                    'trade_date_requested' => '2026-03-19',
                    'trade_date_effective' => '2026-03-18',
                    'terminal_status' => 'HELD',
                    'publishability_state' => 'NOT_READABLE',
                    'source_name' => 'LOCAL_FILE',
                    'source_input_file' => 'C:/tmp/manual-2026-03-19.csv',
                ],
                'output_dir' => 'C:\tmp\run-evidence-manual',
                'file_count' => 2,
                'files' => ['run_summary.json', 'evidence_pack.json'],
            ]);

        $this->app->instance(MarketDataEvidenceExportService::class, $service);

        $command = new ExportEvidenceCommand();
        $command->setLaravel($this->app);
        $tester = new CommandTester($command);

        $exitCode = $tester->execute([
            '--run_id' => 43,
            '--output_dir' => 'C:\tmp\run-evidence-manual',
        ]);

        $display = $tester->getDisplay();

        $this->assertSame(0, $exitCode);
        $this->assertStringContainsString('source_name=LOCAL_FILE', $display);
        $this->assertStringContainsString('source_input_file=C:/tmp/manual-2026-03-19.csv', $display);
        $this->assertStringContainsString('output_dir=C:/tmp/run-evidence-manual', $display);
    }

    public function test_evidence_export_command_exports_correction_evidence(): void
    {
        $service = m::mock(MarketDataEvidenceExportService::class);
        $service->shouldReceive('exportCorrectionEvidence')
            ->once()
            ->with(25, '/tmp/correction-evidence')
            ->andReturn([
                'selector' => ['type' => 'correction', 'id' => 25],
                'summary' => [
                    'correction_id' => 25,
                    'trade_date' => '2026-03-17',
                    'status' => 'PUBLISHED',
                    'publication_switch' => true,
                ],
                'output_dir' => '/tmp/correction-evidence',
                'file_count' => 1,
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

        $display = $tester->getDisplay();

        $this->assertSame(0, $exitCode);
        $this->assertStringContainsString('selector=correction', $display);
        $this->assertStringContainsString('selector_id=25', $display);
        $this->assertStringContainsString('trade_date=2026-03-17', $display);
        $this->assertStringContainsString('status=PUBLISHED', $display);
        $this->assertStringContainsString('publication_switch=1', $display);
        $this->assertStringContainsString('output_dir=/tmp/correction-evidence', $display);
        $this->assertStringContainsString('file_count=1', $display);
        $this->assertStringContainsString('files=correction_evidence.json', $display);
    }

    public function test_evidence_export_command_requires_trade_date_for_replay_selector(): void
    {
        $service = m::mock(MarketDataEvidenceExportService::class);
        $service->shouldNotReceive('exportReplayEvidence');

        $this->app->instance(MarketDataEvidenceExportService::class, $service);

        $command = new ExportEvidenceCommand();
        $command->setLaravel($this->app);
        $tester = new CommandTester($command);

        $exitCode = $tester->execute([
            '--replay_id' => 3001,
            '--output_dir' => '/tmp/replay-evidence',
        ]);

        $this->assertSame(1, $exitCode);
        $this->assertStringContainsString('Replay evidence export requires --trade_date; latest-row resolution is not allowed.', $tester->getDisplay());
    }

    public function test_evidence_export_command_exports_replay_evidence(): void
    {
        $service = m::mock(MarketDataEvidenceExportService::class);
        $service->shouldReceive('exportReplayEvidence')
            ->once()
            ->with(3001, '2026-03-17', '/tmp/replay-evidence')
            ->andReturn([
                'selector' => ['type' => 'replay', 'id' => 3001],
                'summary' => [
                    'replay_id' => 3001,
                    'trade_date' => '2026-03-17',
                    'comparison_result' => 'MATCH',
                    'status' => 'SUCCESS',
                ],
                'output_dir' => '/tmp/replay-evidence',
                'file_count' => 2,
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

        $display = $tester->getDisplay();

        $this->assertSame(0, $exitCode);
        $this->assertStringContainsString('selector=replay', $display);
        $this->assertStringContainsString('selector_id=3001', $display);
        $this->assertStringContainsString('trade_date=2026-03-17', $display);
        $this->assertStringContainsString('comparison_result=MATCH', $display);
        $this->assertStringContainsString('status=SUCCESS', $display);
        $this->assertStringContainsString('output_dir=/tmp/replay-evidence', $display);
        $this->assertStringContainsString('file_count=2', $display);
        $this->assertStringContainsString('files=replay_result.json,replay_evidence_pack.json', $display);
    }

    public function test_evidence_export_command_returns_failure_and_renders_error_when_service_throws(): void
    {
        $service = m::mock(MarketDataEvidenceExportService::class);
        $service->shouldReceive('exportRunEvidence')
            ->once()
            ->with(41, '/tmp/run-evidence')
            ->andThrow(new \RuntimeException('Run not found for evidence export.'));

        $this->app->instance(MarketDataEvidenceExportService::class, $service);

        $command = new ExportEvidenceCommand();
        $command->setLaravel($this->app);
        $tester = new CommandTester($command);

        $exitCode = $tester->execute([
            '--run_id' => 41,
            '--output_dir' => '/tmp/run-evidence',
        ]);

        $this->assertSame(1, $exitCode);
        $this->assertStringContainsString('error=Run not found for evidence export.', $tester->getDisplay());
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
            ->with(3001, '2026-03-17', 'C:\\tmp\\replay-verify')
            ->andReturn([
                'output_dir' => 'C:\\tmp\\replay-verify',
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
            '--output_dir' => 'C:\\tmp\\replay-verify',
        ]);

        $display = $tester->getDisplay();

        $this->assertSame(0, $exitCode);
        $this->assertStringContainsString('replay_id=3001', $display);
        $this->assertStringContainsString('trade_date=2026-03-17', $display);
        $this->assertStringContainsString('comparison_result=MATCH', $display);
        $this->assertStringContainsString('artifact_changed_scope=none', $display);
        $this->assertStringContainsString('fixture_family=market_data_replay_minimum', $display);
        $this->assertStringContainsString('fixture_path=storage/app/market_data/replay-fixtures/valid_case', $display);
        $this->assertStringContainsString('evidence_output_dir=C:/tmp/replay-verify', $display);
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
        $this->assertStringContainsString('fixture_path=storage/app/market_data/replay-fixtures/reason_code_mismatch_case', $display);
    }

    public function test_replay_backfill_command_propagates_operator_options_and_renders_run_and_replay_ids(): void
    {
        $service = m::mock(ReplayBackfillService::class);
        $service->shouldReceive('execute')
            ->once()
            ->with('2026-03-17', '2026-03-18', 'valid_case', '/tmp/fixtures', 'C:\\tmp\\replay-backfill', true)
            ->andReturn([
                'suite' => 'market_data_replay_backfill_minimum',
                'range' => [
                    'start_date' => '2026-03-17',
                    'end_date' => '2026-03-18',
                ],
                'fixture_case' => 'valid_case',
                'all_passed' => true,
                'output_dir' => 'C:\\tmp\\replay-backfill',
                'cases' => [
                    [
                        'trade_date' => '2026-03-17',
                        'status' => 'SUCCESS',
                        'expected_outcome' => 'MATCH',
                        'observed_outcome' => 'MATCH',
                        'passed' => true,
                        'run_id' => 41,
                        'replay_id' => 3001,
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
            '--fixture_root' => '/tmp/fixtures',
            '--output_dir' => 'C:\\tmp\\replay-backfill',
            '--continue_on_error' => true,
        ]);

        $display = $tester->getDisplay();

        $this->assertSame(0, $exitCode);
        $this->assertStringContainsString('suite=market_data_replay_backfill_minimum', $display);
        $this->assertStringContainsString('fixture_case=valid_case', $display);
        $this->assertStringContainsString('fixture_root=/tmp/fixtures', $display);
        $this->assertStringContainsString('fixture_path=/tmp/fixtures/valid_case', $display);
        $this->assertStringContainsString('all_passed=1', $display);
        $this->assertStringContainsString('output_dir=C:/tmp/replay-backfill', $display);
        $this->assertStringContainsString('trade_date=2026-03-17 | status=SUCCESS | expected=MATCH | observed=MATCH | passed=1 | run_id=41 | replay_id=3001 | evidence_output_dir=C:/tmp/replay-backfill/2026-03-17', $display);
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
                'fixture_root' => '/tmp/default-fixtures',
                'fixture_path' => '/tmp/default-fixtures/valid_case',
                'all_passed' => false,
                'output_dir' => '/tmp/replay-backfill',
                'cases' => [
                    [
                        'trade_date' => '2026-03-17',
                        'status' => 'SUCCESS',
                        'expected_outcome' => 'MATCH',
                        'observed_outcome' => 'MATCH',
                        'passed' => true,
                        'run_id' => 41,
                        'replay_id' => 3001,
                    ],
                    [
                        'trade_date' => '2026-03-18',
                        'status' => 'ERROR',
                        'expected_outcome' => 'MATCH',
                        'observed_outcome' => 'ERROR',
                        'passed' => false,
                        'error_message' => 'Readable current publication not found for replay backfill trade date 2026-03-18.',
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
        $this->assertStringContainsString('fixture_root=/tmp/default-fixtures', $display);
        $this->assertStringContainsString('fixture_path=/tmp/default-fixtures/valid_case', $display);
        $this->assertStringContainsString('all_passed=0', $display);
        $this->assertStringContainsString('trade_date=2026-03-17 | status=SUCCESS | expected=MATCH | observed=MATCH | passed=1 | run_id=41 | replay_id=3001', $display);
        $this->assertStringContainsString('trade_date=2026-03-18 | status=ERROR | expected=MATCH | observed=ERROR | passed=0 | error=Readable current publication not found for replay backfill trade date 2026-03-18.', $display);
    }



    public function test_daily_pipeline_command_writes_summary_artifact_for_success_path(): void
    {
        $service = m::mock(MarketDataPipelineService::class);
        $service->shouldReceive('importDaily')
            ->once()
            ->with('2026-03-24', 'api', null)
            ->andReturn((object) [
                'run_id' => 55,
                'trade_date_requested' => '2026-03-24',
                                'stage' => 'INGEST_BARS',
                'lifecycle_state' => 'COMPLETED',
                'terminal_status' => null,
                'publishability_state' => 'NOT_READABLE',
                'notes' => 'candidate_publication_id=44; source_name=API_FREE; source_provider=generic; source_timeout_seconds=15; source_retry_max=3; source_attempt_count=2; source_success_after_retry=yes; source_final_http_status=200; source_final_reason_code=RUN_SOURCE_TIMEOUT',
            ]);

        $this->app->instance(MarketDataPipelineService::class, $service);

        $outputDir = sys_get_temp_dir().'/tradeaxis-daily-success-'.uniqid();

        $command = new \App\Console\Commands\MarketData\DailyPipelineCommand();
        $command->setLaravel($this->app);
        $tester = new CommandTester($command);

        $exitCode = $tester->execute([
            '--requested_date' => '2026-03-24',
            '--source_mode' => 'api',
            '--output_dir' => $outputDir,
        ]);

        $display = $tester->getDisplay();
        $artifactPath = $outputDir.'/market_data_daily_summary.json';
        $normalizedOutputDir = str_replace('\\', '/', $outputDir);
        $normalizedArtifactPath = str_replace('\\', '/', $artifactPath);

        $this->assertSame(0, $exitCode);
        $this->assertStringContainsString('request_mode=import_only', $display);
        $this->assertStringContainsString('output_dir='.$normalizedOutputDir, $display);
        $this->assertStringContainsString('summary_artifact='.$normalizedArtifactPath, $display);
        $this->assertFileExists($artifactPath);

        $payload = json_decode((string) file_get_contents($artifactPath), true);

        $this->assertSame('market-data:daily', $payload['command']);
        $this->assertSame('import_only', $payload['request_mode']);
        $this->assertSame('SUCCESS', $payload['status']);
        $this->assertSame('api', $payload['source_mode']);
        $this->assertSame(55, $payload['run_id']);
        $this->assertSame('API_FREE', $payload['source_name']);
        $this->assertArrayNotHasKey('source_attempt_event_type', $payload);
        $this->assertArrayNotHasKey('source_attempt_count', $payload);
        $this->assertSame('provider=generic | timeout_seconds=15 | retry_max=3 | attempt_count=2 | success_after_retry=yes | final_http_status=200 | final_reason_code=RUN_SOURCE_TIMEOUT', $payload['source_summary']);

        File::deleteDirectory($outputDir);
    }

    public function test_daily_pipeline_command_writes_summary_artifact_for_recovered_failure_path(): void
    {
        $service = m::mock(MarketDataPipelineService::class);
        $service->shouldReceive('importDaily')
            ->once()
            ->with('2026-03-24', 'api', null)
            ->andThrow(new RuntimeException('boom'));

        $runs = m::mock(EodRunRepository::class);
        $runs->shouldReceive('findLatestForRequestedDate')
            ->once()
            ->with('2026-03-24', 'api')
            ->andReturn((object) [
                'run_id' => 44,
                'trade_date_requested' => '2026-03-24',
                'trade_date_effective' => '2026-03-21',
                'stage' => 'INGEST_BARS',
                'lifecycle_state' => 'FAILED',
                'terminal_status' => 'FAILED',
                'publishability_state' => 'NOT_READABLE',
                'notes' => 'source_name=API_FREE; source_provider=generic; source_timeout_seconds=15; source_retry_max=3; source_attempt_count=3; source_final_reason_code=RUN_SOURCE_TIMEOUT',
            ]);

        $this->app->instance(MarketDataPipelineService::class, $service);
        $this->app->instance(EodRunRepository::class, $runs);

        $outputDir = sys_get_temp_dir().'/tradeaxis-daily-failure-'.uniqid();

        $command = new \App\Console\Commands\MarketData\DailyPipelineCommand();
        $command->setLaravel($this->app);
        $tester = new CommandTester($command);

        $exitCode = $tester->execute([
            '--requested_date' => '2026-03-24',
            '--source_mode' => 'api',
            '--output_dir' => $outputDir,
        ]);

        $display = $tester->getDisplay();
        $artifactPath = $outputDir.'/market_data_daily_summary.json';
        $normalizedOutputDir = str_replace('\\', '/', $outputDir);
        $normalizedArtifactPath = str_replace('\\', '/', $artifactPath);

        $this->assertSame(1, $exitCode);
        $this->assertStringContainsString('request_mode=import_only', $display);
        $this->assertStringContainsString('output_dir='.$normalizedOutputDir, $display);
        $this->assertStringContainsString('summary_artifact='.$normalizedArtifactPath, $display);
        $this->assertFileExists($artifactPath);

        $payload = json_decode((string) file_get_contents($artifactPath), true);

        $this->assertSame('market-data:daily', $payload['command']);
        $this->assertSame('import_only', $payload['request_mode']);
        $this->assertSame('ERROR', $payload['status']);
        $this->assertSame('api', $payload['source_mode']);
        $this->assertSame(44, $payload['run_id']);
        $this->assertSame('2026-03-21', $payload['trade_date_effective']);
        $this->assertSame('boom', $payload['error_message']);
        $this->assertArrayNotHasKey('source_attempt_event_type', $payload);
        $this->assertArrayNotHasKey('source_attempt_count', $payload);
        $this->assertSame('provider=generic | timeout_seconds=15 | retry_max=3 | attempt_count=3 | final_reason_code=RUN_SOURCE_TIMEOUT', $payload['source_summary']);

        File::deleteDirectory($outputDir);
    }

    public function test_daily_pipeline_command_normalizes_manual_input_file_paths_in_summary_artifact(): void
    {
        config()->set('market_data.source.local_input_file', null);

        $service = m::mock(MarketDataPipelineService::class);
        $service->shouldReceive('importDaily')
            ->once()
            ->with('2026-03-24', 'manual_file', null)
            ->andReturn((object) [
                'run_id' => 55,
                'trade_date_requested' => '2026-03-24',
                                'stage' => 'INGEST_BARS',
                'lifecycle_state' => 'COMPLETED',
                'terminal_status' => null,
                'publishability_state' => 'NOT_READABLE',
                'notes' => 'candidate_publication_id=44; source_name=LOCAL_FILE; source_input_file=C:\\ops\\manual-2026-03-24.csv',
            ]);

        $this->app->instance(MarketDataPipelineService::class, $service);

        $outputDir = sys_get_temp_dir().'/tradeaxis-daily-manual-success-'.uniqid();

        $command = new \App\Console\Commands\MarketData\DailyPipelineCommand();
        $command->setLaravel($this->app);
        $tester = new CommandTester($command);

        $exitCode = $tester->execute([
            '--requested_date' => '2026-03-24',
            '--source_mode' => 'manual_file',
            '--input_file' => 'C:\\ops\\manual-2026-03-24.csv',
            '--output_dir' => $outputDir,
        ]);

        $display = $tester->getDisplay();
        $artifactPath = $outputDir.'/market_data_daily_summary.json';

        $this->assertSame(0, $exitCode);
        $this->assertStringContainsString('input_file=C:/ops/manual-2026-03-24.csv', $display);
        $this->assertStringContainsString('source_input_file=C:/ops/manual-2026-03-24.csv', $display);
        $this->assertFileExists($artifactPath);

        $payload = json_decode((string) file_get_contents($artifactPath), true);

        $this->assertSame('C:/ops/manual-2026-03-24.csv', $payload['input_file']);
        $this->assertSame('C:/ops/manual-2026-03-24.csv', $payload['source_input_file']);

        File::deleteDirectory($outputDir);
    }

    public function test_daily_pipeline_command_propagates_manual_input_file_override_without_leaking_config(): void
    {
        config()->set('market_data.source.local_input_file', null);

        $service = m::mock(MarketDataPipelineService::class);
        $service->shouldReceive('importDaily')
            ->once()
            ->with('2026-03-24', 'manual_file', null)
            ->andReturnUsing(function () {
                \PHPUnit\Framework\Assert::assertSame('storage\\app\\market_data\\operator\\manual-2026-03-24.csv', config('market_data.source.local_input_file'));

                return (object) [
                    'run_id' => 55,
                    'trade_date_requested' => '2026-03-24',
                    'stage' => 'INGEST_BARS',
                    'lifecycle_state' => 'COMPLETED',
                    'terminal_status' => 'SUCCESS',
                    'publishability_state' => 'READABLE',
                ];
            });

        $this->app->instance(MarketDataPipelineService::class, $service);

        $command = new \App\Console\Commands\MarketData\DailyPipelineCommand();
        $command->setLaravel($this->app);
        $tester = new CommandTester($command);

        $exitCode = $tester->execute([
            '--requested_date' => '2026-03-24',
            '--source_mode' => 'manual_file',
            '--input_file' => 'storage\\app\\market_data\\operator\\manual-2026-03-24.csv',
        ]);

        $display = $tester->getDisplay();

        $this->assertSame(0, $exitCode);
        $this->assertNull(config('market_data.source.local_input_file'));
        $this->assertStringContainsString('request_mode=import_only', $display);
        $this->assertStringContainsString('input_file=storage/app/market_data/operator/manual-2026-03-24.csv', $display);
    }


    public function test_daily_pipeline_command_renders_source_summary_from_run_notes(): void
    {
        $service = m::mock(MarketDataPipelineService::class);
        $service->shouldReceive('importDaily')
            ->once()
            ->with('2026-03-24', 'api', null)
            ->andReturn((object) [
                'run_id' => 55,
                'trade_date_requested' => '2026-03-24',
                'stage' => 'INGEST_BARS',
                'lifecycle_state' => 'COMPLETED',
                'terminal_status' => 'SUCCESS',
                'publishability_state' => 'READABLE',
                'notes' => 'candidate_publication_id=44; source_name=API_FREE; source_provider=generic; source_timeout_seconds=15; source_retry_max=3; source_attempt_count=2; source_success_after_retry=yes; source_final_http_status=200; source_final_reason_code=RUN_SOURCE_TIMEOUT',
            ]);

        $evidence = m::mock(EodEvidenceRepository::class);
        $evidence->shouldReceive('exportRunSourceAttemptTelemetry')
            ->once()
            ->with(55)
            ->andReturn([
                'source_name' => 'API_FREE',
                'provider' => 'generic',
                'timeout_seconds' => 15,
                'retry_max' => 3,
                'attempt_count' => 2,
                'success_after_retry' => true,
                'final_http_status' => 200,
                'final_reason_code' => 'RUN_SOURCE_TIMEOUT',
                'event_type' => 'STAGE_COMPLETED',
            ]);

        $this->app->instance(MarketDataPipelineService::class, $service);
        $this->app->instance(EodEvidenceRepository::class, $evidence);

        $command = new \App\Console\Commands\MarketData\DailyPipelineCommand();
        $command->setLaravel($this->app);
        $tester = new CommandTester($command);

        $exitCode = $tester->execute([
            '--requested_date' => '2026-03-24',
            '--source_mode' => 'api',
        ]);

        $display = $tester->getDisplay();

        $this->assertSame(0, $exitCode);
        $this->assertStringContainsString('source_name=API_FREE', $display);
        $this->assertStringContainsString('source_attempt_event_type=STAGE_COMPLETED', $display);
        $this->assertStringContainsString('source_attempt_count=2', $display);
        $this->assertStringContainsString('source_summary=provider=generic | timeout_seconds=15 | retry_max=3 | attempt_count=2 | success_after_retry=yes | final_http_status=200 | final_reason_code=RUN_SOURCE_TIMEOUT', $display);
    }

    public function test_daily_pipeline_command_renders_failed_source_summary_from_run_notes(): void
    {
        $service = m::mock(MarketDataPipelineService::class);
        $service->shouldReceive('importDaily')
            ->once()
            ->with('2026-03-24', 'api', null)
            ->andReturn((object) [
                'run_id' => 44,
                'trade_date_requested' => '2026-03-24',
                'stage' => 'INGEST_BARS',
                'lifecycle_state' => 'FAILED',
                'terminal_status' => 'FAILED',
                'publishability_state' => 'NOT_READABLE',
                'notes' => 'source_name=API_FREE; source_provider=generic; source_timeout_seconds=15; source_retry_max=3; source_attempt_count=3; source_final_reason_code=RUN_SOURCE_TIMEOUT',
            ]);

        $this->app->instance(MarketDataPipelineService::class, $service);

        $command = new \App\Console\Commands\MarketData\DailyPipelineCommand();
        $command->setLaravel($this->app);
        $tester = new CommandTester($command);

        $exitCode = $tester->execute([
            '--requested_date' => '2026-03-24',
            '--source_mode' => 'api',
        ]);

        $display = $tester->getDisplay();

        $this->assertSame(0, $exitCode);
        $this->assertStringContainsString('source_name=API_FREE', $display);
        $this->assertStringContainsString('source_summary=provider=generic | timeout_seconds=15 | retry_max=3 | attempt_count=3 | final_reason_code=RUN_SOURCE_TIMEOUT', $display);
    }


    public function test_daily_pipeline_command_renders_source_summary_from_attempt_telemetry_when_notes_are_thin(): void
    {
        $service = m::mock(MarketDataPipelineService::class);
        $service->shouldReceive('importDaily')
            ->once()
            ->with('2026-03-24', 'api', null)
            ->andReturn((object) [
                'run_id' => 55,
                'trade_date_requested' => '2026-03-24',
                'stage' => 'FINALIZE',
                'lifecycle_state' => 'COMPLETED',
                'terminal_status' => 'SUCCESS',
                'publishability_state' => 'READABLE',
                'notes' => 'candidate_publication_id=44; source_name=API_FREE',
            ]);

        $evidence = m::mock(EodEvidenceRepository::class);
        $evidence->shouldReceive('exportRunSourceAttemptTelemetry')
            ->once()
            ->with(55)
            ->andReturn([
                'event_id' => 401,
                'event_time' => '2026-03-24 09:15:00',
                'event_type' => 'STAGE_COMPLETED',
                'source_name' => 'API_FREE',
                'provider' => 'generic',
                'timeout_seconds' => 15,
                'retry_max' => 3,
                'attempt_count' => 2,
                'success_after_retry' => 'yes',
                'final_http_status' => 200,
                'final_reason_code' => 'RUN_SOURCE_TIMEOUT',
                'attempts' => [],
            ]);

        $this->app->instance(MarketDataPipelineService::class, $service);
        $this->app->instance(EodEvidenceRepository::class, $evidence);

        $command = new \App\Console\Commands\MarketData\DailyPipelineCommand();
        $command->setLaravel($this->app);
        $tester = new CommandTester($command);

        $exitCode = $tester->execute([
            '--requested_date' => '2026-03-24',
            '--source_mode' => 'api',
        ]);

        $display = $tester->getDisplay();

        $this->assertSame(0, $exitCode);
        $this->assertStringContainsString('source_name=API_FREE', $display);
        $this->assertStringContainsString('source_attempt_event_type=STAGE_COMPLETED', $display);
        $this->assertStringContainsString('source_attempt_count=2', $display);
        $this->assertStringContainsString('source_summary=provider=generic | timeout_seconds=15 | retry_max=3 | attempt_count=2 | success_after_retry=yes | final_http_status=200 | final_reason_code=RUN_SOURCE_TIMEOUT', $display);
    }

    public function test_daily_pipeline_command_recovers_failed_source_summary_from_attempt_telemetry_when_pipeline_throws(): void
    {
        $service = m::mock(MarketDataPipelineService::class);
        $service->shouldReceive('importDaily')
            ->once()
            ->with('2026-03-24', 'api', null)
            ->andThrow(new RuntimeException('boom'));

        $runs = m::mock(EodRunRepository::class);
        $runs->shouldReceive('findLatestForRequestedDate')
            ->once()
            ->with('2026-03-24', 'api')
            ->andReturn((object) [
                'run_id' => 44,
                'trade_date_requested' => '2026-03-24',
                'stage' => 'INGEST_BARS',
                'lifecycle_state' => 'FAILED',
                'terminal_status' => 'FAILED',
                'publishability_state' => 'NOT_READABLE',
                'notes' => 'source_name=API_FREE',
            ]);

        $evidence = m::mock(EodEvidenceRepository::class);
        $evidence->shouldReceive('exportRunSourceAttemptTelemetry')
            ->once()
            ->with(44)
            ->andReturn([
                'event_id' => 402,
                'event_time' => '2026-03-24 09:20:00',
                'event_type' => 'STAGE_FAILED',
                'source_name' => 'API_FREE',
                'provider' => 'generic',
                'timeout_seconds' => 15,
                'retry_max' => 3,
                'attempt_count' => 3,
                'final_reason_code' => 'RUN_SOURCE_TIMEOUT',
                'attempts' => [],
            ]);

        $this->app->instance(MarketDataPipelineService::class, $service);
        $this->app->instance(EodRunRepository::class, $runs);
        $this->app->instance(EodEvidenceRepository::class, $evidence);

        $command = new \App\Console\Commands\MarketData\DailyPipelineCommand();
        $command->setLaravel($this->app);
        $tester = new CommandTester($command);

        $exitCode = $tester->execute([
            '--requested_date' => '2026-03-24',
            '--source_mode' => 'api',
        ]);

        $display = $tester->getDisplay();

        $this->assertSame(1, $exitCode);
        $this->assertStringContainsString('run_id=44', $display);
        $this->assertStringContainsString('source_name=API_FREE', $display);
        $this->assertStringContainsString('source_attempt_event_type=STAGE_FAILED', $display);
        $this->assertStringContainsString('source_attempt_count=3', $display);
        $this->assertStringContainsString('source_summary=provider=generic | timeout_seconds=15 | retry_max=3 | attempt_count=3 | final_reason_code=RUN_SOURCE_TIMEOUT', $display);
        $this->assertStringContainsString('error=boom', $display);
    }





    public function test_daily_pipeline_command_writes_source_attempt_telemetry_artifact_for_success_path_when_attempts_exist(): void
    {
        $service = m::mock(MarketDataPipelineService::class);
        $service->shouldReceive('importDaily')
            ->once()
            ->with('2026-03-24', 'api', null)
            ->andReturn((object) [
                'run_id' => 55,
                'trade_date_requested' => '2026-03-24',
                'trade_date_effective' => '2026-03-24',
                'stage' => 'FINALIZE',
                'lifecycle_state' => 'COMPLETED',
                'terminal_status' => 'SUCCESS',
                'publishability_state' => 'READABLE',
                'notes' => 'candidate_publication_id=44; source_name=API_FREE; source_provider=generic; source_timeout_seconds=15; source_retry_max=3; source_attempt_count=2; source_success_after_retry=yes; source_final_http_status=200; source_final_reason_code=RUN_SOURCE_TIMEOUT',
            ]);

        $evidence = m::mock(EodEvidenceRepository::class);
        $evidence->shouldReceive('exportRunSourceAttemptTelemetry')
            ->once()
            ->with(55)
            ->andReturn([
                'event_id' => 401,
                'event_time' => '2026-03-24 09:15:00',
                'event_type' => 'STAGE_COMPLETED',
                'source_name' => 'API_FREE',
                'provider' => 'generic',
                'timeout_seconds' => 15,
                'retry_max' => 3,
                'attempt_count' => 2,
                'success_after_retry' => 'yes',
                'final_http_status' => 200,
                'final_reason_code' => 'RUN_SOURCE_TIMEOUT',
                'captured_at' => '2026-03-24 09:15:00',
                'attempts' => [
                    [
                        'attempt_number' => 1,
                        'reason_code' => 'RUN_SOURCE_TIMEOUT',
                        'http_status' => 504,
                        'throttle_delay_ms' => 1000,
                        'backoff_delay_ms' => 250,
                        'will_retry' => true,
                    ],
                    [
                        'attempt_number' => 2,
                        'reason_code' => null,
                        'http_status' => 200,
                        'throttle_delay_ms' => 1000,
                        'backoff_delay_ms' => 0,
                        'will_retry' => false,
                    ],
                ],
            ]);

        $this->app->instance(MarketDataPipelineService::class, $service);
        $this->app->instance(EodEvidenceRepository::class, $evidence);

        $outputDir = sys_get_temp_dir().'/tradeaxis-daily-telemetry-success-'.uniqid();

        $command = new \App\Console\Commands\MarketData\DailyPipelineCommand();
        $command->setLaravel($this->app);
        $tester = new CommandTester($command);

        $exitCode = $tester->execute([
            '--requested_date' => '2026-03-24',
            '--source_mode' => 'api',
            '--output_dir' => $outputDir,
        ]);

        $display = $tester->getDisplay();
        $artifactPath = $outputDir.'/source_attempt_telemetry.json';
        $normalizedArtifactPath = str_replace('\\', '/', $artifactPath);

        $this->assertSame(0, $exitCode);
        $this->assertStringContainsString('source_attempt_telemetry_artifact='.$normalizedArtifactPath, $display);
        $this->assertFileExists($artifactPath);

        $payload = json_decode((string) file_get_contents($artifactPath), true);

        $this->assertSame('STAGE_COMPLETED', $payload['event_type']);
        $this->assertSame('API_FREE', $payload['source_name']);
        $this->assertCount(2, $payload['attempts']);
        $this->assertTrue($payload['attempts'][0]['will_retry']);
        $this->assertFalse($payload['attempts'][1]['will_retry']);

        File::deleteDirectory($outputDir);
    }

    public function test_daily_pipeline_command_writes_source_attempt_telemetry_artifact_for_recovered_failure_path_when_attempts_exist(): void
    {
        $service = m::mock(MarketDataPipelineService::class);
        $service->shouldReceive('importDaily')
            ->once()
            ->with('2026-03-24', 'api', null)
            ->andThrow(new RuntimeException('boom'));

        $runs = m::mock(EodRunRepository::class);
        $runs->shouldReceive('findLatestForRequestedDate')
            ->once()
            ->with('2026-03-24', 'api')
            ->andReturn((object) [
                'run_id' => 44,
                'trade_date_requested' => '2026-03-24',
                'trade_date_effective' => '2026-03-21',
                'stage' => 'INGEST_BARS',
                'lifecycle_state' => 'FAILED',
                'terminal_status' => 'FAILED',
                'publishability_state' => 'NOT_READABLE',
                'notes' => 'source_name=API_FREE',
            ]);

        $evidence = m::mock(EodEvidenceRepository::class);
        $evidence->shouldReceive('exportRunSourceAttemptTelemetry')
            ->once()
            ->with(44)
            ->andReturn([
                'event_id' => 402,
                'event_time' => '2026-03-24 09:20:00',
                'event_type' => 'STAGE_FAILED',
                'source_name' => 'API_FREE',
                'provider' => 'generic',
                'timeout_seconds' => 15,
                'retry_max' => 3,
                'attempt_count' => 3,
                'final_reason_code' => 'RUN_SOURCE_TIMEOUT',
                'captured_at' => '2026-03-24 09:20:00',
                'attempts' => [
                    [
                        'attempt_number' => 1,
                        'reason_code' => 'RUN_SOURCE_TIMEOUT',
                        'http_status' => 504,
                        'throttle_delay_ms' => 1000,
                        'backoff_delay_ms' => 250,
                        'will_retry' => true,
                    ],
                    [
                        'attempt_number' => 2,
                        'reason_code' => 'RUN_SOURCE_TIMEOUT',
                        'http_status' => 504,
                        'throttle_delay_ms' => 1000,
                        'backoff_delay_ms' => 500,
                        'will_retry' => true,
                    ],
                    [
                        'attempt_number' => 3,
                        'reason_code' => 'RUN_SOURCE_TIMEOUT',
                        'http_status' => 504,
                        'throttle_delay_ms' => 1000,
                        'backoff_delay_ms' => 1000,
                        'will_retry' => false,
                    ],
                ],
            ]);

        $this->app->instance(MarketDataPipelineService::class, $service);
        $this->app->instance(EodRunRepository::class, $runs);
        $this->app->instance(EodEvidenceRepository::class, $evidence);

        $outputDir = sys_get_temp_dir().'/tradeaxis-daily-telemetry-failure-'.uniqid();

        $command = new \App\Console\Commands\MarketData\DailyPipelineCommand();
        $command->setLaravel($this->app);
        $tester = new CommandTester($command);

        $exitCode = $tester->execute([
            '--requested_date' => '2026-03-24',
            '--source_mode' => 'api',
            '--output_dir' => $outputDir,
        ]);

        $display = $tester->getDisplay();
        $artifactPath = $outputDir.'/source_attempt_telemetry.json';
        $normalizedArtifactPath = str_replace('\\', '/', $artifactPath);

        $this->assertSame(1, $exitCode);
        $this->assertStringContainsString('source_attempt_telemetry_artifact='.$normalizedArtifactPath, $display);
        $this->assertFileExists($artifactPath);

        $payload = json_decode((string) file_get_contents($artifactPath), true);

        $this->assertSame('STAGE_FAILED', $payload['event_type']);
        $this->assertSame('API_FREE', $payload['source_name']);
        $this->assertCount(3, $payload['attempts']);
        $this->assertFalse($payload['attempts'][2]['will_retry']);

        File::deleteDirectory($outputDir);
    }

    public function test_daily_pipeline_command_writes_attempt_telemetry_fields_into_summary_artifact_when_notes_are_thin(): void
    {
        $service = m::mock(MarketDataPipelineService::class);
        $service->shouldReceive('importDaily')
            ->once()
            ->with('2026-03-24', 'api', null)
            ->andReturn((object) [
                'run_id' => 55,
                'trade_date_requested' => '2026-03-24',
                'trade_date_effective' => '2026-03-24',
                'stage' => 'FINALIZE',
                'lifecycle_state' => 'COMPLETED',
                'terminal_status' => 'SUCCESS',
                'publishability_state' => 'READABLE',
                'notes' => 'candidate_publication_id=44; source_name=API_FREE',
            ]);

        $evidence = m::mock(EodEvidenceRepository::class);
        $evidence->shouldReceive('exportRunSourceAttemptTelemetry')
            ->once()
            ->with(55)
            ->andReturn([
                'event_id' => 401,
                'event_time' => '2026-03-24 09:15:00',
                'event_type' => 'STAGE_COMPLETED',
                'source_name' => 'API_FREE',
                'provider' => 'generic',
                'timeout_seconds' => 15,
                'retry_max' => 3,
                'attempt_count' => 2,
                'success_after_retry' => 'yes',
                'final_http_status' => 200,
                'final_reason_code' => 'RUN_SOURCE_TIMEOUT',
                'attempts' => [],
            ]);

        $this->app->instance(MarketDataPipelineService::class, $service);
        $this->app->instance(EodEvidenceRepository::class, $evidence);

        $outputDir = sys_get_temp_dir().'/tradeaxis-daily-thin-notes-success-'.uniqid();

        $command = new \App\Console\Commands\MarketData\DailyPipelineCommand();
        $command->setLaravel($this->app);
        $tester = new CommandTester($command);

        $exitCode = $tester->execute([
            '--requested_date' => '2026-03-24',
            '--source_mode' => 'api',
            '--output_dir' => $outputDir,
        ]);

        $this->assertSame(0, $exitCode);

        $artifactPath = $outputDir.'/market_data_daily_summary.json';
        $this->assertFileExists($artifactPath);

        $payload = json_decode((string) file_get_contents($artifactPath), true);

        $this->assertSame('STAGE_COMPLETED', $payload['source_attempt_event_type']);
        $this->assertSame(2, $payload['source_attempt_count']);
        $this->assertSame('provider=generic | timeout_seconds=15 | retry_max=3 | attempt_count=2 | success_after_retry=yes | final_http_status=200 | final_reason_code=RUN_SOURCE_TIMEOUT', $payload['source_summary']);

        File::deleteDirectory($outputDir);
    }

    public function test_daily_pipeline_command_writes_recovered_attempt_telemetry_fields_into_summary_artifact_when_pipeline_throws(): void
    {
        $service = m::mock(MarketDataPipelineService::class);
        $service->shouldReceive('importDaily')
            ->once()
            ->with('2026-03-24', 'api', null)
            ->andThrow(new RuntimeException('boom'));

        $runs = m::mock(EodRunRepository::class);
        $runs->shouldReceive('findLatestForRequestedDate')
            ->once()
            ->with('2026-03-24', 'api')
            ->andReturn((object) [
                'run_id' => 44,
                'trade_date_requested' => '2026-03-24',
                'trade_date_effective' => '2026-03-21',
                'stage' => 'INGEST_BARS',
                'lifecycle_state' => 'FAILED',
                'terminal_status' => 'FAILED',
                'publishability_state' => 'NOT_READABLE',
                'notes' => 'source_name=API_FREE',
            ]);

        $evidence = m::mock(EodEvidenceRepository::class);
        $evidence->shouldReceive('exportRunSourceAttemptTelemetry')
            ->once()
            ->with(44)
            ->andReturn([
                'event_id' => 402,
                'event_time' => '2026-03-24 09:20:00',
                'event_type' => 'STAGE_FAILED',
                'source_name' => 'API_FREE',
                'provider' => 'generic',
                'timeout_seconds' => 15,
                'retry_max' => 3,
                'attempt_count' => 3,
                'final_reason_code' => 'RUN_SOURCE_TIMEOUT',
                'attempts' => [],
            ]);

        $this->app->instance(MarketDataPipelineService::class, $service);
        $this->app->instance(EodRunRepository::class, $runs);
        $this->app->instance(EodEvidenceRepository::class, $evidence);

        $outputDir = sys_get_temp_dir().'/tradeaxis-daily-thin-notes-failure-'.uniqid();

        $command = new \App\Console\Commands\MarketData\DailyPipelineCommand();
        $command->setLaravel($this->app);
        $tester = new CommandTester($command);

        $exitCode = $tester->execute([
            '--requested_date' => '2026-03-24',
            '--source_mode' => 'api',
            '--output_dir' => $outputDir,
        ]);

        $this->assertSame(1, $exitCode);

        $artifactPath = $outputDir.'/market_data_daily_summary.json';
        $this->assertFileExists($artifactPath);

        $payload = json_decode((string) file_get_contents($artifactPath), true);

        $this->assertSame('STAGE_FAILED', $payload['source_attempt_event_type']);
        $this->assertSame(3, $payload['source_attempt_count']);
        $this->assertSame('provider=generic | timeout_seconds=15 | retry_max=3 | attempt_count=3 | final_reason_code=RUN_SOURCE_TIMEOUT', $payload['source_summary']);

        File::deleteDirectory($outputDir);
    }

    public function test_daily_pipeline_command_recovers_failed_run_summary_when_pipeline_throws(): void
    {
        $service = m::mock(MarketDataPipelineService::class);
        $service->shouldReceive('importDaily')
            ->once()
            ->with('2026-03-24', 'api', null)
            ->andThrow(new RuntimeException('boom'));

        $runs = m::mock(EodRunRepository::class);
        $runs->shouldReceive('findLatestForRequestedDate')
            ->once()
            ->with('2026-03-24', 'api')
            ->andReturn((object) [
                'run_id' => 44,
                'trade_date_requested' => '2026-03-24',
                'stage' => 'INGEST_BARS',
                'lifecycle_state' => 'FAILED',
                'terminal_status' => 'FAILED',
                'publishability_state' => 'NOT_READABLE',
                'notes' => 'source_name=API_FREE; source_provider=generic; source_timeout_seconds=15; source_retry_max=3; source_attempt_count=3; source_final_reason_code=RUN_SOURCE_TIMEOUT',
            ]);

        $evidence = m::mock(EodEvidenceRepository::class);
        $evidence->shouldReceive('exportRunSourceAttemptTelemetry')
            ->once()
            ->with(44)
            ->andReturn([
                'source_name' => 'API_FREE',
                'provider' => 'generic',
                'timeout_seconds' => 15,
                'retry_max' => 3,
                'attempt_count' => 3,
                'final_reason_code' => 'RUN_SOURCE_TIMEOUT',
                'event_type' => 'STAGE_FAILED',
            ]);

        $this->app->instance(MarketDataPipelineService::class, $service);
        $this->app->instance(EodRunRepository::class, $runs);
        $this->app->instance(EodEvidenceRepository::class, $evidence);

        $command = new \App\Console\Commands\MarketData\DailyPipelineCommand();
        $command->setLaravel($this->app);
        $tester = new CommandTester($command);

        $exitCode = $tester->execute([
            '--requested_date' => '2026-03-24',
            '--source_mode' => 'api',
        ]);

        $display = $tester->getDisplay();

        $this->assertSame(1, $exitCode);
        $this->assertStringContainsString('run_id=44', $display);
        $this->assertStringContainsString('source_name=API_FREE', $display);
        $this->assertStringContainsString('source_attempt_event_type=STAGE_FAILED', $display);
        $this->assertStringContainsString('source_attempt_count=3', $display);
        $this->assertStringContainsString('source_summary=provider=generic | timeout_seconds=15 | retry_max=3 | attempt_count=3 | final_reason_code=RUN_SOURCE_TIMEOUT', $display);
        $this->assertStringContainsString('error=boom', $display);
    }

    public function test_daily_pipeline_command_renders_manual_source_input_file_from_run_notes(): void
    {
        $service = m::mock(MarketDataPipelineService::class);
        $service->shouldReceive('importDaily')
            ->once()
            ->with('2026-03-24', 'manual_file', null)
            ->andReturn((object) [
                'run_id' => 55,
                'trade_date_requested' => '2026-03-24',
                'stage' => 'FINALIZE',
                'lifecycle_state' => 'COMPLETED',
                'terminal_status' => 'SUCCESS',
                'publishability_state' => 'READABLE',
                'notes' => 'candidate_publication_id=44; source_name=LOCAL_FILE; source_input_file=C:\\ops\\manual-2026-03-24.csv',
            ]);

        $this->app->instance(MarketDataPipelineService::class, $service);

        $command = new \App\Console\Commands\MarketData\DailyPipelineCommand();
        $command->setLaravel($this->app);
        $tester = new CommandTester($command);

        $exitCode = $tester->execute([
            '--requested_date' => '2026-03-24',
            '--source_mode' => 'manual_file',
        ]);

        $display = $tester->getDisplay();

        $this->assertSame(0, $exitCode);
        $this->assertStringContainsString('source_name=LOCAL_FILE', $display);
        $this->assertStringContainsString('source_input_file=C:/ops/manual-2026-03-24.csv', $display);
    }

    public function test_daily_pipeline_command_renders_coverage_summary_for_pass_outcome(): void
    {
        $service = m::mock(MarketDataPipelineService::class);
        $service->shouldReceive('importDaily')
            ->once()
            ->with('2026-03-24', 'manual_file', null)
            ->andReturn((object) [
                'run_id' => 55,
                'trade_date_requested' => '2026-03-24',
                'stage' => 'FINALIZE',
                'lifecycle_state' => 'COMPLETED',
                'terminal_status' => 'SUCCESS',
                'publishability_state' => 'READABLE',
                'promote_mode' => 'full_publish',
                'publish_target' => 'current_replace',
                'coverage_gate_state' => 'PASS',
                'coverage_available_count' => 900,
                'coverage_universe_count' => 900,
                'coverage_missing_count' => 0,
                'coverage_ratio' => '1.0000',
                'coverage_min_threshold' => '0.9800',
                'coverage_universe_basis' => 'ticker_master_active_on_trade_date',
                'coverage_contract_version' => 'coverage_gate_v1',
                'reason_code' => null,
            ]);

        $this->app->instance(MarketDataPipelineService::class, $service);

        $command = new \App\Console\Commands\MarketData\DailyPipelineCommand();
        $command->setLaravel($this->app);
        $tester = new CommandTester($command);

        $exitCode = $tester->execute([
            '--requested_date' => '2026-03-24',
            '--source_mode' => 'manual_file',
        ]);

        $display = $tester->getDisplay();

        $this->assertSame(0, $exitCode);
        $this->assertStringContainsString('coverage_gate_state=PASS', $display);
        $this->assertStringContainsString('coverage_reason_code=COVERAGE_THRESHOLD_MET', $display);
        $this->assertStringContainsString('coverage_summary=available=900/900 | missing=0 | ratio=1.0000 | threshold=0.9800 | basis=ticker_master_active_on_trade_date | contract=coverage_gate_v1', $display);
    }

    public function test_finalize_command_renders_coverage_hold_context_for_not_readable_run(): void
    {
        $service = m::mock(MarketDataPipelineService::class);
        $service->shouldReceive('completeFinalize')
            ->once()
            ->with(m::on(function ($input) {
                return $input instanceof MarketDataStageInput
                    && $input->requestedDate === '2026-03-24'
                    && $input->sourceMode === 'manual_file'
                    && $input->runId === 55
                    && $input->stage === 'FINALIZE'
                    && $input->correctionId === null;
            }))
            ->andReturn((object) [
                'run_id' => 55,
                'trade_date_requested' => '2026-03-24',
                'stage' => 'FINALIZE',
                'lifecycle_state' => 'COMPLETED',
                'terminal_status' => 'HELD',
                'publishability_state' => 'NOT_READABLE',
                'promote_mode' => 'full_publish',
                'publish_target' => 'current_replace',
                'coverage_gate_state' => 'FAIL',
                'coverage_available_count' => 854,
                'coverage_universe_count' => 900,
                'coverage_missing_count' => 46,
                'coverage_ratio' => '0.9489',
                'coverage_min_threshold' => '0.9800',
                'coverage_universe_basis' => 'ticker_master_active_on_trade_date',
                'coverage_contract_version' => 'coverage_gate_v1',
                'reason_code' => 'RUN_COVERAGE_LOW',
                'coverage_missing_sample_json' => json_encode(['AALI', 'ACES', 'ADRO']),
            ]);

        $this->app->instance(MarketDataPipelineService::class, $service);

        $command = new \App\Console\Commands\MarketData\FinalizeRunCommand();
        $command->setLaravel($this->app);
        $tester = new CommandTester($command);

        $exitCode = $tester->execute([
            '--requested_date' => '2026-03-24',
            '--source_mode' => 'manual_file',
            '--run_id' => 55,
        ]);

        $display = $tester->getDisplay();

        $this->assertSame(0, $exitCode);
        $this->assertStringContainsString('terminal_status=HELD', $display);
        $this->assertStringContainsString('publishability_state=NOT_READABLE', $display);
        $this->assertStringContainsString('coverage_gate_state=FAIL', $display);
        $this->assertStringContainsString('coverage_reason_code=RUN_COVERAGE_LOW', $display);
        $this->assertStringContainsString('coverage_summary=available=854/900 | missing=46 | ratio=0.9489 | threshold=0.9800 | basis=ticker_master_active_on_trade_date | contract=coverage_gate_v1', $display);
        $this->assertStringContainsString('coverage_missing_sample=AALI,ACES,ADRO', $display);
        $this->assertStringContainsString('reason_code=RUN_COVERAGE_LOW', $display);
    }


    public function test_promote_command_renders_readable_success_summary(): void
    {
        $service = m::mock(MarketDataPipelineService::class);
        $runs = m::mock(EodRunRepository::class);

        $runs->shouldReceive('findLatestForRequestedDate')
            ->once()
            ->with('2026-03-24', 'manual_file')
            ->andReturn((object) [
                'run_id' => 55,
                'source' => 'manual_file',
            ]);

        $service->shouldReceive('promoteDaily')
            ->once()
            ->with('2026-03-24', 'manual_file', 55, null, null)
            ->andReturn((object) [
                'run_id' => 55,
                'trade_date_requested' => '2026-03-24',
                'stage' => 'FINALIZE',
                'lifecycle_state' => 'COMPLETED',
                'terminal_status' => 'SUCCESS',
                'publishability_state' => 'READABLE',
                'coverage_gate_state' => 'PASS',
                'coverage_available_count' => 900,
                'coverage_universe_count' => 900,
                'coverage_missing_count' => 0,
                'coverage_ratio' => '1.0000',
                'coverage_min_threshold' => '0.9500',
                'coverage_universe_basis' => 'ticker_master_active_on_trade_date',
                'coverage_contract_version' => 'coverage_gate_v1',
                'notes' => 'source_name=LOCAL_FILE; source_input_file=C:\ops\manual-2026-03-24.csv',
            ]);

        $this->app->instance(MarketDataPipelineService::class, $service);
        $this->app->instance(EodRunRepository::class, $runs);

        $command = new \App\Console\Commands\MarketData\PromoteMarketDataCommand();
        $command->setLaravel($this->app);
        $tester = new CommandTester($command);

        $exitCode = $tester->execute([
            '--requested_date' => '2026-03-24',
            '--source_mode' => 'manual_file',
        ]);

        $display = $tester->getDisplay();

        $this->assertSame(0, $exitCode);
        $this->assertStringContainsString('terminal_status=SUCCESS', $display);
        $this->assertStringContainsString('publishability_state=READABLE', $display);
        $this->assertStringContainsString('coverage_gate_state=PASS', $display);
    }

    public function test_promote_command_renders_not_readable_coverage_failure_summary(): void
    {
        $service = m::mock(MarketDataPipelineService::class);
        $runs = m::mock(EodRunRepository::class);

        $runs->shouldReceive('findLatestForRequestedDate')
            ->once()
            ->with('2026-03-24', 'manual_file')
            ->andReturn((object) [
                'run_id' => 55,
                'source' => 'manual_file',
            ]);

        $service->shouldReceive('promoteDaily')
            ->once()
            ->with('2026-03-24', 'manual_file', 55, null, null)
            ->andReturn((object) [
                'run_id' => 55,
                'trade_date_requested' => '2026-03-24',
                'stage' => 'FINALIZE',
                'lifecycle_state' => 'COMPLETED',
                'terminal_status' => 'FAILED',
                'publishability_state' => 'NOT_READABLE',
                'coverage_gate_state' => 'FAIL',
                'coverage_available_count' => 854,
                'coverage_universe_count' => 900,
                'coverage_missing_count' => 46,
                'coverage_ratio' => '0.9489',
                'coverage_min_threshold' => '0.9500',
                'coverage_universe_basis' => 'ticker_master_active_on_trade_date',
                'coverage_contract_version' => 'coverage_gate_v1',
                'reason_code' => 'RUN_COVERAGE_LOW',
            ]);

        $this->app->instance(MarketDataPipelineService::class, $service);
        $this->app->instance(EodRunRepository::class, $runs);

        $command = new \App\Console\Commands\MarketData\PromoteMarketDataCommand();
        $command->setLaravel($this->app);
        $tester = new CommandTester($command);

        $exitCode = $tester->execute([
            '--requested_date' => '2026-03-24',
            '--source_mode' => 'manual_file',
        ]);

        $display = $tester->getDisplay();

        $this->assertSame(1, $exitCode);
        $this->assertStringContainsString('terminal_status=FAILED', $display);
        $this->assertStringContainsString('publishability_state=NOT_READABLE', $display);
        $this->assertStringContainsString('coverage_gate_state=FAIL', $display);
        $this->assertStringContainsString('reason_code=RUN_COVERAGE_LOW', $display);
    }


    public function test_promote_command_renders_incremental_non_current_summary(): void
    {
        $service = m::mock(MarketDataPipelineService::class);
        $runs = m::mock(EodRunRepository::class);

        $runs->shouldReceive('findLatestForRequestedDate')
            ->once()
            ->with('2026-03-24', 'manual_file')
            ->andReturn((object) [
                'run_id' => 55,
                'source' => 'manual_file',
            ]);

        $service->shouldReceive('promoteDaily')
            ->once()
            ->with('2026-03-24', 'manual_file', 55, null, 'incremental')
            ->andReturn((object) [
                'run_id' => 55,
                'trade_date_requested' => '2026-03-24',
                'stage' => 'FINALIZE',
                'lifecycle_state' => 'COMPLETED',
                'terminal_status' => 'HELD',
                'publishability_state' => 'NOT_READABLE',
                'promote_mode' => 'incremental',
                'publish_target' => 'incremental_candidate',
                'coverage_gate_state' => 'FAIL',
                'coverage_available_count' => 5,
                'coverage_universe_count' => 901,
                'coverage_missing_count' => 896,
                'coverage_ratio' => '0.0055',
                'coverage_min_threshold' => '0.9800',
                'coverage_universe_basis' => 'ticker_master_active_on_trade_date',
                'coverage_contract_version' => 'coverage_gate_v1',
                'reason_code' => 'RUN_NON_CURRENT_PROMOTION',
            ]);

        $this->app->instance(MarketDataPipelineService::class, $service);
        $this->app->instance(EodRunRepository::class, $runs);

        $command = new \App\Console\Commands\MarketData\PromoteMarketDataCommand();
        $command->setLaravel($this->app);
        $tester = new CommandTester($command);

        $exitCode = $tester->execute([
            '--requested_date' => '2026-03-24',
            '--source_mode' => 'manual_file',
            '--mode' => 'incremental',
        ]);

        $display = $tester->getDisplay();

        $this->assertSame(1, $exitCode);
        $this->assertStringContainsString('terminal_status=HELD', $display);
        $this->assertStringContainsString('publishability_state=NOT_READABLE', $display);
        $this->assertStringContainsString('promote_mode=incremental', $display);
        $this->assertStringContainsString('publish_target=incremental_candidate', $display);
        $this->assertStringContainsString('reason_code=RUN_NON_CURRENT_PROMOTION', $display);
    }

}
