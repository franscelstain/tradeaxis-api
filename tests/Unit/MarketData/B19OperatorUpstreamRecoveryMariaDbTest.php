<?php

use App\Application\MarketData\DTOs\MarketDataStageInput;
use App\Application\MarketData\Services\MarketDataPipelineService;
use App\Console\Commands\MarketData\SealDatasetCommand;
use App\Infrastructure\Persistence\MarketData\EodArtifactRepository;
use App\Infrastructure\Persistence\MarketData\EodPublicationRepository;
use App\Models\EodRun;
use Illuminate\Support\Facades\DB;
use Symfony\Component\Console\Tester\CommandTester;
use Tests\Support\R0025SyntheticV2World;
require_once __DIR__.'/B19OwnerFenceMariaDbTest.php';

class B19OperatorUpstreamRecoveryMariaDbTest extends B19OwnerFenceMariaDbTest
{
    use \Tests\Support\RetainsB19ObservedEvidence;
    protected function tearDown(): void { \Carbon\Carbon::setTestNow();parent::tearDown(); }

    public static function requiredArtifactFamilies(): array
    {
        return ['bars'=>['bars'],'indicators'=>['indicators'],'eligibility'=>['eligibility']];
    }

    private function candidateHistory(int $publicationId): array
    {
        $out=[];
        foreach(['bars','indicators','eligibility']as $family)
            $out[$family]=DB::table('eod_'.$family.'_history')->where('publication_id',$publicationId)->get()->toJson();
        return $out;
    }

    /** @dataProvider requiredArtifactFamilies */
    public function test_upstream_completeness_recovers_only_through_new_authenticated_execution_and_preserves_old_artifacts(string $artifact): void
    {
        $sourceDate='2026-03-23';
        $world=R0025SyntheticV2World::build(['http_fetcher'=>function()use(&$sourceDate){
            $body=json_decode(file_get_contents(R0025SyntheticV2World::packagePath().'/inputs/provider_response.json'),true);
            $body['chart']['result'][0]['timestamp']=[(new DateTimeImmutable($sourceDate.' 09:00:00',new DateTimeZone('Asia/Jakarta')))->getTimestamp()];
            return ['status'=>200,'headers'=>['Content-Type'=>'application/json'],'body'=>json_encode($body)];
        }]);$pipeline=$world['pipeline'];$date='2026-03-24';
        $prior=EodRun::findOrFail($world['run_id']);$publications=new EodPublicationRepository();
        DB::table('md_market_calendar_revisions')->insert([
            'revision_uid'=>hash('sha256','e049-upstream-recovery-20260324'),'market_code'=>'IDX','market_segment'=>'REGULAR',
            'cal_date'=>$date,'timezone'=>'Asia/Jakarta','is_trading_day'=>1,'is_half_day'=>0,'session_state'=>'COMPLETED',
            'session_open_at'=>$date.' 09:00:00','session_close_at'=>$date.' 16:00:00','completed_at'=>$date.' 16:00:00',
            'recorded_at'=>$date.' 17:00:00','source_ref'=>'independent-upstream-recovery-next-session','source_version'=>'e049-test-only-v1',
            'provenance_tier'=>'VERIFIED','reconciled_at'=>$date.' 17:00:00','reconciliation_source_ref'=>'independent-upstream-recovery-next-session',
        ]);
        $sourceDate=$date;\Carbon\Carbon::setTestNow('2026-03-25 10:30:00');
        $history=DB::table('eod_bars_history')->where('publication_id',$world['publication_id'])->get()->toJson();
        $run=null;
        foreach(['INGEST_BARS'=>'completeIngest','COMPUTE_INDICATORS'=>'completeIndicators','BUILD_ELIGIBILITY'=>'completeEligibility','HASH'=>'completeHash']as $stage=>$method)
            $run=$pipeline->$method(new MarketDataStageInput($date,'api',$run?$run->run_id:null,$stage,null,false,null,'full_publish'));
        $candidateId=(int)$run->publication_id;
        $table='eod_'.$artifact;
        $rows=DB::table($table)->where('publication_id',$candidateId)->get()->map(fn($row)=>(array)$row)->all();
        $this->assertNotEmpty($rows);
        // Controlled corruption of an unsealed test candidate, never an authorized repair.
        DB::table($table)->where('publication_id',$candidateId)->delete();
        $preSealHistory=$this->candidateHistory($candidateId);
        app()->instance(MarketDataPipelineService::class,$pipeline);
        $command=new SealDatasetCommand();$command->setLaravel(app());$tester=new CommandTester($command);
        $exit=$tester->execute(['--requested_date'=>$date,'--source_mode'=>'api','--run_id'=>$run->run_id]);$output=$tester->getDisplay();
        $this->assertSame(1,$exit,$output);$this->assertStringContainsString('reason_code=DATASET_MANIFEST_INVALID',$output);
        $this->assertStringContainsString('Repair upstream artifact completeness and revalidate before seal.',$output);
        $this->assertSame($preSealHistory,$this->candidateHistory($candidateId),'Incomplete seal must roll back every partially copied artifact family.');
        $failed=EodRun::findOrFail($run->run_id);
        $this->assertSame('FAILED',$failed->lifecycle_state);
        $this->assertSame('FAILED',$failed->terminal_status);
        $this->assertNotNull(DB::table('eod_run_owner_fences')->where('trade_date_requested',$date)->value('released_at'));
        $this->assertSame('UNSEALED',DB::table('eod_publications')->where('publication_id',$candidateId)->value('seal_state'));
        $this->assertNull($publications->resolveCurrentReadablePublicationForTradeDate($date));
        $this->assertSame((int)$world['publication_id'],(int)$publications->resolveCurrentReadablePublicationForTradeDate($prior->trade_date_requested)->publication_id);
        $error=null;
        try{
            $artifacts=new EodArtifactRepository();
            if($artifact==='bars')$artifacts->replaceBars($date,$candidateId,$run->run_id,$rows,[],false);
            elseif($artifact==='indicators')$artifacts->replaceIndicators($date,$run->run_id,$rows,$candidateId,false);
            else $artifacts->replaceEligibility($date,$run->run_id,$rows,$candidateId,false);
        }catch(RuntimeException $e){$error=$e->getMessage();}
        $this->assertStringContainsString('RUN_OWNER_', (string)$error,'Restoring completeness may not revive a released writer.');
        $this->assertSame(0,DB::table($table)->where('publication_id',$candidateId)->count());
        $failedRun=DB::table('eod_runs')->where('run_id',$run->run_id)->first();
        $failedCandidate=DB::table('eod_publications')->where('publication_id',$candidateId)->first();
        $observations=DB::table('md_source_observations')->where('run_id',$run->run_id)->orderBy('source_observation_id')->get()->toJson();
        \Carbon\Carbon::setTestNow('2026-03-25 10:30:00');
        $retry=$pipeline->runDaily($date,'api');
        $this->assertNotSame((int)$run->run_id,(int)$retry->run_id,'Recovery requires new legitimate acquisition, not the released token.');
        $this->assertSame('READABLE',$retry->publishability_state);
        $this->assertSame('SUCCESS',$retry->terminal_status);
        $this->assertNotSame((int)$world['publication_id'],(int)$retry->publication_id,'Prior date cannot be relabeled as the recovered requested date.');
        $newCandidate=DB::table('eod_publications')->where('run_id',$retry->run_id)->first();
        $this->assertNotNull($newCandidate);$this->assertSame('SEALED',$newCandidate->seal_state);
        $this->assertGreaterThan(0,DB::table($table.'_history')->where('publication_id',$newCandidate->publication_id)->count());
        $this->assertEquals($failedRun,DB::table('eod_runs')->where('run_id',$run->run_id)->first());
        $this->assertEquals($failedCandidate,DB::table('eod_publications')->where('publication_id',$candidateId)->first());
        $this->assertSame($preSealHistory,$this->candidateHistory($candidateId),'Recovery must retain the failed candidate forensic state.');
        $this->assertSame($observations,DB::table('md_source_observations')->where('run_id',$run->run_id)->orderBy('source_observation_id')->get()->toJson());
        $this->assertSame($history,DB::table('eod_bars_history')->where('publication_id',$world['publication_id'])->get()->toJson());
        $fresh=$this->onIndependentOwnerConnection(function()use($publications,$date,$candidateId,$newCandidate,$table){
            return ['connection_id'=>DB::selectOne('SELECT CONNECTION_ID() AS id')->id,'pointer'=>$publications->resolveCurrentReadablePublicationForTradeDate($date),
                'old_missing'=>DB::table($table)->where('publication_id',$candidateId)->count(),
                'recomputed'=>DB::table($table.'_history')->where('publication_id',$newCandidate->publication_id)->count()];
        });
        $this->assertNotSame((int)DB::selectOne('SELECT CONNECTION_ID() AS id')->id,(int)$fresh['connection_id']);
        $this->assertSame((int)$retry->publication_id,(int)$fresh['pointer']->publication_id);
        $this->assertSame($date,(string)$fresh['pointer']->trade_date);
        $this->assertSame(0,$fresh['old_missing']);$this->assertGreaterThan(0,$fresh['recomputed']);
        $this->retainObservedWorld('upstream_regeneration_new_owner_'.$artifact,[$prior->run_id,$run->run_id,$retry->run_id],compact('artifact','preSealHistory','exit','output','error','failedRun','failedCandidate','fresh'));
    }
}
