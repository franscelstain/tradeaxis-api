<?php

require_once __DIR__.'/MarketDataEligibilitySnapshotTraceabilitySpec.php';

/**
 * Governed `MD-B16-A001` proof map: every mandatory predicate this stage owns, bound to the
 * implementation surface that carries it and to the two executed guards that establish it.
 *
 * Each family names a positive guard and a distinct fail-closed guard. Every method named here
 * is verified to exist before any binding.
 */
final class MarketDataEligibilitySnapshotProofSpec
{
    public const STAGE = 'MD-B16';

    public const ATTEMPT = 'MD-B16-A001';

    public const BASELINE = 'MD-B16-A001-BL001';

    public const CI = 'CI-MD-B16-A001-001';

    public const EXPECTED_DENOMINATOR = 75;

    /** @return array<string,array<string,mixed>> */
    public static function families(): array
    {
        return [
            'acceptance_separability' => [
                'owner' => 'MD-B16:acceptance-separability',
                'implementation' => [
                    'app/Application/MarketData/Services/EodEligibilityBuildService.php',
                    'app/Infrastructure/Persistence/MarketData/MarketDataReadProductRepository.php',
                ],
                'positive' => ['tests/Unit/MarketData/EligibilityFirstClassFactDimensionsTest.php', 'test_the_new_dimensions_are_protected_against_being_dropped_in_snapshot_or_promote'],
                'negative' => ['tests/Unit/MarketData/StageThreeWriteCompletenessGuardTest.php', 'test_every_mutating_repository_path_invokes_the_relevant_guard'],
                'runtime_required' => true,
            ],
            'decision_fields' => [
                'owner' => 'MD-B16:decision-fields',
                'implementation' => [
                    'app/Application/MarketData/Services/EodEligibilityBuildService.php',
                    'app/Infrastructure/Persistence/MarketData/EodArtifactRepository.php',
                ],
                'positive' => ['tests/Unit/MarketData/StageThreeWriteCompletenessGuardTest.php', 'test_each_eligibility_fact_field_is_required'],
                'negative' => ['tests/Unit/MarketData/AliasNamingAndMeaningBoundaryTest.php', 'test_data_usable_is_the_canonical_field_and_eligible_is_only_its_alias'],
                'runtime_required' => true,
            ],
            'degraded_behaviour' => [
                'owner' => 'MD-B16:degraded-behaviour',
                'implementation' => [
                    'app/Application/MarketData/Services/EodEligibilityBuildService.php',
                    'app/Application/MarketData/Services/EligibilityDecisionService.php',
                ],
                'positive' => ['tests/Unit/MarketData/EligibilityExplainabilityBoundaryTest.php', 'test_a_missing_bar_produces_a_blocked_decision_with_a_reason'],
                'negative' => ['tests/Unit/MarketData/EligibilityFirstClassFactDimensionsTest.php', 'test_each_dimension_follows_its_own_input'],
                'runtime_required' => true,
            ],
            'eligibility_meaning' => [
                'owner' => 'MD-B16:eligibility-meaning',
                'implementation' => [
                    'app/Application/MarketData/Services/EligibilityDecisionService.php',
                ],
                'positive' => ['tests/Unit/MarketData/EligibilityExplainabilityBoundaryTest.php', 'test_eligible_means_usable_data_and_carries_no_selection_verdict'],
                'negative' => ['tests/Unit/MarketData/EligibilityExplainabilityBoundaryTest.php', 'test_a_missing_bar_produces_a_blocked_decision_with_a_reason'],
                'runtime_required' => true,
            ],
            'expectation_delivery_dimension' => [
                'owner' => 'MD-B16:expectation-delivery-dimension',
                'implementation' => [
                    'app/Application/MarketData/Services/EodEligibilityBuildService.php',
                    'app/Application/MarketData/Services/ExpectedBarDecisionService.php',
                ],
                'positive' => ['tests/Unit/MarketData/EligibilityFirstClassFactDimensionsTest.php', 'test_every_row_persists_the_four_previously_absent_dimensions'],
                'negative' => ['tests/Unit/MarketData/EligibilityExplainabilityBoundaryTest.php', 'test_a_missing_bar_produces_a_blocked_decision_with_a_reason'],
                'runtime_required' => true,
            ],
            'explanation_on_usable_rows' => [
                'owner' => 'MD-B16:explanation-on-usable-rows',
                'implementation' => [
                    'app/Application/MarketData/Services/EodEligibilityBuildService.php',
                ],
                'positive' => ['tests/Unit/MarketData/EligibilityFirstClassFactDimensionsTest.php', 'test_a_valid_row_with_field_nulls_is_distinguishable_from_a_fully_populated_one'],
                'negative' => ['tests/Unit/MarketData/EligibilityExplainabilityBoundaryTest.php', 'test_eligible_means_usable_data_and_carries_no_selection_verdict'],
                'runtime_required' => true,
            ],
            'gate_separation' => [
                'owner' => 'MD-B16:gate-separation',
                'implementation' => [
                    'app/Application/MarketData/Services/EodEligibilityBuildService.php',
                    'app/Application/MarketData/Services/CoverageGateEvaluator.php',
                ],
                'positive' => ['tests/Unit/MarketData/FactDimensionSeparationAndProductTableTest.php', 'test_the_five_fact_dimensions_are_separate_under_the_platform_baseline'],
                'negative' => ['tests/Unit/MarketData/FactDimensionSeparationAndProductTableTest.php', 'test_the_five_fact_dimensions_are_separate_under_the_boundary_contract'],
                'runtime_required' => true,
            ],
            'immutability_replay' => [
                'owner' => 'MD-B16:immutability-replay',
                'implementation' => [
                    'app/Infrastructure/Persistence/MarketData/EodArtifactRepository.php',
                    'app/Application/MarketData/Services/MarketDataPipelineService.php',
                ],
                'positive' => ['tests/Unit/MarketData/StageThreeWriteCompletenessGuardTest.php', 'test_snapshot_and_promote_preserve_every_protected_bar_and_eligibility_field'],
                'negative' => ['tests/Unit/MarketData/StageThreeWriteCompletenessGuardTest.php', 'test_promote_rolls_back_all_current_changes_when_history_is_incomplete'],
                'runtime_required' => true,
            ],
            'indicator_state_dimension' => [
                'owner' => 'MD-B16:indicator-state-dimension',
                'implementation' => [
                    'app/Application/MarketData/Services/EodEligibilityBuildService.php',
                    'app/Application/MarketData/Services/IndicatorVectorService.php',
                ],
                'positive' => ['tests/Unit/MarketData/EligibilityFirstClassFactDimensionsTest.php', 'test_a_valid_row_with_field_nulls_is_distinguishable_from_a_fully_populated_one'],
                'negative' => ['tests/Unit/MarketData/IndicatorFieldRegistryAndNullReasonsTest.php', 'test_a_short_history_row_carries_field_level_reasons_not_only_the_primary_reason'],
                'runtime_required' => true,
            ],
            'invalid_bar_boundary' => [
                'owner' => 'MD-B16:invalid-bar-boundary',
                'implementation' => [
                    'app/Infrastructure/Persistence/MarketData/EodArtifactRepository.php',
                ],
                'positive' => ['tests/Unit/MarketData/CanonicalRawImportBoundaryTest.php', 'test_a_zero_price_placeholder_cannot_become_canonical'],
                'negative' => ['tests/Unit/MarketData/StageThreeWriteCompletenessGuardTest.php', 'test_partial_upsert_rejects_incomplete_lineage_before_touching_the_existing_row'],
                'runtime_required' => true,
            ],
            'liquidity_dimension' => [
                'owner' => 'MD-B16:liquidity-dimension',
                'implementation' => [
                    'app/Application/MarketData/Services/EodEligibilityBuildService.php',
                    'app/Application/MarketData/Services/IndicatorVectorService.php',
                ],
                'positive' => ['tests/Unit/MarketData/ActualAndProxyLiquiditySemanticsTest.php', 'test_every_declared_proxy_states_its_window_and_every_per_bar_actual_does_not'],
                'negative' => ['tests/Unit/MarketData/LiquidityMetricLabellingTest.php', 'test_an_unlabelled_populated_metric_is_not_assumed_to_be_a_proxy'],
                'runtime_required' => true,
            ],
            'liquidity_never_blocks' => [
                'owner' => 'MD-B16:liquidity-never-blocks',
                'implementation' => [
                    'app/Application/MarketData/Services/EligibilityDecisionService.php',
                    'app/Application/MarketData/Services/CoverageGateEvaluator.php',
                ],
                'positive' => ['tests/Unit/MarketData/EligibilityExplainabilityBoundaryTest.php', 'test_zero_volume_alone_does_not_block_a_row'],
                'negative' => ['tests/Unit/MarketData/CoverageDormantUniverseTest.php', 'test_a_dormant_ticker_stays_in_the_denominator'],
                'runtime_required' => true,
            ],
            'no_overloaded_reason_code' => [
                'owner' => 'MD-B16:no-overloaded-reason-code',
                'implementation' => [
                    'app/Application/MarketData/Services/EodEligibilityBuildService.php',
                ],
                'positive' => ['tests/Unit/MarketData/EligibilityFirstClassFactDimensionsTest.php', 'test_no_dimension_is_a_delimited_composite'],
                'negative' => ['tests/Unit/MarketData/EligibilityFirstClassFactDimensionsTest.php', 'test_every_row_persists_the_four_previously_absent_dimensions'],
                'runtime_required' => true,
            ],
            'price_basis_contamination_dimension' => [
                'owner' => 'MD-B16:price-basis-contamination-dimension',
                'implementation' => [
                    'app/Application/MarketData/Services/EodEligibilityBuildService.php',
                    'app/Application/MarketData/Services/IndicatorVectorService.php',
                ],
                'positive' => ['tests/Unit/MarketData/EligibilityFirstClassFactDimensionsTest.php', 'test_no_dimension_is_a_delimited_composite'],
                'negative' => ['tests/Unit/MarketData/FactDimensionSeparationAndProductTableTest.php', 'test_provider_adjusted_close_is_not_a_price_product_and_never_a_fallback'],
                'runtime_required' => true,
            ],
            'publication_readability' => [
                'owner' => 'MD-B16:publication-readability',
                'implementation' => [
                    'app/Application/MarketData/Services/MarketDataPipelineService.php',
                    'app/Infrastructure/Persistence/MarketData/MarketDataReadProductRepository.php',
                ],
                'positive' => ['tests/Unit/MarketData/FactDimensionSeparationAndProductTableTest.php', 'test_a_partial_import_cannot_become_a_readable_publication'],
                'negative' => ['tests/Unit/MarketData/OperationalCommandSafetyBoundaryTest.php', 'test_a_failed_run_can_never_be_reported_readable'],
                'runtime_required' => true,
            ],
            'quality_dimension' => [
                'owner' => 'MD-B16:quality-dimension',
                'implementation' => [
                    'app/Application/MarketData/Services/EodEligibilityBuildService.php',
                    'app/Application/MarketData/Services/EligibilityDecisionService.php',
                ],
                'positive' => ['tests/Unit/MarketData/EligibilityExplainabilityBoundaryTest.php', 'test_an_invalid_indicator_carries_its_specific_cause'],
                'negative' => ['tests/Unit/MarketData/EligibilityExplainabilityBoundaryTest.php', 'test_missing_indicators_are_reported_separately_from_a_missing_bar'],
                'runtime_required' => true,
            ],
            'reason_families' => [
                'owner' => 'MD-B16:reason-families',
                'implementation' => [
                    'app/Application/MarketData/Services/EodEligibilityBuildService.php',
                    'app/Application/MarketData/Services/EligibilityDecisionService.php',
                ],
                'positive' => ['tests/Unit/MarketData/EligibilityExplainabilityBoundaryTest.php', 'test_an_invalid_indicator_carries_its_specific_cause'],
                'negative' => ['tests/Unit/MarketData/ReasonCodeSeedExecutionTest.php', 'test_every_registry_code_reaches_the_table'],
                'runtime_required' => true,
            ],
            'reason_set_retention' => [
                'owner' => 'MD-B16:reason-set-retention',
                'implementation' => [
                    'app/Application/MarketData/Services/EodEligibilityBuildService.php',
                ],
                'positive' => ['tests/Unit/MarketData/StageThreeEligibilityProducerTest.php', 'test_producer_writes_explicit_facts_without_changing_the_decision'],
                'negative' => ['tests/Unit/MarketData/EligibilityExplainabilityBoundaryTest.php', 'test_missing_indicators_are_reported_separately_from_a_missing_bar'],
                'runtime_required' => true,
            ],
            'registry_only_codes' => [
                'owner' => 'MD-B16:registry-only-codes',
                'implementation' => [
                    'app/Application/MarketData/Services/EodEligibilityBuildService.php',
                    'app/Application/MarketData/Services/EligibilityDecisionService.php',
                ],
                'positive' => ['tests/Unit/MarketData/ReasonCodeSeedExecutionTest.php', 'test_every_registry_code_reaches_the_table'],
                'negative' => ['tests/Unit/MarketData/EligibilityFirstClassFactDimensionsTest.php', 'test_no_dimension_is_a_delimited_composite'],
                'runtime_required' => true,
            ],
            'row_scope_identity' => [
                'owner' => 'MD-B16:row-scope-identity',
                'implementation' => [
                    'app/Application/MarketData/Services/EodEligibilityBuildService.php',
                    'app/Infrastructure/Persistence/MarketData/EodArtifactRepository.php',
                ],
                'positive' => ['tests/Unit/MarketData/StageThreeEligibilityProducerTest.php', 'test_producer_writes_explicit_facts_without_changing_the_decision'],
                'negative' => ['tests/Unit/MarketData/StageThreeWriteCompletenessGuardTest.php', 'test_each_eligibility_fact_field_is_required'],
                'runtime_required' => true,
            ],
            'shortened_session_canonical' => [
                'owner' => 'MD-B16:shortened-session-canonical',
                'implementation' => [
                    'app/Infrastructure/Persistence/MarketData/EodArtifactRepository.php',
                    'app/Application/MarketData/Services/CoverageGateEvaluator.php',
                ],
                'positive' => ['tests/Unit/MarketData/CanonicalRawImportBoundaryTest.php', 'test_flat_positive_zero_volume_row_remains_canonical'],
                'negative' => ['tests/Unit/MarketData/SourceFailureResilienceTest.php', 'test_a_healthy_run_still_reaches_a_readable_publication'],
                'runtime_required' => true,
            ],
            'source_provenance_dimension' => [
                'owner' => 'MD-B16:source-provenance-dimension',
                'implementation' => [
                    'app/Application/MarketData/Services/EodEligibilityBuildService.php',
                ],
                'positive' => ['tests/Unit/MarketData/EligibilityFirstClassFactDimensionsTest.php', 'test_each_dimension_follows_its_own_input'],
                'negative' => ['tests/Unit/MarketData/FactDimensionSeparationAndProductTableTest.php', 'test_immutable_source_observations_carry_provenance'],
                'runtime_required' => true,
            ],
            'status_event_dimension' => [
                'owner' => 'MD-B16:status-event-dimension',
                'implementation' => [
                    'app/Application/MarketData/Services/EodEligibilityBuildService.php',
                    'app/Application/MarketData/Services/ExpectedBarDecisionService.php',
                ],
                'positive' => ['tests/Unit/MarketData/StageThreeEligibilityProducerTest.php', 'test_producer_writes_explicit_facts_without_changing_the_decision'],
                'negative' => ['tests/Unit/MarketData/CoverageGateEvaluatorTest.php', 'test_evaluator_excludes_source_backed_suspended_tickers_from_expected_universe'],
                'runtime_required' => true,
            ],
            'upstream_only_boundary' => [
                'owner' => 'MD-B16:upstream-only-boundary',
                'implementation' => [
                    'app/Application/MarketData/Services/EligibilityDecisionService.php',
                    'app/Application/MarketData/Services/EodEligibilityBuildService.php',
                ],
                'positive' => ['tests/Unit/MarketData/EligibilityExplainabilityBoundaryTest.php', 'test_the_decision_consults_no_preference_input'],
                'negative' => ['tests/Unit/MarketData/TerminologyOwnerVocabularyTest.php', 'test_config_keeps_data_usable_canonical_and_eligible_only_as_compatibility'],
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
            throw new RuntimeException('No MD-B16 proof family for '.$ruleId);
        }

        return $map[$ruleId];
    }

    /** @return array<string,string> */
    public static function ruleFamilies(): array
    {
        return [
            // acceptance_separability (2)
            'MD-S027-R0063' => 'acceptance_separability',
            'MD-S031-R0026' => 'acceptance_separability',
            // decision_fields (4)
            'MD-S027-R0021' => 'decision_fields',
            'MD-S027-R0022' => 'decision_fields',
            'MD-S027-R0023' => 'decision_fields',
            'MD-S027-R0024' => 'decision_fields',
            // degraded_behaviour (5)
            'MD-S031-R0003' => 'degraded_behaviour',
            'MD-S031-R0004' => 'degraded_behaviour',
            'MD-S031-R0005' => 'degraded_behaviour',
            'MD-S031-R0008' => 'degraded_behaviour',
            'MD-S031-R0009' => 'degraded_behaviour',
            // eligibility_meaning (2)
            'MD-S027-R0032' => 'eligibility_meaning',
            'MD-S027-R0033' => 'eligibility_meaning',
            // expectation_delivery_dimension (3)
            'MD-S027-R0006' => 'expectation_delivery_dimension',
            'MD-S027-R0007' => 'expectation_delivery_dimension',
            'MD-S027-R0008' => 'expectation_delivery_dimension',
            // explanation_on_usable_rows (1)
            'MD-S027-R0031' => 'explanation_on_usable_rows',
            // gate_separation (4)
            'MD-S027-R0043' => 'gate_separation',
            'MD-S027-R0044' => 'gate_separation',
            'MD-S027-R0046' => 'gate_separation',
            'MD-S027-R0047' => 'gate_separation',
            // immutability_replay (2)
            'MD-S027-R0062' => 'immutability_replay',
            'MD-S031-R0019' => 'immutability_replay',
            // indicator_state_dimension (3)
            'MD-S027-R0013' => 'indicator_state_dimension',
            'MD-S031-R0006' => 'indicator_state_dimension',
            'MD-S031-R0007' => 'indicator_state_dimension',
            // invalid_bar_boundary (1)
            'MD-S039-R0004' => 'invalid_bar_boundary',
            // liquidity_dimension (4)
            'MD-S027-R0014' => 'liquidity_dimension',
            'MD-S027-R0015' => 'liquidity_dimension',
            'MD-S027-R0016' => 'liquidity_dimension',
            'MD-S027-R0017' => 'liquidity_dimension',
            // liquidity_never_blocks (3)
            'MD-S027-R0045' => 'liquidity_never_blocks',
            'MD-S027-R0048' => 'liquidity_never_blocks',
            'MD-S031-R0010' => 'liquidity_never_blocks',
            // no_overloaded_reason_code (4)
            'MD-S027-R0025' => 'no_overloaded_reason_code',
            'MD-S027-R0028' => 'no_overloaded_reason_code',
            'MD-S027-R0029' => 'no_overloaded_reason_code',
            'MD-S027-R0030' => 'no_overloaded_reason_code',
            // price_basis_contamination_dimension (2)
            'MD-S027-R0012' => 'price_basis_contamination_dimension',
            'MD-S027-R0020' => 'price_basis_contamination_dimension',
            // publication_readability (6)
            'MD-S027-R0060' => 'publication_readability',
            'MD-S027-R0061' => 'publication_readability',
            'MD-S031-R0014' => 'publication_readability',
            'MD-S031-R0015' => 'publication_readability',
            'MD-S031-R0016' => 'publication_readability',
            'MD-S031-R0017' => 'publication_readability',
            // quality_dimension (2)
            'MD-S027-R0010' => 'quality_dimension',
            'MD-S027-R0011' => 'quality_dimension',
            // reason_families (7)
            'MD-S027-R0051' => 'reason_families',
            'MD-S027-R0052' => 'reason_families',
            'MD-S027-R0053' => 'reason_families',
            'MD-S027-R0054' => 'reason_families',
            'MD-S027-R0055' => 'reason_families',
            'MD-S027-R0056' => 'reason_families',
            'MD-S027-R0058' => 'reason_families',
            // reason_set_retention (4)
            'MD-S027-R0049' => 'reason_set_retention',
            'MD-S031-R0011' => 'reason_set_retention',
            'MD-S031-R0012' => 'reason_set_retention',
            'MD-S085-R0446' => 'reason_set_retention',
            // registry_only_codes (1)
            'MD-S027-R0059' => 'registry_only_codes',
            // row_scope_identity (5)
            'MD-S019-R0080' => 'row_scope_identity',
            'MD-S019-R0081' => 'row_scope_identity',
            'MD-S027-R0003' => 'row_scope_identity',
            'MD-S027-R0004' => 'row_scope_identity',
            'MD-S031-R0002' => 'row_scope_identity',
            // shortened_session_canonical (1)
            'MD-S041-R0042' => 'shortened_session_canonical',
            // source_provenance_dimension (1)
            'MD-S027-R0009' => 'source_provenance_dimension',
            // status_event_dimension (4)
            'MD-S027-R0018' => 'status_event_dimension',
            'MD-S027-R0019' => 'status_event_dimension',
            'MD-S058-R0045' => 'status_event_dimension',
            'MD-S058-R0046' => 'status_event_dimension',
            // upstream_only_boundary (4)
            'MD-S027-R0034' => 'upstream_only_boundary',
            'MD-S027-R0042' => 'upstream_only_boundary',
            'MD-S031-R0025' => 'upstream_only_boundary',
            'MD-S058-R0047' => 'upstream_only_boundary',
        ];
    }

    /** @return array<int,array{rule_id:string,strategy_document_id:string,family:string}> */
    public static function entries(string $root): array
    {
        $out = [];
        foreach (MarketDataEligibilitySnapshotTraceabilitySpec::mandatory($root) as $row) {
            $out[] = [
                'rule_id' => $row['rule_id'],
                'strategy_document_id' => $row['strategy_document_id'],
                'family' => self::familyFor($row),
            ];
        }

        return $out;
    }
}
