<?php

require_once __DIR__.'/MarketDataCoverageGateProofGate.php';

/**
 * Governed `MD-B15` proof binder.
 *
 *   --validate-only                      report the pre-bind state and exit
 *   --evidence-id=E-MD-B15-A001-NNN      bind every mandatory predicate to that evidence
 *   --apply                              write the matrix; without it nothing is persisted
 *
 * The binding is atomic by construction. A partial binding leaves the stage claiming a coverage
 * figure that no single execution established, which is how a stage reaches a number nobody proved.
 * Any predicate that is not pristine stops the whole run before a byte is written.
 */
$root = dirname(__DIR__, 5);
$spec = 'MarketDataCoverageGateProofSpec';
$traceability = 'MarketDataCoverageGateTraceabilitySpec';

$validateOnly = in_array('--validate-only', $argv, true);
$apply = in_array('--apply', $argv, true);

$evidence = null;
foreach ($argv as $argument) {
    if (strpos($argument, '--evidence-id=') === 0) {
        $evidence = substr($argument, strlen('--evidence-id='));
    }
}

if ($validateOnly) {
    $result = MarketDataCoverageGateProofGate::validate($root, false);
    echo json_encode([
        'mode' => 'VALIDATE_ONLY',
        'status' => $result['status'],
        'denominator' => $result['denominator'],
        'proof_map_count' => $result['proof_map_count'],
        'proof_families_used' => $result['proof_families_used'],
        'runtime_pending' => $result['runtime_pending'],
        'errors' => $result['errors'],
    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES).PHP_EOL;
    exit($result['status'] === 'PASS' ? 0 : 1);
}

if ($evidence === null || preg_match(MarketDataCoverageGateProofGate::EVIDENCE_PATTERN, $evidence) !== 1) {
    throw new RuntimeException('Use --validate-only, or --evidence-id=E-MD-B15-A001-NNN [--apply].');
}

$matches = glob($root.'/docs/market_data/records/evidence/'.$evidence.'_*');
if (count($matches) !== 1) {
    throw new RuntimeException('Exactly one governed MD-B15 evidence file must exist for '.$evidence
        .', found '.count($matches).'.');
}

$path = $traceability::matrixPath($root);
$handle = fopen($path, 'r');
if ($handle === false) {
    throw new RuntimeException('Cannot open the traceability matrix.');
}
$headers = fgetcsv($handle);
$bom = strpos($headers[0], chr(0xEF).chr(0xBB).chr(0xBF)) === 0;
$headers[0] = str_replace(chr(0xEF).chr(0xBB).chr(0xBF), '', $headers[0]);
$rows = [];
while (($values = fgetcsv($handle)) !== false) {
    if (count($values) === count($headers)) {
        $rows[] = array_combine($headers, $values);
    }
}
fclose($handle);

$before = [];
foreach ($rows as $row) {
    $before[$row['rule_id']] = implode('|', [
        $row['primary_stage'], $row['coverage_requirement'], $row['applicability'],
        $row['coverage_status'], $row['current_evidence_ids'], $row['notes'],
    ]);
}

$targets = [];
foreach ($spec::entries($root) as $entry) {
    $targets[$entry['rule_id']] = $entry['family'];
}

$families = $spec::families();
$seen = 0;
$bound = 0;
$boundRows = [];

foreach ($rows as &$row) {
    if (! isset($targets[$row['rule_id']])) {
        continue;
    }
    $seen++;

    if ($row['primary_stage'] !== $spec::STAGE
        || $row['coverage_requirement'] !== 'REQUIRED'
        || $row['applicability'] !== 'MANDATORY'
        || strtoupper(trim($row['active'])) !== 'YES') {
        throw new RuntimeException($row['rule_id'].': cannot bind a proof to a row that is not an active MD-B15 mandatory rule.');
    }
    if ($row['coverage_status'] !== 'NOT_ASSESSED' || trim($row['current_evidence_ids']) !== '') {
        throw new RuntimeException($row['rule_id'].': non-pristine predicate, refusing to rebind.');
    }

    $family = $families[$targets[$row['rule_id']]];
    $row['coverage_status'] = 'SATISFIED';
    $row['current_evidence_ids'] = $evidence;
    $row['notes'] = trim($row['notes']).' | '.$spec::ATTEMPT.': proof_family='.$targets[$row['rule_id']]
        .'; positive='.basename($family['positive'][0], '.php').'::'.$family['positive'][1]
        .'; negative='.basename($family['negative'][0], '.php').'::'.$family['negative'][1]
        .'; implementation_surface='.implode(',', $family['implementation']);

    $bound++;
    $boundRows[] = $row;
}
unset($row);

if ($seen !== $spec::EXPECTED_DENOMINATOR) {
    throw new RuntimeException('Binder reached '.$seen.' of '.$spec::EXPECTED_DENOMINATOR.' predicates.');
}
if ($bound !== $spec::EXPECTED_DENOMINATOR) {
    throw new RuntimeException('Binding must be atomic: bound '.$bound.' of '.$spec::EXPECTED_DENOMINATOR.'.');
}

// MD-B07-A002 cleared the proof state of thirty predicates owned by other stages while every gate
// stayed green. Nothing outside this stage may differ.
$foreign = [];
foreach ($rows as $row) {
    $after = implode('|', [
        $row['primary_stage'], $row['coverage_requirement'], $row['applicability'],
        $row['coverage_status'], $row['current_evidence_ids'], $row['notes'],
    ]);
    if (! isset($targets[$row['rule_id']]) && $after !== $before[$row['rule_id']]) {
        $foreign[] = $row['rule_id'].' ('.$row['primary_stage'].')';
    }
}
if ($foreign !== []) {
    throw new RuntimeException('This binder altered rows it does not own: '.implode(', ', $foreign));
}

$check = MarketDataCoverageGateProofGate::validate($root, true, ['mandatory' => $boundRows]);
if ($check['status'] !== 'PASS') {
    throw new RuntimeException('Bound validation failed: '.implode('; ', $check['errors']));
}

echo json_encode([
    'mode' => $apply ? 'BIND_AND_WRITE' : 'BIND_DRY_RUN',
    'status' => 'PASS',
    'stage_id' => $spec::STAGE,
    'attempt_id' => $spec::ATTEMPT,
    'evidence_id' => $evidence,
    'denominator' => $seen,
    'bound' => $bound,
    'foreign_rows_altered' => 0,
    'bound_validation' => $check['status'],
], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES).PHP_EOL;

if (! $apply) {
    echo "DRY RUN - pass --apply to write.\n";
    exit(0);
}

$out = fopen($path, 'w');
$outHeaders = $headers;
if ($bom) {
    $outHeaders[0] = chr(0xEF).chr(0xBB).chr(0xBF).$outHeaders[0];
}
fputcsv($out, $outHeaders);
foreach ($rows as $row) {
    fputcsv($out, array_map(static function ($header) use ($row) {
        return $row[$header];
    }, $headers));
}
fclose($out);
echo 'WROTE '.$path.PHP_EOL;
