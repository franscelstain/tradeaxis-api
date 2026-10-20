<?php

use App\Application\MarketData\Services\MarketDataOperationalStatusService;
use App\Application\MarketData\Services\MarketDataEvidenceExportService;
use App\Infrastructure\Persistence\MarketData\EodRunRepository;
use App\Models\EodRun;
use App\Models\EodRunEvent;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Tests\Support\UsesMarketDataMariaDb;
use Tests\Support\R0025SyntheticV2World;

class B19OperationalObservabilityMariaDbTest extends TestCase
{
    use UsesMarketDataMariaDb;

    protected function setUp(): void { parent::setUp(); $this->bootMarketDataMariaDb(); }
    protected function tearDown(): void { Carbon::setTestNow(); $this->tearDownMarketDataMariaDb(); parent::tearDown(); }

    private function retain(string $name, $value): void
    {
        if ($dir = getenv('B19_OPERATIONAL_ARTIFACT_DIR')) {
            if (! is_dir($dir)) mkdir($dir, 0775, true);
            file_put_contents($dir.'/'.$name.'.json', json_encode($value, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
        }
    }

    public function test_real_run_status_events_counts_hash_seal_config_and_export_have_primary_provenance(): void
    {
        $w = R0025SyntheticV2World::build();
        $date = R0025SyntheticV2World::world()['trade_date'];
        $run = EodRun::findOrFail($w['run_id']);
        $service = new MarketDataOperationalStatusService();
        $status = $service->forDate($date, $w['run_id']);
        $this->assertSame('SUCCESS', $status['terminal_status']);
        $this->assertSame($date, $status['effective_date']);
        $this->assertTrue($status['operationally_healthy'], json_encode($status['observability_defects']));
        $this->assertSame('PASS', $status['coverage_gate_state']);
        $this->assertSame([], $status['failed_or_held_stages']);
        foreach (['bars_rows_written', 'indicators_rows_written', 'eligibility_rows_written'] as $field) $this->assertSame(1, (int) $status['counts'][$field], $field);
        // The frozen world has one valid bar and no history: its independent oracle retains ELIG_INSUFFICIENT_HISTORY.
        foreach (['invalid_bar_count' => 0, 'invalid_indicator_count' => 1, 'hard_reject_count' => 1] as $field => $count) $this->assertSame($count, (int) $status['counts'][$field], $field);
        $this->assertNull($status['counts']['warning_count']);
        $this->assertStringContainsString('D-MD-B19-A001-003', $status['warning_count_provenance']);
        $oracle = json_decode(file_get_contents(R0025SyntheticV2World::packagePath().'/expected/expected_publication_manifest.json'), true);
        foreach (['bars_batch_hash' => 'bars', 'indicators_batch_hash' => 'indicators', 'eligibility_batch_hash' => 'eligibility'] as $field => $key) $this->assertSame($oracle['payload']['artifacts'][$key], $status['hashes'][$field]);
        $this->assertNotEmpty($status['sealed_at']);
        $this->assertTrue($status['config_verified']);
        $this->assertSame((int) $run->config_snapshot_id, (int) $status['config_snapshot_id']);
        $this->assertGreaterThan(7, $status['event_count']);

        $before = DB::table('eod_run_events')->where('run_id', $run->run_id)->orderBy('event_id')->get()->toJson();
        $event = (new EodRunRepository())->appendEvent($run, 'FINALIZE', 'DIAGNOSTIC', 'WARNING',
            'Contradictory supporting message: terminal outcome FAILED', 'RUN_SOURCE_TIMEOUT', ['test_context' => 'supporting_only']);
        $diagnosis = $service->forDate($date, $w['run_id']);
        $this->assertSame('SUCCESS', $diagnosis['terminal_status'], 'A log message cannot overwrite persisted terminal authority.');
        $this->assertContains('RUN_SOURCE_TIMEOUT', $diagnosis['reason_codes']);
        $this->assertSame('WARN', EodRunEvent::findOrFail($event->event_id)->severity);
        $this->assertNull(DB::table('eod_runs')->where('run_id', $run->run_id)->value('warning_count'));
        $this->assertSame($before, DB::table('eod_run_events')->where('run_id', $run->run_id)->where('event_id', '<', $event->event_id)->orderBy('event_id')->get()->toJson());

        $dir = sys_get_temp_dir().'/b19_e029_observability_'.bin2hex(random_bytes(6));
        try {
            app(MarketDataEvidenceExportService::class)->exportRunEvidence($run->run_id, $dir);
            $pack = json_decode(file_get_contents($dir.'/evidence_pack.json'), true);
            $this->assertSame('SUCCESS', $pack['run_summary']['terminal_status']);
            $this->assertSame($date, $pack['run_summary']['trade_date_effective']);
            $this->assertSame($diagnosis['event_count'], $pack['run_event_summary']['event_count']);
            $this->assertSame(1, $pack['run_event_summary']['reason_code_counts']['RUN_SOURCE_TIMEOUT']);
            $this->assertSame('SEALED', $pack['publication_manifest']['seal_state']);
            $this->retain('observability_export', $pack);
        } finally { foreach (glob($dir.'/*') ?: [] as $p) if (is_file($p)) unlink($p); if (is_dir($dir)) rmdir($dir); }
        $this->retain('observability_primary', ['run' => $run, 'events' => $diagnosis['events'], 'status' => $diagnosis]);
    }

    public function test_missing_contradictory_and_unavailable_primary_facts_never_report_operational_health(): void
    {
        $w = R0025SyntheticV2World::build();
        $date = R0025SyntheticV2World::world()['trade_date'];
        $service = new MarketDataOperationalStatusService();
        $run = (array) DB::table('eod_runs')->where('run_id', $w['run_id'])->first();
        $cases = [
            'terminal_status' => null, 'trade_date_effective' => null, 'bars_batch_hash' => null,
            'indicators_batch_hash' => 'contradictory', 'eligibility_batch_hash' => null,
            'sealed_at' => null, 'config_hash' => str_repeat('0', 64), 'config_snapshot_id' => null,
            'bars_rows_written' => null, 'indicators_rows_written' => null, 'eligibility_rows_written' => null,
            'invalid_bar_count' => null, 'invalid_indicator_count' => null, 'hard_reject_count' => null,
        ];
        $results = [];
        foreach ($cases as $field => $bad) {
            DB::table('eod_runs')->where('run_id', $w['run_id'])->update([$field => $bad]);
            try {
                $result = $service->forDate($date, $w['run_id']);
                $this->assertFalse((bool) $result['operationally_healthy'], $field);
                $this->assertNotEmpty($result['observability_defects'], $field);
                $results[$field] = $result['observability_defects'];
            } finally { DB::table('eod_runs')->where('run_id', $w['run_id'])->update([$field => $run[$field]]); }
        }
        unset($run['run_id']);
        $id = DB::table('eod_runs')->insertGetId($run);
        $result = $service->forDate($date, $id);
        $this->assertFalse((bool) $result['operationally_healthy']);
        $this->assertContains('RUN_EVENT_TRAIL_MISSING', $result['observability_defects']);
        $this->assertSame(0, $result['event_count']);
        $this->retain('observability_negative', $results);
    }

    public function test_event_model_and_actual_mariadb_reject_update_and_delete_but_accept_append(): void
    {
        $w = R0025SyntheticV2World::build();
        $event = EodRunEvent::where('run_id', $w['run_id'])->firstOrFail();
        $original = $event->toArray();
        foreach (['model_update', 'model_delete', 'sql_update', 'sql_delete'] as $operation) {
            try {
                if ($operation === 'model_update') { $event->message = 'rewrite'; $event->save(); }
                elseif ($operation === 'model_delete') $event->delete();
                elseif ($operation === 'sql_update') DB::table('eod_run_events')->where('event_id', $event->event_id)->update(['message' => 'rewrite']);
                else DB::table('eod_run_events')->where('event_id', $event->event_id)->delete();
                $this->fail('Event trail changed: '.$operation);
            } catch (\RuntimeException $e) { $this->assertStringContainsString('RUN_EVENT_APPEND_ONLY', $e->getMessage()); }
            $this->assertSame($original, EodRunEvent::findOrFail($event->event_id)->toArray());
        }
        $count = DB::table('eod_run_events')->where('run_id', $w['run_id'])->count();
        (new EodRunRepository())->appendEvent(EodRun::findOrFail($w['run_id']), 'FINALIZE', 'DIAGNOSTIC', 'INFO', 'Append retained');
        $this->assertSame($count + 1, DB::table('eod_run_events')->where('run_id', $w['run_id'])->count());
    }
}
