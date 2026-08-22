<?php

require_once __DIR__.'/MarketDataConfigFoundationTraceabilityGate.php';

/** Exact MD-B04-A002 proof binding and implementation-surface gate. */
final class MarketDataConfigFoundationProofGate
{
    public const EVIDENCE = 'E-MD-B04-A002-001';
    public const RESOLVED_RULE = 'MD-S082-R0062';

    /** @return array<string,array{surfaces:array<int,string>,methods:array<int,array{0:string,1:string}>}> */
    public static function proofMap(): array
    {
        $hash = [
            'surfaces' => [
                'app/Application/MarketData/Services/DeterministicHashService.php',
                'app/Application/MarketData/Services/MarketDataPipelineService.php',
                'app/Infrastructure/Persistence/MarketData/EodPublicationRepository.php',
            ],
            'methods' => [
                ['tests/Unit/MarketData/DeterministicHashServiceTest.php', 'test_input_order_does_not_change_hash'],
                ['tests/Unit/MarketData/DeterministicHashServiceTest.php', 'test_null_uses_the_contractually_locked_empty_token'],
                ['tests/Unit/MarketData/DeterministicHashServiceTest.php', 'test_non_empty_config_cannot_override_the_canonical_null_token'],
                ['tests/Unit/MarketData/DeterministicHashServiceTest.php', 'test_locked_number_formats_keep_trailing_zeroes_and_never_use_scientific_notation'],
                ['tests/Unit/MarketData/DeterministicHashServiceTest.php', 'test_json_objects_and_sets_are_canonicalized_before_hashing'],
                ['tests/Unit/MarketData/DeterministicHashServiceTest.php', 'test_locked_hash_controls_fail_closed_instead_of_silently_falling_back'],
                ['tests/Unit/MarketData/DeterministicHashServiceTest.php', 'test_hash_output_is_lowercase_sha256_and_text_must_be_utf8'],
                ['tests/Unit/MarketData/DeterministicHashServiceTest.php', 'test_decimal_normalization_uses_decimal_text_and_locked_rounding_without_binary_float_drift'],
                ['tests/Unit/MarketData/DeterministicHashServiceTest.php', 'test_dates_timestamps_and_embedded_content_hashes_fail_closed_on_noncanonical_values'],
                ['tests/Unit/MarketData/HashSealDatasetIntegrityStaticGuardTest.php', 'test_hash_metadata_cannot_drift_from_the_canonical_serializer_null_token'],
            ],
        ];
        $config = [
            'surfaces' => [
                'app/Infrastructure/Persistence/MarketData/MarketDataConfigSnapshotRepository.php',
                'app/Infrastructure/MarketData/Config/PlatformConfigRegistry.php',
                'app/Domain/MarketData/MarketDataSemanticBindings.php',
                'config/market_data.php',
            ],
            'methods' => [
                ['tests/Unit/MarketData/ConfigIdentityBindingTest.php', 'test_a_created_run_receives_non_null_config_identity'],
                ['tests/Unit/MarketData/ConfigIdentityBindingTest.php', 'test_the_bound_snapshot_carries_complete_provenance'],
                ['tests/Unit/MarketData/ConfigIdentityBindingTest.php', 'test_compiled_semantic_versions_and_feature_state_are_inside_the_snapshot_identity'],
                ['tests/Unit/MarketData/ConfigIdentityBindingTest.php', 'test_one_semantic_config_change_produces_a_different_identity'],
                ['tests/Unit/MarketData/ConfigIdentityBindingTest.php', 'test_an_unchanged_config_reuses_its_identity'],
                ['tests/Unit/MarketData/ConfigIdentityBindingTest.php', 'test_secret_material_is_redacted_and_the_redaction_is_visible'],
                ['tests/Unit/MarketData/PlatformConfigRegistryConformanceTest.php', 'test_current_runtime_configuration_exactly_matches_the_locked_resolved_key_register'],
                ['tests/Unit/MarketData/PlatformConfigRegistryConformanceTest.php', 'test_an_unregistered_runtime_key_blocks_snapshot_creation'],
                ['tests/Unit/MarketData/PlatformConfigRegistryConformanceTest.php', 'test_a_missing_registered_runtime_key_blocks_snapshot_creation'],
                ['tests/Unit/MarketData/PlatformConfigRegistryConformanceTest.php', 'test_a_registered_key_with_the_wrong_type_blocks_snapshot_creation'],
                ['tests/Unit/MarketData/PlatformConfigRegistryConformanceTest.php', 'test_a_non_empty_hash_null_token_blocks_snapshot_creation'],
                ['tests/Unit/MarketData/PlatformConfigRegistryConformanceTest.php', 'test_registry_metadata_cannot_reintroduce_the_removed_environment_override'],
                ['tests/Unit/MarketData/AsKnownWiringAndConfigIdentityTest.php', 'test_a_promote_run_is_bound_to_a_config_snapshot'],
            ],
        ];
        $reason = [
            'surfaces' => [
                'docs/market_data/development/implementation/db/registry/Reason_Codes_Seed.sql',
                'app/Application/MarketData',
                'app/Infrastructure/Persistence/MarketData',
            ],
            'methods' => [
                ['tests/Unit/MarketData/ReasonCodeSeedExecutionTest.php', 'test_the_seed_statement_actually_executes'],
                ['tests/Unit/MarketData/ReasonCodeSeedExecutionTest.php', 'test_every_registry_code_reaches_the_table'],
                ['tests/Unit/MarketData/ReasonCodeSeedExecutionTest.php', 'test_severity_values_stay_inside_the_locked_vocabulary'],
                ['tests/Unit/MarketData/EmittedReasonCodeRegistrationTest.php', 'test_every_reason_code_emitted_by_the_runtime_is_registered'],
                ['tests/Unit/MarketData/LoggingTraceabilityReasonCodesStaticGuardTest.php', 'test_reason_code_registry_and_seed_are_synchronized'],
            ],
        ];

        $map = [];
        foreach (MarketDataConfigFoundationTraceabilitySpec::requiredOwners() as $document => $owners) {
            foreach ($owners as $number => $owner) {
                if ($owner !== 'MD-B04') {
                    continue;
                }
                $rule = MarketDataConfigFoundationTraceabilitySpec::ruleId($document, $number);
                $map[$rule] = in_array($document, ['MD-S005', 'MD-S019', 'MD-S034'], true)
                    ? $hash
                    : ($document === 'MD-S085' ? $reason : $config);
            }
        }

        foreach (['MD-S082-R0037', 'MD-S082-R0104', 'MD-S082-R0105'] as $rule) {
            $map[$rule]['surfaces'][] = 'app/Application/MarketData/Services/CoverageGateEvaluator.php';
            $map[$rule]['methods'][] = ['tests/Unit/MarketData/CoverageDormantUniverseTest.php', 'test_a_dormant_ticker_stays_in_the_denominator'];
            $map[$rule]['methods'][] = ['tests/Unit/MarketData/CoverageDormantUniverseTest.php', 'test_a_universe_of_dormant_tickers_reports_low_coverage_not_perfect_coverage'];
        }
        $map['MD-S082-R0038']['methods'][] = ['tests/Unit/MarketData/TerminologyOwnerVocabularyTest.php', 'test_no_config_key_carries_watchlist_policy_vocabulary'];
        $map['MD-S082-R0065']['methods'][] = ['tests/Unit/MarketData/PublishedColumnHashCoverageTest.php', 'test_the_hash_covers_every_published_column_of_the_current_table'];
        $map['MD-S082-R0089']['methods'][] = ['tests/Unit/MarketData/ConfigEnvGovernanceCleanupStaticGuardTest.php', 'test_legacy_price_basis_selector_is_pruned_not_left_as_active_config'];

        ksort($map, SORT_STRING);

        return $map;
    }

    /** @param array<int,array<string,string>> $rows */
    public static function validate(array $rows, string $root, array $map = null): array
    {
        $map = $map === null ? self::proofMap() : $map;
        $errors = [];
        $required = [];
        $counts = ['denominator' => 0, 'satisfied' => 0, 'blocked' => 0];
        foreach ($rows as $row) {
            if ($row['primary_stage'] !== 'MD-B04' || $row['coverage_requirement'] !== 'REQUIRED') {
                continue;
            }
            $required[$row['rule_id']] = $row;
        }
        ksort($required, SORT_STRING);
        if (array_keys($required) !== array_keys($map)) {
            $errors[] = 'PROOF_MAP: map must exactly cover the current MD-B04 denominator';
        }

        foreach ($map as $rule => $proof) {
            if (! isset($required[$rule])) {
                $errors[] = $rule.': proof map names a non-denominator rule';
                continue;
            }
            $row = $required[$rule];
            $counts['denominator']++;
            $counts['satisfied']++;
            if ($row['coverage_status'] !== 'SATISFIED' || $row['current_evidence_ids'] !== self::EVIDENCE) {
                $errors[] = $rule.': current A002 proof binding is not exact';
            }
            if ($rule === self::RESOLVED_RULE
                && (strpos($row['notes'], 'D-MD-20260822-06') === false
                    || strpos($row['notes'], 'MD-DEP-0007=RESOLVED') === false)) {
                $errors[] = $rule.': resolved authority decision/dependency lineage is missing';
            }

            foreach ($proof['surfaces'] as $surface) {
                if (! file_exists($root.'/'.$surface)) {
                    $errors[] = $rule.': proof surface is missing '.$surface;
                }
            }
            foreach ($proof['methods'] as $method) {
                $source = @file_get_contents($root.'/'.$method[0]);
                if ($source === false || strpos($source, 'function '.$method[1].'(') === false) {
                    $errors[] = $rule.': proof method does not exist '.$method[0].'::'.$method[1];
                }
            }
        }

        $evidencePath = $root.'/docs/market_data/records/evidence/'.self::EVIDENCE.'_CONFIG_FOUNDATION_CLOSURE_PROOF.json';
        $evidence = file_exists($evidencePath) ? json_decode(file_get_contents($evidencePath), true) : null;
        if (! is_array($evidence)
            || ($evidence['evidence_id'] ?? null) !== self::EVIDENCE
            || ($evidence['baseline_id'] ?? null) !== 'MD-B04-A002-BL001'
            || ($evidence['change_impact_declaration'] ?? null) !== 'CI-MD-B04-A002-001'
            || ($evidence['decision_id'] ?? null) !== 'D-MD-20260822-06') {
            $errors[] = 'EVIDENCE: issued proof record is missing, malformed, or miscorrelated';
        }
        if ($counts !== ['denominator' => 114, 'satisfied' => 114, 'blocked' => 0]) {
            $errors[] = 'COUNTS: expected exact A002 closure proof at 114/114';
        }

        return ['errors' => $errors, 'counts' => $counts, 'status' => $errors === [] ? 'PASS' : 'FAIL'];
    }
}

if (realpath($_SERVER['SCRIPT_FILENAME'] ?? '') === __FILE__) {
    $root = dirname(__DIR__, 5);
    $matrix = $root.'/docs/market_data/authority/governance/STRATEGY_TO_IMPLEMENTATION_TRACEABILITY_MATRIX.csv';
    $rows = MarketDataClassificationConsistencyGate::readMatrix($matrix)['rows'];
    $result = MarketDataConfigFoundationProofGate::validate($rows, $root);
    $result['gate'] = 'MarketDataConfigFoundationProofGate';
    echo json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES).PHP_EOL;
    exit($result['status'] === 'PASS' ? 0 : 1);
}
