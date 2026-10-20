<?php

use App\Infrastructure\Persistence\MarketData\EodPublicationRepository;
use App\Infrastructure\Persistence\MarketData\EodRunRepository;
use App\Models\EodRun;
use Illuminate\Support\Facades\DB;

class B19OwnerFenceMariaDbTest extends TestCase
{
    private string $database;

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
            if ($table === 'eod_run_owner_fences') continue;
            if (! preg_match('/^[a-z0-9_]+$/D', $table)) throw new RuntimeException('UNSAFE_OWNER_TEST_TABLE');
            DB::connection('b19_owner')->statement('CREATE TABLE `'.$table.'` LIKE `tradeaxis_testing`.`'.$table.'`');
        }
        DB::connection('b19_owner')->statement('INSERT INTO eod_reason_codes SELECT * FROM `tradeaxis_testing`.`eod_reason_codes`');
        require_once base_path('database/migrations/2026_10_19_000001_create_eod_run_owner_fences.php');
        (new CreateEodRunOwnerFences())->up();
        require_once base_path('database/migrations/2026_10_20_000002_add_date_owner_lease_evidence.php');
        (new AddDateOwnerLeaseEvidence())->up();
    }

    protected function tearDown(): void
    {
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
}
