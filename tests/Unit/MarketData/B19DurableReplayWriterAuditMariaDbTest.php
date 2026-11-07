<?php

use App\Infrastructure\Persistence\MarketData\ReplayResultRepository;
use Illuminate\Support\Facades\DB;
use Tests\Support\R0025SyntheticV2World;
require_once __DIR__.'/B19OwnerFenceMariaDbTest.php';

/** Original five persisted controls adapted to the real D011 issuer; old RED source/evidence retained. */
class B19DurableReplayWriterAuditMariaDbTest extends B19OwnerFenceMariaDbTest
{
    protected function tearDown(): void { \Carbon\Carbon::setTestNow();parent::tearDown(); }
    private function state(): array
    {
        return ['metrics'=>DB::table('md_replay_daily_metrics')->orderBy('replay_id')->orderBy('trade_date')->get()->all(),
            'counts'=>DB::table('md_replay_reason_code_counts')->orderBy('replay_id')->orderBy('trade_date')->orderBy('reason_code')->get()->all()];
    }
    private function publicationState(): array
    {
        $out=[];
        foreach(['eod_runs','eod_publications','eod_current_publication_pointer','eod_run_events','eod_bars_history','eod_indicators_history','eod_eligibility_history','md_run_input_captures']as $table)
            $out[$table]=DB::table($table)->get()->toJson();
        return $out;
    }
    private function callUnidentified(callable $work): ?string
    {
        try{$this->onIndependentOwnerConnection($work);return null;}catch(RuntimeException $error){return $error->getMessage();}
    }
    public function test_durable_replay_requires_scoped_actor_atomic_pair_and_noncolliding_allocation(): void
    {
        $world=R0025SyntheticV2World::build();$date=R0025SyntheticV2World::world()['trade_date'];$repo=new ReplayResultRepository();
        $baselineTriggers=DB::connection('mysql')->select("SELECT TRIGGER_NAME, EVENT_OBJECT_TABLE FROM information_schema.TRIGGERS WHERE EVENT_OBJECT_SCHEMA='tradeaxis_testing' AND EVENT_OBJECT_TABLE IN ('md_replay_daily_metrics','md_replay_reason_code_counts')");
        $this->assertSame([],$baselineTriggers,'The cloned replay schema must not omit a production mutation-denial trigger.');
        $sourceBefore=$this->publicationState();
        $metric=['replay_id'=>9001,'trade_date'=>$date,'run_id'=>(int)$world['run_id'],'publication_id'=>(int)$world['publication_id'],
            'source'=>'api','status'=>'SUCCESS','seal_state'=>'SEALED','replay_mode'=>'PUBLICATION_EXACT',
            'comparison_result'=>'NOT_ADMISSIBLE','replay_status'=>'BLOCKED','comparison_note'=>'TEST CONTROL: no replay/oracle or result-writer authority was supplied.'];
        require_once base_path('database/migrations/2026_10_29_000001_add_durable_replay_result_authority.php');
        if(!DB::connection()->getSchemaBuilder()->hasTable('md_replay_result_cases'))(new AddDurableReplayResultAuthority())->up();
        $verifier=new \App\Application\MarketData\Services\ReplayVerificationService(
            new \App\Infrastructure\Persistence\MarketData\EodEvidenceRepository(),
            new \App\Infrastructure\Persistence\MarketData\EodPublicationRepository(),$repo);
        $root=getenv('B19_E049_STATE_DIR')?:sys_get_temp_dir().'/e049-result-audit-'.bin2hex(random_bytes(8));
        $fixture=$root.'/actual-result-audit-fixture';
        $verifier->generateFixtureFromRun($world['run_id'],$fixture,'self_generated_result_writer_control',$world['publication_id']);
        $authorized=$verifier->verifyRunAgainstFixture($world['run_id'],$fixture,null,$world['publication_id']);
        $this->assertSame('BLOCKED',$authorized['replay_status'],'A self-generated control is not an independent admissible oracle.');
        $cases=[];
        $before=$this->state();$error=$this->callUnidentified(fn()=>$repo->upsertMetric($metric));$after=$this->state();
        $cases[]=['name'=>'unidentified_insert','before'=>$before,'after'=>$after,'error'=>$error,'must_refuse'=>true];
        $metric=$authorized;$metric['comparison_note']='TEST CONTROL: independent caller replaces the persisted context.';
        $before=$this->state();$error=$this->callUnidentified(fn()=>$repo->upsertMetric($metric));$after=$this->state();
        $cases[]=['name'=>'unidentified_overwrite','before'=>$before,'after'=>$after,'error'=>$error,'must_refuse'=>true];
        $reason='RUN_LOCK_CONFLICT';$this->assertTrue(DB::table('eod_reason_codes')->where('code',$reason)->exists());
        $before=$this->state();$error=$this->callUnidentified(fn()=>$repo->replaceReasonCodeCounts($authorized['replay_id'],$date,[['reason_code'=>$reason,'reason_count'=>999]]));$after=$this->state();
        $cases[]=['name'=>'unidentified_count_replace','before'=>$before,'after'=>$after,'error'=>$error,'must_refuse'=>true];
        $before=$this->state();$error=$this->callUnidentified(fn()=>$repo->nextReplayId());$after=$this->state();
        $cases[]=['name'=>'allocation_collision_overwrite','before'=>$before,'after'=>$after,'error'=>$error,'must_refuse'=>true,
            'concurrent_authorized_allocation_proof'=>'B19DurableReplayResultAuthorityMariaDbTest::test_two_real_workers_contend_on_allocator_and_both_commit_distinct_owned_results'];
        $before=$this->state();$error=null;
        DB::unprepared("CREATE TRIGGER replay_audit_count_failure BEFORE INSERT ON md_replay_reason_code_counts FOR EACH ROW SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT='INJECTED_REPLAY_COUNT_WRITE_FAILURE'");
        try{
            // Actual verifier owns the complete pair; the SQL failure happens at the second persisted family.
            $verifier->verifyRunAgainstFixture($world['run_id'],$fixture,null,$world['publication_id']);
        }catch(Throwable $failure){$error=$failure->getMessage();}
        finally{DB::unprepared('DROP TRIGGER IF EXISTS replay_audit_count_failure');}
        $after=$this->onIndependentOwnerConnection(fn()=>$this->state());
        $cases[]=['name'=>'compound_count_failure','before'=>$before,'after'=>$after,'error'=>$error,'must_rollback'=>true,
            'lifecycle'=>DB::table('md_replay_result_cases')->orderBy('replay_id')->get()->all()];
        $sourceAfter=$this->publicationState();$this->assertSame($sourceBefore,$sourceAfter,'Result audit must not alter source/publication state.');
        $dir=getenv('B19_E049_STATE_DIR');if($dir){if(!is_dir($dir))mkdir($dir,0775,true);
            file_put_contents($dir.'/RESULT_WRITER_AUDIT.json',json_encode(['database'=>DB::connection()->getDatabaseName(),
                'engine'=>DB::selectOne('SELECT VERSION() AS version')->version,'primary_connection_id'=>DB::selectOne('SELECT CONNECTION_ID() AS id')->id,
                'baseline_triggers'=>$baselineTriggers,'cases'=>$cases,'source_before'=>$sourceBefore,'source_after'=>$sourceAfter,
                'scope'=>'Persisted negative audit. Real internal verifier authorizes the baseline and fault-injected pair; self-generated outcomes are BLOCKED, not admitted replay evidence.'],JSON_PRETTY_PRINT));}
        foreach($cases as $case){
            if(!empty($case['must_refuse']))$this->assertTrue($case['error']!==null&&preg_match('/REPLAY_RESULT_(AUTHORITY|OWNER|FENCE)/',$case['error'])&&$case['before']==$case['after'],'Unauthenticated durable result write must be refused without mutation: '.$case['name']);
            elseif(!empty($case['must_rollback'])){$this->assertStringContainsString('INJECTED_REPLAY_COUNT_WRITE_FAILURE',(string)$case['error']);$this->assertEquals($case['before'],$case['after'],'Metric/count failure must roll back the complete result.');}
            else $this->assertTrue($case['safe'],'Racing replay selectors must not permit committed result replacement.');
        }
    }
}
