<?php

require_once __DIR__.'/B18ProducerInputCaptureTest.php';

use Illuminate\Support\Facades\DB;


class B18ProducerInputCaptureMariaDbTest extends TestCase
{
    use B18ProducerInputCaptureAssertions, \Tests\Support\RetainsB19ObservedEvidence;
    private $captureDatabase;
    protected function setUp(): void
    {
        parent::setUp();
        $base=config('database.connections.mysql');
        $this->assertSame('tradeaxis_testing',$base['database'],'Clone only the designated migrated test substrate');
        $control=DB::connection('mysql');
        $this->assertStringContainsStringIgnoringCase('mariadb',$control->select('SELECT VERSION() AS version')[0]->version);
        $this->captureDatabase='tradeaxis_testing_b18_capture_'.bin2hex(random_bytes(6));
        $control->statement('CREATE DATABASE `'.$this->captureDatabase.'` CHARACTER SET utf8mb4 COLLATE utf8mb4_bin');
        config(['database.connections.b18_capture'=>array_merge($base,['database'=>$this->captureDatabase]),'database.default'=>'b18_capture']);
        DB::purge('b18_capture');
        foreach($control->select("SELECT TABLE_NAME AS name FROM information_schema.TABLES WHERE TABLE_SCHEMA='tradeaxis_testing' AND TABLE_TYPE='BASE TABLE'")as $entry){
            $table=$entry->name;if(in_array($table,['eod_run_owner_fences','eod_observer_fences','md_run_input_captures'],true))continue;
            if(!preg_match('/^[a-z0-9_]+$/D',$table))throw new RuntimeException('UNSAFE_CAPTURE_TEST_TABLE');
            DB::statement('CREATE TABLE `'.$table.'` LIKE `tradeaxis_testing`.`'.$table.'`');
        }
        DB::statement('INSERT INTO eod_reason_codes SELECT * FROM `tradeaxis_testing`.`eod_reason_codes`');
        // CREATE TABLE LIKE does not copy triggers/FKs. Build the actual capture table
        // through its existing migration, preserving every original schema assertion.
        require_once base_path('database/migrations/2026_09_16_000001_add_producer_bound_input_captures.php');
        (new AddProducerBoundInputCaptures())->up();
        require_once base_path('database/migrations/2026_10_19_000001_create_eod_run_owner_fences.php');(new CreateEodRunOwnerFences())->up();
        require_once base_path('database/migrations/2026_10_20_000002_add_date_owner_lease_evidence.php');(new AddDateOwnerLeaseEvidence())->up();
        require_once base_path('database/migrations/2026_10_23_000001_add_execution_credentials_and_observer_fences.php');(new AddExecutionCredentialsAndObserverFences())->up();
        require_once base_path('database/migrations/2026_10_25_000001_add_projection_observer_identity.php');(new AddProjectionObserverIdentity())->up();
        // No outer business transaction: ReplayIsolation owns its own rollback-only root.
    }
    protected function tearDown(): void
    {
        try{
            if($this->captureDatabase){
                $this->retainObservedWorld($this->getName(),DB::table('eod_runs')->pluck('run_id')->all(),['fixture'=>'isolated actual migrated capture schema, no caller business transaction']);
                DB::purge('b18_capture');
                if(!preg_match('/^tradeaxis_testing_b18_capture_[a-f0-9]{12}$/D',$this->captureDatabase))throw new RuntimeException('UNSAFE_CAPTURE_TEST_DATABASE');
                DB::connection('mysql')->statement('DROP DATABASE `'.$this->captureDatabase.'`');
            }
        }finally{parent::tearDown();}
    }

    public function test_captured_run_cannot_be_deleted_through_foreign_key_cascade(): void
    {
        $run = $this->owningRun();
        try { DB::table('eod_runs')->where('run_id', $run->run_id)->delete(); $this->fail('Capture must retain its owning run.'); }
        catch (\Illuminate\Database\QueryException $e) { $this->assertStringContainsString('fk_md_run_input_run', $e->getMessage()); }
        $this->assertSame(1, DB::table('md_run_input_captures')->where('run_id', $run->run_id)->count());
    }

    public function test_direct_insert_checks_reject_one_invalid_json_hash_or_empty_population(): void
    {
        $run = $this->owningRun();
        $row = (new \App\Infrastructure\Persistence\MarketData\RunInputCaptureRepository())->forRun($run->run_id)[0];
        unset($row['input_capture_id']); $row['stage_code'] = 'PROBE';
        foreach ([['semantic_payload_json', '{', 'chk_md_input_json'], ['payload_hash', str_repeat('A', 64), 'chk_md_input_hash'], ['member_count', 0, 'chk_md_input_empty']] as [$field, $bad, $constraint]) {
            $invalid = $row; $invalid[$field] = $bad;
            try { DB::table('md_run_input_captures')->insert($invalid); $this->fail('Invalid '.$field.' was inserted.'); }
            catch (\Illuminate\Database\QueryException $e) { $this->assertStringContainsString($constraint, $e->getMessage()); }
        }
        $this->assertSame(1, DB::table('md_run_input_captures')->where('run_id', $run->run_id)->count());
    }

    public function test_schema_has_exact_protections_on_the_actual_capture_table(): void
    {
        $database = DB::connection()->getDatabaseName();
        $this->assertMatchesRegularExpression('/^tradeaxis_testing_b18_capture_[a-f0-9]{12}$/D', $database);
        $this->assertSame($this->captureDatabase, $database);
        $triggers = DB::table('information_schema.TRIGGERS')->where('TRIGGER_SCHEMA', $database)
            ->where('EVENT_OBJECT_TABLE', 'md_run_input_captures')->get()->all();
        $this->assertCount(2, $triggers);
        foreach ($triggers as $trigger) {
            $this->assertSame('BEFORE', $trigger->ACTION_TIMING);
            $this->assertStringContainsString('INPUT_CAPTURE_IMMUTABLE', $trigger->ACTION_STATEMENT);
        }
        $columns = DB::table('information_schema.COLUMNS')->where('TABLE_SCHEMA', $database)
            ->where('TABLE_NAME', 'md_run_input_captures')->get()->keyBy('COLUMN_NAME');
        $this->assertCount(13, $columns);
        foreach (['slot_hash', 'payload_hash'] as $name) $this->assertSame('ascii_bin', $columns[$name]->COLLATION_NAME);
        $this->assertSame('NO', $columns['semantic_payload_json']->IS_NULLABLE);
        $this->assertSame('NO', $columns['run_id']->IS_NULLABLE);
    }
}
