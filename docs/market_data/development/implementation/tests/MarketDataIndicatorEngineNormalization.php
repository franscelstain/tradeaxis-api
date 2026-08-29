<?php

require_once __DIR__.'/MarketDataIndicatorEngineTraceabilitySpec.php';
require_once __DIR__.'/MarketDataClassificationConsistencyGate.php';

/**
 * Governed MD-B14 stage-entry normalization. Usage: php this-file.php [--apply].
 *
 * Resolves every transitional MANDATORY_OR_CONDITIONAL row this stage owns, and records a decision
 * on every non-structural reference row, which is what lets MD-B14 enter DECISION_RECORDED_STAGES
 * without carrying new debt from its first day.
 *
 * The guards are the ones five earlier stages needed, each added after a real failure:
 *   - full-scope coverage: every active row the matrix assigns to MD-B14 must be examined by this
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
$S = 'MarketDataIndicatorEngineTraceabilitySpec';
$matrixPath = $S::matrixPath($root);
$apply = in_array('--apply', $argv, true);

$loaded = MarketDataClassificationConsistencyGate::readMatrix($matrixPath);
$headers = $loaded['headers'];
$rows = $loaded['rows'];
$bom = strpos((string) file_get_contents($matrixPath), "\xEF\xBB\xBF") === 0;

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
    'mixed_run_promoted' => 0,
    'reference_resolved' => 0,
    'conditional_resolved' => 0,
    'reference_decisions_recorded' => 0,
    'already_mandatory' => 0,
    'optional_capability' => 0,
    'structural_reference' => 0,
];

foreach ($rows as &$row) {
    if ($row['primary_stage'] !== $S::STAGE || strtoupper(trim($row['active'])) !== 'YES') {
        continue;
    }
    $examined[$row['rule_id']] = true;
    $rid = $row['rule_id'];

    if (isset($S::ENTRY_MIXED_RUN_PROMOTIONS[$rid])) {
        $row['coverage_requirement'] = 'REQUIRED';
        $row['applicability'] = 'MANDATORY';
        $row['coverage_status'] = 'NOT_ASSESSED';
        $row['current_evidence_ids'] = '';
        $row['notes'] = $S::ATTEMPT
            .': applicability_normalized=MANDATORY; predicate_context='.$row['section']
            .'; normalized_predicate='.preg_replace('/\s+/', ' ', trim($row['rule_text']))
            .'; proof_owner_confirmed='.$S::STAGE
            .'; applicability_basis=always_applicable: '.$S::ENTRY_MIXED_RUN_PROMOTIONS[$rid]
            .'; stage_entry_review=MD-B14 semantic revalidation';
        $counts['mixed_run_promoted']++;

        continue;
    }

    if (isset($S::ENTRY_MANDATORY[$rid])) {
        $row['coverage_requirement'] = 'REQUIRED';
        $row['applicability'] = 'MANDATORY';
        $row['coverage_status'] = 'NOT_ASSESSED';
        $row['current_evidence_ids'] = '';
        $row['notes'] = $S::ATTEMPT
            .': applicability_normalized=MANDATORY; predicate_context='.$row['section']
            .'; normalized_predicate='.preg_replace('/\s+/', ' ', trim($row['rule_text']))
            .'; proof_owner_confirmed='.$S::STAGE
            .'; applicability_basis=always_applicable: '.$S::ENTRY_MANDATORY[$rid]
            .'; stage_entry_review=MD-B14 semantic revalidation';
        $counts['mandatory_resolved']++;

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
            .'; stage_entry_review=MD-B14 semantic revalidation';
        $counts['reference_resolved']++;

        continue;
    }

    if (isset($S::ENTRY_CONDITIONAL[$rid])) {
        $row['coverage_requirement'] = 'REQUIRED';
        $row['applicability'] = 'CONDITIONAL_PENDING';
        $row['coverage_status'] = 'APPLICABILITY_PENDING';
        $row['current_evidence_ids'] = '';
        $row['notes'] = $S::ATTEMPT
            .': applicability_normalized=CONDITIONAL_PENDING; predicate_context='.$row['section']
            .'; normalized_predicate='.preg_replace('/\s+/', ' ', trim($row['rule_text']))
            .'; proof_owner_confirmed='.$S::STAGE
            .'; applicability_basis=condition_unresolved: '.$S::ENTRY_CONDITIONAL[$rid]
            .'; stage_entry_review=MD-B14 semantic revalidation';
        $counts['conditional_resolved']++;

        continue;
    }

    if ($row['applicability'] === 'OPTIONAL_CAPABILITY') {
        $counts['optional_capability']++;

        continue;
    }
    if ($row['coverage_requirement'] === 'REQUIRED') {
        $counts['already_mandatory']++;

        continue;
    }

    // Remaining rows are reference. Structural ones are non-requirable by section 2 and need no
    // decision; the rest get one recorded so an empty-notes row can never again be mistaken for a
    // row nobody read.
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
    throw new RuntimeException('MD-B14-owned rows never examined by this normalization: '
        .implode(', ', $unexamined));
}
$counts['b14_owned_examined'] = count($owned);

foreach ([$S::ENTRY_MANDATORY, $S::ENTRY_REFERENCE, $S::ENTRY_CONDITIONAL] as $named) {
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
    if ($row['applicability'] === 'CONDITIONAL_PENDING') {
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
foreach ($rows as $row) {
    if ($row['primary_stage'] === $S::STAGE && strtoupper(trim($row['active'])) === 'YES'
        && $row['coverage_requirement'] === 'REQUIRED' && $row['applicability'] === 'MANDATORY') {
        $denominator++;
    }
}
$counts['b14_denominator'] = $denominator;

echo json_encode($counts, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES).PHP_EOL;
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
