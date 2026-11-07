<?php

require_once __DIR__.'/B19ReplayScopeContinuationMariaDbTest.php';
use App\Application\MarketData\Services\MarketDataEvidenceExportService;
use App\Application\MarketData\Services\ReplayVerificationService;
use App\Console\Commands\MarketData\VerifyReplayCommand;
use Illuminate\Support\Facades\DB;
use Symfony\Component\Console\Tester\CommandTester;

/** Current-source diagnostic contract, never a candidate-v6 or qualified-build admission. */
class B19ReplayArtifactScopeMariaDbTest extends B19ReplayScopeContinuationMariaDbTest
{
    protected function identityFixtureVersion(bool $companion): string { return 'b19-current-source-scope-diagnostic-20261026'; }
    private function immutableState(): array
    {
        $out=[];
        foreach(['eod_runs','eod_publications','eod_current_publication_pointer','eod_bars','eod_bars_history','eod_indicators','eod_indicators_history','eod_eligibility','eod_eligibility_history','md_publication_lineage_bindings','md_run_input_captures','md_source_observations','md_source_observation_rows']as $table){
            $rows=array_map(fn($r)=>(array)$r,DB::table($table)->get()->all());usort($rows,fn($a,$b)=>strcmp(json_encode($a),json_encode($b)));$out[$table]=$rows;
        }
        return $out;
    }
    protected function afterIdentityRun($verifier,$runId,$fixture,$publicationId,$date,array $expected,array $manifest,array $files,array $world,$replay): void
    {
        $dir=getenv('B19_E049_STATE_DIR');$before=$this->immutableState();$cases=[];
        app()->instance(ReplayVerificationService::class,$verifier);
        foreach([0,1,2,4,3,5,6,7]as $mask){
            $probe=$expected;$labels=[];
            foreach(['bars','indicators','eligibility']as $bit=>$name)if($mask&(1<<$bit)){
                $field=$name.'_batch_hash';$this->assertNotSame(str_repeat('0',64),$probe['expected_artifact_context'][$field]);
                $probe['expected_artifact_context'][$field]=str_repeat('0',64);$labels[]=$name;
            }
            $scope=$mask===0?'none':(count($labels)===1?$labels[0].'_only':'multi_artifact');
            $variant=$files;$variant['expected_replay_result.json']=$probe;
            $variant['expected_hashes.json']=array_intersect_key($probe['expected_artifact_context'],array_flip(['bars_batch_hash','indicators_batch_hash','eligibility_batch_hash']));
            $variant['expected_run_summary.json']=$probe['expected_artifact_context'];$current=$manifest;$current['fixture_version']='b19-scope-counterfactual-'.$mask.'-20261026';
            foreach($variant as $name=>$body)file_put_contents($fixture.'/expected/'.$name,json_encode($body,JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES));
            foreach($current['files']as $name)$current['files_sha256'][$name]=hash_file('sha256',$fixture.'/'.$name);
            file_put_contents($fixture.'/manifest.json',json_encode($current));
            $metric=$verifier->verifyRunAgainstFixture($runId,$fixture,null,$publicationId);
            $this->assertSame($scope,$metric['artifact_changed_scope'],'Scope must come from the independently chosen changed hash members');
            $this->assertSame($mask===0?'MATCH':'MISMATCH',$metric['comparison_result']);$this->assertSame($mask===0?'PASS':'FAIL',$metric['replay_status']);
            $row=DB::table('md_replay_daily_metrics')->where('replay_id',$metric['replay_id'])->where('trade_date',$date)->first();
            $this->assertSame($scope,$row->artifact_changed_scope);$this->assertSame($metric['comparison_result'],$row->comparison_result);
            $out=$dir.'/SCOPE_'.$mask;app(MarketDataEvidenceExportService::class)->exportReplayEvidence($metric['replay_id'],$date,$out);
            $export=json_decode(file_get_contents($out.'/replay_result.json'),true);$this->assertSame($scope,$export['artifact_changed_scope']);
            $command=new VerifyReplayCommand();$command->setLaravel(app());$tester=new CommandTester($command);
            $exit=$tester->execute(['run_id'=>$runId,'fixture_path'=>$fixture,'--mode'=>'PUBLICATION_EXACT','--publication_id'=>$publicationId]);
            $this->assertSame($mask===0?0:1,$exit);$this->assertContains('artifact_changed_scope='.$scope,preg_split('/\r\n|\r|\n/',$tester->getDisplay()));
            $this->assertStringContainsString('comparison_result='.($mask===0?'MATCH':'MISMATCH'),$tester->getDisplay());
            $this->assertSame($before,$this->immutableState(),'Replay diagnosis changed published/source state');
            $cases[]=['mask'=>$mask,'independently_selected_artifacts'=>$labels,'expected_scope'=>$scope,'metric'=>$row,'cli_exit'=>$exit,'cli_output'=>$tester->getDisplay()];
        }
        file_put_contents($dir.'/SCOPE_cases.json',json_encode($cases,JSON_PRETTY_PRINT));
        $boundaries=[];
        $rules=['explicit_null'=>['bars_only','MISMATCH','FAIL',1],
            'missing_expected_member'=>[null,'NOT_ADMISSIBLE','BLOCKED',1],
            'null_with_fallback_match'=>['none','MATCH','PASS',0],
            'null_with_fallback_mismatch'=>['bars_only','MISMATCH','FAIL',1]];
        foreach($rules as $case=>$rule){
            $probe=$expected;$variant=$files;
            if($case==='missing_expected_member')unset($probe['expected_artifact_context']['bars_batch_hash']);
            else $probe['expected_artifact_context']['bars_batch_hash']=null;
            $variant['expected_replay_result.json']=$probe;
            $variant['expected_run_summary.json']=$probe['expected_artifact_context'];
            $variant['expected_hashes.json']=array_intersect_key($probe['expected_artifact_context'],array_flip(['bars_batch_hash','indicators_batch_hash','eligibility_batch_hash']));
            if($case==='null_with_fallback_match')$variant['expected_hashes.json']['bars_batch_hash']=$expected['expected_artifact_context']['bars_batch_hash'];
            if($case==='null_with_fallback_mismatch')$variant['expected_hashes.json']['bars_batch_hash']=str_repeat('0',64);
            $current=$manifest;$current['fixture_version']='b19-scope-boundary-'.$case.'-20261029';
            foreach($variant as $name=>$body)file_put_contents($fixture.'/expected/'.$name,json_encode($body,JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES));
            foreach($current['files']as $name)$current['files_sha256'][$name]=hash_file('sha256',$fixture.'/'.$name);
            file_put_contents($fixture.'/manifest.json',json_encode($current));
            $metric=$verifier->verifyRunAgainstFixture($runId,$fixture,null,$publicationId);
            $row=DB::table('md_replay_daily_metrics')->where('replay_id',$metric['replay_id'])->where('trade_date',$date)->first();
            app(MarketDataEvidenceExportService::class)->exportReplayEvidence($metric['replay_id'],$date,$dir.'/SCOPE_'.$case);
            $export=json_decode(file_get_contents($dir.'/SCOPE_'.$case.'/replay_result.json'),true);
            $command=new VerifyReplayCommand();$command->setLaravel(app());$tester=new CommandTester($command);
            $exit=$tester->execute(['run_id'=>$runId,'fixture_path'=>$fixture,'--mode'=>'PUBLICATION_EXACT','--publication_id'=>$publicationId]);
            $boundaries[]=compact('case','rule','metric','row','export','exit')+['cli_output'=>$tester->getDisplay()];
        }
        file_put_contents($dir.'/SCOPE_unavailable_boundaries.json',json_encode($boundaries,JSON_PRETTY_PRINT));
        foreach($boundaries as $boundary){
            [$scope,$comparison,$status,$exit]=$boundary['rule'];
            $this->assertSame($comparison,$boundary['metric']['comparison_result']);
            $this->assertSame($status,$boundary['metric']['replay_status']);
            $this->assertSame($scope,$boundary['metric']['artifact_changed_scope'],'Hash comparison and scope must use the same resolved inputs; missing proof is unverified, never unchanged.');
            $this->assertSame($scope,$boundary['row']->artifact_changed_scope);$this->assertSame($scope,$boundary['export']['artifact_changed_scope']);
            $this->assertSame($exit,$boundary['exit']);$this->assertContains('artifact_changed_scope='.($scope??''),preg_split('/\r\n|\r|\n/',$boundary['cli_output']));
        }
        $after=$this->immutableState();$this->assertSame($before,$after);
        file_put_contents($dir.'/SCOPE_immutable_before_after.json',json_encode(compact('before','after'),JSON_PRETTY_PRINT));
    }
    public function test_current_build_all_artifact_scope_combinations_reach_metric_export_and_command(): void
    {
        $this->assertMatchesRegularExpression('/^sha256:[a-f0-9]{64}$/',(string)getenv('B19_INDEPENDENT_BUILD_ID'),'Independent source-input reference must precede target construction');
        parent::test_all_identity_paths_follow_independent_inputs_and_persisted_publication_binding();
    }
}
