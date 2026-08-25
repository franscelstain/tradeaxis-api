<?php
require_once __DIR__.'/MarketDataPublicationLifecycleProofGate.php';

$root = dirname(__DIR__, 5);
$rows = MarketDataPublicationLifecycleTraceabilitySpec::rows($root);
$entries = MarketDataPublicationLifecycleProofSpec::entries($root);
$families = MarketDataPublicationLifecycleProofSpec::families();
$checks = [];

$mandatoryCurrent = MarketDataPublicationLifecycleTraceabilitySpec::mandatory($root);
$boundControl = $mandatoryCurrent !== [];
foreach ($mandatoryCurrent as $row) {
    if (($row['coverage_status'] ?? '') !== 'SATISFIED'
        || ! preg_match('/^E-MD-B10-A001-\d{3}$/', trim((string) ($row['current_evidence_ids'] ?? '')))) {
        $boundControl = false;
        break;
    }
}
$control = MarketDataPublicationLifecycleProofGate::validate($root, $boundControl);
$checks[$boundControl ? 'control_bound' : 'control_prebinding'] = $control['status'] === 'PASS';

function mdB10FailsClosed(string $root, array $overrides, bool $bound = false): bool
{
    return MarketDataPublicationLifecycleProofGate::validate($root, $bound, $overrides)['status'] === 'FAIL';
}

// 1. One mandatory predicate disappears from the proof map.
$m = $entries;
array_shift($m);
$checks['missing_mandatory_predicate_binding'] = mdB10FailsClosed($root, ['entries' => $m], $boundControl);

// 2. Duplicate semantic proof binding.
$m = $entries;
$m[] = $m[0];
$checks['duplicate_predicate_binding'] = mdB10FailsClosed($root, ['entries' => $m], $boundControl);

// 3. A family is attributed to a non-B10 proof owner.
$f = $families;
$f['manifest']['owner'] = 'MD-B09:wrong-owner';
$checks['wrong_proof_owner'] = mdB10FailsClosed($root, ['families' => $f], $boundControl);

// 4. Referenced positive test no longer exists.
$f = $families;
$f['manifest']['positive'] = ['tests/Unit/MarketData/DOES_NOT_EXIST.php', 'test_missing'];
$checks['missing_test_reference'] = mdB10FailsClosed($root, ['families' => $f], $boundControl);

// 5. Runtime-dependent predicate is promoted without returned/governed evidence.
$r = $rows;
foreach ($r as &$row) {
    if (($row['active'] ?? '') === 'YES' && ($row['primary_stage'] ?? '') === 'MD-B10'
        && ($row['coverage_requirement'] ?? '') === 'REQUIRED' && ($row['applicability'] ?? '') === 'MANDATORY') {
        $row['coverage_status'] = 'SATISFIED';
        $row['current_evidence_ids'] = '';
        break;
    }
}
unset($row);
$checks['runtime_promoted_without_evidence'] = mdB10FailsClosed($root, ['rows' => $r], $boundControl);

function mdB10SyntheticBoundRows(array $rows, string $evidenceId): array
{
    foreach ($rows as &$row) {
        if (($row['active'] ?? '') === 'YES' && ($row['primary_stage'] ?? '') === 'MD-B10'
            && ($row['coverage_requirement'] ?? '') === 'REQUIRED' && ($row['applicability'] ?? '') === 'MANDATORY') {
            $row['coverage_status'] = 'SATISFIED';
            $row['current_evidence_ids'] = $evidenceId;
        }
    }
    unset($row);
    return $rows;
}

function mdB10GoodEvidence(string $evidenceId): array
{
    return [
        'evidence_id' => $evidenceId,
        'stage_id' => 'MD-B10',
        'attempt_id' => 'MD-B10-A001',
        'baseline_id' => 'MD-B10-A001-BL001',
        'change_impact_declaration' => 'CI-MD-B10-A001-001',
        'mutability' => 'IMMUTABLE_AFTER_ISSUE',
        'proof_admission' => ['status' => 'PASS', 'mandatory_satisfied' => 1072],
    ];
}

function mdB10GoodRelationships(string $evidenceId): array
{
    return [
        ['source_record_id' => $evidenceId, 'target_record_id' => 'MD-B10-A001-BL001', 'relationship_type' => 'DEPENDS_ON'],
        ['source_record_id' => $evidenceId, 'target_record_id' => 'CI-MD-B10-A001-001', 'relationship_type' => 'DEPENDS_ON'],
    ];
}

// 6. Evidence identity is malformed.
$badId = 'BAD-EVIDENCE';
$checks['malformed_evidence_identity'] = mdB10FailsClosed($root, [
    'rows' => mdB10SyntheticBoundRows($rows, $badId),
    'evidence_payload' => mdB10GoodEvidence($badId),
    'relationships' => mdB10GoodRelationships($badId),
], true);

// 7. Evidence is stale/wrong attempt despite a syntactically valid ID.
$eid = 'E-MD-B10-A001-999';
$stale = mdB10GoodEvidence($eid);
$stale['attempt_id'] = 'MD-B10-A000';
$checks['stale_evidence_scope'] = mdB10FailsClosed($root, [
    'rows' => mdB10SyntheticBoundRows($rows, $eid),
    'evidence_payload' => $stale,
    'relationships' => mdB10GoodRelationships($eid),
], true);

// 8. Current evidence is missing a required relationship to the CI.
$checks['invalid_evidence_relationship'] = mdB10FailsClosed($root, [
    'rows' => mdB10SyntheticBoundRows($rows, $eid),
    'evidence_payload' => mdB10GoodEvidence($eid),
    'relationships' => [
        ['source_record_id' => $eid, 'target_record_id' => 'MD-B10-A001-BL001', 'relationship_type' => 'DEPENDS_ON'],
    ],
], true);

// 9. Denominator is silently reduced.
$r = $rows;
foreach ($r as &$row) {
    if (($row['active'] ?? '') === 'YES' && ($row['primary_stage'] ?? '') === 'MD-B10'
        && ($row['coverage_requirement'] ?? '') === 'REQUIRED' && ($row['applicability'] ?? '') === 'MANDATORY') {
        $row['active'] = 'NO';
        break;
    }
}
unset($row);
$checks['denominator_mismatch'] = mdB10FailsClosed($root, ['rows' => $r], $boundControl);

// 10. A reference/context row is improperly promoted into the mandatory denominator.
$r = $rows;
foreach ($r as &$row) {
    if (($row['active'] ?? '') === 'YES' && ($row['primary_stage'] ?? '') === 'MD-B10'
        && ($row['coverage_requirement'] ?? '') === 'REFERENCE_ONLY') {
        $row['coverage_requirement'] = 'REQUIRED';
        $row['applicability'] = 'MANDATORY';
        $row['coverage_status'] = 'NOT_ASSESSED';
        $row['current_evidence_ids'] = '';
        break;
    }
}
unset($row);
$checks['reference_promoted_to_mandatory'] = mdB10FailsClosed($root, ['rows' => $r], $boundControl);

// 11. A mandatory row is incorrectly moved to reference-only.
$r = $rows;
foreach ($r as &$row) {
    if (($row['active'] ?? '') === 'YES' && ($row['primary_stage'] ?? '') === 'MD-B10'
        && ($row['coverage_requirement'] ?? '') === 'REQUIRED' && ($row['applicability'] ?? '') === 'MANDATORY') {
        $row['coverage_requirement'] = 'REFERENCE_ONLY';
        break;
    }
}
unset($row);
$checks['mandatory_moved_to_reference'] = mdB10FailsClosed($root, ['rows' => $r], $boundControl);

// 12. Manifest contract is remapped away from the dedicated manifest proof family.
$m = $entries;
foreach ($m as &$entry) {
    if (($entry['strategy_document_id'] ?? '') === 'MD-S045') {
        $entry['family'] = 'platform_boundary';
        break;
    }
}
unset($entry);
$checks['manifest_proof_mapping_removed'] = mdB10FailsClosed($root, ['entries' => $m], $boundControl);

// 13. Direct-DB immutable-history runtime probe is removed from the required family.
$f = $families;
$f['history_versioning']['runtime_scripts'] = [];
$checks['direct_db_immutability_proof_removed'] = mdB10FailsClosed($root, ['families' => $f], $boundControl);

// 14. Projection reconciliation contract is remapped away from its independent verifier.
$m = $entries;
foreach ($m as &$entry) {
    if (($entry['strategy_document_id'] ?? '') === 'MD-S046') {
        $entry['family'] = 'pointer_integrity';
        break;
    }
}
unset($entry);
$checks['reconciliation_proof_mapping_removed'] = mdB10FailsClosed($root, ['entries' => $m], $boundControl);

$failed = [];
foreach ($checks as $name => $passed) {
    if (! $passed) {
        $failed[] = $name;
    }
}

$result = [
    'status' => $failed === [] ? 'PASS' : 'FAIL',
    'total' => count($checks),
    'controls' => 1,
    'fail_closed_mutations' => count($checks) - 1,
    'checks' => $checks,
    'failed_checks' => $failed,
];

echo json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES).PHP_EOL;
exit($result['status'] === 'PASS' ? 0 : 1);
