<?php

use App\Infrastructure\Persistence\MarketData\EodRunRepository;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
require_once __DIR__.'/B19ArtifactWriterMariaDbTest.php';

/** Actual acquisition control plane; bootstrap is not a fabricated incumbent credential. */
class B19AcquisitionWriterBoundaryMariaDbTest extends B19ArtifactWriterMariaDbTest
{
    public function test_acq_generic_factory_cannot_persist_replay_outside_isolation(): void
    {
        Carbon::setTestNow('2026-03-25 18:00:00');$before=$this->acqState();$error=null;$created=null;
        try{$created=(new EodRunRepository())->getOrCreateOwningRun('2026-03-21','api','INGEST_BARS',null,'replay_verify');}
        catch(RuntimeException $e){$error=$e->getMessage();}
        $after=$this->acqState();$fresh=$this->onIndependentOwnerConnection(fn()=>$this->acqState());
        $this->acqSave('generic_replay',compact('before','after','fresh','error')+['created_run_id'=>$created?$created->run_id:null]);
        $this->assertSame($before,$after,'GENERIC_REPLAY_ESCAPED_ROLLBACK_ONLY');$this->assertSame($before,$fresh);
        $this->assertStringContainsString('REPLAY_ISOLATION_TRANSACTION_REQUIRED',(string)$error);
    }
    public function test_acq_generic_replay_uses_managed_root_and_never_persists(): void
    {
        Carbon::setTestNow('2026-03-25 18:00:00');$before=$this->acqState();$repo=new EodRunRepository();$date='2026-03-21';
        DB::beginTransaction();try{
            $error=null;try{$repo->getOrCreateOwningRun($date,'api','INGEST_BARS',null,'replay_verify');}catch(RuntimeException $e){$error=$e->getMessage();}
            $this->assertStringContainsString('REPLAY_ISOLATION_TRANSACTION_REQUIRED',(string)$error);
            $this->assertSame($before,$this->acqState(),'Caller transaction alone became replay authority');
        }finally{DB::rollBack();}
        $during=null;$visible=null;$commitError=null;
        $run=\App\Infrastructure\Persistence\MarketData\ReplayIsolation::run(function()use($repo,$date,$before,&$during,&$visible,&$commitError){
            $run=$repo->getOrCreateOwningRun($date,'api','INGEST_BARS',null,'replay_verify');
            DB::beginTransaction();$repo->touchStage($run,'INGEST_BARS');DB::commit();
            $this->assertSame('replay_verify',$run->request_mode);$this->assertSame('NOT_READABLE',$run->publishability_state);
            $this->assertSame(1,DB::table('eod_run_events')->where('run_id',$run->run_id)->where('event_type','RUN_CREATED')->count());
            $this->assertSame(1,DB::table('md_run_input_captures')->where('run_id',$run->run_id)->count());
            $during=$this->acqState();$executionConnection=DB::connection();$executionName=DB::getDefaultConnection();
            // The inherited visitor restores b19_owner. Restore the actual managed
            // execution context after visiting the independent read connection.
            try{$visible=$this->onIndependentOwnerConnection(fn()=>$this->acqState());}
            finally{DB::setDefaultConnection($executionName);}
            $this->assertSame($executionConnection,DB::connection());$this->assertSame($before,$visible);
            try{DB::commit();}catch(RuntimeException $e){$commitError=$e->getMessage();}
            $this->assertStringContainsString('REPLAY_COMMIT_FORBIDDEN',(string)$commitError);
            $repo->touchStage($run,'INGEST_BARS',['notes'=>'Commit refusal retained managed authority']);
            return $run;
        });
        $after=$this->acqState();$fresh=$this->onIndependentOwnerConnection(fn()=>$this->acqState());
        $this->acqSave('managed_generic_replay',compact('before','during','visible','after','fresh','commitError'));
        $this->assertNotSame($before,$during);$this->assertSame($before,$after);$this->assertSame($before,$fresh);
        $error=null;try{$repo->touchStage($run,'INGEST_BARS');}catch(RuntimeException $e){$error=$e->getMessage();}
        $this->assertStringContainsString('RUN_OWNER_', (string)$error);$this->assertSame($before,$this->acqState());
    }
    public static function acqModes(): array {return [['full'],['import'],['takeover']];}
    private function acqState(): array
    {
        $m=(new ReflectionClass(B19ArtifactWriterMariaDbTest::class))->getMethod('state');$m->setAccessible(true);return $m->invoke($this);
    }
    private function acqSave(string $name,array $data): void
    {
        $dir=getenv('B19_E049_STATE_DIR');if(!$dir)return;if(!is_dir($dir))mkdir($dir,0775,true);
        file_put_contents($dir.'/ACQ_'.$name.'.json',json_encode($data,JSON_PRETTY_PRINT));
    }
    /** @dataProvider acqModes */
    public function test_acq_each_compound_write_retains_date_and_rolls_back(string $mode): void
    {
        $date='2026-03-21';$repo=new EodRunRepository();config(['market_data.pipeline.active_run_stale_minutes'=>60]);
        Carbon::setTestNow('2026-03-21 18:00:00');$old=null;
        if($mode==='takeover')$old=$repo->getOrCreateOwningRun($date,'api','INGEST_BARS',null,'full_publish');
        Carbon::setTestNow('2026-03-25 18:00:00');
        $source=$mode==='takeover'?'manual_file':'api';$request=$mode==='import'?'import_only':($mode==='takeover'?'correction':'full_publish');
        $targets=['run','lease','credential','created_event','capture'];
        if($mode!=='import')$targets[]='last_lease';
        if($mode==='takeover')$targets=array_merge($targets,['cancel','takeover_event']);
        foreach($targets as $target){
            $before=$this->acqState();$gate=(object)['armed'=>true,'lease_count'=>0];$hit=null;$probe=null;
            DB::listen(function($q)use($gate,&$hit,&$probe,$date,$target){
                if(!$gate->armed)return;
                $lease=preg_match('/^update `eod_run_owner_fences` set `owner_run_id`/i',$q->sql)===1;
                if($lease)$gate->lease_count++;
                $match=($target==='run'&&stripos($q->sql,'insert into `eod_runs`')===0)
                    ||($target==='lease'&&$lease&&$gate->lease_count===1)
                    ||($target==='last_lease'&&$lease&&$gate->lease_count===2)
                    ||($target==='credential'&&stripos($q->sql,'update `eod_run_owner_fences` set `execution_id`')===0)
                    ||($target==='created_event'&&stripos($q->sql,'insert into `eod_run_events`')===0&&in_array('RUN_CREATED',$q->bindings,true))
                    ||($target==='capture'&&stripos($q->sql,'insert into `md_run_input_captures`')===0)
                    ||($target==='cancel'&&stripos($q->sql,'update `eod_runs`')===0&&in_array('CANCELLED',$q->bindings,true))
                    ||($target==='takeover_event'&&stripos($q->sql,'insert into `eod_run_events`')===0&&in_array('RUN_OWNER_TAKEOVER',$q->bindings,true));
                if(!$match)return;$gate->armed=false;$hit=['sql'=>$q->sql,'bindings'=>$q->bindings,'target'=>$target];
                $probe=$this->onIndependentOwnerConnection(function()use($date){
                    DB::statement('SET SESSION innodb_lock_wait_timeout=1');
                    try{return DB::transaction(function()use($date){DB::table('eod_run_owner_fences')->where('trade_date_requested',$date)->lockForUpdate()->first();return 'DATE_LOCK_ACQUIRED';});}
                    catch(\Illuminate\Database\QueryException $e){return $e->getMessage();}
                });
                throw new RuntimeException('ACQUISITION_AFTER_WRITE');
            });
            $error=null;try{$repo->getOrCreateOwningRun($date,$source,'INGEST_BARS',null,$request);}catch(RuntimeException $e){$error=$e->getMessage();}finally{$gate->armed=false;}
            $after=$this->acqState();$fresh=$this->onIndependentOwnerConnection(fn()=>$this->acqState());
            $this->acqSave('atomic_'.$mode.'_'.$target,compact('before','after','fresh','hit','probe','error'));
            $this->assertSame('ACQUISITION_AFTER_WRITE',$error,'Actual target must execute: '.$target);$this->assertNotNull($hit);
            $this->assertStringContainsString('1205',(string)$probe,'Acquisition lost its date lock at '.$target);
            $this->assertSame($before,$after,'Acquisition compound mutation survived rollback: '.$target);$this->assertSame($before,$fresh);
        }
        $before=$this->acqState();$run=$repo->getOrCreateOwningRun($date,$source,'INGEST_BARS',null,$request);
        $after=$this->acqState();$fresh=$this->onIndependentOwnerConnection(fn()=>$this->acqState());$this->assertSame($after,$fresh);
        $this->assertNotSame($before,$after);$lease=DB::table('eod_run_owner_fences')->where('trade_date_requested',$date)->first();
        $this->assertSame((int)$run->run_id,(int)$lease->owner_run_id);$this->assertNotEmpty($lease->execution_id);$this->assertNotEmpty($lease->credential_hash);$this->assertNull($lease->released_at);
        $this->assertSame('NOT_READABLE',$run->publishability_state);$this->assertNull($run->terminal_status);
        $this->assertSame(1,DB::table('eod_run_events')->where('run_id',$run->run_id)->where('event_type','RUN_CREATED')->count());
        $this->assertGreaterThan(0,DB::table('md_run_input_captures')->where('run_id',$run->run_id)->count());
        $repo->touchStage($run,'INGEST_BARS',['notes'=>'Actual newly issued acquisition credential works']);
        if($old){$this->assertSame('CANCELLED',DB::table('eod_runs')->where('run_id',$old->run_id)->value('lifecycle_state'));
            $error=null;try{$repo->touchStage($old,'HASH');}catch(RuntimeException $e){$error=$e->getMessage();}$this->assertStringContainsString('RUN_OWNER_', (string)$error);}
        $this->acqSave('committed_'.$mode,compact('before','after','fresh'));
    }
    public function test_acq_foreign_resume_refuses_and_diagnostics_cannot_borrow_owner(): void
    {
        Carbon::setTestNow('2026-03-25 18:00:00');$repo=new EodRunRepository();$date='2026-03-21';
        $run=$repo->getOrCreateOwningRun($date,'api','INGEST_BARS',null,'full_publish');$before=$this->acqState();
        $lease=(array)DB::table('eod_run_owner_fences')->where('trade_date_requested',$date)->first();
        $error=$this->onIndependentOwnerConnection(function()use($repo,$date){try{$repo->getOrCreateOwningRun($date,'api','HASH',null,'full_publish');return null;}catch(RuntimeException $e){return $e->getMessage();}});
        $this->assertStringContainsString('RUN_OWNER_', (string)$error);$this->assertSame($before,$this->acqState());
        $error=$this->onIndependentOwnerConnection(function()use($repo,$date){try{$repo->getOrCreateOwningRun($date,'manual_file','HASH',null,'correction');return null;}catch(RuntimeException $e){return $e->getMessage();}});
        $after=$this->acqState();$this->acqSave('foreign_diagnostic',compact('before','after','error'));
        $this->assertStringContainsString('RUN_OWNER_', (string)$error);
        $this->assertSame($lease,(array)DB::table('eod_run_owner_fences')->where('trade_date_requested',$date)->first());
        $incumbent=null;foreach($before['eod_runs']as $row)if((int)$row['run_id']===(int)$run->run_id)$incumbent=$row;
        $this->assertNotNull($incumbent);
        $this->assertSame($incumbent,(array)DB::table('eod_runs')->where('run_id',$run->run_id)->first());
        $blocked=DB::table('eod_runs')->where('run_id','<>',$run->run_id)->first();$this->assertNotNull($blocked);
        $this->assertSame('BLOCKED',$blocked->terminal_status);$this->assertSame('NOT_READABLE',$blocked->publishability_state);$this->assertSame(0,(int)$blocked->is_current_publication);
        $event=DB::table('eod_run_events')->where('run_id',$blocked->run_id)->first();
        $this->assertSame('OBSERVATION_ONLY',$event->writer_kind);$this->assertSame('conflict|'.$date,$event->writer_scope_key);$this->assertNotSame($lease['execution_id'],$event->writer_actor_id);
        Carbon::setTestNow('2026-03-25 18:00:10');$resumed=$repo->getOrCreateOwningRun($date,'api','HASH',null,'full_publish');
        $renewed=(array)DB::table('eod_run_owner_fences')->where('trade_date_requested',$date)->first();
        $this->assertSame((int)$run->run_id,(int)$resumed->run_id);
        foreach(['owner_run_id','acquired_at','execution_id','credential_hash']as $key)$this->assertSame($lease[$key],$renewed[$key]);
        $this->assertGreaterThan($lease['heartbeat_at'],$renewed['heartbeat_at']);
        $this->assertSame(1,DB::table('eod_run_events')->where('run_id',$run->run_id)->where('event_type','RUN_CREATED')->count());
    }
}
