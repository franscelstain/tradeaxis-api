<?php

use App\Application\MarketData\Services\StageEightCorpusReconstructionService;
use App\Infrastructure\Persistence\MarketData\EodPublicationRepository;
use App\Infrastructure\Persistence\MarketData\EodRunRepository;
use App\Models\EodRun;
use Illuminate\Support\Facades\DB;
require_once __DIR__.'/B19OwnerFenceMariaDbTest.php';

class B19StageEightAdmissionFenceMariaDbTest extends B19OwnerFenceMariaDbTest
{
    private function fixture(?EodRun $run = null): array
    {
        $run=$run ?: (new EodRunRepository())->getOrCreateOwningRun('2026-03-23','api','INGEST_BARS',null,'corpus_reconstruction');
        $pub=(new EodPublicationRepository())->getOrCreateCandidatePublication($run);
        $id=DB::table('md_corpus_admission_decisions')->insertGetId([
            'decision_uid'=>hash('sha256','scope-stage8'), 'market_code'=>'IDX','market_segment'=>'REGULAR',
            'canonical_price_product'=>'RAW_CANONICAL','intentional_dataset_start'=>'2023-01-02',
            'admitted_from'=>'2026-03-23','measured_through'=>'2026-03-23','coverage_threshold'=>0.95,
            'source_mode'=>'api','status_snapshot_observation_id'=>1,'transition_search_observation_id'=>1,
            'measurement_campaign_id'=>1,'measurement_input_hash'=>str_repeat('a',64),
            'status_revision_set_hash'=>str_repeat('b',64),'algorithm_version'=>'bounded-test-input',
            'measurement_json'=>'{}','state'=>'ACTIVE','reason_code'=>'BOUNDED_FIXTURE',
            'recorded_at'=>'2026-03-25 00:00:00','created_at'=>'2026-03-25 00:00:00']);
        return [$run,$pub,$id];
    }
    private function bind($runId,$decision,?EodRun $actor): void
    {
        $type=new ReflectionClass(StageEightCorpusReconstructionService::class);
        $service=$type->newInstanceWithoutConstructor();$method=$type->getMethod('bindRunAndCandidateAdmission');
        $method->setAccessible(true);$method->invoke($service,$runId,$decision,$actor);
    }
    private function state(): array
    {
        $out=[];foreach(DB::select('SHOW TABLES')as $entry){$table=array_values((array)$entry)[0];
            if(!preg_match('/^(eod_|md_)[a-z0-9_]+$/D',$table))continue;
            $rows=array_map(fn($r)=>(array)$r,DB::table($table)->get()->all());
            usort($rows,fn($a,$b)=>strcmp(json_encode($a),json_encode($b)));$out[$table]=$rows;}
        ksort($out);return $out;
    }
    private function retain($kind,$before,$after,$error): void
    {
        $dir=getenv('B19_E049_STATE_DIR');if(!$dir)return;if(!is_dir($dir))mkdir($dir,0775,true);
        file_put_contents($dir.'/S8_'.$kind.'.json',json_encode(compact('before','after','error'),JSON_PRETTY_PRINT));
    }
    public static function refusalCases(): array
    {return array_map(fn($v)=>[$v],['independent','missing','released','wrong_run','wrong_date','sealed','current','scope','inactive']);}
    /** @dataProvider refusalCases */
    public function test_stage8_admission_refuses_unauthorized_or_immutable_write(string $kind): void
    {
        [$run,$pub,$id]=$this->fixture();$actor=$run;$target=$run->run_id;
        if($kind==='missing')$actor=null;
        if($kind==='released'){
            (new EodRunRepository())->finalize($run,['lifecycle_state'=>'COMPLETED','terminal_status'=>'HELD','publishability_state'=>'NOT_READABLE']);
            $this->onIndependentOwnerConnection(function()use($run){return(new EodRunRepository())->getOrCreateOwningRun($run->trade_date_requested,'api','INGEST_BARS',$run->run_id,'corpus_reconstruction');});
        }
        if($kind==='wrong_run'){$actor=(new EodRunRepository())->getOrCreateOwningRun('2026-03-24','api','INGEST_BARS',null,'corpus_reconstruction');}
        if($kind==='wrong_date')DB::table('eod_publications')->where('publication_id',$pub->publication_id)->update(['trade_date'=>'2026-03-24']);
        if($kind==='sealed')DB::table('eod_publications')->where('publication_id',$pub->publication_id)->update(['seal_state'=>'SEALED']);
        if($kind==='current')DB::table('eod_publications')->where('publication_id',$pub->publication_id)->update(['is_current'=>1]);
        if($kind==='scope')DB::table('md_corpus_admission_decisions')->where('admission_decision_id',$id)->update(['admitted_from'=>'2026-03-24']);
        if($kind==='inactive')DB::table('md_corpus_admission_decisions')->where('admission_decision_id',$id)->update(['state'=>'SUPERSEDED']);
        $before=$this->state();$operation=function()use($target,$id,$actor){try{$this->bind($target,$id,$actor);return null;}catch(RuntimeException $e){return $e->getMessage();}};
        $error=$kind==='independent'?$this->onIndependentOwnerConnection($operation):$operation();
        $after=$this->state();$this->retain($kind,$before,$after,$error);
        $this->assertSame($before,$after,'Stage8 unauthorized persisted '.$kind);
        $expected=in_array($kind,['scope','inactive'],true)?'STAGE8_ADMISSION_EVIDENCE_INVALID':($kind==='sealed'?'SEALED_PUBLICATION_IMMUTABLE':'RUN_OWNER_CONFLICT');
        $this->assertStringContainsString($expected,(string)$error,'Stage8 contractual refusal '.$kind);
    }
    public function test_stage8_date_fence_only_probe_after_publication_write_and_rollback(): void
    {
        [$run,$pub,$id]=$this->fixture();$before=$this->state();$armed=true;$probe=null;
        DB::listen(function($q)use(&$armed,&$probe,$run){
            if(!$armed||stripos($q->sql,'update `eod_publications`')!==0)return;$armed=false;
            $probe=$this->onIndependentOwnerConnection(function()use($run){DB::statement('SET SESSION innodb_lock_wait_timeout=1');
                try{return DB::transaction(function()use($run){
                    DB::table('eod_run_owner_fences')->where('trade_date_requested',$run->trade_date_requested)->lockForUpdate()->first();return 'FENCE_LOCK_ACQUIRED';
                });}catch(\Illuminate\Database\QueryException $e){return $e->getMessage();}});
            throw new RuntimeException('S8_AFTER_PUBLICATION_WRITE');
        });
        try{$this->bind($run->run_id,$id,$run);$this->fail('Publication fault not reached');}
        catch(RuntimeException $e){$this->assertSame('S8_AFTER_PUBLICATION_WRITE',$e->getMessage());}finally{$armed=false;}
        $after=$this->state();$this->retain('date_fence_only_after_publication',$before,$after,$probe);
        $this->assertSame($before,$after,'Stage8 compound mutation survived rollback');
        $this->assertStringContainsString('1205',(string)$probe,'Date-fence-only probe must be blocked');
        $this->assertStringContainsString('eod_run_owner_fences',(string)$probe);
        $this->bind($run->run_id,$id,$run);
        $this->assertSame($id,(int)DB::table('eod_publications')->where('publication_id',$pub->publication_id)->value('corpus_admission_decision_id'));
    }
    public function test_stage8_owner_binding_is_atomic_and_holds_date_lock_through_second_write(): void
    {
        [$run,$pub,$id]=$this->fixture();$before=$this->state();$armed=true;$contention=null;
        DB::listen(function($q)use(&$armed,&$contention,$run){
            if(!$armed||stripos($q->sql,'update `eod_runs`')!==0)return;$armed=false;
            $contention=$this->onIndependentOwnerConnection(function()use($run){DB::statement('SET SESSION innodb_lock_wait_timeout=1');
                try{(new EodRunRepository())->getOrCreateOwningRun($run->trade_date_requested,'api','INGEST_BARS',null,'corpus_reconstruction');return null;}
                catch(\Illuminate\Database\QueryException $e){return $e->getMessage();}});
            throw new RuntimeException('S8_AFTER_RUN_WRITE');});
        try{$this->bind($run->run_id,$id,$run);$this->fail('Fault did not execute');}
        catch(RuntimeException $e){$this->assertSame('S8_AFTER_RUN_WRITE',$e->getMessage());}finally{$armed=false;}
        $after=$this->state();$this->retain('compound_contention',$before,$after,$contention);
        $this->assertSame($before,$after,'Stage8 partial write survived');$this->assertStringContainsString('1205',(string)$contention);
        $this->bind($run->run_id,$id,$run);
        $this->assertSame($id,(int)DB::table('eod_runs')->where('run_id',$run->run_id)->value('corpus_admission_decision_id'));
        $this->assertSame($id,(int)DB::table('eod_publications')->where('publication_id',$pub->publication_id)->value('corpus_admission_decision_id'));
        $this->assertSame(0,DB::table('eod_current_publication_pointer')->count());
        $this->retain('authorized_retry',$before,$this->state(),null);
    }
    public function test_stage8_corpus_import_continues_with_its_actual_owner_without_publication_grant(): void
    {
        try {
            $world=\Tests\Support\R0025SyntheticV2World::build(['run_workflow'=>function($pipeline,$date){
                return $pipeline->completeIngest(new \App\Application\MarketData\DTOs\MarketDataStageInput($date,'api',null,'INGEST_BARS',null,false,null,'corpus_reconstruction'));
            }]);
            $run=EodRun::findOrFail($world['run_id']);[$run,$pub,$id]=$this->fixture($run);
            $this->assertContains($run->lifecycle_state,['PENDING','RUNNING','FINALIZING']);
            $before=$this->state();$this->bind($run->run_id,$id,$run);
            foreach(['COMPUTE_INDICATORS'=>'completeIndicators','BUILD_ELIGIBILITY'=>'completeEligibility','HASH'=>'completeHash']as $stage=>$method)
                $run=$world['pipeline']->$method(new \App\Application\MarketData\DTOs\MarketDataStageInput($run->trade_date_requested,'api',$run->run_id,$stage,null,false,null,'corpus_reconstruction'));
            $this->assertSame($id,(int)DB::table('eod_runs')->where('run_id',$run->run_id)->value('corpus_admission_decision_id'));
            $this->assertSame($id,(int)DB::table('eod_publications')->where('publication_id',$pub->publication_id)->value('corpus_admission_decision_id'));
            $this->assertSame(0,DB::table('eod_current_publication_pointer')->count());
            $this->assertSame('UNSEALED',DB::table('eod_publications')->where('publication_id',$pub->publication_id)->value('seal_state'));
            $this->assertNull(DB::table('eod_run_owner_fences')->where('trade_date_requested',$run->trade_date_requested)->value('released_at'));
            $this->retain('corpus_import_hash_continuity',$before,$this->state(),null);
        } finally { \Carbon\Carbon::setTestNow(); }
    }
}
