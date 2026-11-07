<?php
use App\Infrastructure\Persistence\MarketData\EodRunRepository;
use App\Infrastructure\Persistence\MarketData\EodPublicationRepository;
use App\Infrastructure\Persistence\MarketData\RunInputCaptureRepository;
use Illuminate\Support\Facades\DB;
require_once __DIR__.'/B19OwnerFenceMariaDbTest.php';
class B19ReplayRollbackBoundaryMariaDbTest extends B19OwnerFenceMariaDbTest
{
    protected function tearDown(): void { \Carbon\Carbon::setTestNow(); parent::tearDown(); }
    private function projection(): array
    {
        $result=[];foreach(DB::select('SHOW TABLES')as$entry){$t=(string)array_values((array)$entry)[0];if(!preg_match('/^(eod_|md_)[a-z0-9_]+$/D',$t))continue;$rows=array_map(fn($r)=>(array)$r,DB::table($t)->get()->all());usort($rows,fn($a,$b)=>strcmp(json_encode($a),json_encode($b)));$result[$t]=$rows;}ksort($result);return $result;
    }
    public function test_replay_factory_cannot_leave_persisted_projections_when_caller_commits(): void
    {
        [$date,$old,$active]=$this->completedOwnerWithLiveSuccessor();$repo=new EodRunRepository();$repo->finalize($active,['lifecycle_state'=>'COMPLETED','terminal_status'=>'HELD','publishability_state'=>'NOT_READABLE']);
        $before=$this->projection();$error=null;DB::beginTransaction();try{
            $run=$repo->createAsKnownReplayRun($date,$old->knowledge_cutoff_at,'api');
            (new EodPublicationRepository())->getOrCreateCandidatePublication($run);
            (new RunInputCaptureRepository())->capture($run->run_id,'INGEST_BARS','ancillary',['operation'=>'F02-commit-negative'],[['must_not_survive'=>'replay']]);
            $repo->appendEvent($run,'INGEST_BARS','STAGE_COMPLETED','INFO','Caller attempts commit');
            DB::commit();
        }catch(RuntimeException $e){$error=$e->getMessage();}finally{while(DB::transactionLevel()>0)DB::rollBack();}
        $after=$this->projection();$dir=getenv('B19_E049_STATE_DIR');if($dir){if(!is_dir($dir))mkdir($dir,0775,true);file_put_contents($dir.'/F02_caller_commit.json',json_encode(compact('before','after','error'),JSON_PRETTY_PRINT));}
        $this->assertSame($before,$after,'Caller commit persisted replay run/event/fence/capture/staging/publication');
        $this->assertNotNull($error,'A raw caller transaction must not grant replay execution authority');
    }
    public function test_managed_replay_blocks_commit_escapes_and_rolls_back_all_projection_families(): void
    {
        $world=\Tests\Support\R0025SyntheticV2World::build();
        $historical=\App\Models\EodRun::findOrFail($world['run_id']);$date=$historical->trade_date_requested;
        $row=(array)DB::table('eod_bars')->where('trade_date',$date)->first();$before=$this->projection();
        $source=DB::connection();$nativeSource=$source->getPdo();$sourceId=(int)$source->selectOne('SELECT CONNECTION_ID() AS id')->id;
        $replay=null;$reports=[];
        foreach(['normal','facade','pdo','sql','ddl','rollback','autocommit','nested','fault','retained_source','statement_class','constructor'] as $escape){
            $error=null;$visible=null;
            try{$result=\App\Infrastructure\Persistence\MarketData\ReplayIsolation::run(function()use($escape,$historical,$date,$row,&$replay,&$visible,$source,$nativeSource,$sourceId){
                $this->assertNotSame($sourceId,(int)DB::selectOne('SELECT CONNECTION_ID() AS id')->id);
                $this->assertSame(DB::getDefaultConnection(),DB::connection()->getName(),'Replay Eloquent connection must name its isolated transport');
                $repo=new EodRunRepository();$replay=$repo->createAsKnownReplayRun($date,$historical->knowledge_cutoff_at,'api');
                $candidate=(new EodPublicationRepository())->getOrCreateCandidatePublication($replay);
                (new RunInputCaptureRepository())->capture($replay->run_id,'INGEST_BARS','ancillary',['operation'=>'F02-managed'],[['controlled_projection'=>'rollback']]);
                $repo->appendEvent($replay,'INGEST_BARS','STAGE_COMPLETED','INFO','Actual rollback-only replay');
                $stage=$row;$stage['run_id']=$replay->run_id;$stage['publication_id']=$candidate->publication_id;
                (new \App\Infrastructure\Persistence\MarketData\EodArtifactRepository())->replaceBars($date,$candidate->publication_id,$replay->run_id,[$stage],[],false,true);
                $this->assertSame(1,DB::table('eod_bars')->where('run_id',$replay->run_id)->count());
                $this->assertSame(0,$source->table('eod_runs')->where('run_id',$replay->run_id)->count(),'Independent connection observed uncommitted replay');
                $visible=$this->projection();
                if($escape==='facade')DB::commit();
                elseif($escape==='pdo')DB::connection()->getPdo()->commit();
                elseif($escape==='sql')DB::connection()->getPdo()->exec('COMMIT');
                elseif($escape==='ddl')DB::statement('CREATE TABLE eod_f02_commit_escape (id INT)');
                elseif($escape==='rollback'){DB::rollBack();DB::table('eod_runs')->where('run_id',$historical->run_id)->update(['notes'=>'F02_ROOT_ROLLBACK_AUTOCOMMIT_ESCAPE']);}
                elseif($escape==='autocommit')DB::connection()->getPdo()->setAttribute(PDO::ATTR_AUTOCOMMIT,true);
                elseif($escape==='nested'){DB::beginTransaction();DB::commit();DB::commit();}
                elseif($escape==='fault')throw new RuntimeException('F02_INJECTED_FAILURE');
                elseif($escape==='retained_source')$nativeSource->commit();
                elseif($escape==='statement_class')DB::connection()->getPdo()->setAttribute(PDO::ATTR_STATEMENT_CLASS,[PDOStatement::class]);
                elseif($escape==='constructor'){
                    $nativeSource->beginTransaction();
                    try{DB::connection()->getPdo()->__construct($nativeSource);DB::table('eod_runs')->where('run_id',$historical->run_id)->update(['notes'=>'F02_REBOUND_TRANSPORT']);$nativeSource->commit();}
                    finally{if($nativeSource->inTransaction())$nativeSource->rollBack();}
                }
                return ['rows'=>DB::table('eod_bars')->where('run_id',$replay->run_id)->count()];
            });if($escape!=='normal')$this->fail('Replay commit escape accepted: '.$escape);else $this->assertSame(['rows'=>1],$result);
            }catch(RuntimeException $e){$error=$e->getMessage();if($escape==='normal')throw $e;}
            $after=$this->projection();$this->assertSame($before,$after,'Replay scope persisted '.$escape);
            $this->assertSame($source,DB::connection());$this->assertSame(0,DB::transactionLevel());
            if(!in_array($escape,['normal','retained_source','fault'],true))$this->assertStringContainsString('REPLAY_',(string)$error);
            $reports[$escape]=['before'=>$before,'inside'=>$visible,'after'=>$after,'error'=>$error];
            try{(new EodRunRepository())->appendEvent($replay,'INGEST_BARS','STAGE_COMPLETED','INFO','Escaped replay credential');$this->fail('Returned replay credential gained durable authority');}catch(RuntimeException $e){$this->assertStringContainsString('RUN_OWNER_CONFLICT',$e->getMessage());}
        }
        $dir=getenv('B19_E049_STATE_DIR');if($dir){if(!is_dir($dir))mkdir($dir,0775,true);file_put_contents($dir.'/F02_managed_scope_controls.json',json_encode($reports,JSON_PRETTY_PRINT));}
        $publication=(new EodPublicationRepository())->resolveCurrentReadablePublicationForTradeDate($date);$this->assertNotNull($publication);$this->assertSame((int)$world['publication_id'],(int)$publication->publication_id);
    }
    public function test_managed_replay_refuses_outer_business_transaction_without_touching_it(): void
    {
        $repo=new EodRunRepository();$run=$repo->getOrCreateOwningRun('2026-03-21','api','INGEST_BARS',null,'import_only');
        DB::beginTransaction();try{DB::table('eod_runs')->where('run_id',$run->run_id)->update(['notes'=>'caller business transaction']);
            try{\App\Infrastructure\Persistence\MarketData\ReplayIsolation::run(fn()=>null);$this->fail('Replay shared caller transaction');}catch(RuntimeException $e){$this->assertStringContainsString('REPLAY_ISOLATION_ROOT_REQUIRED',$e->getMessage());}
            $this->assertSame(1,DB::transactionLevel());$this->assertSame('caller business transaction',DB::table('eod_runs')->where('run_id',$run->run_id)->value('notes'));
        }finally{DB::rollBack();}
        $this->assertNotSame('caller business transaction',DB::table('eod_runs')->where('run_id',$run->run_id)->value('notes'));
    }

    public function test_real_as_known_canonicalizer_on_mariadb_preserves_prior_publication_and_all_projections(): void
    {
        $w=\Tests\Support\R0025SyntheticV2World::build();$run=\App\Models\EodRun::findOrFail($w['run_id']);$date=$run->trade_date_requested;
        $before=$this->projection();$observations=new \App\Infrastructure\Persistence\MarketData\SourceObservationRepository();
        $bound=['source_observation_manifest_hash'=>$observations->observationManifestAsKnown($date,$run->knowledge_cutoff_at)['manifest_hash'],
            'source_observation_manifest'=>$observations->observationManifestAsKnown($date,$run->knowledge_cutoff_at),
            'canonical_raw_input_hash'=>$observations->normalizedRowsManifestAsKnown($date,$run->knowledge_cutoff_at)['manifest_hash'],
            'config_snapshot_id'=>$run->config_snapshot_id,'config_snapshot_hash'=>$run->config_hash];
        $result=app(\App\Application\MarketData\Services\AsKnownReplayExecutionService::class)->execute($date,$run->knowledge_cutoff_at,$bound);
        $this->assertSame('SUCCESS',$result['execution_state']);$this->assertSame(1,$result['canonical_row_count']);
        $input=json_decode(file_get_contents(\Tests\Support\R0025SyntheticV2World::packagePath().'/inputs/provider_response.json'),true);
        $expected=(float)$input['chart']['result'][0]['indicators']['quote'][0]['close'][0];
        $this->assertSame($expected,(float)$result['canonical_rows'][0]['close']);$this->assertTrue($result['replay_isolation']);$this->assertFalse($result['durable_projection_mutation']);
        $after=$this->projection();$this->assertSame($before,$after,'Actual AS_KNOWN canonicalizer left durable projection changes');
        $publication=(new EodPublicationRepository())->resolveCurrentReadablePublicationForTradeDate($date);$this->assertNotNull($publication);$this->assertSame((int)$w['publication_id'],(int)$publication->publication_id);
        $dir=getenv('B19_E049_STATE_DIR');if($dir){if(!is_dir($dir))mkdir($dir,0775,true);file_put_contents($dir.'/F02_real_canonicalizer.json',json_encode(compact('before','after','result'),JSON_PRETTY_PRINT));}
    }

    public function test_replay_prepared_statement_and_pdo_cannot_escape_scope_lifetime(): void
    {
        [$date,$old,$active]=$this->completedOwnerWithLiveSuccessor();$repo=new EodRunRepository();$repo->finalize($active,['lifecycle_state'=>'COMPLETED','terminal_status'=>'HELD','publishability_state'=>'NOT_READABLE']);
        $before=$this->projection();$statement=null;$handle=null;
        \App\Infrastructure\Persistence\MarketData\ReplayIsolation::run(function()use(&$statement,&$handle,$old,$date,$repo){
            $repo->createAsKnownReplayRun($date,$old->knowledge_cutoff_at,'api');$handle=DB::connection()->getPdo();
            $statement=$handle->prepare('update eod_runs set notes = ? where run_id = ?');
        });
        $error=null;try{$statement->execute(['F02_ESCAPED_STATEMENT',$old->run_id]);}catch(RuntimeException $e){$error=$e->getMessage();}
        $after=$this->projection();$dir=getenv('B19_E049_STATE_DIR');if($dir){if(!is_dir($dir))mkdir($dir,0775,true);file_put_contents($dir.'/F02_escaped_statement.json',json_encode(compact('before','after','error'),JSON_PRETTY_PRINT));}
        $this->assertSame($before,$after,'Escaped replay statement mutated persisted history');$this->assertStringContainsString('REPLAY_ISOLATION_BOUNDARY_LOST',(string)$error);
        try{$handle->beginTransaction();$this->fail('Expired replay PDO revived a transaction');}catch(RuntimeException $e){$this->assertStringContainsString('REPLAY_ISOLATION_BOUNDARY_LOST',$e->getMessage());}
        $this->assertSame($before,$this->projection());
    }

}
