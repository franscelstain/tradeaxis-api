<?php

use Illuminate\Support\Facades\DB;
use Tests\Support\UsesMarketDataSqlite;

require_once __DIR__.'/../../Support/B18ScenarioFamilyBase.php';

/**
 * `MD-B18-A002` -- `MD-S003-R0025`, the supported-test-mirror half (D-MD-B18-A002-017).
 *
 * > All required scenario families pass on MariaDB production semantics **and** the supported test mirror.
 *
 * The owner decided (D-MD-B18-A002-017) that the mirror must prove semantic-equivalent behaviour for every required bullet; an engine difference never authorizes an
 * exclusion; where SQLite lacks a production-specific primitive the mirror uses an equivalent mechanism; the only exception allowed is one governance already granted
 * (D-MD-B18-A002-001 item 4: SQLite never establishes referential integrity or production nullability).
 *
 * This class runs the SAME six families, the SAME 21 bullets and the SAME scenarios as `B18ScenarioFamiliesOnMariaDbTest` (written once, in `B18ScenarioFamilyBase`) on the
 * in-memory-schema SQLite mirror (`UsesMarketDataSqlite`: hand-maintained schema, foreign keys off), through the same production repositories and services. SQLite lacks four
 * production primitives that the scenarios rely on; each has an equivalent mechanism here, derived from the production definition wherever one exists:
 *
 *  - sealed-history triggers: the nine triggers of the production migration are installed on the mirror, generated from the migration's own SQL (name, table, event, condition,
 *    message), so a direct insert/update/delete of a sealed publication's snapshot rows is refused with the same reason;
 *  - the ENUM of `eod_publications.seal_state`: parsed from the production migration and enforced by mirror triggers that refuse a value outside it with the same message;
 *  - the `GET_LOCK` / `RELEASE_LOCK` advisory locks: registered as SQLite user functions with MariaDB's contract (1 acquired or re-entrant, 0 held by another connection,
 *    `RELEASE_LOCK` 1 / 0 / NULL), so the foundation restore and the independent candidate verification run unchanged;
 *  - concurrent consumers: the mirror database is a file in WAL mode, so a second connection is a genuinely independent consumer that reads only committed state while the
 *    production switch is mid-transaction -- the same scenario as MariaDB, not a single-connection substitute.
 *
 * `information_schema` column metadata is replaced by `PRAGMA table_info` (the assertions are on column existence and nullability).
 * PRIMARY KEY and UNIQUE constraints are proven natively. The one governed exception is the foreign-key assertion (`D001_ITEM4_REFERENTIAL_INTEGRITY`), and each family's
 * invoked exceptions are pinned, so no other omission can pass.
 */
class B18ScenarioFamiliesOnMirrorTest extends B18ScenarioFamilyBase
{
    use UsesMarketDataSqlite;

    protected string $marketDataMariaDbConnection = 'sqlite';

    /** @var string|null the mirror database file (a file so that a second connection can be a concurrent consumer) */
    private $mirrorFile;

    /** @var array<string,int> advisory lock name => owning connection (MariaDB's named locks, process-wide for the test run) */
    private static $locks = [];

    protected function bootSubstrate(): void
    {
        $this->mirrorFile = tempnam(sys_get_temp_dir(), 'r0025mirror');
        config()->set('database.default', 'sqlite');
        config()->set('market_data.source.api.timeout_seconds', 20);
        config()->set('database.connections.sqlite', ['driver' => 'sqlite', 'database' => $this->mirrorFile, 'prefix' => '', 'foreign_key_constraints' => false]);
        DB::purge('sqlite');
        DB::reconnect('sqlite');
        $pdo = DB::connection('sqlite')->getPdo();
        $pdo->exec('PRAGMA journal_mode=WAL');
        $pdo->exec('PRAGMA synchronous=OFF');
        $this->createMarketDataSqliteSchema();
        $this->createProductionFoundationTables();
        $this->registerAdvisoryLocks($pdo);
        $this->installProductionSealedHistoryTriggers();
        $this->installProductionSealStateEnum();
    }

    protected function tearDownSubstrate(): void
    {
        $owner = spl_object_id(DB::connection('sqlite')->getPdo());
        foreach (self::$locks as $name => $holder) {
            if ($holder === $owner) {
                unset(self::$locks[$name]);
            }
        }
        $this->tearDownMarketDataSqlite();
        DB::disconnect('market_data_mirror_reader');
        if ($this->mirrorFile !== null) {
            foreach (['', '-wal', '-shm'] as $suffix) {
                @unlink($this->mirrorFile.$suffix);
            }
        }
    }

    protected function markSubstrateTransactionEnded(): void
    {
        // the mirror wraps nothing in a transaction: writes commit as they are made
    }

    protected function marketDataMariaDb()
    {
        return $this->db();
    }

    protected function isProductionEngine(): bool
    {
        return false;
    }

    protected function duplicateKeyRefusal(): string
    {
        return 'UNIQUE constraint failed';
    }

    // ---- the equivalent mechanisms ---------------------------------------------------------------------------------------------------

    /**
     * The shared security-identity foundation tables (si_*) that the retained-identity restore reads and writes. The mirror schema does not model them, so they are
     * created by running the PRODUCTION migration on the mirror connection (not by a second hand-written definition).
     */
    private function createProductionFoundationTables(): void
    {
        $file = base_path('database/migrations/2026_09_29_000001_create_shared_security_identity_foundation.php');
        if (! class_exists('CreateSharedSecurityIdentityFoundation', false)) {
            require_once $file;
        }
        (new \CreateSharedSecurityIdentityFoundation())->up();
    }

    /** GET_LOCK / RELEASE_LOCK with MariaDB's contract; ownership is per connection, so a second connection that registers the same functions cannot take a held name. */
    private function registerAdvisoryLocks(\PDO $pdo): void
    {
        $owner = spl_object_id($pdo);
        $pdo->sqliteCreateFunction('GET_LOCK', static function ($name, $timeout) use ($owner) {
            if (! isset(self::$locks[$name]) || self::$locks[$name] === $owner) {
                self::$locks[$name] = $owner;

                return 1;
            }

            return 0;
        }, 2);
        $pdo->sqliteCreateFunction('RELEASE_LOCK', static function ($name) use ($owner) {
            if (! isset(self::$locks[$name])) {
                return null;
            }
            if (self::$locks[$name] !== $owner) {
                return 0;
            }
            unset(self::$locks[$name]);

            return 1;
        }, 1);
    }

    /** The nine production sealed-history triggers, translated to SQLite from the production migration's own SQL. */
    private function installProductionSealedHistoryTriggers(): void
    {
        $db = $this->db();
        foreach ($this->productionSealedHistoryTriggers() as $name => [$table, $event, $condition, $message]) {
            $db->unprepared('DROP TRIGGER IF EXISTS `'.$name.'`');
            $db->unprepared('CREATE TRIGGER `'.$name.'` BEFORE '.$event.' ON `'.$table.'` FOR EACH ROW WHEN ('.$condition.') BEGIN SELECT RAISE(ABORT, \''.$message.'\'); END');
        }
    }

    /** The seal_state ENUM of the production migration, enforced on insert and update with MariaDB's strict-mode message. */
    private function installProductionSealStateEnum(): void
    {
        $in = "'".implode("', '", $this->productionSealStateEnum())."'";
        foreach (['INSERT' => 'mirror_enum_eod_publications_seal_state_bi', 'UPDATE OF seal_state' => 'mirror_enum_eod_publications_seal_state_bu'] as $event => $name) {
            $this->db()->unprepared('CREATE TRIGGER `'.$name.'` BEFORE '.$event.' ON `eod_publications` FOR EACH ROW WHEN (NEW.seal_state NOT IN ('.$in.')) BEGIN SELECT RAISE(ABORT, \'Data truncated for column seal_state\'); END');
        }
    }

    protected function columnMetadata(array $tables): array
    {
        $columns = [];
        foreach ($tables as $table) {
            foreach ($this->db()->select("PRAGMA table_info('".$table."')") as $col) {
                $columns[$table][$col->name] = ['type' => strtolower((string) $col->type), 'nullable' => (int) $col->notnull === 0];
            }
        }

        return $columns;
    }

    protected function acquireFixtureLock(string $name): void
    {
        $this->assertSame(1, (int) $this->db()->selectOne('select get_lock(?, 5) as acquired', [$name])->acquired, 'another R0025 concurrency fixture owns the namespace');
    }

    protected function releaseFixtureLock(string $name): void
    {
        $this->db()->selectOne('select release_lock(?) as released', [$name]);
    }

    // ---- the governed exceptions are pinned ------------------------------------------------------------------------------------------

    /**
     * The governed exceptions each family invokes on the mirror. A family that invokes another, more or fewer fails. Only the foreign-key assertion of the pointer scenario
     * (D-MD-B18-A002-001 item 4) is an exception; every other bullet runs in full.
     *
     * @return array<string,array<int,string>>
     */
    protected function governedExceptionsByFamily(): array
    {
        return [
            'Exact publication verification' => [],
            'Degraded acquisition and expectation' => [],
            'Temporal identity and status' => [],
            'Corporate actions and indicators' => [],
            'Correction and read path' => ['D001_ITEM4_REFERENTIAL_INTEGRITY'],
            'As-known isolation' => [],
        ];
    }

    private function runMirrorFamily(string $family): void
    {
        $this->governedExceptionsInvoked = [];
        $this->runFamily($family);
        $invoked = array_values(array_unique($this->governedExceptionsInvoked));
        $expected = $this->governedExceptionsByFamily()[$family];
        sort($invoked);
        sort($expected);
        $this->assertSame($expected, $invoked, $family.': the governed exceptions the mirror invoked are not exactly the reviewed ones for this family');
        $this->assertSame(21, array_sum(array_map('count', $this->bulletMap())), 'the bullet map no longer has 21 bullets');
        $ran = array_filter($this->bulletRuns, function ($r) use ($family) { return $r['family'] === $family; });
        $this->assertCount(count($this->bulletMap()[$family]), $ran, $family.': a bullet scenario did not run');
    }

    // ---- guards -------------------------------------------------------------------------------------------------------------------

    public function test_the_mirror_runs_exactly_the_families_and_bullets_the_contract_names(): void
    {
        $contract = $this->contractBullets();
        $this->assertSame(array_keys($contract), array_keys($this->familyMap()), 'MD-S003 and the family map disagree');
        $this->assertSame(array_keys($contract), array_keys($this->bulletMap()));
        foreach ($contract as $family => $bullets) {
            $this->assertNotSame([], $bullets, $family);
            $this->assertSame($bullets, array_keys($this->bulletMap()[$family]), $family.': the bullet map and MD-S003 disagree');
        }
        $this->assertSame(21, array_sum(array_map('count', $contract)), 'MD-S003 no longer names 21 family bullets');
    }

    public function test_every_family_test_of_the_mirror_runs_its_own_family_through_the_bullet_map(): void
    {
        $source = (string) file_get_contents(__FILE__);
        foreach ($this->mirrorFamilyMap() as $family => $method) {
            $this->assertSame(1, preg_match(
                '/function '.preg_quote($method, '/').'\(\): void\s*\{\s*\$this->runMirrorFamily\(\''.preg_quote($family, '/').'\'\);\s*\}/',
                $source
            ), $method.' must be exactly runMirrorFamily(\''.$family.'\')');
        }
        $this->assertSame(array_keys($this->familyMap()), array_keys($this->mirrorFamilyMap()));
        $this->assertSame(array_keys($this->familyMap()), array_keys($this->governedExceptionsByFamily()));
    }

    /** @return array<string,string> contract heading => the test that exercises it on the mirror */
    protected function mirrorFamilyMap(): array
    {
        return [
            'Exact publication verification' => 'test_exact_publication_verification_family_on_the_mirror',
            'Degraded acquisition and expectation' => 'test_degraded_acquisition_family_on_the_mirror',
            'Temporal identity and status' => 'test_temporal_identity_and_status_family_on_the_mirror',
            'Corporate actions and indicators' => 'test_corporate_actions_and_indicators_family_on_the_mirror',
            'Correction and read path' => 'test_correction_and_read_path_family_on_the_mirror',
            'As-known isolation' => 'test_as_known_isolation_family_on_the_mirror',
        ];
    }

    /** The class is worthless if it silently runs on MariaDB (it would then prove nothing about the mirror), so the substrate is asserted. */
    public function test_these_scenarios_really_run_on_the_sqlite_mirror(): void
    {
        $db = $this->marketDataMariaDb();
        $this->assertSame('sqlite', $db->getDriverName(), 'these scenarios are not running on the SQLite mirror');
        $this->assertSame($this->mirrorFile, $db->getDatabaseName());
        $this->assertSame('wal', strtolower((string) $db->selectOne('PRAGMA journal_mode')->journal_mode), 'the mirror database is not in WAL mode, so a second connection cannot be a concurrent consumer');
        $this->assertSame('sqlite', config('database.default'), 'the production code under test would not be talking to the mirror');
        $this->assertSame('sqlite', DB::connection()->getDriverName());
        $this->assertFalse($this->isProductionEngine());
    }

    /** Each exception is one an explicit governance record grants, and its wording is read from that record, not from this class. */
    public function test_every_governed_exception_is_granted_by_a_governance_record_and_nothing_else_is_one(): void
    {
        $this->assertSame(['D001_ITEM4_REFERENTIAL_INTEGRITY'], array_keys(self::GOVERNED_EXCEPTIONS), 'a mirror exception exists that no governance record was shown to grant');
        foreach (self::GOVERNED_EXCEPTIONS as $id => $exception) {
            $record = (string) file_get_contents(base_path($exception['record']));
            $this->assertNotSame('', $record, $id.': the granting record is unreadable');
            $this->assertStringContainsString('- Status: `ISSUED — USER_APPROVED_BOUNDED_PACKAGE`', $record);
            $this->assertStringContainsString('- Mutability: `IMMUTABLE_AFTER_ISSUE`', $record);
            $this->assertStringContainsString($exception['wording'], preg_replace('/\s+/', ' ', $record), $id.': the record does not grant what the exception claims');
        }
        // the exception is bounded to foreign keys: it must not stand for any construct the record does not name
        $this->assertStringNotContainsString('trigger', strtolower(self::GOVERNED_EXCEPTIONS['D001_ITEM4_REFERENTIAL_INTEGRITY']['covers']));
        $this->assertStringNotContainsString('lock', strtolower(self::GOVERNED_EXCEPTIONS['D001_ITEM4_REFERENTIAL_INTEGRITY']['covers']));
        $this->assertStringNotContainsString('isolation', strtolower(self::GOVERNED_EXCEPTIONS['D001_ITEM4_REFERENTIAL_INTEGRITY']['covers']));
        // an undeclared id is refused outright
        $thrown = null;
        try {
            $this->governedException('ENGINE_ONLY_ANYTHING', static function () {});
        } catch (\LogicException $e) {
            $thrown = $e;
        }
        $this->assertNotNull($thrown, 'an author-invented exception category was accepted');
    }

    public function test_the_mirror_carries_the_nine_production_sealed_history_triggers_and_they_refuse_a_sealed_publication_only(): void
    {
        $db = $this->db();
        $production = $this->productionSealedHistoryTriggers();
        $installed = array_map(static function ($r) { return $r->name; }, $db->select("select name from sqlite_master where type = 'trigger' and name like 'trg_eod_%_sealed_immutable' order by name"));
        $expected = array_keys($production);
        sort($expected);
        $this->assertSame($expected, $installed, 'the mirror triggers are not exactly the production triggers');
        $now = '2026-03-23 10:00:00';
        $db->table('eod_publications')->insert(['publication_id' => 9101, 'trade_date' => '2026-03-23', 'run_id' => 9101, 'publication_version' => 1, 'seal_state' => 'UNSEALED', 'created_at' => $now]);
        $row = ['publication_id' => 9101, 'trade_date' => '2026-03-23', 'ticker_id' => 1, 'source' => 'X', 'created_at' => $now];
        $db->table('eod_bars_history')->insert($row);
        $this->assertSame(1, (int) $db->table('eod_bars_history')->where('publication_id', 9101)->count(), 'an unsealed publication must accept its snapshot rows');
        $db->table('eod_publications')->where('publication_id', 9101)->update(['seal_state' => 'SEALED']);
        foreach (['insert' => function () use ($db, $row) { $db->table('eod_bars_history')->insert(array_merge($row, ['ticker_id' => 2])); },
            'update' => function () use ($db) { $db->table('eod_bars_history')->where('publication_id', 9101)->update(['volume' => 7]); },
            'delete' => function () use ($db) { $db->table('eod_bars_history')->where('publication_id', 9101)->delete(); }] as $kind => $mutation) {
            $this->assertRefused($mutation, 'SEALED_PUBLICATION_IMMUTABLE', 'a sealed publication\'s snapshot row accepted the '.$kind);
        }
        $this->assertSame(1, (int) $db->table('eod_bars_history')->where('publication_id', 9101)->count());
    }

    public function test_the_mirror_enforces_the_production_seal_state_enum(): void
    {
        $this->assertSame(['SEALED', 'UNSEALED'], $this->productionSealStateEnum(), 'the production seal_state vocabulary changed; re-review the mirror');
        $db = $this->db();
        $now = '2026-03-23 10:00:00';
        $this->assertRefused(function () use ($db, $now) {
            $db->table('eod_publications')->insert(['publication_id' => 9102, 'trade_date' => '2026-03-23', 'run_id' => 9102, 'publication_version' => 1, 'seal_state' => 'NOT_A_SEAL_STATE', 'created_at' => $now]);
        }, 'Data truncated for column', 'a seal state outside the ENUM was inserted');
        $db->table('eod_publications')->insert(['publication_id' => 9102, 'trade_date' => '2026-03-23', 'run_id' => 9102, 'publication_version' => 1, 'seal_state' => 'UNSEALED', 'created_at' => $now]);
        $this->assertRefused(function () use ($db) {
            $db->table('eod_publications')->where('publication_id', 9102)->update(['seal_state' => 'NOT_A_SEAL_STATE']);
        }, 'Data truncated for column', 'a seal state outside the ENUM was written');
    }

    public function test_the_advisory_locks_follow_the_mariadb_contract(): void
    {
        $db = $this->db();
        $this->assertSame(1, (int) $db->selectOne('select get_lock(?, 1) as v', ['probe'])->v, 'a free name is acquired');
        $this->assertSame(1, (int) $db->selectOne('select get_lock(?, 1) as v', ['probe'])->v, 'the holder may take it again');
        $other = new \PDO('sqlite:'.$this->mirrorFile);
        $this->registerAdvisoryLocks($other);
        $this->assertSame(0, (int) $other->query("select get_lock('probe', 1)")->fetchColumn(), 'another connection must not take a held name');
        $this->assertSame(0, (int) $other->query("select release_lock('probe')")->fetchColumn(), 'only the holder may release');
        $this->assertSame(1, (int) $db->selectOne('select release_lock(?) as v', ['probe'])->v);
        $this->assertNull($db->selectOne('select release_lock(?) as v', ['probe'])->v, 'releasing a name nobody holds is NULL');
        $this->assertSame(1, (int) $other->query("select get_lock('probe', 1)")->fetchColumn(), 'a released name is free');
        $other->query("select release_lock('probe')")->fetchColumn();
    }

    public function test_a_second_connection_is_an_independent_consumer_that_reads_only_committed_state(): void
    {
        $db = $this->db();
        $config = config('database.connections.sqlite');
        config()->set('database.connections.market_data_mirror_reader', $config);
        $db->table('eod_current_publication_pointer')->insert(['trade_date' => '2026-03-27', 'publication_id' => 1, 'run_id' => 1, 'publication_version' => 1, 'updated_at' => '2026-03-27 10:00:00']);
        $reader = DB::connection('market_data_mirror_reader');
        $this->assertSame(1, (int) $reader->table('eod_current_publication_pointer')->where('trade_date', '2026-03-27')->value('publication_id'));
        $db->beginTransaction();
        $db->table('eod_current_publication_pointer')->where('trade_date', '2026-03-27')->update(['publication_id' => 2]);
        $this->assertSame(2, (int) $db->table('eod_current_publication_pointer')->where('trade_date', '2026-03-27')->value('publication_id'), 'the writer sees its own change');
        $this->assertSame(1, (int) $reader->table('eod_current_publication_pointer')->where('trade_date', '2026-03-27')->value('publication_id'), 'the independent consumer saw an uncommitted change');
        $db->commit();
        $this->assertSame(2, (int) $reader->table('eod_current_publication_pointer')->where('trade_date', '2026-03-27')->value('publication_id'), 'the independent consumer did not see the committed change');
    }

    public function test_the_mirror_reads_column_metadata_through_pragma_with_the_same_shape(): void
    {
        $columns = $this->columnMetadata(['eod_bars_history']);
        $this->assertArrayHasKey('traded_value_idr_actual', $columns['eod_bars_history']);
        $this->assertTrue($columns['eod_bars_history']['traded_value_idr_actual']['nullable']);
        $this->assertFalse($columns['eod_bars_history']['publication_id']['nullable'], 'a NOT NULL column must read as not nullable');
        $this->assertSame(['type', 'nullable'], array_keys($columns['eod_bars_history']['publication_id']));
    }

    // ---- the six families, on the mirror ------------------------------------------------------------------------------------------

    public function test_exact_publication_verification_family_on_the_mirror(): void
    {
        $this->runMirrorFamily('Exact publication verification');
    }

    public function test_degraded_acquisition_family_on_the_mirror(): void
    {
        $this->runMirrorFamily('Degraded acquisition and expectation');
    }

    public function test_temporal_identity_and_status_family_on_the_mirror(): void
    {
        $this->runMirrorFamily('Temporal identity and status');
    }

    public function test_corporate_actions_and_indicators_family_on_the_mirror(): void
    {
        $this->runMirrorFamily('Corporate actions and indicators');
    }

    public function test_correction_and_read_path_family_on_the_mirror(): void
    {
        $this->runMirrorFamily('Correction and read path');
    }

    public function test_as_known_isolation_family_on_the_mirror(): void
    {
        $this->runMirrorFamily('As-known isolation');
    }
}
