<?php

use PHPUnit\Framework\TestCase;

require_once dirname(__DIR__, 3).'/docs/market_data/development/implementation/tests/MarketDataPromotedPredicateProofGate.php';

/**
 * Fail-closed self-test for the `MD-B01-A015` proof-map binder.
 *
 * Every mutation is asserted to have landed before the gate is judged.
 */
class PromotedPredicateProofGateTest extends TestCase
{
    private function root(): string
    {
        return dirname(__DIR__, 3);
    }

    /** @return array<int,array<string,string>> */
    private function rows(): array
    {
        return MarketDataPromotedPredicateProofGate::readMatrix(
            $this->root().'/docs/market_data/authority/governance/STRATEGY_TO_IMPLEMENTATION_TRACEABILITY_MATRIX.csv'
        )['rows'];
    }

    public function test_the_current_matrix_carries_the_complete_a015_binding(): void
    {
        $result = MarketDataPromotedPredicateProofGate::validate($this->rows(), $this->root());

        $this->assertSame([], $result['errors']);
        $this->assertSame(62, $result['bound']);
        $this->assertSame(
            ['denominator' => 207, 'satisfied' => 207, 'not_assessed' => 0],
            $result['counts'],
            'MD-B01 stands at 207/207 after MD-B01-A016 proved the last rule'
        );
    }

    public function test_the_proof_map_is_exactly_the_sixty_two_promoted_rules_and_excludes_the_blocked_one(): void
    {
        $map = MarketDataPromotedPredicateProofGate::proofMap();

        $this->assertCount(62, $map);
        $this->assertArrayNotHasKey(MarketDataPromotedPredicateProofGate::BLOCKED_RULE, $map);
    }

    /**
     * `MD-S020-R0067` was finding-blocked and this test proved the gate refused to let it advance.
     * `D-MD-20260822-04` resolved the block and `MD-B01-A016` proved the rule, so the invariant
     * inverted: the gate must now refuse to let it *regress*, and must refuse a binding that points
     * anywhere but at the attempt that actually proved it.
     */
    public function test_gate_fails_closed_when_the_resolved_rule_regresses_or_loses_its_proof(): void
    {
        $rows = $this->rows();
        $index = $this->indexOf($rows, MarketDataPromotedPredicateProofGate::BLOCKED_RULE);
        $this->assertSame('SATISFIED', $rows[$index]['coverage_status'], 'the resolved rule must start proven');

        $regressed = $rows;
        $regressed[$index]['coverage_status'] = 'NOT_ASSESSED';
        $regressed[$index]['current_evidence_ids'] = '';
        $this->assertNotSame([], MarketDataPromotedPredicateProofGate::validate($regressed, $this->root())['errors'], 'a regression must fail');

        $misbound = $rows;
        $misbound[$index]['current_evidence_ids'] = MarketDataPromotedPredicateProofGate::EVIDENCE;
        $this->assertNotSame([], MarketDataPromotedPredicateProofGate::validate($misbound, $this->root())['errors'], 'binding to the A015 evidence rather than A016 must fail');
    }

    public function test_gate_fails_closed_on_a_partial_binding(): void
    {
        $rows = $this->rows();
        $index = $this->indexOf($rows, 'MD-S020-R0165');
        $this->assertSame('SATISFIED', $rows[$index]['coverage_status'], 'partial-binding mutation target must start bound');
        $rows[$index]['coverage_status'] = 'NOT_ASSESSED';
        $rows[$index]['current_evidence_ids'] = '';

        $errors = MarketDataPromotedPredicateProofGate::validate($rows, $this->root())['errors'];
        $this->assertNotSame([], $errors);
        $this->assertStringContainsString('atomic', implode(' ', $errors));
    }

    public function test_gate_fails_closed_when_a_bound_row_names_the_wrong_evidence(): void
    {
        $rows = $this->rows();
        $index = $this->indexOf($rows, 'MD-S001-R0101');
        $rows[$index]['current_evidence_ids'] = 'E-WRONG';
        $this->assertSame('E-WRONG', $rows[$index]['current_evidence_ids'], 'evidence mutation must land');

        $this->assertNotSame([], MarketDataPromotedPredicateProofGate::validate($rows, $this->root())['errors']);
    }

    public function test_gate_fails_closed_when_a_registered_proof_method_does_not_exist(): void
    {
        $map = MarketDataPromotedPredicateProofGate::proofMap();
        $map['MD-S056-R0114']['methods'] = ['test_method_that_does_not_exist'];
        $this->assertSame(['test_method_that_does_not_exist'], $map['MD-S056-R0114']['methods'], 'proof-map mutation must land');

        $errors = MarketDataPromotedPredicateProofGate::validate($this->rows(), $this->root(), $map)['errors'];
        $this->assertNotSame([], $errors);
        $this->assertStringContainsString('does not exist', implode(' ', $errors));
    }

    public function test_gate_fails_closed_when_a_rule_is_dropped_from_the_map(): void
    {
        $map = MarketDataPromotedPredicateProofGate::proofMap();
        unset($map['MD-S001-R0109']);
        $this->assertCount(61, $map, 'map-shrink mutation must land');

        $errors = MarketDataPromotedPredicateProofGate::validate($this->rows(), $this->root(), $map)['errors'];
        $this->assertNotSame([], $errors);
        $this->assertStringContainsString('exactly 62', implode(' ', $errors));
    }

    public function test_gate_fails_closed_when_a_named_implementation_surface_disappears(): void
    {
        $map = MarketDataPromotedPredicateProofGate::proofMap();
        $map['MD-S056-R0012']['surfaces'][] = 'app/Domain/MarketData/SurfaceThatDoesNotExist.php';
        $this->assertContains('app/Domain/MarketData/SurfaceThatDoesNotExist.php', $map['MD-S056-R0012']['surfaces'], 'surface mutation must land');

        $errors = MarketDataPromotedPredicateProofGate::validate($this->rows(), $this->root(), $map)['errors'];
        $this->assertNotSame([], $errors);
        $this->assertStringContainsString('surface is missing', implode(' ', $errors));
    }

    /** @param array<int,array<string,string>> $rows */
    private function indexOf(array $rows, string $id): int
    {
        foreach ($rows as $index => $row) {
            if ($row['rule_id'] === $id) {
                return $index;
            }
        }

        throw new RuntimeException('Missing rule '.$id);
    }
}
