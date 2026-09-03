<?php

require_once dirname(__DIR__, 3).'/docs/market_data/development/implementation/tests/MarketDataConfigFoundationTraceabilityGate.php';

use PHPUnit\Framework\TestCase;

class ConfigFoundationTraceabilityGateTest extends TestCase
{
    private function rows(): array
    {
        $path = dirname(__DIR__, 3).'/docs/market_data/authority/governance/STRATEGY_TO_IMPLEMENTATION_TRACEABILITY_MATRIX.csv';

        return MarketDataClassificationConsistencyGate::readMatrix($path)['rows'];
    }

    public function test_current_b04_normalization_is_exact(): void
    {
        $result = MarketDataConfigFoundationTraceabilityGate::validate($this->rows());

        $this->assertSame([], $result['errors'], implode("\n", $result['errors']));
        $this->assertSame(
            ['mandatory_b04' => 114, 'moved' => 181, 'reference' => 645],
            $result['counts']
        );
    }

    public function test_list_introducer_cannot_reenter_the_denominator(): void
    {
        $rows = $this->rows();
        $index = $this->indexOf($rows, 'MD-S085-R0001');
        $this->assertSame('REFERENCE_ONLY', $rows[$index]['coverage_requirement']);
        $rows[$index]['coverage_requirement'] = 'REQUIRED';
        $rows[$index]['applicability'] = 'MANDATORY';
        $rows[$index]['coverage_status'] = 'NOT_ASSESSED';

        $this->assertNotSame([], MarketDataConfigFoundationTraceabilityGate::validate($rows)['errors']);
        $classErrors = MarketDataClassificationConsistencyGate::validate($rows)['errors'];
        $this->assertNotEmpty(array_filter(
            $classErrors,
            static fn ($error) => strpos($error, 'REQUIRED_STRUCTURE MD-S085-R0001') !== false
        ));
    }

    public function test_moved_predicate_requires_its_owner_and_b04_support_link(): void
    {
        $rows = $this->rows();
        $index = $this->indexOf($rows, 'MD-S005-R0001');
        $this->assertSame('MD-B10', $rows[$index]['primary_stage']);
        $rows[$index]['primary_stage'] = 'MD-B04';
        $rows[$index]['supporting_stages'] = '';

        $errors = MarketDataConfigFoundationTraceabilityGate::validate($rows)['errors'];
        $this->assertNotEmpty(array_filter($errors, static fn ($error) => strpos($error, 'MD-S005-R0001') !== false));
    }

    public function test_required_predicate_cannot_lose_structured_context(): void
    {
        $rows = $this->rows();
        $index = $this->indexOf($rows, 'MD-S085-R0002');
        $this->assertStringContainsString('predicate_context=MD-S085-R0001', $rows[$index]['notes']);
        $rows[$index]['notes'] = '';

        $errors = MarketDataConfigFoundationTraceabilityGate::validate($rows)['errors'];
        $this->assertNotEmpty(array_filter($errors, static fn ($error) => strpos($error, 'MD-S085-R0002') !== false));
    }

    private function indexOf(array $rows, string $ruleId): int
    {
        foreach ($rows as $index => $row) {
            if ($row['rule_id'] === $ruleId) {
                return $index;
            }
        }

        throw new RuntimeException('Missing traceability row '.$ruleId);
    }
}
