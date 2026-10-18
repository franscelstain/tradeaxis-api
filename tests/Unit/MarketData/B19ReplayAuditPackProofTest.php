<?php
use App\Application\MarketData\Services\MarketDataEvidenceExportService;
use App\Infrastructure\Persistence\MarketData\{EodEvidenceRepository,EodPublicationRepository,EodCorrectionRepository,ReplayResultRepository};
use Illuminate\Support\Facades\{DB,Schema};
use Tests\Support\UsesMarketDataSqlite;
class B19ReplayAuditPackProofTest extends TestCase
{
    use UsesMarketDataSqlite;
    private $dirs=[];
    protected function setUp(): void {parent::setUp();$this->bootMarketDataSqlite();}
    protected function tearDown(): void {foreach($this->dirs as $d)if(is_dir($d)){foreach(glob($d.'/*') as $f)if(is_file($f))unlink($f);rmdir($d);}$this->tearDownMarketDataSqlite();parent::tearDown();}
    private function metric(string $case='counterfactual'): array {return json_decode(file_get_contents(__DIR__.'/../../fixtures/replay/b19-audit-executed-metrics-v1.json'),true)['cases'][$case];}
    private function export(array $m,bool $historical=false): array {
        $m=array_intersect_key($m,array_flip(Schema::getColumnListing('md_replay_daily_metrics')));(new ReplayResultRepository())->upsertMetric($m);if($historical)DB::table('md_replay_daily_metrics')->where('replay_id',$m['replay_id'])->update(['coverage_gate_state'=>'BLOCKED','expected_coverage_gate_state'=>'BLOCKED']);
        $d=sys_get_temp_dir().'/b19_pack_'.uniqid();$this->dirs[]=$d;(new MarketDataEvidenceExportService(new EodEvidenceRepository(),new EodPublicationRepository(),new EodCorrectionRepository()))->exportReplayEvidence($m['replay_id'],$m['trade_date'],$d);
        return json_decode(file_get_contents($d.'/replay_evidence_pack.json'),true);
    }
    public static function requirements(): array { $r=[];foreach(array_merge([102],range(104,123),[169]) as $id)$r['R'.$id]=[$id];return $r; }
    /** @dataProvider requirements */
    public function test_each_replay_pack_requirement_keeps_its_independent_expected_and_actual_meaning(int $id): void {
        $m=$this->metric();$p=$this->export($m);$r=$p['replay_result'];$e=$p['expected_state'];$a=$p['actual_state'];$this->assertArrayHasKey('expected_context',$e);$this->assertArrayHasKey('actual_context',$a);
        switch($id){
        case 102:case 108:case 123:
            $this->assertContains('REPLAY_ARTIFACT_HASH_MISMATCH',$r['mismatch_reason_codes']);$this->assertContains('REPLAY_EFFECTIVE_DATE_MISMATCH',$r['mismatch_reason_codes']);$this->assertContains('REPLAY_PUBLICATION_VERSION_MISMATCH',$r['mismatch_reason_codes']);
            $fields=array_column($r['mismatches'],'field');foreach(['bars_batch_hash','indicators_batch_hash','eligibility_batch_hash','publication_version','trade_date_effective','terminal_status'] as $field)$this->assertContains($field,$fields);break;
        case 104:case 115:$this->assertSame('b19-publication-identity-contract',$r['fixture_id']);$this->assertSame('b19-identity-counterfactual-v4',$r['fixture_version']);$this->assertSame((int)$m['replay_id'],$r['replay_id']);$this->assertSame($m['replay_case'],$r['replay_case']);break;
        case 105:case 116:$this->assertSame('HELD',$e['status']);$this->assertSame('2026-03-22',$e['trade_date_effective']);$this->assertSame(hash('sha256','independent negative fixture v4 bars_batch_hash'),$e['bars_batch_hash']);break;
        case 106:case 117:$this->assertSame('SUCCESS',$a['status']);$this->assertSame('2026-03-23',$a['trade_date_effective']);$oracle=json_decode(file_get_contents(__DIR__.'/../../fixtures/replay/r0025-synthetic-v2-candidate-v5/expected/expected_replay_result.json'),true);$this->assertSame($oracle['expected_artifact_context']['bars_batch_hash'],$a['bars_batch_hash']);break;
        case 107:$this->assertSame('MISMATCH',$r['comparison_result']);$this->assertSame('FAIL',$r['replay_status']);$this->assertSame('SUCCESS',$r['status']);break;
        case 109:$this->assertStringContainsString('mismatch_count=',$r['mismatch_summary']);$this->assertStringContainsString('REPLAY_ARTIFACT_HASH_MISMATCH',$r['mismatch_summary']);break;
        case 110:foreach([$r['coverage'],$r['expected_coverage']] as $c){foreach(['coverage_gate_state'=>'PASS','coverage_ratio'=>1,'coverage_min_threshold'=>0.98,'expected_bar_count'=>1,'available_bar_count'=>1,'missing_bar_count'=>0,'coverage_reason_code'=>'COVERAGE_THRESHOLD_MET'] as $k=>$v)$this->assertSame($v,$c[$k],$k);}break;
        case 111:$m['coverage_gate_state']='BLOCKED';$m['expected_coverage_gate_state']='BLOCKED';$q=$this->export($m,true);foreach([$q['actual_state']['coverage'],$q['expected_state']['coverage']] as $c){$this->assertSame('NOT_EVALUABLE',$c['coverage_gate_state']);$this->assertSame('BLOCKED',$c['legacy_coverage_gate_state_raw']);}break;
        case 112:$this->assertSame($r['bound_inputs']['config_snapshot_hash'],$r['config_identity']);$this->assertMatchesRegularExpression('/^[a-f0-9]{64}$/',$r['config_identity']);$this->assertSame($r['config_identity'],$e['config_identity']);break;
        case 113:case 118:$this->assertSame('multi_artifact',$r['artifact_changed_scope']);$this->assertNotSame($e['bars_batch_hash'],$a['bars_batch_hash']);$this->assertNotSame($e['indicators_batch_hash'],$a['indicators_batch_hash']);$this->assertNotSame($e['eligibility_batch_hash'],$a['eligibility_batch_hash']);break;
        case 114:$this->assertSame('UNSEALED',$e['seal_state']);$this->assertSame('SEALED',$a['seal_state']);$this->assertSame(2,$e['publication_version']);$this->assertSame(1,$a['publication_version']);$q=$this->export($this->metric('correction_unchanged'));$this->assertSame('NOT_RESEALED_UNCHANGED',$q['replay_result']['correction_lifecycle']['actual']['correction_reseal_status']);$this->assertFalse($q['replay_result']['correction_lifecycle']['actual']['correction_publication_switch']);break;
        case 119:foreach(['bars_batch_hash','indicators_batch_hash','eligibility_batch_hash'] as $f)$this->assertNotSame($e[$f],$a[$f]);break;
        case 120:$this->assertNotSame($e['status'],$a['status']);$this->assertNotSame($e['trade_date_effective'],$a['trade_date_effective']);break;
        case 121:case 169:$oracle=json_decode(file_get_contents(__DIR__.'/../../fixtures/replay/r0025-synthetic-v2-candidate-v5/expected/expected_replay_result.json'),true);foreach(['source_observation_manifest_hash','config_snapshot_hash','temporal_identity_hash','event_factor_hash','formula_registry_hash','read_model_version'] as $f)$this->assertSame($oracle['expected_bound_input_context'][$f],$r['bound_inputs'][$f]);$this->assertSame('replay_publication_manifest_identity_v1',$a['actual_context']['actual_publication_manifest_identity']['schema_version']);$this->assertNotSame($r['bound_inputs']['temporal_identity_hash'],$a['actual_context']['actual_publication_manifest_identity']['temporal_revision_set_hash']);$this->assertSame($r['actual_context'],$a['actual_context']);$this->assertSame($r['expected_context'],$e['expected_context']);$this->assertNotSame($e['expected_context'],$a['actual_context']);break;
        case 122:$this->assertSame('PUBLICATION_EXACT',$r['replay_mode']);$this->assertNull($r['knowledge_cutoff_at']);$this->assertSame('PUBLICATION_EXACT',$e['replay_mode']);$this->assertSame('PUBLICATION_EXACT',$a['replay_mode']);$this->assertSame('VERIFIED',$p['evidence_admission']['identity_verification']['status']);break;
        }
    }
    public function test_missing_expected_count_is_not_reconstructed_from_actual_and_invalid_count_is_refused(): void {
        $m=$this->metric();$c=json_decode($m['expected_context_json'],true);unset($c['expected_coverage_context']['coverage_expected_count'],$c['expected_coverage_context']['expected_bar_count']);$m['expected_context_json']=json_encode($c);$p=$this->export($m);
        $this->assertNull($p['expected_state']['coverage']['expected_bar_count']);$this->assertSame(1,$p['actual_state']['coverage']['expected_bar_count']);$this->assertSame('ADMITTED_INCOMPLETE',$p['evidence_admission']['evidence_admission_state']);
        $c['expected_coverage_context']['coverage_expected_count']='malformed';$m['expected_context_json']=json_encode($c);$this->expectException(RuntimeException::class);$this->expectExceptionMessage('REPLAY_COVERAGE_CONTEXT_INVALID');$this->export($m);
    }

}
