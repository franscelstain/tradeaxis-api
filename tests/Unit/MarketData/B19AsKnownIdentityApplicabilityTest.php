<?php

require_once __DIR__.'/B18AsKnownModeIsolationTest.php';

/** Reuses the existing real cutoff execution only for mode/coordinate/applicability, not as a golden oracle. */
class B19AsKnownIdentityApplicabilityTest extends TestCase
{
    use \Tests\Support\UsesMarketDataSqlite;
    private $helper;
    protected function setUp(): void {parent::setUp();$this->bootMarketDataSqlite(true);$this->helper=new B18AsKnownModeIsolationTest();$seed=new ReflectionMethod($this->helper,'seedWorld');$seed->setAccessible(true);$seed->invoke($this->helper);}
    protected function tearDown(): void {$this->tearDownMarketDataSqlite();parent::tearDown();}
    public function test_new_state_paths_keep_the_as_known_coordinate_and_no_analytical_publication(): void
    {
        $verify=new ReflectionMethod(B18AsKnownModeIsolationTest::class,'verifyAsKnown');$verify->setAccessible(true);$verify->invoke($this->helper);
        $stored=new ReflectionMethod(B18AsKnownModeIsolationTest::class,'storedMetric');$stored->setAccessible(true);$row=$stored->invoke($this->helper);
        $cutoff=(new ReflectionClass(B18AsKnownModeIsolationTest::class))->getConstant('CUTOFF');
        $dir=sys_get_temp_dir().'/b19_as_known_identity_'.uniqid();
        try {
            (new \App\Application\MarketData\Services\MarketDataEvidenceExportService(new \App\Infrastructure\Persistence\MarketData\EodEvidenceRepository(),new \App\Infrastructure\Persistence\MarketData\EodPublicationRepository(),new \App\Infrastructure\Persistence\MarketData\EodCorrectionRepository()))->exportReplayEvidence($row->replay_id,$row->trade_date,$dir);
            $result=json_decode(file_get_contents($dir.'/replay_result.json'),true);$expected=json_decode(file_get_contents($dir.'/replay_expected_state.json'),true);$actual=json_decode(file_get_contents($dir.'/replay_actual_state.json'),true);
            $this->assertNotSame('',$cutoff);$this->assertSame($cutoff,$result['knowledge_cutoff_at']);
            foreach([$result['replay_mode'],$expected['replay_mode'],$actual['replay_mode']] as $mode)$this->assertSame('AS_KNOWN',$mode);
            $this->assertSame('CANONICAL_RAW',$actual['actual_context']['executed_replay']['execution_scope']);
            foreach([$actual['actual_context']['actual_publication_manifest_identity'],$expected['expected_context']['expected_publication_manifest_identity']] as $identity){
                $this->assertSame('replay_publication_manifest_identity_v1',$identity['schema_version']);$this->assertSame('NOT_APPLICABLE',$identity['applicability']);
                foreach(['publication_id','publication_manifest_hash','temporal_revision_set_hash','formula_version'] as $key)$this->assertNull($identity[$key],'explicitly inapplicable publication-only member, not an applicable identity proof');
            }
            foreach([$actual['actual_context']['actual_publication_context'],$expected['expected_context']['expected_publication_context']] as $context)foreach(['publication_id','price_product_code','price_product_version','factor_set_id','factor_set_hash'] as $field)$this->assertNull($context[$field]);
            if(getenv('B19_COMPLETION_ARTIFACT_DIR')){$capture=getenv('B19_COMPLETION_ARTIFACT_DIR').'/as_known_applicability';if(!is_dir($capture))mkdir($capture,0775,true);foreach(glob($dir.'/*.json') as $f)copy($f,$capture.'/'.basename($f));file_put_contents($capture.'/persisted_metric.json',json_encode($row,JSON_PRETTY_PRINT));}
            $corrupt=json_decode($row->actual_context_json,true);$corrupt['actual_publication_context']['price_product_code']='STRUCTURAL_ADJUSTED';
            \Illuminate\Support\Facades\DB::table('md_replay_daily_metrics')->where('replay_id',$row->replay_id)->update(['actual_context_json'=>json_encode($corrupt)]);
            try{(new \App\Application\MarketData\Services\MarketDataEvidenceExportService(new \App\Infrastructure\Persistence\MarketData\EodEvidenceRepository(),new \App\Infrastructure\Persistence\MarketData\EodPublicationRepository(),new \App\Infrastructure\Persistence\MarketData\EodCorrectionRepository()))->exportReplayEvidence($row->replay_id,$row->trade_date,$dir);$this->fail('AS_KNOWN fabricated analytical identity accepted');}catch(RuntimeException $error){$this->assertStringContainsString('REPLAY_MANIFEST_IDENTITY_INVALID',$error->getMessage());}
        } finally {
            if(is_dir($dir)){$this->assertSame(0,strpos(realpath($dir),realpath(sys_get_temp_dir()).DIRECTORY_SEPARATOR));foreach(glob($dir.'/*') as $f)if(is_file($f))unlink($f);rmdir($dir);}
        }
    }
}
