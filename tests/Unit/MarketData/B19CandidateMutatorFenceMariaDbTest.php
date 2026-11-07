<?php
use App\Infrastructure\Persistence\MarketData\EodPublicationRepository;
use App\Infrastructure\Persistence\MarketData\EodRunRepository;
use App\Models\EodRun;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Tests\Support\R0025SyntheticV2World;
require_once __DIR__.'/B19OwnerFenceMariaDbTest.php';

class B19CandidateMutatorFenceMariaDbTest extends B19OwnerFenceMariaDbTest
{
    public static function families(): array { return [['provenance'], ['analytical'], ['manifest'], ['discard']]; }
    protected function tearDown(): void { Carbon::setTestNow(); parent::tearDown(); }
    private function candidate(bool $sealed = false): array
    {
        $w = R0025SyntheticV2World::build(['run_workflow' => function ($pipeline, $date) use ($sealed) {
            $run = null;
            $stages = ['INGEST_BARS'=>'completeIngest','COMPUTE_INDICATORS'=>'completeIndicators','BUILD_ELIGIBILITY'=>'completeEligibility','HASH'=>'completeHash'];
            if ($sealed) $stages['SEAL']='completeSeal';
            foreach ($stages as $stage=>$method) $run=$pipeline->$method(new \App\Application\MarketData\DTOs\MarketDataStageInput($date,'api',$run?$run->run_id:null,$stage,null,false,null,'full_publish'));
            return $run;
        }]);
        $run=EodRun::findOrFail($w['run_id']);$p=DB::table('eod_publications')->where('publication_id',$w['publication_id'])->first();
        $this->assertSame('PASS',$run->coverage_gate_state);$this->assertSame($sealed?'SEALED':'UNSEALED',$p->seal_state);
        if (!$sealed) (new \App\Infrastructure\Persistence\MarketData\EodArtifactRepository())->snapshotPublicationFromCurrentTables($run->trade_date_requested,$p->publication_id,$run->run_id);
        Carbon::setTestNow(Carbon::now()->addSecond());
        return [$run,$p];
    }
    private function operation(string $family,EodRun $run,$p,?EodRun $actor): void
    {
        $repo=new EodPublicationRepository();
        if($family==='provenance') $repo->bindCandidateAcquisitionProvenance($p->publication_id,$run->run_id,$p->observation_manifest_hash,$p->config_snapshot_id,$p->semantic_observation_manifest_hash,$actor);
        elseif($family==='analytical') $repo->bindCandidateAnalyticalProduct($p->publication_id,$run->run_id,$p->price_product_code,$p->price_product_version,$p->factor_set_hash,$p->factor_set_id,$actor);
        elseif($family==='manifest') $repo->prepareCandidateManifestForSeal($actor ?: $run,$p->publication_id);
        else $repo->discardCandidatePublication($p->publication_id,$actor);
    }
    private function state(): array
    {
        $state=[];
        foreach(DB::select('SHOW TABLES') as $entry){$table=(string)array_values((array)$entry)[0];if(!preg_match('/^(eod_|md_)[a-z0-9_]+$/D',$table))continue;
            $rows=array_map(fn($x)=>(array)$x,DB::table($table)->get()->all());usort($rows,fn($a,$b)=>strcmp(json_encode($a),json_encode($b)));$state[$table]=$rows;}
        ksort($state);return $state;
    }
    private function retain(string $family,string $kind,array $before,array $after,?string $error): void
    {
        $dir=getenv('B19_E049_STATE_DIR');if(!$dir)return;if(!is_dir($dir))mkdir($dir,0775,true);
        file_put_contents($dir.'/F01_'.$family.'_'.$kind.'.json',json_encode(['before'=>$before,'after'=>$after,'error'=>$error],JSON_PRETTY_PRINT));
    }
    /** @dataProvider families */
    public function test_candidate_mutator_rejects_independent_incumbent_model(string $family): void
    {
        [$run,$p]=$this->candidate();
        foreach([$run,null] as $actor){$before=$this->state();$error=$this->onIndependentOwnerConnection(function()use($family,$run,$p,$actor){try{$this->operation($family,$run,$p,$actor);return null;}catch(RuntimeException $e){return $e->getMessage();}});
            $kind=$actor?'incumbent_model':($family==='manifest'?'incumbent_model_fallback':'missing_actor');
            $after=$this->state();$this->retain($family,'independent_'.$kind,$before,$after,$error);$this->assertSame($before,$after,'Independent caller persisted '.$family);$this->assertStringContainsString('RUN_OWNER_CONFLICT',(string)$error);}
    }
    /** @dataProvider families */
    public function test_candidate_mutator_rejects_released_predecessor_after_takeover(string $family): void
    {
        [$run,$p]=$this->candidate();$repo=new EodRunRepository();$old=$repo->finalize($run,['lifecycle_state'=>'COMPLETED','terminal_status'=>'HELD','publishability_state'=>'NOT_READABLE']);
        $new=$repo->getOrCreateOwningRun($run->trade_date_requested,'manual_file','INGEST_BARS',$old->run_id,'repair_candidate');
        $before=$this->state();$error=null;try{$this->operation($family,$old,$p,$old);}catch(RuntimeException $e){$error=$e->getMessage();}
        $after=$this->state();$this->retain($family,'released',$before,$after,$error);$this->assertSame($before,$after,'Released predecessor persisted '.$family);$this->assertStringContainsString('RUN_OWNER_CONFLICT',(string)$error);
        $this->assertSame((int)$new->run_id,(int)DB::table('eod_run_owner_fences')->where('trade_date_requested',$run->trade_date_requested)->value('owner_run_id'));
    }
    /** @dataProvider families */
    public function test_candidate_mutator_refuses_run_publication_mismatch(string $family): void
    {
        [$run,$p]=$this->candidate();$other=(new EodRunRepository())->getOrCreateOwningRun('2026-03-25','api','INGEST_BARS',null,'full_publish');
        $before=$this->state();$error=null;try{$this->operation($family,$other,$p,$other);}catch(RuntimeException $e){$error=$e->getMessage();}
        $after=$this->state();$this->retain($family,'mismatch',$before,$after,$error);$this->assertSame($before,$after,'Wrong run mutated publication '.$family);$this->assertNotNull($error);
    }
    /** @dataProvider families */
    public function test_candidate_mutator_refuses_sealed_immutability(string $family): void
    {
        [$run,$p]=$this->candidate(true);$before=$this->state();$error=null;try{$this->operation($family,$run,$p,$run);}catch(RuntimeException $e){$error=$e->getMessage();}
        $after=$this->state();$this->retain($family,'sealed',$before,$after,$error);$this->assertSame($before,$after,'Sealed publication mutated '.$family);$this->assertStringContainsString('SEALED_PUBLICATION_IMMUTABLE',(string)$error);
    }
    /** @dataProvider families */
    public function test_candidate_mutator_compound_fault_rolls_back(string $family): void
    {
        [$run,$p]=$this->candidate();$before=$this->state();$armed=true;
        DB::listen(function($q)use(&$armed){if($armed&&preg_match('/^(update|insert|delete) /i',$q->sql)){$armed=false;throw new RuntimeException('F01_AFTER_FIRST_WRITE');}});
        try{$this->operation($family,$run,$p,$run);$this->fail('Compound fault did not execute');}catch(RuntimeException $e){$this->assertSame('F01_AFTER_FIRST_WRITE',$e->getMessage());}finally{$armed=false;}
        $after=$this->state();$this->retain($family,'compound_fault',$before,$after,'F01_AFTER_FIRST_WRITE');$this->assertSame($before,$after,'Partial candidate write survived '.$family);
        $this->operation($family,$run,$p,$run);
        if($family==='discard')$this->assertFalse(DB::table('eod_publications')->where('publication_id',$p->publication_id)->exists());else $this->assertNotNull(DB::table('eod_publications')->where('publication_id',$p->publication_id)->first());
    }
    /** @dataProvider families */
    public function test_candidate_mutator_owner_positive_holds_fence_through_actual_write(string $family): void
    {
        [$run,$p]=$this->candidate();$before=$this->state();$armed=true;$contention=null;
        DB::listen(function($q)use(&$armed,&$contention,$run){if(!$armed||!preg_match('/^(update|insert|delete) /i',$q->sql))return;$armed=false;
            $contention=$this->onIndependentOwnerConnection(function()use($run){DB::statement('SET SESSION innodb_lock_wait_timeout = 1');try{(new EodRunRepository())->getOrCreateOwningRun($run->trade_date_requested,'manual_file','INGEST_BARS',null,'full_publish');return null;}catch(\Illuminate\Database\QueryException $e){return $e->getMessage();}});});
        $this->operation($family,$run,$p,$run);$armed=false;$after=$this->state();$this->retain($family,'positive_contention',$before,$after,$contention);
        $this->assertStringContainsString('Lock wait timeout exceeded',(string)$contention,'Takeover crossed candidate write: '.$family);$this->assertNotSame($before,$after);
        if($family==='discard')$this->assertFalse(DB::table('eod_publications')->where('publication_id',$p->publication_id)->exists());else{$result=DB::table('eod_publications')->where('publication_id',$p->publication_id)->first();$this->assertSame((int)$run->run_id,(int)$result->run_id);if($family==='manifest')$this->assertMatchesRegularExpression('/^[a-f0-9]{64}$/',$result->publication_manifest_hash);}
    }
    /** @dataProvider families */
    public function test_candidate_mutator_refuses_unsealed_current_flag_and_pointer_reference(string $family): void
    {
        [$run,$p]=$this->candidate();
        foreach (['current_flag','pointer'] as $kind) {
            if ($kind==='current_flag') {
                DB::table('eod_publications')->where('publication_id',$p->publication_id)->update(['is_current'=>1]);
            } else {
                DB::table('eod_publications')->where('publication_id',$p->publication_id)->update(['is_current'=>0]);
                DB::table('eod_current_publication_pointer')->insert([
                    'trade_date'=>$run->trade_date_requested,'publication_id'=>$p->publication_id,
                    'run_id'=>$run->run_id,'publication_version'=>$p->publication_version,
                    'sealed_at'=>'2026-03-24 18:00:00','updated_at'=>\Carbon\Carbon::now(),
                ]);
            }
            // Deliberately corrupt staging identity to reach the defensive branch before any mutation.
            $before=$this->state();$error=null;
            try{$this->operation($family,$run,$p,$run);}catch(RuntimeException $e){$error=$e->getMessage();}
            $after=$this->state();$this->retain($family,'unsealed_'.$kind,$before,$after,$error);
            $this->assertSame($before,$after,'Current/pointer staging guard persisted '.$family.':'.$kind);
            $this->assertStringContainsString('current publication is not mutable staging',(string)$error);
        }
    }

    public function test_manifest_independent_persisted_red_control(): void { $this->test_candidate_mutator_rejects_independent_incumbent_model('manifest'); }
    public function test_manifest_released_persisted_red_control(): void { $this->test_candidate_mutator_rejects_released_predecessor_after_takeover('manifest'); }
    public function test_manifest_compound_persisted_red_control(): void { $this->test_candidate_mutator_compound_fault_rolls_back('manifest'); }

    public function test_provenance_independent_persisted_control(): void { $this->test_candidate_mutator_rejects_independent_incumbent_model('provenance'); }
    public function test_analytical_independent_persisted_control(): void { $this->test_candidate_mutator_rejects_independent_incumbent_model('analytical'); }
    public function test_discard_independent_persisted_control(): void { $this->test_candidate_mutator_rejects_independent_incumbent_model('discard'); }
    public function test_manifest_correction_hash_and_candidate_rollback_together(): void
    {
        $body=file_get_contents(R0025SyntheticV2World::packagePath().'/inputs/provider_response.json');
        $w=R0025SyntheticV2World::build(['http_fetcher'=>function()use(&$body){return ['status'=>200,'headers'=>['Content-Type'=>'application/json'],'body'=>$body];}]);
        $date=EodRun::findOrFail($w['run_id'])->trade_date_requested;$corrections=new \App\Infrastructure\Persistence\MarketData\EodCorrectionRepository();
        $request=$corrections->createRequest($date,'DATASET_HASH_CREATED','Independent F01 compound control','requester-A',$w['publication_id'],$w['run_id']);$corrections->approve($request->correction_id,'approver-B');
        $changed=json_decode($body,true);$changed['chart']['result'][0]['indicators']['quote'][0]['close'][0]=106;$body=json_encode($changed);$run=null;
        foreach(['INGEST_BARS'=>'completeIngest','COMPUTE_INDICATORS'=>'completeIndicators','BUILD_ELIGIBILITY'=>'completeEligibility','HASH'=>'completeHash']as$stage=>$method)$run=$w['pipeline']->$method(new \App\Application\MarketData\DTOs\MarketDataStageInput($date,'api',$run?$run->run_id:null,$stage,$request->correction_id,false,null,'correction'));
        $p=DB::table('eod_publications')->where('run_id',$run->run_id)->first();$this->assertSame('PASS',$run->coverage_gate_state);$before=$this->state();$armed=true;
        DB::listen(function($q)use(&$armed){if($armed&&preg_match('/^update [`"]?eod_publications/i',$q->sql)){$armed=false;throw new RuntimeException('F01_CORRECTION_AFTER_MANIFEST_WRITE');}});
        try{(new EodPublicationRepository())->prepareCandidateManifestForSeal($run,$p->publication_id);$this->fail('Manifest compound fault absent');}catch(RuntimeException $e){$this->assertSame('F01_CORRECTION_AFTER_MANIFEST_WRITE',$e->getMessage());}finally{$armed=false;}
        $after=$this->state();$this->retain('manifest','correction_compound',$before,$after,'F01_CORRECTION_AFTER_MANIFEST_WRITE');$this->assertSame($before,$after,'Correction identity or manifest survived rollback');
        (new EodPublicationRepository())->prepareCandidateManifestForSeal($run,$p->publication_id);
        $this->assertMatchesRegularExpression('/^[a-f0-9]{64}$/',DB::table('eod_dataset_corrections')->where('correction_id',$request->correction_id)->value('semantic_identity_hash'));
        $this->assertMatchesRegularExpression('/^[a-f0-9]{64}$/',DB::table('eod_publications')->where('publication_id',$p->publication_id)->value('publication_manifest_hash'));
    }

    public function test_isolated_owner_may_bind_own_analytical_staging_without_publication_rights(): void
    {
        [$run,$p]=$this->candidate();$repo=new EodRunRepository();$repo->finalize($run,['lifecycle_state'=>'COMPLETED','terminal_status'=>'HELD','publishability_state'=>'NOT_READABLE']);
        $import=$repo->getOrCreateOwningRun($run->trade_date_requested,'api','INGEST_BARS',null,'import_only');$pubs=new EodPublicationRepository();$staging=$pubs->getOrCreateCandidatePublication($import);
        $pubs->bindCandidateAnalyticalProduct($staging->publication_id,$import->run_id,$p->price_product_code,$p->price_product_version,$p->factor_set_hash,$p->factor_set_id,$import);
        $bound=DB::table('eod_publications')->where('publication_id',$staging->publication_id)->first();$this->assertSame($p->factor_set_hash,$bound->factor_set_hash);$this->assertSame(0,(int)$bound->is_current);$this->assertSame('UNSEALED',$bound->seal_state);
        $this->assertSame('NOT_READABLE',$import->fresh()->publishability_state);$before=$this->state();
        foreach(['manifest','seal']as$action){try{if($action==='manifest')$pubs->prepareCandidateManifestForSeal($import,$staging->publication_id);else $pubs->sealCandidatePublication($import,'control');$this->fail('Isolated staging acquired '.$action.' rights');}catch(RuntimeException $e){$this->assertStringContainsString('RUN_OWNER_CONFLICT',$e->getMessage());}$this->assertSame($before,$this->state());}
    }

}
