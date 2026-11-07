<?php

use App\Application\MarketData\Services\MarketDataIncidentResponseService;
use App\Application\MarketData\Services\MarketDataSessionFreshnessService;
use App\Infrastructure\Persistence\MarketData\EodRunRepository;
use App\Models\EodRun;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Tests\Support\R0025SyntheticV2World;

require_once __DIR__.'/B19OwnerFenceMariaDbTest.php';

/** Phase mechanism proof only. Configured test markers are not approved operational activation. */
class B19IncidentPhaseCoverageMariaDbTest extends B19OwnerFenceMariaDbTest
{
    use \Tests\Support\RetainsB19ObservedEvidence;
    private $originalActivation;

    protected function setUp(): void
    {
        parent::setUp();
        $this->originalActivation = config('market_data.scope.operational_start_date');
        config(['market_data.scope.operational_start_date' => null]);
    }

    protected function tearDown(): void
    {
        config(['market_data.scope.operational_start_date' => $this->originalActivation]);
        Carbon::setTestNow();
        parent::tearDown();
    }

    public function phaseMarkers(): array
    {
        return [
            'absent marker' => [null, false],
            'future marker' => ['2026-03-24', false],
            'effective exact date' => ['2026-03-23', true],
            'effective prior date' => ['2026-03-22', true],
        ];
    }

    private function observed(EodRun $run, string $reason): void
    {
        (new EodRunRepository())->recordObservation($run, 'detector', 'FINALIZE', 'OBSERVED_CONDITION', 'ERROR',
            'Explicit observed condition; no classifier severity or policy output supplied.', null, ['observed_condition' => $reason]);
    }

    private function publicationState(): array
    {
        $state = [];
        foreach (['eod_runs', 'eod_publications', 'eod_current_publication_pointer', 'eod_bars_history',
            'eod_indicators_history', 'eod_eligibility_history', 'md_source_observations', 'md_source_observation_rows'] as $table) {
            $rows = array_map(static fn ($r) => (array) $r, DB::table($table)->get()->all());
            usort($rows, static fn ($a, $b) => strcmp(json_encode($a), json_encode($b)));
            $state[$table] = $rows;
        }
        return $state;
    }

    /** @dataProvider phaseMarkers */
    public function test_availability_is_phase_gated_but_integrity_and_security_persist_in_every_phase(?string $marker, bool $operational): void
    {
        $world = R0025SyntheticV2World::build(['profile' => 'market-data-row-hash/v1']);
        $run = EodRun::findOrFail($world['run_id']);
        $before = $this->publicationState();
        config(['market_data.scope.operational_start_date' => $marker]);
        $service = new MarketDataIncidentResponseService();
        $this->observed($run, 'RUN_SOURCE_TIMEOUT');
        $availability = $service->observeRun((int) $run->run_id);
        $this->assertSame($operational ? ['source/schema'] : [], $availability['classes'], 'Availability uses the requested-date activation boundary.');
        $this->assertSame($operational ? 'MEDIUM' : null, $availability['severity_floor']);
        $this->assertFalse($availability['operational_activation_proven']);
        $this->assertSame('UNSUPPORTED_PENDING_F018', $availability['incident_closure']);

        $this->observed($run, 'HISTORY_MUTATION_ATTEMPT');
        $integrity = $service->observeRun((int) $run->run_id);
        $this->assertContains('integrity', $integrity['classes'], 'Integrity remains an incident before and after activation.');
        $this->assertSame('CRITICAL', $integrity['severity_floor']);
        $this->observed($run, 'UNAUTHENTICATED_PAYLOAD');
        $security = $service->observeRun((int) $run->run_id);
        $expected = $operational ? ['integrity', 'security/provenance', 'source/schema'] : ['integrity', 'security/provenance'];
        $this->assertSame($expected, $security['classes'], 'Security remains an incident in every phase.');
        $this->assertSame('CRITICAL', $security['severity_floor']);
        $this->assertTrue($security['protection']['block_requested_read']);
        $this->assertTrue($security['protection']['block_affected_evidence']);
        $this->assertFalse($security['operational_activation_proven']);
        $this->assertSame($before, $this->publicationState(), 'Observation never grants publication mutation authority.');
        $independent = $this->onIndependentOwnerConnection(function () use ($run) {
            $rows = DB::table('eod_run_events')->where('run_id', $run->run_id)->where('event_type', 'INCIDENT_RESPONSE_RECORDED')->orderBy('event_id')->get()->all();
            $error = null;
            try { (new MarketDataIncidentResponseService())->assertActionAllowed($run->trade_date_requested, 'ordinary_retry', (int) $run->run_id); }
            catch (RuntimeException $e) { $error = $e->getMessage(); }
            return ['connection_id' => (int) DB::selectOne('SELECT CONNECTION_ID() AS id')->id, 'rows' => $rows, 'refusal' => $error];
        });
        $this->retainObservedWorld('phase_'.($marker ?? 'absent'), [(int) $run->run_id], compact('marker', 'operational', 'availability', 'integrity', 'security', 'independent', 'before'));
        $this->assertNotSame((int) DB::selectOne('SELECT CONNECTION_ID() AS id')->id, $independent['connection_id']);
        $this->assertStringContainsString('INCIDENT_RESPONSE_BLOCKED', (string) $independent['refusal']);
        $last = end($independent['rows']);
        $this->assertSame($security, json_decode($last->event_payload_json, true));
        $this->assertSame('OBSERVATION_ONLY', $last->writer_kind);
        $this->assertNotEmpty($last->writer_actor_id);
        $this->assertGreaterThan(0, (int) $last->writer_fencing_token);
    }

    public function unsupportedSnapshotMarkers(): array
    {
        return ['future marker' => ['2026-03-25'], 'effective exact date' => ['2026-03-24']];
    }

    private function absentRunWorld(): array
    {
        $world = R0025SyntheticV2World::build(['profile' => 'market-data-row-hash/v1', 'run_clock' => '2026-03-23 18:00:00']);
        DB::table('md_market_calendar_revisions')->insert([
            'revision_uid' => hash('sha256', 'e049-phase-next-session-20260324'), 'market_code' => 'IDX', 'market_segment' => 'REGULAR',
            'cal_date' => '2026-03-24', 'timezone' => 'Asia/Jakarta', 'is_trading_day' => 1, 'is_half_day' => 0, 'session_state' => 'COMPLETED',
            'session_open_at' => '2026-03-24 09:00:00', 'session_close_at' => '2026-03-24 16:00:00', 'completed_at' => '2026-03-24 16:00:00',
            'recorded_at' => '2026-03-24 17:00:00', 'source_ref' => 'independent-phase-boundary-session', 'source_version' => 'test-only-v1',
            'provenance_tier' => 'VERIFIED', 'reconciled_at' => '2026-03-24 17:00:00', 'reconciliation_source_ref' => 'independent-phase-boundary-session',
        ]);
        Carbon::setTestNow('2026-03-24 18:00:00');
        return $world;
    }

    public function test_absent_expected_session_is_frontier_without_fabricated_worker_or_activation(): void
    {
        $world = $this->absentRunWorld();
        $before = $this->publicationState();
        $report = (new MarketDataSessionFreshnessService())->measure('2026-03-24');
        $this->assertSame('NOT_APPLICABLE', $report['evaluation']['assessment']);
        $this->assertSame('DEVELOPMENT', $report['activation_state']);
        $this->assertNull($report['run_id']); $this->assertNull($report['publication_id']);
        $this->assertFalse($report['gateway_readable']);
        $this->assertFalse($report['operational_activation_proven']);
        $this->assertSame('NOT_CLAIMED', $report['operational_slo_compliance']);
        $this->assertSame('2026-03-24', $report['latest_expected_trade_date']);
        $this->assertSame('2026-03-23', $report['latest_readable_trade_date']);
        $this->assertSame($before, $this->publicationState());
        $independent = $this->onIndependentOwnerConnection(function () {
            return ['connection_id' => (int) DB::selectOne('SELECT CONNECTION_ID() AS id')->id,
                'alerts' => DB::table('md_freshness_measurements')->where('trade_date', '2026-03-24')->where('record_type', 'ALERT_OPENED')->get()->all(),
                'incidents' => DB::table('md_freshness_measurements')->where('trade_date', '2026-03-24')->where('record_type', 'INCIDENT_RESPONSE')->count(),
                'assessments' => DB::table('md_freshness_measurements')->where('trade_date', '2026-03-24')->where('record_type', 'ASSESSMENT')->get()->all()];
        });
        $this->retainObservedWorld('absent_run_phase_no_marker', [(int) $world['run_id']], compact('report', 'independent', 'before'));
        $this->assertNotSame((int) DB::selectOne('SELECT CONNECTION_ID() AS id')->id, $independent['connection_id']);
        $this->assertSame(0, $independent['incidents']);
        $this->assertCount(0, $independent['alerts'], 'No pre-activation due-run incident is manufactured.');
        $assessment = $independent['assessments'][0];
        $this->assertSame($report['measurement_id'], (int) $assessment->measurement_id);
        $this->assertNotEmpty($assessment->writer_actor_id);
    }

    /**
     * This is a refusal proof, NOT the missing successful non-null snapshot workflow.
     * Original attempted success and its CONFIG_REGISTRY_TYPE_MISMATCH are retained as ERROR.
     * @dataProvider unsupportedSnapshotMarkers
     */
    public function test_locked_null_only_marker_refuses_a_new_snapshot_without_claiming_operational_phase_success(string $marker): void
    {
        $world = $this->absentRunWorld();
        $before = $this->publicationState();
        $snapshots = DB::table('md_config_snapshots')->get()->toJson();
        $measurements = DB::table('md_freshness_measurements')->get()->toJson();
        config(['market_data.scope.operational_start_date' => $marker]);
        $error = null;
        try { (new MarketDataSessionFreshnessService())->measure('2026-03-24'); }
        catch (RuntimeException $e) { $error = $e->getMessage(); }
        $independent = $this->onIndependentOwnerConnection(function () {
            return ['connection_id' => (int) DB::selectOne('SELECT CONNECTION_ID() AS id')->id,
                'publications' => $this->publicationState(), 'snapshots' => DB::table('md_config_snapshots')->get()->toJson(),
                'measurements' => DB::table('md_freshness_measurements')->get()->toJson()];
        });
        $this->retainObservedWorld('snapshot_marker_refusal_'.$marker, [(int) $world['run_id']], compact('marker', 'error', 'before', 'snapshots', 'measurements', 'independent'));
        $this->assertSame('CONFIG_REGISTRY_TYPE_MISMATCH: market_data.scope.operational_start_date expected=null actual=string', $error);
        $this->assertSame($before, $independent['publications']);
        $this->assertSame($snapshots, $independent['snapshots']);
        $this->assertSame($measurements, $independent['measurements']);
        $this->assertNotSame((int) DB::selectOne('SELECT CONNECTION_ID() AS id')->id, $independent['connection_id']);
    }
}
