<?php

use App\Application\MarketData\DTOs\MarketDataStageInput;
use App\Infrastructure\Persistence\MarketData\EodArtifactRepository;
use App\Infrastructure\Persistence\MarketData\EodPublicationRepository;
use App\Infrastructure\Persistence\MarketData\EodRunRepository;
use App\Models\EodRun;
use Illuminate\Support\Facades\DB;
use Tests\Support\R0025SyntheticV2World;
require_once __DIR__.'/B19OwnerFenceMariaDbTest.php';

/** Real migrated worlds; no synthetic execution credential or date-owner row. */
class B19ArtifactWriterMariaDbTest extends B19OwnerFenceMariaDbTest
{
    protected function tearDown(): void { \Carbon\Carbon::setTestNow(); parent::tearDown(); }

    private function candidate(): array
    {
        $world=R0025SyntheticV2World::build(['run_workflow'=>function($pipeline,$date){$run=null;
            foreach(['INGEST_BARS'=>'completeIngest','COMPUTE_INDICATORS'=>'completeIndicators','BUILD_ELIGIBILITY'=>'completeEligibility','HASH'=>'completeHash']as $stage=>$method)
                $run=$pipeline->$method(new MarketDataStageInput($date,'api',$run?$run->run_id:null,$stage,null,false,null,'full_publish'));
            return $run;}]);
        return [EodRun::findOrFail($world['run_id']), (int)$world['publication_id'], $world];
    }

    private function state(): array
    {
        $out=[];
        foreach(DB::select('SHOW TABLES')as $entry){$table=array_values((array)$entry)[0];
            if(!preg_match('/^(eod_|md_)[a-z0-9_]+$/D',$table))continue;
            $rows=array_map(fn($r)=>(array)$r,DB::table($table)->get()->all());
            usort($rows,fn($a,$b)=>strcmp(json_encode($a),json_encode($b)));$out[$table]=$rows;}
        ksort($out);return $out;
    }

    private function retain(string $case,array $before,array $after,$error=null,array $extra=[]): void
    {
        $dir=getenv('B19_E049_STATE_DIR');if(!$dir)return;if(!is_dir($dir))mkdir($dir,0775,true);
        file_put_contents($dir.'/ART_'.$case.'.json',json_encode(compact('before','after','error','extra'),JSON_PRETTY_PRINT));
    }

    private function row(string $table,int $pub): array
    {
        $row=(array)DB::table($table)->where('publication_id',$pub)->first();
        $this->assertNotEmpty($row,'Actual pipeline fixture row required: '.$table);
        foreach(DB::select('SHOW COLUMNS FROM `'.$table.'`')as $col)if($col->Extra==='auto_increment')unset($row[$col->Field]);
        return $row;
    }

    /** Public entrypoints, including both live and history branches. */
    private function operations(EodRun $run,int $pub): array
    {
        $repo=new EodArtifactRepository();$d=(string)$run->trade_date_requested;$r=(int)$run->run_id;
        $bar=$this->row('eod_bars',$pub);$ind=$this->row('eod_indicators',$pub);$elig=$this->row('eod_eligibility',$pub);
        $bad=['trade_date'=>$d,'run_id'=>$r,'source'=>'api','invalid_reason_code'=>'BAR_TICKER_MAPPING_MISSING','created_at'=>'2026-03-25 00:00:00'];
        $ops=[];
        foreach([false,true]as $history){$branch=$history?'history':'live';
            $ops['bars_'.$branch]=fn($change=[])=>$repo->replaceBars($d,$pub,$r,[array_replace($bar,$change)],[],$history);
            $ops['partial_'.$branch]=fn($change=[])=>$repo->upsertBarsPartial($d,$pub,$r,[array_replace($bar,$change)],[],$history);
            $ops['indicators_'.$branch]=fn($change=[])=>$repo->replaceIndicators($d,$r,[array_replace($ind,$change)],$pub,$history);
            $ops['eligibility_'.$branch]=fn($change=[])=>$repo->replaceEligibility($d,$r,[array_replace($elig,$change)],$pub,$history);
            $ops['bars_invalid_'.$branch]=fn($change=[])=>$repo->replaceBars($d,$pub,$r,[$bar],[array_replace($bad,$change)],$history);
            $ops['partial_invalid_'.$branch]=fn($change=[])=>$repo->upsertBarsPartial($d,$pub,$r,[$bar],[array_replace($bad,$change)],$history);
        }
        $ops['invalid']=fn($change=[])=>$repo->persistInvalidBars($d,$r,[array_replace($bad,$change)]);
        $ops['scale']=fn($change=[])=>$repo->bindCandidateBarSourceScale($pub,$d,$bar['listing_id'],array_replace(['source_scale_state'=>$bar['source_scale_state']],$change));
        $ops['structure']=fn($change=[])=>$repo->bindCandidateEligibilityMarketStructure($pub,$d,$elig['listing_id'],array_replace(['market_structure_resolution_state'=>$elig['market_structure_resolution_state']],$change));
        $ops['scale_history']=$ops['scale'];$ops['structure_history']=$ops['structure'];
        $ops['snapshot']=fn($change=[])=>$repo->snapshotPublicationFromCurrentTables($d,$pub,$r);
        $ops['copy_current']=fn($change=[])=>$repo->ensureBarsHistoryFromCurrentTradeDate($d,$pub,$r);
        // The source is retained history; only the explicit target is writable.
        $ops['promote']=fn($change=[])=>$repo->promotePublicationHistoryToCurrent($d,$pub,$r);
        return $ops;
    }

    public function test_artifact_payload_cannot_redirect_an_authenticated_writer(): void
    {
        [$run,$pub]=$this->candidate();
        [$other,$otherPub]=$this->onIndependentOwnerConnection(function(){
            $actor=(new EodRunRepository())->getOrCreateOwningRun('2026-03-24','api','INGEST_BARS',null,'full_publish');
            return [$actor,(new EodPublicationRepository())->getOrCreateCandidatePublication($actor)];
        });
        $values=['trade_date'=>'2026-03-24','run_id'=>(int)$other->run_id,'publication_id'=>(int)$otherPub->publication_id];
        $ops=$this->operations($run,$pub);$violations=[];
        foreach($ops as $name=>$op){
            if(in_array($name,['snapshot','copy_current','promote','scale_history','structure_history'],true))continue;
            // Partial canonical upserts explicitly construct target identities rather than inserting raw ones.
            if(strpos($name,'partial_')===0&&strpos($name,'invalid')===false)continue;
            foreach($values as $field=>$value){
                if(($name==='invalid'||strpos($name,'_invalid_')!==false)&&$field!=='run_id')continue;
                foreach(in_array($name,['scale','structure'],true)?['live','history']:['direct']as $branch){
                    DB::beginTransaction();
                    try{
                        if($branch==='live')foreach(['bars','indicators','eligibility']as $a)DB::table('eod_'.$a.'_history')->where('publication_id',$pub)->delete();
                        if($branch==='history')(new EodArtifactRepository())->snapshotPublicationFromCurrentTables($run->trade_date_requested,$pub,$run->run_id);
                        $before=$this->state();$error=null;
                        try{$op([$field=>$value]);}catch(RuntimeException $e){$error=$e->getMessage();}
                        $after=$this->state();$case=$name.'_'.$branch.'_'.$field;
                        $this->retain('payload_'.$case,$before,$after,$error);
                        if($before!==$after||strpos((string)$error,'ARTIFACT_WRITE_SCOPE_INVALID')===false)$violations[]=$case;
                    }finally{DB::rollBack();}
                }
            }
        }
        $this->assertSame([],$violations,'Artifact payload escaped authenticated target scope');
    }

    public function test_artifact_each_public_writer_refuses_foreign_connection_date_target_and_released_actor(): void
    {
        [$run,$pub]=$this->candidate();$repo=new EodArtifactRepository();$d=$run->trade_date_requested;
        $repo->snapshotPublicationFromCurrentTables($d,$pub,$run->run_id);
        $ops=$this->operations($run,$pub);
        foreach(['independent','wrong_date','wrong_target','sealed','released']as $kind){
            if($kind==='released'){
                (new EodRunRepository())->finalize($run,['lifecycle_state'=>'COMPLETED','terminal_status'=>'HELD','publishability_state'=>'NOT_READABLE']);
                $this->onIndependentOwnerConnection(function()use($run){(new EodRunRepository())->getOrCreateOwningRun($run->trade_date_requested,'api','INGEST_BARS',$run->run_id,'full_publish');});
            }
            foreach($ops as $name=>$op){
                if(in_array($kind,['wrong_target','sealed'],true)&&$name==='invalid')continue;
                if($kind==='sealed'&&$name==='promote')continue; // Sealed history is the legitimate promotion input.
                DB::beginTransaction();
                try{
                    if($kind==='sealed')DB::table('eod_publications')->where('publication_id',$pub)->update(['seal_state'=>'SEALED']);
                    if($kind==='wrong_target')DB::table('eod_publications')->where('publication_id',$pub)->update(['trade_date'=>'2026-03-24']);
                    $action=$op;
                    if($kind==='wrong_date'){$wrong=clone $run;$wrong->trade_date_requested='2026-03-24';$action=$this->operations($wrong,$pub)[$name];}
                    $before=$this->state();$invoke=function()use($action){try{$action();return null;}catch(RuntimeException $e){return $e->getMessage();}};
                    $error=$kind==='independent'?$this->onIndependentOwnerConnection($invoke):$invoke();$after=$this->state();
                    $this->retain('refusal_'.$kind.'_'.$name,$before,$after,$error);
                    $this->assertSame($before,$after,'Artifact unauthorized persisted '.$kind.' '.$name);
                    $this->assertStringContainsString($kind==='sealed'?'SEALED_PUBLICATION_IMMUTABLE':'RUN_OWNER_', (string)$error,'Artifact refusal must be contractual: '.$kind.' '.$name);
                }finally{DB::rollBack();}
            }
        }
    }

    private function prepareMutation(string $name,EodRun $run,int $pub): void
    {
        if(in_array($name,['copy_current','snapshot'],true)){
            foreach($name==='snapshot'?['bars','indicators','eligibility']:['bars']as $a)
                DB::table('eod_'.$a.'_history')->where('publication_id',$pub)->delete();
        }
        if($name==='promote'){
            (new EodArtifactRepository())->snapshotPublicationFromCurrentTables($run->trade_date_requested,$pub,$run->run_id);
            foreach(['bars','indicators','eligibility']as $a)DB::table('eod_'.$a)->where('publication_id',$pub)->delete();
        }
        // Restore a previously computed actual fixture fact, rather than invent a source fact.
        if(in_array($name,['scale_history','structure_history'],true))
            (new EodArtifactRepository())->snapshotPublicationFromCurrentTables($run->trade_date_requested,$pub,$run->run_id);
        $binding=explode('_',$name)[0];
        if(in_array($binding,['scale','structure'],true))foreach(['','_history']as $suffix)
            DB::table('eod_'.($binding==='scale'?'bars':'eligibility').$suffix)->where('publication_id',$pub)
                ->update([$binding==='scale'?'source_scale_state':'market_structure_resolution_state'=>null]);
    }

    public function test_artifact_each_writer_holds_date_fence_rolls_back_and_permits_owner_retry(): void
    {
        [$run,$pub]=$this->candidate();$ops=$this->operations($run,$pub);
        foreach($ops as $name=>$op){
            DB::beginTransaction();
            try{
                $this->prepareMutation($name,$run,$pub);
                $before=$this->state();$armed=true;$probe=null;$hit=null;
                DB::listen(function($q)use(&$armed,&$probe,&$hit,$run){
                    if(!$armed||!preg_match('/^(?:insert into|delete from|update) `eod_(?:bars|indicators|eligibility|invalid_bars)(?:_history)?`/i',$q->sql))return;
                    $armed=false;$hit=$q->sql;
                    $probe=$this->onIndependentOwnerConnection(function()use($run){
                        DB::statement('SET SESSION innodb_lock_wait_timeout=1');
                        try{return DB::transaction(function()use($run){
                            DB::table('eod_run_owner_fences')->where('trade_date_requested',$run->trade_date_requested)->lockForUpdate()->first();return 'FENCE_LOCK_ACQUIRED';
                        });}catch(\Illuminate\Database\QueryException $e){return $e->getMessage();}
                    });
                    throw new RuntimeException('ARTIFACT_AFTER_WRITE');
                });
                // Make canonical partial-upsert branches perform an actual bar UPDATE.
                $change=strpos($name,'partial_')===0&&strpos($name,'invalid')===false?['close'=>(float)$this->row('eod_bars',$pub)['close']+1]:[];
                try{$op($change);$this->fail('Artifact mutation fault not reached '.$name);}
                catch(RuntimeException $e){$this->assertSame('ARTIFACT_AFTER_WRITE',$e->getMessage(),$name);}finally{$armed=false;}
                $after=$this->state();$this->retain('atomic_'.$name,$before,$after,$probe,['actual_mutation'=>$hit]);
                $this->assertSame($before,$after,'Artifact compound write survived rollback '.$name);
                $this->assertStringContainsString('1205',(string)$probe,'Date-fence-only contention missing '.$name);
                $this->assertStringContainsString('eod_run_owner_fences',(string)$probe);
                $op($change);$after=$this->state();
                if(!in_array($name,['bars_live','bars_history','indicators_live','indicators_history','eligibility_live','eligibility_history'],true))
                    $this->assertNotSame($before,$after,'Authorized artifact operation did not persist '.$name);
                foreach(['bars','indicators','eligibility']as $a)foreach(['','_history']as $suffix)
                    foreach(DB::table('eod_'.$a.$suffix)->where('publication_id',$pub)->get()as $row){
                        $this->assertSame((int)$run->run_id,(int)$row->run_id);$this->assertSame($run->trade_date_requested,$row->trade_date);
                    }
                $this->assertSame(0,DB::table('eod_current_publication_pointer')->count());
                $this->assertSame('UNSEALED',DB::table('eod_publications')->where('publication_id',$pub)->value('seal_state'));
                $this->retain('owner_retry_'.$name,$before,$after);
            }finally{DB::rollBack();}
        }
    }

    public function test_artifact_history_copy_preserves_sealed_source_and_authenticates_target(): void
    {
        $world=R0025SyntheticV2World::build();$old=EodRun::findOrFail($world['run_id']);$source=(int)$world['publication_id'];
        $run=(new EodRunRepository())->acquireLinkedRecoveryOwner($old->trade_date_requested,'api',$old->run_id);
        $pub=(new EodPublicationRepository())->getOrCreateCandidatePublication($run);$target=(int)$pub->publication_id;
        $repo=new EodArtifactRepository();$op=fn()=>$repo->replaceBarsHistoryFromPublication($run->trade_date_requested,$source,$target,$run->run_id);
        $before=$this->state();$error=$this->onIndependentOwnerConnection(function()use($op){try{$op();return null;}catch(RuntimeException $e){return $e->getMessage();}});
        $after=$this->state();$this->retain('copy_history_independent',$before,$after,$error);
        $this->assertSame($before,$after);$this->assertStringContainsString('RUN_OWNER_', (string)$error);
        $sourceRows=DB::table('eod_bars_history')->where('publication_id',$source)->orderBy('ticker_id')->get()->map(fn($r)=>(array)$r)->all();
        $pointer=DB::table('eod_current_publication_pointer')->get()->map(fn($r)=>(array)$r)->all();
        $op();$after=$this->state();$this->retain('copy_history_owner',$before,$after);
        $this->assertSame($sourceRows,DB::table('eod_bars_history')->where('publication_id',$source)->orderBy('ticker_id')->get()->map(fn($r)=>(array)$r)->all());
        $this->assertSame($pointer,DB::table('eod_current_publication_pointer')->get()->map(fn($r)=>(array)$r)->all());
        $rows=DB::table('eod_bars_history')->where('publication_id',$target)->get();$this->assertNotEmpty($rows);
        foreach($rows as $row){$this->assertSame((int)$run->run_id,(int)$row->run_id);$this->assertSame($run->trade_date_requested,$row->trade_date);}
        $this->assertGreaterThan(0,DB::table('md_run_input_captures')->where('run_id',$run->run_id)->count());
        // A failed compound re-copy must also roll back captures and artifact rows.
        $before=$this->state();$armed=true;
        DB::listen(function($q)use(&$armed){if($armed&&stripos($q->sql,'delete from `eod_bars_history`')===0){$armed=false;throw new RuntimeException('COPY_HISTORY_AFTER_DELETE');}});
        try{$op();$this->fail('Copy fault not reached');}catch(RuntimeException $e){$this->assertSame('COPY_HISTORY_AFTER_DELETE',$e->getMessage());}finally{$armed=false;}
        $this->retain('copy_history_rollback',$before,$this->state());$this->assertSame($before,$this->state());
        (new EodRunRepository())->finalize($run,['lifecycle_state'=>'COMPLETED','terminal_status'=>'HELD','publishability_state'=>'NOT_READABLE']);
        (new EodRunRepository())->acquireLinkedRecoveryOwner($run->trade_date_requested,'api',$run->run_id);
        $before=$this->state();try{$op();$error=null;}catch(RuntimeException $e){$error=$e->getMessage();}
        $this->retain('copy_history_released',$before,$this->state(),$error);$this->assertSame($before,$this->state());$this->assertStringContainsString('RUN_OWNER_', (string)$error);
    }
}
