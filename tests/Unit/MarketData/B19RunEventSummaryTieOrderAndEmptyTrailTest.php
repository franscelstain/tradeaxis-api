<?php

use App\Infrastructure\Persistence\MarketData\EodEvidenceRepository;
use Illuminate\Support\Facades\DB;
use Tests\Support\UsesMarketDataSqlite;

/**
 * `MD-B19` — two residual questions of `artifact_run_event_summary` (`MD-S075-R0117..R0120`, `R0121`, `R0128`).
 *
 * 1. Deterministic order of events that share a timestamp.
 *    `event_time` is a second-precision DATETIME, so the events of a run routinely tie. A tie is resolved only by
 *    the engine unless the query says how: `ORDER BY event_time` alone returns tied rows in whatever order the plan
 *    happens to produce, which today is primary-key order on the engines in use and is not a guarantee. The trail is
 *    append-only, so `event_id` is its append sequence and the only tie break that does not depend on the plan.
 *    The locked text defines `first_event_*` / `last_event_*` without a tie rule; this guard protects the
 *    determinism the summary needs in order to be "derivable from `eod_run_events`" (MD-S075 section 3; Determinism
 *    Invariants), not a tie rule the contract states. Two proofs, neither of which reads the production source:
 *      - the SQL the repository really executes (captured from the connection) orders by `event_time` then
 *        `event_id`, ascending;
 *      - on a table whose only usable index returns tied rows in an order that is NOT the id order, first and last
 *        are still the lowest and highest event id of the tied rows.
 *
 * 2. The severity of a run with NO events.
 *    The locked text gives `highest_severity` an example for a non-empty trail only and says nothing about an empty
 *    one (see `F-MD-B19-A001-008`). Nothing below pins the value the repository returns today. The guard asserts
 *    only what any reading of the contract needs: the field is present and an empty trail does not report a
 *    problem (`WARN`/`ERROR`) it never observed. Whether the value is `INFO`, `NULL` or another marker is OPEN.
 */
class B19RunEventSummaryTieOrderAndEmptyTrailTest extends TestCase
{
    use UsesMarketDataSqlite;

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

    private function event(int $id, int $run, string $time, string $type, string $severity = 'INFO'): void
    {
        DB::table('eod_run_events')->insert([
            'event_id' => $id, 'run_id' => $run, 'trade_date_requested' => self::DATE, 'event_time' => $time,
            'stage' => 'INGEST', 'event_type' => $type, 'severity' => $severity, 'reason_code' => null,
            'message' => null, 'event_payload_json' => null, 'created_at' => $time,
        ]);
    }

    /** @return array<int,string> the SQL statements that read eod_run_events while $call runs */
    private function executedTrailQueries(callable $call): array
    {
        $seen = [];
        DB::listen(function ($query) use (&$seen) {
            if (stripos($query->sql, 'eod_run_events') !== false && stripos($query->sql, 'select') === 0) {
                $seen[] = $query->sql;
            }
        });
        $call();

        return $seen;
    }

    /** `R0117..R0120`: the statement the repository executes orders by event_time and then by event_id, both ascending. */
    public function test_the_executed_query_orders_ties_by_event_id_after_event_time(): void
    {
        $this->event(1, 9001, '2026-04-21 17:31:00', 'A');
        $queries = $this->executedTrailQueries(fn () => (new EodEvidenceRepository())->summarizeRunEvents(9001));

        $this->assertCount(1, $queries, 'the summarizer reads the trail with exactly one statement');
        $sql = strtolower(str_replace(['"', '`'], '', $queries[0]));
        $this->assertMatchesRegularExpression('/order by event_time( asc)?, event_id( asc)?\s*$/', trim($sql), 'executed SQL: '.$sql);
        $this->assertStringContainsString('run_id', $sql);
    }

    /**
     * `R0117..R0120`: when the only index the engine can use for the run filter orders tied rows by event_type, the
     * summary still takes the lowest event id as first and the highest as last of the tied rows.
     */
    public function test_ties_are_resolved_by_event_id_even_when_the_engine_would_return_them_in_another_order(): void
    {
        DB::statement('DROP INDEX IF EXISTS idx_run_events_run_time');
        DB::statement('CREATE INDEX probe_run_time_type ON eod_run_events (run_id, event_time, event_type)');

        // both ends of the run are ties, and the event types sort in the OPPOSITE order of the event ids
        $this->event(41, 9001, '2026-04-21 17:00:00', 'Z_LOWER_ID');
        $this->event(42, 9001, '2026-04-21 17:00:00', 'A_HIGHER_ID');
        $this->event(45, 9001, '2026-04-21 17:10:00', 'MIDDLE');
        $this->event(43, 9001, '2026-04-21 17:20:00', 'Z_LOWER_ID_LAST');
        $this->event(44, 9001, '2026-04-21 17:20:00', 'A_HIGHER_ID_LAST');

        $plan = DB::select("EXPLAIN QUERY PLAN SELECT * FROM eod_run_events WHERE run_id = 9001 ORDER BY event_time");
        $this->assertStringContainsString('probe_run_time_type', (string) json_encode($plan), 'precondition: the engine would walk the type-ordered index, so a missing tie break shows');

        $summary = (new EodEvidenceRepository())->summarizeRunEvents(9001);
        $this->assertSame('Z_LOWER_ID', $summary['first_event_type'], 'first of two tied events is the lower event_id');
        $this->assertSame('A_HIGHER_ID_LAST', $summary['last_event_type'], 'last of two tied events is the higher event_id');
        $this->assertSame(5, $summary['event_count']);
    }

    /** `R0121`, `R0128`: an empty trail has the field, and does not report a problem it never observed. */
    public function test_an_empty_trail_reports_no_problem_severity_it_never_observed(): void
    {
        $this->event(1, 9002, '2026-04-21 17:31:00', 'OTHER_RUN_ERROR', 'ERROR');
        $summary = (new EodEvidenceRepository())->summarizeRunEvents(424242);

        $this->assertSame(0, $summary['event_count']);
        $this->assertArrayHasKey('highest_severity', $summary, 'the field is present for an empty trail');
        $this->assertNotContains($summary['highest_severity'], ['WARN', 'ERROR'], 'an empty trail has observed neither a warning nor an error, and another run\'s ERROR must not leak');
    }

    /** `R0121`, `R0130`: a trail with an event keeps reporting the observed maximum however the empty case is later decided. */
    public function test_a_single_event_trail_reports_its_own_severity(): void
    {
        $this->event(1, 9003, '2026-04-21 17:31:00', 'ONLY_WARN', 'WARN');
        $this->assertSame('WARN', (new EodEvidenceRepository())->summarizeRunEvents(9003)['highest_severity']);
    }
}
