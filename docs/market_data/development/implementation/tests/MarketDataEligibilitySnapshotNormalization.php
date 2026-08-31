<?php

require_once __DIR__.'/MarketDataEligibilitySnapshotTraceabilitySpec.php';
require_once __DIR__.'/MarketDataClassificationConsistencyGate.php';

/**
 * Governed MD-B16 stage-entry normalization. Usage: php this-file.php [--apply].
 *
 * Carries the four guards earlier stages needed, each added after a real failure:
 *
 *   - full-scope coverage: every active row the matrix assigns to MD-B16 must be examined by this
 *     pass. MD-B07-A001 selected its scope from hand-curated lists, missed one row, and reported
 *     success; the row was a credential-masking obligation.
 *   - foreign-row isolation: nothing owned by another stage may be altered. MD-B07-A002 unbound 30
 *     closed predicates owned by MD-B08, MD-B09 and MD-B10 while every gate stayed green.
 *   - named-rule completeness: a rule named in the spec that this pass never reaches is an error,
 *     so a typo cannot silently shrink the scope.
 *   - no transitional survivor: section 8 forbids a stage relying on a denominator that still
 *     contains a legacy value, so a leftover is fatal rather than merely reported.
 */
$root = dirname(__DIR__, 5);
$S = 'MarketDataEligibilitySnapshotTraceabilitySpec';
$matrixPath = $S::matrixPath($root);
$apply = in_array('--apply', $argv, true);

$loaded = MarketDataClassificationConsistencyGate::readMatrix($matrixPath);
$headers = $loaded['headers'];
$rows = $loaded['rows'];
$bom = strpos((string) file_get_contents($matrixPath), chr(0xEF).chr(0xBB).chr(0xBF)) === 0;

$before = [];
$owned = [];
foreach ($rows as $row) {
    $before[$row['rule_id']] = $row['primary_stage'].'|'.$row['coverage_requirement'].'|'
        .$row['applicability'].'|'.$row['coverage_status'].'|'.$row['current_evidence_ids'].'|'.$row['notes'];
    if ($row['primary_stage'] === $S::STAGE && strtoupper(trim($row['active'])) === 'YES') {
        $owned[$row['rule_id']] = true;
    }
}

$examined = [];
$counts = [
    'mandatory_resolved' => 0,
    'promoted' => 0,
    'reference_resolved' => 0,
    'reference_decisions_recorded' => 0,
    'already_mandatory' => 0,
    'optional_capability' => 0,
    'structural_reference' => 0,
];

$normalize = static function (array $row) {
    return preg_replace('/\s+/', ' ', trim($row['rule_text']));
};

foreach ($rows as &$row) {
    if ($row['primary_stage'] !== $S::STAGE || strtoupper(trim($row['active'])) !== 'YES') {
        continue;
    }
    $examined[$row['rule_id']] = true;
    $rid = $row['rule_id'];

    if (isset($S::ENTRY_MANDATORY[$rid]) || isset($S::ENTRY_PROMOTED[$rid])) {
        $promoted = isset($S::ENTRY_PROMOTED[$rid]);
        $basis = $promoted ? $S::ENTRY_PROMOTED[$rid] : $S::ENTRY_MANDATORY[$rid];
        $row['coverage_requirement'] = 'REQUIRED';
        $row['applicability'] = 'MANDATORY';
        $row['coverage_status'] = 'NOT_ASSESSED';
        $row['current_evidence_ids'] = '';
        $row['notes'] = $S::ATTEMPT
            .': applicability_normalized=MANDATORY; predicate_context='.$row['section']
            .'; normalized_predicate='.$normalize($row)
            .'; proof_owner_confirmed='.$S::STAGE
            .'; applicability_basis=always_applicable: carries an executable coverage obligation in '
            .$basis
            .'; stage_entry_review=MD-B16 semantic revalidation';
        $counts[$promoted ? 'promoted' : 'mandatory_resolved']++;

        continue;
    }

    if (isset($S::ENTRY_REFERENCE[$rid])) {
        $row['coverage_requirement'] = 'REFERENCE_ONLY';
        $row['applicability'] = 'REFERENCE_ONLY';
        $row['coverage_status'] = 'REFERENCE_ONLY';
        $row['current_evidence_ids'] = '';
        $row['notes'] = $S::ATTEMPT
            .': applicability_normalized=REFERENCE_ONLY'
            .'; proof_owner_confirmed='.$S::STAGE
            .'; reference_basis='.$S::ENTRY_REFERENCE[$rid]
            .'; stage_entry_review=MD-B16 semantic revalidation';
        $counts['reference_resolved']++;

        continue;
    }

    if ($row['applicability'] === 'OPTIONAL_CAPABILITY') {
        $counts['optional_capability']++;

        continue;
    }
    if ($row['coverage_requirement'] === 'REQUIRED') {
        $counts['already_mandatory']++;

        // A row carried in as required still needs the parent/context binding and normalized
        // predicate section 3 requires. MD-S023-R0063 reached the MD-B14 denominator with neither
        // and was only caught at closure.
        if (strpos((string) $row['notes'], 'predicate_context=') === false) {
            $row['notes'] = trim($row['notes']).' | '.$S::ATTEMPT
                .': context_binding_correction; predicate_context='.$row['section']
                .'; normalized_predicate='.$normalize($row)
                .'; proof_owner_confirmed='.$S::STAGE;
        }

        continue;
    }

    if (MarketDataClassificationConsistencyGate::structuralClass($row['rule_text']) !== null) {
        $counts['structural_reference']++;

        continue;
    }
    if (MarketDataClassificationConsistencyGate::hasRecordedReferenceDecision($row)) {
        continue;
    }
    $row['notes'] = $S::ATTEMPT
        .': applicability_normalized=REFERENCE_ONLY'
        .'; proof_owner_confirmed='.$S::STAGE
        .'; reference_basis=stage-entry review found no executable proof obligation in this row'
        .' [document '.$row['strategy_document_id'].', section '.$row['section'].']';
    $counts['reference_decisions_recorded']++;
}
unset($row);

$unexamined = array_keys(array_diff_key($owned, $examined));
if ($unexamined !== []) {
    throw new RuntimeException('MD-B16-owned rows never examined by this normalization: '
        .implode(', ', $unexamined));
}
$counts['b16_owned_examined'] = count($owned);

foreach ([$S::ENTRY_MANDATORY, $S::ENTRY_PROMOTED, $S::ENTRY_REFERENCE] as $named) {
    $missed = array_keys(array_diff_key($named, $examined));
    if ($missed !== []) {
        throw new RuntimeException('named rules this pass never reached: '.implode(', ', $missed));
    }
}

$survivors = [];
$pending = 0;
foreach ($rows as $row) {
    if ($row['primary_stage'] !== $S::STAGE || strtoupper(trim($row['active'])) !== 'YES') {
        continue;
    }
    if ($row['applicability'] === 'MANDATORY_OR_CONDITIONAL') {
        $survivors[] = $row['rule_id'];
    }
    if ($row['applicability'] === 'CONDITIONAL_PENDING' || $row['coverage_status'] === 'APPLICABILITY_PENDING') {
        $pending++;
    }
}
if ($survivors !== []) {
    throw new RuntimeException('transitional applicability survived this pass: '.implode(', ', $survivors));
}
$counts['conditional_pending_blocking_closure'] = $pending;

$foreign = [];
foreach ($rows as $row) {
    $key = $row['primary_stage'].'|'.$row['coverage_requirement'].'|'
        .$row['applicability'].'|'.$row['coverage_status'].'|'.$row['current_evidence_ids'].'|'.$row['notes'];
    if ($row['primary_stage'] !== $S::STAGE && $key !== $before[$row['rule_id']]) {
        $foreign[] = $row['rule_id'].' ('.$row['primary_stage'].')';
    }
}
if ($foreign !== []) {
    throw new RuntimeException('this pass altered rows owned by another stage: '.implode(', ', $foreign));
}
$counts['foreign_rows_altered'] = 0;

$denominator = 0;
$unbound = [];
foreach ($rows as $row) {
    if ($row['primary_stage'] === $S::STAGE && strtoupper(trim($row['active'])) === 'YES'
        && $row['coverage_requirement'] === 'REQUIRED' && $row['applicability'] === 'MANDATORY') {
        $denominator++;
        if (strpos((string) $row['notes'], 'predicate_context=') === false
            || strpos((string) $row['notes'], 'normalized_predicate=') === false) {
            $unbound[] = $row['rule_id'];
        }
    }
}
if ($unbound !== []) {
    throw new RuntimeException('denominator rows without parent/context binding: '.implode(', ', $unbound));
}
$counts['b16_denominator'] = $denominator;

echo json_encode($counts, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES).PHP_EOL;
if (! $apply) {
    echo "DRY RUN — pass --apply to write.\n";
    exit(0);
}

$handle = fopen($matrixPath, 'w');
$out = $headers;
if ($bom) {
    $out[0] = chr(0xEF).chr(0xBB).chr(0xBF).$out[0];
}
fputcsv($handle, $out);
foreach ($rows as $row) {
    fputcsv($handle, array_map(static function ($h) use ($row) {
        return $row[$h];
    }, $headers));
}
fclose($handle);
echo 'WROTE '.$matrixPath.PHP_EOL;
