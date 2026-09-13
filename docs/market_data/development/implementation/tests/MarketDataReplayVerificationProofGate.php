<?php
require_once __DIR__.'/MarketDataReplayVerificationProofSpec.php';

/**
 * `MD-B18` proof gate.
 *
 * The earlier version validated a predicate's family by calling the same heuristic that had
 * assigned it, so `WRONG_FAMILY` could never fire. That check is retained for a hand-edited entry
 * list, but the load-bearing assertions are now independent of the map: the reviewed
 * `RULE_FAMILIES` key set is compared against the rule ids the matrix actually carries, and each
 * family's size is compared against a separately declared expected count, so a silent re-bucketing
 * that keeps the total at 121 no longer passes.
 *
 * Each family also declares how it proves its predicates. A behavioural predicate proven by reading
 * source text is the defect recorded in `F-MD-B18-A001-001`, so re-pointing a behavioural family at
 * a text assertion now requires flipping a declared `proof_kind` that this gate reports.
 */
final class MarketDataReplayVerificationProofGate
{
    // A002 evidence only. Binding to `E-MD-B18-A001-001` would cite the withdrawn closure.
    public const EVIDENCE_PATTERN = '/^E-MD-B18-A002-\d{3}$/';

    /** Families whose predicates are about what may be claimed, where a corpus assertion is right. */
    public const CORPUS_PROOF_FAMILIES = ['admissibility_boundary'];

    /**
     * @param  array<string,mixed>  $overrides
     * @return array<string,mixed>
     */
    public static function validate(string $root, bool $bound = false, array $overrides = []): array
    {
        $spec = 'MarketDataReplayVerificationProofSpec';
        $rows = isset($overrides['required']) ? $overrides['required'] : MarketDataReplayVerificationTraceabilitySpec::required($root);
        $entries = isset($overrides['entries']) ? $overrides['entries'] : $spec::entries($root);
        $families = isset($overrides['families']) ? $overrides['families'] : $spec::families();
        $map = isset($overrides['rule_families']) ? $overrides['rule_families'] : $spec::RULE_FAMILIES;
        $expectedCounts = isset($overrides['family_counts']) ? $overrides['family_counts'] : $spec::FAMILY_EXPECTED_COUNTS;

        $errors = [];
        $by = [];
        $used = [];

        foreach ($rows as $row) {
            $by[$row['rule_id']] = $row;
            $ev = trim((string) (isset($row['current_evidence_ids']) ? $row['current_evidence_ids'] : ''));
            if ($bound) {
                if ((isset($row['coverage_status']) ? $row['coverage_status'] : '') !== 'SATISFIED'
                    || preg_match(self::EVIDENCE_PATTERN, $ev) !== 1) {
                    $errors[] = 'BOUND_STATE_INVALID:'.$row['rule_id'];
                }
            } elseif ((isset($row['coverage_status']) ? $row['coverage_status'] : '') !== 'NOT_ASSESSED' || $ev !== '') {
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

        // -- Independent of the map: what the matrix carries versus what was reviewed.
        foreach ($by as $rid => $row) {
            if (! isset($map[$rid])) {
                $errors[] = 'RULE_NOT_IN_REVIEWED_MAP:'.$rid;
            }
        }
        foreach ($map as $rid => $family) {
            if (! isset($by[$rid])) {
                $errors[] = 'REVIEWED_MAP_ROW_NOT_REQUIRED:'.$rid;
            }
        }

        // -- A re-bucketing that keeps the total at 121 changes these.
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
            if (! isset($by[$rid])) {
                $errors[] = 'ORPHAN_ENTRY:'.$rid;

                continue;
            }
            if (isset($map[$rid]) && $family !== $map[$rid]) {
                $errors[] = 'WRONG_FAMILY:'.$rid;
            }
            if (! isset($families[$family])) {
                $errors[] = 'MISSING_FAMILY:'.$family;

                continue;
            }
            $used[$family] = 1;
            $f = $families[$family];
            if (strpos((string) (isset($f['owner']) ? $f['owner'] : ''), 'MD-B18:') !== 0) {
                $errors[] = 'WRONG_OWNER:'.$family;
            }
            foreach (isset($f['implementation']) ? $f['implementation'] : [] as $path) {
                if (! is_file($root.'/'.$path)) {
                    $errors[] = 'MISSING_IMPL:'.$path;
                }
            }
            foreach (['positive', 'negative'] as $kind) {
                $ref = isset($f[$kind]) ? $f[$kind] : [];
                $file = isset($ref[0]) ? $ref[0] : '';
                $method = isset($ref[1]) ? $ref[1] : '';
                $src = is_file($root.'/'.$file) ? file_get_contents($root.'/'.$file) : '';
                if (! $src || strpos($src, 'function '.$method.'(') === false) {
                    $errors[] = 'MISSING_'.strtoupper($kind).'_PROOF:'.$family;

                    continue;
                }
                // A behavioural predicate proven by reading source text is F-MD-B18-A001-001.
                $isCorpusFamily = in_array($family, self::CORPUS_PROOF_FAMILIES, true);
                $guardReadsSource = strpos($src, 'file_get_contents(') !== false
                    && strpos($src, 'RecursiveDirectoryIterator') === false;
                if (! $isCorpusFamily && $guardReadsSource && strpos($file, 'StaticGuard') !== false) {
                    $errors[] = 'BEHAVIOURAL_FAMILY_PROVEN_BY_TEXT:'.$family.':'.basename($file);
                }
            }
        }

        foreach ($by as $rid => $row) {
            if (! isset($seen[$rid])) {
                $errors[] = 'UNMAPPED:'.$rid;
            }
        }
        foreach ($families as $name => $f) {
            if (! isset($used[$name])) {
                $errors[] = 'UNUSED_FAMILY:'.$name;
            }
        }

        return [
            'gate' => 'MarketDataReplayVerificationProofGate',
            'stage_id' => $spec::STAGE,
            'attempt_id' => $spec::ATTEMPT,
            'status' => $errors ? 'FAIL' : 'PASS',
            'denominator' => count($rows),
            'proof_map_count' => count($entries),
            'proof_families_used' => count($used),
            'reviewed_map_size' => count($map),
            'family_counts' => $actualCounts,
            'corpus_proof_families' => self::CORPUS_PROOF_FAMILIES,
            'bound' => $bound,
            'runtime_pending' => $bound ? 0 : count($rows),
            'errors' => array_values(array_unique($errors)),
        ];
    }
}

if (realpath(isset($_SERVER['SCRIPT_FILENAME']) ? $_SERVER['SCRIPT_FILENAME'] : '') === __FILE__) {
    $r = MarketDataReplayVerificationProofGate::validate(dirname(__DIR__, 5), in_array('--bound', $argv, true));
    $r['generated_at'] = date(DATE_ATOM);
    echo json_encode($r, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES).PHP_EOL;
    exit($r['status'] === 'PASS' ? 0 : 1);
}
