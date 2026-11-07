<?php

use App\Infrastructure\Persistence\MarketData\EodPublicationRepository;
use App\Infrastructure\Persistence\MarketData\EodArtifactRepository;
use App\Infrastructure\Persistence\MarketData\EodRunRepository;
use App\Application\MarketData\Services\FinalizeDecisionService;
use Illuminate\Support\Facades\DB;
require_once __DIR__.'/B19ArtifactWriterMariaDbTest.php';

class B19PublicationLifecycleBoundaryMariaDbTest extends B19ArtifactWriterMariaDbTest
{
    public static function families(): array {return [['seal'],['promote']];}
    private function observed(): array
    {$m=(new ReflectionClass(B19ArtifactWriterMariaDbTest::class))->getMethod('state');$m->setAccessible(true);return $m->invoke($this);}
    private function ready(string $kind)
    {
        $m=(new ReflectionClass(B19ArtifactWriterMariaDbTest::class))->getMethod('candidate');$m->setAccessible(true);[$run,$pub]=$m->invoke($this);
        // Same public snapshot/manifest sequence as completeSeal, not a simulated SEALED row.
        (new EodArtifactRepository())->snapshotPublicationFromCurrentTables($run->trade_date_requested,$pub,$run->run_id);
        $repo=new EodPublicationRepository();$repo->prepareCandidateManifestForSeal($run,$pub);
        if($kind==='promote'){
            $publication=$repo->sealCandidatePublication($run,'system','Native publication writer proof');
            $run=(new EodRunRepository())->markSealed($run->fresh(),'system','Native publication writer proof');
            $cutoff=\Carbon\Carbon::now()->greaterThanOrEqualTo(\Carbon\Carbon::parse($run->trade_date_requested.' '.config('market_data.platform.cutoff_time'),config('market_data.platform.timezone')));
            $decision=(new FinalizeDecisionService())->evaluate($cutoff,!empty($run->sealed_at),$publication->seal_state,[
                'coverage_gate_status'=>$run->coverage_gate_state,'coverage_ratio'=>$run->coverage_ratio,
                'coverage_threshold_value'=>$run->coverage_min_threshold,'coverage_threshold_mode'=>$run->coverage_threshold_mode,
                'coverage_contract_version'=>$run->coverage_contract_version,'coverage_universe_basis'=>$run->coverage_universe_basis,
                'expected_universe_count'=>$run->coverage_expected_count,'available_eod_count'=>$run->coverage_available_count,
                'missing_eod_count'=>$run->coverage_missing_count,
            ],null,['source_mode'=>$run->source,'quality_gate_state'=>$run->quality_gate_state,'bars_rows_written'=>$run->bars_rows_written]);
            $this->assertTrue($decision['promotion_allowed'],'Actual complete candidate must satisfy native finalize decision');
            $run=(new EodRunRepository())->updateTelemetry($run,['terminal_status'=>$decision['terminal_status'],
                'publishability_state'=>$decision['publishability_state'],'quality_gate_state'=>$decision['quality_gate_state']]);
        }
        return [$run,$pub];
    }
    private function act(string $kind,$run)
    {return $kind==='seal'?(new EodPublicationRepository())->sealCandidatePublication($run,'system','Direct owned seal proof'):(new EodPublicationRepository())->promoteCandidateToCurrent($run);}
    private function retainLifecycle(string $name,array $data): void
    {$dir=getenv('B19_E049_STATE_DIR');if(!$dir)return;if(!is_dir($dir))mkdir($dir,0775,true);file_put_contents($dir.'/PUB_LIFE_'.$name.'.json',json_encode($data,JSON_PRETTY_PRINT));}

    /** @dataProvider families */
    public function test_publication_lifecycle_direct_owner_refusal_scope_and_sealed_boundary(string $kind): void
    {
        [$run,$pub]=$this->ready($kind);$repo=new EodRunRepository();
        foreach(['foreign','wrong_date','wrong_run','wrong_mode']as $control){
            $before=$this->observed();$actor=clone $run;$error=null;
            if($control==='wrong_date')$actor->trade_date_requested='2026-03-25';
            elseif($control==='wrong_run')$actor->run_id=(int)$actor->run_id+999;
            elseif($control==='wrong_mode')$actor->request_mode='import_only';
            try{if($control==='foreign')$this->onIndependentOwnerConnection(fn()=>$this->act($kind,$actor));else $this->act($kind,$actor);}catch(RuntimeException $e){$error=$e->getMessage();}
            $after=$this->observed();$this->retainLifecycle($kind.'_'.$control,compact('before','after','error'));
            $this->assertSame($before,$after,'Publication lifecycle unauthorized write');$this->assertStringContainsString('RUN_OWNER_',(string)$error);
        }
        $this->assertSame(0,DB::transactionLevel());$result=$this->act($kind,$run);$this->assertSame('SEALED',$result->seal_state);
        if($kind==='promote')$this->assertSame($pub,(int)(new EodPublicationRepository())->resolveCurrentReadablePublicationForTradeDate($run->trade_date_requested)->publication_id);
        else{
            $before=$this->observed();try{$this->act($kind,$run);$this->fail('Repeated seal changed immutable publication');}catch(RuntimeException $e){$this->assertSame('DATASET_HASH_MISSING: Cannot seal publication before all candidate hashes exist.',$e->getMessage());}$this->assertSame($before,$this->observed());
        }
        $run=$repo->finalize($run->fresh(),['lifecycle_state'=>'COMPLETED','terminal_status'=>$kind==='promote'?'SUCCESS':'HELD','publishability_state'=>$kind==='promote'?'READABLE':'NOT_READABLE']);
        $this->onIndependentOwnerConnection(fn()=>(new EodRunRepository())->acquireLinkedRecoveryOwner($run->trade_date_requested,'api',$run->run_id));
        $before=$this->observed();$error=null;try{$this->act($kind,$run);}catch(RuntimeException $e){$error=$e->getMessage();}
        $after=$this->observed();$this->retainLifecycle($kind.'_released',compact('before','after','error'));$this->assertSame($before,$after);$this->assertStringContainsString('RUN_OWNER_',(string)$error);
    }

    /** @dataProvider families */
    public function test_publication_lifecycle_no_outer_transaction_actual_fence_compound_rollback_and_retry(string $kind): void
    {
        [$run,$pub]=$this->ready($kind);$date=$run->trade_date_requested;
        $faults=$kind==='seal'?['publication','run']:['publication','pointer','run'];
        foreach($faults as $fault){
            $before=$this->observed();$gate=(object)['armed'=>true];$probe=null;$hit=null;
            DB::listen(function($q)use($gate,&$probe,&$hit,$date,$kind,$fault){
                $pattern=$kind==='seal'?($fault==='publication'?'/^update `eod_publications` set `seal_state`/i':'/^update `eod_runs` set `publication_semantic_profile`/i'):
                    ($fault==='publication'?'/^update `eod_publications` set `is_current`/i':($fault==='pointer'?'/^(insert into|update) `eod_current_publication_pointer`/i':'/^update `eod_runs` set `publication_id`/i'));
                if(!$gate->armed||!preg_match($pattern,$q->sql))return;$gate->armed=false;$hit=$q->sql;
                $probe=$this->onIndependentOwnerConnection(function()use($date){DB::statement('SET SESSION innodb_lock_wait_timeout=1');try{return DB::transaction(function()use($date){DB::table('eod_run_owner_fences')->where('trade_date_requested',$date)->lockForUpdate()->first();return 'UNLOCKED';});}catch(\Illuminate\Database\QueryException $e){return $e->getMessage();}});
                throw new RuntimeException('PUBLICATION_LIFECYCLE_AFTER_WRITE');
            });
            $this->assertSame(0,DB::transactionLevel());
            try{$this->act($kind,$run);$this->fail('Publication lifecycle fault not reached');}catch(RuntimeException $e){$this->assertSame('PUBLICATION_LIFECYCLE_AFTER_WRITE',$e->getMessage());}finally{$gate->armed=false;}
            $after=$this->observed();$fresh=$this->onIndependentOwnerConnection(fn()=>$this->observed());$this->retainLifecycle($kind.'_atomic_'.$fault,compact('before','after','fresh','probe','hit'));
            $expected=$kind==='seal'?($fault==='publication'?'update `eod_publications` set `seal_state`':'update `eod_runs` set `publication_semantic_profile`'):($fault==='publication'?'update `eod_publications` set `is_current`':($fault==='pointer'?'`eod_current_publication_pointer`':'update `eod_runs` set `publication_id`'));$this->assertStringContainsString($expected,(string)$hit,'Publication fault targeted a different writer');$this->assertSame($before,$after,'Partial publication lifecycle survived');$this->assertSame($before,$fresh);$this->assertStringContainsString('1205',(string)$probe,'Publication lifecycle date fence absent');
        }
        $result=$this->act($kind,$run);$after=$this->observed();$fresh=$this->onIndependentOwnerConnection(fn()=>$this->observed());$this->retainLifecycle($kind.'_retry',compact('before','after','fresh'));
        $this->assertNotSame($before,$after);$this->assertSame($after,$fresh);$this->assertSame('SEALED',$result->seal_state);
    }
}
