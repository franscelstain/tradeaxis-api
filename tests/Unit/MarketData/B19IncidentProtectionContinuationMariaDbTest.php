<?php

use App\Application\MarketData\DTOs\MarketDataStageInput;
use App\Application\MarketData\Services\MarketDataIncidentResponseService;
use App\Application\MarketData\Services\MarketDataEvidenceExportService;
use App\Infrastructure\Persistence\MarketData\EodPublicationRepository;
use App\Infrastructure\Persistence\MarketData\EodRunRepository;
use App\Models\EodRun;
use App\Console\Commands\MarketData\PromoteMarketDataCommand;
use App\Application\MarketData\Services\MarketDataPipelineService;
use Symfony\Component\Console\Tester\CommandTester;
use Illuminate\Support\Facades\DB;
use Tests\Support\R0025SyntheticV2World;
require_once __DIR__.'/B19OwnerFenceMariaDbTest.php';

/** Observed-condition response, not complete detection, principal authentication or incident closure. */
class B19IncidentProtectionContinuationMariaDbTest extends B19OwnerFenceMariaDbTest
{
    use \Tests\Support\RetainsB19ObservedEvidence;
    protected function tearDown(): void { \Carbon\Carbon::setTestNow(); parent::tearDown(); }

    private function world(): array
    {
        $date='2026-03-23';
        $world=R0025SyntheticV2World::build(['http_fetcher'=>function()use(&$date){
            $body=json_decode(file_get_contents(R0025SyntheticV2World::packagePath().'/inputs/provider_response.json'),true);
            $body['chart']['result'][0]['timestamp']=[(new DateTimeImmutable($date.' 09:00:00',new DateTimeZone('Asia/Jakarta')))->getTimestamp()];
            return ['status'=>200,'headers'=>['Content-Type'=>'application/json'],'body'=>json_encode($body)];
        }]);
        DB::table('md_market_calendar_revisions')->insert([
            'revision_uid'=>hash('sha256','e049-security-sealed-next-session'),'market_code'=>'IDX','market_segment'=>'REGULAR',
            'cal_date'=>'2026-03-24','timezone'=>'Asia/Jakarta','is_trading_day'=>1,'is_half_day'=>0,'session_state'=>'COMPLETED',
            'session_open_at'=>'2026-03-24 09:00:00','session_close_at'=>'2026-03-24 16:00:00','completed_at'=>'2026-03-24 16:00:00',
            'recorded_at'=>'2026-03-24 17:00:00','source_ref'=>'independent-security-next-session','source_version'=>'test-only-v1',
            'provenance_tier'=>'VERIFIED','reconciled_at'=>'2026-03-24 17:00:00','reconciliation_source_ref'=>'independent-security-next-session',
        ]);
        $date='2026-03-24';\Carbon\Carbon::setTestNow('2026-03-25 10:30:00');
        $world['current']=$world['pipeline']->runDaily($date,'api');
        $this->assertSame('READABLE',$world['current']->publishability_state);
        return $world;
    }

    private function condition(EodRun $run,string $reason): array
    {
        (new EodRunRepository())->recordObservation($run,'detector','FINALIZE','OBSERVED_CONDITION','ERROR',
            'Independent typed observed-condition input; no severity or protection output supplied.',null,['observed_condition'=>$reason]);
        return (new MarketDataIncidentResponseService())->observeRun((int)$run->run_id);
    }

    private function history(int $id): array
    {
        $out=[];
        foreach(['bars','indicators','eligibility']as $family)
            $out[$family]=DB::table('eod_'.$family.'_history')->where('publication_id',$id)->orderBy('ticker_id')->get()->toJson();
        return $out;
    }

    public function test_security_on_sealed_current_allows_only_fresh_authorized_sanitized_replacement_and_retains_old_containment(): void
    {
        $world=$this->world();$old=$world['current'];$service=new MarketDataIncidentResponseService();$publications=new EodPublicationRepository();
        $oldHistory=$this->history((int)$old->publication_id);
        $source=DB::table('md_source_observations')->where('run_id',$old->run_id)->orderBy('source_observation_id')->get()->toJson();
        $incident=$this->condition($old,'UNAUTHENTICATED_PAYLOAD');
        $this->assertSame(['security/provenance'],$incident['classes']);$this->assertSame('CRITICAL',$incident['severity_floor']);
        $this->assertSame('CONTAIN_AND_NEW_SANITIZED_ARTIFACTS',$incident['protection']['required_recovery']);
        $event=DB::table('eod_run_events')->where('run_id',$old->run_id)->where('event_type','INCIDENT_RESPONSE_RECORDED')->orderByDesc('event_id')->first();
        foreach(['consumer_read','evidence_export','ordinary_retry','correction_baseline','prior_fallback']as $action){
            $error=null;try{$service->assertActionAllowed($old->trade_date_requested,$action,(int)$old->run_id);}catch(RuntimeException $e){$error=$e->getMessage();}
            $this->assertStringContainsString('INCIDENT_RESPONSE_BLOCKED',(string)$error,$action);
        }
        $service->assertActionAllowed($old->trade_date_requested,'new_acquisition');
        $this->assertSame((int)$world['publication_id'],(int)$publications->resolveCurrentReadablePublicationForTradeDate('2026-03-23')->publication_id);
        $retry=null;$failure=null;$commandOutput=null;$commandExit=null;$import=null;
        try{
            $import=$world['pipeline']->importDaily($old->trade_date_requested,'api');
            $this->assertSame('import_only',$import->request_mode);
            $this->assertSame((int)$old->publication_id,(int)DB::table('eod_current_publication_pointer')->where('trade_date',$old->trade_date_requested)->value('publication_id'));
            app()->instance(MarketDataPipelineService::class,$world['pipeline']);
            $countBefore=DB::table('eod_runs')->count();
            $missingReason=new PromoteMarketDataCommand();$missingReason->setLaravel(app());$missingTester=new CommandTester($missingReason);
            $missingExit=$missingTester->execute(['--requested_date'=>$old->trade_date_requested,'--source_mode'=>'api','--run_id'=>$import->run_id,'--force_replace'=>'true']);
            $this->assertSame(1,$missingExit);$this->assertStringContainsString('COMMAND_DESTRUCTIVE_GUARD_REQUIRED',$missingTester->getDisplay());
            $this->assertSame($countBefore,DB::table('eod_runs')->count(),'A missing operator reason must not create a publication owner.');
            $command=new PromoteMarketDataCommand();$command->setLaravel(app());$tester=new CommandTester($command);
            $commandExit=$tester->execute(['--requested_date'=>$old->trade_date_requested,'--source_mode'=>'api','--run_id'=>$import->run_id,
                '--force_replace'=>'true','--force_replace_reason'=>'New sanitized acquisition after scoped security containment.']);
            $commandOutput=$tester->getDisplay();
            $retry=EodRun::query()->where('trade_date_requested',$old->trade_date_requested)->orderByDesc('run_id')->first();
        }catch(Throwable $e){$failure=['type'=>get_class($e),'message'=>$e->getMessage()];}
        $this->retainObservedWorld('security_sealed_current_reacquisition',[$world['run_id'],$old->run_id,$retry?$retry->run_id:0],
            compact('incident','oldHistory','source','failure','commandExit','commandOutput'));
        $this->assertNull($failure,'A new authenticated acquisition with explicit replacement intent must not read/reuse affected evidence. '.json_encode($failure));
        $this->assertSame(0,$commandExit,(string)$commandOutput);
        $this->assertNotSame((int)$old->run_id,(int)$retry->run_id);
        $this->assertNotSame((int)$old->publication_id,(int)$retry->publication_id);
        $this->assertSame('READABLE',$retry->publishability_state);$this->assertSame('SUCCESS',$retry->terminal_status);
        $this->assertSame($oldHistory,$this->history((int)$old->publication_id));
        $this->assertSame($source,DB::table('md_source_observations')->where('run_id',$old->run_id)->orderBy('source_observation_id')->get()->toJson());
        $this->assertSame($event->event_payload_json,DB::table('eod_run_events')->where('event_id',$event->event_id)->value('event_payload_json'));
        $this->assertSame('SEALED',DB::table('eod_publications')->where('publication_id',$old->publication_id)->value('seal_state'));
        // Promotion has its own actor; the fresh source was legitimately acquired by the isolated import actor.
        $newObservations=DB::table('md_source_observations')->where('run_id',$import->run_id)->pluck('source_observation_id')->map(fn($id)=>(int)$id)->all();
        $this->assertNotEmpty($newObservations);
        $this->assertNotSame((int)$import->run_id,(int)$retry->run_id);
        $this->assertSame(0,DB::table('md_source_observations')->where('run_id',$old->run_id)->whereIn('source_observation_id',$newObservations)->count());
        $actualSourceIds=DB::table('eod_bars_history')->where('publication_id',$retry->publication_id)->pluck('source_observation_id')->map(fn($id)=>(int)$id)->all();
        $this->assertNotEmpty($actualSourceIds);$this->assertSame([],array_values(array_diff($actualSourceIds,$newObservations)),
            'Promoted bars must use fresh import observations, never the contained current population.');
        $audit=DB::table('eod_run_events')->where('run_id',$import->run_id)->where('event_type','INCIDENT_BASELINE_REUSE_REFUSED')->first();
        $this->assertNotNull($audit);$this->assertFalse(json_decode($audit->event_payload_json,true)['affected_data_reused']);
        $exportError=null;try{app(MarketDataEvidenceExportService::class)->exportRunEvidence($old->run_id);}catch(RuntimeException $e){$exportError=$e->getMessage();}
        $this->assertStringContainsString('INCIDENT_RESPONSE_BLOCKED',(string)$exportError);
        $independent=$this->onIndependentOwnerConnection(function()use($retry,$old){
            $p=(new EodPublicationRepository())->resolveCurrentReadablePublicationForTradeDate($old->trade_date_requested);
            $blocked=null;try{(new MarketDataIncidentResponseService())->assertActionAllowed($old->trade_date_requested,'evidence_export',(int)$old->run_id);}catch(RuntimeException $e){$blocked=$e->getMessage();}
            return ['connection'=>DB::selectOne('SELECT CONNECTION_ID() AS id')->id,'publication'=>(int)$p->publication_id,'blocked'=>$blocked];
        });
        $this->assertNotSame((int)DB::selectOne('SELECT CONNECTION_ID() AS id')->id,(int)$independent['connection']);
        $this->assertSame((int)$retry->publication_id,$independent['publication']);
        $this->assertStringContainsString('INCIDENT_RESPONSE_BLOCKED',(string)$independent['blocked']);
        $this->retainObservedWorld('security_sealed_current_reacquisition_final',[$world['run_id'],$old->run_id,$retry->run_id],compact('independent','incident'));
    }

    public function test_security_replacement_failure_rolls_back_pointer_and_mirrors_without_releasing_affected_data(): void
    {
        $world=$this->world();$old=$world['current'];$this->condition($old,'SECRET_LEAKAGE');
        $history=$this->history((int)$old->publication_id);
        $import=$world['pipeline']->importDaily($old->trade_date_requested,'api');
        app()->instance(MarketDataPipelineService::class,$world['pipeline']);
        DB::unprepared("CREATE TRIGGER e049_security_switch_fault BEFORE UPDATE ON eod_current_publication_pointer FOR EACH ROW SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT='INJECTED_SECURITY_SWITCH_FAILURE'");
        $command=new PromoteMarketDataCommand();$command->setLaravel(app());$tester=new CommandTester($command);
        try{$exit=$tester->execute(['--requested_date'=>$old->trade_date_requested,'--source_mode'=>'api','--run_id'=>$import->run_id,
            '--force_replace'=>'true','--force_replace_reason'=>'Controlled failed sanitized replacement.']);$output=$tester->getDisplay();}
        finally{DB::unprepared('DROP TRIGGER IF EXISTS e049_security_switch_fault');}
        $failed=EodRun::query()->where('trade_date_requested',$old->trade_date_requested)->orderByDesc('run_id')->first();
        $persisted=$this->onIndependentOwnerConnection(function()use($old,$failed){return [
            'connection'=>DB::selectOne('SELECT CONNECTION_ID() AS id')->id,
            'pointer'=>DB::table('eod_current_publication_pointer')->where('trade_date',$old->trade_date_requested)->first(),
            'failed_candidate'=>DB::table('eod_publications')->where('run_id',$failed->run_id)->first(),
            'old_current'=>DB::table('eod_publications')->where('publication_id',$old->publication_id)->value('is_current'),
            'run'=>DB::table('eod_runs')->where('run_id',$failed->run_id)->first(),
            'fence'=>DB::table('eod_run_owner_fences')->where('trade_date_requested',$old->trade_date_requested)->first(),
            'finalized_message'=>DB::table('eod_run_events')->where('run_id',$failed->run_id)->where('event_type','RUN_FINALIZED')->value('message'),
        ];});
        $this->retainObservedWorld('security_replacement_failed_switch',[$world['run_id'],$old->run_id,$import->run_id,$failed->run_id],compact('history','exit','output','persisted'));
        $this->assertSame(1,$exit,$output);
        $this->assertStringContainsString('INJECTED_SECURITY_SWITCH_FAILURE',(string)$persisted['finalized_message'],'Assert the retained actual SQL failure, not an invented command rendering obligation.');
        $this->assertSame((int)$old->publication_id,(int)$persisted['pointer']->publication_id);
        $this->assertSame(1,(int)$persisted['old_current']);$this->assertSame(0,(int)$persisted['failed_candidate']->is_current);
        $this->assertNotSame('READABLE',$persisted['run']->publishability_state);$this->assertNotNull($persisted['fence']->released_at);
        $this->assertNotSame((int)DB::selectOne('SELECT CONNECTION_ID() AS id')->id,(int)$persisted['connection']);
        $this->assertSame($history,$this->history((int)$old->publication_id));
        $error=null;try{(new EodPublicationRepository())->resolveCurrentReadablePublicationForTradeDate($old->trade_date_requested);}catch(RuntimeException $e){$error=$e->getMessage();}
        $this->assertStringContainsString('INCIDENT_RESPONSE_BLOCKED',(string)$error);
        // A failed new owner must not poison later legitimate recovery or close the old incident.
        $retry=$world['pipeline']->promoteSingleDay($old->trade_date_requested,'api',$import->run_id,null,null,true,'Retry explicit sanitized replacement after rollback.');
        $this->assertSame('READABLE',$retry->publishability_state);
        $this->assertNotSame((int)$failed->run_id,(int)$retry->run_id);
    }

    public function test_security_import_does_not_bypass_later_integrity_or_contained_seed_evidence(): void
    {
        $world=$this->world();$old=$world['current'];$this->condition($old,'SECRET_LEAKAGE');
        $import=$world['pipeline']->importDaily($old->trade_date_requested,'api');
        $this->condition($old,'HISTORY_MUTATION_ATTEMPT');
        $history=$this->history((int)$old->publication_id);$pointer=DB::table('eod_current_publication_pointer')->orderBy('trade_date')->get()->toJson();
        $result=$world['pipeline']->promoteSingleDay($old->trade_date_requested,'api',$import->run_id,null,null,true,'Intent cannot override later integrity containment.');
        $this->retainObservedWorld('security_import_later_integrity_refusal',[$world['run_id'],$old->run_id,$import->run_id,$result->run_id],compact('pointer','history'));
        $this->assertNotSame('READABLE',$result->publishability_state,'Fresh source and force intent cannot override new-acquisition integrity refusal.');
        $this->assertSame($pointer,DB::table('eod_current_publication_pointer')->orderBy('trade_date')->get()->toJson());
        $this->assertSame($history,$this->history((int)$old->publication_id));
        $beforeRuns=DB::table('eod_runs')->orderBy('run_id')->get()->toJson();
        $beforeFences=DB::table('eod_run_owner_fences')->get()->toJson();
        $this->condition($import,'SOURCE_OBSERVATION_PROVENANCE_MISSING');
        $error=null;try{$world['pipeline']->promoteSingleDay($old->trade_date_requested,'api',$import->run_id,null,null,true,'A contained import is not a sanitized seed.');}catch(RuntimeException $e){$error=$e->getMessage();}
        $this->assertStringContainsString('INCIDENT_RESPONSE_BLOCKED',(string)$error,'A contained import cannot become seed evidence.');
        $this->assertSame($beforeRuns,DB::table('eod_runs')->orderBy('run_id')->get()->toJson(),'Refused seed evidence must roll back derived actor creation.');
        $this->assertSame($beforeFences,DB::table('eod_run_owner_fences')->get()->toJson());
    }

    public function test_security_seed_copy_failure_rolls_back_derived_owner_candidate_capture_and_events(): void
    {
        $world=$this->world();$old=$world['current'];$this->condition($old,'UNAUTHENTICATED_PAYLOAD');
        $import=$world['pipeline']->importDaily($old->trade_date_requested,'api');
        $state=function(){ $out=[];foreach(['eod_runs','eod_publications','eod_run_owner_fences','eod_run_events','md_run_input_captures','eod_bars_history','eod_current_publication_pointer']as $table)
            $out[$table]=DB::table($table)->get()->toJson();return $out; };
        $before=$state();$id=(int)$import->publication_id;
        DB::unprepared("CREATE TRIGGER e049_seed_copy_fault BEFORE INSERT ON eod_bars_history FOR EACH ROW BEGIN IF NEW.publication_id > ".$id." THEN SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT='INJECTED_SEED_COPY_FAILURE'; END IF; END");
        $error=null;try{$world['pipeline']->promoteSingleDay($old->trade_date_requested,'api',$import->run_id,null,null,true,'Controlled failed seed copy.');}
        catch(RuntimeException $e){$error=$e->getMessage();}finally{DB::unprepared('DROP TRIGGER IF EXISTS e049_seed_copy_fault');}
        $after=$state();$this->retainObservedWorld('security_seed_copy_atomic_failure',[$world['run_id'],$old->run_id,$import->run_id],compact('before','after','error'));
        $this->assertStringContainsString('INJECTED_SEED_COPY_FAILURE',(string)$error);
        $this->assertSame($before,$after,'Failed seed copy must roll back actor, candidate, capture, events and fencing authority as one unit.');
        $independent=$this->onIndependentOwnerConnection($state);$this->assertSame($before,$independent);
        $retry=$world['pipeline']->promoteSingleDay($old->trade_date_requested,'api',$import->run_id,null,null,true,'Legitimate retry after atomic seed copy failure.');
        $this->assertSame('READABLE',$retry->publishability_state);
    }

    public function test_security_contained_completed_publication_is_not_a_new_sanitized_promote_seed(): void
    {
        $world=$this->world();$old=$world['current'];$this->condition($old,'UNAUTHENTICATED_PAYLOAD');
        $before=DB::table('eod_current_publication_pointer')->orderBy('trade_date')->get()->toJson();
        $history=$this->history((int)$old->publication_id);$runs=DB::table('eod_runs')->count();
        $error=null;$result=null;
        try{$result=$world['pipeline']->promoteSingleDay($old->trade_date_requested,'api',$old->run_id,null,null,true,'Force does not sanitize a contained seed.');}
        catch(RuntimeException $e){$error=$e->getMessage();}
        $this->retainObservedWorld('contained_completed_publication_seed',[$world['run_id'],$old->run_id,$result?$result->run_id:0],compact('before','history','error'));
        $this->assertStringContainsString('INCIDENT_RESPONSE_BLOCKED',(string)$error,'A contained publication cannot become a sanitized acquisition merely by deriving a new run.');
        $this->assertSame($runs,DB::table('eod_runs')->count());
        $this->assertSame($before,DB::table('eod_current_publication_pointer')->orderBy('trade_date')->get()->toJson());
        $this->assertSame($history,$this->history((int)$old->publication_id));
    }

    public function test_integrity_observations_commit_all_reasons_and_refuse_bypass_from_an_independent_reader(): void
    {
        $world=$this->world();$run=$world['current'];$service=new MarketDataIncidentResponseService();
        $history=$this->history((int)$run->publication_id);$pointer=DB::table('eod_current_publication_pointer')->orderBy('trade_date')->get()->toJson();
        $reasons=['HISTORY_MUTATION_ATTEMPT','DATASET_HASH_MISMATCH','SEAL_HASH_MISMATCH','CONFIG_HASH_MISMATCH','MIXED_PUBLICATION','POINTER_ORPHAN_DETECTED'];
        $seen=[];$cases=[];
        foreach($reasons as $reason){
            $seen[]=$reason;$expected=$seen;sort($expected,SORT_STRING);$incident=$this->condition($run,$reason);
            $this->retainIntegrityControl($reason.'_classification',['incident'=>$incident]);
            $this->assertSame(['integrity'],$incident['classes']);$this->assertSame($expected,$incident['reasons']);
            $this->assertSame('CRITICAL',$incident['severity_floor']);$this->assertFalse($incident['protection']['in_place_repair_authorized']);
            $errors=$this->onIndependentOwnerConnection(function()use($service,$run,$world){
                $results=['connection'=>DB::selectOne('SELECT CONNECTION_ID() AS id')->id];
                foreach(['consumer_read','ordinary_retry','prior_fallback','new_acquisition','correction_baseline','release_claim']as $action){
                    $error=null;try{$service->assertActionAllowed($run->trade_date_requested,$action,(int)$run->run_id);}catch(RuntimeException $e){$error=$e->getMessage();}
                    $results[$action]=$error;
                }
                $service->assertActionAllowed($run->trade_date_requested,'evidence_export',(int)$run->run_id);
                $results['prior']=(int)(new EodPublicationRepository())->resolveCurrentReadablePublicationForTradeDate('2026-03-23')->publication_id;
                return $results;
            });
            $cases[]=compact('reason','incident','errors');
            $this->retainIntegrityControl($reason.'_protection',compact('reason','incident','errors'));
            foreach(['consumer_read','ordinary_retry','prior_fallback','new_acquisition','correction_baseline','release_claim']as $action)
                $this->assertStringContainsString('INCIDENT_RESPONSE_BLOCKED',(string)$errors[$action],$reason.'/'.$action);
            $this->assertNotSame((int)DB::selectOne('SELECT CONNECTION_ID() AS id')->id,(int)$errors['connection']);
            $this->assertSame((int)$world['publication_id'],$errors['prior']);
            $this->assertSame($history,$this->history((int)$run->publication_id));
            $this->assertSame($pointer,DB::table('eod_current_publication_pointer')->orderBy('trade_date')->get()->toJson());
        }
        $this->retainObservedWorld('committed_integrity_reason_response',[$world['run_id'],$run->run_id],compact('cases','history','pointer'));
    }
    private function retainIntegrityControl(string $label,array $data): void
    {
        $dir=getenv('B19_E049_STATE_DIR');if(!$dir)return;
        if(!is_dir($dir))mkdir($dir,0775,true);
        $data+=['database'=>DB::connection()->getDatabaseName(),'connection'=>(int)DB::selectOne('SELECT CONNECTION_ID() AS id')->id,
            'persisted_events'=>DB::table('eod_run_events')->orderByDesc('event_id')->limit(2)->get()->all()];
        file_put_contents($dir.'/INTEGRITY_CONTROL_'.$label.'.json',json_encode($data,JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES));
    }

}
