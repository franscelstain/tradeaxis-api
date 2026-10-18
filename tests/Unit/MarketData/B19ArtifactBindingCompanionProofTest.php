<?php
use App\Application\MarketData\Services\MarketDataEvidenceExportService;
use App\Infrastructure\Persistence\MarketData\{EodEvidenceRepository,EodPublicationRepository,EodCorrectionRepository,ReplayResultRepository};
use Illuminate\Support\Facades\Schema;
use Tests\Support\UsesMarketDataSqlite;
class B19ArtifactBindingCompanionProofTest extends TestCase
{
    use UsesMarketDataSqlite;
    private $sample;private $dirs=[];
    protected function setUp(): void {parent::setUp();$this->bootMarketDataSqlite();$this->sample=json_decode(file_get_contents(__DIR__.'/../../fixtures/replay/b19-companion-executed-metric-v1.json'),true);}
    protected function tearDown(): void {foreach($this->dirs as $d)if(is_dir($d)){foreach(glob($d.'/*') as $f)if(is_file($f))unlink($f);rmdir($d);}$this->tearDownMarketDataSqlite();parent::tearDown();}
    private function export(array $m): array {(new ReplayResultRepository())->upsertMetric(array_intersect_key($m,array_flip(Schema::getColumnListing('md_replay_daily_metrics'))));(new ReplayResultRepository())->replaceReasonCodeCounts($m['replay_id'],$m['trade_date'],$this->sample['actual_reason_counts']);$dir=sys_get_temp_dir().'/b19_binding_'.uniqid();$this->dirs[]=$dir;(new MarketDataEvidenceExportService(new EodEvidenceRepository(),new EodPublicationRepository(),new EodCorrectionRepository()))->exportReplayEvidence($m['replay_id'],$m['trade_date'],$dir);return [json_decode(file_get_contents($dir.'/artifact_manifest.json'),true),json_decode(file_get_contents($dir.'/evidence_admission.json'),true),$dir];}
    public static function fields(): array {$r=[];foreach(['run_id','publication_id','publication_manifest_hash','observation_manifest_hash','config_snapshot_id','config_snapshot_hash','temporal_revision_set_hash','factor_set_id','factor_set_hash','price_product_code','price_product_version','canonicalization_version','formula_version','config_registry_revision','read_model_version','trade_date_requested','trade_date_effective','readiness_state','freshness_state'] as $f)$r[$f]=[$f];return $r;}
    private function expected($field){$o=json_decode(file_get_contents(__DIR__.'/../../fixtures/replay/r0025-synthetic-v2-candidate-v5/expected/expected_publication_manifest.json'),true);$i=$this->sample['authoritative_inputs'];if($field==='publication_id')return (int)$i['publication']['publication_id'];if($field==='run_id')return (int)$i['run']['run_id'];if(in_array($field,['config_snapshot_id','factor_set_id'],true))return (int)$i['publication'][$field];if($field==='publication_manifest_hash')return $o['publication_manifest_hash'];if($field==='config_snapshot_hash')return hash('sha256',$i['config_snapshot']['resolved_config_json']);if($field==='temporal_revision_set_hash'){$v=$i['independent_temporal_preimage'];ksort($v);return hash('sha256',json_encode($v,JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE|JSON_PRESERVE_ZERO_FRACTION));}return $o['payload'][$field];}
    /** @dataProvider fields */
    public function test_each_applicable_binding_has_an_independent_value_and_missing_value_is_not_complete(string $field): void {
        $m=$this->sample['metric'];[$index,$admission]=$this->export($m);$this->assertSame('ADMITTED_COMPLETE',$admission['evidence_admission_state']);foreach(['actual','expected'] as $side){$b=$index['binding_contexts'][$side]['bindings'][$field];$this->assertSame('REQUIRED',$b['applicability']);$this->assertSame('AVAILABLE',$b['status']);$this->assertSame($this->expected($field),$b['value']);}
        $c=json_decode($m['expected_context_json'],true);unset($c['expected_artifact_semantic_bindings'][$field]);$m['expected_context_json']=json_encode($c);[$bad,$a]=$this->export($m);$this->assertSame('UNAVAILABLE',$bad['binding_contexts']['expected']['bindings'][$field]['status']);$this->assertSame('ADMITTED_INCOMPLETE',$a['evidence_admission_state']);$this->assertSame('COMPLETE',$bad['binding_contexts']['actual']['status']);
    }
    public function test_each_file_resolves_its_own_immutable_context_and_sorted_complete_reason_set(): void {
        $m=$this->sample['metric'];$e=json_decode($m['expected_context_json'],true);$e['expected_artifact_semantic_bindings']['canonicalization_version']='independent-counterfactual-canonical-v2';$m['expected_context_json']=json_encode($e);$m['fixture_version']='independent-companion-negative-v2';[$index,$a,$dir]=$this->export($m);
        $this->assertSame(['#/binding_contexts/expected'],$index['files']['replay_expected_state.json']['binding_refs']);$this->assertSame(['#/binding_contexts/actual'],$index['files']['replay_actual_state.json']['binding_refs']);
        $this->assertSame('independent-counterfactual-canonical-v2',$index['binding_contexts']['expected']['bindings']['canonicalization_version']['value']);$this->assertSame('idx_regular_raw_v2',$index['binding_contexts']['actual']['bindings']['canonicalization_version']['value']);
        $this->assertSame(['DATASET_HASH_CREATED','ELIG_INSUFFICIENT_HISTORY'],$index['binding_contexts']['actual']['bindings']['reason_codes']['value']);
        foreach($index['files'] as $file=>$member){$this->assertSame(hash('sha256',file_get_contents($dir.'/'.$file)),$member['sha256']);foreach($member['binding_refs'] as $ref)$this->assertArrayHasKey(substr($ref,strlen('#/binding_contexts/')),$index['binding_contexts']);}
    }
    public function test_missing_or_contradictory_context_stays_unavailable_without_backfill(): void {
        $m=$this->sample['metric'];$c=json_decode($m['actual_context_json'],true);$c['actual_artifact_semantic_bindings']['observation_manifest_hash']=str_repeat('1',64);$m['actual_context_json']=json_encode($c);[$i,$a]=$this->export($m);$this->assertSame('UNAVAILABLE',$i['binding_contexts']['actual']['bindings']['observation_manifest_hash']['status']);$this->assertSame('ADMITTED_INCOMPLETE',$a['evidence_admission_state']);
    }
    public function test_failed_comparison_without_diagnostics_is_not_complete(): void {
        $m=$this->sample['metric'];$m['comparison_result']='MISMATCH';$m['replay_status']='FAIL';$m['mismatch_summary']=null;$m['mismatches_json']='[]';$m['mismatch_reason_codes_json']='[]';[$index,$admission]=$this->export($m);$this->assertSame('ADMITTED_INCOMPLETE',$admission['evidence_admission_state']);$this->assertContains('mismatch_summary',$admission['missing_sections']);
    }

    public function test_malformed_supplemental_bindings_cannot_be_declared_available(): void {
        foreach(['config_snapshot_id'=>'1.5','canonicalization_version'=>['not-a-version']] as $field=>$value){$m=$this->sample['metric'];$e=json_decode($m['expected_context_json'],true);$e['expected_artifact_semantic_bindings'][$field]=$value;$m['expected_context_json']=json_encode($e);[$i,$a]=$this->export($m);$this->assertSame('UNAVAILABLE',$i['binding_contexts']['expected']['bindings'][$field]['status']);$this->assertSame('ADMITTED_INCOMPLETE',$a['evidence_admission_state']);}
    }

}
