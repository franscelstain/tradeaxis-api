<?php

use App\Infrastructure\Persistence\MarketData\EodPublicationRepository;
use Illuminate\Support\Facades\DB;
use Tests\Support\UsesMarketDataSqlite;

class PublicationRepositoryIntegrationTest extends TestCase
{
    use UsesMarketDataSqlite;

    protected function setUp(): void
    {
        parent::setUp();
        $this->bootMarketDataSqlite();

        DB::table('eod_runs')->insert([
            'run_id' => 25,
            'trade_date_requested' => '2026-03-20',
            'trade_date_effective' => '2026-03-20',
            'config_version' => 'cfg-old',
            'terminal_status' => 'SUCCESS',
            'publishability_state' => 'READABLE',
            'bars_rows_written' => 2,
            'indicators_rows_written' => 2,
            'eligibility_rows_written' => 2,
            'is_current_publication' => 1,
        ]);

        DB::table('eod_runs')->insert([
            'run_id' => 27,
            'trade_date_requested' => '2026-03-20',
            'trade_date_effective' => '2026-03-20',
            'config_version' => 'cfg-new',
            'terminal_status' => 'SUCCESS',
            'publishability_state' => 'READABLE',
            'bars_rows_written' => 2,
            'indicators_rows_written' => 2,
            'eligibility_rows_written' => 2,
            'is_current_publication' => 1,
        ]);

        DB::table('eod_publications')->insert([
            'publication_id' => 10,
            'trade_date' => '2026-03-20',
            'run_id' => 25,
            'publication_version' => 1,
            'is_current' => 1,
            'supersedes_publication_id' => null,
            'seal_state' => 'SEALED',
            'bars_batch_hash' => 'bars-old',
            'indicators_batch_hash' => 'ind-old',
            'eligibility_batch_hash' => 'elig-old',
            'sealed_at' => '2026-03-20 17:20:00',
            'created_at' => '2026-03-20 17:20:00',
            'updated_at' => '2026-03-20 17:20:00',
        ]);

        DB::table('eod_current_publication_pointer')->insert([
            'trade_date' => '2026-03-20',
            'publication_id' => 10,
            'run_id' => 25,
            'publication_version' => 1,
            'sealed_at' => '2026-03-20 17:20:00',
            'updated_at' => '2026-03-20 17:20:00',
        ]);
    }




    public function test_pointer_resolution_returns_null_when_run_current_mirror_disagrees_with_pointer_and_publication(): void
    {
        DB::table('eod_runs')
            ->where('run_id', 25)
            ->update([
                'is_current_publication' => 0,
                'updated_at' => '2026-03-20 17:25:00',
            ]);

        $repo = new EodPublicationRepository();

        $this->assertNull($repo->findPointerResolvedPublicationForTradeDate('2026-03-20'));
        $this->assertNull($repo->findCurrentPublicationForTradeDate('2026-03-20'));
        $this->assertNull($repo->findCorrectionBaselinePublicationForTradeDate('2026-03-20'));
        $this->assertNull($repo->findLatestReadablePublicationBefore('2026-03-21'));
    }

    public function test_pointer_resolution_returns_null_when_publication_trade_date_mismatches_pointer_trade_date(): void
    {
        DB::table('eod_publications')
            ->where('publication_id', 10)
            ->update([
                'trade_date' => '2026-03-19',
                'updated_at' => '2026-03-20 17:25:00',
            ]);

        $repo = new EodPublicationRepository();

        $this->assertNull($repo->findPointerResolvedPublicationForTradeDate('2026-03-20'));
        $this->assertNull($repo->findCurrentPublicationForTradeDate('2026-03-20'));
        $this->assertNull($repo->findCorrectionBaselinePublicationForTradeDate('2026-03-20'));
    }


    public function test_pointer_resolution_returns_null_when_pointer_publication_version_mismatches_pointed_publication(): void
    {
        $this->seedPointerPublicationVersionMismatchScenario();

        $repository = new EodPublicationRepository();

        $this->assertNull($repository->findPointerResolvedPublicationForTradeDate('2026-03-20'));
        $this->assertNull($repository->findCurrentPublicationForTradeDate('2026-03-20'));
        $this->assertNull($repository->findCorrectionBaselinePublicationForTradeDate('2026-03-20'));
        $this->assertNull($repository->findLatestReadablePublicationBefore('2026-03-21'));
    }


    public function test_pointer_resolution_returns_null_when_pointer_publication_version_mismatches_pointed_publication_version(): void
    {
        DB::table('eod_current_publication_pointer')
            ->where('trade_date', '2026-03-20')
            ->update([
                'publication_version' => 2,
                'updated_at' => '2026-03-20 17:25:00',
            ]);

        $repo = new EodPublicationRepository();

        $this->assertNull($repo->findPointerResolvedPublicationForTradeDate('2026-03-20'));
        $this->assertNull($repo->findCurrentPublicationForTradeDate('2026-03-20'));
        $this->assertNull($repo->findCorrectionBaselinePublicationForTradeDate('2026-03-20'));
        $this->assertNull($repo->findLatestReadablePublicationBefore('2026-03-21'));
    }

    public function test_pointer_resolution_returns_null_when_pointer_run_id_mismatches_pointed_publication_run(): void
    {
        DB::table('eod_current_publication_pointer')
            ->where('trade_date', '2026-03-20')
            ->update([
                'run_id' => 27,
                'updated_at' => '2026-03-20 17:25:00',
            ]);

        $repo = new EodPublicationRepository();

        $this->assertNull($repo->findPointerResolvedPublicationForTradeDate('2026-03-20'));
        $this->assertNull($repo->findCurrentPublicationForTradeDate('2026-03-20'));
        $this->assertNull($repo->findCorrectionBaselinePublicationForTradeDate('2026-03-20'));
        $this->assertNull($repo->findLatestReadablePublicationBefore('2026-03-21'));
    }

    public function test_candidate_seal_and_promote_updates_current_pointer_and_prior_publication(): void
    {
        $repo = new EodPublicationRepository();
        $run = App\Models\EodRun::query()->findOrFail(27);

        $candidate = $repo->getOrCreateCandidatePublication($run, 10);
        $this->assertSame(2, (int) $candidate->publication_version);
        $this->assertSame(0, (int) $candidate->is_current);

        $repo->updateCandidateHashes($candidate->publication_id, [
            'bars_batch_hash' => 'bars-new',
            'indicators_batch_hash' => 'ind-new',
            'eligibility_batch_hash' => 'elig-new',
        ]);

        $sealed = $repo->sealCandidatePublication($run, 'system');
        $this->assertSame('SEALED', $sealed->seal_state);
        $this->assertNotNull($sealed->sealed_at);

        $promoted = $repo->promoteCandidateToCurrent($run, 10);
        $this->assertSame(1, (int) $promoted->is_current);
        $this->assertSame(10, (int) $promoted->supersedes_publication_id);

        // Pakai method yang memang select alias run_terminal_status.
        $current = $repo->findPointerResolvedPublicationForTradeDate('2026-03-20');

        $this->assertNotNull($current);
        $this->assertSame((int) $candidate->publication_id, (int) $current->publication_id);
        $this->assertSame(27, (int) $current->pointer_run_id);
        $this->assertSame('SUCCESS', $current->run_terminal_status);
        $this->assertSame('READABLE', $current->run_publishability_state);

        $old = DB::table('eod_publications')->where('publication_id', 10)->first();
        $this->assertNotNull($old);
        $this->assertSame(0, (int) $old->is_current);
    }
}