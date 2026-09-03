<?php

require_once __DIR__.'/MarketDataReadProductTraceabilitySpec.php';

/**
 * Governed `MD-B17-A002` proof map.
 *
 * Every mandatory predicate this stage owns is bound to the implementation surface that carries
 * it and to two executed guards. The controlled `MD-S082` correction made the six date-level
 * anomaly keys admissible; this successor attempt proves their exact immutable run-snapshot
 * binding and therefore includes `MD-S051-R0070` in the ordinary denominator.
 */
final class MarketDataReadProductProofSpec
{
    public const STAGE = 'MD-B17';

    public const ATTEMPT = 'MD-B17-A002';

    public const BASELINE = 'MD-B17-A002-BL001';

    public const CI = 'CI-MD-B17-A002-001';

    /** The full mandatory denominator. */
    public const EXPECTED_DENOMINATOR = 246;

    /** @return array<string,array<string,mixed>> */
    public static function families(): array
    {
        return [
            'anti_ambiguity' => [
                'owner' => 'MD-B17:anti-ambiguity',
                'implementation' => [
                    'app/Application/MarketData/Services/MarketDataPipelineService.php',
                    'tests/Unit/MarketData/CommandSurfaceSafetyStaticGuardTest.php',
                ],
                'positive' => ['tests/Unit/MarketData/CommandSurfaceSafetyStaticGuardTest.php', 'test_recompute_current_indicators_is_current_bars_only_and_does_not_import_source'],
                'negative' => ['tests/Unit/MarketData/MarketDataReadinessServiceTest.php', 'test_readiness_returns_reason_for_non_readable_run'],
                'runtime_required' => true,
            ],
            'audit_visible_final_fields' => [
                'owner' => 'MD-B17:audit-visible-final-fields',
                'implementation' => [
                    'app/Application/MarketData/Services/MarketDataPipelineService.php',
                    'app/Infrastructure/Persistence/MarketData/EodArtifactRepository.php',
                ],
                'positive' => ['tests/Unit/MarketData/StageThreeWriteCompletenessGuardTest.php', 'test_each_coverage_evidence_field_is_required'],
                'negative' => ['tests/Unit/MarketData/StageThreeWriteCompletenessGuardTest.php', 'test_every_mutating_repository_path_invokes_the_relevant_guard'],
                'runtime_required' => true,
            ],
            'citation_and_capability_boundary' => [
                'owner' => 'MD-B17:citation-and-capability-boundary',
                'implementation' => [
                    'app/Application/MarketData/Services/MarketDataReadinessService.php',
                    'app/Infrastructure/Persistence/MarketData/MarketDataReadProductRepository.php',
                ],
                'positive' => ['tests/Unit/MarketData/MarketDataWatchlistReadModelTest.php', 'test_market_data_read_product_exposes_unusable_rows_without_strategy_screening_or_current_active_filter'],
                'negative' => ['tests/Unit/MarketData/EligibilityExplainabilityBoundaryTest.php', 'test_the_decision_consults_no_preference_input'],
                'runtime_required' => true,
            ],
            'correction_and_pointer_switch' => [
                'owner' => 'MD-B17:correction-and-pointer-switch',
                'implementation' => [
                    'app/Infrastructure/Persistence/MarketData/EodPublicationRepository.php',
                    'app/Application/MarketData/Services/MarketDataPipelineService.php',
                ],
                'positive' => ['tests/Unit/MarketData/PublicationSealPointerLifecycleTest.php', 'test_a_correction_produces_a_new_snapshot_set_rather_than_editing_the_old_one'],
                'negative' => ['tests/Unit/MarketData/PublicationSealPointerLifecycleTest.php', 'test_a_failed_build_leaves_the_existing_pointer_untouched'],
                'runtime_required' => true,
            ],
            'coverage_interaction' => [
                'owner' => 'MD-B17:coverage-interaction',
                'implementation' => [
                    'app/Application/MarketData/Services/MarketDataPipelineService.php',
                    'app/Application/MarketData/Services/CoverageGateEvaluator.php',
                ],
                'positive' => ['tests/Unit/MarketData/MarketDataReadinessServiceTest.php', 'test_readiness_returns_reason_for_coverage_fail'],
                'negative' => ['tests/Unit/MarketData/ReadablePublicationReadContractIntegrationTest.php', 'test_scope_repository_returns_empty_when_current_pointer_coverage_gate_is_not_pass'],
                'runtime_required' => true,
            ],
            'date_level_anomaly' => [
                'owner' => 'MD-B17:date-level-anomaly',
                'implementation' => [
                    'app/Application/MarketData/Services/DateLevelAnomalyCheckService.php',
                    'app/Application/MarketData/Services/MarketDataPipelineService.php',
                    'app/Infrastructure/Persistence/MarketData/MarketDataConfigSnapshotRepository.php',
                    'config/market_data.php',
                ],
                'positive' => ['tests/Unit/MarketData/DateLevelAnomalyCheckB17Test.php', 'test_thresholds_are_loaded_from_the_owning_run_configuration_snapshot'],
                'negative' => ['tests/Unit/MarketData/DateLevelAnomalyCheckB17Test.php', 'test_missing_run_snapshot_binding_fails_closed_before_measurement'],
                'runtime_required' => true,
            ],
            'dates_tracked_independently' => [
                'owner' => 'MD-B17:dates-tracked-independently',
                'implementation' => [
                    'app/Application/MarketData/Services/MarketDataReadinessService.php',
                    'app/Infrastructure/Persistence/MarketData/MarketCalendarRepository.php',
                ],
                'positive' => ['tests/Unit/MarketData/ReadPathShortcutProhibitionTest.php', 'test_nothing_in_the_runtime_names_a_recency_based_resolver'],
                'negative' => ['tests/Unit/MarketData/ReadPathShortcutProhibitionTest.php', 'test_consumer_read_repositories_never_order_publications_by_recency'],
                'runtime_required' => true,
            ],
            'fallback_and_effective_date' => [
                'owner' => 'MD-B17:fallback-and-effective-date',
                'implementation' => [
                    'app/Application/MarketData/Services/MarketDataReadinessService.php',
                    'app/Infrastructure/Persistence/MarketData/MarketDataReadProductRepository.php',
                ],
                'positive' => ['tests/Unit/MarketData/ConsumerReadProductAntiBypassTest.php', 'test_an_unready_date_returns_an_explicit_empty_payload_rather_than_older_rows'],
                'negative' => ['tests/Unit/MarketData/MarketDataWatchlistReadModelTest.php', 'test_watchlist_read_model_blocks_when_no_readable_publication_exists'],
                'runtime_required' => true,
            ],
            'forbidden_shortcuts' => [
                'owner' => 'MD-B17:forbidden-shortcuts',
                'implementation' => [
                    'app/Infrastructure/Persistence/MarketData/MarketDataReadProductRepository.php',
                    'app/Application/MarketData/Services/MarketDataReadProductService.php',
                ],
                'positive' => ['tests/Unit/MarketData/ReadPathShortcutProhibitionTest.php', 'test_nothing_in_the_runtime_resolves_a_dataset_by_latest_trade_date'],
                'negative' => ['tests/Unit/MarketData/MarketDataConsumerReadModelStaticGuardTest.php', 'test_consumer_read_paths_never_query_raw_or_staging_tables'],
                'runtime_required' => true,
            ],
            'held_and_failed_semantics' => [
                'owner' => 'MD-B17:held-and-failed-semantics',
                'implementation' => [
                    'app/Application/MarketData/Services/FinalizeDecisionService.php',
                    'app/Application/MarketData/Services/MarketDataPipelineService.php',
                ],
                'positive' => ['tests/Unit/MarketData/MarketDataPipelineIntegrationTest.php', 'test_promote_daily_without_force_replace_holds_when_valid_current_exists'],
                'negative' => ['tests/Unit/MarketData/OperationalCommandSafetyBoundaryTest.php', 'test_a_failed_run_can_never_be_reported_readable'],
                'runtime_required' => true,
            ],
            'invalid_bar_and_shortened_session' => [
                'owner' => 'MD-B17:invalid-bar-and-shortened-session',
                'implementation' => [
                    'app/Infrastructure/Persistence/MarketData/EodArtifactRepository.php',
                    'app/Application/MarketData/Services/CoverageGateEvaluator.php',
                ],
                'positive' => ['tests/Unit/MarketData/CanonicalRawImportBoundaryTest.php', 'test_a_zero_price_placeholder_cannot_become_canonical'],
                'negative' => ['tests/Unit/MarketData/IndicatorEngineBoundaryB14Test.php', 'test_no_measure_silently_treats_a_shortened_session'],
                'runtime_required' => true,
            ],
            'phase_ownership' => [
                'owner' => 'MD-B17:phase-ownership',
                'implementation' => [
                    'app/Application/MarketData/Services/MarketDataPipelineService.php',
                    'tests/Unit/MarketData/CommandSurfaceSafetyStaticGuardTest.php',
                ],
                'positive' => ['tests/Unit/MarketData/CommandSurfaceSafetyStaticGuardTest.php', 'test_recompute_current_indicators_is_current_bars_only_and_does_not_import_source'],
                'negative' => ['tests/Unit/MarketData/FactDimensionSeparationAndProductTableTest.php', 'test_a_partial_import_cannot_become_a_readable_publication'],
                'runtime_required' => true,
            ],
            'pointer_only_resolution' => [
                'owner' => 'MD-B17:pointer-only-resolution',
                'implementation' => [
                    'app/Infrastructure/Persistence/MarketData/EodPublicationRepository.php',
                    'app/Infrastructure/Persistence/MarketData/MarketDataReadProductRepository.php',
                ],
                'positive' => ['tests/Unit/MarketData/PublicationSealPointerLifecycleTest.php', 'test_exactly_one_publication_is_current_for_a_trade_date'],
                'negative' => ['tests/Unit/MarketData/PublicationSealPointerLifecycleTest.php', 'test_the_pointer_table_structurally_refuses_a_second_current_row'],
                'runtime_required' => true,
            ],
            'promote_preconditions' => [
                'owner' => 'MD-B17:promote-preconditions',
                'implementation' => [
                    'app/Application/MarketData/Services/MarketDataPipelineService.php',
                    'app/Application/MarketData/Services/FinalizeDecisionService.php',
                ],
                'positive' => ['tests/Unit/MarketData/MarketDataReadinessServiceTest.php', 'test_readiness_returns_ready_only_for_current_sealed_success_readable_pass_pointer'],
                'negative' => ['tests/Unit/MarketData/MarketDataReadinessServiceTest.php', 'test_readiness_returns_reason_for_unsealed_publication'],
                'runtime_required' => true,
            ],
            'read_model_analytical_product' => [
                'owner' => 'MD-B17:read-model-analytical-product',
                'implementation' => [
                    'app/Infrastructure/Persistence/MarketData/MarketDataReadProductRepository.php',
                    'app/Application/MarketData/Services/IndicatorVectorService.php',
                ],
                'positive' => ['tests/Unit/MarketData/FactDimensionSeparationAndProductTableTest.php', 'test_structural_adjusted_pairs_ohlc_adjustment_with_inverse_volume'],
                'negative' => ['tests/Unit/MarketData/FactDimensionSeparationAndProductTableTest.php', 'test_provider_adjusted_close_is_not_a_price_product_and_never_a_fallback'],
                'runtime_required' => true,
            ],
            'read_model_eligibility_facts' => [
                'owner' => 'MD-B17:read-model-eligibility-facts',
                'implementation' => [
                    'app/Infrastructure/Persistence/MarketData/MarketDataReadProductRepository.php',
                    'app/Application/MarketData/Services/EodEligibilityBuildService.php',
                ],
                'positive' => ['tests/Unit/MarketData/EligibilityFirstClassFactDimensionsTest.php', 'test_every_row_persists_the_four_previously_absent_dimensions'],
                'negative' => ['tests/Unit/MarketData/EligibilityExplainabilityBoundaryTest.php', 'test_eligible_means_usable_data_and_carries_no_selection_verdict'],
                'runtime_required' => true,
            ],
            'read_model_field_families' => [
                'owner' => 'MD-B17:read-model-field-families',
                'implementation' => [
                    'app/Infrastructure/Persistence/MarketData/MarketDataReadProductRepository.php',
                    'app/Application/MarketData/Services/MarketDataReadProductService.php',
                ],
                'positive' => ['tests/Unit/MarketData/ConsumerReadProductAntiBypassTest.php', 'test_the_row_shape_covers_every_required_field_group'],
                'negative' => ['tests/Unit/MarketData/MarketDataWatchlistReadModelTest.php', 'test_watchlist_read_model_withholds_a_publication_with_an_unrecorded_bar_product'],
                'runtime_required' => true,
            ],
            'read_model_grain_identity' => [
                'owner' => 'MD-B17:read-model-grain-identity',
                'implementation' => [
                    'app/Infrastructure/Persistence/MarketData/MarketDataReadProductRepository.php',
                    'app/Application/MarketData/Services/MarketDataReadProductService.php',
                ],
                'positive' => ['tests/Unit/MarketData/ConsumerReadProductAntiBypassTest.php', 'test_the_payload_declares_its_product_and_read_model_version'],
                'negative' => ['tests/Unit/MarketData/MarketDataWatchlistReadModelTest.php', 'test_watchlist_read_model_does_not_leak_non_current_publication_rows'],
                'runtime_required' => true,
            ],
            'read_model_indicator_context' => [
                'owner' => 'MD-B17:read-model-indicator-context',
                'implementation' => [
                    'app/Infrastructure/Persistence/MarketData/MarketDataReadProductRepository.php',
                    'app/Application/MarketData/Services/IndicatorVectorService.php',
                ],
                'positive' => ['tests/Unit/MarketData/IndicatorFieldRegistryAndNullReasonsTest.php', 'test_every_registered_field_declares_the_whole_registry_entry'],
                'negative' => ['tests/Unit/MarketData/LiquidityMetricLabellingTest.php', 'test_an_unlabelled_populated_metric_is_not_assumed_to_be_a_proxy'],
                'runtime_required' => true,
            ],
            'read_model_readiness_freshness' => [
                'owner' => 'MD-B17:read-model-readiness-freshness',
                'implementation' => [
                    'app/Application/MarketData/Services/MarketDataReadinessService.php',
                    'app/Infrastructure/Persistence/MarketData/MarketDataReadProductRepository.php',
                ],
                'positive' => ['tests/Unit/MarketData/ReadinessDiagnosisAgreementTest.php', 'test_the_readiness_reason_matches_the_canonical_diagnosis'],
                'negative' => ['tests/Unit/MarketData/ReadinessDiagnosisAgreementTest.php', 'test_a_specific_fault_is_not_reported_as_nothing_published'],
                'runtime_required' => true,
            ],
            'read_side_enforcement' => [
                'owner' => 'MD-B17:read-side-enforcement',
                'implementation' => [
                    'app/Infrastructure/Persistence/MarketData/MarketDataReadProductRepository.php',
                    'app/Application/MarketData/Services/MarketDataReadProductService.php',
                ],
                'positive' => ['tests/Unit/MarketData/ConsumerReadProductAntiBypassTest.php', 'test_no_code_outside_the_persistence_layer_reads_the_artifact_tables'],
                'negative' => ['tests/Unit/MarketData/ConsumerReadProductAntiBypassTest.php', 'test_the_gateway_exposes_exactly_one_read_entry_point'],
                'runtime_required' => true,
            ],
            'read_surface_atomicity' => [
                'owner' => 'MD-B17:read-surface-atomicity',
                'implementation' => [
                    'app/Infrastructure/Persistence/MarketData/MarketDataReadProductRepository.php',
                    'app/Infrastructure/Persistence/MarketData/EodPublicationRepository.php',
                ],
                'positive' => ['tests/Unit/MarketData/ConsumerReadProductAntiBypassTest.php', 'test_every_artifact_join_binds_publication_and_run'],
                'negative' => ['tests/Unit/MarketData/ReadablePublicationReadContractIntegrationTest.php', 'test_scope_repository_reads_only_pointer_resolved_readable_publication_rows'],
                'runtime_required' => true,
            ],
            'readable_conditions' => [
                'owner' => 'MD-B17:readable-conditions',
                'implementation' => [
                    'app/Application/MarketData/Services/MarketDataReadinessService.php',
                    'app/Infrastructure/Persistence/MarketData/EodPublicationRepository.php',
                ],
                'positive' => ['tests/Unit/MarketData/CurrentPointerIntegrityScanTest.php', 'test_a_healthy_current_pointer_is_not_flagged'],
                'negative' => ['tests/Unit/MarketData/CurrentPointerIntegrityScanTest.php', 'test_states_the_consumer_cannot_read_are_flagged_by_the_scan'],
                'runtime_required' => true,
            ],
            'readiness_states' => [
                'owner' => 'MD-B17:readiness-states',
                'implementation' => [
                    'app/Application/MarketData/Services/MarketDataReadinessService.php',
                ],
                'positive' => ['tests/Unit/MarketData/MarketDataReadinessServiceTest.php', 'test_readiness_returns_reason_for_missing_pointer'],
                'negative' => ['tests/Unit/MarketData/ReadinessDiagnosisAgreementTest.php', 'test_a_date_that_was_never_published_reports_nothing_published'],
                'runtime_required' => true,
            ],
            'seal_and_supersession' => [
                'owner' => 'MD-B17:seal-and-supersession',
                'implementation' => [
                    'app/Infrastructure/Persistence/MarketData/EodPublicationRepository.php',
                    'app/Infrastructure/Persistence/MarketData/EodArtifactRepository.php',
                ],
                'positive' => ['tests/Unit/MarketData/PublicationSealPointerLifecycleTest.php', 'test_a_sealed_publication_cannot_be_discarded'],
                'negative' => ['tests/Unit/MarketData/PublicationSealPointerLifecycleTest.php', 'test_a_superseded_publication_remains_queryable_and_unchanged'],
                'runtime_required' => true,
            ],
            'source_blocker_interpretation' => [
                'owner' => 'MD-B17:source-blocker-interpretation',
                'implementation' => [
                    'app/Application/MarketData/Services/MarketDataPipelineService.php',
                ],
                'positive' => ['tests/Unit/MarketData/MarketDataPipelineIntegrationTest.php', 'test_manual_file_promote_from_imported_partial_dataset_enforces_coverage_gate_and_does_not_switch_pointer'],
                'negative' => ['tests/Unit/MarketData/OperationalCommandSafetyBoundaryTest.php', 'test_a_failed_run_can_never_be_reported_readable'],
                'runtime_required' => true,
            ],
            'terminal_status_and_publishability' => [
                'owner' => 'MD-B17:terminal-status-and-publishability',
                'implementation' => [
                    'app/Application/MarketData/Services/MarketDataPipelineService.php',
                    'app/Application/MarketData/Services/FinalizeDecisionService.php',
                ],
                'positive' => ['tests/Unit/MarketData/MarketDataReadinessServiceTest.php', 'test_readiness_returns_ready_only_for_current_sealed_success_readable_pass_pointer'],
                'negative' => ['tests/Unit/MarketData/MarketDataReadinessServiceTest.php', 'test_readiness_returns_reason_for_non_success_run'],
                'runtime_required' => true,
            ],
            'versioning_compatibility' => [
                'owner' => 'MD-B17:versioning-compatibility',
                'implementation' => [
                    'app/Infrastructure/Persistence/MarketData/MarketDataReadProductRepository.php',
                    'app/Application/MarketData/Services/MarketDataReadProductService.php',
                ],
                'positive' => ['tests/Unit/MarketData/ReadProductAliasRetirementBoundaryB17Test.php', 'test_neither_compatibility_alias_is_retired_without_the_demonstration_the_contract_requires'],
                'negative' => ['tests/Unit/MarketData/ReadProductAliasRetirementBoundaryB17Test.php', 'test_each_alias_still_stands_for_a_distinct_canonical_field'],
                'runtime_required' => true,
            ],
        ];
    }

    /** @param array<string,string> $row */
    public static function familyFor(array $row): string
    {
        $map = self::ruleFamilies();
        $ruleId = (string) $row['rule_id'];
        if (! isset($map[$ruleId])) {
            throw new RuntimeException('No MD-B17 proof family for '.$ruleId);
        }

        return $map[$ruleId];
    }

    /** @return array<string,string> */
    public static function ruleFamilies(): array
    {
        return [
            // anti_ambiguity (9)
            'MD-S029-R0194' => 'anti_ambiguity',
            'MD-S051-R0082' => 'anti_ambiguity',
            'MD-S051-R0083' => 'anti_ambiguity',
            'MD-S051-R0084' => 'anti_ambiguity',
            'MD-S051-R0085' => 'anti_ambiguity',
            'MD-S051-R0087' => 'anti_ambiguity',
            'MD-S051-R0088' => 'anti_ambiguity',
            'MD-S051-R0089' => 'anti_ambiguity',
            'MD-S051-R0090' => 'anti_ambiguity',
            // audit_visible_final_fields (9)
            'MD-S029-R0184' => 'audit_visible_final_fields',
            'MD-S029-R0186' => 'audit_visible_final_fields',
            'MD-S051-R0058' => 'audit_visible_final_fields',
            'MD-S051-R0059' => 'audit_visible_final_fields',
            'MD-S051-R0060' => 'audit_visible_final_fields',
            'MD-S051-R0061' => 'audit_visible_final_fields',
            'MD-S051-R0062' => 'audit_visible_final_fields',
            'MD-S051-R0063' => 'audit_visible_final_fields',
            'MD-S051-R0064' => 'audit_visible_final_fields',
            // citation_and_capability_boundary (15)
            'MD-S001-R0090' => 'citation_and_capability_boundary',
            'MD-S001-R0091' => 'citation_and_capability_boundary',
            'MD-S001-R0092' => 'citation_and_capability_boundary',
            'MD-S001-R0094' => 'citation_and_capability_boundary',
            'MD-S001-R0095' => 'citation_and_capability_boundary',
            'MD-S001-R0096' => 'citation_and_capability_boundary',
            'MD-S001-R0097' => 'citation_and_capability_boundary',
            'MD-S006-R0031' => 'citation_and_capability_boundary',
            'MD-S008-R0079' => 'citation_and_capability_boundary',
            'MD-S009-R0022' => 'citation_and_capability_boundary',
            'MD-S021-R0048' => 'citation_and_capability_boundary',
            'MD-S022-R0039' => 'citation_and_capability_boundary',
            'MD-S029-R0170' => 'citation_and_capability_boundary',
            'MD-S049-R0022' => 'citation_and_capability_boundary',
            'MD-S051-R0080' => 'citation_and_capability_boundary',
            // correction_and_pointer_switch (4)
            'MD-S006-R0014' => 'correction_and_pointer_switch',
            'MD-S006-R0015' => 'correction_and_pointer_switch',
            'MD-S009-R0015' => 'correction_and_pointer_switch',
            'MD-S022-R0032' => 'correction_and_pointer_switch',
            // coverage_interaction (8)
            'MD-S001-R0065' => 'coverage_interaction',
            'MD-S001-R0066' => 'coverage_interaction',
            'MD-S051-R0046' => 'coverage_interaction',
            'MD-S051-R0047' => 'coverage_interaction',
            'MD-S051-R0048' => 'coverage_interaction',
            'MD-S051-R0049' => 'coverage_interaction',
            'MD-S051-R0050' => 'coverage_interaction',
            'MD-S051-R0051' => 'coverage_interaction',
            // date_level_anomaly (8)
            'MD-S051-R0065' => 'date_level_anomaly',
            'MD-S051-R0067' => 'date_level_anomaly',
            'MD-S051-R0068' => 'date_level_anomaly',
            'MD-S051-R0069' => 'date_level_anomaly',
            'MD-S051-R0070' => 'date_level_anomaly',
            'MD-S051-R0071' => 'date_level_anomaly',
            'MD-S051-R0072' => 'date_level_anomaly',
            'MD-S051-R0073' => 'date_level_anomaly',
            // dates_tracked_independently (11)
            'MD-S022-R0002' => 'dates_tracked_independently',
            'MD-S022-R0003' => 'dates_tracked_independently',
            'MD-S022-R0004' => 'dates_tracked_independently',
            'MD-S022-R0005' => 'dates_tracked_independently',
            'MD-S022-R0006' => 'dates_tracked_independently',
            'MD-S022-R0007' => 'dates_tracked_independently',
            'MD-S030-R0001' => 'dates_tracked_independently',
            'MD-S030-R0002' => 'dates_tracked_independently',
            'MD-S030-R0003' => 'dates_tracked_independently',
            'MD-S030-R0004' => 'dates_tracked_independently',
            'MD-S041-R0062' => 'dates_tracked_independently',
            // fallback_and_effective_date (17)
            'MD-S019-R0033' => 'fallback_and_effective_date',
            'MD-S019-R0035' => 'fallback_and_effective_date',
            'MD-S019-R0036' => 'fallback_and_effective_date',
            'MD-S019-R0037' => 'fallback_and_effective_date',
            'MD-S019-R0038' => 'fallback_and_effective_date',
            'MD-S022-R0030' => 'fallback_and_effective_date',
            'MD-S022-R0031' => 'fallback_and_effective_date',
            'MD-S030-R0005' => 'fallback_and_effective_date',
            'MD-S030-R0006' => 'fallback_and_effective_date',
            'MD-S030-R0007' => 'fallback_and_effective_date',
            'MD-S030-R0008' => 'fallback_and_effective_date',
            'MD-S030-R0009' => 'fallback_and_effective_date',
            'MD-S030-R0010' => 'fallback_and_effective_date',
            'MD-S030-R0011' => 'fallback_and_effective_date',
            'MD-S030-R0012' => 'fallback_and_effective_date',
            'MD-S030-R0013' => 'fallback_and_effective_date',
            'MD-S053-R0101' => 'fallback_and_effective_date',
            // forbidden_shortcuts (18)
            'MD-S006-R0016' => 'forbidden_shortcuts',
            'MD-S006-R0017' => 'forbidden_shortcuts',
            'MD-S006-R0018' => 'forbidden_shortcuts',
            'MD-S006-R0019' => 'forbidden_shortcuts',
            'MD-S006-R0020' => 'forbidden_shortcuts',
            'MD-S006-R0021' => 'forbidden_shortcuts',
            'MD-S006-R0022' => 'forbidden_shortcuts',
            'MD-S006-R0023' => 'forbidden_shortcuts',
            'MD-S021-R0034' => 'forbidden_shortcuts',
            'MD-S021-R0035' => 'forbidden_shortcuts',
            'MD-S021-R0036' => 'forbidden_shortcuts',
            'MD-S021-R0037' => 'forbidden_shortcuts',
            'MD-S021-R0038' => 'forbidden_shortcuts',
            'MD-S021-R0039' => 'forbidden_shortcuts',
            'MD-S021-R0040' => 'forbidden_shortcuts',
            'MD-S029-R0107' => 'forbidden_shortcuts',
            'MD-S049-R0006' => 'forbidden_shortcuts',
            'MD-S059-R0048' => 'forbidden_shortcuts',
            // held_and_failed_semantics (11)
            'MD-S051-R0033' => 'held_and_failed_semantics',
            'MD-S051-R0035' => 'held_and_failed_semantics',
            'MD-S051-R0036' => 'held_and_failed_semantics',
            'MD-S051-R0037' => 'held_and_failed_semantics',
            'MD-S051-R0038' => 'held_and_failed_semantics',
            'MD-S051-R0039' => 'held_and_failed_semantics',
            'MD-S051-R0041' => 'held_and_failed_semantics',
            'MD-S051-R0042' => 'held_and_failed_semantics',
            'MD-S051-R0043' => 'held_and_failed_semantics',
            'MD-S051-R0044' => 'held_and_failed_semantics',
            'MD-S051-R0045' => 'held_and_failed_semantics',
            // invalid_bar_and_shortened_session (4)
            'MD-S008-R0068' => 'invalid_bar_and_shortened_session',
            'MD-S023-R0057' => 'invalid_bar_and_shortened_session',
            'MD-S039-R0003' => 'invalid_bar_and_shortened_session',
            'MD-S041-R0048' => 'invalid_bar_and_shortened_session',
            // phase_ownership (14)
            'MD-S036-R0029' => 'phase_ownership',
            'MD-S051-R0004' => 'phase_ownership',
            'MD-S051-R0005' => 'phase_ownership',
            'MD-S051-R0006' => 'phase_ownership',
            'MD-S051-R0007' => 'phase_ownership',
            'MD-S051-R0008' => 'phase_ownership',
            'MD-S051-R0009' => 'phase_ownership',
            'MD-S051-R0010' => 'phase_ownership',
            'MD-S051-R0012' => 'phase_ownership',
            'MD-S051-R0013' => 'phase_ownership',
            'MD-S051-R0014' => 'phase_ownership',
            'MD-S051-R0015' => 'phase_ownership',
            'MD-S051-R0016' => 'phase_ownership',
            'MD-S051-R0017' => 'phase_ownership',
            // pointer_only_resolution (4)
            'MD-S019-R0024' => 'pointer_only_resolution',
            'MD-S049-R0003' => 'pointer_only_resolution',
            'MD-S049-R0004' => 'pointer_only_resolution',
            'MD-S049-R0005' => 'pointer_only_resolution',
            // promote_preconditions (8)
            'MD-S051-R0025' => 'promote_preconditions',
            'MD-S051-R0026' => 'promote_preconditions',
            'MD-S051-R0027' => 'promote_preconditions',
            'MD-S051-R0028' => 'promote_preconditions',
            'MD-S051-R0029' => 'promote_preconditions',
            'MD-S051-R0030' => 'promote_preconditions',
            'MD-S051-R0031' => 'promote_preconditions',
            'MD-S051-R0032' => 'promote_preconditions',
            // read_model_analytical_product (3)
            'MD-S021-R0014' => 'read_model_analytical_product',
            'MD-S021-R0015' => 'read_model_analytical_product',
            'MD-S021-R0016' => 'read_model_analytical_product',
            // read_model_eligibility_facts (4)
            'MD-S021-R0022' => 'read_model_eligibility_facts',
            'MD-S021-R0023' => 'read_model_eligibility_facts',
            'MD-S021-R0024' => 'read_model_eligibility_facts',
            'MD-S021-R0025' => 'read_model_eligibility_facts',
            // read_model_field_families (4)
            'MD-S021-R0010' => 'read_model_field_families',
            'MD-S021-R0011' => 'read_model_field_families',
            'MD-S021-R0012' => 'read_model_field_families',
            'MD-S021-R0013' => 'read_model_field_families',
            // read_model_grain_identity (10)
            'MD-S006-R0012' => 'read_model_grain_identity',
            'MD-S006-R0013' => 'read_model_grain_identity',
            'MD-S021-R0004' => 'read_model_grain_identity',
            'MD-S021-R0005' => 'read_model_grain_identity',
            'MD-S021-R0006' => 'read_model_grain_identity',
            'MD-S021-R0007' => 'read_model_grain_identity',
            'MD-S021-R0008' => 'read_model_grain_identity',
            'MD-S021-R0009' => 'read_model_grain_identity',
            'MD-S023-R0014' => 'read_model_grain_identity',
            'MD-S057-R0048' => 'read_model_grain_identity',
            // read_model_indicator_context (5)
            'MD-S021-R0017' => 'read_model_indicator_context',
            'MD-S021-R0018' => 'read_model_indicator_context',
            'MD-S021-R0019' => 'read_model_indicator_context',
            'MD-S021-R0020' => 'read_model_indicator_context',
            'MD-S021-R0021' => 'read_model_indicator_context',
            // read_model_readiness_freshness (10)
            'MD-S021-R0026' => 'read_model_readiness_freshness',
            'MD-S021-R0027' => 'read_model_readiness_freshness',
            'MD-S021-R0028' => 'read_model_readiness_freshness',
            'MD-S021-R0029' => 'read_model_readiness_freshness',
            'MD-S021-R0030' => 'read_model_readiness_freshness',
            'MD-S022-R0015' => 'read_model_readiness_freshness',
            'MD-S022-R0016' => 'read_model_readiness_freshness',
            'MD-S022-R0017' => 'read_model_readiness_freshness',
            'MD-S022-R0018' => 'read_model_readiness_freshness',
            'MD-S022-R0019' => 'read_model_readiness_freshness',
            // read_side_enforcement (15)
            'MD-S006-R0001' => 'read_side_enforcement',
            'MD-S006-R0002' => 'read_side_enforcement',
            'MD-S006-R0024' => 'read_side_enforcement',
            'MD-S006-R0025' => 'read_side_enforcement',
            'MD-S021-R0001' => 'read_side_enforcement',
            'MD-S023-R0064' => 'read_side_enforcement',
            'MD-S049-R0002' => 'read_side_enforcement',
            'MD-S049-R0007' => 'read_side_enforcement',
            'MD-S049-R0008' => 'read_side_enforcement',
            'MD-S049-R0009' => 'read_side_enforcement',
            'MD-S049-R0010' => 'read_side_enforcement',
            'MD-S049-R0011' => 'read_side_enforcement',
            'MD-S049-R0012' => 'read_side_enforcement',
            'MD-S049-R0015' => 'read_side_enforcement',
            'MD-S049-R0016' => 'read_side_enforcement',
            // read_surface_atomicity (13)
            'MD-S006-R0005' => 'read_surface_atomicity',
            'MD-S006-R0006' => 'read_surface_atomicity',
            'MD-S006-R0007' => 'read_surface_atomicity',
            'MD-S006-R0008' => 'read_surface_atomicity',
            'MD-S006-R0009' => 'read_surface_atomicity',
            'MD-S006-R0010' => 'read_surface_atomicity',
            'MD-S006-R0011' => 'read_surface_atomicity',
            'MD-S019-R0027' => 'read_surface_atomicity',
            'MD-S019-R0028' => 'read_surface_atomicity',
            'MD-S019-R0029' => 'read_surface_atomicity',
            'MD-S019-R0030' => 'read_surface_atomicity',
            'MD-S021-R0031' => 'read_surface_atomicity',
            'MD-S021-R0032' => 'read_surface_atomicity',
            // readable_conditions (14)
            'MD-S009-R0012' => 'readable_conditions',
            'MD-S009-R0013' => 'readable_conditions',
            'MD-S009-R0014' => 'readable_conditions',
            'MD-S020-R0015' => 'readable_conditions',
            'MD-S022-R0021' => 'readable_conditions',
            'MD-S022-R0022' => 'readable_conditions',
            'MD-S022-R0023' => 'readable_conditions',
            'MD-S022-R0024' => 'readable_conditions',
            'MD-S022-R0025' => 'readable_conditions',
            'MD-S022-R0026' => 'readable_conditions',
            'MD-S022-R0027' => 'readable_conditions',
            'MD-S022-R0028' => 'readable_conditions',
            'MD-S022-R0029' => 'readable_conditions',
            'MD-S022-R0033' => 'readable_conditions',
            // readiness_states (14)
            'MD-S009-R0002' => 'readiness_states',
            'MD-S009-R0010' => 'readiness_states',
            'MD-S009-R0011' => 'readiness_states',
            'MD-S009-R0016' => 'readiness_states',
            'MD-S022-R0001' => 'readiness_states',
            'MD-S022-R0008' => 'readiness_states',
            'MD-S022-R0009' => 'readiness_states',
            'MD-S022-R0010' => 'readiness_states',
            'MD-S022-R0011' => 'readiness_states',
            'MD-S022-R0012' => 'readiness_states',
            'MD-S022-R0013' => 'readiness_states',
            'MD-S022-R0014' => 'readiness_states',
            'MD-S049-R0013' => 'readiness_states',
            'MD-S049-R0014' => 'readiness_states',
            // seal_and_supersession (2)
            'MD-S019-R0031' => 'seal_and_supersession',
            'MD-S019-R0032' => 'seal_and_supersession',
            // source_blocker_interpretation (5)
            'MD-S051-R0052' => 'source_blocker_interpretation',
            'MD-S051-R0053' => 'source_blocker_interpretation',
            'MD-S051-R0054' => 'source_blocker_interpretation',
            'MD-S051-R0055' => 'source_blocker_interpretation',
            'MD-S051-R0056' => 'source_blocker_interpretation',
            // terminal_status_and_publishability (4)
            'MD-S051-R0019' => 'terminal_status_and_publishability',
            'MD-S051-R0023' => 'terminal_status_and_publishability',
            'MD-S067-R0012' => 'terminal_status_and_publishability',
            'MD-S067-R0018' => 'terminal_status_and_publishability',
            // versioning_compatibility (3)
            'MD-S020-R0070' => 'versioning_compatibility',
            'MD-S021-R0041' => 'versioning_compatibility',
            'MD-S021-R0042' => 'versioning_compatibility',
        ];
    }

    /** Every mandatory row in the current denominator. */
    public static function entries(string $root): array
    {
        $out = [];
        foreach (MarketDataReadProductTraceabilitySpec::mandatory($root) as $row) {
            $out[] = [
                'rule_id' => $row['rule_id'],
                'strategy_document_id' => $row['strategy_document_id'],
                'family' => self::familyFor($row),
            ];
        }

        return $out;
    }
}
