<?php

require_once __DIR__.'/MarketDataAnalyticalPriceProductTraceabilitySpec.php';
require_once __DIR__.'/MarketDataClassificationConsistencyGate.php';

/**
 * Governed MD-B12-A003 reference-population completion. Usage: php this-file.php [--apply].
 *
 * MD-B12-A002 took the two unambiguous blocks and left 39 rows behind on purpose. This pass takes
 * the remainder: 15 promotions and 24 recorded reference decisions. Unlike A002 it is not partial,
 * so its completeness assertion is absolute — every non-structural reference row the matrix assigns
 * to MD-B12 must be either promoted or explicitly decided. That is the condition for admitting the
 * stage to DECISION_RECORDED_STAGES, and it is checked here rather than assumed at admission time.
 *
 * Guards carried from the earlier passes:
 *   - every named rule must be reached, so a typo cannot silently shrink the scope;
 *   - a promoted rule must have been REFERENCE_ONLY beforehand, so a no-op cannot look like work;
 *   - no row owned by another stage may be altered, because MD-B07-A002 unbound 30 closed
 *     predicates owned by other stages while every gate stayed green.
 */
$root = dirname(__DIR__, 5);
$S = 'MarketDataAnalyticalPriceProductTraceabilitySpec';
$matrixPath = $root.'/docs/market_data/authority/governance/STRATEGY_TO_IMPLEMENTATION_TRACEABILITY_MATRIX.csv';
$apply = in_array('--apply', $argv, true);

$loaded = MarketDataClassificationConsistencyGate::readMatrix($matrixPath);
$headers = $loaded['headers'];
$rows = $loaded['rows'];
$bom = strpos((string) file_get_contents($matrixPath), "\xEF\xBB\xBF") === 0;

$before = [];
foreach ($rows as $row) {
    $before[$row['rule_id']] = $row['primary_stage'].'|'.$row['coverage_requirement'].'|'
        .$row['coverage_status'].'|'.$row['current_evidence_ids'].'|'.$row['notes'];
}

$promoted = [];
$recorded = [];
$wrongStartState = [];
$unaccounted = [];

foreach ($rows as &$row) {
    if ($row['primary_stage'] !== 'MD-B12' || strtoupper(trim($row['active'])) !== 'YES') {
        continue;
    }

    if (isset($S::A003_REMEDIATED_RULES[$row['rule_id']])) {
        if ($row['coverage_requirement'] !== 'REFERENCE_ONLY') {
            $wrongStartState[] = $row['rule_id'].' was '.$row['coverage_requirement'];

            continue;
        }
        $row['coverage_requirement'] = 'REQUIRED';
        $row['applicability'] = 'MANDATORY';
        $row['coverage_status'] = 'NOT_ASSESSED';
        $row['current_evidence_ids'] = '';
        $row['notes'] = $S::A003_ATTEMPT
            .': applicability_normalized=MANDATORY; predicate_context='.$row['section']
            .'; normalized_predicate='.preg_replace('/\s+/', ' ', trim($row['rule_text']))
            .'; proof_owner_confirmed=MD-B12'
            .'; a003_correction='.$S::A003_REMEDIATED_RULES[$row['rule_id']];
        $promoted[] = $row['rule_id'];

        continue;
    }

    if (isset($S::A003_REFERENCE_DECISIONS[$row['rule_id']])) {
        $row['notes'] = $S::A003_ATTEMPT
            .': applicability_normalized=REFERENCE_ONLY'
            .'; proof_owner_confirmed=MD-B12'
            .'; reference_basis='.$S::A003_REFERENCE_DECISIONS[$row['rule_id']];
        $recorded[] = $row['rule_id'];

        continue;
    }

    if ($row['coverage_requirement'] === 'REFERENCE_ONLY'
        && MarketDataClassificationConsistencyGate::structuralClass($row['rule_text']) === null
        && ! MarketDataClassificationConsistencyGate::hasRecordedReferenceDecision($row)) {
        $unaccounted[] = $row['rule_id'];
    }
}
unset($row);

if ($wrongStartState !== []) {
    throw new RuntimeException('named rules were not REFERENCE_ONLY before this pass: '
        .implode(', ', $wrongStartState));
}
if (count($promoted) !== count($S::A003_REMEDIATED_RULES)) {
    throw new RuntimeException('promotions this pass never reached: '
        .implode(', ', array_diff(array_keys($S::A003_REMEDIATED_RULES), $promoted)));
}
if (count($recorded) !== count($S::A003_REFERENCE_DECISIONS)) {
    throw new RuntimeException('reference decisions this pass never reached: '
        .implode(', ', array_diff(array_keys($S::A003_REFERENCE_DECISIONS), $recorded)));
}

// A003 completes the stage. A row left neither promoted nor decided means the stage is not finished,
// and admitting it to DECISION_RECORDED_STAGES would be a claim this pass cannot support.
if ($unaccounted !== []) {
    throw new RuntimeException('MD-B12 non-structural reference rows left unaccounted for: '
        .implode(', ', $unaccounted));
}

$foreign = [];
foreach ($rows as $row) {
    $key = $row['primary_stage'].'|'.$row['coverage_requirement'].'|'
        .$row['coverage_status'].'|'.$row['current_evidence_ids'].'|'.$row['notes'];
    if ($row['primary_stage'] !== 'MD-B12' && $key !== $before[$row['rule_id']]) {
        $foreign[] = $row['rule_id'].' ('.$row['primary_stage'].')';
    }
}
if ($foreign !== []) {
    throw new RuntimeException('this pass altered rows owned by another stage: '.implode(', ', $foreign));
}

echo json_encode([
    'promoted' => count($promoted),
    'reference_decisions_recorded' => count($recorded),
    'non_structural_reference_unaccounted' => count($unaccounted),
    'foreign_rows_altered' => count($foreign),
], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES).PHP_EOL;

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
