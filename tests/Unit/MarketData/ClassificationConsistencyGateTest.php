<?php

use PHPUnit\Framework\TestCase;

require_once dirname(__DIR__, 3).'/docs/market_data/development/implementation/tests/MarketDataClassificationConsistencyGate.php';

/**
 * Fail-closed self-test for the classification gate raised at `MD-B01-A014`.
 *
 * Every mutation is asserted to have landed before the gate is judged. A mutation that does not
 * mutate is indistinguishable from a guard that does not guard, which this codebase has already
 * recorded once at `MD-B01-A002`.
 */
class ClassificationConsistencyGateTest extends TestCase
{
    /** @return array<int,array<string,string>> */
    private function rows(): array
    {
        $path = dirname(__DIR__, 3).'/docs/market_data/authority/governance/STRATEGY_TO_IMPLEMENTATION_TRACEABILITY_MATRIX.csv';

        return MarketDataClassificationConsistencyGate::readMatrix($path)['rows'];
    }

    public function test_normalized_stages_carry_no_mixed_classification_run(): void
    {
        $result = MarketDataClassificationConsistencyGate::validate($this->rows());

        $this->assertSame([], $result['errors']);
    }

    /**
     * A gate that matches nothing must not be indistinguishable from a clean corpus. These floors
     * are the reason a silently-broken scan cannot report PASS.
     */
    public function test_the_scan_actually_reaches_the_corpus_it_claims_to_check(): void
    {
        $result = MarketDataClassificationConsistencyGate::validate($this->rows());

        $this->assertGreaterThanOrEqual(6000, $result['counts']['active_rows']);
        $this->assertGreaterThanOrEqual(700, $result['runs']);
        $this->assertGreaterThan(2000, $result['counts']['required']);
    }

    /**
     * The unopened stages are reported rather than excused. If this ever reads zero without a
     * governed per-stage resolution, the backlog has been hidden rather than closed.
     */
    public function test_the_unopened_stage_backlog_is_reported_not_suppressed(): void
    {
        $result = MarketDataClassificationConsistencyGate::validate($this->rows());

        $this->assertGreaterThan(0, array_sum($result['pending']));
        $this->assertArrayNotHasKey('MD-B01', $result['pending'], 'a normalized stage must error, never queue');
    }

    public function test_gate_fails_closed_when_a_normalized_stage_run_becomes_mixed_again(): void
    {
        $rows = $this->rows();
        $index = $this->indexOf($rows, 'MD-S020-R0161');
        $this->assertSame('REQUIRED', $rows[$index]['coverage_requirement'], 'mutation target must start required');
        $rows[$index]['coverage_requirement'] = 'REFERENCE_ONLY';

        $errors = MarketDataClassificationConsistencyGate::validate($rows)['errors'];
        $this->assertNotSame([], $errors);
        $this->assertStringContainsString('MIXED_RUN', implode(' ', $errors));
    }

    public function test_gate_fails_closed_when_a_list_introducer_is_given_a_proof_obligation(): void
    {
        $rows = $this->rows();
        $index = $this->indexOf($rows, 'MD-S001-R0099');
        $this->assertSame('REFERENCE_ONLY', $rows[$index]['coverage_requirement'], 'mutation target must start reference-only');
        $this->assertSame(':', substr(trim($rows[$index]['rule_text']), -1), 'mutation target must be a colon introducer');
        $rows[$index]['coverage_requirement'] = 'REQUIRED';

        $errors = MarketDataClassificationConsistencyGate::validate($rows)['errors'];
        $this->assertNotSame([], $errors);
        $this->assertStringContainsString('REQUIRED_STRUCTURE', implode(' ', $errors));
    }

    /**
     * Section 3: a required bare document reference without a governing context can only be proven
     * by the target existing, which is the exact substitution the standard forbids.
     */
    public function test_gate_fails_closed_when_a_required_bare_reference_loses_its_governing_context(): void
    {
        $rows = $this->rows();
        $index = $this->indexOf($rows, 'MD-S020-R0182');
        $this->assertSame('REQUIRED', $rows[$index]['coverage_requirement'], 'mutation target must start required');
        $this->assertSame('BARE_DOC_REF', MarketDataClassificationConsistencyGate::structuralClass($rows[$index]['rule_text']));
        $mutated = str_replace('predicate_context=MD-S020-R0181', 'predicate_context=SELF', $rows[$index]['notes'], $count);
        $this->assertSame(1, $count, 'mutation must land before the guard is judged');
        $rows[$index]['notes'] = $mutated;

        $errors = MarketDataClassificationConsistencyGate::validate($rows)['errors'];
        $this->assertNotSame([], $errors);
        $this->assertStringContainsString('REQUIRED_STRUCTURE', implode(' ', $errors));
    }

    public function test_gate_fails_closed_on_a_vacuous_scan(): void
    {
        $rows = array_slice($this->rows(), 0, 12);

        $errors = MarketDataClassificationConsistencyGate::validate($rows)['errors'];
        $this->assertNotSame([], $errors);
        $this->assertStringContainsString('VACUOUS_SCAN', implode(' ', $errors));
    }

    /**
     * Grammatical mood must never be the discriminator. `MD-S020-R0165` states its prohibition with
     * a copula and `MD-S020-R0161` states one with a modal; both are required, and neither may be
     * demoted for how it is worded.
     */
    public function test_declarative_and_modal_invariants_are_classified_alike(): void
    {
        $rows = $this->rows();
        foreach (['MD-S020-R0160', 'MD-S020-R0161', 'MD-S020-R0165', 'MD-S020-R0170', 'MD-S020-R0172'] as $id) {
            $row = $rows[$this->indexOf($rows, $id)];
            $this->assertSame('REQUIRED', $row['coverage_requirement'], $id.' is a numbered boundary invariant');
        }
    }

    /**
     * The regression test for the defect that produced this check. `MD-S066-R0002` is the second of
     * two adjacent imperative sentences in a four-line LOCKED contract; it sat REFERENCE_ONLY with
     * empty notes through two attempts and every green gate, because a paragraph carries no list
     * marker and MIXED_RUN only ever looks inside enumerated runs.
     */
    public function test_a_standalone_obligation_paragraph_cannot_hide_as_unexplained_reference(): void
    {
        $rows = $this->rows();
        $index = $this->indexOf($rows, 'MD-S066-R0002');
        $this->assertSame('REQUIRED', $rows[$index]['coverage_requirement'], 'mutation target must start required');
        $this->assertNull(
            MarketDataClassificationConsistencyGate::structuralClass($rows[$index]['rule_text']),
            'the row must be non-structural or this check would not apply to it'
        );

        // Exactly the state it was in before MD-B07-A002 found it by hand.
        $rows[$index]['coverage_requirement'] = 'REFERENCE_ONLY';
        $rows[$index]['coverage_status'] = 'REFERENCE_ONLY';
        $rows[$index]['current_evidence_ids'] = '';
        $rows[$index]['notes'] = '';

        $errors = MarketDataClassificationConsistencyGate::validate($rows)['errors'];
        $this->assertNotSame([], $errors);
        $this->assertStringContainsString('UNEXPLAINED_REFERENCE MD-S066-R0002', implode(' ', $errors));
    }

    public function test_gate_fails_closed_when_a_recorded_reference_decision_is_erased(): void
    {
        $rows = $this->rows();
        $index = $this->indexOf($rows, 'MD-S007-R0001');
        $this->assertTrue(
            MarketDataClassificationConsistencyGate::hasRecordedReferenceDecision($rows[$index]),
            'mutation target must start with a recorded decision'
        );
        $rows[$index]['notes'] = '';

        $errors = MarketDataClassificationConsistencyGate::validate($rows)['errors'];
        $this->assertStringContainsString('UNEXPLAINED_REFERENCE MD-S007-R0001', implode(' ', $errors));
    }

    /**
     * A stage pass that clears another stage's proof leaves half-cleared state behind. This is the
     * shape MD-B07-A002 produced across 30 closed predicates while every gate stayed green.
     */
    public function test_gate_fails_closed_when_proof_state_is_half_cleared(): void
    {
        $rows = $this->rows();
        $index = $this->indexOf($rows, 'MD-S066-R0002');
        $this->assertSame('SATISFIED', $rows[$index]['coverage_status'], 'mutation target must start satisfied');
        $this->assertNotSame('', trim($rows[$index]['current_evidence_ids']), 'mutation target must start bound');
        $rows[$index]['current_evidence_ids'] = '';

        $errors = MarketDataClassificationConsistencyGate::validate($rows)['errors'];
        $this->assertStringContainsString('BINDING_COHERENCE MD-S066-R0002', implode(' ', $errors));
    }

    /**
     * The unexplained-reference backlog must stay visible. If it reaches zero without every stage
     * joining DECISION_RECORDED_STAGES, the debt was hidden rather than paid.
     */
    public function test_the_unexplained_reference_backlog_is_reported_not_suppressed(): void
    {
        $result = MarketDataClassificationConsistencyGate::validate($this->rows());

        $this->assertGreaterThan(0, array_sum($result['unexplained_reference_pending']));
        $this->assertGreaterThanOrEqual(2000, $result['counts']['reference_only']);
        $this->assertSame(0, $result['counts']['binding_incoherent']);
        foreach (MarketDataClassificationConsistencyGate::DECISION_RECORDED_STAGES as $stage) {
            $this->assertArrayNotHasKey(
                $stage,
                $result['unexplained_reference_pending'],
                $stage.' has completed its re-check and must error rather than queue'
            );
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
}
