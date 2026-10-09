<?php

use App\Application\MarketData\Services\MarketDataEvidenceExportService;
use Illuminate\Support\Facades\DB;
use Tests\Support\R0025SyntheticV2World;
use Tests\Support\UsesMarketDataMariaDb;

/**
 * `MD-B19` — `run_event_summary.json` of a REAL run, read against the database (`MD-S075-R0114..R0131`).
 *
 * `B19RunEventSummaryTrailDerivationTest` proves the derivation on a seeded trail. This guard proves the other
 * half on a trail the real pipeline wrote: the FILE the exporter writes equals an independent derivation from the
 * rows of `eod_run_events`, carries the run's own identity and requested date (read from `eod_runs`, not from the
 * events), and the trail itself is still there, row for row, after the export.
 *
 * The world is the one the R0025 candidate fixture is built on. It does not mock a repository.
 */
class B19RunEventSummaryRealRunProvenanceTest extends TestCase
{
    use UsesMarketDataMariaDb;

    protected function setUp(): void
    {
        parent::setUp();
        $this->bootMarketDataMariaDb();
    }

    protected function tearDown(): void
    {
        $this->tearDownMarketDataMariaDb();
        parent::tearDown();
    }

    /** @return array{0:string,1:array<string,mixed>,2:array<string,mixed>,3:array<int,array<string,mixed>>,4:array<int,array<string,mixed>>} raw json, file, run, trail before, trail after */
    private function exportRealRun(): array
    {
        $w = R0025SyntheticV2World::build();
        $before = $this->trail($w['run_id']);
        $dir = sys_get_temp_dir().'/md_b19_res_real_'.uniqid('', true);
        app(MarketDataEvidenceExportService::class)->exportRunEvidence($w['run_id'], $dir);
        $this->assertFileExists($dir.'/run_event_summary.json');
        $raw = (string) file_get_contents($dir.'/run_event_summary.json');
        $run = (array) DB::table('eod_runs')->where('run_id', $w['run_id'])->first();
        $this->assertNotSame([], $run, 'the world produced no run');
        $this->assertNotSame([], $before, 'precondition: the real pipeline wrote an event trail');

        return [$raw, json_decode($raw, true), $run, $before, $this->trail($w['run_id'])];
    }

    /** @return array<int,array<string,mixed>> */
    private function trail(int $runId): array
    {
        return DB::table('eod_run_events')->where('run_id', $runId)->orderBy('event_id')->get()->map(fn ($r) => (array) $r)->all();
    }

    /** @return array<string,mixed> */
    private function derive(array $trail): array
    {
        $rows = $trail;
        usort($rows, fn ($a, $b) => [(string) $a['event_time'], (int) $a['event_id']] <=> [(string) $b['event_time'], (int) $b['event_id']]);
        $stages = array_count_values(array_column($rows, 'stage'));
        $reasons = array_count_values(array_filter(array_column($rows, 'reason_code'), fn ($v) => $v !== null && $v !== ''));
        ksort($stages);
        ksort($reasons);
        $order = ['INFO' => 1, 'WARN' => 2, 'ERROR' => 3];
        $top = null;
        foreach ($rows as $r) {
            if ($top === null || $order[$r['severity']] > $order[$top]) {
                $top = $r['severity'];
            }
        }

        return [
            'event_count' => count($rows),
            'first_event_time' => (string) $rows[0]['event_time'],
            'last_event_time' => (string) $rows[count($rows) - 1]['event_time'],
            'first_event_type' => $rows[0]['event_type'],
            'last_event_type' => $rows[count($rows) - 1]['event_type'],
            'highest_severity' => $top,
            'stage_counts' => $stages,
            'reason_code_counts' => $reasons,
        ];
    }

    /** `R0114`, `R0115`: the run's own identity and requested date, read from the run row. */
    public function test_the_file_names_the_run_and_its_requested_date(): void
    {
        [, $file, $run, $trail] = $this->exportRealRun();
        $this->assertSame((int) $run['run_id'], $file['run_id']);
        $this->assertSame((string) $run['trade_date_requested'], $file['trade_date_requested']);
        $this->assertSame([(int) $run['run_id']], array_values(array_unique(array_column($trail, 'run_id'))));
    }

    /** `R0115`: when the run was served on another effective date, the file still carries the REQUESTED date. */
    public function test_the_requested_date_is_not_the_effective_date(): void
    {
        $w = R0025SyntheticV2World::build();
        DB::table('eod_runs')->where('run_id', $w['run_id'])->update(['trade_date_effective' => '2026-03-20']);
        $dir = sys_get_temp_dir().'/md_b19_res_eff_'.uniqid('', true);
        app(MarketDataEvidenceExportService::class)->exportRunEvidence($w['run_id'], $dir);
        $file = json_decode((string) file_get_contents($dir.'/run_event_summary.json'), true);
        $requested = (string) DB::table('eod_runs')->where('run_id', $w['run_id'])->value('trade_date_requested');
        $this->assertNotSame('2026-03-20', $requested, 'precondition');
        $this->assertSame($requested, $file['trade_date_requested']);
    }

    /** @return array{0:string,1:int} raw file and run id of a copy of the world's run with the given number of reason-free INFO events */
    private function exportCloneWithEvents(int $events, array $severities = []): array
    {
        $w = R0025SyntheticV2World::build();
        $row = (array) DB::table('eod_runs')->where('run_id', $w['run_id'])->first();
        $newId = (int) $w['run_id'] + 1000000;
        $row['run_id'] = $newId;
        $row['publication_id'] = null;
        $row['terminal_status'] = 'FAILED';
        $row['publishability_state'] = 'NOT_READABLE';
        DB::table('eod_runs')->insert($row);
        for ($i = 0; $i < $events; $i++) {
            DB::table('eod_run_events')->insert([
                'run_id' => $newId, 'trade_date_requested' => $row['trade_date_requested'], 'event_time' => sprintf('2026-03-25 11:%02d:00', $i),
                'stage' => 'INGEST', 'event_type' => 'T'.$i, 'severity' => $severities[$i] ?? 'INFO', 'reason_code' => null, 'message' => null,
                'event_payload_json' => null, 'created_at' => '2026-03-25 11:00:00',
            ]);
        }
        $dir = sys_get_temp_dir().'/md_b19_res_clone_'.uniqid('', true);
        app(MarketDataEvidenceExportService::class)->exportRunEvidence($newId, $dir);

        return [(string) file_get_contents($dir.'/run_event_summary.json'), $newId];
    }

    /** `R0127`: a run whose events carry no reason code writes `reason_code_counts` as `{}` (the locked example), not `[]`. */
    public function test_a_trail_without_reason_codes_writes_an_empty_json_object(): void
    {
        [$raw, $id] = $this->exportCloneWithEvents(3);
        $d = json_decode($raw);
        $this->assertSame($id, $d->run_id);
        $this->assertSame(3, $d->event_count);
        $this->assertTrue(is_object($d->reason_code_counts), 'reason_code_counts must serialize as {} when empty');
        $this->assertSame(3, $d->stage_counts->INGEST);
        $this->assertSame('INFO', $d->highest_severity, 'three recorded INFO events: INFO is an observation here');
    }

    /** `R0121`, `R0130`: an ERROR recorded between an INFO and a WARN is the severity the written file reports. */
    public function test_a_mixed_trail_writes_its_highest_recorded_severity(): void
    {
        [$raw] = $this->exportCloneWithEvents(3, ['INFO', 'ERROR', 'WARN']);
        $this->assertSame('ERROR', json_decode($raw)->highest_severity);
    }

    /** `R0122`, `R0127`, `R0128`: a run with no events at all writes an honest, empty summary: zero count, null times and types, `{}` maps. */
    public function test_a_run_with_no_events_writes_no_invented_history(): void
    {
        [$raw] = $this->exportCloneWithEvents(0);
        $d = json_decode($raw);
        $this->assertSame(0, $d->event_count);
        $this->assertNull($d->first_event_time);
        $this->assertNull($d->last_event_time);
        $this->assertNull($d->first_event_type);
        $this->assertNull($d->last_event_type);
        $this->assertTrue(property_exists($d, 'highest_severity'));
        $this->assertNull($d->highest_severity, 'zero events: JSON null in the written artifact, not INFO');
        $this->assertMatchesRegularExpression('/"highest_severity":\s*null/', $raw, 'the generated file itself carries a JSON null');
        $this->assertTrue(is_object($d->stage_counts));
        $this->assertTrue(is_object($d->reason_code_counts));
    }

    /** `R0116..R0127`: every derived field equals the independent derivation from this run's raw trail. */
    public function test_the_file_equals_the_independent_derivation_from_the_raw_trail(): void
    {
        [, $file, , $trail] = $this->exportRealRun();
        $expected = $this->derive($trail);
        foreach ($expected as $field => $value) {
            $this->assertArrayHasKey($field, $file, $field.' is absent');
            $this->assertEquals($value, $file[$field], $field);
        }
        $this->assertSame($file['event_count'], array_sum($file['stage_counts']));
    }

    /** `R0130`: an ERROR anywhere in the trail is never presented lower than ERROR. */
    public function test_an_error_in_the_real_trail_is_not_lowered(): void
    {
        [, $file, , $trail] = $this->exportRealRun();
        $hasError = in_array('ERROR', array_column($trail, 'severity'), true);
        if ($hasError) {
            $this->assertSame('ERROR', $file['highest_severity']);
        } else {
            $this->assertNotSame('ERROR', $file['highest_severity']);
        }
    }

    /** `R0127`, JSON shape: the counts are JSON objects keyed by name — an empty one is `{}`, as in the locked example, never `[]`. */
    public function test_the_two_count_maps_are_json_objects_even_when_empty(): void
    {
        [$raw] = $this->exportRealRun();
        $decoded = json_decode($raw);
        $this->assertTrue(is_object($decoded->stage_counts), 'stage_counts must be a JSON object');
        $this->assertTrue(is_object($decoded->reason_code_counts), 'reason_code_counts must be a JSON object (the locked example is {})');
    }

    /** `R0128`, `R0131`: the trail is untouched by the export and the summary is shorter than the trail's detail. */
    public function test_the_export_leaves_the_event_trail_row_for_row_intact(): void
    {
        [, $file, , $before, $after] = $this->exportRealRun();
        $this->assertSame($before, $after, 'exporting the summary must not alter, add or remove an event');
        $this->assertArrayNotHasKey('events', $file);
        $this->assertArrayNotHasKey('message', $file);
        $this->assertSame(count($before), $file['event_count']);
    }
}
