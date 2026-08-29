<?php

require_once __DIR__.'/MarketDataCorporateActionTraceabilitySpec.php';
require_once __DIR__.'/MarketDataClassificationConsistencyGate.php';

/**
 * Governed MD-B11-A003 reference-population completion. Usage: php this-file.php [--apply].
 *
 * MD-B11-A002 took the four prohibition sections and the two date hierarchies and left 167 rows
 * behind on purpose. This pass takes the remainder: 30 promotions and a recorded decision on every
 * other non-structural reference row the matrix assigns to MD-B11.
 *
 * The reference decisions are generated from a section-level basis rather than a per-row string.
 * Rows inside one section are the same kind of text — a wrapped sentence, an enum definition, a
 * capability disclaimer — so a per-row string would repeat rather than inform. The basis names the
 * document and section it was derived from, so the call stays attributable and challengeable.
 *
 * Guards carried from the earlier passes:
 *   - every named promotion must be reached, so a typo cannot silently shrink the scope;
 *   - a promoted rule must have been REFERENCE_ONLY beforehand, so a no-op cannot look like work;
 *   - no row owned by another stage may be altered, because MD-B07-A002 unbound 30 closed
 *     predicates owned by other stages while every gate stayed green;
 *   - and, because this pass completes the stage, nothing non-structural may be left undecided.
 */
$root = dirname(__DIR__, 5);
$S = 'MarketDataCorporateActionTraceabilitySpec';
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
    if ($row['primary_stage'] !== 'MD-B11' || strtoupper(trim($row['active'])) !== 'YES') {
        continue;
    }

    if (isset($S::A003_REMEDIATED_RULES[$row['rule_id']])) {
        // A re-run must be able to repair its own output. A rule this pass already promoted is
        // recognised by its own note and counted as done; a rule that is REQUIRED for any other
        // reason still errors, because that is what the start-state guard exists to catch.
        $alreadyMine = $row['coverage_requirement'] === 'REQUIRED'
            && strpos($row['notes'], $S::A003_ATTEMPT.': applicability_normalized=MANDATORY') === 0;
        if ($alreadyMine) {
            $promoted[] = $row['rule_id'];

            continue;
        }
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
            .'; proof_owner_confirmed=MD-B11'
            .'; a003_correction='.$S::A003_REMEDIATED_RULES[$row['rule_id']];
        $promoted[] = $row['rule_id'];

        continue;
    }

    if ($row['coverage_requirement'] !== 'REFERENCE_ONLY') {
        continue;
    }
    if (MarketDataClassificationConsistencyGate::structuralClass($row['rule_text']) !== null) {
        continue;
    }
    // An exception row is rewritten even when it already carries a decision, so a re-run repairs
    // its own earlier output instead of skipping past it.
    $isException = isset($S::A003_MIXED_RUN_EXCEPTIONS[$row['rule_id']]);
    if (! $isException && MarketDataClassificationConsistencyGate::hasRecordedReferenceDecision($row)) {
        continue;
    }

    $row['notes'] = $S::A003_ATTEMPT
        .': applicability_normalized=REFERENCE_ONLY'
        .'; proof_owner_confirmed=MD-B11'
        .'; reference_basis='.$S::a003ReferenceBasis($row['strategy_document_id'], $row['section']);
    if (isset($S::A003_MIXED_RUN_EXCEPTIONS[$row['rule_id']])) {
        $row['notes'] .= '; semantic_reference_exception=B11_REVIEWED'
            .'; reference_owner_basis='.$S::A003_MIXED_RUN_EXCEPTIONS[$row['rule_id']];
    }
    $recorded[] = $row['rule_id'];
}
unset($row);

if ($wrongStartState !== []) {
    throw new RuntimeException('named promotions were not REFERENCE_ONLY before this pass: '
        .implode(', ', $wrongStartState));
}
if (count($promoted) !== count($S::A003_REMEDIATED_RULES)) {
    throw new RuntimeException('promotions this pass never reached: '
        .implode(', ', array_diff(array_keys($S::A003_REMEDIATED_RULES), $promoted)));
}

// Completion means completion. Re-scan after the pass: nothing non-structural may remain undecided,
// because that is the claim admitting MD-B11 to DECISION_RECORDED_STAGES rests on.
foreach ($rows as $row) {
    if ($row['primary_stage'] !== 'MD-B11' || strtoupper(trim($row['active'])) !== 'YES') {
        continue;
    }
    if ($row['coverage_requirement'] !== 'REFERENCE_ONLY') {
        continue;
    }
    if (MarketDataClassificationConsistencyGate::structuralClass($row['rule_text']) !== null) {
        continue;
    }
    if (! MarketDataClassificationConsistencyGate::hasRecordedReferenceDecision($row)) {
        $unaccounted[] = $row['rule_id'];
    }
}
if ($unaccounted !== []) {
    throw new RuntimeException('MD-B11 non-structural reference rows left undecided: '
        .implode(', ', $unaccounted));
}

$foreign = [];
foreach ($rows as $row) {
    $key = $row['primary_stage'].'|'.$row['coverage_requirement'].'|'
        .$row['coverage_status'].'|'.$row['current_evidence_ids'].'|'.$row['notes'];
    if ($row['primary_stage'] !== 'MD-B11' && $key !== $before[$row['rule_id']]) {
        $foreign[] = $row['rule_id'].' ('.$row['primary_stage'].')';
    }
}
if ($foreign !== []) {
    throw new RuntimeException('this pass altered rows owned by another stage: '.implode(', ', $foreign));
}

echo json_encode([
    'promoted' => count($promoted),
    'reference_decisions_recorded' => count($recorded),
    'non_structural_reference_undecided_after' => count($unaccounted),
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
