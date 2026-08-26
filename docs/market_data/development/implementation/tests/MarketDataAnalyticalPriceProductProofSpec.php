<?php
require_once __DIR__.'/MarketDataAnalyticalPriceProductTraceabilitySpec.php';
final class MarketDataAnalyticalPriceProductProofSpec
{
    public const STAGE='MD-B12'; public const ATTEMPT='MD-B12-A001'; public const BASELINE='MD-B12-A001-BL001'; public const CI='CI-MD-B12-A001-001'; public const EXPECTED_DENOMINATOR=45;
    private static function f($owner,$impl,$positive,$negative){return ['owner'=>$owner,'implementation'=>$impl,'positive'=>$positive,'negative'=>$negative,'runtime_required'=>true];}
    public static function families(): array { return [
        'product_boundary'=>self::f('MD-B12:product-boundary',['app/Application/MarketData/Services/AnalyticalPriceProductService.php','app/Application/MarketData/Services/AnalyticalProductIdentityService.php'],['tests/Unit/MarketData/AnalyticalPriceProductServiceTest.php','test_raw_is_immutable_pass_through_and_provider_adj_close_is_not_selected'],['tests/Unit/MarketData/AnalyticalPriceProductServiceTest.php','test_total_return_is_distinct_and_unavailable_without_governed_distribution_formula']),
        'fail_closed_basis'=>self::f('MD-B12:fail-closed-basis',['app/Application/MarketData/Services/AnalyticalPriceProductService.php'],['tests/Unit/MarketData/AnalyticalPriceProductServiceTest.php','test_future_factor_is_not_admitted_to_earlier_as_of_and_future_bar_fails_closed'],['tests/Unit/MarketData/AnalyticalPriceProductServiceTest.php','test_optional_null_volume_factor_preserves_volume_but_required_null_fails_closed']),
        'persisted_identity'=>self::f('MD-B12:persisted-identity',['app/Application/MarketData/Services/EodIndicatorsComputeService.php','app/Application/MarketData/Services/IndicatorVectorService.php'],['tests/Unit/MarketData/AnalyticalProductIdentityServiceTest.php','test_empty_factor_set_still_has_a_stable_structural_adjusted_identity'],['tests/Unit/MarketData/AnalyticalProductIdentityServiceTest.php','test_product_versions_are_explicit_and_unknown_products_fail_closed']),
        'coherent_vector'=>self::f('MD-B12:coherent-vector',['app/Application/MarketData/Services/IndicatorVectorService.php','app/Application/MarketData/Services/AnalyticalPriceProductService.php'],['tests/Unit/MarketData/AnalyticalPriceProductServiceTest.php','test_structural_product_compounds_price_and_explicit_volume_factors_coherently'],['tests/Unit/MarketData/CoherentPriceProductBoundaryTest.php','test_legacy_adj_close_selector_cannot_become_an_analytical_fallback']),
        'factor_lineage'=>self::f('MD-B12:factor-lineage',['app/Application/MarketData/Services/AdjustmentFactorSetService.php','app/Application/MarketData/Services/AnalyticalPriceProductService.php'],['tests/Unit/MarketData/AdjustmentFactorSetB11Test.php','test_verified_split_ratio_maps_deterministically_to_price_and_volume_factors'],['tests/Unit/MarketData/AnalyticalPriceProductServiceTest.php','test_factor_lineage_and_persisted_identity_are_fail_closed_and_hash_is_deterministic']),
        'action_specific_volume'=>self::f('MD-B12:action-specific-volume',['app/Application/MarketData/Services/AdjustmentFactorSetService.php'],['tests/Unit/MarketData/AdjustmentFactorSetB11Test.php','test_volume_scaled_action_requires_explicit_action_specific_volume_factor'],['tests/Unit/MarketData/AdjustmentFactorSetB11Test.php','test_gap_unknown_cash_distribution_is_not_manufactured_into_structural_factor']),
        'determinism'=>self::f('MD-B12:determinism',['app/Application/MarketData/Services/AnalyticalPriceProductService.php','app/Domain/MarketData/MarketDataSemanticBindings.php'],['tests/Unit/MarketData/AnalyticalPriceProductServiceTest.php','test_factor_lineage_and_persisted_identity_are_fail_closed_and_hash_is_deterministic'],['tests/Unit/MarketData/AnalyticalProductIdentityServiceTest.php','test_factor_hash_is_order_independent_but_revision_sensitive']),
        'publication_contract'=>self::f('MD-B12:publication-contract',['app/Application/MarketData/Services/EodIndicatorsComputeService.php','app/Application/MarketData/Services/IndicatorVectorService.php'],['tests/Unit/MarketData/CoherentPriceProductBoundaryTest.php','test_the_persisted_vector_carries_its_price_product_code'],['tests/Unit/MarketData/BarPriceProductIdentityTest.php','test_the_read_gateway_withholds_unrecorded_or_non_raw_canonical_bar_identity']),
    ]; }
    public static function familyFor(array $r): string {
        $rid=(string)$r['rule_id'];
        if(in_array($rid,['MD-S008-R0006','MD-S012-R0002','MD-S012-R0003','MD-S012-R0004','MD-S012-R0005','MD-S012-R0006','MD-S083-R0002','MD-S083-R0004','MD-S083-R0005'],true)) return 'product_boundary';
        if(in_array($rid,['MD-S012-R0008','MD-S019-R0047','MD-S019-R0048','MD-S083-R0015','MD-S083-R0041','MD-S083-R0063','MD-S083-R0069'],true)) return 'fail_closed_basis';
        if(in_array($rid,['MD-S012-R0011','MD-S012-R0012','MD-S012-R0013','MD-S012-R0014','MD-S012-R0015','MD-S012-R0016','MD-S012-R0017','MD-S012-R0019','MD-S012-R0020','MD-S012-R0021','MD-S019-R0043','MD-S019-R0045'],true)) return 'persisted_identity';
        if(in_array($rid,['MD-S012-R0028','MD-S019-R0046','MD-S083-R0003','MD-S083-R0039'],true)) return 'coherent_vector';
        if(in_array($rid,['MD-S083-R0023','MD-S083-R0024','MD-S083-R0025','MD-S083-R0026','MD-S083-R0027','MD-S083-R0028','MD-S083-R0029'],true)) return 'factor_lineage';
        if($rid==='MD-S083-R0040') return 'action_specific_volume';
        if(in_array($rid,['MD-S020-R0009','MD-S083-R0053'],true)) return 'determinism';
        if(in_array($rid,['MD-S012-R0035','MD-S012-R0036','MD-S083-R0068'],true)) return 'publication_contract';
        throw new RuntimeException('No proof family for '.$rid);
    }
    public static function entries(string $root): array { $out=[]; foreach(MarketDataAnalyticalPriceProductTraceabilitySpec::mandatory($root) as $r) $out[]=['rule_id'=>$r['rule_id'],'strategy_document_id'=>$r['strategy_document_id'],'family'=>self::familyFor($r)]; return $out; }
}
