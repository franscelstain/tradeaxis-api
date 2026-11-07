<?php

use App\Infrastructure\Persistence\MarketData\EodRunRepository;
use App\Models\EodRun;
use Illuminate\Support\Facades\DB;
use Tests\Support\R0025SyntheticV2World;
require_once __DIR__.'/B19ArtifactWriterMariaDbTest.php';

/** Acquisition is trusted issuance, not permission to supply persisted run facts. */
class B19PromoteAcquisitionBoundaryMariaDbTest extends B19ArtifactWriterMariaDbTest
{
    private function promoteState(): array
    {
        $m=(new ReflectionClass(B19ArtifactWriterMariaDbTest::class))->getMethod('state');$m->setAccessible(true);return $m->invoke($this);
    }
    private function savePromote(string $name,array $facts): void
    {
        $dir=getenv('B19_E049_STATE_DIR');if(!$dir)return;if(!is_dir($dir))mkdir($dir,0775,true);
        file_put_contents($dir.'/PROMOTE_'.$name.'.json',json_encode($facts,JSON_PRETTY_PRINT));
    }
    public static function forbiddenPromoteFacts(): array
    {
        return [['readability'],['pointer'],['replay_scope'],['run_identity']];
    }
    /** @dataProvider forbiddenPromoteFacts */
    public function test_promote_constructor_refuses_forged_run_facts(string $case): void
    {
        $world=R0025SyntheticV2World::build();$seed=EodRun::findOrFail($world['run_id']);
        $cases=[
            'readability'=>['terminal_status'=>'SUCCESS','publishability_state'=>'READABLE','lifecycle_state'=>'RUNNING'],
            'pointer'=>['is_current_publication'=>1,'publication_id'=>$seed->publication_id,'publication_version'=>$seed->publication_version],
            'replay_scope'=>['request_mode'=>'replay_verify'],
            'run_identity'=>['run_id'=>(int)$seed->run_id+100000],
        ];
        $before=$this->promoteState();$error=null;$created=null;
        try{$created=(new EodRunRepository())->createPromoteRunFromSeed($seed,'PUBLISH_BARS',$cases[$case]);}
        catch(\RuntimeException|\LogicException $e){$error=$e->getMessage();}
        $after=$this->promoteState();$fresh=$this->onIndependentOwnerConnection(fn()=>$this->promoteState());
        $this->savePromote('forged_'.$case,compact('before','after','fresh','error')+['created_run_id'=>$created?$created->run_id:null]);
        $this->assertSame($before,$after,'PROMOTE_ACQUISITION_FACT_INJECTION persisted '.$case);
        $this->assertSame($before,$fresh);$this->assertNotNull($error,'Acquisition must explicitly refuse forged '.$case);
        $this->assertMatchesRegularExpression('/PROMOTE_(OVERRIDE|SCOPE)_INVALID/',(string)$error);
    }
    public function test_promote_seed_facts_are_reloaded_not_copied_from_dirty_model(): void
    {
        $world=R0025SyntheticV2World::build();$seed=EodRun::findOrFail($world['run_id']);
        $actual=(array)DB::table('eod_runs')->where('run_id',$seed->run_id)->first();
        $seed->source_name='CALLER_FABRICATED_PROVIDER';$seed->source_file_hash=str_repeat('a',64);
        $seed->trade_date_requested='2026-03-24';
        $before=$this->promoteState();$created=(new EodRunRepository())->createPromoteRunFromSeed($seed,'PUBLISH_BARS');
        $after=$this->promoteState();$fresh=$this->onIndependentOwnerConnection(fn()=>$this->promoteState());
        $record=(array)DB::table('eod_runs')->where('run_id',$created->run_id)->first();
        $this->savePromote('dirty_seed',compact('before','after','fresh','record','actual'));
        $this->assertSame($actual['source_name'],$record['source_name'],'PROMOTE_SEED_PROVENANCE_FABRICATED source_name');
        $this->assertSame($actual['source_file_hash'],$record['source_file_hash'],'PROMOTE_SEED_PROVENANCE_FABRICATED source_file_hash');
        $this->assertSame($actual['trade_date_requested'],$record['trade_date_requested'],'Dirty model redirected acquisition date');
        $this->assertSame($actual,(array)DB::table('eod_runs')->where('run_id',$seed->run_id)->first(),'Historical seed changed');
        $this->assertSame($after,$fresh);$this->assertSame('NOT_READABLE',$record['publishability_state']);
        $this->assertSame(0,(int)$record['is_current_publication']);
        (new EodRunRepository())->touchStage($created,'PUBLISH_BARS',['notes'=>'Genuine new promote execution credential']);
    }
    public function test_promote_actual_compound_writes_lock_seed_and_date_and_roll_back(): void
    {
        $world=R0025SyntheticV2World::build();$seed=EodRun::findOrFail($world['run_id']);$date=(string)$seed->trade_date_requested;
        $repo=new EodRunRepository();$before=$this->promoteState();
        foreach(['seed_read','run','credential','event','capture']as $target){
            $gate=(object)['armed'=>true];$hit=null;$probes=[];
            DB::listen(function($q)use($gate,$target,$date,$seed,&$hit,&$probes){
                if(!$gate->armed)return;
                $match=($target==='seed_read'&&stripos($q->sql,'select * from `eod_runs` where `run_id` =')===0)
                    ||($target==='run'&&stripos($q->sql,'insert into `eod_runs`')===0)
                    ||($target==='credential'&&stripos($q->sql,'update `eod_run_owner_fences` set `execution_id`')===0)
                    ||($target==='event'&&stripos($q->sql,'insert into `eod_run_events`')===0&&in_array('RUN_CREATED',$q->bindings,true))
                    ||($target==='capture'&&stripos($q->sql,'insert into `md_run_input_captures`')===0);
                if(!$match)return;$gate->armed=false;$hit=$q->sql;
                foreach(['date','seed']as $lock)$probes[$lock]=$this->onIndependentOwnerConnection(function()use($lock,$seed,$date){
                    DB::statement('SET SESSION innodb_lock_wait_timeout=1');
                    try{return DB::transaction(function()use($lock,$seed,$date){
                        if($lock==='date')DB::table('eod_run_owner_fences')->where('trade_date_requested',$date)->lockForUpdate()->first();
                        else DB::table('eod_runs')->where('run_id',$seed->run_id)->lockForUpdate()->first();
                        return 'ACQUIRED_'.$lock;
                    });}catch(\Illuminate\Database\QueryException $e){return $e->getMessage();}
                });
                throw new RuntimeException('PROMOTE_AFTER_ACTUAL_BOUNDARY');
            });
            $error=null;try{$repo->createPromoteRunFromSeed($seed,'PUBLISH_BARS');}catch(RuntimeException $e){$error=$e->getMessage();}finally{$gate->armed=false;}
            $after=$this->promoteState();$fresh=$this->onIndependentOwnerConnection(fn()=>$this->promoteState());
            $this->savePromote('atomic_'.$target,compact('before','after','fresh','probes','hit','error'));
            $this->assertSame('PROMOTE_AFTER_ACTUAL_BOUNDARY',$error);$this->assertNotNull($hit);
            foreach(['date','seed']as $lock)$this->assertStringContainsString('1205',(string)($probes[$lock]??null),'PROMOTE_ACQUISITION_LOCK_LOST '.$lock);
            $this->assertSame($before,$after,'PROMOTE_PARTIAL_COMMIT '.$target);$this->assertSame($before,$fresh);
        }
        $created=$repo->createPromoteRunFromSeed($seed,'PUBLISH_BARS');$repo->touchStage($created,'PUBLISH_BARS');
        $this->assertSame('RUNNING',$created->fresh()->lifecycle_state,'Valid retry must remain executable');
    }
    public function test_promote_legitimate_intent_and_existing_publication_modes_keep_prior_history(): void
    {
        $world=R0025SyntheticV2World::build();$seed=EodRun::findOrFail($world['run_id']);$repo=new EodRunRepository();$before=$this->promoteState();$ids=[];
        foreach(['promote','full_publish','correction','corpus_reconstruction','repair_candidate']as $mode){
            $run=$repo->createPromoteRunFromSeed($seed,'PUBLISH_BARS',['request_mode'=>$mode,'notes'=>'Explicit legitimate intent '.$mode,
                'correction_id'=>null,'promote_mode'=>'full_publish','publish_target'=>'current_replace']);$ids[]=$run->run_id;
            $row=(array)DB::table('eod_runs')->where('run_id',$run->run_id)->first();
            $this->assertSame($mode,$row['request_mode']);$this->assertSame('PENDING',$row['lifecycle_state']);$this->assertNull($row['terminal_status']);
            $this->assertSame('NOT_READABLE',$row['publishability_state']);$this->assertSame(0,(int)$row['is_current_publication']);$this->assertNull($row['publication_id']);
            $this->assertNotEmpty($row['knowledge_cutoff_at']);$this->assertNotEmpty($row['config_hash']);
            $this->assertSame(1,DB::table('md_run_input_captures')->where('run_id',$run->run_id)->count());
            $ownerState=$this->promoteState();$error=$this->onIndependentOwnerConnection(function()use($repo,$run){try{$repo->touchStage($run,'PUBLISH_BARS');return null;}catch(RuntimeException $e){return $e->getMessage();}});
            $this->assertStringContainsString('RUN_OWNER_CONFLICT',(string)$error);$this->assertSame($ownerState,$this->promoteState());
            $repo->touchStage($run,'PUBLISH_BARS');
            $repo->finalize($run,['lifecycle_state'=>'COMPLETED','terminal_status'=>'HELD','publishability_state'=>'NOT_READABLE']);
            $this->assertNotNull(DB::table('eod_run_owner_fences')->where('trade_date_requested',$seed->trade_date_requested)->value('released_at'));
        }
        $after=$this->promoteState();$fresh=$this->onIndependentOwnerConnection(fn()=>$this->promoteState());
        foreach(['eod_publications','eod_current_publication_pointer','eod_bars','eod_bars_history','eod_indicators_history','eod_eligibility_history']as $table)$this->assertSame($before[$table],$after[$table]);
        $this->assertSame($after,$fresh);$this->assertCount(5,array_unique($ids));
        $this->savePromote('legitimate_modes',compact('before','after','fresh','ids'));
    }
}
