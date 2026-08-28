<?php
require_once __DIR__.'/MarketDataCorporateActionTraceabilitySpec.php';
final class MarketDataCorporateActionProofSpec
{
 public const STAGE='MD-B11'; public const ATTEMPT='MD-B11-A001'; public const BASELINE='MD-B11-A001-BL001'; public const CI='CI-MD-B11-A001-001'; public const EXPECTED_DENOMINATOR=172;
 public static function documentFamilyMap(): array {return ['MD-S008'=>'price_scale_validation','MD-S010'=>'impact_flags','MD-S011'=>'verified_event_lifecycle','MD-S023'=>'raw_immutability','MD-S059'=>'source_observation_boundary','MD-S079'=>'event_type_semantics','MD-S080'=>'market_structure_diagnostics','MD-S084'=>'anomaly_only_detector'];}
 /**
  * MD-B11-A002: family is assigned per document, which is right for 170 of the 172 predicates.
  * MD-S011-R0070 and R0071 are the exception: they prohibit a continuity-check diagnostic from
  * justifying an adjustment or clearing an ambiguity, and verified_event_lifecycle guards do not
  * prove that. Binding them there would have claimed proof the family does not carry.
  */
 public static function ruleFamilyOverride(): array {return [
  'MD-S011-R0070'=>'continuity_diagnostic_boundary',
  'MD-S011-R0071'=>'continuity_diagnostic_boundary',
 ];}
 public static function expectedFamilyForDocument(string $id){$m=self::documentFamilyMap();return $m[$id]??null;}
 private static function f($owner,$impl,$pos,$neg,$runtime=[]){return ['owner'=>$owner,'implementation'=>$impl,'positive'=>$pos,'negative'=>$neg,'runtime_scripts'=>$runtime,'runtime_required'=>true];}
 public static function families(): array {return [
  'price_scale_validation'=>self::f('MD-B11:price-scale-validation',['app/Application/MarketData/Services/PriceScaleBreakDetectionService.php'],['tests/Unit/MarketData/PriceScaleBreakDetectionTest.php','test_detects_a_persistent_split_and_infers_the_ratio_from_open'],['tests/Unit/MarketData/PriceScaleBreakDetectionTest.php','test_ignores_penny_price_oscillation_below_the_minimum_price']),
  'impact_flags'=>self::f('MD-B11:impact-flags',['app/Infrastructure/Persistence/MarketData/EventRiskSourceRepository.php','app/Application/MarketData/Services/EodIndicatorsComputeService.php'],['tests/Unit/MarketData/CorporateActionWindowContaminationTest.php','test_scaling_action_inside_window_quarantines_price_and_volume_indicators'],['tests/Unit/MarketData/CorporateActionLifecycleB11RegressionTest.php','test_event_risk_has_no_silent_action_date_to_ex_date_promotion']),
  'verified_event_lifecycle'=>self::f('MD-B11:verified-event-lifecycle',['app/Application/MarketData/Services/CorporateActionRevisionService.php','app/Application/MarketData/Services/CorporateActionExternalReconciliationService.php'],['tests/Unit/MarketData/CorporateActionRevisionServiceTest.php','test_revision_is_append_only_and_supersession_preserves_event_identity'],['tests/Unit/MarketData/CorporateActionExternalReconciliationServiceTest.php','test_incomplete_scope_never_qualifies_period_as_action_complete'],['docs/market_data/development/implementation/tests/MarketDataB11DeployedSchemaProbe.php']),
  'raw_immutability'=>self::f('MD-B11:raw-immutability',['app/Console/Commands/MarketData/DeriveCorporateActionsCommand.php','app/Console/Commands/MarketData/RepairPriceScaleStretchesCommand.php'],['tests/Unit/MarketData/CorporateActionLifecycleB11RegressionTest.php','test_direct_price_derived_event_and_bar_repair_commands_are_non_mutating'],['tests/Unit/MarketData/CorporateActionLifecycleB11RegressionTest.php','test_direct_price_derived_event_and_bar_repair_commands_are_non_mutating']),
  'source_observation_boundary'=>self::f('MD-B11:source-observation-boundary',['app/Application/MarketData/Services/CorporateActionRevisionService.php'],['tests/Unit/MarketData/CorporateActionRevisionServiceTest.php','test_verified_revision_requires_traceable_source_observation'],['tests/Unit/MarketData/CorporateActionRevisionServiceTest.php','test_manual_verified_requires_reviewer_and_evidence_reference']),
  'event_type_semantics'=>self::f('MD-B11:event-type-semantics',['app/Application/MarketData/Services/AdjustmentFactorSetService.php'],['tests/Unit/MarketData/AdjustmentFactorSetB11Test.php','test_verified_split_ratio_maps_deterministically_to_price_and_volume_factors'],['tests/Unit/MarketData/AdjustmentFactorSetB11Test.php','test_non_adjusting_registry_type_requires_no_factor']),
  'market_structure_diagnostics'=>self::f('MD-B11:market-structure-diagnostics',['app/Application/MarketData/Services/PriceScaleBreakDetectionService.php'],['tests/Unit/MarketData/CorporateActionLifecycleB11RegressionTest.php','test_detector_is_candidate_only_and_uses_v2_ex_date_linkage'],['tests/Unit/MarketData/CorporateActionLifecycleB11RegressionTest.php','test_detector_is_candidate_only_and_uses_v2_ex_date_linkage']),
  'anomaly_only_detector'=>self::f('MD-B11:anomaly-only-detector',['app/Application/MarketData/Services/PriceScaleBreakDetectionService.php','app/Infrastructure/Persistence/MarketData/PriceScaleBreakRepository.php'],['tests/Unit/MarketData/PriceScaleBreakDetectionTest.php','test_only_unresolved_candidates_feed_indicator_quarantine'],['tests/Unit/MarketData/PriceScaleBreakDetectionTest.php','test_dismissal_requires_positive_evidence_and_is_append_only']),
  'continuity_diagnostic_boundary'=>self::f('MD-B11:continuity-diagnostic-boundary',['app/Application/MarketData/Services/AdjustmentFactorSetService.php','app/Application/MarketData/Services/PriceScaleBreakDetectionService.php'],['tests/Unit/MarketData/CorporateActionLifecycleB11RegressionTest.php','test_the_continuity_diagnostic_never_reaches_an_authority_decision'],['tests/Unit/MarketData/PriceScaleBreakDetectionTest.php','test_dismissal_requires_positive_evidence_and_is_append_only']),
 ];}
 public static function entries(string $root): array {$out=[];foreach(MarketDataCorporateActionTraceabilitySpec::mandatory($root) as $r){$family=self::ruleFamilyOverride()[(string)$r['rule_id']]??self::expectedFamilyForDocument((string)$r['strategy_document_id']);$out[]=['rule_id'=>$r['rule_id'],'strategy_document_id'=>$r['strategy_document_id'],'family'=>$family];}return $out;}
}
