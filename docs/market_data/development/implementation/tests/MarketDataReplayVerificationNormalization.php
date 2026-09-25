<?php
require_once __DIR__.'/MarketDataReplayVerificationTraceabilitySpec.php';

// D001/D002 correct ownership and the complete parent, not the required predicates' meaning.
// E005 is false-condition proof only. Never derive N/A from capability names or a missing test.
// E008 re-executes E005's proof after the R0056 guard changed one tested source; it supersedes
// E005 for execution identity only, and every check below applies to it unchanged.
$root = dirname(__DIR__, 5);
$path = $root.'/docs/market_data/authority/governance/STRATEGY_TO_IMPLEMENTATION_TRACEABILITY_MATRIX.csv';
$all = [];
$admissionParent = [];
$rerunConfig = [];
$errors = [];
$h = fopen($path, 'rb');
if (! $h) { throw new RuntimeException('TRACEABILITY_MATRIX_UNREADABLE'); }
$header = fgetcsv($h);
$header[0] = preg_replace('/^\xEF\xBB\xBF/', '', $header[0]);
while (($v = fgetcsv($h)) !== false) {
    if (count($v) !== count($header)) { $errors[] = 'MALFORMED_MATRIX_ROW'; continue; }
    $row = array_combine($header, $v);
    if (in_array($row['rule_id'], array_map(fn ($i) => sprintf('MD-S020-R%04d', $i), range(8, 16)), true)) { $admissionParent[$row['rule_id']] = $row; }
    if ($row['rule_id'] === 'MD-S065-R0003') { $rerunConfig = $row; }
    if ($row['active'] === 'YES' && $row['primary_stage'] === 'MD-B18') { $all[] = $row; }
}
fclose($h);
// D005 Q5 assigns admission to B22; retain every child of the frozen parent.
// This guard checks ownership/context only, never readiness satisfaction.
$admissionOwners = [8 => 'MD-B01', 9 => 'MD-B12', 10 => 'MD-B07', 11 => 'MD-B05',
    12 => 'MD-B15', 13 => 'MD-B14', 14 => 'MD-B22', 15 => 'MD-B17', 16 => 'MD-B01'];
if (count($admissionParent) !== 9) { $errors[] = 'ADMISSION_PARENT_POPULATION_MISMATCH'; }
foreach ($admissionOwners as $number => $owner) {
    $id = sprintf('MD-S020-R%04d', $number);
    $row = $admissionParent[$id] ?? [];
    $applicability = in_array($number, [8, 16], true) ? 'REFERENCE_ONLY' : 'MANDATORY';
    if (($row['active'] ?? '') !== 'YES' || ($row['primary_stage'] ?? '') !== $owner
        || ($row['applicability'] ?? '') !== $applicability) {
        $errors[] = 'ADMISSION_PARENT_MEMBER_INVALID:'.$id;
    }
}
$admission = $admissionParent['MD-S020-R0014'] ?? [];
if (($admission['supporting_stages'] ?? '') !== 'MD-B18;MD-B17'
    || strpos($admission['notes'] ?? '', 'current_ownership_decision=D-MD-B18-A002-005;') === false
    || strpos($admission['notes'] ?? '', 'predicate_context=MD-S020-R0008;') === false
    || strpos($admission['notes'] ?? '', 'normalized_predicate=Market-data documentation, implementation, and operational readiness are admitted only from market-data evidence establishing immutable publication, lineage, reproducibility, and replay;') === false) {
    $errors[] = 'ADMISSION_OWNERSHIP_CONTEXT_INVALID:MD-S020-R0014';
}
if (! is_file($root.'/docs/market_data/records/decisions/D-MD-B18-A002-005_APPROVED_Q1_Q6_BOUNDED_REMEDIATION.md')) {
    $errors[] = 'ADMISSION_OWNERSHIP_DECISION_MISSING';
}
// D-MD-B18-A002-010 (D2) moves MD-S065-R0003 primary to B21 with B18/B04 support. It stays a
// required, unproven obligation: ownership/context only, never readiness satisfaction.
if (($rerunConfig['active'] ?? '') !== 'YES' || ($rerunConfig['primary_stage'] ?? '') !== 'MD-B21'
    || ($rerunConfig['supporting_stages'] ?? '') !== 'MD-B18;MD-B04'
    || ($rerunConfig['coverage_requirement'] ?? '') !== 'REQUIRED' || ($rerunConfig['applicability'] ?? '') !== 'MANDATORY'
    || ($rerunConfig['coverage_status'] ?? '') !== 'NOT_ASSESSED' || ($rerunConfig['current_evidence_ids'] ?? 'x') !== ''
    || strpos($rerunConfig['notes'] ?? '', 'current_ownership_decision=D-MD-B18-A002-010;') === false
    || strpos($rerunConfig['notes'] ?? '', 'proof_owner_confirmed=MD-B21;') === false
    || strpos($rerunConfig['notes'] ?? '', 'predicate_context=MD-S065-R0001; normalized_predicate=Any output-affecting config change must be treated as a contract change. reruns must use the registry version effective for the requested trade date or explicitly documented override. Default:') === false) {
    $errors[] = 'RERUN_CONFIG_OWNERSHIP_CONTEXT_INVALID:MD-S065-R0003';
}
if (! is_file($root.'/docs/market_data/records/decisions/D-MD-B18-A002-010_APPROVED_F017_R0003_OVERRIDE_MECHANISM_A1_C1_D2_AND_B21_OWNERSHIP.md')) {
    $errors[] = 'RERUN_CONFIG_OWNERSHIP_DECISION_MISSING';
}
$counts = array_count_values(array_column($all, 'applicability'));
$expected = ['MANDATORY' => 113, 'CONDITIONAL_NOT_APPLICABLE' => 4, 'REFERENCE_ONLY' => 33, 'OPTIONAL_CAPABILITY' => 2];
foreach ($expected as $class => $count) {
    if (($counts[$class] ?? 0) !== $count) { $errors[] = 'POPULATION_MISMATCH:'.$class; }
}
foreach ($counts as $class => $count) {
    if (! isset($expected[$class])) { $errors[] = 'UNEXPECTED_APPLICABILITY:'.$class; }
}
if (count($all) !== 152) { $errors[] = 'STAGE_POPULATION_MISMATCH'; }
$required = MarketDataReplayVerificationTraceabilitySpec::required($root);
if (count($required) !== MarketDataReplayVerificationTraceabilitySpec::EXPECTED_DENOMINATOR) {
    $errors[] = 'DENOMINATOR_MISMATCH:'.count($required);
}

$siblings = ['MD-S050-R0038', 'MD-S050-R0039', 'MD-S050-R0040', 'MD-S050-R0041'];
$evidenceId = 'E-MD-B18-A002-008';
$evidenceFile = 'E-MD-B18-A002-008_FALSE_CONDITION_REEXECUTION_AFTER_R0056_GUARD.json';
$found = [];
foreach ($all as $row) {
    if (! in_array($row['rule_id'], $siblings, true)) { continue; }
    $found[] = $row['rule_id'];
    if ($row['applicability'] !== 'CONDITIONAL_NOT_APPLICABLE'
        || $row['coverage_status'] !== 'NOT_APPLICABLE'
        || $row['current_evidence_ids'] !== $evidenceId
        || strpos($row['notes'], 'predicate_context=MD-S050-R0037;') === false
        || strpos($row['notes'], 'normalized_predicate=Where as-known replay is not implemented:') === false
        || strpos($row['notes'], 'current_applicability_decision=D-MD-B18-A002-002;') === false) {
        $errors[] = 'FALSE_CONDITION_BINDING_INVALID:'.$row['rule_id'];
    }
}
sort($found);
if ($found !== $siblings) { $errors[] = 'WHOLE_PARENT_POPULATION_MISMATCH'; }

$evidencePath = $root.'/docs/market_data/records/evidence/'.$evidenceFile;
$e = is_file($evidencePath) ? json_decode((string) file_get_contents($evidencePath), true) : null;
foreach (['evidence_id' => $evidenceId, 'stage_id' => 'MD-B18', 'attempt_id' => 'MD-B18-A002',
    'baseline_id' => 'MD-B18-A002-BL001', 'verification_epoch' => 'MD-REBASELINE-20260820-001',
    'decision_id' => 'D-MD-B18-A002-002', 'verdict' => 'NOT_APPLICABLE', 'condition_proof_result' => 'PASS'] as $key => $value) {
    if (($e[$key] ?? null) !== $value) { $errors[] = 'CONDITION_EVIDENCE_IDENTITY_INVALID:'.$key; }
}
if (($e['source']['condition_observed'] ?? null) !== false
    || ($e['source']['child_population'] ?? null) !== 4
    || array_column($e['applicability_proof'] ?? [], 'rule_id') !== $siblings
    || ($e['controls']['mutation']['landed_count'] ?? null) !== 1
    || ($e['controls']['mutation']['caught'] ?? null) !== true) {
    $errors[] = 'FALSE_CONDITION_PROOF_INCOMPLETE';
}
$links = $e['raw_artifacts'] ?? [];
if (count($links) !== 4) { $errors[] = 'CONDITION_ARTIFACT_POPULATION_MISMATCH'; }
foreach ($links as $link) {
    $file = $root.'/'.($link['path'] ?? '');
    if (! is_file($file) || ! hash_equals(strtolower($link['sha256'] ?? ''), hash_file('sha256', $file))) {
        $errors[] = 'CONDITION_ARTIFACT_INVALID:'.($link['path'] ?? '');
    }
}
$sources = $e['tested_source_sha256'] ?? [];
if (count($sources) !== 7) { $errors[] = 'CONDITION_SOURCE_POPULATION_MISMATCH'; }
foreach ($sources as $file => $hash) {
    if (! is_file($root.'/'.$file) || ! hash_equals(strtolower($hash), hash_file('sha256', $root.'/'.$file))) {
        $errors[] = 'CONDITION_EXECUTION_STALE:'.$file;
    }
}
$source = $e['source'] ?? [];
if (! is_file($root.'/'.($source['path'] ?? ''))
    || ! hash_equals(strtolower($source['sha256'] ?? ''), hash_file('sha256', $root.'/'.$source['path']))) {
    $errors[] = 'CONDITION_AUTHORITY_IDENTITY_INVALID';
}
$result = ['gate' => 'MarketDataReplayVerificationNormalization', 'stage_id' => 'MD-B18',
    'status' => $errors ? 'FAIL' : 'PASS', 'counts' => $counts, 'denominator' => count($required),
    'conditional_na_evidence' => $evidenceId, 'whole_parent_population' => count($found),
    'admission_parent_population' => count($admissionParent), 'admission_primary_owner' => $admission['primary_stage'] ?? null,
    'rerun_config_primary_owner' => $rerunConfig['primary_stage'] ?? null,
    'errors' => $errors, 'generated_at' => date(DATE_ATOM)];
echo json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES).PHP_EOL;
exit($result['status'] === 'PASS' ? 0 : 1);
