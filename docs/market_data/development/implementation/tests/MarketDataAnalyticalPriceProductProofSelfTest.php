<?php
require_once __DIR__.'/MarketDataAnalyticalPriceProductProofGate.php';
$root = dirname(__DIR__, 5);
$entries = MarketDataAnalyticalPriceProductProofSpec::entries($root);
$families = MarketDataAnalyticalPriceProductProofSpec::families();
$mandatory = MarketDataAnalyticalPriceProductTraceabilitySpec::mandatory($root);
$bound = $mandatory !== [];
foreach ($mandatory as $r) {
    if (($r['coverage_status'] ?? '') !== 'SATISFIED'
        || ! preg_match('/^E-MD-B12-A\d{3}-\d{3}$/', trim((string) ($r['current_evidence_ids'] ?? '')))) {
        $bound = false;
        break;
    }
}
$checks = [];
$checks[$bound ? 'control_bound' : 'control_prebinding'] = MarketDataAnalyticalPriceProductProofGate::validate($root, $bound)['status'] === 'PASS';
function b12fails($root, $overrides, $bound)
{
    return MarketDataAnalyticalPriceProductProofGate::validate($root, $bound, $overrides)['status'] === 'FAIL';
}
$m = $entries;
array_pop($m);
$checks['missing_mapping'] = b12fails($root, ['entries' => $m], $bound);
$m = $entries;
$m[] = $m[0];
$checks['duplicate_mapping'] = b12fails($root, ['entries' => $m], $bound);
$m = $entries;
$m[0]['family'] = 'determinism';
$checks['wrong_family'] = b12fails($root, ['entries' => $m], $bound);
$f = $families;
unset($f['factor_lineage']);
$checks['missing_family'] = b12fails($root, ['families' => $f], $bound);
$r = $mandatory;
if ($r !== []) {
    $r[0]['coverage_status'] = 'SATISFIED';
    $r[0]['current_evidence_ids'] = '';
}
$checks['satisfied_without_evidence'] = b12fails($root, ['mandatory' => $r], $bound);
$failed = array_keys(array_filter($checks, static function ($v) {
    return ! $v;
}));
$out = [
    'status' => $failed === [] ? 'PASS' : 'FAIL',
    'mode' => $bound ? 'BOUND_CLOSURE' : 'PRE_RUNTIME',
    'total' => count($checks),
    'checks' => $checks,
    'failed_checks' => $failed,
];
echo json_encode($out, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES).PHP_EOL;
exit($out['status'] === 'PASS' ? 0 : 1);
