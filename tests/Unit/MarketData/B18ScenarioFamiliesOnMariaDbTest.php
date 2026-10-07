<?php

use App\Application\MarketData\Services\AdjustmentFactorSetService;
use App\Application\MarketData\Services\AnalyticalPriceProductService;
use App\Application\MarketData\Services\AsKnownReplaySnapshotService;
use App\Application\MarketData\Services\CoverageGateEvaluator;
use App\Application\MarketData\Services\EodBarsIngestService;
use App\Application\MarketData\Services\FinalizeDecisionService;
use App\Application\MarketData\Services\IndicatorVectorService;
use App\Infrastructure\MarketData\Source\LocalFileEodBarsAdapter;
use App\Infrastructure\MarketData\Source\PublicApiEodBarsAdapter;
use App\Infrastructure\MarketData\Source\SourceAcquisitionException;
use App\Infrastructure\Persistence\MarketData\EodArtifactRepository;
use App\Infrastructure\Persistence\MarketData\EodEvidenceRepository;
use App\Infrastructure\Persistence\MarketData\EodPublicationRepository;
use App\Infrastructure\Persistence\MarketData\EodRunRepository;
use App\Infrastructure\Persistence\MarketData\MarketCalendarRepository;
use App\Infrastructure\Persistence\MarketData\MarketDataConfigSnapshotRepository;
use App\Infrastructure\Persistence\MarketData\SourceObservationRepository;
use App\Infrastructure\Persistence\MarketData\TemporalIdentityRepository;
use App\Infrastructure\Persistence\MarketData\TemporalTradingStatusRepository;
use App\Infrastructure\Persistence\MarketData\TickerMasterRepository;
use App\Models\EodRun;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use PHPUnit\Framework\Assert;
use Tests\Support\MocksProducerInputCapture;
use Tests\Support\UsesMarketDataMariaDb;

require_once __DIR__.'/../../Support/B18ScenarioFamilyBase.php';

/**
 * `MD-B18-A002` -- `MD-S003-R0025`.
 *
 * > All required scenario families pass on MariaDB production semantics **and** the supported test
 * > mirror. Any missing family remains an open proof gap; historical green results for superseded
 * > rules do not close it.
 *
 * This class is the MariaDB half. The supported-test-mirror half is `B18ScenarioFamiliesOnMirrorTest`, which runs the same scenarios (written once in
 * `B18ScenarioFamilyBase`) on the in-memory SQLite mirror. It is bound to the *bullets* of the six families, not to their
 * headings: the contract's `### ` headings name six families, and each family is a list of
 * behaviours ("resolve an explicit immutable publication, not latest/current"; "concurrent
 * consumers read exactly one publication"). A family that "passes" on MariaDB by exercising one
 * of its bullets has proved that bullet, so `bulletMap()` names every bullet, is parsed against the
 * contract, and maps each to a scenario. Adding, dropping or rewording a bullet fails here.
 *
 * Every scenario runs the production repositories and services -- the same ones the mirror guards
 * use -- against the migrated MariaDB schema. Nothing here restates a production query. Two kinds
 * of bullet exist, and each says which it is:
 *
 *  - **Engine-dependent.** The behaviour is decided by a query, a constraint, a trigger or a
 *    transaction, which is exactly where a mirror can agree with production until it does not:
 *    `datetime` comparison, `NULL` ordering in supersession joins, decimal storage, enforced
 *    unique/foreign-key/`NOT NULL` constraints, the sealed-immutability triggers, transaction
 *    isolation. Those run against MariaDB and assert the behaviour.
 *  - **Engine-independent.** The production code that decides the bullet performs no database
 *    access (the hold decision on a stale row, the Wilder recursion, the structural price
 *    product). Its mirror guard already proves it, and MariaDB cannot change it. That is not left
 *    as an assumption: the scenario executes the production code with the MariaDB connection as the
 *    default and asserts that it issued **zero queries**. If a later change makes it read the
 *    database, the assertion fails and the bullet must be treated as engine-dependent.
 *
 * The exact-publication member uses the production capture/bind/seal path and verifies the executed target of the frozen synthetic V2 world against the
 * independently derived, independently reviewed and owner-approved candidate-v4 (`D-MD-B18-A002-016`, `E-MD-B18-A002-101`); a fixture generated from the
 * run under verification is refused, whatever it matches.
 */
class B18ScenarioFamiliesOnMariaDbTest extends B18ScenarioFamilyBase
{
    use UsesMarketDataMariaDb;

    protected function bootSubstrate(): void
    {
        $this->bootMarketDataMariaDb();
    }

    protected function tearDownSubstrate(): void
    {
        $this->tearDownMarketDataMariaDb();
    }

    protected function markSubstrateTransactionEnded(): void
    {
        $this->marketDataMariaDbStarted = false;
    }

    public function test_the_family_map_names_exactly_the_families_the_contract_requires(): void
    {
        $headings = array_keys($this->contractBullets());
        $mapped = array_keys($this->familyMap());
        sort($headings);
        sort($mapped);

        $this->assertSame($headings, $mapped,
            'MD-S003 and the reviewed family map disagree about which scenario families exist');
    }

    public function test_every_family_names_a_test_that_exists_in_this_class(): void
    {
        $source = (string) file_get_contents(__FILE__);
        $missing = [];

        foreach ($this->familyMap() as $family => $method) {
            if (strpos($source, 'function '.$method.'(') === false) {
                $missing[] = $family.' -> '.$method;
            }
        }

        $this->assertSame([], $missing, 'these families have no MariaDB scenario');
    }

    /**
     * The bullet map is the contract, bullet for bullet. This is what stops "the family passes"
     * from meaning "one bullet of the family passes": a bullet added, dropped or reworded fails
     * here, and so does a family whose bullet list is empty.
     */
    public function test_the_bullet_map_names_exactly_the_bullets_the_contract_names(): void
    {
        $contract = $this->contractBullets();
        $mapped = array_map('array_keys', $this->bulletMap());

        $this->assertSame(array_keys($contract), array_keys($mapped), 'families differ or are reordered');
        foreach ($contract as $family => $bullets) {
            $this->assertNotSame([], $bullets, $family.' has no bullets, so nothing constrains what "passes" means');
            $this->assertSame($bullets, $mapped[$family], $family.': the reviewed bullet map and MD-S003 disagree');
        }

        $this->assertSame(21, array_sum(array_map('count', $contract)), 'MD-S003 no longer names 21 family bullets');
    }

    /**
     * Every scenario exists, is mapped exactly once, and every method named `s_*` is in the map, so
     * a scenario cannot be written and left unexecuted, nor named and left unwritten.
     */
    public function test_every_bullet_names_one_existing_scenario_and_every_scenario_is_mapped_once(): void
    {
        $source = (string) file_get_contents(__FILE__);
        preg_match_all('/protected function (s_[a-z0-9_]+)\(/', (string) file_get_contents((new \ReflectionClass(B18ScenarioFamilyBase::class))->getFileName()), $m);
        $written = $m[1];

        $mapped = [];
        foreach ($this->bulletMap() as $bullets) {
            foreach ($bullets as $bullet => [$scenario, $kind]) {
                $this->assertContains($kind, ['DB', 'NO_DB'], $bullet);
                $mapped[] = $scenario;
            }
        }

        $this->assertSame([], array_values(array_diff($mapped, $written)), 'a bullet names a scenario that does not exist');
        $this->assertSame([], array_values(array_diff($written, $mapped)), 'a scenario is written but no bullet runs it');
        $this->assertSame(count($mapped), count(array_unique($mapped)), 'a scenario is mapped to more than one bullet');
    }

    /**
     * Each family test runs its whole family from the map, so the family is the bullets and a
     * family test cannot quietly run a subset.
     */
    public function test_every_family_test_runs_its_own_family_through_the_bullet_map(): void
    {
        $source = (string) file_get_contents(__FILE__);
        foreach ($this->familyMap() as $family => $method) {
            $this->assertSame(1, preg_match(
                '/function '.preg_quote($method, '/').'\(\): void\s*\{\s*\$this->runFamily\(\''.preg_quote($family, '/').'\'\);\s*\}/',
                $source
            ), $method.' must be exactly runFamily(\''.$family.'\')');
        }
    }

    /**
     * The whole class is worthless if it silently runs on the mirror, so the substrate is asserted
     * rather than assumed.
     */
    public function test_these_scenarios_really_run_on_mariadb(): void
    {
        $driver = $this->marketDataMariaDb()->getDriverName();
        $this->assertSame('mysql', $driver, 'these scenarios are not running on MariaDB');

        $version = $this->marketDataMariaDb()->select('select version() as v')[0]->v;
        $this->assertStringContainsStringIgnoringCase('mariadb', $version,
            'the connection is MySQL-family but not MariaDB, so this is not the production engine');

        $this->assertSame(
            config('database.connections.'.$this->marketDataMariaDbConnection.'.database'),
            $this->marketDataMariaDb()->getDatabaseName()
        );

        // The production repositories resolve the *default* connection, so it must be this one.
        $this->assertSame($this->marketDataMariaDbConnection, config('database.default'),
            'the production code under test would not be talking to the MariaDB connection');
        $this->assertSame('mysql', DB::connection()->getDriverName());
    }

    /**
     * The mirror is only as equivalent as its derivation source is true. The mirror installs the sealed-history triggers and the seal_state ENUM from the PRODUCTION
     * migrations; this guard proves, against the MariaDB schema that production actually runs, that what the mirror derives is what MariaDB has: the nine triggers (name,
     * table, timing, event, condition, message) and the ENUM.
     */
    public function test_the_mirror_derivation_sources_equal_what_production_defines(): void
    {
        $db = $this->marketDataMariaDb();
        $normalize = static function ($sql) { return strtolower(preg_replace('/[\s`()]+/', '', (string) $sql)); };
        foreach ($this->productionSealedHistoryTriggers() as $name => [$table, $event, $condition, $message]) {
            $row = $db->selectOne('select event_manipulation e, event_object_table t, action_timing a, action_statement s from information_schema.triggers where trigger_schema = ? and trigger_name = ?', [$db->getDatabaseName(), $name]);
            $this->assertNotNull($row, $name.' is not a trigger of the MariaDB schema');
            $this->assertSame([$event, $table, 'BEFORE'], [$row->e, $row->t, $row->a], $name.': the migration and the schema disagree about the trigger');
            // the WHOLE guard clause, condition through message, not a prefix of it: a trivially true condition that merely starts like the real one must not pass
            $clause = 'IF '.$condition." THEN SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = '".$message."'";
            $this->assertStringContainsString($normalize($clause), $normalize($row->s), $name.': the condition and message the mirror derives are not the ones MariaDB runs');
        }
        $type = (string) $db->selectOne('select column_type t from information_schema.columns where table_schema = ? and table_name = ? and column_name = ?', [$db->getDatabaseName(), 'eod_publications', 'seal_state'])->t;
        $this->assertSame(strtolower("enum('".implode("','", $this->productionSealStateEnum())."')"), strtolower($type), 'the ENUM the mirror derives is not the production column type');
    }
    // ---- the six families ------------------------------------------------------------------------

    public function test_exact_publication_verification_family_on_mariadb(): void
    {
        $this->runFamily('Exact publication verification');
    }

    public function test_degraded_acquisition_family_on_mariadb(): void
    {
        $this->runFamily('Degraded acquisition and expectation');
    }

    public function test_temporal_identity_and_status_family_on_mariadb(): void
    {
        $this->runFamily('Temporal identity and status');
    }

    public function test_corporate_actions_and_indicators_family_on_mariadb(): void
    {
        $this->runFamily('Corporate actions and indicators');
    }

    public function test_correction_and_read_path_family_on_mariadb(): void
    {
        $this->runFamily('Correction and read path');
    }

    public function test_as_known_isolation_family_on_mariadb(): void
    {
        $this->runFamily('As-known isolation');
    }

}
