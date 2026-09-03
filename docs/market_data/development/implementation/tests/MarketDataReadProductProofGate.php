<?php

require_once __DIR__.'/MarketDataReadProductProofSpec.php';

/**
 * Governed `MD-B17` proof gate. Usage: php this-file.php [--bound].
 *
 * Without `--bound` it asserts the stage is pristine: every mandatory predicate unassessed and
 * unbound. With `--bound` it asserts every one of them is SATISFIED against a governed `MD-B17`
 * evidence id. Both modes verify the whole proof map, so a stage cannot reach either state with a
 * predicate that no family claims or a family whose guard does not exist.
 *
 * The by-name existence check on every positive and negative method is the part that has caught
 * real defects: a proof map is otherwise a list of intentions, and a method renamed or never
 * written reads exactly like one that runs.
 */
final class MarketDataReadProductProofGate
{
    public const EVIDENCE_PATTERN = '/^E-MD-B17-A002-\d{3}$/';

    /**
     * @param  array<string,mixed>  $overrides
     * @return array<string,mixed>
     */
    public static function validate(string $root, bool $bound = false, array $overrides = []): array
    {
        $spec = 'MarketDataReadProductProofSpec';
        $traceability = 'MarketDataReadProductTraceabilitySpec';

        $mandatory = isset($overrides['mandatory'])
            ? $overrides['mandatory']
            : $traceability::mandatory($root);
        $entries = isset($overrides['entries']) ? $overrides['entries'] : $spec::entries($root);
        $families = isset($overrides['families']) ? $overrides['families'] : $spec::families();

        $errors = [];
        $byRule = [];
        $usedFamilies = [];

        foreach ($mandatory as $row) {
            $byRule[$row['rule_id']] = $row;
            $evidence = trim((string) (isset($row['current_evidence_ids']) ? $row['current_evidence_ids'] : ''));

            if ($bound) {
                if ((isset($row['coverage_status']) ? $row['coverage_status'] : '') !== 'SATISFIED'
                    || preg_match(self::EVIDENCE_PATTERN, $evidence) !== 1) {
                    $errors[] = 'BOUND_STATE_INVALID:'.$row['rule_id'];
                }
            } elseif ((isset($row['coverage_status']) ? $row['coverage_status'] : '') !== 'NOT_ASSESSED'
                || $evidence !== '') {
                $errors[] = 'PREMATURE_BINDING:'.$row['rule_id'];
            }
        }

        if (count($mandatory) !== $spec::EXPECTED_DENOMINATOR) {
            $errors[] = 'DENOMINATOR_MISMATCH:'.count($mandatory);
        }
        if (count($entries) !== $spec::EXPECTED_DENOMINATOR) {
            $errors[] = 'PROOF_MAP_COUNT_MISMATCH:'.count($entries);
        }

        $seen = [];
        foreach ($entries as $entry) {
            $ruleId = isset($entry['rule_id']) ? $entry['rule_id'] : '';

            if (isset($seen[$ruleId])) {
                $errors[] = 'DUPLICATE_ENTRY:'.$ruleId;

                continue;
            }
            $seen[$ruleId] = true;

            if (! isset($byRule[$ruleId])) {
                $errors[] = 'ORPHAN_ENTRY:'.$ruleId;

                continue;
            }

            try {
                $expected = $spec::familyFor($byRule[$ruleId]);
            } catch (Throwable $failure) {
                $errors[] = 'UNMAPPED_FAMILY:'.$ruleId;

                continue;
            }

            if ((isset($entry['family']) ? $entry['family'] : '') !== $expected) {
                $errors[] = 'WRONG_FAMILY:'.$ruleId;
            }

            if (! isset($families[$expected])) {
                $errors[] = 'MISSING_FAMILY:'.$expected;

                continue;
            }
            $usedFamilies[$expected] = true;
            $family = $families[$expected];

            if (strpos((string) (isset($family['owner']) ? $family['owner'] : ''), 'MD-B17:') !== 0) {
                $errors[] = 'WRONG_OWNER:'.$expected;
            }

            foreach (isset($family['implementation']) ? $family['implementation'] : [] as $path) {
                if (! file_exists($root.'/'.$path)) {
                    $errors[] = 'MISSING_IMPL:'.$path;
                }
            }

            foreach (['positive', 'negative'] as $kind) {
                $reference = isset($family[$kind]) ? $family[$kind] : [];
                $file = isset($reference[0]) ? $reference[0] : '';
                $method = isset($reference[1]) ? $reference[1] : '';
                $source = is_file($root.'/'.$file) ? (string) file_get_contents($root.'/'.$file) : '';

                if ($source === '' || strpos($source, 'function '.$method.'(') === false) {
                    $errors[] = 'MISSING_'.strtoupper($kind).'_PROOF:'.$expected;
                }
            }

            // A family whose positive and negative guard are the same method proves one thing
            // twice and calls it two proofs.
            if (isset($family['positive'][1], $family['negative'][1])
                && $family['positive'][1] === $family['negative'][1]) {
                $errors[] = 'DEGENERATE_PROOF_PAIR:'.$expected;
            }
        }

        foreach ($byRule as $ruleId => $row) {
            if (! isset($seen[$ruleId])) {
                $errors[] = 'UNMAPPED:'.$ruleId;
            }
        }

        foreach ($families as $name => $family) {
            if (! isset($usedFamilies[$name])) {
                $errors[] = 'UNUSED_FAMILY:'.$name;
            }
        }

        return [
            'gate' => 'MarketDataReadProductProofGate',
            'stage_id' => $spec::STAGE,
            'attempt_id' => $spec::ATTEMPT,
            'status' => $errors === [] ? 'PASS' : 'FAIL',
            'denominator' => count($mandatory),
            'proof_map_count' => count($entries),
            'proof_families_used' => count($usedFamilies),
            'bound' => $bound,
            'runtime_pending' => $bound ? 0 : count($mandatory),
            'errors' => array_values(array_unique($errors)),
        ];
    }
}

if (realpath(isset($_SERVER['SCRIPT_FILENAME']) ? $_SERVER['SCRIPT_FILENAME'] : '') === __FILE__) {
    $result = MarketDataReadProductProofGate::validate(
        dirname(__DIR__, 5),
        in_array('--bound', $argv, true)
    );
    $result['generated_at'] = date(DATE_ATOM);
    echo json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES).PHP_EOL;
    exit($result['status'] === 'PASS' ? 0 : 1);
}
