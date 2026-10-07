<?php

require_once dirname(__DIR__, 3).'/docs/market_data/development/implementation/tests/MarketDataConfigFoundationSuccessorBinding.php';

use PHPUnit\Framework\TestCase;

/**
 * MD-B04-A003 governed successor binding: only MD-S082-R0036 and MD-S082-R0044 are promoted, every other
 * matrix line is emitted byte-for-byte, and the promotion fails closed on any precondition.
 */
class ConfigFoundationSuccessorBindingTest extends TestCase
{
    private const AFFECTED = ['MD-S082-R0036', 'MD-S082-R0044'];

    private function root(): string
    {
        return dirname(__DIR__, 3);
    }

    private function matrix(): array
    {
        return MarketDataB10SuccessorBinding::readMatrix($this->root().'/docs/market_data/authority/governance/STRATEGY_TO_IMPLEMENTATION_TRACEABILITY_MATRIX.csv');
    }

    /** The matrix as it stood between A003 entry and promotion: the two rows demoted with the entry note. */
    private function entryState(): array
    {
        $matrix = $this->matrix();
        foreach ($matrix['rows'] as $i => $row) {
            if (! in_array($row['rule_id'], self::AFFECTED, true)) {
                continue;
            }
            $notes = $row['notes'];
            $cut = strpos($notes, ' | MD-B04-A003: proof_binding=');
            if ($cut !== false) {
                $notes = substr($notes, 0, $cut);
            }
            $row['coverage_status'] = 'NOT_ASSESSED';
            $row['current_evidence_ids'] = '';
            $row['notes'] = $notes;
            $matrix['rows'][$i] = $row;
            $matrix['lines'][$i] = MarketDataB10SuccessorBinding::encodeLine(array_values($row), MarketDataB10SuccessorBinding::detectStyle($matrix['lines'][$i]));
        }

        return $matrix;
    }

    public function test_the_promotion_changes_exactly_the_two_rows_and_nothing_else(): void
    {
        $entry = $this->entryState();
        $plan = MarketDataConfigFoundationSuccessorBinding::plan($entry, $this->root());

        $this->assertSame([], $plan['errors'], implode("\n", $plan['errors']));
        $this->assertSame(self::AFFECTED, $plan['changed']);
        $differing = [];
        foreach ($plan['lines'] as $i => $line) {
            if ($line !== $entry['lines'][$i]) {
                $differing[] = $entry['rows'][$i]['rule_id'];
            }
        }
        $this->assertSame(self::AFFECTED, $differing, 'every other matrix line is byte-identical');
    }

    public function test_the_promoted_rows_carry_the_a003_evidence_and_keep_the_a002_history_in_the_notes(): void
    {
        $entry = $this->entryState();
        $plan = MarketDataConfigFoundationSuccessorBinding::plan($entry, $this->root());
        $this->assertSame([], $plan['errors']);

        $path = tempnam(sys_get_temp_dir(), 'b04a003');
        file_put_contents($path, ($entry['bom'] ? "\xEF\xBB\xBF" : '').$entry['header_line']."\n".implode("\n", $plan['lines'])."\n");
        $bound = MarketDataB10SuccessorBinding::readMatrix($path);
        unlink($path);
        $this->assertSame([], $bound['errors']);
        foreach ($bound['rows'] as $row) {
            if (! in_array($row['rule_id'], self::AFFECTED, true)) {
                continue;
            }
            $this->assertSame('SATISFIED', $row['coverage_status']);
            $this->assertSame('E-MD-B04-A003-002', $row['current_evidence_ids']);
            $this->assertStringContainsString('MD-B04-A002: proof_binding=E-MD-B04-A002-001', $row['notes'], 'the superseded binding stays visible as history');
            $this->assertStringContainsString('MD-B04-A003: affected_scope=E-MD-B04-A003-001', $row['notes']);
            $this->assertStringContainsString('MD-B04-A003: proof_binding=E-MD-B04-A003-002', $row['notes']);
        }
    }

    public function test_a_row_that_is_not_in_the_entry_state_is_not_promoted(): void
    {
        foreach (self::AFFECTED as $rule) {
            $entry = $this->entryState();
            foreach ($entry['rows'] as $i => $row) {
                if ($row['rule_id'] === $rule) {
                    $entry['rows'][$i]['coverage_status'] = 'SATISFIED';
                }
            }
            $errors = MarketDataConfigFoundationSuccessorBinding::plan($entry, $this->root())['errors'];
            $this->assertNotEmpty(array_filter($errors, static fn ($e) => strpos($e, $rule.': must be NOT_ASSESSED') !== false), $rule);
        }
    }

    public function test_a_row_without_the_a003_entry_note_is_not_promoted(): void
    {
        $entry = $this->entryState();
        foreach ($entry['rows'] as $i => $row) {
            if ($row['rule_id'] === 'MD-S082-R0036') {
                $entry['rows'][$i]['notes'] = str_replace('MD-B04-A003: affected_scope=', 'MD-B04-A003: other=', $row['notes']);
            }
        }
        $errors = MarketDataConfigFoundationSuccessorBinding::plan($entry, $this->root())['errors'];
        $this->assertNotEmpty(array_filter($errors, static fn ($e) => strpos($e, 'MD-S082-R0036: must be NOT_ASSESSED') !== false));
    }

    public function test_an_unaffected_b04_row_that_lost_its_a002_binding_blocks_the_promotion(): void
    {
        $entry = $this->entryState();
        foreach ($entry['rows'] as $i => $row) {
            if ($row['rule_id'] === 'MD-S082-R0006') {
                $entry['rows'][$i]['current_evidence_ids'] = '';
            }
        }
        $errors = MarketDataConfigFoundationSuccessorBinding::plan($entry, $this->root())['errors'];
        $this->assertNotEmpty(array_filter($errors, static fn ($e) => strpos($e, 'MD-S082-R0006: an unaffected B04 row must stay SATISFIED') !== false));
    }

    public function test_a_missing_proof_evidence_blocks_the_promotion(): void
    {
        $errors = MarketDataConfigFoundationSuccessorBinding::plan($this->entryState(), sys_get_temp_dir().'/no-such-root-b04a003')['errors'];
        $this->assertNotEmpty(array_filter($errors, static fn ($e) => strpos($e, 'the A003 proof evidence is missing') !== false));
    }

    public function test_a_denominator_that_is_not_114_blocks_the_promotion(): void
    {
        $entry = $this->entryState();
        foreach ($entry['rows'] as $i => $row) {
            if ($row['rule_id'] === 'MD-S005-R0009') {
                $entry['rows'][$i]['coverage_requirement'] = 'OPTIONAL';
            }
        }
        $errors = MarketDataConfigFoundationSuccessorBinding::plan($entry, $this->root())['errors'];
        $this->assertNotEmpty(array_filter($errors, static fn ($e) => strpos($e, 'the B04 denominator is 113, expected 114') !== false));
    }
}
