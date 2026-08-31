<?php

require_once __DIR__.'/MarketDataCoverageGateTraceabilitySpec.php';

/**
 * Governed `MD-B15-A001` proof map: every mandatory predicate this stage owns, bound to the
 * implementation surface that carries it and to the two executed guards that establish it.
 *
 * Each family names a positive guard and a distinct fail-closed guard. Every method named here
 * is verified to exist before any binding: a proof map is otherwise a list of intentions, and a
 * method never written reads exactly like one that runs.
 */
final class MarketDataCoverageGateProofSpec
{
    public const STAGE = 'MD-B15';

    public const ATTEMPT = 'MD-B15-A001';

    public const BASELINE = 'MD-B15-A001-BL001';

    public const CI = 'CI-MD-B15-A001-001';

    public const EXPECTED_DENOMINATOR = 221;

    /** @return array<string,array<string,mixed>> */
    public static function families(): array
    {
        return [
            'anti_bypass' => [
                'owner' => 'MD-B15:anti-bypass',
                'implementation' => [
                    'app/Application/MarketData/Services/CoverageGateEvaluator.php',
                    'app/Application/MarketData/Services/FinalizeDecisionService.php',
                    'app/Application/MarketData/Services/PublicationFinalizeOutcomeService.php',
                ],
                'positive' => ['tests/Unit/MarketData/CoverageGateNoBypassStaticGuardTest.php', 'test_publication_identity_is_not_compared_inline'],
                'negative' => ['tests/Unit/MarketData/CoverageTelemetryBypassTest.php', 'test_every_caller_shape_still_catches_a_ratio_that_was_asserted_not_derived'],
                'runtime_required' => true,
            ],
            'citation_boundary' => [
                'owner' => 'MD-B15:citation-boundary',
                'implementation' => [
                    'app/Application/MarketData/Services/CoverageGateEvaluator.php',
                ],
                'positive' => ['tests/Unit/MarketData/CoverageEdgeCaseBoundaryB15Test.php', 'test_no_surface_cites_a_coverage_pass_as_proof_of_correctness'],
                'negative' => ['tests/Unit/MarketData/CoverageSilentImprovementBoundaryTest.php', 'test_an_empty_universe_is_not_evaluable_rather_than_passing'],
                'runtime_required' => true,
            ],
            'command_enforcement' => [
                'owner' => 'MD-B15:command-enforcement',
                'implementation' => [
                    'app/Console/Commands/MarketData/PromoteMarketDataCommand.php',
                    'app/Application/MarketData/Services/MarketDataEvidenceExportService.php',
                ],
                'positive' => ['tests/Unit/MarketData/CoveragePolicyDocsStaticGuardTest.php', 'test_operator_surfaces_normalise_coverage_state_and_keep_the_legacy_trace'],
                'negative' => ['tests/Unit/MarketData/CoverageStateNormalizationTest.php', 'test_no_runtime_code_writes_the_retired_verdict'],
                'runtime_required' => true,
            ],
            'delayed_data' => [
                'owner' => 'MD-B15:delayed-data',
                'implementation' => [
                    'app/Application/MarketData/Services/FinalizeDecisionService.php',
                    'app/Application/MarketData/Services/MarketDataPipelineService.php',
                    'config/market_data.php',
                ],
                'positive' => ['tests/Unit/MarketData/CoverageEdgeCaseBoundaryB15Test.php', 'test_the_delay_window_is_a_governed_configuration_with_the_locked_default'],
                'negative' => ['tests/Unit/MarketData/FinalizeDecisionServiceTest.php', 'test_finalize_uses_coverage_fail_with_fallback_as_held_not_readable'],
                'runtime_required' => true,
            ],
            'delivery_numerator' => [
                'owner' => 'MD-B15:delivery-numerator',
                'implementation' => [
                    'app/Application/MarketData/Services/CoverageGateEvaluator.php',
                    'app/Infrastructure/Persistence/MarketData/EodPublicationRepository.php',
                ],
                'positive' => ['tests/Unit/MarketData/CandidateCoverageScopeTest.php', 'test_a_candidate_is_counted_by_its_own_bars_not_the_live_publication'],
                'negative' => ['tests/Unit/MarketData/CandidateCoverageScopeTest.php', 'test_bars_from_another_trade_date_are_not_counted'],
                'runtime_required' => true,
            ],
            'denominator_construction' => [
                'owner' => 'MD-B15:denominator-construction',
                'implementation' => [
                    'app/Application/MarketData/Services/CoverageGateEvaluator.php',
                ],
                'positive' => ['tests/Unit/MarketData/CoverageSilentImprovementBoundaryTest.php', 'test_the_raw_universe_and_the_expected_denominator_are_reported_separately'],
                'negative' => ['tests/Unit/MarketData/CoverageFinalizeExpectedDenominatorWiringTest.php', 'test_finalize_consumes_the_measured_expected_denominator_not_the_raw_universe'],
                'runtime_required' => true,
            ],
            'evidence_component_identity' => [
                'owner' => 'MD-B15:evidence-component-identity',
                'implementation' => [
                    'app/Application/MarketData/Services/CoverageGateEvaluator.php',
                    'app/Infrastructure/Persistence/MarketData/MarketCalendarRepository.php',
                    'app/Infrastructure/Persistence/MarketData/EventRiskSourceRepository.php',
                ],
                'positive' => ['tests/Unit/MarketData/CoverageEvidenceComponentIdentityTest.php', 'test_a_coverage_result_binds_the_resolver_and_revision_identities'],
                'negative' => ['tests/Unit/MarketData/CoverageEvidenceComponentIdentityTest.php', 'test_an_unresolvable_calendar_leaves_the_identity_null_rather_than_defaulted'],
                'runtime_required' => true,
            ],
            'evidence_fields' => [
                'owner' => 'MD-B15:evidence-fields',
                'implementation' => [
                    'app/Application/MarketData/Services/CoverageGateEvaluator.php',
                    'app/Application/MarketData/Services/MarketDataEvidenceExportService.php',
                ],
                'positive' => ['tests/Unit/MarketData/PublicationFinalizeOutcomeServiceTest.php', 'test_readable_outcome_requires_complete_coverage_summary'],
                'negative' => ['tests/Unit/MarketData/CoverageTelemetryBypassTest.php', 'test_coverage_that_cannot_be_proven_blocks_readable'],
                'runtime_required' => true,
            ],
            'exclusion_path_proof' => [
                'owner' => 'MD-B15:exclusion-path-proof',
                'implementation' => [
                    'app/Application/MarketData/Services/CoverageGateEvaluator.php',
                    'app/Infrastructure/Persistence/MarketData/EventRiskSourceRepository.php',
                ],
                'positive' => ['tests/Unit/MarketData/CoverageGateEvaluatorTest.php', 'test_evaluator_excludes_source_backed_suspended_tickers_from_expected_universe'],
                'negative' => ['tests/Unit/MarketData/CoverageSilentImprovementBoundaryTest.php', 'test_excluded_listings_are_named_not_only_counted'],
                'runtime_required' => true,
            ],
            'expectation_states' => [
                'owner' => 'MD-B15:expectation-states',
                'implementation' => [
                    'app/Application/MarketData/Services/CoverageGateEvaluator.php',
                    'app/Infrastructure/Persistence/MarketData/EventRiskSourceRepository.php',
                ],
                'positive' => ['tests/Unit/MarketData/CoverageSilentImprovementBoundaryTest.php', 'test_the_evaluator_reports_every_term_of_the_denominator'],
                'negative' => ['tests/Unit/MarketData/CoverageSilentImprovementBoundaryTest.php', 'test_an_uninterpretable_status_makes_a_listing_unknown_and_keeps_it_in_the_denominator'],
                'runtime_required' => true,
            ],
            'fallback_targets' => [
                'owner' => 'MD-B15:fallback-targets',
                'implementation' => [
                    'app/Application/MarketData/Services/FinalizeDecisionService.php',
                    'app/Application/MarketData/Services/PublicationFinalizeOutcomeService.php',
                ],
                'positive' => ['tests/Unit/MarketData/CoverageEdgeCaseBoundaryB15Test.php', 'test_no_forbidden_fallback_target_is_reachable'],
                'negative' => ['tests/Unit/MarketData/FinalizeDecisionServiceTest.php', 'test_finalize_uses_coverage_fail_without_fallback_as_failed_not_readable'],
                'runtime_required' => true,
            ],
            'finalize_enforcement' => [
                'owner' => 'MD-B15:finalize-enforcement',
                'implementation' => [
                    'app/Application/MarketData/Services/FinalizeDecisionService.php',
                    'app/Application/MarketData/Services/PublicationFinalizeOutcomeService.php',
                ],
                'positive' => ['tests/Unit/MarketData/FinalizeDecisionServiceTest.php', 'test_finalize_uses_coverage_pass_to_allow_readable_promotion'],
                'negative' => ['tests/Unit/MarketData/FinalizeDecisionServiceTest.php', 'test_finalize_uses_not_evaluable_as_non_readable_and_never_promotes'],
                'runtime_required' => true,
            ],
            'forbidden_exclusions' => [
                'owner' => 'MD-B15:forbidden-exclusions',
                'implementation' => [
                    'app/Application/MarketData/Services/CoverageGateEvaluator.php',
                    'app/Infrastructure/Persistence/MarketData/EventRiskSourceRepository.php',
                ],
                'positive' => ['tests/Unit/MarketData/CoverageDormantUniverseTest.php', 'test_a_dormant_ticker_stays_in_the_denominator'],
                'negative' => ['tests/Unit/MarketData/CoverageDormantUniverseTest.php', 'test_a_universe_of_dormant_tickers_reports_low_coverage_not_perfect_coverage'],
                'runtime_required' => true,
            ],
            'gate_states' => [
                'owner' => 'MD-B15:gate-states',
                'implementation' => [
                    'app/Application/MarketData/Services/CoverageGateEvaluator.php',
                    'app/Application/MarketData/Services/CoverageGateStateNormalizer.php',
                ],
                'positive' => ['tests/Unit/MarketData/CoverageGateEvaluatorTest.php', 'test_evaluator_returns_pass_when_available_matches_expected_universe'],
                'negative' => ['tests/Unit/MarketData/CoverageGateEvaluatorTest.php', 'test_evaluator_returns_not_evaluable_when_expected_universe_is_zero'],
                'runtime_required' => true,
            ],
            'implementation_mapping' => [
                'owner' => 'MD-B15:implementation-mapping',
                'implementation' => [
                    'app/Application/MarketData/Services/CoverageGateEvaluator.php',
                    'app/Application/MarketData/Services/FinalizeDecisionService.php',
                    'app/Application/MarketData/Services/MarketDataPipelineService.php',
                    'app/Application/MarketData/Services/EodBarsIngestService.php',
                    'app/Infrastructure/MarketData/Source/PublicApiEodBarsAdapter.php',
                ],
                'positive' => ['tests/Unit/MarketData/CoverageEdgeCaseBoundaryB15Test.php', 'test_every_runtime_class_the_contract_names_exists_and_owns_its_role'],
                'negative' => ['tests/Unit/MarketData/CoverageStateNormalizationTest.php', 'test_an_unrecognised_state_fails_closed'],
                'runtime_required' => true,
            ],
            'knowledge_cutoff_determinism' => [
                'owner' => 'MD-B15:knowledge-cutoff-determinism',
                'implementation' => [
                    'app/Application/MarketData/Services/CoverageGateEvaluator.php',
                    'app/Infrastructure/Persistence/MarketData/EventRiskSourceRepository.php',
                ],
                'positive' => ['tests/Unit/MarketData/CoverageDenominatorKnowledgeCutoffTest.php', 'test_denominator_at_a_fixed_cutoff_is_unmoved_by_a_later_recorded_suspension'],
                'negative' => ['tests/Unit/MarketData/CoverageDenominatorKnowledgeCutoffTest.php', 'test_without_a_cutoff_the_same_corpus_moves'],
                'runtime_required' => true,
            ],
            'legacy_blocked_normalization' => [
                'owner' => 'MD-B15:legacy-blocked-normalization',
                'implementation' => [
                    'app/Application/MarketData/Services/CoverageGateStateNormalizer.php',
                ],
                'positive' => ['tests/Unit/MarketData/CoverageStateNormalizationTest.php', 'test_the_legacy_verdict_becomes_not_evaluable'],
                'negative' => ['tests/Unit/MarketData/CoverageGateNoBypassStaticGuardTest.php', 'test_evaluator_never_emits_the_legacy_blocked_verdict'],
                'runtime_required' => true,
            ],
            'multi_source_boundary' => [
                'owner' => 'MD-B15:multi-source-boundary',
                'implementation' => [
                    'app/Application/MarketData/Services/EodBarsIngestService.php',
                    'app/Application/MarketData/Services/MarketDataPipelineService.php',
                ],
                'positive' => ['tests/Unit/MarketData/CoverageEdgeCaseBoundaryB15Test.php', 'test_no_configuration_permits_mixing_sources_inside_one_run'],
                'negative' => ['tests/Unit/MarketData/CoverageEdgeCaseBoundaryB15Test.php', 'test_the_pruned_multi_source_config_keys_have_not_returned'],
                'runtime_required' => true,
            ],
            'partial_dataset' => [
                'owner' => 'MD-B15:partial-dataset',
                'implementation' => [
                    'app/Application/MarketData/Services/FinalizeDecisionService.php',
                    'app/Application/MarketData/Services/MarketDataPipelineService.php',
                ],
                'positive' => ['tests/Unit/MarketData/FinalizeDecisionServiceTest.php', 'test_manual_file_partial_strict_without_fallback_stays_not_readable'],
                'negative' => ['tests/Unit/MarketData/FinalizeDecisionServiceTest.php', 'test_manual_file_partial_hybrid_with_fallback_is_held_and_keeps_fallback_effective_date'],
                'runtime_required' => true,
            ],
            'pointer_enforcement' => [
                'owner' => 'MD-B15:pointer-enforcement',
                'implementation' => [
                    'app/Application/MarketData/Services/PublicationFinalizeOutcomeService.php',
                    'app/Infrastructure/Persistence/MarketData/EodPublicationRepository.php',
                ],
                'positive' => ['tests/Unit/MarketData/PublicationSealPointerLifecycleTest.php', 'test_exactly_one_publication_is_current_for_a_trade_date'],
                'negative' => ['tests/Unit/MarketData/CurrentPointerIntegrityScanTest.php', 'test_states_the_consumer_cannot_read_are_flagged_by_the_scan'],
                'runtime_required' => true,
            ],
            'publishability_enforcement' => [
                'owner' => 'MD-B15:publishability-enforcement',
                'implementation' => [
                    'app/Application/MarketData/Services/PublicationFinalizeOutcomeService.php',
                ],
                'positive' => ['tests/Unit/MarketData/PublicationFinalizeOutcomeServiceTest.php', 'test_readable_outcome_requires_coverage_pass'],
                'negative' => ['tests/Unit/MarketData/PublicationFinalizeOutcomeServiceTest.php', 'test_outcome_keeps_blocked_non_readable_and_never_promotes'],
                'runtime_required' => true,
            ],
            'reason_code_mapping' => [
                'owner' => 'MD-B15:reason-code-mapping',
                'implementation' => [
                    'app/Application/MarketData/Services/CoverageGateEvaluator.php',
                    'app/Application/MarketData/Services/FinalizeDecisionService.php',
                ],
                'positive' => ['tests/Unit/MarketData/CoveragePolicyLegacyBlockedNormalizationTest.php', 'test_legacy_blocked_coverage_gate_state_normalizes_to_not_evaluable'],
                'negative' => ['tests/Unit/MarketData/CoverageDormantUniverseTest.php', 'test_the_deprecated_dormancy_exclusion_reason_is_never_emitted'],
                'runtime_required' => true,
            ],
            'replay_enforcement' => [
                'owner' => 'MD-B15:replay-enforcement',
                'implementation' => [
                    'app/Application/MarketData/Services/ReplayVerificationService.php',
                ],
                'positive' => ['tests/Unit/MarketData/ReplayVerificationServiceTest.php', 'test_verify_replay_marks_mismatch_when_coverage_contract_fields_diverge'],
                'negative' => ['tests/Unit/MarketData/ReplayVerificationServiceTest.php', 'test_verify_replay_normalizes_legacy_blocked_coverage_state_and_preserves_raw_trace'],
                'runtime_required' => true,
            ],
            'retry_window' => [
                'owner' => 'MD-B15:retry-window',
                'implementation' => [
                    'app/Infrastructure/MarketData/Source/PublicApiEodBarsAdapter.php',
                    'config/market_data.php',
                ],
                'positive' => ['tests/Unit/MarketData/CoverageEdgeCaseBoundaryB15Test.php', 'test_every_retry_control_is_a_governed_configuration_key'],
                'negative' => ['tests/Unit/MarketData/CoverageEdgeCaseBoundaryB15Test.php', 'test_retry_exhaustion_never_reaches_a_readable_state'],
                'runtime_required' => true,
            ],
            'separate_dimensions' => [
                'owner' => 'MD-B15:separate-dimensions',
                'implementation' => [
                    'app/Application/MarketData/Services/CoverageGateEvaluator.php',
                    'app/Application/MarketData/Services/FinalizeDecisionService.php',
                ],
                'positive' => ['tests/Unit/MarketData/CoverageSilentImprovementBoundaryTest.php', 'test_every_excluded_instrument_is_counted_somewhere'],
                'negative' => ['tests/Unit/MarketData/CoverageTelemetryBypassTest.php', 'test_contradictory_coverage_verdicts_are_not_treated_as_pass'],
                'runtime_required' => true,
            ],
            'stale_data' => [
                'owner' => 'MD-B15:stale-data',
                'implementation' => [
                    'app/Application/MarketData/Services/EodBarsIngestService.php',
                ],
                'positive' => ['tests/Unit/MarketData/CoverageEdgeCaseBoundaryB15Test.php', 'test_a_row_outside_the_requested_trade_date_is_refused_as_stale'],
                'negative' => ['tests/Unit/MarketData/CandidateCoverageScopeTest.php', 'test_bars_from_another_trade_date_are_not_counted'],
                'runtime_required' => true,
            ],
            'threshold_and_ratio' => [
                'owner' => 'MD-B15:threshold-and-ratio',
                'implementation' => [
                    'app/Application/MarketData/Services/CoverageGateEvaluator.php',
                    'config/market_data.php',
                ],
                'positive' => ['tests/Unit/MarketData/CoveragePolicyDocsStaticGuardTest.php', 'test_market_data_coverage_min_threshold_stays_locked_to_098'],
                'negative' => ['tests/Unit/MarketData/CoverageGateEvaluatorTest.php', 'test_evaluator_returns_fail_when_available_is_below_threshold'],
                'runtime_required' => true,
            ],
            'universe_hash_identity' => [
                'owner' => 'MD-B15:universe-hash-identity',
                'implementation' => [
                    'app/Application/MarketData/Services/CoverageGateEvaluator.php',
                ],
                'positive' => ['tests/Unit/MarketData/CoverageSilentImprovementBoundaryTest.php', 'test_the_universe_hash_changes_only_when_the_universe_changes'],
                'negative' => ['tests/Unit/MarketData/CoverageSilentImprovementBoundaryTest.php', 'test_a_populated_universe_reconstructs_its_own_ratio'],
                'runtime_required' => true,
            ],
            'universe_temporal_membership' => [
                'owner' => 'MD-B15:universe-temporal-membership',
                'implementation' => [
                    'app/Application/MarketData/Services/CoverageGateEvaluator.php',
                    'app/Infrastructure/Persistence/MarketData/TickerMasterRepository.php',
                ],
                'positive' => ['tests/Unit/MarketData/CoverageDenominatorKnowledgeCutoffTest.php', 'test_denominator_at_a_fixed_cutoff_is_unmoved_by_a_later_recorded_listing'],
                'negative' => ['tests/Unit/MarketData/CoverageDenominatorKnowledgeCutoffTest.php', 'test_a_later_cutoff_admits_the_later_listing'],
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
            throw new RuntimeException('No MD-B15 proof family for '.$ruleId);
        }

        return $map[$ruleId];
    }

    /** @return array<string,string> */
    public static function ruleFamilies(): array
    {
        return [
            // anti_bypass (14)
            'MD-S014-R0005' => 'anti_bypass',
            'MD-S014-R0006' => 'anti_bypass',
            'MD-S014-R0085' => 'anti_bypass',
            'MD-S015-R0004' => 'anti_bypass',
            'MD-S015-R0101' => 'anti_bypass',
            'MD-S015-R0102' => 'anti_bypass',
            'MD-S015-R0103' => 'anti_bypass',
            'MD-S015-R0104' => 'anti_bypass',
            'MD-S015-R0105' => 'anti_bypass',
            'MD-S015-R0106' => 'anti_bypass',
            'MD-S015-R0107' => 'anti_bypass',
            'MD-S024-R0032' => 'anti_bypass',
            'MD-S024-R0033' => 'anti_bypass',
            'MD-S053-R0016' => 'anti_bypass',
            // citation_boundary (8)
            'MD-S014-R0094' => 'citation_boundary',
            'MD-S015-R0113' => 'citation_boundary',
            'MD-S016-R0042' => 'citation_boundary',
            'MD-S024-R0063' => 'citation_boundary',
            'MD-S024-R0064' => 'citation_boundary',
            'MD-S024-R0065' => 'citation_boundary',
            'MD-S029-R0166' => 'citation_boundary',
            'MD-S058-R0061' => 'citation_boundary',
            // command_enforcement (1)
            'MD-S015-R0099' => 'command_enforcement',
            // delayed_data (6)
            'MD-S014-R0023' => 'delayed_data',
            'MD-S014-R0025' => 'delayed_data',
            'MD-S014-R0026' => 'delayed_data',
            'MD-S014-R0027' => 'delayed_data',
            'MD-S014-R0028' => 'delayed_data',
            'MD-S014-R0029' => 'delayed_data',
            // delivery_numerator (13)
            'MD-S024-R0013' => 'delivery_numerator',
            'MD-S024-R0015' => 'delivery_numerator',
            'MD-S024-R0016' => 'delivery_numerator',
            'MD-S024-R0017' => 'delivery_numerator',
            'MD-S024-R0018' => 'delivery_numerator',
            'MD-S024-R0019' => 'delivery_numerator',
            'MD-S024-R0020' => 'delivery_numerator',
            'MD-S024-R0021' => 'delivery_numerator',
            'MD-S029-R0015' => 'delivery_numerator',
            'MD-S053-R0136' => 'delivery_numerator',
            'MD-S053-R0137' => 'delivery_numerator',
            'MD-S053-R0138' => 'delivery_numerator',
            'MD-S053-R0139' => 'delivery_numerator',
            // denominator_construction (9)
            'MD-S015-R0036' => 'denominator_construction',
            'MD-S015-R0037' => 'denominator_construction',
            'MD-S015-R0039' => 'denominator_construction',
            'MD-S015-R0040' => 'denominator_construction',
            'MD-S015-R0041' => 'denominator_construction',
            'MD-S015-R0043' => 'denominator_construction',
            'MD-S015-R0045' => 'denominator_construction',
            'MD-S024-R0011' => 'denominator_construction',
            'MD-S024-R0012' => 'denominator_construction',
            // evidence_component_identity (8)
            'MD-S024-R0045' => 'evidence_component_identity',
            'MD-S024-R0046' => 'evidence_component_identity',
            'MD-S024-R0047' => 'evidence_component_identity',
            'MD-S024-R0049' => 'evidence_component_identity',
            'MD-S024-R0050' => 'evidence_component_identity',
            'MD-S024-R0051' => 'evidence_component_identity',
            'MD-S024-R0052' => 'evidence_component_identity',
            'MD-S041-R0021' => 'evidence_component_identity',
            // evidence_fields (35)
            'MD-S015-R0005' => 'evidence_fields',
            'MD-S015-R0008' => 'evidence_fields',
            'MD-S015-R0011' => 'evidence_fields',
            'MD-S015-R0013' => 'evidence_fields',
            'MD-S015-R0015' => 'evidence_fields',
            'MD-S015-R0017' => 'evidence_fields',
            'MD-S015-R0021' => 'evidence_fields',
            'MD-S015-R0022' => 'evidence_fields',
            'MD-S015-R0023' => 'evidence_fields',
            'MD-S015-R0026' => 'evidence_fields',
            'MD-S015-R0029' => 'evidence_fields',
            'MD-S015-R0034' => 'evidence_fields',
            'MD-S015-R0066' => 'evidence_fields',
            'MD-S015-R0067' => 'evidence_fields',
            'MD-S015-R0068' => 'evidence_fields',
            'MD-S015-R0069' => 'evidence_fields',
            'MD-S015-R0070' => 'evidence_fields',
            'MD-S015-R0071' => 'evidence_fields',
            'MD-S015-R0072' => 'evidence_fields',
            'MD-S015-R0073' => 'evidence_fields',
            'MD-S016-R0025' => 'evidence_fields',
            'MD-S016-R0026' => 'evidence_fields',
            'MD-S016-R0027' => 'evidence_fields',
            'MD-S016-R0028' => 'evidence_fields',
            'MD-S016-R0029' => 'evidence_fields',
            'MD-S024-R0034' => 'evidence_fields',
            'MD-S024-R0036' => 'evidence_fields',
            'MD-S024-R0037' => 'evidence_fields',
            'MD-S024-R0038' => 'evidence_fields',
            'MD-S024-R0039' => 'evidence_fields',
            'MD-S024-R0040' => 'evidence_fields',
            'MD-S029-R0179' => 'evidence_fields',
            'MD-S029-R0180' => 'evidence_fields',
            'MD-S053-R0143' => 'evidence_fields',
            'MD-S053-R0149' => 'evidence_fields',
            // exclusion_path_proof (10)
            'MD-S016-R0010' => 'exclusion_path_proof',
            'MD-S016-R0011' => 'exclusion_path_proof',
            'MD-S016-R0012' => 'exclusion_path_proof',
            'MD-S016-R0013' => 'exclusion_path_proof',
            'MD-S016-R0030' => 'exclusion_path_proof',
            'MD-S024-R0053' => 'exclusion_path_proof',
            'MD-S024-R0054' => 'exclusion_path_proof',
            'MD-S024-R0055' => 'exclusion_path_proof',
            'MD-S024-R0056' => 'exclusion_path_proof',
            'MD-S024-R0057' => 'exclusion_path_proof',
            // expectation_states (7)
            'MD-S016-R0005' => 'expectation_states',
            'MD-S016-R0006' => 'expectation_states',
            'MD-S016-R0007' => 'expectation_states',
            'MD-S016-R0008' => 'expectation_states',
            'MD-S041-R0037' => 'expectation_states',
            'MD-S041-R0043' => 'expectation_states',
            'MD-S058-R0042' => 'expectation_states',
            // fallback_targets (8)
            'MD-S014-R0068' => 'fallback_targets',
            'MD-S014-R0070' => 'fallback_targets',
            'MD-S014-R0072' => 'fallback_targets',
            'MD-S014-R0073' => 'fallback_targets',
            'MD-S014-R0074' => 'fallback_targets',
            'MD-S014-R0075' => 'fallback_targets',
            'MD-S014-R0076' => 'fallback_targets',
            'MD-S014-R0077' => 'fallback_targets',
            // finalize_enforcement (5)
            'MD-S015-R0054' => 'finalize_enforcement',
            'MD-S015-R0055' => 'finalize_enforcement',
            'MD-S015-R0056' => 'finalize_enforcement',
            'MD-S015-R0057' => 'finalize_enforcement',
            'MD-S015-R0058' => 'finalize_enforcement',
            // forbidden_exclusions (13)
            'MD-S014-R0086' => 'forbidden_exclusions',
            'MD-S014-R0087' => 'forbidden_exclusions',
            'MD-S014-R0088' => 'forbidden_exclusions',
            'MD-S016-R0015' => 'forbidden_exclusions',
            'MD-S016-R0016' => 'forbidden_exclusions',
            'MD-S016-R0017' => 'forbidden_exclusions',
            'MD-S016-R0018' => 'forbidden_exclusions',
            'MD-S016-R0019' => 'forbidden_exclusions',
            'MD-S016-R0020' => 'forbidden_exclusions',
            'MD-S016-R0021' => 'forbidden_exclusions',
            'MD-S016-R0022' => 'forbidden_exclusions',
            'MD-S016-R0031' => 'forbidden_exclusions',
            'MD-S024-R0067' => 'forbidden_exclusions',
            // gate_states (4)
            'MD-S015-R0047' => 'gate_states',
            'MD-S015-R0048' => 'gate_states',
            'MD-S015-R0049' => 'gate_states',
            'MD-S015-R0050' => 'gate_states',
            // implementation_mapping (6)
            'MD-S014-R0079' => 'implementation_mapping',
            'MD-S014-R0080' => 'implementation_mapping',
            'MD-S014-R0081' => 'implementation_mapping',
            'MD-S014-R0082' => 'implementation_mapping',
            'MD-S014-R0083' => 'implementation_mapping',
            'MD-S014-R0084' => 'implementation_mapping',
            // knowledge_cutoff_determinism (1)
            'MD-S041-R0079' => 'knowledge_cutoff_determinism',
            // legacy_blocked_normalization (1)
            'MD-S024-R0031' => 'legacy_blocked_normalization',
            // multi_source_boundary (6)
            'MD-S014-R0007' => 'multi_source_boundary',
            'MD-S014-R0008' => 'multi_source_boundary',
            'MD-S014-R0010' => 'multi_source_boundary',
            'MD-S014-R0011' => 'multi_source_boundary',
            'MD-S014-R0012' => 'multi_source_boundary',
            'MD-S014-R0013' => 'multi_source_boundary',
            // partial_dataset (7)
            'MD-S014-R0017' => 'partial_dataset',
            'MD-S014-R0018' => 'partial_dataset',
            'MD-S014-R0019' => 'partial_dataset',
            'MD-S014-R0020' => 'partial_dataset',
            'MD-S014-R0021' => 'partial_dataset',
            'MD-S014-R0022' => 'partial_dataset',
            'MD-S029-R0121' => 'partial_dataset',
            // pointer_enforcement (2)
            'MD-S015-R0063' => 'pointer_enforcement',
            'MD-S015-R0064' => 'pointer_enforcement',
            // publishability_enforcement (6)
            'MD-S015-R0059' => 'publishability_enforcement',
            'MD-S015-R0060' => 'publishability_enforcement',
            'MD-S015-R0061' => 'publishability_enforcement',
            'MD-S015-R0062' => 'publishability_enforcement',
            'MD-S020-R0012' => 'publishability_enforcement',
            'MD-S024-R0041' => 'publishability_enforcement',
            // reason_code_mapping (6)
            'MD-S014-R0056' => 'reason_code_mapping',
            'MD-S015-R0051' => 'reason_code_mapping',
            'MD-S015-R0052' => 'reason_code_mapping',
            'MD-S015-R0053' => 'reason_code_mapping',
            'MD-S085-R0449' => 'reason_code_mapping',
            'MD-S085-R0450' => 'reason_code_mapping',
            // replay_enforcement (2)
            'MD-S015-R0074' => 'replay_enforcement',
            'MD-S015-R0091' => 'replay_enforcement',
            // retry_window (11)
            'MD-S014-R0030' => 'retry_window',
            'MD-S014-R0032' => 'retry_window',
            'MD-S014-R0033' => 'retry_window',
            'MD-S014-R0034' => 'retry_window',
            'MD-S014-R0035' => 'retry_window',
            'MD-S014-R0036' => 'retry_window',
            'MD-S014-R0038' => 'retry_window',
            'MD-S014-R0039' => 'retry_window',
            'MD-S014-R0040' => 'retry_window',
            'MD-S029-R0105' => 'retry_window',
            'MD-S029-R0113' => 'retry_window',
            // separate_dimensions (13)
            'MD-S023-R0052' => 'separate_dimensions',
            'MD-S023-R0053' => 'separate_dimensions',
            'MD-S024-R0002' => 'separate_dimensions',
            'MD-S024-R0003' => 'separate_dimensions',
            'MD-S024-R0004' => 'separate_dimensions',
            'MD-S024-R0005' => 'separate_dimensions',
            'MD-S024-R0006' => 'separate_dimensions',
            'MD-S024-R0007' => 'separate_dimensions',
            'MD-S024-R0008' => 'separate_dimensions',
            'MD-S024-R0009' => 'separate_dimensions',
            'MD-S029-R0131' => 'separate_dimensions',
            'MD-S029-R0189' => 'separate_dimensions',
            'MD-S058-R0044' => 'separate_dimensions',
            // stale_data (6)
            'MD-S014-R0041' => 'stale_data',
            'MD-S014-R0042' => 'stale_data',
            'MD-S014-R0043' => 'stale_data',
            'MD-S014-R0044' => 'stale_data',
            'MD-S014-R0045' => 'stale_data',
            'MD-S053-R0127' => 'stale_data',
            // threshold_and_ratio (8)
            'MD-S001-R0059' => 'threshold_and_ratio',
            'MD-S001-R0060' => 'threshold_and_ratio',
            'MD-S024-R0024' => 'threshold_and_ratio',
            'MD-S024-R0025' => 'threshold_and_ratio',
            'MD-S024-R0026' => 'threshold_and_ratio',
            'MD-S024-R0027' => 'threshold_and_ratio',
            'MD-S024-R0028' => 'threshold_and_ratio',
            'MD-S024-R0029' => 'threshold_and_ratio',
            // universe_hash_identity (2)
            'MD-S016-R0024' => 'universe_hash_identity',
            'MD-S024-R0035' => 'universe_hash_identity',
            // universe_temporal_membership (3)
            'MD-S016-R0002' => 'universe_temporal_membership',
            'MD-S016-R0003' => 'universe_temporal_membership',
            'MD-S057-R0047' => 'universe_temporal_membership',
        ];
    }

    /** @return array<int,array{rule_id:string,strategy_document_id:string,family:string}> */
    public static function entries(string $root): array
    {
        $out = [];
        foreach (MarketDataCoverageGateTraceabilitySpec::mandatory($root) as $row) {
            $out[] = [
                'rule_id' => $row['rule_id'],
                'strategy_document_id' => $row['strategy_document_id'],
                'family' => self::familyFor($row),
            ];
        }

        return $out;
    }
}
