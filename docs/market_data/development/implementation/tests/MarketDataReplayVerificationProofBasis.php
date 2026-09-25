<?php

require_once __DIR__.'/MarketDataReplayVerificationPredicateMap.php';
require_once __DIR__.'/MarketDataReplayVerificationTraceabilitySpec.php';

/**
 * `MD-B18-A002` accumulating proof basis — one entry per predicate this attempt has established.
 *
 * This is the artifact `F-MD-B19-A001-002` requires and `MD-B18-A001` did not have. A predicate is
 * listed here only once a guard has been written or verified **for that predicate**, executed, and
 * shown able to fail under an injected defect. Membership of a proof family is not a reason to
 * appear here; `MarketDataReplayVerificationPredicateMap` records the family-level assessment that
 * turned out not to be proof, and is deliberately left unedited as the record of what was wrong.
 *
 * The file is expected to be incomplete while the attempt is open. `MarketDataReplayVerificationProofGate`
 * reports the shortfall by name rather than passing on the rows that are present.
 *
 * `basis` states how the guard establishes the predicate, in one line. It is the sentence a reviewer
 * needs in order to disagree.
 */
final class MarketDataReplayVerificationProofBasis
{
    public const ATTEMPT = 'MD-B18-A002';

    /** @var array<string,array<string,string>> */
    public const PROVEN = [
        // ---- MD-S040 "Evidence export and replay verification must preserve" -- the ten list
        // members. One guard establishes all ten because it enumerates them from the contract
        // itself and asserts each separately; a member added to MD-S040 with no assertion fails
        // the mapping test rather than passing unnoticed. That is the difference between a guard
        // that covers many predicates and a guard that is merely filed against many predicates.
        'MD-S040-R0070' => [
            'positive' => 'B18ReplayEvidencePreservationContractTest::test_every_preserved_item_survives_the_real_export',
            'negative' => 'B18ReplayEvidencePreservationContractTest::test_a_failing_coverage_gate_still_exports_the_threshold_it_failed_against',
            'basis' => 'coverage_gate_state is asserted in both the actual and expected coverage blocks of the exported replay_result.json',
        ],
        'MD-S040-R0072' => [
            'positive' => 'B18ReplayEvidencePreservationContractTest::test_every_preserved_item_survives_the_real_export',
            'negative' => 'B18ReplayEvidencePreservationContractTest::test_a_failing_coverage_gate_still_exports_the_threshold_it_failed_against',
            'basis' => 'coverage_ratio is asserted in the actual and expected coverage blocks, and the negative guard asserts a FAIL cannot be exported without it',
        ],
        'MD-S040-R0073' => [
            'positive' => 'B18ReplayEvidencePreservationContractTest::test_every_preserved_item_survives_the_real_export',
            'negative' => 'B18ReplayEvidencePreservationContractTest::test_a_failing_coverage_gate_still_exports_the_threshold_it_failed_against',
            'basis' => 'coverage_min_threshold is asserted, and the negative guard asserts a FAIL cannot be exported without the threshold it failed against',
        ],
        'MD-S040-R0074' => [
            'positive' => 'B18ReplayEvidencePreservationContractTest::test_every_preserved_item_survives_the_real_export',
            'negative' => 'B18ReplayEvidencePreservationContractTest::test_a_failing_coverage_gate_still_exports_the_threshold_it_failed_against',
            'basis' => 'expected, available and missing bar counts are each asserted individually in the exported coverage block',
        ],
        'MD-S040-R0075' => [
            'positive' => 'B18ReplayEvidencePreservationContractTest::test_every_preserved_item_survives_the_real_export',
            'negative' => 'B18ReplayEvidencePreservationContractTest::test_a_failing_coverage_gate_still_exports_the_threshold_it_failed_against',
            'basis' => 'terminal_status is asserted in the exported replay result; removing it from the builder turns the guard red',
        ],
        'MD-S040-R0076' => [
            'positive' => 'B18ReplayEvidencePreservationContractTest::test_every_preserved_item_survives_the_real_export',
            'negative' => 'B18ReplayEvidencePreservationContractTest::test_a_failing_coverage_gate_still_exports_the_threshold_it_failed_against',
            'basis' => 'publishability_state is asserted in the exported replay result',
        ],
        'MD-S040-R0078' => [
            'positive' => 'B18ReplayEvidencePreservationContractTest::test_every_preserved_item_survives_the_real_export',
            'negative' => 'B18ReplayEvidencePreservationContractTest::test_a_failing_coverage_gate_still_exports_the_threshold_it_failed_against',
            'basis' => 'trade_date_effective is asserted in the exported replay result; nulling it in the builder turns the guard red',
        ],
        'MD-S040-R0079' => [
            'positive' => 'B18ReplayEvidencePreservationContractTest::test_every_preserved_item_survives_the_real_export',
            'negative' => 'B18ReplayEvidencePreservationContractTest::test_a_failing_coverage_gate_still_exports_the_threshold_it_failed_against',
            'basis' => 'publication id and version are asserted in the publication audit context, which is where the export actually carries the id -- a top-level path failed this guard on its first run and the export was correct',
        ],

        // ---- MD-S050 "Every fixture/manifest binds at minimum" and the MD-S019 antecedent that
        // restates seven of the same identities. One guard enumerates the nine contract items,
        // asserts all 24 identity paths, and asserts a second fixture binding different
        // identities produces a different block - so a constant-filled block fails it. The
        // reproducibility consequent (MD-S019-R0073, MD-S003-R0004) needs a double-run byte
        // comparison and is deliberately NOT claimed here.
        'MD-S050-R0007' => [
            'positive' => 'B18ReplayBoundInputIdentityContractTest::test_every_named_identity_is_bound_into_the_exported_replay_result',
            'negative' => 'B18ReplayBoundInputIdentityContractTest::test_a_fixture_binding_different_identities_produces_a_different_block',
            'basis' => 'replay mode, fixture id and version, requested and effective date and the knowledge cutoff are each asserted present in the exported replay result',
        ],
        'MD-S050-R0010' => [
            'positive' => 'B18ReplayBoundInputIdentityContractTest::test_every_named_identity_is_bound_into_the_exported_replay_result',
            'negative' => 'B18ReplayBoundInputIdentityContractTest::test_a_fixture_binding_different_identities_produces_a_different_block',
            'basis' => 'the immutable source-observation manifest hash is asserted bound and record-derived',
        ],
        'MD-S050-R0011' => [
            'positive' => 'B18ReplayBoundInputIdentityContractTest::test_every_named_identity_is_bound_into_the_exported_replay_result',
            'negative' => 'B18ReplayBoundInputIdentityContractTest::test_a_fixture_binding_different_identities_produces_a_different_block',
            'basis' => 'the canonical RAW input hash is asserted bound and record-derived',
        ],
        'MD-S050-R0013' => [
            'positive' => 'B18ReplayBoundInputIdentityContractTest::test_every_named_identity_is_bound_into_the_exported_replay_result',
            'negative' => 'B18ReplayBoundInputIdentityContractTest::test_a_fixture_binding_different_identities_produces_a_different_block',
            'basis' => 'config snapshot id and hash are both asserted bound and record-derived',
        ],
        'MD-S050-R0015' => [
            'positive' => 'B18ReplayBoundInputIdentityContractTest::test_every_named_identity_is_bound_into_the_exported_replay_result',
            'negative' => 'B18ReplayBoundInputIdentityContractTest::test_a_fixture_binding_different_identities_produces_a_different_block',
            'basis' => 'expected seal state, expected publication version, expected pointer publication id and version, and the expected bars and indicators batch hashes are each asserted bound',
        ],
        'MD-S019-R0066' => [
            'positive' => 'B18ReplayBoundInputIdentityContractTest::test_every_named_identity_is_bound_into_the_exported_replay_result',
            'negative' => 'B18ReplayBoundInputIdentityContractTest::test_a_fixture_binding_different_identities_produces_a_different_block',
            'basis' => 'the immutable source-observation manifest is asserted bound into the replay result and read from the record; the reproducibility consequent this antecedent serves is MD-S019-R0073 and remains outstanding',
        ],
        'MD-S019-R0070' => [
            'positive' => 'B18ReplayBoundInputIdentityContractTest::test_every_named_identity_is_bound_into_the_exported_replay_result',
            'negative' => 'B18ReplayBoundInputIdentityContractTest::test_a_fixture_binding_different_identities_produces_a_different_block',
            'basis' => 'the full configuration snapshot id and hash are asserted bound and record-derived; the consequent MD-S019-R0073 remains outstanding',
        ],
        'MD-S019-R0072' => [
            'positive' => 'B18ReplayBoundInputIdentityContractTest::test_every_named_identity_is_bound_into_the_exported_replay_result',
            'negative' => 'B18ReplayBoundInputIdentityContractTest::test_a_fixture_binding_different_identities_produces_a_different_block',
            'basis' => 'serialization rules are asserted bound through the serialization version and are record-derived; the consequent MD-S019-R0073 remains outstanding',
        ],

        // ---- F-MD-B18-A002-013/F-MD-B18-A002-021 remediation, via E-MD-B18-A002-044/045/046. Moved
        // here from INCOMPLETE: E044 fixed the writer content (temporal_identity_hash re-sourced from
        // the correct C02 universe_identity component instead of C11 market-structure/board content;
        // event_factor_hash gained a fifth member over the C09 ancillary component, which already
        // carries corporate-action-window/price-scale-break contamination content per
        // ProducerAncillaryCapture::deriveContamination/derivePriceScaleBreaks), and this remediation
        // rebinds the guard from B18ReplayBoundInputIdentityContractTest (which only ever proved
        // MarketDataEvidenceExportService pass-through of a fabricated metric row, never the real
        // writer -- unchanged by E044, which is why these stayed INCOMPLETE at E045) to
        // B18ReplayComparisonExhaustivenessTest's real-path perturbation pair, plus a dedicated
        // domain-isolation probe proving the exact defect class F-021 found (a field silently
        // reflecting the wrong capture domain) cannot recur undetected: changing only the
        // universe_identity component changes temporal_identity_hash and nothing else; changing only
        // the ancillary component changes event_factor_hash and nothing else.
        'MD-S050-R0008' => [
            'positive' => 'B18ReplayComparisonExhaustivenessTest::test_a_divergence_in_any_frozen_input_denies_pass',
            'negative' => 'ReplayVerificationServiceTest::test_temporal_identity_and_event_factor_hash_are_domain_isolated_by_component',
            'basis' => 'temporal_identity_hash is a componentGroupHash() over the bound context\'s universe_identity (C02) capture references only -- each slot_hash covers the full selection_context the C1 capture contract requires to include dataset_start, and each payload_hash covers the resolved issuer/instrument/listing/symbol/provider-mapping population, so this one field binds both dataset boundary and temporal universe/listing/symbol/provider mappings. The comparison guard proves it is load-bearing against the real writer; the domain-isolation probe proves changing only this component moves this field and no other, ruling out the market-structure/board mismatch F-021 found.',
        ],
        'MD-S050-R0009' => [
            'positive' => 'B18ReplayComparisonExhaustivenessTest::test_a_divergence_in_any_frozen_input_denies_pass',
            'negative' => 'B18ReplayComparisonExhaustivenessTest::test_a_fixture_declaring_every_frozen_input_correctly_still_passes',
            'basis' => 'calendar_status_hash canonically combines calendar_revision_set_hash (derived from the run\'s own calendar_session captures, filtered to the trade date) and status_revision_set_hash (derived from eligibility-expectation captures\' trading-status resolution) -- both already correctly domain-matched before this remediation. The comparison guard, exercising the real ReplayVerificationService::actualBoundInputContext() computation, proves this field is genuinely resolved from that content and that a divergence in it denies PASS.',
        ],
        'MD-S050-R0012' => [
            'positive' => 'B18ReplayComparisonExhaustivenessTest::test_a_divergence_in_any_frozen_input_denies_pass',
            'negative' => 'ReplayVerificationServiceTest::test_temporal_identity_and_event_factor_hash_are_domain_isolated_by_component',
            'basis' => 'event_factor_hash combines event_revision_set_hash (event revisions), source_scale_assessment_set_hash and factor_decision_set_hash (verification/decision states over factor-set revisions), the existing factor_set_hash, and a fifth member -- a componentGroupHash() over the bound context\'s ancillary (C09) capture references, whose own payload already contains corporate-action-window and price-scale-break contamination content (ProducerAncillaryCapture::deriveContamination/derivePriceScaleBreaks, asserted at capture time). All four named members are bound. The domain-isolation probe proves the ancillary component specifically drives this field and nothing else.',
        ],
        'MD-S019-R0067' => [
            'positive' => 'B18ReplayComparisonExhaustivenessTest::test_a_divergence_in_any_frozen_input_denies_pass',
            'negative' => 'ReplayVerificationServiceTest::test_temporal_identity_and_event_factor_hash_are_domain_isolated_by_component',
            'basis' => 'same basis as MD-S050-R0008, which this restates as the Invariant-14 antecedent; the consequent MD-S019-R0073 remains outstanding',
        ],
        'MD-S019-R0068' => [
            'positive' => 'B18ReplayComparisonExhaustivenessTest::test_a_divergence_in_any_frozen_input_denies_pass',
            'negative' => 'B18ReplayComparisonExhaustivenessTest::test_a_fixture_declaring_every_frozen_input_correctly_still_passes',
            'basis' => 'same basis as MD-S050-R0009, which this restates as the Invariant-14 antecedent; the consequent MD-S019-R0073 remains outstanding',
        ],
        'MD-S019-R0069' => [
            'positive' => 'B18ReplayComparisonExhaustivenessTest::test_a_divergence_in_any_frozen_input_denies_pass',
            'negative' => 'ReplayVerificationServiceTest::test_temporal_identity_and_event_factor_hash_are_domain_isolated_by_component',
            'basis' => 'this rule\'s narrower wording ("corporate-action event/factor-set revisions") is the subset of MD-S050-R0012\'s four members that event_revision_set_hash/factor_decision_set_hash/factor_set_hash already cover on their own, independent of the ancillary/contamination member added for R0012; the consequent MD-S019-R0073 remains outstanding',
        ],


        // ---- Re-verified under MD-B18-A002: predicates whose MD-B18-A001 guard does establish
        // them. The guards were re-executed in this attempt (149 tests green) and re-probed --
        // 11/11 fail-closed probes caught with controls green either side -- because a pass
        // proven under a withdrawn closure is not inheritable. Thirteen of these are citation
        // prohibitions carried by the declared corpus guard, which legitimately covers many
        // predicates: it scans every active surface for the claims they forbid.
        'MD-S002-R0016' => [
            'positive' => 'B18ReplayAdmissibilityBoundaryTest::test_no_active_surface_cites_a_replay_verdict_for_something_replay_cannot_establish',
            'negative' => 'B18ReplayAdmissibilityBoundaryTest::test_every_pattern_matches_the_claim_it_forbids_and_spares_the_denial',
            'basis' => 'citation boundary for a metric set',
        ],
        'MD-S003-R0022' => [
            'positive' => 'B18AsKnownSnapshotIsolationTest::test_a_later_cutoff_exposes_later_revisions_without_rewriting_the_earlier_snapshot',
            'negative' => 'B18AsKnownSnapshotIsolationTest::test_every_later_revision_kind_is_bound_to_an_executing_guard',
            'basis' => 'one corpus asserts that a declared later cutoff exposes distinct master, status, calendar, config/formula, event and factor revisions while a rerun at the earlier cutoff retains its byte-identical hash and performs no bound-input writes',
        ],
        'MD-S003-R0024' => [
            'positive' => 'ReplayAdmissibilityVerdictStorabilityTest::test_a_relabelled_self_generated_fixture_is_still_refused',
            'negative' => 'ReplayAdmissibilityVerdictStorabilityTest::test_the_inadmissible_verdict_is_never_counted_as_a_pass',
            'basis' => 'fixtures must be independent oracles and self-generated expectations are refused - exactly the subject of the guard',
        ],
        'MD-S003-R0031' => [
            'positive' => 'B18ReplayAdmissibilityBoundaryTest::test_no_active_surface_cites_a_replay_verdict_for_something_replay_cannot_establish',
            'negative' => 'B18ReplayAdmissibilityBoundaryTest::test_every_pattern_matches_the_claim_it_forbids_and_spares_the_denial',
            'basis' => 'citation boundary for a clean quality replay',
        ],
        'MD-S004-R0011' => [
            'positive' => 'B18ReplayAdmissibilityBoundaryTest::test_no_active_surface_cites_a_replay_verdict_for_something_replay_cannot_establish',
            'negative' => 'B18ReplayAdmissibilityBoundaryTest::test_every_pattern_matches_the_claim_it_forbids_and_spares_the_denial',
            'basis' => 'capability-boundary statement about what may be claimed',
        ],
        'MD-S050-R0001' => [
            'positive' => 'ReplayModeContractTest::test_only_the_two_locked_replay_modes_are_accepted',
            'negative' => 'OpsCommandSurfaceTest::test_replay_verify_refuses_an_inadmissible_mode_without_attempting_verification',
            'basis' => 'only the two locked modes accepted is the subject of the guard',
        ],
        'MD-S050-R0045' => [
            'positive' => 'B18ReplayAdmissibilityBoundaryTest::test_no_active_surface_cites_a_replay_verdict_for_something_replay_cannot_establish',
            'negative' => 'B18ReplayAdmissibilityBoundaryTest::test_every_pattern_matches_the_claim_it_forbids_and_spares_the_denial',
            'basis' => 'capability boundary: replay cannot prove value correctness',
        ],
        'MD-S050-R0050' => [
            'positive' => 'B18ReplayAdmissibilityBoundaryTest::test_no_active_surface_cites_a_replay_verdict_for_something_replay_cannot_establish',
            'negative' => 'B18ReplayAdmissibilityBoundaryTest::test_every_pattern_matches_the_claim_it_forbids_and_spares_the_denial',
            'basis' => 'explicit admissibility rule for a replay PASS',
        ],

        // ---- MD-S050 "Required fixtures include" -- the eight named anti-survivorship cases.
        // Three identity cases had no executing fixture anywhere and were built in this attempt;
        // the other five already had behavioural guards that execute exactly their scenario, and
        // B18AntiSurvivorshipFixtureCorpusTest binds each case to its guard and asserts that guard
        // still exists, so a rename cannot silently remove a required fixture. 5/5 fail-closed
        // probes caught, controls green either side.
        'MD-S050-R0019' => [
            'positive' => 'B18AntiSurvivorshipFixtureCorpusTest::test_a_listing_active_at_T_but_delisted_today_stays_in_the_historical_universe',
            'negative' => 'B18AntiSurvivorshipFixtureCorpusTest::test_every_named_fixture_guard_exists_and_is_executable',
            'basis' => 'a listing delisted in 2025 is asserted present in the 2024 universe and absent from a 2026 read, so both directions are executed; removing the delisted-date interval from the universe query turns it red',
        ],
        'MD-S050-R0020' => [
            'positive' => 'B18AntiSurvivorshipFixtureCorpusTest::test_a_symbol_change_resolves_to_the_symbol_and_mapping_effective_on_the_trade_date',
            'negative' => 'B18AntiSurvivorshipFixtureCorpusTest::test_every_named_fixture_guard_exists_and_is_executable',
            'basis' => 'the pre-change symbol and mapping resolve on a pre-change date, the post-change pair on a post-change date to the same listing id, and asking for the post-change symbol on a pre-change date is refused with PROVIDER_SYMBOL_MAPPING_MISSING rather than returning a soft miss',
        ],
        'MD-S050-R0021' => [
            'positive' => 'B18AntiSurvivorshipFixtureCorpusTest::test_reused_symbol_text_resolves_to_the_listing_that_held_it_on_the_trade_date',
            'negative' => 'B18AntiSurvivorshipFixtureCorpusTest::test_every_named_fixture_guard_exists_and_is_executable',
            'basis' => 'the same symbol text resolves to two different listings on either side of the handover, asserted through both the provider mapping and the projected universe so the symbol effective interval is load-bearing, with exactly one listing holding the text on each date',
        ],
        'MD-S050-R0024' => [
            'positive' => 'AsKnownReplayBoundaryTest::test_a_configuration_recorded_after_the_cutoff_is_invisible_and_none_is_created',
            'negative' => 'B18AntiSurvivorshipFixtureCorpusTest::test_every_named_fixture_guard_exists_and_is_executable',
            'basis' => 'a configuration recorded after the cutoff is asserted invisible and no snapshot is invented in its place',
        ],
        'MD-S050-R0026' => [
            'positive' => 'SourceObservationAsKnownBoundaryTest::test_zero_row_provider_outage_remains_in_as_known_observation_manifest',
            'negative' => 'B18AntiSurvivorshipFixtureCorpusTest::test_every_named_fixture_guard_exists_and_is_executable',
            'basis' => 'a zero-row provider outage is asserted to remain in the as-known observation manifest with its SOURCE_TIMEOUT reason code, so it cannot vanish through dormancy or current-universe filtering',
        ],

        // ---- The reproducibility consequent. MD-S019-R0066..R0072 establish that the identities
        // are bound; these three establish that binding them identically produces the same bytes.
        // Byte-identity rather than field equality, because a reordered key or a stamped wall
        // clock gives equal fields and different bytes, and it is the bytes that get hashed.
        // Probes: injecting microtime into the replay result turned it red, and neutralising the
        // changed-input fixture turned the negative guard red.

        // ---- MD-S003 "Required scenario families" / degraded acquisition. These are MD-B18's
        // own replay-suite obligations: the heading makes the obligation "a scenario proving it
        // must run", not "the subsystem must behave". Two of the three are established by
        // behavioural guards that already execute exactly the scenario; R0007 is deliberately
        // absent because one of its four named observation defects -- schema-invalid -- has no
        // guard anywhere in the suite.
        'MD-S003-R0006' => [
            'positive' => 'CalendarProvenanceAndStatusTest::test_absent_status_evidence_resolves_to_unknown_not_normal',
            'negative' => 'CalendarProvenanceAndStatusTest::test_a_long_suspension_is_not_reclassified_as_dormancy',
            'basis' => 'absent status evidence resolves to UNKNOWN with TRADING_STATUS_NO_EVIDENCE rather than NORMAL, and a multi-year suspension stays SUSPENSION rather than being reclassified DORMANT - the two ways an unknown expectation could become a holiday or dormancy. A verified holiday is still refused as a non-trading day by test_a_verified_holiday_is_refused_as_a_non_trading_day, so the guard is not satisfied by answering UNKNOWN to everything. Probe: relabelling the no-evidence reason code turned it red.',
        ],
        'MD-S003-R0008' => [
            'positive' => 'EmptyDatasetFailSafeTest::test_an_available_fallback_holds_the_run_rather_than_failing_it',
            'negative' => 'EmptyDatasetFailSafeTest::test_no_fallback_fails_the_run_outright',
            'basis' => 'a run that falls back to a prior date is asserted HELD and NOT_READABLE with trade_date_effective carrying the prior date, so the earlier result cannot present itself as requested-date fresh data; the negative guard fails the run outright when no fallback exists, so the pair is not satisfied by an implementation that always holds.',
        ],
        'MD-S003-R0007' => [
            'positive' => 'B18DegradedObservationDefectCorpusTest::test_a_prior_date_row_holds_the_run_under_its_own_stale_reason',
            'negative' => 'B18DegradedObservationDefectCorpusTest::test_the_same_fixture_dated_on_the_requested_day_becomes_canonical',
            'basis' => 'the four named defects are read from the MD-S003 line itself and each bound to an executing guard with the outcome it proves: zero-price and schema-invalid quarantine (CanonicalRawImportBoundaryTest, PublicApiEodBarsAdapterTest, both pre-existing), stale and wrong-date hold. The last two had no executing guard -- the only candidate asserted that the string RUN_STALE_DATA appears in the ingest source -- so they are new: a misdated row is fed to the real EodBarsIngestService and asserted to hold the run under RUN_STALE_DATA naming the date it saw, in both directions and in a mixed batch, with nothing reaching the artifact writer. The negative guard ingests the byte-identical fixture dated on the requested day and asserts it becomes canonical, so an ingest that refuses everything cannot satisfy the three. Probes: relaxing the boundary to count>1 turned both single-date guards red (the row then fell through to RUN_SOURCE_NO_VALID_DATA), relabelling the reason RUN_PARTIAL_DATA turned all three red, widening it to count>=1 turned the control red, and breaking either half of the reviewed map turned the mapping guards red.',
        ],

        // ---- MD-S003 corporate actions and indicators. Same reading as R0006/R0008: the heading
        // makes each member "a scenario proving it must run". Each is bound to a guard that
        // executes the real surface, and each was probed against the mutation that would make the
        // rule false rather than against a rename.
        'MD-S003-R0012' => [
            'positive' => 'AdjustmentFactorSetB11Test::test_only_authoritative_or_manual_verified_revisions_are_adjustment_active',
            'negative' => 'CorporateActionCandidateBoundaryTest::test_a_price_derived_action_does_not_suppress_contamination',
            'basis' => 'the guard seeds a SYNTHETIC_CANDIDATE revision alongside PROVIDER_REPORTED and REJECTED ones and asserts the factor set resolves exactly one row, AUTHORITATIVE_VERIFIED, so the synthetic price-break candidate never activates a factor while a verified event still does -- the assertion is not satisfied by a factor set that admits nothing. The negative guard asserts that refusing the price-derived factor leaves the window quarantined rather than silently clean, which is the way the rule would otherwise be honoured and still corrupt output. Probe: adding SYNTHETIC_CANDIDATE to the verification whereIn in AdjustmentFactorSetService turned the positive red.',
        ],
        'MD-S003-R0013' => [
            'positive' => 'CoherentPriceProductBoundaryTest::test_every_ohlc_field_moves_on_the_same_scale',
            'negative' => 'CoherentPriceProductBoundaryTest::test_provider_adjusted_close_is_not_scaled_by_a_platform_factor',
            'basis' => 'a verified factor revision (price 0.5, volume 2.0) is applied through the real AnalyticalPriceProductService and open/high/low/close are each asserted at their own scaled value with volume asserted inverse, so structural coherence is checked field by field rather than on close alone. The negative guard asserts the provider adj_close is left as observed, so "scale every numeric field" -- the naive way to pass the positive -- fails. Probes: dropping high from the scaled field list turned the positive red; adding adj_close to it turned the negative red.',
        ],
        'MD-S003-R0015' => [
            'positive' => 'B18LongChainWilderAtrOracleTest::test_a_two_hundred_session_chain_matches_the_independently_computed_wilder_atr',
            'negative' => 'B18LongChainWilderAtrOracleTest::test_a_correction_thirty_sessions_back_still_moves_the_atr_by_its_decayed_amount',
            'basis' => 'new. The pre-existing ATR oracle runs on a ramp whose true range is constant, and its own comment says a Wilder average of a constant series is that constant regardless of seeding -- so it cannot detect a wrong recursion, and its correction oracle measures ma20, a finite window. This guard runs 200 varied-true-range sessions against a recursion transcribed from EOD_Indicators_Formula_Spec.md (seed = mean of the first 14 TRs, then (ATR*13 + TR)/14), and a third test pins that transcription on a 16-bar chain so the seed mean and the Wilder step cannot be conflated. The correction half edits one historical high 30 sessions before the requested date -- only that TR moves, since TR reads the previous close -- and asserts the ATR moves by exactly deltaTR/14 * (13/14)^30, plus separately that it moves at all, which is the spec claim that impact is recursive and unbounded until recomputed, not a fixed 15-day horizon. Probes: changing the coefficient to (ATR*14 + TR)/15 turned both red; truncating the chain to a 28-session horizon made the correction guard report 0.0 against an expected 0.0387, the exact truncation the spec forbids. A control asserts an edit outside the evaluated chain moves nothing.',
        ],
        'MD-S003-R0016' => [
            'positive' => 'ActualTradedValueProvenanceTest::test_a_proxy_derived_value_cannot_enter_the_actual_field',
            'negative' => 'ActualAndProxyLiquiditySemanticsTest::test_the_legacy_alias_carries_the_proxy_value_and_never_the_actual',
            'basis' => 'the meaning half executes ActualTradedValueFactService and asserts a value_origin of CLOSE_VOLUME_PROXY is refused with ACTUAL_TRADED_VALUE_NOT_SOURCE_REPORTED, so a proxy-derived number cannot acquire the actual meaning; the field-identity half executes IndicatorVectorService and asserts adv20_close_volume_proxy_idr is populated while adv20_traded_value_idr_actual stays null, and that the legacy dv20_idr alias carries the proxy and never the actual -- the alias being the path by which the two would share identity in practice. Probes: admitting CLOSE_VOLUME_PROXY to the origin allowlist turned the positive red; writing the proxy into the actual field in the vector service turned five guards red across both classes, the negative among them.',
        ],

        // ---- MD-S003 correction and read path. Each member is exercised on its actual owning
        // runtime surface rather than inferred from the replay verifier that happened to be the
        // old family-level assignment.
        'MD-S003-R0017' => [
            'positive' => 'MarketDataEvidenceExportServiceTest::test_export_run_evidence_resolves_historical_sealed_publication_without_current_pointer_dependency',
            'negative' => 'SealedArtifactMutationGuardTest::test_mutating_a_sealed_readable_but_no_longer_current_publication_is_blocked',
            'basis' => 'a superseded sealed publication is exported through HISTORICAL_PUBLICATION_AUDIT with publication-scoped lineage and no current-pointer dependency, while the mutation guard refuses writes to that same class of sealed non-current readable baseline. Probes: forcing historical_publication_allowed false turned the export guard red; disabling the live sealed-dataset guard turned the mutation guard red while the unsealed control stayed green.',
        ],
        'MD-S003-R0018' => [
            'positive' => 'MarketDataPipelineIntegrationTest::test_run_daily_correction_replaces_current_publication_and_marks_correction_published',
            'negative' => 'MarketDataPipelineIntegrationTest::test_run_daily_correction_with_reseal_failure_keeps_prior_current_and_leaves_candidate_non_current',
            'basis' => 'the end-to-end correction path creates a distinct version, validates it, seals it, atomically makes it current and marks the correction PUBLISHED; an injected seal failure leaves the candidate UNSEALED/non-current and the prior sealed pointer untouched. The separate finalize precondition guard was executed and its mutation-probe (ignoring candidate seal state) turned red.',
        ],
        'MD-S003-R0019' => [
            'positive' => 'PublicationRepositoryIntegrationTest::test_candidate_seal_and_promote_updates_current_pointer_and_prior_publication',
            'negative' => 'PublicationSealPointerLifecycleTest::test_the_pointer_table_structurally_refuses_a_second_current_row',
            'basis' => 'promotion resolves the one date pointer to the newly sealed publication and clears the prior current mirrors in one repository transaction; the pointer primary key independently refuses a second publication for the same date. Probe: removing the mirror-schema primary key made the duplicate-pointer guard red.',
        ],
        'MD-S003-R0020' => [
            'positive' => 'SourceFailureResilienceTest::test_holding_to_a_fallback_date_is_still_not_readable',
            'negative' => 'EmptyDatasetFailSafeTest::test_no_fallback_fails_the_run_outright',
            'basis' => 'the explicit fallback case retains the prior trade_date_effective while asserting HELD, NOT_READABLE, quality FAIL, coverage FAIL and RUN_PARTIAL_DATA; the no-fallback control fails rather than inventing an effective date. Probes: dropping the fallback effective date and making the base state readable each turned the positive red.',
        ],

        // ---- Replay comparison exhaustiveness and mode/import-promote policy. Two of these are code
        // changes rather than guards: appendImportPromotionPolicyMismatches() had no implementation at all,
        // and upsertMetric was defaulting a missing replay mode.
        'MD-S085-R0452' => [
            'positive' => 'B18ReplayComparisonExhaustivenessTest::test_a_manual_file_run_readable_without_a_coverage_pass_is_a_mismatch',
            'negative' => 'B18ReplayComparisonExhaustivenessTest::test_the_unperturbed_fixture_passes',
            'basis' => 'the replay reason code is asserted to be a comparison outcome and nothing more: the verdict is FAIL or BLOCKED with comparison_result MISMATCH, and the publication repository is passed as a mock carrying no expectations at all, so any seal or pointer move the replay attempted would fail the test before it could be observed. A replay that created a readable publication would therefore be caught rather than recorded.',
        ],
        'MD-S050-R0035' => [
            'positive' => 'ReplayResultRepositoryIntegrationTest::test_an_incomplete_bound_input_set_is_refused_rather_than_persisted',
            'negative' => 'ReplayResultRepositoryIntegrationTest::test_replay_result_repository_persists_metric_and_reason_code_counts',
            'basis' => 'code fix. The guard drives upsertMetric with replay_mode null and with an unsupported value and asserts each is refused with REPLAY_MODE_REQUIRED and REPLAY_MODE_UNSUPPORTED rather than persisted, so mode is a first-class field of every stored result rather than an optional one. The working tree had upsertMetric defaulting a missing mode to PUBLICATION_EXACT, which made publication and as-known outcomes indistinguishable -- the exact state R0035 says does not satisfy the mandatory-mode rule -- and left this test red; the default was removed rather than the test relaxed. The negative guard persists a complete metric and reads it back, so the rule is not satisfied by refusing everything. Probe: reinstating the default turned the positive red.',
        ],

        // ---- As-known temporal sequence. The suite proved the cutoff hides later facts and never proved it
        // shows earlier ones, so a cutoff implemented as a wall would have passed everything.
        'MD-S058-R0069' => [
            'positive' => 'B18AsKnownTemporalSequenceTest::test_a_suspension_lifted_later_is_still_suspended_as_known_before_the_lift_was_recorded',
            'negative' => 'B18AsKnownTemporalSequenceTest::test_the_current_status_is_not_substituted_for_the_as_known_one',
            'basis' => 'new. The sequence the contract names is executed through the real TemporalTradingStatusRepository: a suspension effective 2026-03-10 recorded 2026-03-12, and the lift that closes it effective 2026-03-18 but recorded 2026-05-01 as a superseding revision, which is how this schema expresses a status ending -- a bare NORMAL row is rejected as TRADING_STATUS_TYPE_UNGOVERNED and would not model an unsuspension. One trade date read at two cutoffs and with none: SUSPENSION while only the suspension is on record, no suspension in force once the lift is. A cutoff treated as a wall gives one answer at every read and a current-state lookup gives the other, so neither substitution passes. The negative guard pins that current and as-known genuinely disagree on this fixture, and a third test asserts a listing with no status record resolves UNKNOWN with TRADING_STATUS_NO_EVIDENCE rather than assumed normal, which is the missing-provider-bar half. Probe: dropping the knowledge-time restriction from the supersession join turned both red while all eleven AsKnownReplayBoundaryTest guards stayed green.',
        ],

        // ---- Blocked-not-passed. Both rest on the same admissibility refusal, asserted from two sides:
        // the verdict it produces and the fallback it declines to make.
        'MD-S082-R0015' => [
            'positive' => 'B18ReplayComparisonExhaustivenessTest::test_a_publication_with_no_configuration_snapshot_is_blocked_rather_than_passed',
            'negative' => 'B18ReplayComparisonExhaustivenessTest::test_the_unperturbed_fixture_passes',
            'basis' => 'publication replay over a CONFIG_UNBOUND publication is asserted BLOCKED rather than PASS, and the block is asserted to name REPLAY_CONFIG_UNBOUND explicitly rather than being an anonymous refusal -- which is the naming-the-state half of the citation rule. The negative guard is the byte-identical fixture whose publication does carry a config snapshot reaching PASS, so the rule is not satisfied by blocking every replay. A further guard asserts the blocked row is persisted with admission_state NOT_ADMISSIBLE, so the state travels with any later citation of it. Probe: disabling the CONFIG_UNBOUND rule turned the positive red.',
        ],

        // ---- MD-S050-R0002 and what it unlocked. The frozen-input comparison did not exist: the identities
        // were recorded into every result and compared by nothing.
        //
        // F-MD-B18-A002-021 closing remediation: this eleven-item conjunction's last two residual gaps --
        // eligibility version captured nowhere, and read_model_version/serialization_version/executable_build_identity
        // reading live config -- are both closed (the second by E-047/E-048; the first by D-MD-B18-A002-006's
        // config-driven eligibility_contract_version, captured through the existing registry_versions component,
        // no new field). Reviewing for promotion surfaced one further, previously-unproven gap of its own:
        // formula_registry_hash/reason_registry_hash's extraction (scanning components for component_key ===
        // 'registry_versions' and taking its payload_hash) had never been exercised by any test with a real
        // matching component present -- the exhaustiveness fixture supplied none, so its own baseline for
        // those two fields was permanently the empty string. Closed by adding a real registry_versions entry
        // to that fixture and a dedicated test
        // (ReplayVerificationServiceTest::test_formula_and_reason_registry_hash_come_from_the_registry_versions_component)
        // proving presence yields the real payload_hash, a change moves both fields, and absence is an honest
        // empty value. A further, explicitly-required check: a registry_versions capture written before
        // eligibility_contract_version existed must not become BLOCKED merely because current code always
        // writes the field now -- verifyBoundContext() never re-derives a component's expected content from
        // current config or code, only checks a component's own stored payload_hash against its own stored
        // content, so an old-shape capture verifies exactly as well as a new one. Proven directly
        // (B18BeforeSealValidationTest::test_reader_verifies_a_registry_versions_capture_predating_eligibility_contract_version)
        // against the real Reader path with a hand-inserted old-shape capture: VERIFIED, not BLOCKED, and its
        // registry_content honestly carries no eligibility_contract_version key rather than a manufactured one.
        'MD-S050-R0002' => [
            'positive' => 'B18ReplayComparisonExhaustivenessTest::test_a_divergence_in_any_frozen_input_denies_pass',
            'negative' => 'B18ReplayComparisonExhaustivenessTest::test_a_fixture_declaring_every_frozen_input_correctly_still_passes',
            'basis' => 'code change, not only a guard. None of the eleven frozen-input identities was compared: verifyRunAgainstFixture wrote them into the stored result and compareExpectedAndActual never looked, and the fixture schema had no expected side at all, so a replay resolving today\'s indicator registry, today\'s build identity, or a different temporal identity than the one frozen with the publication reported MATCH. Added ReplayVerificationService::actualBoundInputContext() as the single source for the recorded and the compared value, an expected_bound_input_context block read from the fixture, and a per-field comparison over BOUND_INPUT_FIELDS. The guard declares all eleven at the values the replay resolved and perturbs one at a time, asserting the verdict is denied and the mismatch names bound_input_<field>; a companion test parses the MD-S050 publication-replay sentence and asserts the reviewed map covers exactly the inputs it names, so an input added to the contract with no field behind it fails. The negative guard declares all eleven correctly and still reaches PASS, so the rule is not satisfied by rejecting any fixture that carries the block. All eleven fields are now individually real and non-live: temporal_identity_hash/event_factor_hash from componentGroupHash() over the universe_identity/ancillary captures (domain-isolation proven), config/formula/reason from real captured content, and read_model_version/serialization_version/executable_build_identity/eligibility (folded into the combined registry_versions payload_hash) all genuinely captured and decoded, not live-config or hardcoded. Reason codes are the registry as it stands -- config identity has its own, the rest fall through to REPLAY_NON_DETERMINISTIC_OUTPUT, which the registry defines as a deterministic-field mismatch with no more specific code; a dedicated REPLAY_BOUND_INPUT_MISMATCH was written and then reverted because Reason_Codes_Registry.md is STRATEGY/CONTROLLED_REVISION and adding vocabulary so an implementation change can emit it is not an implementation decision. Probe: comparing an empty field list turned exactly the eleven perturbations red and nothing else.',
        ],
        // ---- MD-S003-R0003, promoted alongside MD-S050-R0002 above for the same closing reason: its map
        // reuses that predicate's frozen-input perturbations for "temporal revisions" and "formulas", so the
        // same eleven-field closure applies directly.
        'MD-S003-R0003' => [
            'positive' => 'B18ReplayComparisonExhaustivenessTest::test_the_exact_verification_map_names_exactly_what_the_contract_names',
            'negative' => 'B18ReplayComparisonExhaustivenessTest::test_every_guard_the_exact_verification_map_names_exists',
            'basis' => 'the eleven items MD-S003 names for exact publication verification -- frozen observations, temporal revisions, config, factors, formulas, artifacts, hashes, manifest, seal, reasons, terminal state -- are each mapped to an executing guard in this class, and the map is checked against the contract line parsed from the document rather than transcribed, so an item added with nothing verifying it fails. Observations, temporal revisions, config, factors and formulas are covered by the frozen-input perturbations added for MD-S050-R0002; artifacts, hashes, seal, reasons and terminal state by the assertion-class perturbations; manifest by the fixture whose manifest declares a file it does not carry, which is refused outright. The negative guard asserts every guard the map names still exists, so a rename empties nothing silently. "Formulas" shares MD-S050-R0002\'s eligibility/formula/reason/read-model/serialization/build closure directly, since both read the same actualBoundInputContext() fields through the same perturbation table. Probe: removing seal from the map turned the positive red.',
        ],
        // ---- MD-S050-R0016, five-domain cumulative closure (E-MD-B18-A002-074): the last three
        // previously-unverified required bound inputs (temporal_identity_hash,
        // source_observation_manifest_hash, canonical_raw_input_hash) are now fail-closed and
        // probed; calendar_status_hash and event_factor_hash's remaining sub-components are
        // confirmed already-protected or legitimately-empty rather than fixed.
        'MD-S050-R0016' => [
            'positive' => 'B18ReplayComparisonExhaustivenessTest::test_a_config_snapshot_with_no_recorded_hash_is_blocked_rather_than_passed',
            'negative' => 'B18ReplayComparisonExhaustivenessTest::test_an_unrecorded_config_hash_is_not_filled_from_current_state',
            'basis' => 'PROVEN. Replay_Verification_Contract_LOCKED.md\'s Required bound inputs list plus ":33" ("Missing input is BLOCKED, not permission to query current/latest state") apply to eleven bound-input fields (BOUND_INPUT_FIELDS) in both replay modes. G03 (E-MD-B18-A002-067) proved the publication-mode service half: after the VERIFIED check, replayAdmissibility() blocks an empty source observation, canonical raw input, temporal, formula/reason registry, read-model, serialization or build identity as REPLAY_BOUND_INPUT_INCOMPLETE, and the direct write refuses a non-BLOCKED result missing any of those in either mode. Gap A (E-MD-B18-A002-069): a config snapshot ID with no recorded hash resolves to the honest CONFIG_IDENTITY_UNRECORDED marker and is blocked rather than accepted as non-empty, at both admission and direct write; AS_KNOWN\'s own config path (MarketDataConfigSnapshotRepository::resolveAsKnown()) independently throws CONFIG_SNAPSHOT_NOT_KNOWN_AT_CUTOFF when no snapshot was recorded at or before the cutoff. Gap B1 (E-MD-B18-A002-071, D-MD-B18-A002-008): AS_KNOWN read_model_version binds MarketDataReadProductService::READ_MODEL_VERSION instead of a nonexistent config key, required at direct write in both modes; formula_registry_hash derives structurally from the config snapshot\'s own resolvedConfigPayload(), which throws on a missing/malformed resolved_config or semantic_bindings, so it cannot silently resolve empty either; serialization_version is a raw, unhashed pass-through of the config row, so a genuinely-missing value produces a genuinely-empty string the existing both-modes empty() check already catches; executable_build_identity is explicitly permitted to reflect the live executable rather than a historical binding, per the same precedent MD-S050-R0002 established. Gap B2 (E-MD-B18-A002-073): AS_KNOWN reason_registry_hash is the honest REASON_REGISTRY_IDENTITY_UNAVAILABLE marker instead of a hash of two hardcoded arrays; verifyAsKnownAgainstFixture() returns BLOCKED before invoking the production canonicalizer whenever it captures that marker, unconditionally, since MD-S085 defines no historical reason-registry identity any as-known replay could ever bind; the direct write refuses a non-BLOCKED result carrying it, in both modes. This unit (five-domain cumulative closure, E-MD-B18-A002-074) closes the last three previously-unverified inputs. temporal_identity_hash: readProjectedUniverseAsOf() never threw on an empty universe -- a whole-system-empty state, unconditional on trading-day status since listing existence does not depend on today being a trading day -- so AsKnownReplaySnapshotService::capture() now returns TEMPORAL_IDENTITY_UNAVAILABLE when the universe is empty, checked identically at admission and direct write. source_observation_manifest_hash/canonical_raw_input_hash: observationManifestAsKnown()/normalizedRowsManifestAsKnown() never threw on zero rows either; capture() now returns SOURCE_OBSERVATION_UNAVAILABLE when a trading day (calendar_context.is_trading_day) has zero recorded observations or zero identity-bound rows, gated on trading-day status because a non-trading day legitimately has none to record -- the same distinction ExpectedBarDecisionService::decide() already draws via EXPECTED_BAR_NON_TRADING_DAY, not an invented rule. calendar_status_hash: its calendar half was already, independently fail-closed -- MarketCalendarRepository::sessionContext() throws MARKET_CALENDAR_EVIDENCE_MISSING when no calendar row exists for the date, now proven as an executing boundary rather than assumed from reading the repository; its trading-status half legitimately returns a named UNKNOWN/no-evidence sentinel per listing when no override exists, which is the common, correct default for the overwhelming majority of listing-days (most listings have no special status override most days) and is itself distinctly represented in the hashed content, not a missing required input. event_factor_hash: zero corporate-action/factor-set revisions is the common, legitimate value for the vast majority of trade dates, confirmed by code inspection (no minimum-cardinality requirement exists anywhere in this domain) and a ground-truth capture against a fully empty world; no counterpart of is_trading_day exists to distinguish a genuine gap from a correct absence here, so no discriminating missing-input test is constructible, and none is required. Real-path proof for the two new fail-closed domains: a whole-system-empty temporal universe is BLOCKED, not PASS; a trading day with zero recorded observations at all is BLOCKED, not PASS, distinct from a recorded zero-row provider outage (SourceObservationAsKnownBoundaryTest::test_zero_row_provider_outage_remains_in_as_known_observation_manifest), which stays legitimately admissible; a non-trading day with zero observations is not marked unavailable (negative control, direct against capture()); the calendar throw is proven executing, not assumed. Four probes (temporal empty-check bypassed, source-observation empty-check bypassed, admission-check bypassed for both new markers, storage check removed) each isolated to their own target, byte-restored and sha256-verified; publication-mode and sibling AS_KNOWN control suites (B18AsKnownSnapshotIsolationTest, ReplayVerificationServiceTest, SourceObservationAsKnownBoundaryTest) unaffected by every probe. Full tests/Unit/MarketData: 2475 tests, 34674 assertions, 7 failures, 0 errors -- exact match to the MD-DEP-0015 corpus-oracle baseline, zero new failures. Every canonical required input this predicate\'s own BOUND_INPUT_FIELDS scope names is now either present-when-required-and-checked, explicitly fail-closed with an executing probe when unavailable, structurally protected by an upstream throw, or authority-confirmed as legitimately empty with no missing-input failure mode -- no current/latest fallback exists anywhere in this closure. MD-S050-R0016 therefore promotes to PROVEN.',
        ],
        // ---- F-MD-B18-A002-017 G01-B (E-MD-B18-A002-075): MD-S003-R0023 and MD-S004-R0004 rebound from a
        // hand-built exported row to the real writer -> persisted row -> real export. No production change.
        'MD-S003-R0023' => [
            'positive' => 'B18ReplayPersistedEvidenceBindingTest::test_the_real_writer_records_every_frozen_identity_and_the_export_reproduces_it',
            'negative' => 'B18ReplayPersistedEvidenceBindingTest::test_each_frozen_identity_follows_only_its_own_canonical_source',
            'basis' => 'PROVEN (proof rebound, no production change; E-MD-B18-A002-075, F-MD-B18-A002-017 G01-B). Historical_Replay_and_Data_Quality_Backtest.md:50, "Per-run evidence": record replay mode, fixture/manifest hash, requested/effective dates, knowledge cutoff, all frozen revision/snapshot IDs, expected/actual readiness and reason sets, field-level mismatch paths, artifact/manifest/seal hashes, executable build identity, and PASS/FAIL/BLOCKED -- a recording obligation on both replay modes (the cutoff is an AS_KNOWN item; a publication result records it null by design). The recorded pair (B18ReplayEvidenceSelfExplanationTest) exports a hand-built metric through a mocked repository, so it cannot fail if the real writer stops recording an identity, records a constant, or reads today\'s environment -- the row never came from the writer; it stays as the reviewed-map and export-shape guard and is no longer the proof. New proof, B18ReplayPersistedEvidenceBindingTest, drives the real ReplayVerificationService -> real ReplayResultRepository::upsertMetric -> md_replay_daily_metrics -> real EodEvidenceRepository::findReplayMetric -> real MarketDataEvidenceExportService, stubbing only what the writer consumes (the run, the publication and the VERIFIED bound-input context Reader projects), which are the canonical sources each identity is asserted against. Positive: all twelve frozen identities (observation manifest, canonical raw input, temporal, calendar/status, event/factor, config id and hash, formula, reason registry, read-model, serialization, executable build) are persisted non-empty, exported exactly as persisted, and equal to their canonical source where that source is one stated value; the rest of the line (mode, null cutoff for a publication replay, fixture manifest hash, requested/effective dates, artifact hash, PASS) is read back from the same export, and a diverging replay records the named mismatch path (bars_batch_hash). Negative, eleven per-source perturbations: changing exactly one canonical source moves exactly the identities it feeds and no other, so a constant, a live read or one identity standing in for another fails where a presence check cannot; and a second real replay run after the live configuration and build identity were changed records identical identities (later environment drift cannot change exact evidence). The AS_KNOWN mode and knowledge cutoff are asserted on the real as-known writer\'s persisted-and-exported result in B18AsKnownModeIsolationTest. Probes, each byte-restored and sha256-verified: the writer stops persisting event_factor_hash (3 new tests RED; the old hand-built guard stayed 14/14 GREEN), the writer reads the build identity from live config (3 RED, old guard GREEN), the writer stops persisting the as-known knowledge cutoff (the export test and the R0005 recording test RED, old guard GREEN). No production code changed: every guard passed unmutated on its first run, so this was a proof-guard gap, not an implementation defect (classification PROOF_GUARD_GAP; the earlier REBIND_ONLY reading was corrected because no existing guard drove the real writer).',
        ],
        'MD-S004-R0004' => [
            'positive' => 'B18AsKnownModeIsolationTest::test_the_as_known_export_binds_listing_identity_per_row_and_per_export',
            'negative' => 'B18ReplayPersistedEvidenceBindingTest::test_factor_identity_and_formula_identity_are_bound_independently',
            'basis' => 'PROVEN (proof rebound, no production change; E-MD-B18-A002-075, F-MD-B18-A002-017 G01-B). Point_In_Time_Backtest_Input_Contract_LOCKED.md:15, "Required input identity": every row/export binds listing identity, requested/effective trade date, knowledge cutoff, as-known replay ID/publication-like artifact ID, read-model version, full config hash, factor/formula versions, and lineage; the availability timestamp is distinct from the market trade date. Both replay modes; the cutoff is as-known only and the publication id publication only. Three defects in the recorded binding, none in production. (1) Listing identity was mapped to the run-level universe hash alone; authority reads at two granularities ("row/export", with "symbol changes/reuse use listing IDs"), so both are now proven and neither stands in for the other: the export binds the universe identity (temporal_identity_hash), and every row of the exported as-known dataset -- universe rows and normalized source rows -- names its own listing_id. (2) "Factor/formula versions" was mapped to formula_registry_hash alone; factor identity (event_factor_hash, the per-row factor_set_id/listing_id the exported factor rows carry, and the lineage\'s factor_set_hash) is a different binding and was never asserted. (3) The "lineage" binding passed on any non-empty array -- publication_artifact_lineage is always a three-key structure even when every value is null -- so it is replaced by the verifier\'s own lineage block (actual_lineage, the block REPLAY_LINEAGE_MISMATCH is raised over) asserted with real content. Authority enumerates no lineage fields; this proof holds the repository\'s existing operational definition, invents no lineage field and decides no richer one. The old proof also exported a hand-built metric through a mocked repository. Proof now, on the real writer and the real export: positive -- the as-known export binds a valid universe identity, its cutoff and mode, and every universe row and source row carries a positive listing_id, each source row also carrying an availability timestamp (acquired_at) that is not the trade date; negative -- factor identity and formula identity move independently in both modes (a factor-set change moves only event_factor_hash, a formula/registry change moves only the formula hash), so a valid formula hash cannot hide a missing factor identity or the reverse. Also proven: the universe identity and its rows follow the universe (a second listing changes the hash and adds a row naming it), lineage carries its content and follows its sources, and requested/effective dates, the publication id and a stored availability timestamp distinct from the trade date come from the real writer. Probes, byte-restored and sha256-verified: stripping listing_id from the as-known rows failed the per-row assertion (universe row 0 names no listing) after the export-level universe-hash assertions had passed, so a valid run-level universe hash does not hide it; replacing the universe identity with a constant failed the follows-the-universe test while the per-row test stayed green; replacing the as-known factor identity with the formula hash, making the publication formula hash a constant, dropping the factor set from the publication event/factor identity, dropping the lineage factor set, and collapsing the stored availability timestamp onto the trade date each turned exactly their own guard red, the old hand-built guard staying green throughout. Classification PROOF_GUARD_GAP; production already carried every binding.',
        ],

        // ---- F-MD-B18-A002-014 remediation (E051): four predicates, one shared defect. Their bound
        // guard, B18ReplayRerunDeterminismTest, calls MarketDataEvidenceExportService::exportReplayEvidence()
        // twice on one hand-fabricated md_replay_daily_metrics row -- it never calls
        // ReplayVerificationService::verifyRunAgainstFixture() (the replay sense) or MarketDataPipelineService
        // (the rebuild sense) at all, so it proves the exporter is deterministic, nothing about whether
        // replaying or rebuilding is. Two of the four predicates concern the replay sense only, one concerns
        // the rebuild sense only, and one names both -- each rebound to a guard that actually exercises the
        // sense it requires, verified concretely (not assumed) before rebinding: for the rebuild sense, an
        // exploratory probe called MarketDataPipelineService::runDaily() twice, independently, for the same
        // unchanged date and confirmed the second run gets a genuinely new run_id, computes the identical
        // bars_batch_hash, and is held NOT_READABLE (final_reason_code RUN_LOCK_CONFLICT / RUN_NON_CURRENT_PROMOTION
        // depending on path) rather than silently becoming current -- the system already does the right
        // thing; this remediation is proof-binding only, not a production change.
        'MD-S019-R0073' => [
            'positive' => 'ReplayVerificationServiceTest::test_replaying_the_same_unchanged_publication_twice_persists_byte_identical_results_except_execution_identity',
            'negative' => 'ReplayVerificationServiceTest::test_a_genuine_input_divergence_between_two_independent_replays_is_detected',
            'basis' => 'the consequent of the MD-S019 bound-input conditional, replay sense: verifyRunAgainstFixture() is called twice, each time through a genuinely separate ReplayVerificationService instance and a genuinely separate set of mocks representing the same stable underlying run/publication/fixture -- not the same object reused, and the first call\'s return value is never fed into the second as its actual. Every field of the persisted metric is asserted identical between the two calls except replay_id, which is asserted to differ (each independent execution mints its own identity, proving this is two executions compared with each other, not one compared with itself). The negative guard diverges one real input (bars_batch_hash) between the two independent calls and asserts the persisted result moves in exactly that field, so a constant-output implementation could not satisfy the positive guard by accident.',
        ],
        'MD-S005-R0095' => [
            'positive' => 'ReplayVerificationServiceTest::test_replaying_the_same_unchanged_publication_twice_persists_byte_identical_results_except_execution_identity',
            'negative' => 'ReplayVerificationServiceTest::test_a_genuine_input_divergence_between_two_independent_replays_is_detected',
            'basis' => 'same basis as MD-S019-R0073, which restates this required proof: exact publication replay reproduces hashes -- two genuinely independent verifyRunAgainstFixture() executions against the same unchanged publication and fixture persist byte-identical bars/indicators/eligibility batch hashes and every other content field, differing only in the replay\'s own execution identity.',
        ],
        'MD-S003-R0004' => [
            'positive' => 'MarketDataPipelineIntegrationTest::test_run_daily_correction_with_unchanged_artifacts_cancels_request_and_preserves_current_publication',
            'negative' => 'MarketDataPipelineIntegrationTest::test_run_daily_correction_replaces_current_publication_and_marks_correction_published',
            'basis' => 'rebuild sense, "prove an unchanged rerun is byte-identical and does not create a fake correction" -- read literally against Audit_Hash_and_Reproducibility_Contract_LOCKED.md\'s own rerun rule ("an unchanged rebuild with identical stable inputs/versions produces identical artifact hashes and must not create a fake corrected publication"). This pre-existing, already-green, real-pipeline test (not written for this finding, found by cross-referencing MarketDataPipelineIntegrationTest against F-014\'s predicates) runs a real SQLite pipeline once, then reruns it through the explicit correction workflow with byte-identical input: bars_batch_hash/indicators_batch_hash/eligibility_batch_hash are each asserted identical to the baseline run, and the correction record is asserted CONSUMED_CURRENT with no publication_version switch and a null published_at -- an unchanged rebuild is explicitly recorded as not a real correction, never silently promoted as one. The negative guard is the immediately preceding test in the same file: a rerun with genuinely changed content (a different close price) is asserted to produce a different publication_version and a correction explicitly marked CORRECTION_PUBLISHED, proving the unchanged-rerun guard is not satisfied by an implementation that always treats any rerun as a no-op.',
        ],
        'MD-S019-R0009' => [
            'positive' => 'MarketDataPipelineIntegrationTest::test_run_daily_correction_with_unchanged_artifacts_cancels_request_and_preserves_current_publication',
            'negative' => 'MarketDataPipelineIntegrationTest::test_run_daily_correction_replaces_current_publication_and_marks_correction_published',
            'basis' => 'Invariant 1\'s own three named hashes (bars_batch_hash, indicators_batch_hash, eligibility_batch_hash) are each asserted individually identical across an unchanged rebuild by the cited positive guard, and each is shown load-bearing by the negative guard\'s genuinely different content producing a genuinely different publication version -- the rerun half of "this must hold across reruns and replay". The replay half is the same claim ReplayVerificationServiceTest::test_replaying_the_same_unchanged_publication_twice_persists_byte_identical_results_except_execution_identity independently proves for MD-S019-R0073/MD-S005-R0095, not re-cited here only because this schema holds one positive/negative pair per entry; both halves are genuinely proven, not assumed from one.',
        ],

        // ---- F-MD-B18-A002-015 (bounded first remediation unit, C2 status semantics): MD-S050-R0030
        // ("FAIL: comparison executed and diverged") and MD-S050-R0031 ("BLOCKED: required
        // fixture/runtime/input proof was unavailable") were both bound to
        // ReplayEvidenceExportServiceTest::test_export_replay_evidence_writes_replay_result_and_reason_code_summary
        // / ReplayComparisonDetectsDivergenceTest::test_missing_expected_proof_is_reported_rather_than_ignored
        // -- neither guard exercises `replay_status`/`comparison_result` at all (the first only proves
        // export pass-through of a stored row; the second calls `compareExpectedAndActual()` directly
        // by reflection and asserts only that a missing-proof path lands in the raw mismatch list, not
        // what the outer verdict becomes). The real defect this predicate pair names was executable, not
        // proof-only: `compareExpectedAndActual()` folded a fixture's own missing required
        // `expected_*` sections into its ordinary mismatch list, so `replayStatusForComparison()` mapped
        // them to `FAIL` -- indistinguishable from a comparison that genuinely executed and diverged.
        // `replayAdmissibility()` (already the authoritative "may this verdict be believed at all" gate,
        // already producing `NOT_ADMISSIBLE`/`BLOCKED` for a self-generated fixture, an unbound
        // configuration, or an unverified bound-input context) gained one further, structurally
        // identical check: `$fixture['expected_proof_missing']` non-empty is now checked first and
        // returns its own `REPLAY_EXPECTED_PROOF_INCOMPLETE` reason, naming every missing path. This
        // reuses the exact existing BLOCKED mechanism rather than inventing a new status value --
        // `Replay_Verification_Contract_LOCKED.md`'s own BLOCKED definition already reads "fixture/
        // runtime/input proof", covering the fixture-incompleteness case and the two pre-existing
        // runtime/input cases under one status. `compareExpectedAndActual()` itself is unchanged: the
        // missing-path mismatch entries (`expected_proof.<path>`, reason `REPLAY_EXPECTED_PROOF_INCOMPLETE`)
        // still land in `mismatches`/`mismatch_reason_codes`, so the exact missing path stays visible in
        // evidence even though the outer verdict is now `BLOCKED`, not `FAIL`.
        'MD-S050-R0031' => [
            'positive' => 'ReplayVerificationServiceTest::test_admission_blocks_publication_exact_when_required_fixture_proof_is_missing',
            'negative' => 'ReplayVerificationServiceTest::test_admission_reports_fail_not_blocked_when_verified_context_still_diverges',
            'basis' => 'a fixture genuinely missing one required expected-proof section (`expected_coverage_context.coverage_reason_code`, actually absent from the JSON on disk, not fabricated to look missing), with a genuinely VERIFIED bound context and every other field agreeing, reports `replay_status=BLOCKED`/`comparison_result=NOT_ADMISSIBLE`/`admission_state=NOT_ADMISSIBLE` -- never `FAIL`. The missing path still names itself in both `mismatch_summary` and the raw `mismatches` list (`expected_proof.expected_coverage_context.coverage_reason_code`, reason `REPLAY_EXPECTED_PROOF_INCOMPLETE`), so C2\'s "exact missing field/path" requirement is not lost under the BLOCKED verdict. The negative guard is the neighbouring genuinely-VERIFIED-context-with-a-real-divergence case, which reports `FAIL`/`ADMISSIBLE`, not `BLOCKED` -- proving the two are not merged. A pre-existing test (`test_verify_replay_fails_safe_when_expected_proof_is_incomplete`) that had asserted the defective `MISMATCH`/`FAIL` outcome for a near-empty fixture was corrected to assert `NOT_ADMISSIBLE`/`BLOCKED` while keeping its own missing-path assertion. Mutation-proven: disabling the new admissibility check (byte-restored after) turned this predicate\'s guard red while the FAIL counterpart stayed green; forcing `MISMATCH` to also map to `BLOCKED` in `replayStatusForComparison()` (byte-restored after) turned the FAIL counterpart red while this guard stayed green -- proving the two verdicts are independently, not coincidentally, correct.',
        ],
        'MD-S050-R0030' => [
            'positive' => 'ReplayVerificationServiceTest::test_admission_reports_fail_not_blocked_when_verified_context_still_diverges',
            'negative' => 'ReplayVerificationServiceTest::test_admission_blocks_publication_exact_when_required_fixture_proof_is_missing',
            'basis' => 'restates MD-S050-R0031\'s same discriminating pair from the FAIL side: complete required proof, a genuinely VERIFIED bound context, and one deliberate field divergence (`bars_rows_written`) reports `replay_status=FAIL`/`comparison_result=MISMATCH`/`admission_state=ADMISSIBLE`, with the diverged field named in `mismatches`. The negative guard is MD-S050-R0031\'s own missing-required-proof case, which reports `BLOCKED`, never `FAIL` -- the same mutation-proven pair proves both directions of this predicate too.',
        ],

        // ---- F-MD-B18-A002-015 (bounded second remediation unit, C2 status semantics carried into
        // the backfill consumer): MD-S050-R0053 ("BLOCKED is not a weaker PASS"), MD-S002-R0009
        // ("BLOCKED treated as missing proof, never converted to pass") and MD-S002-R0010 ("pass rates
        // ... cannot compensate for a semantic mismatch") were all three bound to
        // B18ReplayAdmissibilityBoundaryTest -- a citation-scanning corpus guard for an entirely
        // different concern (whether any active surface cites a replay PASS as evidence it cannot
        // establish), never exercising ReplayBackfillService at all. The real defect these three name
        // was executable: `expectedOutcomeForFixtureCase()` returned `null` for any `--fixture_case`
        // value outside its four-entry map, and `$passed = $expectedOutcome ? ... : true` turned that
        // into an automatic pass for every date -- after doing the full replay/export work for each
        // one, per C2's "case/fixture tidak dikenal ... tolak sebelum bekerja; tidak memilih pointer
        // atau menebak expected outcome." `ReplayBackfillService::execute()` now rejects any
        // `$fixtureCase` not in a single `KNOWN_FIXTURE_CASES` source of truth (shared with
        // `expectedOutcomeForFixtureCase()`, so the two can never diverge) before any calendar lookup,
        // directory creation, or replay execution -- proven by `shouldNotReceive` on every
        // collaborator, not merely by asserting the thrown exception. Separately, re-verified per
        // instruction that the *known*-case comparison itself needed no further change: `$observedOutcome
        // === $expectedOutcome` already fails for every one of the four named cases (`MATCH`,
        // `MISMATCH`, and the exception-path `ERROR` sentinel) when the actual outcome is
        // `NOT_ADMISSIBLE` (E-MD-B18-A002-052's BLOCKED value), since none of those four values is the
        // string `NOT_ADMISSIBLE` -- proven directly with a `reason_code_mismatch_case` fixture whose
        // replay returns `comparison_result=NOT_ADMISSIBLE`/`replay_status=BLOCKED`. No current named
        // fixture case expects `BLOCKED`/`NOT_ADMISSIBLE` (confirmed by reading the map in full), so
        // C2's "fixture negatif memang mengharapkan ERROR/BLOCKED" row has nothing to implement here;
        // inventing such a case was out of scope and not done. Sibling consumers
        // (`ReplaySmokeSuiteService`'s hardcoded internal case map; `FullRangeCurrentEvidenceReplayService`'s
        // positive allowlist requiring `MATCH`+`PASS`+zero mismatches+both admission states
        // `ADMITTED_COMPLETE`) were confirmed, not assumed, structurally immune to this defect and were
        // not touched.
        'MD-S050-R0053' => [
            'positive' => 'ReplayBackfillServiceTest::test_execute_does_not_count_a_blocked_replay_as_passed_for_a_mismatch_expecting_case',
            'negative' => 'ReplayBackfillServiceTest::test_execute_runs_verification_for_each_trading_date_and_writes_summary',
            'basis' => 'a `reason_code_mismatch_case` fixture (expects `MISMATCH`) whose replay reports `comparison_result=NOT_ADMISSIBLE`/`replay_status=BLOCKED` is asserted `passed=false` and `all_passed=false`, with `replay_status` preserved as `BLOCKED` in the case record -- `BLOCKED` is never silently promoted to a pass because the fixture expected something else. The negative guard is the pre-existing, unmodified three-date `valid_case` run where every replay genuinely `MATCH`es, asserted `all_passed=true` -- so the rule is not satisfied by a comparator that rejects everything. Mutation-proven: forcing `$passed = true` unconditionally (byte-restored after, sha256 identical) turned the positive red while the unrelated unknown-case and missing-publication tests stayed green.',
        ],
        'MD-S002-R0009' => [
            'positive' => 'ReplayBackfillServiceTest::test_execute_does_not_count_a_blocked_replay_as_passed_for_a_mismatch_expecting_case',
            'negative' => 'ReplayBackfillServiceTest::test_execute_runs_verification_for_each_trading_date_and_writes_summary',
            'basis' => 'same discriminating pair as MD-S050-R0053, which this restates as the release-candidate acceptance-criteria wording: `BLOCKED` is treated as missing proof (the `passed=false`/`all_passed=false` assertions) and never silently converted to pass.',
        ],
        'MD-S002-R0010' => [
            'positive' => 'ReplayBackfillServiceTest::test_execute_does_not_count_a_blocked_replay_as_passed_for_a_mismatch_expecting_case',
            'negative' => 'ReplayBackfillServiceTest::test_execute_rejects_an_unknown_fixture_case_before_any_work',
            'basis' => 'the backfill instance of "pass rates ... cannot compensate for a semantic mismatch": `$allPassed` in `ReplayBackfillService::execute()` is monotonic by construction -- set `true` once before the date loop and only ever written to `false` inside it, with no code path that resets it back to `true` once any date fails -- so the single-date `BLOCKED` case the positive guard proves generalizes to any range: one non-conforming date can never be compensated by others that matched, because nothing in the loop is capable of un-failing the aggregate. The negative guard proves the companion half of "cannot compensate" -- an ambiguous/unrecognised case is rejected outright rather than having some default outcome silently chosen for it (`tidak memilih pointer atau menebak expected outcome`), proven with `shouldNotReceive` on every collaborator so no work happens before the refusal.',
        ],

        // ---- MD-S050-R0032. The pre-existing evidence guard only ever exported replays that matched, so the
        // mismatch and distribution halves of the sentence had nothing to survive.
        'MD-S050-R0032' => [
            'positive' => 'B18ReplayEvidenceSelfExplanationTest::test_every_preserved_item_survives_the_export_of_a_failed_replay',
            'negative' => 'B18ReplayEvidenceSelfExplanationTest::test_a_matching_replay_exports_the_same_structure_with_no_mismatches',
            'basis' => 'new. B18ReplayEvidencePreservationContractTest exports real evidence but asserts the MD-S040 list from a different contract, and every replay it exports has comparison_result EXPECTED_DEGRADE with a null mismatch_summary -- so the two items MD-S050-R0032 names that the other list does not, mismatch paths and reason distributions, are empty in every export it produces, and asserting they survived would have asserted that null survived. This class exports a replay that actually failed. The eight items of the R0032 sentence are each mapped to an exported path and asserted present and non-empty, with the map checked against the sentence parsed from the contract so an item added with nothing behind it fails. The final clause -- without requiring a mutable database as the primary explanation -- shapes the rest: every exported mismatch is asserted to name its field, both values and a reason code, and the mismatch count is asserted to agree with the number of paths; the reason distribution is asserted to carry a positive count per code, because a list of codes without counts is not a distribution. The negative guard exports a MATCH and asserts the mismatch block is legitimately empty while every non-divergence item is still present, so a PASS export is not a thinner artifact and the rule is not satisfied by an exporter that refuses to write passing replays. Probes: emptying the exported mismatches turned two red while all three B18ReplayEvidencePreservationContractTest guards stayed green, which is the demonstration that the pre-existing guard never covered this; stripping reason_count turned the distribution guard red; dropping timestamps from the reviewed map turned the contract-map guard red.',
        ],


        // ---- MD-S019-R0009. Invariant 1 is a conjunction over three hashes; the rerun guard perturbed one.

        // ---- MD-S003-R0023, the per-run recording obligation.

        // ---- MD-S004 point-in-time input contract. Both rows were PARTIAL because one member of each list
        // had a guard and the binding was filed as though that settled the rest.

        // ---- MD-S004-R0002, the cutoff-bounded input set.

        // ---- MD-S004-R0004, what every row and export binds.

        // ---- MD-S002 release-candidate criteria. These are family-level claims, so a family-level corpus
        // guard is semantically identical to the predicate -- the opposite of the case F-MD-B19-A001-002
        // records, where the predicate was one behaviour. MD-S002-R0004 is deliberately not here: runtime,
        // locale and concurrency determinism needs more than one runtime and this environment has one.

        // ---- MD-S004-R0008, the acceptance fixture floor.

        // ---- MD-S082-R0218. Two sentences, one guard each; the registry-leak half only became provable
        // once the frozen-input comparison existed.

        // ---- MD-S082-R0216/R0217. Both carry the same lead sentence about operational config selection and
        // differ only in which replay-mode bullet follows; the lead sentence had no guard on either row.
        'MD-S082-R0216' => [
            'positive' => 'B18ConfigEffectiveTimeSelectionTest::test_the_configuration_effective_for_the_run_context_governs_and_not_the_newest',
            'negative' => 'B18ReplayComparisonExhaustivenessTest::test_a_divergence_in_any_frozen_input_denies_pass',
            'basis' => 'the row is the shared lead sentence plus the publication-replay bullet. The lead sentence had no guard on either row -- both recorded bases say production selecting the approved effective configuration is not asserted -- and it is now executed against the real MarketDataConfigSnapshotRepository on the SQLite mirror, with its three claims separated: approved is enforced at admission, since an unregistered key is refused with CONFIG_REGISTRY_KEY_MISMATCH naming the key and no snapshot row is written; effective for the run context is two approved configurations governing from 1 March and 1 April with a run for 24 March asserted to select the March one, so taking the newest approved configuration -- the obvious wrong answer, and the one the April row would give -- fails; and records when it became known is recorded_at asserted to hold the resolution moment while effective_at holds the run context, and asserted to differ, which is the only reason the as-known branch can filter on one without disturbing the other. The bullet, publication replay uses the exact snapshot frozen with the publication, is the negative guard: config_snapshot_hash is one of the eleven frozen inputs now compared field by field, so a replay running against any configuration other than the one frozen with the publication is denied. Probes: removing the effective_at bound turned the effective-time guard red; disabling the registry key check turned the approval guard red; collapsing recorded_at onto effective_at turned the knowledge-time guard red.',
        ],
        'MD-S082-R0217' => [
            'positive' => 'B18ConfigEffectiveTimeSelectionTest::test_as_known_resolution_sees_only_the_revision_recorded_by_its_cutoff',
            'negative' => 'B18ConfigEffectiveTimeSelectionTest::test_the_moment_a_configuration_became_known_is_recorded_separately_from_its_effective_time',
            'basis' => 'the same lead sentence as MD-S082-R0216, executed by the same fixture, plus the as-known bullet. The bullet is asserted on two revisions of one governing interval: a correction effective from 1 March but recorded on 15 March is invisible to a read as of 10 March and selected without a cutoff, so the only difference between the two answers is the cutoff and knowledge time is what selects rather than effective time alone -- and the cutoff is a filter rather than a wall, which the March revision still resolving proves. Then freezes a new replay snapshot is the as-known capture asserted elsewhere in this attempt to produce a snapshot hash stable across repeated captures at one cutoff, distinct at a later one, and to write no bound-input rows. The negative guard pins that recorded_at and effective_at are separate facts carrying different values, without which the cutoff filter above would have nothing to select on and the positive would pass vacuously. Probes: removing the recorded_at bound from the as-known branch turned the positive red; collapsing recorded_at onto effective_at turned the negative red.',
        ],

        // ---- MD-S065-R0003. The rerun rule is the effective-time selection proven for MD-S082-R0216; the
        // contract-change rule is a distinct identity for a changed configuration and an untouched old one.

        // ---- MD-S082-R0224/R0225, before-seal validation items 5 and 6. They were not in the same state:
        // item 5 was already enforced by a chain and needed proving, item 6 was enforced by nothing.
        'MD-S082-R0225' => [
            'positive' => 'B18BeforeSealValidationTest::test_a_configuration_recorded_after_the_run_cutoff_is_refused_before_seal',
            'negative' => 'B18BeforeSealValidationTest::test_a_configuration_recorded_exactly_at_the_cutoff_still_seals',
            'basis' => 'code change. Nothing anywhere compared the frozen configuration\'s recorded_at against the run\'s knowledge_cutoff_at, so a publication could freeze a configuration recorded after its own knowledge boundary -- an ordinary outcome for a run whose cutoff is 18:00 that resolves configuration at 18:05. As-known replay at that cutoff can never see it: resolveAsKnown either resolves an older snapshot, silently a different configuration than the one frozen, or refuses with CONFIG_SNAPSHOT_NOT_KNOWN_AT_CUTOFF, so the publication is unreproducible the moment it is sealed. EodPublicationRepository::assertReplayDeterminismBeforeSeal() was added for that one comparison and runs inside sealCandidatePublication(); the refusal names both the revision time and the cutoff it was measured against, and the candidate stays UNSEALED. The negative guard pins the boundary as inclusive -- a configuration recorded exactly at the cutoff was knowable and seals -- so the rule is not satisfied by refusing anything recorded near the cutoff. The other way the boundary goes missing, a run with no cutoff at all, is asserted at its real enforcement point: the lineage binder refuses with RUN_KNOWLEDGE_CUTOFF_MISSING. Probes: removing the call from the seal path turned the positive red; changing the comparison to >= turned the negative red.',
        ],


        // ---- MD-S003-R0025. The mirror half was proven and the MariaDB half by nothing: every DB-backed
        // guard swaps to an in-memory SQLite connection. Both substrates now run the six required families.



        // ---- MD-S050-R0056, under D-MD-B18-A002-003: MD-B18 primary, MD-B22 supporting for the relock act.
        'MD-S050-R0056' => [
            'positive' => 'B18ProductionPathReplayFixturesTest::test_the_whole_production_path_corpus_executes_and_passes_on_mariadb',
            'negative' => 'B18ProductionPathReplayFixturesTest::test_the_corpus_harness_refuses_every_way_a_fixture_can_fail_to_count',
            'basis' => 'scoped by D-MD-B18-A002-003: executed publication and as-known fixtures, including all eight anti-survivorship cases, on the MariaDB production engine, through the App\Infrastructure\Persistence\MarketData repositories, against the repository-migrated schema -- not the production deployment and not production data. The aggregate derives its nine members from MD-S050 through the reviewed map plus the publication fixture, runs every member in-process inside a rolled-back savepoint on the production-engine connection, and counts only a member that completes with at least one assertion. It asserts driver, MariaDB version, database identity and zero pending repository migrations, and it fails rather than skips when the engine is unavailable. The negative drives the same harness with bodies that throw, fail an assertion, skip, go incomplete, assert nothing or do not exist, beside a passing control and a savepoint-isolation check. Probes on 2026-09-14 against the clean XAMPP-template instance each landed once, with controls green either side and byte restore: the F-008 exception at publication-fixture entry is reported THREW; a skip in the calendar case SKIPPED; an early return in the outage case NO_ASSERTIONS; the corporate-action case removed from the map MISSING; a falsified calendar expectation FAILED; a nonexistent test database fails the aggregate while the other twelve tests skip. The previous pair stayed green under the same F-008 exception (E-MD-B18-A002-006); it remains as supporting guards, not as this basis. The relock act itself belongs to MD-B22 and is neither performed nor authorized here.',
        ],

        // ---- F-MD-B18-A002-015 G04, E-MD-B18-A002-054: MD-S040-R0071/R0077 moved from INCOMPLETE.
        // coverage_reason_code is now a persisted column on eod_runs and on md_replay_daily_metrics
        // (both the actual and expected sides), written verbatim by the producer
        // (MarketDataPipelineService) and read verbatim by every consumer
        // (MarketDataEvidenceExportService, ReplayVerificationService) -- neither ever re-derives it
        // from coverage_gate_state again. resolveCoverageReasonCodeFromState() remains in both
        // classes only for its other, out-of-scope callers (publication_reason_code /
        // pointer_switch_reason_code), not for this field.
        'MD-S040-R0071' => [
            'positive' => 'B18ReplayEvidencePreservationContractTest::test_every_preserved_item_survives_the_real_export',
            'negative' => 'B18ReplayEvidencePreservationContractTest::test_a_failing_coverage_gate_still_exports_the_threshold_it_failed_against',
            'basis' => 'schema change plus code change. coverage_reason_code carries exactly three producer values (RUN_COVERAGE_NOT_EVALUABLE / COVERAGE_THRESHOLD_MET / RUN_COVERAGE_LOW) that map one-to-one onto coverage_gate_state\'s three values, so no two producer-emitted coverage_reason_code values ever share one coverage_gate_state -- a same-state/different-reason collision is not constructible from this field\'s real vocabulary, confirmed by reading CoverageGateEvaluator::evaluateCaptured()/notEvaluableResult() in full. The defect this closes is therefore a wrong-value bug, not an ambiguity: the pre-fix export path (MarketDataEvidenceExportService::buildCoverageState()/buildExpectedCoverageState()) called resolveCoverageReasonCodeFromState(), which returns COVERAGE_BELOW_THRESHOLD for a FAIL gate state -- a string the producer never emits for this field (it emits RUN_COVERAGE_LOW) -- and ReplayVerificationService::buildActualReplayState() carried its own separate copy of the identical wrong mapping. The positive guard\'s fixture sets coverage_reason_code=COVERAGE_BELOW_MIN_RATIO on a FAIL-state metric (a value neither derivation nor any registered vocabulary would produce for FAIL) and expected_coverage_reason_code=RUN_COVERAGE_LOW, and asserts both are exported byte-exact -- proof that the exporter echoes the persisted column rather than reconstructing from state, since reconstruction could not produce either asserted value. The negative guard confirms a FAIL export still carries both coverage_min_threshold and coverage_ratio, the values a reader needs to interpret the reason against. Historical rows written before this migration have no persisted value and read back NULL, never a reconstruction: MarketDataEvidenceExportServiceTest::test_export_run_evidence_normalizes_legacy_blocked_coverage_state_and_preserves_raw_trace was re-pointed from asserting the old reconstructed RUN_COVERAGE_NOT_EVALUABLE to asserting NULL, which is what a genuinely field-absent legacy row now produces. Mutation-proven: reverting MarketDataEvidenceExportService::buildCoverageState() to call resolveCoverageReasonCodeFromState() again turned the positive guard red (asserting COVERAGE_BELOW_MIN_RATIO, observing the reconstructed COVERAGE_BELOW_THRESHOLD) and independently turned the legacy-row test red the other way (asserting NULL, observing a reconstructed RUN_COVERAGE_NOT_EVALUABLE); reverting ReplayVerificationService::buildActualReplayState() turned ReplayVerificationServiceTest::test_verify_replay_marks_mismatch_when_coverage_contract_fields_diverge red on its coverage_reason_code assertion; removing the telemetry write in MarketDataPipelineService::completeEligibility() turned MarketDataPipelineServiceTest\'s telemetry assertion red. All three mutations byte-restored via copy, sha256-verified identical, controls confirmed green again, no mutant artifact left behind.',
        ],
        'MD-S040-R0077' => [
            'positive' => 'B18ReplayEvidencePreservationContractTest::test_every_preserved_item_survives_the_real_export',
            'negative' => 'ReplayVerificationServiceTest::test_verify_replay_marks_mismatch_when_coverage_contract_fields_diverge',
            'basis' => 'the F-MD-B18-A002-015 guard gap this predicate named directly: only the PASS/export-echo path was ever asserted, so a MISMATCH case -- where the comparison diverges and final_reason_code must still survive in actual_context rather than being blanked or replaced by the fixture\'s expectation -- went unexercised. The positive guard (unchanged) proves the PASS/export path per MD-S040 as before. The rebound negative guard sets the run\'s final_reason_code explicitly (RUN_COVERAGE_LOW, distinct from the fixture\'s expected COVERAGE_THRESHOLD_MET) on a fixture engineered to MISMATCH on coverage_gate_state and coverage_ratio, and asserts actual_context.actual_run_context.final_reason_code still reads RUN_COVERAGE_LOW after the comparison -- proving survival under a genuine divergence, not merely an unchallenged echo. This predicate needed no schema or production change of its own; MD-S040-R0071\'s persistence fix incidentally strengthens its fallback chain ($run->final_reason_code ?? $run->source_final_reason_code ?? $coverageReasonCode in buildActualReplayState()) from an approximated to an exact fallback value, but that fallback is not what this guard exercises, since the test sets final_reason_code explicitly.',
        ],
        // F-MD-B18-A002-015 G07 rebind: equal row counts cannot establish equivalence; a content/hash divergence at unchanged population produces its own mismatch. Rebound to B18ReplayComparisonExhaustivenessTest::test_a_divergence_in_any_named_assertion_class_denies_pass which exercises hash-only perturbations (bars_batch_hash, indicators_batch_hash, eligibility_batch_hash) with equal row counts.
        'MD-S050-R0033' => [
            'positive' => 'B18ReplayComparisonExhaustivenessTest::test_the_unperturbed_fixture_passes',
            'negative' => 'B18ReplayComparisonExhaustivenessTest::test_a_divergence_in_any_named_assertion_class_denies_pass',
            'basis' => 'equal row counts cannot establish PASS; content/hash divergence at unchanged population produces MISMATCH with the specific hash as the mismatched field',
        ],

        // F-MD-B18-A002-015 G08: the corpus guard read prose only, so a real consumer enacting the
        // prohibition as code -- a call, not a claim -- would cross it silently. New executable guard
        // B18ReplayVerdictConsumerBoundaryTest closes that gap.
        'MD-S050-R0051' => [
            'positive' => 'B18ReplayVerdictConsumerBoundaryTest::test_no_reviewed_consumer_closes_releases_dismisses_or_satisfies_on_a_verdict',
            'negative' => 'B18ReplayVerdictConsumerBoundaryTest::test_each_forbidden_action_pattern_fires_on_its_own_sample_violation',
            'basis' => 'a static consumer guard, not a prose scan: REVIEWED_VERDICT_CONSUMERS is a positive-locator, fail-closed enumeration (test_the_reviewed_list_is_exactly_every_file_that_reads_a_verdict_field) of every file under app/ that reads a replay verdict field (replay_status, comparison_result) -- today exactly 13 files, matching F-015\'s own review: the replay, backfill, evidence and command surfaces, plus ReplayResultRepository. Each reviewed file is scanned for the rule\'s four prohibited actions (closes a finding, releases a quarantine, dismisses a corporate-action candidate, satisfies a continuity check), matched bidirectionally (verb-then-subject or subject-then-verb, since a real call site as often reads $this->quarantine->release(...) as releaseQuarantine(...)) so the OHLC close field and the publication/correction candidate vocabulary already present throughout this exact surface cannot false-positive (test_the_reviewed_surfaces_own_benign_close_and_candidate_vocabulary_is_untouched). Confirmed by reading all 13 files directly: none contains finding/quarantine/corporate-action-candidate/continuity vocabulary at all outside comments, so the rule already held and no production code was changed -- only the guard was missing. Mutation-proven three ways, each byte-restored and sha256-verified identical before/after, controls green either side: injecting `replay_status` into an unreviewed file (PriceScaleBreakDetectionService.php) turned the positive-locator drift guard red, naming the new file; injecting a real prohibited call (`$this->findingRepository->closeFinding(...)`) into a reviewed consumer (ReplayVerificationService.php) turned the action-scan red, naming the exact file and the exact prohibited action; the sample-violation test independently proves all four patterns fire on synthetic in-memory violations without needing any file mutation.',
        ],
        // F-MD-B18-A002-015 G08: forbidden() carried no pattern of its own for this predicate.
        'MD-S050-R0052' => [
            'positive' => 'B18ReplayAdmissibilityBoundaryTest::test_no_active_surface_cites_a_replay_verdict_for_something_replay_cannot_establish',
            'negative' => 'B18ReplayAdmissibilityBoundaryTest::test_every_pattern_matches_the_claim_it_forbids_and_spares_the_denial',
            'basis' => 'a dedicated pattern phrased from the rule\'s own named alternative (admissible/independent evidence), not from the paraphrase F-MD-B18-A002-015\'s own gap-table entry for this predicate already quotes verbatim ("the replay verdict establishes correctness") as a named example -- a pattern built on that wording would false-positive against that live, unexcluded finding document. The new pattern requires `replay (verdict|pass|result) ... (admissible|independent) evidence ... correctness`, confirmed by corpus grep to have zero matches anywhere in the current active corpus and zero collision with F-015\'s own quoted example. Proven by the corpus scan\'s shared machinery: test_every_pattern_matches_the_claim_it_forbids_and_spares_the_denial confirms the pattern fires on its own claim sentence and stays silent on all three denial-prefixed versions of that sentence; test_no_active_surface_cites_a_replay_verdict_for_something_replay_cannot_establish confirms zero live violations. Mutation-proven: injecting the exact claim sentence into a live scanned file (CURRENT_STATE.md, outside every excluded prefix) turned the corpus scan red, naming the injected file and the matched span verbatim; the file was restored byte-for-byte, sha256-verified identical, and the control -- including F-015\'s own quoted example, which the pattern correctly spares -- confirmed green again.',
        ],

        // F-MD-B18-A002-015 G09 (re-executed after an invalid prior closure that moved these four
        // rows INCOMPLETE -> PROVEN byte-for-byte with no new guard, no rebind, and no probe -- caught
        // before commit and discarded). The finding's own remedy for MD-S036-R0012 names the old basis's
        // defect precisely: it pointed at the ReplayMode (PUBLICATION_EXACT/AS_KNOWN) guard, a different
        // domain from the `replay_verify` *request_mode* enumerated in MarketDataStageInput::
        // ALLOWED_REQUEST_MODES and enforced by MarketDataPipelineService::assertAllowedRequestMode.
        // RequestModeVocabularyTest already exercised that real method behaviourally, but its one
        // dataProvider case for replay_verify is derived from the very array under test: removing
        // replay_verify from ALLOWED_REQUEST_MODES shrinks the dataProvider by one case instead of
        // failing it, confirmed live (mutated, ran, 16/16 green -- the guard went quiet, not red;
        // byte-restored, sha256 identical, re-confirmed 16/16). A new, independent test was added --
        // test_replay_verify_specifically_is_an_accepted_request_mode -- asserting the literal string
        // against the real assertAllowedRequestMode(), not a value read from the array it verifies.
        // Mutation-proven: the same removal now turns this new test red with REQUEST_MODE_INVALID
        // naming exactly the removed mode; byte-restored, sha256 identical, control green (17/17).
        'MD-S036-R0012' => [
            'positive' => 'RequestModeVocabularyTest::test_replay_verify_specifically_is_an_accepted_request_mode',
            'negative' => 'RequestModeVocabularyTest::test_an_unknown_request_mode_is_rejected',
            'basis' => 'replay_verify is independently asserted as an accepted request_mode against the real MarketDataPipelineService::assertAllowedRequestMode(), not derived from the vocabulary array itself, so removing it from ALLOWED_REQUEST_MODES cannot silently shrink the proof; the negative guard (a hardcoded unknown mode) independently proves the boundary is not accept-all',
        ],
        // F-MD-B18-A002-015 G09: the predicate is a read-side rule about a stored, already-unmoded
        // result ("carrying no mode... unclassified... may not be cited as either"), not about command
        // invocation. B18ReplayEvidenceSelfExplanationTest::test_an_unmoded_result_is_not_admitted_as_citable_evidence
        // forces replay_mode=null onto a real exported metric row (simulating a corpus row that
        // predates MD-S050-R0035's write-time mandate) and asserts the real
        // MarketDataEvidenceExportService::exportReplayEvidence -- not mocked -- marks the pack
        // ADMITTED_INCOMPLETE with reason EVIDENCE_ADMISSION_INCOMPLETE and names
        // replay_mode_invalid_or_historical_unclassified among missing_sections, i.e. the unmoded
        // result is refused citable status rather than defaulted into either mode. Mutation-proven:
        // removing the else-branch that appends that missing-section entry
        // (MarketDataEvidenceExportService.php) turned exactly that assertion red
        // ("Failed asserting that an array contains 'replay_mode_invalid_or_historical_unclassified'");
        // byte-restored, sha256 identical, control green (14/14). The companion test
        // test_every_exported_pack_requires_and_carries_the_mode_that_produced_it is the positive
        // control: a correctly-moded PUBLICATION_EXACT or AS_KNOWN result is admitted
        // ADMITTED_COMPLETE, so the guard is not simply refusing every export.
        'MD-S050-R0036' => [
            'positive' => 'B18ReplayEvidenceSelfExplanationTest::test_an_unmoded_result_is_not_admitted_as_citable_evidence',
            'negative' => 'B18ReplayEvidenceSelfExplanationTest::test_every_exported_pack_requires_and_carries_the_mode_that_produced_it',
            'basis' => 'a stored result with no mode is exported as ADMITTED_INCOMPLETE naming replay_mode_invalid_or_historical_unclassified rather than being cited as either mode by default; a correctly-moded result is independently proven ADMITTED_COMPLETE, so the rule is not satisfied by refusing every export',
        ],
        // F-MD-B18-A002-015 G09: the finding names the exact defect in the old basis -- "the positive
        // guard only checks that the mapped guard methods exist" -- which is precisely what
        // test_every_later_revision_kind_is_bound_to_an_executing_guard does (a string search for
        // "function <name>(" in a file). Rebound to
        // test_a_later_cutoff_exposes_later_revisions_without_rewriting_the_earlier_snapshot, the
        // behavioural guard that seeds real DB rows for all seven contract-named roots (master, event,
        // status, calendar, config, formula, factor), distinguished only by recorded_at, and asserts
        // each root's early-cutoff view excludes the later-recorded revision while the late-cutoff view
        // includes both, that re-running the early cutoff is byte-identical (no rewrite), and that no
        // row is created/mutated by either capture. One discriminating probe run per root in
        // AsKnownReplaySnapshotService::capture(), each isolated, each byte-restored and sha256-verified
        // identical before continuing to the next, each caught by a distinct assertion in the same test:
        // master (identity cutoff forced to a future date -- LATE leaked into the April universe, line
        // 154); status (same -- SUSPENSION became UNKNOWN, line 165); calendar (same --
        // calendar-late leaked into the early capture, line 160); config (same -- late
        // config_snapshot_id leaked into the early capture, line 144); formula (indicator_config read
        // from live config() instead of the frozen snapshot payload -- roc_lookback_days leaked 21
        // instead of 20, line 145, independent of the config probe which passed); event (recorded_at
        // filter removed on md_corporate_action_revisions -- two revisions visible at the early cutoff
        // instead of one, line 170); factor (recorded_at filter removed on
        // md_adjustment_factor_sets -- two factor_sets visible at the early cutoff instead of one, line
        // 171, independent of the event probe which caught a different line). The negative guard,
        // test_an_incomplete_historical_config_snapshot_is_refused_instead_of_using_live_config, uses
        // real repositories and a real corrupted DB row (not a mock) and was independently re-probed:
        // removing the refusal and substituting a live-config fallback turned it red (an
        // ErrorException on the now-absent resolved_config key, rather than a silent live-config
        // success) -- byte-restored, sha256 identical, control green.
        'MD-S003-R0021' => [
            'positive' => 'B18AsKnownSnapshotIsolationTest::test_a_later_cutoff_exposes_later_revisions_without_rewriting_the_earlier_snapshot',
            'negative' => 'B18AsKnownSnapshotIsolationTest::test_an_incomplete_historical_config_snapshot_is_refused_instead_of_using_live_config',
            'basis' => 'each of the seven contract-named roots (master, event, status, calendar, config, formula, factor) is individually mutation-proven against the real repositories and the real service: forcing any one root to ignore its cutoff leaks a later-recorded revision into the earlier-cutoff capture, caught by a distinct assertion for each root; an incomplete historical snapshot is independently proven to refuse rather than fall back to live configuration',
        ],
        // F-MD-B18-A002-015 G09: same corpus and same seven-root mutation proof as MD-S003-R0021 --
        // this predicate is the fixture-proof framing of the identical rule ("as-known replay excludes
        // later revisions") rather than a separate mechanism, so it is reviewed independently but bound
        // to the same guard pair rather than to a second, duplicate corpus.
        'MD-S005-R0096' => [
            'positive' => 'B18AsKnownSnapshotIsolationTest::test_a_later_cutoff_exposes_later_revisions_without_rewriting_the_earlier_snapshot',
            'negative' => 'B18AsKnownSnapshotIsolationTest::test_an_incomplete_historical_config_snapshot_is_refused_instead_of_using_live_config',
            'basis' => 'as-known replay\'s exclusion of later revisions is proven per-root against the real repositories and service, not by method-existence alone: seven independent mutation probes (one per contract-named root) each turn the behavioural guard red on its own distinct assertion, and an incomplete historical snapshot is independently proven to refuse rather than substitute live configuration',
        ],

        // F-MD-B18-A002-016 first bounded unit: ReplayBackfillService chose each date's publication with
        // findCurrentPublicationForTradeDate($tradeDate), then passed that pointer-derived id to
        // verifyRunAgainstFixture() as though it had been declared -- laundering a "whatever is current"
        // lookup into an apparently explicit identity. After a correction moved the pointer, the same
        // command would verify a different publication than the fixture was created against. Fixed:
        // the fixture directory itself is now the declared manifest (`{fixtureRoot}/{tradeDate}/publication_{N}`),
        // resolved via EodPublicationRepository::buildManifestByPublicationId(), a pure identity-keyed
        // lookup with no pointer/current concept; findCurrentPublicationForTradeDate is never called
        // from this class again. Zero, one, and more-than-one declared publication_<id> directories are
        // each handled explicitly (undeclared/ambiguous both fail closed before any replay work), and a
        // declared identity whose real trade_date disagrees with its directory fails closed rather than
        // silently accepting either date.
        'MD-S050-R0027' => [
            'positive' => 'ReplayBackfillServiceTest::test_pointer_moving_after_fixture_creation_does_not_retarget_the_replay',
            'negative' => 'ReplayBackfillServiceTest::test_execute_rejects_a_date_with_no_declared_publication_before_any_work',
            'basis' => 'the fixture-declared publication survives a pointer move to a different publication after fixture creation (findCurrentPublicationForTradeDate proven never called, by explicit shouldNotReceive), and a date with no declared identity is refused before any replay work rather than falling back to whatever the pointer would resolve; mutation-proven by reintroducing the removed pointer-derived-identity code, which turned 7 of 8 targeted tests red (the eighth, the unknown-fixture-case guard, fires earlier and is unaffected), byte-restored and sha256-verified, control green',
        ],
        // F-MD-B18-A002-016: the general PUBLICATION_EXACT resolution boundary this predicate names
        // ("resolve an explicit immutable publication, not latest/current") already existed as real code
        // in ReplayVerificationService::verifyRunAgainstFixture (REPLAY_EXPLICIT_PUBLICATION_REQUIRED,
        // refusing a readable expectation with no explicit id), but its only guard,
        // B18ReplayContractStaticGuardTest::test_exact_missing_publication_fails_closed, was a static
        // source-text check -- it asserted the reason-code string and the absence of one specific
        // literal call pattern, never executing the refusal or proving any pointer/current lookup was
        // genuinely bypassed. Two new behavioural tests replace that as this predicate's proof: the
        // refusal fires for a real READABLE-expected run with no explicit id anywhere (no argument, no
        // manifest, no expected_publication_context), before any pointer/current lookup is even stubbed
        // (an unexpected call to findCurrentPublicationForTradeDate, findReadableCurrentPublicationForRun,
        // or resolvePublicationForEvidenceAudit would itself fail the test); and a genuinely explicit id
        // supplied as the 4th argument is accepted and resolved through the explicit path, proving the
        // boundary is not reject-everything. No production code change was required for this predicate;
        // the implementation was already correct.
        'MD-S003-R0002' => [
            'positive' => 'ReplayVerificationServiceTest::test_publication_exact_accepts_a_readable_expectation_with_a_genuinely_explicit_publication_id',
            'negative' => 'ReplayVerificationServiceTest::test_publication_exact_refuses_a_readable_expectation_with_no_explicit_publication_id_before_any_pointer_lookup',
            'basis' => 'a real, unmocked call to verifyRunAgainstFixture with no explicit publication id anywhere throws REPLAY_EXPLICIT_PUBLICATION_REQUIRED before any pointer/current lookup is consulted (proven by leaving those lookup methods unstubbed, so an unexpected call itself fails the test), while a genuinely explicit id is independently proven to resolve successfully through the explicit path; mutation-proven by removing the refusal, which turned the negative test red on an unstubbed downstream call rather than silently substituting a pointer-resolved publication, byte-restored and sha256-verified, control green',
        ],

        // F-MD-B18-A002-016 G04: "record and compare request mode, import status, promote status,
        // source mode, pointer switch status, and publication state." request_mode/source_mode/
        // publishability_state were already proven load-bearing by the perturbation table. The real
        // production compareField() calls for import_status/promote_status/pointer_switched
        // (ReplayVerificationService::compareExpectedAndActual) already existed and were correctly
        // implemented -- each derived from the real run row (request_mode/terminal_status/
        // publishability_state/bars_rows_written/is_current_publication), not from the expected side
        // -- but no fixture in this file ever declared a non-null expected value for these three
        // fields, so compareField()'s null-expectation skip meant real code no test ever exercised.
        // Fixed with three new perturbation-table entries (no production change): baseline defaults
        // for import_status='COMPLETED'/promote_status='PROMOTED'/pointer_switched=true match the real
        // actual-side computation for the shared baseline run (so all 13 pre-existing perturbations
        // are unaffected), and three new entries diverge each field from that baseline. Mutation-proven:
        // removing the three compareField() calls turned exactly these three new perturbation cases red
        // (17 tests run, 3 failures, the other 14 including the unrelated 11 stayed green); byte-restored
        // via git checkout (file confirmed byte-identical to committed HEAD both before and after, since
        // this session's F-016 work never otherwise touched it), sha256-verified, control green (42/42).
        'MD-S036-R0007' => [
            'positive' => 'B18ReplayComparisonExhaustivenessTest::test_a_divergence_in_any_named_assertion_class_denies_pass',
            'negative' => 'B18ReplayComparisonExhaustivenessTest::test_the_unperturbed_fixture_passes',
            'basis' => 'all six named fields (request_mode, import_status, promote_status, source_mode, pointer_switched, publishability_state) are each individually proven load-bearing by the perturbation table against the real ReplayVerificationService::compareExpectedAndActual, with the unperturbed fixture as the control that a genuine match still reaches PASS',
        ],
        // F-MD-B18-A002-016 G04: three clauses. Clause 3 (unexpected import promotion is a mismatch,
        // not a silent pass) was already proven via appendImportPromotionPolicyMismatches() and its
        // guard pair. Clause 2 (replay compares 7 named fields: request_mode/source_mode/import_status/
        // promote_status/publication_state/pointer_state/reason_code) shares R0007's exact gap for
        // import_status/promote_status/pointer_switched, now closed by the same three perturbation
        // entries; final_reason_code was already proven. Clause 1 -- "Evidence export must show whether
        // a run is import-only or promoted without requiring direct DB inspection" -- was genuinely
        // unguarded: MarketDataEvidenceExportService::buildRunSummary/deriveImportStatus/
        // derivePromoteStatus were already correctly implemented as pure functions of the already-fetched
        // $run row (confirmed by reading both helpers: neither takes a repository or issues a query), but
        // zero existing test ever exercised the import_promote_boundary export block for either scenario.
        // New test exports an import-only run and a promoted run through the same method, with neither
        // collaborator mock stubbing anything import/promote-specific beyond what every export already
        // needs -- an unstubbed extra DB call would itself fail the test.
        'MD-S036-R0031' => [
            'positive' => 'MarketDataEvidenceExportServiceTest::test_export_run_evidence_distinguishes_import_only_from_promoted_without_extra_db_inspection',
            'negative' => 'B18ReplayComparisonExhaustivenessTest::test_an_import_only_expectation_against_an_unpromoted_run_raises_no_promotion_mismatch',
            'basis' => 'the export genuinely distinguishes an import-only run from a promoted run (request_mode/import_status/promote_status/promoted/import_promote_boundary.boundary_rule all differ correctly) using only the already-fetched run row, no extra collaborator stubbed; the replay-side unexpected-promotion clause and the request-mode/import-status/promote-status/pointer-switched comparison clause are proven by the same guards bound to MD-S036-R0007 and the pre-existing import-only promotion-policy pair. Mutation-proven: nulling the boundary_rule ternary and separately broadening the promoted computation to ignore publishability_state/pointer state each turned the export-distinguishing test red on its own distinct assertion; byte-restored via git checkout, sha256-verified, control green',
        ],
        // F-MD-B18-A002-016 G04: the replay half (a manual_file run whose import succeeded and which
        // claims READABLE while its coverage gate did not pass is a MISMATCH) was already proven. The
        // evidence-export half was not: MarketDataEvidenceExportService::isReadableRun() already
        // required terminal_status=SUCCESS AND publishability_state=READABLE AND coverage_gate_state=PASS,
        // but every existing test that set coverage_gate_state=FAIL also set terminal_status=HELD, so
        // readability was already false from the terminal-status clause alone and the coverage condition
        // was never isolated -- a broken coverage clause and a correct one would have passed the same
        // tests identically. New test holds terminal_status=SUCCESS and publishability_state=READABLE
        // constant (import genuinely succeeded, run superficially looks READABLE) and varies only
        // coverage_gate_state, asserting the exported final_outcome_note correctly says "not readable"
        // rather than the SUCCESS+READABLE message, paired with a positive control (coverage PASS ->
        // genuinely readable, publication_manifest.json written).
        'MD-S040-R0080' => [
            'positive' => 'MarketDataEvidenceExportServiceTest::test_export_run_evidence_does_not_treat_a_manual_file_run_as_readable_when_coverage_failed_despite_import_success',
            'negative' => 'MarketDataEvidenceExportServiceTest::test_export_run_evidence_treats_a_manual_file_run_as_readable_when_coverage_passed',
            'basis' => 'a SUCCESS + READABLE-looking run whose coverage gate failed is exported with a "not readable" final_outcome_note and no publication_manifest.json, isolating the coverage clause of isReadableRun() from the terminal-status/publishability-state clauses every prior fixture conflated it with; the positive control (coverage PASS) is independently proven genuinely readable. Complements the pre-existing replay-side proof (B18ReplayComparisonExhaustivenessTest::test_a_manual_file_run_readable_without_a_coverage_pass_is_a_mismatch), so neither the evidence nor the replay flow treats manual_file as readable merely because import succeeded. Mutation-proven: removing the coverage_gate_state=PASS clause from isReadableRun() turned exactly the positive test red (the note read as SUCCESS+READABLE instead of not-readable) while the negative control stayed green; byte-restored via git checkout, sha256-verified, control green',
        ],
        // F-MD-B18-A002-016 (G05): full-parent aggregate. The nine items MD-S050-R0017's sentence names,
        // reconstructed from Replay_Verification_Contract_LOCKED.md line 37 rather than the prior draft's
        // narrative, are each bound to the guard that executes the prohibition and independently
        // mutation-probed against the production clause it depends on, one probe per member, all nine caught.
        'MD-S050-R0017' => [
            'positive' => 'B18AntiFutureResolutionTest::test_the_anti_future_map_names_exactly_what_the_contract_names',
            'negative' => 'B18AntiFutureResolutionTest::test_every_anti_future_guard_exists_and_is_executable',
            'basis' => 'the nine items the anti-future sentence names -- today\'s is_active, current symbol, current sector, current suspension/status, latest calendar correction, later corporate-action revision, later factor, current config, latest provider mapping -- are each bound in antiFutureMap() to a guard that executes the prohibition, and the map is checked against the sentence parsed live from Replay_Verification_Contract_LOCKED.md rather than transcribed, so an item added to the contract with nothing behind it fails this test (re-verified this unit: mutating the contract to add a tenth, unmapped item made the parse produce a 10-item set against the map\'s 9 and the test failed on the array-diff). Seven members already had executing guards spread across B18AntiSurvivorshipFixtureCorpusTest, AsKnownReplayBoundaryTest and B18AsKnownSnapshotIsolationTest; current sector and latest provider mapping are proven inside this file against real, unmocked repositories. Each of the nine was independently mutation-probed against its own production clause, byte-restored and sha256-verified after every probe, with the target test confirmed red and sibling members confirmed unaffected: (1) is_active -- TemporalIdentityRepository::baseIdentityQuery\'s delisted_date/delisted_recorded_at knowledge-time OR-clause, probed by removing the recorded_at branch; (2) current symbol -- the same method\'s md_listing_symbols join effective_from/effective_to/retracted_at conditions, probed by removing them; (3) current sector -- SectorClassificationRepository::resolveSectorContextForTickerIds\'s member.recorded_at <= $knownAt clause, probed by removing it, exposing the May reclassification to an April cutoff; (4) current suspension/status -- TemporalTradingStatusRepository::resolveStatus\'s knownAt-gated branch, probed by disabling the condition; (5) latest calendar correction -- MarketCalendarRepository::terminalRevisionRowsForDate\'s recorded_at <= knownAt clause (disambiguated from a second, unrelated occurrence at a different line by its unique preceding cal_date predicate), probed by removing it; (6) later corporate-action revision -- EventRiskSourceRepository::applyKnowledgeCutoff, which the fixture\'s legacy-table path actually calls, probed by making it a no-op; (7) later factor -- AsKnownReplaySnapshotService::eventFactorContext\'s md_adjustment_factor_sets recorded_at <= $knowledgeCutoff clause, probed by hardcoding the cutoff to a future date; (8) current config -- MarketDataConfigSnapshotRepository::governingSnapshot\'s knownAt-gated recorded_at clause, probed by disabling the condition; (9) latest provider mapping -- TemporalIdentityRepository::resolveProviderContext\'s conditional pm.recorded_at/pm.retracted_at knowledge-time block, probed by replacing it with an unconditional whereNull(retracted_at). All nine implementations were already correct; no production code was changed for this predicate. No current/latest substitution exists in any of the nine paths: each clause is conditioned on the caller-supplied knownAt/knowledgeCutoff rather than defaulting to an unfiltered current read, which is exactly what each probe demonstrated by disabling that one condition and observing the later fact leak through while the other eight members stayed green.',
        ],
        // F-MD-B18-A002-016 (G08): capability boundary. The corpus pattern guard named MD-S050-R0046
        // scans the active documents for assertions that "replay proves the source observation was faithful."
        // The contract forbids this: provider error is frozen by the same reproducibility mechanism.
        // Rebind-only: the pattern already exists in B18ReplayAdmissibilityBoundaryTest. Discriminating probe:
        // injected a forbidden claim, test turned red on correct pattern match; removed claim, test green again.
        'MD-S050-R0046' => [
            'positive' => 'B18ReplayAdmissibilityBoundaryTest::test_no_active_surface_cites_a_replay_verdict_for_something_replay_cannot_establish',
            'negative' => 'B18ReplayAdmissibilityBoundaryTest::test_every_pattern_matches_the_claim_it_forbids_and_spares_the_denial',
            'basis' => 'the corpus pattern guard at MD-S050-R0046 in the forbidden() map scans the active documents and forbids the claim that replay proves source observation faithfulness. A successful replay is evidence of reproducibility only; provider error inside a frozen observation is frozen by the same mechanism that guarantees determinism. The pattern is executed by the corpus scan test and verified to fire on the claim and spare denials. The active corpus contains no such assertion. Discriminating probe: injected a document containing "A successful replay proves the source observation was faithful", the scan test turned red with correct pattern-match detection; removed the document, test green again. No production code changed.',
        ],
        // F-MD-B18-A002-016 (G09): rebind-only. The prior basis (TemporalIdentityLayerContractTest point-in-time
        // and retraction tests) never seeds a delisted listing, so it is structurally blind to survivorship.
        // Rebound to B18AntiSurvivorshipFixtureCorpusTest's dedicated delisting fixture, which seeds both a
        // delisted-today/active-at-T listing and a live control and asserts both the historical and current reads.
        'MD-S003-R0009' => [
            'positive' => 'B18AntiSurvivorshipFixtureCorpusTest::test_a_listing_active_at_T_but_delisted_today_stays_in_the_historical_universe',
            'negative' => 'B18AntiSurvivorshipFixtureCorpusTest::test_a_listing_active_at_T_but_delisted_today_stays_in_the_historical_universe',
            'basis' => 'the fixture seeds a listing delisted 2025-06-30 alongside a live control, then reads TemporalIdentityRepository::readProjectedUniverseAsOf() at a historical trade date (2024-05-02, before delisting) and at a current date (2026-03-02, after). The delisted listing must appear at the historical date and must not appear at the current date -- both directions are asserted, so a query that ignores delisted_date entirely would fail the current-date assertion, and a query that drops delisted listings unconditionally would fail the historical-date assertion. The test constructs TemporalIdentityRepository directly with no ProducerInputScope active, so it exercises baseIdentityQuery() (the real DB-backed clause), not the mocked/active-producer branch. Mutation-proven this unit: removed the delisted_date > $tradeDate OR-branch from baseIdentityQuery(), leaving only whereNull(delisted_date); target test turned red (the historical-date assertion failed, "GONE" missing from the as-of-T universe) while the other 9 tests in the file stayed green; byte-restored via git-equivalent literal replacement and sha256-verified (cdd98b5...84 unchanged), control re-run green (10/10).',
        ],
        // F-MD-B18-A002-016 (G09): rebind-only. Same prior-basis defect as R0009 (one symbol/one mapping per
        // listing, never seeding a change or reuse). Rebound to the two dedicated symbol-identity fixtures that
        // seed an actual symbol change and an actual symbol reuse across two listings.
        'MD-S003-R0010' => [
            'positive' => 'B18AntiSurvivorshipFixtureCorpusTest::test_a_symbol_change_resolves_to_the_symbol_and_mapping_effective_on_the_trade_date',
            'negative' => 'B18AntiSurvivorshipFixtureCorpusTest::test_reused_symbol_text_resolves_to_the_listing_that_held_it_on_the_trade_date',
            'basis' => 'the first fixture seeds one listing with a symbol rename (OLDSYM->NEWSYM) and a parallel provider-mapping rename, and asserts the old symbol resolves only pre-change, the new symbol resolves only post-change, and the new symbol on a pre-change date refuses outright (fail-closed, not a soft miss) -- proving identity survives a rename through TemporalIdentityRepository::resolveProviderContext(). The second fixture seeds two distinct listings sharing the literal text "REUSED" in disjoint effective windows and asserts each date resolves to the listing that actually held the symbol then, through both resolveProviderContext() (provider-mapping path) and readProjectedUniverseAsOf() (symbol-interval path directly), so a defect isolated to either table is caught. Both tests run TemporalIdentityRepository unmocked against a real DB. Mutation-proven this unit: removed the pm.effective_to upper-bound clause from resolveProviderContext()\'s provider-mapping join (the "mapping end" the F-016 remedy table names as P11-R0010-MAP); both target tests turned red -- the symbol-change test errored on PROVIDER_SYMBOL_MAPPING_AMBIGUOUS (the old and new mapping rows both matched the post-change date once the upper bound was gone) and the reuse test likewise errored, while 8 of the file\'s other 10 tests (including the unrelated R0009 delisting fixture) stayed green; byte-restored and sha256-verified (cdd98b5...84 unchanged), control re-run green (10/10).',
        ],
        // F-MD-B18-A002-016 (G09): PROOF_GUARD_GAP, not rebind-only. The prior candidate
        // (SourceFailureResilienceTest::test_a_provider_failure_never_shrinks_the_denominator) sets
        // expected_universe_count directly in its own fixture and asserts FinalizeDecisionService echoes it back;
        // FinalizeDecisionService never computes that count (verified: lines 16/278 are a straight pass-through
        // of the input array key), so the fixture proves a pass-through is a pass-through, not that the real
        // universe computation survives a provider outage. Audited the actual denominator construction in
        // CoverageGateEvaluator::evaluateCaptured(): expected_universe_count = count($universeByTickerId) is
        // computed from TickerMasterRepository::getUniverseForTradeDate() (the ticker-master/temporal-identity
        // universe) filtered only by verified full-session suspension, strictly before delivered/available ticker
        // ids are even loaded -- architecturally independent of provider delivery, matching
        // Coverage_Universe_Definition_LOCKED.md's denominator = EXPECTED + UNKNOWN rule and its explicit
        // prohibition on excluding dormant/quiet tickers. No production defect found; the implementation was
        // already correct. Added the missing evaluator-level guard this unit (no prior test drove
        // CoverageGateEvaluator::evaluate() through a real, unmocked-boundary total-outage scenario):
        // CoverageGateEvaluatorTest::test_evaluator_keeps_the_full_universe_as_the_denominator_when_the_provider_delivers_nothing
        // seeds a 900-ticker universe, mocks zero delivered ticker ids (total outage), and asserts
        // expected_universe_count stays 900, available_eod_count is 0, and missing_eod_count is 900 (every
        // listing counted missing, none silently excluded).
        'MD-S003-R0005' => [
            'positive' => 'CoverageGateEvaluatorTest::test_evaluator_keeps_the_full_universe_as_the_denominator_when_the_provider_delivers_nothing',
            'negative' => 'CoverageGateEvaluatorTest::test_evaluator_returns_fail_when_available_is_below_threshold',
            'basis' => 'expected_universe_count is computed inside CoverageGateEvaluator::evaluateCaptured() from the ticker-master universe (count($universeByTickerId)), before delivered/available ticker ids are loaded, so it is structurally independent of provider delivery; the only clause that can shrink it is the verified-suspension filter, a canonical universe semantic distinct from provider failure. The positive guard drives the real evaluator (only the repository boundary is mocked) through a total-outage fixture (900-ticker universe, zero delivered ids) and asserts the denominator stays 900, available drops to 0, and missing rises to 900 -- every listing is counted missing rather than excluded. The negative guard is the pre-existing partial-shortfall case (854/900 delivered), confirming the same denominator-preservation property under a milder failure. Mutation-proven this unit: changed the returned expected_universe_count from $expectedUniverseCount (the real universe count) to $deliveredObservationCount (what was actually delivered) in the evaluator\'s return array; both the new total-outage guard and the pre-existing partial-shortfall guard turned red (0 and 854 respectively, instead of 900), while the other 6 of 8 tests in the file (pass-matching, disabled-gate, zero-universe, suspended-exclusion, threshold-output cases where delivered already equals universe or the mutated line is unreached) stayed green; byte-restored and sha256-verified (4a58b78...260 unchanged), control re-run green (8/8). No production code changed -- the defect was in test coverage, not behavior.',
        ],
        // F-MD-B18-A002-016, F-013 carry-forward (package-labelled G01), Determinism_Invariants_LOCKED.md:122
        // (Invariant 14). Three clauses, each independently verified against current authority and current
        // implementation rather than inherited from F-013's RESOLVED status or the prior draft basis narrative.
        'MD-S019-R0074' => [
            'positive' => 'B18ReplayComparisonExhaustivenessTest::test_a_divergence_in_any_frozen_input_denies_pass',
            'negative' => 'SourceObservationAsKnownBoundaryTest::test_an_observation_acquired_after_the_cutoff_is_invisible_to_as_known_replay',
            'basis' => 'clause 1 ("publication replay freezes the exact identities above" -- the seven categories Determinism_Invariants_LOCKED.md:111-118 names: immutable source-observation manifest; temporal issuer/instrument/listing/symbol and provider-mapping revisions; calendar/session/status revisions; corporate-action event/factor-set revisions; full configuration snapshot/hash; price-product and formula/registry versions; serialization rules) is the same predicate MD-S050-R0002 proves, at the strategy layer rather than the implementation layer: ReplayVerificationService::BOUND_INPUT_FIELDS names all eleven fields these seven categories decompose into, and compareExpectedAndActual() compares each individually. F-016\'s own carry-forward note (recorded before F-013\'s E044/E046-E050 remediation) is now stale: it described this same test as perturbing run-row identities production never persisted, which was true only until MD-S050-R0002 was promoted to PROVEN in E-MD-B18-A002-050 on the strength of this exact guard proving all eleven fields individually load-bearing. Re-probed independently this unit rather than trusted from R0002\'s promotion alone: removed temporal_identity_hash from the compared-fields loop in compareExpectedAndActual(); exactly data set #2 (temporal_identity_hash) of the eleven-case perturbation suite turned red, the other ten stayed green; byte-restored and sha256-verified (a40a79a5...532 unchanged), control re-run green (11/11, 66 assertions). Clause 2 ("as-known replay resolves only revisions known by the declared knowledge cutoff") requires per-root knowledge-time proof for the same seven categories, not one root standing in for all seven -- the prior carry-forward note correctly flagged that only the status root (TemporalTradingStatusRepository, via B18AsKnownTemporalSequenceTest) had been shown, which is exactly the "aggregate proven by one member" defect F-MD-B18-A002-015 and G05 (MD-S050-R0017) both already found in this same proof family. Reconstructed per root against current implementation: temporal identity (listing/symbol/provider-mapping) and current sector are proven via B18AntiFutureResolutionTest/B18AntiSurvivorshipFixtureCorpusTest; calendar and trading-status via MarketCalendarRepository/TemporalTradingStatusRepository knowledge-time clauses (same tests); corporate-action and factor-set via EventRiskSourceRepository/AsKnownReplaySnapshotService knowledge-time clauses (same tests); configuration snapshot via MarketDataConfigSnapshotRepository\'s knownAt-gated clause (same tests) -- all nine of these were independently mutation-probed in this attempt\'s G05 unit (E-MD-B18-A002-060), one probe per member, and are not re-probed here. Formula/registry versions and serialization rules are read from AsKnownReplaySnapshotService::capture()\'s $resolvedConfig exclusively (formulaIdentity/reasonIdentity/read_model_version/serialization_version at lines 71-108 all derive from resolvedConfigPayload(), never from live config() -- confirmed by direct reading, and the method\'s own comment records this was a deliberate fix against "the exact future-state leak that resolveForRun(...,$knowledgeCutoff) prevents"), so their knowledge-time correctness is a structural consequence of the config-snapshot clause already probed in G05, not an independent code path requiring its own probe. executable_build_identity is the one field read from live config() in AS_KNOWN mode; this is the same accepted architectural boundary F-013/E-050 already established for MD-S050-R0002 (PUBLICATION_EXACT mode reads it live too, and R0002 was still promoted PROVEN) -- it names which build executed the replay, not a historical market-data fact, so it carries no knowledge-cutoff semantic to leak. Source-observation manifest was the one root with zero discriminating coverage: both existing SourceObservationAsKnownBoundaryTest cases seed only one acquired_at, so neither discriminates observationManifestAsKnown()/normalizedRowsAsKnown()\'s obs.acquired_at <= knownAt clause from an unconditional read. New test added this unit (test_an_observation_acquired_after_the_cutoff_is_invisible_to_as_known_replay): seeds one observation acquired before a cutoff and one acquired after, for the same trade date, and asserts observationManifestAsKnown() and normalizedRowsAsKnown() both return only the early one at the earlier cutoff and both once the cutoff passes the late one too. Mutation-proven: removed the acquired_at <= knownAt clause from observationManifestAsKnown(); the new test turned red (2 observations visible instead of 1) while the other 3 tests in the file stayed green; byte-restored and sha256-verified (f3ee17da...577 unchanged), control re-run green (4/4, 14 assertions). Clause 3 ("current state must not leak into either mode") is not a separate mechanism to probe -- it is the conjunction of clauses 1 and 2 already being individually true: publication replay cannot leak current state because every one of the eleven frozen fields is compared against the fixture-declared value (clause 1), and as-known replay cannot leak current state because every one of the seven identity categories is resolved through a knowledge-time-gated read rather than an unfiltered current read (clause 2, this predicate\'s own probes plus G05\'s nine). No production code changed for any of the three clauses -- every implementation was already correct; one new test closed the one genuinely uncovered root.',
        ],
        // F-MD-B18-A002-017 (G01-A): rebind-only, verified independently rather than assumed from the
        // shared F-013 remediation. Platform_Config_Registry_LOCKED.md:290, two clauses.
        'MD-S082-R0218' => [
            'positive' => 'ReplayVerificationServiceTest::test_registry_content_fields_come_from_decoded_capture_only_when_verified',
            'negative' => 'B18AsKnownModeIsolationTest::test_an_as_known_result_claims_no_publication_and_records_its_own_mode',
            'basis' => 'clause 1 ("current registry state must never leak into historical replay") is not a comparison-completeness claim -- it is a claim about the SOURCE of the "actual" value ReplayVerificationService::actualBoundInputContext() computes, before any comparison runs. The previously recorded guard, B18ReplayComparisonExhaustivenessTest::test_a_divergence_in_any_frozen_input_denies_pass, only overwrites the fixture\'s expected value and asserts the comparison then denies PASS; it never varies whether the underlying "actual" read is live or frozen, so a hypothetical live-config leak would pass this test identically to a correct implementation (both would still mismatch the perturbed expected value). Verified by direct reading: read_model_version/serialization_version/executable_build_identity in actualBoundInputContext() (lines 1111-1119) are each `$verified && isset(decoded registry_content[...]) ? decoded value : \'\'` -- an honest empty string when unverified or undecoded, never a config()/environment read. Rebound to the test that actually exercises this source-level claim: it asserts all three fields equal real decoded values when registry_content is present and equal an empty string (not any fallback) when absent, on a VERIFIED bound context. Mutation-proven this unit: changed the `: \'\'` branch for executable_build_identity to a literal non-empty placeholder (an equivalent narrow live/current-fallback violation, since the actual config() helper is unavailable in this unit-test bootstrap); the target test turned red on exactly that field (expected \'\' vs actual \'PROBE-LIVE-FALLBACK-build-id\'), 21 of 22 sibling tests in the file stayed green (including the R0224 guard below), and B18ReplayComparisonExhaustivenessTest\'s 42 tests stayed fully green, confirming that suite genuinely cannot see this class of defect; byte-restored and sha256-verified (a40a79a5...532 unchanged), control re-run green (22/22, 89 assertions). Clause 2 ("alternate-scenario runs are explicitly labeled and cannot impersonate the historical publication") was already correctly bound and is unchanged: B18AsKnownModeIsolationTest::test_an_as_known_result_claims_no_publication_and_records_its_own_mode asserts an AS_KNOWN result records replay_mode=AS_KNOWN, its own knowledge_cutoff_at, publication_id=null, and source=as_known_replay -- a genuinely distinct, independently reviewed guard for this predicate\'s second clause. No production code changed.',
        ],
        // F-MD-B18-A002-017 (G01-A): rebind-only, verified independently rather than promoted merely
        // because it shares F-013 remediation with MD-S082-R0218 above. Platform_Config_Registry_LOCKED.md:301
        // item 5 ("before seal, validation proves ... current environment drift cannot change publication replay").
        'MD-S082-R0224' => [
            'positive' => 'ReplayVerificationServiceTest::test_formula_and_reason_registry_hash_come_from_the_registry_versions_component',
            'negative' => 'ReplayVerificationServiceTest::test_registry_content_fields_come_from_decoded_capture_only_when_verified',
            'basis' => 'the previously recorded guard (B18BeforeSealValidationTest\'s lineage-binding-mandatory-at-seal pair) proves a real but different property: that config_snapshot_id binding is mandatory before a publication may seal. verifyBoundContext() -- shared by seal-time verifyBeforeSeal and replay-time readBoundContext -- was read in full: it requires component_manifest.status===COMPLETE and re-verifies each listed component\'s hash, but does not require a registry_versions component to be present at all; its absence leaves $registryVersionsContent null without refusing seal. So the seal-time lineage-binding test cannot establish that formula/read-model/serialization/build identity -- the actual fields F-MD-B18-A002-021 found reading live environment (Replay_Verification_Contract_LOCKED.md-adjacent defect, fixed by E-044/E-047/E-048) -- are frozen; it establishes something adjacent (config identity) that was never the defective field. The genuine mechanism preventing "current environment drift" for these fields is at replay time, in actualBoundInputContext(): formula_registry_hash/reason_registry_hash read $registryPayloadHash, which defaults to an empty string and is set only from a genuine registry_versions component\'s own payload_hash (lines 1066-1072) -- never a live or stale fallback. Rebound to the test that proves this directly: baseline/changed/absent cases show the hash equals the real payload_hash, changes when it changes, and is an honest empty string with no registry_versions component at all. Mutation-proven this unit: changed the $registryPayloadHash initial value from \'\' to a literal non-empty placeholder (the narrow stale/fallback-default violation this predicate forbids); the target test turned red on exactly the "absent" case (expected \'\' vs actual \'PROBE-STALE-FALLBACK-registry-hash\'), 21 of 22 sibling tests stayed green (including the R0218 guard above, confirming independence), and B18ReplayComparisonExhaustivenessTest\'s 42 tests stayed fully green, confirming that suite cannot see this defect either; byte-restored and sha256-verified (a40a79a5...532 unchanged), control re-run green (22/22, 89 assertions). The negative guard (test_registry_content_fields_come_from_decoded_capture_only_when_verified) independently covers the other three environment-drift-sensitive fields (read_model_version, serialization_version, executable_build_identity) the same way, so between the two tests all five fields this predicate\'s "environment drift" concerns are proven non-leaking. No production code changed.',
        ],

    ];

    // Withdrawn from PROVEN on 2026-09-14: these four rows are CONDITIONAL_NOT_APPLICABLE under
    // D-MD-B18-A002-002 with false-condition evidence E-MD-B18-A002-008, so they are outside the 115-row
    // denominator and no proof is claimed for them. Kept verbatim for audit; never read as a basis.
    public const WITHDRAWN_NOT_APPLICABLE = [
        'MD-S050-R0038' => [
            'positive' => 'B18ReplayAdmissibilityBoundaryTest::test_no_active_surface_cites_a_replay_verdict_for_something_replay_cannot_establish',
            'negative' => 'B18ReplayAdmissibilityBoundaryTest::test_every_pattern_matches_the_claim_it_forbids_and_spares_the_denial',
            'basis' => 'citation boundary for publication-replay results',
        ],
        'MD-S050-R0039' => [
            'positive' => 'B18ReplayAdmissibilityBoundaryTest::test_no_active_surface_cites_a_replay_verdict_for_something_replay_cannot_establish',
            'negative' => 'B18ReplayAdmissibilityBoundaryTest::test_every_pattern_matches_the_claim_it_forbids_and_spares_the_denial',
            'basis' => 'citation boundary: volume of PASS does not substitute',
        ],
        // ---- MD-S050-R0041. The citation rule is enforceable at the evidence pack, where a claim citing
        // replay evidence is materialised. MD-S050-R0040 was held back here while F-MD-B18-A002-002 stood;
        // that finding is resolved and R0040 is bound at the end of this map.
        'MD-S050-R0041' => [
            'positive' => 'B18ReplayEvidenceSelfExplanationTest::test_a_publication_pack_is_not_held_to_the_as_known_requirements',
            'negative' => 'B18ReplayEvidenceSelfExplanationTest::test_an_unmoded_result_is_not_admitted_as_citable_evidence',
            'basis' => 'the exported evidence pack is where a claim citing replay evidence is actually materialised, so it is where the citation rule is enforceable, and both halves are asserted against a real export. A publication pack and an as-known pack are asserted to require disjoint section sets: the as-known pack must account for the knowledge cutoff and the nine revision identities resolved under it, the publication pack for publication and pointer context and neither is required to account for the other\'s -- so a publication pack has never been examined for the property a point-in-time claim rests on, and citing it for one cites evidence that was not assessed against the question. The negative guard is the unmoded half: MD-S050-R0035 made the mode mandatory on write, and this is the read side for a corpus predating that constraint -- an unmoded result exports as ADMITTED_INCOMPLETE with EVIDENCE_ADMISSION_INCOMPLETE and a missing section naming the unclassified state, rather than quietly defaulting the pack to publication replay. A third guard asserts every admitted pack both requires and carries its mode. Probes: removing the unclassified marker turned the negative red; adding knowledge_cutoff_at to the publication pack\'s required sections turned the positive red with the interchangeability message.',
        ],
        // ---- MD-S050-R0040. The classification the row opens with, made checkable: the eight contract cases
        // are each bound to a cutoff-decided fixture and to the resolver it drives. F-MD-B18-A002-002 named the
        // two that could not be as-known fixtures; both were closed here rather than carried as a capability gap.
        'MD-S050-R0040' => [
            'positive' => 'B18AntiSurvivorshipFixtureCorpusTest::test_every_required_fixture_is_also_an_as_known_fixture',
            'negative' => 'B18CorrectionReadPathScenarioTest::test_two_sealed_publications_with_no_supersession_link_are_refused',
            'basis' => 'schema change plus code change. The row opens with a classification -- the eight anti-survivorship fixtures required below are as-known fixtures -- and six of the eight were; F-MD-B18-A002-002 recorded the other two as unable to be, because the facts they turn on carried no knowledge time at all. Both were closed rather than left as a capability gap. Fixture 1, a listing active at historical T but inactive today: md_listings.delisted_date was a mutable column with no recorded_at of its own, so a cutoff could not tell delisted from delisted-but-not-yet-known, which is survivorship bias reintroduced by the query. Migration AddListingDelistingKnowledgeTime adds a nullable delisted_recorded_at with an index and backfills existing delisted rows from their recorded_at, and TemporalIdentityRepository::baseIdentityQuery now applies the delisting only when it was recorded at or before the cutoff. One column rather than a revision series was deliberate: listing_id is the primary key and listing_uid is unique, so a superseding listing revision would fracture listing identity, which is the thing the anti-survivorship corpus exists to keep intact. Fixture 7, an original and corrected immutable publication: every publication resolver took an explicit id or the current pointer, so which publication would I have been reading at T was not expressible and a correction sealed later silently became the answer for a moment that predated it. EodEvidenceRepository::resolvePublicationAsKnownAt() was added and resolves by the declared supersession chain -- the candidates are the publications sealed at or before the cutoff, the answer is the one nothing sealed by then supersedes. It is not ordered by recency: ReadPathShortcutProhibitionTest bans ORDER BY publication_id DESC from the consumer read repositories because the newest row wins is a guess dressed as an answer, and a first version of this method that carried that ordering as a tiebreaker was rejected by that guard and rewritten rather than exempted. Two sealed publications that do not name each other are refused with EVIDENCE_AS_KNOWN_PUBLICATION_AMBIGUOUS, which is the negative guard: a replay reading the wrong half of a correction pair is worse than a replay that stops. The classification itself is now checkable rather than asserted. A reviewed map binds each of the eight contract cases, parsed from MD-S050 rather than transcribed, to the guard that decides it by a cutoff and to the cutoff-bounded runtime resolver that guard drives, and asserts the guard exists, the resolver exists, and the guard calls it with the argument the cutoff occupies. The arity is load-bearing because every one of these resolvers answers both questions -- readProjectedUniverseAsOf(tradeDate) is the effective-time read and readProjectedUniverseAsOf(tradeDate, knownAt) the as-known one -- so naming the method would have let the map be repointed at the effective-time fixture sitting beside it and stay green. Probes: repointing case 1 at the effective-time fixture turns the classification guard red naming the missing cutoff argument; removing the knowledge-time clause from the delisting filter turns both new listing fixtures red; on the publication resolver, dropping the sealed_at bound makes the correction visible to the earlier cutoff and makes a pre-seal cutoff resolve a row, dropping the seal-state filter admits an unsealed candidate, dropping the mandatory-cutoff half of the guard removes the EVIDENCE_SELECTOR_MISSING refusal, and replacing the ambiguity refusal with a fallback turns the negative red. Every probe was reverted by byte copy and the files verified identical by md5. Residue: F-MD-B18-A002-003 stands unchanged -- the SQLite mirror creates its schema with foreign key constraints disabled and mirrors nullability loosely, so the mirror is weaker than production for the pointer and publication chain these fixtures read.',
        ],
    ];

    // F-MD-B18-A002-008 remediation moved R0056 to PROVEN. F-MD-B18-A002-013 then moved eight bases here:
    // each guard proves the exporter passes an identity through, not that the identity binds what
    // the contract names. They return only with the remediation and composition guards. R0056 moved to
    // PROVEN only after the aggregate executed on the clean instance and every D003 probe was caught.
    public const INCOMPLETE = [
        // F-MD-B18-A002-013: reason_registry_hash hashes constant state names in both modes; price-product, coverage and eligibility versions are unbound in publication mode.
        // E-MD-B18-A002-045/F-MD-B18-A002-021 remediation review: E-044 (Admission) now sources reason_registry_hash/formula_registry_hash from the registry_versions capture's real payload_hash (real eod_reason_codes content, indicator_set_version, coverage_contract_version, and price_product_version via semantic_versions) instead of a hardcoded constant -- genuine progress, though both fields intentionally share one combined value rather than independently splitting formula from reason. This predicate's remaining gap is independent of MD-S050-R0008/R0009/R0012 (now PROVEN): "eligibility version" is captured nowhere in the system at all (a capture-completeness gap, not an Admission wiring gap), and read_model_version/serialization_version/executable_build_identity still read live config, unchanged. Still INCOMPLETE.
        // D-MD-B18-A002-006/DOC-CHG-20260922-001 closing review (E049): the two implementation gaps above are
        // now both closed (eligibility_contract_version registered and captured; read_model/serialization/build
        // decoded from real content since E-047/E-048), but this predicate's own bound guard was found still
        // unchanged: B18ReplayBoundInputIdentityContractTest mocks a fabricated md_replay_daily_metrics row and
        // proves only MarketDataEvidenceExportService's pass-through, never ReplayVerificationService::
        // actualBoundInputContext(), the real writer -- kept INCOMPLETE at E049, blocker corrected to name the
        // guard rather than eligibility.
        // Rebind (this review): B18ReplayBoundInputIdentityContractTest's pass-through fact does not disappear
        // -- MarketDataEvidenceExportService is still proven, separately and unchangedly, to pass a persisted
        // metric row's columns through to the exported JSON verbatim (the same background fact R0008/R0009/R0012
        // already relied on when they were rebound in E046) -- but "record-derived" now needs a guard that
        // inspects the real writer, which this predicate's guard never did. All nine named members fold into
        // exactly two ReplayVerificationService::actualBoundInputContext() fields: formula_registry_hash/
        // reason_registry_hash (one combined hash over the whole registry_versions capture, whose captured
        // payload includes indicator_set_version, coverage_contract_version, price_product_version via
        // semantic_versions, eligibility_contract_version, and the reason-registry content itself -- the same
        // combined-value composition already accepted for MD-S050-R0002/MD-S003-R0003's promotion this round,
        // not a new interpretation invented for this predicate) and read_model_version/serialization_version/
        // executable_build_identity (decoded registry_content, real when VERIFIED, honestly empty otherwise).
        // Rebound to B18ReplayComparisonExhaustivenessTest's real-path perturbation (proves every one of these
        // five fields is individually load-bearing against the real writer) and
        // ReplayVerificationServiceTest::test_formula_and_reason_registry_hash_come_from_the_registry_versions_component
        // (proves formula_registry_hash/reason_registry_hash are genuinely the registry_versions component's own
        // payload_hash, not a constant or coincidence: presence yields the real hash, a change moves both fields,
        // absence is an honest empty value) -- with
        // ReplayVerificationServiceTest::test_registry_content_fields_come_from_decoded_capture_only_when_verified
        // as the companion proof for the other three members (real decoded value only when VERIFIED, honest
        // empty otherwise, never a live-config fallback). No guard was weakened and no new test was added: both
        // negative guards already existed from this round's and E047/E048's own remediation, and the comparison
        // guard already existed from E046. Promoted to PROVEN.
        // F-MD-B18-A002-017: the recorded pair parses the list and checks method names; a survivorship defect the member caught left it green.
        'MD-S004-R0003' => [
            'positive' => 'B18PointInTimeInputContractTest::test_the_no_backfill_map_names_exactly_what_the_contract_names',
            'negative' => 'B18PointInTimeInputContractTest::test_every_guard_both_maps_name_exists_and_is_executable',
            'basis' => 'the five things today may not backfill into an earlier decision are each bound to a guard that executes the prohibition against a real surface: universe and symbol to the anti-survivorship corpus, sector to the knowledge-time reclassification guard added for MD-S050-R0017, action verification to the as-known corporate-action boundary, and current publication to the blocked replay that is asserted to resolve only the fixture-named publication and never a current-pointer selector. The map is checked against the sentence parsed from Point_In_Time_Backtest_Input_Contract_LOCKED.md rather than transcribed, so a member added there fails. The recorded basis for this row was that no-backfill was asserted for identity only; the other four now execute. A further guard asserts the eight members across this row and MD-S004-R0005 resolve to at least five distinct guards, so a family cannot be bound as though it were a predicate. Probes: dropping sector from the map turned the contract-map guard red; renaming a named guard turned the existence guard red.',
        ],
        // F-MD-B18-A002-017: the recorded pair parses the list and counts distinct guards; a survivorship defect the member caught left it green.
        'MD-S004-R0005' => [
            'positive' => 'B18PointInTimeInputContractTest::test_the_survivorship_map_names_exactly_what_the_contract_names',
            'negative' => 'B18PointInTimeInputContractTest::test_the_members_are_not_all_bound_to_a_single_guard',
            'basis' => 'three claims, parsed as three sentences rather than a comma list, each bound to an executing guard: inactive/delisted securities remaining present to the delisted-listing universe fixture, symbol changes and reuse resolving through listing IDs to the reused-symbol-text fixture, and late corrections producing a distinct later-known dataset without rewriting the earlier one to the as-known snapshot guard, which asserts the earlier cutoff still produces a byte-identical snapshot hash after a later cutoff has exposed new revisions. The negative guard is the anti-collapse check: eight members across this row and MD-S004-R0003 must resolve to at least five distinct guards, which is the shape F-MD-B19-A001-002 records and the reason this row was PARTIAL. Probe: renaming a named guard turned the existence guard red.',
        ],
        // F-MD-B18-A002-017: structural pair; a calendar knowledge-time defect the member caught left it green.
        'MD-S004-R0002' => [
            'positive' => 'B18PointInTimeInputContractTest::test_the_cutoff_bounded_input_map_names_exactly_what_the_contract_names',
            'negative' => 'B18PointInTimeInputContractTest::test_every_cutoff_bounded_input_guard_exists',
            'basis' => 'the eight input kinds a decision may contain -- observations, identity, calendar, status, event, factor, config, formula -- are each bound to a guard that executes the cutoff bound against a real repository, and the map is checked against the knowledge-time sentence parsed from Point_In_Time_Backtest_Input_Contract_LOCKED.md so a kind added there fails. The recorded basis for this row was that cutoff-bounded inputs were asserted for identity only; calendar and status are now the knowledge-time sequence guards written for MD-S041-R0032 and MD-S058-R0069, which prove the cutoff is a filter rather than a wall in both directions, and factor and formula are the as-known snapshot guard, which was rewritten to use real repositories under F-MD-B18-A002-001 after its mocks were found to be making the cutoff decision themselves. A cutoff honoured by seven of eight kinds leaks, which is why the existence guard is the negative rather than a convenience. Probe: dropping status from the map turned the contract-map guard red.',
        ],
        // F-MD-B18-A002-017: the corpus test checks method names only, and the denominator clause rests on an outage manifest test that never touches the denominator.
        'MD-S002-R0006' => [
            'positive' => 'B18ReleaseCandidateCriteriaTest::test_the_degraded_and_negative_corpus_is_complete',
            'negative' => 'EmptyDatasetFailSafeTest::test_no_fallback_fails_the_run_outright',
            'basis' => 'the degraded corpus is enumerated and asserted executable: the four MD-S003 observation defects with the outcome each proves, the held-versus-failed pair that produces the expected states through the real FinalizeDecisionService, and the provider outage that must remain in the as-known observation manifest -- which is the denominator-shrinkage half, since an outage that vanished would shrink the expected count rather than being reported. The recorded basis was that one degraded case was proven to stop with an error; the corpus now covers held, failed and unavailable outcomes and both permitted defect outcomes. The negative guard fails a run outright when no fallback exists, so producing expected states is not satisfied by an implementation that always holds. Probe: renaming a member guard turned the corpus test red.',
        ],
        // F-MD-B18-A002-017: the named corpus has no config or factor mismatch class; disabling config comparison left all 16 corpus tests green.
        'MD-S002-R0003' => [
            'positive' => 'B18ReleaseCandidateCriteriaTest::test_the_exact_publication_mismatch_corpus_is_complete',
            'negative' => 'B18ReplayComparisonExhaustivenessTest::test_the_unperturbed_fixture_passes',
            'basis' => 'the eight mismatch classes the criterion names -- value, null-reason, lineage, config, factor, hash, seal, publication -- are each perturbed individually against a real publication fixture and asserted to deny PASS, by the exhaustiveness table built for MD-S050-R0029 and MD-S050-R0002, and a companion test asserts the table covers every class the contract names. This criterion is a family-level claim, so a family-level corpus guard is semantically identical to it, which is the distinction F-MD-B19-A001-002 turns on: a family guard is not proof when the predicate is one behaviour, and is proof when the predicate is the family. The negative guard is the unperturbed fixture reaching PASS, so zero unexplained mismatches is not satisfied by a comparison that fails everything. Probe: renaming a named guard turned the per-criterion corpus test red on its own, not only the shared existence test.',
        ],
        // F-MD-B18-A002-017: the corpus test and its four members are structural; a survivorship defect left all of them green.
        'MD-S002-R0005' => [
            'positive' => 'B18ReleaseCandidateCriteriaTest::test_the_anti_survivorship_and_as_known_isolation_corpus_is_complete',
            'negative' => 'B18ReleaseCandidateCriteriaTest::test_every_guard_named_by_any_criterion_exists',
            'basis' => 'both corpora the criterion names are enumerated from their own contracts and asserted complete and executable: the eight MD-S050 anti-survivorship fixtures through their contract-parsed map, the seven MD-S003 later-revision kinds through theirs, and the nine MD-S050 anti-future items through the map added for MD-S050-R0017. The recorded basis for this row was that the guard executed identity-cutoff invisibility only; the corpus now spans identity, symbol, symbol reuse, calendar, status, event, factor, config, sector and provider mapping. That every member is green is established by the suite run recorded with the stage evidence rather than by a test asserting it about itself, which would be circular. Probe: rewording a criterion away from the contract turned the map guard red; renaming a member guard turned both the corpus test and the existence test red.',
        ],
        // F-MD-B18-A002-017 (Gap B2, E-MD-B18-A002-071): returned from PROVEN. Its own negative
        // clause's prerequisite -- a reachable as-known comparison -- no longer exists once every
        // as-known replay is BLOCKED for an unbindable reason-registry identity.
        'MD-S050-R0005' => [
            'positive' => 'B18AsKnownModeIsolationTest::test_an_as_known_result_claims_no_publication_and_records_its_own_mode',
            'negative' => null,
            'basis' => 'PARTIAL -- not PROVEN (returned from PROVEN by E-MD-B18-A002-071, MD-S050-R0016 Gap B2). Replay_Verification_Contract_LOCKED.md:17: "It may differ from a historical publication when the selected cutoff, approved as-known configuration, or declared scenario differs. It creates new replay artifacts and never mutates or impersonates the original publication." Two clauses. The impersonation clause is unaffected and stays fully proven: the positive guard (test_an_as_known_result_claims_no_publication_and_records_its_own_mode) still passes unmutated, asserting the stored row names AS_KNOWN, its cutoff, source as_known_replay and a null publication_id -- a BLOCKED as-known result still carries these, confirmed by construction in verifyAsKnownAgainstFixture()\'s new early-return block. The "may differ from a historical publication" clause\'s own negative control is not: the prior guard (test_an_as_known_result_diverging_from_its_own_fixture_is_a_mismatch) perturbed the fixture\'s own snapshot_hash and asserted MISMATCH/FAIL, proving a divergence from as-known\'s own expectation is still caught rather than silently accepted under the "may differ" permission. Gap B2 makes every as-known replay BLOCKED before any comparison runs, because MD-S085 defines no historical reason-registry identity for one to bind -- confirmed unconditional, not merely a missing test scenario: no fixture content can reach comparison, since the block fires on the captured snapshot alone. That MISMATCH/FAIL scenario is therefore not merely untested but structurally unreachable through the real verifier, and building the historical reason-registry versioning that would make it reachable is explicitly out of Gap B2\'s scope. Per instruction, not force-replaced with a same-named guard proving a different claim: the renamed test at that former name (test_an_as_known_result_with_a_diverging_fixture_is_still_blocked_by_the_unavailable_reason_registry) proves BLOCKED-regardless-of-other-divergence, a true and useful fact, but not R0005\'s own original negative claim (comparison integrity when as-known genuinely executes). Classification IMPLEMENTATION_DEPENDENCY_UNAVAILABLE: R0005\'s own implementation is not defective and its positive clause remains independently true; its negative clause\'s prerequisite (a reachable as-known comparison) is unavailable because a different required input (reason-registry identity) is genuinely unbindable per current authority. Demoted rather than kept PROVEN on the renamed test\'s different claim. Remediation: none available within current authority; would require real historical reason-registry versioning (out of scope) or an owner decision narrowing R0005\'s own required proof shape.',
        ],
        // F-MD-B18-A002-013 reopened (E-MD-B18-A002-070): returned from PROVEN. E-050 promoted both on the
        // PUBLICATION_EXACT path alone; the predicates cover AS_KNOWN too, where the reason-registry
        // identity is nominal (and, for R0014, read-model version is empty). Publication guards retained.
        'MD-S050-R0014' => [
            'positive' => 'B18ReplayComparisonExhaustivenessTest::test_a_divergence_in_any_frozen_input_denies_pass',
            'negative' => 'ReplayVerificationServiceTest::test_formula_and_reason_registry_hash_come_from_the_registry_versions_component',
            'basis' => 'PARTIAL -- not PROVEN (returned from PROVEN by E-MD-B18-A002-070; F-MD-B18-A002-013 reopened). Replay_Verification_Contract_LOCKED.md:30 requires every fixture/manifest -- both replay modes, matrix MANDATORY with no mode restriction -- to bind formula, indicator registry, reason registry, price-product, coverage, eligibility, read-model, hash/serialization and build versions. PUBLICATION_EXACT is proven and stays proven: actualBoundInputContext() folds the registry members into the registry_versions component payload hash (real eod_reason_codes content, indicator, coverage, price-product via semantic_versions, eligibility) and decodes read_model_version/serialization_version/executable_build_identity from the verified capture; the cited guards establish that and were re-run green. E-MD-B18-A002-050 promoted the predicate on that path alone and never examined AS_KNOWN, although F-MD-B18-A002-013 item 2 records the reason-registry identity as nominal in both modes. Executed (E-MD-B18-A002-070): in the seeded AS_KNOWN world, changing one of 437 eod_reason_codes rows and adding a new code left AS_KNOWN reason_registry_hash and the whole as-known snapshot_hash unchanged -- the identity is {coverage_states, replay_states, build_reason_registry_revision: ""}, not registry content -- and read_model_version is empty. Forcing a constant AS_KNOWN reason hash (the package probe) turned no bound guard red, while the same constant in PUBLICATION_EXACT did. Classification IMPLEMENTATION_AND_PROOF_DEFECT: two AS_KNOWN members are not bound, so no rebind can restore the claim. Remediation ran through F-MD-B18-A002-017 Gap B1 (D-MD-B18-A002-008, E-MD-B18-A002-071: AS_KNOWN binds read-product contract identity market_data_read_product_v1) and Gap B2 (E-MD-B18-A002-071: AS_KNOWN reason-registry identity is now the honest REASON_REGISTRY_IDENTITY_UNAVAILABLE marker and every as-known replay is BLOCKED before comparison). This proof review, post-Gap-B2: fail-closed behaviour does not satisfy R0014\'s own wording, which requires binding -- comparing -- the named versions, not refusing to compare when one cannot be bound. AS_KNOWN can now never bind a reason-registry version at all, so this predicate\'s AS_KNOWN half remains genuinely unmet, not merely unenforced; BLOCKED is the correct response to that fact, not a substitute for it. Remains INCOMPLETE.',
        ],
        'MD-S019-R0071' => [
            'positive' => 'B18ReplayComparisonExhaustivenessTest::test_a_divergence_in_any_frozen_input_denies_pass',
            'negative' => 'ReplayVerificationServiceTest::test_formula_and_reason_registry_hash_come_from_the_registry_versions_component',
            'basis' => 'PARTIAL -- not PROVEN (returned from PROVEN by E-MD-B18-A002-070, reviewed independently of MD-S050-R0014). Determinism_Invariants_LOCKED.md:117, Invariant 14 antecedent "price-product and formula/registry versions"; Invariant 14 closes "Publication replay freezes the exact identities above. As-known replay resolves only revisions known by the declared knowledge cutoff. Current state must not leak into either mode." -- the ingredient applies to both modes. PUBLICATION_EXACT is proven and stays proven on the combined registry_versions payload hash (real reason-registry content plus indicator/coverage/price-product/eligibility versions). AS_KNOWN: the formula/indicator half is real (hash of the as-known snapshot resolved configuration and semantic bindings), but the reason-registry half is not bound -- executed (E-MD-B18-A002-070), a changed and an added eod_reason_codes row left AS_KNOWN reason_registry_hash and snapshot_hash unchanged, so two AS_KNOWN replays with different registry content present as identical inputs, and the antecedent cannot be established for that ingredient. Read-model version is not part of this rule\'s wording and is not a ground here. Classification IMPLEMENTATION_AND_PROOF_DEFECT on the reason-registry ingredient only; the shared root with MD-S050-R0014 is the same AsKnownReplaySnapshotService reason identity. Gap B2 landed (E-MD-B18-A002-071); reviewed independently of R0014, as instructed. Invariant 14 requires \'if replay uses identical ... formula/registry versions, then replay must reproduce identical outputs\' -- a positive reproducibility claim over an actually-used identical value. Fail-closed BLOCKED does not establish this for the reason-registry ingredient: AS_KNOWN cannot use any reason-registry version now, identical or otherwise, so the antecedent can never be satisfied for it. What Gap B2 does prove is the adjacent, narrower Platform_Config_Registry_LOCKED.md:291 guarantee (\'current registry state must never leak into historical replay\') for this ingredient -- confirmed directly: test_mutating_the_current_reason_registry_does_not_make_as_known_admissible inserts a row into the live eod_reason_codes table and the result stays BLOCKED on the same marker. That is a real, proven fact, but it is not R0071\'s own reproducibility claim. Remains INCOMPLETE.',
        ],
        // F-MD-B18-A002-017: the corpus test checks method names only, the helper three probes showed executes nothing; rebind to the executing oracle guards.
        'MD-S002-R0007' => [
            'positive' => 'B18ReleaseCandidateCriteriaTest::test_the_independent_oracle_corpus_is_complete',
            'negative' => 'B18LongChainWilderAtrOracleTest::test_the_fixture_true_range_is_not_constant',
            'basis' => 'both halves of the criterion now have executed oracle comparisons. The long-chain ATR half is the 200-session varied-true-range chain compared against a recursion transcribed from EOD_Indicators_Formula_Spec.md, plus the correction thirty sessions back asserted at its exact Wilder-decayed magnitude -- written for MD-S003-R0015 because the pre-existing ATR oracle ran on a constant-true-range ramp and could not detect a wrong recursion. The corporate-action half is the verified-revision factor activation and the structural OHLC coherence guards written for MD-S003-R0012 and R0013. The negative guard asserts the ATR fixture\'s true range genuinely varies, which is what makes the long chain an oracle comparison rather than a restatement of the constant case. Probe: renaming an oracle guard turned the corpus test red independently of the shared existence test.',
        ],
        // F-MD-B18-A002-017: structural corpus test; disabling sealed-publication immutability left it green while the member went red.
        'MD-S002-R0008' => [
            'positive' => 'B18ReleaseCandidateCriteriaTest::test_the_corrected_publication_corpus_is_complete',
            'negative' => 'PublicationSealPointerLifecycleTest::test_the_pointer_table_structurally_refuses_a_second_current_row',
            'basis' => 'preserving predecessors is the superseded publication keeping its own rows unchanged and being refused for discard with SEALED_PUBLICATION_IMMUTABLE, on a fixture carrying two sealed publications for one date. Switching atomically is the pointer table refusing a second current row for a date by primary key, so no window exists in which two publications are current, together with the read path returning nothing rather than stale rows when the projection disagrees with the pointer -- a partial switch is therefore visible as a refusal instead of as whichever version a reader reached first. Probe: renaming a member guard turned the corpus test red.',
        ],
        // F-MD-B18-A002-017: structural pair; a survivorship defect the acceptance member caught left it green.
        'MD-S004-R0008' => [
            'positive' => 'B18PointInTimeInputContractTest::test_the_acceptance_fixture_map_names_exactly_what_the_contract_names',
            'negative' => 'B18PointInTimeInputContractTest::test_every_acceptance_fixture_guard_exists_and_they_are_distinct',
            'basis' => 'the seven acceptance fixtures the contract requires at minimum are each bound to a guard that executes that scenario against a real surface: the delisted-listing universe fixture, the reused-symbol-text fixture, the as-known corporate-action boundary, the knowledge-time suspension sequence, the no-fallback run that fails outright, the fallback run that holds with the prior effective date, and the two-publication read-path fixture. The map is parsed from the acceptance-fixtures sentence rather than transcribed, so a fixture added there fails. The recorded basis was that the guard executed none of the seven as fixtures. The negative guard is the anti-collapse check as well as the existence check: at minimum prove is a floor over seven distinct scenarios, so they are asserted to resolve to at least six distinct guards -- a binding pointing them all at one test would satisfy the map while proving one of them. Probe: dropping explicit stale fallback from the map turned the contract-map guard red.',
        ],
        // F-MD-B18-A002-017: the recorded pair is a map parse and a substrate check; the exact-publication family runs a test-written query that a repository defect left green; member scenarios remain INCOMPLETE.
        'MD-S003-R0025' => [
            'positive' => 'B18ScenarioFamiliesOnMariaDbTest::test_the_family_map_names_exactly_the_families_the_contract_requires',
            'negative' => 'B18ScenarioFamiliesOnMariaDbTest::test_these_scenarios_really_run_on_mariadb',
            'basis' => 'new substrate. The mirror half was already true -- every DB-backed market-data guard runs on the SQLite mirror -- and the MariaDB half was true of nothing: all 91 of those guards swap database.default to an in-memory SQLite connection, so no family had ever been resolved against the engine production uses. UsesMarketDataMariaDb was added: it points at the migrated tradeaxis_testing schema rather than a hand-maintained mirror definition, wraps each test in a transaction it rolls back so the shared database keeps no residue, and skips rather than fails when MariaDB is unreachable, because an unavailable environment is not a proof failure. All six MD-S003 required scenario families now execute against MariaDB through the same repositories and services the mirror guards use, and the family map is parsed from the contract headings so a family added to MD-S003 with nothing exercising it on MariaDB fails rather than leaving all required scenario families meaning whichever six were written down. The negative guard asserts the substrate itself -- driver mysql, version containing MariaDB, and the expected database name -- because a class that silently ran on the mirror would prove the opposite of what it claims. Two production constraints the mirror cannot enforce surfaced while writing the fixtures and are recorded rather than absorbed: eod_current_publication_pointer.updated_at is NOT NULL with no default on MariaDB, and the pointer carries a foreign key to eod_publications which the mirror creates with foreign_key_constraints disabled, so referential integrity in the pointer/publication chain is enforced by production and by nothing in the mirror. Probes: pointing the trait at SQLite makes all nine tests skip rather than pass, so the class cannot silently claim MariaDB; removing the knowledge-time bound from the status supersession join turned the temporal identity family red on MariaDB, and removing it from the calendar query turned the as-known isolation family red, so the scenarios exercise the behaviour rather than merely connecting. One pending additive migration, AddReplayV2BoundInputContext, was applied to tradeaxis_testing to bring its schema current; a family proven against a stale schema would be proven against the wrong tables, which the trait now refuses by skipping.',
        ],
        // F-MD-B18-A002-018: the positive tests factor-set identity drift in replay; a canonical row keeping provider adj_close left it green while the negative went red.
        'MD-S003-R0014' => [
            'positive' => 'ReplayVerificationServiceTest::test_replay_detects_analytical_factor_set_identity_drift',
            'negative' => 'CanonicalRawImportBoundaryTest::test_provider_adjusted_close_never_reaches_the_canonical_row',
            'basis' => 'the negative guard proves provider adjusted close never reaches the canonical row',
        ],
        // F-MD-B18-A002-018: the pair varies effective time only; removing the status knowledge-time bound left it green.
        'MD-S003-R0011' => [
            'positive' => 'AsKnownReplayBoundaryTest::test_status_revision_selection_applies_both_effective_and_knowledge_time',
            'negative' => 'AsKnownReplayBoundaryTest::test_a_calendar_revision_recorded_after_the_cutoff_is_invisible',
            'basis' => 'status selection is asserted on both sides of effective_from while holding knowledge time fixed, and separate status/calendar cutoff guards exclude revisions recorded after knowledge_cutoff; deleting each effective/recorded predicate made its guard fail',
        ],
        // F-MD-B18-A002-018 with F-MD-B18-A002-013: the positive passes against a calendar wall that refuses every cutoff read; publication-mode calendar identity is unbound.
        'MD-S041-R0032' => [
            'positive' => 'AsKnownReplayBoundaryTest::test_a_calendar_revision_recorded_after_the_cutoff_is_invisible',
            'negative' => 'CalendarProvenanceAndStatusTest::test_calendar_revision_conflict_and_incomplete_verification_both_fail_closed',
            'basis' => 'a future-recorded calendar correction is unavailable at the earlier cutoff, and competing terminal revisions fail closed; widening recorded_at to a future sentinel and bypassing the conflict count each made the guards fail',
        ],
        // F-MD-B18-A002-018: the pair covers the status root and effective time only; removing the status or the config knowledge-time bound left it green.
        'MD-S050-R0028' => [
            'positive' => 'AsKnownReplayBoundaryTest::test_status_revision_selection_applies_both_effective_and_knowledge_time',
            'negative' => 'CalendarProvenanceAndStatusTest::test_same_priority_authoritative_conflict_holds_instead_of_using_recency',
            'basis' => 'effective_from and recorded_at are independently exercised, a superseding correction deterministically replaces its predecessor, and same-priority status or calendar ambiguity fails closed; deleting either time predicate or either conflict branch made the corresponding guard fail',
        ],
        // F-MD-B18-A002-018 with F-MD-B18-A002-013: the negative tests config refusal, not mapping; publication-mode mapping identity is unbound.
        'MD-S055-R0025' => [
            'positive' => 'AsKnownReplayBoundaryTest::test_identity_recorded_after_the_cutoff_is_invisible',
            'negative' => 'AsKnownReplayBoundaryTest::test_an_as_known_config_resolution_refuses_rather_than_inventing_one',
            'basis' => 'the mapping effective on T with as-known limited to revisions known by the cutoff is exactly what the identity-cutoff guard asserts',
        ],
        // F-MD-B18-A002-018: the positive passes against a calendar wall and seeds one late fact rather than a correction.
        'MD-S050-R0022' => [
            'positive' => 'AsKnownReplayBoundaryTest::test_a_calendar_revision_recorded_after_the_cutoff_is_invisible',
            'negative' => 'B18AntiSurvivorshipFixtureCorpusTest::test_every_named_fixture_guard_exists_and_is_executable',
            'basis' => 'a calendar fact recorded after the cutoff is asserted invisible to the as-known read and visible without one, and the corpus guard asserts this fixture still exists',
        ],
        // F-MD-B18-A002-018: the positive passes against a corporate-action wall that hides everything under a cutoff.
        'MD-S050-R0023' => [
            'positive' => 'AsKnownReplayBoundaryTest::test_a_corporate_action_recorded_after_the_cutoff_is_invisible',
            'negative' => 'B18AntiSurvivorshipFixtureCorpusTest::test_every_named_fixture_guard_exists_and_is_executable',
            'basis' => 'a corporate action recorded after the cutoff is asserted invisible to the as-known read, with a second assertion proving the row exists so a filter hiding everything cannot masquerade as a pass',
        ],
        // F-MD-B18-A002-018: no corrected publication in the fixture; removing the as-known seal bound left it green while the correction read-path guard went red.
        'MD-S050-R0025' => [
            'positive' => 'ReplayVerificationServiceTest::test_verify_replay_resolves_historical_publication_without_current_pointer_fallback',
            'negative' => 'B18AntiSurvivorshipFixtureCorpusTest::test_every_named_fixture_guard_exists_and_is_executable',
            'basis' => 'publication 144 version 4 is replayed while it is not the current publication, and the result records HISTORICAL_PUBLICATION_AUDIT with current_pointer_required false, so the original is resolved rather than the corrected current one',
        ],
        // F-MD-B18-A002-018: the null-reason class is not compared (reason-count comparison disabled, table green) and the table asserts only that some mismatch occurs.
        'MD-S050-R0029' => [
            'positive' => 'B18ReplayComparisonExhaustivenessTest::test_a_divergence_in_any_named_assertion_class_denies_pass',
            'negative' => 'B18ReplayComparisonExhaustivenessTest::test_the_unperturbed_fixture_passes',
            'basis' => 'new. One baseline fixture that matches, then one perturbation per class the contract names -- a value (bars_rows_written), a null reason (final_reason_code), a state (publishability, terminal, coverage gate), a lineage (publishing run, publication version), a content hash (all three batch hashes) and the seal -- each asserted to turn PASS into a reason-coded MISMATCH, so a class that is not compared shows up as a perturbation that still passes. A separate test asserts the perturbation table covers every class MD-S050-R0029 lists, so exhaustiveness is not proven over whatever subset happened to be written down; the manifest half is a fixture whose manifest declares a file it does not carry, refused outright. The negative guard is the unperturbed control, without which each perturbation could be failing for an unrelated reason.',
        ],
    ];

    // Transfer audit only, never read as a basis. D005: primary admission belongs to B22, supporting B18/B17.
    // D-MD-B18-A002-010 (D2): MD-S065-R0003 primary B21 with MD-S082-R0207/R0209, supporting B18/B04.
    // Each prior INCOMPLETE entry supplies no accepted basis in any stage.
    public const TRANSFERRED_OWNERSHIP = [
        // F-MD-B18-A002-015: readiness admission rule; the guard forbids one sentence shape, and the admission owner (MD-B17/MD-B22) is a user decision.
        'MD-S020-R0014' => [
            'positive' => 'B18ReplayAdmissibilityBoundaryTest::test_no_active_surface_cites_a_replay_verdict_for_something_replay_cannot_establish',
            'negative' => 'B18ReplayAdmissibilityBoundaryTest::test_every_pattern_matches_the_claim_it_forbids_and_spares_the_denial',
            'basis' => 'admitting market-data readiness only from qualifying market-data evidence is a citation rule the corpus admissibility guard scans for',
        ],
        // D-MD-B18-A002-010 transfer (DOC-CHG-20260925-001): moved verbatim from INCOMPLETE. MD-B21 proves the revised
        // rule once the effective-dated registry exists; the pair below never proved it and is not inherited.
        // F-MD-B18-A002-017 G09 (E-MD-B18-A002-077): stopped, AUTHORITY_AMBIGUITY. The pair below proves configuration-identity minting
        // (R0001's content), not the rerun rule; the rerun path binds live config, so nothing can honestly be rebound to prove it.
        'MD-S065-R0003' => [
            'positive' => 'B18ConfigEffectiveTimeSelectionTest::test_an_output_affecting_change_produces_a_new_configuration_identity',
            'negative' => 'B18ConfigEffectiveTimeSelectionTest::test_an_unchanged_configuration_does_not_mint_a_new_contract',
            'basis' => 'NOT PROVEN (F-MD-B18-A002-017 G09 reconstruction, E-MD-B18-A002-077; classification AUTHORITY_AMBIGUITY). Config_Change_Protocol_LOCKED.md:7, "reruns must use the registry version effective for the requested trade date or explicitly documented override". The earlier basis here claimed the rerun clause was proven by the MD-S082-R0216 effective-time selection guard, and F-017 recorded "production conforms: all three resolveForRun callers pass the requested trade date". Both are disproven for the case that matters. B18ConfigEffectiveTimeSelectionTest builds its intervals by resolving the live configuration once and copying the row, so live content always equals the governing version and no scenario there has a live configuration that differs from it. Executed on the real path (E-077): a seed run for 2026-03-24 bound version A; a later version B became live and was recorded effective 2026-04-10; createPromoteRunFromSeed for 2026-03-24 then bound B, minted as a third snapshot stamped effective 2026-03-24 -- not the version effective for that date. resolveForRun() without a cutoff hashes the live configuration and reuses the governing snapshot only when the hashes are equal; the requested date selects the comparison and stamps effective_at, not the content. There are now four resolveForRun call sites (EodRunRepository :33, :187, :337 and AsKnownReplaySnapshotService :101); the two that pass a cutoff are lookups and conform, the two that do not (getOrCreateOwningRun and createPromoteRunFromSeed) bind live content. The platform also forbids the alternative: assertConsumedConfiguration() rejects a run whose bound snapshot differs from live config, so a rerun cannot execute under a historical version while live config has moved. Authority does not say what the "explicitly documented override" is, and the recompute contract (Current_Indicator_Recompute_Command_Contract.md operational rerun rule) and the correction contracts (Historical_Correction_and_Reseal_Contract_LOCKED.md, Audit_Hash_and_Reproducibility_Contract_LOCKED.md) treat a rerun under changed configuration as a legitimate labelled correction, which a literal reading would forbid. Implementation cannot choose between recognising the current behaviour as the documented override, defaulting to the historical version and blocking, or effective-dated registered changes; it needs an owner decision (E-077 lists the three options). The positive/negative guards below still prove the separate claim that an output-affecting change acquires a new identity and an unchanged configuration does not (MD-S065-R0001\'s content), and remain valid for that. The source comment "this run executes under today\'s resolved config" was checked and is accurate, not stale. Remains INCOMPLETE.',
        ],
    ];

    // Audit only, never read as a basis: the R0056 entry as it stood when F-008 measured that the
    // mapped pair stays green with one publication fixture made non-executable.
    public const SUPERSEDED = [
        // ---- MD-S050-R0056. The corpus MD-S050 requires, executed on the engine and repositories production
        // runs rather than on the mirror. The half this does not reach - the production deployment itself, and
        // a gate that refuses a relock lacking the corpus - is named in the basis rather than assumed.
        'MD-S050-R0056' => [
            'positive' => 'B18ProductionPathReplayFixturesTest::test_the_production_path_corpus_covers_every_anti_survivorship_case',
            'negative' => 'B18ProductionPathReplayFixturesTest::test_these_fixtures_really_run_on_the_production_engine',
            'basis' => 'new substrate for a corpus that already existed. The row requires executed publication and as-known fixtures, including all anti-survivorship cases, on the actual production path; every one of those fixtures executed, and every one executed on the SQLite mirror, which is a hand-maintained definition of the intended schema rather than the schema production runs. B18ProductionPathReplayFixturesTest runs all eight MD-S050 cases plus a publication fixture against MariaDB through the same repositories the pipeline calls, using UsesMarketDataMariaDb: the migrated tradeaxis_testing schema, each test wrapped in a transaction it rolls back, skipping rather than failing when the engine is unreachable. The case list is parsed from MD-S050 rather than transcribed, so a case added to the contract with nothing exercising it on the production path fails here rather than leaving all anti-survivorship cases above quietly meaning the eight that happened to be written down. The negative guard asserts the substrate itself - driver, MariaDB in the version string, and the expected database - because a corpus that claimed the production path and silently delivered the mirror would prove the opposite of what the row asks. What this does not establish is stated in the class and is not claimed here: production path means the engine production runs and the repositories production calls, not the production deployment. No fixture runs against production data, production relock is a governance act rather than something a test performs, and nothing here refuses a relock that lacks this corpus. Building it found a real defect in the fixtures it was porting, which is the part worth recording. The recorded probe for the two as-known symbol fixtures - neutering the retracted_at clause turns them red - was re-run and is false as stated: both fixtures read only cutoffs before the rename was recorded, where the answer is already decided by ls.recorded_at <= knownAt, so the retraction clause could be neutered on both md_listing_symbols and md_provider_symbol_mappings with the corpus staying green, and the mapping rows were seeded and never read at all. Both fixtures now take three reads - before the record, after it, and uncut - and case 2 resolves the provider mapping as well as the symbol, which is what the contract case says. F-MD-B18-A002-002 carries the correction. Probes, each reverted by byte copy with the files verified identical by md5: removing the delisting knowledge-time clause turns case 1 red on MariaDB; neutering ls.retracted_at > knownAt turns case 2 red on both substrates, as does neutering the uncut whereNull branch; neutering pm.retracted_at > knownAt makes the resolver refuse with PROVIDER_SYMBOL_MAPPING_AMBIGUOUS because a retracted mapping answering beside its replacement makes two provider symbols claim one listing on one date; removing the sealed_at cutoff bound turns case 7 red. Two production constraints the mirror does not enforce shaped the fixtures rather than being worked around: the audit resolver refuses a publication whose run row or coverage telemetry is absent, and the configuration resolver selects on the configured environment profile. Residue: F-MD-B18-A002-003 stands - the mirror creates its schema with foreign key constraints disabled and mirrors nullability loosely, which is why this corpus is not redundant with the mirror one.',
        ],
    ];

    /** @return array<int,string> denominator rows this attempt has not yet established */
    public static function outstanding(?string $root = null): array
    {
        $out = [];
        // PredicateMap preserves the original 121-row invalidation audit. It is not today's
        // denominator: D001/D002 move two obligations and evidence excludes four siblings.
        $root = $root ?? dirname(__DIR__, 5);
        foreach (MarketDataReplayVerificationTraceabilitySpec::required($root) as $row) {
            $id = $row['rule_id'];
            if (! isset(self::PROVEN[$id])) {
                $out[] = $id;
            }
        }

        return $out;
    }

    /** @return array<int,string> entries absent from the original reviewed predicate lineage */
    public static function foreign(): array
    {
        $out = [];
        foreach (self::PROVEN as $id => $_entry) {
            if (! isset(MarketDataReplayVerificationPredicateMap::PREDICATES[$id])) {
                $out[] = $id;
            }
        }

        return $out;
    }
}
