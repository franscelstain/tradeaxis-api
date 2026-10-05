<?php
require_once __DIR__.'/MarketDataB10SuccessorBinding.php';
require_once __DIR__.'/MarketDataPublicationLifecycleProofGate.php';
require_once __DIR__.'/MarketDataPublicationLifecycleTraceabilityGate.php';

/**
 * Governed successor-attempt binder for `MD-B10` (F-MD-B10-A002-006). See MarketDataB10SuccessorBinding.
 *
 * Usage:
 *   php MarketDataB10SuccessorBinder.php --attempt=MD-B10-A002 --evidence-id=E-MD-B10-A002-NNN
 *       validate only (default): reports every diagnostic, writes nothing
 *   ... --apply
 *       promote exactly the reopened rules, atomically; refused unless validation has no diagnostic at all
 *
 * Test hooks (temporary copies only): --matrix= --evidence-dir= --relationships= --package-base=
 * --inject=after_temp|after_rename|after_readback (needs MD_B10_BINDER_TEST=1 and a non-canonical matrix).
 */
$root = dirname(__DIR__, 5);
$opts = ['attempt' => null, 'evidence-id' => null, 'matrix' => null, 'evidence-dir' => null, 'relationships' => null, 'package-base' => null, 'inject' => null];
foreach ($argv as $arg) {
    foreach (array_keys($opts) as $name) {
        if (strpos($arg, '--'.$name.'=') === 0) {
            $opts[$name] = substr($arg, strlen($name) + 3);
        }
    }
}
$apply = in_array('--apply', $argv, true);
if ($opts['attempt'] === null || $opts['evidence-id'] === null) {
    fwrite(STDERR, "Usage: php ".basename(__FILE__)." --attempt=MD-B10-A00N --evidence-id=E-MD-B10-A00N-NNN [--apply]\n");
    exit(2);
}

$o = array_filter([
    'matrix' => $opts['matrix'], 'evidence_dir' => $opts['evidence-dir'],
    'relationships' => $opts['relationships'], 'package_base' => $opts['package-base'],
], static function ($v) { return $v !== null; });
$paths = MarketDataB10SuccessorBinding::paths($root, $o);
$canonical = realpath($paths['matrix']) === realpath(MarketDataB10SuccessorBinding::canonicalMatrix($root));

// The B10 gates are the closure gates. The planned post-state must satisfy them before anything is written, and
// the written file must satisfy them again afterwards; otherwise the matrix is left (or restored) as it was.
$gateOverrides = array_filter(['evidence_dir' => $opts['evidence-dir'], 'relationships_path' => $opts['relationships'], 'package_base' => $opts['package-base']],
    static function ($v) { return $v !== null; });
$gateOverrides['successors'] = MarketDataB10SuccessorBinding::profilesUpTo((string) $opts['attempt']);
$postGates = static function (array $rows) use ($root, $gateOverrides): array {
    $errors = [];
    $proof = MarketDataPublicationLifecycleProofGate::validate($root, true, ['rows' => $rows] + $gateOverrides);
    foreach ($proof['errors'] as $e) {
        $errors[] = 'proof gate: '.$e;
    }
    $trace = MarketDataPublicationLifecycleTraceabilityGate::validate($root, true, ['rows' => $rows] + $gateOverrides);
    foreach ($trace['errors'] as $e) {
        $errors[] = 'traceability gate: '.$e;
    }

    return $errors;
};

$plan = MarketDataB10SuccessorBinding::plan($root, $opts['attempt'], $opts['evidence-id'], $o + ['post_gates' => $postGates]);
$report = [
    'mode' => $apply ? 'APPLY' : 'VALIDATE_ONLY',
    'status' => $plan['status'],
    'attempt' => $plan['attempt'],
    'evidence_id' => $plan['evidence_id'],
    'canonical_matrix' => $canonical,
    'already_bound' => $plan['already_bound'],
    'diagnostics' => $plan['diagnostics'],
    'errors' => $plan['errors'],
    'planned_changed_rules' => count($plan['changed_rules']),
    'matrix_sha256_before' => $plan['before_sha256'],
    'matrix_sha256_planned_after' => $plan['after_sha256'],
];

if ($plan['status'] !== 'PASS') {
    echo json_encode($report, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES).PHP_EOL;
    exit(1);
}
if (! $apply) {
    $report['note'] = 'Nothing was written. Pass --apply to promote exactly the planned rules.';
    echo json_encode($report, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES).PHP_EOL;
    exit(0);
}

$verify = static function () use ($root, $paths, $gateOverrides): array {
    $rows = MarketDataB10SuccessorBinding::readMatrix($paths['matrix'])['rows'];
    $errors = [];
    foreach (MarketDataPublicationLifecycleProofGate::validate($root, true, ['rows' => $rows] + $gateOverrides)['errors'] as $e) {
        $errors[] = 'proof gate: '.$e;
    }
    foreach (MarketDataPublicationLifecycleTraceabilityGate::validate($root, true, ['rows' => $rows] + $gateOverrides)['errors'] as $e) {
        $errors[] = 'traceability gate: '.$e;
    }

    return $errors;
};
$write = MarketDataB10SuccessorBinding::apply($plan, $paths, ['canonical' => $canonical, 'inject' => $opts['inject'], 'verify' => $verify]);
$report['write'] = $write;
$report['matrix_sha256_after'] = hash_file('sha256', $paths['matrix']);
$report['status'] = $write['errors'] === [] ? 'PASS' : 'FAIL';
echo json_encode($report, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES).PHP_EOL;
exit($write['errors'] === [] ? 0 : 1);
