<?php

use PHPUnit\Framework\TestCase;

require_once dirname(__DIR__, 3).'/docs/market_data/development/implementation/tests/MarketDataReplayVerificationAcceptanceAggregate.php';

/**
 * `MD-B18-A002` -- `F-MD-B18-A002-017` `G05`: the executing full-parent aggregate.
 *
 * The predicates in scope are claims about whole families ("all anti-survivorship and as-known
 * isolation fixtures passing", "each of the eight cutoff-bound inputs", ...). The reviewed maps in the
 * corpus say which guard carries each member and their own tests check that the guard exists. This
 * class proves the runner that *executes* them:
 *
 *  - the predicate set is the governed one, and every predicate resolves to real members drawn from
 *    the reviewed maps, each bound to its authority sentence by a guard that is itself executed;
 *  - the verdict function fails closed and keeps identity: one bad member turns its own predicate red,
 *    names the member, and does not spread to a predicate that does not use it;
 *  - the executor reports the real PHPUnit outcome of each member -- pass, fail, error, skip,
 *    incomplete, risky/zero-assertion, or not executed -- against real (tiny) test files;
 *  - the real members do execute, and a predicate whose members are skipped is not green.
 *
 * The runner contains no copy of any behaviour it aggregates; these tests never stub a member.
 */
class B18ReleaseCandidateAcceptanceAggregateTest extends TestCase
{
    private const AGG = MarketDataReplayVerificationAcceptanceAggregate::class;

    /** Authority-stated item counts (the sentences the maps are bound to). */
    private const AUTHORITY_ITEM_COUNTS = [
        'MD-S004-R0002' => 8,   // observations, identity, calendar, status, event, factor, config, formula
        'MD-S004-R0003' => 5,   // universe, symbol, sector, action verification, current publication
        'MD-S004-R0005' => 3,   // three sentences
        'MD-S004-R0008' => 7,   // seven acceptance fixtures
        'MD-S003-R0025' => 12,  // six required scenario families, on MariaDB and on the supported test mirror
    ];

    private function root(): string
    {
        return dirname(__DIR__, 3);
    }

    /** @return array<string,array<string,mixed>> */
    private function resolved(): array
    {
        $resolved = (self::AGG)::resolve($this->root());
        $this->assertSame([], $resolved['errors'], 'the aggregate could not resolve its members');

        return $resolved['predicates'];
    }

    /** All members and bindings PASSED with assertions. @return array<string,array<string,mixed>> */
    private function allGreen(array $resolved): array
    {
        $out = [];
        foreach ($resolved as $p) {
            foreach ($p['items'] as $item) {
                $out[$item['ref']] = ['status' => 'PASSED', 'assertions' => 3, 'detail' => ''];
            }
        }

        return $out;
    }

    private function governed(): array
    {
        return (self::AGG)::governedPredicates($this->root());
    }

    // ---- the governed set and the members -------------------------------------------------------

    public function test_the_declared_predicate_set_is_exactly_the_governed_g05_set_of_f017(): void
    {
        $governed = $this->governed();
        $declared = array_keys((self::AGG)::spec());
        sort($declared);

        $this->assertSame($governed, $declared,
            'the aggregate declares a different predicate set than F-MD-B18-A002-017 G05 in the governed package');
        $this->assertCount(9, $governed);
    }

    public function test_every_predicate_resolves_to_members_from_the_reviewed_maps_and_a_current_b18_mandatory_row(): void
    {
        $resolved = $this->resolved();

        foreach ($this->governed() as $id) {
            $this->assertArrayHasKey($id, $resolved, $id.' is governed but resolved to nothing');
            $members = array_filter($resolved[$id]['items'], static function ($i) { return $i['role'] === 'member'; });
            $this->assertNotSame([], $members, $id.' has no executable members');
        }
    }

    public function test_every_item_names_exactly_one_existing_public_guard(): void
    {
        $missing = [];
        foreach ($this->resolved() as $id => $p) {
            foreach ($p['items'] as $item) {
                [$class, $method] = explode('::', $item['ref'], 2);
                $file = $this->root().'/tests/Unit/MarketData/'.$class.'.php';
                if (! is_file($file)) {
                    $missing[] = $id.' -> '.$item['ref'].' (no such test file)';
                    continue;
                }
                if (! class_exists($class, false)) {
                    require_once $file;
                }
                if (! method_exists($class, $method) || ! (new ReflectionMethod($class, $method))->isPublic()
                    || strpos($method, 'test_') !== 0) {
                    $missing[] = $id.' -> '.$item['ref'];
                }
            }
        }

        $this->assertSame([], $missing, 'these items name a guard that is not a public test method');
    }

    public function test_the_authority_stated_item_counts_are_matched_by_the_members_and_each_map_has_an_executed_authority_binding(): void
    {
        $resolved = $this->resolved();

        foreach (self::AUTHORITY_ITEM_COUNTS as $id => $count) {
            $members = array_values(array_filter($resolved[$id]['items'], static function ($i) { return $i['role'] === 'member'; }));
            $this->assertCount($count, $members, $id.' carries a different number of members than its authority sentence names');
        }

        foreach ((self::AGG)::spec() as $id => $sources) {
            foreach ($sources as $source) {
                $this->assertNotSame([], $source['binding'], $id.': a source with no authority binding guard');
            }
            $bindingItems = array_filter($resolved[$id]['items'], static function ($i) { return $i['role'] === 'authority_binding'; });
            $this->assertNotSame([], $bindingItems, $id.' executes no authority binding');
        }
    }

    public function test_predicates_that_share_a_guard_keep_their_own_item_identity(): void
    {
        $resolved = $this->resolved();
        $members = array_values(array_filter($resolved['MD-S004-R0002']['items'], static function ($i) { return $i['role'] === 'member'; }));
        $labels = array_column($members, 'label');

        $this->assertContains('factor', $labels);
        $this->assertContains('formula', $labels);
        $this->assertGreaterThan(count(array_unique(array_column($members, 'ref'))), count($members),
            'factor and formula are separate members of the criterion even where one guard carries both');
    }

    // ---- the verdict function: fail closed, identity kept ---------------------------------------

    public function test_when_every_member_passes_every_predicate_and_the_parent_are_green(): void
    {
        $resolved = $this->resolved();
        $result = (self::AGG)::aggregate($resolved, $this->allGreen($resolved), $this->governed());

        $this->assertSame([], $result['errors']);
        $this->assertSame('GREEN', $result['verdict']);
        foreach ($result['predicates'] as $id => $p) {
            $this->assertSame('GREEN', $p['verdict'], $id);
            $this->assertSame([], $p['red']);
        }
    }

    /**
     * Every member of the predicate, failed in turn, turns the predicate red and is named; a predicate
     * that does not use that guard stays green; and the parent is red. One failing child cannot be
     * accepted, and a sibling cannot absorb it.
     */
    private function assertEveryMemberIsLoadBearingForItsPredicate(string $id): void
    {
        $resolved = $this->resolved();
        $members = array_values(array_filter($resolved[$id]['items'], static function ($i) { return $i['role'] === 'member'; }));
        $this->assertNotSame([], $members, $id.' has no members');

        foreach (array_values(array_unique(array_column($members, 'ref'))) as $ref) {
            $outcomes = $this->allGreen($resolved);
            $outcomes[$ref] = ['status' => 'FAILED', 'assertions' => 1, 'detail' => 'injected'];
            $result = (self::AGG)::aggregate($resolved, $outcomes, $this->governed());

            $this->assertSame('RED', $result['verdict'], $ref);
            $this->assertSame('RED', $result['predicates'][$id]['verdict'], $id.' stayed green with '.$ref.' failing');
            $this->assertContains($ref, array_column($result['predicates'][$id]['red'], 'guard'), $ref.' is not named');

            foreach ($result['predicates'] as $other => $p) {
                $uses = false;
                foreach ($resolved[$other]['items'] as $item) {
                    $uses = $uses || $item['ref'] === $ref;
                }
                $this->assertSame($uses ? 'RED' : 'GREEN', $p['verdict'],
                    $other.' must be '.($uses ? 'red (it uses '.$ref.')' : 'green (it does not use '.$ref.')'));
            }
        }
    }
    public function test_md_s002_r0005_turns_red_when_any_anti_survivorship_or_as_known_isolation_member_fails(): void
    {
        $this->assertEveryMemberIsLoadBearingForItsPredicate('MD-S002-R0005');
    }

    public function test_md_s002_r0006_turns_red_when_any_degraded_or_negative_member_fails(): void
    {
        $this->assertEveryMemberIsLoadBearingForItsPredicate('MD-S002-R0006');
    }

    public function test_md_s002_r0007_turns_red_when_any_atr_or_corporate_action_oracle_member_fails(): void
    {
        $this->assertEveryMemberIsLoadBearingForItsPredicate('MD-S002-R0007');
    }

    public function test_md_s002_r0008_turns_red_when_any_correction_or_atomic_switch_member_fails(): void
    {
        $this->assertEveryMemberIsLoadBearingForItsPredicate('MD-S002-R0008');
    }

    public function test_md_s003_r0025_turns_red_when_any_scenario_family_member_fails(): void
    {
        $this->assertEveryMemberIsLoadBearingForItsPredicate('MD-S003-R0025');
    }

    public function test_md_s004_r0002_turns_red_when_any_of_its_eight_cutoff_bound_input_members_fails(): void
    {
        $this->assertEveryMemberIsLoadBearingForItsPredicate('MD-S004-R0002');
    }

    public function test_md_s004_r0003_turns_red_when_any_of_its_five_no_backfill_members_fails(): void
    {
        $this->assertEveryMemberIsLoadBearingForItsPredicate('MD-S004-R0003');
    }

    public function test_md_s004_r0005_turns_red_when_any_of_its_three_survivorship_members_fails(): void
    {
        $this->assertEveryMemberIsLoadBearingForItsPredicate('MD-S004-R0005');
    }

    public function test_md_s004_r0008_turns_red_when_any_of_its_seven_acceptance_fixture_members_fails(): void
    {
        $this->assertEveryMemberIsLoadBearingForItsPredicate('MD-S004-R0008');
    }

    /** @return array<string,array{0:string,1:array<string,mixed>|null}> */
    public function nonPassingOutcomes(): array
    {
        return [
            'failed' => ['FAILED', ['status' => 'FAILED', 'assertions' => 2, 'detail' => 'x']],
            'errored' => ['ERROR', ['status' => 'ERROR', 'assertions' => 0, 'detail' => 'x']],
            'skipped' => ['SKIPPED', ['status' => 'SKIPPED', 'assertions' => 0, 'detail' => 'x']],
            'warning or risky' => ['WARNING', ['status' => 'WARNING', 'assertions' => 1, 'detail' => 'x']],
            'passed with zero assertions' => ['PASSED_WITH_ZERO_ASSERTIONS', ['status' => 'PASSED', 'assertions' => 0, 'detail' => '']],
            'not executed' => ['NOT_EXECUTED', ['status' => 'NOT_EXECUTED', 'assertions' => 0, 'detail' => 'x']],
            'no outcome recorded at all' => ['NOT_EXECUTED', null],
        ];
    }

    /**
     * Only PASSED with at least one assertion counts. Every other outcome keeps the predicate red with
     * its own reason -- in particular a skip is never converted to a pass.
     *
     * @dataProvider nonPassingOutcomes
     *
     * @param  array<string,mixed>|null  $outcome
     */
    public function test_only_a_passed_member_with_assertions_counts(string $reason, ?array $outcome): void
    {
        $resolved = $this->resolved();
        $victim = $resolved['MD-S004-R0008']['items'][0];
        $outcomes = $this->allGreen($resolved);
        if ($outcome === null) {
            unset($outcomes[$victim['ref']]);
        } else {
            $outcomes[$victim['ref']] = $outcome;
        }
        $result = (self::AGG)::aggregate($resolved, $outcomes, $this->governed());

        $this->assertSame('RED', $result['predicates']['MD-S004-R0008']['verdict']);
        $reasons = array_column($result['predicates']['MD-S004-R0008']['red'], 'reason');
        $this->assertContains($reason, $reasons);
        $this->assertSame('RED', $result['verdict']);
    }

    public function test_a_failing_authority_binding_keeps_the_predicate_red_although_every_member_passes(): void
    {
        $resolved = $this->resolved();
        $binding = null;
        foreach ($resolved['MD-S004-R0003']['items'] as $item) {
            if ($item['role'] === 'authority_binding') {
                $binding = $item;
            }
        }
        $this->assertNotNull($binding);

        $outcomes = $this->allGreen($resolved);
        $outcomes[$binding['ref']] = ['status' => 'FAILED', 'assertions' => 1, 'detail' => 'map no longer equals the authority sentence'];
        $result = (self::AGG)::aggregate($resolved, $outcomes, $this->governed());

        $this->assertSame('RED', $result['predicates']['MD-S004-R0003']['verdict']);
        $this->assertSame('authority_binding', $result['predicates']['MD-S004-R0003']['rows'][count($result['predicates']['MD-S004-R0003']['rows']) - 1]['role']);
    }

    public function test_a_failing_shared_guard_reddens_every_item_that_uses_it_and_each_stays_identifiable(): void
    {
        $resolved = $this->resolved();
        $shared = null;
        foreach ($resolved['MD-S004-R0002']['items'] as $item) {
            if ($item['role'] === 'member' && $item['label'] === 'factor') {
                $shared = $item['ref'];
            }
        }
        $this->assertNotNull($shared);

        $outcomes = $this->allGreen($resolved);
        $outcomes[$shared] = ['status' => 'FAILED', 'assertions' => 1, 'detail' => 'x'];
        $result = (self::AGG)::aggregate($resolved, $outcomes, $this->governed());

        $items = array_column($result['predicates']['MD-S004-R0002']['red'], 'item');
        $this->assertContains('factor', $items);
        $this->assertContains('formula', $items, 'formula uses the same guard and must be reported red as its own item');
    }

    public function test_a_governed_predicate_that_is_not_declared_or_has_no_items_cannot_be_green(): void
    {
        $resolved = $this->resolved();
        $outcomes = $this->allGreen($resolved);

        $missing = $resolved;
        unset($missing['MD-S002-R0007']);
        $a = (self::AGG)::aggregate($missing, $outcomes, $this->governed());
        $this->assertSame('RED', $a['verdict']);
        $this->assertContains('GOVERNED_PREDICATE_NOT_DECLARED:MD-S002-R0007', $a['errors']);

        $empty = $resolved;
        $empty['MD-S002-R0007']['items'] = [];
        $b = (self::AGG)::aggregate($empty, $outcomes, $this->governed());
        $this->assertSame('RED', $b['verdict']);
        $this->assertSame('PREDICATE_HAS_NO_EXECUTED_MEMBERS', $b['predicates']['MD-S002-R0007']['problem']);

        $bindingOnly = $resolved;
        $bindingOnly['MD-S002-R0007']['items'] = array_values(array_filter($resolved['MD-S002-R0007']['items'], static function ($i) { return $i['role'] === 'authority_binding'; }));
        $c = (self::AGG)::aggregate($bindingOnly, $outcomes, $this->governed());
        $this->assertSame('RED', $c['predicates']['MD-S002-R0007']['verdict'], 'a predicate whose members all disappeared is not carried by its binding alone');
    }

    public function test_a_declared_predicate_that_is_not_governed_is_reported(): void
    {
        $resolved = $this->resolved();
        $resolved['MD-S999-R0001'] = ['predicate' => 'MD-S999-R0001', 'items' => $resolved['MD-S002-R0007']['items']];
        $result = (self::AGG)::aggregate($resolved, $this->allGreen($this->resolved()), $this->governed());

        $this->assertSame('RED', $result['verdict']);
        $this->assertContains('DECLARED_PREDICATE_NOT_GOVERNED:MD-S999-R0001', $result['errors']);
    }

    // ---- the authority ties: matrix row state, criterion wording, one guard per item -------------

    /** A copy of the traceability matrix with one row edited; the caller unlinks the returned path. */
    private function matrixWith(string $ruleId, array $changes): string
    {
        $src = fopen($this->root().'/'.(self::AGG)::MATRIX, 'rb');
        $path = sys_get_temp_dir().'/md_b18_g05_matrix_'.uniqid('', true).'.csv';
        $dst = fopen($path, 'wb');
        $bom = fread($src, 3);
        rewind($src);
        if ($bom === "\xEF\xBB\xBF") {
            fwrite($dst, $bom);
            fseek($src, 3);
        }
        $header = fgetcsv($src);
        fputcsv($dst, $header);
        $edited = 0;
        while (($row = fgetcsv($src)) !== false) {
            if (count($row) === count($header) && $row[0] === $ruleId) {
                $named = array_combine($header, $row);
                foreach ($changes as $column => $value) {
                    $named[$column] = $value;
                }
                $row = array_values($named);
                $edited++;
            }
            fputcsv($dst, $row);
        }
        fclose($src);
        fclose($dst);
        $this->assertSame(1, $edited, 'the matrix row to perturb was not found exactly once');

        return $path;
    }

    public function test_a_predicate_that_is_not_a_current_b18_mandatory_row_is_reported(): void
    {
        foreach ([['primary_stage', 'MD-B21'], ['applicability', 'REFERENCE_ONLY'], ['active', 'NO']] as [$column, $value]) {
            $path = $this->matrixWith('MD-S002-R0007', [$column => $value]);
            $resolved = (self::AGG)::resolve($this->root(), ['MD-S002-R0007'], $path);
            unlink($path);

            $this->assertContains('PREDICATE_NOT_A_CURRENT_B18_MANDATORY_ROW:MD-S002-R0007', $resolved['errors'], $column);
        }

        $control = (self::AGG)::resolve($this->root(), ['MD-S002-R0007']);
        $this->assertSame([], $control['errors'], 'the unperturbed matrix resolves clean');
    }

    public function test_a_criterion_whose_wording_no_longer_ends_its_matrix_row_text_is_reported(): void
    {
        $path = $this->matrixWith('MD-S002-R0007', ['rule_text' => '- some other criterion;']);
        $resolved = (self::AGG)::resolve($this->root(), ['MD-S002-R0007'], $path);
        unlink($path);

        $this->assertContains('CRITERION_TEXT_DOES_NOT_MATCH_MATRIX_ROW:MD-S002-R0007', $resolved['errors']);
    }

    /** @return array<string,array{0:array<string,mixed>,1:bool}> */
    public function mapItemShapes(): array
    {
        return [
            'exactly one guard' => [['A' => 'G05FixtureMapHolder::test_one'], true],
            'a guard inside a nested descriptor' => [['A' => ['G05FixtureMapHolder::test_one', 'TemporalIdentityRepository::readProjectedUniverseAsOf', 2]], true],
            'two guards' => [['A' => ['G05FixtureMapHolder::test_one', 'G05FixtureMapHolder::test_two']], false],
            'no guard' => [['A' => 'not a guard reference'], false],
        ];
    }

    /**
     * An item names exactly one executing guard. Two would let a passing sibling stand for it; none
     * would leave it with nothing to execute.
     *
     * @dataProvider mapItemShapes
     *
     * @param  array<string,mixed>  $map
     */
    public function test_a_map_item_must_name_exactly_one_guard(array $map, bool $accepted): void
    {
        G05FixtureMapHolder::$map = $map;
        $source = ['kind' => 'map', 'label' => 'fixture', 'class' => 'G05FixtureMapHolder', 'accessor' => 'theMap', 'binding' => [], 'bare' => false];

        if ($accepted) {
            $items = (self::AGG)::sourceItems($this->root(), $source);
            $this->assertSame(['G05FixtureMapHolder::test_one'], array_column($items, 'ref'));

            return;
        }

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('ITEM_DOES_NOT_NAME_EXACTLY_ONE_GUARD');
        (self::AGG)::sourceItems($this->root(), $source);
    }

    // ---- the executor: the real PHPUnit outcome of each member ----------------------------------

    private function fixtureDir(): string
    {
        $dir = sys_get_temp_dir().'/md_b18_g05_fixture_'.uniqid('', true);
        mkdir($dir, 0775, true);
        file_put_contents($dir.'/G05ExecutorOutcomesFixtureTest.php', <<<'PHP'
<?php
use PHPUnit\Framework\TestCase;

class G05ExecutorOutcomesFixtureTest extends TestCase
{
    public function test_passes(): void { $this->assertTrue(true); }
    public function test_also_passes(): void { $this->assertSame(1, 1); }
    public function test_fails(): void { $this->assertTrue(false, 'deliberate'); }
    public function test_errors(): void { throw new RuntimeException('deliberate'); }
    public function test_is_skipped(): void { $this->markTestSkipped('deliberate'); }
    public function test_is_incomplete(): void { $this->markTestIncomplete('deliberate'); }
    public function test_asserts_nothing(): void { $x = 1; }
    /** @dataProvider sets */
    public function test_with_data_sets($value): void { $this->assertTrue($value); }
    public function sets(): array { return [[true], [false]]; }
}
PHP
        );

        return $dir;
    }

    public function test_the_executor_reports_the_real_outcome_of_each_member_and_isolates_them_from_each_other(): void
    {
        $dir = $this->fixtureDir();
        $c = 'G05ExecutorOutcomesFixtureTest::';
        $refs = [$c.'test_passes', $c.'test_also_passes', $c.'test_fails', $c.'test_errors', $c.'test_is_skipped',
            $c.'test_is_incomplete', $c.'test_asserts_nothing', $c.'test_with_data_sets', $c.'test_does_not_exist',
            'G05ExecutorNoSuchFixtureTest::test_x'];

        $out = (self::AGG)::execute($this->root(), $refs, $dir, 2);

        $this->assertSame('PASSED', $out[$c.'test_passes']['status']);
        $this->assertGreaterThan(0, $out[$c.'test_passes']['assertions']);
        $this->assertSame('PASSED', $out[$c.'test_also_passes']['status'], 'a sibling failing must not change a passing member');
        $this->assertSame('FAILED', $out[$c.'test_fails']['status']);
        $this->assertSame('ERROR', $out[$c.'test_errors']['status']);
        $this->assertSame('SKIPPED', $out[$c.'test_is_skipped']['status']);
        $this->assertSame('SKIPPED', $out[$c.'test_is_incomplete']['status'], 'incomplete is reported by PHPUnit as a skip');
        $this->assertSame(0, $out[$c.'test_asserts_nothing']['assertions'], 'a member that asserts nothing carries zero assertions');
        $this->assertSame('FAILED', $out[$c.'test_with_data_sets']['status'], 'one failing data set fails the member');
        $this->assertSame('NOT_EXECUTED', $out[$c.'test_does_not_exist']['status']);
        $this->assertSame('NOT_EXECUTED', $out['G05ExecutorNoSuchFixtureTest::test_x']['status']);

        // The aggregate consumes exactly these outcomes: only the passing members count.
        $resolved = ['MD-S002-R0007' => ['predicate' => 'MD-S002-R0007', 'items' => []]];
        foreach ($refs as $ref) {
            $resolved['MD-S002-R0007']['items'][] = ['predicate' => 'MD-S002-R0007', 'source' => 'fixture', 'label' => $ref, 'ref' => $ref, 'role' => 'member'];
        }
        $verdict = (self::AGG)::aggregate($resolved, $out, ['MD-S002-R0007'], ['MD-S002-R0007']);
        $reds = array_column($verdict['predicates']['MD-S002-R0007']['red'], 'guard');
        $this->assertNotContains($c.'test_passes', $reds);
        $this->assertNotContains($c.'test_also_passes', $reds);
        $this->assertContains($c.'test_asserts_nothing', $reds, 'a passed member with no assertion is not evidence');
        $this->assertCount(count($refs) - 2, $reds);

        foreach (glob($dir.'/*') as $f) {
            unlink($f);
        }
        rmdir($dir);
    }

    // ---- the real members ------------------------------------------------------------------------

    /** @var array<string,mixed>|null one real execution of the eight mirror-backed predicates, shared */
    private static $realRun;

    /** @return array<string,mixed> */
    private function realRun(): array
    {
        if (self::$realRun === null) {
            $ids = array_values(array_diff($this->governed(), ['MD-S003-R0025']));
            self::$realRun = (self::AGG)::run($this->root(), $ids, 4);
        }

        return self::$realRun;
    }

    /**
     * The predicate's real members execute through real PHPUnit processes and every one PASSED while
     * asserting something, together with the authority binding of every map that feeds it.
     */
    private function assertPredicateExecutesItsRealMembersAndIsGreen(string $id, int $expectedMembers, ?array $run = null): void
    {
        $run = $run ?? $this->realRun();
        $this->assertSame([], $run['errors']);
        $this->assertArrayHasKey($id, $run['predicates']);
        $p = $run['predicates'][$id];

        $this->assertSame([], $p['red'], $id.' has members that did not pass: '.json_encode($p['red']));
        $this->assertSame('GREEN', $p['verdict'], $id);
        $this->assertSame($expectedMembers, $p['member_items'], $id.' executed a different number of members than reviewed');

        $bindings = 0;
        foreach ($p['rows'] as $row) {
            $this->assertSame('PASSED', $row['status'], $row['guard']);
            $this->assertGreaterThan(0, $row['assertions'], $row['guard'].' passed without asserting anything');
            $bindings += $row['role'] === 'authority_binding' ? 1 : 0;
        }
        $this->assertGreaterThan(0, $bindings, $id.' executed no authority binding');
    }
    /** @group real-execution */
    public function test_md_s002_r0005_executes_all_anti_survivorship_and_as_known_isolation_members_and_is_green(): void
    {
        $this->assertPredicateExecutesItsRealMembersAndIsGreen('MD-S002-R0005', 36);
    }

    /** @group real-execution */
    public function test_md_s002_r0006_executes_all_degraded_and_negative_members_and_is_green(): void
    {
        $this->assertPredicateExecutesItsRealMembersAndIsGreen('MD-S002-R0006', 12);
    }

    /** @group real-execution */
    public function test_md_s002_r0007_executes_the_atr_and_corporate_action_oracle_members_and_is_green(): void
    {
        $this->assertPredicateExecutesItsRealMembersAndIsGreen('MD-S002-R0007', 6);
    }

    /** @group real-execution */
    public function test_md_s002_r0008_executes_the_correction_preservation_and_atomic_switch_members_and_is_green(): void
    {
        $this->assertPredicateExecutesItsRealMembersAndIsGreen('MD-S002-R0008', 6);
    }

    /**
     * @group real-execution
     *
     * MD-S003-R0025: the six required scenario families run on MariaDB production semantics AND on the supported SQLite test mirror, with zero skips.
     * The members are the six MariaDB family tests and the six mirror family tests; every one PASSED with assertions, and the authority bindings of both maps ran.
     * It needs MariaDB: where MariaDB is unreachable the members skip and this test fails, because a skipped family is never green.
     */
    public function test_md_s003_r0025_executes_all_scenario_family_members_on_mariadb_and_the_mirror_and_is_green(): void
    {
        $run = (self::AGG)::run($this->root(), ['MD-S003-R0025'], 2);
        $this->assertPredicateExecutesItsRealMembersAndIsGreen('MD-S003-R0025', 12, $run);
        $classes = [];
        foreach ($run['predicates']['MD-S003-R0025']['rows'] as $row) {
            if ($row['role'] === 'member') {
                $classes[explode('::', $row['guard'])[0]][] = $row['guard'];
            }
            $this->assertNotSame('SKIPPED', $row['status'], $row['guard'].' was skipped: zero skips are permitted');
        }
        $this->assertSame(['B18ScenarioFamiliesOnMariaDbTest', 'B18ScenarioFamiliesOnMirrorTest'], array_keys($classes));
        $this->assertCount(6, $classes['B18ScenarioFamiliesOnMariaDbTest']);
        $this->assertCount(6, $classes['B18ScenarioFamiliesOnMirrorTest']);
    }

    /** @group real-execution */
    public function test_md_s004_r0002_executes_all_eight_cutoff_bound_input_members_and_is_green(): void
    {
        $this->assertPredicateExecutesItsRealMembersAndIsGreen('MD-S004-R0002', 8);
    }

    /** @group real-execution */
    public function test_md_s004_r0003_executes_all_five_no_backfill_members_and_is_green(): void
    {
        $this->assertPredicateExecutesItsRealMembersAndIsGreen('MD-S004-R0003', 5);
    }

    /** @group real-execution */
    public function test_md_s004_r0005_executes_all_three_survivorship_and_revision_members_and_is_green(): void
    {
        $this->assertPredicateExecutesItsRealMembersAndIsGreen('MD-S004-R0005', 3);
    }

    /** @group real-execution */
    public function test_md_s004_r0008_executes_all_seven_acceptance_fixture_members_and_is_green(): void
    {
        $this->assertPredicateExecutesItsRealMembersAndIsGreen('MD-S004-R0008', 7);
    }

    /** @group real-execution */
    public function test_the_eight_mirror_backed_predicates_are_green_together_and_the_parent_of_them_is_green(): void
    {
        $run = $this->realRun();

        $this->assertSame(8, count($run['predicates']));
        $this->assertSame('GREEN', $run['verdict']);

        $distinct = [];
        foreach ($run['predicates'] as $p) {
            foreach ($p['rows'] as $row) {
                $distinct[$row['guard']] = true;
            }
        }
        $this->assertSame(count($distinct), $run['guards_executed'],
            'the run executed a different set of guards than its predicates name');
    }
    /**
     * @group real-execution
     *
     * The MariaDB family predicate requires the families to run on MariaDB with no skips. Whatever the
     * environment is, the verdict must agree with the outcomes: a skipped member is never green.
     */
    public function test_the_mariadb_family_predicate_is_never_green_on_a_skipped_member(): void
    {
        $result = (self::AGG)::run($this->root(), ['MD-S003-R0025'], 1);
        $p = $result['predicates']['MD-S003-R0025'];

        $this->assertCount(12, array_filter($p['rows'], static function ($r) { return $r['role'] === 'member'; }));
        $skipped = array_filter($p['rows'], static function ($r) { return $r['status'] === 'SKIPPED'; });
        foreach ($skipped as $row) {
            $this->assertFalse($row['green'], $row['guard'].' was skipped and must not count');
        }
        if ($skipped !== []) {
            $this->assertSame('RED', $p['verdict'], 'a skipped MariaDB family cannot leave the predicate green');
        } else {
            $this->assertSame($p['red'] === [] ? 'GREEN' : 'RED', $p['verdict']);
        }
    }
}

/** A stand-in map owner for the item-shape tests: a real class with a private map accessor. */
class G05FixtureMapHolder
{
    /** @var array<string,mixed> */
    public static $map = [];

    private function theMap(): array
    {
        return self::$map;
    }
}
