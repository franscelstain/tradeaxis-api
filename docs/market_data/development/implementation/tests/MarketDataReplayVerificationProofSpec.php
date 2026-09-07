<?php
require_once __DIR__.'/MarketDataReplayVerificationTraceabilitySpec.php';

/**
 * `MD-B18` proof map.
 *
 * The first version of this file chose a predicate's proof family with a cascading `strpos()` chain
 * over the rule text, ending in an unconditional catch-all. Measured against the real 121 rows, 23
 * predicates reached a family with no keyword match at all, and earlier branches stole predicates
 * from later ones: `MD-S050-R0028`, a bitemporal as-known resolution rule, was filed under
 * `evidence` because its sentence contains "pass". A family chosen because a sentence happens to
 * contain a word is not the semantic attribution `STRATEGY_IMPLEMENTATION_TRACEABILITY_STANDARD.md`
 * §2 requires, so the map is now explicit and reviewed, as in every other stage of this package.
 *
 * The families also all pointed at `B18ReplayContractStaticGuardTest`, which reads source files and
 * asserts substrings. A probe removed the real `DB::rollBack()` from the as-known execution service
 * and left the identical text in a comment: the static guard stayed green, the runtime guard went
 * red. Behavioural predicates now name guards that execute. See `F-MD-B18-A001-001`.
 *
 * Static assertion is still the right instrument where the predicate itself is about text - what a
 * document may claim, what a migration declares - and `admissibility_boundary` uses it deliberately.
 */
final class MarketDataReplayVerificationProofSpec
{
    public const STAGE = 'MD-B18';
    public const ATTEMPT = 'MD-B18-A001';
    public const BASELINE = 'MD-B18-A001-BL001';
    public const CI = 'CI-MD-B18-A001-001';
    public const EXPECTED_DENOMINATOR = 121;

    /**
     * Explicit `rule_id => family`. Every required row appears exactly once; an unmapped row is a
     * hard failure rather than a default, because a default is how a predicate acquires a proof
     * nobody chose for it.
     *
     * @var array<string,string>
     */
    public const RULE_FAMILIES = [
        // -- mode_admission: replay mode is mandatory, explicit and first-class on every result.
        'MD-S036-R0012' => 'mode_admission',
        'MD-S050-R0001' => 'mode_admission',
        'MD-S050-R0035' => 'mode_admission',
        'MD-S050-R0036' => 'mode_admission',
        'MD-S050-R0041' => 'mode_admission',

        // -- exact_publication: an explicit immutable publication, never latest/current, with the
        //    correction lifecycle and single-publication read that depend on it.
        'MD-S002-R0003' => 'exact_publication',
        'MD-S002-R0008' => 'exact_publication',
        'MD-S003-R0002' => 'exact_publication',
        'MD-S003-R0003' => 'exact_publication',
        'MD-S003-R0004' => 'exact_publication',
        'MD-S003-R0017' => 'exact_publication',
        'MD-S003-R0018' => 'exact_publication',
        'MD-S003-R0019' => 'exact_publication',
        'MD-S003-R0020' => 'exact_publication',
        'MD-S005-R0095' => 'exact_publication',
        'MD-S019-R0009' => 'exact_publication',
        'MD-S050-R0002' => 'exact_publication',
        'MD-S050-R0027' => 'exact_publication',
        'MD-S082-R0216' => 'exact_publication',

        // -- as_known_isolation: resolution bounded by the declared knowledge cutoff, and the
        //    anti-future / anti-survivorship cases that only that boundary can produce.
        'MD-S002-R0005' => 'as_known_isolation',
        'MD-S003-R0021' => 'as_known_isolation',
        'MD-S003-R0022' => 'as_known_isolation',
        'MD-S004-R0002' => 'as_known_isolation',
        'MD-S004-R0003' => 'as_known_isolation',
        'MD-S004-R0005' => 'as_known_isolation',
        'MD-S004-R0008' => 'as_known_isolation',
        'MD-S005-R0096' => 'as_known_isolation',
        'MD-S019-R0074' => 'as_known_isolation',
        'MD-S041-R0032' => 'as_known_isolation',
        'MD-S050-R0005' => 'as_known_isolation',
        'MD-S050-R0017' => 'as_known_isolation',
        'MD-S050-R0019' => 'as_known_isolation',
        'MD-S050-R0020' => 'as_known_isolation',
        'MD-S050-R0021' => 'as_known_isolation',
        'MD-S050-R0022' => 'as_known_isolation',
        'MD-S050-R0023' => 'as_known_isolation',
        'MD-S050-R0024' => 'as_known_isolation',
        'MD-S050-R0025' => 'as_known_isolation',
        'MD-S050-R0026' => 'as_known_isolation',
        'MD-S050-R0028' => 'as_known_isolation',
        'MD-S055-R0025' => 'as_known_isolation',
        'MD-S058-R0069' => 'as_known_isolation',
        'MD-S082-R0217' => 'as_known_isolation',
        'MD-S082-R0218' => 'as_known_isolation',
        'MD-S082-R0225' => 'as_known_isolation',

        // -- bound_inputs: the identities a replay must carry, and the refusal when one is absent.
        'MD-S004-R0004' => 'bound_inputs',
        'MD-S019-R0066' => 'bound_inputs',
        'MD-S019-R0067' => 'bound_inputs',
        'MD-S019-R0068' => 'bound_inputs',
        'MD-S019-R0069' => 'bound_inputs',
        'MD-S019-R0070' => 'bound_inputs',
        'MD-S019-R0071' => 'bound_inputs',
        'MD-S019-R0072' => 'bound_inputs',
        'MD-S019-R0073' => 'bound_inputs',
        'MD-S050-R0007' => 'bound_inputs',
        'MD-S050-R0008' => 'bound_inputs',
        'MD-S050-R0009' => 'bound_inputs',
        'MD-S050-R0010' => 'bound_inputs',
        'MD-S050-R0011' => 'bound_inputs',
        'MD-S050-R0012' => 'bound_inputs',
        'MD-S050-R0013' => 'bound_inputs',
        'MD-S050-R0014' => 'bound_inputs',
        'MD-S050-R0015' => 'bound_inputs',
        'MD-S050-R0016' => 'bound_inputs',
        'MD-S065-R0003' => 'bound_inputs',
        'MD-S082-R0015' => 'bound_inputs',

        // -- temporal_identity: point-in-time listing, symbol and status resolution.
        'MD-S003-R0009' => 'temporal_identity',
        'MD-S003-R0010' => 'temporal_identity',
        'MD-S003-R0011' => 'temporal_identity',

        // -- source_observation: immutable observations and degraded acquisition.
        'MD-S003-R0005' => 'source_observation',
        'MD-S003-R0006' => 'source_observation',
        'MD-S003-R0007' => 'source_observation',
        'MD-S003-R0008' => 'source_observation',
        'MD-S050-R0046' => 'source_observation',

        // -- independent_oracle: the expected side must be independently derived.
        'MD-S002-R0007' => 'independent_oracle',
        'MD-S003-R0015' => 'independent_oracle',
        'MD-S003-R0024' => 'independent_oracle',
        'MD-S050-R0033' => 'independent_oracle',

        // -- corporate_action_and_indicator: factor activation and the fields it must not touch.
        'MD-S003-R0012' => 'corporate_action_and_indicator',
        'MD-S003-R0013' => 'corporate_action_and_indicator',
        'MD-S003-R0014' => 'corporate_action_and_indicator',
        'MD-S003-R0016' => 'corporate_action_and_indicator',

        // -- result_and_evidence: PASS / FAIL / BLOCKED and what the evidence must carry.
        'MD-S003-R0023' => 'result_and_evidence',
        'MD-S036-R0007' => 'result_and_evidence',
        'MD-S036-R0031' => 'result_and_evidence',
        'MD-S040-R0070' => 'result_and_evidence',
        'MD-S040-R0071' => 'result_and_evidence',
        'MD-S040-R0072' => 'result_and_evidence',
        'MD-S040-R0073' => 'result_and_evidence',
        'MD-S040-R0074' => 'result_and_evidence',
        'MD-S040-R0075' => 'result_and_evidence',
        'MD-S040-R0076' => 'result_and_evidence',
        'MD-S040-R0077' => 'result_and_evidence',
        'MD-S040-R0078' => 'result_and_evidence',
        'MD-S040-R0079' => 'result_and_evidence',
        'MD-S040-R0080' => 'result_and_evidence',
        'MD-S050-R0029' => 'result_and_evidence',
        'MD-S050-R0030' => 'result_and_evidence',
        'MD-S050-R0031' => 'result_and_evidence',
        'MD-S050-R0032' => 'result_and_evidence',
        'MD-S085-R0452' => 'result_and_evidence',

        // -- admissibility_boundary: what a replay verdict may and may not be cited for. These
        //    predicates are about claims, so a corpus assertion is the matching instrument.
        'MD-S002-R0009' => 'admissibility_boundary',
        'MD-S002-R0010' => 'admissibility_boundary',
        'MD-S002-R0016' => 'admissibility_boundary',
        'MD-S003-R0031' => 'admissibility_boundary',
        'MD-S004-R0007' => 'admissibility_boundary',
        'MD-S004-R0011' => 'admissibility_boundary',
        'MD-S020-R0014' => 'admissibility_boundary',
        'MD-S050-R0038' => 'admissibility_boundary',
        'MD-S050-R0039' => 'admissibility_boundary',
        'MD-S050-R0040' => 'admissibility_boundary',
        'MD-S050-R0045' => 'admissibility_boundary',
        'MD-S050-R0050' => 'admissibility_boundary',
        'MD-S050-R0051' => 'admissibility_boundary',
        'MD-S050-R0052' => 'admissibility_boundary',
        'MD-S050-R0053' => 'admissibility_boundary',

        // -- determinism_and_operations: repeatability across environments and the operator paths.
        'MD-S002-R0004' => 'determinism_and_operations',
        'MD-S002-R0006' => 'determinism_and_operations',
        'MD-S003-R0025' => 'determinism_and_operations',
        'MD-S050-R0056' => 'determinism_and_operations',
        'MD-S082-R0224' => 'determinism_and_operations',
    ];

    /**
     * Expected size of each family. A silent re-bucketing keeps the total at 121 and would otherwise
     * pass every count check in the gate.
     *
     * @var array<string,int>
     */
    public const FAMILY_EXPECTED_COUNTS = [
        'mode_admission' => 5,
        'exact_publication' => 14,
        'as_known_isolation' => 26,
        'bound_inputs' => 21,
        'temporal_identity' => 3,
        'source_observation' => 5,
        'independent_oracle' => 4,
        'corporate_action_and_indicator' => 4,
        'result_and_evidence' => 19,
        'admissibility_boundary' => 15,
        'determinism_and_operations' => 5,
    ];

    /** @return array<string,array<string,mixed>> */
    public static function families(): array
    {
        return [
            'mode_admission' => [
                'owner' => 'MD-B18:mode-admission',
                'implementation' => [
                    'app/Application/MarketData/Services/ReplayMode.php',
                    'app/Console/Commands/MarketData/VerifyReplayCommand.php',
                ],
                'positive' => ['tests/Unit/MarketData/ReplayModeContractTest.php', 'test_only_the_two_locked_replay_modes_are_accepted'],
                'negative' => ['tests/Unit/MarketData/OpsCommandSurfaceTest.php', 'test_replay_verify_refuses_an_inadmissible_mode_without_attempting_verification'],
            ],
            'exact_publication' => [
                'owner' => 'MD-B18:exact-publication',
                'implementation' => [
                    'app/Application/MarketData/Services/ReplayVerificationService.php',
                    'app/Console/Commands/MarketData/VerifyReplayCommand.php',
                ],
                'positive' => ['tests/Unit/MarketData/ReplayVerificationServiceTest.php', 'test_verify_replay_resolves_historical_publication_without_current_pointer_fallback'],
                'negative' => ['tests/Unit/MarketData/ReplayVerificationServiceTest.php', 'test_verify_replay_maps_unsealed_historical_publication_to_reason_coded_failure'],
            ],
            'as_known_isolation' => [
                'owner' => 'MD-B18:as-known-isolation',
                'implementation' => [
                    'app/Application/MarketData/Services/AsKnownReplaySnapshotService.php',
                    'app/Application/MarketData/Services/AsKnownReplayExecutionService.php',
                    'app/Infrastructure/Persistence/MarketData/TemporalIdentityRepository.php',
                ],
                'positive' => ['tests/Unit/MarketData/AsKnownReplayBoundaryTest.php', 'test_identity_recorded_after_the_cutoff_is_invisible'],
                'negative' => ['tests/Unit/MarketData/AsKnownReplayBoundaryTest.php', 'test_an_as_known_config_resolution_refuses_rather_than_inventing_one'],
            ],
            'bound_inputs' => [
                'owner' => 'MD-B18:bound-inputs',
                'implementation' => [
                    'app/Infrastructure/Persistence/MarketData/ReplayResultRepository.php',
                    'database/migrations/2026_09_04_000001_add_replay_v2_bound_input_context.php',
                ],
                'positive' => ['tests/Unit/MarketData/ReplayResultRepositoryIntegrationTest.php', 'test_replay_result_repository_persists_metric_and_reason_code_counts'],
                'negative' => ['tests/Unit/MarketData/ReplayResultRepositoryIntegrationTest.php', 'test_an_incomplete_bound_input_set_is_refused_rather_than_persisted'],
            ],
            'temporal_identity' => [
                'owner' => 'MD-B18:temporal-identity',
                'implementation' => [
                    'app/Infrastructure/Persistence/MarketData/TemporalIdentityRepository.php',
                ],
                'positive' => ['tests/Unit/MarketData/TemporalIdentityLayerContractTest.php', 'test_point_in_time_resolution_returns_the_full_identity_for_the_trade_date'],
                'negative' => ['tests/Unit/MarketData/TemporalIdentityLayerContractTest.php', 'test_retraction_after_cutoff_does_not_erase_symbol_board_or_provider_mapping_from_as_known_history'],
            ],
            'source_observation' => [
                'owner' => 'MD-B18:source-observation',
                'implementation' => [
                    'app/Infrastructure/Persistence/MarketData/SourceObservationRepository.php',
                ],
                'positive' => ['tests/Unit/MarketData/SourceObservationAsKnownBoundaryTest.php', 'test_as_known_rows_require_both_observation_and_identity_binding_to_be_known_by_cutoff'],
                'negative' => ['tests/Unit/MarketData/SourceObservationAsKnownBoundaryTest.php', 'test_zero_row_provider_outage_remains_in_as_known_observation_manifest'],
            ],
            'independent_oracle' => [
                'owner' => 'MD-B18:independent-oracle',
                'implementation' => [
                    'app/Application/MarketData/Services/ReplayVerificationService.php',
                    'app/Console/Commands/MarketData/GenerateReplayFixtureCommand.php',
                ],
                'positive' => ['tests/Unit/MarketData/ReplayAdmissibilityVerdictStorabilityTest.php', 'test_a_relabelled_self_generated_fixture_is_still_refused'],
                'negative' => ['tests/Unit/MarketData/ReplayAdmissibilityVerdictStorabilityTest.php', 'test_the_inadmissible_verdict_is_never_counted_as_a_pass'],
            ],
            'corporate_action_and_indicator' => [
                'owner' => 'MD-B18:corporate-action-and-indicator',
                'implementation' => [
                    'app/Application/MarketData/Services/AdjustmentFactorSetService.php',
                    'app/Application/MarketData/Services/EodIndicatorsComputeService.php',
                ],
                'positive' => ['tests/Unit/MarketData/ReplayVerificationServiceTest.php', 'test_replay_detects_analytical_factor_set_identity_drift'],
                'negative' => ['tests/Unit/MarketData/CanonicalRawImportBoundaryTest.php', 'test_provider_adjusted_close_never_reaches_the_canonical_row'],
            ],
            'result_and_evidence' => [
                'owner' => 'MD-B18:result-and-evidence',
                'implementation' => [
                    'app/Application/MarketData/Services/MarketDataEvidenceExportService.php',
                    'app/Infrastructure/Persistence/MarketData/ReplayResultRepository.php',
                ],
                'positive' => ['tests/Unit/MarketData/ReplayEvidenceExportServiceTest.php', 'test_export_replay_evidence_writes_replay_result_and_reason_code_summary'],
                'negative' => ['tests/Unit/MarketData/ReplayComparisonDetectsDivergenceTest.php', 'test_missing_expected_proof_is_reported_rather_than_ignored'],
            ],
            'admissibility_boundary' => [
                'owner' => 'MD-B18:admissibility-boundary',
                'implementation' => [
                    'app/Application/MarketData/Services/ReplayVerificationService.php',
                    'app/Infrastructure/Persistence/MarketData/ReplayResultRepository.php',
                ],
                'positive' => ['tests/Unit/MarketData/B18ReplayAdmissibilityBoundaryTest.php', 'test_no_active_surface_cites_a_replay_verdict_for_something_replay_cannot_establish'],
                'negative' => ['tests/Unit/MarketData/B18ReplayAdmissibilityBoundaryTest.php', 'test_every_pattern_matches_the_claim_it_forbids_and_spares_the_denial'],
            ],
            'determinism_and_operations' => [
                'owner' => 'MD-B18:determinism-and-operations',
                'implementation' => [
                    'app/Application/MarketData/Services/FullRangeCurrentEvidenceReplayService.php',
                    'app/Application/MarketData/Services/ReplayBackfillService.php',
                    'app/Application/MarketData/Services/BackfillLifecycleOrchestrator.php',
                ],
                'positive' => ['tests/Unit/MarketData/FullRangeCurrentEvidenceReplayServiceTest.php', 'test_execute_generates_current_publication_evidence_fixture_replay_and_summary'],
                'negative' => ['tests/Unit/MarketData/ReplayBackfillServiceTest.php', 'test_execute_marks_error_and_stops_when_publication_is_missing_and_continue_is_false'],
            ],
        ];
    }

    /** @param array<string,string> $row */
    public static function familyFor(array $row): string
    {
        $id = (string) $row['rule_id'];
        if (! isset(self::RULE_FAMILIES[$id])) {
            throw new RuntimeException('No reviewed MD-B18 proof family for '.$id
                .'. Add it to RULE_FAMILIES after reading the predicate; there is no default.');
        }

        return self::RULE_FAMILIES[$id];
    }

    /** @return array<int,array<string,string>> */
    public static function entries(string $root): array
    {
        return array_map(static function (array $row) {
            return ['rule_id' => $row['rule_id'], 'family' => self::familyFor($row)];
        }, MarketDataReplayVerificationTraceabilitySpec::required($root));
    }
}
