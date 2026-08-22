<?php

use Illuminate\Support\Str;
use PHPUnit\Framework\TestCase;

/**
 * W03 — migration framework integrity and schema drift detection.
 *
 * Exit gate: "clean-install/upgrade path tersedia untuk setiap feature berikut; belum ada
 * nullable placeholder yang dianggap conformant."
 *
 * Owner contract: docs/market_data/db/DB_Schema_And_Migration_Sync_Contract_LOCKED.md
 *                 — section "Drift detection is required (LOCKED)"
 *
 * The first test exists because a migration shipped with a class name that did not match its
 * filename. Laravel's migrator resolves the expected class from the filename; when the check
 * fails it falls back to a plain require of an already-required file and the run dies with
 * "Cannot declare class ... because the name is already in use" before any statement executes.
 * That migration could never run, and nothing detected it — the suite stayed green because the
 * SQLite mirror is hand-written and independent of the deployed schema.
 */
class MigrationIntegrityAndDriftTest extends TestCase
{
    private function root(): string
    {
        return dirname(__DIR__, 3);
    }

    /** @return array<string,string> migration name => absolute path */
    private function migrationFiles(): array
    {
        $files = [];
        foreach (glob($this->root().'/database/migrations/*.php') as $path) {
            $files[basename($path, '.php')] = $path;
        }
        ksort($files);

        return $files;
    }

    /**
     * Every migration must declare the class the migrator will look for. A mismatch is not a
     * style issue: it makes the migration unrunnable.
     */
    public function test_every_migration_class_name_matches_its_filename(): void
    {
        $violations = [];

        foreach ($this->migrationFiles() as $name => $path) {
            $source = (string) file_get_contents($path);

            // Anonymous class migrations are valid and carry no name to match. Both
            // `new class(...)` and `new class extends Migration` are legitimate forms.
            if (preg_match('/return\s+new\s+class\b/', $source)) {
                continue;
            }

            if (! preg_match('/^\s*(?:final\s+)?class\s+([A-Za-z_][A-Za-z0-9_]*)/m', $source, $m)) {
                $violations[] = $name.' declares no migration class';
                continue;
            }

            $declared = $m[1];
            $expected = Str::studly(implode('_', array_slice(explode('_', $name), 4)));

            if ($declared !== $expected) {
                $violations[] = $name.': declares '.$declared.', migrator resolves '.$expected;
            }
        }

        $this->assertSame([], $violations, 'a migration whose class name does not match its filename cannot run');
    }

    /**
     * Two migrations declaring the same class collide on load. Filenames differ, class names
     * must too.
     */
    public function test_no_two_migrations_declare_the_same_class(): void
    {
        $seen = [];
        $violations = [];

        foreach ($this->migrationFiles() as $name => $path) {
            $source = (string) file_get_contents($path);
            if (! preg_match('/^\s*(?:final\s+)?class\s+([A-Za-z_][A-Za-z0-9_]*)/m', $source, $m)) {
                continue;
            }
            $class = $m[1];
            if (isset($seen[$class])) {
                $violations[] = $class.' declared by '.$seen[$class].' and '.$name;
            }
            $seen[$class] = $name;
        }

        $this->assertSame([], $violations);
    }

    /**
     * Every migration must be reversible in principle: an `up` without a `down` leaves an
     * upgrade path with no rollback, which the sync contract forbids relying on.
     */
    public function test_every_migration_declares_both_up_and_down(): void
    {
        $violations = [];

        foreach ($this->migrationFiles() as $name => $path) {
            $source = (string) file_get_contents($path);
            if (strpos($source, 'function up(') === false) {
                $violations[] = $name.' has no up()';
            }
            if (strpos($source, 'function down(') === false) {
                $violations[] = $name.' has no down()';
            }
        }

        $this->assertSame([], $violations);
    }

    /**
     * Drift detection, direction one: a migration file present but never applied.
     *
     * This is the condition that produced five separate findings from one unapplied batch. It
     * cannot be detected by the suite alone, because the mirror encodes the intended schema
     * rather than the deployed one — so the check must read the applied-migration record.
     */
    public function test_no_migration_file_remains_unapplied_where_a_database_is_reachable(): void
    {
        $applied = $this->appliedMigrations();

        if ($applied === null) {
            $this->markTestSkipped('no reachable database; drift detection requires the applied-migration record');
        }

        $pending = array_values(array_diff(array_keys($this->migrationFiles()), $applied));

        $this->assertSame([], $pending, 'schema drift: migration files present but not applied');
    }

    /**
     * Drift detection, direction two: an applied migration whose file no longer exists. The
     * deployed schema then contains changes nothing in the repository can reproduce.
     */
    public function test_no_applied_migration_has_lost_its_file(): void
    {
        $applied = $this->appliedMigrations();

        if ($applied === null) {
            $this->markTestSkipped('no reachable database; drift detection requires the applied-migration record');
        }

        $orphans = array_values(array_diff($applied, array_keys($this->migrationFiles())));

        // The prior assertion here was `count($orphans) <= count($applied)`, which is true by
        // construction because orphans is a subset of applied. It could not fail, and it called
        // addToAssertionCount() so the suite reported an assertion that tested nothing. Eleven
        // orphans were present in the deployed database the whole time it was green.
        $outOfScope = [];
        $inScope = [];
        foreach ($orphans as $orphan) {
            // Watchlist is a separate domain. MARKET_DATA_DOCUMENT_AUTHORITY.md clause 6 places
            // watchlist outcomes outside market-data acceptance authority, so a watchlist
            // migration whose file lives in another package is not market-data drift. It is still
            // named here rather than silently dropped, because the sync contract requires the
            // identifiers involved to be recorded in both directions.
            if (preg_match('/(watchlist|_bt_|backtest|paramset|c171)/i', $orphan)) {
                $outOfScope[] = $orphan;
            } else {
                $inScope[] = $orphan;
            }
        }

        $this->assertSame(
            [],
            $inScope,
            'schema drift: market-data migrations applied to the database with no file to reproduce them on a clean install'
        );

        // Explicit result for the out-of-scope direction, per the sync contract.
        $this->assertSame(
            ['2026_06_09_000001_create_watchlist_backtest_oos_schema',
             '2026_06_09_000002_add_stop_rr_to_watchlist_bt_param_grid',
             '2026_06_09_000003_version_watchlist_bt_eval_identity',
             '2026_06_09_000004_version_watchlist_bt_oos_identity',
             '2026_06_10_000001_add_watchlist_backtest_catalog_identity_and_r2_entry_quality',
             '2026_07_24_000001_create_watchlist_runtime_paramset_and_plan_schema',
             '2026_07_25_000001_version_watchlist_official_backtest_evidence_and_paramset_identity',
             '2026_07_27_000001_widen_watchlist_backtest_universe_vol_ratio_precision',
             '2026_07_27_000002_add_c171_real_is_remediation_catalog_bounds',
             '2026_07_28_000001_add_c171_low_price_execution_quality_catalog_fields',
             '2026_07_28_000002_version_c171_tick_risk_evidence_pipeline'],
            $outOfScope,
            'the known out-of-scope orphan set changed; a new orphan must be classified deliberately rather than absorbed'
        );
    }

    /** @return array<int,string>|null */
    private function appliedMigrations(): ?array
    {
        // Read the configured connection rather than hardcoding it. The previous version pinned
        // host, database, user, and an empty password; in any environment that differs it returned
        // null and the drift checks skipped. A skip reads the same as "no drift found", which is
        // the outcome drift detection exists to prevent.
        $config = [];
        $envPath = $this->root().'/.env';
        if (is_file($envPath)) {
            foreach (file($envPath) as $line) {
                if (preg_match('/^(DB_[A-Z_]+)=(.*)$/', trim($line), $m)) {
                    $config[$m[1]] = trim($m[2], "\"'");
                }
            }
        }

        $host = $config['DB_HOST'] ?? '127.0.0.1';
        $port = $config['DB_PORT'] ?? '3306';
        $name = $config['DB_DATABASE'] ?? 'tradeaxis';
        $user = $config['DB_USERNAME'] ?? 'root';
        $pass = $config['DB_PASSWORD'] ?? '';

        try {
            $pdo = new PDO(
                'mysql:host='.$host.';port='.$port.';dbname='.$name,
                $user,
                $pass,
                [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::ATTR_TIMEOUT => 3]
            );
            $rows = $pdo->query('SELECT migration FROM migrations')->fetchAll(PDO::FETCH_COLUMN);
        } catch (\Throwable $e) {
            return null;
        }

        return array_map('strval', $rows);
    }

    // ---------------------------------------------------------------- nullable placeholder gate

    /**
     * The stage exit gate has two halves. The tests above prove the clean-install/upgrade path; this
     * block proves the other one — "belum ada nullable placeholder yang dianggap conformant" — which
     * had no test anywhere in the repository. The word `nullable` appeared exactly once in this file,
     * inside the quotation at the top, for the whole verification epoch.
     *
     * Owner contract: `db/DB_Schema_And_Migration_Sync_Contract_LOCKED.md`, which states the
     * invariant exactly: nullable rollout columns "are not a weaker accepted production state; a
     * later enforcement migration is required after verified backfill", and backfill and enforcement
     * must be "distinct observable stages when nullability is needed for rollout".
     */
    private const V2_FOUNDATION_MIGRATION = '2026_08_02_000001_add_market_data_strategy_v2_foundation';

    private function contractPath(): string
    {
        return $this->root().'/docs/market_data/development/implementation/db/DB_Schema_And_Migration_Sync_Contract_LOCKED.md';
    }

    /**
     * Areas of the V2 rollout matrix: area => relock state.
     *
     * @return array<string,string>
     */
    private function rolloutMatrix(): array
    {
        $rows = [];
        $inMatrix = false;
        foreach (preg_split('/\R/', (string) file_get_contents($this->contractPath())) as $line) {
            $line = trim($line);
            if (strpos($line, '## V2 rollout matrix') === 0) {
                $inMatrix = true;

                continue;
            }
            if ($inMatrix && strpos($line, '## ') === 0) {
                break;
            }
            if (! $inMatrix || strpos($line, '|') !== 0) {
                continue;
            }
            $cells = array_map('trim', explode('|', trim($line, "| \t")));
            if (count($cells) !== 4 || $cells[0] === 'Area' || strpos($cells[0], '---') === 0) {
                continue;
            }
            $rows[$cells[0]] = strtolower($cells[3]);
        }

        return $rows;
    }

    /**
     * The nullable columns the V2 foundation migration introduced, as table => column list.
     *
     * @return array<string,array<int,string>>
     */
    private function v2NullableColumns(): array
    {
        $path = $this->root().'/database/migrations/'.self::V2_FOUNDATION_MIGRATION.'.php';
        $out = [];
        $table = null;
        foreach (preg_split('/\R/', (string) file_get_contents($path)) as $line) {
            if (preg_match("/Schema::(?:create|table)\(\s*'([a-z0-9_]+)'/", $line, $m)) {
                $table = $m[1];
            }
            if ($table !== null && preg_match("/\\\$table->[a-zA-Z]+\(\s*'([a-z0-9_]+)'.*nullable\(\)/", $line, $m)) {
                $out[$table][] = $m[1];
            }
        }

        return $out;
    }

    /**
     * The matrix says every rollout area is still `open`, and the schema agrees: the foundation
     * columns are still nullable. Either side drifting from the other is the defect — a relocked
     * area whose columns were never enforced, or enforced columns the matrix still calls open.
     */
    public function test_the_rollout_matrix_and_the_deployed_schema_agree_that_no_area_is_relocked(): void
    {
        $matrix = $this->rolloutMatrix();
        $this->assertGreaterThanOrEqual(9, count($matrix), 'the V2 rollout matrix must actually be parsed');

        $columns = $this->v2NullableColumns();
        $total = 0;
        foreach ($columns as $list) {
            $total += count($list);
        }
        $this->assertGreaterThan(80, $total, 'the foundation migration must really introduce nullable rollout columns');

        $relocked = [];
        foreach ($matrix as $area => $state) {
            if ($state !== 'open') {
                $relocked[] = $area.' => '.$state;
            }
        }

        $this->assertSame(
            [],
            $relocked,
            'a rollout area may not be relocked while its columns are still nullable placeholders'
        );
    }

    /**
     * No V2 rollout column may have been enforced `NOT NULL` yet, because the matrix reports no
     * verified backfill for any area. An enforcement migration landing while the area is still open
     * is exactly a nullable placeholder being accepted as conformant.
     */
    public function test_no_rollout_column_is_enforced_not_null_before_a_verified_backfill(): void
    {
        $columns = [];
        foreach ($this->v2NullableColumns() as $list) {
            foreach ($list as $column) {
                $columns[$column] = true;
            }
        }
        // 94 nullable declarations across the foundation migration collapse to 69 distinct names.
        $this->assertGreaterThan(60, count($columns), 'the rollout column set must be populated before it is searched for');

        $enforced = [];
        foreach ($this->migrationFiles() as $name => $path) {
            if ($name === self::V2_FOUNDATION_MIGRATION) {
                continue;
            }
            $source = (string) file_get_contents($path);
            foreach (array_keys($columns) as $column) {
                $quoted = preg_quote($column, '/');
                if (preg_match("/'".$quoted."'[^;\\n]*nullable\(false\)/", $source)
                    || preg_match('/(?:MODIFY|CHANGE)\s+(?:COLUMN\s+)?`?'.$quoted.'`?[^;]*NOT\s+NULL/i', $source)) {
                    $enforced[] = $name.' :: '.$column;
                }
            }
        }

        $this->assertSame([], $enforced, 'a rollout column was enforced NOT NULL while its area is still open');
    }

    /**
     * The detectors must be able to fire, or a clean result is indistinguishable from a broken scan.
     */
    public function test_the_nullable_placeholder_detectors_can_fire(): void
    {
        $column = current(current($this->v2NullableColumns()));
        $this->assertIsString($column, 'a rollout column must be resolvable for the probe');

        $quoted = preg_quote($column, '/');
        $this->assertSame(
            1,
            preg_match("/'".$quoted."'[^;\\n]*nullable\(false\)/", "\$table->string('".$column."')->nullable(false);"),
            'the Blueprint enforcement probe must match'
        );
        $this->assertSame(
            1,
            preg_match('/(?:MODIFY|CHANGE)\s+(?:COLUMN\s+)?`?'.$quoted.'`?[^;]*NOT\s+NULL/i', 'ALTER TABLE t MODIFY `'.$column.'` VARCHAR(8) NOT NULL'),
            'the raw-SQL enforcement probe must match'
        );
        $this->assertSame(
            0,
            preg_match("/'".$quoted."'[^;\\n]*nullable\(false\)/", "\$table->string('".$column."')->nullable();"),
            'an ordinary nullable declaration is the rollout state, not a violation'
        );

        $this->assertSame(
            1,
            preg_match('/^open$/', 'open'),
            'the relock-state comparison must be exact rather than substring'
        );
    }

    /**
     * The contract and the dictionary must keep saying the nullable foundation is progress rather
     * than closure. An affirmative statement is not proven by silence: if the sentences disappear,
     * nothing stops the next reader treating the placeholders as the accepted production state.
     */
    public function test_the_nullable_foundation_is_still_recorded_as_progress_not_closure(): void
    {
        $contract = (string) file_get_contents($this->contractPath());
        $this->assertStringContainsString(
            'They are not a weaker accepted production state',
            $contract,
            'the contract must keep stating that nullable rollout columns are not an accepted end state'
        );
        $this->assertStringContainsString(
            'a later enforcement migration is required after verified backfill',
            $contract,
            'the contract must keep requiring backfill before enforcement'
        );
        $this->assertStringContainsString(
            'make backfill and enforcement distinct observable stages',
            $contract,
            'the contract must keep the two stages separate'
        );

        $dictionary = (string) file_get_contents(
            $this->root().'/docs/market_data/development/implementation/db/MARKET_DATA_DICTIONARY.md'
        );
        $this->assertStringContainsString(
            'The current nullable V2 foundation is implementation progress, not closure',
            $dictionary,
            'the dictionary must keep denying that the nullable foundation is closure'
        );
    }
}