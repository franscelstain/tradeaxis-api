<?php
/**
 * Builds the field classification and the manifest of candidate-v4 of the package, and prints the package fingerprint.
 *
 * Standalone (no application code). Run AFTER author_inputs.php and reference_oracle.php:
 *
 *     php build_package.php
 *
 * Fingerprint algorithm (also implemented independently by ReplayVerificationService::fixturePackageFingerprint):
 *     sha256 over lines "<sha256 of file>  <relative path>\n", one per file of the package directory (manifest.json included), ordered by path.
 */
$pkg = dirname(__DIR__);
$expected = json_decode((string) file_get_contents($pkg.'/expected/expected_replay_result.json'), true);
$counts = json_decode((string) file_get_contents($pkg.'/expected/expected_reason_code_counts.json'), true);

// ---- field classification -------------------------------------------------------------------------------------------------
$derived = [
    'expected_config_identity' => 'sha256 of inputs/frozen_config_content.txt',
    'expected_source_context.source_timeout_seconds' => 'frozen config resolved_config.source.api.timeout_seconds',
    'expected_source_context.source_retry_max' => 'frozen config resolved_config.provider.api_retry_max',
    'expected_coverage_context.coverage_min_threshold' => 'frozen config resolved_config.coverage_gate.min_ratio',
    'expected_coverage_context.coverage_threshold_mode' => 'frozen config resolved_config.coverage_gate.threshold_mode',
    'expected_coverage_context.coverage_universe_basis' => 'frozen config resolved_config.coverage_gate.universe_basis',
    'expected_coverage_context.coverage_contract_version' => 'frozen config resolved_config.coverage_gate.contract_version',
    'expected_artifact_context.bars_batch_hash' => 'oracle: V2 bars domain hash of the frozen bar row',
    'expected_artifact_context.indicators_batch_hash' => 'oracle: V2 indicators domain hash of the frozen row',
    'expected_artifact_context.eligibility_batch_hash' => 'oracle: V2 eligibility domain hash of the frozen row',
    'expected_publication_context.factor_set_hash' => 'oracle: semantic factor-set document hash',
    'expected_lineage.bars_batch_hash' => 'oracle: V2 bars domain hash',
    'expected_lineage.indicators_batch_hash' => 'oracle: V2 indicators domain hash',
    'expected_lineage.eligibility_batch_hash' => 'oracle: V2 eligibility domain hash',
    'expected_lineage.factor_set_hash' => 'oracle: semantic factor-set document hash',
    'expected_bound_input_context.source_observation_manifest_hash' => 'oracle: observation manifest document hash',
    'expected_bound_input_context.canonical_raw_input_hash' => 'oracle: V2 bars domain hash',
    'expected_bound_input_context.temporal_identity_hash' => 'oracle: composite of the identity revision set hash',
    'expected_bound_input_context.calendar_status_hash' => 'oracle: composite of the calendar and status revision set hashes',
    'expected_bound_input_context.config_snapshot_hash' => 'sha256 of inputs/frozen_config_content.txt',
    'expected_bound_input_context.serialization_version' => 'frozen config resolved_config.governance.config_serialization_version',
    'expected_bound_input_context.event_factor_hash' => 'oracle: composite of the four semantic nested members and the contamination decision set document hash',
    'expected_bound_input_context.formula_registry_hash' => 'oracle: formula registry document over the frozen configuration versions and the frozen registry literals',
    'expected_bound_input_context.reason_registry_hash' => 'oracle: reason registry document over inputs/frozen_reason_registry.json',
    'expected_bound_input_context.read_model_version' => 'inputs/frozen_registry_literals.json read_model_version',
    'expected_bound_input_context.executable_build_identity' => 'inputs/frozen_build_identity.json build_id (frozen literal, not target-bound)',
    'expected_publication_context.publication_manifest_hash' => 'oracle: canonical publication manifest document (4e) over the frozen inputs and the values the oracle derived, including the freshness state derived from the frozen activation context (section 0)',
];
$classes = ['LITERAL_SEMANTIC_EXPECTATION' => [], 'DERIVED_FROM_FROZEN_INPUT' => [], 'TARGET_BOUND_OPERATIONAL' => []];
$targetBound = [];
$walk = function ($node, string $path) use (&$walk, &$classes, &$targetBound, $derived) {
    if (is_array($node) && $node !== [] && array_keys($node) !== range(0, count($node) - 1)) {
        foreach ($node as $key => $child) {
            $walk($child, $path === '' ? $key : $path.'.'.$key);
        }

        return;
    }
    if ($path === 'comparison_note') {
        return;
    }
    if (is_string($node) && strpos($node, '@TARGET:') === 0) {
        $classes['TARGET_BOUND_OPERATIONAL'][$path] = substr($node, 8);
        $targetBound[$path] = substr($node, 8);
    } elseif (isset($derived[$path])) {
        $classes['DERIVED_FROM_FROZEN_INPUT'][$path] = $derived[$path];
    } else {
        $classes['LITERAL_SEMANTIC_EXPECTATION'][$path] = true;
    }
};
$walk($expected, '');
$classes['LITERAL_SEMANTIC_EXPECTATION'] = array_keys($classes['LITERAL_SEMANTIC_EXPECTATION']);
$elevenBoundInputs = ['source_observation_manifest_hash', 'canonical_raw_input_hash', 'temporal_identity_hash', 'calendar_status_hash', 'event_factor_hash', 'config_snapshot_hash',
    'formula_registry_hash', 'reason_registry_hash', 'read_model_version', 'serialization_version', 'executable_build_identity'];
foreach ($elevenBoundInputs as $field) {
    $value = $expected['expected_bound_input_context'][$field] ?? null;
    if (! is_string($value) || $value === '' || strpos($value, '@TARGET:') === 0) {
        fwrite(STDERR, "bound input {$field} is not a literal
");
        exit(2);
    }
}
$classification = [
    'counts' => array_map('count', $classes) + ['ASSERTED_LITERAL_BOUND_INPUTS' => count($elevenBoundInputs), 'REASON_CODE_COUNT_ROWS' => count($counts)],
    'LITERAL_SEMANTIC_EXPECTATION' => $classes['LITERAL_SEMANTIC_EXPECTATION'],
    'DERIVED_FROM_FROZEN_INPUT' => $classes['DERIVED_FROM_FROZEN_INPUT'],
    'TARGET_BOUND_OPERATIONAL' => $classes['TARGET_BOUND_OPERATIONAL'],
    'ASSERTED_LITERAL_BOUND_INPUTS' => $elevenBoundInputs,
    'reason_code_counts' => 'LITERAL_SEMANTIC_EXPECTATION: expected/expected_reason_code_counts.json',
    'vocabulary_note' => 'Labels such as CURRENT_READABLE_PUBLICATION_AUDIT, MIRROR_VALID, API_FREE, FAIL_CLOSED_BOARD_UNKNOWN or temporal_provider_mapping_v1 are not defined by any authority text; they are the implementation vocabulary. What is independent is the choice of which label applies to the frozen scenario.',
];
file_put_contents(__DIR__.'/field_classification.json', json_encode($classification, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES)."\n");

// ---- manifest ------------------------------------------------------------------------------------------------------------
$world = json_decode((string) file_get_contents($pkg.'/inputs/synthetic_world.json'), true);
$files = [];
$iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($pkg, FilesystemIterator::SKIP_DOTS));
foreach ($iterator as $file) {
    if ($file->isFile()) {
        $relative = substr(str_replace('\\', '/', $file->getPathname()), strlen(str_replace('\\', '/', $pkg)) + 1);
        if ($relative !== 'manifest.json') {
            $files[$relative] = hash_file('sha256', $file->getPathname());
        }
    }
}
ksort($files, SORT_STRING);
$manifest = [
    'fixture_id' => 'r0025-synthetic-v2-exact-publication',
    'fixture_family' => 'independent_golden_synthetic_v2',
    'fixture_version' => 'candidate-4',
    'fixture_schema_version' => 'replay_fixture_v2',
    'fixture_created_at' => (new DateTimeImmutable('now', new DateTimeZone('Asia/Jakarta')))->format(DATE_ATOM),
    'fixture_source' => 'independent_derivation:r0025-synthetic-v2-reference-oracle@4.0.0',
    'version' => 'candidate-4',
    'supersedes_candidate' => ['version' => 'candidate-3', 'path' => 'tests/fixtures/replay/r0025-synthetic-v2-candidate-v3', 'fingerprint' => '8a218f5befc0b1c6f278d20ee86a798ca29185ad4522e8b8a00ee069cec9b85e',
        'status' => 'independent review returned CHANGES REQUIRED (freshness_state NOT_AVAILABLE not independently justified, F-MD-B18-A002-032; recorded E-MD-B18-A002-097); never approved, retained untouched',
        'earlier' => ['version' => 'candidate-2', 'path' => 'tests/fixtures/replay/r0025-synthetic-v2-candidate-v2', 'fingerprint' => 'bbd8c73953eb1397a49ba651e8b66b5b6cf914dd0790142d2a706bb8a49edb9f', 'status' => 'reviewed CHANGES REQUIRED (E-MD-B18-A002-095), never approved',
            'earlier' => ['version' => 'candidate-1', 'path' => 'tests/fixtures/replay/r0025-synthetic-v2-candidate-v1', 'fingerprint' => '05b717c63ef1f5f96a759ed6d2160b46e4eb9d1c9c946e2a03d373229abf26c0', 'status' => 'reviewed CHANGES REQUIRED, never approved']]],
    'fixture_status' => 'CANDIDATE_AWAITING_INDEPENDENT_REVIEW',
    'synthetic' => true,
    'replay_mode' => 'PUBLICATION_EXACT',
    'contract_areas' => ['replay_verification', 'exact_publication_verification', 'artifact_hash_determinism', 'retained_identity', 'frozen_inputs'],
    'files' => array_keys(array_filter($files, static function ($sha, $path) { return strpos($path, 'expected/') === 0 || strpos($path, 'inputs/') === 0; }, ARRAY_FILTER_USE_BOTH)),
    // Only locked values (Fixture_Package_Manifest_LOCKED.md, "Assertion layer values": row, run, hash, publication, replay). The package asserts run-level
    // state and counts (run), content hashes and identities (hash), the publication, pointer, seal, fallback and lineage state with the publication manifest
    // hash (publication) and the replay result (replay). It asserts no row-level value directly, so `row` is not declared.
    'assertion_layers' => ['run', 'hash', 'publication', 'replay'],
    'target_bound_fields' => $targetBound,
    'asserted_literal_bound_inputs' => $elevenBoundInputs,
    'independent_provenance' => [
        'world_label' => $world['label'],
        'world_version' => $world['world_version'],
        'oracle_id' => 'r0025-synthetic-v2-reference-oracle',
        'oracle_version' => '4.0.0',
        'oracle_source' => 'derivation/reference_oracle.php',
        'derivation_reference' => 'derivation/oracle_output.json',
        'derivation_notes' => 'derivation/DERIVATION.md',
        'frozen_input_identity' => ['inputs/synthetic_world.json' => $files['inputs/synthetic_world.json'], 'inputs/provider_response.json' => $files['inputs/provider_response.json'],
            'inputs/foundation_registry.json' => $files['inputs/foundation_registry.json'], 'inputs/frozen_config_content.txt' => $files['inputs/frozen_config_content.txt'],
            'inputs/frozen_reason_registry.json' => $files['inputs/frozen_reason_registry.json'], 'inputs/frozen_registry_literals.json' => $files['inputs/frozen_registry_literals.json'],
            'inputs/frozen_build_identity.json' => $files['inputs/frozen_build_identity.json'], 'inputs/frozen_manifest_member_representation.json' => $files['inputs/frozen_manifest_member_representation.json'], 'inputs/frozen_build_manifest.txt' => $files['inputs/frozen_build_manifest.txt']],
        'expected_content_identity' => ['expected/expected_replay_result.json' => $files['expected/expected_replay_result.json'], 'expected/expected_reason_code_counts.json' => $files['expected/expected_reason_code_counts.json'],
            'expected/expected_publication_manifest.json' => $files['expected/expected_publication_manifest.json']],
        'authority_basis' => ['D-MD-B18-A002-011', 'D-MD-B18-A002-013', 'RUNTIME_ARTIFACT_AND_GOVERNED_EVIDENCE_STANDARD.md section 6A', 'Fixture_Package_Manifest_LOCKED.md', 'Golden_Fixtures_Specification.md',
            'Audit_Hash_and_Reproducibility_Contract_LOCKED.md', 'Hash_Number_Formatting_LOCKED.md', 'E-MD-B10-A002-010 semantic_hash_contract', 'D-MD-B18-A002-014', 'D-MD-B18-A002-015', 'DOC-CHG-20261005-001', 'Downstream_Data_Readiness_Guarantee_LOCKED.md', 'E-MD-B18-A002-093', 'E-MD-B18-A002-095', 'E-MD-B18-A002-097', 'E-MD-B18-A002-098', 'Publication_Manifest_Contract_LOCKED.md', 'Replay_Verification_Contract_LOCKED.md'],
        'author' => 'the fixture-authoring coding Agent (not the reviewer)',
        'independent_reviewer' => null,
        'owner_approval' => null,
        'review_note' => 'This is candidate-v4, the successor of candidate-v3, candidate-v2 and candidate-v1, all reviewed CHANGES REQUIRED. No review or approval of this package exists. The approval is a governed record outside this package that binds the package fingerprint; it is not part of the manifest.',
    ],
    'files_sha256' => $files,
];
file_put_contents($pkg.'/manifest.json', json_encode($manifest, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES)."\n");

// ---- fingerprint ---------------------------------------------------------------------------------------------------------
$all = $files + ['manifest.json' => hash_file('sha256', $pkg.'/manifest.json')];
ksort($all, SORT_STRING);
$lines = '';
foreach ($all as $relative => $sha) {
    $lines .= $sha.'  '.$relative."\n";
}
$fingerprint = hash('sha256', $lines);
file_put_contents($pkg.'/../r0025-synthetic-v2-candidate-v4.fingerprint.txt', $fingerprint."\n");
echo "files: ".count($all)."\nmanifest sha256: ".$all['manifest.json']."\npackage fingerprint: ".$fingerprint."\n";
echo json_encode($classification['counts']), "\n";
