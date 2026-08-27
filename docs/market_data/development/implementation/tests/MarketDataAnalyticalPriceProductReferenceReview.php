<?php

require_once __DIR__.'/MarketDataAnalyticalPriceProductTraceabilitySpec.php';
require_once __DIR__.'/MarketDataClassificationConsistencyGate.php';

/**
 * Governed MD-B12-A002 scoped reference-population re-check. Usage: php this-file.php [--apply].
 *
 * A002 is deliberately partial: it takes `Forbidden behavior (LOCKED)` and
 * `Eligibility for adjustment (LOCKED)` only. A003 takes the rest. A partial pass must therefore
 * prove it is partial *on purpose* — it reports what it leaves behind rather than implying the
 * stage is finished, and MD-B12 stays outside DECISION_RECORDED_STAGES until A003 lands.
 *
 * The guards are the ones MD-B07-A002 and MD-B09-A003 needed:
 *   - it promotes exactly the rules named in REMEDIATED_RULES, no more and no fewer;
 *   - every named rule must actually have been REFERENCE_ONLY, so a typo cannot silently no-op;
 *   - it must not alter a single row owned by another stage.
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
$wrongStartState = [];
$remainingUnexplained = 0;

foreach ($rows as &$row) {
    if ($row['primary_stage'] !== 'MD-B12' || strtoupper(trim($row['active'])) !== 'YES') {
        continue;
    }

    if (isset($S::REMEDIATED_RULES[$row['rule_id']])) {
        if ($row['coverage_requirement'] !== 'REFERENCE_ONLY') {
            $wrongStartState[] = $row['rule_id'].' was '.$row['coverage_requirement'];

            continue;
        }
        $row['coverage_requirement'] = 'REQUIRED';
        $row['applicability'] = 'MANDATORY';
        $row['coverage_status'] = 'NOT_ASSESSED';
        $row['current_evidence_ids'] = '';
        $row['notes'] = $S::REMEDIATION_ATTEMPT
            .': applicability_normalized=MANDATORY; predicate_context='.$row['section']
            .'; normalized_predicate='.preg_replace('/\s+/', ' ', trim($row['rule_text']))
            .'; proof_owner_confirmed=MD-B12'
            .'; '.$S::REMEDIATED_RULES[$row['rule_id']];
        $promoted[] = $row['rule_id'];

        continue;
    }

    if ($row['coverage_requirement'] === 'REFERENCE_ONLY'
        && MarketDataClassificationConsistencyGate::structuralClass($row['rule_text']) === null
        && ! MarketDataClassificationConsistencyGate::hasRecordedReferenceDecision($row)) {
        $remainingUnexplained++;
    }
}
unset($row);

if ($wrongStartState !== []) {
    throw new RuntimeException('named rules were not REFERENCE_ONLY before this pass: '
        .implode(', ', $wrongStartState));
}
if (count($promoted) !== count($S::REMEDIATED_RULES)) {
    $missed = array_diff(array_keys($S::REMEDIATED_RULES), $promoted);
    throw new RuntimeException('named rules this pass never reached: '.implode(', ', $missed));
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
    'remaining_unexplained_reference_for_a003' => $remainingUnexplained,
    'foreign_rows_altered' => count($foreign),
    'scope' => 'Forbidden behavior (LOCKED) and Eligibility for adjustment (LOCKED) only',
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
