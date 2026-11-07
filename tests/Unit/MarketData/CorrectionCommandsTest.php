<?php

use App\Application\MarketData\Services\MarketDataPipelineService;
use App\Application\MarketData\Services\CorrectionCommandPreflightService;
use App\Console\Commands\MarketData\ApproveCorrectionCommand;
use App\Console\Commands\MarketData\RequestCorrectionCommand;
use App\Console\Commands\MarketData\RunCorrectionCommand;
use App\Infrastructure\MarketData\Source\SourceAcquisitionException;
use App\Infrastructure\Persistence\MarketData\EodCorrectionRepository;
use App\Infrastructure\Persistence\MarketData\EodPublicationRepository;
use Mockery as m;
use Symfony\Component\Console\Tester\CommandTester;

class CorrectionCommandsTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        // These adapter tests replace the pipeline with a controlled outcome. The real
        // preflight is exercised separately against persisted MariaDB correction state.
        $preflight = m::mock(CorrectionCommandPreflightService::class);
        $preflight->shouldReceive('inspect')->andReturnUsing(function ($id, $date, $source) {
            return [
                'correction_id' => $id, 'requested_date' => $date, 'source_mode' => $source,
                'source_identity' => 'manual_file:fixture', 'correction_status' => 'APPROVED',
                'reason_code' => 'READABILITY_FIX', 'reason_note' => 'Unit fixture',
                'baseline_publication_id' => 2501, 'baseline_run_id' => 25,
                'candidate_config_content_hash' => str_repeat('a', 64),
                'new_run_id' => 'PENDING_ALLOCATION',
                'new_config_snapshot_id' => 'PENDING_ALLOCATION',
            ];
        });
        $this->app->instance(CorrectionCommandPreflightService::class, $preflight);
    }

    protected function tearDown(): void
    {
        m::close();
        parent::tearDown();
    }

    public function test_request_correction_command_registers_request_and_renders_summary(): void
    {
        $repo = m::mock(EodCorrectionRepository::class);
        $publications = m::mock(EodPublicationRepository::class);

        $publications->shouldReceive('findCorrectionBaselinePublicationForTradeDate')
            ->twice()
            ->withArgs(function ($date, $lock = false) { return $date === '2026-03-17'; })
            ->andReturn((object) [
                'publication_id' => 2501,
                'run_id' => 25,
            ]);

        $repo->shouldReceive('createRequest')
            ->once()
            ->with('2026-03-17', 'READABILITY_FIX', 'Promote readable current publication for trading day 2026-03-17', 'system', 2501, 25)
            ->andReturn((object) [
                'correction_id' => 5,
                'trade_date' => '2026-03-17',
                'status' => 'REQUESTED',
            ]);

        $this->app->instance(EodCorrectionRepository::class, $repo);
        $this->app->instance(EodPublicationRepository::class, $publications);

        $command = new RequestCorrectionCommand();
        $command->setLaravel($this->app);
        $tester = new CommandTester($command);

        $exitCode = $tester->execute([
            '--trade_date' => '2026-03-17',
            '--reason_code' => 'READABILITY_FIX',
            '--reason_note' => 'Promote readable current publication for trading day 2026-03-17',
            '--requested_by' => 'system',
        ]);

        $display = $tester->getDisplay();

        $this->assertSame(0, $exitCode);
        $this->assertStringContainsString('operation_mode=PRE_APPLY_DISCLOSURE', $display);
        $this->assertStringContainsString('expected_row_effects=create one immutable correction request; no publication or pointer mutation', $display);
        $this->assertStringContainsString('pointer_impact=UNCHANGED', $display);
        $this->assertLessThan(strpos($display, 'correction_id=5'), strpos($display, 'operation_mode=PRE_APPLY_DISCLOSURE'));
        $this->assertStringContainsString('correction_id=5', $display);
        $this->assertStringContainsString('trade_date=2026-03-17', $display);
        $this->assertStringContainsString('status=REQUESTED', $display);
        $this->assertStringContainsString('baseline_publication_id=2501', $display);
        $this->assertStringContainsString('baseline_run_id=25', $display);
    }

    public function test_request_correction_command_blocks_without_valid_current_readable_baseline(): void
    {
        $repo = m::mock(EodCorrectionRepository::class);
        $publications = m::mock(EodPublicationRepository::class);

        $publications->shouldReceive('findCorrectionBaselinePublicationForTradeDate')
            ->once()
            ->with('2026-03-17')
            ->andReturn(null);

        $repo->shouldNotReceive('createRequest');

        $this->app->instance(EodCorrectionRepository::class, $repo);
        $this->app->instance(EodPublicationRepository::class, $publications);

        $command = new RequestCorrectionCommand();
        $command->setLaravel($this->app);
        $tester = new CommandTester($command);

        $exitCode = $tester->execute([
            '--trade_date' => '2026-03-17',
            '--reason_code' => 'READABILITY_FIX',
            '--reason_note' => 'Promote readable current publication for trading day 2026-03-17',
            '--requested_by' => 'system',
        ]);

        $display = $tester->getDisplay();

        $this->assertSame(1, $exitCode);
        $this->assertStringContainsString('status=BLOCKED', $display);
        $this->assertStringContainsString('reason_code=CORRECTION_BASELINE_LINK_MISSING', $display);
        $this->assertStringContainsString('trade_date=2026-03-17', $display);
    }

    public function test_approve_correction_command_approves_request_and_renders_summary(): void
    {
        $repo = m::mock(EodCorrectionRepository::class);
        $pending = (object) [
            'correction_id' => 5, 'trade_date' => '2026-03-17', 'status' => 'REQUESTED',
            'baseline_publication_id' => 2501, 'prior_run_id' => 25,
        ];
        $repo->shouldReceive('findById')->once()->with(5)->andReturn($pending);
        $repo->shouldReceive('findByIdForUpdate')->once()->with(5)->andReturn($pending);
        $repo->shouldReceive('approve')
            ->once()
            ->with(5, 'system')
            ->andReturn((object) [
                'correction_id' => 5,
                'trade_date' => '2026-03-17',
                'status' => 'APPROVED',
            ]);

        $this->app->instance(EodCorrectionRepository::class, $repo);

        $command = new ApproveCorrectionCommand();
        $command->setLaravel($this->app);
        $tester = new CommandTester($command);

        $exitCode = $tester->execute([
            'correction_id' => 5,
            '--approved_by' => 'system',
        ]);

        $display = $tester->getDisplay();

        $this->assertSame(0, $exitCode);
        $this->assertStringContainsString('operation_mode=PRE_APPLY_DISCLOSURE', $display);
        $this->assertStringContainsString('expected_row_effects=transition one correction request from REQUESTED to APPROVED; no publication or pointer mutation', $display);
        $this->assertStringContainsString('pointer_impact=UNCHANGED', $display);
        $this->assertLessThan(strpos($display, 'status=APPROVED'), strpos($display, 'operation_mode=PRE_APPLY_DISCLOSURE'));
        $this->assertStringContainsString('correction_id=5', $display);
        $this->assertStringContainsString('trade_date=2026-03-17', $display);
        $this->assertStringContainsString('status=APPROVED', $display);
    }

    public function test_approve_correction_command_blocks_missing_record_with_reason_code(): void
    {
        $repo = m::mock(EodCorrectionRepository::class);
        $repo->shouldReceive('findById')->once()->with(999999)->andReturn(null);
        $repo->shouldNotReceive('approve');

        $this->app->instance(EodCorrectionRepository::class, $repo);

        $command = new ApproveCorrectionCommand();
        $command->setLaravel($this->app);
        $tester = new CommandTester($command);

        $exitCode = $tester->execute([
            'correction_id' => 999999,
            '--approved_by' => 'system',
        ]);

        $display = $tester->getDisplay();

        $this->assertSame(1, $exitCode);
        $this->assertStringContainsString('status=BLOCKED', $display);
        $this->assertStringContainsString('reason_code=COMMAND_CORRECTION_NOT_FOUND', $display);
        $this->assertStringContainsString('correction_id=999999', $display);
    }

    public function test_request_correction_refuses_when_locked_baseline_differs_from_disclosure(): void
    {
        $repo = m::mock(EodCorrectionRepository::class);
        $repo->shouldNotReceive('createRequest');
        $publications = m::mock(EodPublicationRepository::class);
        $publications->shouldReceive('findCorrectionBaselinePublicationForTradeDate')
            ->once()->with('2026-03-17')->andReturn((object) ['publication_id' => 2501, 'run_id' => 25]);
        $publications->shouldReceive('findCorrectionBaselinePublicationForTradeDate')
            ->once()->with('2026-03-17', true)->andReturn((object) ['publication_id' => 2502, 'run_id' => 26]);
        $this->app->instance(EodCorrectionRepository::class, $repo);
        $this->app->instance(EodPublicationRepository::class, $publications);
        $command = new RequestCorrectionCommand();
        $command->setLaravel($this->app);
        $tester = new CommandTester($command);
        $exit = $tester->execute(['--trade_date' => '2026-03-17', '--reason_code' => 'DATASET_HASH_CREATED']);
        $this->assertSame(2, $exit, $tester->getDisplay());
        $this->assertStringContainsString('reason_code=COMMAND_CORRECTION_REQUEST_PREFLIGHT_CHANGED', $tester->getDisplay());
        $this->assertStringNotContainsString('correction_id=', $tester->getDisplay());
    }

    public function test_approve_correction_refuses_when_locked_request_differs_from_disclosure(): void
    {
        $repo = m::mock(EodCorrectionRepository::class);
        $pending = (object) ['correction_id' => 5, 'trade_date' => '2026-03-17', 'status' => 'REQUESTED',
            'baseline_publication_id' => 2501, 'prior_run_id' => 25];
        $locked = clone $pending;
        $locked->status = 'REJECTED';
        $repo->shouldReceive('findById')->once()->with(5)->andReturn($pending);
        $repo->shouldReceive('findByIdForUpdate')->once()->with(5)->andReturn($locked);
        $repo->shouldNotReceive('approve');
        $this->app->instance(EodCorrectionRepository::class, $repo);
        $command = new ApproveCorrectionCommand();
        $command->setLaravel($this->app);
        $tester = new CommandTester($command);
        $exit = $tester->execute(['correction_id' => 5]);
        $this->assertSame(1, $exit, $tester->getDisplay());
        $this->assertStringContainsString('reason_code=COMMAND_CORRECTION_APPROVAL_PREFLIGHT_CHANGED', $tester->getDisplay());
        $this->assertStringNotContainsString('status=APPROVED', $tester->getDisplay());
    }

    public function test_run_correction_command_executes_pipeline_for_approved_request_and_renders_final_status(): void
    {
        $repo = m::mock(EodCorrectionRepository::class);
        $pipeline = m::mock(MarketDataPipelineService::class);

        $approved = (object) [
            'correction_id' => 5,
            'trade_date' => '2026-03-17',
            'status' => 'APPROVED',
        ];

        $published = (object) [
            'correction_id' => 5,
            'trade_date' => '2026-03-17',
            'status' => 'PUBLISHED',
        ];

        $repo->shouldReceive('findById')
            ->once()
            ->with(5)
            ->andReturn($approved);

        $pipeline->shouldReceive('runDaily')
            ->once()
            ->with('2026-03-17', 'manual_file', 5, m::type('array'))
            ->andReturn((object) [
                'run_id' => 33,
                'trade_date_requested' => '2026-03-17',
                'stage' => 'FINALIZE',
                'lifecycle_state' => 'COMPLETED',
                'terminal_status' => 'SUCCESS',
                'publishability_state' => 'READABLE',
            ]);

        $repo->shouldReceive('findById')
            ->once()
            ->with(5)
            ->andReturn($published);

        $this->app->instance(EodCorrectionRepository::class, $repo);
        $this->app->instance(MarketDataPipelineService::class, $pipeline);

        $command = new RunCorrectionCommand();
        $command->setLaravel($this->app);
        $tester = new CommandTester($command);

        $exitCode = $tester->execute([
            'correction_id' => 5,
            '--requested_date' => '2026-03-17',
            '--source_mode' => 'manual_file',
        ]);

        $display = $tester->getDisplay();

        $this->assertSame(0, $exitCode);
        $this->assertStringContainsString('run_id=33', $display);
        $this->assertStringContainsString('requested_date=2026-03-17', $display);
        $this->assertStringContainsString('terminal_status=SUCCESS', $display);
        $this->assertStringContainsString('publishability_state=READABLE', $display);
        $this->assertStringContainsString('correction_id=5', $display);
        $this->assertStringContainsString('correction_status=PUBLISHED', $display);
    }



    public function test_run_correction_command_renders_cancelled_status_when_rerun_is_unchanged(): void
    {
        $repo = m::mock(EodCorrectionRepository::class);
        $pipeline = m::mock(MarketDataPipelineService::class);

        $approved = (object) [
            'correction_id' => 7,
            'trade_date' => '2026-03-17',
            'status' => 'APPROVED',
        ];

        $cancelled = (object) [
            'correction_id' => 7,
            'trade_date' => '2026-03-17',
            'status' => 'CONSUMED_CURRENT',
            'baseline_publication_id' => 2501,
            'new_publication_id' => 2501,
            'replacement_publication_id' => null,
            'new_publication_is_current' => true,
        ];

        $repo->shouldReceive('findById')
            ->once()
            ->with(7)
            ->andReturn($approved);

        $pipeline->shouldReceive('runDaily')
            ->once()
            ->with('2026-03-17', 'manual_file', 7, m::type('array'))
            ->andReturn((object) [
                'run_id' => 44,
                'trade_date_requested' => '2026-03-17',
                'stage' => 'FINALIZE',
                'lifecycle_state' => 'COMPLETED',
                'terminal_status' => 'SUCCESS',
                'publishability_state' => 'READABLE',
            ]);

        $repo->shouldReceive('findById')
            ->once()
            ->with(7)
            ->andReturn($cancelled);

        $this->app->instance(EodCorrectionRepository::class, $repo);
        $this->app->instance(MarketDataPipelineService::class, $pipeline);

        $command = new RunCorrectionCommand();
        $command->setLaravel($this->app);
        $tester = new CommandTester($command);

        $exitCode = $tester->execute([
            'correction_id' => 7,
            '--requested_date' => '2026-03-17',
            '--source_mode' => 'manual_file',
        ]);

        $display = $tester->getDisplay();

        $this->assertSame(0, $exitCode);
        $this->assertStringContainsString('run_id=44', $display);
        $this->assertStringContainsString('requested_date=2026-03-17', $display);
        $this->assertStringContainsString('terminal_status=SUCCESS', $display);
        $this->assertStringContainsString('publishability_state=READABLE', $display);
        $this->assertStringContainsString('correction_id=7', $display);
        $this->assertStringContainsString('correction_status=CONSUMED_CURRENT', $display);
        $this->assertStringContainsString('candidate_publication_id=', $display);
        $this->assertStringContainsString('candidate_publication_switch=false', $display);
    }

    public function test_run_correction_command_marks_failed_when_pipeline_throws_before_safe_publication(): void
    {
        $repo = m::mock(EodCorrectionRepository::class);
        $pipeline = m::mock(MarketDataPipelineService::class);

        $approved = (object) [
            'correction_id' => 11,
            'trade_date' => '2026-03-17',
            'status' => 'APPROVED',
            'baseline_publication_id' => 2501,
            'prior_run_id' => 25,
        ];
        $executing = (object) [
            'correction_id' => 11,
            'trade_date' => '2026-03-17',
            'status' => 'EXECUTING',
            'baseline_publication_id' => 2501,
            'prior_run_id' => 25,
            'new_run_id' => 47,
        ];
        $failed = (object) [
            'correction_id' => 11,
            'trade_date' => '2026-03-17',
            'status' => 'FAILED',
            'baseline_publication_id' => 2501,
            'prior_run_id' => 25,
            'new_run_id' => 47,
            'final_outcome_note' => 'Correction execution failed before safe publication; baseline current pointer preserved. failure_reason_code=RUN_SOURCE_MANUAL_FILE_NOT_FOUND',
        ];

        $repo->shouldReceive('findById')
            ->once()
            ->with(11)
            ->andReturn($approved);

        $pipeline->shouldReceive('runDaily')
            ->once()
            ->with('2026-03-17', 'manual_file', 11, m::type('array'))
            ->andThrow(new SourceAcquisitionException('manual file missing', 'RUN_SOURCE_MANUAL_FILE_NOT_FOUND'));

        $repo->shouldReceive('findById')
            ->once()
            ->with(11)
            ->andReturn($executing);

        $repo->shouldReceive('markFailed')
            ->once()
            ->with(11, 47, 25, 'Correction execution failed before safe publication; baseline current pointer preserved. failure_reason_code=RUN_SOURCE_MANUAL_FILE_NOT_FOUND', 2501, null)
            ->andReturn($failed);

        $this->app->instance(EodCorrectionRepository::class, $repo);
        $this->app->instance(MarketDataPipelineService::class, $pipeline);

        $command = new RunCorrectionCommand();
        $command->setLaravel($this->app);
        $tester = new CommandTester($command);

        $exitCode = $tester->execute([
            'correction_id' => 11,
            '--requested_date' => '2026-03-17',
            '--source_mode' => 'manual_file',
        ]);

        $display = $tester->getDisplay();

        $this->assertSame(1, $exitCode);
        $this->assertStringContainsString('reason_code=CORRECTION_FAILED', $display);
        $this->assertStringContainsString('failure_reason_code=RUN_SOURCE_MANUAL_FILE_NOT_FOUND', $display);
        $this->assertStringContainsString('correction_status=FAILED', $display);
        $this->assertStringContainsString('correction_outcome=FAILED', $display);
        $this->assertStringContainsString('correction_reseal_status=NOT_RESEALED', $display);
        $this->assertStringContainsString('baseline_publication_id=2501', $display);
        $this->assertStringContainsString('candidate_publication_switch=false', $display);
    }


    public function test_run_correction_command_renders_resealed_status_when_finalize_result_is_held_conflict(): void
    {
        $repo = m::mock(EodCorrectionRepository::class);
        $pipeline = m::mock(MarketDataPipelineService::class);

        $approved = (object) [
            'correction_id' => 8,
            'trade_date' => '2026-03-17',
            'status' => 'APPROVED',
        ];

        $resealed = (object) [
            'correction_id' => 8,
            'trade_date' => '2026-03-17',
            'status' => 'RESEALED',
        ];

        $repo->shouldReceive('findById')
            ->once()
            ->with(8)
            ->andReturn($approved);

        $pipeline->shouldReceive('runDaily')
            ->once()
            ->with('2026-03-17', 'manual_file', 8, m::type('array'))
            ->andReturn((object) [
                'run_id' => 45,
                'trade_date_requested' => '2026-03-17',
                'stage' => 'FINALIZE',
                'lifecycle_state' => 'COMPLETED',
                'terminal_status' => 'HELD',
                'publishability_state' => 'NOT_READABLE',
            ]);

        $repo->shouldReceive('findById')
            ->once()
            ->with(8)
            ->andReturn($resealed);

        $this->app->instance(EodCorrectionRepository::class, $repo);
        $this->app->instance(MarketDataPipelineService::class, $pipeline);

        $command = new RunCorrectionCommand();
        $command->setLaravel($this->app);
        $tester = new CommandTester($command);

        $exitCode = $tester->execute([
            'correction_id' => 8,
            '--requested_date' => '2026-03-17',
            '--source_mode' => 'manual_file',
        ]);

        $display = $tester->getDisplay();

        $this->assertSame(2, $exitCode);
        $this->assertStringContainsString('run_id=45', $display);
        $this->assertStringContainsString('requested_date=2026-03-17', $display);
        $this->assertStringContainsString('terminal_status=HELD', $display);
        $this->assertStringContainsString('publishability_state=NOT_READABLE', $display);
        $this->assertStringContainsString('correction_id=8', $display);
        $this->assertStringContainsString('correction_status=RESEALED', $display);
        $this->assertStringContainsString('command_outcome=HELD_NOT_READABLE', $display);
    }

    public function test_run_correction_command_renders_resealed_status_when_finalize_result_is_held_due_to_lock_conflict(): void
    {
        $repo = m::mock(EodCorrectionRepository::class);
        $pipeline = m::mock(MarketDataPipelineService::class);

        $approved = (object) [
            'correction_id' => 9,
            'trade_date' => '2026-03-17',
            'status' => 'APPROVED',
        ];

        $resealed = (object) [
            'correction_id' => 9,
            'trade_date' => '2026-03-17',
            'status' => 'RESEALED',
        ];

        $repo->shouldReceive('findById')
            ->once()
            ->with(9)
            ->andReturn($approved);

        $pipeline->shouldReceive('runDaily')
            ->once()
            ->with('2026-03-17', 'manual_file', 9, m::type('array'))
            ->andReturn((object) [
                'run_id' => 46,
                'trade_date_requested' => '2026-03-17',
                'stage' => 'FINALIZE',
                'lifecycle_state' => 'COMPLETED',
                'terminal_status' => 'HELD',
                'publishability_state' => 'NOT_READABLE',
                'reason_code' => 'RUN_LOCK_CONFLICT',
                'notes' => 'Promotion lost run ownership while switching current publication.',
            ]);

        $repo->shouldReceive('findById')
            ->once()
            ->with(9)
            ->andReturn($resealed);

        $this->app->instance(EodCorrectionRepository::class, $repo);
        $this->app->instance(MarketDataPipelineService::class, $pipeline);

        $command = new RunCorrectionCommand();
        $command->setLaravel($this->app);
        $tester = new CommandTester($command);

        $exitCode = $tester->execute([
            'correction_id' => 9,
            '--requested_date' => '2026-03-17',
            '--source_mode' => 'manual_file',
        ]);

        $display = $tester->getDisplay();

        $this->assertSame(2, $exitCode);
        $this->assertStringContainsString('run_id=46', $display);
        $this->assertStringContainsString('terminal_status=HELD', $display);
        $this->assertStringContainsString('publishability_state=NOT_READABLE', $display);
        $this->assertStringContainsString('reason_code=RUN_LOCK_CONFLICT', $display);
        $this->assertStringContainsString('notes=Promotion lost run ownership while switching current publication.', $display);
        $this->assertStringContainsString('correction_status=RESEALED', $display);
        $this->assertStringContainsString('command_outcome=HELD_NOT_READABLE', $display);
    }


    public function test_run_correction_command_rejects_non_approved_status_before_pipeline_execution(): void
    {
        $repo = m::mock(EodCorrectionRepository::class);
        $pipeline = m::mock(MarketDataPipelineService::class);

        $repo->shouldReceive('findById')
            ->once()
            ->with(5)
            ->andReturn((object) [
                'correction_id' => 5,
                'trade_date' => '2026-03-17',
                'status' => 'REQUESTED',
            ]);

        $pipeline->shouldNotReceive('runDaily');

        $this->app->instance(EodCorrectionRepository::class, $repo);
        $this->app->instance(MarketDataPipelineService::class, $pipeline);

        $command = new RunCorrectionCommand();
        $command->setLaravel($this->app);
        $tester = new CommandTester($command);

        $exitCode = $tester->execute([
            'correction_id' => 5,
            '--requested_date' => '2026-03-17',
            '--source_mode' => 'manual_file',
        ]);

        $display = $tester->getDisplay();

        $this->assertSame(1, $exitCode);
        $this->assertStringContainsString('Correction request must be APPROVED/EXECUTING/RESEALED before execution. Current status=REQUESTED', $display);
    }

    public function test_preapply_mismatch_refuses_without_marking_approved_correction_failed(): void
    {
        $repo = m::mock(EodCorrectionRepository::class);
        $repo->shouldReceive('findById')->once()->with(13)->andReturn((object) [
            'correction_id' => 13, 'trade_date' => '2026-03-17', 'status' => 'APPROVED',
        ]);
        $repo->shouldNotReceive('markFailed');
        $pipeline = m::mock(MarketDataPipelineService::class);
        $pipeline->shouldReceive('runDaily')->once()
            ->with('2026-03-17', 'manual_file', 13, m::type('array'))
            ->andThrow(new RuntimeException('COMMAND_CORRECTION_BASELINE_CHANGED'));
        $this->app->instance(EodCorrectionRepository::class, $repo);
        $this->app->instance(MarketDataPipelineService::class, $pipeline);
        $command = new RunCorrectionCommand();
        $command->setLaravel($this->app);
        $tester = new CommandTester($command);
        $exit = $tester->execute([
            'correction_id' => 13, '--requested_date' => '2026-03-17', '--source_mode' => 'manual_file',
        ]);
        $output = $tester->getDisplay();
        $this->assertSame(2, $exit, $output);
        $this->assertStringStartsWith('operation_mode=PRE_APPLY_DISCLOSURE', $output);
        $this->assertStringContainsString('reason_code=COMMAND_CORRECTION_PREFLIGHT_CHANGED', $output);
        $this->assertStringNotContainsString('correction_outcome=FAILED', $output);
    }

    public function test_run_correction_command_rejects_repair_executed_status_to_preserve_mode_isolation(): void
    {
        $repo = m::mock(EodCorrectionRepository::class);
        $pipeline = m::mock(MarketDataPipelineService::class);

        $repo->shouldReceive('findById')
            ->once()
            ->with(10)
            ->andReturn((object) [
                'correction_id' => 10,
                'trade_date' => '2026-03-17',
                'status' => 'REPAIR_EXECUTED',
            ]);

        $pipeline->shouldNotReceive('runDaily');

        $this->app->instance(EodCorrectionRepository::class, $repo);
        $this->app->instance(MarketDataPipelineService::class, $pipeline);

        $command = new RunCorrectionCommand();
        $command->setLaravel($this->app);
        $tester = new CommandTester($command);

        $exitCode = $tester->execute([
            'correction_id' => 10,
            '--requested_date' => '2026-03-17',
            '--source_mode' => 'manual_file',
        ]);

        $display = $tester->getDisplay();

        $this->assertSame(1, $exitCode);
        $this->assertStringContainsString('Correction request must be APPROVED/EXECUTING/RESEALED before execution. Current status=REPAIR_EXECUTED', $display);
    }

}
