<?php

require_once __DIR__.'/MarketDataTemporalIdentityTraceabilityGate.php';

/** Exact MD-B05-A001 proof binding and implementation-surface gate. */
final class MarketDataTemporalIdentityProofGate
{
    public const EVIDENCE = 'E-MD-B05-A001-001';

    /**
     * Rules whose proof turns on a defect this attempt remediated. Each is pinned to the method that
     * failed before the fix, so a regression cannot pass as a coverage rounding difference.
     */
    public const REMEDIATED_RULES = [
        'MD-S057-R0031' => 'board and market segment were not effective-dated',
        'MD-S057-R0039' => 'point-in-time resolution returned the current board',
        'MD-S055-R0017' => 'a Regular-Market observation could not retain its board context',
        'MD-S052-R0012' => 'the conditional operator-entered class was unconditional on the read side',
    ];

    /** @return array<string,array{surfaces:array<int,string>,methods:array<int,array{0:string,1:string}>}> */
    public static function proofMap(): array
    {
        $identity = [
            'surfaces' => [
                'app/Infrastructure/Persistence/MarketData/TemporalIdentityRepository.php',
                'database/migrations/2026_08_02_000001_add_market_data_strategy_v2_foundation.php',
                'database/migrations/2026_08_03_000001_harden_market_data_orders_1_to_4.php',
            ],
            'methods' => [
                ['tests/Unit/MarketData/TemporalIdentityLayerContractTest.php', 'test_each_identity_layer_is_a_distinct_record_with_its_own_stable_identity'],
                ['tests/Unit/MarketData/TemporalIdentityLayerContractTest.php', 'test_the_listing_layer_carries_venue_segment_and_board_over_an_effective_interval'],
                ['tests/Unit/MarketData/TemporalIdentityLayerContractTest.php', 'test_every_temporal_record_carries_interval_provenance_and_known_time'],
                ['tests/Unit/MarketData/TemporalIdentityLayerContractTest.php', 'test_the_identity_interval_end_is_exclusive_and_the_same_in_every_record_type'],
                ['tests/Unit/MarketData/TemporalIdentityLayerContractTest.php', 'test_recorded_time_and_effective_time_are_separate_coordinates'],
                ['tests/Unit/MarketData/TemporalIdentityLayerContractTest.php', 'test_point_in_time_resolution_returns_the_full_identity_for_the_trade_date'],
                ['tests/Unit/MarketData/TemporalIdentityLayerContractTest.php', 'test_the_projection_reads_the_shared_master_and_does_not_write_to_it'],
            ],
        ];

        $universe = [
            'surfaces' => [
                'app/Infrastructure/Persistence/MarketData/TemporalIdentityRepository.php',
                'app/Application/MarketData/Services/MarketDataPipelineService.php',
            ],
            'methods' => [
                ['tests/Unit/MarketData/TemporalIdentityFixturesTest.php', 'test_a_delisted_listing_is_present_before_and_absent_after_its_delisting'],
                ['tests/Unit/MarketData/TemporalIdentityFixturesTest.php', 'test_a_listing_is_absent_before_its_listed_date'],
                ['tests/Unit/MarketData/TemporalIdentityFixturesTest.php', 'test_a_retracted_symbol_does_not_resolve'],
                ['tests/Unit/MarketData/LegacyTickerAliasBoundaryTest.php', 'test_the_projection_reads_the_legacy_master_without_consulting_the_current_active_flag'],
                ['tests/Unit/MarketData/LegacyTickerAliasBoundaryTest.php', 'test_the_fail_closed_read_surface_does_not_create_identity_while_reading'],
                ['tests/Unit/MarketData/MarketDataOrdersOneToFourFoundationTest.php', 'test_temporal_identity_ignores_current_active_flag_and_resolves_explicit_provider_mapping'],
                ['tests/Unit/MarketData/ListingBoardAndSegmentTemporalityTest.php', 'test_a_segment_move_does_not_remove_the_listing_from_an_earlier_universe'],
                ['tests/Unit/MarketData/AcquisitionKnowledgeCutoffTest.php', 'test_acquisition_does_not_ask_for_a_listing_recorded_after_the_cutoff'],
            ],
        ];

        $board = [
            'surfaces' => [
                'app/Infrastructure/Persistence/MarketData/TemporalIdentityRepository.php',
                'database/migrations/2026_08_22_000001_add_temporal_listing_board_intervals.php',
            ],
            'methods' => [
                ['tests/Unit/MarketData/ListingBoardAndSegmentTemporalityTest.php', 'test_the_temporal_board_record_exists_with_an_interval_and_provenance'],
                ['tests/Unit/MarketData/ListingBoardAndSegmentTemporalityTest.php', 'test_a_board_move_resolves_the_board_effective_on_the_trade_date'],
                ['tests/Unit/MarketData/ListingBoardAndSegmentTemporalityTest.php', 'test_the_prior_board_interval_is_closed_and_never_rewritten'],
                ['tests/Unit/MarketData/ListingBoardAndSegmentTemporalityTest.php', 'test_a_listing_without_a_board_interval_for_the_date_is_not_resolved_from_the_current_column'],
                ['tests/Unit/MarketData/ListingBoardAndSegmentTemporalityTest.php', 'test_overlapping_board_intervals_fail_closed'],
                ['tests/Unit/MarketData/ListingBoardAndSegmentTemporalityTest.php', 'test_a_retracted_board_interval_does_not_resolve'],
                ['tests/Unit/MarketData/ListingBoardAndSegmentTemporalityTest.php', 'test_the_legacy_projection_opens_a_board_interval_from_the_listed_date'],
            ],
        ];

        $mapping = [
            'surfaces' => [
                'app/Infrastructure/Persistence/MarketData/TemporalIdentityRepository.php',
                'app/Infrastructure/MarketData/Source/EquityProviderSymbolResolver.php',
                'app/Infrastructure/MarketData/Source/PublicApiEodBarsAdapter.php',
            ],
            'methods' => [
                ['tests/Unit/MarketData/SymbolMappingLifecycleAndFailureTest.php', 'test_a_mapping_record_carries_identity_namespace_interval_provenance_and_reason'],
                ['tests/Unit/MarketData/SymbolMappingLifecycleAndFailureTest.php', 'test_suffix_rendering_produces_no_identity_and_the_mapping_record_does'],
                ['tests/Unit/MarketData/SymbolMappingLifecycleAndFailureTest.php', 'test_two_listings_holding_one_symbol_at_the_same_instant_fail_closed'],
                ['tests/Unit/MarketData/SymbolMappingLifecycleAndFailureTest.php', 'test_one_listing_with_two_active_provider_symbols_fails_closed'],
                ['tests/Unit/MarketData/SymbolMappingLifecycleAndFailureTest.php', 'test_a_date_outside_mapping_validity_is_rejected_rather_than_served_by_the_current_mapping'],
                ['tests/Unit/MarketData/SymbolMappingLifecycleAndFailureTest.php', 'test_an_unknown_symbol_fabricates_no_identity'],
                ['tests/Unit/MarketData/SymbolMappingLifecycleAndFailureTest.php', 'test_batch_resolution_omits_the_unmappable_symbol_instead_of_substituting_one'],
                ['tests/Unit/MarketData/SymbolMappingLifecycleAndFailureTest.php', 'test_the_acquisition_boundary_surfaces_a_mapping_failure_as_an_explicit_ticker_scoped_reason'],
                ['tests/Unit/MarketData/SymbolMappingLifecycleAndFailureTest.php', 'test_delisting_closes_symbol_validity_without_deleting_the_record'],
                ['tests/Unit/MarketData/SymbolMappingLifecycleAndFailureTest.php', 'test_relisting_states_whether_the_instrument_continues_or_changes'],
                ['tests/Unit/MarketData/SymbolMappingLifecycleAndFailureTest.php', 'test_a_provider_correction_appends_a_revision_and_keeps_the_prior_one'],
                ['tests/Unit/MarketData/TemporalIdentityFixturesTest.php', 'test_a_rename_resolves_to_the_symbol_effective_on_the_trade_date'],
                ['tests/Unit/MarketData/TemporalIdentityFixturesTest.php', 'test_symbol_reuse_resolves_to_the_listing_that_held_it_then'],
                ['tests/Unit/MarketData/TemporalIdentityFixturesTest.php', 'test_provider_mapping_revision_does_not_rewrite_earlier_resolution'],
            ],
        ];

        $alias = [
            'surfaces' => [
                'app/Infrastructure/Persistence/MarketData/TemporalIdentityRepository.php',
                'app/Infrastructure/Persistence/MarketData/TickerMasterRepository.php',
                'config/market_data.php',
            ],
            'methods' => [
                ['tests/Unit/MarketData/LegacyTickerAliasBoundaryTest.php', 'test_the_alias_is_carried_as_a_uniquely_bound_column_on_the_stable_listing'],
                ['tests/Unit/MarketData/LegacyTickerAliasBoundaryTest.php', 'test_an_alias_that_does_not_match_its_stable_identity_fails_closed'],
                ['tests/Unit/MarketData/LegacyTickerAliasBoundaryTest.php', 'test_an_alias_with_no_stable_identity_resolves_to_unknown_rather_than_creating_one'],
                ['tests/Unit/MarketData/LegacyTickerAliasBoundaryTest.php', 'test_no_table_created_since_the_v2_foundation_keys_on_the_legacy_alias'],
                ['tests/Unit/MarketData/LegacyTickerAliasBoundaryTest.php', 'test_the_alias_is_never_silently_dropped_by_a_migration'],
                ['tests/Unit/MarketData/LegacyTickerAliasBoundaryTest.php', 'test_retirement_stays_blocked_while_the_read_product_still_emits_the_alias'],
                ['tests/Unit/MarketData/LegacyTickerAliasBoundaryTest.php', 'test_no_column_added_since_the_v2_foundation_introduces_the_legacy_alias'],
                ['tests/Unit/MarketData/LegacyTickerAliasBoundaryTest.php', 'test_provider_symbol_resolution_does_not_read_the_legacy_projection_config'],
            ],
        ];

        $sector = [
            'surfaces' => [
                'app/Infrastructure/Persistence/MarketData/SectorClassificationRepository.php',
                'database/migrations/2026_08_08_000001_harden_sector_membership_and_analytical_product_identity.php',
                'database/migrations/2026_08_10_000001_require_sector_membership_authority_columns.php',
            ],
            'methods' => [
                ['tests/Unit/MarketData/SectorMembershipTemporalFactTest.php', 'test_a_membership_record_binds_stable_identity_system_interval_source_and_known_time'],
                ['tests/Unit/MarketData/SectorMembershipTemporalFactTest.php', 'test_no_other_classification_system_may_be_stored'],
                ['tests/Unit/MarketData/SectorMembershipTemporalFactTest.php', 'test_a_code_outside_the_governed_taxonomy_cannot_enter_under_the_idx_ic_name'],
                ['tests/Unit/MarketData/SectorMembershipTemporalFactTest.php', 'test_the_membership_interval_end_is_inclusive_and_the_reclassification_boundary_holds'],
                ['tests/Unit/MarketData/SectorMembershipTemporalFactTest.php', 'test_an_uncovered_date_resolves_unknown_rather_than_the_current_sector'],
                ['tests/Unit/MarketData/SectorMembershipTemporalFactTest.php', 'test_a_date_before_the_dataset_start_resolves_no_membership'],
                ['tests/Unit/MarketData/SectorMembershipTemporalFactTest.php', 'test_membership_is_temporal_at_this_stage_rather_than_first_at_the_consuming_stage'],
                ['tests/Unit/MarketData/SectorMembershipTemporalFactTest.php', 'test_no_market_data_surface_ranks_or_weights_sectors'],
                ['tests/Unit/MarketData/SectorSourceAuthorityClassResolutionTest.php', 'test_an_operator_row_without_its_governance_triple_does_not_establish_membership'],
                ['tests/Unit/MarketData/SectorSourceAuthorityClassResolutionTest.php', 'test_an_operator_row_carrying_the_full_triple_does_establish_membership'],
                ['tests/Unit/MarketData/SectorSourceAuthorityClassResolutionTest.php', 'test_an_exchange_authoritative_row_needs_no_operator_or_reason'],
                ['tests/Unit/MarketData/SectorSourceAuthorityClassResolutionTest.php', 'test_a_derived_reference_row_is_refused_under_its_own_reason'],
                ['tests/Unit/MarketData/SectorSourceAuthorityClassResolutionTest.php', 'test_a_governed_row_still_resolves_when_an_ungoverned_row_covers_the_same_date'],
                ['tests/Unit/MarketData/SectorClassificationRepositoryTest.php', 'test_resolves_authoritative_effective_and_as_known_sector_membership'],
                ['tests/Unit/MarketData/SectorClassificationRepositoryTest.php', 'test_reclassification_appends_closure_and_new_fact_without_editing_prior_row'],
                ['tests/Unit/MarketData/SectorClassificationRepositoryTest.php', 'test_overlapping_authoritative_intervals_fail_closed'],
                ['tests/Unit/MarketData/SectorClassificationRepositoryTest.php', 'test_derived_reference_cannot_be_appended_as_membership_authority'],
                ['tests/Unit/MarketData/SectorClassificationRepositoryTest.php', 'test_recorded_at_is_required_for_as_known_membership'],
            ],
        ];

        $capability = [
            'surfaces' => [
                'docs/market_data/authority/strategy/book/Tickers_and_Identity_Dependency_Contract_LOCKED.md',
                'docs/market_data/authority/strategy/book/Symbol_Lifecycle_and_Mapping_Contract.md',
                'docs/market_data/authority/strategy/book/Sector_Classification_Contract_LOCKED.md',
            ],
            'methods' => [
                ['tests/Unit/MarketData/IdentityAndMembershipCapabilityBoundaryTest.php', 'test_each_owner_contract_still_states_the_limit_it_owns'],
                ['tests/Unit/MarketData/IdentityAndMembershipCapabilityBoundaryTest.php', 'test_no_active_document_or_application_surface_makes_a_forbidden_claim'],
                ['tests/Unit/MarketData/IdentityAndMembershipCapabilityBoundaryTest.php', 'test_a_survivorship_free_claim_names_the_resolver_or_its_reconciled_period'],
                ['tests/Unit/MarketData/IdentityAndMembershipCapabilityBoundaryTest.php', 'test_every_pattern_matches_the_claim_it_forbids'],
                ['tests/Unit/MarketData/IdentityAndMembershipCapabilityBoundaryTest.php', 'test_denying_a_forbidden_claim_is_not_making_it'],
            ],
        ];

        $families = compact('identity', 'universe', 'board', 'mapping', 'alias', 'sector', 'capability');
        $assignment = self::familyAssignment();
        $map = [];
        foreach ($assignment as $rule => $family) {
            if (! isset($families[$family])) {
                throw new RuntimeException('Unknown proof family '.$family.' for '.$rule);
            }
            $map[$rule] = $families[$family];
        }

        // The acceptance criteria are the contracts' own summary of the whole mechanism, so each one
        // binds the capability boundary as well as its behavioral family: it is the sentence that
        // states which of two guarantees the criterion establishes.
        foreach (['MD-S057-R0068', 'MD-S055-R0036', 'MD-S052-R0046'] as $rule) {
            $map[$rule]['methods'] = array_merge($map[$rule]['methods'], $families['capability']['methods']);
        }

        ksort($map, SORT_STRING);

        return $map;
    }

    /** @return array<string,string> rule id => proof family */
    public static function familyAssignment(): array
    {
        $assignment = [];
        $bind = function (string $document, array $numbers, string $family) use (&$assignment): void {
            foreach ($numbers as $number) {
                $rule = MarketDataTemporalIdentityTraceabilitySpec::ruleId($document, $number);
                if (isset($assignment[$rule])) {
                    throw new RuntimeException('Duplicate proof family for '.$rule);
                }
                $assignment[$rule] = $family;
            }
        };

        $bind('MD-S057', [1, 2, 4, 5, 6, 7, 8, 11, 12, 13, 14, 15, 16, 17, 35, 36, 37, 38, 40, 42], 'identity');
        $bind('MD-S057', [19, 20, 21, 22, 23, 24, 25, 26, 27, 44, 60, 68], 'universe');
        $bind('MD-S057', [31, 39], 'board');
        $bind('MD-S057', [30, 32, 41, 45, 46], 'mapping');
        $bind('MD-S057', [9, 62, 63, 64, 65, 66, 67], 'alias');
        $bind('MD-S057', [49, 50, 52, 53, 54, 55, 61, 69], 'capability');

        $bind('MD-S055', [1, 2, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15, 16, 18, 20, 21, 22, 23, 26, 27, 28, 29, 36], 'mapping');
        $bind('MD-S055', [17], 'board');
        $bind('MD-S055', [30, 32, 33, 34, 35, 37], 'capability');

        $bind('MD-S052', [1, 3, 5, 6, 7, 10, 11, 12, 13, 14, 16, 17, 18, 19, 20, 21, 22, 23, 24, 43, 45, 46], 'sector');
        $bind('MD-S052', [32, 34, 35, 36, 37, 38, 44], 'capability');

        $assignment['MD-S020-R0011'] = 'universe';
        $assignment['MD-S082-R0163'] = 'alias';

        return $assignment;
    }

    /** @param array<int,array<string,string>> $rows */
    public static function validate(array $rows, string $root, array $map = null): array
    {
        $map = $map === null ? self::proofMap() : $map;
        $errors = [];
        $required = [];
        $counts = ['denominator' => 0, 'satisfied' => 0, 'unbound' => 0];

        foreach ($rows as $row) {
            if ($row['primary_stage'] !== MarketDataTemporalIdentityTraceabilitySpec::STAGE
                || $row['coverage_requirement'] !== 'REQUIRED') {
                continue;
            }
            $required[$row['rule_id']] = $row;
        }
        ksort($required, SORT_STRING);
        if (array_keys($required) !== array_keys($map)) {
            $errors[] = 'PROOF_MAP: map must exactly cover the current MD-B05 denominator';
        }

        foreach ($map as $rule => $proof) {
            if (! isset($required[$rule])) {
                $errors[] = $rule.': proof map names a non-denominator rule';

                continue;
            }
            $row = $required[$rule];
            $counts['denominator']++;
            if ($row['coverage_status'] !== 'SATISFIED' || $row['current_evidence_ids'] !== self::EVIDENCE) {
                $errors[] = $rule.': current A001 proof binding is not exact';
                $counts['unbound']++;
            } else {
                $counts['satisfied']++;
            }
            if (isset(self::REMEDIATED_RULES[$rule]) && strpos($row['notes'], 'remediated_at=MD-B05-A001') === false) {
                $errors[] = $rule.': remediated rule does not record the attempt that fixed it';
            }

            foreach ($proof['surfaces'] as $surface) {
                if (! file_exists($root.'/'.$surface)) {
                    $errors[] = $rule.': proof surface is missing '.$surface;
                }
            }
            foreach ($proof['methods'] as $method) {
                $source = @file_get_contents($root.'/'.$method[0]);
                if ($source === false || strpos($source, 'function '.$method[1].'(') === false) {
                    $errors[] = $rule.': proof method does not exist '.$method[0].'::'.$method[1];
                }
            }
        }

        $evidencePath = $root.'/docs/market_data/records/evidence/'.self::EVIDENCE
            .'_TEMPORAL_IDENTITY_AND_SECTOR_MEMBERSHIP.json';
        $evidence = file_exists($evidencePath) ? json_decode(file_get_contents($evidencePath), true) : null;
        if (! is_array($evidence)
            || ($evidence['evidence_id'] ?? null) !== self::EVIDENCE
            || ($evidence['baseline_id'] ?? null) !== 'MD-B05-A001-BL001'
            || ($evidence['change_impact_declaration'] ?? null) !== 'CI-MD-B05-A001-001') {
            $errors[] = 'EVIDENCE: issued proof record is missing, malformed, or miscorrelated';
        }

        $expected = MarketDataTemporalIdentityTraceabilitySpec::EXPECTED_B05_DENOMINATOR;
        if ($counts !== ['denominator' => $expected, 'satisfied' => $expected, 'unbound' => 0]) {
            $errors[] = 'COUNTS: expected exact A001 closure proof at '.$expected.'/'.$expected;
        }

        return ['errors' => $errors, 'counts' => $counts, 'status' => $errors === [] ? 'PASS' : 'FAIL'];
    }
}

if (realpath($_SERVER['SCRIPT_FILENAME'] ?? '') === __FILE__) {
    $root = dirname(__DIR__, 5);
    $matrix = $root.'/docs/market_data/authority/governance/STRATEGY_TO_IMPLEMENTATION_TRACEABILITY_MATRIX.csv';
    $rows = MarketDataClassificationConsistencyGate::readMatrix($matrix)['rows'];
    $result = MarketDataTemporalIdentityProofGate::validate($rows, $root);
    $result['gate'] = 'MarketDataTemporalIdentityProofGate';
    echo json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES).PHP_EOL;
    exit($result['status'] === 'PASS' ? 0 : 1);
}
