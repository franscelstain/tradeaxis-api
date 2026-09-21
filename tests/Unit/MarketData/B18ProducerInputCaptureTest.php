<?php

use App\Infrastructure\Persistence\MarketData\EodRunRepository;
use App\Infrastructure\Persistence\MarketData\MarketDataConfigSnapshotRepository;
use App\Infrastructure\Persistence\MarketData\RunInputCaptureRepository;
use Illuminate\Support\Facades\DB;
use Tests\Support\UsesMarketDataSqlite;

trait B18ProducerInputCaptureAssertions
{
    private function owningRun()
    {
        return (new EodRunRepository())->getOrCreateOwningRun('2026-03-24', 'api', 'INGEST_BARS', null, 'c1-capture-test');
    }

    public function test_creation_reuse_promote_and_as_known_capture_the_selected_config(): void
    {
        $runs = new EodRunRepository();
        $capture = new RunInputCaptureRepository();
        $run = $this->owningRun();
        $snapshot = (new MarketDataConfigSnapshotRepository())->find($run->config_snapshot_id);
        $rows = $capture->forRun($run->run_id);
        $this->assertCount(1, $rows);
        $this->assertSame($snapshot['resolved_config_json'], $capture->verify($rows[0])['rows'][0]['resolved_config_json']);
        $before = $rows[0];
        config()->set('market_data.governance.build_id', 'c1-second-build');
        $reused = $this->owningRun();
        $this->assertSame($run->run_id, $reused->run_id);
        $this->assertSame($before, $capture->forRun($run->run_id)[0], 'Reuse binds the original selected snapshot, not current config.');
        $promoted = $runs->createPromoteRunFromSeed($run, 'COMPUTE_INDICATORS', ['request_mode' => 'promote']);
        $this->assertNotSame($run->config_snapshot_id, $promoted->config_snapshot_id);
        $this->assertCount(1, $capture->forRun($promoted->run_id));
        $known = $runs->createAsKnownReplayRun('2026-03-24', '2099-01-01 00:00:00');
        $knownPayload = $capture->verify($capture->forRun($known->run_id)[0]);
        $this->assertSame('2099-01-01 00:00:00', $knownPayload['selection_context']['knowledge_cutoff_at']);
        $this->assertSame((int) $known->config_snapshot_id, (int) $knownPayload['rows'][0]['config_snapshot_id']);
    }

    public function test_identical_retry_is_idempotent_but_one_changed_member_conflicts(): void
    {
        $run = $this->owningRun(); $repository = new RunInputCaptureRepository();
        $selection = ['operation' => 'bars-window/v1', 'trade_date' => '2026-03-24'];
        $rows = [['ticker_id' => 1, 'close' => '100.0000'], ['ticker_id' => 2, 'close' => '200.0000']];
        $first = $repository->capture($run->run_id, 'INDICATORS', 'raw_history', $selection, $rows);
        $retry = $repository->capture($run->run_id, 'INDICATORS', 'raw_history', $selection, $rows, null, ['retry_attempt' => 2]);
        $this->assertSame($first, $retry);
        $rows[1]['close'] = '201.0000';
        try {
            $repository->capture($run->run_id, 'INDICATORS', 'raw_history', $selection, $rows);
            $this->fail('A single changed consumed member must conflict.');
        } catch (RuntimeException $e) {
            $this->assertStringContainsString('INPUT_CAPTURE_CONFLICT', $e->getMessage());
        }
        $stored = DB::table('md_run_input_captures')->where('input_capture_id', $first['input_capture_id'])->first();
        $this->assertSame($first['semantic_payload_json'], $stored->semantic_payload_json);
    }

    public function test_live_config_drift_is_rejected_before_consumption(): void
    {
        $run = $this->owningRun(); $repository = new RunInputCaptureRepository();
        $before = $repository->forRun($run->run_id);
        $this->assertSame($before[0], $repository->assertConsumedConfiguration($run));
        config()->set('market_data.governance.build_id', 'different-consumed-build');
        try { $repository->assertConsumedConfiguration($run); $this->fail('Producer drift must not inherit the stored config hash.'); }
        catch (RuntimeException $e) { $this->assertStringContainsString('INPUT_CAPTURE_LIVE_CONFIG_DIVERGENCE', $e->getMessage()); }
        $this->assertSame($before, $repository->forRun($run->run_id));
    }

    public function test_existing_unbound_run_is_not_reconstructed_from_current_config(): void
    {
        $run = $this->owningRun();
        DB::table('eod_runs')->where('run_id', $run->run_id)->update(['config_snapshot_id' => null]);
        try { $this->owningRun(); $this->fail('Existing missing identity must remain unavailable.'); }
        catch (RuntimeException $e) { $this->assertStringContainsString('INPUT_CAPTURE_CONFIG_MISSING', $e->getMessage()); }
        $this->assertNull(DB::table('eod_runs')->where('run_id', $run->run_id)->value('config_snapshot_id'));
    }

    public function test_empty_sets_require_explicit_source_and_population_basis(): void
    {
        $run = $this->owningRun(); $repository = new RunInputCaptureRepository();
        try {
            $repository->capture($run->run_id, 'INDICATORS', 'event_factor', ['operation' => 'events/v1'], []);
            $this->fail('Empty query output must not imply complete capture.');
        } catch (RuntimeException $e) {
            $this->assertStringContainsString('INPUT_CAPTURE_EMPTY_BASIS_REQUIRED', $e->getMessage());
        }
        $row = $repository->capture($run->run_id, 'INDICATORS', 'event_factor', ['operation' => 'events/v1'], [],
            ['reason' => 'NO_EVENTS_IN_RANGE', 'source' => 'md_corporate_action_revisions', 'evaluated_population' => 0]);
        $this->assertSame([], $repository->verify($row)['rows']);
        $this->assertSame(0, (int) $row['member_count']);
    }

    public function test_one_corrupt_count_or_payload_or_slot_is_rejected_on_read(): void
    {
        $repository = new RunInputCaptureRepository(); $run = $this->owningRun();
        $original = $repository->forRun($run->run_id)[0];
        foreach (['member_count' => 2, 'payload_hash' => str_repeat('0', 64), 'slot_hash' => str_repeat('0', 64), 'semantic_payload_json' => '{}'] as $field => $value) {
            $bad = $original; $bad[$field] = $value;
            try { $repository->verify($bad); $this->fail('Corrupt '.$field.' was accepted.'); }
            catch (RuntimeException $e) { $this->assertStringContainsString('INPUT_CAPTURE_INTEGRITY_FAILED', $e->getMessage()); }
        }
        $this->assertSame($original, $repository->forRun($run->run_id)[0]);
    }

    public function test_database_rejects_update_delete_and_upsert_without_changing_bytes(): void
    {
        $run = $this->owningRun(); $repository = new RunInputCaptureRepository();
        $before = $repository->forRun($run->run_id);
        $id = $before[0]['input_capture_id'];
        foreach (['update', 'delete', 'upsert'] as $operation) {
            try {
                $query = DB::table('md_run_input_captures')->where('input_capture_id', $id);
                if ($operation === 'delete') $query->delete();
                elseif ($operation === 'update') $query->update(['member_count' => 999]);
                else DB::table('md_run_input_captures')->updateOrInsert(['input_capture_id' => $id], ['member_count' => 999]);
                $this->fail('Direct '.$operation.' must be refused.');
            } catch (\Illuminate\Database\QueryException $e) {
                $this->assertStringContainsString('INPUT_CAPTURE_IMMUTABLE', $e->getMessage());
            }
            $this->assertSame($before, $repository->forRun($run->run_id));
        }
    }

    public function test_failed_promote_identity_capture_rolls_back_the_created_run(): void
    {
        $run = $this->owningRun();
        $count = DB::table('eod_runs')->count();
        try {
            (new EodRunRepository())->createPromoteRunFromSeed($run, 'COMPUTE_INDICATORS', ['config_hash' => str_repeat('0', 64)]);
            $this->fail('A differing claimed config cannot be captured.');
        } catch (RuntimeException $e) {
            $this->assertStringContainsString('INPUT_CAPTURE_CONFIG_IDENTITY_MISMATCH', $e->getMessage());
        }
        $this->assertSame($count, DB::table('eod_runs')->count());
    }

    public function test_rollback_refuses_to_erase_capture_proof(): void
    {
        $run = $this->owningRun();
        require_once dirname(__DIR__, 3).'/database/migrations/2026_09_16_000001_add_producer_bound_input_captures.php';
        try { (new AddProducerBoundInputCaptures())->down(); $this->fail('Destructive rollback was allowed.'); }
        catch (RuntimeException $e) { $this->assertStringContainsString('INPUT_CAPTURE_ROLLBACK_WOULD_ERASE_PROOF', $e->getMessage()); }
        $this->assertCount(1, (new RunInputCaptureRepository())->forRun($run->run_id));
    }
}

class B18ProducerInputCaptureTest extends TestCase
{
    use UsesMarketDataSqlite, B18ProducerInputCaptureAssertions;
    protected function setUp(): void { parent::setUp(); $this->bootMarketDataSqlite(); }
    protected function tearDown(): void { $this->tearDownMarketDataSqlite(); parent::tearDown(); }
}
