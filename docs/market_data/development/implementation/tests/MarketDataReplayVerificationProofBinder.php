<?php
require_once __DIR__.'/MarketDataReplayVerificationProofGate.php';
require_once __DIR__.'/MarketDataReplayVerificationProofBasis.php';

/**
 * Governed `MD-B18` proof binder.
 *
 *   --validate-only                      report the pre-bind state and exit
 *   --evidence-id=E-MD-B18-A002-NNN      bind every required predicate to that evidence
 *   --apply                              write the matrix; without it nothing is persisted
 *
 * The earlier version of this file wrote the matrix the moment it was invoked, with no dry run, no
 * check that the rows it was about to overwrite were pristine, no assertion that it had reached the
 * expected 121, and no check that rows outside `MD-B18` were untouched. It also recorded only the
 * evidence id, so a bound row said which evidence proved it but not which guard did. `MD-B07-A002`
 * cleared the proof state of thirty predicates owned by other stages while every gate stayed green;
 * that is what the isolation check below exists to prevent.
 *
 * The binding is atomic by construction. Any predicate that is not pristine stops the whole run
 * before a byte is written, because a partial binding leaves the stage claiming a coverage figure
 * that no single execution established.
 *
 * `MD-B18-A002` adds the constraint the withdrawal was about. The `MD-B18-A001` binder recorded
 * the **family's** guard pair against every predicate in that family -- eleven pairs for 121
 * predicates -- which is what `F-MD-B19-A001-002` measured as not establishing the members. This
 * binder reads `MarketDataReplayVerificationProofBasis`, refuses any predicate that carries no
 * reviewed basis, and writes that predicate's **own** guard pair. A predicate with no basis is
 * not bound and stops the run, so the matrix cannot record a coverage figure the proof basis
 * does not support.
 */
$root = dirname(__DIR__, 5);
$spec = 'MarketDataReplayVerificationProofSpec';
$gate = 'MarketDataReplayVerificationProofGate';

$validateOnly = in_array('--validate-only', $argv, true);
$apply = in_array('--apply', $argv, true);

$evidence = null;
foreach ($argv as $argument) {
    if (strpos($argument, '--evidence-id=') === 0) {
        $evidence = substr($argument, strlen('--evidence-id='));
    }
}

if ($validateOnly) {
    $result = $gate::validate($root, false);

    // The gate validates the family map, which is complete by construction. It says nothing
    // about whether each predicate has a reviewed basis, and reporting PASS on that alone is
    // the misleading green this mode exists to avoid: the caller runs --validate-only first
    // and would read it as "ready to bind".
    $withoutBasis = MarketDataReplayVerificationProofBasis::outstanding();
    $errors = $result['errors'];
    $status = $result['status'];
    if ($withoutBasis !== []) {
        $status = 'BLOCKED';
        $errors[] = 'B18_PREDICATES_WITHOUT_REVIEWED_BASIS: '.count($withoutBasis).' of '
            .$spec::EXPECTED_DENOMINATOR.' - '.implode(', ', $withoutBasis);
    }

    echo json_encode([
        'mode' => 'VALIDATE_ONLY',
        'status' => $status,
        'attempt_id' => $spec::ATTEMPT,
        'predicates_with_reviewed_basis' => count(MarketDataReplayVerificationProofBasis::PROVEN),
        'predicates_without_reviewed_basis' => $withoutBasis,
        'denominator' => $result['denominator'],
        'proof_map_count' => $result['proof_map_count'],
        'proof_families_used' => $result['proof_families_used'],
        'reviewed_map_size' => $result['reviewed_map_size'],
        'runtime_pending' => $result['runtime_pending'],
        'errors' => $errors,
    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES).PHP_EOL;
    exit($status === 'PASS' ? 0 : 1);
}

if ($evidence === null || preg_match($gate::EVIDENCE_PATTERN, $evidence) !== 1) {
    throw new RuntimeException('Use --validate-only, or --evidence-id=E-MD-B18-A002-NNN [--apply].');
}

$matches = glob($root.'/docs/market_data/records/evidence/'.$evidence.'_*');
if (count($matches) !== 1) {
    throw new RuntimeException('Exactly one governed MD-B18 evidence file must exist for '.$evidence
        .', found '.count($matches).'.');
}
$payload = json_decode((string) file_get_contents($matches[0]), true);
if (! is_array($payload)
    || (isset($payload['attempt_id']) ? $payload['attempt_id'] : '') !== $spec::ATTEMPT
    || (isset($payload['verdict']) ? $payload['verdict'] : '') !== 'PASS') {
    throw new RuntimeException('B18_RUNTIME_EVIDENCE_NOT_ADMISSIBLE: '.$evidence
        .' must be '.$spec::ATTEMPT.' evidence carrying verdict PASS.');
}

$pre = $gate::validate($root, false);
if ($pre['status'] !== 'PASS') {
    throw new RuntimeException('B18_PREBIND_GATE_FAILED: '.implode('; ', $pre['errors']));
}

$path = $root.'/docs/market_data/authority/governance/STRATEGY_TO_IMPLEMENTATION_TRACEABILITY_MATRIX.csv';

/**
 * Read line by line and keep every original line.
 *
 * An earlier version read with fgetcsv and rewrote the whole file with fputcsv. It reported zero
 * foreign rows altered, and it was right about that -- no field of any other row changed meaning --
 * but fputcsv re-quotes, so the write touched 6226 rows it did not own at the byte level. A mass
 * rewrite of the authority matrix is not something a binder may do as a side effect of binding 121
 * predicates, and a semantic isolation check cannot see it. Untouched lines are now written back
 * exactly as they were read, so the diff is the binding and nothing else.
 */
$raw = file($path, FILE_IGNORE_NEW_LINES);
if ($raw === false || $raw === []) {
    throw new RuntimeException('Cannot open the traceability matrix.');
}
$headerLine = array_shift($raw);
$headers = str_getcsv($headerLine);
$bom = strpos($headers[0], chr(0xEF).chr(0xBB).chr(0xBF)) === 0;
$headers[0] = str_replace(chr(0xEF).chr(0xBB).chr(0xBF), '', $headers[0]);

$rows = [];
$originalLine = [];
$lineOfRow = [];
foreach ($raw as $index => $line) {
    if (trim($line) === '') {
        continue;
    }
    $values = str_getcsv($line);
    if (count($values) !== count($headers)) {
        // A field carrying an embedded newline would land here. The matrix has none, and the binder
        // refuses rather than guessing, because a line-based writer cannot be trusted over one.
        throw new RuntimeException('Matrix line '.($index + 2).' does not parse to '
            .count($headers).' fields; a line-based binding is unsafe on this file.');
    }
    $row = array_combine($headers, $values);
    $rows[] = $row;
    $originalLine[count($rows) - 1] = $line;
    $lineOfRow[$row['rule_id']] = count($rows) - 1;
}

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
$mutated = [];

foreach ($rows as &$row) {
    if (! isset($targets[$row['rule_id']])) {
        continue;
    }
    $seen++;

    if ($row['primary_stage'] !== $spec::STAGE
        || $row['coverage_requirement'] !== 'REQUIRED'
        || ! in_array($row['applicability'], ['MANDATORY', 'CONDITIONAL_APPLICABLE'], true)
        || strtoupper(trim($row['active'])) !== 'YES') {
        throw new RuntimeException($row['rule_id'].': cannot bind a proof to a row that is not an active MD-B18 required rule.');
    }
    if ($row['coverage_status'] !== 'NOT_ASSESSED' || trim($row['current_evidence_ids']) !== '') {
        throw new RuntimeException($row['rule_id'].': non-pristine predicate, refusing to rebind.');
    }

    $family = $families[$targets[$row['rule_id']]];

    // The predicate's own reviewed basis, not its family's. Without this the binder writes
    // eleven guard pairs across 121 rows, which is the shape `F-MD-B19-A001-002` withdrew the
    // MD-B18-A001 closure for.
    $basis = MarketDataReplayVerificationProofBasis::PROVEN[$row['rule_id']] ?? null;
    if ($basis === null) {
        throw new RuntimeException($row['rule_id'].': no reviewed per-predicate proof basis. '
            .'Binding it would record the family guard pair as though it established this '
            .'predicate. Establish the basis first, or the coverage figure is not supported.');
    }

    $row['coverage_status'] = 'SATISFIED';
    $row['current_evidence_ids'] = $evidence;
    $row['notes'] = trim($row['notes']).' | '.$spec::ATTEMPT.': proof_family='.$targets[$row['rule_id']]
        .'; positive='.$basis['positive']
        .'; negative='.$basis['negative']
        .'; implementation_surface='.implode(',', $family['implementation']);

    $bound++;
    $boundRows[] = $row;
    $mutated[$row['rule_id']] = true;
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

$check = $gate::validate($root, true, ['required' => $boundRows]);
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
    'predicates_with_reviewed_basis' => count(MarketDataReplayVerificationProofBasis::PROVEN),
    'predicates_without_reviewed_basis' => count(MarketDataReplayVerificationProofBasis::outstanding()),
    'bound_validation' => $check['status'],
], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES).PHP_EOL;

if (! $apply) {
    echo "DRY RUN - pass --apply to write.\n";
    exit(0);
}

/** Quote the way fputcsv does, so a rewritten row matches the file's own conventions. */
$csvField = static function ($value) {
    $value = (string) $value;
    if (preg_match('/[",\r\n]/', $value) === 1) {
        return '"'.str_replace('"', '""', $value).'"';
    }

    return $value;
};

$lines = [$headerLine];
foreach ($rows as $index => $row) {
    if (! isset($mutated[$row['rule_id']])) {
        $lines[] = $originalLine[$index];

        continue;
    }
    $fields = [];
    foreach ($headers as $header) {
        $fields[] = $csvField($row[$header]);
    }
    $lines[] = implode(',', $fields);
}

$rewritten = count(array_filter($lines, static function ($line) { return true; })) - 1;
if ($rewritten !== count($rows)) {
    throw new RuntimeException('Line reconstruction lost rows: '.$rewritten.' of '.count($rows).'.');
}

file_put_contents($path, implode(chr(10), $lines).chr(10));
echo 'WROTE '.$path.PHP_EOL;
