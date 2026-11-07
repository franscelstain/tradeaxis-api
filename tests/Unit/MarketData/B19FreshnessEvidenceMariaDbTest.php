<?php

use App\Application\MarketData\Services\MarketDataFreshnessEvidenceService;
use App\Infrastructure\Persistence\MarketData\EodRunRepository;
use App\Models\EodRun;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Tests\Support\R0025SyntheticV2World;
use Tests\Support\UsesMarketDataMariaDb;

class B19FreshnessEvidenceMariaDbTest extends TestCase
{
    use UsesMarketDataMariaDb;
    use \Tests\Support\RetainsB19ObservedEvidence;

    private $activation;
    private $provider;
    private $cutoff;
    protected function setUp(): void { parent::setUp(); $this->bootMarketDataMariaDb(); $this->activation = config('market_data.scope.operational_start_date'); $this->provider = config('market_data.provider'); $this->cutoff = config('market_data.platform.cutoff_time'); }
    protected function tearDown(): void { config(['market_data.scope.operational_start_date' => $this->activation, 'market_data.provider' => $this->provider, 'market_data.platform.cutoff_time' => $this->cutoff]); Carbon::setTestNow(); $this->tearDownMarketDataMariaDb(); parent::tearDown(); }

    private function build(): array
    {
        return R0025SyntheticV2World::build(['profile' => 'market-data-row-hash/v1', 'run_clock' => '2026-03-23 18:00:00',
            'http_fetcher' => function () {
                Carbon::setTestNow('2026-03-23 18:00:03');
                return ['status' => 200, 'headers' => ['Content-Type: application/json'],
                    'body' => file_get_contents(R0025SyntheticV2World::packagePath().'/inputs/provider_response.json')];
            }]);
    }

    public function test_actual_pipeline_milestones_are_persisted_with_exact_observation_and_calendar_identity(): void
    {
        config(['market_data.scope.operational_start_date' => null]);
        $world = $this->build();
        Carbon::setTestNow('2026-03-23 18:00:03');
        $before = DB::table('eod_bars_history')->orderBy('ticker_id')->get()->toJson();
        $service = new MarketDataFreshnessEvidenceService();
        $result = $service->assessRun($world['run_id']);
        $this->assertSame('NOT_APPLICABLE', $result['evaluation']['assessment']);
        $this->assertNull($result['evaluation']['freshness_target']);
        $this->assertFalse($result['operational_activation_proven']);
        $this->assertSame('NOT_CLAIMED', $result['operational_slo_compliance']);
        $this->assertSame('2026-03-23 18:00:00', $result['milestones']['first_acquisition_attempt']);
        $this->assertSame('2026-03-23 18:00:03', $result['milestones']['immutable_observation_completion']);
        $this->assertSame(7200, $result['elapsed_seconds_from_session_completion']['first_acquisition_attempt']);
        $this->assertSame(7203, $result['elapsed_seconds_from_session_completion']['consumer_gateway_verification']);
        foreach ($result['milestones'] as $name => $time) {
            $this->assertSame($name === 'first_acquisition_attempt' ? '2026-03-23 18:00:00' : '2026-03-23 18:00:03', $time, $name);
        }
        $this->assertSame((int) $world['publication_id'], $result['publication_id']);
        $this->assertSame('2026-03-23', $result['latest_readable_trade_date']);
        $this->assertSame('2026-03-23', $result['latest_acquired_trade_date']);
        $this->assertSame('2026-03-23', $result['latest_canonicalized_trade_date']);
        $this->assertSame([(int) DB::table('eod_bars')->where('trade_date', '2026-03-23')->value('source_observation_id')], $result['source_observation_ids']);
        foreach ($result['source_observation_ids'] as $id) {
            $this->assertSame(hash('sha256', file_get_contents(R0025SyntheticV2World::packagePath().'/inputs/provider_response.json')),
                DB::table('md_source_observations')->where('source_observation_id', $id)->value('payload_hash'));
        }
        $this->assertSame((int) DB::table('md_market_calendar_revisions')->where('cal_date', '2026-03-23')->value('calendar_revision_id'), $result['calendar_revision_id']);
        $event = DB::table('eod_run_events')->where('run_id', $world['run_id'])->where('event_type', 'FRESHNESS_MEASURED')->first();
        $saved = json_decode($event->event_payload_json, true);
        $hash = $saved['content_sha256']; unset($saved['content_sha256']);
        $this->assertSame(hash('sha256', $this->canonical($saved)), $hash);
        $this->assertSame($result, $saved);
        Carbon::setTestNow('2026-03-25 18:00:00');
        $later = $service->assessRun($world['run_id']);
        $this->assertSame($result['milestones']['active_publication_readability'], $later['milestones']['active_publication_readability']);
        $this->assertSame('2026-03-25 18:00:00', $later['milestones']['consumer_gateway_verification']);
        $this->assertSame($before, DB::table('eod_bars_history')->orderBy('ticker_id')->get()->toJson());
        $this->assertSame(0, DB::table('eod_run_events')->where('event_type', 'FRESHNESS_ALERT_OPENED')->count(), 'Preactivation lateness is neither production failure nor availability success.');
        $this->retainObservedWorld('freshness_milestones', [$world['run_id']], ['first' => $result, 'later' => $later]);
    }

    public function test_alerts_deduplicate_and_a_successful_job_cannot_recover_without_requested_date_gateway_readability(): void
    {
        $world = $this->build();
        // Conditional evaluator exercise after immutable preactivation run creation;
        // this does not approve activation or change the retained config snapshot.
        config(['market_data.scope.operational_start_date' => '2026-03-23']);
        // Independent two further completed expected sessions, absent from the
        // frozen one-date input fixture. Do not infer them from weekdays.
        foreach (['2026-03-24', '2026-03-25'] as $date) {
            DB::table('md_market_calendar_revisions')->insert([
                'revision_uid' => hash('sha256', 'e049-freshness-'.$date), 'market_code' => 'IDX', 'market_segment' => 'REGULAR',
                'cal_date' => $date, 'timezone' => 'Asia/Jakarta', 'is_trading_day' => 1, 'is_half_day' => 0,
                'session_state' => 'COMPLETED', 'session_open_at' => $date.' 09:00:00', 'session_close_at' => $date.' 16:00:00',
                'completed_at' => $date.' 16:00:00', 'recorded_at' => $date.' 17:00:00',
                'source_ref' => 'independent-e049-calendar-'.$date, 'source_version' => 'e049-test-only-v1',
                'provenance_tier' => 'VERIFIED', 'reconciled_at' => $date.' 17:00:00',
                'reconciliation_source_ref' => 'independent-e049-calendar-'.$date,
            ]);
        }
        $pointer = (array) DB::table('eod_current_publication_pointer')->where('trade_date', '2026-03-23')->first();
        DB::table('eod_current_publication_pointer')->where('trade_date', '2026-03-23')->delete();
        $service = new MarketDataFreshnessEvidenceService();
        Carbon::setTestNow('2026-03-23 18:00:03');
        $warning = $service->assessRun($world['run_id']);
        $this->assertSame('PENDING', $warning['evaluation']['freshness_target']);
        $this->assertSame('WARNING', json_decode(DB::table('eod_run_events')->where('event_type', 'FRESHNESS_ALERT_OPENED')->value('event_payload_json'), true)['alert_severity']);
        Carbon::setTestNow('2026-03-25 18:00:00');
        $first = $service->assessRun($world['run_id']);
        $this->assertFalse($first['gateway_readable']);
        $this->assertSame('BREACH', $first['evaluation']['freshness_target'], json_encode($first['evaluation']));
        $this->assertSame(2, $first['evaluation']['expected_session_lag']);
        $this->assertNull($first['latest_readable_trade_date']);
        $service->assessRun($world['run_id']);
        $this->assertSame(1, DB::table('eod_run_events')->where('event_type', 'FRESHNESS_ALERT_OPENED')->count());
        $this->assertSame(1, DB::table('eod_run_events')->where('event_type', 'FRESHNESS_ALERT_ESCALATED')->count());
        $alert = json_decode(DB::table('eod_run_events')->where('event_type', 'FRESHNESS_ALERT_ESCALATED')->value('event_payload_json'), true);
        $this->assertSame('CRITICAL', $alert['alert_severity']);
        $this->assertSame('2026-03-23', $alert['requested_date']);
        $this->assertSame($first['source_observation_ids'], $alert['source_observation_ids']);
        $this->assertSame('SUCCESS', DB::table('eod_runs')->where('run_id', $world['run_id'])->value('terminal_status'));
        $this->assertSame(0, DB::table('eod_run_events')->where('event_type', 'FRESHNESS_ALERT_RECOVERED')->count());
        DB::table('eod_current_publication_pointer')->insert($pointer);
        $recovered = $service->assessRun($world['run_id']);
        $this->assertTrue($recovered['gateway_readable']);
        $this->assertSame('ACCEPTABLE', $recovered['evaluation']['freshness_target'], 'Readability after the same-session cutoff but before the next-session cutoff is ACCEPTABLE, independently of assessment age.');
        $measurements = DB::table('eod_run_events')->where('event_type', 'FRESHNESS_MEASURED')->orderBy('event_id')->get();
        $this->assertSame('BREACH', json_decode($measurements[1]->event_payload_json, true)['evaluation']['freshness_target'], 'The observed unreadability breach remains immutable after recovery.');
        $this->assertSame(1, DB::table('eod_run_events')->where('event_type', 'FRESHNESS_ALERT_RECOVERED')->count());
        $this->retainObservedWorld('freshness_alert_lifecycle', [$world['run_id']], ['warning' => $warning, 'unreadable' => $first, 'recovered' => $recovered]);
        $service->assessRun($world['run_id']);
        $this->assertSame(1, DB::table('eod_run_events')->where('event_type', 'FRESHNESS_ALERT_RECOVERED')->count());
    }

    public function test_registered_commands_execute_and_refuse_invalid_identity_and_changed_config_without_data_mutation(): void
    {
        $world = $this->build();
        Carbon::setTestNow('2026-03-23 18:00:03');
        $before = DB::table('eod_bars_history')->orderBy('ticker_id')->get()->toJson();
        foreach ([\App\Console\Commands\MarketData\MeasureFreshnessCommand::class,
            \App\Console\Commands\MarketData\ObserveIncidentCommand::class] as $class) {
            $command = new $class(); $command->setLaravel($this->app);
            $tester = new \Symfony\Component\Console\Tester\CommandTester($command);
            $this->assertSame(1, $tester->execute(['--run_id' => '-1']));
            $this->assertStringContainsString('status=BLOCKED', $tester->getDisplay());
            $this->assertSame(0, $tester->execute(['--run_id' => (string) $world['run_id']]));
            $output = json_decode(trim($tester->getDisplay()), true);
            $this->assertSame((int) $world['run_id'], $output['run_id']);
            $this->assertFalse($output['operational_activation_proven']);
        }
        $original = DB::table('eod_runs')->where('run_id', $world['run_id'])->value('config_hash');
        DB::table('eod_runs')->where('run_id', $world['run_id'])->update(['config_hash' => str_repeat('0', 64)]);
        try {
            $command = new \App\Console\Commands\MarketData\MeasureFreshnessCommand(); $command->setLaravel($this->app);
            $tester = new \Symfony\Component\Console\Tester\CommandTester($command);
            $this->assertSame(1, $tester->execute(['--run_id' => (string) $world['run_id']]));
            $this->assertStringContainsString('FRESHNESS_CONFIG_IDENTITY_UNVERIFIED', $tester->getDisplay());
        } finally { DB::table('eod_runs')->where('run_id', $world['run_id'])->update(['config_hash' => $original]); }
        $this->assertSame($before, DB::table('eod_bars_history')->orderBy('ticker_id')->get()->toJson());
    }

    public function test_missing_calendar_refuses_and_no_source_event_is_reconstructed_from_job_success(): void
    {
        $world = $this->build();
        $run = (array) DB::table('eod_runs')->where('run_id', $world['run_id'])->first();
        unset($run['run_id']);
        $id = DB::table('eod_runs')->insertGetId($run);
        $report = (new MarketDataFreshnessEvidenceService())->assessRun($id);
        $this->assertNull($report['milestones']['first_acquisition_attempt']);
        $this->assertNull($report['milestones']['immutable_observation_completion']);
        $this->assertSame([], $report['source_observation_ids']);
        $this->assertNull($report['milestones']['valid_canonical_completion']);
        $clone = EodRun::findOrFail($id);
        (new EodRunRepository())->appendEvent($clone, 'INGEST_BARS', 'OBSERVATIONS_CAPTURED', 'INFO',
            'Adversarial completion signal with foreign observation and no corresponding producer capture.', null,
            ['source_observation_ids' => [(int) DB::table('eod_bars')->where('trade_date', '2026-03-23')->value('source_observation_id')]]);
        $blocked = false;
        try { (new MarketDataFreshnessEvidenceService())->assessRun($id); }
        catch (RuntimeException $error) { $this->assertStringContainsString('FRESHNESS_OBSERVATION_PROVENANCE_CONFLICT', $error->getMessage()); $blocked = true; }
        $this->assertTrue($blocked, 'A completion event without the producer-bound observation population cannot establish a milestone.');
        DB::table('md_market_calendar_revisions')->where('cal_date', '2026-03-23')->delete();
        $before = DB::table('eod_run_events')->where('event_type', 'FRESHNESS_MEASURED')->count();
        try { (new MarketDataFreshnessEvidenceService())->assessRun($world['run_id']); $this->fail('Missing calendar must refuse.'); }
        catch (RuntimeException $error) { $this->assertStringContainsString('MARKET_CALENDAR_EVIDENCE_MISSING', $error->getMessage()); }
        $this->assertSame($before, DB::table('eod_run_events')->where('event_type', 'FRESHNESS_MEASURED')->count());
    }

    public function test_acquisition_failure_count_does_not_relabel_a_downstream_job_failure_as_provider_failure(): void
    {
        $world = $this->build();
        Carbon::setTestNow('2026-03-23 18:00:03');
        config(['market_data.scope.operational_start_date' => '2026-03-23']);
        // A persisted SUCCESS acquisition is independent of a later indicator/job failure.
        DB::table('eod_runs')->where('run_id', $world['run_id'])->update([
            'terminal_status' => 'FAILED', 'source_final_reason_code' => 'RUN_INDICATORS_FAILED',
        ]);
        $result = (new MarketDataFreshnessEvidenceService())->assessRun($world['run_id']);
        $this->assertSame(0, $result['evaluation']['failed_expected_session_streak'], 'Only actual acquisition outcomes count, never a downstream failed job or positive row-count heuristic.');
        $this->assertSame('NOT_TRIGGERED', $result['evaluation']['source_evaluation']);
        $this->assertSame('SUCCESS', $result['acquisition_outcome_evidence']['2026-03-23']['raw_source_final_status']);
        $this->assertSame((int) $world['run_id'], $result['acquisition_outcome_evidence']['2026-03-23']['run_id']);
        $this->assertSame((int) DB::table('eod_run_events')->where('run_id', $world['run_id'])->where('stage', 'INGEST_BARS')->where('event_type', 'STAGE_COMPLETED')->value('event_id'),
            $result['acquisition_outcome_evidence']['2026-03-23']['event_id']);
        (new EodRunRepository())->appendEvent(EodRun::findOrFail($world['run_id']), 'INGEST_BARS', 'STAGE_COMPLETED', 'WARN',
            'Controlled contradictory partial acquisition: positive canonical rows cannot establish complete acquisition.', null,
            ['source_acquisition' => ['source_final_status' => 'PARTIAL']]);
        $partial = (new MarketDataFreshnessEvidenceService())->assessRun($world['run_id']);
        $this->assertNull($partial['acquisition_outcome_evidence']['2026-03-23']['outcome']);
        $this->assertSame('EVIDENCE_INCOMPLETE', $partial['evaluation']['source_evaluation']);
        $this->retainObservedWorld('acquisition_outcomes_not_job_outcomes', [$world['run_id']], ['success_acquisition_failed_job' => $result, 'partial' => $partial]);
    }

    public function test_five_real_failed_session_acquisitions_open_only_conditional_source_evaluation_with_exact_event_provenance(): void
    {
        // Versioned, captured test capacity inputs: two transport attempts/session.
        // Ten HTTP failures must count as five failed sessions, never ten sessions.
        config(['market_data.provider.api_retry_max' => 1, 'market_data.provider.api_backoff_ms' => 0,
            'market_data.provider.api_throttle_qps' => 0]);
        $manualContext = new \App\Application\MarketData\Services\ManualSourceInputContext();
        app()->instance(\App\Application\MarketData\Services\ManualSourceInputContext::class, $manualContext);
        $dates = ['2026-03-23', '2026-03-24', '2026-03-25', '2026-03-26', '2026-03-27'];
        $runIds = []; $httpCalls = 0; $providerStatus = 503;
        $world = R0025SyntheticV2World::build(['profile' => 'market-data-row-hash/v1',
            'http_fetcher' => function () use (&$httpCalls, &$providerStatus) {
                $httpCalls++;
                $body = json_decode(file_get_contents(R0025SyntheticV2World::packagePath().'/inputs/provider_response.json'), true);
                $body['chart']['result'][0]['timestamp'] = [(new DateTimeImmutable('2026-03-27 09:00:00', new DateTimeZone('Asia/Jakarta')))->getTimestamp()];
                return ['status' => $providerStatus, 'headers' => ['Content-Type: application/json'],
                    'body' => $providerStatus === 503 ? 'unavailable' : json_encode($body)];
            },
            'run_workflow' => function ($pipeline) use ($dates, &$runIds) {
                foreach (array_slice($dates, 1) as $date) {
                    DB::table('md_market_calendar_revisions')->insert([
                        'revision_uid' => hash('sha256', 'e049-outage-calendar-'.$date), 'market_code' => 'IDX', 'market_segment' => 'REGULAR',
                        'cal_date' => $date, 'timezone' => 'Asia/Jakarta', 'is_trading_day' => 1, 'is_half_day' => 0,
                        'session_state' => 'COMPLETED', 'session_open_at' => $date.' 09:00:00', 'session_close_at' => $date.' 16:00:00',
                        'completed_at' => $date.' 16:00:00', 'recorded_at' => $date.' 17:00:00',
                        'source_ref' => 'independent-e049-outage-calendar-'.$date, 'source_version' => 'e049-test-only-v1',
                        'provenance_tier' => 'VERIFIED', 'reconciled_at' => $date.' 17:00:00',
                        'reconciliation_source_ref' => 'independent-e049-outage-calendar-'.$date,
                    ]);
                }
                foreach ($dates as $date) {
                    Carbon::setTestNow($date.' 18:00:00');
                    try { $unavailable = $pipeline->runDaily($date, 'api'); $this->assertNotSame('READABLE', $unavailable->publishability_state); }
                    catch (\App\Infrastructure\MarketData\Source\SourceAcquisitionException $expected) {}
                    $runIds[$date] = (int) DB::table('eod_runs')->where('trade_date_requested', $date)->orderByDesc('run_id')->value('run_id');
                    $this->assertContains(DB::table('eod_runs')->where('run_id', $runIds[$date])->value('terminal_status'), ['FAILED', 'HELD']);
                    $this->assertFalse(DB::table('eod_current_publication_pointer')->where('trade_date', $date)->exists());
                }
                return EodRun::findOrFail($runIds['2026-03-27']);
            }]);
        $this->assertSame(10, $httpCalls);
        $this->retainObservedWorld('five_failed_session_inputs', array_values($runIds));
        $service = new MarketDataFreshnessEvidenceService();
        $development = $service->assessRun($world['run_id']);
        $this->assertSame('NOT_APPLICABLE', $development['evaluation']['assessment']);
        $this->assertNull($development['evaluation']['source_evaluation']);
        $noProductionIncident = (new \App\Application\MarketData\Services\MarketDataIncidentResponseService())->observeRun($world['run_id']);
        $this->assertSame([], $noProductionIncident['classes'], 'Development source failure is not a production availability incident.');
        $this->assertFalse((new \App\Application\MarketData\Services\MarketDataOperationalStatusService())->forDate('2026-03-27', $world['run_id'])['operationally_healthy'],
            'A clean observed-incident classification cannot turn a real failed/held run into health or availability success.');
        // Conditional mechanism test only. No marker is approved, backdated or
        // rebound to a run; every immutable run snapshot still carries NULL.
        config(['market_data.scope.operational_start_date' => '2026-03-23']);
        Carbon::setTestNow('2026-03-26 18:00:00');
        $four = $service->assessRun($runIds['2026-03-26']);
        $this->assertSame(4, $four['evaluation']['failed_expected_session_streak']);
        $this->assertSame('NOT_TRIGGERED', $four['evaluation']['source_evaluation']);
        Carbon::setTestNow('2026-03-27 18:00:00');
        $five = $service->assessRun($world['run_id']);
        $this->assertSame(5, $five['evaluation']['failed_expected_session_streak']);
        $this->assertSame('OPEN_FUTURE_SOURCE_EVALUATION', $five['evaluation']['source_evaluation']);
        $this->assertSame(['2026-03-23', '2026-03-24', '2026-03-25', '2026-03-26', '2026-03-27'], $five['evaluation']['failed_expected_session_dates']);
        $open = DB::table('eod_run_events')->where('event_type', 'SOURCE_CAPABILITY_EVALUATION_OPENED')->first();
        $this->assertNotNull($open, 'The full-horizon trigger must create a persisted review-opening event.');
        $opening = json_decode($open->event_payload_json, true);
        $this->assertSame('OPEN_FOR_FUTURE_SOURCE_REVIEW', $opening['evaluation_state']);
        $this->assertSame('yahoo_finance', $opening['provider'] ?? null, 'The source-capability opening must retain its actual provider identity.');
        $this->assertSame(['source_mode' => 'api', 'provider' => 'yahoo_finance'], $five['acquisition_stream_identity']);
        $this->assertSame('OBSERVED_FAILURE_ACROSS_FULL_FIVE_SESSION_HORIZON', $opening['source_capability_evidence']);
        $this->assertFalse($opening['source_change_authorized']);
        $this->assertCount(5, $opening['acquisition_outcome_evidence']);
        $this->assertCount(5, $opening['calendar_revision_ids']);
        $service->assessRun($world['run_id']);
        $this->assertSame(1, DB::table('eod_run_events')->where('event_type', 'SOURCE_CAPABILITY_EVALUATION_OPENED')->count());
        $this->assertFalse($five['evaluation']['source_change_authorized']);
        $this->assertFalse($five['operational_activation_proven']);
        $this->assertSame('NOT_CLAIMED', $five['operational_slo_compliance']);
        foreach ($dates as $date) {
            $evidence = $five['acquisition_outcome_evidence'][$date];
            $this->assertSame('FAILED', $evidence['outcome']);
            $this->assertSame($runIds[$date], $evidence['run_id']);
            $this->assertSame((int) DB::table('eod_run_events')->where('run_id', $runIds[$date])->where('stage', 'INGEST_BARS')->where('event_type', 'STAGE_FAILED')->value('event_id'), $evidence['event_id']);
            $snapshot = json_decode(DB::table('md_config_snapshots')->where('config_snapshot_id', EodRun::findOrFail($runIds[$date])->config_snapshot_id)->value('resolved_config_json'), true);
            $this->assertNull($snapshot['resolved_config']['scope']['operational_start_date']);
        }
        // A successful/differently reported manual acquisition is a separate
        // source identity. It must not erase five actual API failures. Isolate
        // this competing-source branch in a real MariaDB savepoint, then resume
        // the existing API retry world with its original rows and fence intact.
        $retainedDirectory = getenv('B19_E049_STATE_DIR');
        $manualFile = $retainedDirectory ? $retainedDirectory.'/independent_manual_control.csv' : sys_get_temp_dir().'/e049-manual-'.bin2hex(random_bytes(6)).'.csv';
        if (! is_dir(dirname($manualFile))) mkdir(dirname($manualFile), 0775, true);
        file_put_contents($manualFile, "ticker_code,trade_date,open,high,low,close,volume,canonical_source,source_row_ref\nSYNV2,2026-03-27,100,110,95,105,1000,YAHOO_FINANCE,e049-independent-manual:2026-03-27\n");
        DB::beginTransaction();
        try {
            config(['market_data.scope.operational_start_date' => null]); $manualContext->set($manualFile);
            $manual = $world['pipeline']->importDaily('2026-03-27', 'manual_file');
            $this->assertSame('manual_file', $manual->source);
            $this->assertSame(1, (int) $manual->bars_rows_written);
            config(['market_data.scope.operational_start_date' => '2026-03-23']);
            $apiAfterManual = $service->assessRun($world['run_id']);
            $this->assertSame(5, $apiAfterManual['evaluation']['failed_expected_session_streak'], 'Other-source completion cannot erase actual API acquisition failures.');
            $this->assertSame('OPEN_FUTURE_SOURCE_EVALUATION', $apiAfterManual['evaluation']['source_evaluation']);
            $this->retainObservedWorld('api_failures_with_independent_manual_completion', array_merge(array_values($runIds), [(int) $manual->run_id]), $apiAfterManual);
        } finally {
            DB::rollBack(); $manualContext->set(null);
            if (! $retainedDirectory && is_file($manualFile)) unlink($manualFile);
            config(['market_data.scope.operational_start_date' => '2026-03-23']);
        }
        $failedObservations = DB::table('md_source_observations')->whereIn('run_id', array_values($runIds))->orderBy('source_observation_id')->get()->toJson();
        $providerStatus = 200;
        // Actual retry stays in the currently permitted preactivation config.
        config(['market_data.scope.operational_start_date' => null]);
        $retry = $world['pipeline']->runDaily('2026-03-27', 'api');
        $this->assertSame('READABLE', $retry->publishability_state);
        $this->assertNotSame($world['run_id'], (int) $retry->run_id);
        $this->assertSame(11, $httpCalls);
        $this->assertSame($failedObservations, DB::table('md_source_observations')->whereIn('run_id', array_values($runIds))->orderBy('source_observation_id')->get()->toJson());
        config(['market_data.scope.operational_start_date' => '2026-03-23']);
        $afterRetry = $service->assessRun($retry->run_id);
        $this->assertSame(0, $afterRetry['evaluation']['failed_expected_session_streak']);
        $this->assertSame('NOT_TRIGGERED', $afterRetry['evaluation']['source_evaluation']);
        $this->assertSame((int) $retry->run_id, $afterRetry['acquisition_outcome_evidence']['2026-03-27']['run_id']);
        $this->assertFalse($afterRetry['evaluation']['source_change_authorized']);
        $this->assertSame($opening, json_decode(DB::table('eod_run_events')->where('event_id', $open->event_id)->value('event_payload_json'), true), 'Successful retry does not rewrite or close the retained source-evaluation opening.');
        $this->assertSame('OPEN_FUTURE_SOURCE_EVALUATION', $five['evaluation']['source_evaluation'], 'Retained failure measurements are never relabeled after recovery.');
        $this->retainObservedWorld('five_actual_failed_sessions_conditional_evaluation', array_merge(array_values($runIds), [(int) $retry->run_id]),
            ['development' => $development, 'four' => $four, 'five' => $five, 'actual_retry' => $afterRetry]);
    }

    public function test_import_promotion_lineage_uses_actual_acquisition_owner_and_refuses_missing_or_conflicting_seed_context(): void
    {
        $seedId = null;
        $world = R0025SyntheticV2World::build(['profile' => 'market-data-row-hash/v1', 'run_clock' => '2026-03-23 18:00:00',
            'http_fetcher' => function () {
                Carbon::setTestNow('2026-03-23 18:00:03');
                return ['status' => 200, 'headers' => ['Content-Type: application/json'],
                    'body' => file_get_contents(R0025SyntheticV2World::packagePath().'/inputs/provider_response.json')];
            },
            'run_workflow' => function ($pipeline) use (&$seedId) {
                $seed = $pipeline->importDaily('2026-03-23', 'api'); $seedId = (int) $seed->run_id;
                return $pipeline->promoteDaily('2026-03-23', 'api', $seedId);
            }]);
        $this->assertNotSame($seedId, $world['run_id']);
        $service = new MarketDataFreshnessEvidenceService();
        $report = $service->assessRun($world['run_id']);
        $this->assertSame('2026-03-23 18:00:00', $report['milestones']['first_acquisition_attempt'], 'Promotion must resolve its immutable acquisition seed, never fabricate a new acquisition timestamp.');
        $this->assertSame($seedId, $report['acquisition_run_id']);
        $this->assertSame([$world['run_id'], $seedId], $report['seed_lineage_run_ids']);
        foreach (['first_acquisition_attempt', 'immutable_observation_completion', 'valid_canonical_completion'] as $key) {
            $this->assertSame($seedId, (int) DB::table('eod_run_events')->where('event_id', $report['milestone_event_ids'][$key])->value('run_id'));
        }
        $this->assertSame($world['run_id'], $report['publication_run_id']);
        $this->assertSame((int) $world['publication_id'], $report['publication_id']);
        $this->assertSame('2026-03-23 18:00:03', $report['milestones']['active_publication_readability']);
        $source = (array) DB::table('eod_runs')->where('run_id', $seedId)->first();
        foreach (['trade_date_requested' => '2026-03-24', 'source' => 'manual_file', 'config_hash' => str_repeat('0', 64),
            'observation_manifest_hash' => str_repeat('0', 64)] as $field => $bad) {
            DB::table('eod_runs')->where('run_id', $seedId)->update([$field => $bad]);
            try {
                $blocked = false;
                try { $service->assessRun($world['run_id']); }
                catch (RuntimeException $error) { $this->assertStringContainsString('FRESHNESS_SEED_', $error->getMessage()); $blocked = true; }
                $this->assertTrue($blocked, 'Conflicting seed '.$field.' must refuse.');
            } finally { DB::table('eod_runs')->where('run_id', $seedId)->update([$field => $source[$field]]); }
        }
        $copy = (array) DB::table('eod_runs')->where('run_id', $world['run_id'])->first(); unset($copy['run_id']);
        $orphan = DB::table('eod_runs')->insertGetId($copy);
        (new EodRunRepository())->appendEvent(EodRun::findOrFail($orphan), 'FINALIZE', 'RUN_CREATED', 'INFO',
            'Adversarial nonexistent seed reference.', null, ['seed_run_id' => 999999999999]);
        $blocked = false;
        try { $service->assessRun($orphan); }
        catch (RuntimeException $error) { $this->assertStringContainsString('FRESHNESS_SEED_RUN_MISSING', $error->getMessage()); $blocked = true; }
        $this->assertTrue($blocked);
        $this->assertSame($source, (array) DB::table('eod_runs')->where('run_id', $seedId)->first());
        $this->retainObservedWorld('import_promote_seed_identity', [$seedId, $world['run_id'], (int) $orphan], $report);
    }

    public function test_v2_seed_lineage_keeps_semantic_and_compatibility_provenance_separate_and_refuses_ambiguous_cycles(): void
    {
        $seedId = null;
        $world = R0025SyntheticV2World::build(['run_workflow' => function ($pipeline) use (&$seedId) {
            $seed = $pipeline->importDaily('2026-03-23', 'api'); $seedId = (int) $seed->run_id;
            return $pipeline->promoteDaily('2026-03-23', 'api', $seedId);
        }]);
        $service = new MarketDataFreshnessEvidenceService();
        $report = $service->assessRun($world['run_id']);
        $seed = EodRun::findOrFail($seedId);
        $promotion = EodRun::findOrFail($world['run_id']);
        $this->assertSame('READABLE', $promotion->publishability_state);
        $this->assertSame($seedId, $report['acquisition_run_id']);
        $this->assertSame($seed->observation_manifest_hash, $promotion->observation_manifest_hash);
        $this->assertSame($seed->semantic_observation_manifest_hash, $promotion->semantic_observation_manifest_hash);
        $this->assertNotSame($seed->observation_manifest_hash, $seed->semantic_observation_manifest_hash,
            'V2 semantic provenance is not an alias for compatibility provenance.');
        $before = (array) DB::table('eod_runs')->where('run_id', $seedId)->first();
        foreach (['config_snapshot_id' => 999999999, 'config_snapshot_ref' => 'unregistered-snapshot',
            'artifact_hash_profile' => 'market-data-row-hash/v1', 'semantic_observation_manifest_hash' => str_repeat('e', 64)] as $field => $bad) {
            DB::table('eod_runs')->where('run_id', $seedId)->update([$field => $bad]);
            try {
                $refused = false;
                try { $service->assessRun($world['run_id']); }
                catch (RuntimeException $error) { $this->assertStringContainsString('FRESHNESS_SEED_', $error->getMessage()); $refused = true; }
                $this->assertTrue($refused, 'Seed identity mismatch must refuse: '.$field);
            } finally { DB::table('eod_runs')->where('run_id', $seedId)->update([$field => $before[$field]]); }
        }
        $copy = (array) DB::table('eod_runs')->where('run_id', $world['run_id'])->first(); unset($copy['run_id']);
        $cases = ['missing' => 'FRESHNESS_SEED_LINK_MISSING', 'duplicate' => 'FRESHNESS_SEED_CREATION_AMBIGUOUS',
            'cycle' => 'FRESHNESS_SEED_CYCLE', 'event_scope' => 'FRESHNESS_SEED_EVENT_SCOPE_MISMATCH'];
        $caseRuns = [];
        foreach ($cases as $case => $expected) {
            $id = (int) DB::table('eod_runs')->insertGetId($copy); $caseRuns[] = $id;
            $child = EodRun::findOrFail($id);
            if ($case === 'event_scope') $child->trade_date_requested = '2026-03-24';
            if ($case !== 'missing') (new EodRunRepository())->appendEvent($child, 'FINALIZE', 'RUN_CREATED', 'INFO',
                'Adversarial seed-link input, no mutation of issued immutable events.', null,
                ['seed_run_id' => $case === 'cycle' ? $id : $seedId]);
            if ($case === 'duplicate') (new EodRunRepository())->appendEvent($child, 'FINALIZE', 'RUN_CREATED', 'INFO',
                'Contradictory second creation identity.', null, ['seed_run_id' => $seedId]);
            $refused = false;
            try { $service->assessRun($id); }
            catch (RuntimeException $error) { $this->assertSame($expected, $error->getMessage()); $refused = true; }
            $this->assertTrue($refused, 'Invalid acquisition lineage must not receive a measurement: '.$case);
            $this->assertSame(0, DB::table('eod_run_events')->where('run_id', $id)->where('event_type', 'FRESHNESS_MEASURED')->count());
        }
        $this->assertSame($before, (array) DB::table('eod_runs')->where('run_id', $seedId)->first());
        $this->assertSame((int) $world['publication_id'], (int) DB::table('eod_current_publication_pointer')->where('trade_date', '2026-03-23')->value('publication_id'));
        $this->retainObservedWorld('v2_seed_lineage_refusal_matrix', array_merge([$seedId, $world['run_id']], $caseRuns), $report);
    }

    public function test_legitimate_promotion_config_change_preserves_each_verified_execution_snapshot(): void
    {
        $before = config('market_data.pipeline.daily_enabled'); $seedId = null;
        try {
            config(['market_data.pipeline.daily_enabled' => false]);
            $world = R0025SyntheticV2World::build(['profile' => 'market-data-row-hash/v1',
                'run_workflow' => function ($pipeline) use (&$seedId) {
                    $seed = $pipeline->importDaily('2026-03-23', 'api'); $seedId = (int) $seed->run_id;
                    config(['market_data.pipeline.daily_enabled' => true]);
                    return $pipeline->promoteDaily('2026-03-23', 'api', $seedId);
                }]);
            $seed = EodRun::findOrFail($seedId); $promotion = EodRun::findOrFail($world['run_id']);
            $this->assertSame('READABLE', $promotion->publishability_state);
            $this->assertNotSame($seed->config_hash, $promotion->config_hash, 'Promotion executes under its own actual config, never the earlier seed config.');
            $report = (new MarketDataFreshnessEvidenceService())->assessRun($world['run_id']);
            $this->assertSame($seedId, $report['acquisition_run_id']);
            $this->assertSame($seed->config_hash, $report['acquisition_config_hash']);
            $this->assertSame((int) $seed->config_snapshot_id, $report['acquisition_config_snapshot_id']);
            $this->assertSame($promotion->config_hash, $report['config_hash']);
            $this->assertNotNull($report['milestones']['first_acquisition_attempt']);
            $originalIdentity = ['config_snapshot_id' => $seed->config_snapshot_id, 'config_hash' => $seed->config_hash, 'config_snapshot_ref' => $seed->config_snapshot_ref];
            DB::table('eod_runs')->where('run_id', $seedId)->update(['config_snapshot_id' => $promotion->config_snapshot_id,
                'config_hash' => $promotion->config_hash, 'config_snapshot_ref' => $promotion->config_snapshot_ref]);
            try {
                $refused = false;
                try { (new MarketDataFreshnessEvidenceService())->assessRun($world['run_id']); }
                catch (RuntimeException $error) { $this->assertSame('FRESHNESS_SEED_CONFIG_IDENTITY_UNVERIFIED', $error->getMessage()); $refused = true; }
                $this->assertTrue($refused, 'A different valid snapshot cannot replace the immutable acquisition producer binding.');
            } finally { DB::table('eod_runs')->where('run_id', $seedId)->update($originalIdentity); }
            $this->retainObservedWorld('separate_acquisition_promotion_config', [$seedId, $world['run_id']], $report);
        } finally { config(['market_data.pipeline.daily_enabled' => $before]); }
    }

    public function test_disabled_daily_schedule_preserves_moving_development_frontier_with_asof_and_no_operational_success_or_incident(): void
    {
        $daily = config('market_data.pipeline.daily_enabled'); $date = '2026-03-23';
        try {
            config(['market_data.pipeline.daily_enabled' => false]);
            $world = R0025SyntheticV2World::build(['profile' => 'market-data-row-hash/v1',
                'run_clock' => '2026-03-23 18:00:00', 'http_fetcher' => function () use (&$date) {
                    $body = json_decode(file_get_contents(R0025SyntheticV2World::packagePath().'/inputs/provider_response.json'), true);
                    $body['chart']['result'][0]['timestamp'] = [(new DateTimeImmutable($date.' 09:00:00', new DateTimeZone('Asia/Jakarta')))->getTimestamp()];
                    return ['status' => 200, 'headers' => ['Content-Type: application/json'], 'body' => json_encode($body)];
                }]);
            $schedule = new \Illuminate\Console\Scheduling\Schedule();
            $kernel = $this->app->make(\Illuminate\Contracts\Console\Kernel::class);
            $method = new ReflectionMethod(get_class($kernel), 'schedule'); $method->setAccessible(true); $method->invoke($kernel, $schedule);
            $scheduledDaily = array_filter($schedule->events(), function ($event) { return strpos((string) $event->command, 'market-data:scheduled') !== false; });
            $this->assertCount(0, $scheduledDaily, 'daily_enabled=false is a valid development choice; it must not register daily acquisition.');
            $oldHistory = DB::table('eod_bars_history')->where('publication_id', $world['publication_id'])->get()->toJson();
            DB::table('md_market_calendar_revisions')->insert([
                'revision_uid' => hash('sha256', 'e049-frontier-independent-2026-03-24'), 'market_code' => 'IDX', 'market_segment' => 'REGULAR',
                'cal_date' => '2026-03-24', 'timezone' => 'Asia/Jakarta', 'is_trading_day' => 1, 'is_half_day' => 0,
                'session_state' => 'COMPLETED', 'session_open_at' => '2026-03-24 09:00:00', 'session_close_at' => '2026-03-24 16:00:00',
                'completed_at' => '2026-03-24 16:00:00', 'recorded_at' => '2026-03-24 17:00:00',
                'source_ref' => 'independent-e049-next-session', 'source_version' => 'e049-test-only-v1', 'provenance_tier' => 'VERIFIED',
                'reconciled_at' => '2026-03-24 17:00:00', 'reconciliation_source_ref' => 'independent-e049-next-session',
            ]);
            // Independent future-recorded control: the March 24 publication is
            // actually acquired, canonicalized and sealed only on March 25.
            $date = '2026-03-24'; Carbon::setTestNow('2026-03-25 18:00:00');
            $next = $world['pipeline']->runDaily($date, 'api');
            $this->assertSame('READABLE', $next->publishability_state);
            $this->assertSame('2026-03-25 18:00:00', DB::table('md_source_observations')->where('run_id', $next->run_id)->value('acquired_at'));
            Carbon::setTestNow('2026-03-24 18:00:00');
            $service = new MarketDataFreshnessEvidenceService();
            $gap = $service->assessRun($world['run_id']);
            $this->retainObservedWorld('frontier_before_assertions', [$world['run_id']], $gap + [
                'observed_source_rows' => DB::table('md_source_observation_rows as rows')->join('md_source_observations as obs', 'obs.source_observation_id', '=', 'rows.source_observation_id')
                    ->where('rows.trade_date', '>=', '2026-03-23')->orderByDesc('rows.trade_date')->limit(10)->get(['rows.trade_date', 'obs.acquired_at', 'obs.run_id']),
                'observed_canonical_rows' => DB::table('eod_bars')->where('trade_date', '>=', '2026-03-23')->orderByDesc('trade_date')->limit(10)->get(),
            ]);
            $this->assertSame('2026-03-24', $gap['latest_expected_trade_date']);
            foreach (['latest_acquired_trade_date', 'latest_canonicalized_trade_date', 'latest_readable_trade_date'] as $key) $this->assertSame('2026-03-23', $gap[$key], $key);
            $this->assertSame('2026-03-24T18:00:00+07:00', $gap['assessed_at']);
            $status = (new \App\Application\MarketData\Services\MarketDataOperationalStatusService())->forDate('2026-03-23', $world['run_id']);
            $this->assertSame('2026-03-24T18:00:00+07:00', $status['evidence_as_of'] ?? null, 'Status must disclose the clock governing frontier evidence.');
            foreach (['latest_acquired_trade_date', 'latest_canonicalized_trade_date', 'latest_readable_trade_date'] as $key) {
                $this->assertSame('2026-03-23', $status[$key], 'Status must not claim future-recorded frontier knowledge: '.$key);
            }
            $this->assertSame('DEVELOPMENT', $gap['activation_state']);
            $this->assertSame('NOT_APPLICABLE', $gap['evaluation']['assessment']);
            $this->assertNull($gap['evaluation']['freshness_target']);
            $this->assertNull($gap['evaluation']['source_evaluation']);
            $this->assertFalse($gap['operational_activation_proven']);
            $this->assertSame('NOT_CLAIMED', $gap['operational_slo_compliance']);
            $this->assertSame([], (new \App\Application\MarketData\Services\MarketDataIncidentResponseService())->observeRun($world['run_id'])['classes']);
            $this->assertSame(0, DB::table('eod_run_events')->whereIn('event_type', ['FRESHNESS_ALERT_OPENED', 'FRESHNESS_ALERT_ESCALATED'])->count());
            // A declared future frontier is not a capability boundary: actual
            // authenticated acquisition/promotion moves it without activation.
            Carbon::setTestNow('2026-03-25 18:00:00');
            $moved = $service->assessRun($next->run_id);
            foreach (['latest_acquired_trade_date', 'latest_canonicalized_trade_date', 'latest_readable_trade_date'] as $key) $this->assertSame('2026-03-24', $moved[$key]);
            $this->assertSame(0, \Illuminate\Support\Facades\Artisan::call('market-data:status', ['--requested_date' => '2026-03-24', '--run_id' => $next->run_id]));
            $statusMoved = json_decode(\Illuminate\Support\Facades\Artisan::output(), true);
            $this->assertSame('2026-03-25T18:00:00+07:00', $statusMoved['evidence_as_of']);
            foreach (['latest_acquired_trade_date', 'latest_canonicalized_trade_date', 'latest_readable_trade_date'] as $key) $this->assertSame('2026-03-24', $statusMoved[$key]);
            $this->assertSame('NOT_APPLICABLE', $moved['evaluation']['assessment']);
            $this->assertSame('NOT_CLAIMED', $moved['operational_slo_compliance']);
            $this->assertNull(config('market_data.scope.operational_start_date'));
            $this->assertSame($oldHistory, DB::table('eod_bars_history')->where('publication_id', $world['publication_id'])->get()->toJson());
            $this->assertSame((int) $world['publication_id'], (int) DB::table('eod_current_publication_pointer')->where('trade_date', '2026-03-23')->value('publication_id'));
            $this->retainObservedWorld('moving_development_frontier', [$world['run_id'], (int) $next->run_id], ['gap' => $gap, 'moved' => $moved]);
        } finally { config(['market_data.pipeline.daily_enabled' => $daily]); }
    }

    public function test_on_target_measurement_does_not_verify_price_or_external_clock_fact_or_claim_slo_attainment(): void
    {
        config(['market_data.platform.cutoff_time' => '17:30:00']);
        // Frozen independent input says close=105. A controlled provider revision
        // says 106, which is still individually admissible. Neither is asserted
        // here to be an external market oracle or a new golden replay fixture.
        $world = R0025SyntheticV2World::build(['run_clock' => '2026-03-23 17:30:00',
            'http_fetcher' => function () {
                $body = json_decode(file_get_contents(R0025SyntheticV2World::packagePath().'/inputs/provider_response.json'), true);
                $body['chart']['result'][0]['indicators']['quote'][0]['close'][0] = 106;
                return ['status' => 200, 'headers' => ['Content-Type: application/json'], 'body' => json_encode($body)];
            }]);
        $fixedInput = json_decode(file_get_contents(R0025SyntheticV2World::packagePath().'/inputs/provider_response.json'), true);
        $this->assertSame(105, $fixedInput['chart']['result'][0]['indicators']['quote'][0]['close'][0]);
        $this->assertEquals(106, DB::table('eod_bars_history')->where('publication_id', $world['publication_id'])->value('close'));
        $this->assertSame('READABLE', \App\Models\EodRun::findOrFail($world['run_id'])->publishability_state);
        config(['market_data.scope.operational_start_date' => '2026-03-23']);
        $service = new MarketDataFreshnessEvidenceService(); $first = $service->assessRun($world['run_id']);
        $this->assertSame('ON_TARGET', $first['evaluation']['freshness_target']);
        $this->assertSame(0, $first['evaluation']['expected_session_lag']);
        $this->assertSame(5400, $first['elapsed_seconds_from_session_completion']['active_publication_readability']);
        $this->assertSame('NOT_CLAIMED', $first['operational_slo_compliance']);
        $this->assertFalse($first['operational_activation_proven']);
        $this->assertStringContainsString('not price verification, platform correctness', $first['claim_limit']);
        $unobserved = (new \App\Application\MarketData\Services\MarketDataIncidentResponseService())->observeRun($world['run_id']);
        $this->assertSame([], $unobserved['classes'], 'A price differing from the fixed fixture does not create a detected condition by itself.');
        $this->assertSame('NO_CLASSIFIED_OBSERVED_CONDITION', $unobserved['state']);
        $this->assertStringContainsString('no completeness, health, consumer consequence', $unobserved['claim_limit']);
        $firstRow = DB::table('eod_run_events')->where('run_id', $world['run_id'])->where('event_type', 'FRESHNESS_MEASURED')->orderBy('event_id')->first();
        $history = DB::table('eod_bars_history')->get()->toJson(); $pointer = DB::table('eod_current_publication_pointer')->get()->toJson();
        // Independently controlled inaccurate calendar assertion. The monitor
        // can measure this declared boundary precisely; it cannot authenticate
        // its external truth. Preserve both revision identities and measurements.
        $revision = (array) DB::table('md_market_calendar_revisions')->where('calendar_revision_id', $first['calendar_revision_id'])->first();
        unset($revision['calendar_revision_id']);
        $revision['revision_uid'] = hash('sha256', 'e049-wrong-clock-independent-control');
        $revision['source_ref'] = 'independent-e049-deliberately-five-minutes-early';
        $revision['completed_at'] = '2026-03-23 15:55:00';
        $revision['session_close_at'] = '2026-03-23 15:55:00';
        $revision['recorded_at'] = '2026-03-23 17:31:00';
        $revision['supersedes_revision_id'] = $first['calendar_revision_id'];
        $secondRevision = DB::table('md_market_calendar_revisions')->insertGetId($revision, 'calendar_revision_id');
        Carbon::setTestNow('2026-03-23 17:31:01');
        $differentClock = $service->assessRun($world['run_id']);
        $this->assertSame((int) $secondRevision, $differentClock['calendar_revision_id']);
        $this->assertSame('independent-e049-deliberately-five-minutes-early', $differentClock['calendar_source_ref']);
        $this->assertSame('ON_TARGET', $differentClock['evaluation']['freshness_target']);
        $this->assertSame(5700, $differentClock['elapsed_seconds_from_session_completion']['active_publication_readability']);
        $this->assertSame(300, $differentClock['elapsed_seconds_from_session_completion']['active_publication_readability'] - $first['elapsed_seconds_from_session_completion']['active_publication_readability']);
        $this->assertSame($firstRow->event_payload_json, DB::table('eod_run_events')->where('event_id', $firstRow->event_id)->value('event_payload_json'));
        $this->assertSame($history, DB::table('eod_bars_history')->get()->toJson());
        $this->assertSame($pointer, DB::table('eod_current_publication_pointer')->get()->toJson());
        $this->retainObservedWorld('timeliness_claim_and_declared_clock_boundaries', [$world['run_id']], ['first' => $first, 'changed_declared_clock' => $differentClock]);
    }

    public function test_prior_diagnostic_measurement_cannot_invent_first_readability_without_native_finalization_identity(): void
    {
        $world = $this->build();
        $service = new MarketDataFreshnessEvidenceService();
        $native = $service->assessRun($world['run_id']);
        $this->assertSame('2026-03-23 18:00:03', $native['milestones']['active_publication_readability']);
        $canonical = DB::table('eod_bars')->get()->toJson();
        $history = DB::table('eod_bars_history')->get()->toJson();
        $pointer = DB::table('eod_current_publication_pointer')->get()->toJson();
        // Connection-local fault view: preserve the actual append-only table and
        // all native producer identities. Hide only the native finalization,
        // leaving an earlier diagnostic that purported to know readability.
        DB::statement('CREATE TEMPORARY TABLE e049_event_fault LIKE eod_run_events');
        DB::statement("INSERT INTO e049_event_fault SELECT * FROM eod_run_events WHERE NOT (run_id = ? AND event_type = 'RUN_FINALIZED')", [$world['run_id']]);
        DB::statement('CREATE TEMPORARY TABLE eod_run_events LIKE e049_event_fault');
        DB::statement('INSERT INTO eod_run_events SELECT * FROM e049_event_fault');
        try {
            config(['market_data.scope.operational_start_date' => '2026-03-23']);
            $missing = $service->assessRun($world['run_id']);
            $this->assertTrue($missing['gateway_readable']);
            $this->assertNull($missing['milestones']['active_publication_readability'], 'A prior diagnostic cannot substitute for the native publication finalization identity.');
            $this->assertNull($missing['elapsed_seconds_from_session_completion']['active_publication_readability']);
            $this->assertSame('EVIDENCE_INCOMPLETE', $missing['evaluation']['assessment']);
            $this->assertSame('FIRST_READABILITY_NOT_RETAINED', $missing['evaluation']['reason']);
            $this->assertSame($canonical, DB::table('eod_bars')->get()->toJson());
            $this->assertSame($history, DB::table('eod_bars_history')->get()->toJson());
            $this->assertSame($pointer, DB::table('eod_current_publication_pointer')->get()->toJson());
            $this->retainObservedWorld('unretained_first_readability_fault', [$world['run_id']], $missing);
        } finally {
            DB::statement('DROP TEMPORARY TABLE eod_run_events');
            DB::statement('DROP TEMPORARY TABLE e049_event_fault');
        }
        $this->assertSame(1, DB::table('eod_run_events')->where('run_id', $world['run_id'])->where('event_type', 'RUN_FINALIZED')->count(), 'The actual immutable native finalization remains intact.');
        $this->assertSame($native['milestones']['active_publication_readability'], $service->assessRun($world['run_id'])['milestones']['active_publication_readability']);
    }

    // Independent JSON hash oracle; no production hashing method is called.
    private function canonical($value): string
    {
        $sort = function ($item) use (&$sort) {
            if (is_array($item)) {
                if ($item && array_keys($item) !== range(0, count($item) - 1)) ksort($item, SORT_STRING);
                foreach ($item as $key => $child) $item[$key] = $sort($child);
            }
            return $item;
        };
        return json_encode($sort($value), JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRESERVE_ZERO_FRACTION);
    }
}
