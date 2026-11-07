<?php

use App\Application\MarketData\Services\MarketDataSessionFreshnessService;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\Support\R0025SyntheticV2World;
use Tests\Support\UsesMarketDataMariaDb;

class B19ExpectedSessionEvidenceMariaDbTest extends TestCase
{
    use UsesMarketDataMariaDb;
    use \Tests\Support\RetainsB19ObservedEvidence;

    protected function setUp(): void { parent::setUp(); $this->bootMarketDataMariaDb(); $this->assertTrue(Schema::hasTable('md_freshness_measurements'), 'The actual MariaDB schema must include the monitoring migration.'); }
    protected function tearDown(): void { Carbon::setTestNow(); $this->tearDownMarketDataMariaDb(); parent::tearDown(); }

    private function world(?callable $fetcher = null): array
    {
        $layout = ['profile' => 'market-data-row-hash/v1', 'run_clock' => '2026-03-23 18:00:00'];
        if ($fetcher !== null) $layout['http_fetcher'] = $fetcher;
        $world = R0025SyntheticV2World::build($layout);
        DB::table('md_market_calendar_revisions')->insert([
            'revision_uid' => hash('sha256', 'e049-absent-run-independent-2026-03-24'), 'market_code' => 'IDX', 'market_segment' => 'REGULAR',
            'cal_date' => '2026-03-24', 'timezone' => 'Asia/Jakarta', 'is_trading_day' => 1, 'is_half_day' => 0,
            'session_state' => 'COMPLETED', 'session_open_at' => '2026-03-24 09:00:00', 'session_close_at' => '2026-03-24 16:00:00',
            'completed_at' => '2026-03-24 16:00:00', 'recorded_at' => '2026-03-24 17:00:00',
            'source_ref' => 'independent-e049-absent-run-session', 'source_version' => 'e049-test-only-v1', 'provenance_tier' => 'VERIFIED',
            'reconciled_at' => '2026-03-24 17:00:00', 'reconciliation_source_ref' => 'independent-e049-absent-run-session',
        ]);
        Carbon::setTestNow('2026-03-24 18:00:00');
        return $world;
    }

    public function test_absent_expected_run_is_persisted_unavailable_not_success_and_command_refuses_wrong_identity_and_missing_calendar(): void
    {
        $world = $this->world(); $service = new MarketDataSessionFreshnessService();
        $runs = DB::table('eod_runs')->count();
        $history = DB::table('eod_bars_history')->get()->toJson(); $pointer = DB::table('eod_current_publication_pointer')->get()->toJson();
        $report = $service->measure('2026-03-24');
        $this->assertNull($report['run_id']); $this->assertNull($report['publication_id']);
        $this->assertFalse($report['gateway_readable']);
        $this->assertSame('EXPECTED_SESSION_WITHOUT_RUN', $report['measurement_scope']);
        $this->assertSame('2026-03-24', $report['latest_expected_trade_date']);
        $this->assertSame('2026-03-23', $report['latest_readable_trade_date']);
        foreach ($report['milestones'] as $time) $this->assertNull($time);
        foreach ($report['elapsed_seconds_from_session_completion'] as $elapsed) $this->assertNull($elapsed, 'Missing is unavailable, never zero delay.');
        $this->assertSame('NOT_APPLICABLE', $report['evaluation']['assessment']);
        $this->assertSame('NOT_CLAIMED', $report['operational_slo_compliance']);
        $this->assertFalse($report['operational_activation_proven']);
        $this->assertSame($runs, DB::table('eod_runs')->count(), 'Monitoring must not fabricate an acquisition worker.');
        $this->assertSame($report['measurement_id'], $service->measure('2026-03-24')['measurement_id']);
        $command = new \App\Console\Commands\MarketData\MeasureExpectedSessionCommand(); $command->setLaravel($this->app);
        $tester = new \Symfony\Component\Console\Tester\CommandTester($command);
        $this->assertSame(2, $tester->execute(['--date' => '2026-03-24']));
        $this->assertNull(json_decode(trim($tester->getDisplay()), true)['run_id']);
        $this->assertSame(1, $tester->execute(['--date' => '2026-03-24', '--run_id' => (string) $world['run_id']]));
        $this->assertStringContainsString('FRESHNESS_REQUEST_RUN_SCOPE_MISMATCH', $tester->getDisplay());
        $this->assertSame(1, $tester->execute(['--date' => '2026-03-24', '--run_id' => '-1']));
        $before = DB::table('md_freshness_measurements')->count();
        DB::table('md_market_calendar_revisions')->where('cal_date', '2026-03-24')->delete();
        $this->assertSame(1, $tester->execute(['--date' => '2026-03-24']));
        $this->assertStringContainsString('MARKET_CALENDAR_EVIDENCE_MISSING', $tester->getDisplay());
        $this->assertSame($before, DB::table('md_freshness_measurements')->count());
        $this->assertSame($history, DB::table('eod_bars_history')->get()->toJson());
        $this->assertSame($pointer, DB::table('eod_current_publication_pointer')->get()->toJson());
        $this->retainObservedWorld('absent_expected_run', [$world['run_id']], ['report' => $report,
            'persisted_measurements' => DB::table('md_freshness_measurements')->orderBy('measurement_id')->get()->all()]);
    }

    public function test_session_monitor_serializes_competing_connections_and_append_only_evidence_rejects_update_and_delete(): void
    {
        $world = $this->world(); $service = new MarketDataSessionFreshnessService();
        $report = $service->measure('2026-03-24');
        $row = (array) DB::table('md_freshness_measurements')->where('measurement_id', $report['measurement_id'])->first();
        $decoded = json_decode($row['measurement_json'], true);
        // Independent hash of the retained exact canonical bytes, not a production helper.
        $this->assertSame(hash('sha256', $row['measurement_json']), $row['content_sha256']);
        $this->assertSame('2026-03-24', $decoded['requested_date']);
        $this->assertSame('ASSESSMENT', $decoded['record_type']);
        foreach (['update', 'delete'] as $operation) {
            $refused = false;
            try {
                $query = DB::table('md_freshness_measurements')->where('measurement_id', $report['measurement_id']);
                if ($operation === 'update') $query->update(['measurement_json' => '{}']); else $query->delete();
            } catch (\Illuminate\Database\QueryException $error) { $this->assertStringContainsString('FRESHNESS_EVIDENCE_APPEND_ONLY', $error->getMessage()); $refused = true; }
            $this->assertTrue($refused, 'Persisted freshness evidence is append-only: '.$operation);
        }
        config(['database.connections.e049_monitor_contender' => config('database.connections.'.$this->marketDataMariaDbConnection)]);
        $other = DB::connection('e049_monitor_contender');
        $key = 'md-freshness:'.substr(hash('sha256', DB::connection()->getDatabaseName().'|2026-03-24'), 0, 48);
        $this->assertSame(1, (int) $other->selectOne('SELECT GET_LOCK(?, 0) AS acquired', [$key])->acquired);
        $before = DB::table('md_freshness_measurements')->count();
        try {
            $refused = false;
            try { $service->measure('2026-03-24'); }
            catch (RuntimeException $error) { $this->assertSame('FRESHNESS_MEASUREMENT_LOCK_CONFLICT', $error->getMessage()); $refused = true; }
            $this->assertTrue($refused, 'Competing monitor must not append duplicated transitions.');
        } finally { $other->selectOne('SELECT RELEASE_LOCK(?) AS released', [$key]); DB::disconnect('e049_monitor_contender'); }
        $this->assertSame($before, DB::table('md_freshness_measurements')->count());
        $this->assertSame($row, (array) DB::table('md_freshness_measurements')->where('measurement_id', $report['measurement_id'])->first());
        $this->assertSame($report['measurement_id'], $service->measure('2026-03-24')['measurement_id']);
        $this->retainObservedWorld('monitor_lock_and_immutable_ledger', [$world['run_id']], $report + ['row' => $row]);
    }
    public function test_integrity_alert_without_a_run_deduplicates_and_recovers_only_through_real_requested_date_promotion(): void
    {
        $date = '2026-03-23';
        $world = $this->world(function () use (&$date) {
            $body = json_decode(file_get_contents(R0025SyntheticV2World::packagePath().'/inputs/provider_response.json'), true);
            $body['chart']['result'][0]['timestamp'] = [(new DateTimeImmutable($date.' 09:00:00', new DateTimeZone('Asia/Jakarta')))->getTimestamp()];
            return ['status' => 200, 'headers' => ['Content-Type: application/json'], 'body' => json_encode($body)];
        });
        $history = DB::table('eod_bars_history')->where('publication_id', $world['publication_id'])->get()->toJson();
        $oldPointer = (array) DB::table('eod_current_publication_pointer')->where('trade_date', '2026-03-23')->first();
        $fault = $oldPointer; $fault['trade_date'] = '2026-03-24'; $fault['updated_at'] = '2026-03-24 18:00:00';
        // The unique publication constraint forbids two pointers to the same
        // publication. Inject an actual cross-date pointer move instead, then
        // restore this exact controlled pointer before legitimate promotion.
        DB::table('eod_current_publication_pointer')->where('trade_date', '2026-03-23')->delete();
        DB::table('eod_current_publication_pointer')->insert($fault);
        $service = new MarketDataSessionFreshnessService();
        $bad = $service->measure('2026-03-24');
        $this->assertFalse($bad['gateway_readable']); $this->assertNotEmpty($bad['gateway_error']);
        $this->assertSame('NOT_APPLICABLE', $bad['evaluation']['assessment']);
        $this->assertSame(1, DB::table('md_freshness_measurements')->where('trade_date', '2026-03-24')->where('record_type', 'ALERT_OPENED')->count());
        $alert = json_decode(DB::table('md_freshness_measurements')->where('record_type', 'ALERT_OPENED')->value('measurement_json'), true);
        $this->assertSame('CRITICAL', $alert['alert_severity']); $this->assertSame('GATEWAY_INTEGRITY', $alert['root_cause']);
        $policy = new \App\Application\MarketData\Services\MarketDataIncidentResponseService();
        $incidentRow = DB::table('md_freshness_measurements')->where('record_type', 'INCIDENT_RESPONSE')->first();
        $this->assertNotNull($incidentRow, 'A surfaced no-worker integrity condition must create persisted incident intake.');
        $incident = json_decode($incidentRow->measurement_json, true);
        $this->assertSame(hash('sha256', $incidentRow->measurement_json), $incidentRow->content_sha256);
        $this->assertSame('2026-03-24', $incident['requested_date']);
        $this->assertNull($incident['run_id']); $this->assertNull($incident['source_mode']); $this->assertNull($incident['publication_id']);
        $this->assertSame($bad['measurement_id'], $incident['assessment_id']);
        $this->assertEquals($fault, $incident['observed_pointer']);
        $this->assertSame(['integrity'], $incident['classes']); $this->assertSame('CRITICAL', $incident['severity_floor']);
        $this->assertSame('UNSUPPORTED_PENDING_F018', $incident['incident_closure']);
        $policy->assertActionAllowed('2026-03-25', 'new_acquisition');
        $policy->assertActionAllowed('2026-03-24', 'evidence_export');
        foreach (['new_acquisition', 'ordinary_retry', 'prior_fallback', 'consumer_read', 'correction_baseline'] as $action) {
            $refused = false;
            try { $policy->assertActionAllowed('2026-03-24', $action); }
            catch (RuntimeException $error) { $this->assertStringContainsString('INCIDENT_RESPONSE_BLOCKED:', $error->getMessage()); $refused = true; }
            $this->assertTrue($refused, 'Observed date-scoped gateway ambiguity without a worker must block '.$action);
        }

        $before = DB::table('md_freshness_measurements')->count();
        foreach (['date', 'assessment', 'assessment_hash', 'pointer', 'source', 'publication'] as $case) {
            DB::beginTransaction();
            try {
                $forged = $incident;
                if ($case === 'date') $forged['requested_date'] = '2026-03-25';
                if ($case === 'assessment') $forged['assessment_id'] = $bad['measurement_id'] + 999;
                if ($case === 'assessment_hash') $forged['assessment_sha256'] = str_repeat('a', 64);
                if ($case === 'pointer') $forged['observed_pointer']['run_id'] += 999;
                if ($case === 'source') $forged['source_mode'] = 'api';
                if ($case === 'publication') $forged['publication_id'] = $world['publication_id'];
                $json = \App\Infrastructure\Persistence\MarketData\RunInputCaptureRepository::canonicalJson($forged);
                DB::table('md_freshness_measurements')->insert([
                    'trade_date' => '2026-03-24', 'run_id' => null, 'record_type' => 'INCIDENT_RESPONSE',
                    'measurement_json' => $json, 'content_sha256' => hash('sha256', $json), 'recorded_at' => $incidentRow->recorded_at,
                ]);
                $refused = false;
                try { $policy->assertActionAllowed('2026-03-24', 'evidence_export'); }
                catch (RuntimeException $error) { $this->assertSame('INCIDENT_SESSION_ASSESSMENT_IDENTITY_INVALID', $error->getMessage()); $refused = true; }
                $this->assertTrue($refused, 'Rehashed no-worker incident must bind the original assessment: '.$case);
            } finally { DB::rollBack(); }
        }
        $this->assertSame($before, DB::table('md_freshness_measurements')->count());

        $service->measure('2026-03-24');
        $this->assertSame(1, DB::table('md_freshness_measurements')->where('record_type', 'ALERT_OPENED')->count());
        $this->assertSame(1, DB::table('md_freshness_measurements')->where('record_type', 'INCIDENT_RESPONSE')->count(), 'Repeated assessment must not duplicate incident intake.');
        $refused = false;
        try { (new \App\Infrastructure\Persistence\MarketData\EodPublicationRepository())->findLatestReadablePublicationBefore('2026-03-24'); }
        catch (RuntimeException $error) { $this->assertStringContainsString('INCIDENT_RESPONSE_BLOCKED: prior_fallback', $error->getMessage()); $refused = true; }
        $this->assertTrue($refused, 'Actual fallback entry point must enforce no-worker containment.');
        // Removing this controlled invalid pointer is not recovery: no current
        // readable publication for the requested date exists yet.
        DB::table('eod_current_publication_pointer')->where('trade_date', '2026-03-24')->delete();
        DB::table('eod_current_publication_pointer')->insert($oldPointer);
        $empty = $service->measure('2026-03-24'); $this->assertFalse($empty['gateway_readable']);
        $this->assertSame($world['publication_id'], (int) (new \App\Infrastructure\Persistence\MarketData\EodPublicationRepository())->resolveCurrentReadablePublicationForTradeDate('2026-03-23')->publication_id);
        $policy->assertActionAllowed('2026-03-24', 'new_acquisition');
        $this->assertSame((array) $incidentRow, (array) DB::table('md_freshness_measurements')->where('measurement_id', $incidentRow->measurement_id)->first(), 'Current gateway re-verification is not mutation or closure of the incident record.');
        $this->assertSame(0, DB::table('md_freshness_measurements')->where('record_type', 'ALERT_RECOVERED')->count(), 'An unreadable or merely imported date cannot recover an alert.');
        $date = '2026-03-24'; $seed = $world['pipeline']->importDaily($date, 'api');
        $refused = false;
        try { $service->measure($date); }
        catch (RuntimeException $error) { $this->assertStringContainsString('FRESHNESS_RUN_SELECTION_REQUIRED', $error->getMessage()); $refused = true; }
        $this->assertTrue($refused, 'An unverified candidate run is never picked by latest-ID guessing.');
        $partial = $service->measure($date, (int) $seed->run_id);
        $this->assertFalse($partial['gateway_readable']); $this->assertNull($partial['milestones']['candidate_seal']);
        $this->assertSame(0, DB::table('md_freshness_measurements')->where('record_type', 'ALERT_RECOVERED')->count(), 'An unreadable or merely imported date cannot recover an alert.');
        $promotion = $world['pipeline']->promoteDaily($date, 'api', $seed->run_id);
        $this->assertSame('READABLE', $promotion->publishability_state);
        $recovered = $service->measure($date);
        $this->assertSame((int) $promotion->run_id, $recovered['run_id']);
        $this->assertSame((int) $seed->run_id, $recovered['acquisition_run_id']);
        $this->assertTrue($recovered['gateway_readable']);
        $this->assertSame((int) $promotion->publication_id, $recovered['publication_id']);
        foreach ($recovered['milestones'] as $name => $time) $this->assertNotNull($time, $name);
        $recovery = json_decode(DB::table('md_freshness_measurements')->where('record_type', 'ALERT_RECOVERED')->value('measurement_json'), true);
        $this->assertSame($alert['dedup_key'], $recovery['dedup_key']);
        $this->assertSame((int) $promotion->publication_id, $recovery['publication_id']);
        $service->measure($date);
        $this->assertSame(1, DB::table('md_freshness_measurements')->where('record_type', 'ALERT_RECOVERED')->count());
        $this->assertSame($history, DB::table('eod_bars_history')->where('publication_id', $world['publication_id'])->get()->toJson());
        $this->assertSame($oldPointer, (array) DB::table('eod_current_publication_pointer')->where('trade_date', '2026-03-23')->first());
        $this->retainObservedWorld('no_run_integrity_to_real_promotion', [$world['run_id'], (int) $seed->run_id, (int) $promotion->run_id],
            ['bad' => $bad, 'partial' => $partial, 'recovered' => $recovered,
             'persisted_measurements' => DB::table('md_freshness_measurements')->orderBy('measurement_id')->get()->all()]);
    }

    public function test_integrity_alert_is_critical_before_the_freshness_deadline_in_both_run_and_session_paths(): void
    {
        $world = $this->world();
        // Conditional mechanism only. The original run/config stays immutable;
        // this does not approve activation or create operational SLO evidence.
        config(['market_data.scope.operational_start_date' => '2026-03-23']);
        $pointer = (array) DB::table('eod_current_publication_pointer')->where('trade_date', '2026-03-23')->first();
        $history = DB::table('eod_bars_history')->get()->toJson();
        $canonical = DB::table('eod_bars')->get()->toJson();
        DB::table('eod_current_publication_pointer')->where('trade_date', '2026-03-23')->update(['publication_version' => 999]);
        $runReport = (new \App\Application\MarketData\Services\MarketDataFreshnessEvidenceService())->assessRun($world['run_id']);
        $this->assertFalse($runReport['gateway_readable']);
        $this->assertNotEmpty($runReport['gateway_error']);
        $this->assertSame('PENDING', $runReport['evaluation']['freshness_target']);
        $runAlert = json_decode(DB::table('eod_run_events')->where('run_id', $world['run_id'])->where('event_type', 'FRESHNESS_ALERT_OPENED')->value('event_payload_json'), true);
        $this->assertSame('GATEWAY_INTEGRITY', $runAlert['root_cause']);
        $this->assertSame('CRITICAL', $runAlert['alert_severity'], 'Run-bound integrity ambiguity must remain CRITICAL even before a freshness breach.');
        // A new no-worker OPERATIONAL snapshot is not authorized by the current NULL-typed registry.
        // Exercise the session alert producer with this exact already captured run, never bypass that registry.
        $dateReport = (new MarketDataSessionFreshnessService())->measure('2026-03-23', $world['run_id']);
        $this->assertSame($world['run_id'], $dateReport['run_id']);
        $this->assertNotEmpty($dateReport['gateway_error']);
        $this->assertSame('PENDING', $dateReport['evaluation']['freshness_target']);
        $dateAlert = json_decode(DB::table('md_freshness_measurements')->where('trade_date', '2026-03-23')->where('record_type', 'ALERT_OPENED')->value('measurement_json'), true);
        $this->assertSame('GATEWAY_INTEGRITY', $dateAlert['root_cause']);
        $this->assertSame('CRITICAL', $dateAlert['alert_severity'], 'Session integrity ambiguity must remain CRITICAL even before a freshness breach.');
        DB::table('eod_current_publication_pointer')->where('trade_date', '2026-03-23')->delete();
        DB::table('eod_current_publication_pointer')->insert($pointer);
        $good = (new \App\Application\MarketData\Services\MarketDataFreshnessEvidenceService())->assessRun($world['run_id']);
        $this->assertTrue($good['gateway_readable']);
        $this->assertTrue((new MarketDataSessionFreshnessService())->measure('2026-03-23', $world['run_id'])['gateway_readable']);
        $this->assertSame(1, DB::table('md_freshness_measurements')->where('trade_date', '2026-03-23')->where('record_type', 'ALERT_RECOVERED')->count());
        $this->assertSame(1, DB::table('eod_run_events')->where('run_id', $world['run_id'])->where('event_type', 'FRESHNESS_ALERT_RECOVERED')->count());
        $this->assertSame(0, DB::table('md_freshness_measurements')->where('trade_date', '2026-03-24')->where('record_type', 'ALERT_RECOVERED')->count(), 'A verified neighboring date cannot recover the requested-date incident.');
        $this->assertSame($history, DB::table('eod_bars_history')->get()->toJson());
        $this->assertSame($canonical, DB::table('eod_bars')->get()->toJson());
        $this->assertSame($pointer, (array) DB::table('eod_current_publication_pointer')->where('trade_date', '2026-03-23')->first());
        $this->retainObservedWorld('critical_integrity_before_freshness_deadline', [$world['run_id']], ['run' => $runReport, 'date' => $dateReport, 'readable_prior' => $good]);
    }

    public function test_incomplete_assessment_horizon_cannot_report_an_earlier_date_as_the_latest_expected_session(): void
    {
        $world = $this->world();
        DB::table('md_market_calendar_revisions')->where('cal_date', '2026-03-24')->delete();
        $report = (new MarketDataSessionFreshnessService())->measure('2026-03-23', $world['run_id']);
        $this->assertNull($report['latest_expected_trade_date'], 'A missing required calendar date must not shorten the horizon.');
        $this->assertFalse($report['expected_horizon_provenance']['assessment_horizon_verified']);
        $this->assertStringContainsString('MARKET_CALENDAR_EVIDENCE_MISSING', $report['expected_horizon_provenance']['error']);
        $this->assertSame('NOT_APPLICABLE', $report['evaluation']['assessment']);
        $this->assertSame('NOT_CLAIMED', $report['operational_slo_compliance']);
        $this->retainObservedWorld('incomplete_monitor_horizon', [$world['run_id']], $report);
    }

}
