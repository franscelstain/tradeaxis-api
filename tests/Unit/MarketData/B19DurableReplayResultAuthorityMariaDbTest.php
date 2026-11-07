<?php

use App\Application\MarketData\Services\ReplayVerificationService;
use App\Infrastructure\Persistence\MarketData\EodEvidenceRepository;
use App\Infrastructure\Persistence\MarketData\EodPublicationRepository;
use App\Infrastructure\Persistence\MarketData\ReplayResultRepository;
use Illuminate\Support\Facades\DB;
use Tests\Support\R0025SyntheticV2World;
require_once __DIR__.'/B19OwnerFenceMariaDbTest.php';

/** Actual issuer/SQL evidence; self-generated control fixtures remain explicitly BLOCKED, never golden. */
class B19DurableReplayResultAuthorityMariaDbTest extends B19OwnerFenceMariaDbTest
{
    protected function setUp(): void
    {
        parent::setUp();
        require_once base_path('database/migrations/2026_10_29_000001_add_durable_replay_result_authority.php');
        if(!DB::connection()->getSchemaBuilder()->hasTable('md_replay_result_cases'))(new AddDurableReplayResultAuthority())->up();
    }
    protected function tearDown(): void { \Carbon\Carbon::setTestNow();parent::tearDown(); }
    private function verifier(): ReplayVerificationService
    { return new ReplayVerificationService(new EodEvidenceRepository(),new EodPublicationRepository(),new ReplayResultRepository()); }
    private function fixture(array $world): string
    {
        $root=getenv('B19_E049_STATE_DIR')?:sys_get_temp_dir().'/e049-result-'.bin2hex(random_bytes(6));
        $dir=$root.'/fixture-'.$world['run_id'].'-'.bin2hex(random_bytes(4));
        $this->verifier()->generateFixtureFromRun($world['run_id'],$dir,'self_generated_result_authority_control',$world['publication_id']);
        return $dir;
    }
    private function sourceState(): array
    {
        $out=[];foreach(['eod_runs','eod_publications','eod_current_publication_pointer','eod_run_events','eod_bars','eod_bars_history',
            'eod_indicators_history','eod_eligibility_history','md_run_input_captures','md_source_observations','eod_run_owner_fences','eod_observer_fences']as $table)$out[$table]=DB::table($table)->get()->toJson();
        return $out;
    }
    private function resultState(): array
    {
        $out=[];foreach(['md_replay_daily_metrics','md_replay_reason_code_counts','md_replay_result_cases','md_replay_result_attempts','md_replay_result_sequences']as $table)
            $out[$table]=DB::table($table)->get()->toJson();return $out;
    }
    private function retainResult(string $label,array $data): void
    {
        $dir=getenv('B19_E049_STATE_DIR');if(!$dir)return;
        if(!is_dir($dir))mkdir($dir,0775,true);
        $data+=['database'=>DB::connection()->getDatabaseName(),'engine'=>DB::selectOne('SELECT VERSION() AS version')->version,
            'connection'=>(int)DB::selectOne('SELECT CONNECTION_ID() AS id')->id,'result_state'=>$this->resultState()];
        file_put_contents($dir.'/'.$label.'.json',json_encode($data,JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES));
    }

    public function test_real_verifier_owns_atomic_blocked_result_and_identical_retry_is_read_only_while_anonymous_writers_refuse(): void
    {
        $world=R0025SyntheticV2World::build();$fixture=$this->fixture($world);$source=$this->sourceState();$verifier=$this->verifier();
        $result=$verifier->verifyRunAgainstFixture($world['run_id'],$fixture,null,$world['publication_id']);
        $this->assertSame('BLOCKED',$result['replay_status'],'An actual self-generated fixture cannot be admitted by the writer correction.');
        $id=(int)$result['replay_id'];$case=DB::table('md_replay_result_cases')->where('replay_id',$id)->first();
        $this->assertSame('COMPLETED',$case->state);$this->assertNotNull($case->released_at);$this->assertSame(1,(int)$case->fencing_token);
        $this->assertSame('BLOCKED',DB::table('md_replay_daily_metrics')->where('replay_id',$id)->value('replay_status'));
        $before=$this->resultState();$refusals=[];
        foreach(['new_metric','metric','new_counts','counts','allocation']as $family){
            $error=null;try{$this->onIndependentOwnerConnection(function()use($family,$result,$id){
                $repo=new ReplayResultRepository();
                if(in_array($family,['metric','new_metric'],true)){$copy=$result;$copy['comparison_note']='unauthorized overwrite';if($family==='new_metric')$copy['replay_id']=$id+9001;$repo->upsertMetric($copy);}
                elseif(in_array($family,['counts','new_counts'],true))$repo->replaceReasonCodeCounts($family==='new_counts'?$id+9002:$id,$result['trade_date'],[['reason_code'=>'RUN_LOCK_CONFLICT','reason_count'=>999]]);
                else $repo->nextReplayId();
            });}catch(RuntimeException $e){$error=$e->getMessage();}
            $refusals[$family]=$error;
            $readback=$this->onIndependentOwnerConnection(fn()=>['connection'=>(int)DB::selectOne('SELECT CONNECTION_ID() AS id')->id,'state'=>$this->resultState()]);
            $this->retainResult('anonymous_result_write_'.$family,compact('family','before','error','readback'));

            $this->assertStringContainsString('REPLAY_RESULT_AUTHORITY_REQUIRED',(string)$error);
            $this->assertSame($before,$readback['state'],'Unidentified independent writers cannot alter result or lifecycle.');
        }
        $queries=[];DB::listen(function($event)use(&$queries){$queries[]=$event->sql;});
        $retry=$verifier->verifyRunAgainstFixture($world['run_id'],$fixture,$id,$world['publication_id']);
        $retryQueries=$queries;$this->assertTrue($retry['result_reused']);$this->assertFalse($retry['fresh_comparison_performed']);
        $this->assertSame($before,$this->resultState());
        $this->assertSame([],array_values(array_filter($retryQueries,fn($sql)=>preg_match('/^\s*(INSERT|UPDATE|DELETE|REPLACE|CREATE|ALTER|DROP)\b/i',$sql))),
            'A completed identical retry is read-only, including allocation and attempt history.');
        $portable=$fixture.'-portable';\Illuminate\Support\Facades\File::copyDirectory($fixture,$portable);
        $portableRetry=$verifier->verifyRunAgainstFixture($world['run_id'],$portable,$id,$world['publication_id']);
        $this->assertTrue($portableRetry['result_reused'],'Verified identical bytes retain identity when transported to another path.');
        $this->assertSame($before,$this->resultState());
        $manifest=file_get_contents($fixture.'/manifest.json');$changed=json_decode($manifest,true);$changed['fixture_version']='changed-input-after-completion';
        file_put_contents($fixture.'/manifest.json',json_encode($changed));$scopeError=null;
        try{$verifier->verifyRunAgainstFixture($world['run_id'],$fixture,$id,$world['publication_id']);}
        catch(RuntimeException $e){$scopeError=$e->getMessage();}finally{file_put_contents($fixture.'/manifest.json',$manifest);}
        $this->assertStringContainsString('REPLAY_RESULT_OWNER_SCOPE_MISMATCH',(string)$scopeError,'Changed fixture inputs require a new replay_id.');
        $this->assertSame($before,$this->resultState());
        $this->assertSame($source,$this->sourceState(),'Result authority grants no publication/source mutation.');
        $this->retainResult('real_issuer_readonly_and_anonymous_refusal',compact('result','retry','source','refusals','retryQueries'));
    }

    public function test_legacy_unattributed_reason_counts_are_not_reallocated_or_deleted_by_a_new_result_owner(): void
    {
        $world=R0025SyntheticV2World::build();$fixture=$this->fixture($world);
        // This is representable in the existing migrated schema: reason counts have no metric FK.
        // Preserve an old unattributed fact, not a synthetic authority/owner row.
        DB::table('md_replay_reason_code_counts')->insert(['replay_id'=>1,'trade_date'=>'2026-03-23','reason_code'=>'RUN_LOCK_CONFLICT','reason_count'=>999]);
        $before=DB::table('md_replay_reason_code_counts')->where('replay_id',1)->get()->toJson();
        $result=$this->verifier()->verifyRunAgainstFixture($world['run_id'],$fixture,null,$world['publication_id']);
        $after=DB::table('md_replay_reason_code_counts')->where('replay_id',1)->get()->toJson();
        $this->retainResult('legacy_counts_allocation_and_preservation',compact('before','after','result'));
        $this->assertNotSame(1,(int)$result['replay_id'],'Atomic allocation must not reuse an unattributed legacy count identity.');
        $this->assertSame($before,$after,'New result completion must not delete legacy reason evidence.');
    }

    public function test_count_write_failure_rolls_back_complete_result_then_reacquires_a_new_attempt_without_rewriting_history(): void
    {
        $world=R0025SyntheticV2World::build();$fixture=$this->fixture($world);$source=$this->sourceState();
        DB::unprepared("CREATE TRIGGER e049_result_count_fault BEFORE INSERT ON md_replay_reason_code_counts FOR EACH ROW SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT='INJECTED_RESULT_COUNT_FAILURE'");
        $error=null;try{$this->verifier()->verifyRunAgainstFixture($world['run_id'],$fixture,null,$world['publication_id']);}
        catch(RuntimeException $e){$error=$e->getMessage();}finally{DB::unprepared('DROP TRIGGER IF EXISTS e049_result_count_fault');}
        $case=DB::table('md_replay_result_cases')->first();$attempt=DB::table('md_replay_result_attempts')->first();$failed=$this->resultState();
        $this->retainResult('atomic_result_count_failure',compact('case','attempt','failed','error','source'));
        $this->assertStringContainsString('INJECTED_RESULT_COUNT_FAILURE',(string)$error);
        $this->assertSame(0,DB::table('md_replay_daily_metrics')->count(),'Count failure cannot leave a metric committed.');
        $this->assertSame(0,DB::table('md_replay_reason_code_counts')->count());
        $this->assertSame('FAILED',$case->state);$this->assertNotNull($case->released_at);$this->assertSame('FAILED',$attempt->state);
        $this->assertSame($failed,$this->onIndependentOwnerConnection(fn()=>$this->resultState()));
        $result=$this->verifier()->verifyRunAgainstFixture($world['run_id'],$fixture,(int)$case->replay_id,$world['publication_id']);
        $success=DB::table('md_replay_result_cases')->first();
        $this->assertSame('COMPLETED',$success->state);$this->assertSame(2,(int)$success->fencing_token);
        $this->assertNotSame($case->execution_id,$success->execution_id);$this->assertNotSame($case->credential_hash,$success->credential_hash);
        $this->assertEquals($attempt,DB::table('md_replay_result_attempts')->where('execution_id',$attempt->execution_id)->first());
        $this->assertSame(2,DB::table('md_replay_result_attempts')->count());$this->assertSame('BLOCKED',$result['replay_status']);
        $this->assertSame($source,$this->sourceState());
        $this->retainResult('atomic_failure_authenticated_retry',compact('case','attempt','success','result'));
    }

    public function test_valid_incumbent_cannot_be_borrowed_and_expired_takeover_fences_old_completion_and_telemetry(): void
    {
        $world=R0025SyntheticV2World::build();$fixture=$this->fixture($world);$source=$this->sourceState();$repo=new ReplayResultRepository();
        $old=null;$new=null;$busy=null;$stale=null;$beforeBusy=null;$afterBusy=null;$independent=null;
        try{$repo->duringVerifiedRequest($world['run_id'],$fixture,'PUBLICATION_EXACT',$world['publication_id'],null,null,
            function()use($repo,$world,$fixture,&$old,&$new,&$busy,&$beforeBusy,&$afterBusy,&$independent){
                $id=$repo->nextReplayId();$old=DB::table('md_replay_result_cases')->where('replay_id',$id)->first();$beforeBusy=$this->resultState();
                try{$this->onIndependentOwnerConnection(fn()=>$this->verifier()->verifyRunAgainstFixture($world['run_id'],$fixture,$id,$world['publication_id']));}
                catch(RuntimeException $e){$busy=$e->getMessage();}
                $afterBusy=$this->resultState();
                // Assert outside the production exception boundary: PHPUnit failures are RuntimeExceptions too.
                if($busy===null)throw new RuntimeException('TEST_BUSY_REFUSAL_MISSING');
                \Carbon\Carbon::setTestNow(\Carbon\Carbon::now(config('market_data.platform.timezone'))->addSeconds(301));
                $new=$this->onIndependentOwnerConnection(function()use($world,$fixture,$id,&$independent){
                    $independent=(int)DB::selectOne('SELECT CONNECTION_ID() AS id')->id;
                    return $this->verifier()->verifyRunAgainstFixture($world['run_id'],$fixture,$id,$world['publication_id']);
                });
                // Old worker resumes after the new independently acquired actor completed.
                // It holds its original real issuer credential, not a synthetic owner row.
                $repo->stageResult($new,$new['reason_code_counts']);return $new;
            });}catch(RuntimeException $e){$stale=$e->getMessage();}
        $current=DB::table('md_replay_result_cases')->where('replay_id',$old->replay_id)->first();
        $this->retainResult('busy_expired_takeover_stale_completion',compact('old','new','busy','stale','current','beforeBusy','afterBusy','independent','source'));
        $this->assertSame($beforeBusy,$afterBusy,'A competing request must not acquire an active result case.');
        $this->assertStringContainsString('REPLAY_RESULT_OWNER_BUSY',(string)$busy,'A valid incumbent identity does not grant its credential to a competitor.');
        $this->assertStringContainsString('REPLAY_RESULT_OWNER_STALE_OR_RELEASED',(string)$stale);
        $this->assertSame('COMPLETED',$current->state,'A stale worker cannot overwrite successor completion with FAILED telemetry.');
        $this->assertSame(2,(int)$current->fencing_token);$this->assertNotSame($old->execution_id,$current->execution_id);
        $this->assertSame('EXPIRED',DB::table('md_replay_result_attempts')->where('execution_id',$old->execution_id)->value('state'));
        $this->assertNotSame((int)DB::selectOne('SELECT CONNECTION_ID() AS id')->id,$independent);
        $this->assertSame($source,$this->sourceState());
    }

    public function test_post_terminal_observation_changes_actual_replay_input_and_cannot_reuse_a_completed_case(): void
    {
        $world=R0025SyntheticV2World::build();$fixture=$this->fixture($world);$verifier=$this->verifier();
        $first=$verifier->verifyRunAgainstFixture($world['run_id'],$fixture,null,$world['publication_id']);
        $before=$this->resultState();
        (new \App\Infrastructure\Persistence\MarketData\EodRunRepository())->recordObservation(\App\Models\EodRun::findOrFail($world['run_id']),
            'detector','FINALIZE','DIAGNOSTIC_WARNING','WARN','Legitimate later observation; no publication write or new incident claim.',
            'RUN_LOCK_CONFLICT',[]);
        $error=null;$retry=null;
        try{$retry=$verifier->verifyRunAgainstFixture($world['run_id'],$fixture,$first['replay_id'],$world['publication_id']);}
        catch(RuntimeException $e){$error=$e->getMessage();}
        $fresh=$verifier->verifyRunAgainstFixture($world['run_id'],$fixture,null,$world['publication_id']);
        $this->retainResult('later_observed_input_cannot_reuse_result',compact('first','before','retry','error','fresh'));
        $this->assertNotSame($first['reason_code_counts'],$fresh['reason_code_counts'],'The later real observation is an actually consumed input.');
        $this->assertStringContainsString('REPLAY_RESULT_OWNER_SCOPE_MISMATCH',(string)$error,'Changed persisted replay input requires a new ID; cache cannot hide it.');
        $this->assertNotSame((int)$first['replay_id'],(int)$fresh['replay_id']);
        $this->assertSame($first['comparison_result'],DB::table('md_replay_daily_metrics')->where('replay_id',$first['replay_id'])->value('comparison_result'));
    }

    public function test_metric_identity_cannot_be_forged_even_by_a_legitimately_issued_result_actor(): void
    {
        $world=R0025SyntheticV2World::build();$fixture=$this->fixture($world);$repo=new ReplayResultRepository();
        $baseline=$this->verifier()->verifyRunAgainstFixture($world['run_id'],$fixture,null,$world['publication_id']);
        $other=(new \App\Infrastructure\Persistence\MarketData\EodRunRepository())->createPromoteRunFromSeed(\App\Models\EodRun::findOrFail($world['run_id']),'HASH');
        $cases=[];
        foreach(['date','publication','publication_owner']as $variant){
            $error=null;$id=null;
            try{$repo->duringVerifiedRequest($world['run_id'],$fixture,'PUBLICATION_EXACT',$world['publication_id'],null,null,
                function()use($repo,$baseline,$variant,$other,&$id){
                    $id=$repo->nextReplayId();$metric=$baseline;$metric['replay_id']=$id;
                    if($variant==='date')$metric['trade_date']='2026-03-24';
                    if($variant==='publication')$metric['publication_id']=2147483000;
                    if($variant==='publication_owner')$metric['publication_run_id']=(int)$other->run_id;
                    $repo->stageResult($metric,$metric['reason_code_counts']);return $metric;
                });}catch(RuntimeException $e){$error=$e->getMessage();}
            $cases[]=compact('variant','id','error');
        }
        $this->retainResult('forged_metric_identity_valid_actor',compact('cases','baseline'));
        foreach($cases as $case){
            $this->assertStringContainsString('REPLAY_RESULT_OWNER_METRIC_SCOPE_MISMATCH',(string)$case['error'],'No invented metric identity: '.$case['variant']);
            $this->assertSame(0,DB::table('md_replay_daily_metrics')->where('replay_id',$case['id'])->count());
        }
    }

    public function test_as_known_result_stays_after_projection_rollback_and_future_observation_does_not_change_asof_input(): void
    {
        $world=R0025SyntheticV2World::build(['run_clock'=>'2026-03-25 10:30:00']);$date='2026-03-23';$cutoff='2026-03-25 11:00:00';
        $snapshotter=new \App\Application\MarketData\Services\AsKnownReplaySnapshotService();$input=$snapshotter->capture($date,$cutoff);
        $dir=(getenv('B19_E049_STATE_DIR')?:sys_get_temp_dir().'/e049-result-'.bin2hex(random_bytes(6))).'/as_known_negative_authority_control';
        mkdir($dir.'/expected',0775,true);
        file_put_contents($dir.'/manifest.json',json_encode(['fixture_id'=>'author-negative-as-known-result-authority','fixture_version'=>'control-v1',
            'fixture_family'=>'unqualified_author_negative_control','replay_mode'=>'AS_KNOWN','assertion_scope'=>'CANONICAL_RAW','trade_date'=>$date,'knowledge_cutoff'=>$cutoff]));
        file_put_contents($dir.'/expected/as_known_snapshot.json',json_encode($input));
        // Input context documents actual selection. The independently chosen wrong output hash
        // intentionally cannot establish a golden fixture, determinism PASS or independent admission.
        file_put_contents($dir.'/expected/as_known_execution.json',json_encode(['execution_scope'=>'CANONICAL_RAW','execution_state'=>'SUCCESS',
            'canonical_output_hash'=>str_repeat('0',64),'canonical_row_count'=>1,'invalid_row_count'=>0,'reason_code_counts'=>[]]));
        $source=$this->sourceState();$result=$this->verifier()->verifyAsKnownAgainstFixture($world['run_id'],$dir,$cutoff);
        $this->retainResult('as_known_actual_projection_negative_control',compact('result','source','input'));
        $this->assertSame('FAIL',$result['replay_status'],'The deliberately wrong canonical hash must not become PASS or a missing-input surrogate.');
        $this->assertSame('bars_only',$result['artifact_changed_scope'],'An executed CANONICAL_RAW output hash divergence identifies bars, not a replay-mode label.');
        $this->assertNotNull($result['actual_context']['executed_replay']);
        $this->assertNotSame(str_repeat('0',64),$result['actual_context']['executed_replay']['canonical_output_hash']);
        $this->assertSame($source,$this->sourceState(),'Run/event/fence/capture/projection changes must all roll back before durable result completion.');
        $this->assertSame('COMPLETED',DB::table('md_replay_result_cases')->where('replay_id',$result['replay_id'])->value('state'));
        $exportRoot=$dir.'-diagnostic-exports';
        app(\App\Application\MarketData\Services\MarketDataEvidenceExportService::class)->exportReplayEvidence($result['replay_id'],$date,$exportRoot.'/bars');
        $export=json_decode(file_get_contents($exportRoot.'/bars/replay_result.json'),true);
        $this->assertSame('bars_only',$export['artifact_changed_scope']);
        $command=new \App\Console\Commands\MarketData\VerifyReplayCommand();$command->setLaravel(app());
        $tester=new \Symfony\Component\Console\Tester\CommandTester($command);
        $exit=$tester->execute(['run_id'=>$world['run_id'],'fixture_path'=>$dir,'--mode'=>'AS_KNOWN','--knowledge_cutoff'=>$cutoff]);
        $this->assertSame(1,$exit);$this->assertStringContainsString('artifact_changed_scope=bars_only',$tester->getDisplay());
        $inputOnlyDir=$dir.'-equal-output-wrong-input';\Illuminate\Support\Facades\File::copyDirectory($dir,$inputOnlyDir);
        // A controlled equal-output vector, not an independently approved comparison oracle.
        // The deliberately wrong input identity guarantees FAIL; copying this diagnostic vector grants no admission.
        $wrongInput=$input;$this->assertNotSame(str_repeat('0',64),$input['reason_registry_hash']);
        $wrongInput['reason_registry_hash']=str_repeat('0',64);
        file_put_contents($inputOnlyDir.'/expected/as_known_snapshot.json',json_encode($wrongInput));
        file_put_contents($inputOnlyDir.'/expected/as_known_execution.json',json_encode($result['actual_context']['executed_replay']));
        $inputOnly=$this->verifier()->verifyAsKnownAgainstFixture($world['run_id'],$inputOnlyDir,$cutoff);
        $this->assertSame('FAIL',$inputOnly['replay_status']);$this->assertSame('none',$inputOnly['artifact_changed_scope'],'An input-only mismatch cannot invent an artifact hash change.');
        $this->assertSame($inputOnly['expected_context']['executed_replay']['canonical_output_hash'],$inputOnly['actual_context']['executed_replay']['canonical_output_hash']);
        app(\App\Application\MarketData\Services\MarketDataEvidenceExportService::class)->exportReplayEvidence($inputOnly['replay_id'],$date,$exportRoot.'/input-only');
        $inputExport=json_decode(file_get_contents($exportRoot.'/input-only/replay_result.json'),true);
        $this->assertSame('none',$inputExport['artifact_changed_scope']);
        $this->assertSame($source,$this->sourceState());
        $this->retainResult('as_known_artifact_diagnosis_metric_export_command',compact('result','export','exit','inputOnly','inputExport')+['cli_output'=>$tester->getDisplay()]);
        \Carbon\Carbon::setTestNow('2026-03-26 10:00:00');
        (new \App\Infrastructure\Persistence\MarketData\EodRunRepository())->recordObservation(\App\Models\EodRun::findOrFail($world['run_id']),
            'detector','FINALIZE','DIAGNOSTIC_WARNING','WARN','Future operational observation, outside the requested as-known cutoff.','RUN_LOCK_CONFLICT',[]);
        $afterInput=$snapshotter->capture($date,$cutoff);$this->assertSame($input,$afterInput,'The future observation is not an AS_KNOWN producer input.');
        $before=$this->resultState();$retry=null;$error=null;
        try{$retry=$this->verifier()->verifyAsKnownAgainstFixture($world['run_id'],$dir,$cutoff,$result['replay_id']);}
        catch(RuntimeException $e){$error=$e->getMessage();}
        $this->retainResult('as_known_future_observation_retry',compact('result','before','retry','error','input','afterInput'));
        $this->assertNull($error,'Future operational data must not invalidate an identical AS_KNOWN request.');
        $this->assertTrue($retry['result_reused']);$this->assertSame($before,$this->resultState());
    }

    public function test_missing_asof_source_proof_persists_blocked_result_without_opening_projection_or_publication_rights(): void
    {
        \Carbon\Carbon::setTestNow('2026-03-25 09:00:00');
        // Issue the known configuration through its real producer before the synthetic acquisition.
        // No historical snapshot is edited or retroactively backdated.
        (new \App\Infrastructure\Persistence\MarketData\MarketDataConfigSnapshotRepository())->resolveForRun('2026-03-23');
        $world=R0025SyntheticV2World::build(['run_clock'=>'2026-03-25 10:30:00']);$cutoff='2026-03-25 09:30:00';$date='2026-03-23';
        $input=(new \App\Application\MarketData\Services\AsKnownReplaySnapshotService())->capture($date,$cutoff);
        $dir=(getenv('B19_E049_STATE_DIR')?:sys_get_temp_dir().'/e049-result-'.bin2hex(random_bytes(6))).'/as_known_unavailable_authority_control';mkdir($dir.'/expected',0775,true);
        file_put_contents($dir.'/manifest.json',json_encode(['fixture_id'=>'author-unavailable-input-control','fixture_family'=>'unqualified_author_negative_control',
            'replay_mode'=>'AS_KNOWN','assertion_scope'=>'CANONICAL_RAW','trade_date'=>$date,'knowledge_cutoff'=>$cutoff]));
        file_put_contents($dir.'/expected/as_known_snapshot.json',json_encode($input));
        file_put_contents($dir.'/expected/as_known_execution.json',json_encode(['execution_scope'=>'CANONICAL_RAW','canonical_output_hash'=>str_repeat('0',64)]));
        $source=$this->sourceState();$result=$this->verifier()->verifyAsKnownAgainstFixture($world['run_id'],$dir,$cutoff);
        $this->retainResult('as_known_missing_proof_blocked',compact('input','result','source'));
        $this->assertSame('BLOCKED',$result['replay_status']);$this->assertSame('NOT_ADMISSIBLE',$result['comparison_result']);
        $this->assertNull($result['artifact_changed_scope'],'An unexecuted replay cannot assert that no artifact changed.');
        $this->assertNull($result['actual_context']['executed_replay']);$this->assertFalse($result['fresh_comparison_performed']);
        $this->assertSame('REPLAY_RESULT_ONLY',$result['writer_kind']);$this->assertSame($source,$this->sourceState());
        $before=$this->resultState();$error=null;
        try{\App\Infrastructure\Persistence\MarketData\ReplayIsolation::run(function()use($world,$dir,$cutoff){
            return $this->verifier()->verifyAsKnownAgainstFixture($world['run_id'],$dir,$cutoff);
        });}catch(RuntimeException $e){$error=$e->getMessage();}
        $this->assertStringContainsString('REPLAY_RESULT_AUTHORITY_ISOLATION_FORBIDDEN',(string)$error);
        $this->assertSame($before,$this->resultState(),'Result authority cannot be minted inside rollback-only projection.');
    }

    public function test_result_actor_on_independent_connection_cannot_borrow_a_live_publication_owners_model(): void
    {
        $world=R0025SyntheticV2World::build();$fixture=$this->fixture($world);$runs=new \App\Infrastructure\Persistence\MarketData\EodRunRepository();
        $owner=$runs->createPromoteRunFromSeed(\App\Models\EodRun::findOrFail($world['run_id']),'HASH');
        $source=$this->sourceState();$refusals=[];$end=null;
        try{$this->onIndependentOwnerConnection(function()use($world,$fixture,$owner,&$refusals){
            return (new ReplayResultRepository())->duringVerifiedRequest($world['run_id'],$fixture,'PUBLICATION_EXACT',$world['publication_id'],null,null,
                function()use($owner,$world,&$refusals){
                    $publications=new EodPublicationRepository();$runs=new \App\Infrastructure\Persistence\MarketData\EodRunRepository();
                    foreach([
                        'event'=>fn()=>$runs->appendEvent($owner,'HASH','DIAGNOSTIC','INFO','Result authority is not publication authority.'),
                        'candidate'=>fn()=>$publications->getOrCreateCandidatePublication($owner),
                        'pointer'=>fn()=>$publications->clearCurrentPublicationState($owner->trade_date_requested,$owner),
                        'hash'=>fn()=>$publications->updateCandidateHashes($world['publication_id'],['bars_batch_hash'=>str_repeat('a',64)],$owner),
                    ]as $kind=>$attempt){$error=null;try{$attempt();}catch(RuntimeException $e){$error=$e->getMessage();}$refusals[$kind]=$error;}
                    throw new RuntimeException('END_RESULT_ROLE_REFUSAL_CONTROL');
                });
        });}catch(RuntimeException $e){$end=$e->getMessage();}
        $this->retainResult('result_role_cannot_borrow_live_owner',compact('source','refusals','end'));
        $this->assertSame('END_RESULT_ROLE_REFUSAL_CONTROL',$end);
        foreach($refusals as $kind=>$error)$this->assertStringContainsString('RUN_OWNER_CONFLICT',(string)$error,$kind);
        $this->assertSame($source,$this->sourceState());
        $runs->appendEvent($owner,'HASH','DIAGNOSTIC','INFO','Actual incumbent execution still owns its permitted event path.');
        $this->assertSame(1,DB::table('eod_run_events')->where('run_id',$owner->run_id)->where('message','Actual incumbent execution still owns its permitted event path.')->count());
    }

    public function test_two_real_workers_contend_on_allocator_and_both_commit_distinct_owned_results(): void
    {
        $world=R0025SyntheticV2World::build();$fixture=$this->fixture($world);
        $this->verifier()->verifyRunAgainstFixture($world['run_id'],$fixture,null,$world['publication_id']);
        $source=$this->sourceState();$workers=[];$waits=[];$outcomes=[];$controlError=null;
        DB::beginTransaction();DB::table('md_replay_result_sequences')->where('sequence_key','replay')->lockForUpdate()->first();
        try{
            for($i=0;$i<2;$i++){
                $pipes=[];$process=proc_open([PHP_BINARY,base_path('tests/Support/MarketDataMariaDbReplayResultWorker.php'),DB::connection()->getDatabaseName(),
                    (string)$world['run_id'],(string)$world['publication_id'],$fixture],[0=>['pipe','r'],1=>['pipe','w'],2=>['pipe','w']],$pipes,base_path());
                $this->assertIsResource($process);fclose($pipes[0]);$ready=trim(fgets($pipes[1]));$this->assertMatchesRegularExpression('/^READY [0-9]+$/',$ready);
                $workers[]=[$process,$pipes,(int)substr($ready,6)];
            }
            foreach($workers as [$process,$pipes])$this->assertSame("WAITING_SEQUENCE\n",fgets($pipes[1]));
            $ids=array_column($workers,2);
            for($i=0;$i<100;$i++){
                $waits=DB::select("SELECT trx_mysql_thread_id,trx_state,trx_query FROM information_schema.INNODB_TRX WHERE trx_state='LOCK WAIT' AND trx_mysql_thread_id IN (".implode(',',$ids).")");
                if(count($waits)===2)break;usleep(100000);
            }
            $this->assertCount(2,$waits,'Both real independent connections must actually wait on the held allocator key.');
        }catch(Throwable $e){$controlError=$e;}finally{DB::commit();}
        foreach($workers as [$process,$pipes,$connection]){
            $text=stream_get_contents($pipes[1]);$stderr=stream_get_contents($pipes[2]);fclose($pipes[1]);fclose($pipes[2]);$exit=proc_close($process);
            $outcomes[]=['exit'=>$exit,'stdout'=>$text,'stderr'=>$stderr,'connection'=>$connection,'result'=>json_decode(trim($text),true)];
        }
        $this->retainResult('two_worker_allocator_contention',compact('waits','outcomes','source'));
        if($controlError)throw $controlError;
        foreach($outcomes as $outcome){$this->assertSame(0,$outcome['exit'],$outcome['stdout'].$outcome['stderr']);
            $this->assertIsArray($outcome['result'],$outcome['stdout']);$this->assertSame('COMPLETED',$outcome['result']['outcome']);}
        $this->assertNotSame($outcomes[0]['result']['replay_id'],$outcomes[1]['result']['replay_id']);
        $this->assertNotSame($outcomes[0]['result']['writer_execution_id'],$outcomes[1]['result']['writer_execution_id']);
        $this->assertSame(3,DB::table('md_replay_result_cases')->where('state','COMPLETED')->count());
        $this->assertSame($source,$this->sourceState());
    }

    public function test_attempt_audit_and_failure_cleanup_write_before_owner_release_in_the_same_transaction(): void
    {
        $world=R0025SyntheticV2World::build();$fixture=$this->fixture($world);$result=null;$error=null;
        DB::unprepared("CREATE TRIGGER e049_result_audit_owner_guard BEFORE UPDATE ON md_replay_result_attempts FOR EACH ROW BEGIN
            DECLARE owner_state VARCHAR(24); DECLARE owner_release DATETIME; DECLARE owner_token BIGINT; DECLARE owner_execution CHAR(32); DECLARE owner_expiry DATETIME;
            SELECT state,released_at,fencing_token,execution_id,expires_at INTO owner_state,owner_release,owner_token,owner_execution,owner_expiry
                FROM md_replay_result_cases WHERE replay_id=NEW.replay_id;
            IF NEW.state IN ('COMPLETED','FAILED') AND (owner_state <> 'RUNNING' OR owner_release IS NOT NULL OR owner_token <> NEW.fencing_token
                OR owner_execution <> NEW.execution_id OR owner_expiry <= NEW.finished_at) THEN
                SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT='RESULT_OWNER_RELEASED_BEFORE_AUDIT';
            END IF;
            IF NEW.state='EXPIRED' AND (owner_state <> 'RUNNING' OR owner_release IS NOT NULL OR owner_token <= NEW.fencing_token
                OR owner_execution=NEW.execution_id OR owner_expiry <= NEW.finished_at) THEN
                SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT='RESULT_TAKEOVER_ACTOR_NOT_CURRENT_AT_AUDIT';
            END IF;
        END");
        try{
            try{$result=$this->verifier()->verifyRunAgainstFixture($world['run_id'],$fixture,null,$world['publication_id']);}
            catch(RuntimeException $e){$error=$e->getMessage();}
            $this->retainResult('attempt_completion_write_time_owner',compact('result','error'));
            $this->assertNull($error,'Finalizer audit must retain current authority until its actual write is complete.');
            $this->assertSame('COMPLETED',DB::table('md_replay_result_cases')->where('replay_id',$result['replay_id'])->value('state'));
            DB::unprepared("CREATE TRIGGER e049_result_failure_owner_probe BEFORE INSERT ON md_replay_reason_code_counts FOR EACH ROW SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT='INJECTED_RESULT_COUNT_FAILURE'");
            $failure=null;try{$this->verifier()->verifyRunAgainstFixture($world['run_id'],$fixture,null,$world['publication_id']);}
            catch(RuntimeException $e){$failure=$e->getMessage();}finally{DB::unprepared('DROP TRIGGER IF EXISTS e049_result_failure_owner_probe');}
            $failed=DB::table('md_replay_result_cases')->orderByDesc('replay_id')->first();
            $this->retainResult('attempt_failure_cleanup_write_time_owner',compact('failed','failure'));
            $this->assertStringContainsString('INJECTED_RESULT_COUNT_FAILURE',(string)$failure);
            $this->assertSame('FAILED',$failed->state,'Failure telemetry must precede release and complete coherently.');
            $this->assertSame('FAILED',DB::table('md_replay_result_attempts')->where('execution_id',$failed->execution_id)->value('state'));
        }finally{DB::unprepared('DROP TRIGGER IF EXISTS e049_result_audit_owner_guard');}
    }
    public function test_explicit_immutable_publication_in_fixture_manifest_remains_a_valid_selector_without_an_api_override(): void
    {
        $world=R0025SyntheticV2World::build();$fixture=$this->fixture($world);$source=$this->sourceState();
        $manifest=json_decode(file_get_contents($fixture.'/manifest.json'),true);
        $this->assertSame((int)$world['publication_id'],(int)$manifest['publication_id']);
        $result=null;$error=null;
        try{$result=$this->verifier()->verifyRunAgainstFixture($world['run_id'],$fixture);}
        catch(RuntimeException $e){$error=$e->getMessage();}
        $this->retainResult('manifest_explicit_publication_selector',compact('result','error','manifest','source'));
        $this->assertNull($error,'An explicit immutable fixture selector must not require a redundant caller override.');
        $this->assertSame('BLOCKED',$result['replay_status']);
        $this->assertSame((int)$world['publication_id'],(int)$result['publication_id']);
        $this->assertSame($source,$this->sourceState());
    }

    public function test_result_authority_schema_rollback_is_reversible_only_before_any_attributable_evidence_exists(): void
    {
        $migration=new AddDurableReplayResultAuthority();$schema=DB::connection()->getSchemaBuilder();
        $migration->down();
        foreach(['md_replay_result_sequences','md_replay_result_cases','md_replay_result_attempts']as $table)$this->assertFalse($schema->hasTable($table));
        $migration->up();
        $world=R0025SyntheticV2World::build();$fixture=$this->fixture($world);
        $result=$this->verifier()->verifyRunAgainstFixture($world['run_id'],$fixture,null,$world['publication_id']);
        $before=$this->resultState();$error=null;
        try{$migration->down();}catch(RuntimeException $e){$error=$e->getMessage();}
        $this->assertStringContainsString('REPLAY_RESULT_DOWNGRADE_REQUIRES_GOVERNED_EVIDENCE_DISPOSITION',(string)$error);
        $this->assertSame($before,$this->onIndependentOwnerConnection(fn()=>$this->resultState()));
        $this->retainResult('migration_rollback_evidence_preserved',compact('error','before','result'));
    }

    public function test_result_lock_retains_acquired_heartbeat_and_expiry_without_reviving_or_extending_its_lease(): void
    {
        $world=R0025SyntheticV2World::build();$fixture=$this->fixture($world);$source=$this->sourceState();$advanced=false;
        DB::listen(function($event)use(&$advanced){
            if(!$advanced && strpos(strtolower($event->sql),'insert into `md_replay_result_attempts`')===0){
                $advanced=true;\Carbon\Carbon::setTestNow(\Carbon\Carbon::now(config('market_data.platform.timezone'))->addSeconds(30));
            }
        });
        $result=$this->verifier()->verifyRunAgainstFixture($world['run_id'],$fixture,null,$world['publication_id']);
        $case=DB::table('md_replay_result_cases')->where('replay_id',$result['replay_id'])->first();
        $this->retainResult('result_lock_heartbeat_actual_verified_progress',compact('case','result','advanced','source'));
        $this->assertTrue($advanced,'The clock control must reach the actual new attempt INSERT.');
        $this->assertNotNull($case->heartbeat_at??null,'A replay-result lock must retain its authenticated heartbeat timestamp.');
        $this->assertSame(30,\Carbon\Carbon::parse($case->acquired_at)->diffInSeconds(\Carbon\Carbon::parse($case->heartbeat_at)));
        $this->assertSame(300,\Carbon\Carbon::parse($case->acquired_at)->diffInSeconds(\Carbon\Carbon::parse($case->expires_at)),'Progress cannot implicitly extend a lease.');
        $this->assertSame($case->released_at,$case->heartbeat_at);
        $this->assertSame($source,$this->sourceState());
    }

    public function test_competing_real_worker_waits_on_the_actual_metric_transaction_and_can_only_read_completed_result(): void
    {
        $world=R0025SyntheticV2World::build();$fixture=$this->fixture($world);$source=$this->sourceState();
        $worker=null;$pipes=[];$workerId=null;$waits=[];$visible=null;$observed=false;$controlError=null;
        DB::listen(function($event)use($world,$fixture,&$worker,&$pipes,&$workerId,&$waits,&$visible,&$observed,&$controlError){
            if($observed || !preg_match('/^insert into `md_replay_daily_metrics`/i',$event->sql))return;
            $observed=true;
            try{
                $id=DB::table('md_replay_result_cases')->value('replay_id');
                $worker=proc_open([PHP_BINARY,base_path('tests/Support/MarketDataMariaDbReplayResultWorker.php'),DB::connection()->getDatabaseName(),
                    (string)$world['run_id'],(string)$world['publication_id'],$fixture,(string)$id],
                    [0=>['pipe','r'],1=>['pipe','w'],2=>['pipe','w']],$pipes,base_path());
                if(!is_resource($worker))throw new RuntimeException('RESULT_CASE_WORKER_START_FAILED');
                fclose($pipes[0]);$ready=trim(fgets($pipes[1]));
                if(!preg_match('/^READY ([0-9]+)$/',$ready,$match))throw new RuntimeException('RESULT_CASE_WORKER_READY_INVALID: '.$ready);
                $workerId=(int)$match[1];$barrier=fgets($pipes[1]);
                if($barrier!=="WAITING_CASE\n")throw new RuntimeException('RESULT_CASE_WORKER_BARRIER_INVALID: '.$barrier);
                for($n=0;$n<100;$n++){
                    $waits=DB::select("SELECT trx_mysql_thread_id,trx_state,trx_query FROM information_schema.INNODB_TRX WHERE trx_state='LOCK WAIT' AND trx_mysql_thread_id=".$workerId);
                    if(count($waits)===1)break;usleep(100000);
                }
                $visible=$this->onIndependentOwnerConnection(fn()=>$this->resultState());
            }catch(Throwable $error){$controlError=$error;}
        });
        $result=null;$error=null;
        try{$result=$this->verifier()->verifyRunAgainstFixture($world['run_id'],$fixture,null,$world['publication_id']);}
        catch(Throwable $e){$error=$e;}
        $outcome=null;
        if(is_resource($worker)){
            $stdout=stream_get_contents($pipes[1]);$stderr=stream_get_contents($pipes[2]);fclose($pipes[1]);fclose($pipes[2]);$exit=proc_close($worker);
            $outcome=compact('exit','stdout','stderr')+['result'=>json_decode(trim($stdout),true)];
        }
        $this->retainResult('actual_metric_transaction_case_contention',compact('result','workerId','waits','visible','outcome','observed')+
            ['control_error'=>$controlError?(string)$controlError:null,'runtime_error'=>$error?(string)$error:null]);
        if($controlError)throw $controlError;if($error)throw $error;
        $this->assertTrue($observed);$this->assertCount(1,$waits,'The actual result case lock must cover the metric/count/completion transaction.');
        $this->assertSame('[]',$visible['md_replay_daily_metrics'],'The independent reader must not see the uncommitted metric.');
        $this->assertSame('[]',$visible['md_replay_reason_code_counts']);
        $this->assertSame(0,$outcome['exit'],$outcome['stdout'].$outcome['stderr']);
        $this->assertIsArray($outcome['result']);$this->assertTrue($outcome['result']['result_reused']);
        $this->assertSame($result['replay_id'],$outcome['result']['replay_id']);
        $this->assertSame($result['writer_execution_id'],$outcome['result']['writer_execution_id']);
        $this->assertSame(1,DB::table('md_replay_result_attempts')->count(),'A waiting read-only retry obtains no new actor or publication rights.');
        $this->assertSame($source,$this->sourceState());
    }

    public function test_replay_command_preserves_explicit_invalid_identifiers_instead_of_silently_allocating_or_retargeting(): void
    {
        $world=R0025SyntheticV2World::build();$fixture=$this->fixture($world);$source=$this->sourceState();
        app()->instance(ReplayVerificationService::class,$this->verifier());
        $baseline=$this->verifier()->verifyRunAgainstFixture($world['run_id'],$fixture,null,$world['publication_id']);
        $base=['run_id'=>$world['run_id'],'fixture_path'=>$fixture,'--mode'=>'PUBLICATION_EXACT','--publication_id'=>$world['publication_id']];
        $cases=[];
        foreach([
            'explicit_zero'=>[['--replay_id'=>'0'],'REPLAY_RESULT_OWNER_SCOPE_INVALID'],
            'negative'=>[['--replay_id'=>'-1'],'REPLAY_RESULT_OWNER_SCOPE_INVALID'],
            'explicit_empty'=>[['--replay_id'=>''],'REPLAY_RESULT_OWNER_SCOPE_INVALID'],
            'explicit_no_value'=>[['--replay_id'=>null],'REPLAY_RESULT_OWNER_SCOPE_INVALID'],
            'decimal'=>[['--replay_id'=>'1.0'],'REPLAY_RESULT_OWNER_SCOPE_INVALID'],
            'trailing_replay_text'=>[['--replay_id'=>$baseline['replay_id'].'-wrong'],'REPLAY_RESULT_OWNER_SCOPE_INVALID'],
            'overflow'=>[['--replay_id'=>'9223372036854775808'],'REPLAY_RESULT_OWNER_SCOPE_INVALID'],
            'trailing_run_text'=>[['run_id'=>$world['run_id'].'-wrong'],'COMMAND_MISSING_REQUIRED_INPUT'],
            'trailing_publication_text'=>[['--publication_id'=>$world['publication_id'].'-wrong'],'REPLAY_EXPLICIT_PUBLICATION_REQUIRED'],
            'unknown_positive_selector'=>[['--replay_id'=>'99999999'],'REPLAY_RESULT_OWNER_ID_UNKNOWN'],
        ]as $label=>[$override,$expectedReason]){
            $before=$this->resultState();$command=new \App\Console\Commands\MarketData\VerifyReplayCommand();$command->setLaravel(app());
            $tester=new \Symfony\Component\Console\Tester\CommandTester($command);
            $exit=$tester->execute(array_merge($base,$override));$output=$tester->getDisplay();
            preg_match('/^reason_code=([A-Z0-9_]+)/m',$output,$match);$actualReason=$match[1]??null;
            $after=$this->onIndependentOwnerConnection(fn()=>$this->resultState());
            $cases[]=compact('label','override','before','after','exit','output','expectedReason','actualReason');
        }
        $this->retainResult('command_explicit_identifier_validation',compact('cases','baseline','source'));
        foreach($cases as $case){
            $this->assertSame(1,$case['exit']);
            $this->assertSame($case['expectedReason'],$case['actualReason'],'An invalid explicit identifier must be refused before acquisition: '.$case['label']);
            $this->assertSame($case['before'],$case['after'],'An invalid explicit identifier cannot allocate or mutate a result: '.$case['label']);
        }
        $before=$this->resultState();$command=new \App\Console\Commands\MarketData\VerifyReplayCommand();$command->setLaravel(app());
        $tester=new \Symfony\Component\Console\Tester\CommandTester($command);
        $exit=$tester->execute($base+['--replay_id'=>(string)$baseline['replay_id']]);
        $this->assertSame(1,$exit,'The real self-generated comparison remains BLOCKED.');
        $this->assertStringContainsString('result_reused=true',$tester->getDisplay());$this->assertStringContainsString('fresh_comparison_performed=false',$tester->getDisplay());$this->assertSame($before,$this->resultState());
        $freshCommand=new \App\Console\Commands\MarketData\VerifyReplayCommand();$freshCommand->setLaravel(app());
        $fresh=new \Symfony\Component\Console\Tester\CommandTester($freshCommand);$freshExit=$fresh->execute($base);
        $this->assertSame(1,$freshExit);$this->assertStringContainsString('writer_kind=REPLAY_RESULT_ONLY',$fresh->getDisplay());
        $this->assertSame(2,DB::table('md_replay_result_cases')->count(),'Omitting the selector explicitly requests one legitimate fresh execution.');
        $this->assertSame($source,$this->sourceState());
        $this->retainResult('command_valid_readonly_retry_and_fresh_request',compact('baseline','source')+['retry_output'=>$tester->getDisplay(),'fresh_output'=>$fresh->getDisplay()]);
    }

    public function test_result_entry_rejects_ambiguous_raw_target_identity_before_case_allocation(): void
    {
        $world=R0025SyntheticV2World::build();$fixture=$this->fixture($world);$source=$this->sourceState();$verifier=$this->verifier();
        $baseline=$verifier->verifyRunAgainstFixture($world['run_id'],$fixture,null,$world['publication_id']);$cases=[];
        foreach(['run_suffix','publication_suffix','run_decimal','publication_decimal','boolean_replay_selector','fixture_boolean_selector']as $variant){
            $run=$world['run_id'];$publication=$world['publication_id'];$replay=null;$original=file_get_contents($fixture.'/manifest.json');
            if($variant==='run_suffix')$run.='-wrong';if($variant==='publication_suffix')$publication.='-wrong';
            if($variant==='run_decimal')$run.='.0';if($variant==='publication_decimal')$publication.='.0';
            if($variant==='boolean_replay_selector')$replay=true;
            if($variant==='fixture_boolean_selector'){$publication=null;$manifest=json_decode($original,true);$manifest['publication_id']=true;file_put_contents($fixture.'/manifest.json',json_encode($manifest));}
            $before=$this->resultState();$error=null;$result=null;
            try{$result=$verifier->verifyRunAgainstFixture($run,$fixture,$replay,$publication);}
            catch(RuntimeException $e){$error=$e->getMessage();}
            finally{file_put_contents($fixture.'/manifest.json',$original);}
            $after=$this->onIndependentOwnerConnection(fn()=>$this->resultState());
            $cases[]=compact('variant','run','publication','replay','before','after','error','result');
        }
        $this->retainResult('raw_target_identity_validation',compact('cases','baseline','source'));
        foreach($cases as $case){
            $this->assertNotNull($case['error'],'A raw invalid target must not be coerced into an existing identity: '.$case['variant']);
            $this->assertSame($case['before'],$case['after'],'Invalid target identity cannot allocate or mutate a result: '.$case['variant']);
        }
        $this->assertSame($source,$this->sourceState());
    }

}
