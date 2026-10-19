<?php
use App\Application\MarketData\Services\MarketDataEvidenceExportService;
use Illuminate\Support\Facades\DB;
use Tests\Support\UsesMarketDataSqlite;

/** Per-question source literals and unavailable-state negatives, paired with real MariaDB positive. */
class B19RunAuditPackProofTest extends TestCase
{
    use UsesMarketDataSqlite;
    private $dirs=[];
    protected function setUp(): void {parent::setUp();$this->bootMarketDataSqlite();}
    protected function tearDown(): void {foreach($this->dirs as $d){foreach(glob($d.'/*') as $f)if(is_file($f))unlink($f);if(is_dir($d))rmdir($d);}$this->tearDownMarketDataSqlite();parent::tearDown();}
    private function create(array $v=[]): int {return DB::table('eod_runs')->insertGetId(array_merge(['source'=>'api','trade_date_requested'=>'2026-04-21','trade_date_effective'=>'2026-04-20','terminal_status'=>'HELD','publishability_state'=>'NOT_READABLE','coverage_expected_count'=>4,'coverage_available_count'=>3,'coverage_missing_count'=>1,'coverage_ratio'=>0.75,'coverage_min_threshold'=>0.98,'coverage_gate_state'=>'BLOCKED','coverage_reason_code'=>'RUN_COVERAGE_NOT_EVALUABLE','final_reason_code'=>'RUN_SOURCE_TIMEOUT','config_snapshot_id'=>17,'config_hash'=>hash('sha256','independently retained config bytes'),'observation_manifest_hash'=>hash('sha256','independent source observation bytes'),'notes'=>'source_name=NOTES_SOURCE; source_provider=notes-provider; source_timeout_seconds=17; source_retry_max=4; source_attempt_count=1; source_final_reason_code=RUN_SOURCE_TIMEOUT','created_at'=>'2026-04-21 17:10:00'],$v));}
    private function event($id): void {DB::table('eod_run_events')->insert(['run_id'=>$id,'trade_date_requested'=>'2026-04-21','event_time'=>'2026-04-21 17:02:00','stage'=>'INGEST_BARS','event_type'=>'STAGE_FAILED','severity'=>'ERROR','reason_code'=>'RUN_SOURCE_TIMEOUT','message'=>'Independent failed request','event_payload_json'=>json_encode(['exception_context'=>['source_name'=>'EVENT_SOURCE','provider'=>'event-provider','timeout_seconds'=>17,'retry_max'=>4,'attempt_count'=>1,'success_after_retry'=>false,'final_http_status'=>504,'final_reason_code'=>'RUN_SOURCE_TIMEOUT','captured_at'=>'2026-04-21 17:01:00','attempts'=>[['attempt_number'=>1,'reason_code'=>'RUN_SOURCE_TIMEOUT','http_status'=>504,'throttle_delay_ms'=>100,'backoff_delay_ms'=>0,'will_retry'=>false]]]]),'created_at'=>'2026-04-21 17:02:00']);}
    private function export($id): array {$d=sys_get_temp_dir().'/b19_run_pack_'.uniqid();$this->dirs[]=$d;app(MarketDataEvidenceExportService::class)->exportRunEvidence($id,$d);return [json_decode(file_get_contents($d.'/evidence_pack.json'),true),$d];}
    public static function rules(): array {$r=[];foreach(array_merge([17],range(19,47)) as $n)$r['R'.$n]=[$n];unset($r['R47']);return $r;}
    /** @dataProvider rules */
    public function test_each_run_pack_question_preserves_authoritative_facts_and_unavailability(int $id): void {
        $run=$this->create();$this->event($run);[$p,$d]=$this->export($run);$s=$p['run_summary'];
        switch($id){
        case 17:$this->assertSame('2026-04-21',$s['trade_date_requested']);$this->assertSame('HELD',$s['terminal_status']);$this->assertSame('NOT_READABLE',$p['publication_resolution']['publishability_state']);break;
        case 19:$this->assertSame($run,$s['run_id']);$this->assertSame('HELD',$s['terminal_status']);break;
        case 20:$this->assertNull($p['publication_manifest']);$this->assertNull($p['publication_resolution']['publication_id']);$this->assertSame('NOT_CREATED_OR_NOT_READABLE',$p['publication_resolution']['publication_state']);break;
        case 21:case 44:$this->assertSame($run,$p['run_event_summary']['run_id']);$this->assertSame(1,$p['run_event_summary']['event_count']);$this->assertSame(['INGEST_BARS'=>1],$p['run_event_summary']['stage_counts']);$this->assertSame(['RUN_SOURCE_TIMEOUT'=>1],$p['run_event_summary']['reason_code_counts']);break;
        case 22:case 38:$this->assertSame('2026-04-21',$s['trade_date_requested']);break;
        case 23:case 41:$this->assertSame('2026-04-20',$s['trade_date_effective']);$this->assertTrue($p['fallback_context']['fallback_used']);$this->assertFalse($p['fallback_context']['fallback_publication_readable']);break;
        case 24:case 40:$this->assertSame('HELD',$s['terminal_status']);$this->assertSame('NOT_READABLE',$s['publishability_state']);break;
        case 25:case 45:$this->assertSame([['reason_code'=>'RUN_SOURCE_TIMEOUT','count'=>1]],$p['dominant_reason_codes']);break;
        case 26:$this->assertNull($p['artifact_context']['artifacts']['bars']['artifact_hash']);$this->assertSame('ADMITTED_INCOMPLETE',$p['evidence_admission']['evidence_admission_state']);break;
        case 27:case 42:$this->assertNull($p['publication_resolution']['publication_seal_state']);$this->assertFalse($p['pointer_context']['readable_pointer_validated']);break;
        case 28:case 46:$this->assertSame(17,$s['config_snapshot_id']);$this->assertSame(hash('sha256','independently retained config bytes'),$s['config_hash']);$this->assertNull($s['config_snapshot_hash']);$this->assertSame('ADMITTED_INCOMPLETE',$p['evidence_admission']['evidence_admission_state']);break;
        case 29:$this->assertSame(hash('sha256','independent source observation bytes'),$s['observation_manifest_hash']);break;
        case 30:$this->assertNull($s['temporal_revision_set_hash']);$this->assertNull($s['factor_set_id']);$this->assertNull($s['factor_set_hash']);break;
        case 31:foreach(['canonicalization_version','formula_version','read_model_version'] as $f)$this->assertNull($s[$f]);break;
        case 32:case 43:$this->assertSame('MISSING',$p['pointer_context']['pointer_resolve_status']);$this->assertFalse($p['publication_resolution']['is_current_publication']);break;
        case 33:foreach(['coverage_gate_state'=>'NOT_EVALUABLE','coverage_ratio'=>0.75,'coverage_min_threshold'=>0.98,'expected_bar_count'=>4,'available_bar_count'=>3,'missing_bar_count'=>1,'coverage_reason_code'=>'RUN_COVERAGE_NOT_EVALUABLE'] as $f=>$expected)$this->assertSame($expected,$p['coverage_context'][$f]);break;
        case 34:$this->assertSame('NOT_EVALUABLE',$p['coverage_context']['coverage_gate_state']);$this->assertSame('BLOCKED',$p['coverage_context']['legacy_coverage_gate_state_raw']);$this->assertSame('BLOCKED',DB::table('eod_runs')->where('run_id',$run)->value('coverage_gate_state'));break;
        case 35:$this->assertSame('NOTES_SOURCE',$p['source_context']['source_name']);$this->assertStringContainsString('RUN_SOURCE_TIMEOUT',$p['source_context']['source_summary']);$this->assertSame('RUN_SOURCE_TIMEOUT',$p['source_context']['final_reason_code']);break;
        case 36:$this->assertSame(504,$p['source_attempt_telemetry']['attempts'][0]['http_status']);$this->assertFileExists($d.'/source_attempt_telemetry.json');break;
        case 37:$md=file_get_contents($d.'/anomaly_report.md');$this->assertStringContainsString('HELD',$md);$this->assertStringContainsString('RUN_SOURCE_TIMEOUT',$md);break;
        case 39:$this->assertSame($run,$s['run_id']);$this->assertSame($run,$p['lineage']['run_to_finalize_decision']['run_id']);break;
        }
        $this->assertSame('ADMITTED_INCOMPLETE',$p['evidence_admission']['evidence_admission_state'],'Unavailable applicable identities are never completed by matching NULLs.');
    }
    public function test_missing_source_notes_are_enriched_only_from_own_event_and_manual_path_keeps_provenance(): void {
        $id=$this->create(['notes'=>null]);$this->event($id);[$p]=$this->export($id);$this->assertSame('EVENT_SOURCE',$p['source_context']['source_name']);$this->assertSame('event-provider',$p['source_context']['provider']);$this->assertSame(1,$p['source_context']['attempt_count']);$this->assertSame('RUN_SOURCE_TIMEOUT',$p['source_context']['final_reason_code']);
        $path='storage/app/source/manual/explicit-input.csv';$m=$this->create(['source'=>'manual_file','source_input_file'=>$path,'notes'=>null]);[$q]=$this->export($m);$this->assertSame('explicit-input.csv',$q['source_context']['source_input_file']);$this->assertSame($path,DB::table('eod_runs')->where('run_id',$m)->value('source_input_file'));$this->assertNull($q['source_attempt_telemetry']);
    }
}
