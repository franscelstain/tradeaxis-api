<?php

require_once __DIR__.'/MarketDataReadProductTraceabilitySpec.php';

/**
 * Governed MD-B17-A002 proof-state invalidation after the authorised strategy refreeze.
 *
 * Usage: php this-file.php [--apply]
 *
 * The predecessor's 245 SATISFIED rows are current-epoch facts under A001, but none may be inherited
 * into A002. This tool resets exactly that complete predecessor set, keeps the previously withheld
 * rule in the denominator, proves the six additive MD-S082 rows are structural reference metadata,
 * and refuses any partial or foreign state before writing atomically.
 */
$root = dirname(__DIR__, 5);
$matrixPath = MarketDataReadProductTraceabilitySpec::matrixPath($root);
$apply = in_array('--apply', $argv, true);

$handle = fopen($matrixPath, 'r');
if ($handle === false) {
    throw new RuntimeException('Cannot open traceability matrix.');
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

$referenceIds = [
    'MD-S082-R0228', 'MD-S082-R0229', 'MD-S082-R0230',
    'MD-S082-R0231', 'MD-S082-R0232', 'MD-S082-R0233',
];
$references = [];
$mandatory = 0;
$resettable = 0;
$alreadyPristine = 0;
$invalid = [];
$touched = [];

foreach ($rows as &$row) {
    if (in_array($row['rule_id'], $referenceIds, true)) {
        $references[$row['rule_id']] = $row['primary_stage'] === 'MD-B17'
            && $row['coverage_requirement'] === 'REFERENCE_ONLY'
            && $row['applicability'] === 'REFERENCE_ONLY'
            && $row['coverage_status'] === 'REFERENCE_ONLY'
            && trim($row['current_evidence_ids']) === '';
    }

    if ($row['primary_stage'] !== 'MD-B17'
        || strtoupper(trim($row['active'])) !== 'YES'
        || $row['coverage_requirement'] !== 'REQUIRED'
        || $row['applicability'] !== 'MANDATORY') {
        continue;
    }

    $mandatory++;
    $evidence = trim($row['current_evidence_ids']);
    if ($row['coverage_status'] === 'SATISFIED' && $evidence === 'E-MD-B17-A001-001') {
        $resettable++;
    } elseif ($row['coverage_status'] === 'NOT_ASSESSED' && $evidence === '') {
        $alreadyPristine++;
    } else {
        $invalid[] = $row['rule_id'].':'.$row['coverage_status'].':'.$evidence;
        continue;
    }

    if (strpos($row['notes'], 'MD-B17-A002: predecessor_binding_invalidated=') === false) {
        $row['notes'] = trim($row['notes']).' | MD-B17-A002: predecessor_binding_invalidated='.
            ($evidence === '' ? 'UNBOUND_A001_RULE' : $evidence)
            .'; basis=authorised strategy refreeze requires fresh successor proof; inherited_satisfaction=NO';
    }
    $row['coverage_status'] = 'NOT_ASSESSED';
    $row['current_evidence_ids'] = '';
    $touched[$row['rule_id']] = true;
}
unset($row);

$missingReferences = array_values(array_diff($referenceIds, array_keys($references)));
$badReferences = array_keys(array_filter($references, static function ($valid) {
    return ! $valid;
}));
if ($mandatory !== 246) {
    $invalid[] = 'DENOMINATOR_MISMATCH:'.$mandatory;
}
if ($missingReferences !== [] || $badReferences !== []) {
    $invalid[] = 'ADDITIVE_REFERENCE_ROWS_INVALID:missing='.implode(',', $missingReferences)
        .';bad='.implode(',', $badReferences);
}
if (!(($resettable === 245 && $alreadyPristine === 1) || ($resettable === 0 && $alreadyPristine === 246))) {
    $invalid[] = 'NON_ATOMIC_PREDECESSOR_STATE:resettable='.$resettable.';pristine='.$alreadyPristine;
}

$foreign = [];
foreach ($rows as $row) {
    $after = implode('|', [
        $row['primary_stage'], $row['coverage_requirement'], $row['applicability'],
        $row['coverage_status'], $row['current_evidence_ids'], $row['notes'],
    ]);
    if (! isset($touched[$row['rule_id']]) && $after !== $before[$row['rule_id']]) {
        $foreign[] = $row['rule_id'].' ('.$row['primary_stage'].')';
    }
}
if ($foreign !== []) {
    $invalid[] = 'FOREIGN_ROWS_ALTERED:'.implode(',', $foreign);
}

$result = [
    'tool' => 'MarketDataReadProductSuccessorRebaseline',
    'stage_id' => 'MD-B17',
    'attempt_id' => 'MD-B17-A002',
    'mode' => $apply ? 'APPLY' : 'DRY_RUN',
    'status' => $invalid === [] ? 'PASS' : 'FAIL',
    'mandatory_denominator' => $mandatory,
    'predecessor_bindings_to_invalidate' => $resettable,
    'already_pristine' => $alreadyPristine,
    'additive_reference_rows' => count($references),
    'foreign_rows_altered' => count($foreign),
    'errors' => $invalid,
];
echo json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES).PHP_EOL;
if ($invalid !== []) {
    exit(1);
}
if (! $apply) {
    echo "DRY RUN - pass --apply to write.\n";
    exit(0);
}

$out = fopen($matrixPath, 'w');
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
echo 'WROTE '.$matrixPath.PHP_EOL;
