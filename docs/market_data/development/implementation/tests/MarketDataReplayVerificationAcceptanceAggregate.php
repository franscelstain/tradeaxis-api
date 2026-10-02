<?php

/**
 * `MD-B18-A002` -- executing full-parent aggregate for the `G05` predicates of
 * `F-MD-B18-A002-017` (`MD_B18_A002_CONSOLIDATED_REMEDIATION_PACKAGE.md`, "G05: executing
 * full-parent aggregate dan semua anggota wajib").
 *
 * Why this exists. Each of these predicates is a claim about a whole family ("all anti-survivorship
 * and as-known isolation fixtures passing", "each of the eight cutoff-bound inputs", ...). The
 * reviewed maps in the test corpus say which executing guard carries each member, and their own
 * tests check that the guard is *named* and *exists*. None of that runs the guard: a member whose
 * production behaviour breaks leaves every existence check green. This runner executes the members.
 *
 * What it does, and does not do:
 *
 *  - The predicate set is not typed in here. It is read from the governed package headings
 *    (`#### <id> -- G05 / ...`) and compared with the declared set in both directions.
 *  - Members are not restated here either. Each predicate declares which reviewed map(s) feed it, and
 *    the map is read from the test class that owns it. Each map is bound to its authority sentence
 *    by a guard in that class; that guard is itself a member, so a member added to or removed from
 *    the authority text turns the predicate red.
 *  - Each member is executed by the real PHPUnit as its own process against the real test class; this
 *    file contains no re-implementation of any behaviour being proven and no stub of a guard.
 *  - A member counts only if it PASSED with at least one assertion. Failed, errored, skipped,
 *    incomplete, risky, zero-assertion and not-executed members all keep the predicate red, and a
 *    skip is never converted to a pass (the MariaDB family predicate requires zero skips).
 *  - A predicate's verdict is computed from its own items only, so one predicate's failure cannot be
 *    absorbed by a sibling, and every item keeps its identity (predicate, source, authority wording,
 *    guard, outcome). The overall verdict is green only when every predicate is.
 */
final class MarketDataReplayVerificationAcceptanceAggregate
{
    public const PACKAGE = 'docs/market_data/development/implementation/MD_B18_A002_CONSOLIDATED_REMEDIATION_PACKAGE.md';

    public const MATRIX = 'docs/market_data/authority/governance/STRATEGY_TO_IMPLEMENTATION_TRACEABILITY_MATRIX.csv';

    public const TEST_DIR = 'tests/Unit/MarketData';

    private const CRITERIA_CLASS = 'B18ReleaseCandidateCriteriaTest';

    private const CRITERIA_BINDING = 'B18ReleaseCandidateCriteriaTest::test_the_criteria_map_names_exactly_what_the_contract_names';

    private const CRITERIA_MAP = [
        'anti' => 'all anti-survivorship and as-known isolation fixtures passing;',
        'degraded' => 'all degraded/negative fixtures producing their expected held/failed/unavailable states without silent repair or denominator shrinkage;',
        'oracle' => 'long-chain ATR and corporate-action results matching independent oracles;',
        'correction' => 'corrected publications preserving their predecessors and switching atomically; and',
    ];

    /**
     * predicate => sources. A `criteria` source is one bullet of the MD-S002 release-candidate list; a
     * `map` source is a reviewed map in a test class, bound to its authority sentence by `binding`.
     *
     * @return array<string,array<int,array<string,mixed>>>
     */
    public static function spec(): array
    {
        $criteria = static function (string $key): array {
            return ['kind' => 'criteria', 'label' => 'MD-S002 release-candidate bullet', 'class' => self::CRITERIA_CLASS,
                'accessor' => 'criteriaMap', 'key' => self::CRITERIA_MAP[$key], 'binding' => [self::CRITERIA_BINDING]];
        };
        $map = static function (string $label, string $class, string $accessor, array $binding, bool $bare = false): array {
            return ['kind' => 'map', 'label' => $label, 'class' => $class, 'accessor' => $accessor, 'binding' => $binding, 'bare' => $bare];
        };

        return [
            'MD-S002-R0005' => [
                $criteria('anti'),
                $map('MD-S050 anti-survivorship fixtures (exact publication)', 'B18AntiSurvivorshipFixtureCorpusTest', 'fixtureMap',
                    ['B18AntiSurvivorshipFixtureCorpusTest::test_the_contract_list_and_the_reviewed_fixture_map_cover_exactly_the_same_cases']),
                $map('MD-S050 anti-survivorship fixtures (as-known)', 'B18AntiSurvivorshipFixtureCorpusTest', 'asKnownFixtureMap',
                    ['B18AntiSurvivorshipFixtureCorpusTest::test_every_required_fixture_is_also_an_as_known_fixture']),
                $map('MD-S003 later-revision isolation kinds', 'B18AsKnownSnapshotIsolationTest', 'laterRevisionGuardMap',
                    ['B18AsKnownSnapshotIsolationTest::test_every_later_revision_kind_is_bound_to_an_executing_guard']),
                $map('MD-S050 anti-future items', 'B18AntiFutureResolutionTest', 'antiFutureMap',
                    ['B18AntiFutureResolutionTest::test_the_anti_future_map_names_exactly_what_the_contract_names']),
            ],
            'MD-S002-R0006' => [
                $criteria('degraded'),
                $map('MD-S003 degraded observation defects', 'B18DegradedObservationDefectCorpusTest', 'defectMap',
                    ['B18DegradedObservationDefectCorpusTest::test_the_contract_line_and_the_reviewed_defect_map_name_exactly_the_same_defects']),
            ],
            'MD-S002-R0007' => [$criteria('oracle')],
            'MD-S002-R0008' => [$criteria('correction')],
            'MD-S003-R0025' => [
                $map('MD-S003 required scenario families (MariaDB)', 'B18ScenarioFamiliesOnMariaDbTest', 'familyMap',
                    ['B18ScenarioFamiliesOnMariaDbTest::test_the_family_map_names_exactly_the_families_the_contract_requires',
                        'B18ScenarioFamiliesOnMariaDbTest::test_the_bullet_map_names_exactly_the_bullets_the_contract_names',
                        'B18ScenarioFamiliesOnMariaDbTest::test_every_bullet_names_one_existing_scenario_and_every_scenario_is_mapped_once',
                        'B18ScenarioFamiliesOnMariaDbTest::test_every_family_test_runs_its_own_family_through_the_bullet_map',
                        'B18ScenarioFamiliesOnMariaDbTest::test_these_scenarios_really_run_on_mariadb'], true),
            ],
            'MD-S004-R0002' => [
                $map('MD-S004 cutoff-bounded input kinds', 'B18PointInTimeInputContractTest', 'cutoffBoundedInputMap',
                    ['B18PointInTimeInputContractTest::test_the_cutoff_bounded_input_map_names_exactly_what_the_contract_names']),
            ],
            'MD-S004-R0003' => [
                $map('MD-S004 no-backfill clauses', 'B18PointInTimeInputContractTest', 'noBackfillMap',
                    ['B18PointInTimeInputContractTest::test_the_no_backfill_map_names_exactly_what_the_contract_names']),
            ],
            'MD-S004-R0005' => [
                $map('MD-S004 survivorship and revision claims', 'B18PointInTimeInputContractTest', 'survivorshipMap',
                    ['B18PointInTimeInputContractTest::test_the_survivorship_map_names_exactly_what_the_contract_names']),
            ],
            'MD-S004-R0008' => [
                $map('MD-S004 acceptance fixtures', 'B18PointInTimeInputContractTest', 'acceptanceFixtureMap',
                    ['B18PointInTimeInputContractTest::test_the_acceptance_fixture_map_names_exactly_what_the_contract_names']),
            ],
        ];
    }

    /** @return array<int,string> the G05 predicates the governed package names, sorted */
    public static function governedPredicates(string $root): array
    {
        $path = $root.'/'.self::PACKAGE;
        if (! is_file($path)) {
            throw new RuntimeException('G05_PACKAGE_UNREADABLE');
        }
        $text = (string) file_get_contents($path);
        // Only F-MD-B18-A002-017's own section: other findings carry G05 labels for predicates that are
        // already proven (MD-S050-R0017 under F-016), and they are not this aggregate's obligation.
        $start = strpos($text, "\n### F-MD-B18-A002-017 ");
        if ($start === false) {
            throw new RuntimeException('G05_F017_SECTION_MISSING');
        }
        $end = strpos($text, "\n### ", $start + 5);
        $section = substr($text, $start, $end === false ? null : $end - $start);
        preg_match_all('/^#### (MD-S\d+-R\d+) \x{2014} G05 \//mu', $section, $m);
        $ids = array_values(array_unique($m[1]));
        sort($ids);

        return $ids;
    }

    /**
     * Resolve every declared predicate to its items. Never executes a guard.
     *
     * @return array{predicates:array<string,array<string,mixed>>,errors:array<int,string>}
     */
    public static function resolve(string $root, ?array $only = null, ?string $matrixPath = null): array
    {
        $errors = [];
        $out = [];
        $matrix = self::matrixRows($matrixPath ?? $root.'/'.self::MATRIX);

        foreach (self::spec() as $id => $sources) {
            if ($only !== null && ! in_array($id, $only, true)) {
                continue;
            }
            $row = $matrix[$id] ?? null;
            if ($row === null || ($row['active'] ?? '') !== 'YES' || ($row['primary_stage'] ?? '') !== 'MD-B18'
                || ($row['applicability'] ?? '') !== 'MANDATORY') {
                $errors[] = 'PREDICATE_NOT_A_CURRENT_B18_MANDATORY_ROW:'.$id;
            }
            $items = [];
            $bindings = [];
            foreach ($sources as $source) {
                foreach ($source['binding'] as $b) {
                    $bindings[$b] = $source['label'];
                }
                try {
                    $resolved = self::sourceItems($root, $source);
                } catch (Throwable $e) {
                    $errors[] = 'SOURCE_UNRESOLVED:'.$id.':'.$source['label'].':'.$e->getMessage();
                    continue;
                }
                if ($source['kind'] === 'criteria' && $row !== null
                    && substr(rtrim((string) $row['rule_text']), -strlen($source['key'])) !== $source['key']) {
                    $errors[] = 'CRITERION_TEXT_DOES_NOT_MATCH_MATRIX_ROW:'.$id;
                }
                foreach ($resolved as $item) {
                    $items[] = $item + ['predicate' => $id, 'source' => $source['label'], 'role' => 'member'];
                }
            }
            foreach ($bindings as $ref => $label) {
                $items[] = ['predicate' => $id, 'source' => $label, 'label' => 'authority binding of the map', 'ref' => $ref, 'role' => 'authority_binding'];
            }
            if ($items === []) {
                $errors[] = 'PREDICATE_HAS_NO_ITEMS:'.$id;
            }
            $out[$id] = ['predicate' => $id, 'items' => $items];
        }

        return ['predicates' => $out, 'errors' => $errors];
    }

    /** @return array<int,array<string,string>> items of one source: label + one guard ref each */
    public static function sourceItems(string $root, array $source): array
    {
        $class = $source['class'];
        if (! class_exists($class, false)) {
            require_once $root.'/'.self::TEST_DIR.'/'.$class.'.php';
        }
        $reflection = new ReflectionClass($class);
        $method = new ReflectionMethod($class, $source['accessor']);
        $method->setAccessible(true);
        $map = $method->invoke($reflection->newInstanceWithoutConstructor());
        if (! is_array($map) || $map === []) {
            throw new RuntimeException('EMPTY_MAP');
        }
        if ($source['kind'] === 'criteria') {
            if (! isset($map[$source['key']])) {
                throw new RuntimeException('CRITERION_NOT_IN_MAP');
            }
            $refs = $map[$source['key']];
            $items = [];
            foreach ($refs as $ref) {
                $items[] = ['label' => $ref, 'ref' => $ref];
            }

            return $items;
        }
        $items = [];
        foreach ($map as $label => $value) {
            $refs = [];
            $value = (array) $value;
            array_walk_recursive($value, static function ($v) use (&$refs, $class, $source) {
                if (! is_string($v)) {
                    return;
                }
                if (! empty($source['bare']) && preg_match('/^test_[A-Za-z0-9_]+$/', $v) === 1) {
                    $refs[] = $class.'::'.$v;
                } elseif (preg_match('/^[A-Za-z0-9_]+::test_[A-Za-z0-9_]+$/', $v) === 1) {
                    $refs[] = $v;
                }
            });
            if (count($refs) !== 1) {
                throw new RuntimeException('ITEM_DOES_NOT_NAME_EXACTLY_ONE_GUARD:'.$label);
            }
            $items[] = ['label' => (string) $label, 'ref' => $refs[0]];
        }

        return $items;
    }

    /** @return array<string,array<string,string>> */
    private static function matrixRows(string $path): array
    {
        $h = fopen($path, 'rb');
        if (! $h) {
            throw new RuntimeException('MATRIX_UNREADABLE');
        }
        $header = fgetcsv($h);
        $header[0] = preg_replace('/^\xEF\xBB\xBF/', '', $header[0]);
        $rows = [];
        while (($v = fgetcsv($h)) !== false) {
            if (count($v) === count($header)) {
                $row = array_combine($header, $v);
                $rows[$row['rule_id']] = $row;
            }
        }
        fclose($h);

        return $rows;
    }

    /**
     * Execute guards: one PHPUnit process per test class (one file per invocation), classes in
     * parallel up to `$parallel`, every guard reported by its own outcome.
     *
     * @param  array<int,string>  $refs  `Class::method`
     * @return array<string,array<string,mixed>> ref => [status, assertions, detail]
     */
    public static function execute(string $root, array $refs, ?string $testDir = null, int $parallel = 4): array
    {
        $testDir = $testDir ?? $root.'/'.self::TEST_DIR;
        $byClass = [];
        foreach (array_unique($refs) as $ref) {
            [$class, $method] = explode('::', $ref, 2);
            $byClass[$class][] = $method;
        }
        $jobs = [];
        foreach ($byClass as $class => $methods) {
            $tmp = sys_get_temp_dir().'/md_b18_g05_'.uniqid('', true);
            $filter = '/^'.preg_quote($class, '/').'::('.implode('|', array_map(static function ($m) { return preg_quote($m, '/'); }, $methods)).')(?: with data set .*)?$/';
            $jobs[] = ['class' => $class, 'methods' => $methods, 'junit' => $tmp.'.xml', 'log' => $tmp.'.log',
                'cmd' => [PHP_BINARY, $root.'/vendor/phpunit/phpunit/phpunit', $testDir.'/'.$class.'.php', '--filter', $filter, '--log-junit', $tmp.'.xml']];
        }

        $outcomes = [];
        $running = [];
        $queue = $jobs;
        while ($queue !== [] || $running !== []) {
            while ($queue !== [] && count($running) < max(1, $parallel)) {
                $job = array_shift($queue);
                $spec = [0 => ['pipe', 'r'], 1 => ['file', $job['log'], 'w'], 2 => ['file', $job['log'], 'a']];
                $proc = proc_open($job['cmd'], $spec, $pipes, $root);
                if (! is_resource($proc)) {
                    foreach ($job['methods'] as $m) {
                        $outcomes[$job['class'].'::'.$m] = ['status' => 'NOT_EXECUTED', 'assertions' => 0, 'detail' => 'process could not start'];
                    }
                    continue;
                }
                fclose($pipes[0]);
                $running[] = $job + ['proc' => $proc, 'started' => microtime(true)];
            }
            foreach ($running as $i => $job) {
                $status = proc_get_status($job['proc']);
                if ($status['running'] && microtime(true) - $job['started'] < 900) {
                    continue;
                }
                if ($status['running']) {
                    proc_terminate($job['proc']);
                }
                proc_close($job['proc']);
                $outcomes = array_merge($outcomes, self::readJunit($job));
                @unlink($job['junit']);
                @unlink($job['log']);
                unset($running[$i]);
            }
            if ($running !== []) {
                usleep(100000);
            }
        }

        return $outcomes;
    }

    /** @return array<string,array<string,mixed>> */
    private static function readJunit(array $job): array
    {
        $out = [];
        $log = is_file($job['log']) ? substr((string) file_get_contents($job['log']), -400) : '';
        $found = [];
        if (is_file($job['junit']) && filesize($job['junit']) > 0) {
            $xml = @simplexml_load_file($job['junit']);
            if ($xml !== false) {
                foreach ($xml->xpath('//testcase') as $case) {
                    $name = (string) $case['name'];
                    $base = preg_replace('/ with data set .*$/s', '', $name);
                    $status = 'PASSED';
                    if (isset($case->failure)) {
                        $status = 'FAILED';
                    } elseif (isset($case->error)) {
                        $status = 'ERROR';
                    } elseif (isset($case->skipped)) {
                        $status = 'SKIPPED';
                    } elseif (isset($case->warning)) {
                        $status = 'WARNING';
                    }
                    $assertions = (int) $case['assertions'];
                    $detail = $status === 'PASSED' ? '' : trim(substr((string) ($case->failure ?? $case->error ?? $case->warning ?? ''), 0, 300));
                    if (! isset($found[$base])) {
                        $found[$base] = ['status' => $status, 'assertions' => $assertions, 'detail' => $detail, 'cases' => 1];
                    } else {
                        $found[$base]['cases']++;
                        $found[$base]['assertions'] += $assertions;
                        if ($status !== 'PASSED' && $found[$base]['status'] === 'PASSED') {
                            $found[$base]['status'] = $status;
                            $found[$base]['detail'] = $detail;
                        }
                    }
                }
            }
        }
        foreach ($job['methods'] as $method) {
            $ref = $job['class'].'::'.$method;
            $out[$ref] = $found[$method] ?? ['status' => 'NOT_EXECUTED', 'assertions' => 0, 'detail' => 'not present in the test run: '.$log];
        }

        return $out;
    }

    /**
     * Pure verdict. Green only when every declared predicate has items and every item's guard PASSED
     * with at least one assertion.
     *
     * @param  array<string,array<string,mixed>>  $resolved  from resolve()['predicates']
     * @param  array<string,array<string,mixed>>  $outcomes  from execute()
     * @param  array<int,string>  $governed  the governed G05 set
     * @param  array<int,string>|null  $only  predicates evaluated in this run (null = all)
     * @return array<string,mixed>
     */
    public static function aggregate(array $resolved, array $outcomes, array $governed, ?array $only = null): array
    {
        $errors = [];
        $expected = $only === null ? $governed : array_values(array_intersect($governed, $only));
        foreach ($expected as $id) {
            if (! isset($resolved[$id])) {
                $errors[] = 'GOVERNED_PREDICATE_NOT_DECLARED:'.$id;
            }
        }
        foreach (array_keys($resolved) as $id) {
            if (! in_array($id, $governed, true)) {
                $errors[] = 'DECLARED_PREDICATE_NOT_GOVERNED:'.$id;
            }
        }

        $predicates = [];
        foreach ($expected as $id) {
            $items = isset($resolved[$id]) ? $resolved[$id]['items'] : [];
            $rows = [];
            $red = [];
            foreach ($items as $item) {
                $o = $outcomes[$item['ref']] ?? ['status' => 'NOT_EXECUTED', 'assertions' => 0, 'detail' => 'no outcome recorded'];
                $green = $o['status'] === 'PASSED' && (int) $o['assertions'] > 0;
                $reason = $green ? null : ($o['status'] === 'PASSED' ? 'PASSED_WITH_ZERO_ASSERTIONS' : $o['status']);
                $rows[] = ['role' => $item['role'], 'source' => $item['source'], 'item' => $item['label'], 'guard' => $item['ref'],
                    'status' => $o['status'], 'assertions' => (int) $o['assertions'], 'green' => $green, 'reason' => $reason, 'detail' => $o['detail'] ?? ''];
                if (! $green) {
                    $red[] = ['guard' => $item['ref'], 'item' => $item['label'], 'reason' => $reason];
                }
            }
            $members = array_filter($rows, static function ($r) { return $r['role'] === 'member'; });
            $problem = null;
            if ($items === [] || $members === []) {
                $problem = 'PREDICATE_HAS_NO_EXECUTED_MEMBERS';
            }
            $predicates[$id] = [
                'verdict' => ($red === [] && $problem === null) ? 'GREEN' : 'RED',
                'problem' => $problem,
                'member_items' => count($members),
                'items' => count($rows),
                'distinct_guards' => count(array_unique(array_column($rows, 'guard'))),
                'red' => $red,
                'rows' => $rows,
            ];
        }

        $overallGreen = $errors === [] && $predicates !== [];
        foreach ($predicates as $p) {
            $overallGreen = $overallGreen && $p['verdict'] === 'GREEN';
        }

        return ['verdict' => $overallGreen ? 'GREEN' : 'RED', 'errors' => $errors, 'predicates' => $predicates];
    }

    /**
     * Resolve, execute and aggregate.
     *
     * @param  array<int,string>|null  $only
     * @return array<string,mixed>
     */
    public static function run(string $root, ?array $only = null, int $parallel = 4): array
    {
        $resolved = self::resolve($root, $only);
        $governed = self::governedPredicates($root);
        $refs = [];
        foreach ($resolved['predicates'] as $p) {
            foreach ($p['items'] as $item) {
                $refs[] = $item['ref'];
            }
        }
        $outcomes = self::execute($root, $refs, null, $parallel);
        $result = self::aggregate($resolved['predicates'], $outcomes, $governed, $only);
        $result['errors'] = array_merge($resolved['errors'], $result['errors']);
        if ($resolved['errors'] !== []) {
            $result['verdict'] = 'RED';
        }
        $result['guards_executed'] = count(array_unique($refs));

        return $result;
    }
}

if (isset($_SERVER['SCRIPT_FILENAME']) && realpath($_SERVER['SCRIPT_FILENAME']) === __FILE__) {
    $root = dirname(__DIR__, 5);
    require_once $root.'/vendor/autoload.php';
    $only = null;
    $parallel = 4;
    foreach (array_slice($argv, 1) as $arg) {
        if (strpos($arg, '--only=') === 0) {
            $only = array_values(array_filter(explode(',', substr($arg, 7))));
        } elseif (strpos($arg, '--parallel=') === 0) {
            $parallel = max(1, (int) substr($arg, 11));
        }
    }
    $result = MarketDataReplayVerificationAcceptanceAggregate::run($root, $only, $parallel);
    $result['gate'] = 'MarketDataReplayVerificationAcceptanceAggregate';
    $result['generated_at'] = date(DATE_ATOM);
    echo json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES).PHP_EOL;
    exit($result['verdict'] === 'GREEN' ? 0 : 1);
}
