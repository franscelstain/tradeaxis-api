<?php

use App\Infrastructure\Persistence\MarketData\EodPublicationRepository;
use App\Infrastructure\Persistence\MarketData\EodRunRepository;
use App\Models\EodRun;
use Illuminate\Support\Facades\DB;

class B19OwnerFenceMariaDbTest extends TestCase
{
    use \Tests\Support\RetainsB19ObservedEvidence;

    private string $database;
    private array $connectionEvidence = [];

    protected function setUp(): void
    {
        parent::setUp();
        $base = config('database.connections.mysql');
        $this->assertSame('tradeaxis_testing', $base['database'], 'Owner proof may only use the designated test server/database.');
        $this->database = 'tradeaxis_testing_b19_owner_'.bin2hex(random_bytes(6));
        $control = DB::connection('mysql');
        $this->assertStringContainsStringIgnoringCase('mariadb', $control->select('SELECT VERSION() AS version')[0]->version);
        $control->statement('CREATE DATABASE `'.$this->database.'` CHARACTER SET utf8mb4 COLLATE utf8mb4_bin');
        config()->set('database.connections.b19_owner', array_merge($base, ['database' => $this->database]));
        config()->set('database.default', 'b19_owner');
        DB::purge('b19_owner');
        $tables = $control->select("SELECT TABLE_NAME AS name FROM information_schema.TABLES WHERE TABLE_SCHEMA='tradeaxis_testing' AND TABLE_TYPE='BASE TABLE'");
        foreach ($tables as $entry) {
            $table = $entry->name;
            if (in_array($table, ['eod_run_owner_fences', 'eod_observer_fences'], true)) continue;
            if (! preg_match('/^[a-z0-9_]+$/D', $table)) throw new RuntimeException('UNSAFE_OWNER_TEST_TABLE');
            DB::connection('b19_owner')->statement('CREATE TABLE `'.$table.'` LIKE `tradeaxis_testing`.`'.$table.'`');
        }
        DB::connection('b19_owner')->statement('INSERT INTO eod_reason_codes SELECT * FROM `tradeaxis_testing`.`eod_reason_codes`');
        require_once base_path('database/migrations/2026_10_19_000001_create_eod_run_owner_fences.php');
        (new CreateEodRunOwnerFences())->up();
        require_once base_path('database/migrations/2026_10_20_000002_add_date_owner_lease_evidence.php');
        (new AddDateOwnerLeaseEvidence())->up();
        require_once base_path('database/migrations/2026_10_23_000001_add_execution_credentials_and_observer_fences.php');
        (new AddExecutionCredentialsAndObserverFences())->up();
        require_once base_path('database/migrations/2026_10_25_000001_add_projection_observer_identity.php');
        (new AddProjectionObserverIdentity())->up();
    }

    protected function tearDown(): void
    {
        if (isset($this->database) && DB::connection('b19_owner')->getSchemaBuilder()->hasTable('eod_runs')) {
            config()->set('database.default', 'b19_owner');
            $this->retainObservedWorld($this->getName(), DB::table('eod_runs')->pluck('run_id')->all(), ['connections' => $this->connectionEvidence]);
        }
        DB::purge('b19_owner');
        if (isset($this->database)) {
            if (! preg_match('/^tradeaxis_testing_b19_owner_[a-f0-9]{12}$/D', $this->database)) {
                throw new RuntimeException('UNSAFE_OWNER_TEST_DATABASE');
            }
            DB::connection('mysql')->statement('DROP DATABASE `'.$this->database.'`');
        }
        parent::tearDown();
    }

    public function test_mariadb_migration_and_two_process_date_owner_fence(): void
    {
        $db = DB::connection('b19_owner');
        $this->assertSame('mysql', $db->getDriverName());
        $status = $db->select("SHOW TABLE STATUS LIKE 'eod_run_owner_fences'");
        $this->assertCount(1, $status);
        $this->assertSame('InnoDB', $status[0]->Engine);
        $indexes = $db->select('SHOW INDEX FROM eod_run_owner_fences');
        $this->assertCount(1, $indexes);
        $this->assertSame('trade_date_requested', $indexes[0]->Column_name);
        $terminal = $db->select("SHOW COLUMNS FROM eod_runs LIKE 'terminal_status'");
        $this->assertCount(1, $terminal);
        $this->assertStringContainsString("'SKIPPED'", $terminal[0]->Type);
        $this->assertStringContainsString("'BLOCKED'", $terminal[0]->Type);
        $unfenced = new EodRun();
        $unfenced->run_id = 99001;
        $unfenced->trade_date_requested = '2026-03-22';
        $unfenced->request_mode = 'full_publish';
        foreach (['sealCandidatePublication', 'promoteCandidateToCurrent'] as $method) {
            try {
                (new EodPublicationRepository())->$method($unfenced, null);
                $this->fail('MariaDB direct publication entry admitted a missing owner fence: '.$method);
            } catch (RuntimeException $e) {
                $this->assertStringContainsString('RUN_OWNER_FENCE_MISSING', $e->getMessage());
            }
        }
        $this->assertSame(0, $db->table('eod_run_owner_fences')->where('trade_date_requested', '2026-03-22')->count());

        // Hold an uncommitted date row so both independent PHP processes wait on the same InnoDB key.
        $db->beginTransaction();
        $db->table('eod_run_owner_fences')->insert([
            'trade_date_requested' => '2026-03-21', 'owner_run_id' => null, 'updated_at' => \Carbon\Carbon::now(),
        ]);
        $workers = [];
        try {
            foreach ([['manual_file', 'full_publish'], ['api', 'correction']] as [$source, $mode]) {
                $pipes = [];
                $process = proc_open([PHP_BINARY, base_path('tests/Support/MarketDataMariaDbOwnerWorker.php'),
                    $this->database, $source, $mode],
                    [0 => ['pipe', 'r'], 1 => ['pipe', 'w'], 2 => ['pipe', 'w']], $pipes, base_path());
                $this->assertIsResource($process);
                fclose($pipes[0]);
                $workers[] = [$process, $pipes];
            }
            foreach ($workers as [, $pipes]) {
                $this->assertSame("READY\n", fgets($pipes[1]));
            }
            usleep(150000);
            $db->commit();
            $outcomes = [];
            foreach ($workers as [$process, $pipes]) {
                $outcomes[] = json_decode(trim(stream_get_contents($pipes[1])), true);
                $error = stream_get_contents($pipes[2]);
                fclose($pipes[1]); fclose($pipes[2]);
                $this->assertSame(0, proc_close($process), $error);
            }
            $this->assertCount(1, array_filter($outcomes, static fn ($x) => $x['outcome'] === 'OWNER'), json_encode($outcomes));
            $this->assertCount(1, array_filter($outcomes, static fn ($x) => $x['outcome'] === 'BLOCKED'), json_encode($outcomes));
            $this->assertStringContainsString('RUN_OWNER_CONFLICT', implode(' ', array_column($outcomes, 'reason')));
            $ownerId = (int) $db->table('eod_run_owner_fences')->where('trade_date_requested', '2026-03-21')->value('owner_run_id');
            $this->assertGreaterThan(0, $ownerId);
            $lease = $db->table('eod_run_owner_fences')->where('trade_date_requested','2026-03-21')->first();
            $this->assertNotNull($lease->acquired_at);
            $this->assertNotNull($lease->heartbeat_at);
            $this->assertGreaterThan($lease->heartbeat_at,$lease->expires_at);
            $this->assertSame(1, $db->table('eod_runs')->where('trade_date_requested', '2026-03-21')
                ->whereIn('lifecycle_state', ['PENDING', 'RUNNING', 'FINALIZING'])->count());
            $this->assertSame($ownerId, (int) $db->table('eod_runs')->where('trade_date_requested', '2026-03-21')
                ->whereIn('lifecycle_state', ['PENDING', 'RUNNING', 'FINALIZING'])->value('run_id'));
            $this->assertSame(1, $db->table('eod_runs')->where('trade_date_requested', '2026-03-21')
                ->where('terminal_status', 'BLOCKED')->where('publishability_state', 'NOT_READABLE')->count());

            $owner = EodRun::findOrFail($ownerId);
            $prior = $owner->replicate();
            $prior->lifecycle_state = 'COMPLETED';
            $prior->terminal_status = 'SUCCESS';
            $prior->publishability_state = 'READABLE';
            $prior->is_current_publication = 1;
            $prior->created_at = $owner->created_at;
            $prior->updated_at = $owner->updated_at;
            $prior->save();
            $priorPublicationId = $db->table('eod_publications')->insertGetId([
                'trade_date' => '2026-03-21', 'run_id' => $prior->run_id,
                'publication_version' => 1, 'is_current' => 1, 'seal_state' => 'SEALED',
                'sealed_at' => '2026-03-21 18:00:00', 'readiness_state' => 'READABLE',
                'created_at' => '2026-03-21 18:00:00', 'updated_at' => '2026-03-21 18:00:00',
            ]);
            $db->table('eod_runs')->where('run_id', $prior->run_id)->update(['publication_id' => $priorPublicationId, 'publication_version' => 1]);
            $db->table('eod_current_publication_pointer')->insert([
                'trade_date' => '2026-03-21', 'publication_id' => $priorPublicationId,
                'run_id' => $prior->run_id, 'publication_version' => 1,
                'sealed_at' => '2026-03-21 18:00:00', 'updated_at' => '2026-03-21 18:00:00',
            ]);
            $this->assertSame($priorPublicationId, (int) (new EodPublicationRepository())
                ->findRawCurrentPublicationStateForTradeDate('2026-03-21')->publication_id,
                'Historical pointer inspection remains available to a run that does not own the write fence.');
            $rogue = $owner->replicate();
            $rogue->source = 'api';
            $rogue->request_mode = 'correction';
            $rogue->created_at = $owner->created_at;
            $rogue->updated_at = $owner->updated_at;
            $rogue->save();
            try {
                (new EodRunRepository())->finalize($rogue, [
                    'lifecycle_state' => 'COMPLETED', 'terminal_status' => 'SUCCESS', 'publishability_state' => 'READABLE',
                ]);
                $this->fail('Non-owner finalized as readable on MariaDB.');
            } catch (RuntimeException $e) {
                $this->assertStringContainsString('RUN_OWNER_CONFLICT', $e->getMessage());
            }
            $publications = new EodPublicationRepository();
            foreach (['sealCandidatePublication', 'promoteCandidateToCurrent'] as $method) {
                try {
                    $publications->$method($rogue, null);
                    $this->fail('Non-owner reached '.$method);
                } catch (RuntimeException $e) {
                    $this->assertStringContainsString('RUN_OWNER_CONFLICT', $e->getMessage());
                }
            }
            $this->assertSame($priorPublicationId, (int) $db->table('eod_current_publication_pointer')->where('trade_date', '2026-03-21')->value('publication_id'));
            $this->assertSame(1, $db->table('eod_publications')->where('is_current', 1)->count());
            $this->assertSame(1, $db->table('eod_runs')->where('is_current_publication', 1)->count());
            $this->assertNull($db->table('eod_runs')->where('run_id', $rogue->run_id)->value('terminal_status'));
        } finally {
            if ($db->transactionLevel() > 0) $db->rollBack();
            foreach ($workers as [$process, $pipes]) {
                if (is_resource($process)) proc_terminate($process);
            }
        }
    }

    public function test_terminal_status_upgrade_preserves_old_outcomes_and_refuses_destructive_downgrade(): void
    {
        $db = DB::connection('b19_owner');
        $db->statement("ALTER TABLE eod_runs MODIFY terminal_status ENUM('SUCCESS','HELD','FAILED') NULL");
        foreach (['SUCCESS','HELD','FAILED'] as $status) $db->table('eod_runs')->insert([
            'trade_date_requested'=>'2026-03-21','lifecycle_state'=>'COMPLETED','terminal_status'=>$status,
            'quality_gate_state'=>'BLOCKED','publishability_state'=>'NOT_READABLE','stage'=>'FINALIZE','source'=>'api',
            'started_at'=>'2026-03-21 18:00:00','created_at'=>'2026-03-21 18:00:00','updated_at'=>'2026-03-21 18:00:00',
        ]);
        $before = $db->table('eod_runs')->orderBy('run_id')->get()->toJson();
        require_once base_path('database/migrations/2026_10_19_000002_expand_eod_run_terminal_status_for_fenced_outcomes.php');
        $migration = new ExpandEodRunTerminalStatusForFencedOutcomes(); $migration->up();
        $this->assertSame($before, $db->table('eod_runs')->orderBy('run_id')->get()->toJson());
        foreach (['SKIPPED','BLOCKED'] as $status) {
            $row = (array) $db->table('eod_runs')->first(); unset($row['run_id']); $row['terminal_status']=$status;
            $id=$db->table('eod_runs')->insertGetId($row);
            $this->assertSame($status,$db->table('eod_runs')->where('run_id',$id)->value('terminal_status'));
        }
        try { $migration->down(); $this->fail('Downgrade discarded governed terminal meanings.'); }
        catch (RuntimeException $e) { $this->assertStringContainsString('RUN_STATUS_ROLLBACK_BLOCKED',$e->getMessage()); }
        $this->assertSame(5,$db->table('eod_runs')->count());
    }

    public function test_live_lease_heartbeat_prevents_takeover_even_when_run_timestamp_is_stale(): void
    {
        $oldTtl = config('market_data.pipeline.active_run_stale_minutes');
        config()->set('market_data.pipeline.active_run_stale_minutes', 60);
        try {
            $runs = new EodRunRepository();
            $date = '2026-03-21';
            $owner = $runs->getOrCreateOwningRun($date, 'api', 'INGEST_BARS', null, 'full_publish');
            $before = DB::table('eod_run_owner_fences')->where('trade_date_requested', $date)->first();
            $this->assertSame((int) $owner->run_id, (int) $before->owner_run_id);
            $this->assertGreaterThan((string) \Carbon\Carbon::now(), (string) $before->expires_at);
            DB::table('eod_runs')->where('run_id', $owner->run_id)
                ->update(['updated_at' => \Carbon\Carbon::now()->subHours(2)]);
            try {
                $runs->getOrCreateOwningRun($date, 'manual_file', 'INGEST_BARS', null, 'correction');
                $this->fail('A live heartbeat was displaced using only an old run updated_at value.');
            } catch (\RuntimeException $error) {
                $this->assertStringContainsString('RUN_OWNER_CONFLICT', $error->getMessage());
            }
            $this->assertSame((int) $owner->run_id, (int) DB::table('eod_run_owner_fences')
                ->where('trade_date_requested', $date)->value('owner_run_id'));
            $this->assertSame('PENDING', DB::table('eod_runs')->where('run_id', $owner->run_id)->value('lifecycle_state'));
            $this->assertSame(1, (int) DB::table('eod_run_events')->where('run_id', $owner->run_id)
                ->where('event_type', 'RUN_OWNER_CONFLICT')->count());
            $runs->assertPublishCriticalOwner($owner->fresh());
            $renewed = DB::table('eod_run_owner_fences')->where('trade_date_requested', $date)->first();
            $this->assertSame((string) $before->acquired_at, (string) $renewed->acquired_at);
            $this->assertSame((int) $owner->run_id, (int) $renewed->owner_run_id);
            $this->assertGreaterThanOrEqual((string) $before->heartbeat_at, (string) $renewed->heartbeat_at);

            DB::table('eod_run_owner_fences')->where('trade_date_requested', $date)->update([
                'heartbeat_at' => \Carbon\Carbon::now()->subHours(2),
                'expires_at' => \Carbon\Carbon::now()->subHour(),
            ]);
            $newOwner = $runs->getOrCreateOwningRun($date, 'manual_file', 'INGEST_BARS', null, 'correction');
            $this->assertNotSame((int) $owner->run_id, (int) $newOwner->run_id);
            $this->assertSame((int) $newOwner->run_id, (int) DB::table('eod_run_owner_fences')
                ->where('trade_date_requested', $date)->value('owner_run_id'));
            $this->assertSame('CANCELLED', DB::table('eod_runs')->where('run_id', $owner->run_id)->value('lifecycle_state'));
            $this->assertSame(1, (int) DB::table('eod_run_events')->where('run_id', $newOwner->run_id)
                ->where('event_type', 'RUN_OWNER_TAKEOVER')->count());
            try {
                $runs->assertPublishCriticalOwner($owner->fresh());
                $this->fail('Expired owner regained the publication fence after audited takeover.');
            } catch (\RuntimeException $error) {
                $this->assertStringContainsString('RUN_OWNER_CONFLICT', $error->getMessage());
            }
            $staleStage = (string) DB::table('eod_runs')->where('run_id', $owner->run_id)->value('stage');
            try {
                $runs->touchStage($owner->fresh(), 'HASH');
                $this->fail('A stale fenced worker changed its run stage after takeover.');
            } catch (\RuntimeException $error) {
                $this->assertStringContainsString('RUN_OWNER_CONFLICT', $error->getMessage());
            }
            $this->assertSame($staleStage, (string) DB::table('eod_runs')->where('run_id', $owner->run_id)->value('stage'));
            $stale = $owner->fresh();
            $beforeWrite = (array) DB::table('eod_runs')->where('run_id', $owner->run_id)->first();
            foreach ([
                'telemetry' => static fn () => $runs->updateTelemetry($stale, ['source_attempt_count' => 99]),
                'hashes' => static fn () => $runs->storeHashes($stale, [
                    'bars_batch_hash' => str_repeat('a', 64),
                    'indicators_batch_hash' => str_repeat('b', 64),
                    'eligibility_batch_hash' => str_repeat('c', 64),
                ]),
                'seal' => static fn () => $runs->markSealed($stale, 'stale-worker', 'forbidden'),
            ] as $surface => $write) {
                try {
                    $write();
                    $this->fail('Stale fenced worker wrote '.$surface.' after takeover.');
                } catch (\RuntimeException $error) {
                    $this->assertStringContainsString('RUN_OWNER_CONFLICT', $error->getMessage(), $surface);
                }
            }
            $this->assertSame($beforeWrite, (array) DB::table('eod_runs')->where('run_id', $owner->run_id)->first());
            $newOwnerStage = (string) DB::table('eod_runs')->where('run_id', $newOwner->run_id)->value('stage');
            DB::table('eod_run_owner_fences')->where('trade_date_requested', $date)->delete();
            try {
                $runs->touchStage($newOwner->fresh(), 'HASH');
                $this->fail('A missing fence was implicitly reacquired by a state writer.');
            } catch (\RuntimeException $error) {
                $this->assertStringContainsString('RUN_OWNER_CONFLICT', $error->getMessage());
            }
            $this->assertSame(0, (int) DB::table('eod_run_owner_fences')->where('trade_date_requested', $date)->count());
            $this->assertSame($newOwnerStage, (string) DB::table('eod_runs')->where('run_id', $newOwner->run_id)->value('stage'));
        } finally {
            config()->set('market_data.pipeline.active_run_stale_minutes', $oldTtl);
        }
    }

    protected function completedOwnerWithLiveSuccessor(): array
    {
        $date = '2026-03-21';
        $runs = new EodRunRepository();
        $old = $runs->getOrCreateOwningRun($date, 'api', 'INGEST_BARS', null, 'full_publish');
        $old = $runs->finalize($old, [
            'lifecycle_state' => 'COMPLETED', 'terminal_status' => 'HELD',
            'publishability_state' => 'NOT_READABLE', 'quality_gate_state' => 'BLOCKED',
        ]);
        $new = $runs->getOrCreateOwningRun($date, 'manual_file', 'FINALIZE', (int) $old->run_id, 'repair_candidate');
        $this->assertSame((int) $new->run_id, (int) DB::table('eod_run_owner_fences')
            ->where('trade_date_requested', $date)->value('owner_run_id'));
        $this->assertNull(DB::table('eod_run_owner_fences')->where('trade_date_requested', $date)->value('released_at'));
        $this->retainObservedWorld($this->getName().'_before', [(int) $old->run_id, (int) $new->run_id]);
        return [$date, $old, $new];
    }

    protected function onIndependentOwnerConnection(callable $operation)
    {
        $primary = (int) DB::connection('b19_owner')->select('SELECT CONNECTION_ID() AS id')[0]->id;
        config()->set('database.connections.b19_owner_second', array_merge(
            config('database.connections.b19_owner'), ['database' => $this->database]
        ));
        DB::purge('b19_owner_second');
        config()->set('database.default', 'b19_owner_second');
        try {
            $secondary = (int) DB::connection('b19_owner_second')->select('SELECT CONNECTION_ID() AS id')[0]->id;
            $this->assertNotSame($primary, $secondary);
            $this->connectionEvidence[] = ['primary' => $primary, 'secondary' => $secondary];
            $this->retainObservedWorld($this->getName().'_before_independent_'.count($this->connectionEvidence), DB::table('eod_runs')->pluck('run_id')->all(), ['connections' => $this->connectionEvidence]);
            return $operation();
        } finally {
            config()->set('database.default', 'b19_owner');
            DB::disconnect('b19_owner_second');
        }
    }

    public function test_released_owner_cannot_append_event_after_successor_takes_over_on_independent_connection(): void
    {
        [$date, $old, $new] = $this->completedOwnerWithLiveSuccessor();
        $before = DB::table('eod_run_events')->where('run_id', $old->run_id)->count();
        $error = $this->onIndependentOwnerConnection(function () use ($old) {
            try {
                (new EodRunRepository())->appendEvent(EodRun::findOrFail($old->run_id), 'FINALIZE',
                    'STALE_OWNER_ATTEMPT', 'WARN', 'Stale worker must not append an audit event.');
                return null;
            } catch (\RuntimeException $caught) {
                return $caught->getMessage();
            }
        });
        $this->assertSame($before, DB::table('eod_run_events')->where('run_id', $old->run_id)->count(),
            'Released old owner appended an event through an independent connection.');
        $this->assertStringContainsString('RUN_OWNER_CONFLICT', (string) $error);
        $this->assertSame((int) $new->run_id, (int) DB::table('eod_run_owner_fences')
            ->where('trade_date_requested', $date)->value('owner_run_id'));
    }

    public function test_unidentified_second_connection_cannot_rewrite_current_mirror(): void
    {
        [$date, $old, $new] = $this->completedOwnerWithLiveSuccessor();
        DB::table('eod_runs')->where('run_id', $new->run_id)->update(['is_current_publication' => 1]);
        $before = DB::table('eod_runs')->whereIn('run_id', [$old->run_id, $new->run_id])
            ->orderBy('run_id')->get(['run_id', 'is_current_publication'])->toJson();
        $error = $this->onIndependentOwnerConnection(function () use ($date, $old) {
            try {
                (new EodRunRepository())->syncCurrentPublicationMirror($date, (int) $old->run_id);
                return null;
            } catch (\RuntimeException $caught) {
                return $caught->getMessage();
            }
        });
        $this->assertSame($before, DB::table('eod_runs')->whereIn('run_id', [$old->run_id, $new->run_id])
            ->orderBy('run_id')->get(['run_id', 'is_current_publication'])->toJson(),
            'Unidentified second connection rewrote the current-run mirror.');
        $this->assertStringContainsString('RUN_OWNER_CONFLICT', (string) $error);
    }

    public function test_released_owner_cannot_create_candidate_after_successor_takes_over(): void
    {
        [$date, $old, $new] = $this->completedOwnerWithLiveSuccessor();
        $before = DB::table('eod_publications')->where('trade_date', $date)->count();
        $error = $this->onIndependentOwnerConnection(function () use ($old) {
            try {
                (new EodPublicationRepository())->getOrCreateCandidatePublication(EodRun::findOrFail($old->run_id));
                return null;
            } catch (\RuntimeException $caught) {
                return $caught->getMessage();
            }
        });
        $this->assertSame($before, DB::table('eod_publications')->where('trade_date', $date)->count(),
            'Released owner created a candidate after successor takeover.');
        $this->assertStringContainsString('RUN_OWNER_CONFLICT', (string) $error);
        $this->assertSame((int) $new->run_id, (int) DB::table('eod_run_owner_fences')
            ->where('trade_date_requested', $date)->value('owner_run_id'));
    }

    public function test_unidentified_second_connection_cannot_clear_current_pointer_and_mirrors(): void
    {
        [$date, $old, $new] = $this->completedOwnerWithLiveSuccessor();
        $sealedAt = '2026-03-21 18:00:00';
        $publicationId = DB::table('eod_publications')->insertGetId([
            'trade_date' => $date, 'run_id' => $old->run_id, 'publication_version' => 1,
            'is_current' => 1, 'seal_state' => 'SEALED', 'sealed_at' => $sealedAt,
            'readiness_state' => 'READABLE', 'created_at' => $sealedAt, 'updated_at' => $sealedAt,
        ]);
        DB::table('eod_current_publication_pointer')->insert([
            'trade_date' => $date, 'publication_id' => $publicationId, 'run_id' => $old->run_id,
            'publication_version' => 1, 'sealed_at' => $sealedAt, 'updated_at' => $sealedAt,
        ]);
        DB::table('eod_runs')->where('run_id', $old->run_id)->update(['is_current_publication' => 1]);
        $before = [
            'pointer' => DB::table('eod_current_publication_pointer')->where('trade_date', $date)->first(),
            'publication' => DB::table('eod_publications')->where('publication_id', $publicationId)->first(),
            'mirrors' => DB::table('eod_runs')->whereIn('run_id', [$old->run_id, $new->run_id])
                ->orderBy('run_id')->get(['run_id', 'is_current_publication'])->toJson(),
        ];
        $error = $this->onIndependentOwnerConnection(function () use ($date) {
            try {
                (new EodPublicationRepository())->clearCurrentPublicationState($date);
                return null;
            } catch (\RuntimeException $caught) {
                return $caught->getMessage();
            }
        });
        $this->assertEquals($before['pointer'], DB::table('eod_current_publication_pointer')->where('trade_date', $date)->first());
        $this->assertEquals($before['publication'], DB::table('eod_publications')->where('publication_id', $publicationId)->first());
        $this->assertSame($before['mirrors'], DB::table('eod_runs')->whereIn('run_id', [$old->run_id, $new->run_id])
            ->orderBy('run_id')->get(['run_id', 'is_current_publication'])->toJson());
        $this->assertStringContainsString('RUN_OWNER_CONFLICT', (string) $error);
    }

    public function test_unidentified_second_connection_cannot_update_candidate_hashes(): void
    {
        [$date, $old, $new] = $this->completedOwnerWithLiveSuccessor();
        $candidate = (new EodPublicationRepository())->getOrCreateCandidatePublication($new);
        $beforePublication = DB::table('eod_publications')->where('publication_id', $candidate->publication_id)->first();
        $beforeRun = DB::table('eod_runs')->where('run_id', $new->run_id)->first();
        $error = $this->onIndependentOwnerConnection(function () use ($candidate) {
            try {
                (new EodPublicationRepository())->updateCandidateHashes($candidate->publication_id, [
                    'bars_batch_hash' => str_repeat('a', 64),
                    'indicators_batch_hash' => str_repeat('b', 64),
                    'eligibility_batch_hash' => str_repeat('c', 64),
                ]);
                return null;
            } catch (\RuntimeException $caught) {
                return $caught->getMessage();
            }
        });
        $this->assertEquals($beforePublication, DB::table('eod_publications')->where('publication_id', $candidate->publication_id)->first());
        $this->assertEquals($beforeRun, DB::table('eod_runs')->where('run_id', $new->run_id)->first());
        $this->assertStringContainsString('RUN_OWNER_CONFLICT', (string) $error);
        $this->assertSame((int) $new->run_id, (int) DB::table('eod_run_owner_fences')
            ->where('trade_date_requested', $date)->value('owner_run_id'));
    }

    protected function fencedState(): string
    {
        $state = [];
        foreach (['eod_run_owner_fences' => 'trade_date_requested', 'eod_runs' => 'run_id',
            'eod_run_events' => 'event_id', 'eod_publications' => 'publication_id',
            'eod_current_publication_pointer' => 'trade_date'] as $table => $key) {
            $state[$table] = DB::table($table)->orderBy($key)->get()->all();
        }
        return json_encode($state);
    }

    protected function primeUnsafeCurrentPointer(EodRun $owner, $candidate): void
    {
        DB::table('eod_current_publication_pointer')->insert([
            'trade_date'=>$owner->trade_date_requested, 'publication_id'=>$candidate->publication_id,
            'run_id'=>$owner->run_id, 'publication_version'=>$candidate->publication_version,
            'sealed_at'=>'2026-03-21 18:00:00', 'updated_at'=>'2026-03-21 18:00:00',
        ]);
        DB::table('eod_publications')->where('publication_id',$candidate->publication_id)->update(['is_current'=>1]);
        DB::table('eod_runs')->where('run_id',$owner->run_id)->update(['is_current_publication'=>1]);
    }

    protected function protectedOperations(EodRun $owner, $candidateId): array
    {
        return [
            'event' => static fn () => (new EodRunRepository())->appendEvent($owner, 'FINALIZE', 'FENCED_PROBE', 'INFO', 'Authorized active writer'),
            'mirror' => static fn () => (new EodRunRepository())->syncCurrentPublicationMirror($owner->trade_date_requested, $owner->run_id, $owner),
            'candidate' => static fn () => (new EodPublicationRepository())->getOrCreateCandidatePublication($owner),
            'hashes' => static fn () => (new EodPublicationRepository())->updateCandidateHashes($candidateId, [
                'bars_batch_hash' => str_repeat('a',64), 'indicators_batch_hash' => str_repeat('b',64),
                'eligibility_batch_hash' => str_repeat('c',64),
            ], $owner),
            'clear' => static fn () => (new EodPublicationRepository())->clearCurrentPublicationState($owner->trade_date_requested, $owner),
        ];
    }

    public function test_shared_five_writer_boundary_authorizes_owner_and_rejects_wrong_released_and_missing_identity(): void
    {
        [$date, $old, $owner] = $this->completedOwnerWithLiveSuccessor();
        $candidate = (new EodPublicationRepository())->getOrCreateCandidatePublication($owner);
        $this->primeUnsafeCurrentPointer($owner, $candidate);
        foreach ($this->protectedOperations($owner, $candidate->publication_id) as $family => $operation) {
            $operation();
            if ($family === 'mirror') $this->assertSame(1,(int)DB::table('eod_runs')->where('run_id',$owner->run_id)->value('is_current_publication'));
            if ($family === 'candidate') $this->assertSame(1,DB::table('eod_publications')->where('run_id',$owner->run_id)->count());
        }
        $this->assertSame(1, DB::table('eod_run_events')->where('run_id',$owner->run_id)->where('event_type','FENCED_PROBE')->count());
        $this->assertSame(str_repeat('a',64), DB::table('eod_publications')->where('publication_id',$candidate->publication_id)->value('bars_batch_hash'));
        $this->assertSame(str_repeat('a',64), DB::table('eod_runs')->where('run_id',$owner->run_id)->value('bars_batch_hash'));
        $this->assertSame(0, DB::table('eod_current_publication_pointer')->count());
        foreach (['old' => $old, 'wrong_date' => clone $owner, 'wrong_scope' => clone $owner, 'wrong_mode' => clone $owner] as $kind => $bad) {
            if ($kind === 'wrong_date') $bad->trade_date_requested = '2026-03-22';
            if ($kind === 'wrong_scope') $bad->source = 'unrelated-source';
            if ($kind === 'wrong_mode') $bad->request_mode = 'replay_verify';
            foreach ($this->protectedOperations($bad, $candidate->publication_id) as $family => $operation) {
                $before = $this->fencedState();
                $error = $this->onIndependentOwnerConnection(function () use ($operation) {
                    try { $operation(); return null; } catch (RuntimeException $e) { return $e->getMessage(); }
                });
                $this->assertSame($before, $this->fencedState(), 'Unauthorized '.$kind.' mutated '.$family);
                $this->assertStringContainsString('RUN_OWNER_CONFLICT', (string) $error, $kind.'/'.$family);
            }
        }
        // An unrelated candidate on the same date is a target, never the acting owner's candidate.
        DB::table('eod_publications')->where('publication_id',$candidate->publication_id)->update(['run_id'=>$old->run_id]);
        $before = $this->fencedState();
        try { $this->protectedOperations($owner,$candidate->publication_id)['hashes'](); $this->fail('Other-run candidate admitted'); }
        catch (RuntimeException $e) { $this->assertStringContainsString('RUN_OWNER_CONFLICT',$e->getMessage()); }
        $this->assertSame($before,$this->fencedState());
        $fakeMode = clone $owner; $fakeMode->request_mode = 'import_only';
        $before = $this->fencedState();
        try { (new EodRunRepository())->storeHashes($fakeMode, ['bars_batch_hash'=>'unauthorized','indicators_batch_hash'=>'unauthorized','eligibility_batch_hash'=>'unauthorized']); $this->fail('Forged mode bypassed run hash fence'); }
        catch (RuntimeException $e) { $this->assertStringContainsString('RUN_OWNER_CONFLICT',$e->getMessage()); }
        $this->assertSame($before,$this->fencedState());
        try { (new EodRunRepository())->appendEvent(new EodRun(), 'FINALIZE', 'ABSENT_TOKEN', 'INFO', 'Must refuse'); $this->fail('Missing event token admitted'); }
        catch (RuntimeException $e) { $this->assertStringContainsString('RUN_OWNER_CONFLICT',$e->getMessage()); }
        $this->assertSame($before,$this->fencedState());
        // A released fence remains invalid even if a stale in-memory/persisted run says RUNNING.
        DB::table('eod_run_owner_fences')->where('trade_date_requested',$date)->update(['released_at'=>'2026-03-21 19:00:00']);
        foreach ($this->protectedOperations($owner,$candidate->publication_id) as $family=>$operation) {
            $before=$this->fencedState();
            try { $operation(); $this->fail('Released fence admitted '.$family); }
            catch (RuntimeException $e) { $this->assertStringContainsString('RUN_OWNER_CONFLICT',$e->getMessage()); }
            $this->assertSame($before,$this->fencedState(),'Released fence mutated '.$family);
        }
        DB::table('eod_run_owner_fences')->where('trade_date_requested',$date)->delete();
        foreach ($this->protectedOperations($owner,$candidate->publication_id) as $family=>$operation) {
            $before=$this->fencedState();
            try { $operation(); $this->fail('Missing fence admitted '.$family); }
            catch (RuntimeException $e) { $this->assertStringContainsString('RUN_OWNER_CONFLICT',$e->getMessage()); }
            $this->assertSame($before,$this->fencedState(),'Missing fence mutated '.$family);
        }
    }

    public function test_shared_five_writer_transaction_rolls_back_every_partial_mutation(): void
    {
        [$date, $old, $owner] = $this->completedOwnerWithLiveSuccessor();
        $candidate = (new EodPublicationRepository())->getOrCreateCandidatePublication($owner);
        $this->primeUnsafeCurrentPointer($owner, $candidate);
        $armed = false; $remaining = 1;
        DB::listen(function ($query) use (&$armed, &$remaining) {
            if ($armed && preg_match('/^(insert|update|delete) /i', $query->sql) && --$remaining === 0) {
                $armed = false;
                throw new RuntimeException('R0004_INJECTED_AFTER_SQL');
            }
        });
        foreach (array_keys($this->protectedOperations($owner,$candidate->publication_id)) as $family) {
            $operation=$this->protectedOperations($owner,$candidate->publication_id)[$family];
            if ($family==='candidate') DB::table('eod_publications')->where('publication_id',$candidate->publication_id)->delete();
            $before=$this->fencedState(); $remaining=['event'=>1,'mirror'=>2,'candidate'=>1,'hashes'=>2,'clear'=>3][$family]; $armed=true;
            try { $operation(); $this->fail('Fault injection not reached: '.$family); }
            catch (RuntimeException $e) { $this->assertSame('R0004_INJECTED_AFTER_SQL',$e->getMessage()); }
            finally { $armed=false; }
            $this->assertSame($before,$this->fencedState(),'Rollback left partial '.$family);
            if ($family==='candidate') $candidate=(new EodPublicationRepository())->getOrCreateCandidatePublication($owner);
        }
        $before=$this->fencedState(); $remaining=3; $armed=true;
        try { (new EodRunRepository())->finalize($owner,['lifecycle_state'=>'COMPLETED','terminal_status'=>'HELD']); $this->fail('Release fault not injected'); }
        catch (RuntimeException $e) { $this->assertSame('R0004_INJECTED_AFTER_SQL',$e->getMessage()); }
        finally { $armed=false; }
        $this->assertSame($before,$this->fencedState(),'Terminal audit/release must roll back together');
    }

    public function test_each_writer_holds_date_lock_against_independent_owner_acquisition_until_commit(): void
    {
        [$date,$old,$owner]=$this->completedOwnerWithLiveSuccessor();
        $candidate=(new EodPublicationRepository())->getOrCreateCandidatePublication($owner);
        $armed=false; $blocked=[];
        DB::listen(function ($query) use (&$armed,&$blocked,$date) {
            if (! $armed || ! preg_match('/^(insert|update|delete) /i',$query->sql)) return;
            $armed=false;
            $blocked[]=$this->onIndependentOwnerConnection(function () use ($date) {
                DB::statement('SET SESSION innodb_lock_wait_timeout = 1');
                try { (new EodRunRepository())->getOrCreateOwningRun($date,'api','INGEST_BARS',null,'full_publish'); return null; }
                catch (\Illuminate\Database\QueryException $e) { return $e->getMessage(); }
            });
        });
        foreach (array_keys($this->protectedOperations($owner,$candidate->publication_id)) as $family) {
            $operation=$this->protectedOperations($owner,$candidate->publication_id)[$family];
            if ($family==='candidate') DB::table('eod_publications')->where('publication_id',$candidate->publication_id)->delete();
            $armed=true; $operation(); $armed=false;
            $this->assertStringContainsString('Lock wait timeout exceeded',(string) end($blocked),'Takeover raced '.$family);
            $this->assertSame((int)$owner->run_id,(int)DB::table('eod_run_owner_fences')->where('trade_date_requested',$date)->value('owner_run_id'));
            if ($family==='candidate') $candidate=(new EodPublicationRepository())->findByRunId($owner->run_id);
        }
        $this->assertCount(5,$blocked);
    }
}
