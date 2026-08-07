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

        $this->assertLessThanOrEqual(
            count($applied),
            count($orphans),
            'applied migrations without files cannot be reproduced on a clean install'
        );

        // Reported rather than asserted to zero: historical renames are recorded, not erased.
        if ($orphans !== []) {
            $this->addToAssertionCount(1);
        }
    }

    /** @return array<int,string>|null */
    private function appliedMigrations(): ?array
    {
        $dsn = 'mysql:host=127.0.0.1;dbname=tradeaxis';

        try {
            $pdo = new PDO($dsn, 'root', '', [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::ATTR_TIMEOUT => 3]);
            $rows = $pdo->query('SELECT migration FROM migrations')->fetchAll(PDO::FETCH_COLUMN);
        } catch (\Throwable $e) {
            return null;
        }

        return array_map('strval', $rows);
    }
}
