<?php

use App\Application\MarketData\Services\ArtifactSemanticHashService;
use App\Application\MarketData\Services\MarketDataFreshnessEvidenceService;
use App\Application\MarketData\Services\MarketDataSessionFreshnessService;
use App\Infrastructure\Persistence\MarketData\RunWriterContext;
use App\Models\EodRun;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Tests\Support\R0025SyntheticV2World;
require_once __DIR__.'/B19ArtifactWriterMariaDbTest.php';

/** Existing issuer only. These tests do not claim authenticated external operator identity. */
class B19ObservationWriterBoundaryMariaDbTest extends B19ArtifactWriterMariaDbTest
{
    public static function observationFamilies(): array { return [['run'],['session']]; }
    private function obsState(): array
    {
        $m=(new ReflectionClass(B19ArtifactWriterMariaDbTest::class))->getMethod('state');$m->setAccessible(true);return $m->invoke($this);
    }
    private function obsSave(string $name,array $data): void
    {
        $dir=getenv('B19_E049_STATE_DIR');if(!$dir)return;if(!is_dir($dir))mkdir($dir,0775,true);
        file_put_contents($dir.'/OBS_'.$name.'.json',json_encode($data,JSON_PRETTY_PRINT));
    }
    private function obsWorld(): array
    {
        config(['market_data.scope.operational_start_date'=>null]);
        return R0025SyntheticV2World::build(['profile'=>ArtifactSemanticHashService::LEGACY_PROFILE_V1,'run_clock'=>'2026-03-23 18:00:00',
            'http_fetcher'=>function(){Carbon::setTestNow('2026-03-23 18:00:03');return ['status'=>200,'headers'=>['Content-Type'=>'application/json'],
                'body'=>file_get_contents(R0025SyntheticV2World::packagePath().'/inputs/provider_response.json')];}]);
    }
    private function privateAppend(string $kind,EodRun $run,array $payload): int
    {
        $class=$kind==='run'?MarketDataFreshnessEvidenceService::class:MarketDataSessionFreshnessService::class;
        $service=new $class();$m=(new ReflectionClass($class))->getMethod('append');$m->setAccessible(true);
        $result=$kind==='run'?$m->invoke($service,$run,'FRESHNESS_MEASURED',$payload):
            $m->invoke($service,(string)$run->trade_date_requested,(int)$run->run_id,'ASSESSMENT',$payload,Carbon::now('Asia/Jakarta'));
        return $result===null?0:(int)$result;
    }
    /** @dataProvider observationFamilies */
    public function test_obs_existing_public_flow_holds_lease_and_rolls_back(string $kind): void
    {
        $world=$this->obsWorld();$run=EodRun::findOrFail($world['run_id']);$date=(string)$run->trade_date_requested;
        $role=$kind==='run'?'freshness':'session';$table=$kind==='run'?'eod_run_events':'md_freshness_measurements';
        $type=$kind==='run'?'FRESHNESS_MEASURED':'ASSESSMENT';$scope=$role.'|'.$date;
        Carbon::setTestNow('2026-03-23 18:01:00');$before=$this->obsState();$armed=true;$hit=null;$probe=null;
        DB::listen(function($q)use(&$armed,&$hit,&$probe,$table,$type,$scope){
            if(!$armed||stripos($q->sql,'insert into `'.$table.'`')!==0||!in_array($type,$q->bindings,true))return;
            $armed=false;$hit=$q->sql;
            $probe=$this->onIndependentOwnerConnection(function()use($scope){
                DB::statement('SET SESSION innodb_lock_wait_timeout=1');
                try{return DB::transaction(function()use($scope){DB::table('eod_observer_fences')->where('scope_key',$scope)->lockForUpdate()->first();return 'OBSERVER_LOCK_ACQUIRED';});}
                catch(\Illuminate\Database\QueryException $e){return $e->getMessage();}
            });
            throw new RuntimeException('OBSERVATION_AFTER_ACTUAL_INSERT');
        });
        $work=function()use($kind,$run,$date){return $kind==='run'?(new MarketDataFreshnessEvidenceService())->assessRun((int)$run->run_id):
            (new MarketDataSessionFreshnessService())->measure($date,(int)$run->run_id);};
        $error=null;try{$work();}catch(RuntimeException $e){$error=$e->getMessage();}finally{$armed=false;}
        $after=$this->obsState();$fresh=$this->onIndependentOwnerConnection(fn()=>$this->obsState());
        $this->obsSave('atomic_'.$kind,compact('before','after','fresh','probe','hit','error','scope'));
        $this->assertSame('OBSERVATION_AFTER_ACTUAL_INSERT',$error);$this->assertNotNull($hit);
        $this->assertStringContainsString('1205',(string)$probe);$this->assertSame($before,$after);$this->assertSame($before,$fresh);
        $report=$work();$after=$this->obsState();$fresh=$this->onIndependentOwnerConnection(fn()=>$this->obsState());
        $this->obsSave('retry_'.$kind,compact('before','after','fresh','report'));
        $this->assertNotSame($before,$after);$this->assertSame($after,$fresh);
        // Session measurement calls the real run assessment, whose separately attributed
        // freshness events are part of the same transaction. Existing event rows stay immutable.
        $allowed=$kind==='run'?['eod_observer_fences','eod_run_events']:['eod_observer_fences','eod_run_events','md_freshness_measurements'];
        foreach($before as $name=>$rows)if(!in_array($name,$allowed,true))$this->assertSame($rows,$after[$name],'Observation mutated '.$name);
        $oldEvents=[];foreach($before['eod_run_events']as $row)$oldEvents[$row['event_id']]=$row;
        foreach($after['eod_run_events']as $row){
            if(isset($oldEvents[$row['event_id']])){$this->assertSame($oldEvents[$row['event_id']],$row);continue;}
            $this->assertSame('OBSERVATION_ONLY',$row['writer_kind']);$this->assertSame('freshness|'.$date,$row['writer_scope_key']);
            $this->assertSame((int)$run->run_id,(int)$row['run_id']);
            $this->assertContains($row['event_type'],['FRESHNESS_MEASURED','FRESHNESS_ALERT_OPENED','FRESHNESS_ALERT_ESCALATED','FRESHNESS_ALERT_RECOVERED','SOURCE_CAPABILITY_EVALUATION_OPENED']);
        }
        $row=DB::table($table)->where($kind==='run'?'event_type':'record_type',$type)->orderByDesc($kind==='run'?'event_id':'measurement_id')->first();
        $this->assertSame($scope,$row->writer_scope_key);$this->assertNotEmpty($row->writer_actor_id);
        $this->assertGreaterThan(0,(int)$row->writer_fencing_token);
        $this->assertNotNull(DB::table('eod_observer_fences')->where('scope_key',$scope)->value('released_at'));
    }
    public function test_obs_absent_run_incident_is_fenced_atomic_and_attributed(): void
    {
        $world=$this->obsWorld();$prior=EodRun::findOrFail($world['run_id']);$date='2026-03-24';
        DB::table('md_market_calendar_revisions')->insert([
            'revision_uid'=>hash('sha256','e049-observer-boundary-calendar24'),'market_code'=>'IDX','market_segment'=>'REGULAR','cal_date'=>$date,
            'timezone'=>'Asia/Jakarta','is_trading_day'=>1,'is_half_day'=>0,'session_state'=>'COMPLETED',
            'session_open_at'=>$date.' 09:00:00','session_close_at'=>$date.' 16:00:00','completed_at'=>$date.' 16:00:00',
            'recorded_at'=>$date.' 17:00:00','source_ref'=>'declared-synthetic-observer-calendar','source_version'=>'observer-proof-v1',
            'provenance_tier'=>'VERIFIED','reconciled_at'=>$date.' 17:00:00','reconciliation_source_ref'=>'declared-synthetic-observer-calendar']);
        // Deliberately invalid fixture pointer. The observation must report this real persisted
        // orphan, not a caller-invented incident description or a fabricated missing worker.
        DB::table('eod_current_publication_pointer')->insert(['trade_date'=>$date,'publication_id'=>999999,'run_id'=>999999,
            'publication_version'=>1,'updated_at'=>$date.' 18:00:00']);
        Carbon::setTestNow($date.' 18:00:00');$before=$this->obsState();$scope='session_incident|'.$date;
        foreach(['insert_fault','revoked_credential']as $fault){
            $gate=(object)['armed'=>true];$hit=null;$probe=null;
            DB::listen(function($q)use($gate,&$hit,&$probe,$fault,$scope){
                if(!$gate->armed)return;
                if($fault==='revoked_credential'){
                    if(stripos($q->sql,'update `eod_observer_fences`')!==0||!in_array($scope,$q->bindings,true)||strpos($q->sql,'credential_hash')===false)return;
                    $gate->armed=false;$hit=$q->sql;
                    DB::table('eod_observer_fences')->where('scope_key',$scope)->update(['credential_hash'=>str_repeat('0',64)]);return;
                }
                if(stripos($q->sql,'insert into `md_freshness_measurements`')!==0||!in_array('INCIDENT_RESPONSE',$q->bindings,true))return;
                $gate->armed=false;$hit=$q->sql;
                $probe=$this->onIndependentOwnerConnection(function()use($scope){DB::statement('SET SESSION innodb_lock_wait_timeout=1');
                    try{return DB::transaction(function()use($scope){DB::table('eod_observer_fences')->where('scope_key',$scope)->lockForUpdate()->first();return 'OBSERVER_LOCK_ACQUIRED';});}
                    catch(\Illuminate\Database\QueryException $e){return $e->getMessage();}});
                throw new RuntimeException('INCIDENT_AFTER_ACTUAL_INSERT');
            });
            $error=null;try{(new MarketDataSessionFreshnessService())->measure($date);}catch(RuntimeException $e){$error=$e->getMessage();}finally{$gate->armed=false;}
            $after=$this->obsState();$fresh=$this->onIndependentOwnerConnection(fn()=>$this->obsState());
            $this->obsSave('absent_'.$fault,compact('before','after','fresh','hit','probe','error'));
            $this->assertNotNull($hit);$this->assertSame($before,$after,'Absent-run compound observation survived rollback');$this->assertSame($before,$fresh);
            if($fault==='insert_fault'){$this->assertSame('INCIDENT_AFTER_ACTUAL_INSERT',$error);$this->assertStringContainsString('1205',(string)$probe);}
            else $this->assertStringContainsString('OBSERVER_AUTHORITY_INVALID',(string)$error,'Revoked incident credential was accepted');
        }
        $report=(new MarketDataSessionFreshnessService())->measure($date);$this->assertNull($report['run_id']);$this->assertNull($report['publication_id']);
        $this->assertFalse($report['gateway_readable']);$this->assertNotEmpty($report['gateway_error']);
        $row=DB::table('md_freshness_measurements')->where('record_type','INCIDENT_RESPONSE')->first();$this->assertNotNull($row);
        $this->assertNull($row->run_id);$this->assertSame($scope,$row->writer_scope_key);$this->assertNotEmpty($row->writer_actor_id);$this->assertGreaterThan(0,(int)$row->writer_fencing_token);
        $payload=json_decode($row->measurement_json,true);$this->assertSame($date,$payload['requested_date']);$this->assertSame(['integrity'],$payload['classes']);
        $this->assertSame('UNSUPPORTED_PENDING_F018',$payload['incident_closure']);$this->assertFalse($payload['operational_activation_proven']);
        $after=$this->obsState();foreach(['eod_runs','eod_publications','eod_current_publication_pointer','eod_bars_history','eod_indicators_history','eod_eligibility_history']as $table)$this->assertSame($before[$table],$after[$table]);
        $this->obsSave('absent_commit',compact('before','after','report','payload'));
        $badBefore=$this->obsState();$error=null;try{(new \App\Application\MarketData\Services\MarketDataIncidentResponseService())->observeSessionAssessment(999999);}catch(RuntimeException $e){$error=$e->getMessage();}
        $this->assertSame('INCIDENT_SESSION_ASSESSMENT_IDENTITY_INVALID',$error);$this->assertSame($badBefore,$this->obsState());
    }
    /** @dataProvider observationFamilies */
    public function test_obs_actual_append_refuses_missing_wrong_and_revoked_scope(string $kind): void
    {
        $world=$this->obsWorld();$run=EodRun::findOrFail($world['run_id']);$date=(string)$run->trade_date_requested;$role=$kind==='run'?'freshness':'session';
        Carbon::setTestNow('2026-03-23 18:02:00');
        $payload=$kind==='run'?(new MarketDataFreshnessEvidenceService())->assessRun((int)$run->run_id):
            (new MarketDataSessionFreshnessService())->measure($date,(int)$run->run_id);
        unset($payload['measurement_id']);$payload['assessed_at']=Carbon::now('Asia/Jakarta')->addSecond()->toIso8601String();
        foreach(['missing','wrong_role','wrong_target']as $case){
            $before=$this->obsState();$error=null;
            try{
                $append=fn()=>$this->privateAppend($kind,$run,$payload);
                if($case==='missing')$append();
                elseif($case==='wrong_role')RunWriterContext::observe('detector',$run,$append);
                else RunWriterContext::observeDate($role,'2026-03-24',null,$append);
            }catch(RuntimeException $e){$error=$e->getMessage();}
            $after=$this->obsState();$this->obsSave('refuse_'.$kind.'_'.$case,compact('before','after','error'));
            $this->assertStringContainsString('OBSERVER_AUTHORITY_INVALID',(string)$error);$this->assertSame($before,$after);
        }
        RunWriterContext::observe($role,$run,function()use($kind,$run,$payload,$role,$date){
            foreach([['actor_id'=>str_repeat('0',32)],['token'=>999999],['target_run_id'=>null],['released_at'=>'2026-03-23 18:00:00'],['credential_hash'=>str_repeat('0',64)]]as $i=>$revocation){
                DB::beginTransaction();try{
                    DB::table('eod_observer_fences')->where('scope_key',$role.'|'.$date)->update($revocation);
                    $before=$this->obsState();$error=null;try{$this->privateAppend($kind,$run,$payload);}catch(RuntimeException $e){$error=$e->getMessage();}
                    $after=$this->obsState();$this->obsSave('revoked_'.$kind.'_'.$i,compact('before','after','error'));
                    $this->assertStringContainsString('OBSERVER_AUTHORITY_INVALID',(string)$error);$this->assertSame($before,$after);
                }finally{DB::rollBack();}
            }
            $this->privateAppend($kind,$run,$payload);
        });
        $before=$this->obsState();$payload['assessed_at']=Carbon::now('Asia/Jakarta')->addSeconds(2)->toIso8601String();$error=null;
        try{$this->privateAppend($kind,$run,$payload);}catch(RuntimeException $e){$error=$e->getMessage();}
        $this->assertStringContainsString('OBSERVER_AUTHORITY_INVALID',(string)$error);$this->assertSame($before,$this->obsState());
    }
}
