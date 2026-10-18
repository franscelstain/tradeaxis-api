<?php

use App\Application\MarketData\Services\MarketDataEvidenceExportService;
use App\Application\MarketData\Services\ReplayVerificationService;
use App\Infrastructure\Persistence\MarketData\EodEvidenceRepository;
use App\Infrastructure\Persistence\MarketData\EodPublicationRepository;
use App\Infrastructure\Persistence\MarketData\EodCorrectionRepository;
use App\Infrastructure\Persistence\MarketData\ReplayResultRepository;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\Support\UsesMarketDataSqlite;

/** Fast negative guards paired with the actual MariaDB execution, not a mock-only producer proof. */
class B19ReplayIdentityProjectionTest extends TestCase
{
    use UsesMarketDataSqlite;
    private $sample;private $paths=[];
    protected function setUp(): void {parent::setUp();$this->bootMarketDataSqlite();$this->sample=json_decode(file_get_contents(__DIR__.'/../../fixtures/replay/b19-identity-executed-metric-v1.json'),true);}
    protected function tearDown(): void {
        foreach($this->paths as $p){if(!is_dir($p))continue;$this->assertSame(0,strpos(realpath($p),realpath(sys_get_temp_dir()).DIRECTORY_SEPARATOR));$it=new RecursiveIteratorIterator(new RecursiveDirectoryIterator($p,FilesystemIterator::SKIP_DOTS),RecursiveIteratorIterator::CHILD_FIRST);foreach($it as $f)$f->isDir()?rmdir($f->getPathname()):unlink($f->getPathname());rmdir($p);}
        $this->tearDownMarketDataSqlite();parent::tearDown();
    }
    private function value(array $v,string $path){foreach(explode('.',$path) as $key){$this->assertArrayHasKey($key,$v,$path);$v=$v[$key];}return $v;}
    public function test_as_known_export_does_not_substitute_publication_mode(): void {
        $out=$this->export($this->sample['as_known_actual_metric']);
        foreach($out as $artifact)$this->assertSame('AS_KNOWN',$artifact['replay_mode']);
        $this->assertSame($this->sample['as_known_input_coordinate'],$this->value($out['replay_result'],'knowledge_cutoff_at'));
    }
    public static function fields(): array {
        $result=[];
        foreach([240=>['knowledge_cutoff_at','cutoff'],241=>['bound_inputs.source_observation_manifest_hash','observation'],242=>['bound_inputs.config_snapshot_id','config_id'],243=>['bound_inputs.config_snapshot_hash','config'],244=>['actual_context.actual_publication_manifest_identity.temporal_revision_set_hash','temporal'],245=>['actual_context.actual_publication_context.factor_set_id','factor_id'],246=>['actual_context.actual_publication_context.factor_set_hash','factor'],247=>['actual_context.actual_publication_context.price_product_code','product'],248=>['actual_context.actual_publication_manifest_identity.formula_version','formula'],249=>['bound_inputs.read_model_version','read_model']] as $id=>$v)$result['R'.$id]=[$id,'replay_result',$v[0],$v[1]];
        foreach(['expected'=>[267,'replay_expected_state','expected_context','expected_bound_input_context','expected_publication_manifest_identity','expected_publication_context'],'actual'=>[284,'replay_actual_state','actual_context','actual_bound_input_context','actual_publication_manifest_identity','actual_publication_context']] as $side=>$v){[$id,$file,$ctx,$bound,$env,$pub]=$v;foreach([['replay_mode','mode'],["$ctx.$bound.source_observation_manifest_hash",'observation'],["$ctx.$bound.config_snapshot_hash",'config'],["$ctx.$env.temporal_revision_set_hash",'temporal'],["$ctx.$pub.factor_set_hash",'factor'],["$ctx.$pub.price_product_code",'product'],["$ctx.$env.formula_version",'formula'],["$ctx.$bound.read_model_version",'read_model']] as $offset=>$member)$result['R'.($id+$offset)]=[$id+$offset,$file,$member[0],$member[1]];}
        return $result;
    }
    private function expected(string $source){
        $e=$this->sample['independent_expected'];$i=$this->sample['authoritative_inputs'];
        if($source==='cutoff')return null;if($source==='mode')return 'PUBLICATION_EXACT';
        if($source==='config_id')return (int)$i['publication']['config_snapshot_id'];
        if($source==='factor_id')return (int)$i['publication']['factor_set_id'];
        if($source==='temporal'){$v=$i['independent_temporal_preimage'];ksort($v);return hash('sha256',json_encode($v,JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE|JSON_PRESERVE_ZERO_FRACTION));}
        if($source==='config')return hash('sha256',$i['config_snapshot']['resolved_config_json']);
        if($source==='formula')return $e['expected_publication_manifest_identity']['formula_version'];
        if(in_array($source,['factor','product'],true))return $e['expected_publication_context'][$source==='factor'?'factor_set_hash':'price_product_code'];
        return $e['expected_bound_input_context'][$source==='observation'?'source_observation_manifest_hash':'read_model_version'];
    }
    private function export(array $metric): array {
        $metric=array_intersect_key($metric,array_flip(Schema::getColumnListing('md_replay_daily_metrics')));
        (new ReplayResultRepository())->upsertMetric($metric);
        $dir=sys_get_temp_dir().'/b19_identity_projection_'.uniqid();$this->paths[]=$dir;
        (new MarketDataEvidenceExportService(new EodEvidenceRepository(),new EodPublicationRepository(),new EodCorrectionRepository()))->exportReplayEvidence($metric['replay_id'],$metric['trade_date'],$dir);
        $out=[];foreach(['replay_result','replay_expected_state','replay_actual_state'] as $f)$out[$f]=json_decode(file_get_contents($dir.'/'.$f.'.json'),true);return $out;
    }
    /** @dataProvider fields */
    public function test_each_identity_member_has_its_independent_expected_value(int $id,string $artifact,string $path,string $source): void {
        $out=$this->export($this->sample['metric']);$value=$this->value($out[$artifact],$path);$this->assertSame($this->expected($source),$value,'MD-S075-R'.$id.' independent source');
        if($source!=='cutoff')$this->assertNotNull($value);
    }
    public function test_expected_and_actual_contexts_are_preserved_separately(): void {
        $metric=$this->sample['metric'];$e=json_decode($metric['expected_context_json'],true);
        foreach(['source_observation_manifest_hash','config_snapshot_hash'] as $key)$e['expected_bound_input_context'][$key]=hash('sha256','independent counterfactual fixture v2 '.$key);
        $e['expected_bound_input_context']['read_model_version']='counterfactual-read-model-v2';
        $e['expected_publication_context']['factor_set_hash']=hash('sha256','counterfactual-factors-v2');$e['expected_publication_context']['price_product_code']='TOTAL_RETURN';
        $e['expected_publication_manifest_identity']['temporal_revision_set_hash']=hash('sha256','counterfactual temporal input fixture v2');$e['expected_publication_manifest_identity']['formula_version']='counterfactual-formula-v2';
        $metric['expected_context_json']=json_encode($e);$metric['fixture_version']='b19-negative-context-v2';$metric['comparison_result']='MISMATCH';$metric['replay_status']='FAIL';
        $out=$this->export($metric);
        foreach(self::fields() as [$id,$artifact,$path,$source]){
            if($artifact==='replay_expected_state'&&$source!=='mode'){$wanted=$this->value(['expected_context'=>$e],$path);$this->assertSame($wanted,$this->value($out[$artifact],$path));$this->assertNotSame($this->expected($source),$wanted);}
            elseif($artifact!=='replay_expected_state')$this->assertSame($this->expected($source),$this->value($out[$artifact],$path),'counterfactual expected side must not replace actual R'.$id);
        }
    }
    public static function corruptions(): array {return ['missing envelope'=>['missing'],'NULL formula'=>['formula'],'wrong binding'=>['binding'],'wrong mode'=>['mode'],'wrong cutoff'=>['cutoff']];}
    /** @dataProvider corruptions */
    public function test_missing_or_contradictory_versioned_storage_is_refused(string $bad): void {
        $m=$this->sample['metric'];$a=json_decode($m['actual_context_json'],true);
        if($bad==='missing')unset($a['actual_publication_manifest_identity']);elseif($bad==='formula')$a['actual_publication_manifest_identity']['formula_version']=null;elseif($bad==='binding')$a['actual_publication_manifest_identity']['publication_id']+=1000;elseif($bad==='mode')$m['replay_mode']='AS_KNOWN';else $m['knowledge_cutoff_at']='2026-03-23 18:00:00';
        $m['actual_context_json']=json_encode($a);$this->expectException(RuntimeException::class);$this->expectExceptionMessage($bad==='mode'?'REPLAY_KNOWLEDGE_CUTOFF_REQUIRED':'REPLAY_MANIFEST_IDENTITY_INVALID');$this->export($m);
    }
    public function test_exact_persisted_names_and_versioned_paths_are_not_aliases(): void {
        $out=$this->export($this->sample['metric']);$r=$out['replay_result'];
        $this->assertArrayHasKey('knowledge_cutoff_at',$r);$this->assertArrayNotHasKey('knowledge_cutoff',$r);
        foreach(['observation_manifest_hash','temporal_revision_set_hash','factor_set_hash','formula_version'] as $alias)$this->assertArrayNotHasKey($alias,$r);
        $this->assertSame('replay_publication_manifest_identity_v1',$this->value($r,'actual_context.actual_publication_manifest_identity.schema_version'));
        $this->assertNotSame($r['bound_inputs']['temporal_identity_hash'],$r['actual_context']['actual_publication_manifest_identity']['temporal_revision_set_hash']);
        $this->assertNotSame($r['bound_inputs']['event_factor_hash'],$r['actual_context']['actual_publication_context']['factor_set_hash']);
        $this->assertNotSame($r['bound_inputs']['formula_registry_hash'],$r['actual_context']['actual_publication_manifest_identity']['formula_version']);
    }
    public function test_config_references_and_contradictory_snapshot_hashes_are_not_digests(): void {
        $source=$this->sample['authoritative_inputs']['config_snapshot'];
        DB::table('md_config_snapshots')->insert(array_intersect_key($source,array_flip(Schema::getColumnListing('md_config_snapshots'))));
        $service=new ReplayVerificationService(new EodEvidenceRepository(),new EodPublicationRepository(),new ReplayResultRepository());$method=new ReflectionMethod($service,'configIdentityForRun');$method->setAccessible(true);
        $run=(object)['config_snapshot_id'=>(int)$source['config_snapshot_id'],'config_hash'=>$source['config_hash'],'config_snapshot_ref'=>'legacy-reference'];
        $this->assertSame(hash('sha256',$source['resolved_config_json']),$method->invoke($service,$run));
        $unavailable=(new ReflectionClass($service))->getConstant('CONFIG_IDENTITY_UNRECORDED');
        foreach([null,'not-a-digest'] as $bad){$run->config_hash=$bad;$this->assertSame($unavailable,$method->invoke($service,$run));}
        $repo=$this->createMock(EodPublicationRepository::class);$repo->method('buildManifestByPublicationId')->willReturn((object)['bound_input_context'=>['status'=>'VERIFIED','components'=>[],'registry_content'=>[]],'config_snapshot_id'=>(int)$source['config_snapshot_id'],'config_snapshot_hash'=>$source['config_hash']]);
        $boundService=new ReplayVerificationService(new EodEvidenceRepository(),$repo,new ReplayResultRepository());$bound=new ReflectionMethod($boundService,'actualBoundInputContext');$bound->setAccessible(true);
        $run->config_hash=str_repeat('0',64);$this->assertSame($unavailable,$bound->invoke($boundService,$run,(object)['publication_id'=>44])['config_snapshot_hash']);
        $run->config_hash=$source['config_hash'];$run->config_snapshot_id+=1;$this->assertSame($unavailable,$bound->invoke($boundService,$run,(object)['publication_id'=>44])['config_snapshot_hash']);
    }
    public function test_expected_envelope_validation_refuses_wrong_schema_and_applicability(): void {
        $service=new ReplayVerificationService(new EodEvidenceRepository(),new EodPublicationRepository(),new ReplayResultRepository());$method=new ReflectionMethod($service,'completeReplayManifestIdentity');$method->setAccessible(true);
        $valid=$this->capture();$this->assertTrue($method->invoke($service,$valid));
        foreach([['schema_version'=>'wrong'],['applicability'=>'NOT_APPLICABLE'],['publication_id'=>null],['formula_version'=>null]] as $bad)$this->assertFalse($method->invoke($service,array_merge($valid,$bad)));
    }
    public static function unavailableCapture(): array {return ['RAW unsupported'=>['price_product_code','RAW'],'missing product'=>['price_product_code',null],'missing factor'=>['factor_set_id',null],'missing temporal'=>['temporal_revision_set_hash',null],'missing formula'=>['formula_version',null]];}
    private function capture(array $overrides=[]): array {
        $manifest=(object)array_merge(['bound_input_context'=>['status'=>'VERIFIED'],'price_product_code'=>'STRUCTURAL_ADJUSTED','price_product_version'=>'structural_v1','factor_set_id'=>4,'factor_set_hash'=>str_repeat('4',64),'temporal_revision_set_hash'=>str_repeat('5',64),'formula_version'=>'formula_v1','identity_revision_set_hash'=>str_repeat('8',64),'formula_registry_hash'=>str_repeat('9',64)],$overrides);
        $repo=$this->createMock(EodPublicationRepository::class);$repo->method('buildManifestByPublicationId')->willReturn($manifest);$repo->method('assertPublicationManifestHashValid')->willReturn(true);
        $service=new ReplayVerificationService(new EodEvidenceRepository(),$repo,new ReplayResultRepository());$method=new ReflectionMethod($service,'replayPublicationManifestIdentity');$method->setAccessible(true);
        return $method->invoke($service,(object)['publication_id'=>44,'publication_manifest_hash'=>str_repeat('6',64)]);
    }
    public function test_capture_preserves_distinct_manifest_members_and_version(): void {
        $identity=$this->capture();$this->assertSame(['schema_version'=>'replay_publication_manifest_identity_v1','applicability'=>'APPLICABLE','publication_id'=>44,'publication_manifest_hash'=>str_repeat('6',64),'temporal_revision_set_hash'=>str_repeat('5',64),'formula_version'=>'formula_v1'],$identity);
        $this->assertNotSame(str_repeat('8',64),$identity['temporal_revision_set_hash']);$this->assertNotSame(str_repeat('9',64),$identity['formula_version']);
    }
    /** @dataProvider unavailableCapture */
    public function test_capture_does_not_manufacture_an_applicable_identity(string $field,$bad): void {
        $identity=$this->capture([$field=>$bad]);
        $this->assertSame('UNAVAILABLE',$identity['applicability']);$this->assertNotSame('NOT_APPLICABLE',$identity['applicability']);
    }
}
