<?php

use App\Application\MarketData\Services\MarketDataPipelineService;
use App\Console\Commands\MarketData\ScheduledWorkflowCommand;
use App\Console\Commands\MarketData\OperationalStatusCommand;
use App\Console\Commands\MarketData\PromoteMarketDataCommand;
use App\Console\Commands\MarketData\ExportEvidenceCommand;
use App\Models\EodRun;
use App\Infrastructure\Persistence\MarketData\EodRunRepository;
use App\Infrastructure\Persistence\MarketData\EodPublicationRepository;
use App\Console\Commands\MarketData\RepairCurrentPublicationIntegrityCommand;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Symfony\Component\Console\Tester\CommandTester;
use Tests\Support\UsesMarketDataMariaDb;
use Tests\Support\R0025SyntheticV2World;

class B19OperationalCommandsMariaDbTest extends TestCase
{
    use UsesMarketDataMariaDb;
    protected function setUp(): void { parent::setUp(); $this->bootMarketDataMariaDb(); }
    protected function tearDown(): void { Carbon::setTestNow(); $this->tearDownMarketDataMariaDb(); parent::tearDown(); }
    private function command(string $class, array $args): array
    {
        $command = new $class(); $command->setLaravel($this->app); $tester = new CommandTester($command);
        $code = $tester->execute($args); return [$code, trim($tester->getDisplay())];
    }
    private function retain(string $name, $data): void
    {
        if ($dir = getenv('B19_OPERATIONAL_ARTIFACT_DIR')) { if (! is_dir($dir)) mkdir($dir,0775,true); file_put_contents($dir.'/'.$name.'.json', json_encode($data,JSON_PRETTY_PRINT)); }
    }
    private function assertSchedulerOutcomeShape(array $outcome): void
    {
        foreach (['scheduler_outcome','run_id','publication_id','requested_date','latest_expected_trade_date','latest_acquired_trade_date',
            'latest_canonicalized_trade_date','latest_readable_trade_date','reason_codes','next_action'] as $key) {
            $this->assertArrayHasKey($key, $outcome, $key);
        }
    }

    public function test_invalid_sole_current_publication_freezes_readability_until_governed_repair(): void
    {
        $world = R0025SyntheticV2World::build();
        $date = R0025SyntheticV2World::world()['trade_date'];
        $publicationId = (int) $world['publication_id'];
        $repository = app(EodPublicationRepository::class);
        $this->assertSame($publicationId, (int) $repository->resolveCurrentReadablePublicationForTradeDate($date)->publication_id);

        // Contradictory current mirror: the pointer still names a sealed publication,
        // but that publication no longer asserts that it is current.
        DB::table('eod_publications')->where('publication_id', $publicationId)->update(['is_current' => 0]);
        $this->assertNull($repository->resolveCurrentReadablePublicationForTradeDate($date));
        $this->assertSame($publicationId, (int) DB::table('eod_current_publication_pointer')->where('trade_date', $date)->value('publication_id'));

        [$dryCode, $dryOutput] = $this->command(RepairCurrentPublicationIntegrityCommand::class, ['--trade_date' => $date]);
        $this->assertSame(1, $dryCode, $dryOutput);
        $this->assertStringContainsString('PUBLICATION_NOT_MARKED_CURRENT', $dryOutput);
        $this->assertStringContainsString('operation_mode=DRY_RUN', $dryOutput);
        $this->assertSame($publicationId, (int) DB::table('eod_current_publication_pointer')->where('trade_date', $date)->value('publication_id'));

        [$refusalCode, $refusalOutput] = $this->command(RepairCurrentPublicationIntegrityCommand::class, ['--trade_date' => $date, '--apply' => true]);
        $this->assertSame(1, $refusalCode, $refusalOutput);
        $this->assertStringContainsString('COMMAND_DESTRUCTIVE_GUARD_REQUIRED', $refusalOutput);
        $this->assertNull($repository->resolveCurrentReadablePublicationForTradeDate($date));
        $this->retain('invalid_sole_current_freeze', ['trade_date' => $date, 'publication_id' => $publicationId,
            'dry_run' => $dryOutput, 'apply_without_reason' => $refusalOutput,
            'pointer_after_refusal' => DB::table('eod_current_publication_pointer')->where('trade_date', $date)->first()]);
    }

    public function test_registered_scheduled_command_executes_real_chain_then_idempotently_skips_verified_current(): void
    {
        $outcome = null;
        $world = R0025SyntheticV2World::build(['run_workflow' => function ($pipeline, $date) use (&$outcome) {
            $this->app->instance(MarketDataPipelineService::class, $pipeline);
            [$code, $output] = $this->command(ScheduledWorkflowCommand::class, ['--source_mode' => 'api']);
            $this->assertSame(0, $code, $output);
            $outcome = json_decode($output, true);
            $this->assertSame(0, $outcome['process_exit_code']);
            $this->assertSchedulerOutcomeShape($outcome);
            $this->assertSame('SUCCESS_READABLE', $outcome['scheduler_outcome']);
            $this->assertSame($date, $outcome['requested_date']);
            return EodRun::findOrFail($outcome['run_id']);
        }]);
        $date = R0025SyntheticV2World::world()['trade_date'];
        $ownerFence = DB::table('eod_run_owner_fences')->where('trade_date_requested', $date)->first();
        $this->assertNotNull($ownerFence, 'the clean scheduled record must have an explicit date owner');
        $this->assertSame((int) $world['run_id'], (int) $ownerFence->owner_run_id);
        $before = [DB::table('md_source_observations')->count(), DB::table('eod_runs')->count(), DB::table('eod_current_publication_pointer')->get()->toJson()];
        [$code, $retry] = $this->command(ScheduledWorkflowCommand::class, ['--source_mode' => 'api']);
        $this->assertSame(0, $code, $retry); $retry = json_decode($retry,true);
        $this->assertSame('SKIPPED_ALREADY_READABLE', $retry['scheduler_outcome']);
        $this->assertSame(0, $retry['process_exit_code']);
        $this->assertSame($outcome['publication_id'], $retry['publication_id']);
        $this->assertSame($before, [DB::table('md_source_observations')->count(), DB::table('eod_runs')->count(), DB::table('eod_current_publication_pointer')->get()->toJson()]);
        $this->assertSame(2, DB::table('eod_run_events')->where('run_id',$world['run_id'])->where('event_type','SCHEDULER_OUTCOME')->count());
        $readState = [DB::table('eod_runs')->where('trade_date_requested',$date)->get()->toJson(), DB::table('eod_run_events')->where('run_id',$world['run_id'])->get()->toJson(),
            DB::table('eod_current_publication_pointer')->where('trade_date',$date)->get()->toJson(), DB::table('md_source_observations')->count()];
        [$code,$status] = $this->command(OperationalStatusCommand::class, ['--requested_date'=>$date,'--run_id'=>$world['run_id']]);
        $this->assertSame(0,$code,$status); $status=json_decode($status,true);
        $this->assertSame($readState, [DB::table('eod_runs')->where('trade_date_requested',$date)->get()->toJson(), DB::table('eod_run_events')->where('run_id',$world['run_id'])->get()->toJson(),
            DB::table('eod_current_publication_pointer')->where('trade_date',$date)->get()->toJson(), DB::table('md_source_observations')->count()], 'Operational status must be read-only.');
        foreach (['requested_date','latest_expected_trade_date'] as $key) $this->assertSame($date,$status[$key],$key);
        foreach (['latest_acquired_trade_date','latest_canonicalized_trade_date','latest_readable_trade_date'] as $key) $this->assertGreaterThanOrEqual($date,$status[$key],$key);
        $this->assertTrue($status['operationally_healthy']);
        $unchanged=DB::table('eod_runs')->get()->toJson();
        [$code,$output]=$this->command(OperationalStatusCommand::class,['--requested_date'=>$date,'--latest'=>true]);
        $this->assertSame(1,$code); $this->assertStringContainsString('COMMAND_CONFLICTING_OPTIONS',$output);
        [$code,$output]=$this->command(PromoteMarketDataCommand::class,['--requested_date'=>$date,'--force_replace'=>'true']);
        $this->assertSame(1,$code); $this->assertStringContainsString('COMMAND_DESTRUCTIVE_GUARD_REQUIRED',$output);
        [$code,$output]=$this->command(ExportEvidenceCommand::class,['--run_id'=>$world['run_id'],'--correction_id'=>1]);
        $this->assertSame(1,$code); $this->assertStringContainsString('COMMAND_CONFLICTING_OPTIONS',$output);
        [$code,$output]=$this->command(ScheduledWorkflowCommand::class,['--source_mode'=>'unregistered']);
        $this->assertSame(1,$code); $this->assertStringContainsString('COMMAND_INVALID_SOURCE_MODE',$output);
        $this->assertSame($unchanged,DB::table('eod_runs')->get()->toJson());
        $this->retain('scheduler_chain',['first'=>$outcome,'retry'=>$retry,'status'=>$status,'owner_fence'=>$ownerFence,'events'=>DB::table('eod_run_events')->where('run_id',$world['run_id'])->orderBy('event_id')->get()->all()]);
    }

    public function test_due_run_source_failure_retains_attempts_and_prior_readable_date_without_claiming_success(): void
    {
        $mode = 'normal';
        $calls = 0;
        $body = file_get_contents(R0025SyntheticV2World::packagePath().'/inputs/provider_response.json');
        config()->set('market_data.provider.api_retry_max', 2);
        config()->set('market_data.provider.api_backoff_ms', 1);
        $world = R0025SyntheticV2World::build(['http_fetcher' => function () use (&$mode, &$calls, $body) {
            $calls++;
            return ['status' => $mode === 'normal' ? 200 : 504, 'body' => $mode === 'normal' ? $body : 'gateway timeout', 'headers' => ['Content-Type' => 'application/json']];
        }]);
        $this->app->instance(MarketDataPipelineService::class, $world['pipeline']);
        $pointer = DB::table('eod_current_publication_pointer')->get()->toJson();
        $observationIds = DB::table('md_source_observations')->orderBy('source_observation_id')->pluck('source_observation_id')->all();
        $observations = DB::table('md_source_observations')->whereIn('source_observation_id', $observationIds)->orderBy('source_observation_id')->get()->toJson();
        $calendar = (array) DB::table('md_market_calendar_revisions')->where('cal_date', R0025SyntheticV2World::world()['trade_date'])->first();
        unset($calendar['calendar_revision_id']);
        foreach (['session_open_at','session_close_at','completed_at','recorded_at'] as $field) $calendar[$field] = str_replace('2026-03-23','2030-01-02',$calendar[$field]);
        $calendar['cal_date']='2030-01-02'; $calendar['revision_uid']=hash('sha256','e029-due2030');
        DB::table('md_market_calendar_revisions')->insert($calendar);
        Carbon::setTestNow('2030-01-03 10:30:00'); $mode='outage'; $calls=0;
        [$code,$output]=$this->command(ScheduledWorkflowCommand::class,['--source_mode'=>'api']);
        $outcome=json_decode($output,true);
        $this->assertSame(3,$code,$output); $this->assertSame('SUCCESS_HELD',$outcome['scheduler_outcome']);
        $this->assertSame(3, $outcome['process_exit_code']);
        $this->assertSchedulerOutcomeShape($outcome);
        $this->assertSame('2030-01-02',$outcome['requested_date']);
        $run=EodRun::findOrFail($outcome['run_id']);
        $ownerFence = DB::table('eod_run_owner_fences')->where('trade_date_requested', '2030-01-02')->first();
        $this->assertNotNull($ownerFence, 'held scheduled work still has explicit ownership');
        $this->assertSame((int) $run->run_id, (int) $ownerFence->owner_run_id);
        $this->assertSame('HELD',$run->terminal_status); $this->assertSame('NOT_READABLE',$run->publishability_state);
        $this->assertSame(3,(int)$run->source_attempt_count);
        $this->assertStringContainsString('source_attempt_count=3',(string)$run->notes);
        $this->assertSame($pointer,DB::table('eod_current_publication_pointer')->get()->toJson());
        $this->assertSame($observations,DB::table('md_source_observations')->whereIn('source_observation_id', $observationIds)->orderBy('source_observation_id')->get()->toJson());
        $this->assertSame(3, $calls, 'Configured retry_max=2 means the initial request plus two retries.');
        $status=(new \App\Application\MarketData\Services\MarketDataOperationalStatusService())->forDate('2030-01-02',(int)$run->run_id);
        $this->retain('scheduler_failed_diagnostic',['outcome'=>$outcome,'run'=>$run,'status'=>$status,'events'=>DB::table('eod_run_events')->where('run_id',$run->run_id)->orderBy('event_id')->get()->all()]);
        $this->assertFalse((bool)$status['operationally_healthy']);
        $this->assertContains('RUN_SOURCE_TIMEOUT',$status['reason_codes']);
        $this->assertContains('RUN_COVERAGE_LOW',$status['reason_codes']);
        $this->assertContains('PUBLISH_BARS',$status['failed_or_held_stages']);
        $this->assertContains('REQUESTED_DATE_NOT_READABLE',$status['observability_defects']);
        $this->assertSame('RUN_SOURCE_TIMEOUT', $status['source_final_reason_code']);
        $this->assertNull($status['publication_id']);
        $this->assertSame('2030-01-02', $outcome['latest_expected_trade_date']);
        $this->assertLessThan($outcome['requested_date'], $outcome['latest_acquired_trade_date']);
        $this->assertLessThan($outcome['requested_date'], $outcome['latest_canonicalized_trade_date']);
        $this->assertLessThan($outcome['requested_date'], $outcome['latest_readable_trade_date']);
        $this->assertSame('FAIL', $status['coverage_gate_state']);
        $this->assertSame(1,(int)$run->coverage_missing_count);
        $this->assertSame(0.0,(float)$run->coverage_ratio);
        $this->retain('scheduler_failed',['outcome'=>$outcome,'run'=>$run,'status'=>$status,'owner_fence'=>$ownerFence]);
    }

    public static function schemaDriftPriorPublicationCases(): array
    {
        return [
            'verified prior publication' => [true],
            'no prior readable publication' => [false],
        ];
    }

    /** @dataProvider schemaDriftPriorPublicationCases */
    public function test_schema_drift_refuses_best_effort_due_publication_and_reports_verified_prior_only_when_present(bool $publishPrior): void
    {
        $body = json_decode((string) file_get_contents(R0025SyntheticV2World::packagePath().'/inputs/provider_response.json'), true);
        $drifted = $body;
        unset($drifted['chart']['result'][0]['indicators']['quote']);
        $mode = 'valid';
        $world = R0025SyntheticV2World::build([
            'http_fetcher' => function () use (&$mode, $body, $drifted) {
                return ['status' => 200, 'body' => json_encode($mode === 'valid' ? $body : $drifted),
                    'headers' => ['Content-Type' => 'application/json']];
            },
            'run_workflow' => function ($pipeline, $date) use ($publishPrior) {
                return $publishPrior ? $pipeline->runDaily($date, 'api') : $pipeline->importDaily($date, 'api');
            },
        ]);
        $this->app->instance(MarketDataPipelineService::class, $world['pipeline']);
        $priorDate = R0025SyntheticV2World::world()['trade_date'];
        $priorPointer = DB::table('eod_current_publication_pointer')->get()->toJson();
        $this->assertSame($publishPrior, DB::table('eod_current_publication_pointer')->where('trade_date', $priorDate)->exists());

        $calendar = (array) DB::table('md_market_calendar_revisions')->where('cal_date', $priorDate)->first();
        unset($calendar['calendar_revision_id']);
        foreach (['session_open_at','session_close_at','completed_at','recorded_at'] as $field) {
            $calendar[$field] = str_replace($priorDate, '2030-01-02', $calendar[$field]);
        }
        $calendar['cal_date'] = '2030-01-02';
        $calendar['revision_uid'] = hash('sha256', 'e043-schema-drift-2030');
        DB::table('md_market_calendar_revisions')->insert($calendar);
        Carbon::setTestNow('2030-01-03 10:30:00');
        $mode = 'drifted';

        [$code, $output] = $this->command(ScheduledWorkflowCommand::class, ['--source_mode' => 'api']);
        $outcome = json_decode($output, true);
        $this->assertNotSame(0, $code, $output);
        $this->assertSame('2030-01-02', $outcome['requested_date']);
        $this->assertContains('RUN_SOURCE_RESPONSE_CHANGED', $outcome['reason_codes'], $output);
        $this->assertNotSame('SUCCESS_READABLE', $outcome['scheduler_outcome']);
        $this->assertSame($priorPointer, DB::table('eod_current_publication_pointer')->get()->toJson());
        $this->assertFalse(DB::table('eod_current_publication_pointer')->where('trade_date', '2030-01-02')->exists());
        $this->assertSame(0, DB::table('eod_bars')->where('trade_date', '2030-01-02')->count());
        $this->assertSame(0, DB::table('md_source_observations')->where('requested_trade_date', '2030-01-02')
            ->whereIn('outcome_state', ['ACCEPTED', 'NORMALIZED'])->count());
        $this->assertNotNull($outcome['run_id']);
        [$statusCode, $statusOutput] = $this->command(OperationalStatusCommand::class,
            ['--requested_date' => '2030-01-02', '--run_id' => $outcome['run_id']]);
        $this->assertSame(2, $statusCode, $statusOutput);
        $status = json_decode($statusOutput, true);
        $this->assertFalse($status['operationally_healthy']);
        $this->assertNull($status['publication_id']);
        $this->assertSame($publishPrior ? $priorDate : null, $status['latest_readable_trade_date']);
        $this->retain('schema_drift_'.($publishPrior ? 'with_prior' : 'without_prior'),
            ['outcome' => $outcome, 'status' => $status, 'prior_pointer' => json_decode($priorPointer, true)]);
    }

    public function test_scheduled_nonretryable_low_coverage_uses_held_exit_without_readable_pointer(): void
    {
        $body = file_get_contents(R0025SyntheticV2World::packagePath().'/inputs/provider_response.json');
        $calls = 0;
        $world = R0025SyntheticV2World::build(['http_fetcher' => function () use ($body, &$calls) {
            $calls++;
            return ['status' => 200, 'body' => $body, 'headers' => ['Content-Type' => 'application/json']];
        }]);
        $this->app->instance(MarketDataPipelineService::class, $world['pipeline']);
        $calendar = (array) DB::table('md_market_calendar_revisions')->where('cal_date', R0025SyntheticV2World::world()['trade_date'])->first();
        unset($calendar['calendar_revision_id']);
        foreach (['session_open_at','session_close_at','completed_at','recorded_at'] as $field) $calendar[$field] = str_replace('2026-03-23','2030-01-02',$calendar[$field]);
        $calendar['cal_date'] = '2030-01-02';
        $calendar['revision_uid'] = hash('sha256', 'e035-held2030');
        DB::table('md_market_calendar_revisions')->insert($calendar);
        Carbon::setTestNow('2030-01-03 10:30:00');

        [$code, $output] = $this->command(ScheduledWorkflowCommand::class, ['--source_mode' => 'api']);
        $outcome = json_decode($output, true);
        $this->assertSame(2, $code, $output);
        $this->assertSame(2, $outcome['process_exit_code']);
        $this->assertSame('SUCCESS_HELD', $outcome['scheduler_outcome']);
        $this->assertContains('RUN_COVERAGE_LOW', $outcome['reason_codes']);
        $this->assertSame(0, DB::table('eod_current_publication_pointer')->where('trade_date', '2030-01-02')->count());
    }

    public function test_scheduled_competitor_reports_locked_without_starting_a_second_owner(): void
    {
        $world = R0025SyntheticV2World::build();
        $this->app->instance(MarketDataPipelineService::class, $world['pipeline']);
        $calendar = (array) DB::table('md_market_calendar_revisions')->where('cal_date', R0025SyntheticV2World::world()['trade_date'])->first();
        unset($calendar['calendar_revision_id']);
        foreach (['session_open_at','session_close_at','completed_at','recorded_at'] as $field) $calendar[$field] = str_replace('2026-03-23','2030-01-02',$calendar[$field]);
        $calendar['cal_date']='2030-01-02'; $calendar['revision_uid']=hash('sha256','e029-lock2030');
        DB::table('md_market_calendar_revisions')->insert($calendar);
        Carbon::setTestNow('2030-01-03 10:30:00');
        $owner = (new EodRunRepository())->getOrCreateOwningRun('2030-01-02','manual_file','INGEST_BARS',null,'promote');
        $fence = DB::table('eod_run_owner_fences')->where('trade_date_requested','2030-01-02')->first();
        $this->assertSame((int)$owner->run_id,(int)$fence->owner_run_id);
        [$code,$output]=$this->command(ScheduledWorkflowCommand::class,['--source_mode'=>'api']);
        $outcome=json_decode($output,true);
        $this->assertSchedulerOutcomeShape($outcome);
        $this->assertSame(5,$code,$output); $this->assertSame('SKIPPED_LOCKED',$outcome['scheduler_outcome']);
        $this->assertSame(5, $outcome['process_exit_code']);
        $this->assertSame('2030-01-02',$outcome['requested_date']);
        $this->assertContains('RUN_LOCK_CONFLICT',$outcome['reason_codes']);
        $this->assertSame((int)$owner->run_id,(int)DB::table('eod_run_owner_fences')->where('trade_date_requested','2030-01-02')->value('owner_run_id'));
        $this->assertSame(0,DB::table('eod_current_publication_pointer')->where('trade_date','2030-01-02')->count());
        $this->retain('scheduler_locked',['outcome'=>$outcome,'owner_run_id'=>$owner->run_id,'fence'=>$fence]);
    }

    public function test_missing_verified_calendar_produces_explicit_failed_scheduler_outcome(): void
    {
        DB::table('md_market_calendar_revisions')->delete();
        [$code,$output]=$this->command(ScheduledWorkflowCommand::class,['--source_mode'=>'api']);
        $outcome=json_decode($output,true);
        $this->assertSchedulerOutcomeShape($outcome);
        $this->assertSame(6,$code,$output); $this->assertSame('FAILED',$outcome['scheduler_outcome']);
        $this->assertSame(6, $outcome['process_exit_code']);
        $this->assertNull($outcome['requested_date']); $this->assertNull($outcome['run_id']);
        $this->assertContains('COMMAND_EXECUTION_FAILED',$outcome['reason_codes']);
        $this->assertStringContainsString('MARKET_CALENDAR_EXPECTED_DATE_UNAVAILABLE',$outcome['error']);
        $this->assertArrayHasKey('latest_acquired_trade_date',$outcome);
        $this->retain('scheduler_calendar_blocked',$outcome);
    }

    public function test_scheduled_exit_mapping_keeps_all_contract_categories_distinct(): void
    {
        $command = new ScheduledWorkflowCommand();
        $method = new \ReflectionMethod($command, 'processExitCode');
        $method->setAccessible(true);
        $cases = [
            'readable' => [['scheduler_outcome' => 'SUCCESS_READABLE', 'reason_codes' => []], 0],
            'held' => [['scheduler_outcome' => 'SUCCESS_HELD', 'reason_codes' => ['RUN_COVERAGE_LOW']], 2],
            'retryable' => [['scheduler_outcome' => 'FAILED', 'reason_codes' => ['RUN_SOURCE_TIMEOUT']], 3],
            'integrity' => [['scheduler_outcome' => 'FAILED', 'reason_codes' => ['DATASET_HASH_MISMATCH']], 4],
            'lock' => [['scheduler_outcome' => 'SKIPPED_LOCKED', 'reason_codes' => ['RUN_LOCK_CONFLICT']], 5],
            'blocked_proof' => [['scheduler_outcome' => 'FAILED', 'reason_codes' => ['COMMAND_EXECUTION_FAILED']], 6],
        ];
        $codes = [];
        foreach ($cases as $category => [$result, $expected]) {
            $actual = $method->invoke($command, $result);
            $this->assertSame($expected, $actual, $category);
            $codes[] = $actual;
        }
        $this->assertCount(count($cases), array_unique($codes));
    }
}
