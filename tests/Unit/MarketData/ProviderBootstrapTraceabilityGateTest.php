<?php

require_once dirname(__DIR__, 3).'/docs/market_data/development/implementation/tests/MarketDataProviderBootstrapTraceabilityGate.php';

use PHPUnit\Framework\TestCase;

class ProviderBootstrapTraceabilityGateTest extends TestCase
{
    private function rows(): array
    {
        $path = dirname(__DIR__, 3).'/docs/market_data/authority/governance/STRATEGY_TO_IMPLEMENTATION_TRACEABILITY_MATRIX.csv';

        return MarketDataClassificationConsistencyGate::readMatrix($path)['rows'];
    }

    public function test_current_normalization_is_exact_and_has_no_transitional_applicability(): void
    {
        $result = MarketDataProviderBootstrapTraceabilityGate::validateNormalization($this->rows());

        $this->assertSame([], $result['errors'], implode("\n", $result['errors']));
        $this->assertSame(['mandatory_b02' => 86, 'optional_b02' => 6, 'not_applicable_b02' => 6], $result['counts']);
    }

    public function test_reference_only_mixed_member_regression_is_load_bearing(): void
    {
        $rows = $this->rows();
        foreach ($rows as &$row) {
            if ($row['rule_id'] === 'MD-S059-R0128') {
                $row['coverage_requirement'] = 'REFERENCE_ONLY';
                $row['applicability'] = 'REFERENCE_ONLY';
                $row['coverage_status'] = 'REFERENCE_ONLY';
                break;
            }
        }
        unset($row);

        $errors = MarketDataProviderBootstrapTraceabilityGate::validateNormalization($rows)['errors'];
        $this->assertNotEmpty(array_filter($errors, static fn ($error) => strpos($error, 'MD-S059-R0128') !== false));
        $classErrors = MarketDataClassificationConsistencyGate::validate($rows)['errors'];
        $this->assertNotEmpty(array_filter($classErrors, static fn ($error) => strpos($error, 'MIXED_RUN MD-S059-R0128') !== false));
    }

    public function test_structural_table_header_cannot_be_promoted_to_fake_coverage(): void
    {
        $rows = $this->rows();
        foreach ($rows as &$row) {
            if ($row['rule_id'] === 'MD-S059-R0116') {
                $row['coverage_requirement'] = 'REQUIRED';
                $row['applicability'] = 'MANDATORY';
                $row['coverage_status'] = 'NOT_ASSESSED';
                break;
            }
        }
        unset($row);

        $errors = MarketDataProviderBootstrapTraceabilityGate::validateNormalization($rows)['errors'];
        $this->assertNotEmpty(array_filter($errors, static fn ($error) => strpos($error, 'MD-S059-R0116') !== false));
        $classErrors = MarketDataClassificationConsistencyGate::validate($rows)['errors'];
        $this->assertNotEmpty(array_filter($classErrors, static fn ($error) => strpos($error, 'REQUIRED_STRUCTURE MD-S059-R0116') !== false));
    }

    public function test_proof_owner_move_and_applicability_lifecycle_are_load_bearing(): void
    {
        $rows = $this->rows();
        foreach ($rows as &$row) {
            if ($row['rule_id'] === 'MD-S059-R0044') {
                $row['primary_stage'] = 'MD-B02';
            }
            if ($row['rule_id'] === 'MD-S059-R0105') {
                $row['applicability'] = 'MANDATORY_OR_CONDITIONAL';
                $row['coverage_status'] = 'NOT_ASSESSED';
            }
        }
        unset($row);

        $errors = MarketDataProviderBootstrapTraceabilityGate::validateNormalization($rows)['errors'];
        $this->assertNotEmpty(array_filter($errors, static fn ($error) => strpos($error, 'MD-S059-R0044') !== false));
        $this->assertNotEmpty(array_filter($errors, static fn ($error) => strpos($error, 'MD-S059-R0105') !== false));
    }
}
