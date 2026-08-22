<?php

require_once dirname(__DIR__, 3).'/docs/market_data/development/implementation/tests/MarketDataSourceAcquisitionProofGate.php';

use PHPUnit\Framework\TestCase;

class SourceAcquisitionTraceabilityGateTest extends TestCase
{
    private function rows(): array
    {
        $path = dirname(__DIR__, 3).'/docs/market_data/authority/governance/STRATEGY_TO_IMPLEMENTATION_TRACEABILITY_MATRIX.csv';

        return MarketDataClassificationConsistencyGate::readMatrix($path)['rows'];
    }

    private function indexOf(array $rows, string $rule): int
    {
        foreach ($rows as $index => $row) {
            if ($row['rule_id'] === $rule) {
                return $index;
            }
        }
        $this->fail('Missing rule '.$rule);
    }

    private function root(): string
    {
        return dirname(__DIR__, 3);
    }

    public function test_current_b07_normalization_is_exact(): void
    {
        $result = MarketDataSourceAcquisitionTraceabilityGate::validate($this->rows());

        $this->assertSame([], $result['errors'], implode("\n", $result['errors']));
        $this->assertSame(115, $result['counts']['b07_denominator']);
        $this->assertSame(88, $result['counts']['moved']);
        $this->assertSame(51, $result['counts']['reference']);
        $this->assertSame(254, $result['counts']['reviewed']);
    }

    public function test_a_mixed_member_cannot_be_demoted_again(): void
    {
        $rows = $this->rows();
        $index = $this->indexOf($rows, 'MD-S053-R0007');
        $rows[$index]['coverage_requirement'] = 'REFERENCE_ONLY';
        $rows[$index]['applicability'] = 'REFERENCE_ONLY';
        $errors = MarketDataSourceAcquisitionTraceabilityGate::validate($rows)['errors'];

        $this->assertNotEmpty(array_filter($errors, static function ($error) {
            return strpos($error, 'MD-S053-R0007') !== false;
        }));
    }

    public function test_a_backfill_predicate_cannot_be_owned_by_b07_for_closure_convenience(): void
    {
        $rows = $this->rows();
        $index = $this->indexOf($rows, 'MD-S053-R0202');
        $rows[$index]['primary_stage'] = 'MD-B07';
        $errors = MarketDataSourceAcquisitionTraceabilityGate::validate($rows)['errors'];

        $this->assertNotEmpty(array_filter($errors, static function ($error) {
            return strpos($error, 'MD-S053-R0202') !== false;
        }));
    }

    public function test_a_contextual_alignment_rule_cannot_lose_its_parent(): void
    {
        $rows = $this->rows();
        $index = $this->indexOf($rows, 'MD-S053-R0171');
        $rows[$index]['notes'] = str_replace(
            'predicate_context=MD-S053-R0170',
            'predicate_context=SELF_CONTAINED',
            $rows[$index]['notes']
        );
        $errors = MarketDataSourceAcquisitionTraceabilityGate::validate($rows)['errors'];

        $this->assertNotEmpty(array_filter($errors, static function ($error) {
            return strpos($error, 'MD-S053-R0171') !== false;
        }));
    }

    public function test_forbidden_warmup_fragments_have_explicit_negative_predicates(): void
    {
        $rows = $this->rows();
        foreach (['MD-S053-R0219', 'MD-S053-R0220', 'MD-S053-R0221'] as $rule) {
            $row = $rows[$this->indexOf($rows, $rule)];
            $this->assertStringContainsString('must not', $row['notes'], $rule);
            $this->assertSame('MD-B19', $row['primary_stage'], $rule);
        }
    }

    public function test_proof_map_exactly_partitions_the_current_b07_denominator(): void
    {
        $expected = [];
        foreach (MarketDataSourceAcquisitionTraceabilitySpec::requiredOwners() as $document => $rows) {
            foreach ($rows as $number => $stage) {
                if ($stage === 'MD-B07') {
                    $expected[] = MarketDataSourceAcquisitionTraceabilitySpec::ruleId($document, $number);
                }
            }
        }
        sort($expected, SORT_STRING);
        $actual = array_keys(MarketDataSourceAcquisitionProofGate::proofMap());

        $this->assertSame($expected, $actual);
        $this->assertCount(115, $actual);
    }

    public function test_the_proof_map_names_only_existing_behavior_methods(): void
    {
        $checked = 0;
        foreach (MarketDataSourceAcquisitionProofGate::proofMap() as $rule => $proof) {
            foreach ($proof['methods'] as [$file, $method]) {
                $source = @file_get_contents($this->root().'/'.$file);
                $this->assertNotFalse($source, $rule.': missing '.$file);
                $this->assertStringContainsString('function '.$method.'(', $source, $rule.': '.$method);
                $checked++;
            }
        }
        $this->assertGreaterThan(700, $checked);
    }

    public function test_proof_gate_rejects_an_unbound_or_unannotated_remediated_rule(): void
    {
        $rows = $this->rows();
        $index = $this->indexOf($rows, 'MD-S053-R0094');
        $rows[$index]['coverage_status'] = 'NOT_ASSESSED';
        $rows[$index]['notes'] = str_replace('remediated_at=MD-B07-A001', 'remediated_at=missing', $rows[$index]['notes']);
        $errors = MarketDataSourceAcquisitionProofGate::validate($rows, $this->root())['errors'];

        $this->assertNotEmpty(array_filter($errors, static function ($error) {
            return strpos($error, 'MD-S053-R0094') !== false;
        }));
    }
}
