<?php

use App\Application\MarketData\Services\MarketDataSessionFreshnessService;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Tests\Support\R0025SyntheticV2World;

require_once __DIR__.'/B19OwnerFenceMariaDbTest.php';

/** Persisted cross-date alert evidence; the injected pointer fault is test-only. */
class B19AlertCrossDateIdentityMariaDbTest extends B19OwnerFenceMariaDbTest
{
    use \Tests\Support\RetainsB19ObservedEvidence;

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    public function test_two_affected_dates_retain_distinct_assessments_and_alerts_without_cross_date_recovery(): void
    {
        config(['market_data.scope.operational_start_date' => null]);
        $world = R0025SyntheticV2World::build(['profile' => 'market-data-row-hash/v1', 'run_clock' => '2026-03-23 18:00:00']);
        $original = (array) DB::table('eod_current_publication_pointer')->where('trade_date', '2026-03-23')->first();
        $history = DB::table('eod_bars_history')->where('publication_id', $world['publication_id'])->get()->toJson();
        foreach (['2026-03-24', '2026-03-25'] as $date) {
            DB::table('md_market_calendar_revisions')->insert([
                'revision_uid' => hash('sha256', 'r0031-independent-session-'.$date),
                'market_code' => 'IDX', 'market_segment' => 'REGULAR', 'cal_date' => $date,
                'timezone' => 'Asia/Jakarta', 'is_trading_day' => 1, 'is_half_day' => 0,
                'session_state' => 'COMPLETED', 'session_open_at' => $date.' 09:00:00',
                'session_close_at' => $date.' 16:00:00', 'completed_at' => $date.' 16:00:00',
                'recorded_at' => $date.' 17:00:00', 'source_ref' => 'r0031-independent-calendar-'.$date,
                'source_version' => 'r0031-test-v1', 'provenance_tier' => 'VERIFIED',
                'reconciled_at' => $date.' 17:00:00',
                'reconciliation_source_ref' => 'r0031-independent-calendar-'.$date,
            ]);
        }

        $service = new MarketDataSessionFreshnessService();
        $from = '2026-03-23';
        $observed = [];
        foreach (['2026-03-24', '2026-03-25'] as $date) {
            $fault = $original;
            $fault['trade_date'] = $date;
            $fault['updated_at'] = $date.' 18:00:00';
            DB::table('eod_current_publication_pointer')->where('trade_date', $from)->delete();
            DB::table('eod_current_publication_pointer')->insert($fault);
            $from = $date;
            Carbon::setTestNow($date.' 18:00:00');
            $first = $service->measure($date);
            Carbon::setTestNow($date.' 18:01:00');
            $second = $service->measure($date);
            $this->assertNull($first['run_id']);
            $this->assertFalse($first['gateway_readable']);
            $this->assertNotEmpty($first['gateway_error']);
            $this->assertNotSame($first['measurement_id'], $second['measurement_id']);
            $observed[$date] = [$first['measurement_id'], $second['measurement_id']];
        }

        config(['database.connections.r0031_reader' => config('database.connections.b19_owner')]);
        $reader = DB::connection('r0031_reader');
        try {
            $this->assertNotSame((int) DB::selectOne('SELECT CONNECTION_ID() AS id')->id,
                (int) $reader->selectOne('SELECT CONNECTION_ID() AS id')->id);
            $keys = [];
            foreach ($observed as $date => $ids) {
                $rows = $reader->table('md_freshness_measurements')->where('trade_date', $date)
                    ->orderBy('measurement_id')->get();
                $assessments = $rows->where('record_type', 'ASSESSMENT')->values();
                $opened = $rows->where('record_type', 'ALERT_OPENED')->values();
                $this->assertCount(2, $assessments, 'Each observed instant is retained for '.$date);
                $this->assertSame($ids, $assessments->pluck('measurement_id')->map(static fn ($id) => (int) $id)->all());
                $this->assertCount(1, $opened, 'The same date/root has one open alert.');
                $this->assertSame(0, $rows->where('record_type', 'ALERT_RECOVERED')->count());
                foreach ($assessments as $row) {
                    $payload = json_decode($row->measurement_json, true);
                    $this->assertSame($date, $payload['requested_date']);
                    $this->assertSame([], $payload['source_observation_ids']);
                    $this->assertSame(hash('sha256', $row->measurement_json), $row->content_sha256);
                    $this->assertSame('EXPECTED_SESSION_WITHOUT_RUN', $payload['measurement_scope']);
                }
                $alert = json_decode($opened[0]->measurement_json, true);
                $this->assertSame($date, $alert['requested_date']);
                $this->assertNull($alert['run_id']);
                $this->assertSame('GATEWAY_INTEGRITY', $alert['root_cause']);
                $keys[$date] = $alert['dedup_key'];
            }
            $this->assertNotSame($keys['2026-03-24'], $keys['2026-03-25']);
        } finally { DB::disconnect('r0031_reader'); }

        DB::table('eod_current_publication_pointer')->where('trade_date', '2026-03-25')->delete();
        DB::table('eod_current_publication_pointer')->insert($original);
        Carbon::setTestNow('2026-03-25 18:02:00');
        $this->assertTrue((new \App\Infrastructure\Persistence\MarketData\EodPublicationRepository())
            ->resolveCurrentReadablePublicationForTradeDate('2026-03-23') !== null);
        $this->assertFalse($service->measure('2026-03-24')['gateway_readable']);
        $this->assertFalse($service->measure('2026-03-25')['gateway_readable']);
        $this->assertSame(0, DB::table('md_freshness_measurements')->where('record_type', 'ALERT_RECOVERED')->count(),
            'A prior date becoming readable cannot recover either affected requested date.');
        $this->assertSame($history, DB::table('eod_bars_history')->where('publication_id', $world['publication_id'])->get()->toJson());
        $this->retainObservedWorld('r0031_cross_date_alerts', [$world['run_id']], [
            'observed_assessment_ids' => $observed,
            'measurements' => DB::table('md_freshness_measurements')->orderBy('measurement_id')->get()->all(),
        ]);
    }
}
