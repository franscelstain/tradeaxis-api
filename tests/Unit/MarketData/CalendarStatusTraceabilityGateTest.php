<?php

require_once dirname(__DIR__, 3).'/docs/market_data/development/implementation/tests/MarketDataCalendarStatusProofGate.php';

use PHPUnit\Framework\TestCase;

class CalendarStatusTraceabilityGateTest extends TestCase
{
    private function rows(): array
    {
        $path = dirname(__DIR__, 3).'/docs/market_data/authority/governance/STRATEGY_TO_IMPLEMENTATION_TRACEABILITY_MATRIX.csv';

        return MarketDataClassificationConsistencyGate::readMatrix($path)['rows'];
    }

    private function root(): string
    {
        return dirname(__DIR__, 3);
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

    public function test_current_b06_normalization_is_exact(): void
    {
        $result = MarketDataCalendarStatusTraceabilityGate::validate($this->rows());

        $this->assertSame([], $result['errors'], implode("\n", $result['errors']));
        $this->assertSame(78, $result['counts']['b06_denominator']);
        $this->assertSame(53, $result['counts']['moved']);
        $this->assertSame(27, $result['counts']['reference']);
    }

    public function test_a_predicate_cannot_be_demoted_or_misowned(): void
    {
        $rows = $this->rows();
        $index = $this->indexOf($rows, 'MD-S041-R0031');
        $rows[$index]['coverage_requirement'] = 'REFERENCE_ONLY';
        $errors = MarketDataCalendarStatusTraceabilityGate::validate($rows)['errors'];
        $this->assertNotEmpty(array_filter($errors, static function ($error) {
            return strpos($error, 'MD-S041-R0031') !== false;
        }));

        $rows = $this->rows();
        $index = $this->indexOf($rows, 'MD-S058-R0069');
        $rows[$index]['primary_stage'] = 'MD-B06';
        $errors = MarketDataCalendarStatusTraceabilityGate::validate($rows)['errors'];
        $this->assertNotEmpty(array_filter($errors, static function ($error) {
            return strpos($error, 'MD-S058-R0069') !== false;
        }));
    }

    public function test_contextual_fragment_cannot_lose_its_parent(): void
    {
        $rows = $this->rows();
        $index = $this->indexOf($rows, 'MD-S058-R0018');
        $rows[$index]['notes'] = str_replace(
            'predicate_context=MD-S058-R0016',
            'predicate_context=SELF_CONTAINED',
            $rows[$index]['notes']
        );
        $errors = MarketDataCalendarStatusTraceabilityGate::validate($rows)['errors'];
        $this->assertNotEmpty(array_filter($errors, static function ($error) {
            return strpos($error, 'MD-S058-R0018') !== false;
        }));
    }

    public function test_proof_map_exactly_partitions_the_current_b06_denominator(): void
    {
        $owners = MarketDataCalendarStatusTraceabilitySpec::requiredOwners();
        $expected = [];
        foreach ($owners as $document => $rows) {
            foreach ($rows as $number => $stage) {
                if ($stage === 'MD-B06') {
                    $expected[] = MarketDataCalendarStatusTraceabilitySpec::ruleId($document, $number);
                }
            }
        }
        sort($expected, SORT_STRING);
        $actual = array_keys(MarketDataCalendarStatusProofGate::proofMap());

        $this->assertSame($expected, $actual);
        $this->assertCount(78, $actual);
    }

    public function test_the_proof_map_names_only_existing_behavior_methods(): void
    {
        $checked = 0;
        foreach (MarketDataCalendarStatusProofGate::proofMap() as $rule => $proof) {
            foreach ($proof['methods'] as [$file, $method]) {
                $source = @file_get_contents($this->root().'/'.$file);
                $this->assertNotFalse($source, $rule.': missing '.$file);
                $this->assertStringContainsString('function '.$method.'(', $source, $rule.': '.$method);
                $checked++;
            }
        }
        $this->assertGreaterThan(300, $checked);
    }

    public function test_proof_gate_rejects_an_unbound_rule(): void
    {
        $rows = $this->rows();
        $index = $this->indexOf($rows, 'MD-S058-R0040');
        $rows[$index]['coverage_status'] = 'NOT_ASSESSED';
        $errors = MarketDataCalendarStatusProofGate::validate($rows, $this->root())['errors'];

        $this->assertNotEmpty(array_filter($errors, static function ($error) {
            return strpos($error, 'MD-S058-R0040') !== false;
        }));
    }
}
