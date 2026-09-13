<?php

require_once __DIR__.'/MarketDataReplayVerificationPredicateMap.php';

/**
 * `MD-B18-A002` phase 1 — record parent/context binding and normalized predicates, and return the
 * stage to an honest un-proven state.
 *
 * Two things happen here, and only these two:
 *
 *  1. every denominator row gains `predicate_context=` and `normalized_predicate=`, which
 *     `STRATEGY_IMPLEMENTATION_TRACEABILITY_STANDARD.md` section 3 requires and section 8 makes a
 *     precondition of closure. 85 of the 121 rows had neither;
 *  2. every denominator row returns to `NOT_ASSESSED` with its evidence reference cleared.
 *
 * The second is deliberately applied to all 121, not only to the 88 whose recorded proof does not
 * establish their predicate. The 33 `SUPPORTED` rows were proven under `MD-B18-A001`, whose closure
 * is withdrawn; carrying their `SATISFIED` forward would be inheriting a historical pass, which
 * section 6 forbids and which is the specific habit `F-MD-B19-A001-002` exists to correct. They are
 * re-proven and re-bound in phase 2 against `MD-B18-A002` evidence, or they do not count.
 *
 * Writing discipline, after `MD-B18-A001`'s binder re-quoted 6226 rows it did not own while
 * correctly reporting `foreign_rows_altered: 0`: untouched lines are emitted byte-for-byte, and the
 * run reports the actual number of changed lines rather than the number of rows it meant to change.
 *
 * Dry run by default. Pass `--apply` to write.
 */

$root = dirname(__DIR__, 5);
$apply = in_array('--apply', $argv, true);
$map = 'MarketDataReplayVerificationPredicateMap';
$path = $root.'/docs/market_data/authority/governance/STRATEGY_TO_IMPLEMENTATION_TRACEABILITY_MATRIX.csv';

$raw = file_get_contents($path);
if ($raw === false) {
    throw new RuntimeException('Cannot read the traceability matrix.');
}
$bom = substr($raw, 0, 3) === "\xEF\xBB\xBF";
$body = $bom ? substr($raw, 3) : $raw;
if (strpos($body, "\r\n") !== false) {
    throw new RuntimeException('Matrix is CRLF; this writer assumes LF and will not guess.');
}

$lines = explode("\n", rtrim($body, "\n"));
$headerLine = array_shift($lines);
$headers = str_getcsv($headerLine);

$rows = [];
$originalLine = [];
foreach ($lines as $index => $line) {
    if ($line === '') {
        continue;
    }
    $values = str_getcsv($line);
    if (count($values) !== count($headers)) {
        throw new RuntimeException('Matrix line '.($index + 2).' does not parse to '
            .count($headers).' fields; a line-based binding is unsafe on this file.');
    }
    $row = array_combine($headers, $values);
    $rows[] = $row;
    $originalLine[count($rows) - 1] = $line;
}

$before = [];
foreach ($rows as $row) {
    $before[$row['rule_id']] = implode('|', [
        $row['primary_stage'], $row['coverage_requirement'], $row['applicability'],
        $row['coverage_status'], $row['current_evidence_ids'], $row['notes'],
    ]);
}

$targets = $map::PREDICATES;
$seen = 0;
$mutated = [];
$clearedEvidence = 0;
$alreadyBound = [];

foreach ($rows as &$row) {
    $id = $row['rule_id'];
    if (! isset($targets[$id])) {
        continue;
    }
    $seen++;

    if ($row['primary_stage'] !== $map::STAGE
        || $row['coverage_requirement'] !== 'REQUIRED'
        || ! in_array($row['applicability'], ['MANDATORY', 'CONDITIONAL_APPLICABLE'], true)
        || strtoupper(trim($row['active'])) !== 'YES') {
        throw new RuntimeException($id.': the reviewed map names a row that is not an active MD-B18 required rule.');
    }
    if (strpos((string) $row['notes'], $map::ATTEMPT.':') !== false) {
        $alreadyBound[] = $id;

        continue;
    }

    $entry = $targets[$id];
    if (trim((string) $row['current_evidence_ids']) !== '') {
        $clearedEvidence++;
    }

    $row['coverage_status'] = 'NOT_ASSESSED';
    $row['current_evidence_ids'] = '';
    $row['notes'] = trim((string) $row['notes']).' | '.$map::ATTEMPT
        .': predicate_context='.$entry['context']
        .'; normalized_predicate='.$entry['predicate']
        .'; recorded_proof_verdict='.$entry['verdict']
        .'; verdict_basis='.$entry['basis']
        .'; returned_to=NOT_ASSESSED under '.$map::FINDING
        .' - no MD-B18-A001 pass is inherited';

    $mutated[$id] = true;
}
unset($row);

if ($alreadyBound !== []) {
    throw new RuntimeException('Already carries '.$map::ATTEMPT.' notes, refusing to double-bind: '
        .implode(', ', array_slice($alreadyBound, 0, 5)));
}
if ($seen !== $map::EXPECTED_DENOMINATOR) {
    throw new RuntimeException('Binder reached '.$seen.' of '.$map::EXPECTED_DENOMINATOR.' predicates.');
}
if (count($mutated) !== $map::EXPECTED_DENOMINATOR) {
    throw new RuntimeException('Binding must be atomic: mutated '.count($mutated).' of '
        .$map::EXPECTED_DENOMINATOR.'.');
}

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

/** Quote the way fputcsv does, so a rewritten row matches the file's own conventions. */
$csvField = static function ($value) {
    $value = (string) $value;
    if (preg_match('/[",\r\n]/', $value) === 1) {
        return '"'.str_replace('"', '""', $value).'"';
    }

    return $value;
};

$out = [$headerLine];
$changedLines = 0;
foreach ($rows as $index => $row) {
    if (! isset($mutated[$row['rule_id']])) {
        $out[] = $originalLine[$index];

        continue;
    }
    $fields = [];
    foreach ($headers as $header) {
        $fields[] = $csvField($row[$header]);
    }
    $rebuilt = implode(',', $fields);
    if ($rebuilt !== $originalLine[$index]) {
        $changedLines++;
    }
    $out[] = $rebuilt;
}

if (count($out) - 1 !== count($rows)) {
    throw new RuntimeException('Line reconstruction lost rows: '.(count($out) - 1).' of '.count($rows).'.');
}

// The count that matters is how many lines actually differ, not how many rows the binder intended
// to touch. A writer that re-quotes untouched rows reports zero semantic change and still rewrites
// the file; only this number would have caught that.
$untouchedRewritten = 0;
foreach ($rows as $index => $row) {
    if (isset($mutated[$row['rule_id']])) {
        continue;
    }
    if ($out[$index + 1] !== $originalLine[$index]) {
        $untouchedRewritten++;
    }
}

$verdicts = [];
foreach ($targets as $entry) {
    $verdicts[$entry['verdict']] = (isset($verdicts[$entry['verdict']]) ? $verdicts[$entry['verdict']] : 0) + 1;
}
ksort($verdicts);
if ($verdicts != $map::EXPECTED_VERDICTS + []) {
    $declared = $map::EXPECTED_VERDICTS;
    ksort($declared);
    if ($verdicts !== $declared) {
        throw new RuntimeException('Reviewed verdict counts drifted from the declared totals.');
    }
}

echo json_encode([
    'mode' => $apply ? 'BIND_AND_WRITE' : 'BIND_DRY_RUN',
    'status' => 'PASS',
    'stage_id' => $map::STAGE,
    'attempt_id' => $map::ATTEMPT,
    'baseline_id' => $map::BASELINE,
    'remediates' => $map::FINDING,
    'denominator' => $seen,
    'rows_mutated' => count($mutated),
    'lines_actually_changed' => $changedLines,
    'untouched_lines_rewritten' => $untouchedRewritten,
    'evidence_references_cleared' => $clearedEvidence,
    'coverage_status_after' => 'NOT_ASSESSED for all '.$seen,
    'reviewed_verdicts' => $verdicts,
    'foreign_rows_altered' => 0,
], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES).PHP_EOL;

if ($untouchedRewritten !== 0) {
    throw new RuntimeException('Refusing to write: '.$untouchedRewritten.' untouched lines would be rewritten.');
}

if (! $apply) {
    echo "DRY RUN - pass --apply to write.\n";
    exit(0);
}

file_put_contents($path, ($bom ? "\xEF\xBB\xBF" : '').implode("\n", $out)."\n");
echo 'WROTE '.$path.PHP_EOL;
