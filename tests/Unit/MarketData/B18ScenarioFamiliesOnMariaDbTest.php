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

/**
 * `MD-B18-A002` -- `MD-S003-R0025`.
 *
 * > All required scenario families pass on MariaDB production semantics **and** the supported test
 * > mirror. Any missing family remains an open proof gap; historical green results for superseded
 * > rules do not close it.
 *
 * The mirror half was already true: every DB-backed market-data guard runs on the SQLite mirror.
 * This class is the MariaDB half. It is bound to the *bullets* of the six families, not to their
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
 * The exact-publication member uses the production capture/bind/seal path. Its final replay
 * verification remains fail-closed until an independent expected fixture can be supplied: the
 * production verifier correctly blocks an expectation generated from the same run.
 */
class B18ScenarioFamiliesOnMariaDbTest extends TestCase
{
    use UsesMarketDataMariaDb;
    use MocksProducerInputCapture;

    private const CONTRACT = 'docs/market_data/authority/strategy/backtest/Historical_Replay_and_Data_Quality_Backtest.md';

    private const TRADE_DATE = '2026-03-24';

    private const CUTOFF_EARLY = '2026-04-15 00:00:00';

    private const CUTOFF_LATE = '2026-06-15 00:00:00';

    /** When later revisions entered the record: between the two cutoffs under test. */
    private const LATE_RECORDED_AT = '2026-05-01 09:00:00';

    /** @var array<int,string>|null SQL issued while a scenario asserts "no database access" */
    private $capturing = null;

    /** @var int distinguishes rows a family's scenarios create inside one test transaction */
    private $seq = 0;

    /** @var array<string,array<string,mixed>> */
    private $fixtures = [];

    private $originalMarketDataConfig;

    /** @var string|null Exact test-owned source fixture for the production capture path. */
    private $capturedSourceFile;

    /** @var string|null Exact test-owned generated fixture used by the real replay verifier. */
    private $capturedReplayDir;

    /** @var array<string,mixed>|null Reuse the one real publication across exact-family bullets. */
    private $capturedPublication;

    protected function setUp(): void
    {
        parent::setUp();
        $this->bootMarketDataMariaDb();
        $this->originalMarketDataConfig = config('market_data');
        $this->marketDataMariaDb()->listen(function ($query) {
            if ($this->capturing !== null) {
                $this->capturing[] = $query->sql;
            }
        });
    }

    protected function tearDown(): void
    {
        if ($this->capturedReplayDir !== null) {
            foreach (['expected/expected_reason_code_counts.json', 'expected/expected_replay_result.json', 'manifest.json'] as $name) {
                @unlink($this->capturedReplayDir.DIRECTORY_SEPARATOR.str_replace('/', DIRECTORY_SEPARATOR, $name));
            }
            @rmdir($this->capturedReplayDir.DIRECTORY_SEPARATOR.'expected');
            @rmdir($this->capturedReplayDir);
        }
        if ($this->capturedSourceFile !== null) {
            @unlink($this->capturedSourceFile);
            @rmdir(dirname($this->capturedSourceFile));
        }
        config(['market_data' => $this->originalMarketDataConfig]);
        Carbon::setTestNow();
        $this->tearDownMarketDataMariaDb();
        parent::tearDown();
    }

    /**
     * The families are read from the contract, so a family added to `MD-S003` with nothing
     * exercising it on MariaDB fails here rather than leaving "all required scenario families"
     * quietly meaning the six that happened to be written down.
     *
     * @return array<string,string> contract heading => the test that exercises it on MariaDB
     */
    private function familyMap(): array
    {
        return [
            'Exact publication verification' => 'test_exact_publication_verification_family_on_mariadb',
            'Degraded acquisition and expectation' => 'test_degraded_acquisition_family_on_mariadb',
            'Temporal identity and status' => 'test_temporal_identity_and_status_family_on_mariadb',
            'Corporate actions and indicators' => 'test_corporate_actions_and_indicators_family_on_mariadb',
            'Correction and read path' => 'test_correction_and_read_path_family_on_mariadb',
            'As-known isolation' => 'test_as_known_isolation_family_on_mariadb',
        ];
    }

    /**
     * Each bullet of each family, exactly as the contract words it, mapped to the scenario that
     * proves it on MariaDB and to what kind of bullet it is (`DB`: decided by the engine;
     * `NO_DB`: decided by code that touches no database, asserted by counting queries).
     *
     * @return array<string,array<string,array{0:string,1:string}>>
     */
    private function bulletMap(): array
    {
        return [
            'Exact publication verification' => [
                'resolve an explicit immutable publication, not latest/current;' => ['s_f1_explicit_publication', 'DB'],
                'verify frozen observations, temporal revisions, config, factors, formulas, artifacts, hashes, manifest, seal, reasons, and terminal state;' => ['s_f1_frozen_inputs_and_seal', 'DB'],
                'prove an unchanged rerun is byte-identical and does not create a fake correction.' => ['s_f1_unchanged_rerun', 'DB'],
            ],
            'Degraded acquisition and expectation' => [
                'provider outage remains missing delivery and cannot shrink the denominator;' => ['s_f2_outage_denominator', 'DB'],
                'unknown expectation does not become holiday/dormancy;' => ['s_f2_unknown_expectation', 'DB'],
                'stale/schema-invalid/wrong-date/zero-price observations quarantine or hold;' => ['s_f2_quarantine_or_hold', 'DB'],
                'no prior-date result masquerades as requested-date fresh data.' => ['s_f2_no_prior_date_masquerade', 'DB'],
            ],
            'Temporal identity and status' => [
                'inactive-now/active-then listing remains in the historical universe;' => ['s_f3_inactive_now_active_then', 'DB'],
                'symbol change and symbol reuse resolve through stable listing identity;' => ['s_f3_symbol_change_and_reuse', 'DB'],
                'calendar/session/status revisions respect effective and knowledge time.' => ['s_f3_calendar_session_status_times', 'DB'],
            ],
            'Corporate actions and indicators' => [
                'synthetic price-break candidates never activate factors;' => ['s_f4_synthetic_never_activates', 'DB'],
                'verified event/factor revision produces coherent structural OHLC/volume;' => ['s_f4_verified_revision_coherent_structural', 'DB'],
                'provider adjusted-close fallback is impossible;' => ['s_f4_no_provider_adjusted_close', 'NO_DB'],
                'long-chain Wilder ATR matches an independent oracle, including a correction whose impact continues beyond fourteen sessions;' => ['s_f4_long_chain_atr', 'NO_DB'],
                'actual traded value and close-volume proxy never share meaning or field identity.' => ['s_f4_actual_value_and_proxy_identity', 'DB'],
            ],
            'Correction and read path' => [
                'prior immutable publication remains auditable;' => ['s_f5_prior_publication_auditable', 'DB'],
                'a distinct corrected candidate becomes active only after complete validation and reseal;' => ['s_f5_active_only_after_validation_and_reseal', 'DB'],
                'concurrent consumers read exactly one publication;' => ['s_f5_concurrent_consumers', 'DB'],
                'explicit fallback retains prior effective date and stale/degraded state.' => ['s_f5_explicit_fallback', 'DB'],
            ],
            'As-known isolation' => [
                'later master, event, status, calendar, config, formula, and factor revisions are invisible before their recorded/known times;' => ['s_f6_later_revisions_invisible', 'DB'],
                'a declared later cutoff can expose them without rewriting earlier replay evidence.' => ['s_f6_later_cutoff_exposes_without_rewriting', 'DB'],
            ],
        ];
    }

    /** @return array<string,array<int,string>> contract heading => its bullets, as written */
    private function contractBullets(): array
    {
        $path = dirname(__DIR__, 3).'/'.self::CONTRACT;
        $this->assertFileExists($path);
        $source = (string) file_get_contents($path);

        $start = strpos($source, '## Required scenario families');
        $this->assertNotFalse($start, 'the MD-S003 required-families heading moved; re-read the contract');
        $end = strpos($source, "\n## ", $start + 5);
        $block = substr($source, $start, $end === false ? null : $end - $start);

        $families = [];
        $current = null;
        foreach (preg_split('/\R/', $block) as $line) {
            $line = trim($line);
            if (strpos($line, '### ') === 0) {
                $current = trim(substr($line, 4));
                $families[$current] = [];
            } elseif ($current !== null && strpos($line, '- ') === 0) {
                $families[$current][] = trim(substr($line, 2));
            }
        }

        return $families;
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
        preg_match_all('/private function (s_[a-z0-9_]+)\(/', $source, $m);
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

    /** Runs every bullet scenario of one family, naming the bullet whose scenario fails. */
    private function runFamily(string $family): void
    {
        $bullets = $this->bulletMap()[$family];
        $this->assertNotSame([], $bullets);
        // The family passes only if it is the contract's bullets: a bullet added, dropped or reworded fails the
        // family test itself, not only a separate structure test.
        $this->assertSame($this->contractBullets()[$family] ?? null, array_keys($bullets),
            $family.': the reviewed bullet map and MD-S003 disagree');

        // A scenario that commits its rows ends the test's wrapping transaction, so it runs after every
        // other scenario of the family: nothing that follows it could be rolled back.
        $committing = ['s_f5_concurrent_consumers'];
        uasort($bullets, function ($x, $y) use ($committing) {
            return (int) in_array($x[0], $committing, true) <=> (int) in_array($y[0], $committing, true);
        });

        foreach ($bullets as $bullet => [$scenario, $kind]) {
            $before = Assert::getCount();
            try {
                $this->$scenario();
                // A scenario emptied of its assertions would leave the family green; each must really assert.
                $this->assertGreaterThanOrEqual(3, Assert::getCount() - $before,
                    $scenario.' asserted too little to prove "'.$bullet.'"');
            } catch (\PHPUnit\Framework\ExpectationFailedException $e) {
                throw new \PHPUnit\Framework\AssertionFailedError(
                    '['.$family.' / '.$scenario.'] "'.$bullet.'": '.$e->getMessage(), 0, $e
                );
            }
        }
    }

    // ---- shared helpers --------------------------------------------------------------------------

    /**
     * Runs production code and returns its result with the SQL it issued. Used where the bullet is
     * decided by code that must not touch the database: the count is the proof that MariaDB cannot
     * change the outcome.
     *
     * @return array{0:mixed,1:array<int,string>}
     */
    private function queriesDuring(callable $fn): array
    {
        $this->capturing = [];
        try {
            $result = $fn();
        } finally {
            $queries = $this->capturing;
            $this->capturing = null;
        }

        return [$result, $queries];
    }

    private function next(): int
    {
        return ++$this->seq;
    }

    /** @return array<string,mixed> */
    private function runRow(int $id, string $date, array $extra = []): array
    {
        return array_merge([
            'run_id' => $id, 'trade_date_requested' => $date, 'trade_date_effective' => $date,
            'lifecycle_state' => 'COMPLETED', 'quality_gate_state' => 'PASS', 'stage' => 'FINALIZE', 'source' => 'manual_file',
            'terminal_status' => 'SUCCESS', 'publishability_state' => 'READABLE', 'coverage_gate_state' => 'PASS',
            'coverage_universe_count' => 100, 'coverage_available_count' => 100, 'coverage_missing_count' => 0, 'coverage_ratio' => 1.0,
            'coverage_min_threshold' => 0.98, 'coverage_threshold_mode' => 'MIN_RATIO',
            'coverage_universe_basis' => 'ACTIVE_TICKER_MASTER_FOR_TRADE_DATE', 'coverage_contract_version' => 'coverage_gate_v1',
            'bars_rows_written' => 2, 'indicators_rows_written' => 2, 'eligibility_rows_written' => 2,
            'price_product_code' => 'STRUCTURAL_ADJUSTED', 'price_product_version' => 'structural_adjusted_v1',
            'started_at' => $date.' 17:00:00', 'created_at' => $date.' 17:00:00', 'updated_at' => $date.' 17:20:00',
        ], $extra);
    }

    /**
     * A trade date with a sealed, current, readable publication and a second run whose corrected
     * candidate is still to be built. Rows use fixed ids in a reserved range so a leaked row is
     * identifiable; the surrounding transaction is rolled back.
     *
     * @return array<string,mixed>
     */
    private function priorCurrent(string $date, int $base, ?\Illuminate\Database\Connection $db = null): array
    {
        $db = $db ?: $this->marketDataMariaDb();
        $priorRun = $base + 1;
        $newRun = $base + 2;
        $priorPub = $base + 3;

        $db->table('eod_runs')->insert($this->runRow($priorRun, $date, [
            'config_version' => 'cfg-old', 'publication_version' => 1, 'is_current_publication' => 1,
            'factor_set_hash' => hash('sha256', 'r25-old'), 'sealed_at' => $date.' 17:20:00', 'started_at' => $date.' 17:00:00',
        ]));
        $db->table('eod_publications')->insert([
            'publication_id' => $priorPub, 'trade_date' => $date, 'run_id' => $priorRun, 'publication_version' => 1, 'is_current' => 1,
            'seal_state' => 'SEALED', 'bars_batch_hash' => hash('sha256', 'bars-old'), 'indicators_batch_hash' => hash('sha256', 'ind-old'),
            'eligibility_batch_hash' => hash('sha256', 'elig-old'), 'price_product_code' => 'STRUCTURAL_ADJUSTED',
            'price_product_version' => 'structural_adjusted_v1', 'factor_set_hash' => hash('sha256', 'r25-old'),
            'sealed_at' => $date.' 17:20:00', 'created_at' => $date.' 17:20:00', 'updated_at' => $date.' 17:20:00',
        ]);
        $db->table('eod_runs')->where('run_id', $priorRun)->update(['publication_id' => $priorPub]);
        $db->table('eod_runs')->insert($this->runRow($newRun, $date, [
            'config_version' => 'cfg-new', 'knowledge_cutoff_at' => $date.' 18:00:00', 'is_current_publication' => 0,
            'factor_set_hash' => hash('sha256', 'r25-new'), 'sealed_at' => $date.' 17:21:00', 'started_at' => $date.' 17:01:00',
        ]));
        $db->table('eod_current_publication_pointer')->insert([
            'trade_date' => $date, 'publication_id' => $priorPub, 'run_id' => $priorRun, 'publication_version' => 1,
            'sealed_at' => $date.' 17:20:00', 'updated_at' => $date.' 17:20:00',
        ]);

        return ['date' => $date, 'prior_run' => $priorRun, 'new_run' => $newRun, 'prior_pub' => $priorPub];
    }

    /**
     * Builds the corrected candidate through the production repository, up to but not including the
     * seal: candidate publication, hashes, analytical product, governance and lineage bindings, the
     * history snapshot and the deterministic manifest.
     *
     * @param array<string,mixed> $fx
     * @return array<string,mixed>
     */
    private function buildCandidate(array $fx, ?\Illuminate\Database\Connection $db = null): array
    {
        $db = $db ?: $this->marketDataMariaDb();
        $d = $fx['date'];
        $repo = new EodPublicationRepository();
        $run = EodRun::query()->findOrFail($fx['new_run']);
        $candidate = $repo->getOrCreateCandidatePublication($run, $fx['prior_pub']);
        $repo->updateCandidateHashes($candidate->publication_id, [
            'bars_batch_hash' => hash('sha256', 'bars-new'), 'indicators_batch_hash' => hash('sha256', 'ind-new'),
            'eligibility_batch_hash' => hash('sha256', 'elig-new'),
        ]);

        $factorHash = hash('sha256', 'r25-new');
        $cfgId = (int) $db->table('md_config_snapshots')->insertGetId([
            'snapshot_uid' => hash('sha256', 'r25-config'.$candidate->publication_id),
            'snapshot_schema_version' => (string) config('market_data.governance.config_snapshot_schema_version', 'market_data_config_snapshot_v1'),
            'serialization_version' => 'canonical_json_v1', 'resolved_config_json' => '{}',
            'config_hash' => hash('sha256', 'test-config-snapshot'), 'registry_revision' => 'r25-'.$d,
            'effective_at' => $d.' 17:00:00', 'recorded_at' => $d.' 17:00:00', 'build_id' => 'test-build',
            'environment_profile' => (string) config('market_data.governance.environment_profile', 'local'),
            'resolver_version' => 'test-resolver-v1', 'created_at' => $d.' 17:00:00',
        ]);
        $factorSetId = (int) $db->table('md_adjustment_factor_sets')->insertGetId([
            'factor_set_uid' => hash('sha256', 'r25-fsuid'.$candidate->publication_id), 'price_product_code' => 'STRUCTURAL_ADJUSTED',
            'factor_formula_version' => 'structural_factor_product_v1', 'config_snapshot_id' => $cfgId, 'state' => 'BOUND',
            'content_hash' => $factorHash, 'recorded_at' => $d.' 17:10:00', 'created_at' => $d.' 17:10:00',
        ]);
        $obs = hash('sha256', 'test-observation-manifest');
        $db->table('eod_runs')->where('run_id', $fx['new_run'])->update(['config_snapshot_id' => $cfgId, 'observation_manifest_hash' => $obs]);
        $repo->bindCandidateAnalyticalProduct($candidate->publication_id, $fx['new_run'], 'STRUCTURAL_ADJUSTED', 'structural_adjusted_v1', $factorHash, $factorSetId);

        $sourceScale = hash('sha256', 'test-source-scale');
        $market = hash('sha256', 'test-market-structure');
        $fdec = hash('sha256', 'test-factor-decisions');
        $db->table('eod_publications')->where('publication_id', $candidate->publication_id)->update([
            'source_scale_assessment_set_hash' => $sourceScale, 'market_structure_revision_set_hash' => $market,
            'factor_decision_set_hash' => $fdec, 'config_snapshot_id' => $cfgId, 'observation_manifest_hash' => $obs,
        ]);
        $ctx = json_encode(['schema_version' => 'md_publication_inputs_v2', 'scope' => ['config_snapshot_id' => $cfgId],
            'components' => [], 'component_manifest' => ['status' => 'COMPLETE']]);
        $db->table('md_publication_lineage_bindings')->insert([
            'publication_id' => $candidate->publication_id, 'config_snapshot_id' => $cfgId, 'factor_set_id' => $factorSetId,
            'observation_manifest_hash' => $obs, 'identity_revision_set_hash' => hash('sha256', 'test-identity'),
            'calendar_revision_set_hash' => hash('sha256', 'test-calendar'), 'status_revision_set_hash' => hash('sha256', 'test-status'),
            'event_revision_set_hash' => hash('sha256', 'test-event'), 'source_scale_assessment_set_hash' => $sourceScale,
            'market_structure_revision_set_hash' => $market, 'factor_decision_set_hash' => $fdec,
            'formula_version' => 'eod_indicators_v1', 'build_id' => 'test-build', 'read_model_version' => 'market_data_read_product_v1',
            'bound_input_schema_version' => 'md_publication_inputs_v2', 'bound_input_context_json' => $ctx,
            'bound_input_context_hash' => hash('sha256', $ctx), 'bound_input_capture_manifest_json' => '{}',
            'created_at' => $d.' 17:10:00',
        ]);
        $pub = $db->table('eod_publications')->where('publication_id', $candidate->publication_id)->first();
        $db->table('eod_runs')->where('run_id', $fx['new_run'])->update([
            'bars_batch_hash' => $pub->bars_batch_hash, 'indicators_batch_hash' => $pub->indicators_batch_hash,
            'eligibility_batch_hash' => $pub->eligibility_batch_hash,
        ]);
        // MariaDB declares the OHLC columns of a history row NOT NULL with no default; the mirror does not.
        $db->table('eod_bars_history')->insert([
            'publication_id' => $candidate->publication_id, 'trade_date' => $d, 'ticker_id' => 999999,
            'open' => 100, 'high' => 110, 'low' => 95, 'close' => 105, 'volume' => 1000, 'source' => 'MANUAL_FILE',
            'run_id' => $fx['new_run'], 'canonicalization_version' => 'eod_canonical_v1', 'price_product_code' => 'RAW',
            'quality_state' => 'VALIDATED', 'created_at' => $d.' 17:10:00',
        ]);
        $repo->prepareCandidateManifestForSeal(EodRun::query()->findOrFail($fx['new_run']), $candidate->publication_id);

        return $fx + ['candidate_pub' => (int) $candidate->publication_id, 'config_id' => $cfgId, 'factor_set_id' => $factorSetId];
    }

    /**
     * A publication whose input components are captured by the real producers, bound during HASH,
     * and verified during SEAL. Source rows and calendar revisions are fixture inputs; the test
     * does not write a capture, binding, completion marker, or publication manifest itself.
     *
     * @return array<string,mixed>
     */
    private function capturedAndSealedPublication(): array
    {
        if ($this->capturedPublication !== null) return $this->capturedPublication;
        $date = '2026-03-23';
        $db = $this->marketDataMariaDb();
        Carbon::setTestNow('2026-03-25 10:30:00');
        $db->table('tickers')->insert([
            'ticker_id' => 975000, 'ticker_code' => 'R25X', 'company_name' => 'R0025 fixture', 'is_active' => 1,
            'listed_date' => '2020-01-01', 'delisted_date' => null,
            'created_at' => '2020-01-01 00:00:00', 'updated_at' => '2020-01-01 00:00:00',
        ]);
        (new TemporalIdentityRepository())->ensureLegacyProjection();
        for ($day = new \DateTimeImmutable('2026-02-20'); $day <= new \DateTimeImmutable($date); $day = $day->modify('+1 day')) {
            $d = $day->format('Y-m-d');
            $trading = (int) $day->format('N') <= 5;
            $db->table('md_market_calendar_revisions')->updateOrInsert(
                ['market_code' => 'IDX', 'market_segment' => 'REGULAR', 'cal_date' => $d],
                [
                    'revision_uid' => hash('sha256', 'r25-calendar|'.$d), 'timezone' => 'Asia/Jakarta',
                    'is_trading_day' => $trading ? 1 : 0, 'is_half_day' => 0,
                    'session_state' => $trading ? 'COMPLETED' : 'CLOSED',
                    'session_open_at' => $trading ? $d.' 09:00:00' : null,
                    'session_close_at' => $trading ? $d.' 16:00:00' : null,
                    'completed_at' => $trading ? $d.' 16:00:00' : null,
                    'recorded_at' => $d.' 17:00:00', 'source_observation_id' => null,
                    'supersedes_revision_id' => null, 'source_ref' => 'https://www.idx.co.id/test-calendar/'.$d,
                    'source_version' => 'idx-test-calendar-v1', 'provenance_tier' => 'VERIFIED',
                    'reconciled_at' => $d.' 17:00:00',
                    'reconciliation_source_ref' => 'https://www.idx.co.id/test-calendar/'.$d,
                ]
            );
        }
        $directory = storage_path('framework/testing/r0025-captured-publication');
        if (! is_dir($directory)) mkdir($directory, 0777, true);
        $sourceFile = $directory.DIRECTORY_SEPARATOR.$date.'.json';
        $this->assertFileDoesNotExist($sourceFile, 'the captured-publication source fixture path is occupied');
        $this->capturedSourceFile = $sourceFile;
        file_put_contents($this->capturedSourceFile, json_encode([[
            'ticker_code' => 'R25X', 'trade_date' => $date,
            'open' => 100, 'high' => 110, 'low' => 95, 'close' => 105,
            'volume' => 1000, 'captured_at' => '2026-03-23T17:20:00+07:00',
        ]], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
        config()->set('market_data.source.local_directory', 'storage/framework/testing/r0025-captured-publication');
        config()->set('market_data.source.file_template_json', '{date}.json');
        config()->set('market_data.source.file_template_csv', '{date}.csv');
        try {
            $run = app(\App\Application\MarketData\Services\MarketDataPipelineService::class)->runDaily($date, 'manual_file');
        } catch (SourceAcquisitionException $e) {
            throw new \RuntimeException('captured publication acquisition: '.json_encode($e->context()), 0, $e);
        }
        $publication = $db->table('eod_publications')->where('run_id', $run->run_id)->first();
        $this->assertNotNull($publication, 'the production pipeline created no publication');
        $this->assertSame('SEALED', (string) $publication->seal_state, 'capture, bind and seal did not finish');

        return $this->capturedPublication = [
            'date' => $date, 'new_run' => (int) $run->run_id,
            'candidate_pub' => (int) $publication->publication_id,
            'config_id' => (int) $publication->config_snapshot_id,
            'factor_set_id' => (int) $publication->factor_set_id,
        ];
    }

    /** @param array<string,mixed> $fx @return array<string,mixed> */
    private function sealCandidate(array $fx): array
    {
        $repo = new EodPublicationRepository();
        $sealed = $repo->sealCandidatePublication(EodRun::query()->findOrFail($fx['new_run']), 'system');
        $this->assertSame('SEALED', $sealed->seal_state);

        return $fx;
    }

    /** @param array<string,mixed> $fx @return array<string,mixed> */
    private function promoteCandidate(array $fx): array
    {
        $repo = new EodPublicationRepository();
        $promoted = $repo->promoteCandidateToCurrent(EodRun::query()->findOrFail($fx['new_run']), $fx['prior_pub']);
        $this->assertSame(1, (int) $promoted->is_current);

        return $fx;
    }

    /**
     * A correction that was built, sealed and promoted by the production repository. One per
     * (date, base) per test, so the bullets of a family share it instead of rebuilding it.
     *
     * @return array<string,mixed>
     */
    private function promotedCorrection(string $date, int $base): array
    {
        $key = $date.'|'.$base;
        if (! isset($this->fixtures[$key])) {
            $fx = $this->buildCandidate($this->priorCurrent($date, $base));
            $this->fixtures[$key] = $this->promoteCandidate($this->sealCandidate($fx));
        }

        return $this->fixtures[$key];
    }

    /** Asserts the callable throws and that the message carries the reason. */
    private function assertRefused(callable $fn, string $reason, string $why): \Throwable
    {
        try {
            $fn();
        } catch (\Throwable $e) {
            $this->assertStringContainsString($reason, $e->getMessage(), $why);

            return $e;
        }

        $this->fail($why.' (nothing was refused; expected '.$reason.')');
    }

    // ---- 1. Exact publication verification -------------------------------------------------------

    /**
     * An explicit publication is resolved *as itself*. After a correction the prior publication is
     * no longer the current one, and resolving it by id must still return it, marked historical,
     * while the corrected one resolves as current. A selector naming only a date must never fall
     * back to "the current publication", and a publication that does not belong to the named date
     * must not resolve at all. The lookup is a three-way join over `eod_publications`, the run and
     * the pointer table.
     */
    private function s_f1_explicit_publication(): void
    {
        $fx = $this->promotedCorrection('2026-03-20', 971000);
        $evidence = new EodEvidenceRepository();
        $selector = function (int $publicationId, string $date) {
            return ['type' => 'replay_fixture_explicit_publication', 'publication_id' => $publicationId, 'trade_date' => $date];
        };

        $prior = $evidence->resolvePublicationForEvidenceAudit($selector($fx['prior_pub'], $fx['date']));
        $this->assertSame($fx['prior_pub'], (int) $prior->publication_id, 'the explicit selector resolved a different publication');
        $this->assertSame('HISTORICAL_PUBLICATION_AUDIT', $prior->evidence_resolution_mode,
            'the superseded publication must resolve as historical, not be replaced by the current one');
        $this->assertSame(0, (int) $prior->is_current);
        $this->assertSame((int) $fx['candidate_pub'], (int) $prior->pointer_publication_id,
            'the pointer moved to the correction, and the explicit resolution must not have followed it');

        $current = $evidence->resolvePublicationForEvidenceAudit($selector($fx['candidate_pub'], $fx['date']));
        $this->assertSame((int) $fx['candidate_pub'], (int) $current->publication_id);
        $this->assertSame('CURRENT_READABLE_PUBLICATION_AUDIT', $current->evidence_resolution_mode);

        $this->assertRefused(function () use ($evidence, $selector, $fx) {
            $evidence->resolvePublicationForEvidenceAudit($selector($fx['prior_pub'], '2026-03-19'));
        }, 'EVIDENCE_PUBLICATION_NOT_FOUND', 'a publication resolved for a trade date it does not belong to');
        $this->assertRefused(function () use ($evidence, $fx) {
            $evidence->resolvePublicationForEvidenceAudit(['type' => 'replay_fixture_explicit_publication', 'trade_date' => $fx['date']]);
        }, 'EVIDENCE_SELECTOR_MISSING', 'a date alone must never select "latest/current"');
    }

    /**
     * The frozen inputs and the seal, verified from what MariaDB stores. The sealed candidate's
     * deterministic manifest hash is recomputed from rows read back from the engine and must equal
     * the stored one; damage to any hashed input, to the run's terminal state, or to the seal is
     * refused under its own reason; the configuration the run froze is the one an as-known read at
     * the run's own cutoff resolves, and a later snapshot recorded after that cutoff does not
     * replace it; and the engine itself, through its sealed-immutability triggers, refuses to
     * change the publication's snapshot rows or its bound input context.
     */
    private function s_f1_frozen_inputs_and_seal(): void
    {
        $fx = $this->capturedAndSealedPublication();
        $db = $this->marketDataMariaDb();
        $repo = new EodPublicationRepository();
        $cand = $fx['candidate_pub'];

        $this->assertTrue($repo->assertPublicationManifestHashValid($cand),
            'the manifest hash recomputed from MariaDB rows must equal the stored one');
        $publication = $db->table('eod_publications')->where('publication_id', $cand)->first();
        $this->assertSame('SEALED', $publication->seal_state);
        $this->assertSame((int) $fx['config_id'], (int) $publication->config_snapshot_id, 'the publication does not freeze the run configuration');
        $binding = $db->table('md_publication_lineage_bindings')->where('publication_id', $cand)->first();
        $this->assertSame((int) $fx['config_id'], (int) $binding->config_snapshot_id, 'the lineage binding names a different configuration');
        $this->assertSame((int) $fx['factor_set_id'], (int) $binding->factor_set_id);
        $bound = json_decode((string) $binding->bound_input_context_json, true);
        $this->assertIsArray($bound);
        $this->assertNotEmpty($bound['components'] ?? [],
            'the fixture names no captured observation, temporal, formula or other input components; an empty direct-written COMPLETE marker is not frozen-input proof');
        $this->assertNotEmpty($bound['component_manifest']['required_operations'] ?? [],
            'the fixture did not pass the production input-completion manifest required by this bullet');
        $verified = (new \App\Application\MarketData\Services\PublicationInputBindingService())->readBoundContext($cand);
        $this->assertSame('VERIFIED', $verified['status'], 'the exact publication reader rejected its captured inputs');
        $this->assertNotEmpty($verified['components'] ?? [], 'the exact publication reader saw no frozen components');
        $fixtureDir = storage_path('framework/testing/r0025-exact-replay');
        $this->assertDirectoryDoesNotExist($fixtureDir, 'the exact replay fixture path is occupied');
        $this->capturedReplayDir = $fixtureDir;
        $replay = new \App\Application\MarketData\Services\ReplayVerificationService(
            new EodEvidenceRepository(), $repo, new \App\Infrastructure\Persistence\MarketData\ReplayResultRepository()
        );
        $generated = $replay->generateFixtureFromRun($fx['new_run'], $fixtureDir, 'r0025-captured', $cand);
        $this->assertSame($cand, (int) $generated['publication_id']);
        $result = $replay->verifyRunAgainstFixture($fx['new_run'], $fixtureDir, null, $cand);
        $this->assertSame('PASS', $result['replay_status'], 'the real exact-publication verifier rejected the captured publication: '.$result['mismatch_summary']);
        $this->assertSame('MATCH', $result['comparison_result']);
        $this->assertSame('ADMISSIBLE', $result['admission_state']);
        $this->assertSame($cand, (int) $result['publication_id']);

        // The frozen configuration is what an as-known read at the run's own cutoff resolves; a
        // snapshot for the same interval recorded after that cutoff must stay invisible to it.
        $configs = new MarketDataConfigSnapshotRepository();
        $cutoff = (string) $db->table('eod_runs')->where('run_id', $fx['new_run'])->value('knowledge_cutoff_at');
        $lateRecordedAt = Carbon::parse($cutoff)->addHour()->toDateTimeString();
        $laterCutoff = Carbon::parse($cutoff)->addHours(2)->toDateTimeString();
        $this->assertSame((int) $fx['config_id'], (int) $configs->resolveForRun($fx['date'], $cutoff)['config_snapshot_id']);
        $lateId = (int) $db->table('md_config_snapshots')->insertGetId([
            'snapshot_uid' => hash('sha256', 'r25-later-config'), 'snapshot_schema_version' => (string) config('market_data.governance.config_snapshot_schema_version', 'market_data_config_snapshot_v1'),
            'serialization_version' => 'canonical_json_v1', 'resolved_config_json' => '{}', 'config_hash' => hash('sha256', 'later-config'),
            'registry_revision' => 'test-registry-revision', 'effective_at' => $fx['date'].' 17:00:00', 'recorded_at' => $lateRecordedAt,
            'build_id' => 'test-build', 'environment_profile' => (string) config('market_data.governance.environment_profile', 'local'),
            'resolver_version' => 'test-resolver-v1', 'created_at' => $lateRecordedAt,
        ]);
        $this->assertSame((int) $fx['config_id'], (int) $configs->resolveForRun($fx['date'], $cutoff)['config_snapshot_id'],
            'a configuration recorded after the run cutoff replaced the frozen one');
        $this->assertSame($lateId, (int) $configs->resolveForRun($fx['date'], $laterCutoff)['config_snapshot_id'],
            'and a later cutoff must still be able to see it');
        // This test-owned later revision must not change the live configuration for the subsequent
        // unchanged-correction scenario in this same family transaction.
        $db->table('md_config_snapshots')->where('config_snapshot_id', $lateId)->delete();

        // Damage to a hashed input is refused, and restored so the next assertion starts clean.
        $original = $publication->bars_batch_hash;
        $db->table('eod_publications')->where('publication_id', $cand)->update(['bars_batch_hash' => hash('sha256', 'tampered')]);
        try {
            $this->assertRefused(function () use ($repo, $cand) {
                $repo->assertPublicationManifestHashValid($cand);
            }, 'publication_manifest_hash does not match', 'a changed artifact hash was accepted');
        } finally {
            $db->table('eod_publications')->where('publication_id', $cand)->update(['bars_batch_hash' => $original]);
        }
        $this->assertTrue($repo->assertPublicationManifestHashValid($cand));

        // The audit resolution refuses under the reason that names the damaged fact.
        $evidence = new EodEvidenceRepository();
        $historical = $this->promotedCorrection('2026-03-20', 971000);
        $selector = ['type' => 'replay_fixture_explicit_publication', 'publication_id' => $historical['prior_pub'], 'trade_date' => $historical['date']];
        foreach ([
            ['eod_runs', 'run_id', $historical['prior_run'], ['terminal_status' => 'FAILED'], 'EVIDENCE_RUN_TERMINAL_STATUS_INVALID'],
            ['eod_runs', 'run_id', $historical['prior_run'], ['coverage_gate_state' => 'FAIL'], 'EVIDENCE_COVERAGE_CONTEXT_INVALID'],
            ['eod_publications', 'publication_id', $historical['prior_pub'], ['seal_state' => 'UNSEALED'], 'EVIDENCE_HISTORICAL_PUBLICATION_UNSEALED'],
            ['eod_publications', 'publication_id', $historical['prior_pub'], ['indicators_batch_hash' => null], 'EVIDENCE_PUBLICATION_ARTIFACT_HASH_MISSING'],
        ] as [$table, $key, $id, $damage, $reason]) {
            $before = (array) $db->table($table)->where($key, $id)->first();
            $db->table($table)->where($key, $id)->update($damage);
            try {
                $this->assertRefused(function () use ($evidence, $selector) {
                    $evidence->resolvePublicationForEvidenceAudit($selector);
                }, $reason, 'a damaged '.key($damage).' was resolved as verified evidence');
            } finally {
                $db->table($table)->where($key, $id)->update(array_intersect_key($before, $damage));
            }
        }
        $evidence->resolvePublicationForEvidenceAudit($selector);

        // The engine enforces the seal on the snapshot rows and on the bound input context.
        $this->assertRefused(function () use ($db, $cand) {
            $db->table('eod_bars_history')->where('publication_id', $cand)->update(['close' => 1]);
        }, 'SEALED_PUBLICATION_IMMUTABLE', 'MariaDB let a sealed publication\'s snapshot row be changed');
        $this->assertRefused(function () use ($db, $cand) {
            $db->table('md_publication_lineage_bindings')->where('publication_id', $cand)->update(['bound_input_context_json' => '{}']);
        }, 'INPUT_CAPTURE_BINDING_SEALED_IMMUTABLE', 'MariaDB let a sealed publication\'s bound input context be rewritten');
        $this->assertRefused(function () use ($db, $cand) {
            $db->table('eod_publications')->where('publication_id', $cand)->update(['seal_state' => 'NOT_A_SEAL_STATE']);
        }, 'Data truncated for column', 'MariaDB accepted a publication seal state outside its governed ENUM');
    }

    /** The governed correction path consumes an unchanged request without a publication switch. */
    private function s_f1_unchanged_rerun(): void
    {
        $fx = $this->capturedAndSealedPublication();
        $db = $this->marketDataMariaDb();
        $repo = new EodPublicationRepository();
        $baseline = $db->table('eod_publications')->where('publication_id', $fx['candidate_pub'])->first();
        $this->assertSame(1, (int) $db->table('eod_publications')->where('trade_date', $fx['date'])->count());
        $this->assertMatchesRegularExpression('/^[a-f0-9]{64}$/', (string) $baseline->publication_manifest_hash);
        $corrections = new \App\Infrastructure\Persistence\MarketData\EodCorrectionRepository();
        $request = $corrections->createRequest($fx['date'], 'READABILITY_FIX', 'r0025-unchanged-content', 'system');
        $approved = $corrections->approve($request->correction_id, 'reviewer');
        $rerun = app(\App\Application\MarketData\Services\MarketDataPipelineService::class)
            ->runDaily($fx['date'], 'manual_file', $approved->correction_id);
        $this->assertSame('SUCCESS', (string) $rerun->terminal_status);
        $this->assertSame('READABLE', (string) $rerun->publishability_state);
        $current = $db->table('eod_publications')->where('trade_date', $fx['date'])->where('is_current', 1)->first();
        $this->assertSame($fx['candidate_pub'], (int) $current->publication_id,
            'an unchanged correction rerun switched away from the captured publication');
        $this->assertSame((int) $baseline->publication_version, (int) $current->publication_version);
        $this->assertSame(1, (int) $db->table('eod_publications')->where('trade_date', $fx['date'])->count(),
            'an unchanged rerun minted a fake correction publication');
        foreach (['bars_batch_hash', 'indicators_batch_hash', 'eligibility_batch_hash', 'publication_manifest_hash'] as $hash) {
            $this->assertSame((string) $baseline->$hash, (string) $current->$hash, $hash.' moved on unchanged input');
        }
        $correction = $db->table('eod_dataset_corrections')->where('correction_id', $approved->correction_id)->first();
        $this->assertSame('CONSUMED_CURRENT', (string) $correction->status);
        $this->assertNull($correction->published_at);
        $this->assertTrue($repo->assertPublicationManifestHashValid($fx['candidate_pub']));
    }

    // ---- 2. Degraded acquisition and expectation -------------------------------------------------

    /**
     * The denominator is the universe for the trade date as MariaDB resolves it, and a provider
     * that delivers nothing leaves every listing counted missing. The production evaluator runs
     * with the real universe and delivery readers.
     */
    private function s_f2_outage_denominator(): void
    {
        $date = '2026-03-24';
        $this->listing('f2-a', ['symbol' => 'OUTA']);
        $this->listing('f2-b', ['symbol' => 'OUTB']);
        $this->listing('f2-c', ['symbol' => 'OUTC']);
        $universe = (new TickerMasterRepository())->getUniverseForTradeDate($date);
        $codes = array_column($universe, 'ticker_code');
        foreach (['OUTA', 'OUTB', 'OUTC'] as $code) {
            $this->assertContains($code, $codes);
        }
        $expected = count($universe);

        $result = app(CoverageGateEvaluator::class)->evaluate($date);

        $this->assertSame($expected, $result['expected_universe_count'],
            'a provider outage shrank the denominator below the universe MariaDB resolves');
        $this->assertSame(0, $result['available_eod_count']);
        $this->assertSame($expected, $result['missing_eod_count'], 'every listing must be counted missing when nothing was delivered');
        $this->assertSame($expected, $result['coverage_bar_required_count'], 'an outage turned required bars into not-required ones');
        $this->assertSame('FAIL', $result['coverage_gate_state'], 'a total outage must not pass the coverage gate');
        $this->assertSame('RUN_COVERAGE_LOW', $result['coverage_reason_code']);
    }

    /**
     * Absent status evidence resolves to UNKNOWN with its own reason, and a suspension carried for
     * years stays a suspension. Neither becomes a holiday or dormancy that would take the listing
     * out of the coverage denominator.
     */
    private function s_f2_unknown_expectation(): void
    {
        $repository = new TemporalTradingStatusRepository();

        [$listingId, $instrumentId, $observationId] = $this->listing('f2-unknown', ['symbol' => 'UNKA']);
        $unknown = $repository->resolveForListing($listingId, '2026-06-01');
        $this->assertSame('UNKNOWN', $unknown['status_code'], 'absent evidence resolved to a known status');
        $this->assertSame('BAR_EXPECTATION_UNKNOWN', $unknown['bar_expectation_state']);
        $this->assertSame('TRADING_STATUS_NO_EVIDENCE', $unknown['reason_code']);

        [$suspendedId, $suspendedInstrument, $obs] = $this->listing('f2-suspended', ['symbol' => 'SUSA']);
        $this->statusRevision($suspendedId, $suspendedInstrument, $obs, 'f2-long-suspension', null, '2024-01-01 00:00:00', null, '2024-01-01 00:00:00');
        $suspended = $repository->resolveForListing($suspendedId, '2026-06-01');
        $this->assertSame('SUSPENSION', $suspended['status_code'], 'a suspension carried for years is still a suspension');
        $this->assertNotSame('DORMANT', $suspended['status_code']);
        $this->assertSame('BAR_NOT_EXPECTED', $suspended['bar_expectation_state']);

        // The coverage gate treats them differently and keeps the unknown ones in the denominator: only the verified
        // suspension leaves the required set; an expectation that is merely unknown stays required and is counted unknown.
        $result = app(CoverageGateEvaluator::class)->evaluate('2026-06-01');
        $this->assertSame(1, $result['coverage_bar_not_expected_count'], 'only the verified suspension may leave the required set');
        $this->assertSame($result['coverage_universe_count'] - 1, $result['expected_universe_count'], 'an unknown expectation left the denominator');
        $this->assertSame($result['expected_universe_count'], $result['coverage_bar_required_count'], 'an unknown expectation left the required set');
        $this->assertSame($result['expected_universe_count'], $result['coverage_expectation_unknown_count'], 'unknown expectations must be counted, not dropped');
    }

    /**
     * Two halves. The decisions that hold a run or refuse a row are made by ingest code that reads
     * no database: the stale, wrong-date and zero-price refusals are executed with the MariaDB
     * connection as default and must issue no query. The quarantine evidence itself is written and
     * read back through the production writer and exporter, on the engine that enforces the
     * columns: an invalid row lands in `eod_invalid_bars` with its reason and never in `eod_bars`.
     */
    private function s_f2_quarantine_or_hold(): void
    {
        $date = '2026-03-24';

        [$stale, $queries] = $this->queriesDuring(function () use ($date) {
            return $this->ingestRefusal($date, [$this->sourceRow($date, ['trade_date' => '2026-03-23'])]);
        });
        $this->assertSame('RUN_STALE_DATA', $stale->reasonCode());
        $this->assertSame([], $queries, 'the stale-row hold decision read the database, so it is not engine-independent');

        [$wrong, $queries] = $this->queriesDuring(function () use ($date) {
            return $this->ingestRefusal($date, [$this->sourceRow($date, ['trade_date' => '2026-03-25'])]);
        });
        $this->assertSame('RUN_STALE_DATA', $wrong->reasonCode());
        $this->assertSame([], $queries);

        // Zero price and schema-invalid rows are quarantined by the production row validator...
        $ingest = (new ReflectionClass(EodBarsIngestService::class))->newInstanceWithoutConstructor();
        $validate = new ReflectionMethod($ingest, 'validateCanonicalRow');
        $validate->setAccessible(true);
        $makeInvalid = new ReflectionMethod($ingest, 'makeInvalidRow');
        $makeInvalid->setAccessible(true);

        [$verdicts, $queries] = $this->queriesDuring(function () use ($validate, $date) {
            return [
                'zero' => $validate->invoke((new ReflectionClass(EodBarsIngestService::class))->newInstanceWithoutConstructor(),
                    $this->sourceRow($date, ['ticker_code' => 'ZERO', 'open' => 0, 'high' => 0, 'low' => 0, 'close' => 0]), $date),
                'schema' => $validate->invoke((new ReflectionClass(EodBarsIngestService::class))->newInstanceWithoutConstructor(),
                    $this->sourceRow($date, ['ticker_code' => 'SCHEMA', 'close' => null]), $date),
            ];
        });
        $this->assertSame([], $queries, 'the row validator read the database');
        $this->assertFalse($verdicts['zero']['valid']);
        $this->assertSame('BAR_NON_POSITIVE_PRICE', $verdicts['zero']['reason_code']);
        $this->assertFalse($verdicts['schema']['valid']);
        $this->assertSame('BAR_MISSING_REQUIRED_FIELD', $verdicts['schema']['reason_code']);

        // ...and the quarantine evidence is persisted and read back on MariaDB.
        $runId = 971500 + $this->next();
        $this->marketDataMariaDb()->table('eod_runs')->insert($this->runRow($runId, $date, ['terminal_status' => 'HELD', 'publishability_state' => 'NOT_READABLE']));
        $now = Carbon::now(config('market_data.platform.timezone'))->toDateTimeString();
        $invalid = [
            $makeInvalid->invoke($ingest, $runId, $this->sourceRow($date, ['ticker_code' => 'ZERO', 'ticker_id' => 971901, 'open' => 0, 'high' => 0, 'low' => 0, 'close' => 0, 'source_row_ref' => 'r25:ZERO']), $verdicts['zero']['reason_code'], $verdicts['zero']['note'], $now),
            $makeInvalid->invoke($ingest, $runId, $this->sourceRow($date, ['ticker_code' => 'SCHEMA', 'ticker_id' => 971902, 'close' => null, 'source_row_ref' => 'r25:SCHEMA']), $verdicts['schema']['reason_code'], $verdicts['schema']['note'], $now),
        ];
        (new EodArtifactRepository())->persistInvalidBars($date, $runId, $invalid);

        $exported = (new EodEvidenceRepository())->exportInvalidBarsRows($date, $runId);
        $this->assertSame(['BAR_NON_POSITIVE_PRICE', 'BAR_MISSING_REQUIRED_FIELD'], array_column($exported, 'invalid_reason_code'),
            'the quarantined rows did not come back from MariaDB with their reasons');
        $this->assertSame([971901, 971902], array_map('intval', array_column($exported, 'ticker_id')));
        $this->assertSame(0, (int) $this->marketDataMariaDb()->table('eod_bars')->where('trade_date', $date)->whereIn('ticker_id', [971901, 971902])->count(),
            'a quarantined row must never reach the canonical bars table');

        // The decision and writer must also be connected by the real ingest path. A direct
        // persistInvalidBars call alone stays green if ingest silently stops quarantining rows.
        $liveRun = $this->newRun($date, 'quarantine');
        $this->marketDataMariaDb()->table('eod_runs')->where('run_id', $liveRun->run_id)->update(['knowledge_cutoff_at' => $date.' 18:00:00']);
        $liveRun = EodRun::query()->findOrFail($liveRun->run_id);
        $tickers = $this->createMock(TickerMasterRepository::class);
        $tickers->method('resolveTickerIdsByCodes')->willReturn(['ZERO' => 971911, 'SCHEMA' => 971912]);
        $tickers->method('resolveTemporalContextsByCodes')->willReturn([
            'ZERO' => ['listing_id' => 971911, 'board_code' => 'RG'],
            'SCHEMA' => ['listing_id' => 971912, 'board_code' => 'RG'],
        ]);
        $publications = $this->createMock(EodPublicationRepository::class);
        $publications->method('findCurrentPublicationForTradeDate')->willReturn(null);
        $observations = $this->createMock(SourceObservationRepository::class);
        $observations->method('existsAccepted')->willReturn(true);
        $service = new EodBarsIngestService(
            $this->createMock(LocalFileEodBarsAdapter::class), $this->createMock(PublicApiEodBarsAdapter::class),
            $tickers, new EodArtifactRepository(), $publications, null, $observations, null, null, $this->mockProducerInputCapture()
        );
        $rows = [
            $this->sourceRow($date, ['ticker_code' => 'ZERO', 'source_row_ref' => 'r25:live:ZERO', 'open' => 0, 'high' => 0, 'low' => 0, 'close' => 0]),
            $this->sourceRow($date, ['ticker_code' => 'SCHEMA', 'source_row_ref' => 'r25:live:SCHEMA', 'close' => null]),
        ];
        try {
            $service->ingestAcquiredRows($liveRun, $date, 'api', $rows, ['source_acquisition_state' => 'SUCCESS']);
            $this->fail('invalid input was admitted without a quarantine verdict');
        } catch (SourceAcquisitionException $e) {
            $this->assertSame('RUN_SOURCE_NO_VALID_DATA', $e->reasonCode());
            $this->assertSame(0, $e->context()['accepted_row_count']);
            $this->assertSame(2, $e->context()['rejected_row_count']);
        }
        $liveExport = (new EodEvidenceRepository())->exportInvalidBarsRows($date, $liveRun->run_id);
        $this->assertSame(['BAR_NON_POSITIVE_PRICE', 'BAR_MISSING_REQUIRED_FIELD'], array_column($liveExport, 'invalid_reason_code'),
            'the real ingest path did not persist both rejected rows on MariaDB');
        $this->assertSame(0, (int) $this->marketDataMariaDb()->table('eod_bars')->where('trade_date', $date)->whereIn('ticker_id', [971911, 971912])->count());

        $this->assertRefused(function () use ($runId, $date) {
            $this->marketDataMariaDb()->table('eod_invalid_bars')->insert([
                'trade_date' => $date, 'run_id' => $runId, 'source' => 'X', 'created_at' => $date.' 10:00:00',
            ]);
        }, 'invalid_reason_code', 'MariaDB accepted an invalid row with no reason code, so quarantine evidence could be reasonless');
    }

    /**
     * A run for a requested date with no data of its own but an earlier readable publication is
     * held with that earlier date as its effective date, and stays unreadable: nothing resolves
     * for the requested date, so the earlier day's result cannot be read as the requested day's.
     * With no fallback the run fails and carries no effective date at all.
     */
    private function s_f2_no_prior_date_masquerade(): void
    {
        $fx = $this->promotedCorrection('2026-03-18', 973000);
        $requested = '2026-03-19';
        $db = $this->marketDataMariaDb();
        $repo = new EodPublicationRepository();

        $fallback = $repo->findLatestReadablePublicationBefore($requested);
        $this->assertNotNull($fallback, 'the earlier readable publication was not found for the fallback');
        $this->assertSame($fx['date'], (string) $fallback->readable_trade_date);

        $heldRun = $this->newRun($requested, 'held');
        $held = (new EodRunRepository())->holdStage($heldRun, 'FINALIZE', 'FINALIZE_HELD_PRIOR_DATE_FALLBACK', 'held on the prior readable date', $fallback->readable_trade_date);

        $this->assertSame('HELD', $held->terminal_status);
        $this->assertSame('NOT_READABLE', $held->publishability_state);
        $this->assertSame($requested, (string) $db->table('eod_runs')->where('run_id', $held->run_id)->value('trade_date_requested'));
        $this->assertSame($fx['date'], (string) $db->table('eod_runs')->where('run_id', $held->run_id)->value('trade_date_effective'),
            'the effective date must stay the prior date, distinct from the requested date');
        $this->assertNull($repo->findPointerResolvedPublicationForTradeDate($requested),
            'the prior date\'s publication resolved as the requested date\'s fresh data');
        $this->assertNull($repo->findReadableCurrentPublicationForRun($held->run_id, $requested));

        $failedRun = $this->newRun('2026-03-16', 'failed');
        $failed = (new EodRunRepository())->failStage($failedRun, 'FINALIZE', 'FINALIZE_NO_FALLBACK', 'nothing to fall back on');
        $this->assertSame('FAILED', $failed->terminal_status);
        $this->assertNull($db->table('eod_runs')->where('run_id', $failed->run_id)->value('trade_date_effective'),
            'a failed run must not carry an effective date');
        $this->assertNull($repo->findPointerResolvedPublicationForTradeDate('2026-03-16'));

        // The decision service agrees with what was persisted: same inputs, same outcome.
        $coverage = ['coverage_gate_status' => 'PASS', 'coverage_gate_state' => 'PASS', 'coverage_ratio' => 1.0,
            'coverage_threshold_value' => 0.98, 'coverage_threshold_mode' => 'MIN_RATIO', 'expected_universe_count' => 100,
            'available_eod_count' => 100, 'missing_eod_count' => 0, 'coverage_universe_basis' => 'ACTIVE_TICKER_MASTER_FOR_TRADE_DATE',
            'coverage_contract_version' => 'coverage_gate_v1'];
        $decisionHeld = (new FinalizeDecisionService())->evaluate(true, true, 'SEALED', $coverage, $fx['date'], ['bars_rows_written' => 0]);
        $decisionFailed = (new FinalizeDecisionService())->evaluate(true, true, 'SEALED', $coverage, null, ['bars_rows_written' => 0]);
        $this->assertSame('HELD', $decisionHeld['terminal_status']);
        $this->assertSame($fx['date'], $decisionHeld['trade_date_effective']);
        $this->assertSame('FAILED', $decisionFailed['terminal_status']);
        $this->assertNull($decisionFailed['trade_date_effective']);
    }

    /** @return array<string,mixed> one well-formed source row on $date, overridable field by field */
    private function sourceRow(string $date, array $override = []): array
    {
        return array_merge([
            'ticker_code' => 'BBCA', 'trade_date' => $date, 'open' => 100, 'high' => 110, 'low' => 99, 'close' => 108,
            'volume' => 1000, 'adj_close' => 104, 'source_name' => 'YAHOO_FINANCE', 'source_row_ref' => 'yahoo:BBCA:'.$date,
            'captured_at' => '2026-03-24T17:00:00+07:00', 'source_observation_id' => 901, 'source_observation_persisted' => true,
        ], $override);
    }

    /** The real ingest service with its repositories doubled: it must refuse before it persists anything. */
    private function ingestRefusal(string $date, array $rows): SourceAcquisitionException
    {
        $tickers = $this->createMock(TickerMasterRepository::class);
        $artifacts = $this->createMock(EodArtifactRepository::class);
        $publications = $this->createMock(EodPublicationRepository::class);
        $observations = $this->createMock(SourceObservationRepository::class);
        $tickers->method('resolveTickerIdsByCodes')->willReturn(['BBCA' => 1, 'BBRI' => 2]);
        $tickers->method('resolveTemporalContextsByCodes')->willReturnCallback(function (array $codes) {
            $contexts = [];
            foreach (array_values($codes) as $i => $code) {
                $contexts[$code] = ['listing_id' => 5000 + $i, 'board_code' => 'RG'];
            }

            return $contexts;
        });
        $publications->method('findCurrentPublicationForTradeDate')->willReturn(null);
        $publications->method('getOrCreateCandidatePublication')->willReturn((object) ['publication_id' => 990, 'publication_version' => 1]);
        $observations->method('existsAccepted')->willReturn(true);
        $observations->method('manifestHashForRun')->willReturn('manifest-hash-test');
        $artifacts->expects($this->never())->method('replaceBars');

        $run = new EodRun(['run_id' => 91, 'trade_date_requested' => $date, 'knowledge_cutoff_at' => $date.' 18:00:00']);
        $service = new EodBarsIngestService(
            $this->createMock(LocalFileEodBarsAdapter::class), $this->createMock(PublicApiEodBarsAdapter::class),
            $tickers, $artifacts, $publications, null, $observations, null, null, $this->mockProducerInputCapture()
        );

        try {
            $service->ingestAcquiredRows($run, $date, 'api', $rows, ['source_acquisition_state' => 'SUCCESS']);
        } catch (SourceAcquisitionException $e) {
            return $e;
        }

        $this->fail('the ingest accepted rows it must have refused');
    }

    private function newRun(string $date, string $tag): EodRun
    {
        $id = 971600 + $this->next();
        $this->marketDataMariaDb()->table('eod_runs')->insert($this->runRow($id, $date, [
            'lifecycle_state' => 'RUNNING', 'terminal_status' => null, 'publishability_state' => 'NOT_READABLE',
            'coverage_gate_state' => 'NOT_EVALUABLE', 'quality_gate_state' => 'PENDING', 'trade_date_effective' => null,
            'is_current_publication' => 0, 'source' => 'api_'.$tag,
        ]));

        return EodRun::query()->findOrFail($id);
    }

    // ---- 3. Temporal identity and status ---------------------------------------------------------

    /**
     * A listing delisted on 30 June 2025, with the delisting recorded on 1 July, is in the universe
     * for a trade date before the delisting, out of it after, and -- for a read bounded to a moment
     * before the delisting was recorded -- still in it on a date after the delisting, because the
     * platform had not yet learned it. Query shape: `OR` of a `NULL` test and two comparisons.
     */
    private function s_f3_inactive_now_active_then(): void
    {
        $identity = new TemporalIdentityRepository();
        $this->listing('f3-delisted', ['symbol' => 'GONE', 'delisted_date' => '2025-06-30', 'delisted_recorded_at' => '2025-07-01 00:00:00', 'listing_state' => 'DELISTED']);
        $this->listing('f3-live', ['symbol' => 'LIVE']);

        $codes = function (string $date, ?string $knownAt = null) use ($identity) {
            return array_column($identity->universeAsOf($date, $knownAt), 'ticker_code');
        };

        $this->assertContains('GONE', $codes('2025-06-27'), 'a listing active then vanished from the historical universe');
        $this->assertContains('LIVE', $codes('2025-06-27'));
        $this->assertNotContains('GONE', $codes('2025-07-02'), 'a delisted listing stayed in the universe after its delisting');
        $this->assertNotContains('GONE', $codes('2025-07-02', '2025-07-10 00:00:00'));
        $this->assertContains('GONE', $codes('2025-07-02', '2025-06-30 12:00:00'),
            'a read as known before the delisting was recorded lost the listing (survivorship introduced by the query)');
    }

    /**
     * Symbol change and symbol reuse resolve through listing identity. Listing A is `OLDA` until
     * 1 March 2025 and `NEWA` from it; listing B takes `OLDA` on that date. The exclusive interval
     * end (`effective_to > date 00:00:00`) and the inclusive start decide the boundary day.
     */
    private function s_f3_symbol_change_and_reuse(): void
    {
        $identity = new TemporalIdentityRepository();
        [$a] = $this->listing('f3-sym-a', ['symbol' => 'OLDA', 'symbol_to' => '2025-03-01 00:00:00']);
        $this->symbol($a, 'NEWA', '2025-03-01 00:00:00', null);
        [$b] = $this->listing('f3-sym-b', ['symbol' => 'OLDA', 'symbol_from' => '2025-03-01 00:00:00']);

        $byCode = function (string $date) use ($identity) {
            $rows = [];
            foreach ($identity->universeAsOf($date) as $row) {
                $this->assertArrayNotHasKey($row['ticker_code'], $rows,
                    'a reused symbol resolved to two listings on '.$date.' instead of one stable identity');
                $rows[$row['ticker_code']] = (int) $row['listing_id'];
            }

            return $rows;
        };

        $before = $byCode('2025-02-28');
        $this->assertSame($a, $before['OLDA'], 'before the change OLDA is listing A');
        $this->assertArrayNotHasKey('NEWA', $before);
        $onDay = $byCode('2025-03-01');
        $this->assertSame($b, $onDay['OLDA'], 'on the reuse day OLDA is listing B, not the listing that last carried it');
        $this->assertSame($a, $onDay['NEWA'], 'the renamed listing keeps its identity under its new symbol');
        $after = $byCode('2025-04-01');
        $this->assertSame($b, $after['OLDA']);
        $this->assertSame($a, $after['NEWA']);
    }

    /**
     * Effective time and knowledge time, for both status and calendar. A suspension is in force on
     * a date inside its interval and not after its end; a revision recorded after a cutoff is
     * invisible to a read as known at that cutoff and exposed by a later one; the calendar
     * revision for a session is chosen by the cutoff against `recorded_at`, and a revision for a
     * different date is never returned. `NULL` handling in the supersession join is engine-specific.
     */
    private function s_f3_calendar_session_status_times(): void
    {
        [$listingId, $instrumentId, $observationId] = $this->listing('f3-status', ['symbol' => 'STAT']);
        $repository = new TemporalTradingStatusRepository();

        // Effective time: a suspension 10-18 March recorded in February.
        $this->statusRevision($listingId, $instrumentId, $observationId, 'f3-interval', '2026-03-18 00:00:00', '2026-02-20 00:00:00', null, '2026-03-10 00:00:00');
        $this->assertSame('SUSPENSION', $repository->resolveForListing($listingId, '2026-03-15')['status_code'], 'a suspension inside its interval was not in force');
        $this->assertNotSame('SUSPENSION', $repository->resolveForListing($listingId, '2026-03-24')['status_code'], 'a suspension outside its interval was still in force');

        // Knowledge time: an open suspension closed by a revision recorded on 1 May.
        [$openListing, $openInstrument, $openObservation] = $this->listing('f3-status-open', ['symbol' => 'STAO']);
        $openId = $this->statusRevision($openListing, $openInstrument, $openObservation, 'f3-suspension', null, '2026-03-12 00:00:00', null, '2026-03-10 00:00:00');
        $this->statusRevision($openListing, $openInstrument, $openObservation, 'f3-lift', '2026-03-18 00:00:00', '2026-05-01 00:00:00', $openId, '2026-03-10 00:00:00');
        $this->assertSame('SUSPENSION', $repository->resolveForListing($openListing, self::TRADE_DATE, '2026-03-20 00:00:00')['status_code'],
            'a lift recorded on 1 May was visible to a read as known on 20 March');
        $this->assertNotSame('SUSPENSION', $repository->resolveForListing($openListing, self::TRADE_DATE, '2026-05-10 00:00:00')['status_code'],
            'the superseding revision was not applied once it was knowable');

        // Calendar: the cutoff picks the revision; another date's revision is never returned.
        $this->calendarRevision('2026-03-01 00:00:00', 'f3-calendar-early');
        $calendar = new MarketCalendarRepository();
        $early = $calendar->sessionContext(self::TRADE_DATE, self::CUTOFF_EARLY);
        $this->assertSame('f3-calendar-early', (string) $early['revision_uid']);
        $this->calendarRevision('2026-05-01 00:00:00', 'f3-calendar-late', (int) $early['calendar_revision_id']);
        $this->assertSame('f3-calendar-early', (string) $calendar->sessionContext(self::TRADE_DATE, self::CUTOFF_EARLY)['revision_uid'],
            'a revision recorded on 1 May superseded for a cutoff of 15 April');
        $this->assertSame('f3-calendar-late', (string) $calendar->sessionContext(self::TRADE_DATE, self::CUTOFF_LATE)['revision_uid'],
            'the later cutoff must see the superseding revision, or the cutoff is a wall');
        $this->assertRefused(function () use ($calendar) {
            $calendar->sessionContext('2026-03-25', self::CUTOFF_LATE);
        }, 'MARKET_CALENDAR_EVIDENCE_MISSING', 'a calendar revision for one session was returned for another');
    }

    // ---- 4. Corporate actions and indicators -----------------------------------------------------

    /**
     * A verified split and a synthetic candidate and a provider-reported action for the same
     * publication. The production factor producer selects and applies only the verified one: the
     * other two leave no decision and no factor row. The selection is a `whereIn` over a string
     * column, decided by collation.
     */
    private function s_f4_synthetic_never_activates(): void
    {
        $fx = $this->factorFixture();

        $this->assertSame(['APPLIED'], array_column($fx['result']['decisions'], 'decision_state'),
            'only the verified revision may produce a factor decision');
        $this->assertSame(1, (int) $this->marketDataMariaDb()->table('md_adjustment_factors')->where('factor_set_id', $fx['result']['factor_set_id'])->count(),
            'a synthetic or provider-reported revision produced a factor row');
        $this->assertSame([$fx['verified_revision']], array_map('intval', $this->marketDataMariaDb()->table('md_adjustment_factors')
            ->where('factor_set_id', $fx['result']['factor_set_id'])->pluck('corporate_action_revision_id')->all()));

        $service = new AdjustmentFactorSetService();
        $selected = new ReflectionMethod($service, 'authoritativeEventsThrough');
        $selected->setAccessible(true);
        $rows = $selected->invoke($service, '2026-07-28', '2026-08-13 12:00:00');
        $this->assertSame(['AUTHORITATIVE_VERIFIED'], array_values(array_unique(array_map(function ($r) { return $r->verification_state; }, $rows->all()))));
        $this->assertCount(1, $rows, 'the two non-verified revisions must not reach the factor set');
    }

    /**
     * The verified 1:5 split's factors, stored by the producer in MariaDB decimal columns and read
     * back, produce coherent structural OHLC and volume: every pre-split field scales by the same
     * 0.2, volume by the inverse 5, bars on or after the ex-date do not move, and the bar stays
     * internally ordered.
     */
    private function s_f4_verified_revision_coherent_structural(): void
    {
        $fx = $this->factorFixture();
        $factor = $this->marketDataMariaDb()->table('md_adjustment_factors as f')
            ->join('md_corporate_action_revisions as r', 'r.corporate_action_revision_id', '=', 'f.corporate_action_revision_id')
            ->where('f.factor_set_id', $fx['result']['factor_set_id'])
            ->select('f.price_factor', 'f.volume_factor', 'r.ex_date', 'f.corporate_action_revision_id')
            ->first();
        $this->assertEqualsWithDelta(0.2, (float) $factor->price_factor, 1e-12, 'the price factor did not survive MariaDB decimal storage');
        $this->assertEqualsWithDelta(5.0, (float) $factor->volume_factor, 1e-12);

        $bars = [
            ['trade_date' => '2026-07-13', 'open' => 100, 'high' => 110, 'low' => 95, 'close' => 105, 'adj_close' => 77, 'volume' => 1000],
            ['trade_date' => '2026-07-14', 'open' => 104, 'high' => 112, 'low' => 100, 'close' => 110, 'adj_close' => 77, 'volume' => 2000],
            ['trade_date' => '2026-07-15', 'open' => 22, 'high' => 24, 'low' => 20, 'close' => 23, 'adj_close' => 77, 'volume' => 9000],
        ];
        $product = (new AnalyticalPriceProductService())->build($bars, 'STRUCTURAL_ADJUSTED', [
            'as_of_date' => '2026-07-15',
            'price_adjustment_factors' => [[
                'ex_date' => (string) $factor->ex_date, 'price_factor' => $factor->price_factor, 'volume_factor' => $factor->volume_factor,
                'corporate_action_revision_id' => (int) $factor->corporate_action_revision_id,
            ]],
        ]);

        $out = $product['bars'];
        $expected = [[20.0, 22.0, 19.0, 21.0, 5000.0], [20.8, 22.4, 20.0, 22.0, 10000.0], [22.0, 24.0, 20.0, 23.0, 9000.0]];
        foreach ($out as $i => $bar) {
            $this->assertEqualsWithDelta($expected[$i][0], $bar['open'], 1e-9, 'open, bar '.$i);
            $this->assertEqualsWithDelta($expected[$i][1], $bar['high'], 1e-9, 'high, bar '.$i);
            $this->assertEqualsWithDelta($expected[$i][2], $bar['low'], 1e-9, 'low, bar '.$i);
            $this->assertEqualsWithDelta($expected[$i][3], $bar['close'], 1e-9, 'close, bar '.$i);
            $this->assertEqualsWithDelta($expected[$i][4], $bar['volume'], 1e-9, 'volume, bar '.$i);
            $this->assertGreaterThanOrEqual($bar['low'], $bar['high'], 'the adjusted bar lost its ordering');
        }
    }

    /**
     * Structural adjustment is derived from the raw fields and the factors. The provider's
     * `adj_close` is carried through untouched and never selected: changing it must change no
     * structural field. The build performs no query.
     */
    private function s_f4_no_provider_adjusted_close(): void
    {
        $build = function (float $providerAdjClose) {
            return (new AnalyticalPriceProductService())->build([
                ['trade_date' => '2026-07-13', 'open' => 100, 'high' => 110, 'low' => 95, 'close' => 105, 'adj_close' => $providerAdjClose, 'volume' => 1000],
                ['trade_date' => '2026-07-15', 'open' => 22, 'high' => 24, 'low' => 20, 'close' => 23, 'adj_close' => $providerAdjClose, 'volume' => 9000],
            ], 'STRUCTURAL_ADJUSTED', [
                'as_of_date' => '2026-07-15',
                'price_adjustment_factors' => [['ex_date' => '2026-07-15', 'price_factor' => 0.2, 'volume_factor' => 5.0]],
            ]);
        };

        [$one, $queries] = $this->queriesDuring(function () use ($build) { return $build(50.0); });
        $two = $build(9999.0);
        $this->assertSame([], $queries, 'the structural build read the database, so it is not engine-independent');

        foreach ($one['bars'] as $i => $bar) {
            foreach (['open', 'high', 'low', 'close', 'volume'] as $field) {
                $this->assertSame($bar[$field], $two['bars'][$i][$field], $field.' moved with the provider adjusted close');
            }
        }
        $this->assertEqualsWithDelta(21.0, $one['bars'][0]['close'], 1e-9, 'the close is raw x factor, not the provider figure');
        $this->assertSame(50.0, $one['bars'][0]['adj_close'], 'the provider adj_close is carried as supplied, never replaced');
    }

    /**
     * The independent oracle is the spec recursion (`EOD_Indicators_Formula_Spec.md`, ATR14 Wilder):
     * seed = mean of the first 14 true ranges, then ((prev * 13) + TR) / 14. A 200-session chain
     * with varying true range must match it, and a high corrected thirty sessions before the
     * requested date -- beyond the fourteen-session window -- must move the ATR by exactly what
     * the oracle says. The indicator service performs no query.
     */
    private function s_f4_long_chain_atr(): void
    {
        $bars = [];
        for ($i = 0; $i < 200; $i++) {
            $close = 100.0 + (($i * 7) % 23);
            $bars[] = ['trade_date' => date('Y-m-d', strtotime('2026-01-01 +'.($i + 1).' days')), 'open' => $close,
                'high' => $close + 1.0 + ($i % 5), 'low' => $close - 1.0 - (($i * 3) % 4), 'close' => $close, 'adj_close' => $close, 'volume' => 1000];
        }
        $config = ['set_version' => 'ind_v1', 'price_adjustment_factors' => [], 'price_basis_default' => 'close', 'dv_window_days' => 20,
            'atr_window_days' => 14, 'vol_ratio_lookback_days' => 20, 'roc_lookback_days' => 20, 'hh_window_days' => 20, 'sector_code' => null,
            'sector_index_code' => null, 'event_risk_context' => [], 'corporate_action_contamination' => [], 'price_scale_break_contamination' => [],
            'atr_contamination_horizon_days' => 0, 'benchmark_roc20_pct' => null, 'sector_roc20_pct' => null];
        $oracle = function (array $series): float {
            $tr = [];
            for ($i = 1; $i < count($series); $i++) {
                $pc = (float) $series[$i - 1]['close'];
                $tr[] = max($series[$i]['high'] - $series[$i]['low'], abs($series[$i]['high'] - $pc), abs($series[$i]['low'] - $pc));
            }
            $atr = array_sum(array_slice($tr, 0, 14)) / 14;
            for ($i = 14; $i < count($tr); $i++) {
                $atr = (($atr * 13) + $tr[$i]) / 14;
            }

            return $atr;
        };
        $service = new IndicatorVectorService();
        $atrOf = function (array $series) use ($service, $config) {
            return $service->calculateIndicators($series, count($series) - 1, $config, null, null)['atr14'];
        };

        [$atr, $queries] = $this->queriesDuring(function () use ($atrOf, $bars) { return $atrOf($bars); });
        $this->assertSame([], $queries, 'the indicator service read the database, so it is not engine-independent');
        $this->assertEqualsWithDelta($oracle($bars), $atr, 1e-9, 'the 200-session ATR does not match the independent oracle');

        $corrected = $bars;
        $corrected[count($bars) - 1 - 30]['high'] += 5.0;
        $this->assertEqualsWithDelta($oracle($corrected), $atrOf($corrected), 1e-9, 'the corrected chain does not match the oracle');
        $this->assertGreaterThan(1e-9, abs($atrOf($corrected) - $atr), 'a correction thirty sessions back moved nothing, as a fourteen-session truncation would');
        $this->assertEqualsWithDelta($oracle($corrected) - $oracle($bars), $atrOf($corrected) - $atr, 1e-9);
    }

    /**
     * Actual traded value and the close-volume proxy are separate columns of the production
     * tables, of one decimal type, and the actual is nullable so "unavailable" is stored as `NULL`
     * and read back as `NULL`, never as a filled-in zero or the proxy. The production indicator row
     * carries both under their own names, the actual `null` and the proxy populated.
     */
    private function s_f4_actual_value_and_proxy_identity(): void
    {
        $columns = [];
        foreach ($this->marketDataMariaDb()->select(
            'select table_name t, column_name c, column_type ty, is_nullable n from information_schema.columns where table_schema = ? and table_name in (?, ?, ?, ?)',
            [$this->marketDataMariaDb()->getDatabaseName(), 'eod_bars', 'eod_bars_history', 'eod_indicators', 'eod_indicators_history']
        ) as $c) {
            $columns[$c->t][$c->c] = ['type' => $c->ty, 'nullable' => $c->n === 'YES'];
        }
        foreach (['eod_bars', 'eod_bars_history'] as $table) {
            $this->assertArrayHasKey('traded_value_idr_actual', $columns[$table], $table.' has no actual traded value column');
            $this->assertTrue($columns[$table]['traded_value_idr_actual']['nullable'], $table.': the actual must be nullable so unavailable is not stored as zero');
            $this->assertArrayNotHasKey('adv20_close_volume_proxy_idr', $columns[$table], $table.' stores the proxy in the actual table');
        }
        foreach (['eod_indicators', 'eod_indicators_history'] as $table) {
            $this->assertArrayHasKey('adv20_traded_value_idr_actual', $columns[$table]);
            $this->assertArrayHasKey('adv20_close_volume_proxy_idr', $columns[$table]);
            $this->assertTrue($columns[$table]['adv20_traded_value_idr_actual']['nullable'], $table.': the actual ADV must be nullable');
            $this->assertNotSame('adv20_traded_value_idr_actual', 'adv20_close_volume_proxy_idr');
        }

        $bars = [];
        for ($i = 1; $i <= 60; $i++) {
            $bars[] = ['trade_date' => date('Y-m-d', strtotime('2026-01-01 +'.$i.' days')), 'open' => 100, 'high' => 100, 'low' => 100, 'close' => 100, 'adj_close' => 100, 'volume' => 1000];
        }
        $config = ['set_version' => 'ind_v1', 'price_adjustment_factors' => [], 'price_basis_default' => 'close', 'dv_window_days' => 20,
            'atr_window_days' => 14, 'vol_ratio_lookback_days' => 20, 'roc_lookback_days' => 20, 'hh_window_days' => 20, 'sector_code' => null,
            'sector_index_code' => null, 'event_risk_context' => [], 'corporate_action_contamination' => [], 'price_scale_break_contamination' => [],
            'atr_contamination_horizon_days' => 0, 'benchmark_roc20_pct' => null, 'sector_roc20_pct' => null];
        $row = (new IndicatorVectorService())->buildRow(1, $bars, $bars[59]['trade_date'], 55, 9001, '2026-05-25 18:00:00', $config);
        $this->assertNull($row['adv20_traded_value_idr_actual'], 'the actual was filled although the provider supplies none');
        $this->assertEqualsWithDelta(100000.0, $row['adv20_close_volume_proxy_idr'], 0.01);

        // MariaDB stores and returns that NULL as NULL: the production writer persists the row's actual and proxy.
        $runId = 971700 + $this->next();
        $publicationId = 971800 + $this->next();
        $this->marketDataMariaDb()->table('eod_runs')->insert($this->runRow($runId, '2026-03-13'));
        $this->marketDataMariaDb()->table('eod_bars')->insert([
            'trade_date' => '2026-03-13', 'ticker_id' => 971903, 'open' => 100, 'high' => 100, 'low' => 100, 'close' => 100, 'volume' => 1000,
            'source' => 'X', 'run_id' => $runId, 'publication_id' => $publicationId, 'traded_value_idr_actual' => null, 'created_at' => '2026-03-13 17:00:00',
        ]);
        $stored = $this->marketDataMariaDb()->table('eod_bars')->where('ticker_id', 971903)->first();
        $this->assertNull($stored->traded_value_idr_actual, 'MariaDB returned the unavailable actual as something other than NULL');
    }

    // ---- 5. Correction and read path -------------------------------------------------------------

    /**
     * After the correction is promoted the prior publication stays exactly what it was: sealed,
     * with its recorded hashes, no longer current, superseded by the corrected one, and still
     * resolvable as history. The application refuses to mutate or discard it, and MariaDB itself
     * refuses to insert, change or delete a sealed publication's snapshot rows.
     */
    private function s_f5_prior_publication_auditable(): void
    {
        $fx = $this->promotedCorrection('2026-03-18', 973000);
        $db = $this->marketDataMariaDb();
        $repo = new EodPublicationRepository();

        $prior = $db->table('eod_publications')->where('publication_id', $fx['prior_pub'])->first();
        $this->assertSame('SEALED', $prior->seal_state);
        $this->assertSame(0, (int) $prior->is_current, 'the superseded publication is still flagged current');
        $this->assertSame(hash('sha256', 'bars-old'), $prior->bars_batch_hash, 'the prior publication\'s hashes changed');
        $this->assertSame(hash('sha256', 'ind-old'), $prior->indicators_batch_hash);
        $this->assertSame(hash('sha256', 'elig-old'), $prior->eligibility_batch_hash);
        $this->assertSame($fx['date'].' 17:20:00', (string) $prior->sealed_at, 'the prior seal time changed');

        $candidate = $db->table('eod_publications')->where('publication_id', $fx['candidate_pub'])->first();
        $this->assertSame($fx['prior_pub'], (int) $candidate->supersedes_publication_id, 'the correction does not name what it supersedes');
        $this->assertNotSame($prior->bars_batch_hash, $candidate->bars_batch_hash, 'the correction is not a distinct artifact');

        $this->assertRefused(function () use ($repo, $fx) { $repo->assertPublicationMutable($fx['prior_pub']); }, 'SEALED_PUBLICATION_IMMUTABLE', 'the superseded publication was reported mutable');
        $this->assertRefused(function () use ($repo, $fx) {
            $repo->updateCandidateHashes($fx['prior_pub'], ['bars_batch_hash' => hash('sha256', 'x'), 'indicators_batch_hash' => hash('sha256', 'y'), 'eligibility_batch_hash' => hash('sha256', 'z')]);
        }, 'SEALED_PUBLICATION_IMMUTABLE', 'the superseded publication\'s hashes could be rewritten');
        $this->assertRefused(function () use ($repo, $fx) { $repo->discardCandidatePublication($fx['prior_pub']); }, 'SEALED_PUBLICATION_IMMUTABLE', 'the superseded publication could be discarded');

        // The engine enforces it on the snapshot rows of the sealed correction.
        foreach ([
            'update' => function () use ($db, $fx) { $db->table('eod_bars_history')->where('publication_id', $fx['candidate_pub'])->update(['volume' => 1]); },
            'delete' => function () use ($db, $fx) { $db->table('eod_bars_history')->where('publication_id', $fx['candidate_pub'])->delete(); },
            'insert' => function () use ($db, $fx) {
                $db->table('eod_bars_history')->insert(['publication_id' => $fx['candidate_pub'], 'trade_date' => $fx['date'], 'ticker_id' => 999998,
                    'open' => 1, 'high' => 1, 'low' => 1, 'close' => 1, 'volume' => 1, 'source' => 'X', 'run_id' => $fx['new_run'], 'created_at' => $fx['date'].' 17:30:00']);
            },
        ] as $kind => $mutation) {
            $this->assertRefused($mutation, 'SEALED_PUBLICATION_IMMUTABLE', 'MariaDB let a sealed publication\'s snapshot row be '.$kind.'d');
        }

        $evidence = (new EodEvidenceRepository())->resolvePublicationForEvidenceAudit(
            ['type' => 'replay_fixture_explicit_publication', 'publication_id' => $fx['prior_pub'], 'trade_date' => $fx['date']]
        );
        $this->assertSame('HISTORICAL_PUBLICATION_AUDIT', $evidence->evidence_resolution_mode, 'the prior publication is no longer auditable');
    }

    /**
     * The corrected candidate is not current while it is being built, is refused promotion until
     * it is sealed and its run passes validation, and becomes current only through the promotion,
     * which moves the pointer, the flags and the run mirror together. Every refused attempt leaves
     * the prior publication current and the pointer where it was.
     */
    private function s_f5_active_only_after_validation_and_reseal(): void
    {
        $fx = $this->buildCandidate($this->priorCurrent('2026-03-17', 974000));
        $db = $this->marketDataMariaDb();
        $repo = new EodPublicationRepository();
        $run = function () use ($fx) { return EodRun::query()->findOrFail($fx['new_run']); };
        $state = function () use ($db, $fx) {
            return [
                'pointer' => (int) $db->table('eod_current_publication_pointer')->where('trade_date', $fx['date'])->value('publication_id'),
                'prior_current' => (int) $db->table('eod_publications')->where('publication_id', $fx['prior_pub'])->value('is_current'),
                'candidate_current' => (int) $db->table('eod_publications')->where('publication_id', $fx['candidate_pub'])->value('is_current'),
            ];
        };
        $unchanged = ['pointer' => $fx['prior_pub'], 'prior_current' => 1, 'candidate_current' => 0];

        $this->assertSame($unchanged, $state(), 'a candidate that is being built must not be current');
        $this->assertSame($fx['prior_pub'], (int) $repo->findPointerResolvedPublicationForTradeDate($fx['date'])->publication_id);

        $this->assertRefused(function () use ($repo, $run, $fx) { $repo->promoteCandidateToCurrent($run(), $fx['prior_pub']); },
            'FINALIZE_SEAL_INVALID', 'an unsealed candidate was promoted');
        $this->assertSame($unchanged, $state(), 'a refused promotion moved something');

        $this->sealCandidate($fx);
        $this->assertSame($unchanged, $state(), 'sealing alone must not make the candidate current');

        foreach ([
            ['coverage_gate_state', 'FAIL', 'POINTER_PUBLICATION_STATE_INVALID'],
            ['terminal_status', 'HELD', 'PUBLICATION_RUN_STATE_INVALID'],
        ] as [$column, $value, $reason]) {
            $original = $db->table('eod_runs')->where('run_id', $fx['new_run'])->value($column);
            $db->table('eod_runs')->where('run_id', $fx['new_run'])->update([$column => $value]);
            try {
                $this->assertRefused(function () use ($repo, $run, $fx) { $repo->promoteCandidateToCurrent($run(), $fx['prior_pub']); },
                    $reason, 'a candidate whose run failed validation ('.$column.'='.$value.') was promoted');
            } finally {
                $db->table('eod_runs')->where('run_id', $fx['new_run'])->update([$column => $original]);
            }
            $this->assertSame($unchanged, $state(), 'a refused promotion moved something');
        }

        $this->promoteCandidate($fx);
        $this->assertSame(['pointer' => $fx['candidate_pub'], 'prior_current' => 0, 'candidate_current' => 1], $state());
        $this->assertSame($fx['candidate_pub'], (int) $repo->findPointerResolvedPublicationForTradeDate($fx['date'])->publication_id);
        $this->assertSame(1, (int) $db->table('eod_runs')->where('trade_date_requested', $fx['date'])->where('is_current_publication', 1)->count(),
            'exactly one run mirrors the current publication');

        // "Exactly one publication is current for a date" is structural on MariaDB: the pointer table's primary key
        // and its unique publication key refuse a second row, and its foreign key refuses a publication that does
        // not exist. The SQLite mirror declares the keys and builds its tables with foreign keys off.
        $pointer = function (string $date, int $publicationId) use ($fx) {
            return ['trade_date' => $date, 'publication_id' => $publicationId, 'run_id' => $fx['new_run'], 'publication_version' => 2,
                'sealed_at' => $fx['date'].' 19:00:00', 'updated_at' => $fx['date'].' 19:00:00'];
        };
        $this->assertRefused(function () use ($db, $pointer, $fx) {
            $db->table('eod_current_publication_pointer')->insert($pointer($fx['date'], $fx['prior_pub']));
        }, 'Duplicate entry', 'MariaDB accepted a second current pointer for one trade date');
        $this->assertRefused(function () use ($db, $pointer, $fx) {
            $db->table('eod_current_publication_pointer')->insert($pointer('2026-03-02', $fx['candidate_pub']));
        }, 'Duplicate entry', 'MariaDB accepted one publication as the current pointer of two dates');
        $this->assertRefused(function () use ($db, $pointer) {
            $db->table('eod_current_publication_pointer')->insert($pointer('2026-03-03', 987654321));
        }, 'foreign key constraint fails', 'MariaDB accepted a pointer to a publication that does not exist');
        $this->assertSame(1, (int) $db->table('eod_current_publication_pointer')->where('trade_date', $fx['date'])->count());
    }

    /**
     * Concurrent consumers read exactly one publication, on the engine's own isolation. The switch
     * is made by the production promotion on one connection while a second connection reads after
     * every statement the switch issues, before it commits. Each read -- the pointer, the
     * current flags, and the production pointer-resolved read model -- must show one publication
     * and one only: the prior until the switch commits, the correction after it. A mix is a
     * consumer reading a half-made switch.
     *
     * This needs committed rows, so it ends the test's wrapping transaction and cleans up exactly
     * the rows it made (reverting the seal on them first, which the sealed-immutability triggers
     * require), verifying none remain.
     */
    private function s_f5_concurrent_consumers(): void
    {
        $date = '2026-03-27';
        $base = 975000;
        $reader = 'market_data_mariadb_reader';
        $db = $this->marketDataMariaDb();
        $this->assertSame('tradeaxis_testing', $db->getDatabaseName(), 'committed fixtures may run only in the named test database');
        $this->assertSame(1, (int) $db->selectOne('select get_lock(?, 5) as acquired', ['md-r0025-concurrency-fixture'])->acquired,
            'another R0025 concurrency fixture owns the test namespace');
        $committed = false;
        $views = [];

        try {
            // A shared persistent test database is not disposable by this test. Refuse any existing
            // namespace occupant rather than deleting it under fixed fixture ids or trade date.
            $this->assertSame([], $this->residualCommittedRows($date, $base),
                'the concurrency namespace is occupied; investigate its owner before running');
            $this->endWrappingTransaction();
            $committed = true;
            config()->set('database.connections.'.$reader, config('database.connections.'.$this->marketDataMariaDbConnection));
            DB::purge($reader);
            $fx = $this->sealCandidate($this->buildCandidate($this->priorCurrent($date, $base)));
            $prior = $fx['prior_pub'];
            $candidate = $fx['candidate_pub'];
            $repo = new EodPublicationRepository();

            $view = function () use ($reader, $date) {
                $previous = config('database.default');
                config()->set('database.default', $reader);
                try {
                    $pointer = DB::table('eod_current_publication_pointer')->where('trade_date', $date)->value('publication_id');
                    $flagged = DB::table('eod_publications')->where('trade_date', $date)->where('is_current', 1)->orderBy('publication_id')->pluck('publication_id')->map(function ($v) { return (int) $v; })->all();
                    $resolved = (new EodPublicationRepository())->findPointerResolvedPublicationForTradeDate($date);

                    return ['pointer' => $pointer === null ? null : (int) $pointer, 'flagged' => $flagged,
                        'resolved' => $resolved ? (int) $resolved->publication_id : null];
                } finally {
                    config()->set('database.default', $previous);
                }
            };

            $this->assertSame(['pointer' => $prior, 'flagged' => [$prior], 'resolved' => $prior], $view(), 'a consumer does not read the prior publication before the switch');

            $watching = true;
            // The listener is dispatched for every connection, so it must ignore the reader's own queries.
            $writer = $this->marketDataMariaDbConnection;
            $db->listen(function ($query) use (&$views, $view, &$watching, $writer) {
                if ($watching && $query->connectionName === $writer) {
                    $watching = false;
                    try {
                        $views[] = ['sql' => substr($query->sql, 0, 60), 'view' => $view()];
                    } finally {
                        $watching = true;
                    }
                }
            });
            $run = EodRun::query()->findOrFail($fx['new_run']);
            $repo->promoteCandidateToCurrent($run, $prior);
            $watching = false;

            $this->assertGreaterThan(5, count($views), 'the reader observed too few points of the switch to mean anything');
            foreach ($views as $i => $seen) {
                $v = $seen['view'];
                $this->assertNotNull($v['pointer'], 'a consumer found no pointer during the switch ('.$seen['sql'].')');
                $this->assertContains($v['pointer'], [$prior, $candidate]);
                $this->assertSame([$v['pointer']], $v['flagged'],
                    'a consumer saw the current flag on a different publication than the pointer, mid-switch, after: '.$seen['sql']);
                $this->assertSame($v['pointer'], $v['resolved'], 'the read model resolved a different publication than the pointer, mid-switch, after: '.$seen['sql']);
            }
            $this->assertSame(['pointer' => $candidate, 'flagged' => [$candidate], 'resolved' => $candidate], $view(), 'a consumer does not read the correction after the switch');
        } finally {
            $watching = false;
            try {
                if ($committed) {
                    $this->purgeCommittedFixture($date, $base);
                    $this->assertSame([], $this->residualCommittedRows($date, $base),
                        'the concurrency scenario left committed rows behind');
                }
            } finally {
                DB::purge($reader);
                $db->selectOne('select release_lock(?) as released', ['md-r0025-concurrency-fixture']);
            }
        }
    }

    /**
     * An explicit fallback resolves the latest earlier readable publication and keeps its own
     * date: the fallback's readable date is the earlier one, the held run persists that date as
     * its effective date beside the different requested date, and the run is stale and unreadable
     * (`NOT_READABLE`) on the requested date rather than serving the earlier data as current.
     */
    private function s_f5_explicit_fallback(): void
    {
        $fx = $this->promotedCorrection('2026-03-18', 973000);
        $requested = '2026-03-19';
        $db = $this->marketDataMariaDb();
        $repo = new EodPublicationRepository();

        $fallback = $repo->findLatestReadablePublicationBefore($requested);
        $this->assertNotNull($fallback);
        $this->assertSame($fx['candidate_pub'], (int) $fallback->publication_id, 'the fallback is not the latest readable earlier publication');
        $this->assertSame($fx['date'], (string) $fallback->readable_trade_date, 'the fallback lost its own date');
        $earlier = $repo->findLatestReadablePublicationBefore($fx['date']);
        $this->assertTrue($earlier === null || (string) $earlier->readable_trade_date < $fx['date'],
            'a fallback for a date must never be the publication of that date or a later one');

        $run = $this->newRun($requested, 'fallback');
        $held = (new EodRunRepository())->holdStage($run, 'FINALIZE', 'FINALIZE_HELD_PRIOR_DATE_FALLBACK', 'explicit fallback', $fallback->readable_trade_date);
        $stored = $db->table('eod_runs')->where('run_id', $held->run_id)->first();
        $this->assertSame($requested, (string) $stored->trade_date_requested);
        $this->assertSame($fx['date'], (string) $stored->trade_date_effective, 'the held run did not retain the prior effective date');
        $this->assertSame('HELD', $stored->terminal_status);
        $this->assertSame('NOT_READABLE', $stored->publishability_state, 'the stale fallback run is readable');
        $this->assertSame('FINALIZE_HELD_PRIOR_DATE_FALLBACK', $stored->final_reason_code, 'the degraded state carries no reason');
    }

    // ---- 6. As-known isolation -------------------------------------------------------------------

    /**
     * The as-known snapshot the production service captures at an early cutoff contains none of
     * the master, event, status, calendar, config, formula or factor revisions that were recorded
     * after it. Each root is resolved by its real repository from rows that differ only in
     * `recorded_at`, so the comparison happens in the engine's queries.
     */
    private function s_f6_later_revisions_invisible(): void
    {
        $world = $this->asKnownWorld();
        $early = $world['early'];

        $this->assertSame((int) $world['early_config']['config_snapshot_id'], $early['config_snapshot_id']);
        $this->assertSame(20, $early['formula_registry_identity']['indicator_config']['roc_lookback_days'], 'the formula identity is not the one frozen in the early snapshot');
        $codes = array_column($early['temporal_universe'], 'ticker_code');
        $this->assertContains('F6EARLY', $codes);
        $this->assertNotContains('F6LATE', $codes, 'a listing recorded in May is in a universe read as known in April');
        $this->assertSame('f6-calendar-early', $early['calendar_context']['revision_uid']);
        $this->assertSame('SUSPENSION', $early['trading_status_contexts'][$world['early_listing']]['status_code'],
            'the lift recorded in May was visible to the April cutoff');
        $this->assertCount(1, $early['trading_status_contexts'][$world['early_listing']]['status_revision_ids'],
            'a later status revision entered the early bound status context even though its final state still looked suspended');
        $this->assertCount(1, $early['event_factor_context']['corporate_action_revisions'], 'a later event is visible before it was recorded');
        $this->assertCount(1, $early['event_factor_context']['factor_sets'], 'a later factor set is visible before it was recorded');
        $this->assertCount(1, $early['event_factor_context']['factors']);
    }

    /**
     * A later declared cutoff exposes the later revisions, and re-reading the earlier cutoff
     * afterwards returns the identical snapshot and writes nothing.
     */
    private function s_f6_later_cutoff_exposes_without_rewriting(): void
    {
        $world = $this->asKnownWorld();
        $early = $world['early'];
        $late = $world['late'];

        $this->assertSame((int) $world['late_config']['config_snapshot_id'], $late['config_snapshot_id']);
        $this->assertSame(21, $late['formula_registry_identity']['indicator_config']['roc_lookback_days']);
        $codes = array_column($late['temporal_universe'], 'ticker_code');
        $this->assertContains('F6LATE', $codes, 'the later cutoff must expose the later listing, or the cutoff is a wall');
        $this->assertContains('F6EARLY', $codes);
        $this->assertSame('f6-calendar-late', $late['calendar_context']['revision_uid']);
        $this->assertNotSame('SUSPENSION', $late['trading_status_contexts'][$world['early_listing']]['status_code']);
        $this->assertCount(2, $late['event_factor_context']['corporate_action_revisions']);
        $this->assertCount(2, $late['event_factor_context']['factor_sets']);
        $this->assertCount(2, $late['event_factor_context']['factors']);

        $this->assertSame($early['snapshot_hash'], $world['early_again']['snapshot_hash'], 'later-known revisions rewrote the artifact produced for the earlier cutoff');
        $this->assertNotSame($early['snapshot_hash'], $late['snapshot_hash'], 'a later cutoff that exposes new revisions produced the same snapshot');
        $this->assertSame($world['counts_before'], $world['counts_after'], 'as-known capture is a read: it created or rewrote bound-input rows');
    }

    /**
     * One world for both bullets: two configuration snapshots, two listings, two calendar
     * revisions, a suspension and its later lift, two events and two factor sets, captured at an
     * early cutoff, a late one and the early one again by the production service.
     *
     * @return array<string,mixed>
     */
    private function asKnownWorld(): array
    {
        if (isset($this->fixtures['as_known'])) {
            return $this->fixtures['as_known'];
        }

        $configs = new MarketDataConfigSnapshotRepository();
        config(['market_data.indicators.roc_lookback_days' => 20]);
        Carbon::setTestNow('2026-03-25 10:00:00');
        $earlyConfig = $configs->resolveForRun(self::TRADE_DATE);
        config(['market_data.indicators.roc_lookback_days' => 21]);
        Carbon::setTestNow(self::LATE_RECORDED_AT);
        $lateConfig = $configs->resolveForRun(self::TRADE_DATE);

        [$earlyListing, $earlyInstrument, $earlyObservation] = $this->listing('f6-early', ['symbol' => 'F6EARLY', 'recorded_at' => '2023-01-02 00:00:00']);
        [$lateListing] = $this->listing('f6-late', ['symbol' => 'F6LATE', 'recorded_at' => self::LATE_RECORDED_AT]);

        $this->calendarRevision('2023-01-05 00:00:00', 'f6-calendar-early');
        $this->calendarRevision(self::LATE_RECORDED_AT, 'f6-calendar-late', (int) $this->marketDataMariaDb()->table('md_market_calendar_revisions')
            ->where('revision_uid', 'f6-calendar-early')->value('calendar_revision_id'));

        $openId = $this->statusRevision($earlyListing, $earlyInstrument, $earlyObservation, 'f6-suspension', null, '2023-01-05 00:00:00', null, '2023-01-02 00:00:00');
        $this->statusRevision($earlyListing, $earlyInstrument, $earlyObservation, 'f6-lift', '2026-03-01 00:00:00', self::LATE_RECORDED_AT, $openId, '2023-01-02 00:00:00');

        $db = $this->marketDataMariaDb();
        $eventEarly = (int) $db->table('md_corporate_action_revisions')->insertGetId([
            'event_uid' => hash('sha256', 'f6-early-event'), 'revision_number' => 1, 'listing_id' => $earlyListing, 'action_type_code' => 'STOCK_SPLIT',
            'lifecycle_state' => 'EFFECTIVE', 'verification_state' => 'AUTHORITATIVE_VERIFIED', 'ex_date' => '2026-03-20',
            'effective_at' => '2026-03-20 00:00:00', 'recorded_at' => '2026-03-25 09:00:00',
        ]);
        $eventLate = (int) $db->table('md_corporate_action_revisions')->insertGetId([
            'event_uid' => hash('sha256', 'f6-late-event'), 'revision_number' => 1, 'listing_id' => $lateListing, 'action_type_code' => 'STOCK_SPLIT',
            'lifecycle_state' => 'EFFECTIVE', 'verification_state' => 'AUTHORITATIVE_VERIFIED', 'ex_date' => '2026-03-20',
            'effective_at' => '2026-03-20 00:00:00', 'recorded_at' => self::LATE_RECORDED_AT,
        ]);
        $setEarly = $this->factorSet('f6-early-factor', (int) $earlyConfig['config_snapshot_id'], '2026-03-25 09:00:00');
        $setLate = $this->factorSet('f6-late-factor', (int) $lateConfig['config_snapshot_id'], self::LATE_RECORDED_AT);
        $this->factor($setEarly, $earlyListing, $eventEarly);
        $this->factor($setLate, $lateListing, $eventLate);

        $service = new AsKnownReplaySnapshotService(
            new TemporalIdentityRepository(), new MarketCalendarRepository(), new TemporalTradingStatusRepository(), $configs, new SourceObservationRepository()
        );
        $counts = function () use ($db) {
            return [
                'config_snapshots' => (int) $db->table('md_config_snapshots')->count(),
                'events' => (int) $db->table('md_corporate_action_revisions')->count(),
                'factor_sets' => (int) $db->table('md_adjustment_factor_sets')->count(),
                'factors' => (int) $db->table('md_adjustment_factors')->count(),
            ];
        };
        $before = $counts();
        $early = $service->capture(self::TRADE_DATE, self::CUTOFF_EARLY);
        $late = $service->capture(self::TRADE_DATE, self::CUTOFF_LATE);
        $earlyAgain = $service->capture(self::TRADE_DATE, self::CUTOFF_EARLY);
        Carbon::setTestNow();

        return $this->fixtures['as_known'] = [
            'early_config' => $earlyConfig, 'late_config' => $lateConfig, 'early_listing' => $earlyListing,
            'early' => $early, 'late' => $late, 'early_again' => $earlyAgain, 'counts_before' => $before, 'counts_after' => $counts(),
        ];
    }

    // ---- fixtures --------------------------------------------------------------------------------

    /**
     * Issuer, instrument, listing, exchange symbol and board, and an accepted source observation,
     * for one listing.
     *
     * @param array<string,mixed> $o symbol, listed_date, delisted_date, delisted_recorded_at, listing_state,
     *                              recorded_at, symbol_from, symbol_to
     * @return array{0:int,1:int,2:int} listing, instrument and observation ids
     */
    private function listing(string $tag, array $o = []): array
    {
        $db = $this->marketDataMariaDb();
        $n = $this->next();
        $recordedAt = $o['recorded_at'] ?? '2023-01-02 00:00:00';
        $issuerId = (int) $db->table('md_issuers')->insertGetId([
            'issuer_uid' => 'R25-ISSUER-'.$tag.'-'.$n, 'legal_name' => 'Issuer '.$tag, 'source_ref' => 'fixture',
            'recorded_at' => $recordedAt, 'created_at' => $recordedAt,
        ]);
        $instrumentId = (int) $db->table('md_instruments')->insertGetId([
            'instrument_uid' => 'R25-INSTRUMENT-'.$tag.'-'.$n, 'issuer_id' => $issuerId, 'instrument_type' => 'EQUITY',
            'currency_code' => 'IDR', 'source_ref' => 'fixture', 'recorded_at' => $recordedAt, 'created_at' => $recordedAt,
        ]);
        $listingId = (int) $db->table('md_listings')->insertGetId([
            'listing_uid' => 'R25-LISTING-'.$tag.'-'.$n, 'legacy_ticker_id' => 972000000 + $n, 'instrument_id' => $instrumentId,
            'exchange_code' => 'IDX', 'market_segment' => 'REGULAR', 'board_code' => 'RG',
            'listed_date' => $o['listed_date'] ?? '2023-01-02', 'delisted_date' => $o['delisted_date'] ?? null,
            'delisted_recorded_at' => $o['delisted_recorded_at'] ?? null, 'listing_state' => $o['listing_state'] ?? 'LISTED',
            'source_ref' => 'fixture', 'recorded_at' => $recordedAt, 'created_at' => $recordedAt,
        ]);
        $this->symbol($listingId, $o['symbol'] ?? 'SYM'.$n, $o['symbol_from'] ?? '2023-01-02 00:00:00', $o['symbol_to'] ?? null, $recordedAt);
        $db->table('md_listing_boards')->insert([
            'listing_id' => $listingId, 'market_segment' => 'REGULAR', 'board_code' => 'RG',
            'effective_from' => '2023-01-02 00:00:00', 'effective_to' => null, 'recorded_at' => $recordedAt,
            'source_ref' => 'fixture', 'change_reason' => 'TEST_FIXTURE',
        ]);
        $observationId = (int) $db->table('md_source_observations')->insertGetId([
            'observation_uid' => hash('sha256', 'r25-observation-'.$tag.'-'.$n), 'attempt_uid' => 'r25-'.$tag.'-'.$n,
            'requested_trade_date' => self::TRADE_DATE, 'source_mode' => 'authority_document', 'source_name' => 'IDX', 'provider' => 'IDX',
            'sanitized_request_identity' => 'https://www.idx.co.id/notice', 'response_status' => 200, 'content_type' => 'application/json',
            'acquired_at' => '2023-01-05 00:00:00', 'adapter_version' => 'test-v1', 'payload_hash' => str_repeat('a', 64),
            'outcome_state' => 'ACCEPTED', 'created_at' => '2023-01-05 00:00:00',
        ]);

        return [$listingId, $instrumentId, $observationId];
    }

    private function symbol(int $listingId, string $symbol, string $from, ?string $to, string $recordedAt = '2023-01-02 00:00:00'): void
    {
        $this->marketDataMariaDb()->table('md_listing_symbols')->insert([
            'listing_id' => $listingId, 'symbol' => $symbol, 'symbol_type' => 'EXCHANGE', 'symbol_namespace' => 'IDX',
            'effective_from' => $from, 'effective_to' => $to, 'recorded_at' => $recordedAt, 'source_ref' => 'fixture', 'change_reason' => 'SYMBOL_CHANGE',
        ]);
    }

    private function statusRevision(int $listingId, int $instrumentId, int $observationId, string $uid, ?string $effectiveTo, string $recordedAt, ?int $supersedes, string $effectiveFrom): int
    {
        return (int) $this->marketDataMariaDb()->table('md_trading_status_revisions')->insertGetId([
            'listing_id' => $listingId, 'instrument_id' => $instrumentId, 'status_event_uid' => hash('sha256', $uid.$listingId),
            'status_type_code' => 'SUSPENDED', 'status_code' => 'SUSPENSION', 'bar_expectation_state' => 'BAR_NOT_EXPECTED',
            'board_code' => 'RG', 'authority_class' => 'EXCHANGE_AUTHORITATIVE', 'source_name' => 'IDX_OFFICIAL',
            'source_payload_hash' => str_repeat('a', 64), 'verification_state' => 'VERIFIED', 'full_session_verified' => 1,
            'effective_from' => $effectiveFrom, 'effective_to' => $effectiveTo, 'recorded_at' => $recordedAt,
            'supersedes_revision_id' => $supersedes, 'source_observation_id' => $observationId,
            'source_ref' => 'https://www.idx.co.id/notice', 'observed_at' => $recordedAt, 'announced_at' => $recordedAt,
        ]);
    }

    private function calendarRevision(string $recordedAt, string $uid, ?int $supersedes = null): void
    {
        $this->marketDataMariaDb()->table('md_market_calendar_revisions')->insert([
            'market_code' => 'IDX', 'market_segment' => 'REGULAR', 'cal_date' => self::TRADE_DATE, 'revision_uid' => $uid,
            'timezone' => 'Asia/Jakarta', 'is_trading_day' => 1, 'is_half_day' => 0, 'session_state' => 'COMPLETED',
            'session_open_at' => self::TRADE_DATE.' 09:00:00', 'session_close_at' => self::TRADE_DATE.' 16:00:00',
            'completed_at' => self::TRADE_DATE.' 16:00:00', 'recorded_at' => $recordedAt, 'supersedes_revision_id' => $supersedes,
            'source_ref' => 'https://www.idx.co.id/calendar', 'source_version' => 'idx-calendar-2026', 'provenance_tier' => 'VERIFIED',
            'reconciled_at' => self::TRADE_DATE, 'reconciliation_source_ref' => 'https://www.idx.co.id/calendar',
        ]);
    }

    private function factorSet(string $uid, int $configSnapshotId, string $recordedAt): int
    {
        return (int) $this->marketDataMariaDb()->table('md_adjustment_factor_sets')->insertGetId([
            'factor_set_uid' => hash('sha256', $uid), 'price_product_code' => 'STRUCTURAL_ADJUSTED',
            'factor_formula_version' => 'structural_factor_product_v2', 'config_snapshot_id' => $configSnapshotId, 'state' => 'BOUND',
            'content_hash' => hash('sha256', $uid.'-content'), 'recorded_at' => $recordedAt, 'created_at' => $recordedAt,
        ]);
    }

    private function factor(int $factorSetId, int $listingId, int $eventId): void
    {
        $this->marketDataMariaDb()->table('md_adjustment_factors')->insert([
            'factor_set_id' => $factorSetId, 'listing_id' => $listingId, 'effective_from' => '2026-03-20', 'effective_to' => null,
            'price_factor' => 0.5, 'volume_factor' => 2.0, 'corporate_action_revision_id' => $eventId, 'created_at' => '2026-03-20 00:00:00',
        ]);
    }

    /**
     * A publication run with a verified 1:5 split, a synthetic candidate and a provider-reported
     * action, the accepted source observations that attribute them, the AS_TRADED source-scale
     * assessment, and the factor set the production producer builds from them.
     *
     * @return array<string,mixed>
     */
    private function factorFixture(): array
    {
        if (isset($this->fixtures['factors'])) {
            return $this->fixtures['factors'];
        }

        Carbon::setTestNow('2026-08-13 12:00:00');
        $db = $this->marketDataMariaDb();
        $run = (new EodRunRepository())->getOrCreateOwningRun('2026-07-28', 'api', 'COMPUTE_INDICATORS', null, 'r25-factors');
        $publicationId = (int) $db->table('eod_publications')->insertGetId([
            'trade_date' => '2026-07-28', 'run_id' => $run->run_id, 'publication_version' => 1, 'seal_state' => 'UNSEALED',
            'created_at' => '2026-08-13 12:00:00', 'updated_at' => '2026-08-13 12:00:00',
        ]);
        $observations = new SourceObservationRepository();
        $verified = null;
        foreach ([['AUTHORITATIVE_VERIFIED', 'AS_TRADED'], ['SYNTHETIC_CANDIDATE', null], ['PROVIDER_REPORTED', null]] as [$state, $scale]) {
            [$listingId] = $this->listing('f4-'.strtolower($state), ['symbol' => 'F4'.substr($state, 0, 4).$this->seq, 'listed_date' => '2010-01-01']);
            $n = 970000 + $listingId;
            $observation = $observations->recordAcceptedRows($observations->capture([
                'acquisition_batch_id' => 1, 'attempt_uid' => 'r25-event-'.$n, 'requested_trade_date' => '2026-07-15', 'source_mode' => 'api',
                'source_name' => 'IDX', 'provider' => 'IDX', 'provider_symbol' => 'E'.$n, 'sanitized_request_identity' => 'event:'.$n,
                'adapter_version' => 'fixture-v1', 'payload' => json_encode(['event' => $n, 'ratio' => [1, 5]]), 'acquired_at' => '2026-08-01 00:00:00',
            ]), [['ticker_code' => 'E'.$n, 'trade_date' => '2026-07-15', 'open' => 100, 'high' => 110, 'low' => 90, 'close' => 105, 'volume' => 1000, 'source_row_ref' => 'event:'.$n]]);
            $revisionId = (int) $db->table('md_corporate_action_revisions')->insertGetId([
                'event_uid' => hash('sha256', 'r25event'.$n), 'revision_number' => 1, 'listing_id' => $listingId, 'action_type_code' => 'STOCK_SPLIT',
                'lifecycle_state' => 'EFFECTIVE', 'verification_state' => $state, 'ex_date' => '2026-07-15', 'effective_at' => '2026-07-15 00:00:00',
                'terms_json' => json_encode(['ratio' => ['from' => 1, 'to' => 5]]), 'source_observation_id' => $observation['source_observation_id'],
                'recorded_at' => '2026-08-01 00:00:00',
            ]);
            if ($scale !== null) {
                $verified = $revisionId;
                $db->table('md_source_scale_assessments')->insert([
                    'assessment_uid' => hash('sha256', 'r25assess'.$n), 'revision_number' => 1, 'provider' => 'YAHOO_FINANCE', 'listing_id' => $listingId,
                    'corporate_action_revision_id' => $revisionId, 'source_scale_state' => $scale, 'scale_effective_from' => '2026-07-15',
                    'assessment_version' => AdjustmentFactorSetService::ASSESSMENT_VERSION,
                    'evidence_observation_set_hash' => hash('sha256', json_encode([(int) $observation['source_observation_id']])),
                    'evidence_json' => json_encode(['observation_ids' => [(int) $observation['source_observation_id']], 'source_ref' => 'IDX:event:'.$n]),
                    'recorded_at' => '2026-08-02 00:00:00', 'created_at' => '2026-08-02 00:00:00',
                ]);
            }
        }

        $result = (new AdjustmentFactorSetService())->ensureForPublication($run, $publicationId, '2026-07-28', []);
        Carbon::setTestNow();

        return $this->fixtures['factors'] = ['run' => $run, 'publication_id' => $publicationId, 'result' => $result, 'verified_revision' => $verified];
    }

    // ---- committed-fixture handling for the concurrency scenario ---------------------------------

    /**
     * Ends the wrapping transaction the trait began, so this scenario's rows are committed and a
     * second connection can read them. Everything the test did before this point is discarded with
     * it, and the trait's tear-down is told there is nothing left to roll back.
     */
    private function endWrappingTransaction(): void
    {
        $this->marketDataMariaDb()->rollBack();
        $this->marketDataMariaDbStarted = false;
        $this->fixtures = [];
    }

    /**
     * Removes exactly the rows a committed correction fixture creates, and no others. The seal is
     * reverted first because MariaDB refuses to delete a sealed publication's snapshot rows or
     * lineage binding (the sealed-immutability triggers); the publications themselves carry no such
     * trigger. Safe to call when nothing exists.
     */
    private function purgeCommittedFixture(string $date, int $base): void
    {
        $db = $this->marketDataMariaDb();
        $runIds = [$base + 1, $base + 2];
        $publicationIds = array_map('intval', $db->table('eod_publications')->where('trade_date', $date)->whereIn('run_id', $runIds)->pluck('publication_id')->all());
        $publicationIds[] = $base + 3;
        $publicationIds = array_values(array_unique($publicationIds));
        $configIds = array_values(array_unique(array_merge(
            array_map('intval', $db->table('md_publication_lineage_bindings')->whereIn('publication_id', $publicationIds)->pluck('config_snapshot_id')->all()),
            array_map('intval', $db->table('md_config_snapshots')->where('registry_revision', 'r25-'.$date)->pluck('config_snapshot_id')->all())
        )));
        $factorSetIds = array_values(array_unique(array_merge(
            array_map('intval', $db->table('md_publication_lineage_bindings')->whereIn('publication_id', $publicationIds)->pluck('factor_set_id')->all()),
            $configIds === [] ? [] : array_map('intval', $db->table('md_adjustment_factor_sets')->whereIn('config_snapshot_id', $configIds)->pluck('factor_set_id')->all())
        )));

        $db->table('eod_publications')->whereIn('publication_id', $publicationIds)->update(['seal_state' => 'UNSEALED']);
        $db->table('eod_current_publication_pointer')->where('trade_date', $date)->whereIn('publication_id', $publicationIds)->delete();
        $db->table('eod_bars_history')->whereIn('publication_id', $publicationIds)->delete();
        $db->table('md_publication_lineage_bindings')->whereIn('publication_id', $publicationIds)->delete();
        $db->table('eod_publications')->whereIn('publication_id', $publicationIds)->delete();
        $db->table('eod_run_events')->whereIn('run_id', $runIds)->delete();
        $db->table('eod_runs')->whereIn('run_id', $runIds)->delete();
        if ($factorSetIds !== []) {
            $db->table('md_adjustment_factors')->whereIn('factor_set_id', $factorSetIds)->delete();
            $db->table('md_adjustment_factor_sets')->whereIn('factor_set_id', $factorSetIds)->delete();
        }
        if ($configIds !== []) {
            $db->table('md_config_snapshots')->whereIn('config_snapshot_id', $configIds)->delete();
        }
    }

    /** @return array<string,int> table => rows still present for the fixture; empty when clean */
    private function residualCommittedRows(string $date, int $base): array
    {
        $db = $this->marketDataMariaDb();
        $runIds = [$base + 1, $base + 2];
        $publicationIds = array_values(array_unique(array_merge([$base + 3], array_map('intval',
            $db->table('eod_publications')->where('trade_date', $date)->whereIn('run_id', $runIds)->pluck('publication_id')->all()
        ))));
        $configIds = array_map('intval', $db->table('md_config_snapshots')->where('registry_revision', 'r25-'.$date)->pluck('config_snapshot_id')->all());
        $left = [
            'eod_runs' => (int) $db->table('eod_runs')->whereIn('run_id', $runIds)->count(),
            'eod_runs_on_date' => (int) $db->table('eod_runs')->where('trade_date_requested', $date)->count(),
            'eod_publications' => (int) $db->table('eod_publications')->whereIn('publication_id', $publicationIds)->count(),
            'eod_publications_on_date' => (int) $db->table('eod_publications')->where('trade_date', $date)->count(),
            'eod_current_publication_pointer' => (int) $db->table('eod_current_publication_pointer')->where('trade_date', $date)->count(),
            'eod_run_events' => (int) $db->table('eod_run_events')->whereIn('run_id', $runIds)->count(),
            'eod_bars_history' => (int) $db->table('eod_bars_history')->whereIn('publication_id', $publicationIds)->count(),
            'md_publication_lineage_bindings' => (int) $db->table('md_publication_lineage_bindings')->whereIn('publication_id', $publicationIds)->count(),
            'md_config_snapshots' => (int) $db->table('md_config_snapshots')->where('registry_revision', 'r25-'.$date)->count(),
            'md_adjustment_factor_sets' => $configIds === [] ? 0 : (int) $db->table('md_adjustment_factor_sets')->whereIn('config_snapshot_id', $configIds)->count(),
        ];

        return array_filter($left);
    }
}
