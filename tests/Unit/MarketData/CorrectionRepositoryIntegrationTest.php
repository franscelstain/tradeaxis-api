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

        $executing = $repo->markExecuting($created->correction_id, 25, 27);
        $this->assertSame('EXECUTING', $executing->status);
        $this->assertSame(25, (int) $executing->prior_run_id);
        $this->assertSame(27, (int) $executing->new_run_id);
        $this->assertSame(1, (int) $executing->execution_count);
        $this->assertNotNull($executing->last_executed_at);

        $resealed = $repo->markResealed($created->correction_id, 27);
        $this->assertSame('RESEALED', $resealed->status);
        $this->assertSame(27, (int) $resealed->new_run_id);

        $published = $repo->markPublished($created->correction_id, 27, 25, 'publication switched');
        $this->assertSame('PUBLISHED', $published->status);
        $this->assertSame('publication switched', $published->final_outcome_note);
        $this->assertNotNull($published->published_at);

        $persisted = DB::table('eod_dataset_corrections')->where('correction_id', $created->correction_id)->first();
        $this->assertSame('PUBLISHED', $persisted->status);
        $this->assertSame(25, (int) $persisted->prior_run_id);
        $this->assertSame(27, (int) $persisted->new_run_id);
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


    public function test_correction_repository_allows_repair_candidate_rerun_but_blocks_current_rerun_after_consumption(): void
    {
        $repo = new EodCorrectionRepository();

        $created = $repo->createRequest('2026-03-20', 'READABILITY_FIX', 'Iterative repair', 'system');
        $approved = $repo->approve($created->correction_id, 'reviewer');

        $eligibleRepair = $repo->canExecuteCorrection($approved->correction_id, '2026-03-20', 'repair_candidate');
        $this->assertSame('APPROVED', $eligibleRepair->status);

        $repo->markExecuting($approved->correction_id, 25, 27, 'repair_candidate');
        $repairExecuted = $repo->markRepairExecuted($approved->correction_id, 27, 25, 'repair iteration completed');

        $this->assertSame('REPAIR_EXECUTED', $repairExecuted->status);
        $this->assertSame(1, (int) $repairExecuted->execution_count);
        $this->assertNotNull($repairExecuted->last_executed_at);

        $eligibleRepairRerun = $repo->canExecuteCorrection($approved->correction_id, '2026-03-20', 'repair_candidate');
        $this->assertSame('REPAIR_EXECUTED', $eligibleRepairRerun->status);

        try {
            $repo->canExecuteCorrection($approved->correction_id, '2026-03-20', 'correction_current');
            $this->fail('Expected correction_current execution to require fresh approval after repair execution.');
        } catch (\RuntimeException $e) {
            $this->assertSame('Correction request must be APPROVED before execution.', $e->getMessage());
        }

        $reapproved = $repo->approve($approved->correction_id, 'reviewer-2');
        $this->assertSame('APPROVED', $reapproved->status);

        $repo->markExecuting($approved->correction_id, 27, 28, 'correction_current');
        $published = $repo->markPublished($approved->correction_id, 28, 27, 'current replaced');
        $this->assertSame('PUBLISHED', $published->status);
        $this->assertNotNull($published->current_consumed_at);

        try {
            $repo->canExecuteCorrection($approved->correction_id, '2026-03-20', 'repair_candidate');
            $this->fail('Expected consumed correction to block repair rerun.');
        } catch (\RuntimeException $e) {
            $this->assertSame('Correction request is already consumed for correction_current execution and cannot be executed again.', $e->getMessage());
        }
    }

}
