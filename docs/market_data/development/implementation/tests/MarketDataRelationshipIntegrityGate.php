<?php
/**
 * PHP 7.3+; current relationship integrity gate.
 *
 * `WORK_CORRELATION_AND_RECORD_REGISTRY_STANDARD.md` section 3 defines relationship integrity as
 * two properties, not one:
 *
 *   1. validity     — every row references registered records and uses an allowed relationship;
 *   2. completeness — every relationship required by current records is present as a row.
 *
 * The previous version checked validity only. It returned PASS against an empty registry, which the
 * standard now names explicitly as a result the gate MUST NOT produce: "A relationship gate MUST NOT
 * return PASS merely because the registry contains zero rows or because all existing rows are
 * valid."
 *
 * Completeness is derived from the canonical relationship-bearing columns of
 * `WORK_RECORD_REGISTRY.csv` plus the predecessor declarations carried inside baseline-lock records.
 * A relationship is required when it crosses an attempt, stage, or baseline boundary.
 */

function rows($p)
{
    $o = [];
    if (! is_file($p)) {
        return $o;
    }
    $f = fopen($p, 'r');
    $h = fgetcsv($f);
    if (isset($h[0])) {
        $h[0] = preg_replace('/^\xEF\xBB\xBF/', '', $h[0]);
    }
    while (($r = fgetcsv($f)) !== false) {
        if (! is_array($h) || count($r) < 1) {
            continue;
        }
        $a = [];
        foreach ($h as $i => $k) {
            $a[$k] = isset($r[$i]) ? trim($r[$i]) : '';
        }
        $o[] = $a;
    }
    fclose($f);

    return $o;
}

/** Correlation identity of a record id: the MD-Bxx-Ayyy it belongs to, or '' when pre-attempt. */
function correlationOf($id)
{
    if (preg_match('/^(?:[A-Z]{1,3}-)?MD-(B\d{2}-A\d{3})/', $id, $m)) {
        return 'MD-'.$m[1];
    }

    return '';
}

$md = realpath(dirname(__DIR__, 3));
$records = rows($md.'/records/WORK_RECORD_REGISTRY.csv');
$relationships = rows($md.'/records/WORK_RELATIONSHIP_REGISTRY.csv');
$dependencies = rows($md.'/development/implementation/MD_DEPENDENCY_REGISTRY.csv');

$errors = [];
$missing = [];

// ---------------------------------------------------------------- validity
$byId = [];
$attemptIdentity = [];
foreach ($records as $r) {
    $id = $r['record_id'];
    if ($id === '') {
        continue;
    }
    if (isset($byId[$id])) {
        $errors[] = 'duplicate record '.$id;
    }
    $byId[$id] = $r;

    if ($r['attempt_id'] !== '') {
        if ($r['work_id'] !== $r['attempt_id']) {
            $errors[] = 'work identity mismatch '.$id;
        }
        if (! preg_match('/^MD-B\d{2}-A\d{3}$/', $r['attempt_id'])) {
            $errors[] = 'bad attempt '.$id;
        }
        if ($r['baseline_id'] !== '' && strpos($r['baseline_id'], $r['attempt_id']) !== 0) {
            $errors[] = 'baseline does not belong to its attempt '.$id;
        }
        $key = [$r['stage_id'], $r['baseline_id']];
        if (isset($attemptIdentity[$r['attempt_id']]) && $attemptIdentity[$r['attempt_id']] !== $key) {
            $errors[] = 'attempt identity split '.$r['attempt_id'];
        }
        $attemptIdentity[$r['attempt_id']] = $key;
    }
    if ($r['document_path'] !== '' && ! is_file($md.'/'.$r['document_path'])) {
        $errors[] = 'record document missing '.$id;
    }
}

$allowedTypes = ['CARRIED_FROM', 'DEPENDS_ON', 'REMEDIATES', 'SUPERSEDES', 'DERIVED_FROM', 'RELATED', 'RETURNS_TO'];
$relIds = [];
$edge = [];
foreach ($relationships as $r) {
    $rid = $r['relationship_id'];
    if (isset($relIds[$rid])) {
        $errors[] = 'duplicate relationship '.$rid;
    }
    $relIds[$rid] = 1;

    if (! isset($byId[$r['source_record_id']]) || ! isset($byId[$r['target_record_id']])) {
        $errors[] = 'unresolved relationship '.$rid;
        continue;
    }
    if ($r['source_record_id'] === $r['target_record_id']) {
        $errors[] = 'self relationship '.$rid;
    }
    if (! in_array($r['relationship_type'], $allowedTypes, true)) {
        $errors[] = 'disallowed relationship type '.$rid.' ('.$r['relationship_type'].')';
    }
    if (trim($r['justification']) === '') {
        $errors[] = 'relationship without justification '.$rid;
    }
    $edge[$r['source_record_id'].'>'.$r['target_record_id']] = $r['relationship_type'];
}

// no SUPERSEDES cycle
foreach ($relationships as $r) {
    if ($r['relationship_type'] !== 'SUPERSEDES') {
        continue;
    }
    if (isset($edge[$r['target_record_id'].'>'.$r['source_record_id']])
        && $edge[$r['target_record_id'].'>'.$r['source_record_id']] === 'SUPERSEDES') {
        $errors[] = 'supersession cycle '.$r['relationship_id'];
    }
}

// ---------------------------------------------------------------- completeness
$relationshipColumns = [
    'related_findings' => 'DEPENDS_ON',
    'related_evidence' => 'CARRIED_FROM',
    'related_decisions' => 'DEPENDS_ON',
    'supersedes' => 'SUPERSEDES',
];

foreach ($records as $r) {
    $source = $r['record_id'];
    $sourceCorrelation = correlationOf($source);

    foreach ($relationshipColumns as $column => $expected) {
        if (! isset($r[$column]) || trim($r[$column]) === '') {
            continue;
        }
        foreach (array_filter(array_map('trim', explode(';', $r[$column]))) as $target) {
            if ($target === '' || $target === $source) {
                continue;
            }
            if (! isset($byId[$target])) {
                $missing[] = [
                    'source' => $source,
                    'target' => $target,
                    'column' => $column,
                    'reason' => 'declared target is not a registered work record',
                ];
                continue;
            }
            // required only when the relationship crosses a correlation boundary
            if (correlationOf($target) === $sourceCorrelation && $sourceCorrelation !== '') {
                continue;
            }
            if (! isset($edge[$source.'>'.$target])) {
                $missing[] = [
                    'source' => $source,
                    'target' => $target,
                    'column' => $column,
                    'expected_type' => $expected,
                    'reason' => 'cross-correlation relationship declared by a current record but absent from WORK_RELATIONSHIP_REGISTRY',
                ];
            }
        }
    }

    // dependency references must resolve against the dependency registry
    if (isset($r['related_dependencies']) && trim($r['related_dependencies']) !== '') {
        $known = array_column($dependencies, 'dependency_id');
        foreach (array_filter(array_map('trim', explode(';', $r['related_dependencies']))) as $dep) {
            if (! in_array($dep, $known, true)) {
                $errors[] = 'record '.$source.' references unknown dependency '.$dep;
            }
        }
    }
}

// baseline predecessor chains declared inside the lock records themselves
foreach ($records as $r) {
    if ($r['record_type'] !== 'BASELINE_LOCK' || $r['document_path'] === '') {
        continue;
    }
    $lock = json_decode((string) file_get_contents($md.'/'.$r['document_path']), true);
    if (! is_array($lock)) {
        continue;
    }
    foreach (['predecessor_attempt', 'predecessor_stage'] as $key) {
        if (! isset($lock[$key]['baseline_id'])) {
            continue;
        }
        $target = $lock[$key]['baseline_id'];
        if ($target === '' || $target === $r['record_id']) {
            continue;
        }
        if (! isset($byId[$target])) {
            $missing[] = ['source' => $r['record_id'], 'target' => $target, 'column' => $key,
                'reason' => 'baseline lock declares a predecessor baseline that is not a registered work record'];
            continue;
        }
        if (! isset($edge[$r['record_id'].'>'.$target])) {
            $missing[] = ['source' => $r['record_id'], 'target' => $target, 'column' => $key,
                'expected_type' => 'CARRIED_FROM',
                'reason' => 'baseline lock declares a predecessor baseline but no relationship row carries it forward'];
        }
    }
}

// ---------------------------------------------------------------- verdict
$status = ($errors || $missing) ? 'FAIL' : 'PASS';

$out = [
    'gate' => 'MarketDataRelationshipIntegrityGate',
    'status' => $status,
    'records' => count($records),
    'relationships' => count($relationships),
    'validity_errors' => $errors,
    'completeness_gaps' => $missing,
    'checks' => [
        'validity' => $errors ? 'FAIL' : 'PASS',
        'completeness' => $missing ? 'FAIL' : 'PASS',
    ],
    'note' => 'Completeness is derived from the canonical relationship-bearing columns of WORK_RECORD_REGISTRY.csv and from predecessor declarations inside baseline-lock records. An empty relationship registry cannot pass while any current record declares a cross-correlation relationship.',
    'generated_at' => date(DATE_ATOM),
];

file_put_contents(
    $md.'/records/evidence/MD_RELATIONSHIP_INTEGRITY_GATE_LATEST.json',
    json_encode($out, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES).PHP_EOL
);
echo json_encode($out, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES).PHP_EOL;
exit($status === 'FAIL' ? 1 : 0);
