<?php

use App\Application\MarketData\Services\MarketDataIncidentResponseService;
use App\Infrastructure\Persistence\MarketData\EodPublicationRepository;
use App\Infrastructure\Persistence\MarketData\EodRunRepository;
use App\Models\EodRun;
use Illuminate\Support\Facades\DB;
use Tests\Support\R0025SyntheticV2World;
require_once __DIR__.'/B19OwnerFenceMariaDbTest.php';

/** Immutable observations describe the artifact observed then, not a mutable outcome selector. */
class B19IncidentHistoricalIdentityMariaDbTest extends B19OwnerFenceMariaDbTest
{
    use \Tests\Support\RetainsB19ObservedEvidence;
    protected function tearDown(): void { \Carbon\Carbon::setTestNow(); parent::tearDown(); }

    private function artifactState(): array
    {
        $out=[];
        foreach(['eod_publications','eod_current_publication_pointer','eod_bars','eod_bars_history','eod_indicators_history','eod_eligibility_history']as $table)
            $out[$table]=DB::table($table)->get()->toJson();
        return $out;
    }

    public function test_incident_historical_identity_survives_authorized_outcome_selection_without_losing_containment(): void
    {
        $world=R0025SyntheticV2World::build();$repo=new EodRunRepository();$publications=new EodPublicationRepository();
        $prior=EodRun::findOrFail($world['run_id']);
        $owner=$repo->createPromoteRunFromSeed($prior,'HASH');
        $candidate=$publications->getOrCreateCandidatePublication($owner,$world['publication_id']);
        $owner=$repo->updateTelemetry($owner,['publication_id'=>(int)$candidate->publication_id,'publication_version'=>(int)$candidate->publication_version]);
        $repo->recordObservation($owner,'detector','HASH','OBSERVED_CONDITION','ERROR','Controlled semantic condition, independently selected.',null,['observed_condition'=>'MIXED_PRICE_BASIS']);
        $service=new MarketDataIncidentResponseService();$incident=$service->observeRun((int)$owner->run_id);
        $this->assertSame(['semantic'],$incident['classes']);$this->assertSame((int)$candidate->publication_id,$incident['publication_id']);
        $service->assertActionAllowed($owner->trade_date_requested,'consumer_read',(int)$prior->run_id);
        $event=DB::table('eod_run_events')->where('run_id',$owner->run_id)->where('event_type','INCIDENT_RESPONSE_RECORDED')->first();
        $before=$this->artifactState();$originalEvent=$event->event_payload_json;
        $owner=$repo->updateTelemetry($owner,['publication_id'=>(int)$prior->publication_id,'publication_version'=>(int)$prior->publication_version]);
        $this->assertSame((int)$prior->publication_id,(int)DB::table('eod_runs')->where('run_id',$owner->run_id)->value('publication_id'));
        $this->assertSame($before,$this->artifactState(),'Outcome selection must not rewrite artifact or pointer identity.');
        $this->assertSame($originalEvent,DB::table('eod_run_events')->where('event_id',$event->event_id)->value('event_payload_json'));
        $results=[];
        foreach([['consumer_read',(int)$prior->run_id,false],['evidence_export',(int)$prior->run_id,false],['ordinary_retry',(int)$owner->run_id,true],['consumer_read',(int)$owner->run_id,true]]as [$action,$target,$blocked]){
            $error=null;try{$service->assertActionAllowed($owner->trade_date_requested,$action,$target);}catch(RuntimeException $e){$error=$e->getMessage();}
            $results[]=compact('action','target','blocked','error');
        }
        $this->retainObservedWorld('incident_historical_outcome_identity',[$prior->run_id,$owner->run_id],compact('before','originalEvent','incident','results'));
        foreach($results as $result){
            if($result['blocked'])$this->assertStringContainsString('INCIDENT_RESPONSE_BLOCKED',(string)$result['error'],'Historical incident must retain its original containment.');
            else $this->assertNull($result['error'],'A legitimate outcome reference change must not invalidate historical incident identity.');
        }
        $independent=$this->onIndependentOwnerConnection(function()use($service,$prior,$owner){
            $service->assertActionAllowed($owner->trade_date_requested,'consumer_read',(int)$prior->run_id);
            return [DB::selectOne('SELECT CONNECTION_ID() AS id')->id,DB::table('eod_run_events')->where('run_id',$owner->run_id)->where('event_type','INCIDENT_RESPONSE_RECORDED')->value('event_payload_json')];
        });
        $this->assertNotSame((int)DB::selectOne('SELECT CONNECTION_ID() AS id')->id,(int)$independent[0]);
        $this->assertSame($originalEvent,$independent[1]);
        $this->assertSame((int)$world['publication_id'],(int)$publications->resolveCurrentReadablePublicationForTradeDate($prior->trade_date_requested)->publication_id);
    }

    public function test_incident_context_versions_null_artifact_rehash_refusals_and_observer_rollback(): void
    {
        $world=R0025SyntheticV2World::build();$repo=new EodRunRepository();$service=new MarketDataIncidentResponseService();
        $prior=EodRun::findOrFail($world['run_id']);$owner=$repo->createPromoteRunFromSeed($prior,'HASH');
        $this->assertNull($owner->publication_id);
        $nullIncident=$service->observeRun((int)$owner->run_id);
        $this->assertNull($nullIncident['observed_artifact']);
        $candidate=(new EodPublicationRepository())->getOrCreateCandidatePublication($owner,$world['publication_id']);
        $owner=$repo->updateTelemetry($owner,['publication_id'=>(int)$candidate->publication_id,'publication_version'=>(int)$candidate->publication_version]);
        $service->assertActionAllowed($owner->trade_date_requested,'ordinary_retry',(int)$owner->run_id);
        $incident=$service->observeRun((int)$owner->run_id);
        $event=DB::table('eod_run_events')->where('run_id',$owner->run_id)->where('event_type','INCIDENT_RESPONSE_RECORDED')->orderByDesc('event_id')->first();
        $this->assertSame((int)$owner->run_id,$incident['observed_artifact']['run_id']);
        $owner=$repo->updateTelemetry($owner,['publication_id'=>(int)$prior->publication_id,'publication_version'=>(int)$prior->publication_version]);
        $pointer=$this->artifactState();$cases=[];
        foreach(['legacy_own','legacy_null','wrong_version','wrong_artifact_owner','missing_context','unknown_artifact','missing_with_known_selector','null_with_version','future_pointer','wrong_source','wrong_target','wrong_hash']as $case){
            DB::beginTransaction();try{
                $payload=$incident;unset($payload['content_sha256']);$allowed=in_array($case,['legacy_own','legacy_null'],true);
                if(strpos($case,'legacy_')===0){$payload['schema']='observed-incident-response/v2';unset($payload['observed_artifact']);}
                if($case==='legacy_null'){$payload['publication_id']=null;$payload['publication_version']=null;$payload['observed_pointer_matches_artifact']=false;}
                if($case==='wrong_version')$payload['publication_version']++;
                if($case==='wrong_artifact_owner')$payload['observed_artifact']['run_id']=(int)$prior->run_id;
                if($case==='missing_context')unset($payload['observed_artifact']);
                if($case==='unknown_artifact'){$payload['publication_id']=2147483000;$payload['observed_artifact']['publication_id']=2147483000;}
                if($case==='missing_with_known_selector')$payload['observed_artifact']=null;
                if($case==='null_with_version'){$payload['publication_id']=null;$payload['observed_artifact']=null;}
                if($case==='future_pointer')$payload['observed_pointer']['trade_date']='2026-03-24';
                if($case==='wrong_source')$payload['source_mode']='manual_file';
                if($case==='wrong_target')$payload['run_id']=(int)$prior->run_id;
                $payload['content_sha256']=hash('sha256',\App\Infrastructure\Persistence\MarketData\RunInputCaptureRepository::canonicalJson($payload));
                if($case==='wrong_hash')$payload['content_sha256']=str_repeat('0',64);
                // Deliberately corrupt a cloned persisted row: read-validation adversary, not a writer authority exemption.
                DB::table('eod_run_events')->where('event_id',$event->event_id)->update(['event_payload_json'=>json_encode($payload)]);
                $error=null;try{$service->assertActionAllowed($owner->trade_date_requested,'ordinary_retry',(int)$owner->run_id);}catch(RuntimeException $e){$error=$e->getMessage();}
                $cases[]=compact('case','allowed','error','payload');
                if($allowed)$this->assertNull($error,'Legacy historical identity must remain interpretable: '.$case);
                else $this->assertSame('INCIDENT_EVIDENCE_IDENTITY_INVALID',$error,'Rehashed historical context must fail closed: '.$case);
                $this->assertSame($pointer,$this->artifactState());
            }finally{DB::rollBack();}
        }
        $events=DB::table('eod_run_events')->orderBy('event_id')->get()->toJson();
        $fences=DB::table('eod_observer_fences')->orderBy('scope_key')->get()->toJson();$failure=null;
        try{DB::transaction(function()use($service,$repo,$owner){
            $repo->recordObservation($owner,'detector','HASH','OBSERVED_CONDITION','ERROR','Controlled rollback condition.',null,['observed_condition'=>'MIXED_PRICE_BASIS']);
            $service->observeRun((int)$owner->run_id);throw new RuntimeException('INJECTED_AFTER_OBSERVATION');
        });}catch(RuntimeException $e){$failure=$e->getMessage();}
        $this->assertSame('INJECTED_AFTER_OBSERVATION',$failure);
        $this->assertSame($events,DB::table('eod_run_events')->orderBy('event_id')->get()->toJson());
        $this->assertSame($fences,DB::table('eod_observer_fences')->orderBy('scope_key')->get()->toJson());
        $this->assertSame($pointer,$this->artifactState());
        $this->retainObservedWorld('incident_context_compatibility_and_rollback',[$prior->run_id,$owner->run_id],compact('cases','failure','events','fences'));
        $service->observeRun((int)$owner->run_id);
        $service->assertActionAllowed($owner->trade_date_requested,'ordinary_retry',(int)$owner->run_id);
    }
}
