<?php

use App\Application\MarketData\Services\MarketDataEvidenceExportService;
use App\Infrastructure\Persistence\MarketData\{EodEvidenceRepository,EodPublicationRepository,EodCorrectionRepository,ReplayResultRepository};
use Illuminate\Support\Facades\{DB,Schema};
use Tests\Support\UsesMarketDataSqlite;

class B19ReplayAdmissionBoundaryTest extends TestCase
{
    use UsesMarketDataSqlite;
    private $dir;
    protected function setUp(): void {parent::setUp();$this->bootMarketDataSqlite();$this->dir=sys_get_temp_dir().'/b19_admission_'.uniqid();}
    protected function tearDown(): void {if(is_dir($this->dir)){foreach(glob($this->dir.'/*') as $f)if(is_file($f))unlink($f);rmdir($this->dir);}$this->tearDownMarketDataSqlite();parent::tearDown();}
    private function metric(): array {return json_decode(file_get_contents(__DIR__.'/../../fixtures/replay/b19-identity-executed-metric-v1.json'),true)['metric'];}
    private function export(array $m): array {
        $legacy = $m['__legacy_inject'] ?? false;
        $columns = array_flip(Schema::getColumnListing('md_replay_daily_metrics'));
        (new ReplayResultRepository())->upsertMetric(array_intersect_key($legacy ? $this->metric() : $m,$columns));
        if ($legacy) DB::table('md_replay_daily_metrics')->where('replay_id',$m['replay_id'])->update(array_intersect_key($m,$columns));
        (new MarketDataEvidenceExportService(new EodEvidenceRepository(),new EodPublicationRepository(),new EodCorrectionRepository()))->exportReplayEvidence($m['replay_id'],$m['trade_date'],$this->dir);
        $out=[];foreach(['replay_result','evidence_admission','replay_evidence_pack'] as $f)$out[$f]=json_decode(file_get_contents($this->dir.'/'.$f.'.json'),true);
        if(getenv('B19_COMPLETION_ARTIFACT_DIR')){$to=getenv('B19_COMPLETION_ARTIFACT_DIR').'/identity_boundary';if(!is_dir($to))mkdir($to,0775,true);foreach(glob($this->dir.'/*') as $f)copy($f,$to.'/'.basename($f));}
        return $out;
    }
    public function test_legacy_comparison_pass_is_readable_but_cannot_admit_complete_identity_evidence(): void {
        $m=$this->metric();$a=json_decode($m['actual_context_json'],true);$e=json_decode($m['expected_context_json'],true);
        unset($a['replay_artifact_identity_schema_version'],$a['actual_publication_manifest_identity'],$e['expected_publication_manifest_identity']);
        $m['actual_context_json']=json_encode($a);$m['expected_context_json']=json_encode($e);$out=$this->export($m);
        $this->assertSame('MATCH',$out['replay_result']['comparison_result']);$this->assertSame('PASS',$out['replay_result']['replay_status']);
        $this->assertSame($m['actual_context_json'],DB::table('md_replay_daily_metrics')->value('actual_context_json'),'read export must not backfill historical rows');
        $this->assertSame('ADMITTED_INCOMPLETE',$out['evidence_admission']['evidence_admission_state'],'historical comparison PASS cannot imply complete versioned identity admission');
        $this->assertSame('UNAVAILABLE',$out['evidence_admission']['identity_verification']['status']);
        $this->assertContains('replay_artifact_identity',$out['evidence_admission']['missing_sections']);
    }
    public static function missingIdentities(): array {return [['observation'],['config'],['read_model'],['expected_observation'],['fixture_version'],['scalar_publication'],['factor'],['product_version']];}
    /** @dataProvider missingIdentities */
    public function test_missing_or_unbound_identity_members_cannot_be_complete(string $bad): void {
        $m=$this->metric();
        if($bad==='observation')$m['source_observation_manifest_hash']=null;
        elseif($bad==='config')$m['config_snapshot_hash']='CONFIG_IDENTITY_UNRECORDED';
        elseif($bad==='read_model')$m['read_model_version']=null;
        elseif($bad==='fixture_version')$m['fixture_version']=null;
        elseif($bad==='scalar_publication')$m['publication_id']+=10;
        elseif(in_array($bad,['factor','product_version'],true)){$a=json_decode($m['actual_context_json'],true);$a['actual_publication_context'][$bad==='factor'?'factor_set_id':'price_product_version']=null;$m['actual_context_json']=json_encode($a);}
        else {$e=json_decode($m['expected_context_json'],true);unset($e['expected_bound_input_context']['source_observation_manifest_hash']);$m['expected_context_json']=json_encode($e);}
        $m['__legacy_inject']=true;$out=$this->export($m);$this->assertSame('PASS',$out['replay_result']['replay_status']);$this->assertSame('ADMITTED_INCOMPLETE',$out['evidence_admission']['evidence_admission_state']);$this->assertSame('UNAVAILABLE',$out['evidence_admission']['identity_verification']['status']);
    }

}
