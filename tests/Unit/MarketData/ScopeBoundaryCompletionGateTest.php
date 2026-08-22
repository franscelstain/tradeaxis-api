<?php

use PHPUnit\Framework\TestCase;

require_once dirname(__DIR__, 3).'/docs/market_data/development/implementation/tests/MarketDataScopeBoundaryCompletionGate.php';

class ScopeBoundaryCompletionGateTest extends TestCase
{
    private function root(): string
    {
        return dirname(__DIR__, 3);
    }

    /** @return array<int,array<string,string>> */
    private function rows(): array
    {
        return MarketDataScopeBoundaryCompletionGate::readMatrix(
            $this->root().'/docs/market_data/authority/governance/STRATEGY_TO_IMPLEMENTATION_TRACEABILITY_MATRIX.csv'
        )['rows'];
    }

    public function test_current_matrix_has_the_exact_a013_proof_scope_and_preserves_the_blocked_rule(): void
    {
        $result = MarketDataScopeBoundaryCompletionGate::validate($this->rows(), $this->root());

        $this->assertSame([], $result['errors']);
        $this->assertSame(31, $result['proof_map_rules']);
        $this->assertContains($result['phase'], ['PRE_BIND', 'BIND_COMPLETE']);
        $this->assertSame('MD-S020-R0067', $result['blocked_rule']);
    }

    /**
     * A013 bound 31 rows against a denominator of 143 that the Stage Register then called FINAL.
     * `MD-B01-A014` proved the denominator understated the stage by 64 rows and `MD-B01-A015` proved
     * the 62 rows that promotion had left owing. `MD-B01-A016` proved the last one after
     * `D-MD-20260822-04` resolved `MD-DEP-0005`, so the stage stands at 207 of 207.
     */
    public function test_in_memory_atomic_binding_reaches_207_of_207(): void
    {
        $bound = MarketDataScopeBoundaryCompletionGate::bind($this->rows());
        $result = MarketDataScopeBoundaryCompletionGate::validate($bound, $this->root());

        $this->assertSame([], $result['errors']);
        $this->assertSame('BIND_COMPLETE', $result['phase']);
        $this->assertSame(31, $result['bound_rules']);
        $this->assertSame(['denominator' => 207, 'satisfied' => 207, 'not_assessed' => 0], $result['counts']);
    }

    public function test_gate_fails_closed_when_a_mapped_rule_disappears(): void
    {
        $rows = array_values(array_filter($this->rows(), static function (array $row): bool {
            return $row['rule_id'] !== 'MD-S001-R0003';
        }));
        $this->assertCount(6489, $rows, 'row-removal mutation must land');

        $result = MarketDataScopeBoundaryCompletionGate::validate($rows, $this->root());
        $this->assertNotSame([], $result['errors']);
    }

    public function test_gate_fails_closed_if_the_finding_blocked_rule_is_advanced(): void
    {
        $rows = $this->rows();
        foreach ($rows as &$row) {
            if ($row['rule_id'] === MarketDataScopeBoundaryCompletionGate::BLOCKED_RULE) {
                $row['coverage_status'] = 'SATISFIED';
                $row['current_evidence_ids'] = MarketDataScopeBoundaryCompletionGate::EVIDENCE;
            }
        }
        unset($row);
        $this->assertSame('SATISFIED', array_values(array_filter($rows, static function (array $row): bool {
            return $row['rule_id'] === MarketDataScopeBoundaryCompletionGate::BLOCKED_RULE;
        }))[0]['coverage_status'], 'blocked-rule mutation must land');

        $result = MarketDataScopeBoundaryCompletionGate::validate($rows, $this->root());
        $this->assertNotSame([], $result['errors']);
    }

    public function test_gate_fails_closed_on_partial_binding_or_wrong_evidence(): void
    {
        $rows = MarketDataScopeBoundaryCompletionGate::bind($this->rows());
        foreach ($rows as &$row) {
            if ($row['rule_id'] === 'MD-S020-R0083') {
                $row['current_evidence_ids'] = 'E-WRONG';
            }
        }
        unset($row);
        $this->assertSame('E-WRONG', array_values(array_filter($rows, static function (array $row): bool {
            return $row['rule_id'] === 'MD-S020-R0083';
        }))[0]['current_evidence_ids'], 'evidence mutation must land');

        $result = MarketDataScopeBoundaryCompletionGate::validate($rows, $this->root());
        $this->assertNotSame([], $result['errors']);
    }

    public function test_gate_fails_closed_when_a_registered_proof_method_or_surface_is_missing(): void
    {
        $map = MarketDataScopeBoundaryCompletionGate::proofMap();
        $map['MD-S056-R0047']['proofs'][0][1] = 'test_method_that_does_not_exist';
        $map['MD-S056-R0047']['surfaces'][] = 'missing/a013/surface.php';
        $this->assertSame('test_method_that_does_not_exist', $map['MD-S056-R0047']['proofs'][0][1]);

        $result = MarketDataScopeBoundaryCompletionGate::validate($this->rows(), $this->root(), $map);
        $this->assertNotSame([], $result['errors']);
    }
}
