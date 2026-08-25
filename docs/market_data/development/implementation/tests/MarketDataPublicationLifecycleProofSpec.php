<?php
require_once __DIR__.'/MarketDataPublicationLifecycleTraceabilitySpec.php';

final class MarketDataPublicationLifecycleProofSpec
{
    public const STAGE = 'MD-B10';
    public const ATTEMPT = 'MD-B10-A001';
    public const BASELINE = 'MD-B10-A001-BL001';
    public const CI = 'CI-MD-B10-A001-001';
    public const EXPECTED_DENOMINATOR = 1072;

    public static function documentFamilyMap(): array
    {
        return [
            'MD-S001' => 'platform_boundary',
            'MD-S005' => 'audit_manifest',
            'MD-S007' => 'history_versioning',
            'MD-S008' => 'canonical_snapshot',
            'MD-S013' => 'correction_safety',
            'MD-S018' => 'seal_freeze',
            'MD-S019' => 'determinism',
            'MD-S023' => 'bar_snapshot',
            'MD-S025' => 'finalization',
            'MD-S026' => 'retention_no_rewrite',
            'MD-S029' => 'resilience_publication',
            'MD-S032' => 'finalize_pointer',
            'MD-S033' => 'force_replace',
            'MD-S035' => 'historical_correction',
            'MD-S036' => 'import_promote',
            'MD-S040' => 'manual_publishability',
            'MD-S041' => 'calendar_revision_lineage',
            'MD-S043' => 'pointer_integrity',
            'MD-S044' => 'publication_lock',
            'MD-S045' => 'manifest',
            'MD-S046' => 'traceability_reconciliation',
            'MD-S047' => 'cross_consistency',
            'MD-S048' => 'publishability_integrity',
            'MD-S053' => 'source_correction_lineage',
            'MD-S054' => 'source_mapping_lineage',
            'MD-S057' => 'identity_revision_lineage',
            'MD-S058' => 'status_revision_lineage',
            'MD-S059' => 'provider_history_no_rewrite',
            'MD-S067' => 'run_status',
            'MD-S082' => 'config_binding',
            'MD-S085' => 'reason_semantics',
        ];
    }

    private static function family(
        string $owner,
        array $implementation,
        array $positive,
        array $negative,
        array $runtimeScripts = []
    ): array {
        return [
            'owner' => $owner,
            'implementation' => $implementation,
            'positive' => $positive,
            'negative' => $negative,
            'runtime_scripts' => $runtimeScripts,
            'runtime_required' => true,
        ];
    }

    public static function families(): array
    {
        $pipeline = 'app/Application/MarketData/Services/MarketDataPipelineService.php';
        $publication = 'app/Infrastructure/Persistence/MarketData/EodPublicationRepository.php';
        $artifact = 'app/Infrastructure/Persistence/MarketData/EodArtifactRepository.php';
        $hash = 'app/Application/MarketData/Services/DeterministicHashService.php';
        $reconciliation = 'app/Application/MarketData/Services/PublicationProjectionReconciliationService.php';
        $repair = 'app/Application/MarketData/Services/PublicationProjectionRepairService.php';
        $migration = 'database/migrations/2026_08_24_000001_enforce_sealed_history_and_projection_reconciliation.php';
        $probe = 'docs/market_data/development/implementation/tests/MarketDataB10DeployedSchemaProbe.php';
        $repairProbe = 'tools/market_data/MarketDataB10ProjectionRepairDeployedProbe.php';

        return [
            'platform_boundary' => self::family('MD-B10:publication-state-machine', [$pipeline, $publication],
                ['tests/Unit/MarketData/PublicationRepositoryIntegrationTest.php', 'test_candidate_seal_and_promote_updates_current_pointer_and_prior_publication'],
                ['tests/Unit/MarketData/PublicationRepositoryIntegrationTest.php', 'test_promote_candidate_to_current_blocks_uncontrolled_replace_when_valid_current_exists']),
            'audit_manifest' => self::family('MD-B10:manifest-audit', [$publication, $hash],
                ['tests/Unit/MarketData/PublicationManifestAndSealOrderingStaticGuardTest.php', 'test_manifest_semantic_payload_contains_locked_binding_families_and_avoids_volatile_execution_identity'],
                ['tests/Unit/MarketData/PublicationManifestAndSealOrderingStaticGuardTest.php', 'test_canonical_document_hash_is_order_independent_but_semantic_change_sensitive']),
            'history_versioning' => self::family('MD-B10:immutable-history', [$artifact, $publication, $migration],
                ['tests/Unit/MarketData/PublicationSealPointerLifecycleTest.php', 'test_a_superseded_publication_remains_queryable_and_unchanged'],
                ['tests/Unit/MarketData/PublicationHistoryDatabaseImmutabilityStaticGuardTest.php', 'test_forward_migration_deploys_insert_update_delete_guards_for_all_three_history_tables'],
                [$probe]),
            'canonical_snapshot' => self::family('MD-B10:canonical-publication-snapshot', [$artifact, $pipeline],
                ['tests/Unit/MarketData/PublicationSealPointerLifecycleTest.php', 'test_a_correction_produces_a_new_snapshot_set_rather_than_editing_the_old_one'],
                ['tests/Unit/MarketData/PublicationSealPointerLifecycleTest.php', 'test_a_sealed_snapshot_set_cannot_be_rewritten_by_the_artifact_writer']),
            'correction_safety' => self::family('MD-B10:correction-lifecycle', [$pipeline, $publication],
                ['tests/Unit/MarketData/MarketDataPipelineIntegrationTest.php', 'test_run_daily_correction_replaces_current_publication_and_marks_correction_published'],
                ['tests/Unit/MarketData/MarketDataPipelineIntegrationTest.php', 'test_run_daily_correction_with_reseal_failure_keeps_prior_current_and_leaves_candidate_non_current']),
            'seal_freeze' => self::family('MD-B10:seal-freeze', [$pipeline, $publication, $artifact],
                ['tests/Unit/MarketData/PublicationManifestAndSealOrderingStaticGuardTest.php', 'test_complete_seal_orders_snapshot_completeness_hash_and_manifest_before_seal'],
                ['tests/Unit/MarketData/PublicationManifestAndSealOrderingStaticGuardTest.php', 'test_manifest_hash_is_prepared_and_verified_and_partial_candidates_cannot_fake_sealed_state']),
            'determinism' => self::family('MD-B10:publication-determinism', [$hash, $publication],
                ['tests/Unit/MarketData/PublicationDiffServiceTest.php', 'test_is_unchanged_returns_true_when_all_batch_hashes_match'],
                ['tests/Unit/MarketData/PublicationDiffServiceTest.php', 'test_is_unchanged_returns_false_when_any_hash_changes']),
            'bar_snapshot' => self::family('MD-B10:bar-publication-snapshot', [$artifact, $reconciliation],
                ['tests/Unit/MarketData/PublicationProjectionReconciliationServiceTest.php', 'test_exact_projection_and_current_publication_history_reconcile_to_pass_and_persist_counts'],
                ['tests/Unit/MarketData/PublicationProjectionReconciliationServiceTest.php', 'test_value_mismatch_and_wrong_publication_binding_are_detected']),
            'finalization' => self::family('MD-B10:finalization', [$pipeline, $publication],
                ['tests/Unit/MarketData/MarketDataPipelineIntegrationTest.php', 'test_run_daily_persists_full_db_backed_pipeline_and_current_publication'],
                ['tests/Unit/MarketData/MarketDataPipelineIntegrationTest.php', 'test_run_daily_success_path_with_post_switch_resolution_mismatch_holds_and_clears_invalid_current_pointer']),
            'retention_no_rewrite' => self::family('MD-B10:history-retention', [$artifact, $migration],
                ['tests/Unit/MarketData/PublicationSealPointerLifecycleTest.php', 'test_a_superseded_publication_remains_queryable_and_unchanged'],
                ['tests/Unit/MarketData/PublicationHistoryDatabaseImmutabilityStaticGuardTest.php', 'test_guard_is_bound_to_sealed_publication_identity_and_update_checks_old_and_new_binding'],
                [$probe]),
            'resilience_publication' => self::family('MD-B10:failure-preserves-publication', [$pipeline, $publication],
                ['tests/Unit/MarketData/MarketDataPipelineIntegrationTest.php', 'test_run_daily_api_success_after_retry_exports_source_context_in_run_evidence'],
                ['tests/Unit/MarketData/MarketDataPipelineIntegrationTest.php', 'test_run_daily_correction_with_changed_artifacts_and_promotion_failure_holds_and_preserves_prior_current_publication']),
            'finalize_pointer' => self::family('MD-B10:finalize-pointer', [$publication, $pipeline],
                ['tests/Unit/MarketData/PublicationFinalizeOutcomeServiceTest.php', 'test_finalize_success_when_current_pointer_resolves_to_candidate_publication'],
                ['tests/Unit/MarketData/PublicationFinalizeOutcomeServiceTest.php', 'test_finalize_held_when_current_pointer_resolution_does_not_match_candidate']),
            'force_replace' => self::family('MD-B10:operator-force-replace', [$publication, $pipeline],
                ['tests/Unit/MarketData/MarketDataPipelineIntegrationTest.php', 'test_promote_daily_with_force_replace_switches_current_and_records_audit_event'],
                ['tests/Unit/MarketData/MarketDataPipelineIntegrationTest.php', 'test_promote_daily_without_force_replace_holds_when_valid_current_exists']),
            'historical_correction' => self::family('MD-B10:historical-correction-reseal', [$pipeline, $publication, $artifact],
                ['tests/Unit/MarketData/MarketDataPipelineIntegrationTest.php', 'test_run_daily_correction_replaces_current_publication_and_marks_correction_published'],
                ['tests/Unit/MarketData/MarketDataPipelineIntegrationTest.php', 'test_run_daily_correction_with_changed_artifacts_and_post_switch_resolution_mismatch_restores_prior_current_publication']),
            'import_promote' => self::family('MD-B10:import-promote-separation', [$pipeline, $publication],
                ['tests/Unit/MarketData/CanonicalRawImportBoundaryTest.php', 'test_import_creates_a_candidate_without_sealing_or_switching_the_pointer'],
                ['tests/Unit/MarketData/MarketDataPipelineIntegrationTest.php', 'test_manual_file_promote_from_imported_partial_dataset_enforces_coverage_gate_and_does_not_switch_pointer']),
            'manual_publishability' => self::family('MD-B10:manual-publishability', [$pipeline, $publication],
                ['tests/Unit/MarketData/MarketDataPipelineIntegrationTest.php', 'test_run_daily_full_coverage_persists_finalize_coverage_payload_and_readable_publication'],
                ['tests/Unit/MarketData/MarketDataPipelineIntegrationTest.php', 'test_run_daily_low_coverage_with_fallback_holds_requested_date_and_preserves_old_readable_publication']),
            'calendar_revision_lineage' => self::family('MD-B10:temporal-revision-lineage', [$publication, $artifact],
                ['tests/Unit/MarketData/PublicationSealPointerLifecycleTest.php', 'test_a_superseded_publication_remains_queryable_and_unchanged'],
                ['tests/Unit/MarketData/PublicationSealPointerLifecycleTest.php', 'test_a_sealed_snapshot_set_cannot_be_rewritten_by_the_artifact_writer']),
            'pointer_integrity' => self::family('MD-B10:current-pointer-integrity', [$publication],
                ['tests/Unit/MarketData/PublicationRepositoryIntegrationTest.php', 'test_candidate_seal_and_promote_updates_current_pointer_and_prior_publication'],
                ['tests/Unit/MarketData/PublicationRepositoryIntegrationTest.php', 'test_pointer_resolution_returns_null_when_pointer_publication_version_mismatches_pointed_publication']),
            'publication_lock' => self::family('MD-B10:publication-lock-replacement', [$publication, $pipeline],
                ['tests/Unit/MarketData/PublicationRepositoryIntegrationTest.php', 'test_promote_candidate_to_current_allows_operator_force_replace_when_valid_current_exists'],
                ['tests/Unit/MarketData/PublicationRepositoryIntegrationTest.php', 'test_promote_candidate_to_current_blocks_uncontrolled_replace_when_valid_current_exists']),
            'manifest' => self::family('MD-B10:publication-manifest', [$publication, $hash, $pipeline],
                ['tests/Unit/MarketData/PublicationManifestAndSealOrderingStaticGuardTest.php', 'test_manifest_semantic_payload_contains_locked_binding_families_and_avoids_volatile_execution_identity'],
                ['tests/Unit/MarketData/PublicationManifestAndSealOrderingStaticGuardTest.php', 'test_manifest_hash_is_prepared_and_verified_and_partial_candidates_cannot_fake_sealed_state']),
            'traceability_reconciliation' => self::family('MD-B10:projection-reconciliation-and-lineage', [$reconciliation, $repair, $publication, $artifact, $migration],
                ['tests/Unit/MarketData/PublicationProjectionReconciliationServiceTest.php', 'test_exact_projection_and_current_publication_history_reconcile_to_pass_and_persist_counts'],
                ['tests/Unit/MarketData/PublicationProjectionReconciliationServiceTest.php', 'test_projection_rows_without_a_resolvable_current_publication_are_persisted_as_orphans'],
                [$probe, $repairProbe]),
            'cross_consistency' => self::family('MD-B10:publishability-cross-consistency', [$pipeline, $publication],
                ['tests/Unit/MarketData/PublicationFinalizeOutcomeServiceTest.php', 'test_finalize_success_when_current_pointer_resolves_to_candidate_publication'],
                ['tests/Unit/MarketData/PublicationFinalizeOutcomeServiceTest.php', 'test_outcome_keeps_held_not_readable_when_coverage_fail_has_fallback']),
            'publishability_integrity' => self::family('MD-B10:publishability-state-integrity', [$pipeline, $publication],
                ['tests/Unit/MarketData/PublicationFinalizeOutcomeServiceTest.php', 'test_finalize_success_when_current_pointer_resolves_to_candidate_publication'],
                ['tests/Unit/MarketData/PublicationFinalizeOutcomeServiceTest.php', 'test_outcome_keeps_blocked_non_readable_and_never_promotes']),
            'source_correction_lineage' => self::family('MD-B10:source-observation-publication-lineage', [$pipeline, $publication],
                ['tests/Unit/MarketData/SourceObservationImmutabilityTest.php', 'test_identical_refetch_records_confirmation_without_a_finding'],
                ['tests/Unit/MarketData/SourceObservationImmutabilityTest.php', 'test_changed_refetch_opens_an_explicit_divergence_with_both_values_and_delta']),
            'source_mapping_lineage' => self::family('MD-B10:source-mapping-publication-lineage', [$pipeline, $publication],
                ['tests/Unit/MarketData/SourceObservationImmutabilityTest.php', 'test_a_rerun_appends_a_new_observation_and_never_overwrites'],
                ['tests/Unit/MarketData/PublicationSealPointerLifecycleTest.php', 'test_a_sealed_snapshot_set_cannot_be_rewritten_by_the_artifact_writer']),
            'identity_revision_lineage' => self::family('MD-B10:identity-publication-binding', [$publication],
                ['tests/Unit/MarketData/ConfigIdentityBindingTest.php', 'test_the_bound_snapshot_carries_complete_provenance'],
                ['tests/Unit/MarketData/PublicationSealPointerLifecycleTest.php', 'test_a_sealed_snapshot_set_cannot_be_rewritten_by_the_artifact_writer']),
            'status_revision_lineage' => self::family('MD-B10:status-revision-publication-lineage', [$publication],
                ['tests/Unit/MarketData/PublicationSealPointerLifecycleTest.php', 'test_a_correction_produces_a_new_snapshot_set_rather_than_editing_the_old_one'],
                ['tests/Unit/MarketData/PublicationSealPointerLifecycleTest.php', 'test_a_sealed_snapshot_set_cannot_be_rewritten_by_the_artifact_writer']),
            'provider_history_no_rewrite' => self::family('MD-B10:provider-history-no-rewrite', [$pipeline, $publication],
                ['tests/Unit/MarketData/SourceObservationImmutabilityTest.php', 'test_a_rerun_appends_a_new_observation_and_never_overwrites'],
                ['tests/Unit/MarketData/PublicationSealPointerLifecycleTest.php', 'test_a_sealed_snapshot_set_cannot_be_rewritten_by_the_artifact_writer']),
            'run_status' => self::family('MD-B10:publication-run-status', [$pipeline, $publication],
                ['tests/Unit/MarketData/PublicationFinalizeOutcomeServiceTest.php', 'test_finalize_success_when_current_pointer_resolves_to_candidate_publication'],
                ['tests/Unit/MarketData/PublicationFinalizeOutcomeServiceTest.php', 'test_finalize_held_when_promotion_errors_after_candidate_seal']),
            'config_binding' => self::family('MD-B10:config-publication-binding', [$publication, $pipeline],
                ['tests/Unit/MarketData/ConfigIdentityBindingTest.php', 'test_a_created_run_receives_non_null_config_identity'],
                ['tests/Unit/MarketData/ConfigIdentityBindingTest.php', 'test_one_semantic_config_change_produces_a_different_identity']),
            'reason_semantics' => self::family('MD-B10:publication-failure-reasons', [$pipeline, $publication, $artifact],
                ['tests/Unit/MarketData/MarketDataPipelineIntegrationTest.php', 'test_run_daily_full_coverage_persists_finalize_coverage_payload_and_readable_publication'],
                ['tests/Unit/MarketData/PublicationSealPointerLifecycleTest.php', 'test_sealed_publication_cannot_be_completed_with_missing_history_rows_after_seal']),
        ];
    }

    public static function entries(string $root): array
    {
        $docMap = self::documentFamilyMap();
        $entries = [];
        foreach (MarketDataPublicationLifecycleTraceabilitySpec::mandatory($root) as $row) {
            $docId = (string) $row['strategy_document_id'];
            if (! isset($docMap[$docId])) {
                $entries[] = [
                    'rule_id' => (string) $row['rule_id'],
                    'strategy_document_id' => $docId,
                    'family' => '__UNMAPPED__',
                ];
                continue;
            }
            $entries[] = [
                'rule_id' => (string) $row['rule_id'],
                'strategy_document_id' => $docId,
                'family' => $docMap[$docId],
            ];
        }

        usort($entries, static function ($a, $b) {
            return strcmp($a['rule_id'], $b['rule_id']);
        });

        return $entries;
    }

    public static function expectedFamilyForDocument(string $documentId)
    {
        $map = self::documentFamilyMap();
        return $map[$documentId] ?? null;
    }
}
