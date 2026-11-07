<?php

use App\Application\MarketData\Services\DeterministicHashService;
use App\Application\MarketData\Services\PublicationProjectionReconciliationService;
use App\Application\MarketData\Services\PublicationProjectionRepairService;
use App\Infrastructure\Persistence\MarketData\EodArtifactRepository;
use App\Infrastructure\Persistence\MarketData\EodPublicationRepository;
use Illuminate\Support\Facades\DB;
use Tests\Support\R0025SyntheticV2World;
require_once __DIR__.'/B19ArtifactWriterMariaDbTest.php';

class B19ProjectionRecoveryMariaDbTest extends B19ArtifactWriterMariaDbTest
{
    private function observed(): array
    {
        $m=(new ReflectionClass(B19ArtifactWriterMariaDbTest::class))->getMethod('state');$m->setAccessible(true);return $m->invoke($this);
    }
    private function reconciliation(): PublicationProjectionReconciliationService
    {return new PublicationProjectionReconciliationService(new EodPublicationRepository(),new DeterministicHashService());}
    private function repair(): PublicationProjectionRepairService
    {return new PublicationProjectionRepairService(new EodPublicationRepository(),new EodArtifactRepository(),$this->reconciliation());}
    private function retainProjection(string $name,array $data): void
    {
        $dir=getenv('B19_E049_STATE_DIR');if(!$dir)return;if(!is_dir($dir))mkdir($dir,0775,true);
        file_put_contents($dir.'/PROJECTION_'.$name.'.json',json_encode($data,JSON_PRETTY_PRINT));
    }
    public function test_projection_recovery_restores_drift_with_new_owner_preserving_sealed_history_and_pointer(): void
    {
        $world=R0025SyntheticV2World::build();$run=\App\Models\EodRun::findOrFail($world['run_id']);$date=$run->trade_date_requested;
        $this->assertSame('COMPLETED',$run->lifecycle_state);
        DB::table('eod_bars')->where('publication_id',$world['publication_id'])->update(['close'=>999]);
        $before=$this->observed();$error=null;$result=null;
        try{$result=$this->repair()->repairTradeDate($date,'Controlled persisted projection-recovery proof');}
        catch(Throwable $e){$error=$e->getMessage();}
        $after=$this->observed();$this->retainProjection('owner_repair',compact('before','after','result','error'));
        $this->assertNull($error,'Authorized projection recovery failed');
        $this->assertSame('REBUILT_AND_VERIFIED',$result['repair_state']);$this->assertSame('PASS',$result['after']['reconciliation_state']);
        foreach(['eod_bars_history','eod_indicators_history','eod_eligibility_history','eod_publications','eod_current_publication_pointer']as $table)
            $this->assertSame($before[$table],$after[$table],'Projection recovery changed sealed source '.$table);
        $this->assertNotSame($before['eod_bars'],$after['eod_bars']);
        $this->assertGreaterThan((int)$run->run_id,(int)$result['recovery_run_id']);
        $historical=(array)DB::table('eod_runs')->where('run_id',$run->run_id)->first();
        $this->assertSame($before['eod_runs'][0],$historical,'Projection recovery rewrote terminal history');
        $fresh=$this->onIndependentOwnerConnection(fn()=>$this->observed());$this->assertSame($after,$fresh);
    }
    public function test_projection_repair_command_discloses_source_before_first_persisted_write(): void
    {
        $world=R0025SyntheticV2World::build();$date=\App\Models\EodRun::findOrFail($world['run_id'])->trade_date_requested;
        DB::table('eod_bars')->where('publication_id',$world['publication_id'])->update(['close'=>999]);
        $configId=(int)DB::table('eod_runs')->where('run_id',$world['run_id'])->value('config_snapshot_id');
        $publicationConfigId=(int)DB::table('eod_publications')->where('publication_id',$world['publication_id'])->value('config_snapshot_id');
        $this->assertGreaterThan(0,$configId);$this->assertSame($configId,$publicationConfigId);
        $expectedConfigHash=(string)DB::table('md_config_snapshots')->where('config_snapshot_id',$configId)->value('config_hash');
        $this->assertMatchesRegularExpression('/^[a-f0-9]{64}$/',$expectedConfigHash);
        $before=$this->observed();
        $command=new \App\Console\Commands\MarketData\RepairPublicationProjectionCommand();
        $command->setLaravel($this->app);$tester=new \Symfony\Component\Console\Tester\CommandTester($command);
        $firstWriteSql=null;$displayAtFirstWrite=null;
        DB::listen(function($q)use(&$firstWriteSql,&$displayAtFirstWrite,$tester){
            if($firstWriteSql!==null||!preg_match('/^(insert|update|delete)\s/i',$q->sql))return;
            $firstWriteSql=$q->sql;$displayAtFirstWrite=$tester->getDisplay();
        });
        $exit=$tester->execute(['--trade_date'=>$date,'--apply'=>true,'--reason'=>'Explicit projection correction']);
        $after=$this->observed();$fresh=$this->onIndependentOwnerConnection(fn()=>$this->observed());
        $this->retainProjection('command_preapply',compact('before','after','fresh','exit','firstWriteSql','displayAtFirstWrite'));
        $this->assertSame(0,$exit,$tester->getDisplay());
        $this->assertNotNull($firstWriteSql,'Command did not exercise a persisted write');
        foreach(['operation_mode=PRE_APPLY_DISCLOSURE','repair_publication_id='.$world['publication_id'],
            'repair_run_id='.$world['run_id'],'config_snapshot_id='.$configId,'config_hash='.$expectedConfigHash,
            'expected_projection_effect=REBUILD_FROM_IMMUTABLE_HISTORY',
            'pointer_impact=UNCHANGED','repair_reason=Explicit projection correction'] as $required)
            $this->assertStringContainsString($required,(string)$displayAtFirstWrite,'Pre-write disclosure missing '.$required);
        foreach(['eod_bars_history','eod_indicators_history','eod_eligibility_history','eod_publications','eod_current_publication_pointer'] as $table)
            $this->assertSame($before[$table],$after[$table],'Repair command changed sealed source '.$table);
        $this->assertSame($after,$fresh);
        $this->assertSame('PASS',$this->reconciliation()->inspectTradeDate($date)['reconciliation_state']);
    }
    public function test_projection_repair_command_refuses_a_new_current_source_after_preflight(): void
    {
        $response=file_get_contents(R0025SyntheticV2World::packagePath().'/inputs/provider_response.json');
        $world=R0025SyntheticV2World::build(['http_fetcher'=>function()use(&$response){
            return ['status'=>200,'body'=>$response,'headers'=>['Content-Type'=>'application/json']];
        }]);
        $baseRun=\App\Models\EodRun::findOrFail($world['run_id']);$date=$baseRun->trade_date_requested;
        $baseline=(int)$world['publication_id'];
        $changed=json_decode($response,true);$changed['chart']['result'][0]['indicators']['quote'][0]['close'][0]=106;
        $response=json_encode($changed);
        $corrections=new \App\Infrastructure\Persistence\MarketData\EodCorrectionRepository();
        $request=$corrections->createRequest($date,'DATASET_HASH_CREATED','Projection preflight race proof','operator-proof',$baseline,(int)$baseRun->run_id);
        $corrections->approve($request->correction_id,'approver-proof');
        $replacement=$world['pipeline']->runDaily($date,'api',$request->correction_id);
        $current=(int)DB::table('eod_current_publication_pointer')->where('trade_date',$date)->value('publication_id');
        $this->assertNotSame($baseline,$current);
        DB::table('eod_bars')->where('publication_id',$current)->update(['close'=>999]);

        $afterTakeover=null;$test=$this;
        $service=new class(new EodPublicationRepository(),new EodArtifactRepository(),$this->reconciliation(),
            function()use($test,$date,$baseline,$baseRun,$replacement,&$afterTakeover){
                $test->onIndependentOwnerConnection(function()use($date,$baseline,$baseRun,$replacement){
                    $runs=new \App\Infrastructure\Persistence\MarketData\EodRunRepository();
                    $owner=$runs->acquireLinkedRecoveryOwner($date,'api',(int)$replacement->run_id);
                    (new EodPublicationRepository())->restorePriorCurrentPublication($date,$baseline,(int)$baseRun->run_id,$owner);
                    $runs->finalize($owner,['lifecycle_state'=>'COMPLETED','terminal_status'=>'HELD',
                        'publishability_state'=>'NOT_READABLE','quality_gate_state'=>'BLOCKED',
                        'final_reason_code'=>'RUN_CURRENT_PUBLICATION_INTEGRITY_REPAIRED']);
                });
                $afterTakeover=$test->observed();
            }) extends PublicationProjectionRepairService {
            private $afterInspect;
            public function __construct($pubs,$artifacts,$reconciliation,callable $afterInspect)
            {parent::__construct($pubs,$artifacts,$reconciliation);$this->afterInspect=$afterInspect;}
            public function inspectTradeDate(string $date): array
            {$result=parent::inspectTradeDate($date);($this->afterInspect)();return $result;}
        };
        $this->app->instance(PublicationProjectionRepairService::class,$service);
        $command=new \App\Console\Commands\MarketData\RepairPublicationProjectionCommand();
        $command->setLaravel($this->app);$tester=new \Symfony\Component\Console\Tester\CommandTester($command);
        $exit=$tester->execute(['--trade_date'=>$date,'--apply'=>true,'--reason'=>'Explicit preflight race control']);
        $after=$this->observed();$fresh=$this->onIndependentOwnerConnection(fn()=>$this->observed());
        $this->retainProjection('command_preflight_takeover',compact('baseline','current','afterTakeover','after','fresh','exit'));
        $this->assertSame(1,$exit,$tester->getDisplay());
        $this->assertStringContainsString('PROJECTION_REPAIR_PREFLIGHT_CHANGED',$tester->getDisplay());
        $this->assertSame($baseline,(int)DB::table('eod_current_publication_pointer')->where('trade_date',$date)->value('publication_id'));
        $this->assertSame($afterTakeover,$after,'Command mutated after competitor changed the disclosed source.');
        $this->assertSame($after,$fresh);
    }
    public function test_projection_observation_has_own_fenced_append_only_identity(): void
    {
        $world=R0025SyntheticV2World::build();$date=\App\Models\EodRun::findOrFail($world['run_id'])->trade_date_requested;
        $before=$this->observed();$result=$this->reconciliation()->reconcileTradeDate($date);$after=$this->observed();
        $this->retainProjection('observation_identity',compact('before','after','result'));
        $this->assertSame('OBSERVATION_ONLY',$result['writer_kind']??null,'Projection evidence lacks independent writer authority');
        $this->assertNotEmpty($result['writer_actor_id']??null);$this->assertSame('projection|'.$date,$result['writer_scope_key']??null);
        $this->assertGreaterThan(0,(int)($result['writer_fencing_token']??0));
        foreach(['eod_runs','eod_publications','eod_current_publication_pointer','eod_bars','eod_bars_history','eod_run_owner_fences']as $t)$this->assertSame($before[$t],$after[$t],'Observer gained publication write rights '.$t);
    }

    public function test_projection_repair_compound_failure_retains_date_fence_and_rolls_back_acquisition_audit_and_all_tables(): void
    {
        $world=R0025SyntheticV2World::build();$date=\App\Models\EodRun::findOrFail($world['run_id'])->trade_date_requested;
        DB::table('eod_bars')->where('publication_id',$world['publication_id'])->update(['close'=>999]);
        foreach(['bars','indicators','eligibility','observation','release'] as $fault){
            $before=$this->observed();$gate=(object)['armed'=>true];$hit=null;$probe=null;
            DB::listen(function($q)use($gate,&$hit,&$probe,$fault,$date){
                $pattern=$fault==='release'?'/^update `eod_run_owner_fences` set `released_at`/i':($fault==='observation'?'/^insert into `md_publication_projection_reconciliations`/i':'/^delete from `eod_'.$fault.'`/i');
                if(!$gate->armed||!preg_match($pattern,$q->sql))return;$gate->armed=false;$hit=$q->sql;
                $probe=$this->onIndependentOwnerConnection(function()use($date){DB::statement('SET SESSION innodb_lock_wait_timeout=1');
                    try{return DB::transaction(function()use($date){DB::table('eod_run_owner_fences')->where('trade_date_requested',$date)->lockForUpdate()->first();return 'UNLOCKED';});}
                    catch(\Illuminate\Database\QueryException $e){return $e->getMessage();}});
                throw new RuntimeException('PROJECTION_AFTER_WRITE');
            });
            try{$this->repair()->repairTradeDate($date,'Atomic repair control');$this->fail('Compound repair fault not reached');}
            catch(RuntimeException $e){$this->assertSame('PROJECTION_AFTER_WRITE',$e->getMessage());}finally{$gate->armed=false;}
            $after=$this->observed();$fresh=$this->onIndependentOwnerConnection(fn()=>$this->observed());
            $this->retainProjection('atomic_'.$fault,compact('before','after','fresh','hit','probe'));
            $expected=$fault==='release'?'update `eod_run_owner_fences` set `released_at`':($fault==='observation'?'insert into `md_publication_projection_reconciliations`':'delete from `eod_'.$fault.'`');$this->assertStringContainsString($expected,(string)$hit,'Projection fault targeted a different writer');$this->assertSame($before,$after,'Partial projection recovery committed');$this->assertSame($before,$fresh);
            $this->assertStringContainsString('1205',(string)$probe,'Projection repair lost date fence');
        }
        $result=$this->repair()->repairTradeDate($date,'Authorized retry after rollback');$this->assertSame('PASS',$result['after']['reconciliation_state']);
        $owner=\App\Models\EodRun::findOrFail($result['recovery_run_id']);$this->assertSame('COMPLETED',$owner->lifecycle_state);$this->assertSame('HELD',$owner->terminal_status);$this->assertSame('NOT_READABLE',$owner->publishability_state);
        $this->assertNotNull(DB::table('eod_run_owner_fences')->where('trade_date_requested',$date)->value('released_at'));
    }

    public function test_projection_repair_refuses_missing_foreign_released_wrong_date_and_wrong_source_without_mutation(): void
    {
        $world=R0025SyntheticV2World::build();$historical=\App\Models\EodRun::findOrFail($world['run_id']);$date=$historical->trade_date_requested;$pub=(int)$world['publication_id'];
        $repo=new \App\Infrastructure\Persistence\MarketData\EodRunRepository();$owner=$repo->acquireLinkedRecoveryOwner($date,'api',$historical->run_id);
        DB::table('eod_bars')->where('publication_id',$pub)->update(['close'=>999]);
        foreach(['missing','foreign','released','wrong_date','wrong_source','incumbent','reason']as $kind){
            $before=$this->observed();$error=null;
            try{
                if($kind==='incumbent')$this->repair()->repairTradeDate($date,'Cannot borrow incumbent');
                elseif($kind==='reason')$this->repair()->repairTradeDate($date);
                elseif($kind==='foreign')$this->onIndependentOwnerConnection(fn()=>(new EodArtifactRepository())->repairCurrentProjectionFromHistory($date,$pub,$owner));
                else (new EodArtifactRepository())->repairCurrentProjectionFromHistory($kind==='wrong_date'?'2026-03-25':$date,$kind==='wrong_source'?$pub+999:$pub,$kind==='missing'?null:($kind==='released'?$historical:$owner));
            }catch(RuntimeException $e){$error=$e->getMessage();}
            $after=$this->observed();$this->retainProjection('refusal_'.$kind,compact('before','after','error'));
            $this->assertSame($before,$after,'Unauthorized repair persisted '.$kind);$this->assertNotNull($error,'Unauthorized repair accepted '.$kind);
            $this->assertMatchesRegularExpression('/RUN_OWNER_|PROJECTION_REPAIR_SOURCE_CHANGED|PROJECTION_REPAIR_REASON_REQUIRED/',(string)$error);
        }
        // Current legitimate owner can rebuild projections without changing historical data identity.
        (new EodArtifactRepository())->repairCurrentProjectionFromHistory($date,$pub,$owner);
        $this->assertSame('PASS',$this->reconciliation()->inspectTradeDate($date)['reconciliation_state']);
        $repo->finalize($owner,['lifecycle_state'=>'COMPLETED','terminal_status'=>'HELD','publishability_state'=>'NOT_READABLE']);
        $before=$this->observed();try{(new EodArtifactRepository())->repairCurrentProjectionFromHistory($date,$pub,$owner);$this->fail('Released repair actor accepted');}catch(RuntimeException $e){$this->assertStringContainsString('RUN_OWNER_',$e->getMessage());}
        $this->assertSame($before,$this->observed());
    }

    public function test_projection_observation_refuses_unscoped_wrong_role_date_and_released_lease_and_has_no_publication_rights(): void
    {
        $world=R0025SyntheticV2World::build();$historical=\App\Models\EodRun::findOrFail($world['run_id']);$date=$historical->trade_date_requested;
        $svc=$this->reconciliation();$before=$this->observed();$payload=$svc->inspectTradeDate($date);$this->assertSame($before,$this->observed(),'Pure inspection performed DML');
        $persist=(new ReflectionClass($svc))->getMethod('persist');$persist->setAccessible(true);
        foreach(['missing','wrong_role','wrong_date','released','superseded']as $kind){
            $before=$this->observed();$error=null;DB::beginTransaction();
            try{
                $work=function()use($kind,$svc,$persist,$payload,$date){
                    if($kind==='released')DB::table('eod_observer_fences')->where('scope_key','projection|'.$date)->update(['released_at'=>\Carbon\Carbon::now()]);
                    if($kind==='superseded')DB::table('eod_observer_fences')->where('scope_key','projection|'.$date)->increment('token');
                    return $persist->invoke($svc,$payload);
                };
                if($kind==='missing')$work();
                else \App\Infrastructure\Persistence\MarketData\RunWriterContext::observeDate($kind==='wrong_role'?'session':'projection',$kind==='wrong_date'?'2026-03-25':$date,null,$work);
            }catch(RuntimeException $e){$error=$e->getMessage();}finally{DB::rollBack();}
            $after=$this->observed();$this->retainProjection('observer_refusal_'.$kind,compact('before','after','error'));
            $this->assertStringContainsString('OBSERVER_AUTHORITY_INVALID',(string)$error);$this->assertSame($before,$after);
        }
        \App\Infrastructure\Persistence\MarketData\RunWriterContext::observeDate('projection',$date,null,function()use($historical,$date,$world){
            $before=$this->observed();try{(new EodArtifactRepository())->repairCurrentProjectionFromHistory($date,(int)$world['publication_id'],$historical);$this->fail('Observer obtained publication authority');}
            catch(RuntimeException $e){$this->assertStringContainsString('RUN_OWNER_',$e->getMessage());}$this->assertSame($before,$this->observed());
        });
        $first=$svc->reconcileTradeDate($date);$second=$svc->reconcileTradeDate($date);
        $this->assertSame($first['reconciliation_hash'],$second['reconciliation_hash']);$this->assertNotSame($first['writer_actor_id'],$second['writer_actor_id']);
        $this->assertGreaterThan($first['writer_fencing_token'],$second['writer_fencing_token']);
    }

    public function test_projection_observation_actual_insert_holds_own_fence_and_rolls_back(): void
    {
        $world=R0025SyntheticV2World::build();$date=\App\Models\EodRun::findOrFail($world['run_id'])->trade_date_requested;
        $this->reconciliation()->reconcileTradeDate($date);$before=$this->observed();$armed=true;$probe=null;
        DB::listen(function($q)use(&$armed,&$probe,$date){if(!$armed||!preg_match('/^insert into `md_publication_projection_reconciliations`/i',$q->sql))return;$armed=false;
            $probe=$this->onIndependentOwnerConnection(function()use($date){DB::statement('SET SESSION innodb_lock_wait_timeout=1');
                try{return DB::transaction(function()use($date){DB::table('eod_observer_fences')->where('scope_key','projection|'.$date)->lockForUpdate()->first();return 'UNLOCKED';});}
                catch(\Illuminate\Database\QueryException $e){return $e->getMessage();}});throw new RuntimeException('OBSERVER_AFTER_INSERT');});
        try{$this->reconciliation()->reconcileTradeDate($date);$this->fail('Observer fault not reached');}catch(RuntimeException $e){$this->assertSame('OBSERVER_AFTER_INSERT',$e->getMessage());}finally{$armed=false;}
        $after=$this->observed();$fresh=$this->onIndependentOwnerConnection(fn()=>$this->observed());$this->retainProjection('observer_atomic',compact('before','after','fresh','probe'));
        $this->assertSame($before,$after);$this->assertSame($before,$fresh);$this->assertStringContainsString('1205',(string)$probe);
        $this->assertSame('PASS',$this->reconciliation()->reconcileTradeDate($date)['reconciliation_state']);
    }
}
