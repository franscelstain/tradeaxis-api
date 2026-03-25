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

        $this->assertSame('CANCELLED', $cancelled->status);
        $this->assertSame('unchanged artifacts', $cancelled->final_outcome_note);
        $this->assertSame(28, (int) $cancelled->prior_run_id);
        $this->assertSame(30, (int) $cancelled->new_run_id);
    }
}
