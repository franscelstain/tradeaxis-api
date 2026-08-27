<?php

require_once __DIR__.'/MarketDataLiquidityMetricProofGate.php';
require_once __DIR__.'/MarketDataLiquidityMetricTraceabilityGate.php';

/**
 * Atomic binder for the MD-B13 mandatory predicates.
 *
 * The binder refuses to run while any conditional row is still APPLICABILITY_PENDING. Binding the
 * mandatory predicates first would produce a stage reading 33/33 with fifteen rows nobody had
 * decided, and a coverage figure that omits pending applicability is exactly what the standard
 * forbids.
 */
$root = dirname(__DIR__, 5);
$validateOnly = in_array('--validate-only', $argv, true);
$apply = in_array('--apply', $argv, true);
$evidence = null;
foreach ($argv as $argument) {
    if (strpos($argument, '--evidence-id=') === 0) {
        $evidence = substr($argument, 14);
    }
}

if ($validateOnly) {
    $result = MarketDataLiquidityMetricProofGate::validate($root, false);
    echo json_encode([
        'status' => $result['status'],
        'mode' => 'VALIDATE_ONLY',
        'denominator' => $result['denominator'],
        'proof_map_count' => $result['proof_map_count'],
        'proof_families_used' => $result['proof_families_used'],
        'runtime_pending' => $result['runtime_pending'],
        'errors' => $result['errors'],
    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES).PHP_EOL;
    exit($result['status'] === 'PASS' ? 0 : 1);
}

if (! $evidence || ! preg_match('/^E-MD-B13-A001-\d{3}$/', $evidence)) {
    throw new RuntimeException('Use --validate-only or --evidence-id=E-MD-B13-A001-NNN [--apply].');
}

$matches = glob($root.'/docs/market_data/records/evidence/'.$evidence.'_*');
if (count($matches) !== 1) {
    throw new RuntimeException('Exactly one governed B13 evidence file required for '.$evidence.'.');
}

$pending = MarketDataLiquidityMetricTraceabilitySpec::conditionalPending($root);
if ($pending !== []) {
    throw new RuntimeException(
        'APPLICABILITY_PENDING_BLOCKS_BINDING: '.count($pending).' conditional rows are undecided; '
        .'resolve each with evidence before binding the mandatory predicates.'
    );
}

$path = MarketDataLiquidityMetricTraceabilitySpec::matrixPath($root);
$handle = fopen($path, 'r');
$headers = fgetcsv($handle);
$bom = strpos($headers[0], "\xEF\xBB\xBF") === 0;
$headers[0] = preg_replace('/^\xEF\xBB\xBF/', '', $headers[0]);
$rows = [];
while (($values = fgetcsv($handle)) !== false) {
    if (count($values) === count($headers)) {
        $rows[] = array_combine($headers, $values);
    }
}
fclose($handle);

$ids = array_flip(array_column(MarketDataLiquidityMetricProofSpec::entries($root), 'rule_id'));
$bound = 0;
$boundMandatory = [];

foreach ($rows as &$row) {
    if (! isset($ids[$row['rule_id']])) {
        continue;
    }
    if ($row['coverage_status'] !== 'NOT_ASSESSED' || trim($row['current_evidence_ids']) !== '') {
        throw new RuntimeException('Non-pristine predicate '.$row['rule_id']);
    }
    $row['coverage_status'] = 'SATISFIED';
    $row['current_evidence_ids'] = $evidence;
    $boundMandatory[] = $row;
    $bound++;
}
unset($row);

if ($bound !== MarketDataLiquidityMetricProofSpec::EXPECTED_DENOMINATOR) {
    throw new RuntimeException('Binder count mismatch: bound '.$bound);
}

$check = MarketDataLiquidityMetricProofGate::validate($root, true, ['mandatory' => $boundMandatory]);
if ($check['status'] !== 'PASS') {
    throw new RuntimeException('Bound validation failed: '.implode(';', $check['errors']));
}

echo json_encode([
    'status' => 'PASS',
    'denominator' => $bound,
    'evidence_id' => $evidence,
    'apply' => $apply,
], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES).PHP_EOL;

if (! $apply) {
    exit(0);
}

$handle = fopen($path, 'w');
$outHeaders = $headers;
if ($bom) {
    $outHeaders[0] = "\xEF\xBB\xBF".$outHeaders[0];
}
fputcsv($handle, $outHeaders);
foreach ($rows as $row) {
    fputcsv($handle, array_map(static function ($key) use ($row) {
        return $row[$key];
    }, $headers));
}
fclose($handle);
