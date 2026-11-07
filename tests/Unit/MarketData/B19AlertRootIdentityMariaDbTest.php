<?php

use App\Application\MarketData\Services\MarketDataFreshnessEvidenceService;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Tests\Support\R0025SyntheticV2World;

require_once __DIR__.'/B19OwnerFenceMariaDbTest.php';

/** Distinct roots for one run/date and gateway-bound recovery on committed MariaDB rows. */
class B19AlertRootIdentityMariaDbTest extends B19OwnerFenceMariaDbTest
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

    public function test_distinct_run_date_roots_retain_source_observations_and_recover_only_with_gateway(): void
    {
        $world = R0025SyntheticV2World::build(['profile' => 'market-data-row-hash/v1', 'run_clock' => '2026-03-23 18:00:00']);
        // Conditional evaluator exercise after the immutable development snapshot.
        // This marker is not an approval or proof of operational activation.
        config(['market_data.scope.operational_start_date' => '2026-03-23']);
        $runId = (int) $world['run_id'];
        $date = '2026-03-23';
        $original = (array) DB::table('eod_current_publication_pointer')->where('trade_date', $date)->first();
        $history = DB::table('eod_bars_history')->where('publication_id', $world['publication_id'])->get()->toJson();
        DB::table('eod_current_publication_pointer')->where('trade_date', $date)->delete();
        $service = new MarketDataFreshnessEvidenceService();
        Carbon::setTestNow('2026-03-23 18:00:03');
        $unreadable = $service->assessRun($runId);
        $this->assertFalse($unreadable['gateway_readable']);
        $this->assertNotEmpty($unreadable['source_observation_ids']);

        $fault = $original;
        $fault['publication_version'] = 999;
        DB::table('eod_current_publication_pointer')->insert($fault);
        Carbon::setTestNow('2026-03-23 18:01:00');
        $integrity = $service->assessRun($runId);
        $this->assertFalse($integrity['gateway_readable']);
        $this->assertNotEmpty($integrity['gateway_error']);
        Carbon::setTestNow('2026-03-23 18:02:00');
        $service->assessRun($runId);

        config(['database.connections.r0031_root_reader' => config('database.connections.b19_owner')]);
        $reader = DB::connection('r0031_root_reader');
        try {
            $this->assertNotSame((int) DB::selectOne('SELECT CONNECTION_ID() AS id')->id,
                (int) $reader->selectOne('SELECT CONNECTION_ID() AS id')->id);
            $events = $reader->table('eod_run_events')->where('run_id', $runId)
                ->whereIn('event_type', ['FRESHNESS_ALERT_OPENED', 'FRESHNESS_ALERT_RECOVERED', 'FRESHNESS_MEASURED'])
                ->orderBy('event_id')->get();
            $opens = $events->where('event_type', 'FRESHNESS_ALERT_OPENED')->values();
            $measures = $events->where('event_type', 'FRESHNESS_MEASURED')->values();
            $this->assertCount(2, $opens, 'One alert per distinct run/date/root despite repeated assessments.');
            $this->assertGreaterThanOrEqual(3, $measures->count());
            $this->assertCount(0, $events->where('event_type', 'FRESHNESS_ALERT_RECOVERED'));
            $keys = [];
            foreach ($opens as $row) {
                $payload = json_decode($row->event_payload_json, true);
                $this->assertSame($runId, (int) $row->run_id);
                $this->assertSame($date, $payload['requested_date']);
                $this->assertSame($unreadable['source_observation_ids'], $payload['source_observation_ids']);
                $keys[$payload['root_cause']] = $payload['dedup_key'];
            }
            $this->assertEqualsCanonicalizing(['GATEWAY_INTEGRITY', 'REQUESTED_DATE_UNREADABLE'], array_keys($keys));
            $this->assertNotSame($keys['GATEWAY_INTEGRITY'], $keys['REQUESTED_DATE_UNREADABLE']);
            $instants = [];
            foreach ($measures as $row) {
                $payload = json_decode($row->event_payload_json, true);
                $this->assertSame($date, $payload['requested_date']);
                $this->assertSame($unreadable['source_observation_ids'], $payload['source_observation_ids']);
                $instants[] = $payload['assessed_at'];
            }
            $this->assertGreaterThanOrEqual(3, count(array_unique($instants)), 'Every observation instant remains in append-only evidence.');
        } finally { DB::disconnect('r0031_root_reader'); }

        DB::table('eod_current_publication_pointer')->where('trade_date', $date)->update(['publication_version' => $original['publication_version']]);
        Carbon::setTestNow('2026-03-23 18:03:00');
        $readable = $service->assessRun($runId);
        $this->assertTrue($readable['gateway_readable']);
        $this->assertSame((int) $world['publication_id'], $readable['publication_id']);
        $recoveries = DB::table('eod_run_events')->where('run_id', $runId)
            ->where('event_type', 'FRESHNESS_ALERT_RECOVERED')->orderBy('event_id')->get();
        $this->assertCount(2, $recoveries);
        $recoveredKeys = [];
        foreach ($recoveries as $row) {
            $payload = json_decode($row->event_payload_json, true);
            $this->assertSame($date, $payload['requested_date']);
            $this->assertSame((int) $world['publication_id'], $payload['publication_id']);
            $recoveredKeys[] = $payload['dedup_key'];
        }
        $this->assertEqualsCanonicalizing(array_values($keys), $recoveredKeys);
        $service->assessRun($runId);
        $this->assertSame(2, DB::table('eod_run_events')->where('run_id', $runId)
            ->where('event_type', 'FRESHNESS_ALERT_RECOVERED')->count());
        $this->assertSame($history, DB::table('eod_bars_history')->where('publication_id', $world['publication_id'])->get()->toJson());
        $this->retainObservedWorld('r0031_distinct_root_alerts', [$runId], [
            'unreadable' => $unreadable, 'integrity' => $integrity, 'readable' => $readable,
            'alert_events' => DB::table('eod_run_events')->where('run_id', $runId)
                ->where('event_type', 'like', 'FRESHNESS_%')->orderBy('event_id')->get()->all(),
        ]);
    }
}
