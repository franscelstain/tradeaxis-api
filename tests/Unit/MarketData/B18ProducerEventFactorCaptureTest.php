<?php

use App\Application\MarketData\Services\AdjustmentFactorSetService;
use App\Infrastructure\Persistence\MarketData\EodRunRepository;
use App\Infrastructure\Persistence\MarketData\ProducerEventFactorCapture;
use App\Infrastructure\Persistence\MarketData\ProducerRawInputLineage;
use App\Infrastructure\Persistence\MarketData\RunInputCaptureRepository;
use App\Infrastructure\Persistence\MarketData\SourceObservationRepository;
use Illuminate\Support\Facades\DB;
use Tests\Support\UsesMarketDataSqlite;

class B18ProducerEventFactorCaptureTest extends TestCase
{
    use UsesMarketDataSqlite;
    private $run;
    protected function setUp(): void
    {
        parent::setUp(); $this->bootMarketDataSqlite(); \Carbon\Carbon::setTestNow('2026-08-13 12:00:00');
        $this->run = (new EodRunRepository())->getOrCreateOwningRun('2026-07-28','api','COMPUTE_INDICATORS',null,'c08');
        DB::table('eod_publications')->insert(['publication_id'=>99,'trade_date'=>'2026-07-28','run_id'=>$this->run->run_id,'publication_version'=>1,'seal_state'=>'UNSEALED','created_at'=>'2026-08-13 12:00:00']);
    }
    protected function tearDown(): void { \Carbon\Carbon::setTestNow(); $this->tearDownMarketDataSqlite(); parent::tearDown(); }
    private function seed(): void
    {
        for ($id=1;$id<=11;$id++) {
            if ($id!==10) DB::table('md_listings')->insert(['listing_id'=>$id,'listing_uid'=>hash('sha256','listing'.$id),'legacy_ticker_id'=>$id,
                'instrument_id'=>$id,'exchange_code'=>'IDX','market_segment'=>'REGULAR','board_code'=>'MAIN','listed_date'=>'2010-01-01','listing_state'=>'LISTED','recorded_at'=>'2020-01-01 00:00:00','created_at'=>'2020-01-01 00:00:00']);
            $source=new SourceObservationRepository();
            $observation=$source->recordAcceptedRows($source->capture(['acquisition_batch_id'=>1,'attempt_uid'=>'c08-event-'.$id,'requested_trade_date'=>'2026-07-15',
                'source_mode'=>'api','source_name'=>'IDX','provider'=>'IDX','provider_symbol'=>'E'.$id,'sanitized_request_identity'=>'event:'.$id,
                'adapter_version'=>'fixture-v1','payload'=>json_encode(['event'=>$id,'ratio'=>[1,5]]),'acquired_at'=>'2026-08-01 00:00:00']),
                [['ticker_code'=>'E'.$id,'trade_date'=>'2026-07-15','open'=>100,'high'=>110,'low'=>90,'close'=>105,'volume'=>1000,'source_row_ref'=>'event:'.$id]]);
            DB::table('md_corporate_action_revisions')->insert(['corporate_action_revision_id'=>$id,'event_uid'=>hash('sha256','event'.$id),'revision_number'=>1,
                'listing_id'=>$id,'action_type_code'=>$id===4?'CASH_DIVIDEND':'STOCK_SPLIT','lifecycle_state'=>$id===9?'CANCELLED':'EFFECTIVE',
                'verification_state'=>$id===6?'UNVERIFIED':'AUTHORITATIVE_VERIFIED','ex_date'=>$id===11?null:($id===7?'2026-08-01':'2026-07-15'),
                'effective_at'=>'2026-07-15 00:00:00','terms_json'=>json_encode(['ratio'=>['from'=>1,'to'=>5]]),'source_observation_id'=>$observation['source_observation_id'],
                'recorded_at'=>$id===8?'2026-08-14 00:00:00':'2026-08-01 00:00:00','supersedes_revision_id'=>$id===6?5:null]);
            if ($id<=2) DB::table('md_source_scale_assessments')->insert(['source_scale_assessment_id'=>$id,'assessment_uid'=>hash('sha256','assessment'.$id),
                'revision_number'=>1,'provider'=>'YAHOO_FINANCE','listing_id'=>$id,'corporate_action_revision_id'=>$id,'source_scale_state'=>$id===1?'AS_TRADED':'PROVIDER_BACK_ADJUSTED',
                'scale_effective_from'=>'2026-07-15','assessment_version'=>AdjustmentFactorSetService::ASSESSMENT_VERSION,
                'evidence_observation_set_hash'=>hash('sha256',json_encode([(int)$observation['source_observation_id']])),
                'evidence_json'=>json_encode(['observation_ids'=>[(int)$observation['source_observation_id']],'source_ref'=>'IDX:event:'.$id]),
                'recorded_at'=>'2026-08-02 00:00:00','created_at'=>'2026-08-02 00:00:00']);
        }
        $base=(array) DB::table('md_source_scale_assessments')->where('source_scale_assessment_id',1)->first();
        foreach ([10,11,12,13] as $id) {
            $a=$base;$a['source_scale_assessment_id']=$id;$a['assessment_uid']=hash('sha256','assessment-extra'.$id);
            $a['revision_number']=$id===11?3:0;
            $a['recorded_at']=$id===11?'2026-08-14 00:00:00':'2026-08-01 00:00:00';
            $a['supersedes_assessment_id']=$id===11?1:null;
            $a['provider']=$id===12?'OTHER_PROVIDER':'YAHOO_FINANCE';
            DB::table('md_source_scale_assessments')->insert($a);
        }
        DB::table('md_source_scale_assessments')->where('source_scale_assessment_id',1)->update(['supersedes_assessment_id'=>10]);
    }
    private function capsule(): array
    {
        $repo=new RunInputCaptureRepository(); $found=[];
        foreach ($repo->forRun($this->run->run_id) as $row) { $p=$repo->verify($row); if($p['selection_context']['operation']==='event-factor-revisions/v1')$found[]=$p; }
        $this->assertCount(1,$found);return $found[0];
    }
    public function test_c08_actual_direct_producer_retains_selected_rejected_terms_assessments_and_factor_content(): void
    {
        $this->seed();$service=new AdjustmentFactorSetService();$result=$service->ensureForPublication($this->run,99,'2026-07-28',[]);
        $this->assertSame(['APPLIED','HELD_PROVIDER_BACK_ADJUSTED','HELD_SOURCE_SCALE_UNKNOWN'],array_column($result['decisions'],'decision_state'));
        $p=$this->capsule();$c=$p['rows'][0];
        $this->assertCount(11,$c['tables']['md_corporate_action_revisions']);$this->assertCount(4,$c['trace']['selected_events']);$this->assertCount(7,$c['omitted_revisions']);
        $this->assertCount(7,$c['tables']['md_source_scale_assessments']);$this->assertCount(4,$c['assessment_omissions']);$this->assertEquals([['revision_id'=>4,'basis'=>'TYPE_REGISTRY_HAS_NO_SCALED_CONTINUITY']],$c['factor_omissions']);$this->assertCount(3,$c['tables']['md_adjustment_factor_decisions']);$this->assertCount(1,$c['tables']['md_adjustment_factors']);
        ProducerEventFactorCapture::assertValid($c,$p['selection_context']);
        $this->assertSame($result,$service->ensureForPublication($this->run,99,'2026-07-28',[]));$this->assertSame($p,$this->capsule());
    }
    public function test_c08_repaired_hash_cannot_hide_single_member_or_decision_damage(): void
    {
        $this->seed();(new AdjustmentFactorSetService())->ensureForPublication($this->run,99,'2026-07-28',[]);$p=$this->capsule();
        $mutations=[
            static function(&$c){array_pop($c['trace']['selected_events']);},
            static function(&$c){unset($c['tables']['md_corporate_action_revisions'][10]['effective_at']);},
            static function(&$c){unset($c['tables']['md_source_scale_assessments'][3]['recorded_at']);},
            static function(&$c){array_pop($c['omitted_revisions']);},
            static function(&$c){array_pop($c['assessment_omissions']);},
            static function(&$c){$c['trace']['canonical_payload']['decisions'][0]['reason_code']='FORGED';$c['factor_payload_json']=json_encode($c['trace']['canonical_payload']);$h=hash('sha256',$c['factor_payload_json']);$c['tables']['md_adjustment_factor_sets'][0]['content_hash']=$h;$c['tables']['md_adjustment_factor_sets'][0]['factor_set_uid']=$h;$c['result']['factor_set_hash']=$h;},
            static function(&$c){$c['tables']['md_corporate_action_revisions'][0]['terms_json']='{"ratio":{"from":1,"to":9}}';},
            static function(&$c){$c['trace']['assessments'][1]['source_scale_state']='UNKNOWN';},
            static function(&$c){$c['result']['decisions'][0]['decision_state']='HELD_SOURCE_SCALE_UNKNOWN';},
            static function(&$c){$c['tables']['md_adjustment_factor_decisions'][0]['candidate_price_factor']='9';},
            static function(&$c){$c['tables']['md_adjustment_factors'][0]['price_factor']='9';},
            static function(&$c){$c['result']['held_events_by_ticker']=[];},
            static function(&$c){$c['tables']['md_source_observations'][1]['payload_hash']=str_repeat('a',64);},
        ];
        foreach($mutations as $i=>$mutate){$c=$p['rows'][0];$mutate($c);$c['table_hashes']=array_map([ProducerRawInputLineage::class,'hash'],$c['tables']);
            try{ProducerEventFactorCapture::assertValid($c,$p['selection_context']);$this->fail('Mutation passed: '.$i);}catch(RuntimeException $e){$this->assertStringContainsString('INPUT_CAPTURE_FACTOR_',$e->getMessage());}}
    }
    public function test_c08_empty_population_and_missing_owner_fail_closed(): void
    {
        $result=(new AdjustmentFactorSetService())->ensureForPublication($this->run,99,'2026-07-28',[]);$this->assertSame([],$result['decisions']);
        $p=$this->capsule();$this->assertSame('NO_EVENT_REVISIONS_IN_PRODUCER_POPULATION',$p['rows'][0]['empty_basis']);ProducerEventFactorCapture::assertValid($p['rows'][0],$p['selection_context']);
        $this->expectExceptionMessage('INPUT_CAPTURE_RUN_NOT_FOUND');(new AdjustmentFactorSetService())->ensureForPublication((object)['run_id'=>9999],99,'2026-07-28',[]);
    }
    public function test_c08_changed_omitted_revision_conflicts_without_overwriting_capture(): void
    {
        $this->seed();$service=new AdjustmentFactorSetService();$service->ensureForPublication($this->run,99,'2026-07-28',[]);$before=$this->capsule();
        DB::table('md_corporate_action_revisions')->where('corporate_action_revision_id',11)->update(['terms_json'=>'{"changed":"unselected-input"}']);
        try{$service->ensureForPublication($this->run,99,'2026-07-28',[]);$this->fail('Changed input retry accepted');}
        catch(RuntimeException $e){$this->assertStringContainsString('INPUT_CAPTURE_CONFLICT',$e->getMessage());}
        $this->assertSame($before,$this->capsule());ProducerEventFactorCapture::assertValid($before['rows'][0],$before['selection_context']);
    }
    public function test_c08_historical_missing_binding_never_reconstructs_current_event_inputs(): void
    {
        $before=DB::table('md_run_input_captures')->count();
        DB::table('eod_runs')->where('run_id',$this->run->run_id)->update(['request_mode'=>'replay_verify']);
        $this->run=DB::table('eod_runs')->where('run_id',$this->run->run_id)->first();
        try{(new AdjustmentFactorSetService())->ensureForPublication($this->run,99,'2026-07-28',[]);$this->fail('Historical reconstruction accepted');}
        catch(RuntimeException $e){$this->assertSame('INPUT_CAPTURE_FACTOR_HISTORICAL_BINDING_REQUIRED',$e->getMessage());}
        $this->assertSame(0,DB::table('md_adjustment_factor_sets')->count());$this->assertSame($before,DB::table('md_run_input_captures')->count());
    }

}
