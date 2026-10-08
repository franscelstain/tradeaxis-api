<?php

use App\Infrastructure\Persistence\MarketData\EodEvidenceRepository;
use Illuminate\Support\Facades\DB;
use Tests\Support\UsesMarketDataSqlite;

/**
 * `MD-B19` — the derivation of `run_event_summary.json` from the append-only `eod_run_events` trail
 * (`MD-S075-R0116..R0131`, section 3 of Run_Artifacts_Format_LOCKED.md).
 *
 * The summary is a DERIVATIVE of the trail. Every expectation here is computed by `expected()` below from the raw
 * rows with a different algorithm from the repository's (sort in PHP by time then id, tally with array_count_values),
 * on a trail that is built to defeat the usual shortcuts:
 *
 *  - event ids are NOT in time order, and two events share a timestamp, so first/last depend on the (time, id) tie
 *    break and not on insertion order;
 *  - the target run's severities are mixed and its ERROR is neither first nor last;
 *  - two other runs (one on the same trade date, one on another) carry their own events with other stages, other
 *    reason codes and an ERROR, so any read that is not isolated by run id changes a number;
 *  - one event has no reason code, which must not be counted as a reason.
 */
class B19RunEventSummaryTrailDerivationTest extends TestCase
{
    use UsesMarketDataSqlite;

    private const RUN = 9001;
    private const DATE = '2026-04-21';

    protected function setUp(): void
    {
        parent::setUp();
        $this->bootMarketDataSqlite();
    }

    protected function tearDown(): void
    {
        $this->tearDownMarketDataSqlite();
        parent::tearDown();
    }

    private function event(int $id, int $run, string $date, string $time, string $stage, string $type, string $severity, ?string $reason): void
    {
        DB::table('eod_run_events')->insert([
            'event_id' => $id, 'run_id' => $run, 'trade_date_requested' => $date, 'event_time' => $time,
            'stage' => $stage, 'event_type' => $type, 'severity' => $severity, 'reason_code' => $reason,
            'message' => null, 'event_payload_json' => null, 'created_at' => $time,
        ]);
    }

    /** Target trail: inserted out of time order; the earliest event (RUN_CREATED) has a HIGHER id than the events after it, and ids 17/18 tie at 17:35:00. */
    private function seedTrail(): void
    {
        $d = self::DATE;
        $r = self::RUN;
        $this->event(14, $r, $d, '2026-04-21 17:33:00', 'CANONICALIZE', 'CANON_DONE', 'INFO', null);
        $this->event(15, $r, $d, '2026-04-21 17:31:00', 'INGEST', 'RUN_CREATED', 'INFO', null);
        $this->event(16, $r, $d, '2026-04-21 17:34:00', 'INDICATORS', 'INDICATOR_WARN', 'WARN', 'COVERAGE_LOW');
        $this->event(18, $r, $d, '2026-04-21 17:35:00', 'FINALIZE', 'FINAL_STATUS_COMMITTED', 'INFO', 'COVERAGE_LOW');
        $this->event(12, $r, $d, '2026-04-21 17:32:00', 'INGEST', 'SOURCE_FETCHED', 'INFO', null);
        $this->event(17, $r, $d, '2026-04-21 17:35:00', 'INDICATORS', 'INDICATOR_ERROR', 'ERROR', 'INDICATOR_FAIL');
        $this->event(13, $r, $d, '2026-04-21 17:32:30', 'INGEST', 'ROWS_PARSED', 'INFO', null);
        // other run, same date: other stages, other reasons, an ERROR, and times that would win first/last
        $this->event(21, 9002, $d, '2026-04-21 09:00:00', 'PUBLISH', 'OTHER_FIRST', 'ERROR', 'OTHER_REASON');
        $this->event(22, 9002, $d, '2026-04-21 23:59:00', 'PUBLISH', 'OTHER_LAST', 'ERROR', 'OTHER_REASON');
        // other run, other date
        $this->event(31, 9003, '2026-04-22', '2026-04-22 08:00:00', 'INGEST', 'ELSEWHERE', 'ERROR', 'COVERAGE_LOW');
    }

    /** Independent derivation from the raw rows of one run. */
    private function expected(int $run): array
    {
        $rows = array_map(fn ($r) => (array) $r, DB::table('eod_run_events')->where('run_id', $run)->get()->all());
        usort($rows, fn ($a, $b) => [$a['event_time'], $a['event_id']] <=> [$b['event_time'], $b['event_id']]);
        $stages = $rows ? array_count_values(array_column($rows, 'stage')) : [];
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
            'first_event_time' => $rows ? $rows[0]['event_time'] : null,
            'last_event_time' => $rows ? $rows[count($rows) - 1]['event_time'] : null,
            'first_event_type' => $rows ? $rows[0]['event_type'] : null,
            'last_event_type' => $rows ? $rows[count($rows) - 1]['event_type'] : null,
            'highest_severity' => $top,
            'stage_counts' => $stages,
            'reason_code_counts' => $reasons,
        ];
    }

    private function summary(int $run = self::RUN): array
    {
        return (new EodEvidenceRepository())->summarizeRunEvents($run);
    }

    /** `R0116`: the count of this run's events and no other run's. */
    public function test_event_count_is_the_number_of_this_runs_events_only(): void
    {
        $this->seedTrail();
        $this->assertSame(7, $this->summary()['event_count']);
        $this->assertSame(2, $this->summary(9002)['event_count']);
        $this->assertSame(1, $this->summary(9003)['event_count']);
        $this->assertSame($this->expected(self::RUN)['event_count'], $this->summary()['event_count']);
    }

    /** `R0117`, `R0119`: the first event is the earliest by time, ties broken by the lowest event id. */
    public function test_first_event_is_the_earliest_by_time_and_not_the_first_inserted_or_lowest_id(): void
    {
        $this->seedTrail();
        $s = $this->summary();
        $this->assertSame('2026-04-21 17:31:00', $s['first_event_time']);
        $this->assertSame('RUN_CREATED', $s['first_event_type']);
        $this->assertSame($this->expected(self::RUN)['first_event_time'], $s['first_event_time']);
        $this->assertSame($this->expected(self::RUN)['first_event_type'], $s['first_event_type']);
    }

    /** `R0118`, `R0120`: the last event is the latest by time and, for the shared timestamp, the highest event id. */
    public function test_last_event_is_the_latest_by_time_and_the_higher_id_wins_a_tie(): void
    {
        $this->seedTrail();
        $s = $this->summary();
        $this->assertSame('2026-04-21 17:35:00', $s['last_event_time']);
        $this->assertSame('FINAL_STATUS_COMMITTED', $s['last_event_type'], 'two events share 17:35:00; the higher event_id is the last');
        $this->assertSame($this->expected(self::RUN)['last_event_type'], $s['last_event_type']);
    }

    /** `R0117..R0120`: a tie at the FIRST position is broken the same way (lowest id first). */
    public function test_a_tie_for_first_place_is_broken_by_the_lower_event_id(): void
    {
        $this->event(52, 9100, self::DATE, '2026-04-21 17:00:00', 'INGEST', 'SECOND_BY_ID', 'INFO', null);
        $this->event(51, 9100, self::DATE, '2026-04-21 17:00:00', 'INGEST', 'FIRST_BY_ID', 'INFO', null);
        $this->event(53, 9100, self::DATE, '2026-04-21 17:05:00', 'FINALIZE', 'END', 'INFO', null);
        $s = $this->summary(9100);
        $this->assertSame('FIRST_BY_ID', $s['first_event_type']);
        $this->assertSame('END', $s['last_event_type']);
        $this->assertSame('2026-04-21 17:00:00', $s['first_event_time']);
    }

    /** `R0121`, `R0130`: the highest severity is the maximum of the trail, ERROR in the middle of it counts. */
    public function test_highest_severity_is_the_maximum_of_the_trail_and_error_is_never_lowered(): void
    {
        $this->seedTrail();
        $this->assertSame('ERROR', $this->summary()['highest_severity'], 'the ERROR is neither first nor last, and the last event is INFO');
        $this->assertSame($this->expected(self::RUN)['highest_severity'], $this->summary()['highest_severity']);
    }

    /** @return array<string,array{0:array<int,string>,1:string}> */
    public static function severityTrails(): array
    {
        return [
            'info only' => [['INFO', 'INFO', 'INFO'], 'INFO'],
            'warn after info' => [['INFO', 'WARN', 'INFO'], 'WARN'],
            'warn first, info after' => [['WARN', 'INFO', 'INFO'], 'WARN'],
            'error first' => [['ERROR', 'INFO', 'WARN'], 'ERROR'],
            'error last' => [['INFO', 'WARN', 'ERROR'], 'ERROR'],
            'error between warns' => [['WARN', 'ERROR', 'WARN'], 'ERROR'],
            'single error' => [['ERROR'], 'ERROR'],
            'single warn' => [['WARN'], 'WARN'],
        ];
    }

    /**
     * @dataProvider severityTrails
     *
     * @param array<int,string> $severities in time order
     */
    public function test_highest_severity_over_mixed_trails(array $severities, string $expected): void
    {
        foreach ($severities as $i => $severity) {
            $this->event(60 + $i, 9200, self::DATE, sprintf('2026-04-21 18:%02d:00', $i), 'INGEST', 'E'.$i, $severity, null);
        }
        $this->assertSame($expected, $this->summary(9200)['highest_severity']);
    }

    /** `R0122..R0126`: stage counts are this run's tally per stage, keyed by the stage name, and add up to the count. */
    public function test_stage_counts_tally_this_runs_events_per_stage(): void
    {
        $this->seedTrail();
        $s = $this->summary();
        $this->assertSame(['CANONICALIZE' => 1, 'FINALIZE' => 1, 'INDICATORS' => 2, 'INGEST' => 3], $s['stage_counts']);
        $this->assertSame($this->expected(self::RUN)['stage_counts'], $s['stage_counts']);
        $this->assertSame($s['event_count'], array_sum($s['stage_counts']), 'every event is in exactly one stage');
        $this->assertArrayNotHasKey('PUBLISH', $s['stage_counts'], 'a stage of another run must not appear');
    }

    /** `R0123..R0126`: each named stage of the example is its own key with its own count. */
    public function test_each_stage_of_the_example_is_counted_under_its_own_name(): void
    {
        $this->seedTrail();
        $s = $this->summary()['stage_counts'];
        $this->assertSame(3, $s['INGEST']);
        $this->assertSame(1, $s['CANONICALIZE']);
        $this->assertSame(2, $s['INDICATORS']);
        $this->assertSame(1, $s['FINALIZE']);
    }

    /** `R0127`: reason codes are counted per code over this run's events; events without a reason are not a reason. */
    public function test_reason_code_counts_tally_this_runs_reason_codes_only(): void
    {
        $this->seedTrail();
        $s = $this->summary();
        $this->assertSame(['COVERAGE_LOW' => 2, 'INDICATOR_FAIL' => 1], $s['reason_code_counts']);
        $this->assertSame($this->expected(self::RUN)['reason_code_counts'], $s['reason_code_counts']);
        $this->assertSame(3, array_sum($s['reason_code_counts']), 'three of the seven events carry a reason code; four do not');
        $this->assertArrayNotHasKey('OTHER_REASON', $s['reason_code_counts']);
    }

    /** `R0127`: a trail with no reason codes reports none and does not invent one. */
    public function test_a_trail_without_reason_codes_has_no_reason_code_counts(): void
    {
        $this->event(70, 9300, self::DATE, '2026-04-21 19:00:00', 'INGEST', 'A', 'INFO', null);
        $this->event(71, 9300, self::DATE, '2026-04-21 19:01:00', 'FINALIZE', 'B', 'INFO', null);
        $this->assertSame([], $this->summary(9300)['reason_code_counts']);
    }

    /** `R0128`: an absent trail yields an absent history: nothing is invented. */
    public function test_an_empty_trail_invents_no_events_times_types_or_counts(): void
    {
        $this->seedTrail();
        $s = $this->summary(424242);
        $this->assertSame(0, $s['event_count']);
        $this->assertNull($s['first_event_time']);
        $this->assertNull($s['last_event_time']);
        $this->assertNull($s['first_event_type']);
        $this->assertNull($s['last_event_type']);
        $this->assertSame([], $s['stage_counts']);
        $this->assertSame([], $s['reason_code_counts']);
    }

    /** `R0128`, `R0131`: the summary is derived, not stored: it follows the trail when the trail grows, and it does not change the trail. */
    public function test_the_summary_is_re_derived_from_the_trail_and_leaves_the_trail_untouched(): void
    {
        $this->seedTrail();
        $before = DB::table('eod_run_events')->orderBy('event_id')->get()->map(fn ($r) => (array) $r)->all();
        $first = $this->summary();
        $this->assertSame($before, DB::table('eod_run_events')->orderBy('event_id')->get()->map(fn ($r) => (array) $r)->all(), 'summarizing must not write to the trail');

        $this->event(19, self::RUN, self::DATE, '2026-04-21 17:40:00', 'FINALIZE', 'LATE_EVENT', 'WARN', 'LATE_REASON');
        $second = $this->summary();
        $this->assertSame($first['event_count'] + 1, $second['event_count']);
        $this->assertSame('LATE_EVENT', $second['last_event_type']);
        $this->assertSame(1, $second['reason_code_counts']['LATE_REASON']);
        $this->assertSame($first['stage_counts']['FINALIZE'] + 1, $second['stage_counts']['FINALIZE']);
        $this->assertSame($this->expected(self::RUN), array_intersect_key($second, $this->expected(self::RUN)));
    }

    /** `R0116..R0127`: the whole summary of the seeded trail equals the independent derivation. */
    public function test_the_whole_summary_equals_the_independent_derivation(): void
    {
        $this->seedTrail();
        foreach ([self::RUN, 9002, 9003] as $run) {
            $this->assertSame($this->expected($run), $this->summary($run), 'run '.$run);
        }
    }
}
