<?php

require_once dirname(__DIR__, 3).'/docs/market_data/development/implementation/tests/MarketDataConfigFoundationProofGate.php';

use PHPUnit\Framework\TestCase;

class ConfigFoundationProofGateTest extends TestCase
{
    private function root(): string
    {
        return dirname(__DIR__, 3);
    }

    private function rows(): array
    {
        return MarketDataClassificationConsistencyGate::readMatrix(
            $this->root().'/docs/market_data/authority/governance/STRATEGY_TO_IMPLEMENTATION_TRACEABILITY_MATRIX.csv'
        )['rows'];
    }

    public function test_current_successor_proof_is_exactly_114_of_114(): void
    {
        $result = MarketDataConfigFoundationProofGate::validate($this->rows(), $this->root());

        $this->assertSame([], $result['errors'], implode("\n", $result['errors']));
        $this->assertSame(['denominator' => 114, 'satisfied' => 114, 'blocked' => 0], $result['counts']);
    }

    public function test_resolved_authority_predicate_cannot_lose_its_successor_proof_binding(): void
    {
        $rows = $this->rows();
        $index = $this->indexOf($rows, MarketDataConfigFoundationProofGate::RESOLVED_RULE);
        $this->assertSame('SATISFIED', $rows[$index]['coverage_status']);
        $rows[$index]['coverage_status'] = 'NOT_ASSESSED';
        $rows[$index]['current_evidence_ids'] = '';

        $errors = MarketDataConfigFoundationProofGate::validate($rows, $this->root())['errors'];
        $this->assertNotEmpty(array_filter($errors, static fn ($error) => strpos($error, 'MD-S082-R0062') !== false));
    }

    public function test_a_proven_row_cannot_regress_or_bind_wrong_evidence(): void
    {
        $rows = $this->rows();
        $index = $this->indexOf($rows, 'MD-S005-R0009');
        $rows[$index]['coverage_status'] = 'NOT_ASSESSED';
        $rows[$index]['current_evidence_ids'] = 'E-WRONG';

        $errors = MarketDataConfigFoundationProofGate::validate($rows, $this->root())['errors'];
        $this->assertNotEmpty(array_filter($errors, static fn ($error) => strpos($error, 'MD-S005-R0009') !== false));
    }

    public function test_proof_map_cannot_drop_a_rule_or_name_a_missing_method(): void
    {
        $map = MarketDataConfigFoundationProofGate::proofMap();
        unset($map['MD-S034-R0003']);
        $errors = MarketDataConfigFoundationProofGate::validate($this->rows(), $this->root(), $map)['errors'];
        $this->assertStringContainsString('exactly cover', implode(' ', $errors));

        $map = MarketDataConfigFoundationProofGate::proofMap();
        $map['MD-S034-R0003']['methods'][] = ['tests/Unit/MarketData/DeterministicHashServiceTest.php', 'test_missing_proof'];
        $errors = MarketDataConfigFoundationProofGate::validate($this->rows(), $this->root(), $map)['errors'];
        $this->assertStringContainsString('does not exist', implode(' ', $errors));
    }

    public function test_the_two_rebound_predicates_are_bound_to_the_a003_evidence_and_the_others_to_a002(): void
    {
        foreach ($this->rows() as $row) {
            if ($row['primary_stage'] !== 'MD-B04' || $row['coverage_requirement'] !== 'REQUIRED') {
                continue;
            }
            $expected = in_array($row['rule_id'], ['MD-S082-R0036', 'MD-S082-R0044'], true) ? 'E-MD-B04-A003-002' : 'E-MD-B04-A002-001';
            $this->assertSame($expected, $row['current_evidence_ids'], $row['rule_id']);
            $this->assertSame($expected, MarketDataConfigFoundationProofGate::expectedEvidence($row['rule_id']));
        }
    }

    public function test_a_rebound_predicate_bound_back_to_the_superseded_a002_proof_is_refused(): void
    {
        $rows = $this->rows();
        $index = $this->indexOf($rows, 'MD-S082-R0036');
        $rows[$index]['current_evidence_ids'] = 'E-MD-B04-A002-001';

        $errors = MarketDataConfigFoundationProofGate::validate($rows, $this->root())['errors'];
        $this->assertNotEmpty(array_filter($errors, static fn ($error) => strpos($error, 'MD-S082-R0036: current proof binding is not exact') !== false));
    }

    public function test_an_unaffected_predicate_bound_to_the_a003_evidence_is_refused(): void
    {
        $rows = $this->rows();
        $index = $this->indexOf($rows, 'MD-S082-R0006');
        $rows[$index]['current_evidence_ids'] = 'E-MD-B04-A003-002';

        $errors = MarketDataConfigFoundationProofGate::validate($rows, $this->root())['errors'];
        $this->assertNotEmpty(array_filter($errors, static fn ($error) => strpos($error, 'MD-S082-R0006: current proof binding is not exact') !== false));
    }

    public function test_a_rebound_predicate_without_its_a003_proof_note_is_refused(): void
    {
        $rows = $this->rows();
        $index = $this->indexOf($rows, 'MD-S082-R0044');
        $rows[$index]['notes'] = str_replace('MD-B04-A003: proof_binding=', 'MD-B04-A003: other=', $rows[$index]['notes']);

        $errors = MarketDataConfigFoundationProofGate::validate($rows, $this->root())['errors'];
        $this->assertNotEmpty(array_filter($errors, static fn ($error) => strpos($error, 'MD-S082-R0044: the A003 proof-binding note is missing') !== false));
    }

    public function test_the_proof_of_the_reason_registry_rules_must_run_every_reason_registry_member_test(): void
    {
        foreach (['MD-S082-R0036', 'MD-S082-R0044'] as $rule) {
            foreach (MarketDataConfigFoundationProofGate::SUCCESSOR_MEMBER_METHODS as $method) {
                $map = MarketDataConfigFoundationProofGate::proofMap();
                $map[$rule]['methods'] = array_values(array_filter($map[$rule]['methods'], static fn ($m) => $m[1] !== $method));

                $errors = MarketDataConfigFoundationProofGate::validate($this->rows(), $this->root(), $map)['errors'];
                $this->assertContains($rule.': the proof does not run the reason-registry member test '.$method, $errors, $rule.' without '.$method);
            }
        }
    }

    public function test_the_generic_configuration_guard_pair_alone_is_not_a_proof_of_the_reason_registry_rules(): void
    {
        $map = MarketDataConfigFoundationProofGate::proofMap();
        foreach (['MD-S082-R0036', 'MD-S082-R0044'] as $rule) {
            $map[$rule]['methods'] = array_values(array_filter($map[$rule]['methods'], static fn ($m) => $m[0] !== MarketDataConfigFoundationProofGate::SUCCESSOR_MEMBER_TEST));
        }

        $errors = MarketDataConfigFoundationProofGate::validate($this->rows(), $this->root(), $map)['errors'];
        $this->assertNotEmpty(array_filter($errors, static fn ($error) => strpos($error, 'does not run the reason-registry member test') !== false));
    }

    private function indexOf(array $rows, string $ruleId): int
    {
        foreach ($rows as $index => $row) {
            if ($row['rule_id'] === $ruleId) {
                return $index;
            }
        }

        throw new RuntimeException('Missing rule '.$ruleId);
    }
}
