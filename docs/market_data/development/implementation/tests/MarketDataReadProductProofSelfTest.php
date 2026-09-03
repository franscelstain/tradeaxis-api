<?php

require_once __DIR__.'/MarketDataReadProductProofGate.php';

/**
 * Falsifiability control for the `MD-B17` proof gate.
 *
 * A gate that passes is worth nothing until it has been shown to fail. The control entry runs
 * first: if the unmutated matrix does not pass, every verdict after it is meaningless, so it is
 * reported as its own check rather than assumed.
 */
$root = dirname(__DIR__, 5);
$spec = 'MarketDataReadProductProofSpec';
$traceability = 'MarketDataReadProductTraceabilitySpec';

$entries = $spec::entries($root);
$families = $spec::families();
$mandatory = $traceability::mandatory($root);

/** Detect the live matrix mode and reject a caller flag that contradicts it. */
$bound = $mandatory !== [];
foreach ($mandatory as $row) {
    if ((isset($row['coverage_status']) ? $row['coverage_status'] : '') !== 'SATISFIED'
        || preg_match(MarketDataReadProductProofGate::EVIDENCE_PATTERN,
            trim((string) (isset($row['current_evidence_ids']) ? $row['current_evidence_ids'] : ''))) !== 1) {
        $bound = false;
        break;
    }
}

$modeErrors = [];
$requested = null;
if (in_array('--bound', $argv, true)) {
    $requested = true;
}
if (in_array('--pre-binding', $argv, true)) {
    $requested = $requested === true ? null : false;
    if ($requested === null) {
        $modeErrors[] = 'CONTRADICTORY_MODE_FLAGS';
    }
}
if ($requested !== null && $requested !== $bound) {
    $modeErrors[] = 'MODE_CONTRADICTS_MATRIX:requested='.($requested ? 'BOUND' : 'PRE_RUNTIME')
        .',actual='.($bound ? 'BOUND' : 'PRE_RUNTIME');
}

$fails = static function (array $overrides) use ($root, $bound) {
    return MarketDataReadProductProofGate::validate($root, $bound, $overrides)['status'] === 'FAIL';
};

$checks = [];
$checks[$bound ? 'control_bound' : 'control_prebinding'] =
    MarketDataReadProductProofGate::validate($root, $bound)['status'] === 'PASS';

$dropped = $entries;
array_pop($dropped);
$checks['a_dropped_predicate_is_caught'] = $fails(['entries' => $dropped]);

$duplicated = $entries;
$duplicated[] = $entries[0];
$checks['a_duplicated_predicate_is_caught'] = $fails(['entries' => $duplicated]);

$misfiled = $entries;
$misfiled[0]['family'] = $misfiled[0]['family'] === 'read_model_grain_identity' ? 'readiness_states' : 'read_model_grain_identity';
$checks['a_predicate_filed_under_the_wrong_family_is_caught'] = $fails(['entries' => $misfiled]);

$withoutFamily = $families;
unset($withoutFamily['readiness_states']);
$checks['a_missing_family_is_caught'] = $fails(['families' => $withoutFamily]);

$renamedGuard = $families;
$renamedGuard['forbidden_shortcuts']['positive'][1] = 'test_a_method_nobody_ever_wrote';
$checks['a_guard_method_that_does_not_exist_is_caught'] = $fails(['families' => $renamedGuard]);

$missingSurface = $families;
$missingSurface['read_model_grain_identity']['implementation'][] = 'app/Application/MarketData/Services/NotAService.php';
$checks['a_missing_implementation_surface_is_caught'] = $fails(['families' => $missingSurface]);

$degenerate = $families;
$degenerate['read_surface_atomicity']['negative'][1] = $degenerate['read_surface_atomicity']['positive'][1];
$checks['one_guard_counted_twice_is_caught'] = $fails(['families' => $degenerate]);

$foreignOwner = $families;
$foreignOwner['pointer_only_resolution']['owner'] = 'MD-B11:calendar-identity';
$checks['a_family_owned_by_another_stage_is_caught'] = $fails(['families' => $foreignOwner]);

$wrongLifecycle = $mandatory;
if ($wrongLifecycle !== []) {
    $wrongLifecycle[0]['coverage_status'] = 'SATISFIED';
    $wrongLifecycle[0]['current_evidence_ids'] = '';
}
$checks['satisfied_without_evidence_is_caught'] = $fails(['mandatory' => $wrongLifecycle]);

$shortDenominator = $mandatory;
if ($shortDenominator !== []) {
    array_pop($shortDenominator);
}
$checks['a_shrunken_denominator_is_caught'] = $fails(['mandatory' => $shortDenominator]);

$failed = array_keys(array_filter($checks, static function ($passed) {
    return ! $passed;
}));

$result = [
    'gate' => 'MarketDataReadProductProofSelfTest',
    'stage_id' => $spec::STAGE,
    'attempt_id' => $spec::ATTEMPT,
    'mode' => $bound ? 'BOUND_CLOSURE' : 'PRE_RUNTIME',
    'mode_errors' => $modeErrors,
    'status' => ($failed === [] && $modeErrors === []) ? 'PASS' : 'FAIL',
    'total' => count($checks),
    'checks' => $checks,
    'failed_checks' => $failed,
    'generated_at' => date(DATE_ATOM),
];

echo json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES).PHP_EOL;
exit($result['status'] === 'PASS' ? 0 : 1);
