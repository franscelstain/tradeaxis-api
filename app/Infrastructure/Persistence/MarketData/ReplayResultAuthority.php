<?php

namespace App\Infrastructure\Persistence\MarketData;

use App\Application\MarketData\Services\AsKnownReplaySnapshotService;
use App\Application\MarketData\Services\ReplayMode;
use App\Domain\MarketData\ReplayRequestIdentity;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

/** D011 internal verifier issuer. F03 applies; this does not authenticate an external principal. */
final class ReplayResultAuthority
{
    private static $contexts;
    private const LEASE_SECONDS = 300;

    private static function context(): array
    {
        $connection=DB::connection();
        if (!self::$contexts || !self::$contexts->contains($connection))
            throw new \RuntimeException('REPLAY_RESULT_AUTHORITY_REQUIRED: a replay ID or historical run is not an execution credential.');
        return self::$contexts[$connection];
    }

    public static function replayId(): int { return self::context()['replay_id']; }

    /** This is the trusted verifier-entry acquisition, not an implicit low-level writer exemption. */
    public static function during($subject, string $fixture, string $mode, $publication, ?string $cutoff, $requestedId, callable $work)
    {
        ReplayIsolation::assertResultPersistenceAllowed();
        $subject=ReplayRequestIdentity::positiveInteger($subject);
        if($subject===null)throw new \RuntimeException('REPLAY_RESULT_OWNER_SCOPE_INVALID: run_id must be a positive integer.');
        if($publication!==null && ($publication=ReplayRequestIdentity::positiveInteger($publication))===null)
            throw new \RuntimeException('REPLAY_RESULT_OWNER_SCOPE_INVALID: publication_id must be a positive integer.');
        if($requestedId!==null && ($requestedId=ReplayRequestIdentity::positiveInteger($requestedId))===null)
            throw new \RuntimeException('REPLAY_RESULT_OWNER_SCOPE_INVALID: replay_id must be a positive integer; omit it for a new execution.');
        foreach(['md_replay_result_sequences','md_replay_result_cases','md_replay_result_attempts']as $table)
            if(!DB::connection()->getSchemaBuilder()->hasTable($table))throw new \RuntimeException('REPLAY_RESULT_AUTHORITY_SCHEMA_REQUIRED');
        $scope=self::scope($subject,$fixture,$mode,$publication,$cutoff);
        $hash=self::scopeHash($scope);$connection=DB::connection();
        $lease=DB::transaction(function()use($scope,$hash,$requestedId){
            $id=$requestedId;
            $case=null;
            if($id===null){
                DB::table('md_replay_result_sequences')->insertOrIgnore(['sequence_key'=>'replay','next_id'=>1]);
                $sequence=DB::table('md_replay_result_sequences')->where('sequence_key','replay')->lockForUpdate()->first();
                $id=max((int)$sequence->next_id,(int)DB::table('md_replay_daily_metrics')->max('replay_id')+1,
                    (int)DB::table('md_replay_result_cases')->max('replay_id')+1,
                    (int)DB::table('md_replay_reason_code_counts')->max('replay_id')+1);
                DB::table('md_replay_result_sequences')->where('sequence_key','replay')->update(['next_id'=>$id+1]);
            }else{
                // Supplied IDs select existing cases; they never mint an authority chosen by a caller.
                $case=DB::table('md_replay_result_cases')->where('replay_id',$id)->lockForUpdate()->first();
                if(!$case){
                    if(DB::table('md_replay_daily_metrics')->where('replay_id',$id)->exists()
                        || DB::table('md_replay_reason_code_counts')->where('replay_id',$id)->exists())
                        throw new \RuntimeException('REPLAY_RESULT_OWNER_LEGACY_READ_ONLY: legacy evidence has no attributable writer.');
                    throw new \RuntimeException('REPLAY_RESULT_OWNER_ID_UNKNOWN: allocate a new execution without replay_id.');
                }
            }
            if($case && !hash_equals($case->scope_hash,$hash))
                throw new \RuntimeException('REPLAY_RESULT_OWNER_SCOPE_MISMATCH: changed input requires a new replay_id.');
            if($case && $case->state==='COMPLETED')return ['completed'=>$case];
            if($case && (DB::table('md_replay_daily_metrics')->where('replay_id',$id)->exists()
                || DB::table('md_replay_reason_code_counts')->where('replay_id',$id)->exists()))
                throw new \RuntimeException('REPLAY_RESULT_OWNER_PARTIAL_EVIDENCE_INVALID: retain inconsistent evidence; do not overwrite it.');
            $now=Carbon::now(config('market_data.platform.timezone'));
            if($case && $case->state==='RUNNING' && $case->released_at===null && $now->lt(Carbon::parse($case->expires_at,config('market_data.platform.timezone'))))
                throw new \RuntimeException('REPLAY_RESULT_OWNER_BUSY: incumbent execution credential cannot be borrowed.');
            $actor=bin2hex(random_bytes(16));$secret=bin2hex(random_bytes(32));$token=$case?(int)$case->fencing_token+1:1;
            $expires=$now->copy()->addSeconds(self::LEASE_SECONDS);
            $row=['trade_date'=>$scope['trade_date'],'scope_hash'=>$hash,'scope_json'=>RunInputCaptureRepository::canonicalJson($scope),
                'state'=>'RUNNING','fencing_token'=>$token,'execution_id'=>$actor,'credential_hash'=>hash('sha256',$secret),
                'acquired_at'=>$now,'heartbeat_at'=>$now,'expires_at'=>$expires,'released_at'=>null,'result_hash'=>null,'result_json'=>null,'result_json_hash'=>null];
            if($case)DB::table('md_replay_result_cases')->where('replay_id',$id)->update($row);
            else DB::table('md_replay_result_cases')->insert(['replay_id'=>$id]+$row);
            if($case && $case->state==='RUNNING')DB::table('md_replay_result_attempts')->where('execution_id',$case->execution_id)
                ->where('state','RUNNING')->update(['state'=>'EXPIRED','finished_at'=>$now,'closed_by_execution_id'=>$actor]);
            DB::table('md_replay_result_attempts')->insert(['execution_id'=>$actor,'replay_id'=>$id,'fencing_token'=>$token,
                'state'=>'RUNNING','started_at'=>$now,'expires_at'=>$expires]);
            return ['replay_id'=>(int)$id,'execution_id'=>$actor,'secret'=>$secret,'token'=>$token,'scope'=>$scope,
                'phase'=>'VERIFYING','staged'=>null,'request'=>[$scope['subject_run_id'],$scope['fixture_path'],$scope['mode'],$scope['publication_id'],$scope['knowledge_cutoff']]];
        },5);
        if(isset($lease['completed'])){
            $case=$lease['completed'];
            if(!is_string($case->result_json) || !hash_equals((string)$case->result_json_hash,hash('sha256',$case->result_json)))
                throw new \RuntimeException('REPLAY_RESULT_OWNER_COMPLETED_EVIDENCE_INVALID');
            if(!hash_equals((string)$case->result_hash,self::storedResultHash((int)$case->replay_id)))
                throw new \RuntimeException('REPLAY_RESULT_OWNER_COMPLETED_EVIDENCE_INVALID');
            $response=json_decode($case->result_json,true);
            if(!is_array($response))throw new \RuntimeException('REPLAY_RESULT_OWNER_COMPLETED_EVIDENCE_INVALID');
            $response['result_reused']=true;$response['fresh_comparison_performed']=false;
            return $response;
        }
        if(!self::$contexts)self::$contexts=new \SplObjectStorage();
        $previous=self::$contexts->contains($connection)?self::$contexts[$connection]:null;
        self::$contexts[$connection]=$lease;
        try{
            $response=$work();$context=self::context();
            if(!$context['staged'])throw new \RuntimeException('REPLAY_RESULT_AUTHORITY_RESULT_REQUIRED');
            if(!is_array($response))throw new \RuntimeException('REPLAY_RESULT_AUTHORITY_RESPONSE_INVALID');
            $response['result_reused']=false;
            $response['fresh_comparison_performed']=$context['scope']['mode']===ReplayMode::PUBLICATION_EXACT
                || isset($response['actual_context']['executed_replay']);
            $response['writer_kind']='REPLAY_RESULT_ONLY';
            $response['writer_execution_id']=$context['execution_id'];
            $response['writer_fencing_token']=$context['token'];
            $response['writer_scope_hash']=self::scopeHash($context['scope']);
            $response['writer_provenance_limit']='F03_INTERNAL_VERIFIER_ISSUER';
            if(self::scopeHash(self::scope(...$context['request']))!==self::scopeHash($context['scope']))
                throw new \RuntimeException('REPLAY_RESULT_OWNER_INPUT_CHANGED: request inputs changed during verification.');
            $context['phase']='PERSISTING';self::$contexts[$connection]=$context;
            DB::transaction(function()use($response,$context){
                self::lock();
                DB::table('md_replay_result_cases')->where('replay_id',$context['replay_id'])
                    ->update(['heartbeat_at'=>Carbon::now(config('market_data.platform.timezone'))]);
                [$metric,$counts]=$context['staged'];
                $repo=new ReplayResultRepository();$repo->upsertMetric($metric);$repo->replaceReasonCodeCounts($metric['replay_id'],$metric['trade_date'],$counts);
                $now=Carbon::now(config('market_data.platform.timezone'));$json=RunInputCaptureRepository::canonicalJson($response);
                self::lock();
                DB::table('md_replay_result_attempts')->where('execution_id',$context['execution_id'])->where('state','RUNNING')
                    ->update(['state'=>'COMPLETED','finished_at'=>$now,'closed_by_execution_id'=>$context['execution_id']]);
                self::lock();
                DB::table('md_replay_result_cases')->where('replay_id',$context['replay_id'])->update([
                    'state'=>'COMPLETED','released_at'=>$now,'result_hash'=>self::storedResultHash($context['replay_id']),
                    'result_json'=>$json,'result_json_hash'=>hash('sha256',$json)]);
            });
            return $response;
        }catch(\Throwable $error){
            // Failure telemetry may not write through a stale/released execution.
            try{DB::transaction(function()use($error){
                $context=self::context();self::lock(false);$now=Carbon::now(config('market_data.platform.timezone'));
                DB::table('md_replay_result_cases')->where('replay_id',$context['replay_id'])->update(['heartbeat_at'=>$now]);
                DB::table('md_replay_result_attempts')->where('execution_id',$context['execution_id'])->where('state','RUNNING')
                    ->update(['state'=>'FAILED','finished_at'=>$now,'failure_class'=>substr(get_class($error),0,191),'closed_by_execution_id'=>$context['execution_id']]);
                self::lock(false);
                DB::table('md_replay_result_cases')->where('replay_id',$context['replay_id'])->update(['state'=>'FAILED','released_at'=>$now]);
            });}catch(\Throwable $refusal){ /* No mutation of a successor, completed case or expired authority. */ }
            throw $error;
        }finally{
            if($previous===null)self::$contexts->detach($connection);else self::$contexts[$connection]=$previous;
        }
    }

    public static function stage(array $metric,array $counts): void
    {
        $context=self::context();
        if($context['phase']!=='VERIFYING' || $context['staged']!==null)throw new \RuntimeException('REPLAY_RESULT_AUTHORITY_STAGE_INVALID');
        self::assertMetricScope($metric,$context);
        $seen=[];
        foreach($counts as $count){
            $reason=(string)($count['reason_code']??'');$value=$count['reason_count']??null;
            if($reason==='' || isset($seen[$reason]) || filter_var($value,FILTER_VALIDATE_INT)===false || (int)$value<0)
                throw new \RuntimeException('REPLAY_RESULT_OWNER_COUNTS_INVALID');
            $seen[$reason]=true;
        }
        $context['staged']=[$metric,$counts];self::$contexts[DB::connection()]=$context;
    }

    /** Called inside each actual low-level mutation transaction. Lock ordering: result case, then result rows. */
    public static function assertMetricWrite(array $metric): void
    {
        $context=self::context();self::lock();self::assertMetricScope($metric,$context);
        // Repository already validates this derived classification against comparison_result.
        // An omitted staged classification is not permission to change any supplied payload.
        if(!array_key_exists('replay_status',$context['staged'][0]))unset($metric['replay_status']);
        if(self::resultHash($metric,$context['staged'][1])!==self::resultHash(...$context['staged']))
            throw new \RuntimeException('REPLAY_RESULT_OWNER_PAYLOAD_MISMATCH');
    }
    public static function assertCountsWrite($id,$date,array $counts): void
    {
        $context=self::context();self::lock();
        if((int)$id!==$context['replay_id'] || (string)$date!==$context['scope']['trade_date']
            || self::hash($counts)!==self::hash($context['staged'][1]))throw new \RuntimeException('REPLAY_RESULT_OWNER_COUNTS_SCOPE_MISMATCH');
    }
    private static function assertMetricScope(array $metric,array $context): void
    {
        $scope=$context['scope'];
        if((int)($metric['replay_id']??0)!==$context['replay_id'] || (string)($metric['trade_date']??'')!==$scope['trade_date']
            || ($metric['replay_mode']??null)!==$scope['mode']
            || ($scope['mode']===ReplayMode::PUBLICATION_EXACT && (int)($metric['publication_id']??0)!==(int)$scope['publication_id'])
            || ($scope['mode']===ReplayMode::PUBLICATION_EXACT
                && (int)($metric['publication_run_id']??0)!==(int)($scope['publication']['run_id']??0))
            || ($scope['mode']===ReplayMode::AS_KNOWN && (($metric['publication_id']??null)!==null
                || ($metric['publication_run_id']??null)!==null
                || (string)($metric['knowledge_cutoff_at']??'')!==$scope['knowledge_cutoff'])))
            throw new \RuntimeException('REPLAY_RESULT_OWNER_METRIC_SCOPE_MISMATCH');
    }
    private static function lock(bool $persisting=true): void
    {
        ReplayIsolation::assertResultPersistenceAllowed();$context=self::context();
        if(DB::transactionLevel()<=0 || ($persisting && $context['phase']!=='PERSISTING'))
            throw new \RuntimeException('REPLAY_RESULT_AUTHORITY_WRITE_BOUNDARY_REQUIRED');
        $case=DB::table('md_replay_result_cases')->where('replay_id',$context['replay_id'])->lockForUpdate()->first();
        if(!$case || $case->state!=='RUNNING' || $case->released_at!==null || (int)$case->fencing_token!==$context['token']
            || !hash_equals($case->execution_id,$context['execution_id']) || !hash_equals($case->credential_hash,hash('sha256',$context['secret']))
            || !hash_equals($case->scope_hash,self::scopeHash($context['scope']))
            || Carbon::now(config('market_data.platform.timezone'))->gte(Carbon::parse($case->expires_at,config('market_data.platform.timezone'))))
            throw new \RuntimeException('REPLAY_RESULT_OWNER_STALE_OR_RELEASED');
    }
    private static function resultHash(array $metric,array $counts): string
    {
        unset($metric['created_at'],$metric['updated_at']);
        return self::hash(['metric'=>$metric,'counts'=>$counts]);
    }
    private static function storedResultHash(int $id): string
    {
        $metrics=DB::table('md_replay_daily_metrics')->where('replay_id',$id)->orderBy('trade_date')->get()->map(function($row){
            $row=(array)$row;unset($row['created_at'],$row['updated_at']);return $row;
        })->all();
        $counts=DB::table('md_replay_reason_code_counts')->where('replay_id',$id)->orderBy('trade_date')->orderBy('reason_code')->get()->all();
        return self::hash(['metrics'=>$metrics,'counts'=>$counts]);
    }
    private static function hash($value): string { return hash('sha256',RunInputCaptureRepository::canonicalJson($value)); }
    private static function scopeHash(array $scope): string
    {
        // Physical location is retained as provenance; verified content/target/input identity defines the request.
        unset($scope['fixture_path']);return self::hash($scope);
    }

    /** Derive identities from actual rows and fixture bytes, never caller-attested provenance or owner models. */
    private static function scope(int $subject,string $fixture,string $mode,?int $publication,?string $cutoff): array
    {
        $mode=ReplayMode::normalize($mode);$run=DB::table('eod_runs')->where('run_id',$subject)->first();
        $root=realpath($fixture);
        if(!$run || $root===false || !is_dir($root))throw new \RuntimeException('REPLAY_RESULT_OWNER_REQUEST_INVALID');
        $files=[];$prefix=rtrim(str_replace('\\','/',$root),'/').'/';
        foreach(new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($root,\FilesystemIterator::SKIP_DOTS))as $file){
            if(!$file->isFile())continue;
            $actual=str_replace('\\','/',(string)$file->getRealPath());
            if(strpos($actual,$prefix)!==0 || $file->isLink())throw new \RuntimeException('REPLAY_RESULT_OWNER_FIXTURE_SCOPE_INVALID');
            $files[substr($actual,strlen($prefix))]=hash_file('sha256',$file->getPathname());
        }
        ksort($files,SORT_STRING);
        $manifest=json_decode((string)@file_get_contents($root.'/manifest.json'),true);
        if(!is_array($manifest))throw new \RuntimeException('REPLAY_RESULT_OWNER_FIXTURE_INVALID');
        $date=(string)$run->trade_date_requested;$pub=null;$snapshot=null;
        if($mode===ReplayMode::PUBLICATION_EXACT){
            if($publication===null){
                $expected=json_decode((string)@file_get_contents($root.'/expected/expected_replay_result.json'),true);
                $selector=$manifest['publication_id']??($expected['expected_publication_context']['publication_id']
                    ??($expected['expected_publication_id']??($expected['publication_id']??null)));
                $publication=ReplayRequestIdentity::positiveInteger($selector);
            }
            if(!$publication || !($pub=DB::table('eod_publications')->where('publication_id',$publication)->first())
                || (string)$pub->trade_date!==$date)throw new \RuntimeException('REPLAY_RESULT_OWNER_PUBLICATION_SCOPE_INVALID');
        }else{
            $expected=json_decode((string)@file_get_contents($root.'/expected/as_known_snapshot.json'),true);
            if(!is_array($expected))throw new \RuntimeException('REPLAY_EXPECTED_PROOF_INCOMPLETE: AS_KNOWN fixture must bind its expected snapshot.');
            if(trim((string)$cutoff)==='')throw new \RuntimeException('REPLAY_KNOWLEDGE_CUTOFF_REQUIRED: AS_KNOWN replay requires knowledge_cutoff.');
            if(($expected['knowledge_cutoff']??($manifest['knowledge_cutoff']??null))!==$cutoff)
                throw new \RuntimeException('REPLAY_KNOWLEDGE_CUTOFF_MISMATCH: command and fixture knowledge_cutoff must match exactly.');
            $date=(string)($expected['trade_date']??($manifest['trade_date']??$date));$publication=null;
            $snapshot=app(AsKnownReplaySnapshotService::class)->capture($date,$cutoff);
        }
        $build=(new ProducerRegistrySnapshot())->build();
        $identity=[];foreach(['run_id','trade_date_requested','source','config_snapshot_id','config_hash','observation_manifest_hash',
            'semantic_observation_manifest_hash','source_file_hash','knowledge_cutoff_at']as $field)$identity[$field]=$run->$field??null;
        $target=[];if($pub)foreach(['publication_id','run_id','trade_date','publication_version','publication_manifest_hash','bars_batch_hash',
            'indicators_batch_hash','eligibility_batch_hash','seal_fingerprint','is_current']as $field)$target[$field]=$pub->$field??null;
        $inputComponents=[];
        if($mode===ReplayMode::PUBLICATION_EXACT){
            $sourceRunIds=array_values(array_unique([$subject,$pub?(int)$pub->run_id:$subject]));
            $evidence=new EodEvidenceRepository();
            $correction=!empty($run->correction_id)?$evidence->findCorrectionById($run->correction_id):$evidence->findCorrectionByRunId($subject);
            // Actual consumed operational state, never caller-attested provenance.
            // Result cases/attempts/metrics are outputs, not their own inputs.
            $inputComponents=[
                'run_state'=>self::hash(DB::table('eod_runs')->whereIn('run_id',$sourceRunIds)->orderBy('run_id')->get()->all()),
                'publication_state'=>self::hash($pub),
                'pointer_state'=>self::hash(DB::table('eod_current_publication_pointer')->where('trade_date',$date)->first()),
                'event_state'=>self::hash(DB::table('eod_run_events')->whereIn('run_id',$sourceRunIds)->orderBy('event_id')->get()->all()),
                'correction_context'=>self::hash($correction),
            ];
        }else{
            // AS_KNOWN consumes the resolved cutoff snapshot, not later pointer,
            // event or mutable run telemetry. Subject identity is merely the validated selector.
            $identity=array_intersect_key($identity,array_flip(['run_id','trade_date_requested','source']));
        }
        return ['schema'=>'durable-replay-result-scope/v1','mode'=>$mode,'subject_run_id'=>$subject,'trade_date'=>$date,
            'publication_id'=>$publication,'knowledge_cutoff'=>$mode===ReplayMode::AS_KNOWN?$cutoff:null,
            'fixture_path'=>$root,'fixture_files'=>$files,'fixture_content_hash'=>self::hash($files),'subject'=>$identity,
            'publication'=>$target,'as_known_input_snapshot'=>$snapshot,'actual_input_components'=>$inputComponents,'verifier_build'=>$build['build_id']];
    }
}
