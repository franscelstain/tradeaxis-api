<?php

require_once __DIR__.'/MarketDataIndicatorEngineTraceabilitySpec.php';

/**
 * Governed `MD-B14-A001` proof map: every mandatory predicate this stage owns, bound to the
 * implementation surface that carries it and to the two executed guards that establish it.
 *
 * Each family names a positive guard and a fail-closed guard, because a rule proven only by a
 * value being right is not proven against the case the contract actually cares about. Every
 * method named here is checked to exist before any binding: `MD-B12-A003` reached a draft that
 * bound a predicate to a guard method that had never existed.
 */
final class MarketDataIndicatorEngineProofSpec
{
    public const STAGE = 'MD-B14';

    public const ATTEMPT = 'MD-B14-A001';

    public const BASELINE = 'MD-B14-A001-BL001';

    public const CI = 'CI-MD-B14-A001-001';

    public const EXPECTED_DENOMINATOR = 147;

    /**
     * @return array<string,array{owner:string,implementation:array<int,string>,positive:array<int,string>,negative:array<int,string>,runtime_required:bool}>
     */
    public static function families(): array
    {
        return [
            'actual_versus_proxy' => [
                'owner' => 'MD-B14:actual-versus-proxy',
                'implementation' => [
                    'app/Application/MarketData/Services/IndicatorVectorService.php',
                ],
                'positive' => ['tests/Unit/MarketData/ActualVersusProxyMetricBoundaryTest.php', 'test_the_proxy_is_raw_close_times_raw_volume'],
                'negative' => ['tests/Unit/MarketData/ActualVersusProxyMetricBoundaryTest.php', 'test_the_actual_traded_value_is_null_rather_than_filled_with_the_proxy'],
                'runtime_required' => true,
            ],
            'artifact_hash_versioning' => [
                'owner' => 'MD-B14:artifact-hash-versioning',
                'implementation' => [
                    'app/Application/MarketData/Services/DeterministicHashService.php',
                    'app/Infrastructure/Persistence/MarketData/EodArtifactRepository.php',
                ],
                'positive' => ['tests/Unit/MarketData/IndicatorArtifactColumnParityTest.php', 'test_current_and_history_indicator_tables_expose_the_same_columns'],
                'negative' => ['tests/Unit/MarketData/IndicatorArtifactColumnParityTest.php', 'test_promote_to_current_preserves_the_contamination_trail'],
                'runtime_required' => true,
            ],
            'atr_level_published' => [
                'owner' => 'MD-B14:atr-level-published',
                'implementation' => [
                    'app/Application/MarketData/Services/IndicatorVectorService.php',
                ],
                'positive' => ['tests/Unit/MarketData/IndicatorFieldRegistryAndNullReasonsTest.php', 'test_the_registered_atr_level_is_published_and_not_permanently_null'],
                'negative' => ['tests/Unit/MarketData/IndicatorIndependentOracleTest.php', 'test_boundary_atr_uses_the_same_structural_adjustment_product_as_other_prices'],
                'runtime_required' => true,
            ],
            'atr_recursive_state' => [
                'owner' => 'MD-B14:atr-recursive-state',
                'implementation' => [
                    'app/Application/MarketData/Services/IndicatorVectorService.php',
                ],
                'positive' => ['tests/Unit/MarketData/IndicatorIndependentOracleTest.php', 'test_atr_is_seeded_from_the_boundary_not_the_load_window'],
                'negative' => ['tests/Unit/MarketData/IndicatorIndependentOracleTest.php', 'test_atr_oracle_on_a_constant_true_range_series'],
                'runtime_required' => true,
            ],
            'bound_input_identity' => [
                'owner' => 'MD-B14:bound-input-identity',
                'implementation' => [
                    'app/Application/MarketData/Services/IndicatorVectorService.php',
                    'app/Application/MarketData/Services/AnalyticalPriceProductService.php',
                ],
                'positive' => ['tests/Unit/MarketData/AnalyticalProductIdentityServiceTest.php', 'test_factor_hash_is_order_independent_but_revision_sensitive'],
                'negative' => ['tests/Unit/MarketData/AnalyticalPriceProductServiceTest.php', 'test_future_factor_is_not_admitted_to_earlier_as_of_and_future_bar_fails_closed'],
                'runtime_required' => true,
            ],
            'calendar_identity' => [
                'owner' => 'MD-B14:calendar-identity',
                'implementation' => [
                    'app/Application/MarketData/Services/IndicatorVectorService.php',
                    'app/Infrastructure/Persistence/MarketData/MarketCalendarRepository.php',
                ],
                'positive' => ['tests/Unit/MarketData/IndicatorEngineBoundaryB14Test.php', 'test_all_five_window_surfaces_resolve_through_one_calendar'],
                'negative' => ['tests/Unit/MarketData/TradingWindowWarmupTest.php', 'test_the_window_counts_trading_days_not_calendar_days'],
                'runtime_required' => true,
            ],
            'command_boundary' => [
                'owner' => 'MD-B14:command-boundary',
                'implementation' => [
                    'app/Console/Commands/MarketData/RecomputeCurrentIndicatorsCommand.php',
                    'app/Application/MarketData/Services/EodIndicatorsComputeService.php',
                ],
                'positive' => ['tests/Unit/MarketData/IndicatorEngineBoundaryB14Test.php', 'test_the_recompute_path_never_writes_a_source_or_master_table'],
                'negative' => ['tests/Unit/MarketData/IndicatorEngineBoundaryB14Test.php', 'test_the_recompute_command_never_dispatches_a_source_or_import_command'],
                'runtime_required' => true,
            ],
            'contamination_radius' => [
                'owner' => 'MD-B14:contamination-radius',
                'implementation' => [
                    'app/Application/MarketData/Services/IndicatorVectorService.php',
                ],
                'positive' => ['tests/Unit/MarketData/IndicatorHorizonRoleManifestTest.php', 'test_the_contamination_radius_is_published_and_matches_the_registry'],
                'negative' => ['tests/Unit/MarketData/IndicatorHorizonRoleManifestTest.php', 'test_each_role_matches_the_span_the_contract_defines_for_it'],
                'runtime_required' => true,
            ],
            'contamination_semantics' => [
                'owner' => 'MD-B14:contamination-semantics',
                'implementation' => [
                    'app/Application/MarketData/Services/IndicatorVectorService.php',
                    'app/Infrastructure/Persistence/MarketData/EventRiskSourceRepository.php',
                ],
                'positive' => ['tests/Unit/MarketData/ContaminationAnchoredOnBreakDateTest.php', 'test_an_unexplained_break_still_contaminates'],
                'negative' => ['tests/Unit/MarketData/ContaminationAnchoredOnBreakDateTest.php', 'test_a_neutral_factor_does_not_excuse_a_detected_break'],
                'runtime_required' => true,
            ],
            'cross_contract_alignment' => [
                'owner' => 'MD-B14:cross-contract-alignment',
                'implementation' => [
                    'app/Application/MarketData/Services/DeterministicHashService.php',
                ],
                'positive' => ['tests/Unit/MarketData/IndicatorEngineBoundaryB14Test.php', 'test_the_named_alignment_contracts_resolve_and_do_not_contradict_the_format_rule'],
                'negative' => ['tests/Unit/MarketData/DeterministicHashServiceTest.php', 'test_decimal_normalization_uses_decimal_text_and_locked_rounding_without_binary_float_drift'],
                'runtime_required' => true,
            ],
            'distinct_null_causes' => [
                'owner' => 'MD-B14:distinct-null-causes',
                'implementation' => [
                    'app/Application/MarketData/Services/IndicatorVectorService.php',
                ],
                'positive' => ['tests/Unit/MarketData/IndicatorFieldRegistryAndNullReasonsTest.php', 'test_a_price_scale_break_is_reason_coded_distinctly_from_a_corporate_action'],
                'negative' => ['tests/Unit/MarketData/IndicatorFieldRegistryAndNullReasonsTest.php', 'test_a_clean_row_carries_no_field_level_reasons'],
                'runtime_required' => true,
            ],
            'exact_window_dependencies' => [
                'owner' => 'MD-B14:exact-window-dependencies',
                'implementation' => [
                    'app/Application/MarketData/Services/IndicatorVectorService.php',
                ],
                'positive' => ['tests/Unit/MarketData/IndicatorIndependentOracleTest.php', 'test_gap_oracle_nulls_the_dependent_indicator_rather_than_shortening_the_window'],
                'negative' => ['tests/Unit/MarketData/TradingWindowWarmupTest.php', 'test_closed_days_are_not_counted_toward_the_window'],
                'runtime_required' => true,
            ],
            'field_registry' => [
                'owner' => 'MD-B14:field-registry',
                'implementation' => [
                    'app/Application/MarketData/Services/IndicatorVectorService.php',
                ],
                'positive' => ['tests/Unit/MarketData/IndicatorFieldRegistryAndNullReasonsTest.php', 'test_every_registered_field_declares_the_whole_registry_entry'],
                'negative' => ['tests/Unit/MarketData/IndicatorFieldRegistryAndNullReasonsTest.php', 'test_a_published_window_with_no_registry_entry_fails_closed'],
                'runtime_required' => true,
            ],
            'horizon_roles' => [
                'owner' => 'MD-B14:horizon-roles',
                'implementation' => [
                    'app/Application/MarketData/Services/IndicatorVectorService.php',
                ],
                'positive' => ['tests/Unit/MarketData/IndicatorHorizonRoleManifestTest.php', 'test_every_published_dependency_window_declares_a_horizon_role'],
                'negative' => ['tests/Unit/MarketData/IndicatorHorizonRoleManifestTest.php', 'test_a_window_without_a_declared_role_fails_closed'],
                'runtime_required' => true,
            ],
            'mutation_impact_resolution' => [
                'owner' => 'MD-B14:mutation-impact-resolution',
                'implementation' => [
                    'app/Application/MarketData/Services/EodBarsMutationImpactResolver.php',
                ],
                'positive' => ['tests/Unit/MarketData/OutOfOrderImportImpactStaticGuardTest.php', 'test_impact_resolver_escalates_affected_publications_to_correction'],
                'negative' => ['tests/Unit/MarketData/EodBarsMutationImpactResolverTest.php', 'test_unchanged_upsert_is_noop_for_indicator_and_publication_impact'],
                'runtime_required' => true,
            ],
            'per_field_nullability' => [
                'owner' => 'MD-B14:per-field-nullability',
                'implementation' => [
                    'app/Application/MarketData/Services/IndicatorVectorService.php',
                ],
                'positive' => ['tests/Unit/MarketData/IndicatorFieldRegistryAndNullReasonsTest.php', 'test_a_short_history_row_carries_field_level_reasons_not_only_the_primary_reason'],
                'negative' => ['tests/Unit/MarketData/IndicatorFieldRegistryAndNullReasonsTest.php', 'test_a_contaminated_row_keeps_field_level_reasons_beside_the_primary_reason'],
                'runtime_required' => true,
            ],
            'precision_and_serialization' => [
                'owner' => 'MD-B14:precision-and-serialization',
                'implementation' => [
                    'app/Application/MarketData/Services/IndicatorVectorService.php',
                    'app/Application/MarketData/Services/DeterministicHashService.php',
                ],
                'positive' => ['tests/Unit/MarketData/IndicatorFieldRegistryAndNullReasonsTest.php', 'test_declared_precision_matches_the_schema_and_the_hash_serializer'],
                'negative' => ['tests/Unit/MarketData/DeterministicHashServiceTest.php', 'test_decimal_normalization_uses_decimal_text_and_locked_rounding_without_binary_float_drift'],
                'runtime_required' => true,
            ],
            'recompute_immutability' => [
                'owner' => 'MD-B14:recompute-immutability',
                'implementation' => [
                    'app/Application/MarketData/Services/IndicatorVectorService.php',
                    'app/Application/MarketData/Services/EodIndicatorsComputeService.php',
                ],
                'positive' => ['tests/Unit/MarketData/TermOwnershipAndPriceProductTest.php', 'test_an_unresolved_factor_blocks_rather_than_mutates_history'],
                'negative' => ['tests/Unit/MarketData/EodBarsMutationImpactResolverTest.php', 'test_unchanged_upsert_is_noop_for_indicator_and_publication_impact'],
                'runtime_required' => true,
            ],
            'replay_determinism' => [
                'owner' => 'MD-B14:replay-determinism',
                'implementation' => [
                    'app/Application/MarketData/Services/ReplayBackfillService.php',
                    'app/Application/MarketData/Services/DeterministicHashService.php',
                ],
                'positive' => ['tests/Unit/MarketData/ReplayConfigIdentityVariesWithConfigTest.php', 'test_a_configuration_change_produces_a_different_replay_identity'],
                'negative' => ['tests/Unit/MarketData/IndicatorIndependentOracleTest.php', 'test_correction_oracle_propagates_by_exactly_the_expected_amount'],
                'runtime_required' => true,
            ],
            'row_identity_binding' => [
                'owner' => 'MD-B14:row-identity-binding',
                'implementation' => [
                    'app/Application/MarketData/Services/IndicatorVectorService.php',
                    'app/Infrastructure/Persistence/MarketData/EodArtifactRepository.php',
                ],
                'positive' => ['tests/Unit/MarketData/CanonicalScopeFrontierAndDecisionGradeTest.php', 'test_one_price_basis_and_one_formula_version_identify_an_indicator_row'],
                'negative' => ['tests/Unit/MarketData/CoherentPriceProductBoundaryTest.php', 'test_the_persisted_vector_carries_its_price_product_code'],
                'runtime_required' => true,
            ],
            'sector_measure_binding' => [
                'owner' => 'MD-B14:sector-measure-binding',
                'implementation' => [
                    'app/Application/MarketData/Services/IndicatorVectorService.php',
                    'app/Application/MarketData/Services/BenchmarkIndicatorVectorService.php',
                ],
                'positive' => ['tests/Unit/MarketData/SectorMembershipTemporalFactTest.php', 'test_a_membership_record_binds_stable_identity_system_interval_source_and_known_time'],
                'negative' => ['tests/Unit/MarketData/SectorMembershipTemporalFactTest.php', 'test_an_uncovered_date_resolves_unknown_rather_than_the_current_sector'],
                'runtime_required' => true,
            ],
            'shortened_session' => [
                'owner' => 'MD-B14:shortened-session',
                'implementation' => [
                    'app/Application/MarketData/Services/IndicatorVectorService.php',
                    'app/Application/MarketData/Services/EodIndicatorsComputeService.php',
                ],
                'positive' => ['tests/Unit/MarketData/IndicatorEngineBoundaryB14Test.php', 'test_no_measure_silently_treats_a_shortened_session'],
                'negative' => ['tests/Unit/MarketData/TradingWindowWarmupTest.php', 'test_a_closed_date_cannot_anchor_a_window'],
                'runtime_required' => true,
            ],
            'structural_product_input' => [
                'owner' => 'MD-B14:structural-product-input',
                'implementation' => [
                    'app/Application/MarketData/Services/IndicatorVectorService.php',
                    'app/Application/MarketData/Services/AnalyticalPriceProductService.php',
                ],
                'positive' => ['tests/Unit/MarketData/AnalyticalPriceProductServiceTest.php', 'test_raw_is_immutable_pass_through_and_provider_adj_close_is_not_selected'],
                'negative' => ['tests/Unit/MarketData/AnalyticalPriceProductServiceTest.php', 'test_structural_product_compounds_price_and_explicit_volume_factors_coherently'],
                'runtime_required' => true,
            ],
            'warm_up_and_dataset_start' => [
                'owner' => 'MD-B14:warm-up-and-dataset-start',
                'implementation' => [
                    'app/Application/MarketData/Services/IndicatorVectorService.php',
                ],
                'positive' => ['tests/Unit/MarketData/IndicatorVectorServiceTest.php', 'test_short_history_calculates_each_field_as_soon_as_its_own_warmup_is_met'],
                'negative' => ['tests/Unit/MarketData/TermOwnershipAndPriceProductTest.php', 'test_indicators_return_deterministic_null_until_warm_up_history_exists'],
                'runtime_required' => true,
            ],
            'zero_placeholder_prohibition' => [
                'owner' => 'MD-B14:zero-placeholder-prohibition',
                'implementation' => [
                    'app/Application/MarketData/Services/IndicatorVectorService.php',
                    'app/Application/MarketData/Services/AnalyticalPriceProductService.php',
                ],
                'positive' => ['tests/Unit/MarketData/CanonicalRawImportBoundaryTest.php', 'test_a_zero_price_placeholder_cannot_become_canonical'],
                'negative' => ['tests/Unit/MarketData/IndicatorVectorServiceTest.php', 'test_zero_denominator_extension_calculations_return_null_without_error'],
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
            throw new RuntimeException('No MD-B14 proof family for '.$ruleId);
        }

        return $map[$ruleId];
    }

    /** @return array<string,string> */
    public static function ruleFamilies(): array
    {
        return [
            // actual_versus_proxy (4)
            'MD-S060-R0020' => 'actual_versus_proxy',
            'MD-S061-R0015' => 'actual_versus_proxy',
            'MD-S061-R0027' => 'actual_versus_proxy',
            'MD-S081-R0065' => 'actual_versus_proxy',
            // artifact_hash_versioning (2)
            'MD-S081-R0057' => 'artifact_hash_versioning',
            'MD-S081-R0060' => 'artifact_hash_versioning',
            // atr_level_published (2)
            'MD-S081-R0063' => 'atr_level_published',
            'MD-S081-R0064' => 'atr_level_published',
            // atr_recursive_state (9)
            'MD-S028-R0028' => 'atr_recursive_state',
            'MD-S037-R0005' => 'atr_recursive_state',
            'MD-S056-R0021' => 'atr_recursive_state',
            'MD-S060-R0029' => 'atr_recursive_state',
            'MD-S060-R0030' => 'atr_recursive_state',
            'MD-S061-R0016' => 'atr_recursive_state',
            'MD-S081-R0059' => 'atr_recursive_state',
            'MD-S081-R0061' => 'atr_recursive_state',
            'MD-S081-R0062' => 'atr_recursive_state',
            // bound_input_identity (14)
            'MD-S060-R0003' => 'bound_input_identity',
            'MD-S060-R0005' => 'bound_input_identity',
            'MD-S060-R0006' => 'bound_input_identity',
            'MD-S060-R0007' => 'bound_input_identity',
            'MD-S061-R0003' => 'bound_input_identity',
            'MD-S061-R0004' => 'bound_input_identity',
            'MD-S061-R0005' => 'bound_input_identity',
            'MD-S061-R0006' => 'bound_input_identity',
            'MD-S061-R0008' => 'bound_input_identity',
            'MD-S061-R0009' => 'bound_input_identity',
            'MD-S061-R0010' => 'bound_input_identity',
            'MD-S061-R0012' => 'bound_input_identity',
            'MD-S061-R0013' => 'bound_input_identity',
            'MD-S061-R0018' => 'bound_input_identity',
            // calendar_identity (2)
            'MD-S041-R0053' => 'calendar_identity',
            'MD-S060-R0010' => 'calendar_identity',
            // command_boundary (12)
            'MD-S017-R0005' => 'command_boundary',
            'MD-S017-R0006' => 'command_boundary',
            'MD-S038-R0007' => 'command_boundary',
            'MD-S038-R0008' => 'command_boundary',
            'MD-S038-R0009' => 'command_boundary',
            'MD-S038-R0010' => 'command_boundary',
            'MD-S038-R0011' => 'command_boundary',
            'MD-S038-R0012' => 'command_boundary',
            'MD-S038-R0013' => 'command_boundary',
            'MD-S038-R0014' => 'command_boundary',
            'MD-S038-R0026' => 'command_boundary',
            'MD-S061-R0031' => 'command_boundary',
            // contamination_radius (5)
            'MD-S056-R0024' => 'contamination_radius',
            'MD-S081-R0034' => 'contamination_radius',
            'MD-S081-R0035' => 'contamination_radius',
            'MD-S081-R0036' => 'contamination_radius',
            'MD-S081-R0037' => 'contamination_radius',
            // contamination_semantics (3)
            'MD-S081-R0043' => 'contamination_semantics',
            'MD-S081-R0044' => 'contamination_semantics',
            'MD-S081-R0045' => 'contamination_semantics',
            // cross_contract_alignment (3)
            'MD-S034-R0024' => 'cross_contract_alignment',
            'MD-S034-R0025' => 'cross_contract_alignment',
            'MD-S034-R0026' => 'cross_contract_alignment',
            // distinct_null_causes (6)
            'MD-S019-R0054' => 'distinct_null_causes',
            'MD-S019-R0055' => 'distinct_null_causes',
            'MD-S028-R0024' => 'distinct_null_causes',
            'MD-S037-R0007' => 'distinct_null_causes',
            'MD-S061-R0028' => 'distinct_null_causes',
            'MD-S081-R0028' => 'distinct_null_causes',
            // exact_window_dependencies (6)
            'MD-S019-R0042' => 'exact_window_dependencies',
            'MD-S019-R0052' => 'exact_window_dependencies',
            'MD-S037-R0004' => 'exact_window_dependencies',
            'MD-S060-R0011' => 'exact_window_dependencies',
            'MD-S060-R0012' => 'exact_window_dependencies',
            'MD-S061-R0022' => 'exact_window_dependencies',
            // field_registry (10)
            'MD-S081-R0013' => 'field_registry',
            'MD-S081-R0014' => 'field_registry',
            'MD-S081-R0015' => 'field_registry',
            'MD-S081-R0026' => 'field_registry',
            'MD-S081-R0049' => 'field_registry',
            'MD-S081-R0050' => 'field_registry',
            'MD-S081-R0051' => 'field_registry',
            'MD-S081-R0052' => 'field_registry',
            'MD-S081-R0053' => 'field_registry',
            'MD-S081-R0054' => 'field_registry',
            // horizon_roles (4)
            'MD-S056-R0019' => 'horizon_roles',
            'MD-S056-R0020' => 'horizon_roles',
            'MD-S056-R0022' => 'horizon_roles',
            'MD-S056-R0129' => 'horizon_roles',
            // mutation_impact_resolution (2)
            'MD-S023-R0063' => 'mutation_impact_resolution',
            'MD-S060-R0061' => 'mutation_impact_resolution',
            // per_field_nullability (18)
            'MD-S017-R0037' => 'per_field_nullability',
            'MD-S017-R0039' => 'per_field_nullability',
            'MD-S017-R0040' => 'per_field_nullability',
            'MD-S017-R0041' => 'per_field_nullability',
            'MD-S017-R0042' => 'per_field_nullability',
            'MD-S019-R0050' => 'per_field_nullability',
            'MD-S019-R0051' => 'per_field_nullability',
            'MD-S028-R0011' => 'per_field_nullability',
            'MD-S028-R0022' => 'per_field_nullability',
            'MD-S028-R0025' => 'per_field_nullability',
            'MD-S037-R0002' => 'per_field_nullability',
            'MD-S037-R0006' => 'per_field_nullability',
            'MD-S037-R0010' => 'per_field_nullability',
            'MD-S038-R0034' => 'per_field_nullability',
            'MD-S060-R0032' => 'per_field_nullability',
            'MD-S061-R0019' => 'per_field_nullability',
            'MD-S061-R0024' => 'per_field_nullability',
            'MD-S061-R0029' => 'per_field_nullability',
            // precision_and_serialization (4)
            'MD-S060-R0013' => 'precision_and_serialization',
            'MD-S060-R0014' => 'precision_and_serialization',
            'MD-S061-R0017' => 'precision_and_serialization',
            'MD-S061-R0030' => 'precision_and_serialization',
            // recompute_immutability (5)
            'MD-S017-R0045' => 'recompute_immutability',
            'MD-S017-R0048' => 'recompute_immutability',
            'MD-S028-R0029' => 'recompute_immutability',
            'MD-S037-R0012' => 'recompute_immutability',
            'MD-S061-R0021' => 'recompute_immutability',
            // replay_determinism (3)
            'MD-S028-R0035' => 'replay_determinism',
            'MD-S060-R0069' => 'replay_determinism',
            'MD-S081-R0066' => 'replay_determinism',
            // row_identity_binding (9)
            'MD-S028-R0005' => 'row_identity_binding',
            'MD-S028-R0006' => 'row_identity_binding',
            'MD-S028-R0007' => 'row_identity_binding',
            'MD-S028-R0008' => 'row_identity_binding',
            'MD-S028-R0009' => 'row_identity_binding',
            'MD-S028-R0010' => 'row_identity_binding',
            'MD-S028-R0012' => 'row_identity_binding',
            'MD-S061-R0020' => 'row_identity_binding',
            'MD-S081-R0003' => 'row_identity_binding',
            // sector_measure_binding (5)
            'MD-S052-R0004' => 'sector_measure_binding',
            'MD-S052-R0027' => 'sector_measure_binding',
            'MD-S052-R0029' => 'sector_measure_binding',
            'MD-S052-R0030' => 'sector_measure_binding',
            'MD-S052-R0031' => 'sector_measure_binding',
            // shortened_session (3)
            'MD-S041-R0045' => 'shortened_session',
            'MD-S041-R0046' => 'shortened_session',
            'MD-S041-R0049' => 'shortened_session',
            // structural_product_input (4)
            'MD-S020-R0013' => 'structural_product_input',
            'MD-S060-R0004' => 'structural_product_input',
            'MD-S061-R0007' => 'structural_product_input',
            'MD-S061-R0014' => 'structural_product_input',
            // warm_up_and_dataset_start (3)
            'MD-S028-R0023' => 'warm_up_and_dataset_start',
            'MD-S037-R0003' => 'warm_up_and_dataset_start',
            'MD-S041-R0055' => 'warm_up_and_dataset_start',
            // zero_placeholder_prohibition (9)
            'MD-S017-R0043' => 'zero_placeholder_prohibition',
            'MD-S017-R0044' => 'zero_placeholder_prohibition',
            'MD-S028-R0026' => 'zero_placeholder_prohibition',
            'MD-S037-R0001' => 'zero_placeholder_prohibition',
            'MD-S037-R0008' => 'zero_placeholder_prohibition',
            'MD-S037-R0011' => 'zero_placeholder_prohibition',
            'MD-S060-R0060' => 'zero_placeholder_prohibition',
            'MD-S061-R0025' => 'zero_placeholder_prohibition',
            'MD-S061-R0026' => 'zero_placeholder_prohibition',
        ];
    }

    /** @return array<int,array{rule_id:string,strategy_document_id:string,family:string}> */
    public static function entries(string $root): array
    {
        $out = [];
        foreach (MarketDataIndicatorEngineTraceabilitySpec::mandatory($root) as $row) {
            $out[] = [
                'rule_id' => $row['rule_id'],
                'strategy_document_id' => $row['strategy_document_id'],
                'family' => self::familyFor($row),
            ];
        }

        return $out;
    }
}
