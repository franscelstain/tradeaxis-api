<?php

require_once __DIR__.'/MarketDataSourceResilienceTraceabilitySpec.php';

/** Current MD-B08-A001 proof ownership by semantic resilience family. */
final class MarketDataSourceResilienceProofSpec
{
    /** @return array<string,string> rule id => proof family */
    public static function familyAssignment(): array
    {
        $assignment = [];
        $bind = static function (string $document, array $numbers, string $family) use (&$assignment): void {
            foreach ($numbers as $number) {
                $rule = MarketDataSourceResilienceTraceabilitySpec::ruleId($document, $number);
                if (isset($assignment[$rule])) {
                    throw new RuntimeException('Duplicate B08 proof family for '.$rule);
                }
                $assignment[$rule] = $family;
            }
        };

        $bind('MD-S029', [8, 9, 10, 11, 18, 19, 20, 21, 22, 122], 'import_boundary');
        $bind('MD-S029', [30, 31, 32, 33, 34, 56, 58, 59, 60, 61, 66, 67, 68, 69, 70, 71, 109, 110, 111, 112, 114, 115, 116, 118, 119, 120, 159, 195, 196, 197, 198, 199], 'source_protection');
        $bind('MD-S029', [38, 39, 40, 41, 42, 44, 45, 46, 47, 49, 50, 51, 90, 91, 92, 93, 94, 99, 100, 101, 103, 106, 143, 144, 145, 146, 147, 148], 'source_recovery');
        $bind('MD-S029', [62, 64, 77, 95, 96, 97, 123, 125, 126, 127, 128, 130, 134, 135, 137, 138, 139, 140, 141, 161, 162, 163, 164], 'failure_taxonomy');
        $bind('MD-S029', [16, 151, 152, 153, 154, 155, 157, 158, 167, 168, 169, 171, 173, 174, 175, 176, 177, 178, 181, 182, 183, 185, 188, 190, 191, 192, 208], 'telemetry_traceability');

        $bind('MD-S040', [4, 7, 8], 'source_recovery');
        $bind('MD-S040', [44], 'source_protection');

        $bind('MD-S053', [28, 31, 44, 45, 52, 60, 100], 'source_recovery');
        $bind('MD-S058', [30], 'source_recovery');
        $bind('MD-S058', [52], 'failure_taxonomy');
        $bind('MD-S059', [44], 'source_recovery');
        $bind('MD-S059', [76], 'source_protection');
        $bind('MD-S059', [141], 'telemetry_traceability');
        $bind('MD-S067', [20], 'failure_taxonomy');
        $bind('MD-S085', [447], 'failure_taxonomy');

        ksort($assignment, SORT_STRING);

        return $assignment;
    }

    /** @return array<string,array{surfaces:array<int,string>,methods:array<int,array{0:string,1:string}>}> */
    public static function families(): array
    {
        return [
            'import_boundary' => [
                'surfaces' => [
                    'app/Application/MarketData/Services/MarketDataPipelineService.php',
                    'app/Application/MarketData/Services/EodBarsIngestService.php',
                ],
                'methods' => [
                    ['tests/Unit/MarketData/CanonicalRawImportBoundaryTest.php', 'test_import_creates_a_candidate_without_sealing_or_switching_the_pointer'],
                    ['tests/Unit/MarketData/MarketDataPipelineIntegrationTest.php', 'test_manual_file_import_only_writes_candidate_bars_without_finalize_or_pointer_switch'],
                    ['tests/Unit/MarketData/SourceFailureResilienceTest.php', 'test_a_provider_failure_never_produces_a_readable_publication'],
                ],
            ],
            'source_protection' => [
                'surfaces' => [
                    'app/Infrastructure/MarketData/Source/PublicApiEodBarsAdapter.php',
                    'app/Application/MarketData/Services/ApiBackfillRangeAcquisitionService.php',
                    'app/Application/MarketData/Services/SectorIndexApiIngestService.php',
                ],
                'methods' => [
                    ['tests/Unit/MarketData/SourceCircuitBreakerTest.php', 'test_the_breaker_opens_when_observed_failures_cross_the_configured_threshold'],
                    ['tests/Unit/MarketData/SourceCircuitBreakerTest.php', 'test_the_breaker_stays_closed_exactly_at_the_threshold'],
                    ['tests/Unit/MarketData/SourceCircuitBreakerTest.php', 'test_the_breaker_has_no_implicit_minimum_sample_threshold'],
                    ['tests/Unit/MarketData/SourceCircuitBreakerTest.php', 'test_invalid_breaker_threshold_fails_closed_as_configuration_error'],
                    ['tests/Unit/MarketData/PublicApiEodBarsAdapterTest.php', 'test_api_adapter_uses_the_registered_retry_budget_without_hidden_clamp'],
                    ['tests/Unit/MarketData/PublicApiEodBarsAdapterTest.php', 'test_single_date_circuit_breaker_stops_fanout_and_preserves_registered_root_failure_reason'],
                    ['tests/Unit/MarketData/PublicApiEodBarsAdapterTest.php', 'test_range_circuit_breaker_protects_range_window_fanout'],
                    ['tests/Unit/MarketData/PublicApiEodBarsAdapterTest.php', 'test_benchmark_fanout_uses_the_same_circuit_breaker_and_root_reason_taxonomy'],
                    ['tests/Unit/MarketData/PublicApiEodBarsAdapterTest.php', 'test_api_adapter_raises_auth_error_without_retry'],
                    ['tests/Unit/MarketData/SourceProviderResilienceStaticGuardTest.php', 'test_all_provider_fanout_paths_have_source_protection_and_retry_is_transient_only'],
                    ['tests/Unit/MarketData/SourceProviderResilienceStaticGuardTest.php', 'test_circuit_breaker_uses_only_the_registered_configured_threshold'],
                    ['tests/Unit/MarketData/SourceProviderResilienceStaticGuardTest.php', 'test_source_capability_availability_trigger_is_bound_to_five_expected_sessions'],
                    ['tests/Unit/MarketData/DateDrivenCapabilityAndProviderAbstractionTest.php', 'test_the_import_strategy_may_window_and_retry_while_the_requested_range_still_governs'],
                    ['tests/Unit/MarketData/DateDrivenCapabilityAndProviderAbstractionTest.php', 'test_the_domain_accepts_an_arbitrary_requested_range'],
                    ['tests/Unit/MarketData/YahooPeriodBoundsTest.php', 'test_a_range_far_outside_the_provider_default_window_is_still_expressible'],
                    ['tests/Unit/MarketData/ApiBackfillRangeAcquisitionServiceTest.php', 'test_acquire_uses_window_by_ticker_requests_instead_of_date_by_ticker_requests'],
                ],
            ],
            'source_recovery' => [
                'surfaces' => [
                    'app/Infrastructure/MarketData/Source/LocalFileEodBarsAdapter.php',
                    'app/Application/MarketData/Services/MarketDataPipelineService.php',
                    'app/Infrastructure/MarketData/Source/PublicApiEodBarsAdapter.php',
                ],
                'methods' => [
                    ['tests/Unit/MarketData/ProviderNeutralBoundaryTest.php', 'test_manual_file_is_recovery_and_never_the_default_source_mode'],
                    ['tests/Unit/MarketData/ManualFilePolicyEnforcementStaticGuardTest.php', 'test_manual_file_adapter_keeps_local_identity_and_never_calls_api_provider'],
                    ['tests/Unit/MarketData/LocalFileEodBarsAdapterTest.php', 'test_fetch_or_load_eod_bars_prefers_explicit_manual_input_file_override'],
                    ['tests/Unit/MarketData/LocalFileEodBarsAdapterTest.php', 'test_empty_manual_csv_is_blocked_with_reason_code'],
                    ['tests/Unit/MarketData/MarketDataPipelineServiceTest.php', 'test_complete_ingest_holds_rate_limited_source_when_prior_readable_fallback_exists'],
                ],
            ],
            'failure_taxonomy' => [
                'surfaces' => [
                    'app/Infrastructure/MarketData/Source/PublicApiEodBarsAdapter.php',
                    'app/Application/MarketData/Services/MarketDataPipelineService.php',
                    'app/Infrastructure/Persistence/MarketData/EventRiskSourceRepository.php',
                ],
                'methods' => [
                    ['tests/Unit/MarketData/SourceFailureResilienceTest.php', 'test_a_provider_failure_is_never_silent'],
                    ['tests/Unit/MarketData/SourceFailureResilienceTest.php', 'test_holding_to_a_fallback_date_is_still_not_readable'],
                    ['tests/Unit/MarketData/EmittedReasonCodeRegistrationTest.php', 'test_every_reason_code_emitted_by_the_runtime_is_registered'],
                    ['tests/Unit/MarketData/EmittedReasonCodeRegistrationTest.php', 'test_b08_source_adapter_returned_run_codes_are_scanned_without_widening_downstream_scope'],
                    ['tests/Unit/MarketData/EventRiskSourceRepositoryTest.php', 'test_repository_rejects_unknown_event_type_code'],
                    ['tests/Unit/MarketData/DerivationFillsRecordedActionTest.php', 'test_unexplained_price_break_never_creates_a_synthetic_action'],
                ],
            ],
            'telemetry_traceability' => [
                'surfaces' => [
                    'app/Application/MarketData/Services/MarketDataPipelineService.php',
                    'app/Infrastructure/Persistence/MarketData/EodEvidenceRepository.php',
                    'app/Application/MarketData/Services/MarketDataEvidenceExportService.php',
                    'app/Application/MarketData/Services/MarketDataBackfillService.php',
                    'app/Application/MarketData/Services/SectorIndexApiIngestService.php',
                    'app/Console/Commands/MarketData/AbstractMarketDataCommand.php',
                    'app/Console/Commands/MarketData/IngestSectorIndexBarsApiCommand.php',
                ],
                'methods' => [
                    ['tests/Unit/MarketData/SourceProtectionTelemetryPersistenceTest.php', 'test_circuit_breaker_state_survives_append_only_event_to_evidence_projection'],
                    ['tests/Unit/MarketData/SourceProtectionTelemetryPersistenceTest.php', 'test_source_observation_audit_projects_immutable_hash_schema_and_rejection_reasons'],
                    ['tests/Unit/MarketData/SourceProviderResilienceStaticGuardTest.php', 'test_source_protection_state_is_audit_visible_without_a_second_schema_truth'],
                    ['tests/Unit/MarketData/SourceProviderResilienceStaticGuardTest.php', 'test_resilience_audit_fields_propagate_through_operational_recovery_projections'],
                    ['tests/Unit/MarketData/MarketDataPipelineServiceTest.php', 'test_complete_ingest_holds_rate_limited_source_when_prior_readable_fallback_exists'],
                    ['tests/Unit/MarketData/MarketDataEvidenceExportServiceTest.php', 'test_export_run_evidence_writes_minimum_required_files'],
                    ['tests/Unit/MarketData/MarketDataPipelineIntegrationTest.php', 'test_run_daily_api_success_after_retry_exports_source_context_in_run_evidence'],
                    ['tests/Unit/MarketData/MarketDataPipelineIntegrationTest.php', 'test_run_daily_api_source_timeout_degraded_hold_persists_attempt_context_in_run_event'],
                ],
            ],
        ];
    }

    /** @return array<string,array{surfaces:array<int,string>,methods:array<int,array{0:string,1:string}>}> */
    public static function proofMap(): array
    {
        $families = self::families();
        $map = [];
        foreach (self::familyAssignment() as $rule => $family) {
            if (! isset($families[$family])) {
                throw new RuntimeException('Unknown B08 proof family '.$family.' for '.$rule);
            }
            $map[$rule] = $families[$family];
        }
        ksort($map, SORT_STRING);

        return $map;
    }
}
