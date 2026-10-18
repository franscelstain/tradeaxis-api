<?php
use App\Application\MarketData\Services\MarketDataEvidenceExportService;
use App\Infrastructure\Persistence\MarketData\{EodEvidenceRepository,EodPublicationRepository,EodCorrectionRepository,ReplayResultRepository};
use Illuminate\Support\Facades\{DB,Schema};
use Tests\Support\UsesMarketDataSqlite;

class B19ArtifactCrossCuttingProofTest extends TestCase
{
    use UsesMarketDataSqlite;
    private $dirs=[];
    protected function setUp(): void {parent::setUp();$this->bootMarketDataSqlite();}
    protected function tearDown(): void {foreach($this->dirs as $dir)if(is_dir($dir)){foreach(glob($dir.'/*') as $f)if(is_file($f))unlink($f);rmdir($dir);}$this->tearDownMarketDataSqlite();parent::tearDown();}
    private function dir(): string {$d=sys_get_temp_dir().'/b19_cross_'.uniqid();$this->dirs[]=$d;return $d;}
    private function service() {return new MarketDataEvidenceExportService(new EodEvidenceRepository(),new EodPublicationRepository(),new EodCorrectionRepository());}
    private function sample(): array {return json_decode(file_get_contents(__DIR__.'/../../fixtures/replay/b19-identity-executed-metric-v1.json'),true)['metric'];}
    private function export(array $m,string $dir): array {(new ReplayResultRepository())->upsertMetric(array_intersect_key($m,array_flip(Schema::getColumnListing('md_replay_daily_metrics'))));return $this->service()->exportReplayEvidence($m['replay_id'],$m['trade_date'],$dir);}
    public static function secrets(): array {return [['credentials'],['access_token'],['internal_sensitive_token'],['password'],['client_secret'],['secret'],['internal_token']];}
    /** @dataProvider secrets */
    public function test_sensitive_fixture_metadata_is_refused_before_it_can_leak(string $key): void {
        $m=$this->sample();$e=json_decode($m['expected_context_json'],true);$e[$key]='b19-sensitive-sentinel';$m['expected_context_json']=json_encode($e);$dir=$this->dir();
        try{$this->export($m,$dir);$this->fail('sensitive fixture metadata exported');}catch(RuntimeException $error){$this->assertStringContainsString('EVIDENCE_SENSITIVE_CONTENT',$error->getMessage());$this->assertStringNotContainsString('b19-sensitive-sentinel',$error->getMessage());}
        foreach(glob($dir.'/*') as $file)$this->assertStringNotContainsString('b19-sensitive-sentinel',file_get_contents($file));
    }
    public function test_csv_and_markdown_sinks_refuse_embedded_credentials(): void {
        $method=new ReflectionMethod($this->service(),'writeCsv');$method->setAccessible(true);$dir=$this->dir();mkdir($dir);
        $this->expectException(RuntimeException::class);$this->expectExceptionMessage('EVIDENCE_SENSITIVE_CONTENT');
        $method->invoke($this->service(),$dir.'/test.csv',['source_row_ref'],[['source_row_ref'=>'https://user:password@example.test/private']]);
    }
    public function test_exact_serialized_file_hashes_timestamp_context_and_historical_inputs_are_preserved(): void {
        $m=$this->sample();$dir=$this->dir();$r=$this->export($m,$dir);$this->assertFileExists($dir.'/artifact_manifest.json');$index=json_decode(file_get_contents($dir.'/artifact_manifest.json'),true);
        $this->assertSame('artifact_file_manifest_v1',$index['schema_version']);$this->assertSame('Asia/Jakarta',$index['timestamp_context']['platform_timezone']);
        $this->assertSame('platform timezone for offset-free database timestamps; explicit offsets retained as stated alternatives',$index['timestamp_context']['representation']);
        $this->assertSame(array_values(array_diff($r['files'],['artifact_manifest.json'])),array_keys($index['files']));
        foreach($index['files'] as $file=>$meta){$this->assertSame(hash('sha256',file_get_contents($dir.'/'.$file)),$meta['sha256']);$this->assertSame(filesize($dir.'/'.$file),$meta['bytes']);}
        $old=$index['files'];$canonical=[$m['bars_batch_hash'],$m['indicators_batch_hash'],$m['eligibility_batch_hash']];$before=$m['actual_context_json'];
        $m['fixture_version']='b19-independent-negative-context-v2';$m['expected_status']='HELD';$m['comparison_result']='MISMATCH';$m['replay_status']='FAIL';$newDir=$this->dir();$this->export($m,$newDir);
        $next=json_decode(file_get_contents($newDir.'/artifact_manifest.json'),true);$this->assertNotSame($old['replay_expected_state.json']['sha256'],$next['files']['replay_expected_state.json']['sha256']);$this->assertNotSame($old['replay_evidence_pack.json']['sha256'],$next['files']['replay_evidence_pack.json']['sha256']);
        $row=DB::table('md_replay_daily_metrics')->first();$this->assertSame($canonical,[$row->bars_batch_hash,$row->indicators_batch_hash,$row->eligibility_batch_hash]);$this->assertSame($before,$row->actual_context_json);
    }
    public function test_stale_readable_publication_cannot_survive_a_held_run_export_in_the_same_directory(): void {
        DB::table('eod_runs')->insert(['run_id'=>707,'trade_date_requested'=>'2026-10-19','source'=>'api','terminal_status'=>'HELD','publishability_state'=>'NOT_READABLE','created_at'=>'2026-10-19 17:00:00']);
        $dir=$this->dir();mkdir($dir);$bytes='{"trade_date":"2026-10-19","seal_state":"SEALED","publication_id":999}';file_put_contents($dir.'/publication_manifest.json',$bytes);
        try{$this->service()->exportRunEvidence(707,$dir);$this->fail('contradictory stale publication silently retained');}catch(RuntimeException $e){$this->assertStringContainsString('EVIDENCE_OUTPUT_CONFLICT',$e->getMessage());}
        $this->assertSame($bytes,file_get_contents($dir.'/publication_manifest.json'),'historical files must be preserved; choose a fresh output directory');
    }
    public function test_delivered_companion_and_member_bytes_are_immutable(): void {
        $m=$this->sample();$d=$this->dir();$this->export($m,$d);$before=file_get_contents($d.'/artifact_manifest.json');$member=file_get_contents($d.'/replay_expected_state.json');$this->export($m,$d);$this->assertSame($before,file_get_contents($d.'/artifact_manifest.json'));
        $m['fixture_version']='independent-new-version';try{$this->export($m,$d);$this->fail('rewrote delivered proof');}catch(RuntimeException $e){$this->assertStringContainsString('EVIDENCE_ARTIFACT_IMMUTABLE',$e->getMessage());}$this->assertSame($before,file_get_contents($d.'/artifact_manifest.json'));$this->assertSame($member,file_get_contents($d.'/replay_expected_state.json'));
    }

}
