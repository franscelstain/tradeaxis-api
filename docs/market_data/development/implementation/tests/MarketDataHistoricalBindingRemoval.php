<?php

/**
 * PHP 7.3+; governed `MD-B03-A002` removal of executable test bindings to `HISTORICAL_ONLY`
 * documents.
 *
 * `D-MD-20260820-02` decomposed 26 composite audit documents into role-pure extracts and removed
 * the physical originals. 24 test files still assert against the removed paths.
 * `CURRENT_VERIFICATION_REBASELINE_STANDARD.md` gives pre-epoch statements zero current-verification
 * effect, and `F-MD-B00-A001-001` rules that repointing these tests at the extracts would smuggle
 * inherited PASS into the current epoch. So the assertions cannot be repaired by repointing; they
 * are removed.
 *
 * Removal is per test method, never per file by default. A file may hold a dead binding in one
 * method and a live invariant in the next — `TestingDatabaseIsolationStaticGuardTest` fails one of
 * six tests and the other five guard real bootstrap and artisan behaviour. Taking the file would
 * take those too.
 *
 * A file is retired only when every one of its test methods is in the removal set.
 *
 * Private helpers left unreferenced by the removal are deleted with it; a helper kept alive only by
 * a method that no longer exists is dead code, which the residue standard treats as removable
 * residue rather than as harmless.
 *
 * Usage:
 *   (no flags)   dry run: report what would be removed
 *   --apply      perform the removal
 */
final class MarketDataHistoricalBindingRemoval
{
    /**
     * Test methods whose assertions resolve only through a document removed by `D-MD-20260820-02`.
     *
     * The set is the exact failing population measured at `MD-B03-A002-BL001`: 71 methods across 24
     * files, every one failing on a missing `docs/market_data/**` path. It is written out rather
     * than rediscovered so the removal is reviewable against the baseline measurement.
     *
     * @return array<string,array<int,string>>
     */
    public static function removalSet(): array
    {
        return [
            'AuditCrossReferenceIntegrityTest' => [
                'test_both_documents_name_the_same_current_canonical_override',
                'test_both_documents_parse_into_a_meaningful_number_of_entries',
                'test_contract_names_are_unique',
                'test_every_related_contract_resolves_to_a_tracked_contract',
                'test_every_related_implementation_resolves_to_a_recorded_entry',
                'test_every_tracked_contract_names_at_least_one_implementation',
            ],
            'AuditDocsSynchronizationStaticGuardTest' => [
                'test_archived_source_state_evidence_states_a_single_settled_decision',
                'test_audit_governance_enforces_append_only_anti_duplication_and_static_guard',
                'test_current_canonical_overrides_are_aligned_and_precede_history',
                'test_locked_contracts_have_concrete_validation_evidence',
                'test_revised_stage_four_onward_sequence_is_independent_and_keeps_activation_outside_build',
                'test_stage_five_is_fail_closed_complete_and_cannot_be_reopened_by_history',
                'test_stage_four_records_only_the_owner_raw_decision_and_keeps_implementation_open',
                'test_stage_seven_closure_is_record_only_source_backed_and_history_clean',
                'test_stage_six_records_only_declared_authoritative_terms_and_leaves_application_open',
                'test_stage_three_closes_only_write_guards_and_keeps_corpus_findings_open',
                'test_stage_two_f045_cannot_regress_to_active_or_ambiguous_audit_state',
            ],
            'CommandSurfaceSafetyStaticGuardTest' => [
                'test_all_registered_market_data_commands_are_in_ops_safety_inventory',
            ],
            'ConfigEnvGovernanceCleanupStaticGuardTest' => [
                'test_audit_docs_preserve_config_env_cleanup_history_without_requiring_it_as_active_session',
                'test_cleanup_does_not_regress_source_mode_read_side_or_db_integrity_contracts',
                'test_inventory_records_schema_config_env_pruning_and_validation_status',
            ],
            'CoverageGateCandidateScopeHardeningStaticGuardTest' => [
                'test_audit_docs_record_candidate_scope_hardening_without_replacing_existing_coverage_contract',
            ],
            'CoveragePolicyDocsStaticGuardTest' => [
                'test_active_test_matrix_uses_not_evaluable_instead_of_coverage_blocked',
            ],
            'DbIntegrityConstraintEnforcementStaticGuardTest' => [
                'test_db_integrity_inventory_is_present_and_contract_mapped',
            ],
            'DbIntegrityFkImplicitIntegrityDecisionStaticGuardTest' => [
                'test_audit_docs_record_locked_decision_after_operator_local_runtime_proof',
                'test_decision_inventory_locks_hybrid_fk_vs_implicit_policy_without_schema_sync_false_claim',
            ],
            'EvidenceHistoricalLineageCompletenessStaticGuardTest' => [
                'test_historical_evidence_static_inventory_and_audit_docs_are_present',
            ],
            'GlobalConvergenceClosureTest' => [
                'test_every_unwritten_semantic_field_has_a_recorded_reason',
            ],
            'LoggingTraceabilityReasonCodesStaticGuardTest' => [
                'test_traceability_inventory_exists_and_covers_critical_areas',
            ],
            'MarketDataConsumerReadModelStaticGuardTest' => [
                'test_docs_lock_consumer_read_model_scope_without_strategy_claims',
            ],
            'OperationalReadinessStaticGuardTest' => [
                'test_all_three_operator_documents_describe_the_same_command_surface',
                'test_audit_docs_reference_operational_readiness_contract',
            ],
            'OpsCommandSurfaceRuntimeMatrixStaticGuardTest' => [
                'test_inventory_closes_fixture_limited_state_changing_cases',
                'test_inventory_lists_all_public_market_data_commands',
                'test_inventory_records_help_invalid_and_seeded_runtime_proof',
                'test_runtime_matrix_inventory_records_locked_decision',
            ],
            'OpsEnvironmentBaselineStaticGuardTest' => [
                'test_audit_docs_active_session_and_contract_are_synchronized',
                'test_clean_output_policy_forbids_hiding_the_noise_instead_of_fixing_it',
                'test_composer_platform_decision_is_documented_without_lock_drift_patch',
                'test_extensions_required_by_the_baseline_are_actually_loaded',
                'test_inventory_leaves_no_undecided_rows',
                'test_ops_environment_baseline_document_exists_and_locks_clean_output_policy',
            ],
            'ProductionSchedulerCronStaticGuardTest' => [
                'test_audit_docs_record_scheduler_cron_deployment_proof',
                'test_scheduler_config_and_env_surface_are_documented',
                'test_scheduler_runtime_proof_claim_is_backed_by_current_artifacts',
            ],
            'ProductionValidationRuntimeProofStaticGuardTest' => [
                'test_audit_docs_do_not_claim_locked_without_runtime_evidence',
                'test_every_artisan_command_named_in_the_inventory_is_registered',
                'test_failure_and_correction_runtime_proof_is_recorded',
                'test_manual_validation_commands_have_expected_output_and_pass_fail_criteria',
                'test_production_validation_contract_is_tracked',
                'test_production_validation_inventory_exists',
                'test_replay_fixture_generation_is_documented_and_guarded',
                'test_replay_runtime_persistence_fix_is_documented_and_guarded',
                'test_runtime_parity_provider_statuses_are_reason_coded_and_no_false_pass_is_allowed',
                'test_validation_inventory_lists_required_phpunit_commands',
                'test_validation_inventory_requires_evidence_export_runtime_proof',
                'test_validation_inventory_requires_replay_runtime_proof',
                'test_validation_inventory_requires_runtime_evidence_before_done',
                'test_validation_inventory_tracks_missing_runtime_proof_as_pending',
            ],
            'ProviderSmokeSafeModeStaticGuardTest' => [
                'test_provider_smoke_artifact_and_docs_are_tracked_without_false_pass',
            ],
            'ReadSideConsumerSurfaceFinalSweepStaticGuardTest' => [
                'test_audit_docs_track_final_sweep_without_new_contract',
                'test_final_sweep_inventory_exists_and_maps_to_existing_read_side_contract',
                'test_producer_and_diagnostic_paths_are_classified_in_inventory_not_as_consumer_bypass',
                'test_runtime_environment_baseline_is_recorded_in_always_read_audit_materials',
            ],
            'ReplayHistoricalDeterminismHardeningStaticGuardTest' => [
                'test_replay_historical_inventory_and_audit_docs_are_present',
            ],
            'RunPublicationPointerLinkageStaticGuardTest' => [
                'test_linkage_inventory_covers_every_stage_of_the_run_publication_chain',
            ],
            'StageEightReconstructionStaticGuardTest' => [
                'test_completed_stage_eight_is_backed_by_admission_and_zero_violation_evidence',
            ],
            'TestCoverageBehavioralStaticGuardTest' => [
                'test_behavioral_guard_keeps_static_checks_as_support_not_runtime_replacement',
                'test_behavioral_inventory_documents_all_critical_market_data_areas_and_mock_policy',
                'test_command_surface_mock_heavy_tests_are_explicitly_not_counted_as_lifecycle_proof',
            ],
            'TestingDatabaseIsolationStaticGuardTest' => [
                'test_audit_docs_record_testing_database_isolation_contract',
            ],
        ];
    }

    /** Locate a method body by brace balance and return [startOffset, endOffset] or null. */
    public static function methodSpan(string $source, string $name): ?array
    {
        if (! preg_match('/\n[ \t]*(?:public|private|protected)?[ \t]*function[ \t]+'.preg_quote($name, '/').'[ \t]*\(/', $source, $m, PREG_OFFSET_CAPTURE)) {
            return null;
        }
        $start = $m[0][1];

        // Absorb an immediately preceding docblock so the comment does not outlive its method.
        //
        // This walks backwards line by line. A regex anchored with `$` and `/s` looked equivalent
        // and was not: `(?:(?!\*\/).)*` is greedy, so it began at the first docblock in the file
        // and swallowed everything up to the method — which deleted a class's closing brace twice
        // before the cause was found. Scanning lines cannot reach past the block it is reading.
        $lineStart = strrpos(substr($source, 0, $start + 1), "\n");
        $before = substr($source, 0, $start);
        $lines = explode("\n", $before);
        $cursor = count($lines) - 1;
        if ($cursor >= 0 && rtrim($lines[$cursor]) === '') {
            // the match began at the newline preceding the signature
        }
        if ($cursor >= 0 && preg_match('/^\s*\*\/\s*$/', $lines[$cursor])) {
            $scan = $cursor;
            while ($scan >= 0 && ! preg_match('/^\s*\/\*\*/', $lines[$scan])) {
                $scan--;
            }
            if ($scan >= 0) {
                $start = strlen(implode("\n", array_slice($lines, 0, $scan)));
            }
        }
        unset($lineStart);

        $brace = strpos($source, '{', $start);
        if ($brace === false) {
            return null;
        }
        $depth = 0;
        $length = strlen($source);
        for ($i = $brace; $i < $length; $i++) {
            $ch = $source[$i];
            if ($ch === '{') {
                $depth++;
            } elseif ($ch === '}') {
                $depth--;
                if ($depth === 0) {
                    return [$start, $i + 1];
                }
            }
        }

        return null;
    }

    /** Private helpers no longer referenced anywhere in the file. */
    public static function orphanedHelpers(string $source): array
    {
        preg_match_all('/private function ([A-Za-z0-9_]+)\s*\(/', $source, $m);
        $orphans = [];
        foreach ($m[1] as $helper) {
            if (substr_count($source, $helper) <= 1) {
                $orphans[] = $helper;
            }
        }

        return $orphans;
    }

    public static function testMethodsIn(string $source): array
    {
        preg_match_all('/public function (test_[A-Za-z0-9_]+)\s*\(/', $source, $m);

        return $m[1];
    }
}

if (realpath($_SERVER['SCRIPT_FILENAME'] ?? '') === __FILE__) {
    $root = realpath(dirname(__DIR__, 5));
    $apply = in_array('--apply', $argv, true);
    $removed = 0;
    $retired = [];
    $edited = [];
    $errors = [];
    $reportedOrphans = [];

    foreach (MarketDataHistoricalBindingRemoval::removalSet() as $class => $methods) {
        $path = $root.'/tests/Unit/MarketData/'.$class.'.php';
        if (! is_file($path)) {
            $errors[] = $class.': file not found';
            continue;
        }
        $source = (string) file_get_contents($path);
        $all = MarketDataHistoricalBindingRemoval::testMethodsIn($source);
        $survivors = array_values(array_diff($all, $methods));

        foreach ($methods as $method) {
            if (! in_array($method, $all, true)) {
                $errors[] = $class.'::'.$method.': not present in the file';
            }
        }

        if ($survivors === []) {
            $retired[] = $class;
            $removed += count($methods);
            if ($apply) {
                unlink($path);
            }
            continue;
        }

        foreach ($methods as $method) {
            $span = MarketDataHistoricalBindingRemoval::methodSpan($source, $method);
            if ($span === null) {
                $errors[] = $class.'::'.$method.': span not resolvable';
                continue;
            }
            $source = substr($source, 0, $span[0]).substr($source, $span[1]);
            $removed++;
        }

        // Orphaned private helpers are reported, never auto-deleted. An earlier version removed
        // them automatically and emptied a whole class: brace-balanced excision is safe for a
        // method whose span is known, and unsafe as a repeated rewrite over shifting offsets. Dead
        // private helpers in a test file are inert; a silently emptied guard file is not.
        $orphans = MarketDataHistoricalBindingRemoval::orphanedHelpers($source);
        if ($orphans !== []) {
            $reportedOrphans[$class] = $orphans;
        }

        $edited[] = $class.' (-'.count($methods).', '.count($survivors).' kept)';
        if ($apply) {
            file_put_contents($path, $source);
        }
    }

    echo json_encode([
        'tool' => 'MarketDataHistoricalBindingRemoval',
        'mode' => $apply ? 'APPLY' : 'DRY_RUN',
        'methods_removed' => $removed,
        'files_retired' => $retired,
        'files_edited' => $edited,
        'orphaned_helpers' => $reportedOrphans,
        'errors' => $errors,
    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES).PHP_EOL;
    exit($errors === [] ? 0 : 1);
}
