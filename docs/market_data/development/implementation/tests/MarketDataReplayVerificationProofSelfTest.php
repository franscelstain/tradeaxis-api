<?php
require_once __DIR__.'/MarketDataReplayVerificationProofGate.php';

/**
 * Falsifiability control for the `MD-B18` proof gate.
 *
 * A gate that passes is worth nothing until it has been shown to fail. The first six mutations were
 * already here and still apply. The five after them cover the assertions added when
 * `F-MD-B18-A001-001` found that the map was built by keyword accident and proven by source-text
 * grep: a reviewed map that has lost a rule, a map carrying a rule the matrix does not require, a
 * silent re-bucketing that keeps the total at 121, an undeclared family, and a behavioural family
 * re-pointed at the retired static guard.
 *
 * Every mutation is verified as applied before the gate runs; a mutation that never lands reports
 * as a guard that did not react.
 */
$root = dirname(__DIR__, 5);
$spec = 'MarketDataReplayVerificationProofSpec';
$base = MarketDataReplayVerificationTraceabilitySpec::required($root);
$entries = $spec::entries($root);
$families = $spec::families();
$map = $spec::RULE_FAMILIES;
$counts = $spec::FAMILY_EXPECTED_COUNTS;

/**
 * Which mode the matrix is actually in.
 *
 * The baseline runs the gate against the live matrix. Once the 121 predicates were bound, running it
 * in pre-binding mode made the control red and every mutation after it meaningless -- the same
 * silent-fallback shape found in the MD-B08, MD-B11, MD-B12 and MD-B17 self-tests. The mode is now
 * detected, an explicit flag is honoured, and a requested mode that contradicts the matrix is fatal.
 */
$modeErrors = [];
$bound = $base !== [];
foreach ($base as $row) {
    $ev = trim((string) (isset($row['current_evidence_ids']) ? $row['current_evidence_ids'] : ''));
    if ((isset($row['coverage_status']) ? $row['coverage_status'] : '') !== 'SATISFIED'
        || preg_match(MarketDataReplayVerificationProofGate::EVIDENCE_PATTERN, $ev) !== 1) {
        $bound = false;
        break;
    }
}

$requested = null;
if (in_array('--bound', $argv, true)) {
    $requested = true;
}
if (in_array('--pre-binding', $argv, true)) {
    if ($requested === true) {
        $modeErrors[] = 'CONTRADICTORY_MODE_FLAGS';
    }
    $requested = false;
}
if ($requested !== null && $requested !== $bound && $modeErrors === []) {
    $modeErrors[] = 'MODE_CONTRADICTS_MATRIX:requested='.($requested ? 'BOUND' : 'PRE_RUNTIME')
        .',actual='.($bound ? 'BOUND' : 'PRE_RUNTIME');
}

$tests = [];
$run = function ($name, array $overrides, $expectPass, $expectError = null) use (&$tests, $root, $bound) {
    $r = MarketDataReplayVerificationProofGate::validate($root, $bound, $overrides);
    $ok = ($r['status'] === 'PASS') === $expectPass;
    $matched = true;
    if ($expectError !== null) {
        $matched = false;
        foreach ($r['errors'] as $error) {
            if (strpos($error, $expectError) === 0) {
                $matched = true;
                break;
            }
        }
    }
    $tests[] = [
        'name' => $name,
        'passed' => $ok && $matched,
        'observed' => $r['status'],
        'expected_error' => $expectError,
        'reason_recognised' => $matched,
        'errors' => $r['errors'],
    ];
};

$all = ['required' => $base, 'entries' => $entries, 'families' => $families,
    'rule_families' => $map, 'family_counts' => $counts];

$run('baseline', $all, true);

$x = $all; array_pop($x['required']);
$run('denominator_missing', $x, false, 'DENOMINATOR_MISMATCH');

$x = $all;
if ($bound) {
    $x['required'][0]['current_evidence_ids'] = '';
    $run('satisfied_without_evidence', $x, false, 'BOUND_STATE_INVALID');
} else {
    $x['required'][0]['coverage_status'] = 'SATISFIED';
    $x['required'][0]['current_evidence_ids'] = 'E-MD-B18-A001-999';
    $run('premature_satisfied', $x, false, 'PREMATURE_BINDING');
}

$x = $all; $x['entries'][0]['family'] = 'not_a_family';
$run('wrong_family', $x, false, 'WRONG_FAMILY');

$x = $all; $x['entries'][] = $x['entries'][0];
$run('duplicate_entry', $x, false, 'DUPLICATE_ENTRY');

$x = $all; unset($x['families'][array_key_first($x['families'])]);
$run('missing_family', $x, false, 'MISSING_FAMILY');

// -- assertions added after F-MD-B18-A001-001.

$x = $all; $dropped = array_key_first($x['rule_families']); unset($x['rule_families'][$dropped]);
$run('reviewed_map_lost_a_rule', $x, false, 'RULE_NOT_IN_REVIEWED_MAP');

$x = $all; $x['rule_families']['MD-S999-R9999'] = 'mode_admission';
$run('reviewed_map_carries_a_foreign_rule', $x, false, 'REVIEWED_MAP_ROW_NOT_REQUIRED');

// A predicate moved between families. The total stays 121 and every earlier count check passes.
$x = $all;
$moved = null;
foreach ($x['rule_families'] as $rid => $family) {
    if ($family === 'as_known_isolation') { $moved = $rid; break; }
}
$x['rule_families'][$moved] = 'mode_admission';
$run('silent_rebucketing_keeps_the_total', $x, false, 'FAMILY_COUNT_MISMATCH');

$x = $all; $x['family_counts'] = $counts; unset($x['family_counts']['temporal_identity']);
$run('family_without_a_declared_count', $x, false, 'FAMILY_COUNT_UNDECLARED');

// The defect the finding recorded: a behavioural family proven by reading source text.
$x = $all;
$x['families']['as_known_isolation']['positive'] = [
    'tests/Unit/MarketData/B18ReplayContractStaticGuardTest.php',
    'test_as_known_has_a_separate_cutoff_bound_execution_path',
];
$run('behavioural_family_repointed_at_the_static_guard', $x, false, 'BEHAVIOURAL_FAMILY_PROVEN_BY_TEXT');

$failed = array_values(array_filter($tests, function ($t) { return ! $t['passed']; }));
$result = [
    'gate' => 'MarketDataReplayVerificationProofSelfTest',
    'stage_id' => $spec::STAGE,
    'attempt_id' => $spec::ATTEMPT,
    'mode' => $bound ? 'BOUND_CLOSURE' : 'PRE_RUNTIME',
    'mode_errors' => $modeErrors,
    'status' => ($failed === [] && $modeErrors === []) ? 'PASS' : 'FAIL',
    'total' => count($tests),
    'failed' => array_column($failed, 'name'),
    'tests' => $tests,
    'generated_at' => date(DATE_ATOM),
];
echo json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES).PHP_EOL;
exit($result['status'] === 'PASS' ? 0 : 1);
