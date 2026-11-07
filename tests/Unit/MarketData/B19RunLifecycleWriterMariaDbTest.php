<?php

use App\Application\MarketData\DTOs\MarketDataStageInput;
use App\Infrastructure\Persistence\MarketData\EodRunRepository;
use Illuminate\Support\Facades\DB;
require_once __DIR__.'/B19ArtifactWriterMariaDbTest.php';

class B19RunLifecycleWriterMariaDbTest extends B19ArtifactWriterMariaDbTest
{
    public static function lifecycleFamilies(): array { return array_map(fn($x)=>[$x],['touch','telemetry','hashes','seal','fail','hold','finalize','import','capture']); }
    private function fixture(string $method,...$args)
    {
        $m=(new ReflectionClass(B19ArtifactWriterMariaDbTest::class))->getMethod($method);$m->setAccessible(true);return $m->invoke($this,...$args);
    }
    private function observed(): array { return $this->fixture('state'); }
    private function retainLifecycle(string $name,array $data): void
    {
        $dir=getenv('B19_E049_STATE_DIR');if(!$dir)return;if(!is_dir($dir))mkdir($dir,0775,true);
        file_put_contents($dir.'/LIFE_'.$name.'.json',json_encode($data,JSON_PRETTY_PRINT));
    }
    public function test_lifecycle_sealed_run_content_cannot_be_rewritten_by_its_active_owner(): void
    {
        [$run,$pub,$world]=$this->fixture('candidate');
        $run=$world['pipeline']->completeSeal(new MarketDataStageInput($run->trade_date_requested,'api',$run->run_id,'SEAL',null,false,null,'full_publish'));
        $repo=new EodRunRepository();$violations=[];
        foreach(['touch','telemetry','hashes','seal','finalize']as $kind){
            DB::beginTransaction();
            try{
                $actor=\App\Models\EodRun::findOrFail($run->run_id);$before=$this->observed();$error=null;
                try{
                    if($kind==='touch')$repo->touchStage($actor,'HASH',['config_hash'=>str_repeat('f',64)]);
                    elseif($kind==='telemetry')$repo->updateTelemetry($actor,['bars_batch_hash'=>str_repeat('f',64)]);
                    elseif($kind==='hashes')$repo->storeHashes($actor,['bars_batch_hash'=>str_repeat('f',64),'indicators_batch_hash'=>$actor->indicators_batch_hash,'eligibility_batch_hash'=>$actor->eligibility_batch_hash]);
                    elseif($kind==='seal')$repo->markSealed($actor,'different-actor','rewrite sealed note');
                    else $repo->finalize($actor,['lifecycle_state'=>'COMPLETED','terminal_status'=>'HELD','source_file_hash'=>str_repeat('f',64)]);
                }catch(RuntimeException $e){$error=$e->getMessage();}
                $after=$this->observed();$this->retainLifecycle('sealed_'.$kind,compact('before','after','error'));
                if($before!==$after||strpos((string)$error,'SEALED_PUBLICATION_IMMUTABLE')===false)$violations[]=$kind;
            }finally{DB::rollBack();}
        }
        $this->assertSame([],$violations,'Active owner rewrote protected sealed run content');
    }

    public function test_lifecycle_sealed_hash_refusal_is_visible_to_a_fresh_connection(): void
    {
        [$run,$pub,$world]=$this->fixture('candidate');
        $run=$world['pipeline']->completeSeal(new MarketDataStageInput($run->trade_date_requested,'api',$run->run_id,'SEAL',null,false,null,'full_publish'));
        $this->assertSame(0,DB::transactionLevel());$before=$this->observed();$error=null;
        try{(new EodRunRepository())->storeHashes($run,['bars_batch_hash'=>str_repeat('f',64),'indicators_batch_hash'=>$run->indicators_batch_hash,'eligibility_batch_hash'=>$run->eligibility_batch_hash]);}
        catch(RuntimeException $e){$error=$e->getMessage();}
        $after=$this->observed();$fresh=$this->onIndependentOwnerConnection(fn()=>$this->observed());
        $this->retainLifecycle('sealed_hash_committed',compact('before','after','fresh','error'));
        $this->assertSame($before,$after,'Sealed run hash rewrite committed');$this->assertSame($before,$fresh);
        $this->assertStringContainsString('SEALED_PUBLICATION_IMMUTABLE',(string)$error);
    }

    public function test_lifecycle_identical_sealed_content_and_operational_completion_remain_authorized(): void
    {
        [$run,$pub,$world]=$this->fixture('candidate');
        $run=$world['pipeline']->completeSeal(new MarketDataStageInput($run->trade_date_requested,'api',$run->run_id,'SEAL',null,false,null,'full_publish'));
        $repo=new EodRunRepository();$before=$this->observed();
        $run=$repo->updateTelemetry($run,['coverage_ratio'=>(float)$run->coverage_ratio,'coverage_missing_sample_json'=>$run->coverage_missing_sample_json]);
        $run=$repo->storeHashes($run,['bars_batch_hash'=>$run->bars_batch_hash,'indicators_batch_hash'=>$run->indicators_batch_hash,'eligibility_batch_hash'=>$run->eligibility_batch_hash]);
        $run=$repo->markSealed($run,$run->sealed_by,$run->seal_note);
        $this->assertSame($before,$this->observed(),'Idempotent sealed confirmations changed content');
        $run=$repo->finalize($run,['lifecycle_state'=>'COMPLETED','terminal_status'=>'HELD','publishability_state'=>'NOT_READABLE']);
        $this->assertSame('COMPLETED',$run->lifecycle_state);
        $this->assertNotNull(DB::table('eod_run_owner_fences')->where('trade_date_requested',$run->trade_date_requested)->value('released_at'));
        $this->assertSame(1,DB::table('eod_run_events')->where('run_id',$run->run_id)->where('event_type','RUN_OWNER_RELEASED')->count());
    }

    public function test_lifecycle_typed_coverage_sample_is_not_double_encoded_or_rewritten_after_seal(): void
    {
        [$run,$pub,$world]=$this->fixture('candidate');$repo=new EodRunRepository();
        $this->assertSame(0,(int)$run->coverage_missing_count);
        // Independent expected empty sample follows the actual complete one-listing population.
        $run=$repo->updateTelemetry($run,['coverage_missing_sample_json'=>[]]);
        $raw=DB::table('eod_runs')->where('run_id',$run->run_id)->value('coverage_missing_sample_json');
        $this->assertSame([],json_decode($raw,true),'Coverage sample was double encoded instead of storing an array');
        $run=$world['pipeline']->completeSeal(new MarketDataStageInput($run->trade_date_requested,'api',$run->run_id,'SEAL',null,false,null,'full_publish'));
        $before=$this->observed();$repo->updateTelemetry($run,['coverage_missing_sample_json'=>[]]);
        $this->assertSame($before,$this->observed(),'Equivalent typed sample changed sealed content');
    }

    private function subject(string $kind): array
    {
        [$run,$pub,$world]=$this->fixture('candidate');$repo=new EodRunRepository();
        if($kind==='seal'){
            $preSeal=(array)DB::table('eod_runs')->where('run_id',$run->run_id)->first(['sealed_at','sealed_by','seal_note']);
            $run=$world['pipeline']->completeSeal(new MarketDataStageInput($run->trade_date_requested,'api',$run->run_id,'SEAL',null,false,null,'full_publish'));
            // Test arrangement for the native temporal boundary: retain the fully validated
            // publication seal, restore only the exact pre-seal run metadata, then exercise the
            // real first run-seal writer with its genuinely acquired, still-active credential.
            DB::table('eod_runs')->where('run_id',$run->run_id)->update($preSeal);
            $run=\App\Models\EodRun::findOrFail($run->run_id);
            $this->assertNull(\App\Models\EodRun::findOrFail($run->run_id)->sealed_at);
        }elseif(in_array($kind,['import','capture'],true)){
            $repo->finalize($run,['lifecycle_state'=>'COMPLETED','terminal_status'=>'HELD','publishability_state'=>'NOT_READABLE']);
            $run=$repo->getOrCreateOwningRun($run->trade_date_requested,'api','INGEST_BARS',$run->run_id,$kind==='import'?'import_only':'repair_candidate');
        }
        return [$run,$pub,$world];
    }
    private function action(string $kind,$run): void
    {
        $repo=new EodRunRepository();
        if($kind==='touch')$repo->touchStage($run,'HASH',['notes'=>'Owned writer boundary control']);
        elseif($kind==='telemetry')$repo->updateTelemetry($run,['notes'=>'Owned telemetry boundary control']);
        elseif($kind==='hashes')$repo->storeHashes($run,['bars_batch_hash'=>hash('sha256','mutable writer control|'.$run->run_id),'indicators_batch_hash'=>$run->indicators_batch_hash,'eligibility_batch_hash'=>$run->eligibility_batch_hash]);
        elseif($kind==='seal')$repo->markSealed($run,'system','Seal writer boundary control');
        elseif($kind==='fail')$repo->failStage($run,'HASH','RUN_LOCK_CONFLICT','Writer rollback control');
        elseif($kind==='hold')$repo->holdStage($run,'HASH','RUN_LOCK_CONFLICT','Writer rollback control');
        elseif($kind==='import')$repo->completeImportOnly($run,'INGEST_BARS');
        elseif($kind==='capture')(new \App\Infrastructure\Persistence\MarketData\RunInputCaptureRepository())->capture((int)$run->run_id,'INGEST_BARS','ancillary',['operation'=>'writer-boundary-control/v1'],[['declared_control'=>'owned-input']]);
        else $repo->finalize($run,['lifecycle_state'=>'COMPLETED','terminal_status'=>'HELD','publishability_state'=>'NOT_READABLE']);
    }
    /** @dataProvider lifecycleFamilies */
    public function test_lifecycle_each_boundary_refuses_foreign_and_released_actor(string $kind): void
    {
        [$run]=$this->subject($kind);$before=$this->observed();
        $error=$this->onIndependentOwnerConnection(function()use($kind,$run){try{$this->action($kind,$run);return null;}catch(RuntimeException $e){return $e->getMessage();}});
        $after=$this->observed();$this->retainLifecycle('foreign_'.$kind,compact('before','after','error'));
        $this->assertSame($before,$after,'Foreign lifecycle persisted '.$kind);$this->assertStringContainsString('RUN_OWNER_', (string)$error);
        (new EodRunRepository())->finalize($run,['lifecycle_state'=>'COMPLETED','terminal_status'=>'HELD','publishability_state'=>'NOT_READABLE']);
        $this->onIndependentOwnerConnection(fn()=>(new EodRunRepository())->acquireLinkedRecoveryOwner($run->trade_date_requested,'api',$run->run_id));
        $before=$this->observed();$error=null;
        try{$this->action($kind,$run);}catch(RuntimeException $e){$error=$e->getMessage();}
        $after=$this->observed();$this->retainLifecycle('released_'.$kind,compact('before','after','error'));
        $this->assertSame($before,$after,'Released lifecycle persisted '.$kind);$this->assertStringContainsString('RUN_OWNER_', (string)$error);
    }
    /** @dataProvider lifecycleFamilies */
    public function test_lifecycle_each_boundary_retains_fence_rolls_back_and_retries(string $kind): void
    {
        [$run]=$this->subject($kind);$id=$run->run_id;$date=$run->trade_date_requested;
        // Advance the observed execution clock so an idempotent seal confirmation still writes updated_at.
        \Carbon\Carbon::setTestNow(\Carbon\Carbon::now()->addSecond());
        $this->assertSame(0,DB::transactionLevel());$before=$this->observed();$armed=true;$probe=null;$hit=null;
        DB::listen(function($q)use(&$armed,&$probe,&$hit,$date,$kind){
            $terminal=in_array($kind,['fail','hold','finalize','import'],true);
            $pattern=$terminal?'/^update `eod_run_owner_fences` set `released_at`/i':($kind==='capture'?'/^insert into `md_run_input_captures`/i':'/^update `eod_runs`/i');
            if(!$armed||!preg_match($pattern,$q->sql))return;$armed=false;$hit=$q->sql;
            $probe=$this->onIndependentOwnerConnection(function()use($date){DB::statement('SET SESSION innodb_lock_wait_timeout=1');
                try{return DB::transaction(function()use($date){DB::table('eod_run_owner_fences')->where('trade_date_requested',$date)->lockForUpdate()->first();return 'FENCE_LOCK_ACQUIRED';});}
                catch(\Illuminate\Database\QueryException $e){return $e->getMessage();}});
            throw new RuntimeException('LIFECYCLE_AFTER_WRITE');
        });
        try{$this->action($kind,\App\Models\EodRun::findOrFail($id));$this->fail('Lifecycle fault not reached '.$kind);}
        catch(RuntimeException $e){$this->assertSame('LIFECYCLE_AFTER_WRITE',$e->getMessage());}finally{$armed=false;}
        $after=$this->observed();$fresh=$this->onIndependentOwnerConnection(fn()=>$this->observed());
        $this->retainLifecycle('atomic_'.$kind,compact('before','after','fresh','probe','hit'));
        $this->assertSame($before,$after,'Lifecycle partial transition survived '.$kind);$this->assertSame($before,$fresh);
        $this->assertStringContainsString('1205',(string)$probe,'Lifecycle date fence missing '.$kind);
        $this->action($kind,\App\Models\EodRun::findOrFail($id));$after=$this->observed();$fresh=$this->onIndependentOwnerConnection(fn()=>$this->observed());
        $this->retainLifecycle('retry_'.$kind,compact('before','after','fresh'));
        $this->assertNotSame($before,$after);$this->assertSame($after,$fresh);
    }
    public function test_lifecycle_existing_capture_is_read_only_after_release_but_new_or_conflicting_capture_refuses(): void
    {
        [$run]=$this->subject('capture');$captures=new \App\Infrastructure\Persistence\MarketData\RunInputCaptureRepository();
        $selection=['operation'=>'writer-boundary-control/v1'];$rows=[['declared_control'=>'owned-input']];
        $record=$captures->capture((int)$run->run_id,'INGEST_BARS','ancillary',$selection,$rows);
        (new EodRunRepository())->finalize($run,['lifecycle_state'=>'COMPLETED','terminal_status'=>'HELD','publishability_state'=>'NOT_READABLE']);
        $before=$this->observed();$writes=[];DB::listen(function($q)use(&$writes){if(preg_match('/^(insert|update|delete) /i',$q->sql))$writes[]=$q->sql;});
        $existing=$this->onIndependentOwnerConnection(fn()=>$captures->capture((int)$run->run_id,'INGEST_BARS','ancillary',$selection,$rows));
        $this->assertSame($record,$existing);$this->assertSame([],$writes);$this->assertSame($before,$this->observed());
        foreach(['conflict','new_slot']as $kind){$error=null;
            try{$captures->capture((int)$run->run_id,'INGEST_BARS','ancillary',$kind==='new_slot'?['operation'=>'different-slot/v1']:$selection,$kind==='conflict'?[['declared_control'=>'conflicting']]:$rows);}
            catch(RuntimeException $e){$error=$e->getMessage();}
            $after=$this->observed();$this->retainLifecycle('capture_'.$kind,compact('before','after','error'));
            $this->assertSame($before,$after);$this->assertStringContainsString($kind==='conflict'?'INPUT_CAPTURE_CONFLICT':'RUN_OWNER_', (string)$error);
        }
    }

    public function test_lifecycle_sealed_manifest_header_identities_are_persistently_immutable(): void
    {
        [$run,$pub,$world]=$this->fixture('candidate');
        $run=$world['pipeline']->completeSeal(new MarketDataStageInput($run->trade_date_requested,'api',$run->run_id,'SEAL',null,false,null,'full_publish'));
        $repo=new EodRunRepository();$violations=[];
        $fields=['seal_fingerprint'=>hash('sha256','forged-seal'),'config_version'=>'forged-seal-config',
            'source_name'=>'FORGED_SOURCE','source_provider'=>'FORGED_PROVIDER','promote_mode'=>'repair_candidate',
            'publish_target'=>'non_current','correction_id'=>9999,'supersedes_run_id'=>9999,'corpus_admission_decision_id'=>9999];
        foreach($fields as $field=>$value){
            $actor=\App\Models\EodRun::findOrFail($run->run_id);$this->assertNotSame((string)$actor->$field,(string)$value);
            $this->assertSame(0,DB::transactionLevel());$before=$this->observed();$error=null;
            try{$repo->updateTelemetry($actor,[$field=>$value]);}catch(RuntimeException $e){$error=$e->getMessage();}
            $after=$this->observed();$fresh=$this->onIndependentOwnerConnection(fn()=>$this->observed());
            $this->retainLifecycle('sealed_header_'.$field,compact('before','after','fresh','error'));
            $this->assertSame($after,$fresh,'Fresh connection disagrees with committed header state');
            if($before!==$after||strpos((string)$error,'SEALED_PUBLICATION_IMMUTABLE')===false)$violations[]=$field;
        }
        $this->assertSame([],$violations,'Sealed manifest header rewrite committed or lacked its immutable-boundary refusal');
    }

    public static function terminalFamilies(): array {return [['fail'],['hold']];}
    /** @dataProvider terminalFamilies */
    public function test_lifecycle_terminal_writer_cannot_save_dirty_sealed_content(string $kind): void
    {
        [$run,$pub,$world]=$this->fixture('candidate');
        $run=$world['pipeline']->completeSeal(new MarketDataStageInput($run->trade_date_requested,'api',$run->run_id,'SEAL',null,false,null,'full_publish'));
        $before=$this->observed();$actor=$run->fresh();$actor->bars_batch_hash=hash('sha256','dirty-sealed-terminal');$error=null;
        $this->assertSame(0,DB::transactionLevel());
        try{if($kind==='fail')(new EodRunRepository())->failStage($actor,'FINALIZE','RUN_LOCK_CONFLICT','Dirty terminal writer control');
            else (new EodRunRepository())->holdStage($actor,'FINALIZE','RUN_LOCK_CONFLICT','Dirty terminal writer control');}
        catch(RuntimeException $e){$error=$e->getMessage();}
        $after=$this->observed();$fresh=$this->onIndependentOwnerConnection(fn()=>$this->observed());
        $this->retainLifecycle('dirty_terminal_'.$kind,compact('before','after','fresh','error'));
        $this->assertSame($after,$fresh);$this->assertSame($before,$after,'Dirty terminal transition rewrote sealed content');
        $this->assertStringContainsString('SEALED_PUBLICATION_IMMUTABLE',(string)$error);
    }
    /** @dataProvider terminalFamilies */
    public function test_lifecycle_clean_terminal_writer_preserves_sealed_content_and_releases_owner(string $kind): void
    {
        [$run,$pub,$world]=$this->fixture('candidate');
        $run=$world['pipeline']->completeSeal(new MarketDataStageInput($run->trade_date_requested,'api',$run->run_id,'SEAL',null,false,null,'full_publish'));
        $before=$this->observed();
        if($kind==='fail')$result=(new EodRunRepository())->failStage($run,'FINALIZE','RUN_LOCK_CONFLICT','Clean terminal writer control');
        else $result=(new EodRunRepository())->holdStage($run,'FINALIZE','RUN_LOCK_CONFLICT','Clean terminal writer control');
        $after=$this->observed();$this->retainLifecycle('clean_terminal_'.$kind,compact('before','after'));
        foreach(['eod_publications','eod_bars_history','eod_indicators_history','eod_eligibility_history','eod_current_publication_pointer']as $table)$this->assertSame($before[$table],$after[$table]);
        $original=null;foreach($before['eod_runs']as $row)if((int)$row['run_id']===(int)$run->run_id)$original=$row;$this->assertNotNull($original);
        $persisted=(array)DB::table('eod_runs')->where('run_id',$run->run_id)->first();
        foreach(['bars_batch_hash','indicators_batch_hash','eligibility_batch_hash','seal_fingerprint','sealed_at','config_hash','config_version']as $field)$this->assertSame($original[$field],$persisted[$field]);
        $this->assertSame($kind==='fail'?'FAILED':'HELD',$result->terminal_status);
        $this->assertNotNull(DB::table('eod_run_owner_fences')->where('trade_date_requested',$run->trade_date_requested)->value('released_at'));
    }
    public static function isolatedFamilies(): array {return [['import'],['replay']];}
    /** @dataProvider isolatedFamilies */
    public function test_lifecycle_isolated_dirty_model_cannot_claim_seal_or_readability(string $kind): void
    {
        [$old]=$this->fixture('candidate');$repo=new EodRunRepository();$date=$old->trade_date_requested;
        $repo->finalize($old,['lifecycle_state'=>'COMPLETED','terminal_status'=>'HELD','publishability_state'=>'NOT_READABLE']);
        $outside=$this->observed();
        $work=function()use($kind,$repo,$date,$old){
            $run=$kind==='import'?$repo->getOrCreateOwningRun($date,'api','INGEST_BARS',$old->run_id,'import_only'):
                $repo->createAsKnownReplayRun($date,$old->knowledge_cutoff_at,'api');
            $actor=$run->fresh();$actor->publishability_state='READABLE';$actor->terminal_status='SUCCESS';$actor->sealed_at=\Carbon\Carbon::now()->toDateTimeString();
            $before=$this->observed();$error=null;
            try{$repo->touchStage($actor,'HASH',['notes'=>'Isolated dirty-state refusal control']);}catch(RuntimeException $e){$error=$e->getMessage();}
            $after=$this->observed();$this->retainLifecycle('isolated_dirty_'.$kind,compact('before','after','error'));
            if($kind==='import')$this->assertSame($after,$this->onIndependentOwnerConnection(fn()=>$this->observed()));
            $this->assertSame($before,$after,'Isolated dirty model claimed seal or readable state');$this->assertStringContainsString('RUN_OWNER_CONFLICT',(string)$error);
            // Clean authorized metadata still works within this genuinely acquired isolated scope.
            $clean=$repo->touchStage($run->fresh(),'HASH',['notes'=>'Authorized isolated metadata']);$this->assertSame('Authorized isolated metadata',$clean->notes);
            $this->assertNotSame('READABLE',$clean->publishability_state);$this->assertNull($clean->sealed_at);
        };
        if($kind==='replay'){\App\Infrastructure\Persistence\MarketData\ReplayIsolation::run($work);$this->assertSame($outside,$this->observed(),'Replay metadata escaped rollback-only scope');}
        else $work();
    }
    public function test_lifecycle_owned_outcome_reference_may_preserve_prior_current_without_rewriting_sealed_identity(): void
    {
        $world=\Tests\Support\R0025SyntheticV2World::build();$repo=new EodRunRepository();
        $prior=\App\Models\EodRun::findOrFail($world['run_id']);$date=$prior->trade_date_requested;
        $priorPub=(int)$world['publication_id'];$priorVersion=(int)$world['publication']->publication_version;
        $run=null;
        foreach(['INGEST_BARS'=>'completeIngest','COMPUTE_INDICATORS'=>'completeIndicators','BUILD_ELIGIBILITY'=>'completeEligibility','HASH'=>'completeHash','SEAL'=>'completeSeal']as $stage=>$method)
            $run=$world['pipeline']->$method(new MarketDataStageInput($date,'api',$run?$run->run_id:null,$stage,null,false,null,'full_publish'));
        $candidate=DB::table('eod_publications')->where('run_id',$run->run_id)->where('seal_state','SEALED')->first();
        $this->assertNotNull($candidate);$this->assertNotSame($priorPub,(int)$candidate->publication_id);
        $this->assertSame((int)$candidate->publication_id,(int)$run->publication_id);
        $reference=['publication_id'=>$priorPub,'publication_version'=>$priorVersion];$before=$this->observed();
        $foreign=$this->onIndependentOwnerConnection(function()use($repo,$run,$reference){try{$repo->updateTelemetry($run,$reference);return null;}catch(RuntimeException $e){return $e->getMessage();}});
        $this->assertStringContainsString('RUN_OWNER_', (string)$foreign);$this->assertSame($before,$this->observed());
        $error=null;try{$run=$repo->updateTelemetry($run,$reference);}catch(RuntimeException $e){$error=$e->getMessage();}
        $after=$this->observed();$fresh=$this->onIndependentOwnerConnection(fn()=>$this->observed());
        $this->retainLifecycle('outcome_reference',compact('before','after','fresh','error','reference','foreign'));
        $this->assertNull($error,'Run outcome reference is operational; changing it must not rewrite or be confused with sealed publication identity');
        $this->assertSame($after,$fresh);$this->assertSame($priorPub,(int)$run->publication_id);
        foreach($before as $table=>$rows)if($table!=='eod_runs')$this->assertSame($rows,$after[$table],'Outcome reference altered '.$table);
        $oldRow=null;foreach($before['eod_runs']as $row)if((int)$row['run_id']===(int)$run->run_id)$oldRow=$row;
        $newRow=(array)DB::table('eod_runs')->where('run_id',$run->run_id)->first();
        foreach($oldRow as $key=>$value)if(!in_array($key,['publication_id','publication_version','updated_at'],true))$this->assertSame($value,$newRow[$key],'Reference changed sealed run field '.$key);
        $error=null;try{$repo->updateTelemetry($run,['bars_batch_hash'=>str_repeat('f',64)]);}catch(RuntimeException $e){$error=$e->getMessage();}
        $this->assertStringContainsString('SEALED_PUBLICATION_IMMUTABLE',(string)$error);$this->assertSame($after,$this->observed());
        $run=$repo->finalize($run,['lifecycle_state'=>'COMPLETED','terminal_status'=>'HELD','publishability_state'=>'NOT_READABLE']);
        $this->assertSame('HELD',$run->terminal_status);
        $this->assertSame($priorPub,(int)(new \App\Infrastructure\Persistence\MarketData\EodPublicationRepository())->resolveCurrentReadablePublicationForTradeDate($date)->publication_id);
        $before=$this->observed();$error=null;try{$repo->updateTelemetry($run,['publication_id'=>$candidate->publication_id,'publication_version'=>$candidate->publication_version]);}catch(RuntimeException $e){$error=$e->getMessage();}
        $this->assertStringContainsString('RUN_OWNER_', (string)$error);$this->assertSame($before,$this->observed());
    }

}
