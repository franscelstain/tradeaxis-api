<?php

require_once __DIR__.'/MarketDataLiquidityMetricTraceabilitySpec.php';

/**
 * The MD-B13 proof plan: every mandatory predicate mapped to one proof family, and every family to
 * an implementation surface plus a positive and a fail-closed test method.
 *
 * `familyFor()` throws on an unmapped rule rather than returning a default. A default would let a
 * predicate added to the denominator later be silently absorbed into a family that does not prove
 * it, which is the failure mode a coverage figure cannot show.
 */
final class MarketDataLiquidityMetricProofSpec
{
    public const STAGE = 'MD-B13';

    public const ATTEMPT = 'MD-B13-A001';

    public const BASELINE = 'MD-B13-A001-BL001';

    public const CI = 'CI-MD-B13-A001-001';

    public const EXPECTED_DENOMINATOR = 33;

    private static function family(string $owner, array $implementation, array $positive, array $negative): array
    {
        return [
            'owner' => $owner,
            'implementation' => $implementation,
            'positive' => $positive,
            'negative' => $negative,
            'runtime_required' => true,
        ];
    }

    public static function families(): array
    {
        return [
            'canonical_volume_unit_identity' => self::family(
                'MD-B13:canonical-volume-unit-identity',
                [
                    'app/Application/MarketData/Services/VolumeUnitNormalizationService.php',
                    'app/Infrastructure/MarketData/Source/PublicApiEodBarsAdapter.php',
                    'app/Infrastructure/Persistence/MarketData/SourceObservationRepository.php',
                ],
                ['tests/Unit/MarketData/VolumeUnitAndTurnoverBoundaryTest.php', 'test_a_declared_share_unit_normalizes_with_no_multiplier'],
                ['tests/Unit/MarketData/VolumeUnitAndTurnoverBoundaryTest.php', 'test_a_declared_unit_without_evidence_does_not_normalize']
            ),
            'adjusted_volume_separation' => self::family(
                'MD-B13:adjusted-volume-separation',
                [
                    'app/Application/MarketData/Services/IndicatorVectorService.php',
                    'app/Application/MarketData/Services/AnalyticalPriceProductService.php',
                ],
                ['tests/Unit/MarketData/ActualAndProxyLiquiditySemanticsTest.php', 'test_raw_volume_is_never_overwritten_by_the_adjusted_analytical_volume'],
                ['tests/Unit/MarketData/ActualAndProxyLiquiditySemanticsTest.php', 'test_the_proxy_uses_the_raw_series_even_when_indicators_run_on_the_adjusted_one']
            ),
            'actual_traded_value_source_authority' => self::family(
                'MD-B13:actual-traded-value-source-authority',
                [
                    'app/Application/MarketData/Services/ActualTradedValueFactService.php',
                    'app/Application/MarketData/Services/EodBarsIngestService.php',
                ],
                ['tests/Unit/MarketData/ActualTradedValueProvenanceTest.php', 'test_a_complete_source_backed_fact_is_storable'],
                ['tests/Unit/MarketData/ActualTradedValueProvenanceTest.php', 'test_a_proxy_derived_value_cannot_enter_the_actual_field']
            ),
            'proxy_construction_and_basis' => self::family(
                'MD-B13:proxy-construction-and-basis',
                ['app/Application/MarketData/Services/IndicatorVectorService.php'],
                ['tests/Unit/MarketData/ActualAndProxyLiquiditySemanticsTest.php', 'test_the_proxy_is_raw_close_times_raw_volume_averaged_over_the_declared_window'],
                ['tests/Unit/MarketData/ActualAndProxyLiquiditySemanticsTest.php', 'test_a_missing_volume_anywhere_in_the_window_yields_null_rather_than_a_partial_average']
            ),
            'rolling_actual_and_proxy_separation' => self::family(
                'MD-B13:rolling-actual-and-proxy-separation',
                [
                    'app/Application/MarketData/Services/IndicatorVectorService.php',
                    'app/Infrastructure/Persistence/MarketData/MarketDataReadProductRepository.php',
                ],
                ['tests/Unit/MarketData/ActualAndProxyLiquiditySemanticsTest.php', 'test_the_legacy_alias_carries_the_proxy_value_and_never_the_actual'],
                ['tests/Unit/MarketData/ActualAndProxyLiquiditySemanticsTest.php', 'test_the_actual_field_never_receives_the_proxy_value']
            ),
            'persisted_proxy_labelling' => self::family(
                'MD-B13:persisted-proxy-labelling',
                [
                    'app/Application/MarketData/Services/LiquidityMetricLabelService.php',
                    'app/Domain/MarketData/LiquidityMetricLabelRegistry.php',
                    'app/Application/MarketData/Services/MarketDataPipelineService.php',
                    'database/migrations/2026_08_27_000001_add_liquidity_metric_labelling_and_volume_unit_identity.php',
                ],
                ['tests/Unit/MarketData/LiquidityMetricLabellingTest.php', 'test_declared_labels_persist_and_resolve_from_storage'],
                ['tests/Unit/MarketData/UnlabelledLiquidityMetricPublicationTest.php', 'test_a_populated_metric_with_no_persisted_label_blocks_publication']
            ),
            'alias_governance_and_naming' => self::family(
                'MD-B13:alias-governance-and-naming',
                [
                    'app/Domain/MarketData/LiquidityMetricLabelRegistry.php',
                    'app/Application/MarketData/Services/LiquidityMetricLabelService.php',
                ],
                ['tests/Unit/MarketData/LiquidityMetricLabellingTest.php', 'test_the_legacy_alias_declares_its_target_and_its_retirement_condition'],
                ['tests/Unit/MarketData/LiquidityMetricLabellingTest.php', 'test_an_alias_whose_retirement_condition_was_emptied_is_reported_as_drift']
            ),
            'lot_size_boundary' => self::family(
                'MD-B13:lot-size-boundary',
                [
                    'app/Application/MarketData/Services/VolumeUnitNormalizationService.php',
                    'app/Domain/MarketData/MarketDataSemanticBindings.php',
                ],
                ['tests/Unit/MarketData/VolumeUnitAndTurnoverBoundaryTest.php', 'test_market_data_declares_only_one_canonical_volume_unit'],
                ['tests/Unit/MarketData/VolumeUnitAndTurnoverBoundaryTest.php', 'test_a_lot_reporting_source_fails_closed_instead_of_being_converted']
            ),
            'precision_and_correction_lineage' => self::family(
                'MD-B13:precision-and-correction-lineage',
                [
                    'app/Application/MarketData/Services/IndicatorVectorService.php',
                    'app/Application/MarketData/Services/ActualTradedValueFactService.php',
                ],
                ['tests/Unit/MarketData/ActualAndProxyLiquiditySemanticsTest.php', 'test_the_proxy_is_rounded_only_at_the_storage_boundary'],
                ['tests/Unit/MarketData/ActualAndProxyLiquiditySemanticsTest.php', 'test_a_proxy_repair_never_rewrites_the_raw_volume_it_was_computed_from']
            ),
            'capability_boundary_and_citation_limit' => self::family(
                'MD-B13:capability-boundary-and-citation-limit',
                [
                    'app/Domain/MarketData/LiquidityMetricLabelRegistry.php',
                    'app/Infrastructure/Persistence/MarketData/MarketDataReadProductRepository.php',
                ],
                ['tests/Unit/MarketData/ActualAndProxyLiquiditySemanticsTest.php', 'test_every_declared_liquidity_metric_states_its_kind_units_and_market_scope'],
                ['tests/Unit/MarketData/ActualAndProxyLiquiditySemanticsTest.php', 'test_every_declared_proxy_states_its_window_and_every_per_bar_actual_does_not']
            ),
            'metric_domain_boundary' => self::family(
                'MD-B13:metric-domain-boundary',
                [
                    'app/Domain/MarketData/LiquidityMetricLabelRegistry.php',
                    'app/Infrastructure/Persistence/MarketData/MarketDataReadProductRepository.php',
                ],
                ['tests/Unit/MarketData/ActualAndProxyLiquiditySemanticsTest.php', 'test_no_liquidity_metric_carries_a_ranking_or_recommendation_semantic'],
                ['tests/Unit/MarketData/ActualAndProxyLiquiditySemanticsTest.php', 'test_a_metric_field_implying_traded_value_without_its_basis_is_not_declarable']
            ),
        ];
    }

    /** @var array<string,array<int,string>> */
    private const FAMILY_RULES = [
        'canonical_volume_unit_identity' => ['MD-S086-R0002', 'MD-S086-R0003', 'MD-S086-R0004', 'MD-S086-R0005'],
        'adjusted_volume_separation' => ['MD-S086-R0006'],
        'actual_traded_value_source_authority' => ['MD-S086-R0007', 'MD-S086-R0008', 'MD-S086-R0009', 'MD-S086-R0010'],
        'proxy_construction_and_basis' => ['MD-S086-R0012', 'MD-S086-R0013', 'MD-S086-R0014'],
        'rolling_actual_and_proxy_separation' => ['MD-S086-R0015', 'MD-S086-R0016', 'MD-S086-R0017', 'MD-S086-R0018'],
        'persisted_proxy_labelling' => ['MD-S086-R0019', 'MD-S086-R0020', 'MD-S086-R0021', 'MD-S086-R0022'],
        'alias_governance_and_naming' => ['MD-S086-R0024', 'MD-S086-R0026', 'MD-S086-R0027', 'MD-S086-R0028'],
        'lot_size_boundary' => ['MD-S086-R0029'],
        'precision_and_correction_lineage' => ['MD-S086-R0030', 'MD-S086-R0031', 'MD-S086-R0032', 'MD-S086-R0033'],
        'capability_boundary_and_citation_limit' => ['MD-S086-R0034', 'MD-S086-R0040', 'MD-S086-R0041'],
        'metric_domain_boundary' => ['MD-S042-R0015'],
    ];

    public static function familyFor(array $row): string
    {
        $ruleId = (string) $row['rule_id'];
        foreach (self::FAMILY_RULES as $family => $rules) {
            if (in_array($ruleId, $rules, true)) {
                return $family;
            }
        }

        throw new RuntimeException('No proof family for '.$ruleId);
    }

    public static function entries(string $root): array
    {
        $out = [];
        foreach (MarketDataLiquidityMetricTraceabilitySpec::mandatory($root) as $row) {
            $out[] = [
                'rule_id' => $row['rule_id'],
                'strategy_document_id' => $row['strategy_document_id'],
                'family' => self::familyFor($row),
            ];
        }

        return $out;
    }
}
