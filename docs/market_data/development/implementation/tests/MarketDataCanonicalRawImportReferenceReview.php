<?php

require_once __DIR__.'/MarketDataCanonicalRawImportTraceabilitySpec.php';
require_once __DIR__.'/MarketDataClassificationConsistencyGate.php';

/**
 * Governed MD-B09-A003 reference-population re-check. Usage: php this-file.php [--apply].
 *
 * B09 has no owner-map normalization of its own, so this pass is deliberately narrow: it promotes
 * the rules named in REMEDIATED_RULES, records the decision on the rules named in
 * REFERENCE_DECISIONS, and touches nothing else.
 *
 * It carries the two guards the B07 and B08 normalizations needed:
 *   - it must account for every non-structural reference row the matrix assigns to MD-B09, so a
 *     row cannot be skipped and still let the pass report success;
 *   - it must not disturb any other stage's rows at all.
 */
$root = dirname(__DIR__, 5);
$S = 'MarketDataCanonicalRawImportTraceabilitySpec';
$matrixPath = $S::matrixPath($root);
$apply = in_array('--apply', $argv, true);

$loaded = MarketDataClassificationConsistencyGate::readMatrix($matrixPath);
$headers = $loaded['headers'];
$rows = $loaded['rows'];
$bom = strpos((string) file_get_contents($matrixPath), "\xEF\xBB\xBF") === 0;

$before = [];
foreach ($rows as $row) {
    $before[$row['rule_id']] = $row['primary_stage'].'|'.$row['coverage_requirement'].'|'
        .$row['coverage_status'].'|'.$row['current_evidence_ids'];
}

$promoted = 0;
$recorded = 0;
$outstanding = [];

foreach ($rows as &$row) {
    if ($row['primary_stage'] !== $S::STAGE || strtoupper(trim($row['active'])) !== 'YES') {
        continue;
    }

    if (isset($S::REMEDIATED_RULES[$row['rule_id']])) {
        $row['coverage_requirement'] = 'REQUIRED';
        $row['applicability'] = 'MANDATORY';
        $row['coverage_status'] = 'NOT_ASSESSED';
        $row['current_evidence_ids'] = '';
        $row['notes'] = trim($row['notes']) === ''
            ? $S::REMEDIATION_ATTEMPT.': applicability_normalized=MANDATORY; predicate_context=SELF_CONTAINED'
                .'; normalized_predicate='.preg_replace('/\s+/', ' ', trim($row['rule_text']))
                .'; proof_owner_confirmed='.$S::STAGE
                .'; '.$S::REMEDIATED_RULES[$row['rule_id']]
            : $row['notes'];
        $promoted++;

        continue;
    }

    if (isset($S::REFERENCE_DECISIONS[$row['rule_id']])) {
        $row['notes'] = $S::REMEDIATION_ATTEMPT
            .': applicability_normalized=REFERENCE_ONLY'
            .'; proof_owner_confirmed='.$S::STAGE
            .'; reference_basis='.$S::REFERENCE_DECISIONS[$row['rule_id']];
        $recorded++;

        continue;
    }

    if ($row['coverage_requirement'] === 'REFERENCE_ONLY'
        && MarketDataClassificationConsistencyGate::structuralClass($row['rule_text']) === null
        && ! MarketDataClassificationConsistencyGate::hasRecordedReferenceDecision($row)) {
        $outstanding[] = $row['rule_id'];
    }
}
unset($row);

// Every non-structural reference row must be either promoted or explicitly decided. A re-check that
// leaves one unaccounted for is a re-check of a subset.
if ($outstanding !== []) {
    throw new RuntimeException('B09 non-structural reference rows left unaccounted for: '
        .implode(', ', $outstanding));
}

// This pass owns MD-B09 rows only. Anything else it touched is a bug, not a side effect.
$foreign = [];
foreach ($rows as $row) {
    $key = $row['primary_stage'].'|'.$row['coverage_requirement'].'|'
        .$row['coverage_status'].'|'.$row['current_evidence_ids'];
    if ($row['primary_stage'] !== $S::STAGE && $key !== $before[$row['rule_id']]) {
        $foreign[] = $row['rule_id'].' ('.$row['primary_stage'].')';
    }
}
if ($foreign !== []) {
    throw new RuntimeException('this pass altered rows owned by another stage: '.implode(', ', $foreign));
}

$counts = [
    'promoted' => $promoted,
    'reference_decisions_recorded' => $recorded,
    'non_structural_reference_unaccounted' => count($outstanding),
    'foreign_rows_altered' => count($foreign),
];
if ($promoted !== count($S::REMEDIATED_RULES) || $recorded !== count($S::REFERENCE_DECISIONS)) {
    throw new RuntimeException('B09 review did not reach every named rule: '.json_encode($counts));
}

echo json_encode($counts, JSON_PRETTY_PRINT).PHP_EOL;
if (! $apply) {
    echo "DRY RUN — pass --apply to write.\n";
    exit(0);
}

$handle = fopen($matrixPath, 'w');
$out = $headers;
if ($bom) {
    $out[0] = "\xEF\xBB\xBF".$out[0];
}
fputcsv($handle, $out);
foreach ($rows as $row) {
    fputcsv($handle, array_map(static function ($h) use ($row) {
        return $row[$h];
    }, $headers));
}
fclose($handle);
echo 'WROTE '.$matrixPath.PHP_EOL;
