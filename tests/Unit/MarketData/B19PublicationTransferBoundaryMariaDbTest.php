<?php

use App\Application\MarketData\DTOs\MarketDataStageInput;
use App\Infrastructure\Persistence\MarketData\EodArtifactRepository;
use App\Infrastructure\Persistence\MarketData\EodPublicationRepository;
use App\Infrastructure\Persistence\MarketData\EodRunRepository;
use App\Infrastructure\Persistence\MarketData\ReplayIsolation;
use App\Models\EodRun;
use Illuminate\Support\Facades\DB;
use Tests\Support\R0025SyntheticV2World;
require_once __DIR__.'/B19ArtifactWriterMariaDbTest.php';

/** Existing semantics only. Test-only fixture reflection never issues a runtime credential. */
class B19PublicationTransferBoundaryMariaDbTest extends B19ArtifactWriterMariaDbTest
{
    private function fixture(string $method, ...$args)
    {
        $m=(new ReflectionClass(B19ArtifactWriterMariaDbTest::class))->getMethod($method);
        $m->setAccessible(true);return $m->invoke($this,...$args);
    }

    private function observed(): array { return $this->fixture('state'); }

    private function assertProjection(string $source,string $target,int $sourcePub,int $targetPub,int $targetRun): void
    {
        $sourceRows=DB::table($source)->where('publication_id',$sourcePub)->orderBy('ticker_id')->get();
        $targetRows=DB::table($target)->where('publication_id',$targetPub)->orderBy('ticker_id')->get();
        $this->assertNotEmpty($targetRows,'History transfer target missing after authorized fallback');
        $this->assertSame($sourceRows->count(),$targetRows->count());
        $ids=[];foreach([$source,$target]as $table)foreach(DB::select('SHOW COLUMNS FROM `'.$table.'`')as $column)
            if($column->Extra==='auto_increment')$ids[]=$column->Field;
        foreach($sourceRows as $index=>$sourceRow){
            $expected=(array)$sourceRow;$actual=(array)$targetRows[$index];
            // Independent expectation: preserve every shared source fact, bind only target identity/time.
            foreach(array_diff(array_intersect(array_keys($expected),array_keys($actual)),array_merge($ids,['publication_id','run_id','created_at']))as $key)
                $this->assertSame($expected[$key],$actual[$key],'Source fact changed during transfer '.$source.':'.$key);
            $this->assertSame($targetRun,(int)$actual['run_id']);$this->assertSame($targetPub,(int)$actual['publication_id']);
        }
    }

    private function retainTransfer(string $name,array $evidence): void
    {
        $dir=getenv('B19_E049_STATE_DIR');if(!$dir)return;if(!is_dir($dir))mkdir($dir,0775,true);
        file_put_contents($dir.'/TRANSFER_'.$name.'.json',json_encode($evidence,JSON_PRETTY_PRINT));
    }

    private function refusal(string $name,callable $op,string $code,bool $foreign=false): void
    {
        $before=$this->observed();
        $invoke=function()use($op){try{$op();return null;}catch(RuntimeException $e){return $e->getMessage();}};
        $error=$foreign?$this->onIndependentOwnerConnection($invoke):$invoke();
        $after=$this->observed();$this->retainTransfer($name,compact('before','after','error'));
        $this->assertSame($before,$after,'Transfer refusal persisted '.$name);
        $this->assertStringContainsString($code,(string)$error,'Transfer refusal must be contractual '.$name);
    }

    private function committedAtomic(string $name,string $date,callable $op,string $writePattern): array
    {
        // No enclosing transaction can accidentally retain a prematurely released inner lock.
        $this->assertSame(0,DB::transactionLevel(),'No outer transaction in actual-boundary probe');
        $before=$this->observed();$armed=true;$probe=null;$hit=null;$level=null;
        DB::listen(function($q)use(&$armed,&$probe,&$hit,&$level,$date,$writePattern){
            if(!$armed||!preg_match($writePattern,$q->sql))return;
            $armed=false;$hit=$q->sql;$level=DB::transactionLevel();
            $probe=$this->onIndependentOwnerConnection(function()use($date){
                DB::statement('SET SESSION innodb_lock_wait_timeout=1');
                try{return DB::transaction(function()use($date){
                    DB::table('eod_run_owner_fences')->where('trade_date_requested',$date)->lockForUpdate()->first();
                    return 'FENCE_LOCK_ACQUIRED';
                });}catch(\Illuminate\Database\QueryException $e){return $e->getMessage();}
            });
            throw new RuntimeException('TRANSFER_FAULT_AFTER_WRITE');
        });
        try{$op();$this->fail('Transfer fault not reached '.$name);}
        catch(RuntimeException $e){$this->assertSame('TRANSFER_FAULT_AFTER_WRITE',$e->getMessage(),$name);}
        finally{$armed=false;}
        $after=$this->observed();$fresh=$this->onIndependentOwnerConnection(fn()=>$this->observed());
        $this->retainTransfer('atomic_'.$name,compact('before','after','fresh','probe','hit','level'));
        $this->assertSame($before,$after,'Transfer compound write survived rollback '.$name);
        $this->assertSame($before,$fresh,'Fresh connection sees partial transfer '.$name);
        $this->assertStringContainsString('1205',(string)$probe,'No-outer artifact date fence missing '.$name);
        $this->assertStringContainsString('eod_run_owner_fences',(string)$probe);
        $this->assertGreaterThan(0,$level,'Mutation must retain its own transaction '.$name);
        $op();$after=$this->observed();$fresh=$this->onIndependentOwnerConnection(fn()=>$this->observed());
        $this->assertSame(0,DB::transactionLevel());$this->assertSame($after,$fresh,'Committed retry not visible '.$name);
        $this->retainTransfer('committed_retry_'.$name,compact('before','after','fresh'));
        return [$before,$after];
    }

    public function test_transfer_artifact_date_fence_without_outer_transaction(): void
    {
        [$run,$pub]=$this->fixture('candidate');$ops=$this->fixture('operations',$run,$pub);
        foreach($ops as $name=>$op){
            $this->fixture('prepareMutation',$name,$run,$pub);
            $change=strpos($name,'partial_')===0&&strpos($name,'invalid')===false?
                ['close'=>(float)$this->fixture('row','eod_bars',$pub)['close']+1]:[];
            $this->committedAtomic($name,$run->trade_date_requested,fn()=>$op($change),
                '/^(?:insert into|delete from|update) `eod_(?:bars|indicators|eligibility|invalid_bars)(?:_history)?`/i');
        }
    }

    public function test_transfer_live_fallback_source_target_refusals_and_atomic_copy(): void
    {
        [$sourceRun,$source]=$this->fixture('candidate');$date=$sourceRun->trade_date_requested;
        // Deliberately arrange the supported legacy live-only source while it is UNSEALED.
        DB::table('eod_bars_history')->where('publication_id',$source)->delete();
        $this->assertSame(0,DB::table('eod_bars_history')->where('publication_id',$source)->count());
        $repo=new EodRunRepository();$repo->finalize($sourceRun,['lifecycle_state'=>'COMPLETED','terminal_status'=>'HELD','publishability_state'=>'NOT_READABLE']);
        $owner=$repo->acquireLinkedRecoveryOwner($date,'api',$sourceRun->run_id);
        $target=(int)(new EodPublicationRepository())->getOrCreateCandidatePublication($owner)->publication_id;
        $artifacts=new EodArtifactRepository();$op=fn()=>$artifacts->replaceBarsHistoryFromPublication($date,$source,$target,$owner->run_id);
        $this->refusal('copy_foreign',$op,'RUN_OWNER_',true);
        $this->refusal('copy_released_source_actor',fn()=>$artifacts->replaceBarsHistoryFromPublication($date,$source,$target,$sourceRun->run_id),'RUN_OWNER_');
        $this->refusal('copy_missing_source',fn()=>$artifacts->replaceBarsHistoryFromPublication($date,9999999,$target,$owner->run_id),'INPUT_CAPTURE_RAW_COPY_SOURCE_MISSING');
        $other=$repo->getOrCreateOwningRun('2026-03-25','api','INGEST_BARS',null,'full_publish');
        $otherPub=(int)(new EodPublicationRepository())->getOrCreateCandidatePublication($other)->publication_id;
        // Adversarial staging fixture, not a market-price/lineage admission: the other date really has rows.
        $otherBar=array_replace($this->fixture('row','eod_bars',$source),['trade_date'=>'2026-03-25','run_id'=>(int)$other->run_id,'publication_id'=>$otherPub]);
        $artifacts->replaceBars('2026-03-25',$otherPub,$other->run_id,[$otherBar],[]);
        $this->assertGreaterThan(0,DB::table('eod_bars')->where('publication_id',$otherPub)->count());
        $this->refusal('copy_wrong_date',fn()=>$artifacts->replaceBarsHistoryFromPublication('2026-03-25',$source,$target,$owner->run_id),'RUN_OWNER_');
        $this->refusal('copy_wrong_run',fn()=>$artifacts->replaceBarsHistoryFromPublication($date,$source,$target,$other->run_id),'RUN_OWNER_');
        $this->refusal('copy_foreign_target',fn()=>$artifacts->replaceBarsHistoryFromPublication($date,$source,$otherPub,$owner->run_id),'RUN_OWNER_');
        $this->refusal('copy_other_date_source',fn()=>$artifacts->replaceBarsHistoryFromPublication($date,$otherPub,$target,$owner->run_id),'INPUT_CAPTURE_RAW_COPY_SOURCE_MISSING');
        // Explicit corrupt sealed target fixture must refuse rather than rewrite it.
        DB::table('eod_publications')->where('publication_id',$target)->update(['seal_state'=>'SEALED']);
        $this->refusal('copy_sealed_target',$op,'SEALED_PUBLICATION_IMMUTABLE');
        DB::table('eod_publications')->where('publication_id',$target)->update(['seal_state'=>'UNSEALED']);
        $sourceBefore=$this->observed();
        $this->committedAtomic('copy_live_fallback',$date,$op,'/^delete from `eod_bars_history`/i');
        $after=$this->observed();
        foreach(['eod_bars','eod_current_publication_pointer','eod_publications','eod_runs']as $table)
            $this->assertSame($sourceBefore[$table],$after[$table],'History copy mutated source/pointer '.$table);
        $this->assertProjection('eod_bars','eod_bars_history',$source,$target,(int)$owner->run_id);
        $this->assertGreaterThan(0,DB::table('md_run_input_captures')->where('run_id',$owner->run_id)->count());
        $repo->finalize($owner,['lifecycle_state'=>'COMPLETED','terminal_status'=>'HELD','publishability_state'=>'NOT_READABLE']);
        $successor=$this->onIndependentOwnerConnection(fn()=>$repo->acquireLinkedRecoveryOwner($date,'api',$owner->run_id));
        $this->refusal('copy_released_after_takeover',$op,'RUN_OWNER_');
        $this->assertSame((int)$successor->run_id,(int)DB::table('eod_run_owner_fences')->where('trade_date_requested',$date)->value('owner_run_id'));
    }

    public function test_transfer_direct_sealed_promotion_is_atomic_and_does_not_switch_pointer(): void
    {
        $w=R0025SyntheticV2World::build(['run_workflow'=>function($pipeline,$date){$run=null;
            foreach(['INGEST_BARS'=>'completeIngest','COMPUTE_INDICATORS'=>'completeIndicators','BUILD_ELIGIBILITY'=>'completeEligibility','HASH'=>'completeHash','SEAL'=>'completeSeal']as $stage=>$method)
                $run=$pipeline->$method(new MarketDataStageInput($date,'api',$run?$run->run_id:null,$stage,null,false,null,'full_publish'));
            return $run;}]);
        $run=EodRun::findOrFail($w['run_id']);$pub=(int)$w['publication_id'];$date=$run->trade_date_requested;
        $this->assertSame('SEALED',DB::table('eod_publications')->where('publication_id',$pub)->value('seal_state'));
        $artifacts=new EodArtifactRepository();$op=fn()=>$artifacts->promotePublicationHistoryToCurrent($date,$pub,$run->run_id);
        $this->refusal('promote_sealed_foreign',$op,'RUN_OWNER_',true);
        // Remove live projection only as test arrangement, preserving the real sealed source.
        foreach(['bars','indicators','eligibility']as $a)DB::table('eod_'.$a)->where('trade_date',$date)->delete();
        $before=$this->observed();
        $this->committedAtomic('promote_sealed',$date,$op,'/^insert into `eod_eligibility`/i');
        $after=$this->observed();
        foreach(['eod_bars_history','eod_indicators_history','eod_eligibility_history','eod_publications','eod_runs','eod_current_publication_pointer']as $t)
            $this->assertSame($before[$t],$after[$t],'Direct projection promotion rewrote sealed authority '.$t);
        foreach(['bars','indicators','eligibility']as $a){
            $this->assertProjection('eod_'.$a.'_history','eod_'.$a,$pub,$pub,(int)$run->run_id);
        }
    }

    public function test_transfer_direct_promotion_refuses_isolated_import_and_replay(): void
    {
        $repo=new EodRunRepository();$pubs=new EodPublicationRepository();$artifacts=new EodArtifactRepository();$date='2026-03-21';
        $import=$repo->getOrCreateOwningRun($date,'api','INGEST_BARS',null,'import_only');$p=$pubs->getOrCreateCandidatePublication($import);
        $this->refusal('promote_import',fn()=>$artifacts->promotePublicationHistoryToCurrent($date,$p->publication_id,$import->run_id),'RUN_OWNER_');
        $repo->completeImportOnly($import,'INGEST_BARS');$before=$this->observed();
        ReplayIsolation::run(function()use($repo,$pubs,$artifacts,$date,$import){
            $replay=$repo->createAsKnownReplayRun($date,$import->knowledge_cutoff_at,'api');$p=$pubs->getOrCreateCandidatePublication($replay);
            $this->refusal('promote_replay',fn()=>$artifacts->promotePublicationHistoryToCurrent($date,$p->publication_id,$replay->run_id),'RUN_OWNER_');
        });
        $after=$this->observed();$this->retainTransfer('isolated_replay_rollback',compact('before','after'));
        $this->assertSame($before,$after,'Direct promotion refusal escaped replay rollback');
    }

    public function test_transfer_restore_refuses_non_owner_and_holds_fence_through_last_write(): void
    {
        $w=R0025SyntheticV2World::build();$historical=EodRun::findOrFail($w['run_id']);$prior=(int)$w['publication_id'];$date=$historical->trade_date_requested;
        $runs=new EodRunRepository();$pubs=new EodPublicationRepository();$owner=$runs->acquireLinkedRecoveryOwner($date,'api',$historical->run_id);
        $op=fn()=>$pubs->restorePriorCurrentPublication($date,$prior,$historical->run_id,$owner);
        $this->refusal('restore_missing',fn()=>$pubs->restorePriorCurrentPublication($date,$prior,$historical->run_id),'RUN_OWNER_');
        $this->refusal('restore_released_historical',fn()=>$pubs->restorePriorCurrentPublication($date,$prior,$historical->run_id,$historical),'RUN_OWNER_');
        $this->refusal('restore_competitor_model',$op,'RUN_OWNER_',true);
        $other=$runs->getOrCreateOwningRun('2026-03-25','api','INGEST_BARS',null,'full_publish');
        $this->refusal('restore_wrong_date',fn()=>$pubs->restorePriorCurrentPublication($date,$prior,$historical->run_id,$other),'RUN_OWNER_');
        $this->refusal('restore_prior_run_mismatch',fn()=>$pubs->restorePriorCurrentPublication($date,$prior,$other->run_id,$owner),'POINTER_PUBLICATION_TRADE_DATE_MISMATCH');
        // Legitimately clear the pointer first; restoration must be a material transition, not an idempotent oracle.
        $pubs->clearCurrentPublicationState($date,$owner);
        $this->assertSame(0,DB::table('eod_current_publication_pointer')->where('trade_date',$date)->count());
        $historicalBefore=(array)DB::table('eod_runs')->where('run_id',$historical->run_id)->first();
        $sourceBefore=$this->observed();
        // Fail after the last mirror update, so the entire compound transition must roll back.
        $this->committedAtomic('restore_last_mirror',$date,$op,'/^update `eod_runs` set `publication_id`/i');
        $historicalAfter=(array)DB::table('eod_runs')->where('run_id',$historical->run_id)->first();
        foreach(['lifecycle_state','terminal_status','publishability_state','bars_batch_hash','indicators_batch_hash','eligibility_batch_hash','sealed_at','publication_id','publication_version']as $field)
            $this->assertSame($historicalBefore[$field],$historicalAfter[$field],'Restore rewrote immutable historical identity '.$field);
        $sourceAfter=$this->observed();foreach(['eod_bars_history','eod_indicators_history','eod_eligibility_history','md_source_observations','md_run_input_captures']as $t)
            $this->assertSame($sourceBefore[$t],$sourceAfter[$t],'Restore changed immutable source '.$t);
        $pointer=DB::table('eod_current_publication_pointer')->where('trade_date',$date)->first();
        $this->assertSame($prior,(int)$pointer->publication_id);$this->assertSame((int)$historical->run_id,(int)$pointer->run_id);
        $this->assertSame($prior,(int)$pubs->resolveCurrentReadablePublicationForTradeDate($date)->publication_id);
        $runs->finalize($owner,['lifecycle_state'=>'COMPLETED','terminal_status'=>'HELD','publishability_state'=>'NOT_READABLE']);
        $successor=$this->onIndependentOwnerConnection(function()use($runs,$pubs,$date,$prior,$historical,$owner){
            $successor=$runs->acquireLinkedRecoveryOwner($date,'api',$owner->run_id);
            // Use the credential on the actual issuing connection, never a reloaded model on a fresh connection.
            $pubs->restorePriorCurrentPublication($date,$prior,$historical->run_id,$successor);return $successor;
        });
        $this->refusal('restore_stale_after_takeover',$op,'RUN_OWNER_');
        $this->assertSame($prior,(int)DB::table('eod_current_publication_pointer')->where('trade_date',$date)->value('publication_id'));
    }

    public function test_transfer_material_import_projection_refusal(): void
    {
        [$old,$source]=$this->fixture('candidate');$date=$old->trade_date_requested;
        $rows=[];foreach(['bars','indicators','eligibility']as $a)$rows[$a]=$this->fixture('row','eod_'.$a,$source);
        $runs=new EodRunRepository();$runs->finalize($old,['lifecycle_state'=>'COMPLETED','terminal_status'=>'HELD','publishability_state'=>'NOT_READABLE']);
        $import=$runs->getOrCreateOwningRun($date,'api','INGEST_BARS',$old->run_id,'import_only');
        $pub=(int)(new EodPublicationRepository())->getOrCreateCandidatePublication($import)->publication_id;
        foreach($rows as &$row){$row['run_id']=(int)$import->run_id;$row['publication_id']=$pub;}unset($row);
        $artifacts=new EodArtifactRepository();
        $artifacts->replaceBars($date,$pub,$import->run_id,[$rows['bars']],[],true);
        $artifacts->replaceIndicators($date,$import->run_id,[$rows['indicators']],$pub,true);
        $artifacts->replaceEligibility($date,$import->run_id,[$rows['eligibility']],$pub,true);
        $this->assertGreaterThan(0,DB::table('eod_bars_history')->where('publication_id',$pub)->count());
        $this->refusal('material_import',fn()=>$artifacts->promotePublicationHistoryToCurrent($date,$pub,$import->run_id),'RUN_OWNER_');
    }

    public function test_transfer_material_missing_actor_restore_refusal(): void
    {
        $w=R0025SyntheticV2World::build();$old=EodRun::findOrFail($w['run_id']);$date=$old->trade_date_requested;
        $owner=(new EodRunRepository())->acquireLinkedRecoveryOwner($date,'api',$old->run_id);
        $repo=new EodPublicationRepository();$repo->clearCurrentPublicationState($date,$owner);
        $this->assertSame(0,DB::table('eod_current_publication_pointer')->where('trade_date',$date)->count());
        $this->refusal('material_restore_missing',fn()=>$repo->restorePriorCurrentPublication($date,$w['publication_id'],$old->run_id),'RUN_OWNER_');
    }
}
