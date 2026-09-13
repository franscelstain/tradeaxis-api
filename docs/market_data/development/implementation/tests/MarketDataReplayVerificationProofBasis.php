<?php

require_once __DIR__.'/MarketDataReplayVerificationPredicateMap.php';

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
        'MD-S040-R0071' => [
            'positive' => 'B18ReplayEvidencePreservationContractTest::test_every_preserved_item_survives_the_real_export',
            'negative' => 'B18ReplayEvidencePreservationContractTest::test_a_failing_coverage_gate_still_exports_the_threshold_it_failed_against',
            'basis' => 'the exported coverage reason code is asserted; it is derived from the normalized gate state because no coverage reason code is persisted on the replay metric, and the fixture proves the derivation rather than an echo',
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
        'MD-S040-R0077' => [
            'positive' => 'B18ReplayEvidencePreservationContractTest::test_every_preserved_item_survives_the_real_export',
            'negative' => 'B18ReplayEvidencePreservationContractTest::test_a_failing_coverage_gate_still_exports_the_threshold_it_failed_against',
            'basis' => 'final_reason_code is a persisted column and is asserted to survive the export verbatim, unlike the derived coverage reason code',
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
        'MD-S050-R0008' => [
            'positive' => 'B18ReplayBoundInputIdentityContractTest::test_every_named_identity_is_bound_into_the_exported_replay_result',
            'negative' => 'B18ReplayBoundInputIdentityContractTest::test_a_fixture_binding_different_identities_produces_a_different_block',
            'basis' => 'the temporal identity hash carrying dataset boundary and universe/listing/symbol/provider mappings is asserted bound and record-derived',
        ],
        'MD-S050-R0009' => [
            'positive' => 'B18ReplayBoundInputIdentityContractTest::test_every_named_identity_is_bound_into_the_exported_replay_result',
            'negative' => 'B18ReplayBoundInputIdentityContractTest::test_a_fixture_binding_different_identities_produces_a_different_block',
            'basis' => 'the calendar/status revision hash is asserted bound and record-derived',
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
        'MD-S050-R0012' => [
            'positive' => 'B18ReplayBoundInputIdentityContractTest::test_every_named_identity_is_bound_into_the_exported_replay_result',
            'negative' => 'B18ReplayBoundInputIdentityContractTest::test_a_fixture_binding_different_identities_produces_a_different_block',
            'basis' => 'the event/factor revision hash covering verification states and factor-set revisions is asserted bound and record-derived',
        ],
        'MD-S050-R0013' => [
            'positive' => 'B18ReplayBoundInputIdentityContractTest::test_every_named_identity_is_bound_into_the_exported_replay_result',
            'negative' => 'B18ReplayBoundInputIdentityContractTest::test_a_fixture_binding_different_identities_produces_a_different_block',
            'basis' => 'config snapshot id and hash are both asserted bound and record-derived',
        ],
        'MD-S050-R0014' => [
            'positive' => 'B18ReplayBoundInputIdentityContractTest::test_every_named_identity_is_bound_into_the_exported_replay_result',
            'negative' => 'B18ReplayBoundInputIdentityContractTest::test_a_fixture_binding_different_identities_produces_a_different_block',
            'basis' => 'formula registry, reason registry, read-model, serialization and executable build identities are each asserted bound and record-derived',
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
        'MD-S019-R0067' => [
            'positive' => 'B18ReplayBoundInputIdentityContractTest::test_every_named_identity_is_bound_into_the_exported_replay_result',
            'negative' => 'B18ReplayBoundInputIdentityContractTest::test_a_fixture_binding_different_identities_produces_a_different_block',
            'basis' => 'temporal issuer/instrument/listing/symbol and provider-mapping identity is asserted bound and record-derived; the consequent MD-S019-R0073 remains outstanding',
        ],
        'MD-S019-R0068' => [
            'positive' => 'B18ReplayBoundInputIdentityContractTest::test_every_named_identity_is_bound_into_the_exported_replay_result',
            'negative' => 'B18ReplayBoundInputIdentityContractTest::test_a_fixture_binding_different_identities_produces_a_different_block',
            'basis' => 'calendar/session/status revisions are asserted bound and record-derived; the consequent MD-S019-R0073 remains outstanding',
        ],
        'MD-S019-R0069' => [
            'positive' => 'B18ReplayBoundInputIdentityContractTest::test_every_named_identity_is_bound_into_the_exported_replay_result',
            'negative' => 'B18ReplayBoundInputIdentityContractTest::test_a_fixture_binding_different_identities_produces_a_different_block',
            'basis' => 'corporate-action event and factor-set revisions are asserted bound and record-derived; the consequent MD-S019-R0073 remains outstanding',
        ],
        'MD-S019-R0070' => [
            'positive' => 'B18ReplayBoundInputIdentityContractTest::test_every_named_identity_is_bound_into_the_exported_replay_result',
            'negative' => 'B18ReplayBoundInputIdentityContractTest::test_a_fixture_binding_different_identities_produces_a_different_block',
            'basis' => 'the full configuration snapshot id and hash are asserted bound and record-derived; the consequent MD-S019-R0073 remains outstanding',
        ],
        'MD-S019-R0071' => [
            'positive' => 'B18ReplayBoundInputIdentityContractTest::test_every_named_identity_is_bound_into_the_exported_replay_result',
            'negative' => 'B18ReplayBoundInputIdentityContractTest::test_a_fixture_binding_different_identities_produces_a_different_block',
            'basis' => 'price-product and formula/registry versions are asserted bound through the formula and reason registry hashes and the read-model version; the consequent MD-S019-R0073 remains outstanding',
        ],
        'MD-S019-R0072' => [
            'positive' => 'B18ReplayBoundInputIdentityContractTest::test_every_named_identity_is_bound_into_the_exported_replay_result',
            'negative' => 'B18ReplayBoundInputIdentityContractTest::test_a_fixture_binding_different_identities_produces_a_different_block',
            'basis' => 'serialization rules are asserted bound through the serialization version and are record-derived; the consequent MD-S019-R0073 remains outstanding',
        ],

        // ---- Re-verified under MD-B18-A002: predicates whose MD-B18-A001 guard does establish
        // them. The guards were re-executed in this attempt (149 tests green) and re-probed --
        // 11/11 fail-closed probes caught with controls green either side -- because a pass
        // proven under a withdrawn closure is not inheritable. Thirteen of these are citation
        // prohibitions carried by the declared corpus guard, which legitimately covers many
        // predicates: it scans every active surface for the claims they forbid.
        'MD-S002-R0009' => [
            'positive' => 'B18ReplayAdmissibilityBoundaryTest::test_no_active_surface_cites_a_replay_verdict_for_something_replay_cannot_establish',
            'negative' => 'B18ReplayAdmissibilityBoundaryTest::test_every_pattern_matches_the_claim_it_forbids_and_spares_the_denial',
            'basis' => 'BLOCKED-never-a-pass is exactly a citation prohibition the corpus guard scans for',
        ],
        'MD-S002-R0010' => [
            'positive' => 'B18ReplayAdmissibilityBoundaryTest::test_no_active_surface_cites_a_replay_verdict_for_something_replay_cannot_establish',
            'negative' => 'B18ReplayAdmissibilityBoundaryTest::test_every_pattern_matches_the_claim_it_forbids_and_spares_the_denial',
            'basis' => 'pass-rate-cannot-compensate is a citation prohibition in scope of the corpus guard',
        ],
        'MD-S002-R0016' => [
            'positive' => 'B18ReplayAdmissibilityBoundaryTest::test_no_active_surface_cites_a_replay_verdict_for_something_replay_cannot_establish',
            'negative' => 'B18ReplayAdmissibilityBoundaryTest::test_every_pattern_matches_the_claim_it_forbids_and_spares_the_denial',
            'basis' => 'citation boundary for a metric set',
        ],
        'MD-S003-R0002' => [
            'positive' => 'ReplayVerificationServiceTest::test_verify_replay_resolves_historical_publication_without_current_pointer_fallback',
            'negative' => 'ReplayVerificationServiceTest::test_verify_replay_maps_unsealed_historical_publication_to_reason_coded_failure',
            'basis' => 'resolving an explicit immutable publication without current-pointer fallback is the subject of the guard',
        ],
        'MD-S003-R0005' => [
            'positive' => 'SourceObservationAsKnownBoundaryTest::test_as_known_rows_require_both_observation_and_identity_binding_to_be_known_by_cutoff',
            'negative' => 'SourceObservationAsKnownBoundaryTest::test_zero_row_provider_outage_remains_in_as_known_observation_manifest',
            'basis' => 'the negative guard keeps a zero-row provider outage in the manifest, so the denominator cannot shrink',
        ],
        'MD-S003-R0009' => [
            'positive' => 'TemporalIdentityLayerContractTest::test_point_in_time_resolution_returns_the_full_identity_for_the_trade_date',
            'negative' => 'TemporalIdentityLayerContractTest::test_retraction_after_cutoff_does_not_erase_symbol_board_or_provider_mapping_from_as_known_history',
            'basis' => 'inactive-now/active-then listing remaining in the historical universe is asserted',
        ],
        'MD-S003-R0010' => [
            'positive' => 'TemporalIdentityLayerContractTest::test_point_in_time_resolution_returns_the_full_identity_for_the_trade_date',
            'negative' => 'TemporalIdentityLayerContractTest::test_retraction_after_cutoff_does_not_erase_symbol_board_or_provider_mapping_from_as_known_history',
            'basis' => 'symbol change and reuse resolving through stable listing identity is asserted',
        ],
        'MD-S003-R0014' => [
            'positive' => 'ReplayVerificationServiceTest::test_replay_detects_analytical_factor_set_identity_drift',
            'negative' => 'CanonicalRawImportBoundaryTest::test_provider_adjusted_close_never_reaches_the_canonical_row',
            'basis' => 'the negative guard proves provider adjusted close never reaches the canonical row',
        ],
        'MD-S003-R0011' => [
            'positive' => 'AsKnownReplayBoundaryTest::test_status_revision_selection_applies_both_effective_and_knowledge_time',
            'negative' => 'AsKnownReplayBoundaryTest::test_a_calendar_revision_recorded_after_the_cutoff_is_invisible',
            'basis' => 'status selection is asserted on both sides of effective_from while holding knowledge time fixed, and separate status/calendar cutoff guards exclude revisions recorded after knowledge_cutoff; deleting each effective/recorded predicate made its guard fail',
        ],
        'MD-S003-R0021' => [
            'positive' => 'B18AsKnownSnapshotIsolationTest::test_every_later_revision_kind_is_bound_to_an_executing_guard',
            'negative' => 'B18AsKnownSnapshotIsolationTest::test_an_incomplete_historical_config_snapshot_is_refused_instead_of_using_live_config',
            'basis' => 'the contract-derived seven-kind map binds master, event, status, calendar, config, formula and factor to executed cutoff guards; deleting the factor mapping failed the map, and replacing the selected historical formula config with live config failed the snapshot corpus',
        ],
        'MD-S003-R0022' => [
            'positive' => 'B18AsKnownSnapshotIsolationTest::test_a_later_cutoff_exposes_later_revisions_without_rewriting_the_earlier_snapshot',
            'negative' => 'B18AsKnownSnapshotIsolationTest::test_every_later_revision_kind_is_bound_to_an_executing_guard',
            'basis' => 'one corpus asserts that a declared later cutoff exposes distinct master, status, calendar, config/formula, event and factor revisions while a rerun at the earlier cutoff retains its byte-identical hash and performs no bound-input writes',
        ],
        'MD-S005-R0096' => [
            'positive' => 'B18AsKnownSnapshotIsolationTest::test_every_later_revision_kind_is_bound_to_an_executing_guard',
            'negative' => 'B18AsKnownSnapshotIsolationTest::test_an_incomplete_historical_config_snapshot_is_refused_instead_of_using_live_config',
            'basis' => 'the contract-derived seven-root corpus executes exclusion guards for every later revision named by MD-S003; substituting live formula config and removing a root mapping each turned the corpus red',
        ],
        'MD-S041-R0032' => [
            'positive' => 'AsKnownReplayBoundaryTest::test_a_calendar_revision_recorded_after_the_cutoff_is_invisible',
            'negative' => 'CalendarProvenanceAndStatusTest::test_calendar_revision_conflict_and_incomplete_verification_both_fail_closed',
            'basis' => 'a future-recorded calendar correction is unavailable at the earlier cutoff, and competing terminal revisions fail closed; widening recorded_at to a future sentinel and bypassing the conflict count each made the guards fail',
        ],
        'MD-S050-R0028' => [
            'positive' => 'AsKnownReplayBoundaryTest::test_status_revision_selection_applies_both_effective_and_knowledge_time',
            'negative' => 'CalendarProvenanceAndStatusTest::test_same_priority_authoritative_conflict_holds_instead_of_using_recency',
            'basis' => 'effective_from and recorded_at are independently exercised, a superseding correction deterministically replaces its predecessor, and same-priority status or calendar ambiguity fails closed; deleting either time predicate or either conflict branch made the corresponding guard fail',
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
        'MD-S020-R0014' => [
            'positive' => 'B18ReplayAdmissibilityBoundaryTest::test_no_active_surface_cites_a_replay_verdict_for_something_replay_cannot_establish',
            'negative' => 'B18ReplayAdmissibilityBoundaryTest::test_every_pattern_matches_the_claim_it_forbids_and_spares_the_denial',
            'basis' => 'admitting market-data readiness only from qualifying market-data evidence is a citation rule the corpus admissibility guard scans for',
        ],
        'MD-S036-R0012' => [
            'positive' => 'ReplayModeContractTest::test_only_the_two_locked_replay_modes_are_accepted',
            'negative' => 'OpsCommandSurfaceTest::test_replay_verify_refuses_an_inadmissible_mode_without_attempting_verification',
            'basis' => 'replay_verify as an allowed request mode is established by the mode guard and its command-surface refusal counterpart',
        ],
        'MD-S050-R0001' => [
            'positive' => 'ReplayModeContractTest::test_only_the_two_locked_replay_modes_are_accepted',
            'negative' => 'OpsCommandSurfaceTest::test_replay_verify_refuses_an_inadmissible_mode_without_attempting_verification',
            'basis' => 'only the two locked modes accepted is the subject of the guard',
        ],
        'MD-S050-R0027' => [
            'positive' => 'ReplayVerificationServiceTest::test_verify_replay_resolves_historical_publication_without_current_pointer_fallback',
            'negative' => 'ReplayVerificationServiceTest::test_verify_replay_maps_unsealed_historical_publication_to_reason_coded_failure',
            'basis' => 'starting from explicit publication identity and never latest/current is exactly what the guard asserts',
        ],
        'MD-S050-R0030' => [
            'positive' => 'ReplayEvidenceExportServiceTest::test_export_replay_evidence_writes_replay_result_and_reason_code_summary',
            'negative' => 'ReplayComparisonDetectsDivergenceTest::test_missing_expected_proof_is_reported_rather_than_ignored',
            'basis' => 'the divergence guard executes a comparison that diverges and reports FAIL',
        ],
        'MD-S050-R0031' => [
            'positive' => 'ReplayEvidenceExportServiceTest::test_export_replay_evidence_writes_replay_result_and_reason_code_summary',
            'negative' => 'ReplayComparisonDetectsDivergenceTest::test_missing_expected_proof_is_reported_rather_than_ignored',
            'basis' => 'missing expected proof is reported rather than ignored, which is the BLOCKED semantics',
        ],
        'MD-S050-R0033' => [
            'positive' => 'ReplayAdmissibilityVerdictStorabilityTest::test_a_relabelled_self_generated_fixture_is_still_refused',
            'negative' => 'ReplayAdmissibilityVerdictStorabilityTest::test_the_inadmissible_verdict_is_never_counted_as_a_pass',
            'basis' => 'exit status or row counts alone is not replay proof; the inadmissible verdict is never counted as a pass',
        ],
        'MD-S050-R0036' => [
            'positive' => 'ReplayModeContractTest::test_only_the_two_locked_replay_modes_are_accepted',
            'negative' => 'OpsCommandSurfaceTest::test_replay_verify_refuses_an_inadmissible_mode_without_attempting_verification',
            'basis' => 'an unmoded result is refused rather than defaulted, which the mode guard establishes',
        ],
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
        'MD-S050-R0045' => [
            'positive' => 'B18ReplayAdmissibilityBoundaryTest::test_no_active_surface_cites_a_replay_verdict_for_something_replay_cannot_establish',
            'negative' => 'B18ReplayAdmissibilityBoundaryTest::test_every_pattern_matches_the_claim_it_forbids_and_spares_the_denial',
            'basis' => 'capability boundary: replay cannot prove value correctness',
        ],
        'MD-S050-R0046' => [
            'positive' => 'SourceObservationAsKnownBoundaryTest::test_as_known_rows_require_both_observation_and_identity_binding_to_be_known_by_cutoff',
            'negative' => 'SourceObservationAsKnownBoundaryTest::test_zero_row_provider_outage_remains_in_as_known_observation_manifest',
            'basis' => 'that replay cannot prove source faithfulness is a capability boundary the observation guard framing establishes',
        ],
        'MD-S050-R0050' => [
            'positive' => 'B18ReplayAdmissibilityBoundaryTest::test_no_active_surface_cites_a_replay_verdict_for_something_replay_cannot_establish',
            'negative' => 'B18ReplayAdmissibilityBoundaryTest::test_every_pattern_matches_the_claim_it_forbids_and_spares_the_denial',
            'basis' => 'explicit admissibility rule for a replay PASS',
        ],
        'MD-S050-R0051' => [
            'positive' => 'B18ReplayAdmissibilityBoundaryTest::test_no_active_surface_cites_a_replay_verdict_for_something_replay_cannot_establish',
            'negative' => 'B18ReplayAdmissibilityBoundaryTest::test_every_pattern_matches_the_claim_it_forbids_and_spares_the_denial',
            'basis' => 'explicit prohibition on what a PASS may close',
        ],
        'MD-S050-R0052' => [
            'positive' => 'B18ReplayAdmissibilityBoundaryTest::test_no_active_surface_cites_a_replay_verdict_for_something_replay_cannot_establish',
            'negative' => 'B18ReplayAdmissibilityBoundaryTest::test_every_pattern_matches_the_claim_it_forbids_and_spares_the_denial',
            'basis' => 'names the admissible alternative evidence; the corpus guard forbids the substitution',
        ],
        'MD-S050-R0053' => [
            'positive' => 'B18ReplayAdmissibilityBoundaryTest::test_no_active_surface_cites_a_replay_verdict_for_something_replay_cannot_establish',
            'negative' => 'B18ReplayAdmissibilityBoundaryTest::test_every_pattern_matches_the_claim_it_forbids_and_spares_the_denial',
            'basis' => 'BLOCKED is not a weaker PASS - a citation rule',
        ],
        'MD-S055-R0025' => [
            'positive' => 'AsKnownReplayBoundaryTest::test_identity_recorded_after_the_cutoff_is_invisible',
            'negative' => 'AsKnownReplayBoundaryTest::test_an_as_known_config_resolution_refuses_rather_than_inventing_one',
            'basis' => 'the mapping effective on T with as-known limited to revisions known by the cutoff is exactly what the identity-cutoff guard asserts',
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
        'MD-S050-R0022' => [
            'positive' => 'AsKnownReplayBoundaryTest::test_a_calendar_revision_recorded_after_the_cutoff_is_invisible',
            'negative' => 'B18AntiSurvivorshipFixtureCorpusTest::test_every_named_fixture_guard_exists_and_is_executable',
            'basis' => 'a calendar fact recorded after the cutoff is asserted invisible to the as-known read and visible without one, and the corpus guard asserts this fixture still exists',
        ],
        'MD-S050-R0023' => [
            'positive' => 'AsKnownReplayBoundaryTest::test_a_corporate_action_recorded_after_the_cutoff_is_invisible',
            'negative' => 'B18AntiSurvivorshipFixtureCorpusTest::test_every_named_fixture_guard_exists_and_is_executable',
            'basis' => 'a corporate action recorded after the cutoff is asserted invisible to the as-known read, with a second assertion proving the row exists so a filter hiding everything cannot masquerade as a pass',
        ],
        'MD-S050-R0024' => [
            'positive' => 'AsKnownReplayBoundaryTest::test_a_configuration_recorded_after_the_cutoff_is_invisible_and_none_is_created',
            'negative' => 'B18AntiSurvivorshipFixtureCorpusTest::test_every_named_fixture_guard_exists_and_is_executable',
            'basis' => 'a configuration recorded after the cutoff is asserted invisible and no snapshot is invented in its place',
        ],
        'MD-S050-R0025' => [
            'positive' => 'ReplayVerificationServiceTest::test_verify_replay_resolves_historical_publication_without_current_pointer_fallback',
            'negative' => 'B18AntiSurvivorshipFixtureCorpusTest::test_every_named_fixture_guard_exists_and_is_executable',
            'basis' => 'publication 144 version 4 is replayed while it is not the current publication, and the result records HISTORICAL_PUBLICATION_AUDIT with current_pointer_required false, so the original is resolved rather than the corrected current one',
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
        'MD-S019-R0073' => [
            'positive' => 'B18ReplayRerunDeterminismTest::test_an_unchanged_rerun_produces_byte_identical_artifacts',
            'negative' => 'B18ReplayRerunDeterminismTest::test_a_changed_bound_input_changes_the_bytes',
            'basis' => 'the consequent of the MD-S019 bound-input conditional: two exports of the same replay are asserted byte-identical across every artifact by sha256, and the negative guard asserts replay_result.json changes when a bound input changes, so constant output cannot satisfy it',
        ],
        'MD-S003-R0004' => [
            'positive' => 'B18ReplayRerunDeterminismTest::test_an_unchanged_rerun_produces_byte_identical_artifacts',
            'negative' => 'B18ReplayRerunDeterminismTest::test_a_changed_bound_input_changes_the_bytes',
            'basis' => 'an unchanged rerun is asserted byte-identical, and a separate test passes the publication and correction repositories as mocks with no expectations so any write to either fails - the fake-correction half of the predicate',
        ],
        'MD-S005-R0095' => [
            'positive' => 'B18ReplayRerunDeterminismTest::test_an_unchanged_rerun_produces_byte_identical_artifacts',
            'negative' => 'B18ReplayRerunDeterminismTest::test_a_changed_bound_input_changes_the_bytes',
            'basis' => 'exact publication replay is asserted to reproduce the same hashes: the artifacts carrying bars, indicators and eligibility batch hashes are byte-identical across two runs of the same fixture',
        ],

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
        'MD-S050-R0029' => [
            'positive' => 'B18ReplayComparisonExhaustivenessTest::test_a_divergence_in_any_named_assertion_class_denies_pass',
            'negative' => 'B18ReplayComparisonExhaustivenessTest::test_the_unperturbed_fixture_passes',
            'basis' => 'new. One baseline fixture that matches, then one perturbation per class the contract names -- a value (bars_rows_written), a null reason (final_reason_code), a state (publishability, terminal, coverage gate), a lineage (publishing run, publication version), a content hash (all three batch hashes) and the seal -- each asserted to turn PASS into a reason-coded MISMATCH, so a class that is not compared shows up as a perturbation that still passes. A separate test asserts the perturbation table covers every class MD-S050-R0029 lists, so exhaustiveness is not proven over whatever subset happened to be written down; the manifest half is a fixture whose manifest declares a file it does not carry, refused outright. The negative guard is the unperturbed control, without which each perturbation could be failing for an unrelated reason.',
        ],
        'MD-S036-R0007' => [
            'positive' => 'B18ReplayComparisonExhaustivenessTest::test_an_import_only_expectation_is_not_satisfied_by_a_run_that_promoted',
            'negative' => 'B18ReplayComparisonExhaustivenessTest::test_an_import_only_expectation_against_an_unpromoted_run_raises_no_promotion_mismatch',
            'basis' => 'request mode, source mode and publication state are each proven load-bearing by the perturbation table, and import status, promote status and pointer switch status by the import-only fixture: a fixture declaring request_mode import_only against a run that promoted is asserted to raise REPLAY_IMPORT_PROMOTE_MISMATCH naming import_only_promote_status_policy and import_only_pointer_switch_policy. The negative guard runs the same import-only expectation against a run that did not promote and asserts no promotion mismatch, so the rule is not satisfied by failing every import-only fixture.',
        ],
        'MD-S036-R0031' => [
            'positive' => 'B18ReplayComparisonExhaustivenessTest::test_an_import_only_expectation_is_not_satisfied_by_a_run_that_promoted',
            'negative' => 'B18ReplayComparisonExhaustivenessTest::test_an_import_only_expectation_against_an_unpromoted_run_raises_no_promotion_mismatch',
            'basis' => 'code change, not only a guard. The contract sentence is unexpected import promotion must be a replay mismatch, not a silent pass, and it had no implementation: compareField() skips a null expectation and the fixture schema leaves import_status, promote_status, promoted and pointer_switched optional, so a fixture declaring request_mode import_only had its promotion state checked by nothing. REPLAY_IMPORT_PROMOTE_MISMATCH was a registered reason code with no path able to emit it. ReplayVerificationService::appendImportPromotionPolicyMismatches() was added so that declaring the request mode makes all three promotion signals load-bearing. Probe: removing the call turned the positive red while everything else stayed green.',
        ],
        'MD-S040-R0080' => [
            'positive' => 'B18ReplayComparisonExhaustivenessTest::test_a_manual_file_run_readable_without_a_coverage_pass_is_a_mismatch',
            'negative' => 'B18ReplayComparisonExhaustivenessTest::test_the_unperturbed_fixture_passes',
            'basis' => 'the prohibition rather than the exhibit: a manual_file run whose import succeeded and which claims READABLE while its coverage gate did not pass is asserted to raise manual_file_readable_coverage_policy and a FAIL verdict, so import success alone cannot produce readability. The negative guard is the same manual_file fixture with a passing coverage gate reaching PASS, so the rule is not satisfied by refusing every manual_file run. Probe: disabling the readability policy condition in appendManualFilePolicyMismatches turned the positive red.',
        ],
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
        'MD-S050-R0016' => [
            'positive' => 'B18ReplayComparisonExhaustivenessTest::test_a_publication_with_no_configuration_snapshot_is_blocked_rather_than_passed',
            'negative' => 'B18ReplayComparisonExhaustivenessTest::test_a_blocked_replay_does_not_fall_back_to_the_current_publication',
            'basis' => 'both halves executed against the real ReplayVerificationService. A run and publication carrying no config snapshot are asserted to yield replay_status BLOCKED with comparison_result NOT_ADMISSIBLE and a summary naming REPLAY_CONFIG_UNBOUND -- BLOCKED rather than FAIL, since FAIL would say the comparison ran and disagreed. The second half is the one the predicate is really about: the evidence repository is asserted to be asked only for replay_fixture_explicit_publication and never for a current-pointer resolution, so a missing input is a refusal to proceed rather than permission to answer from latest state. A third guard asserts the blocked outcome is still persisted with admission_state NOT_ADMISSIBLE and its mode, so a block cannot vanish from the corpus. Probe: disabling the CONFIG_UNBOUND admissibility rule turned all three red.',
        ],
        'MD-S082-R0015' => [
            'positive' => 'B18ReplayComparisonExhaustivenessTest::test_a_publication_with_no_configuration_snapshot_is_blocked_rather_than_passed',
            'negative' => 'B18ReplayComparisonExhaustivenessTest::test_the_unperturbed_fixture_passes',
            'basis' => 'publication replay over a CONFIG_UNBOUND publication is asserted BLOCKED rather than PASS, and the block is asserted to name REPLAY_CONFIG_UNBOUND explicitly rather than being an anonymous refusal -- which is the naming-the-state half of the citation rule. The negative guard is the byte-identical fixture whose publication does carry a config snapshot reaching PASS, so the rule is not satisfied by blocking every replay. A further guard asserts the blocked row is persisted with admission_state NOT_ADMISSIBLE, so the state travels with any later citation of it. Probe: disabling the CONFIG_UNBOUND rule turned the positive red.',
        ],

        // ---- MD-S050-R0002 and what it unlocked. The frozen-input comparison did not exist: the identities
        // were recorded into every result and compared by nothing.
        'MD-S050-R0002' => [
            'positive' => 'B18ReplayComparisonExhaustivenessTest::test_a_divergence_in_any_frozen_input_denies_pass',
            'negative' => 'B18ReplayComparisonExhaustivenessTest::test_a_fixture_declaring_every_frozen_input_correctly_still_passes',
            'basis' => 'code change, not only a guard. None of the eleven frozen-input identities was compared: verifyRunAgainstFixture wrote them into the stored result and compareExpectedAndActual never looked, and the fixture schema had no expected side at all, so a replay resolving today\'s indicator registry, today\'s build identity, or a different temporal identity than the one frozen with the publication reported MATCH. Added ReplayVerificationService::actualBoundInputContext() as the single source for the recorded and the compared value, an expected_bound_input_context block read from the fixture, and a per-field comparison over BOUND_INPUT_FIELDS. The guard declares all eleven at the values the replay resolved and perturbs one at a time, asserting the verdict is denied and the mismatch names bound_input_<field>; a companion test parses the MD-S050 publication-replay sentence and asserts the reviewed map covers exactly the inputs it names, so an input added to the contract with no field behind it fails. The negative guard declares all eleven correctly and still reaches PASS, so the rule is not satisfied by rejecting any fixture that carries the block. Reason codes are the registry as it stands -- config identity has its own, the rest fall through to REPLAY_NON_DETERMINISTIC_OUTPUT, which the registry defines as a deterministic-field mismatch with no more specific code; a dedicated REPLAY_BOUND_INPUT_MISMATCH was written and then reverted because Reason_Codes_Registry.md is STRATEGY/CONTROLLED_REVISION and adding vocabulary so an implementation change can emit it is not an implementation decision. Probe: comparing an empty field list turned exactly the eleven perturbations red and nothing else.',
        ],
        'MD-S003-R0003' => [
            'positive' => 'B18ReplayComparisonExhaustivenessTest::test_the_exact_verification_map_names_exactly_what_the_contract_names',
            'negative' => 'B18ReplayComparisonExhaustivenessTest::test_every_guard_the_exact_verification_map_names_exists',
            'basis' => 'the eleven items MD-S003 names for exact publication verification -- frozen observations, temporal revisions, config, factors, formulas, artifacts, hashes, manifest, seal, reasons, terminal state -- are each mapped to an executing guard in this class, and the map is checked against the contract line parsed from the document rather than transcribed, so an item added with nothing verifying it fails. Observations, temporal revisions, config, factors and formulas are covered by the frozen-input perturbations added for MD-S050-R0002; artifacts, hashes, seal, reasons and terminal state by the assertion-class perturbations; manifest by the fixture whose manifest declares a file it does not carry, which is refused outright. The negative guard asserts every guard the map names still exists, so a rename empties nothing silently. Probe: removing seal from the map turned the positive red.',
        ],
        'MD-S019-R0074' => [
            'positive' => 'B18ReplayComparisonExhaustivenessTest::test_a_divergence_in_any_frozen_input_denies_pass',
            'negative' => 'B18AsKnownTemporalSequenceTest::test_a_suspension_lifted_later_is_still_suspended_as_known_before_the_lift_was_recorded',
            'basis' => 'the predicate has three clauses and each is executed. Publication replay freezes the exact identities: the eleven frozen inputs are now compared field by field, so a replay running against any identity other than the one the fixture records as frozen is denied -- that is the clause that had no enforcement at all before MD-S050-R0002. As-known replay resolves only revisions known by the declared cutoff: the negative guard reads one trade date at two cutoffs and with none, and the answer changes as knowledge accrues rather than being fixed or walled off. Current state must not leak into either mode: in publication replay a divergence toward the current registry, build or serialization identity is exactly what the frozen-input comparison catches, and in as-known mode the same guard asserts the current answer and the as-known answer genuinely differ on the fixture, so a current-state substitution is detectable rather than coincidentally right.',
        ],

        // ---- MD-S050-R0017, the anti-future list. Two of its nine items were covered only by a reflection
        // check that a cutoff parameter exists, which an ignored parameter passes.
        'MD-S050-R0017' => [
            'positive' => 'B18AntiFutureResolutionTest::test_the_anti_future_map_names_exactly_what_the_contract_names',
            'negative' => 'B18AntiFutureResolutionTest::test_every_anti_future_guard_exists_and_is_executable',
            'basis' => 'the nine items the anti-future sentence names are each bound to a guard that executes the prohibition, and the map is checked against the sentence parsed from Replay_Verification_Contract_LOCKED.md rather than transcribed, so an item added with nothing behind it fails. Seven already had executing guards across B18AntiSurvivorshipFixtureCorpusTest, AsKnownReplayBoundaryTest and B18AsKnownSnapshotIsolationTest. Two did not and are new: current sector and latest provider mapping were covered only by test_every_temporal_root_accepts_a_knowledge_cutoff, which reflects over the method signature -- a cutoff parameter that is accepted and ignored passes it. Both now run against real repositories: a sector reclassification effective from the dataset start but recorded in May resolves A1 at an April cutoff and B2 without one; a provider remapping modelled as the schema expects, by retracting the original when the replacement is learned, resolves ANTIF.JK at the April cutoff and ANTIF.KL without one, exercising both the recorded_at and retracted_at knowledge-time filters. Two live mappings over one trade date was the fixture\'s first shape and the resolver refused it as PROVIDER_SYMBOL_MAPPING_AMBIGUOUS, which is the fail-closed rule rather than a fixture error. Probes: dropping the sector recorded_at filter turned the sector guard red; widening the provider mapping recorded_at bound turned the mapping guard red, failing closed on ambiguity rather than silently serving the later mapping.',
        ],

        // ---- MD-S050-R0032. The pre-existing evidence guard only ever exported replays that matched, so the
        // mismatch and distribution halves of the sentence had nothing to survive.
        'MD-S050-R0032' => [
            'positive' => 'B18ReplayEvidenceSelfExplanationTest::test_every_preserved_item_survives_the_export_of_a_failed_replay',
            'negative' => 'B18ReplayEvidenceSelfExplanationTest::test_a_matching_replay_exports_the_same_structure_with_no_mismatches',
            'basis' => 'new. B18ReplayEvidencePreservationContractTest exports real evidence but asserts the MD-S040 list from a different contract, and every replay it exports has comparison_result EXPECTED_DEGRADE with a null mismatch_summary -- so the two items MD-S050-R0032 names that the other list does not, mismatch paths and reason distributions, are empty in every export it produces, and asserting they survived would have asserted that null survived. This class exports a replay that actually failed. The eight items of the R0032 sentence are each mapped to an exported path and asserted present and non-empty, with the map checked against the sentence parsed from the contract so an item added with nothing behind it fails. The final clause -- without requiring a mutable database as the primary explanation -- shapes the rest: every exported mismatch is asserted to name its field, both values and a reason code, and the mismatch count is asserted to agree with the number of paths; the reason distribution is asserted to carry a positive count per code, because a list of codes without counts is not a distribution. The negative guard exports a MATCH and asserts the mismatch block is legitimately empty while every non-divergence item is still present, so a PASS export is not a thinner artifact and the rule is not satisfied by an exporter that refuses to write passing replays. Probes: emptying the exported mismatches turned two red while all three B18ReplayEvidencePreservationContractTest guards stayed green, which is the demonstration that the pre-existing guard never covered this; stripping reason_count turned the distribution guard red; dropping timestamps from the reviewed map turned the contract-map guard red.',
        ],

        // ---- MD-S050-R0005. The mutates half was covered; the impersonates half -- a result that reads like
        // a publication replay without having written anything -- was not.
        'MD-S050-R0005' => [
            'positive' => 'B18AsKnownModeIsolationTest::test_an_as_known_result_claims_no_publication_and_records_its_own_mode',
            'negative' => 'B18AsKnownModeIsolationTest::test_an_as_known_result_diverging_from_its_own_fixture_is_a_mismatch',
            'basis' => 'new, and end to end: every repository and both as-known services are real, because AsKnownReplaySnapshotService is final and cannot be stubbed and standing in for the persistence layer would mean asserting a fixture. AsKnownReplayExecutionServiceTest already covered the mutates half by asserting no row is added to eod_runs, eod_publications or eod_bars; the impersonates half is a different failure and had no guard, since a run that writes nothing can still store a result that reads like a publication replay. The stored metric is asserted to record AS_KNOWN, the cutoff it was taken as of, source as_known_replay, and a null publication_id; a sealed publication with its pointer and history rows is present throughout and its full fingerprint is asserted unchanged while a replay row that did not exist before appears; and deterministic_fields_checked is asserted to name the as-known snapshot and canonical output while naming none of bars_batch_hash, seal_state, publication_version or is_current_publication -- which is what may differ from a historical publication rests on, since as-known is judged against its own expectation and differing from the publication can never become a failure. A further guard asserts a PUBLICATION_EXACT fixture handed to the as-known verifier is refused with REPLAY_MODE_MISMATCH. The negative guard perturbs the fixture snapshot hash and asserts MISMATCH/FAIL naming as_known_snapshot_hash, so may differ from a historical publication is not may differ from anything. Probes: making the as-known metric carry the run publication id turned the impersonation guard red; adding seal_state to the compared field list turned the comparison-surface guard red.',
        ],

        // ---- MD-S019-R0009. Invariant 1 is a conjunction over three hashes; the rerun guard perturbed one.
        'MD-S019-R0009' => [
            'positive' => 'B18ReplayRerunDeterminismTest::test_each_batch_hash_is_individually_load_bearing_across_a_rerun',
            'negative' => 'B18ReplayRerunDeterminismTest::test_an_unchanged_rerun_produces_byte_identical_artifacts',
            'basis' => 'the antecedent is Invariant 1 -- identical semantic content and bindings imply bars_batch_hash, indicators_batch_hash AND eligibility_batch_hash are each identical -- and R0009 adds that it holds across reruns and replay. The pre-existing rerun guard proves the identical direction for the whole artifact set at once and perturbs only bars_batch_hash, so it does not establish that the three are individually load-bearing, which is what makes the conjunction a claim rather than a list. Each of the three is now perturbed separately across a real double export and asserted to move replay_result.json, with the table checked against the Invariant 1 block parsed from Determinism_Invariants_LOCKED.md so a fourth hash added there fails rather than going unchecked. The negative guard is the unchanged rerun asserting byte-identical artifacts by sha256, so the pair is not satisfied by an exporter whose output varies freely. Probe: the eligibility hash reaches replay_result.json by four independent paths -- the top-level field, the replay-metric publication artifact lineage, the run-derived lineage, and the resolution-context lineage -- and neutering fewer than all four left the guard green; constanting all four turned exactly the eligibility data set red. Dropping indicators from the reviewed table turned the contract-map guard red.',
        ],

        // ---- MD-S003-R0023, the per-run recording obligation.
        'MD-S003-R0023' => [
            'positive' => 'B18ReplayEvidenceSelfExplanationTest::test_every_item_the_contract_requires_recording_is_present',
            'negative' => 'B18ReplayEvidenceSelfExplanationTest::test_every_frozen_identity_is_recorded_individually',
            'basis' => 'the ten items of the MD-S003 per-run evidence line are each mapped to the exported path that records them, with the map checked against the line parsed from the document so an item added there fails rather than going unrecorded. Nine are asserted on a real export of a failed publication replay; the knowledge cutoff is asserted on an AS_KNOWN export instead, because a PUBLICATION_EXACT result records it as null by design -- it is pinned to an immutable publication rather than to a moment of knowledge, which is the contract\'s own two-mode split rather than a gap. The negative guard is the one that makes all in all frozen revision/snapshot IDs load-bearing: the twelve identities are asserted present and non-empty individually, so a bound-input block carrying one identity cannot satisfy a check that the block exists. Probes: removing formula_registry_hash from the recorded block turned the individual-identity guard red; dropping the knowledge cutoff from the reviewed map turned the contract-map guard red.',
        ],

        // ---- MD-S004 point-in-time input contract. Both rows were PARTIAL because one member of each list
        // had a guard and the binding was filed as though that settled the rest.
        'MD-S004-R0003' => [
            'positive' => 'B18PointInTimeInputContractTest::test_the_no_backfill_map_names_exactly_what_the_contract_names',
            'negative' => 'B18PointInTimeInputContractTest::test_every_guard_both_maps_name_exists_and_is_executable',
            'basis' => 'the five things today may not backfill into an earlier decision are each bound to a guard that executes the prohibition against a real surface: universe and symbol to the anti-survivorship corpus, sector to the knowledge-time reclassification guard added for MD-S050-R0017, action verification to the as-known corporate-action boundary, and current publication to the blocked replay that is asserted to resolve only the fixture-named publication and never a current-pointer selector. The map is checked against the sentence parsed from Point_In_Time_Backtest_Input_Contract_LOCKED.md rather than transcribed, so a member added there fails. The recorded basis for this row was that no-backfill was asserted for identity only; the other four now execute. A further guard asserts the eight members across this row and MD-S004-R0005 resolve to at least five distinct guards, so a family cannot be bound as though it were a predicate. Probes: dropping sector from the map turned the contract-map guard red; renaming a named guard turned the existence guard red.',
        ],
        'MD-S004-R0005' => [
            'positive' => 'B18PointInTimeInputContractTest::test_the_survivorship_map_names_exactly_what_the_contract_names',
            'negative' => 'B18PointInTimeInputContractTest::test_the_members_are_not_all_bound_to_a_single_guard',
            'basis' => 'three claims, parsed as three sentences rather than a comma list, each bound to an executing guard: inactive/delisted securities remaining present to the delisted-listing universe fixture, symbol changes and reuse resolving through listing IDs to the reused-symbol-text fixture, and late corrections producing a distinct later-known dataset without rewriting the earlier one to the as-known snapshot guard, which asserts the earlier cutoff still produces a byte-identical snapshot hash after a later cutoff has exposed new revisions. The negative guard is the anti-collapse check: eight members across this row and MD-S004-R0003 must resolve to at least five distinct guards, which is the shape F-MD-B19-A001-002 records and the reason this row was PARTIAL. Probe: renaming a named guard turned the existence guard red.',
        ],

        // ---- MD-S004-R0002, the cutoff-bounded input set.
        'MD-S004-R0002' => [
            'positive' => 'B18PointInTimeInputContractTest::test_the_cutoff_bounded_input_map_names_exactly_what_the_contract_names',
            'negative' => 'B18PointInTimeInputContractTest::test_every_cutoff_bounded_input_guard_exists',
            'basis' => 'the eight input kinds a decision may contain -- observations, identity, calendar, status, event, factor, config, formula -- are each bound to a guard that executes the cutoff bound against a real repository, and the map is checked against the knowledge-time sentence parsed from Point_In_Time_Backtest_Input_Contract_LOCKED.md so a kind added there fails. The recorded basis for this row was that cutoff-bounded inputs were asserted for identity only; calendar and status are now the knowledge-time sequence guards written for MD-S041-R0032 and MD-S058-R0069, which prove the cutoff is a filter rather than a wall in both directions, and factor and formula are the as-known snapshot guard, which was rewritten to use real repositories under F-MD-B18-A002-001 after its mocks were found to be making the cutoff decision themselves. A cutoff honoured by seven of eight kinds leaks, which is why the existence guard is the negative rather than a convenience. Probe: dropping status from the map turned the contract-map guard red.',
        ],

        // ---- MD-S004-R0004, what every row and export binds.
        'MD-S004-R0004' => [
            'positive' => 'B18ReplayEvidenceSelfExplanationTest::test_every_required_binding_is_present_in_the_export',
            'negative' => 'B18ReplayEvidenceSelfExplanationTest::test_the_availability_timestamp_is_a_separate_field_from_the_trade_date',
            'basis' => 'the eight bindings the required-input-identity sentence names are each mapped to an exported path and asserted present in a real export, with the map checked against the sentence parsed from Point_In_Time_Backtest_Input_Contract_LOCKED.md so a binding added there fails. The recorded basis for this row was that listing identity, effective date and read-model binding were not enforced: listing identity is now the temporal identity hash in the bound-input block, the effective date is trade_date_effective, and read_model_version is asserted individually rather than as part of a block. The knowledge cutoff is taken from an AS_KNOWN export for the same reason as MD-S003-R0023 -- a publication replay is pinned to an immutable publication rather than a moment of knowledge and records it null by design. The negative guard is the final clause of the predicate rather than a convenience: the availability timestamp and the market trade date must be separate fields, since collapsing them is how a backtest silently gains foresight. Probes: removing read_model_version from the export turned the binding guard red; dropping lineage from the reviewed map turned the contract-map guard red.',
        ],

        // ---- MD-S002 release-candidate criteria. These are family-level claims, so a family-level corpus
        // guard is semantically identical to the predicate -- the opposite of the case F-MD-B19-A001-002
        // records, where the predicate was one behaviour. MD-S002-R0004 is deliberately not here: runtime,
        // locale and concurrency determinism needs more than one runtime and this environment has one.
        'MD-S002-R0003' => [
            'positive' => 'B18ReleaseCandidateCriteriaTest::test_the_exact_publication_mismatch_corpus_is_complete',
            'negative' => 'B18ReplayComparisonExhaustivenessTest::test_the_unperturbed_fixture_passes',
            'basis' => 'the eight mismatch classes the criterion names -- value, null-reason, lineage, config, factor, hash, seal, publication -- are each perturbed individually against a real publication fixture and asserted to deny PASS, by the exhaustiveness table built for MD-S050-R0029 and MD-S050-R0002, and a companion test asserts the table covers every class the contract names. This criterion is a family-level claim, so a family-level corpus guard is semantically identical to it, which is the distinction F-MD-B19-A001-002 turns on: a family guard is not proof when the predicate is one behaviour, and is proof when the predicate is the family. The negative guard is the unperturbed fixture reaching PASS, so zero unexplained mismatches is not satisfied by a comparison that fails everything. Probe: renaming a named guard turned the per-criterion corpus test red on its own, not only the shared existence test.',
        ],
        'MD-S002-R0005' => [
            'positive' => 'B18ReleaseCandidateCriteriaTest::test_the_anti_survivorship_and_as_known_isolation_corpus_is_complete',
            'negative' => 'B18ReleaseCandidateCriteriaTest::test_every_guard_named_by_any_criterion_exists',
            'basis' => 'both corpora the criterion names are enumerated from their own contracts and asserted complete and executable: the eight MD-S050 anti-survivorship fixtures through their contract-parsed map, the seven MD-S003 later-revision kinds through theirs, and the nine MD-S050 anti-future items through the map added for MD-S050-R0017. The recorded basis for this row was that the guard executed identity-cutoff invisibility only; the corpus now spans identity, symbol, symbol reuse, calendar, status, event, factor, config, sector and provider mapping. That every member is green is established by the suite run recorded with the stage evidence rather than by a test asserting it about itself, which would be circular. Probe: rewording a criterion away from the contract turned the map guard red; renaming a member guard turned both the corpus test and the existence test red.',
        ],
        'MD-S002-R0006' => [
            'positive' => 'B18ReleaseCandidateCriteriaTest::test_the_degraded_and_negative_corpus_is_complete',
            'negative' => 'EmptyDatasetFailSafeTest::test_no_fallback_fails_the_run_outright',
            'basis' => 'the degraded corpus is enumerated and asserted executable: the four MD-S003 observation defects with the outcome each proves, the held-versus-failed pair that produces the expected states through the real FinalizeDecisionService, and the provider outage that must remain in the as-known observation manifest -- which is the denominator-shrinkage half, since an outage that vanished would shrink the expected count rather than being reported. The recorded basis was that one degraded case was proven to stop with an error; the corpus now covers held, failed and unavailable outcomes and both permitted defect outcomes. The negative guard fails a run outright when no fallback exists, so producing expected states is not satisfied by an implementation that always holds. Probe: renaming a member guard turned the corpus test red.',
        ],
        'MD-S002-R0007' => [
            'positive' => 'B18ReleaseCandidateCriteriaTest::test_the_independent_oracle_corpus_is_complete',
            'negative' => 'B18LongChainWilderAtrOracleTest::test_the_fixture_true_range_is_not_constant',
            'basis' => 'both halves of the criterion now have executed oracle comparisons. The long-chain ATR half is the 200-session varied-true-range chain compared against a recursion transcribed from EOD_Indicators_Formula_Spec.md, plus the correction thirty sessions back asserted at its exact Wilder-decayed magnitude -- written for MD-S003-R0015 because the pre-existing ATR oracle ran on a constant-true-range ramp and could not detect a wrong recursion. The corporate-action half is the verified-revision factor activation and the structural OHLC coherence guards written for MD-S003-R0012 and R0013. The negative guard asserts the ATR fixture\'s true range genuinely varies, which is what makes the long chain an oracle comparison rather than a restatement of the constant case. Probe: renaming an oracle guard turned the corpus test red independently of the shared existence test.',
        ],
        'MD-S002-R0008' => [
            'positive' => 'B18ReleaseCandidateCriteriaTest::test_the_corrected_publication_corpus_is_complete',
            'negative' => 'PublicationSealPointerLifecycleTest::test_the_pointer_table_structurally_refuses_a_second_current_row',
            'basis' => 'preserving predecessors is the superseded publication keeping its own rows unchanged and being refused for discard with SEALED_PUBLICATION_IMMUTABLE, on a fixture carrying two sealed publications for one date. Switching atomically is the pointer table refusing a second current row for a date by primary key, so no window exists in which two publications are current, together with the read path returning nothing rather than stale rows when the projection disagrees with the pointer -- a partial switch is therefore visible as a refusal instead of as whichever version a reader reached first. Probe: renaming a member guard turned the corpus test red.',
        ],

        // ---- MD-S004-R0008, the acceptance fixture floor.
        'MD-S004-R0008' => [
            'positive' => 'B18PointInTimeInputContractTest::test_the_acceptance_fixture_map_names_exactly_what_the_contract_names',
            'negative' => 'B18PointInTimeInputContractTest::test_every_acceptance_fixture_guard_exists_and_they_are_distinct',
            'basis' => 'the seven acceptance fixtures the contract requires at minimum are each bound to a guard that executes that scenario against a real surface: the delisted-listing universe fixture, the reused-symbol-text fixture, the as-known corporate-action boundary, the knowledge-time suspension sequence, the no-fallback run that fails outright, the fallback run that holds with the prior effective date, and the two-publication read-path fixture. The map is parsed from the acceptance-fixtures sentence rather than transcribed, so a fixture added there fails. The recorded basis was that the guard executed none of the seven as fixtures. The negative guard is the anti-collapse check as well as the existence check: at minimum prove is a floor over seven distinct scenarios, so they are asserted to resolve to at least six distinct guards -- a binding pointing them all at one test would satisfy the map while proving one of them. Probe: dropping explicit stale fallback from the map turned the contract-map guard red.',
        ],

        // ---- MD-S082-R0218. Two sentences, one guard each; the registry-leak half only became provable
        // once the frozen-input comparison existed.
        'MD-S082-R0218' => [
            'positive' => 'B18ReplayComparisonExhaustivenessTest::test_a_divergence_in_any_frozen_input_denies_pass',
            'negative' => 'B18AsKnownModeIsolationTest::test_an_as_known_result_claims_no_publication_and_records_its_own_mode',
            'basis' => 'two sentences, one guard each. Current registry state must never leak into historical replay: formula_registry_hash and reason_registry_hash are two of the eleven frozen inputs now compared field by field, so a publication replay resolving today\'s indicator registry or today\'s reason registry instead of the one frozen with the publication is denied and the mismatch names which registry moved. That comparison did not exist before MD-S050-R0002 -- the identities were written into every stored result and compared by nothing -- which is why this row\'s recorded basis said registry state generally was not asserted. Alternate-scenario runs are explicitly labeled and cannot impersonate the historical publication: an as-known result is asserted end to end to record AS_KNOWN and its cutoff, to carry a null publication_id, and to leave the sealed publication, its pointer and its history rows byte-identical while producing a replay row of its own. Probes: comparing an empty frozen-input list turned the registry perturbations red along with the other nine; making the as-known metric carry the run publication id turned the labelling guard red.',
        ],

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
        'MD-S065-R0003' => [
            'positive' => 'B18ConfigEffectiveTimeSelectionTest::test_an_output_affecting_change_produces_a_new_configuration_identity',
            'negative' => 'B18ConfigEffectiveTimeSelectionTest::test_an_unchanged_configuration_does_not_mint_a_new_contract',
            'basis' => 'two claims, both executed against the real MarketDataConfigSnapshotRepository. An output-affecting config change is treated as a contract change: changing the ROC lookback, which changes a published indicator value, is asserted to produce a distinct config_hash and a new snapshot id, and the previous snapshot row is asserted byte-identical afterwards -- so anything already citing the old identity still means what it meant rather than silently acquiring the new semantics. Reruns use the registry version effective for the requested trade date: that is the effective-time selection proven for MD-S082-R0216, where two approved configurations govern from 1 March and 1 April and a run for 24 March selects the March one rather than the newest. The negative guard is the one that makes the identity claim mean anything: resolving twice without changing the configuration reuses the governing snapshot rather than minting a contract per run, so a new identity signals that the configuration changed and not that a run happened. Probe: making the resolver reuse the governing snapshot regardless of hash turned the positive red.',
        ],

        // ---- MD-S082-R0224/R0225, before-seal validation items 5 and 6. They were not in the same state:
        // item 5 was already enforced by a chain and needed proving, item 6 was enforced by nothing.
        'MD-S082-R0224' => [
            'positive' => 'B18BeforeSealValidationTest::test_a_candidate_without_its_lineage_binding_cannot_seal',
            'negative' => 'B18BeforeSealValidationTest::test_a_completely_bound_candidate_seals',
            'basis' => 'the property is enforced before seal by a chain, and the guard proves the chain rather than adding a check to it. The load-bearing link is asserted directly: deleting the publication lineage binding makes sealCandidatePublication refuse with DATASET_MANIFEST_INVALID naming publication_lineage_binding, and the candidate stays UNSEALED and therefore correctable. The other end is asserted too: PublicationGovernanceBindingService, the only writer of that binding, refuses with CONFIG_SNAPSHOT_NOT_FOUND for a run whose configuration snapshot does not exist, so the binding cannot come into being for an unbound configuration. A sealed publication therefore always carries a frozen configuration and its replay never asks the live environment what the configuration was. A separate guard shows why before seal is the operative word: after sealing, assertPublicationMutable refuses with SEALED_PUBLICATION_IMMUTABLE, so a binding missing at seal can never be added. The negative guard is the completely bound candidate sealing, so the refusals are caused by the one thing each test removes rather than by an incomplete fixture. A null check was written into the seal path first and then removed: its red state could only be produced by hand-writing a lineage row that production cannot emit, and a guard whose failure mode is unreachable proves nothing.',
        ],
        'MD-S082-R0225' => [
            'positive' => 'B18BeforeSealValidationTest::test_a_configuration_recorded_after_the_run_cutoff_is_refused_before_seal',
            'negative' => 'B18BeforeSealValidationTest::test_a_configuration_recorded_exactly_at_the_cutoff_still_seals',
            'basis' => 'code change. Nothing anywhere compared the frozen configuration\'s recorded_at against the run\'s knowledge_cutoff_at, so a publication could freeze a configuration recorded after its own knowledge boundary -- an ordinary outcome for a run whose cutoff is 18:00 that resolves configuration at 18:05. As-known replay at that cutoff can never see it: resolveAsKnown either resolves an older snapshot, silently a different configuration than the one frozen, or refuses with CONFIG_SNAPSHOT_NOT_KNOWN_AT_CUTOFF, so the publication is unreproducible the moment it is sealed. EodPublicationRepository::assertReplayDeterminismBeforeSeal() was added for that one comparison and runs inside sealCandidatePublication(); the refusal names both the revision time and the cutoff it was measured against, and the candidate stays UNSEALED. The negative guard pins the boundary as inclusive -- a configuration recorded exactly at the cutoff was knowable and seals -- so the rule is not satisfied by refusing anything recorded near the cutoff. The other way the boundary goes missing, a run with no cutoff at all, is asserted at its real enforcement point: the lineage binder refuses with RUN_KNOWLEDGE_CUTOFF_MISSING. Probes: removing the call from the seal path turned the positive red; changing the comparison to >= turned the negative red.',
        ],

        // ---- MD-S050-R0041. The citation rule is enforceable at the evidence pack, where a claim citing
        // replay evidence is materialised. MD-S050-R0040 was held back here while F-MD-B18-A002-002 stood;
        // that finding is resolved and R0040 is bound at the end of this map.
        'MD-S050-R0041' => [
            'positive' => 'B18ReplayEvidenceSelfExplanationTest::test_a_publication_pack_is_not_held_to_the_as_known_requirements',
            'negative' => 'B18ReplayEvidenceSelfExplanationTest::test_an_unmoded_result_is_not_admitted_as_citable_evidence',
            'basis' => 'the exported evidence pack is where a claim citing replay evidence is actually materialised, so it is where the citation rule is enforceable, and both halves are asserted against a real export. A publication pack and an as-known pack are asserted to require disjoint section sets: the as-known pack must account for the knowledge cutoff and the nine revision identities resolved under it, the publication pack for publication and pointer context and neither is required to account for the other\'s -- so a publication pack has never been examined for the property a point-in-time claim rests on, and citing it for one cites evidence that was not assessed against the question. The negative guard is the unmoded half: MD-S050-R0035 made the mode mandatory on write, and this is the read side for a corpus predating that constraint -- an unmoded result exports as ADMITTED_INCOMPLETE with EVIDENCE_ADMISSION_INCOMPLETE and a missing section naming the unclassified state, rather than quietly defaulting the pack to publication replay. A third guard asserts every admitted pack both requires and carries its mode. Probes: removing the unclassified marker turned the negative red; adding knowledge_cutoff_at to the publication pack\'s required sections turned the positive red with the interchangeability message.',
        ],

        // ---- MD-S003-R0025. The mirror half was proven and the MariaDB half by nothing: every DB-backed
        // guard swaps to an in-memory SQLite connection. Both substrates now run the six required families.
        'MD-S003-R0025' => [
            'positive' => 'B18ScenarioFamiliesOnMariaDbTest::test_the_family_map_names_exactly_the_families_the_contract_requires',
            'negative' => 'B18ScenarioFamiliesOnMariaDbTest::test_these_scenarios_really_run_on_mariadb',
            'basis' => 'new substrate. The mirror half was already true -- every DB-backed market-data guard runs on the SQLite mirror -- and the MariaDB half was true of nothing: all 91 of those guards swap database.default to an in-memory SQLite connection, so no family had ever been resolved against the engine production uses. UsesMarketDataMariaDb was added: it points at the migrated tradeaxis_testing schema rather than a hand-maintained mirror definition, wraps each test in a transaction it rolls back so the shared database keeps no residue, and skips rather than fails when MariaDB is unreachable, because an unavailable environment is not a proof failure. All six MD-S003 required scenario families now execute against MariaDB through the same repositories and services the mirror guards use, and the family map is parsed from the contract headings so a family added to MD-S003 with nothing exercising it on MariaDB fails rather than leaving all required scenario families meaning whichever six were written down. The negative guard asserts the substrate itself -- driver mysql, version containing MariaDB, and the expected database name -- because a class that silently ran on the mirror would prove the opposite of what it claims. Two production constraints the mirror cannot enforce surfaced while writing the fixtures and are recorded rather than absorbed: eod_current_publication_pointer.updated_at is NOT NULL with no default on MariaDB, and the pointer carries a foreign key to eod_publications which the mirror creates with foreign_key_constraints disabled, so referential integrity in the pointer/publication chain is enforced by production and by nothing in the mirror. Probes: pointing the trait at SQLite makes all nine tests skip rather than pass, so the class cannot silently claim MariaDB; removing the knowledge-time bound from the status supersession join turned the temporal identity family red on MariaDB, and removing it from the calendar query turned the as-known isolation family red, so the scenarios exercise the behaviour rather than merely connecting. One pending additive migration, AddReplayV2BoundInputContext, was applied to tradeaxis_testing to bring its schema current; a family proven against a stale schema would be proven against the wrong tables, which the trait now refuses by skipping.',
        ],

        // ---- MD-S050-R0040. The classification the row opens with, made checkable: the eight contract cases
        // are each bound to a cutoff-decided fixture and to the resolver it drives. F-MD-B18-A002-002 named the
        // two that could not be as-known fixtures; both were closed here rather than carried as a capability gap.
        'MD-S050-R0040' => [
            'positive' => 'B18AntiSurvivorshipFixtureCorpusTest::test_every_required_fixture_is_also_an_as_known_fixture',
            'negative' => 'B18CorrectionReadPathScenarioTest::test_two_sealed_publications_with_no_supersession_link_are_refused',
            'basis' => 'schema change plus code change. The row opens with a classification -- the eight anti-survivorship fixtures required below are as-known fixtures -- and six of the eight were; F-MD-B18-A002-002 recorded the other two as unable to be, because the facts they turn on carried no knowledge time at all. Both were closed rather than left as a capability gap. Fixture 1, a listing active at historical T but inactive today: md_listings.delisted_date was a mutable column with no recorded_at of its own, so a cutoff could not tell delisted from delisted-but-not-yet-known, which is survivorship bias reintroduced by the query. Migration AddListingDelistingKnowledgeTime adds a nullable delisted_recorded_at with an index and backfills existing delisted rows from their recorded_at, and TemporalIdentityRepository::baseIdentityQuery now applies the delisting only when it was recorded at or before the cutoff. One column rather than a revision series was deliberate: listing_id is the primary key and listing_uid is unique, so a superseding listing revision would fracture listing identity, which is the thing the anti-survivorship corpus exists to keep intact. Fixture 7, an original and corrected immutable publication: every publication resolver took an explicit id or the current pointer, so which publication would I have been reading at T was not expressible and a correction sealed later silently became the answer for a moment that predated it. EodEvidenceRepository::resolvePublicationAsKnownAt() was added and resolves by the declared supersession chain -- the candidates are the publications sealed at or before the cutoff, the answer is the one nothing sealed by then supersedes. It is not ordered by recency: ReadPathShortcutProhibitionTest bans ORDER BY publication_id DESC from the consumer read repositories because the newest row wins is a guess dressed as an answer, and a first version of this method that carried that ordering as a tiebreaker was rejected by that guard and rewritten rather than exempted. Two sealed publications that do not name each other are refused with EVIDENCE_AS_KNOWN_PUBLICATION_AMBIGUOUS, which is the negative guard: a replay reading the wrong half of a correction pair is worse than a replay that stops. The classification itself is now checkable rather than asserted. A reviewed map binds each of the eight contract cases, parsed from MD-S050 rather than transcribed, to the guard that decides it by a cutoff and to the cutoff-bounded runtime resolver that guard drives, and asserts the guard exists, the resolver exists, and the guard calls it with the argument the cutoff occupies. The arity is load-bearing because every one of these resolvers answers both questions -- readProjectedUniverseAsOf(tradeDate) is the effective-time read and readProjectedUniverseAsOf(tradeDate, knownAt) the as-known one -- so naming the method would have let the map be repointed at the effective-time fixture sitting beside it and stay green. Probes: repointing case 1 at the effective-time fixture turns the classification guard red naming the missing cutoff argument; removing the knowledge-time clause from the delisting filter turns both new listing fixtures red; on the publication resolver, dropping the sealed_at bound makes the correction visible to the earlier cutoff and makes a pre-seal cutoff resolve a row, dropping the seal-state filter admits an unsealed candidate, dropping the mandatory-cutoff half of the guard removes the EVIDENCE_SELECTOR_MISSING refusal, and replacing the ambiguity refusal with a fallback turns the negative red. Every probe was reverted by byte copy and the files verified identical by md5. Residue: F-MD-B18-A002-003 stands unchanged -- the SQLite mirror creates its schema with foreign key constraints disabled and mirrors nullability loosely, so the mirror is weaker than production for the pointer and publication chain these fixtures read.',
        ],

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
    public static function outstanding(): array
    {
        $out = [];
        foreach (MarketDataReplayVerificationPredicateMap::PREDICATES as $id => $_entry) {
            if (! isset(self::PROVEN[$id])) {
                $out[] = $id;
            }
        }

        return $out;
    }

    /** @return array<int,string> entries naming a row this stage does not own */
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
