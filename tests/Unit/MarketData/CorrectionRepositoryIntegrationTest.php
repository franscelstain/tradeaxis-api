<?php

use App\Infrastructure\Persistence\MarketData\EodCorrectionRepository;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Tests\Support\UsesMarketDataSqlite;

class CorrectionRepositoryIntegrationTest extends TestCase
{
    use UsesMarketDataSqlite;

    protected function setUp(): void
    {
        parent::setUp();
        $this->bootMarketDataSqlite();
        Carbon::setTestNow('2026-03-25 10:30:00');
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    public function test_correction_repository_persists_full_request_to_publish_lifecycle(): void
    {
        $repo = new EodCorrectionRepository();

        $created = $repo->createRequest('2026-03-20', 'READABILITY_FIX', 'Need reseal', 'system');
        $this->assertSame('REQUESTED', $created->status);
        $this->assertSame('system', $created->requested_by);

        $approved = $repo->approve($created->correction_id, 'reviewer');
        $this->assertSame('APPROVED', $approved->status);
        $this->assertSame('reviewer', $approved->approved_by);

        $executing = $repo->markExecuting($created->correction_id, 25, 27, 'correction_current', 2501, null);
        $this->assertSame('EXECUTING', $executing->status);
        $this->assertSame(25, (int) $executing->prior_run_id);
        $this->assertSame(27, (int) $executing->new_run_id);
        $this->assertSame(2501, (int) $executing->baseline_publication_id);
        $this->assertNull($executing->replacement_publication_id);
        $this->assertSame(1, (int) $executing->execution_count);
        $this->assertNotNull($executing->last_executed_at);

        $resealed = $repo->markResealed($created->correction_id, 27, 2701);
        $this->assertSame('RESEALED', $resealed->status);
        $this->assertSame(27, (int) $resealed->new_run_id);
        $this->assertSame(2701, (int) $resealed->replacement_publication_id);

        $published = $repo->markPublished($created->correction_id, 27, 25, 'publication switched', 2501, 2701);
        $this->assertSame('PUBLISHED', $published->status);
        $this->assertSame('publication switched', $published->final_outcome_note);
        $this->assertSame(2501, (int) $published->baseline_publication_id);
        $this->assertSame(2701, (int) $published->replacement_publication_id);
        $this->assertNotNull($published->published_at);

        $persisted = DB::table('eod_dataset_corrections')->where('correction_id', $created->correction_id)->first();
        $this->assertSame('PUBLISHED', $persisted->status);
        $this->assertSame(25, (int) $persisted->prior_run_id);
        $this->assertSame(27, (int) $persisted->new_run_id);
        $this->assertSame(2501, (int) $persisted->baseline_publication_id);
        $this->assertSame(2701, (int) $persisted->replacement_publication_id);
    }

    public function test_correction_repository_can_cancel_with_outcome_note(): void
    {
        $repo = new EodCorrectionRepository();
        $created = $repo->createRequest('2026-03-20', 'NO_CHANGE', 'No diff', 'system');

        $cancelled = $repo->markCancelled($created->correction_id, 30, 28, 'unchanged artifacts');

        $this->assertSame('CONSUMED_CURRENT', $cancelled->status);
        $this->assertNotNull($cancelled->current_consumed_at);
        $this->assertSame('unchanged artifacts', $cancelled->final_outcome_note);
        $this->assertSame(28, (int) $cancelled->prior_run_id);
        $this->assertSame(30, (int) $cancelled->new_run_id);
    }

    public function test_correction_repository_marks_failed_without_consuming_current_pointer(): void
    {
        $repo = new EodCorrectionRepository();
        $created = $repo->createRequest('2026-03-20', 'READABILITY_FIX', 'Source failure', 'system', 2501, 25);
        $approved = $repo->approve($created->correction_id, 'reviewer');
        $repo->markExecuting($approved->correction_id, 25, 47, 'correction_current', 2501, null);

        $failed = $repo->markFailed(
            $approved->correction_id,
            47,
            25,
            'Correction execution failed before safe publication; baseline current pointer preserved. failure_reason_code=RUN_SOURCE_MANUAL_FILE_NOT_FOUND',
            2501,
            null
        );

        $this->assertSame('FAILED', $failed->status);
        $this->assertSame(25, (int) $failed->prior_run_id);
        $this->assertSame(47, (int) $failed->new_run_id);
        $this->assertSame(2501, (int) $failed->baseline_publication_id);
        $this->assertNull($failed->replacement_publication_id);
        $this->assertNull($failed->current_consumed_at);
        $this->assertStringContainsString('RUN_SOURCE_MANUAL_FILE_NOT_FOUND', $failed->final_outcome_note);

        try {
            $repo->canExecuteCorrection($approved->correction_id, '2026-03-20', 'correction_current');
            $this->fail('Expected failed correction_current request to require a new approval/request before execution.');
        } catch (\RuntimeException $e) {
            $this->assertSame('Correction request must be APPROVED before execution.', $e->getMessage());
        }
    }


    public function test_correction_repository_allows_repair_candidate_rerun_increments_metadata_and_preserves_non_current_state_until_current_publish(): void
    {
        $repo = new EodCorrectionRepository();

        $created = $repo->createRequest('2026-03-20', 'READABILITY_FIX', 'Iterative repair', 'system');
        $approved = $repo->approve($created->correction_id, 'reviewer');

        $eligibleRepair = $repo->canExecuteCorrection($approved->correction_id, '2026-03-20', 'repair_candidate');
        $this->assertSame('APPROVED', $eligibleRepair->status);

        $repo->markExecuting($approved->correction_id, 25, 27, 'repair_candidate');
        $firstRepair = $repo->markRepairExecuted($approved->correction_id, 27, 25, 'repair iteration completed');

        $this->assertSame('REPAIR_EXECUTED', $firstRepair->status);
        $this->assertSame(1, (int) $firstRepair->execution_count);
        $this->assertSame(25, (int) $firstRepair->prior_run_id);
        $this->assertSame(27, (int) $firstRepair->new_run_id);
        $this->assertNotNull($firstRepair->last_executed_at);
        $this->assertNull($firstRepair->current_consumed_at);

        $eligibleRepairRerun = $repo->canExecuteCorrection($approved->correction_id, '2026-03-20', 'repair_candidate');
        $this->assertSame('REPAIR_EXECUTED', $eligibleRepairRerun->status);

        $repo->markExecuting($approved->correction_id, 27, 29, 'repair_candidate');
        $secondRepair = $repo->markRepairExecuted($approved->correction_id, 29, 27, 'repair rerun completed');

        $this->assertSame('REPAIR_EXECUTED', $secondRepair->status);
        $this->assertSame(2, (int) $secondRepair->execution_count);
        $this->assertSame(27, (int) $secondRepair->prior_run_id);
        $this->assertSame(29, (int) $secondRepair->new_run_id);
        $this->assertSame('repair rerun completed', $secondRepair->final_outcome_note);
        $this->assertNotNull($secondRepair->last_executed_at);
        $this->assertNull($secondRepair->current_consumed_at);

        try {
            $repo->canExecuteCorrection($approved->correction_id, '2026-03-20', 'correction_current');
            $this->fail('Expected correction_current execution to require fresh approval after repair execution.');
        } catch (\RuntimeException $e) {
            $this->assertSame('Correction request must be APPROVED before execution.', $e->getMessage());
        }

        $persistedAfterRepair = DB::table('eod_dataset_corrections')->where('correction_id', $approved->correction_id)->first();
        $this->assertSame('REPAIR_EXECUTED', $persistedAfterRepair->status);
        $this->assertSame(2, (int) $persistedAfterRepair->execution_count);
        $this->assertNull($persistedAfterRepair->current_consumed_at);

        $reapproved = $repo->approve($approved->correction_id, 'reviewer-2');
        $this->assertSame('APPROVED', $reapproved->status);

        $repo->markExecuting($approved->correction_id, 29, 30, 'correction_current');
        $published = $repo->markPublished($approved->correction_id, 30, 29, 'current replaced');
        $this->assertSame('PUBLISHED', $published->status);
        $this->assertSame(3, (int) $published->execution_count);
        $this->assertNotNull($published->current_consumed_at);

        try {
            $repo->canExecuteCorrection($approved->correction_id, '2026-03-20', 'repair_candidate');
            $this->fail('Expected consumed correction to block repair rerun.');
        } catch (\RuntimeException $e) {
            $this->assertSame('Correction request is already consumed for correction_current execution and cannot be executed again.', $e->getMessage());
        }
    }


    public function test_correction_repository_blocks_second_correction_current_execution_after_current_consumption(): void
    {
        $repo = new EodCorrectionRepository();

        $created = $repo->createRequest('2026-03-20', 'READABILITY_FIX', 'Single-use current correction', 'system');
        $approved = $repo->approve($created->correction_id, 'reviewer');

        $eligibleCurrent = $repo->canExecuteCorrection($approved->correction_id, '2026-03-20', 'correction_current');
        $this->assertSame('APPROVED', $eligibleCurrent->status);

        $repo->markExecuting($approved->correction_id, 25, 27, 'correction_current');
        $consumed = $repo->markConsumedForCurrent($approved->correction_id, 27, 25, 'current correction consumed');

        $this->assertSame('CONSUMED_CURRENT', $consumed->status);
        $this->assertSame(1, (int) $consumed->execution_count);
        $this->assertSame(25, (int) $consumed->prior_run_id);
        $this->assertSame(27, (int) $consumed->new_run_id);
        $this->assertNotNull($consumed->last_executed_at);
        $this->assertNotNull($consumed->current_consumed_at);

        try {
            $repo->canExecuteCorrection($approved->correction_id, '2026-03-20', 'correction_current');
            $this->fail('Expected consumed correction_current request to block second current execution.');
        } catch (\RuntimeException $e) {
            $this->assertSame('Correction request is already consumed for correction_current execution and cannot be executed again.', $e->getMessage());
        }

        try {
            $repo->approve($approved->correction_id, 'reviewer-2');
            $this->fail('Expected consumed correction_current request to block re-approval.');
        } catch (\RuntimeException $e) {
            $this->assertSame('Correction request is already consumed for correction_current execution and cannot be approved again.', $e->getMessage());
        }
    }

}
