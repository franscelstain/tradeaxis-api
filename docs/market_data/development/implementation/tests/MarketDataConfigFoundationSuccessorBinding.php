<?php

require_once __DIR__.'/MarketDataB10SuccessorBinding.php';
require_once __DIR__.'/MarketDataConfigFoundationProofGate.php';

/**
 * Governed MD-B04-A003 successor binding.
 *
 * MD-B04-A002 bound all 114 mandatory B04 predicates to E-MD-B04-A002-001 through
 * MarketDataConfigFoundationProofBinder, which rewrites every row and is left unchanged. MD-B04-A003 reopened
 * exactly two of them (MD-S082-R0036, MD-S082-R0044; scope evidence E-MD-B04-A003-001) and re-proved them
 * (E-MD-B04-A003-002). This is the mechanism that promotes exactly those two rows.
 *
 * Invariants:
 *  - only MD-S082-R0036 and MD-S082-R0044 change, and only coverage_status, current_evidence_ids and notes; every
 *    other line of the matrix is emitted byte-for-byte;
 *  - both rows must currently be NOT_ASSESSED with no evidence and carry the A003 entry note; every other B04 row
 *    must stay SATISFIED on the A002 evidence;
 *  - the proof evidence must exist, belong to MD-B04-A003 / MD-B04-A003-BL001 / CI-MD-B04-A003-001 and name exactly
 *    the two predicates as rebound;
 *  - the write is all or nothing: the result is read back and compared line by line, and any failure restores the
 *    original bytes.
 *
 * Usage: php MarketDataConfigFoundationSuccessorBinding.php [--apply]
 */
final class MarketDataConfigFoundationSuccessorBinding
{
    public const ENTRY_NOTE = 'MD-B04-A003: affected_scope=E-MD-B04-A003-001';

    public static function proofNote(): string
    {
        return 'MD-B04-A003: proof_binding='.MarketDataConfigFoundationProofGate::SUCCESSOR_EVIDENCE
            .'; proof_chain=current successor authority (D-MD-B18-A002-018) -> producer (the reason-registry identity as a derived member of every new configuration snapshot) -> positive and discriminating tests -> mutation probes -> governed evidence'
            .'; carried_from=E-MD-B04-A002-001 withdrawn for this predicate at A003 entry; dependency=MD-DEP-0023';
    }

    /**
     * @param array{rows:array<int,array<string,string>>,lines:array<int,string>} $matrix MarketDataB10SuccessorBinding::readMatrix result
     * @return array{errors:array<int,string>,lines:array<int,string>,changed:array<int,string>}
     */
    public static function plan(array $matrix, string $root): array
    {
        $errors = [];
        $lines = $matrix['lines'];
        $changed = [];
        $affected = MarketDataConfigFoundationProofGate::SUCCESSOR_RULES;

        $evidence = glob($root.'/docs/market_data/records/evidence/'.MarketDataConfigFoundationProofGate::SUCCESSOR_EVIDENCE.'_*.json');
        $payload = $evidence ? json_decode((string) file_get_contents($evidence[0]), true) : null;
        if (! is_array($payload)
            || ($payload['evidence_id'] ?? null) !== MarketDataConfigFoundationProofGate::SUCCESSOR_EVIDENCE
            || ($payload['attempt_id'] ?? null) !== 'MD-B04-A003'
            || ($payload['baseline_id'] ?? null) !== 'MD-B04-A003-BL001'
            || ($payload['change_impact_declaration'] ?? null) !== 'CI-MD-B04-A003-001'
            || ($payload['mutability'] ?? null) !== 'IMMUTABLE_AFTER_ISSUE'
            || ($payload['rebound_predicates'] ?? null) !== $affected) {
            $errors[] = 'the A003 proof evidence is missing, mutable, or does not name exactly the two rebound predicates';
        }

        $seen = [];
        $b04 = 0;
        foreach ($matrix['rows'] as $i => $row) {
            if ($row['active'] !== 'YES' || $row['primary_stage'] !== 'MD-B04' || $row['coverage_requirement'] !== 'REQUIRED' || $row['applicability'] !== 'MANDATORY') {
                continue;
            }
            $b04++;
            $rule = $row['rule_id'];
            if (! in_array($rule, $affected, true)) {
                if ($row['coverage_status'] !== 'SATISFIED' || trim($row['current_evidence_ids']) !== MarketDataConfigFoundationProofGate::EVIDENCE) {
                    $errors[] = $rule.': an unaffected B04 row must stay SATISFIED on '.MarketDataConfigFoundationProofGate::EVIDENCE;
                }
                continue;
            }
            $seen[] = $rule;
            if ($row['coverage_status'] !== 'NOT_ASSESSED' || trim($row['current_evidence_ids']) !== '' || strpos($row['notes'], self::ENTRY_NOTE) === false) {
                $errors[] = $rule.': must be NOT_ASSESSED with no evidence and carry the A003 entry note before it is promoted';
                continue;
            }
            $style = MarketDataB10SuccessorBinding::detectStyle($matrix['lines'][$i]);
            if ($style === null) {
                $errors[] = $rule.': the matrix line does not round-trip';
                continue;
            }
            $new = $row;
            $new['coverage_status'] = 'SATISFIED';
            $new['current_evidence_ids'] = MarketDataConfigFoundationProofGate::SUCCESSOR_EVIDENCE;
            $new['notes'] = trim($row['notes']).' | '.self::proofNote();
            $lines[$i] = MarketDataB10SuccessorBinding::encodeLine(array_values($new), $style);
            $changed[] = $rule;
        }
        if ($b04 !== 114) {
            $errors[] = 'the B04 denominator is '.$b04.', expected 114';
        }
        if ($seen !== $affected) {
            $errors[] = 'the rebound rules found in the matrix are not exactly '.implode(', ', $affected);
        }

        return ['errors' => $errors, 'lines' => $lines, 'changed' => $changed];
    }

    /** @return array{errors:array<int,string>,changed:array<int,string>,applied:bool} */
    public static function run(string $root, bool $apply): array
    {
        $path = $root.'/docs/market_data/authority/governance/STRATEGY_TO_IMPLEMENTATION_TRACEABILITY_MATRIX.csv';
        $original = (string) file_get_contents($path);
        $matrix = MarketDataB10SuccessorBinding::readMatrix($path);
        if ($matrix['errors'] !== []) {
            return ['errors' => $matrix['errors'], 'changed' => [], 'applied' => false];
        }
        $plan = self::plan($matrix, $root);
        if ($plan['errors'] !== [] || ! $apply) {
            return ['errors' => $plan['errors'], 'changed' => $plan['changed'], 'applied' => false];
        }

        $raw = ($matrix['bom'] ? "\xEF\xBB\xBF" : '').$matrix['header_line']."\n".implode("\n", $plan['lines'])."\n";
        file_put_contents($path, $raw);
        $after = MarketDataB10SuccessorBinding::readMatrix($path);
        $differing = [];
        foreach ($after['lines'] as $i => $line) {
            if ($line !== $matrix['lines'][$i]) {
                $differing[] = $after['rows'][$i]['rule_id'];
            }
        }
        if ($after['errors'] !== [] || count($after['lines']) !== count($matrix['lines']) || $differing !== $plan['changed']) {
            file_put_contents($path, $original);

            return ['errors' => ['read-back verification failed; the original matrix bytes were restored'], 'changed' => [], 'applied' => false];
        }

        return ['errors' => [], 'changed' => $plan['changed'], 'applied' => true];
    }
}

if (realpath($_SERVER['SCRIPT_FILENAME'] ?? '') === __FILE__) {
    $result = MarketDataConfigFoundationSuccessorBinding::run(dirname(__DIR__, 5), in_array('--apply', $argv, true));
    echo json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES).PHP_EOL;
    exit($result['errors'] === [] ? 0 : 1);
}
