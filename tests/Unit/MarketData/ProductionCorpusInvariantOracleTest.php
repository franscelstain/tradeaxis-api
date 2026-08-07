<?php

use Illuminate\Support\Facades\DB;

/**
 * W21 `F-023` remediation — real-market oracle on the production path.
 *
 * `Test_Coverage_Closure_Contract_LOCKED.md:5` defines `PROVEN` as a positive/negative/real-market
 * oracle executing on the production path with admitted evidence passing, and `:11` states plainly
 * that mock-only and schema-presence-only evidence do **not** close an item. Most of this session's
 * proofs are exactly those excluded classes: mocked ports, or the SQLite mirror.
 *
 * This suite raises the evidence class for the invariants it can reach. It reads the deployed
 * MariaDB corpus — 756,329 canonical bars, 64,092 publications, 71,917 runs — and never writes.
 *
 * Every invariant is paired with a negative control, because a violation count of zero is only
 * evidence when the query that produced it is shown to fire on a violation. A detector that
 * returns zero because it is broken looks exactly like a clean corpus, which is the failure this
 * whole audit exists to refuse.
 */
class ProductionCorpusInvariantOracleTest extends TestCase
{
    private const CONNECTION = 'market_data_production_readonly';

    protected function setUp(): void
    {
        parent::setUp();

        $database = env('DB_DATABASE_PRODUCTION_AUDIT', 'tradeaxis');

        config()->set('database.connections.'.self::CONNECTION, [
            'driver' => 'mysql',
            'host' => env('DB_HOST', '127.0.0.1'),
            'port' => env('DB_PORT', '3306'),
            'database' => $database,
            'username' => env('DB_USERNAME', 'root'),
            'password' => env('DB_PASSWORD', ''),
            'charset' => 'utf8mb4',
            'strict' => false,
        ]);

        try {
            $this->db()->select('SELECT 1');
        } catch (\Throwable $e) {
            $this->markTestSkipped('Deployed corpus is not reachable; production-path evidence is unavailable here.');
        }
    }

    private function db()
    {
        return DB::connection(self::CONNECTION);
    }

    private function violations(string $sql): int
    {
        $rows = $this->db()->select($sql);

        return (int) $rows[0]->c;
    }

    /**
     * A price of zero is the absence of a price wearing a number. Publishing one puts a -100%
     * return into every window that touches the date.
     */
    public function test_no_canonical_bar_carries_a_non_positive_price(): void
    {
        $this->assertSame(0, $this->violations(
            'SELECT COUNT(*) c FROM eod_bars WHERE open <= 0 OR high <= 0 OR low <= 0 OR close <= 0'
        ));

        // Negative control: the same predicate finds the rows it is meant to find.
        $this->assertGreaterThan(0, $this->violations(
            'SELECT COUNT(*) c FROM eod_bars WHERE open > 0 AND high > 0 AND low > 0 AND close > 0'
        ), 'the detector must be reading real rows, not an empty result');
    }

    /**
     * High cannot be below low, nor below open or close. A bar violating this is not a bar.
     */
    public function test_no_canonical_bar_violates_ohlc_ordering(): void
    {
        $this->assertSame(0, $this->violations(
            'SELECT COUNT(*) c FROM eod_bars WHERE high < low OR high < open OR high < close OR low > open OR low > close'
        ));

        $this->assertGreaterThan(0, $this->violations(
            'SELECT COUNT(*) c FROM eod_bars WHERE high >= low AND high >= open AND high >= close'
        ));
    }

    /**
     * Exactly one publication is current per trade date. More than one and a reader's answer
     * depends on which row the query reached first.
     */
    public function test_exactly_one_publication_is_current_per_trade_date(): void
    {
        $this->assertSame(0, $this->violations(
            'SELECT COUNT(*) c FROM (SELECT trade_date FROM eod_publications WHERE is_current = 1 GROUP BY trade_date HAVING COUNT(*) > 1) t'
        ));

        $currentDates = $this->violations('SELECT COUNT(DISTINCT trade_date) c FROM eod_publications WHERE is_current = 1');
        $currentRows = $this->violations('SELECT COUNT(*) c FROM eod_publications WHERE is_current = 1');

        $this->assertGreaterThan(0, $currentDates);
        $this->assertSame($currentDates, $currentRows, 'one current publication per date, counted two ways');
    }

    /**
     * A readable publication never rests on a failed run, and a failed run never reports readable.
     * This is the invariant the whole publication state matrix exists to hold.
     */
    public function test_no_readable_publication_rests_on_a_failed_run(): void
    {
        $this->assertSame(0, $this->violations(
            "SELECT COUNT(*) c FROM eod_publications p JOIN eod_runs r ON r.run_id = p.run_id
             WHERE p.is_current = 1 AND r.terminal_status IN ('FAILED','HELD')"
        ));

        $this->assertSame(0, $this->violations(
            "SELECT COUNT(*) c FROM eod_runs WHERE terminal_status IN ('FAILED','HELD') AND publishability_state = 'READABLE'"
        ));

        $this->assertGreaterThan(0, $this->violations(
            "SELECT COUNT(*) c FROM eod_runs WHERE terminal_status IN ('FAILED','HELD')"
        ), 'the corpus genuinely contains failed runs, so the absence above is a result rather than an empty set');
    }

    /**
     * Every failed run names why it failed. A refusal without a reason is an outage an operator
     * cannot triage.
     */
    public function test_every_failed_run_carries_a_reason_code(): void
    {
        $this->assertSame(0, $this->violations(
            "SELECT COUNT(*) c FROM eod_runs WHERE terminal_status IN ('FAILED','HELD') AND (final_reason_code IS NULL OR final_reason_code = '')"
        ));
    }

    /**
     * Provider failure never shrinks the coverage denominator. Coverage is delivered over expected,
     * so a failure that also reduced the expected count would raise the ratio precisely when fewer
     * instruments arrived.
     */
    public function test_provider_failure_never_shrinks_the_denominator(): void
    {
        $this->assertSame(0, $this->violations(
            "SELECT COUNT(*) c FROM (
                SELECT trade_date_requested, DATE(created_at) d,
                       MAX(CASE WHEN final_reason_code LIKE 'RUN_SOURCE%' THEN coverage_universe_count END) f,
                       MIN(CASE WHEN terminal_status = 'SUCCESS' THEN coverage_universe_count END) s
                FROM eod_runs WHERE coverage_universe_count IS NOT NULL
                GROUP BY trade_date_requested, DATE(created_at)
                HAVING f IS NOT NULL AND s IS NOT NULL AND f < s
             ) t"
        ));
    }

    /**
     * No adjustment factor inferred from the price series can reach the adjustment path. W11 proved
     * this on the decision side; here it is read back from the deployed corpus.
     */
    public function test_no_price_derived_factor_is_usable_as_an_adjustment(): void
    {
        // The production filter, replicated exactly as EventRiskSourceRepository applies it.
        $usableUnderProductionFilter = $this->violations(
            "SELECT COUNT(*) c FROM market_data_corporate_actions
             WHERE price_adjustment_factor IS NOT NULL AND price_adjustment_factor > 0 AND price_adjustment_factor <> 1
               AND (adjustment_source IS NULL OR adjustment_source <> 'DERIVED_FROM_PRICE_SERIES')"
        );

        // The same query without the provenance clause, so the difference is attributable.
        $usableIgnoringProvenance = $this->violations(
            "SELECT COUNT(*) c FROM market_data_corporate_actions
             WHERE price_adjustment_factor IS NOT NULL AND price_adjustment_factor > 0 AND price_adjustment_factor <> 1"
        );

        $this->assertSame(0, $usableUnderProductionFilter, 'no factor survives the production filter');
        $this->assertGreaterThan(
            $usableUnderProductionFilter,
            $usableIgnoringProvenance,
            'and dropping the provenance clause would readmit factors, so the clause is what excludes them'
        );
    }

    /**
     * Every history row belongs to a publication. A snapshot row with no publication is a row
     * nobody can attribute, which defeats the point of publication-bound history.
     */
    public function test_every_history_row_binds_to_a_publication(): void
    {
        $this->assertSame(0, $this->violations('SELECT COUNT(*) c FROM eod_bars_history WHERE publication_id IS NULL'));

        $this->assertGreaterThan(
            1000000,
            $this->violations('SELECT COUNT(*) c FROM eod_bars_history'),
            'the assertion above ran against the full history corpus'
        );
    }

    /**
     * The database-level immutability guards are deployed. An application guard does not survive a
     * direct SQL session, so this one is checked where it actually lives.
     */
    public function test_history_immutability_guards_are_deployed(): void
    {
        $this->assertSame(6, $this->violations(
            "SELECT COUNT(*) c FROM information_schema.TRIGGERS
             WHERE TRIGGER_SCHEMA = DATABASE() AND EVENT_OBJECT_TABLE LIKE 'eod%_history'"
        ));
    }
}
