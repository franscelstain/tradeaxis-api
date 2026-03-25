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

        $current = $repo->findCurrentPublicationForTradeDate('2026-03-20');
        $this->assertSame((int) $candidate->publication_id, (int) $current->publication_id);
        $this->assertSame(27, (int) $current->pointer_run_id);
        $this->assertSame('SUCCESS', $current->run_terminal_status);

        $old = DB::table('eod_publications')->where('publication_id', 10)->first();
        $this->assertSame(0, (int) $old->is_current);
    }
}
