<?php

use PHPUnit\Framework\TestCase;

/**
 * `MD-B18-A002` — `MD-S004-R0007`, decomposed.
 *
 * The row is one sentence group with three clauses, and they are not in the same position:
 *
 * 1. *Signal features use the declared coherent analytical product.* — a platform obligation, and
 *    one the read product already carries.
 * 2. *Simulated execution must separately choose realistic executable prices/times from allowed
 *    facts; it may not trade on same-session information before its availability timestamp.* — an
 *    obligation on the **downstream backtest**. There is no simulated execution anywhere in this
 *    application, and no availability-timestamp surface for one to honour. The same contract says
 *    so in its own words two paragraphs earlier: strategy interpretation "belongs to the backtest
 *    and is not a future-performance label or an instruction to trade".
 * 3. *Corporate-action cashflow/total-return treatment is explicit and cannot be inferred from
 *    provider adjusted close.* — a platform obligation, and enforced.
 *
 * A predicate with three clauses is not satisfied by two, so `MD-S004-R0007` stays unbound. What
 * this class does is stop the decomposition living in prose: each clause is mapped to the guard that
 * executes it or to the recorded reason it has none, and the third clause's absence is asserted
 * against the codebase rather than asserted about it. If an availability-timestamp surface is ever
 * built here, the last test fails and says so — at which point the clause has an implementation to
 * be proven against and the row can be revisited.
 *
 * `F-MD-B18-A002-005` carries the ownership question.
 */
class B18PriceAndExecutionBoundaryTest extends TestCase
{
    private const CONTRACT = 'docs/market_data/authority/strategy/backtest/Point_In_Time_Backtest_Input_Contract_LOCKED.md';

    /**
     * Clause => the guards that execute it, or `[]` where the clause has no implementation surface
     * in this application.
     *
     * @return array<string,array<int,string>>
     */
    private function clauseMap(): array
    {
        return [
            'Signal features use the declared coherent analytical product.' => [
                'CoherentPriceProductBoundaryTest::test_an_adjusted_vector_declares_the_structural_adjusted_product',
                'CoherentPriceProductBoundaryTest::test_the_persisted_vector_carries_its_price_product_code',
            ],
            'Simulated execution must separately choose realistic executable prices/times from allowed facts; it may not trade on same-session information before its availability timestamp.' => [],
            'Corporate-action cashflow/total-return treatment is explicit and cannot be inferred from provider adjusted close.' => [
                'CoherentPriceProductBoundaryTest::test_provider_adjusted_close_is_not_scaled_by_a_platform_factor',
                'CoherentPriceProductBoundaryTest::test_legacy_adj_close_selector_cannot_become_an_analytical_fallback',
                'AdjustmentFactorSetB11Test::test_only_authoritative_or_manual_verified_revisions_are_adjustment_active',
            ],
        ];
    }

    /**
     * The clauses are read from the contract sentence rather than transcribed, so a clause added to
     * or reworded in `MD-S004` fails here instead of leaving the decomposition describing a sentence
     * that no longer exists.
     */
    public function test_the_clause_map_matches_the_contract_sentence(): void
    {
        $path = dirname(__DIR__, 3).'/'.self::CONTRACT;
        $this->assertFileExists($path);

        $source = (string) file_get_contents($path);
        $start = strpos($source, '## Price and execution boundary');
        $this->assertNotFalse($start, 'the MD-S004 price and execution boundary heading moved; '
            .'re-read the contract rather than relaxing this map');

        $end = strpos($source, "\n## ", $start + 5);
        $block = trim(substr($source, $start, $end === false ? null : $end - $start));
        $block = trim(substr($block, strlen('## Price and execution boundary')));

        // One paragraph, split on sentence ends that are followed by a capital.
        $clauses = preg_split('/(?<=\.)\s+(?=[A-Z])/', trim($block));
        $clauses = array_values(array_filter(array_map('trim', $clauses)));

        $mapped = array_keys($this->clauseMap());
        sort($clauses);
        sort($mapped);

        $this->assertSame($clauses, $mapped,
            'MD-S004-R0007 and the reviewed clause map disagree; a clause with no recorded position '
                .'would otherwise be neither proven nor named as unproven');
    }

    public function test_every_guard_a_clause_names_exists(): void
    {
        $missing = [];

        foreach ($this->clauseMap() as $clause => $refs) {
            foreach ($refs as $ref) {
                [$class, $method] = explode('::', $ref);
                $file = __DIR__.'/'.$class.'.php';
                if (! is_file($file) || strpos((string) file_get_contents($file), 'function '.$method.'(') === false) {
                    $missing[] = $clause.' -> '.$ref;
                }
            }
        }

        $this->assertSame([], $missing, 'these clauses name a guard that no longer exists');
    }

    /**
     * Exactly one clause is unproven, and it is the simulated-execution one. Asserting the count
     * keeps the row honest in both directions: a second clause quietly losing its corpus would fail
     * here, and so would the simulated-execution clause being marked proven by pointing it at a
     * guard about something else.
     */
    public function test_exactly_one_clause_is_recorded_as_having_no_implementation_surface(): void
    {
        $unproven = array_keys(array_filter($this->clauseMap(), function (array $refs) {
            return $refs === [];
        }));

        $this->assertCount(1, $unproven,
            'MD-S004-R0007 should have exactly one clause without an implementation surface');
        $this->assertStringStartsWith('Simulated execution must separately choose', $unproven[0]);
    }

    /**
     * The claim that the clause has no surface, checked against the application rather than
     * asserted about it.
     *
     * Two things would have to exist for the clause to be provable here: something that simulates
     * execution, and an availability timestamp for it to respect. Neither does. If either is built,
     * this fails — which is the point: at that moment `MD-S004-R0007` stops being blocked on scope
     * and starts being blocked on a guard nobody has written.
     */
    public function test_this_application_still_has_no_simulated_execution_surface(): void
    {
        $app = dirname(__DIR__, 3).'/app';
        $this->assertDirectoryExists($app);

        $hits = [];
        $files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($app));
        foreach ($files as $file) {
            if (! $file->isFile() || strtolower($file->getExtension()) !== 'php') {
                continue;
            }
            $source = (string) file_get_contents($file->getPathname());
            foreach (['availability_timestamp', 'available_at', 'SimulatedExecution', 'simulateExecution'] as $marker) {
                if (strpos($source, $marker) !== false) {
                    $hits[] = basename($file->getPathname()).' contains '.$marker;
                }
            }
        }

        $this->assertSame([], $hits,
            'a simulated-execution or availability-timestamp surface now exists, so the clause '
                .'MD-S004-R0007 leaves unproven has something to be proven against; revisit the row '
                .'rather than leaving it recorded as out of scope');
    }
}
