<?php

require_once __DIR__.'/MarketDataLiquidityMetricProofGate.php';
require_once __DIR__.'/MarketDataLiquidityMetricTraceabilityGate.php';

/**
 * Mutation self-test for the MD-B13 proof and traceability gates.
 *
 * The control entry is first and is not decoration. A control that fails makes every verdict after
 * it meaningless — a gate reporting FAIL on a mutation it would have failed anyway has proven
 * nothing about the mutation. Read `control_*` before reading anything else.
 */
$root = dirname(__DIR__, 5);

$entries = MarketDataLiquidityMetricProofSpec::entries($root);
$families = MarketDataLiquidityMetricProofSpec::families();
$mandatory = MarketDataLiquidityMetricTraceabilitySpec::mandatory($root);

$bound = $mandatory !== [];
foreach ($mandatory as $row) {
    if (($row['coverage_status'] ?? '') !== 'SATISFIED'
        || ! preg_match('/^E-MD-B13-A001-\d{3}$/', trim((string) ($row['current_evidence_ids'] ?? '')))) {
        $bound = false;
        break;
    }
}

$checks = [];
$checks[$bound ? 'control_bound' : 'control_prebinding'] =
    MarketDataLiquidityMetricProofGate::validate($root, $bound)['status'] === 'PASS';
$checks['control_traceability'] =
    MarketDataLiquidityMetricTraceabilityGate::validate($root, $bound)['status'] === 'PASS';

$proofFails = static function (array $overrides) use ($root, $bound): bool {
    return MarketDataLiquidityMetricProofGate::validate($root, $bound, $overrides)['status'] === 'FAIL';
};

$mutated = $entries;
array_pop($mutated);
$checks['missing_mapping'] = $proofFails(['entries' => $mutated]);

$mutated = $entries;
$mutated[] = $mutated[0];
$checks['duplicate_mapping'] = $proofFails(['entries' => $mutated]);

/*
 * The wrong family must be a family this rule genuinely does not belong to, and it must not be the
 * family the rule already has. Picking a name at random would test the string comparison; picking a
 * real sibling family tests the mapping.
 */
$mutated = $entries;
$mutated[0]['family'] = $mutated[0]['family'] === 'lot_size_boundary' ? 'metric_domain_boundary' : 'lot_size_boundary';
$checks['wrong_family'] = $proofFails(['entries' => $mutated]);

$mutatedFamilies = $families;
unset($mutatedFamilies['persisted_proxy_labelling']);
$checks['missing_family'] = $proofFails(['families' => $mutatedFamilies]);

/*
 * A family whose negative proof method does not exist must fail. This is the mutation that catches
 * a renamed or deleted fail-closed test, which is the one a green suite cannot see: the positive
 * test keeps passing and the prohibition stops being proven.
 */
$mutatedFamilies = $families;
$mutatedFamilies['persisted_proxy_labelling']['negative'] = [
    'tests/Unit/MarketData/UnlabelledLiquidityMetricPublicationTest.php',
    'test_method_that_does_not_exist',
];
$checks['absent_negative_proof'] = $proofFails(['families' => $mutatedFamilies]);

$mutatedFamilies = $families;
$mutatedFamilies['lot_size_boundary']['implementation'] = ['app/Application/MarketData/Services/DoesNotExist.php'];
$checks['absent_implementation'] = $proofFails(['families' => $mutatedFamilies]);

$mutatedFamilies = $families;
$mutatedFamilies['lot_size_boundary']['owner'] = 'MD-B12:borrowed-owner';
$checks['foreign_family_owner'] = $proofFails(['families' => $mutatedFamilies]);

$mutatedRows = $mandatory;
if ($mutatedRows !== []) {
    $mutatedRows[0]['coverage_status'] = 'SATISFIED';
    $mutatedRows[0]['current_evidence_ids'] = '';
}
$checks['satisfied_without_evidence'] = $proofFails(['mandatory' => $mutatedRows]);

$mutatedRows = $mandatory;
if ($mutatedRows !== []) {
    $mutatedRows[0]['coverage_status'] = 'SATISFIED';
    $mutatedRows[0]['current_evidence_ids'] = 'E-MD-B12-A001-001';
}
$checks['bound_to_foreign_stage_evidence'] = $proofFails(['mandatory' => $mutatedRows]);

$mutatedRows = $mandatory;
array_pop($mutatedRows);
$checks['denominator_shrunk'] = $proofFails(['mandatory' => $mutatedRows]);

$failed = array_keys(array_filter($checks, static function ($value) {
    return ! $value;
}));
$controlFailed = array_values(array_filter($failed, static function ($name) {
    return strpos($name, 'control_') === 0;
}));

$out = [
    'status' => $failed === [] ? 'PASS' : 'FAIL',
    'mode' => $bound ? 'BOUND_CLOSURE' : 'PRE_RUNTIME',
    'control_failed' => $controlFailed,
    'total' => count($checks),
    'checks' => $checks,
    'failed_checks' => $failed,
    'note' => $controlFailed === []
        ? 'Controls passed, so each FAIL verdict below is attributable to its mutation.'
        : 'A control failed. Every mutation verdict in this run is unattributable and must not be read as proof.',
];

echo json_encode($out, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES).PHP_EOL;
exit($out['status'] === 'PASS' ? 0 : 1);
