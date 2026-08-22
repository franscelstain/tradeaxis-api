<?php

require_once dirname(__DIR__, 3).'/docs/market_data/development/implementation/tests/MarketDataTemporalIdentityProofGate.php';

use PHPUnit\Framework\TestCase;

/**
 * Self-test for the two MD-B05 gates.
 *
 * A gate that reports PASS is only worth reading if it can report FAIL. Every check below removes
 * one thing the gate is supposed to require and asserts the gate notices — and each mutation is
 * verified to have changed the row before the gate is judged, because a mutation that silently
 * failed to land looks exactly like a guard that did not react.
 */
class TemporalIdentityTraceabilityGateTest extends TestCase
{
    private function root(): string
    {
        return dirname(__DIR__, 3);
    }

    private function rows(): array
    {
        $path = $this->root().'/docs/market_data/authority/governance/STRATEGY_TO_IMPLEMENTATION_TRACEABILITY_MATRIX.csv';

        return MarketDataClassificationConsistencyGate::readMatrix($path)['rows'];
    }

    private function indexOf(array $rows, string $ruleId): int
    {
        foreach ($rows as $index => $row) {
            if ($row['rule_id'] === $ruleId) {
                return $index;
            }
        }
        $this->fail('rule not present in the matrix: '.$ruleId);
    }

    public function test_current_b05_normalization_is_exact(): void
    {
        $result = MarketDataTemporalIdentityTraceabilityGate::validate($this->rows());

        $this->assertSame([], $result['errors'], implode("\n", $result['errors']));
        $this->assertSame(
            [
                'mandatory_b05' => 115,
                'moved' => 20,
                'reference' => 31,
                'imported' => 2,
                'contextual' => 71,
                'b05_denominator' => 117,
            ],
            $result['counts']
        );
    }

    public function test_current_b05_proof_binding_is_exact(): void
    {
        $result = MarketDataTemporalIdentityProofGate::validate($this->rows(), $this->root());

        $this->assertSame([], $result['errors'], implode("\n", $result['errors']));
        $this->assertSame(['denominator' => 117, 'satisfied' => 117, 'unbound' => 0], $result['counts']);
    }

    /** The spec is the reviewed classification; a structural row may not acquire an owner. */
    public function test_the_spec_refuses_to_assign_an_owner_to_a_structural_row(): void
    {
        $reference = MarketDataTemporalIdentityTraceabilitySpec::STRUCTURAL_REFERENCE;
        $owners = MarketDataTemporalIdentityTraceabilitySpec::requiredOwners();

        foreach ($reference as $document => $numbers) {
            foreach ($numbers as $number) {
                $this->assertArrayNotHasKey(
                    $number,
                    $owners[$document],
                    $document.' R'.$number.' is both structural and owned'
                );
            }
            $this->assertSame(
                MarketDataTemporalIdentityTraceabilitySpec::DOCUMENT_COUNTS[$document],
                count($owners[$document]) + count($numbers),
                $document.': the two sets must partition the document'
            );
        }
    }

    public function test_a_structural_introducer_cannot_reenter_the_denominator(): void
    {
        $rows = $this->rows();
        $index = $this->indexOf($rows, 'MD-S057-R0018');
        $this->assertSame('REFERENCE_ONLY', $rows[$index]['coverage_requirement'], 'the mutation must start from the real state');
        $rows[$index]['coverage_requirement'] = 'REQUIRED';
        $rows[$index]['applicability'] = 'MANDATORY';
        $rows[$index]['coverage_status'] = 'NOT_ASSESSED';

        $errors = MarketDataTemporalIdentityTraceabilityGate::validate($rows)['errors'];
        $this->assertNotEmpty(array_filter($errors, static function ($error) {
            return strpos($error, 'MD-S057-R0018') !== false;
        }));

        $classErrors = MarketDataClassificationConsistencyGate::validate($rows)['errors'];
        $this->assertNotEmpty(array_filter($classErrors, static function ($error) {
            return strpos($error, 'REQUIRED_STRUCTURE MD-S057-R0018') !== false;
        }), 'the shared classification invariant must reach the same conclusion independently');
    }

    public function test_a_predicate_cannot_be_demoted_out_of_the_denominator(): void
    {
        $rows = $this->rows();
        $index = $this->indexOf($rows, 'MD-S055-R0026');
        $this->assertSame('REQUIRED', $rows[$index]['coverage_requirement']);
        $rows[$index]['coverage_requirement'] = 'REFERENCE_ONLY';
        $rows[$index]['applicability'] = 'REFERENCE_ONLY';

        $result = MarketDataTemporalIdentityTraceabilityGate::validate($rows);
        $this->assertNotEmpty(array_filter($result['errors'], static function ($error) {
            return strpos($error, 'MD-S055-R0026') !== false;
        }));

        // The denominator is derived from the reviewed spec rather than counted from the matrix, so
        // a demotion is reported as a lifecycle error and the count stays where review put it. That
        // is the intended direction: a silently shrinking denominator is how coverage improves
        // without anything being proven.
        $this->assertSame(117, $result['counts']['b05_denominator']);
    }

    public function test_a_moved_predicate_must_keep_its_owner_and_its_b05_support_link(): void
    {
        $rows = $this->rows();
        $index = $this->indexOf($rows, 'MD-S052-R0027');
        $this->assertSame('MD-B14', $rows[$index]['primary_stage']);
        $rows[$index]['primary_stage'] = 'MD-B05';
        $rows[$index]['supporting_stages'] = '';

        $errors = MarketDataTemporalIdentityTraceabilityGate::validate($rows)['errors'];
        $this->assertNotEmpty(array_filter($errors, static function ($error) {
            return strpos($error, 'MD-S052-R0027') !== false;
        }));
    }

    public function test_a_required_predicate_cannot_lose_its_governing_parent(): void
    {
        $rows = $this->rows();
        $index = $this->indexOf($rows, 'MD-S057-R0035');
        $this->assertStringContainsString('predicate_context=MD-S057-R0034', $rows[$index]['notes']);
        $rows[$index]['notes'] = str_replace(
            'predicate_context=MD-S057-R0034',
            'predicate_context=SELF_CONTAINED',
            $rows[$index]['notes']
        );
        $this->assertStringNotContainsString('predicate_context=MD-S057-R0034', $rows[$index]['notes'], 'the mutation must land');

        $errors = MarketDataTemporalIdentityTraceabilityGate::validate($rows)['errors'];
        $this->assertNotEmpty(array_filter($errors, static function ($error) {
            return strpos($error, 'MD-S057-R0035') !== false;
        }));
    }

    public function test_the_proof_gate_rejects_an_unbound_or_misbound_rule(): void
    {
        foreach ([
            'unbound' => ['coverage_status', 'NOT_ASSESSED'],
            'misbound' => ['current_evidence_ids', 'E-MD-B04-A002-001'],
        ] as $label => [$column, $value]) {
            $rows = $this->rows();
            $index = $this->indexOf($rows, 'MD-S052-R0012');
            $this->assertNotSame($value, $rows[$index][$column], $label.': the mutation must change something');
            $rows[$index][$column] = $value;

            $errors = MarketDataTemporalIdentityProofGate::validate($rows, $this->root())['errors'];
            $this->assertNotEmpty(array_filter($errors, static function ($error) {
                return strpos($error, 'MD-S052-R0012') !== false;
            }), $label.': the proof gate did not notice');
        }
    }

    /**
     * A rule this attempt remediated must carry the attempt that fixed it. Without the annotation a
     * regression reads as an ordinary coverage difference rather than as the defect returning.
     */
    public function test_a_remediated_rule_must_record_the_attempt_that_fixed_it(): void
    {
        foreach (array_keys(MarketDataTemporalIdentityProofGate::REMEDIATED_RULES) as $rule) {
            $rows = $this->rows();
            $index = $this->indexOf($rows, $rule);
            $this->assertStringContainsString('remediated_at=MD-B05-A001', $rows[$index]['notes'], $rule);
            $rows[$index]['notes'] = str_replace('remediated_at=MD-B05-A001', 'remediated_at=', $rows[$index]['notes']);
            $this->assertStringNotContainsString('remediated_at=MD-B05-A001', $rows[$index]['notes'], 'the mutation must land');

            $errors = MarketDataTemporalIdentityProofGate::validate($rows, $this->root())['errors'];
            $this->assertNotEmpty(array_filter($errors, static function ($error) use ($rule) {
                return strpos($error, $rule.': remediated rule') !== false;
            }), $rule.': the annotation is not enforced');
        }
    }

    /** Every proof method the map names must exist, or the map is a list of intentions. */
    public function test_the_proof_map_names_only_methods_that_exist(): void
    {
        $checked = 0;
        foreach (MarketDataTemporalIdentityProofGate::proofMap() as $rule => $proof) {
            foreach ($proof['methods'] as [$file, $method]) {
                $source = @file_get_contents($this->root().'/'.$file);
                $this->assertNotFalse($source, $rule.': missing proof file '.$file);
                $this->assertStringContainsString('function '.$method.'(', $source, $rule.': '.$file.'::'.$method);
                $checked++;
            }
        }

        $this->assertGreaterThan(500, $checked, 'the proof map must actually bind methods');
    }
}
