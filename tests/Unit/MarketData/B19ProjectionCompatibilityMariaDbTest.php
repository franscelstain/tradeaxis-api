<?php

use App\Application\MarketData\DTOs\MarketDataStageInput;
use App\Application\MarketData\Services\PublicationProjectionReconciliationService;
use App\Infrastructure\Persistence\MarketData\EodRunRepository;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\Support\R0025SyntheticV2World;
require_once __DIR__.'/B19ArtifactWriterMariaDbTest.php';

/** Actual upgraded proof schemas only; no deployed database or fabricated historical actor. */
class B19ProjectionCompatibilityMariaDbTest extends B19ArtifactWriterMariaDbTest
{
    private function stateCompat(): array
    {
        $method=(new ReflectionClass(B19ArtifactWriterMariaDbTest::class))->getMethod('state');
        $method->setAccessible(true);return $method->invoke($this);
    }
    private function saveCompat(string $name,array $data): void
    {
        $dir=getenv('B19_E049_STATE_DIR');if(!$dir)return;
        if(!is_dir($dir))mkdir($dir,0775,true);
        file_put_contents($dir.'/COMP_'.$name.'.json',json_encode($data,JSON_PRETTY_PRINT));
    }
    public function test_comp_projection_schema_upgrade(): void
    {
        $world=R0025SyntheticV2World::build();$run=\App\Models\EodRun::findOrFail($world['run_id']);$date=$run->trade_date_requested;
        $service=app(PublicationProjectionReconciliationService::class);
        $service->reconcileTradeDate($date);
        $table='md_publication_projection_reconciliations';
        $audit=['writer_kind','writer_actor_id','writer_scope_key','writer_fencing_token'];
        $this->assertSame(0,DB::transactionLevel());
        // Explicit fixture: recreate the pre-upgrade table shape with real diagnostic contents.
        // This is not a supported downgrade or a claim about an actual old production writer.
        Schema::table($table,function(Blueprint $blueprint)use($audit){$blueprint->dropColumn($audit);});
        $legacy=DB::table($table)->orderBy('reconciliation_id')->get()->toArray();
        $this->assertNotEmpty($legacy);
        (new AddProjectionObserverIdentity())->up();
        $upgraded=DB::table($table)->orderBy('reconciliation_id')->get()->toArray();
        $this->assertCount(count($legacy),$upgraded);
        foreach($upgraded as $i=>$row){
            $values=(array)$row;
            foreach($audit as $column){$this->assertArrayHasKey($column,$values);$this->assertNull($values[$column],'Legacy writer facts must remain unavailable');unset($values[$column]);}
            $this->assertSame((array)$legacy[$i],$values,'Upgrade rewrote legacy reconciliation facts');
        }
        $beforeDown=$this->stateCompat();$columns=Schema::getColumnListing($table);$error=null;
        try{(new AddProjectionObserverIdentity())->down();}catch(RuntimeException $e){$error=$e->getMessage();}
        $this->assertSame('PROJECTION_OBSERVER_DOWNGRADE_REQUIRES_GOVERNED_EVIDENCE_DISPOSITION',$error);
        $this->assertSame($columns,Schema::getColumnListing($table));$this->assertSame($beforeDown,$this->stateCompat());
        $service->reconcileTradeDate($date);
        $rows=DB::table($table)->orderBy('reconciliation_id')->get()->toArray();$latest=(array)end($rows);
        $this->assertCount(count($legacy)+1,$rows);
        foreach($audit as $column)$this->assertNotNull($latest[$column],'New observation needs actual writer attribution');
        $this->assertSame('projection|'.$date,$latest['writer_scope_key']);
        $this->assertSame((array)$upgraded[0],(array)$rows[0],'New append must not backfill legacy attribution');
        $after=$this->stateCompat();$fresh=$this->onIndependentOwnerConnection(fn()=>$this->stateCompat());$this->assertSame($after,$fresh);
        $this->saveCompat('upgrade',compact('legacy','upgraded','beforeDown','after','fresh','error','columns'));
    }
    public function test_comp_legacy_sample_guard(): void
    {
        $method=(new ReflectionClass(B19ArtifactWriterMariaDbTest::class))->getMethod('candidate');$method->setAccessible(true);
        [$run,$pub,$world]=$method->invoke($this);$repo=new EodRunRepository();
        $this->assertSame(0,(int)$run->coverage_missing_count);
        // Real old serialization of the independently known empty sample, set before seal by
        // the actual current owner. No sealed fixture SQL or synthetic owner/secret is used.
        $run=$repo->updateTelemetry($run,['coverage_missing_sample_json'=>json_encode([])]);
        $this->assertSame(json_encode('[]'),DB::table('eod_runs')->where('run_id',$run->run_id)->value('coverage_missing_sample_json'));
        $run=$world['pipeline']->completeSeal(new MarketDataStageInput($run->trade_date_requested,'api',$run->run_id,'SEAL',null,false,null,'full_publish'));
        $before=$this->stateCompat();$raw=DB::table('eod_runs')->where('run_id',$run->run_id)->value('coverage_missing_sample_json');
        $this->assertSame('[]',$run->coverage_missing_sample_json);
        $run=$repo->updateTelemetry($run,['coverage_missing_sample_json'=>$run->coverage_missing_sample_json]);
        $this->assertSame($raw,DB::table('eod_runs')->where('run_id',$run->run_id)->value('coverage_missing_sample_json'));
        $this->assertSame($before,$this->stateCompat(),'Exact legacy confirmation rewrote sealed state');
        $error=null;try{$repo->updateTelemetry($run,['coverage_missing_sample_json'=>[]]);}catch(RuntimeException $e){$error=$e->getMessage();}
        $after=$this->stateCompat();$fresh=$this->onIndependentOwnerConnection(fn()=>$this->stateCompat());
        $this->saveCompat('legacy_sample',compact('before','after','fresh','raw','error'));
        $this->assertStringContainsString('SEALED_PUBLICATION_IMMUTABLE',(string)$error);
        $this->assertSame($before,$after);$this->assertSame($before,$fresh);
        $run=$repo->finalize($run,['lifecycle_state'=>'COMPLETED','terminal_status'=>'HELD','publishability_state'=>'NOT_READABLE']);
        $before=$this->stateCompat();$error=null;
        try{$repo->updateTelemetry($run,['coverage_missing_sample_json'=>$run->coverage_missing_sample_json]);}catch(RuntimeException $e){$error=$e->getMessage();}
        $this->assertStringContainsString('RUN_OWNER_', (string)$error);$this->assertSame($before,$this->stateCompat());
    }
}
