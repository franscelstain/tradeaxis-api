<?php

use App\Application\MarketData\Services\PublicationInputBindingService;
use App\Application\MarketData\Services\SemanticNestedIdentityService;
use App\Infrastructure\Persistence\MarketData\EodRunRepository;
use Illuminate\Support\Facades\DB;
require_once __DIR__.'/B19ArtifactWriterMariaDbTest.php';

class B19CandidateBindingWriterMariaDbTest extends B19ArtifactWriterMariaDbTest
{
    public static function bindings(): array { return [['input'],['nested']]; }
    private function fixture(string $method,...$args)
    {
        $m=(new ReflectionClass(B19ArtifactWriterMariaDbTest::class))->getMethod($method);
        $m->setAccessible(true);return $m->invoke($this,...$args);
    }
    private function observed(): array { return $this->fixture('state'); }
    private function retainBinding(string $name,array $data): void
    {
        $dir=getenv('B19_E049_STATE_DIR');if(!$dir)return;if(!is_dir($dir))mkdir($dir,0775,true);
        file_put_contents($dir.'/BIND_'.$name.'.json',json_encode($data,JSON_PRETTY_PRINT));
    }
    private function operation(string $family,$run,int $pub): array
    {
        if($family==='input')return(new PublicationInputBindingService())->bind($run,$pub,$run->trade_date_requested);
        $p=DB::table('eod_publications')->where('publication_id',$pub)->first();
        return(new SemanticNestedIdentityService())->bind($run,$p,$run->trade_date_requested,false);
    }
    private function prepareBinding(string $family,int $pub): void
    {
        $row=DB::table('md_publication_lineage_bindings')->where('publication_id',$pub)->first();$this->assertNotNull($row);
        if($family==='input'){
            $this->assertNotEmpty($row->bound_input_context_hash,'Actual HASH fixture must already bind verified producer captures');
            DB::table('md_publication_lineage_bindings')->where('publication_id',$pub)->update(array_fill_keys(['bound_input_schema_version','bound_input_context_json','bound_input_context_hash','bound_input_capture_manifest_json'],null));
        }else{
            $this->assertNotEmpty($row->semantic_nested_identity_version);
            DB::table('md_publication_lineage_bindings')->where('publication_id',$pub)->update(['semantic_nested_identity_version'=>null]);
        }
    }
    /** @dataProvider bindings */
    public function test_binding_independent_caller_cannot_persist_candidate_lineage(string $family): void
    {
        [$run,$pub]=$this->fixture('candidate');$this->prepareBinding($family,$pub);
        $before=$this->observed();
        $foreign=$this->onIndependentOwnerConnection(function()use($family,$run,$pub){
            $before=$this->observed();$error=null;
            try{$this->operation($family,$run,$pub);}catch(RuntimeException $e){$error=$e->getMessage();}
            $after=$this->observed();return compact('before','after','error');
        });
        $after=$this->observed();$this->retainBinding('independent_'.$family,compact('before','after','foreign'));
        $this->assertSame($before,$after,'Unauthenticated candidate binding persisted '.$family);
        $this->assertSame($foreign['before'],$foreign['after'],'Independent writer sees persisted binding '.$family);
        $this->assertStringContainsString('RUN_OWNER_', (string)$foreign['error']);
    }

    /** @dataProvider bindings */
    public function test_binding_owner_transaction_refusal_and_committed_retry(string $family): void
    {
        [$run,$pub]=$this->fixture('candidate');$this->prepareBinding($family,$pub);
        $this->assertSame(0,DB::transactionLevel());$before=$this->observed();$armed=true;$probe=null;
        DB::listen(function($q)use(&$armed,&$probe,$run){
            if(!$armed||stripos($q->sql,'update `md_publication_lineage_bindings`')!==0)return;$armed=false;
            $probe=$this->onIndependentOwnerConnection(function()use($run){DB::statement('SET SESSION innodb_lock_wait_timeout=1');
                try{return DB::transaction(function()use($run){DB::table('eod_run_owner_fences')->where('trade_date_requested',$run->trade_date_requested)->lockForUpdate()->first();return 'FENCE_LOCK_ACQUIRED';});}
                catch(\Illuminate\Database\QueryException $e){return $e->getMessage();}});
            throw new RuntimeException('BIND_AFTER_LINEAGE_UPDATE');
        });
        try{$this->operation($family,$run,$pub);$this->fail('Binding fault not reached');}
        catch(RuntimeException $e){$this->assertSame('BIND_AFTER_LINEAGE_UPDATE',$e->getMessage());}finally{$armed=false;}
        $after=$this->observed();$fresh=$this->onIndependentOwnerConnection(fn()=>$this->observed());
        $this->retainBinding('atomic_'.$family,compact('before','after','fresh','probe'));
        $this->assertSame($before,$after);$this->assertSame($before,$fresh);
        $this->assertStringContainsString('1205',(string)$probe,'Binding date fence must remain through actual UPDATE');
        $this->assertStringContainsString('eod_run_owner_fences',(string)$probe);
        $result=$this->operation($family,$run,$pub);$after=$this->observed();$fresh=$this->onIndependentOwnerConnection(fn()=>$this->observed());
        $this->retainBinding('committed_'.$family,compact('before','after','fresh','result'));
        $this->assertNotSame($before,$after);$this->assertSame($after,$fresh);
        $other=(new EodRunRepository())->getOrCreateOwningRun('2026-03-25','api','INGEST_BARS',null,'full_publish');
        $otherPub=(int)(new \App\Infrastructure\Persistence\MarketData\EodPublicationRepository())->getOrCreateCandidatePublication($other)->publication_id;
        $before=$this->observed();$error=null;
        try{$this->operation($family,$run,$otherPub);}catch(RuntimeException $e){$error=$e->getMessage();}
        $this->retainBinding('wrong_target_'.$family,['before'=>$before,'after'=>$this->observed(),'error'=>$error]);
        $this->assertSame($before,$this->observed());$this->assertStringContainsString('RUN_OWNER_', (string)$error);
        (new EodRunRepository())->finalize($run,['lifecycle_state'=>'COMPLETED','terminal_status'=>'HELD','publishability_state'=>'NOT_READABLE']);
        $this->onIndependentOwnerConnection(fn()=>(new EodRunRepository())->acquireLinkedRecoveryOwner($run->trade_date_requested,'api',$run->run_id));
        $before=$this->observed();$error=null;try{$this->operation($family,$run,$pub);}catch(RuntimeException $e){$error=$e->getMessage();}
        $this->retainBinding('released_'.$family,['before'=>$before,'after'=>$this->observed(),'error'=>$error]);
        $this->assertSame($before,$this->observed());$this->assertStringContainsString('RUN_OWNER_', (string)$error);
    }

    /** @dataProvider bindings */
    public function test_binding_sealed_confirmation_is_read_only_and_corruption_refuses(string $family): void
    {
        [$run,$pub,$world]=$this->fixture('candidate');
        $run=$world['pipeline']->completeSeal(new \App\Application\MarketData\DTOs\MarketDataStageInput($run->trade_date_requested,'api',$run->run_id,'SEAL',null,false,null,'full_publish'));
        $this->assertSame('SEALED',DB::table('eod_publications')->where('publication_id',$pub)->value('seal_state'));
        $before=$this->observed();$writes=[];
        DB::listen(function($q)use(&$writes){if(preg_match('/^(insert|update|delete) /i',$q->sql))$writes[]=$q->sql;});
        $result=$this->onIndependentOwnerConnection(fn()=>$this->operation($family,$run,$pub));
        $after=$this->observed();$this->retainBinding('sealed_confirmation_'.$family,compact('before','after','writes','result'));
        $this->assertSame($before,$after);$this->assertSame([],$writes,'Sealed confirmation may not issue DML');
        // Deliberate corruption exercises refusal; it is not a production mutation or accepted source fact.
        $column=$family==='input'?'bound_input_context_hash':'semantic_nested_identity_version';
        DB::table('md_publication_lineage_bindings')->where('publication_id',$pub)->update([$column=>$family==='input'?str_repeat('f',64):null]);
        $before=$this->observed();$error=null;try{$this->operation($family,$run,$pub);}catch(RuntimeException $e){$error=$e->getMessage();}
        $after=$this->observed();$this->retainBinding('sealed_corruption_'.$family,compact('before','after','error'));
        $this->assertSame($before,$after);$this->assertStringContainsString('SEALED_PUBLICATION_IMMUTABLE',(string)$error);
    }

    public function test_factor_first_persistence_holds_actual_date_fence(): void
    {
        $this->fixture('candidate');$repo=new EodRunRepository();$date='2026-03-25';
        $run=$repo->getOrCreateOwningRun($date,'api','INGEST_BARS',null,'full_publish');
        $pub=(new \App\Infrastructure\Persistence\MarketData\EodPublicationRepository())->getOrCreateCandidatePublication($run);
        // Prime the immutable registry capture: idempotent reads must not accidentally supply a writer fence.
        (new \App\Infrastructure\Persistence\MarketData\RunInputCaptureRepository())->captureRegistryVersions($run);
        $before=$this->observed();$armed=true;$probe=null;
        DB::listen(function($q)use(&$armed,&$probe,$date){
            if(!$armed||stripos($q->sql,'insert into `md_adjustment_factor_sets`')!==0)return;$armed=false;
            $probe=$this->onIndependentOwnerConnection(function()use($date){DB::statement('SET SESSION innodb_lock_wait_timeout=1');
                try{return DB::transaction(function()use($date){DB::table('eod_run_owner_fences')->where('trade_date_requested',$date)->lockForUpdate()->first();return 'FENCE_LOCK_ACQUIRED';});}
                catch(\Illuminate\Database\QueryException $e){return $e->getMessage();}});
            throw new RuntimeException('FACTOR_AFTER_FIRST_INSERT');
        });
        try{(new \App\Application\MarketData\Services\AdjustmentFactorSetService())->ensureForPublication($run,$pub->publication_id,$date,[]);$this->fail('Factor fault not reached');}
        catch(RuntimeException $e){$this->assertSame('FACTOR_AFTER_FIRST_INSERT',$e->getMessage());}finally{$armed=false;}
        $after=$this->observed();$this->retainBinding('factor_date_fence',compact('before','after','probe'));
        $this->assertSame($before,$after);$this->assertStringContainsString('1205',(string)$probe,'Factor date fence missing at actual issuance');
    }
    public function test_factor_nonempty_decisions_unknown_assessment_and_applied_factors_share_owner_transaction(): void
    {
        $this->fixture('candidate');$repo=new EodRunRepository();$date='2026-03-25';
        $run=$repo->getOrCreateOwningRun($date,'api','COMPUTE_INDICATORS',null,'full_publish');
        $pub=(new \App\Infrastructure\Persistence\MarketData\EodPublicationRepository())->getOrCreateCandidatePublication($run);
        // The migrated proof clone copies schema, not reference seed contents. Use the existing
        // governed type dictionary semantics also declared in UsesMarketDataSqlite.
        DB::table('market_data_corporate_action_types')->insert(['action_type_code'=>'STOCK_SPLIT',
            'price_continuity_impact'=>'SCALED','volume_continuity_impact'=>'SCALED','share_count_changes'=>1]);
        $captures=new \App\Infrastructure\Persistence\MarketData\RunInputCaptureRepository();$captures->captureRegistryVersions($run);
        // Declared synthetic event facts and assessments, independent of the producer's result.
        $listingIds=[];
        foreach([1,2,3] as $i){
            $listing=DB::table('md_listings')->insertGetId(['listing_uid'=>hash('sha256','fence-event-listing-'.$i),'legacy_ticker_id'=>800+$i,
                'instrument_id'=>800+$i,'exchange_code'=>'IDX','market_segment'=>'REGULAR','board_code'=>'MAIN','listed_date'=>'2010-01-01',
                'listing_state'=>'LISTED','recorded_at'=>'2020-01-01 00:00:00','created_at'=>'2020-01-01 00:00:00'],'listing_id');$listingIds[]=$listing;
            $source=new \App\Infrastructure\Persistence\MarketData\SourceObservationRepository();
            $observation=$source->recordAcceptedRows($source->capture(['acquisition_batch_id'=>1,'attempt_uid'=>'factor-fence-event-'.$i,
                'requested_trade_date'=>'2026-03-15','source_mode'=>'api','source_name'=>'IDX','provider'=>'IDX','provider_symbol'=>'F'.$i,
                'sanitized_request_identity'=>'synthetic-event:'.$i,'adapter_version'=>'fixture-v1','payload'=>json_encode(['declared_event'=>$i,'ratio'=>[1,5]]),'acquired_at'=>'2026-03-20 00:00:00']),
                [['ticker_code'=>'F'.$i,'trade_date'=>'2026-03-15','open'=>100,'high'=>110,'low'=>90,'close'=>105,'volume'=>1000,'source_row_ref'=>'synthetic-event:'.$i]]);
            $event=DB::table('md_corporate_action_revisions')->insertGetId(['event_uid'=>hash('sha256','factor-fence-event-'.$i),'revision_number'=>1,
                'listing_id'=>$listing,'action_type_code'=>'STOCK_SPLIT','lifecycle_state'=>'EFFECTIVE','verification_state'=>'AUTHORITATIVE_VERIFIED',
                'ex_date'=>'2026-03-15','effective_at'=>'2026-03-15 00:00:00','terms_json'=>json_encode(['ratio'=>['from'=>1,'to'=>5]]),
                'source_observation_id'=>$observation['source_observation_id'],'recorded_at'=>'2026-03-20 00:00:00'],'corporate_action_revision_id');
            if($i<3)DB::table('md_source_scale_assessments')->insert(['assessment_uid'=>hash('sha256','factor-fence-assessment-'.$i),
                'revision_number'=>1,'provider'=>'YAHOO_FINANCE','listing_id'=>$listing,'corporate_action_revision_id'=>$event,
                'source_scale_state'=>$i===1?'AS_TRADED':'PROVIDER_BACK_ADJUSTED','scale_effective_from'=>'2026-03-15',
                'assessment_version'=>\App\Application\MarketData\Services\AdjustmentFactorSetService::ASSESSMENT_VERSION,
                'evidence_observation_set_hash'=>hash('sha256',json_encode([(int)$observation['source_observation_id']])),
                'evidence_json'=>json_encode(['observation_ids'=>[(int)$observation['source_observation_id']],'source_ref'=>'synthetic:'.$i]),
                'recorded_at'=>'2026-03-21 00:00:00','created_at'=>'2026-03-21 00:00:00']);
        }
        $service=new \App\Application\MarketData\Services\AdjustmentFactorSetService();
        $before=$this->observed();$error=$this->onIndependentOwnerConnection(function()use($service,$run,$pub,$date){try{$service->ensureForPublication($run,$pub->publication_id,$date,[]);return null;}catch(RuntimeException $e){return $e->getMessage();}});
        $this->assertStringContainsString('RUN_OWNER_',(string)$error);$this->assertSame($before,$this->observed());
        foreach(['md_source_scale_assessments','md_adjustment_factor_sets','md_adjustment_factor_decisions','md_adjustment_factors']as $table){
            $before=$this->observed();$gate=(object)['armed'=>true];$probe=null;$hit=null;
            DB::listen(function($q)use($gate,&$probe,&$hit,$table,$date){if(!$gate->armed||stripos($q->sql,'insert into `'.$table.'`')!==0)return;$gate->armed=false;$hit=$q->sql;
                $probe=$this->onIndependentOwnerConnection(function()use($date){DB::statement('SET SESSION innodb_lock_wait_timeout=1');try{return DB::transaction(function()use($date){DB::table('eod_run_owner_fences')->where('trade_date_requested',$date)->lockForUpdate()->first();return 'UNLOCKED';});}catch(\Illuminate\Database\QueryException $e){return $e->getMessage();}});
                throw new RuntimeException('FACTOR_NONEMPTY_AFTER_WRITE');});
            try{$service->ensureForPublication($run,$pub->publication_id,$date,[]);$this->fail('Factor compound fault not reached '.$table);}catch(RuntimeException $e){$this->assertSame('FACTOR_NONEMPTY_AFTER_WRITE',$e->getMessage());}finally{$gate->armed=false;}
            $after=$this->observed();$fresh=$this->onIndependentOwnerConnection(fn()=>$this->observed());$this->retainBinding('factor_nonempty_'.$table,compact('before','after','fresh','probe','hit'));
            $this->assertStringContainsString('insert into `'.$table.'`',(string)$hit,'Factor fault targeted a different writer');$this->assertSame($before,$after,'Partial factor transaction survived');$this->assertSame($before,$fresh);$this->assertStringContainsString('1205',(string)$probe);
        }
        $result=$service->ensureForPublication($run,$pub->publication_id,$date,[]);
        $this->assertSame(['APPLIED','HELD_PROVIDER_BACK_ADJUSTED','HELD_SOURCE_SCALE_UNKNOWN'],array_column($result['decisions'],'decision_state'));
        $this->assertSame(3,DB::table('md_adjustment_factor_decisions')->where('factor_set_id',$result['factor_set_id'])->count());
        $this->assertSame(1,DB::table('md_adjustment_factors')->where('factor_set_id',$result['factor_set_id'])->count());
        $this->assertSame('UNKNOWN',DB::table('md_source_scale_assessments')->where('listing_id',$listingIds[2])->value('source_scale_state'));
        $this->assertSame($result,$service->ensureForPublication($run,$pub->publication_id,$date,[]));
        $repo->finalize($run,['lifecycle_state'=>'COMPLETED','terminal_status'=>'HELD','publishability_state'=>'NOT_READABLE']);
        $this->onIndependentOwnerConnection(fn()=>(new EodRunRepository())->acquireLinkedRecoveryOwner($date,'api',$run->run_id));
        $before=$this->observed();try{$service->ensureForPublication($run,$pub->publication_id,$date,[]);$this->fail('Released factor actor accepted');}catch(RuntimeException $e){$this->assertStringContainsString('RUN_OWNER_',$e->getMessage());}$this->assertSame($before,$this->observed());
    }

}
