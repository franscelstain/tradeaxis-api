<?php

use App\Infrastructure\Persistence\MarketData\EodRunRepository;
use App\Infrastructure\Persistence\MarketData\ProducerInputScope;
use App\Infrastructure\Persistence\MarketData\ProducerInputCompletionManifest;
use App\Infrastructure\Persistence\MarketData\ProducerSourceObservationJournal;
use App\Infrastructure\Persistence\MarketData\RunInputCaptureRepository;
use App\Infrastructure\Persistence\MarketData\SourceObservationRepository;
use Illuminate\Support\Facades\DB;
use Tests\Support\UsesMarketDataSqlite;

class B18ProducerSourceObservationJournalTest extends TestCase
{
    use UsesMarketDataSqlite;

    protected function setUp(): void
    {
        parent::setUp(); $this->bootMarketDataSqlite(); \Carbon\Carbon::setTestNow('2026-03-25 10:30:00');
    }

    protected function tearDown(): void
    {
        \Carbon\Carbon::setTestNow(); $this->tearDownMarketDataSqlite(); parent::tearDown();
    }

    private function runContext() { return (new EodRunRepository())->getOrCreateOwningRun('2026-03-24', 'api', 'INGEST_BARS', null, 'c06'); }

    private function envelope($run): array
    {
        return ['run_id' => $run->run_id, 'attempt_uid' => 'c06-attempt', 'requested_trade_date' => '2026-03-24',
            'source_mode' => 'api', 'source_name' => 'C06', 'provider' => 'fixture', 'provider_symbol' => 'ALPHA',
            'sanitized_request_identity' => 'https://example.test/bars?token=private-secret',
            'adapter_version' => 'c06-v1', 'payload' => '{"token":"private-secret","data":[]}',
            'acquired_at' => '2026-03-25 10:00:00'];
    }

    private function repository(): RunInputCaptureRepository
    {
        // Only build capture is unrelated here; input storage and verification are real SQLite.
        return new class extends RunInputCaptureRepository {
            public function captureRegistryVersions($run): array { return []; }
        };
    }

    private function journals($run, $repo): array
    {
        $out = [];
        foreach ($repo->forRun((int) $run->run_id) as $row) {
            $p = $repo->verify($row);
            if ($p['selection_context']['operation'] === 'source-observation-journal/v1') $out[] = $p;
        }
        return $out;
    }

    public function test_all_persisted_outcomes_and_superseding_provenance_are_bound_without_inventing_states(): void
    {
        $run = $this->runContext(); $repo = $this->repository(); $source = new SourceObservationRepository();
        $ids = ProducerInputScope::during($run, 'ACQUISITION', 'c06-journal/v1', function () use ($run, $source) {
            $capture = $source->capture($this->envelope($run)); $ids = [$capture['source_observation_id']];
            foreach ([['ACCEPTED', null], ['REJECTED', 'BAR_INVALID_SOURCE_ROW'], ['STALE', 'SOURCE_DATA_STALE'],
                ['REJECTED', 'SOURCE_SCHEMA_INVALID'], ['FAILED', 'SOURCE_FILE_MISSING']] as [$state, $reason]) {
                $out = $source->recordOutcome($capture, $state, $reason); $ids[] = $out['source_observation_id'];
            }
            $out = $source->recordOutcome($capture, 'ACCEPTED', null, ['supersedes_observation_id' => $ids[1]]);
            $ids[] = $out['source_observation_id']; return $ids;
        }, $repo, false);
        $journals = $this->journals($run, $repo); $this->assertCount(7, $journals);
        $actual = [];
        foreach ($journals as $p) {
            $j = $p['rows'][0]; $id = $j['observation']['source_observation_id']; $actual[] = (int) $id;
            $stored = (array) DB::table('md_source_observations')->where('source_observation_id', $id)->first();
            $this->assertSame(RunInputCaptureRepository::canonicalJson($stored), RunInputCaptureRepository::canonicalJson($j['observation']));
            ProducerSourceObservationJournal::assertValid($j, (int) $id);
            $this->assertStringNotContainsString('private-secret', RunInputCaptureRepository::canonicalJson($j));
            foreach (['parent_observation_id', 'supersedes_observation_id'] as $field) if ($stored[$field] !== null) {
                $ref = (array) DB::table('md_source_observations')->where('source_observation_id', $stored[$field])->first();
                $this->assertSame(RunInputCaptureRepository::canonicalJson($ref), RunInputCaptureRepository::canonicalJson($j['references'][$field]));
            }
        }
        sort($actual); sort($ids); $this->assertSame($ids, $actual);
        $manifest = (new ProducerInputCompletionManifest())->inspect($run, $repo);
        $this->assertContains('source_observations.no_producer_ingress_population', $manifest['missing_paths']);
    }

    public function test_unscoped_recorder_does_not_require_a_producer_run(): void
    {
        $source = new SourceObservationRepository(); $run = (object) ['run_id' => null];
        $capture = $source->capture($this->envelope($run));
        $this->assertTrue($capture['persisted']); $this->assertSame(0, DB::table('md_run_input_captures')->count());
    }

    public function test_retry_same_journal_is_idempotent_and_changed_one_outcome_conflicts(): void
    {
        $run = $this->runContext(); $repo = $this->repository();
        $capture = (new SourceObservationRepository())->capture($this->envelope($run));
        $id = (int) $capture['source_observation_id'];
        $read = static function () use ($id) { ProducerSourceObservationJournal::record($id); };
        ProducerInputScope::during($run, 'ACQUISITION', 'retry/v1', $read, $repo, false);
        $before = $repo->forRun((int) $run->run_id);
        ProducerInputScope::during($run, 'ACQUISITION', 'retry/v1', $read, $repo, false);
        $this->assertSame($before, $repo->forRun((int) $run->run_id));
        DB::table('md_source_observations')->where('source_observation_id', $id)->update(['reason_code' => 'DAMAGED']);
        try { ProducerInputScope::during($run, 'ACQUISITION', 'retry/v1', $read, $repo, false); $this->fail('Expected conflict'); }
        catch (\RuntimeException $e) { $this->assertStringContainsString('INPUT_CAPTURE_CONFLICT', $e->getMessage()); }
        $this->assertSame($before, $repo->forRun((int) $run->run_id));
    }

    public function test_single_missing_journal_is_detected_with_real_storage_savepoint(): void
    {
        $run = $this->runContext(); $repo = $this->repository(); $source = new SourceObservationRepository();
        try {
            ProducerInputScope::during($run, 'ACQUISITION', 'omission/v1', function () use ($run, $source) {
                $first = $source->capture($this->envelope($run));
                DB::beginTransaction(); $source->recordOutcome($first, 'REJECTED', 'SOURCE_SCHEMA_INVALID'); DB::rollBack();
            }, $repo, false);
            $this->fail('Missing declared journal must fail');
        } catch (\RuntimeException $e) { $this->assertStringContainsString('INPUT_CAPTURE_OBSERVATION_JOURNAL_COMPLETION_MISMATCH', $e->getMessage()); }
        $this->assertCount(1, $this->journals($run, $repo));
    }

    public function test_adapter_wrapping_a_capture_failure_cannot_mask_its_identity(): void
    {
        $run = $this->runContext(); $repo = $this->repository();
        $this->expectExceptionMessage('INPUT_CAPTURE_OBSERVATION_MISSING');
        ProducerInputScope::during($run, 'ACQUISITION', 'wrapped/v1', static function () {
            try { ProducerSourceObservationJournal::record(999999); }
            catch (\Throwable $e) { throw new \RuntimeException('SOURCE_OBSERVATION_PERSISTENCE_FAILED', 0, $e); }
        }, $repo, false);
    }

    public function test_failed_acquisition_journals_survive_and_do_not_block_later_attempt(): void
    {
        $run = $this->runContext(); $repo = $this->repository(); $source = new SourceObservationRepository();
        try {
            ProducerInputScope::during($run, 'ACQUISITION', 'acquire/v1', function () use ($run, $source) {
                $source->recordTransportFailure($this->envelope($run), 'SOURCE_TIMEOUT');
                throw new \RuntimeException('SOURCE_TIMEOUT');
            }, $repo, false);
            $this->fail('Failure must propagate');
        } catch (\RuntimeException $e) { $this->assertSame('SOURCE_TIMEOUT', $e->getMessage()); }
        $before = $this->journals($run, $repo); $this->assertCount(2, $before);
        ProducerInputScope::during($run, 'ACQUISITION', 'acquire/v1', function () use ($run, $source) {
            $source->recordOutcome($source->capture($this->envelope($run)), 'ACCEPTED');
        }, $repo, false);
        $after = $this->journals($run, $repo); $this->assertCount(4, $after);
        foreach ($before as $journal) $this->assertContains($journal, $after);
        $this->assertFalse(ProducerInputScope::active());
    }

    public function test_content_validator_rejects_one_missing_coordinate_and_wrong_parent_payload(): void
    {
        $run = $this->runContext(); $repo = $this->repository(); $source = new SourceObservationRepository();
        ProducerInputScope::during($run, 'ACQUISITION', 'content/v1', function () use ($run, $source) {
            $source->recordOutcome($source->capture($this->envelope($run)), 'REJECTED', 'SOURCE_SCHEMA_INVALID');
        }, $repo, false);
        $journals = $this->journals($run, $repo); $this->assertCount(2, $journals);
        foreach ($journals as $p) if ($p['rows'][0]['observation']['parent_observation_id'] !== null) $valid = $p['rows'][0];
        $this->assertNotEmpty($valid);
        foreach (['coordinate', 'parent'] as $mutation) {
            $bad = $valid;
            if ($mutation === 'coordinate') unset($bad['observation']['acquired_at']);
            else $bad['references']['parent_observation_id']['payload_hash'] = str_repeat('f', 64);
            try { ProducerSourceObservationJournal::assertValid($bad, (int) $valid['observation']['source_observation_id']); $this->fail('Damaged content accepted'); }
            catch (\RuntimeException $e) { $this->assertStringContainsString('INPUT_CAPTURE_OBSERVATION_', $e->getMessage()); }
        }
    }
}
