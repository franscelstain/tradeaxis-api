<?php

require_once __DIR__.'/B18ProducerInputCaptureTest.php';

use Illuminate\Support\Facades\DB;
use Tests\Support\UsesMarketDataMariaDb;

class B18ProducerInputCaptureMariaDbTest extends TestCase
{
    use UsesMarketDataMariaDb, B18ProducerInputCaptureAssertions;
    protected function setUp(): void { parent::setUp(); $this->bootMarketDataMariaDb(); }
    protected function tearDown(): void { $this->tearDownMarketDataMariaDb(); parent::tearDown(); }

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
        $this->assertSame('tradeaxis_testing', $database);
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
