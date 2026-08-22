<?php

use PHPUnit\Framework\TestCase;

/**
 * `MD-S020-R0067` — every contract that uses the `eligible` alias must repeat that it means
 * `data_usable`, "and that repetition is the only thing preventing the misreading".
 *
 * `D-MD-20260822-04` resolved how that obligation is discharged for the one frozen strategy contract
 * that names the alias without restating it: through the canonical semantic owner plus that
 * document's own explicit delegation of field semantics. The decision did not mark the rule
 * satisfied — it made it provable. This suite is the proof.
 *
 * The predicate is a chain, so it is tested as one. Four links must each hold:
 *
 *   1. `Terminology_and_Scope.md` owns the term and states the meaning;
 *   2. `CONSUMER_READ_CONTRACT_LOCKED.md` delegates field semantics to the read-model contract;
 *   3. `Downstream_Consumer_Read_Model_Contract_LOCKED.md` states the meaning in full;
 *   4. the owner boundary names that contract in its required cross-contract alignment.
 *
 * Removing any one link breaks the basis the decision rests on, so each is asserted separately
 * rather than as a single aggregate. A chain proven only end-to-end would stay green while an
 * intermediate document quietly dropped the sentence that carries it.
 *
 * No strategy byte is read for anything but its content, and none is written.
 */
class AliasMeaningOwnershipChainTest extends TestCase
{
    private const TERM_OWNER = 'docs/market_data/authority/strategy/book/Terminology_and_Scope.md';

    private const READINESS_CONTRACT = 'docs/market_data/authority/strategy/book/CONSUMER_READ_CONTRACT_LOCKED.md';

    private const READ_MODEL_CONTRACT = 'docs/market_data/authority/strategy/book/Downstream_Consumer_Read_Model_Contract_LOCKED.md';

    private const OWNER_BOUNDARY = 'docs/market_data/authority/strategy/book/Domain_Boundary_Invariants_LOCKED.md';

    private const DECISION = 'docs/market_data/records/decisions/D-MD-20260822-04_ELIGIBLE_ALIAS_OWNERSHIP_PRECEDENCE.md';

    private function root(): string
    {
        return dirname(__DIR__, 3);
    }

    private function read(string $relative): string
    {
        $path = $this->root().'/'.$relative;
        $body = @file_get_contents($path);
        if ($body === false) {
            $this->fail('Document under proof is unreadable: '.$relative);
        }

        return (string) $body;
    }

    /**
     * Link 1 — the canonical owner. `eligibility snapshot` is in the Term ownership register, and the
     * owner states the alias meaning outright.
     */
    public function test_the_term_owner_registers_the_term_and_states_the_alias_meaning(): void
    {
        $owner = $this->read(self::TERM_OWNER);

        $this->assertMatchesRegularExpression(
            '/## Term ownership register \(LOCKED\)/',
            $owner,
            'the register section must exist for the term to have a canonical owner'
        );
        $this->assertMatchesRegularExpression(
            '/\| Dimensi fakta \|[^|]*eligibility snapshot/',
            $owner,
            'the register must claim `eligibility snapshot` as an owned term'
        );
        $this->assertStringContainsString(
            'A compatibility field named `eligible` has only this upstream data-usability meaning',
            $owner,
            'the canonical owner must state the alias meaning'
        );
    }

    /**
     * Link 2 — the readiness contract delegates. This is what makes the chain governed rather than
     * inferred: the document that names the alias says, in its own opening, where field semantics
     * are defined.
     */
    public function test_the_readiness_contract_delegates_field_semantics_to_the_read_model_contract(): void
    {
        $readiness = $this->read(self::READINESS_CONTRACT);

        $this->assertStringContainsString(
            'read only the versioned market-data read model defined by `Downstream_Consumer_Read_Model_Contract_LOCKED.md`',
            $readiness,
            'the delegation must be explicit in the contract that names the alias'
        );

        // The premise of the decision: this document names the alias and does not restate the meaning.
        // If that ever changes the decision is not wrong, but the chain is no longer what was reviewed.
        $this->assertStringContainsString('`eligible = 1`', $readiness, 'the reviewed sentence must still be the one naming the alias');
        $this->assertDoesNotMatchRegularExpression(
            '/data[_ -]?usab/i',
            $readiness,
            'if this contract starts stating the meaning itself, D-MD-20260822-04 must be revisited rather than silently outgrown'
        );
    }

    /**
     * Link 3 — the delegated contract carries the full repetition, including the freshness caveat the
     * readiness contract restates.
     */
    public function test_the_read_model_contract_states_the_alias_meaning_in_full(): void
    {
        $readModel = $this->read(self::READ_MODEL_CONTRACT);

        $this->assertStringContainsString(
            'A compatibility `eligible` field means `data_usable`',
            $readModel,
            'the delegated contract must repeat the meaning'
        );
        $this->assertStringContainsString(
            'it is not watchlist selection, tradability approval, alpha, ranking, or portfolio policy',
            $readModel,
            'the repetition must also deny the policy reading the owner boundary warns about'
        );
        $this->assertStringContainsString(
            'it does not by itself prove that the requested dataset publication is readable',
            $readModel,
            'the delegated contract must carry the freshness caveat the readiness contract restates'
        );
    }

    /**
     * Link 4 — the owner boundary requires alignment with the delegated contract, so the chain is
     * closed by the owner rather than by the decision that relies on it.
     */
    public function test_the_owner_boundary_requires_alignment_with_the_delegated_contract(): void
    {
        $boundary = $this->read(self::OWNER_BOUNDARY);

        $this->assertSame(
            1,
            preg_match('/## Required cross-contract alignment(.*?)\n## /s', $boundary, $section),
            'the alignment section must be locatable'
        );
        $this->assertStringContainsString(
            '`Downstream_Consumer_Read_Model_Contract_LOCKED.md`',
            $section[1],
            'the owner boundary must name the delegated contract among its required alignments'
        );
        $this->assertStringContainsString(
            '`Terminology_and_Scope.md`',
            $section[1],
            'the owner boundary must name the term owner among its required alignments'
        );
    }

    /**
     * The decision itself must remain present and must not have been widened into a general waiver.
     * A governed basis that can be edited into something broader is not a basis.
     */
    public function test_the_governing_decision_is_present_and_scope_limited(): void
    {
        $decision = $this->read(self::DECISION);

        $this->assertStringContainsString('- Decision status: `ISSUED`', $decision);
        $this->assertStringContainsString('- Strategy impact: `NONE`', $decision);
        $this->assertStringContainsString('## Scope limit', $decision, 'the decision must keep its scope limit');
        $this->assertStringContainsString(
            'No strategy revision is authorised or required',
            $decision,
            'the decision must keep recording that strategy was not changed'
        );
    }

    /**
     * Every link must be individually load-bearing. Each fixture removes one link from a copy of the
     * document text and asserts the corresponding check would fail — so the chain cannot rot one
     * document at a time while the suite stays green.
     */
    public function test_each_link_is_individually_load_bearing(): void
    {
        $cases = [
            'term owner definition' => [
                $this->read(self::TERM_OWNER),
                'A compatibility field named `eligible` has only this upstream data-usability meaning',
            ],
            'delegation sentence' => [
                $this->read(self::READINESS_CONTRACT),
                'read only the versioned market-data read model defined by `Downstream_Consumer_Read_Model_Contract_LOCKED.md`',
            ],
            'read-model repetition' => [
                $this->read(self::READ_MODEL_CONTRACT),
                'A compatibility `eligible` field means `data_usable`',
            ],
            'owner alignment listing' => [
                $this->read(self::OWNER_BOUNDARY),
                '`Downstream_Consumer_Read_Model_Contract_LOCKED.md`',
            ],
        ];

        foreach ($cases as $label => [$text, $needle]) {
            $this->assertStringContainsString($needle, $text, $label.': the link must be present to begin with');
            $broken = str_replace($needle, '', $text, $count);
            $this->assertGreaterThan(0, $count, $label.': the removal must land before the check is judged');
            $this->assertStringNotContainsString($needle, $broken, $label.': removing the link must actually remove it');
        }
    }
}
