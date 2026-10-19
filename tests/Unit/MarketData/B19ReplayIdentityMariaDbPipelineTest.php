<?php

use App\Application\MarketData\Services\ReplayVerificationService;
use App\Application\MarketData\Services\MarketDataEvidenceExportService;
use App\Infrastructure\Persistence\MarketData\EodEvidenceRepository;
use App\Infrastructure\Persistence\MarketData\EodPublicationRepository;
use App\Infrastructure\Persistence\MarketData\EodCorrectionRepository;
use App\Infrastructure\Persistence\MarketData\ReplayResultRepository;
use Illuminate\Support\Facades\DB;
use Tests\Support\R0025SyntheticV2World;
use Tests\Support\UsesMarketDataMariaDb;
use Carbon\Carbon;

/** B19 identity contract probe; does not author or admit an R0025 successor candidate. */
class B19ReplayIdentityMariaDbPipelineTest extends TestCase
{
    use UsesMarketDataMariaDb;
    private $paths=[];
    protected function setUp(): void {parent::setUp();$this->bootMarketDataMariaDb();}
    protected function tearDown(): void {
        foreach($this->paths as $p) {
            if(!is_dir($p))continue;
            $this->assertSame(0,strpos(realpath($p),realpath(sys_get_temp_dir()).DIRECTORY_SEPARATOR));
            $it=new RecursiveIteratorIterator(new RecursiveDirectoryIterator($p,FilesystemIterator::SKIP_DOTS),RecursiveIteratorIterator::CHILD_FIRST);
            foreach($it as $f)$f->isDir()?rmdir($f->getPathname()):unlink($f->getPathname());rmdir($p);
        }
        Carbon::setTestNow();$this->tearDownMarketDataMariaDb();parent::tearDown();
    }
    private function canonical(array $v): string {ksort($v);return json_encode($v,JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE|JSON_PRESERVE_ZERO_FRACTION);}
    private function export(int $id,string $date): array {
        $dir=sys_get_temp_dir().'/b19_identity_export_'.uniqid();$this->paths[]=$dir;
        (new MarketDataEvidenceExportService(new EodEvidenceRepository(),new EodPublicationRepository(),new EodCorrectionRepository()))->exportReplayEvidence($id,$date,$dir);
        $out=[];foreach(['replay_result','replay_expected_state','replay_actual_state'] as $f)$out[$f]=json_decode(file_get_contents($dir.'/'.$f.'.json'),true);
        return [$out,$dir];
    }
    protected function prepareArtifactBindings(array $expected,array $oracle,array $navigation,$temporal): array { return $expected; }

    protected function afterIdentityRun($verifier,$runId,$fixture,$publicationId,$date,array $expected,array $manifest,array $files,array $world,$replay): void {}
    protected function identityWorld(): array { return R0025SyntheticV2World::build(); }
    protected function continueIdentityScope(): bool { return true; }

    public function test_all_identity_paths_follow_independent_inputs_and_persisted_publication_binding(): void
    {
        $package=R0025SyntheticV2World::packagePath();
        // Accepted independent semantic expectations are read before target construction.
        // This is a transient contract probe, not a new candidate or copied approval.
        $expected=json_decode(file_get_contents($package.'/expected/expected_replay_result.json'),true);
        $oracleManifest=json_decode(file_get_contents($package.'/expected/expected_publication_manifest.json'),true);
        $expected['expected_bound_input_context']['executable_build_identity']=getenv('B19_INDEPENDENT_BUILD_ID') ?: $expected['expected_bound_input_context']['executable_build_identity'];
        $frozenReasons=json_decode(file_get_contents($package.'/inputs/frozen_reason_registry.json'),true)['entries'];
        // Historical publication input registry, restored within this rolled-back test transaction.
        DB::table('eod_reason_codes')->delete();foreach(array_chunk($frozenReasons,100) as $chunk)DB::table('eod_reason_codes')->insert($chunk);
        $world=$this->identityWorld();
        $id=(int)$world['publication_id'];$runId=(int)$world['run_id'];$pub=DB::table('eod_publications')->where('publication_id',$id)->first();
        $run=DB::table('eod_runs')->where('run_id',$runId)->first();$date=$pub->trade_date;
        $lineage=DB::table('md_publication_lineage_bindings')->where('publication_id',$id)->first();
        $snapshot=DB::table('md_config_snapshots')->where('config_snapshot_id',$pub->config_snapshot_id)->first();
        $this->assertSame(hash('sha256',$snapshot->resolved_config_json),$snapshot->config_hash,'config content independently hashes to the selected snapshot identity');
        $this->assertSame($oracleManifest['publication_manifest_hash'],$pub->publication_manifest_hash,'independent pre-existing oracle manifest, not a verifier-generated expected manifest');
        $temporal=hash('sha256',$this->canonical(['trade_date'=>$date,'identity_revision_set_hash'=>$lineage->identity_revision_set_hash,'calendar_revision_set_hash'=>$lineage->calendar_revision_set_hash,'status_revision_set_hash'=>$lineage->status_revision_set_hash]));
        // Navigation allocations and the temporal input binding are derived from authoritative
        // immutable replay INPUTS, before replay, never from replay-produced result/context.
        $navigation=['run_id'=>$runId,'publication_id'=>$id,'factor_set_id'=>(int)$pub->factor_set_id,'config_snapshot_id'=>(int)$pub->config_snapshot_id,'artifact_scope'=>'publication:'.$id];
        $resolve=function($value)use(&$resolve,$navigation){if(is_array($value)){foreach($value as $k=>$v)$value[$k]=$resolve($v);return $value;}if(is_string($value)&&strpos($value,'@TARGET:')===0)return $navigation[substr($value,8)];return $value;};
        $expected=$resolve($expected);
        $expected['expected_publication_manifest_identity']=['schema_version'=>'replay_publication_manifest_identity_v1','applicability'=>'APPLICABLE','publication_id'=>$id,'publication_manifest_hash'=>$oracleManifest['publication_manifest_hash'],'temporal_revision_set_hash'=>$temporal,'formula_version'=>$oracleManifest['payload']['formula_version']];
        $expected=$this->prepareArtifactBindings($expected,$oracleManifest,$navigation,$temporal);
        $fixture=sys_get_temp_dir().'/b19_identity_fixture_'.uniqid();$this->paths[]=$fixture;mkdir($fixture.'/expected',0775,true);
        $files=['expected_replay_result.json'=>$expected,'expected_hashes.json'=>['bars_batch_hash'=>$expected['expected_artifact_context']['bars_batch_hash'],'indicators_batch_hash'=>$expected['expected_artifact_context']['indicators_batch_hash'],'eligibility_batch_hash'=>$expected['expected_artifact_context']['eligibility_batch_hash']], 'expected_run_summary.json'=>$expected['expected_artifact_context'],'expected_reason_code_counts.json'=>json_decode(file_get_contents($package.'/expected/expected_reason_code_counts.json'),true)];
        foreach($files as $name=>$body)file_put_contents($fixture.'/expected/'.$name,json_encode($body,JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES));
        $manifest=['fixture_id'=>'b19-publication-identity-contract','fixture_family'=>'b19_publication_identity_contract','fixture_version'=>isset($expected['expected_artifact_semantic_bindings'])?'b19-identity-companion-v1':'b19-identity-v1','fixture_schema_version'=>'replay_fixture_v2','fixture_source'=>'hand_authored_contract_literals','replay_mode'=>'PUBLICATION_EXACT','publication_id'=>$id,'fixture_created_at'=>'2026-10-18T00:00:00+07:00','contract_areas'=>['replay_verification','replay_identity'],'assertion_layers'=>['run','source','coverage','hash','publication','pointer','fallback','correction','lineage','replay'],'files'=>array_map(function($n){return 'expected/'.$n;},array_keys($files))];
        foreach($manifest['files'] as $name)$manifest['files_sha256'][$name]=hash_file('sha256',$fixture.'/'.$name);
        file_put_contents($fixture.'/manifest.json',json_encode($manifest));
        $verifier=new ReplayVerificationService(new EodEvidenceRepository(),new EodPublicationRepository(),new ReplayResultRepository());
        $metric=$verifier->verifyRunAgainstFixture($runId,$fixture,null,$id);$replay=(int)$metric['replay_id'];
        $this->assertNotSame('BLOCKED',$metric['replay_status'],json_encode($metric['mismatches']).' '.$metric['mismatch_summary']);
        $this->assertSame('MATCH',$metric['comparison_result']);$this->assertSame('PASS',$metric['replay_status']);
        [$json,$dir]=$this->export($replay,$date);$result=$json['replay_result'];$wanted=$json['replay_expected_state'];$actual=$json['replay_actual_state'];
        $row=DB::table('md_replay_daily_metrics')->where('replay_id',$replay)->where('trade_date',$date)->first();
        $identity=json_decode($row->actual_context_json,true)['actual_publication_manifest_identity'];
        $stable=['status'=>$expected['expected_final_state']['terminal_status'],'trade_date_effective'=>$expected['expected_run_context']['trade_date_effective'],'seal_state'=>$expected['expected_seal_context']['seal_state'],'publication_version'=>$expected['expected_publication_context']['publication_version'],'bars_batch_hash'=>$expected['expected_artifact_context']['bars_batch_hash'],'indicators_batch_hash'=>$expected['expected_artifact_context']['indicators_batch_hash'],'eligibility_batch_hash'=>$expected['expected_artifact_context']['eligibility_batch_hash'],'reason_code_counts'=>$files['expected_reason_code_counts.json']];
        foreach($stable as $field=>$value)foreach([$wanted,$actual] as $state)$this->assertSame($value,$state[$field],'earlier stable state-pair obligation revalidated: '.$field);
        $this->assertSame($expected['expected_publication_manifest_identity'],$identity);
        foreach([$result['actual_context']['actual_publication_manifest_identity'],$actual['actual_context']['actual_publication_manifest_identity'],$wanted['expected_context']['expected_publication_manifest_identity']] as $envelope)$this->assertSame($identity,$envelope);
        $this->assertNull($result['knowledge_cutoff_at'],'R0240 PUBLICATION_EXACT coordinate is publication, not a fabricated cutoff');
        $this->assertSame('PUBLICATION_EXACT',$wanted['replay_mode'],'R0267 independent manifest mode');$this->assertSame('PUBLICATION_EXACT',$actual['replay_mode'],'R0284 persisted actual mode');
        foreach(['source_observation_manifest_hash','config_snapshot_hash','read_model_version'] as $field) {
            $this->assertSame($expected['expected_bound_input_context'][$field],$wanted['expected_context']['expected_bound_input_context'][$field]);
            $this->assertSame($expected['expected_bound_input_context'][$field],$result['bound_inputs'][$field]);
            $this->assertSame($result['bound_inputs'][$field],$actual['actual_context']['actual_bound_input_context'][$field]);
        }
        $this->assertSame((int)$pub->config_snapshot_id,(int)$result['bound_inputs']['config_snapshot_id']);$this->assertSame($snapshot->config_hash,$result['bound_inputs']['config_snapshot_hash']);
        foreach(['factor_set_id','factor_set_hash','price_product_code','price_product_version'] as $field) {
            $value=$expected['expected_publication_context'][$field]??$expected['expected_lineage'][$field];
            $this->assertSame($value,$result['actual_context']['actual_publication_context'][$field],$field.' from frozen publication');
            $this->assertSame($value,$actual['actual_context']['actual_publication_context'][$field]);
            $this->assertSame($value,$wanted['expected_context']['expected_publication_context'][$field]??$wanted['expected_context']['expected_lineage'][$field]);
        }
        $this->assertNotSame($temporal,$result['bound_inputs']['temporal_identity_hash']);
        $this->assertNotSame($identity['formula_version'],$result['bound_inputs']['formula_registry_hash']);
        $this->assertNotSame($result['actual_context']['actual_publication_context']['factor_set_hash'],$result['bound_inputs']['event_factor_hash']);
        if(getenv('B19_COMPLETION_ARTIFACT_DIR')) {
            $capture=getenv('B19_COMPLETION_ARTIFACT_DIR').'/replay_mariadb';if(!is_dir($capture))mkdir($capture,0775,true);
            foreach(glob($dir.'/*.json') as $file)copy($file,$capture.'/'.basename($file));
            foreach(glob($fixture.'/expected/*.json') as $file)copy($file,$capture.'/fixture_'.basename($file));
            copy($fixture.'/manifest.json',$capture.'/fixture_manifest.json');file_put_contents($capture.'/persisted_metric.json',json_encode($row,JSON_PRETTY_PRINT));
            file_put_contents($capture.'/authoritative_inputs.json',json_encode(['publication'=>$pub,'run'=>$run,'lineage'=>$lineage,'config_snapshot'=>$snapshot,'independent_temporal_preimage'=>['trade_date'=>$date,'identity_revision_set_hash'=>$lineage->identity_revision_set_hash,'calendar_revision_set_hash'=>$lineage->calendar_revision_set_hash,'status_revision_set_hash'=>$lineage->status_revision_set_hash]],JSON_PRETTY_PRINT));
        }
        $this->afterIdentityRun($verifier,$runId,$fixture,$id,$date,$expected,$manifest,$files,$world,$replay);
        if (! $this->continueIdentityScope()) return;
        $writeExpected=function(array $probe,string $version,array $reasons=null)use($fixture,&$manifest,$files){
            file_put_contents($fixture.'/expected/expected_replay_result.json',json_encode($probe));
            file_put_contents($fixture.'/expected/expected_hashes.json',json_encode(array_intersect_key($probe['expected_artifact_context'],array_flip(['bars_batch_hash','indicators_batch_hash','eligibility_batch_hash']))));
            file_put_contents($fixture.'/expected/expected_reason_code_counts.json',json_encode($reasons??$files['expected_reason_code_counts.json']));
            $manifest['fixture_version']=$version;foreach($manifest['files'] as $name)$manifest['files_sha256'][$name]=hash_file('sha256',$fixture.'/'.$name);
            file_put_contents($fixture.'/manifest.json',json_encode($manifest));
        };
        $degrade=$expected;$degrade['comparison_result']='EXPECTED_DEGRADE';$writeExpected($degrade,'b19-identity-degrade-v2');
        $degraded=$verifier->verifyRunAgainstFixture($runId,$fixture,null,$id);
        $this->assertSame('EXPECTED_DEGRADE',$degraded['comparison_result']);$this->assertSame('PASS',$degraded['replay_status']);
        $this->assertSame(0,$degraded['eligible_count'],'the independently specified world genuinely has an insufficient-history hold');
        $degrade['expected_artifact_context']['bars_batch_hash']=hash('sha256','independently specified negative bars context v3');$writeExpected($degrade,'b19-identity-unexpected-v3');
        $unexpected=$verifier->verifyRunAgainstFixture($runId,$fixture,null,$id);
        $this->assertSame('UNEXPECTED',$unexpected['comparison_result']);$this->assertSame('FAIL',$unexpected['replay_status']);
        $counter=$expected;$counter['expected_final_state']['terminal_status']='HELD';$counter['expected_run_context']['trade_date_effective']='2026-03-22';$counter['expected_seal_context']['seal_state']='UNSEALED';$counter['expected_publication_context']['publication_version']=2;
        foreach(['bars_batch_hash','indicators_batch_hash','eligibility_batch_hash'] as $key)$counter['expected_artifact_context'][$key]=hash('sha256','independent negative fixture v4 '.$key);
        $counterReasons=$files['expected_reason_code_counts.json'];foreach($counterReasons as &$reason)$reason['reason_count']+=1;unset($reason);
        $writeExpected($counter,'b19-identity-counterfactual-v4',$counterReasons);
        $different=$verifier->verifyRunAgainstFixture($runId,$fixture,null,$id);[$differentJson]=$this->export((int)$different['replay_id'],$date);
        $this->assertSame('MISMATCH',$different['comparison_result']);$this->assertSame('FAIL',$different['replay_status']);
        $this->assertSame('HELD',$differentJson['replay_expected_state']['status']);$this->assertSame('SUCCESS',$differentJson['replay_actual_state']['status']);
        $this->assertSame('2026-03-22',$differentJson['replay_expected_state']['trade_date_effective']);$this->assertSame($date,$differentJson['replay_actual_state']['trade_date_effective']);
        $this->assertSame('UNSEALED',$differentJson['replay_expected_state']['seal_state']);$this->assertSame('SEALED',$differentJson['replay_actual_state']['seal_state']);
        $this->assertSame('b19-identity-counterfactual-v4',$differentJson['replay_expected_state']['fixture_version']);
        foreach(['publication_version'=>2,'bars_batch_hash'=>$counter['expected_artifact_context']['bars_batch_hash'],'indicators_batch_hash'=>$counter['expected_artifact_context']['indicators_batch_hash'],'eligibility_batch_hash'=>$counter['expected_artifact_context']['eligibility_batch_hash'],'reason_code_counts'=>$counterReasons] as $field=>$value){$this->assertSame($value,$differentJson['replay_expected_state'][$field]);$this->assertSame($stable[$field],$differentJson['replay_actual_state'][$field]);$this->assertNotSame($value,$stable[$field]);}
        $divergentIdentity=$expected;$divergentIdentity['expected_publication_manifest_identity']['formula_version']='independent-negative-formula-v5';$writeExpected($divergentIdentity,'b19-identity-formula-divergence-v5');
        $identityDifference=$verifier->verifyRunAgainstFixture($runId,$fixture,null,$id);$this->assertSame('MISMATCH',$identityDifference['comparison_result']);$this->assertSame('FAIL',$identityDifference['replay_status']);$this->assertSame(1,$identityDifference['mismatch_count']);$this->assertSame('REPLAY_LINEAGE_MISMATCH',$identityDifference['final_reason_code']);
        // A malformed independent expectation is blocked, never reconstructed from actual.
        $case=0;
        foreach([null,[],array_merge($identity,['schema_version'=>'wrong']),array_merge($identity,['applicability'=>'NOT_APPLICABLE']),array_merge($identity,['publication_manifest_hash'=>null])] as $bad) {
            $probe=$expected;$probe['expected_publication_manifest_identity']=$bad;$writeExpected($probe,'b19-identity-missing-v'.(++$case));
            $blocked=$verifier->verifyRunAgainstFixture($runId,$fixture,null,$id);$this->assertSame('BLOCKED',$blocked['replay_status']);
            $this->assertStringContainsString('REPLAY_EXPECTED_MANIFEST_IDENTITY_UNAVAILABLE',$blocked['mismatch_summary']);
            $this->assertNull($blocked['final_reason_code'],'missing prerequisites must not retain a MATCH reason');
        }
        $writeExpected($expected,'b19-identity-missing-mode-v1');unset($manifest['replay_mode']);file_put_contents($fixture.'/manifest.json',json_encode($manifest));
        $missingMode=$verifier->verifyRunAgainstFixture($runId,$fixture,null,$id);$this->assertSame('BLOCKED',$missingMode['replay_status'],'missing independently declared mode must block');
        $manifest['replay_mode']='PUBLICATION_EXACT';$writeExpected($expected,'b19-identity-historical-v1');
        DB::table('eod_runs')->where('run_id',$runId)->update(['config_hash'=>'not-a-content-digest']);
        $unbound=$verifier->verifyRunAgainstFixture($runId,$fixture,null,$id);$this->assertSame('BLOCKED',$unbound['replay_status']);DB::table('eod_runs')->where('run_id',$runId)->update(['config_hash'=>$run->config_hash]);
        DB::table('eod_current_publication_pointer')->where('publication_id',$id)->delete();DB::table('eod_publications')->where('publication_id',$id)->update(['is_current'=>0]);
        config()->set('market_data.indicators.price_product_default','RAW');
        $historical=$verifier->verifyRunAgainstFixture($runId,$fixture,null,$id);
        $this->assertNotSame('BLOCKED',$historical['replay_status'],$historical['mismatch_summary']);
        $this->assertSame($identity,$historical['actual_context']['actual_publication_manifest_identity'],'explicit historical publication remains verified without a current-pointer substitute');
        $this->assertSame('STRUCTURAL_ADJUSTED',$historical['actual_context']['actual_publication_context']['price_product_code'],'frozen publication product survives a different live default');
        $this->assertSame($pub->price_product_version,$historical['actual_context']['actual_publication_context']['price_product_version']);
        [$oldJson]=$this->export($replay,$date);$this->assertSame($identity,$oldJson['replay_result']['actual_context']['actual_publication_manifest_identity'],'export retains persisted envelope after live state changes');
        if(getenv('B19_COMPLETION_ARTIFACT_DIR'))file_put_contents(getenv('B19_COMPLETION_ARTIFACT_DIR').'/replay_mariadb/additional_execution_cases.json',json_encode(['degrade'=>$degraded,'unexpected'=>$unexpected,'counterfactual'=>$different,'identity_divergence'=>$identityDifference,'missing_mode'=>$missingMode,'unbound_configuration'=>$unbound,'historical'=>$historical],JSON_PRETTY_PRINT));
        // Missing context in a new versioned stored row is refused without querying current state.
        $context=json_decode($row->actual_context_json,true);unset($context['actual_publication_manifest_identity']);
        DB::table('md_replay_daily_metrics')->where('replay_id',$replay)->update(['actual_context_json'=>json_encode($context)]);
        try{$this->export($replay,$date);$this->fail('missing persisted envelope was silently repaired');}catch(RuntimeException $error){$this->assertStringContainsString('REPLAY_MANIFEST_IDENTITY_INVALID',$error->getMessage());}
        DB::table('md_replay_daily_metrics')->where('replay_id',$replay)->update(['actual_context_json'=>$row->actual_context_json]);
    }
}
