<?php

use App\Application\MarketData\DTOs\MarketDataStageInput;
use App\Application\MarketData\Services\PublicationGovernanceBindingService;
use App\Infrastructure\Persistence\MarketData\EodRunRepository;
use App\Models\EodRun;
use Illuminate\Support\Facades\DB;
use Tests\Support\R0025SyntheticV2World;
require_once __DIR__.'/B19OwnerFenceMariaDbTest.php';

class B19GovernanceWriterBoundaryMariaDbTest extends B19OwnerFenceMariaDbTest
{
    protected function tearDown(): void { \Carbon\Carbon::setTestNow(); parent::tearDown(); }
    private function candidate(): array
    {
        $world=R0025SyntheticV2World::build(['run_workflow'=>function($pipeline,$date){$run=null;
            foreach(['INGEST_BARS'=>'completeIngest','COMPUTE_INDICATORS'=>'completeIndicators','BUILD_ELIGIBILITY'=>'completeEligibility','HASH'=>'completeHash']as $stage=>$method)
                $run=$pipeline->$method(new MarketDataStageInput($date,'api',$run?$run->run_id:null,$stage,null,false,null,'full_publish'));
            return $run;}]);
        return [EodRun::findOrFail($world['run_id']),DB::table('eod_publications')->where('publication_id',$world['publication_id'])->first()];
    }
    private function state(): array
    {
        $out=[];foreach(DB::select('SHOW TABLES')as $entry){$table=array_values((array)$entry)[0];if(!preg_match('/^(eod_|md_)[a-z0-9_]+$/D',$table))continue;
            $rows=array_map(fn($r)=>(array)$r,DB::table($table)->get()->all());usort($rows,fn($a,$b)=>strcmp(json_encode($a),json_encode($b)));$out[$table]=$rows;}
        ksort($out);return $out;
    }
    private function retain($kind,$before,$after,$error): void
    {
        $dir=getenv('B19_E049_STATE_DIR');if(!$dir)return;if(!is_dir($dir))mkdir($dir,0775,true);
        file_put_contents($dir.'/GOV_'.$kind.'.json',json_encode(compact('before','after','error'),JSON_PRETTY_PRINT));
    }
    public function test_governance_binding_rejects_independent_and_released_owner_at_actual_write(): void
    {
        [$run,$pub]=$this->candidate();
        foreach(['independent','released']as $kind){
            if($kind==='released'){
                (new EodRunRepository())->finalize($run,['lifecycle_state'=>'COMPLETED','terminal_status'=>'HELD','publishability_state'=>'NOT_READABLE']);
                (new EodRunRepository())->getOrCreateOwningRun($run->trade_date_requested,'api','INGEST_BARS',$run->run_id,'full_publish');}
            $before=$this->state();$operation=function()use($run,$pub){try{(new PublicationGovernanceBindingService())->bind($run,$pub,$pub->trade_date);return null;}catch(RuntimeException $e){return $e->getMessage();}};
            $error=$kind==='independent'?$this->onIndependentOwnerConnection($operation):$operation();
            $after=$this->state();$this->retain($kind,$before,$after,$error);
            $this->assertSame($before,$after,'Governance unauthorized persisted '.$kind);$this->assertStringContainsString('RUN_OWNER_CONFLICT',(string)$error);
        }
    }
    public function test_governance_binding_owner_contention_and_compound_rollback(): void
    {
        [$run,$pub]=$this->candidate();$before=$this->state();$armed=true;$contention=null;
        DB::listen(function($q)use(&$armed,&$contention,$run){if(!$armed||stripos($q->sql,'delete from `md_publication_market_structure_bindings`')!==0)return;$armed=false;
            $contention=$this->onIndependentOwnerConnection(function()use($run){DB::statement('SET SESSION innodb_lock_wait_timeout=1');try{
                (new EodRunRepository())->getOrCreateOwningRun($run->trade_date_requested,'api','INGEST_BARS',null,'full_publish');return null;
            }catch(\Illuminate\Database\QueryException $e){return $e->getMessage();}});
            throw new RuntimeException('GOV_AFTER_FIRST_DELETE');});
        try{(new PublicationGovernanceBindingService())->bind($run,$pub,$pub->trade_date);$this->fail('Fault not reached');}
        catch(RuntimeException $e){$this->assertSame('GOV_AFTER_FIRST_DELETE',$e->getMessage());}finally{$armed=false;}
        $after=$this->state();$this->retain('compound_contention',$before,$after,$contention);
        $this->assertSame($before,$after,'Governance partial write survived');$this->assertStringContainsString('1205',(string)$contention);
        $result=(new PublicationGovernanceBindingService())->bind($run,$pub,$pub->trade_date);
        $this->assertNotEmpty($result);$this->assertSame(0,DB::table('eod_current_publication_pointer')->count());
        $this->retain('authorized_retry',$before,$this->state(),null);
    }
}
