<?php

require_once __DIR__.'/MarketDataOperationsProofSpec.php';

/**
 * `MD-B19` proof gate.
 *
 * Carries forward the assertions `F-MD-B18-A001-001` forced into the MD-B18 gate, because MD-B19 is
 * six times the size and the same shortcuts would do six times the damage:
 *
 *   - the reviewed map is compared against the rule ids the matrix actually carries, independently
 *     of the map itself;
 *   - each family's size is compared against a separately declared expected count, so a
 *     re-bucketing that keeps the total at 743 does not pass;
 *   - a family whose named guard does not exist is reported by name;
 *   - a behavioural family proven by a source-text guard is rejected.
 *
 * A family with no guard assigned yet is `GUARD_NOT_ASSIGNED`, reported by name. It is a measured
 * gap, never a family that passes by default.
 */
final class MarketDataOperationsProofGate
{
    public const EVIDENCE_PATTERN = '/^E-MD-B19-A001-\d{3}$/';

    /** Families whose predicates are about what may be claimed or how documents align. */
    public const CORPUS_PROOF_FAMILIES = ['admissibility_boundary', 'cross_contract_alignment'];

    /**
     * @param  array<string,mixed>  $overrides
     * @return array<string,mixed>
     */
    public static function validate(string $root, bool $bound = false, array $overrides = []): array
    {
        $spec = 'MarketDataOperationsProofSpec';
        $rows = isset($overrides['required']) ? $overrides['required'] : MarketDataOperationsTraceabilitySpec::required($root);
        $entries = isset($overrides['entries']) ? $overrides['entries'] : $spec::entries($root);
        $familyDefs = isset($overrides['families']) ? $overrides['families'] : $spec::families();
        $map = isset($overrides['rule_families']) ? $overrides['rule_families'] : $spec::RULE_FAMILIES;
        $expectedCounts = isset($overrides['family_counts']) ? $overrides['family_counts'] : $spec::FAMILY_EXPECTED_COUNTS;

        $errors = [];
        $byRule = [];
        $used = [];
        $unproven = [];

        foreach ($rows as $row) {
            $byRule[$row['rule_id']] = $row;
            $evidence = trim((string) (isset($row['current_evidence_ids']) ? $row['current_evidence_ids'] : ''));
            if ($bound) {
                if ((isset($row['coverage_status']) ? $row['coverage_status'] : '') !== 'SATISFIED'
                    || preg_match(self::EVIDENCE_PATTERN, $evidence) !== 1) {
                    $errors[] = 'BOUND_STATE_INVALID:'.$row['rule_id'];
                }
            } elseif ((isset($row['coverage_status']) ? $row['coverage_status'] : '') !== 'NOT_ASSESSED' || $evidence !== '') {
                $errors[] = 'PREMATURE_BINDING:'.$row['rule_id'];
            }
            if (in_array(isset($row['applicability']) ? $row['applicability'] : '', ['MANDATORY_OR_CONDITIONAL', 'CONDITIONAL_PENDING'], true)) {
                $errors[] = 'TRANSITIONAL_OR_PENDING:'.$row['rule_id'];
            }
        }

        if (count($rows) !== $spec::EXPECTED_DENOMINATOR) {
            $errors[] = 'DENOMINATOR_MISMATCH:'.count($rows);
        }
        if (count($entries) !== $spec::EXPECTED_DENOMINATOR) {
            $errors[] = 'PROOF_MAP_COUNT_MISMATCH:'.count($entries);
        }

        // -- independent of the map: what the matrix carries versus what was reviewed.
        foreach ($byRule as $rid => $row) {
            if (! isset($map[$rid])) {
                $errors[] = 'RULE_NOT_IN_REVIEWED_MAP:'.$rid;
            }
        }
        foreach ($map as $rid => $family) {
            if (! isset($byRule[$rid])) {
                $errors[] = 'REVIEWED_MAP_ROW_NOT_REQUIRED:'.$rid;
            }
        }

        $actualCounts = [];
        foreach ($map as $family) {
            $actualCounts[$family] = (isset($actualCounts[$family]) ? $actualCounts[$family] : 0) + 1;
        }
        foreach ($expectedCounts as $family => $expected) {
            $actual = isset($actualCounts[$family]) ? $actualCounts[$family] : 0;
            if ($actual !== $expected) {
                $errors[] = 'FAMILY_COUNT_MISMATCH:'.$family.':expected='.$expected.',actual='.$actual;
            }
        }
        foreach ($actualCounts as $family => $actual) {
            if (! isset($expectedCounts[$family])) {
                $errors[] = 'FAMILY_COUNT_UNDECLARED:'.$family;
            }
        }

        $seen = [];
        foreach ($entries as $entry) {
            $rid = isset($entry['rule_id']) ? $entry['rule_id'] : '';
            $family = isset($entry['family']) ? $entry['family'] : '';
            if (isset($seen[$rid])) {
                $errors[] = 'DUPLICATE_ENTRY:'.$rid;

                continue;
            }
            $seen[$rid] = 1;
            if (! isset($byRule[$rid])) {
                $errors[] = 'ORPHAN_ENTRY:'.$rid;

                continue;
            }
            if (isset($map[$rid]) && $family !== $map[$rid]) {
                $errors[] = 'WRONG_FAMILY:'.$rid;
            }
            if (! isset($familyDefs[$family])) {
                $errors[] = 'MISSING_FAMILY:'.$family;

                continue;
            }
            $used[$family] = 1;
        }

        foreach ($byRule as $rid => $row) {
            if (! isset($seen[$rid])) {
                $errors[] = 'UNMAPPED:'.$rid;
            }
        }

        foreach ($familyDefs as $name => $definition) {
            if (! isset($used[$name])) {
                $errors[] = 'UNUSED_FAMILY:'.$name;
            }
            if (strpos((string) (isset($definition['owner']) ? $definition['owner'] : ''), 'MD-B19:') !== 0) {
                $errors[] = 'WRONG_OWNER:'.$name;
            }
            foreach (isset($definition['implementation']) ? $definition['implementation'] : [] as $path) {
                if (! file_exists($root.'/'.$path)) {
                    $errors[] = 'MISSING_IMPL:'.$path;
                }
            }

            $assigned = true;
            foreach (['positive', 'negative'] as $kind) {
                $ref = isset($definition[$kind]) ? $definition[$kind] : [];
                if (count($ref) !== 2) {
                    $assigned = false;

                    continue;
                }
                $file = $ref[0];
                $method = $ref[1];
                $src = is_file($root.'/'.$file) ? file_get_contents($root.'/'.$file) : '';
                if (! $src || strpos($src, 'function '.$method.'(') === false) {
                    $errors[] = 'MISSING_'.strtoupper($kind).'_PROOF:'.$name;

                    continue;
                }
                $isCorpusFamily = in_array($name, self::CORPUS_PROOF_FAMILIES, true);
                $readsSource = strpos($src, 'file_get_contents(') !== false
                    && strpos($src, 'RecursiveDirectoryIterator') === false;
                if (! $isCorpusFamily && $readsSource && strpos($file, 'StaticGuard') !== false) {
                    $errors[] = 'BEHAVIOURAL_FAMILY_PROVEN_BY_TEXT:'.$name.':'.basename($file);
                }
            }
            if (! $assigned) {
                $unproven[] = $name;
                $errors[] = 'GUARD_NOT_ASSIGNED:'.$name;
            }
        }

        sort($unproven);
        $unprovenPredicates = 0;
        foreach ($unproven as $name) {
            $unprovenPredicates += isset($actualCounts[$name]) ? $actualCounts[$name] : 0;
        }

        /*
         * Per-predicate proof attribution.
         *
         * `F-MD-B19-A001-002` measured what a family-level assignment hides: `MD-B18` closed at
         * 121/121 with eleven positive guards, one per family, so 26 distinct as-known obligations
         * were all recorded as proven by a single guard that executes none of them as fixtures. Of
         * 85 predicates re-checked against the body of the guard bound to them, 38 were established
         * by a different obligation entirely and 20 by a proper subset.
         *
         * Family membership is a review grouping. It is not proof that the family guard establishes
         * a given member. So each denominator row must carry its own reviewed basis saying how the
         * guard establishes that predicate. One guard may legitimately serve many predicates -- a
         * guard that parses the contract field list and asserts every named field really does
         * establish each field predicate -- but that has to be affirmed per predicate rather than
         * inherited from the family.
         *
         * The basis map is deliberately allowed to be incomplete while the stage is open: rows
         * without one are counted and named, the same way an unguarded family is.
         */
        $basis = [];
        if (method_exists($spec, 'proofBasis')) {
            $basis = $spec::proofBasis();
        } elseif (defined($spec.'::RULE_PROOF_BASIS')) {
            $basis = constant($spec.'::RULE_PROOF_BASIS');
        }
        $withoutBasis = [];
        foreach ($rows as $row) {
            $id = (string) $row['rule_id'];
            if (! isset($basis[$id]) || trim((string) $basis[$id]) === '') {
                $withoutBasis[] = $id;
            }
        }
        $foreignBasis = array_values(array_diff(array_keys($basis), array_map(static function ($r) {
            return (string) $r['rule_id'];
        }, $rows)));
        if ($foreignBasis !== []) {
            $errors[] = 'PROOF_BASIS_FOR_FOREIGN_ROW:'.implode(',', array_slice($foreignBasis, 0, 5));
        }
        if ($withoutBasis !== []) {
            $errors[] = 'PREDICATES_WITHOUT_A_REVIEWED_PROOF_BASIS:'.count($withoutBasis);
        }

        return [
            'gate' => 'MarketDataOperationsProofGate',
            'stage_id' => $spec::STAGE,
            'attempt_id' => $spec::ATTEMPT,
            'status' => $errors ? 'FAIL' : 'PASS',
            'denominator' => count($rows),
            'proof_map_count' => count($entries),
            'reviewed_map_size' => count($map),
            'families_declared' => count($familyDefs),
            'families_used' => count($used),
            'families_without_a_guard' => count($unproven),
            'predicates_without_a_guard' => $unprovenPredicates,
            'predicates_with_a_reviewed_basis' => count($rows) - count($withoutBasis),
            'predicates_without_a_reviewed_basis' => count($withoutBasis),
            'predicates_without_a_reviewed_basis_sample' => array_slice($withoutBasis, 0, 10),
            'unproven_families' => $unproven,
            'corpus_proof_families' => self::CORPUS_PROOF_FAMILIES,
            'bound' => $bound,
            'errors' => array_values(array_unique($errors)),
        ];
    }
}

if (realpath(isset($_SERVER['SCRIPT_FILENAME']) ? $_SERVER['SCRIPT_FILENAME'] : '') === __FILE__) {
    $result = MarketDataOperationsProofGate::validate(dirname(__DIR__, 5), in_array('--bound', $argv, true));
    $result['generated_at'] = date(DATE_ATOM);
    echo json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES).PHP_EOL;
    exit($result['status'] === 'PASS' ? 0 : 1);
}
