<?php

namespace Tests\Support;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * The MariaDB counterpart to `UsesMarketDataSqlite`.
 *
 * `MD-S003` acceptance requires the scenario families to pass "on MariaDB production semantics
 * **and** the supported test mirror". Every DB-backed market-data test uses the SQLite mirror, so
 * the mirror half was proven and the MariaDB half by nothing. A mirror is a mirror: it agrees with
 * production until it doesn't, and the places it can silently differ -- type affinity, string
 * comparison, date handling, `NULL` ordering, constraint enforcement -- are exactly where a
 * temporal query decides which revision a replay sees.
 *
 * Two differences from the SQLite trait, both deliberate:
 *
 *  - **The schema is the migrated one.** The SQLite mirror builds its tables from a hand-maintained
 *    definition; that is what makes it a mirror rather than production semantics. Here the schema
 *    is whatever `php artisan migrate` produced, so a column the mirror models loosely is exercised
 *    as it really is.
 *  - **Each test runs inside a transaction that is rolled back.** The database is shared and
 *    persistent, so tests must leave nothing behind. Nothing here drops or recreates a table.
 *
 * If MariaDB is not reachable the test is skipped rather than failed: an unavailable environment is
 * not a proof failure, and recording it as one would make the suite lie about what was verified.
 * A skip is visible in the run output and cannot be mistaken for a pass.
 */
trait UsesMarketDataMariaDb
{
    protected string $marketDataMariaDbConnection = 'market_data_mariadb_testing';

    /** @var bool */
    private $marketDataMariaDbStarted = false;

    protected function bootMarketDataMariaDb(): void
    {
        $base = config('database.connections.mysql');
        if (! is_array($base)) {
            $this->markTestSkipped('no mysql connection is configured, so MariaDB semantics cannot be exercised');
        }

        config()->set('database.connections.'.$this->marketDataMariaDbConnection, array_merge($base, [
            'database' => env('MARKET_DATA_MARIADB_TEST_DATABASE', 'tradeaxis_testing'),
        ]));
        config()->set('database.default', $this->marketDataMariaDbConnection);

        DB::purge($this->marketDataMariaDbConnection);

        try {
            DB::connection($this->marketDataMariaDbConnection)->getPdo();
        } catch (\Throwable $e) {
            $this->markTestSkipped('MariaDB is not reachable: '.$e->getMessage());
        }

        $this->assertMarketDataMariaDbSchemaIsCurrent();

        DB::connection($this->marketDataMariaDbConnection)->beginTransaction();
        $this->marketDataMariaDbStarted = true;
    }

    protected function tearDownMarketDataMariaDb(): void
    {
        if ($this->marketDataMariaDbStarted) {
            // Rolled back rather than truncated: the database is shared, and a test that cleaned up
            // by deleting rows would delete rows it did not create if a fixture id ever collided.
            DB::connection($this->marketDataMariaDbConnection)->rollBack();
            $this->marketDataMariaDbStarted = false;
        }

        DB::disconnect($this->marketDataMariaDbConnection);
    }

    /**
     * A stale schema would make a family "pass on MariaDB" against tables that are not the ones
     * production runs, which is the same defect as not testing it at all. The columns named here
     * are the ones the replay families actually resolve against.
     */
    private function assertMarketDataMariaDbSchemaIsCurrent(): void
    {
        $required = [
            'md_replay_daily_metrics' => ['replay_mode', 'knowledge_cutoff_at', 'temporal_identity_hash', 'mismatches_json'],
            'md_config_snapshots' => ['effective_at', 'recorded_at', 'config_hash'],
            'md_listing_symbols' => ['recorded_at', 'retracted_at'],
            'md_trading_status_revisions' => ['recorded_at', 'supersedes_revision_id'],
            'md_market_calendar_revisions' => ['recorded_at', 'supersedes_revision_id'],
        ];

        foreach ($required as $table => $columns) {
            if (! Schema::connection($this->marketDataMariaDbConnection)->hasTable($table)) {
                $this->markTestSkipped($table.' is absent from the MariaDB test database; run migrations against it');
            }
            foreach ($columns as $column) {
                if (! Schema::connection($this->marketDataMariaDbConnection)->hasColumn($table, $column)) {
                    $this->markTestSkipped(
                        $table.'.'.$column.' is absent from the MariaDB test database, so its schema is '
                        .'behind the migrations; a family proven against it would be proven against the '
                        .'wrong tables'
                    );
                }
            }
        }
    }

    /** The connection under test, for assertions that must name the substrate they ran on. */
    protected function marketDataMariaDb()
    {
        return DB::connection($this->marketDataMariaDbConnection);
    }
}
