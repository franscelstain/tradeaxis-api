<?php

use App\Application\MarketData\Services\EodBarsIngestService;
use App\Application\MarketData\Services\MarketDataEvidenceExportService;
use App\Infrastructure\MarketData\Source\LocalFileEodBarsAdapter;
use App\Infrastructure\MarketData\Source\PublicApiEodBarsAdapter;
use App\Infrastructure\Persistence\MarketData\EodArtifactRepository;
use App\Infrastructure\Persistence\MarketData\EodPublicationRepository;
use App\Infrastructure\Persistence\MarketData\SourceObservationRepository;
use App\Infrastructure\Persistence\MarketData\TickerMasterRepository;
use App\Models\EodRun;
use Illuminate\Support\Facades\DB;
use Tests\Support\UsesMarketDataSqlite;

require_once __DIR__.'/ReasonCodeSeedExecutionTest.php';

class B19ConflictReasonPipelineTest extends TestCase
{
    use UsesMarketDataSqlite;
    use \Tests\Support\MocksProducerInputCapture;
    private $paths=[];
    protected function setUp(): void {
        parent::setUp();$this->bootMarketDataSqlite();
        $seed=new ReasonCodeSeedExecutionTest();$method=new ReflectionMethod($seed,'executableStatement');$method->setAccessible(true);
        DB::table('eod_reason_codes')->delete();DB::unprepared($method->invoke($seed));
    }
    protected function tearDown(): void {
        foreach ($this->paths as $p) {
            if (!is_dir($p)) continue;
            $this->assertSame(0,strpos(realpath($p),realpath(sys_get_temp_dir()).DIRECTORY_SEPARATOR));
            $it=new RecursiveIteratorIterator(new RecursiveDirectoryIterator($p,FilesystemIterator::SKIP_DOTS),RecursiveIteratorIterator::CHILD_FIRST);
            foreach ($it as $f) $f->isDir()?rmdir($f->getPathname()):unlink($f->getPathname());
            rmdir($p);
        }
        $this->tearDownMarketDataSqlite();parent::tearDown();
    }
    public static function paths(): array {return ['normal'=>[false],'recovered'=>[true]];}
    /** @dataProvider paths */
    public function test_stable_listing_conflicts_and_duplicate_winners_reach_real_storage_and_export(bool $recovered): void
    {
        config()->set('market_data.platform.timezone','Asia/Jakarta');
        config()->set('market_data.source.canonicalization_version','canon_v1');
        config()->set('market_data.scope.raw_product_code','RAW');
        $tickers=$this->createMock(TickerMasterRepository::class);
        $tickers->method('resolveTickerIdsByCodes')->willReturn(['CLEAN'=>1,'ALIAS_A'=>2,'ALIAS_B'=>3,'DUP'=>4]);
        $tickers->method('resolveTemporalContextsByCodes')->willReturn(['CLEAN'=>['listing_id'=>701,'board_code'=>'RG'],'ALIAS_A'=>['listing_id'=>702,'board_code'=>'RG'],'ALIAS_B'=>['listing_id'=>702,'board_code'=>'RG'],'DUP'=>['listing_id'=>704,'board_code'=>'RG']]);
        $pub=$this->createMock(EodPublicationRepository::class);
        $pub->method('findCurrentPublicationForTradeDate')->willReturn(null);
        $pub->method('getOrCreateCandidatePublication')->willReturn((object)['publication_id'=>990,'publication_version'=>1]);
        $obs=$this->createMock(SourceObservationRepository::class);$obs->method('existsAccepted')->willReturn(true);$obs->method('manifestHashForRun')->willReturn('independent-input-manifest');
        $rows=[];
        foreach ([['CLEAN','clean',9001,108],['ALIAS_A','conflict_a',9002,108],['ALIAS_B','conflict_b',9003,106],['ALIAS_A','conflict_c',9004,107],['DUP','dup_b',9005,108],['DUP','dup_a',9006,108]] as [$code,$ref,$id,$close]) $rows[]=['ticker_code'=>$code,'trade_date'=>'2026-03-24','open'=>100,'high'=>110,'low'=>99,'close'=>$close,'volume'=>1000,'adj_close'=>104,'source_name'=>'YAHOO_FINANCE','source_row_ref'=>$ref,'source_observation_id'=>$id,'source_observation_persisted'=>true,'captured_at'=>'2026-03-24T17:00:00+07:00'];
        DB::table('eod_runs')->insert(['run_id'=>91,'source'=>'api','trade_date_requested'=>'2026-03-24','trade_date_effective'=>'2026-03-24','terminal_status'=>'SUCCESS','publishability_state'=>'NOT_READABLE','sealed_at'=>null,'created_at'=>'2026-03-24 17:00:00','updated_at'=>'2026-03-24 17:00:00']);
        $run=new EodRun(['run_id'=>91,'trade_date_requested'=>'2026-03-24','knowledge_cutoff_at'=>'2026-03-24 18:00:00']);
        $service=new EodBarsIngestService($this->createMock(LocalFileEodBarsAdapter::class),$this->createMock(PublicApiEodBarsAdapter::class),$tickers,new EodArtifactRepository(),$pub,null,$obs,null,null,$this->mockProducerInputCapture());
        $method=$recovered?'ingestRecoveredRowsPartial':'ingestAcquiredRows';
        $service->{$method}($run,'2026-03-24','api',$rows,['source_acquisition_state'=>'SUCCESS']);
        $this->assertSame([1,4],DB::table('eod_bars')->orderBy('ticker_id')->pluck('ticker_id')->map(function($n){return (int)$n;})->all(),'same stable listing across two compatibility ticker IDs is one conflict set');
        $bad=DB::table('eod_invalid_bars')->where('run_id',91)->orderBy('source_row_ref')->get()->keyBy('source_row_ref');
        foreach (['conflict_a'=>9002,'conflict_b'=>9003,'conflict_c'=>9004] as $ref=>$id) {
            $this->assertSame('BAR_CONFLICTING_SOURCE_ROWS',$bad[$ref]->invalid_reason_code);
            $this->assertSame(702,(int)$bad[$ref]->listing_id);
            $this->assertSame($id,(int)$bad[$ref]->source_observation_id);
        }
        $this->assertSame('BAR_DUPLICATE_SOURCE_ROW',$bad['dup_b']->invalid_reason_code);
        $this->assertSame(9006,(int)DB::table('eod_bars')->where('listing_id',704)->value('source_observation_id'),'deterministic equivalent duplicate winner remains lexicographically smallest source reference');
        $this->assertCount(4,$bad);
        $dir=sys_get_temp_dir().'/b19_conflict_pipeline_'.uniqid();$this->paths[]=$dir;
        app(MarketDataEvidenceExportService::class)->exportRunEvidence(91,$dir);
        $csv=array_map('str_getcsv',file($dir.'/invalid_bars_export.csv',FILE_IGNORE_NEW_LINES));
        $header=array_shift($csv);$byRef=[];foreach($csv as $line){$row=array_combine($header,$line);$byRef[$row['source_row_ref']]=$row;}
        foreach (['conflict_a'=>9002,'conflict_b'=>9003,'conflict_c'=>9004] as $ref=>$id) {
            $this->assertSame('BAR_CONFLICTING_SOURCE_ROWS',$byRef[$ref]['invalid_reason_code']);
            $this->assertSame((string)$id,$byRef[$ref]['source_observation_id']);
            $this->assertSame('702',$byRef[$ref]['listing_id']);
        }
        $this->assertSame('BAR_DUPLICATE_SOURCE_ROW',$byRef['dup_b']['invalid_reason_code']);
        if (getenv('B19_COMPLETION_ARTIFACT_DIR')) {
            $capture=getenv('B19_COMPLETION_ARTIFACT_DIR').'/conflict_'.($recovered?'recovered':'normal');if(!is_dir($capture))mkdir($capture,0775,true);
            copy($dir.'/invalid_bars_export.csv',$capture.'/invalid_bars_export.csv');
            file_put_contents($capture.'/independent_inputs.json',json_encode($rows,JSON_PRETTY_PRINT));
            file_put_contents($capture.'/persisted_invalid.json',json_encode($bad->values()->all(),JSON_PRETTY_PRINT));
            file_put_contents($capture.'/persisted_canonical.json',json_encode(DB::table('eod_bars')->get()->all(),JSON_PRETTY_PRINT));
        }
    }
    public function test_conflict_is_registered_active_bar_hard_and_duplicate_is_still_warn(): void
    {
        $r=DB::table('eod_reason_codes')->where('code','BAR_CONFLICTING_SOURCE_ROWS')->first();
        $this->assertNotNull($r);$this->assertSame('BAR',$r->category);$this->assertSame('HARD',$r->severity);$this->assertSame(1,(int)$r->is_active);
        $this->assertSame('WARN',DB::table('eod_reason_codes')->where('code','BAR_DUPLICATE_SOURCE_ROW')->value('severity'));
    }
}
