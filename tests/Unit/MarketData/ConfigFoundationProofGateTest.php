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
