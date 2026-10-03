<?php
/**
 * Builds the field classification and the manifest of the candidate package, and prints the package fingerprint.
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
$notAsserted = [
    'event_factor_hash' => 'still binds the ancillary captures, which carry materialized rows with local ids (F-MD-B10-A002-004 residual); not asserted until that member is remediated',
    'formula_registry_hash' => 'binds the executable build and registry capture payload; not a semantic fact of this world',
    'reason_registry_hash' => 'binds the executable build and registry capture payload; not a semantic fact of this world',
    'executable_build_identity' => 'the build id of the executing code; volatile by design',
];
$classification = [
    'counts' => array_map('count', $classes) + ['NOT_ASSERTED_BOUND_INPUTS' => count($notAsserted), 'REASON_CODE_COUNT_ROWS' => count($counts)],
    'LITERAL_SEMANTIC_EXPECTATION' => $classes['LITERAL_SEMANTIC_EXPECTATION'],
    'DERIVED_FROM_FROZEN_INPUT' => $classes['DERIVED_FROM_FROZEN_INPUT'],
    'TARGET_BOUND_OPERATIONAL' => $classes['TARGET_BOUND_OPERATIONAL'],
    'NOT_ASSERTED_BOUND_INPUTS' => $notAsserted,
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
    'fixture_version' => 'candidate-1',
    'fixture_schema_version' => 'replay_fixture_v2',
    'fixture_created_at' => (new DateTimeImmutable('now', new DateTimeZone('Asia/Jakarta')))->format(DATE_ATOM),
    'fixture_source' => 'independent_derivation:r0025-synthetic-v2-reference-oracle@1.0.0',
    'version' => 'candidate-1',
    'fixture_status' => 'CANDIDATE_AWAITING_INDEPENDENT_REVIEW',
    'synthetic' => true,
    'replay_mode' => 'PUBLICATION_EXACT',
    'contract_areas' => ['replay_verification', 'exact_publication_verification', 'artifact_hash_determinism', 'retained_identity', 'frozen_inputs'],
    'files' => array_keys(array_filter($files, static function ($sha, $path) { return strpos($path, 'expected/') === 0 || strpos($path, 'inputs/') === 0; }, ARRAY_FILTER_USE_BOTH)),
    'assertion_layers' => ['run', 'source', 'coverage', 'hash', 'seal', 'publication', 'pointer', 'fallback', 'lineage', 'replay'],
    'target_bound_fields' => $targetBound,
    'not_asserted_bound_inputs' => $notAsserted,
    'independent_provenance' => [
        'world_label' => $world['label'],
        'world_version' => $world['world_version'],
        'oracle_id' => 'r0025-synthetic-v2-reference-oracle',
        'oracle_version' => '1.0.0',
        'oracle_source' => 'derivation/reference_oracle.php',
        'derivation_reference' => 'derivation/oracle_output.json',
        'derivation_notes' => 'derivation/DERIVATION.md',
        'frozen_input_identity' => ['inputs/synthetic_world.json' => $files['inputs/synthetic_world.json'], 'inputs/provider_response.json' => $files['inputs/provider_response.json'],
            'inputs/foundation_registry.json' => $files['inputs/foundation_registry.json'], 'inputs/frozen_config_content.txt' => $files['inputs/frozen_config_content.txt']],
        'expected_content_identity' => ['expected/expected_replay_result.json' => $files['expected/expected_replay_result.json'], 'expected/expected_reason_code_counts.json' => $files['expected/expected_reason_code_counts.json']],
        'authority_basis' => ['D-MD-B18-A002-011', 'D-MD-B18-A002-013', 'RUNTIME_ARTIFACT_AND_GOVERNED_EVIDENCE_STANDARD.md section 6A', 'Fixture_Package_Manifest_LOCKED.md', 'Golden_Fixtures_Specification.md',
            'Audit_Hash_and_Reproducibility_Contract_LOCKED.md', 'Hash_Number_Formatting_LOCKED.md', 'E-MD-B10-A002-010 semantic_hash_contract'],
        'author' => 'the fixture-authoring coding Agent (not the reviewer)',
        'independent_reviewer' => null,
        'owner_approval' => null,
        'review_note' => 'No review or approval exists. The approval is a governed record outside this package that binds the package fingerprint; it is not part of the manifest.',
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
file_put_contents($pkg.'/../r0025-synthetic-v2-candidate-v1.fingerprint.txt', $fingerprint."\n");
echo "files: ".count($all)."\nmanifest sha256: ".$all['manifest.json']."\npackage fingerprint: ".$fingerprint."\n";
echo json_encode($classification['counts']), "\n";
