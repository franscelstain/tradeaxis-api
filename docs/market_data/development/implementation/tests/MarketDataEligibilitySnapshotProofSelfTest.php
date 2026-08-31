<?php

require_once __DIR__.'/MarketDataEligibilitySnapshotProofGate.php';

/**
 * Falsifiability control for the `MD-B16` proof gate.
 *
 * A gate that passes is worth nothing until it has been shown to fail. The control entry runs
 * first: if the unmutated matrix does not pass, every verdict after it is meaningless, so it is
 * reported as its own check rather than assumed.
 */
$root = dirname(__DIR__, 5);
$spec = 'MarketDataEligibilitySnapshotProofSpec';
$traceability = 'MarketDataEligibilitySnapshotTraceabilitySpec';

$entries = $spec::entries($root);
$families = $spec::families();
$mandatory = $traceability::mandatory($root);

$bound = $mandatory !== [];
foreach ($mandatory as $row) {
    if ((isset($row['coverage_status']) ? $row['coverage_status'] : '') !== 'SATISFIED'
        || preg_match(MarketDataEligibilitySnapshotProofGate::EVIDENCE_PATTERN,
            trim((string) (isset($row['current_evidence_ids']) ? $row['current_evidence_ids'] : ''))) !== 1) {
        $bound = false;
        break;
    }
}

$fails = static function (array $overrides) use ($root, $bound) {
    return MarketDataEligibilitySnapshotProofGate::validate($root, $bound, $overrides)['status'] === 'FAIL';
};

$checks = [];
$checks[$bound ? 'control_bound' : 'control_prebinding'] =
    MarketDataEligibilitySnapshotProofGate::validate($root, $bound)['status'] === 'PASS';

$dropped = $entries;
array_pop($dropped);
$checks['a_dropped_predicate_is_caught'] = $fails(['entries' => $dropped]);

$duplicated = $entries;
$duplicated[] = $entries[0];
$checks['a_duplicated_predicate_is_caught'] = $fails(['entries' => $duplicated]);

$misfiled = $entries;
$misfiled[0]['family'] = $misfiled[0]['family'] === 'decision_fields' ? 'eligibility_meaning' : 'decision_fields';
$checks['a_predicate_filed_under_the_wrong_family_is_caught'] = $fails(['entries' => $misfiled]);

$withoutFamily = $families;
unset($withoutFamily['eligibility_meaning']);
$checks['a_missing_family_is_caught'] = $fails(['families' => $withoutFamily]);

$renamedGuard = $families;
$renamedGuard['liquidity_never_blocks']['positive'][1] = 'test_a_method_nobody_ever_wrote';
$checks['a_guard_method_that_does_not_exist_is_caught'] = $fails(['families' => $renamedGuard]);

$missingSurface = $families;
$missingSurface['decision_fields']['implementation'][] = 'app/Application/MarketData/Services/NotAService.php';
$checks['a_missing_implementation_surface_is_caught'] = $fails(['families' => $missingSurface]);

$degenerate = $families;
$degenerate['no_overloaded_reason_code']['negative'][1] = $degenerate['no_overloaded_reason_code']['positive'][1];
$checks['one_guard_counted_twice_is_caught'] = $fails(['families' => $degenerate]);

$foreignOwner = $families;
$foreignOwner['publication_readability']['owner'] = 'MD-B11:calendar-identity';
$checks['a_family_owned_by_another_stage_is_caught'] = $fails(['families' => $foreignOwner]);

$wrongLifecycle = $mandatory;
if ($wrongLifecycle !== []) {
    $wrongLifecycle[0]['coverage_status'] = 'SATISFIED';
    $wrongLifecycle[0]['current_evidence_ids'] = '';
}
$checks['satisfied_without_evidence_is_caught'] = $fails(['mandatory' => $wrongLifecycle]);

$shortDenominator = $mandatory;
array_pop($shortDenominator);
$checks['a_shrunken_denominator_is_caught'] = $fails(['mandatory' => $shortDenominator]);

$failed = array_keys(array_filter($checks, static function ($passed) {
    return ! $passed;
}));

$result = [
    'gate' => 'MarketDataEligibilitySnapshotProofSelfTest',
    'stage_id' => $spec::STAGE,
    'attempt_id' => $spec::ATTEMPT,
    'mode' => $bound ? 'BOUND_CLOSURE' : 'PRE_RUNTIME',
    'status' => $failed === [] ? 'PASS' : 'FAIL',
    'total' => count($checks),
    'checks' => $checks,
    'failed_checks' => $failed,
    'generated_at' => date(DATE_ATOM),
];

echo json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES).PHP_EOL;
exit($result['status'] === 'PASS' ? 0 : 1);
