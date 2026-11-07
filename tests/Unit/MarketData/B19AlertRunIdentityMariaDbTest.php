<?php

use App\Application\MarketData\Services\MarketDataFreshnessEvidenceService;
use App\Infrastructure\Persistence\MarketData\EodRunRepository;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Tests\Support\R0025SyntheticV2World;

require_once __DIR__.'/B19OwnerFenceMariaDbTest.php';

/** Two legitimate acquisition identities on one requested date and root. */
class B19AlertRunIdentityMariaDbTest extends B19OwnerFenceMariaDbTest
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

    public function test_separate_legitimate_runs_on_one_date_have_distinct_root_alert_keys(): void
    {
        $world = R0025SyntheticV2World::build(['profile' => 'market-data-row-hash/v1', 'run_clock' => '2026-03-23 18:00:00']);
        config(['market_data.scope.operational_start_date' => '2026-03-23']);
        $date = '2026-03-23';
        $runId = (int) $world['run_id'];
        $pointer = (array) DB::table('eod_current_publication_pointer')->where('trade_date', $date)->first();
        $history = DB::table('eod_bars_history')->where('publication_id', $world['publication_id'])->get()->toJson();
        DB::table('eod_current_publication_pointer')->where('trade_date', $date)->delete();
        $service = new MarketDataFreshnessEvidenceService();
        Carbon::setTestNow('2026-03-23 18:00:03');
        $first = $service->assessRun($runId);
        $this->assertFalse($first['gateway_readable']);
        $this->assertSame('SUCCESS', DB::table('eod_runs')->where('run_id', $runId)->value('terminal_status'));

        Carbon::setTestNow('2026-03-23 18:01:00');
        // A new run snapshot must obey the current LOCKED null-only registry.
        // Reapply the conditional marker only after legitimate acquisition.
        config(['market_data.scope.operational_start_date' => null]);
        $secondRun = (new EodRunRepository())->getOrCreateOwningRun($date, 'api', 'INGEST_BARS', null, 'import_only');
        $this->assertMatchesRegularExpression('/^[a-f0-9]{64}$/', (string) DB::table('eod_run_owner_fences')
            ->where('trade_date_requested', $date)->where('owner_run_id', $secondRun->run_id)
            ->whereNull('released_at')->value('credential_hash'), 'The second run is genuinely acquired, not cloned or fabricated.');
        config(['market_data.scope.operational_start_date' => '2026-03-23']);
        $this->assertNotSame($runId, (int) $secondRun->run_id);
        $second = $service->assessRun((int) $secondRun->run_id);
        $this->assertFalse($second['gateway_readable']);
        $this->assertSame($date, $second['requested_date']);

        config(['database.connections.r0031_run_reader' => config('database.connections.b19_owner')]);
        $reader = DB::connection('r0031_run_reader');
        try {
            $this->assertNotSame((int) DB::selectOne('SELECT CONNECTION_ID() AS id')->id,
                (int) $reader->selectOne('SELECT CONNECTION_ID() AS id')->id);
            $keys = [];
            foreach ([$runId, (int) $secondRun->run_id] as $id) {
                $rows = $reader->table('eod_run_events')->where('run_id', $id)
                    ->where('event_type', 'FRESHNESS_ALERT_OPENED')->get();
                $this->assertCount(1, $rows);
                $payload = json_decode($rows[0]->event_payload_json, true);
                $this->assertSame($date, $payload['requested_date']);
                $this->assertSame('REQUESTED_DATE_UNREADABLE', $payload['root_cause']);
                $keys[$id] = $payload['dedup_key'];
                $this->assertSame(0, $reader->table('eod_run_events')->where('run_id', $id)
                    ->where('event_type', 'FRESHNESS_ALERT_RECOVERED')->count());
            }
            $this->assertNotSame($keys[$runId], $keys[(int) $secondRun->run_id]);
        } finally { DB::disconnect('r0031_run_reader'); }

        $this->assertSame($history, DB::table('eod_bars_history')->where('publication_id', $world['publication_id'])->get()->toJson());
        $this->assertSame(0, DB::table('eod_current_publication_pointer')->where('trade_date', $date)->count());
        $this->retainObservedWorld('r0031_distinct_run_alerts', [$runId, (int) $secondRun->run_id], [
            'first' => $first, 'second' => $second, 'original_pointer' => $pointer,
        ]);
    }
}
