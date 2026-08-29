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
  'MD-S010-R0017'=>'event_risk_flag_semantics',
  'MD-S010-R0018'=>'event_risk_flag_semantics',
  'MD-S010-R0019'=>'event_risk_flag_semantics',
  'MD-S010-R0020'=>'event_risk_flag_semantics',
  'MD-S010-R0025'=>'event_risk_flag_semantics',
  'MD-S011-R0003'=>'detector_authority_boundary',
  'MD-S011-R0037'=>'continuity_diagnostic_boundary',
  'MD-S011-R0038'=>'continuity_diagnostic_boundary',
  'MD-S011-R0040'=>'continuity_diagnostic_boundary',
  'MD-S011-R0041'=>'continuity_diagnostic_boundary',
  'MD-S011-R0042'=>'continuity_diagnostic_boundary',
  'MD-S011-R0055'=>'raw_immutability',
  'MD-S080-R0010'=>'exchange_market_structure_authority',
  'MD-S080-R0011'=>'exchange_market_structure_authority',
  'MD-S080-R0012'=>'exchange_market_structure_authority',
  'MD-S080-R0026'=>'exchange_market_structure_authority',
  'MD-S080-R0028'=>'exchange_market_structure_authority',
  'MD-S080-R0032'=>'exchange_market_structure_authority',
  'MD-S080-R0045'=>'dual_use_fact_boundary',
  'MD-S080-R0061'=>'exchange_market_structure_authority',
  'MD-S084-R0002'=>'detector_authority_boundary',
  'MD-S084-R0004'=>'detector_authority_boundary',
  'MD-S084-R0005'=>'detector_authority_boundary',
  'MD-S084-R0006'=>'detector_authority_boundary',
  'MD-S084-R0007'=>'detector_authority_boundary',
  'MD-S084-R0042'=>'raw_immutability',
  'MD-S084-R0045'=>'detector_idempotency',
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
  'event_risk_flag_semantics'=>self::f('MD-B11:event-risk-flag-semantics',['app/Infrastructure/Persistence/MarketData/EventRiskSourceRepository.php','app/Application/MarketData/Services/EodEligibilityBuildService.php'],['tests/Unit/MarketData/EligibilityExplainabilityBoundaryTest.php','test_the_decision_consults_no_preference_input'],['tests/Unit/MarketData/CorporateActionWindowContaminationTest.php','test_eligibility_maps_contamination_to_its_own_reason_code']),
  'detector_authority_boundary'=>self::f('MD-B11:detector-authority-boundary',['app/Application/MarketData/Services/PriceScaleBreakDetectionService.php','app/Application/MarketData/Services/CorporateActionDerivationService.php'],['tests/Unit/MarketData/CorporateActionLifecycleB11RegressionTest.php','test_detector_is_candidate_only_and_uses_v2_ex_date_linkage'],['tests/Unit/MarketData/DerivationFillsRecordedActionTest.php','test_unexplained_price_break_never_creates_a_synthetic_action']),
  'exchange_market_structure_authority'=>self::f('MD-B11:exchange-market-structure-authority',['app/Application/MarketData/Services/AuthoritativeExchangeMarketStructureService.php'],['tests/Unit/MarketData/RecordAuthoritativeExchangeMarketStructureCommandTest.php','test_apply_records_effective_dated_tiers_and_evidence_without_touching_output'],['tests/Unit/MarketData/RecordAuthoritativeExchangeMarketStructureCommandTest.php','test_incomplete_scope_board_drift_and_stage_eight_field_are_rejected']),
  'dual_use_fact_boundary'=>self::f('MD-B11:dual-use-fact-boundary',['app/Application/MarketData/Services/AuthoritativeExchangeMarketStructureService.php'],['tests/Unit/MarketData/DualUseFactAndContractAlignmentTest.php','test_each_dual_use_fact_carries_only_its_market_data_half'],['tests/Unit/MarketData/DualUseFactAndContractAlignmentTest.php','test_no_active_document_overrides_the_owner_boundary_definition']),
  'detector_idempotency'=>self::f('MD-B11:detector-idempotency',['app/Application/MarketData/Services/PriceScaleBreakDetectionService.php'],['tests/Unit/MarketData/PriceScaleBreakDetectionTest.php','test_detection_is_idempotent_and_dry_run_is_non_mutating'],['tests/Unit/MarketData/PriceScaleBreakDetectionTest.php','test_dismissal_requires_positive_evidence_and_is_append_only']),
 ];}
 public static function entries(string $root): array {$out=[];foreach(MarketDataCorporateActionTraceabilitySpec::mandatory($root) as $r){$family=self::ruleFamilyOverride()[(string)$r['rule_id']]??self::expectedFamilyForDocument((string)$r['strategy_document_id']);$out[]=['rule_id'=>$r['rule_id'],'strategy_document_id'=>$r['strategy_document_id'],'family'=>$family];}return $out;}
}
