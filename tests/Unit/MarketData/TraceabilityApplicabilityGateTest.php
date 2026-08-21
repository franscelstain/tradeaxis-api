<?php

use PHPUnit\Framework\TestCase;

require_once dirname(__DIR__, 3).'/docs/market_data/development/implementation/tests/MarketDataTraceabilityApplicabilityGate.php';

class TraceabilityApplicabilityGateTest extends TestCase
{
    /** @return array<int,array<string,string>> */
    private function rows(): array
    {
        $path = dirname(__DIR__, 3).'/docs/market_data/authority/governance/STRATEGY_TO_IMPLEMENTATION_TRACEABILITY_MATRIX.csv';

        return MarketDataTraceabilityApplicabilityGate::readMatrix($path)['rows'];
    }

    public function test_current_md_b01_scope_has_explicit_applicability_and_bound_predicates(): void
    {
        $result = MarketDataTraceabilityApplicabilityGate::validate($this->rows());

        $this->assertSame([], $result['errors']);
        $this->assertSame(207, $result['counts']['required_rows']);
        $this->assertSame(201, $result['counts']['mandatory']);
        $this->assertSame(6, $result['counts']['conditional_applicable']);
        $this->assertSame(0, $result['counts']['conditional_pending']);
    }

    /**
     * The 72 rows `MD-B01-A014` promoted are checked against a context recomputed from the matrix,
     * not against the note that declares it. A note that names a parent the row does not have must
     * be rejected, or the structured note would only be validating itself.
     */
    public function test_gate_fails_closed_when_a_promoted_row_declares_a_parent_it_does_not_have(): void
    {
        $rows = $this->rows();
        $index = $this->indexOf($rows, 'MD-S020-R0025');
        $mutated = str_replace(
            'predicate_context=MD-S020-R0017',
            'predicate_context=MD-S020-R0008',
            $rows[$index]['notes'],
            $count
        );
        $this->assertSame(1, $count, 'mutation must land before the guard is judged');
        $rows[$index]['notes'] = $mutated;

        $this->assertNotSame([], MarketDataTraceabilityApplicabilityGate::validate($rows)['errors']);
    }

    public function test_gate_fails_closed_when_a_promoted_row_normalizes_a_predicate_it_does_not_state(): void
    {
        $rows = $this->rows();
        $index = $this->indexOf($rows, 'MD-S020-R0071');
        $mutated = preg_replace(
            '/normalized_predicate=.+?; applicability_basis=/',
            'normalized_predicate=Market Data owns every published market fact and its lineage; applicability_basis=',
            $rows[$index]['notes'],
            1,
            $count
        );
        $this->assertSame(1, $count, 'mutation must land before the guard is judged');
        $rows[$index]['notes'] = $mutated;

        $this->assertNotSame([], MarketDataTraceabilityApplicabilityGate::validate($rows)['errors']);
    }

    public function test_gate_fails_closed_when_a_promoted_row_is_demoted_back_out_of_the_denominator(): void
    {
        $rows = $this->rows();
        $index = $this->indexOf($rows, 'MD-S020-R0165');
        $this->assertSame('REQUIRED', $rows[$index]['coverage_requirement'], 'mutation target must start required');
        $rows[$index]['coverage_requirement'] = 'REFERENCE_ONLY';

        $this->assertNotSame([], MarketDataTraceabilityApplicabilityGate::validate($rows)['errors']);
    }

    public function test_gate_fails_closed_on_a_legacy_transitional_applicability(): void
    {
        $rows = $this->rows();
        $rows[$this->indexOf($rows, 'MD-S001-R0003')]['applicability'] = 'MANDATORY_OR_CONDITIONAL';

        $this->assertNotSame([], MarketDataTraceabilityApplicabilityGate::validate($rows)['errors']);
    }

    public function test_gate_fails_closed_when_a_fragment_loses_its_parent_binding(): void
    {
        $rows = $this->rows();
        $index = $this->indexOf($rows, 'MD-S001-R0033');
        $rows[$index]['notes'] = str_replace('predicate_context=MD-S001-R0031;MD-S001-R0032', 'predicate_context=SELF', $rows[$index]['notes']);

        $this->assertNotSame([], MarketDataTraceabilityApplicabilityGate::validate($rows)['errors']);
    }

    public function test_gate_fails_closed_on_an_invalid_conditional_lifecycle(): void
    {
        $rows = $this->rows();
        $index = $this->indexOf($rows, 'MD-S001-R0077');
        $rows[$index]['applicability'] = 'CONDITIONAL_PENDING';
        $rows[$index]['coverage_status'] = 'SATISFIED';

        $this->assertNotSame([], MarketDataTraceabilityApplicabilityGate::validate($rows)['errors']);
    }

    public function test_gate_fails_closed_when_a_context_parent_is_unknown(): void
    {
        $rows = $this->rows();
        $index = $this->indexOf($rows, 'MD-S020-R0182');
        $rows[$index]['notes'] = str_replace('predicate_context=MD-S020-R0181', 'predicate_context=MD-S020-R9999', $rows[$index]['notes']);

        $this->assertNotSame([], MarketDataTraceabilityApplicabilityGate::validate($rows)['errors']);
    }

    public function test_gate_fails_closed_on_source_fingerprint_drift(): void
    {
        $rows = $this->rows();
        $rows[$this->indexOf($rows, 'MD-S056-R0142')]['rule_text'] .= ' drift';

        $this->assertNotSame([], MarketDataTraceabilityApplicabilityGate::validate($rows)['errors']);
    }

    public function test_current_system_summary_carries_the_three_previously_missing_state_predicates(): void
    {
        $summary = (string) file_get_contents(
            dirname(__DIR__, 3).'/docs/market_data/development/implementation/guides/system/SYSTEM_DATA_PRODUCT_MAP.md'
        );

        $this->assertSame([], $this->summaryErrors($summary));
    }

    public function test_current_system_summary_guard_fails_closed_when_any_state_predicate_is_removed(): void
    {
        $summary = (string) file_get_contents(
            dirname(__DIR__, 3).'/docs/market_data/development/implementation/guides/system/SYSTEM_DATA_PRODUCT_MAP.md'
        );
        foreach ([
            'archived proof window' => 'archived proof gap',
            'official IDX authority' => 'authority gap',
            'bila ringkasan ini berbeda, definisi owner tersebut yang berlaku' => 'precedence gap',
        ] as $predicate => $mutation) {
            $mutated = str_replace($predicate, $mutation, $summary, $count);
            $this->assertSame(1, $count, $predicate.' mutation must land before the guard is judged');
            $this->assertNotSame([], $this->summaryErrors($mutated), $predicate.' mutation must fail closed');
        }
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

    /** @return array<int,string> */
    private function summaryErrors(string $summary): array
    {
        $required = [
            'MD-S001-R0155' => '/2023-01-02` sampai `2025-10-31` adalah archived proof window, bukan dataset end atau current-freshness proof/',
            'MD-S001-R0158' => '/source-state\/internal conformance tidak membuktikan official IDX authority, commercial data SLA, redistribution right, atau achieved `decision-grade` correctness/i',
            'MD-S056-R0142' => '/Terminology_and_Scope\.md.*bila ringkasan ini berbeda, definisi owner tersebut yang berlaku/is',
        ];
        $errors = [];
        foreach ($required as $ruleId => $pattern) {
            if (!preg_match($pattern, $summary)) {
                $errors[] = $ruleId;
            }
        }

        return $errors;
    }
}
