<?php

require_once __DIR__.'/MarketDataSourceAcquisitionTraceabilityGate.php';

/** Exact MD-B07-A001 behavioral proof binding and implementation-surface gate. */
final class MarketDataSourceAcquisitionProofGate
{
    public const EVIDENCE = 'E-MD-B07-A001-001';

    public const REMEDIATED_RULES = [
        'MD-S053-R0004' => 'raw, accepted, rejected, failure, and canonical lineage now share immutable observation identities',
        'MD-S053-R0064' => 'observation envelopes now persist run, batch, checkpoint, and requested scope',
        'MD-S053-R0075' => 'accepted and rejected normalized rows now link to their raw capture',
        'MD-S053-R0082' => 'missing payload identity or observation persistence now fails before canonical writes',
        'MD-S053-R0087' => 'Yahoo container and quote-series cardinality are validated before normalization',
        'MD-S053-R0091' => 'returned provider symbol must match the effective requested mapping',
        'MD-S053-R0094' => 'the active Yahoo schema version is code-locked to its released adapter version',
        'MD-S053-R0095' => 'incompatible content, structure, cardinality, and malformed rows produce rejection evidence',
        'MD-S053-R0106' => 'an identical refetch records an explicit confirmation without a finding',
        'MD-S053-R0107' => 'a changed refetch opens a finding with both observations, values, and deltas',
        'MD-S053-R0108' => 'acquisition leaves divergence findings open for downstream correction',
        'MD-S053-R0112' => 'different observations cannot be archived without an explicit comparison finding',
        'MD-S053-R0168' => 'silent refetch divergence is prohibited by transactional comparison persistence',
        'MD-S054-R0013' => 'acquisition resolves effective provider identity for each requested trading date',
        'MD-S054-R0014' => 'OHLCV presence, numeric domain, positivity, and ordering are reason-coded',
        'MD-S054-R0016' => 'provider adjusted close remains observation metadata and never becomes canonical RAW close',
        'MD-S054-R0017' => 'mapping, schema, row, and duplicate failures retain reason-coded observation linkage',
        'MD-S054-R0018' => 'conflicting duplicates are quarantined without captured-at/latest-wins selection',
        'MD-S055-R0024' => 'canonical rows preserve stable listing plus exact observation and mapping revision linkage',
        'MD-S066-R0001' => 'request and payload evidence redact secret query and authorization material',
    ];

    /** @return array<string,array{surfaces:array<int,string>,methods:array<int,array{0:string,1:string}>}> */
    public static function proofMap(): array
    {
        $families = [
            'boundary' => [
                'surfaces' => [
                    'app/Application/MarketData/Ports/ApiEodBarsSource.php',
                    'app/Application/MarketData/Services/EodBarsIngestService.php',
                    'app/Infrastructure/MarketData/Source/PublicApiEodBarsAdapter.php',
                ],
                'methods' => [
                    ['tests/Unit/MarketData/DateDrivenCapabilityAndProviderAbstractionTest.php', 'test_the_acquisition_port_is_provider_neutral_and_date_addressed'],
                    ['tests/Unit/MarketData/DateDrivenCapabilityAndProviderAbstractionTest.php', 'test_the_provider_query_shape_lives_in_the_source_adapter'],
                    ['tests/Unit/MarketData/ProviderNeutralBoundaryTest.php', 'test_application_layer_type_hints_the_port_and_never_the_adapter'],
                    ['tests/Unit/MarketData/MarketDataOrdersOneToFourArchitectureTest.php', 'test_application_services_depend_on_provider_neutral_source_ports'],
                    ['tests/Unit/MarketData/CanonicalRawImportBoundaryTest.php', 'test_import_creates_a_candidate_without_sealing_or_switching_the_pointer'],
                    ['tests/Unit/MarketData/SourceFailureResilienceTest.php', 'test_a_provider_failure_never_produces_a_readable_publication'],
                ],
            ],
            'date_window' => [
                'surfaces' => [
                    'app/Infrastructure/MarketData/Source/PublicApiEodBarsAdapter.php',
                    'app/Application/MarketData/Services/ApiBackfillRangeAcquisitionService.php',
                ],
                'methods' => [
                    ['tests/Unit/MarketData/DateDrivenCapabilityAndProviderAbstractionTest.php', 'test_the_import_strategy_may_window_and_retry_while_the_requested_range_still_governs'],
                    ['tests/Unit/MarketData/DateDrivenCapabilityAndProviderAbstractionTest.php', 'test_the_domain_accepts_an_arbitrary_requested_date'],
                    ['tests/Unit/MarketData/DateDrivenCapabilityAndProviderAbstractionTest.php', 'test_the_domain_accepts_an_arbitrary_requested_range'],
                    ['tests/Unit/MarketData/PublicApiEodBarsAdapterTest.php', 'test_yahoo_finance_range_adapter_groups_multi_date_rows_without_date_fanout'],
                    ['tests/Unit/MarketData/ApiBackfillRangeAcquisitionServiceTest.php', 'test_checkpoint_fails_when_ticker_has_warmup_row_but_missing_requested_trade_date'],
                    ['tests/Unit/MarketData/EodBarsIngestServiceTest.php', 'test_api_ingest_uses_ticker_universe_when_fetching_provider_rows'],
                    ['tests/Unit/MarketData/TradingWindowWarmupTest.php', 'test_the_window_counts_trading_days_not_calendar_days'],
                ],
            ],
            'source_mode' => [
                'surfaces' => [
                    'app/Infrastructure/MarketData/Source/LocalFileEodBarsAdapter.php',
                    'app/Infrastructure/MarketData/Source/PublicApiEodBarsAdapter.php',
                    'app/Application/MarketData/Services/EodBarsIngestService.php',
                ],
                'methods' => [
                    ['tests/Unit/MarketData/ManualFilePolicyEnforcementStaticGuardTest.php', 'test_manual_file_adapter_keeps_local_identity_and_never_calls_api_provider'],
                    ['tests/Unit/MarketData/LocalFileEodBarsAdapterTest.php', 'test_fetch_or_load_eod_bars_prefers_explicit_manual_input_file_override'],
                    ['tests/Unit/MarketData/LocalFileEodBarsAdapterTest.php', 'test_explicit_manual_input_file_can_filter_multi_date_csv_by_requested_date'],
                    ['tests/Unit/MarketData/EodBarsIngestServiceTest.php', 'test_single_day_ingest_rejects_mixed_source_names_within_one_run_boundary'],
                    ['tests/Unit/MarketData/ProviderNeutralBoundaryTest.php', 'test_manual_file_is_recovery_and_never_the_default_source_mode'],
                ],
            ],
            'observation' => [
                'surfaces' => [
                    'app/Application/MarketData/Ports/SourceObservationRecorder.php',
                    'app/Infrastructure/Persistence/MarketData/SourceObservationRepository.php',
                    'database/migrations/2026_08_22_000002_harden_source_observation_acquisition.php',
                    'database/migrations/2026_08_22_000003_add_source_observation_rejected_rows.php',
                ],
                'methods' => [
                    ['tests/Unit/MarketData/SourceObservationImmutabilityTest.php', 'test_a_rerun_appends_a_new_observation_and_never_overwrites'],
                    ['tests/Unit/MarketData/SourceObservationImmutabilityTest.php', 'test_an_identical_payload_still_produces_a_distinct_observation'],
                    ['tests/Unit/MarketData/SourceObservationImmutabilityTest.php', 'test_empty_and_failed_outcomes_are_recorded_with_provenance'],
                    ['tests/Unit/MarketData/SourceObservationImmutabilityTest.php', 'test_secret_material_never_reaches_the_stored_envelope'],
                    ['tests/Unit/MarketData/SourceObservationImmutabilityTest.php', 'test_capture_without_payload_or_verifiable_external_identity_fails_closed'],
                    ['tests/Unit/MarketData/SourceObservationImmutabilityTest.php', 'test_partially_invalid_response_persists_reason_coded_row_evidence_linked_to_the_capture'],
                    ['tests/Unit/MarketData/SourceObservationImmutabilityTest.php', 'test_the_run_manifest_hash_moves_when_an_observation_is_added'],
                    ['tests/Unit/MarketData/MarketDataOrdersOneToFourFoundationTest.php', 'test_source_observation_is_append_only_redacted_and_manifest_bound'],
                    ['tests/Unit/MarketData/MarketDataOrdersOneToFourFoundationTest.php', 'test_acquisition_fails_closed_when_raw_observation_cannot_be_persisted'],
                ],
            ],
            'schema' => [
                'surfaces' => [
                    'app/Infrastructure/MarketData/Source/PublicApiEodBarsAdapter.php',
                    'app/Infrastructure/Persistence/MarketData/SourceObservationRepository.php',
                ],
                'methods' => [
                    ['tests/Unit/MarketData/PublicApiEodBarsAdapterTest.php', 'test_success_status_with_missing_or_incompatible_content_type_is_rejected_before_parse'],
                    ['tests/Unit/MarketData/PublicApiEodBarsAdapterTest.php', 'test_misaligned_quote_arrays_fail_as_schema_change_before_row_acceptance'],
                    ['tests/Unit/MarketData/PublicApiEodBarsAdapterTest.php', 'test_response_provider_symbol_mismatch_fails_closed_against_requested_mapping'],
                    ['tests/Unit/MarketData/PublicApiEodBarsAdapterTest.php', 'test_active_yahoo_schema_version_must_match_the_released_adapter_contract'],
                    ['tests/Unit/MarketData/PublicApiEodBarsAdapterTest.php', 'test_api_adapter_blocks_empty_success_response_as_no_valid_data'],
                    ['tests/Unit/MarketData/PublicApiEodBarsAdapterTest.php', 'test_range_response_persists_partial_invalid_row_evidence_instead_of_silently_skipping_it'],
                    ['tests/Unit/MarketData/PublicApiEodBarsAdapterTest.php', 'test_api_adapter_normalizes_yahoo_finance_chart_payload_using_ticker_universe'],
                ],
            ],
            'divergence' => [
                'surfaces' => [
                    'app/Infrastructure/Persistence/MarketData/SourceObservationRepository.php',
                    'database/migrations/2026_08_22_000002_harden_source_observation_acquisition.php',
                ],
                'methods' => [
                    ['tests/Unit/MarketData/SourceObservationImmutabilityTest.php', 'test_identical_refetch_records_confirmation_without_a_finding'],
                    ['tests/Unit/MarketData/SourceObservationImmutabilityTest.php', 'test_changed_refetch_opens_an_explicit_divergence_with_both_values_and_delta'],
                ],
            ],
            'canonical_handoff' => [
                'surfaces' => [
                    'app/Application/MarketData/Services/EodBarsIngestService.php',
                    'app/Infrastructure/Persistence/MarketData/EodArtifactRepository.php',
                    'app/Infrastructure/Persistence/MarketData/SourceObservationRepository.php',
                ],
                'methods' => [
                    ['tests/Unit/MarketData/CanonicalRawImportBoundaryTest.php', 'test_a_zero_price_placeholder_cannot_become_canonical'],
                    ['tests/Unit/MarketData/CanonicalRawImportBoundaryTest.php', 'test_provider_adjusted_close_never_reaches_the_canonical_row'],
                    ['tests/Unit/MarketData/CanonicalRawImportBoundaryTest.php', 'test_a_row_without_a_source_observation_cannot_become_canonical'],
                    ['tests/Unit/MarketData/CanonicalRawImportBoundaryTest.php', 'test_a_canonical_row_carries_complete_traceability'],
                    ['tests/Unit/MarketData/EodBarsIngestServiceTest.php', 'test_unknown_ticker_code_is_written_as_invalid_row_instead_of_failing_whole_ingest'],
                    ['tests/Unit/MarketData/EodBarsIngestServiceTest.php', 'test_zero_canonical_rows_preserve_acquisition_telemetry_and_expose_invalid_reason_summary'],
                    ['tests/Unit/MarketData/EodBarsIngestServiceTest.php', 'test_conflicting_duplicate_rows_are_quarantined_and_never_resolved_by_latest_capture_time'],
                    ['tests/Unit/MarketData/SymbolMappingLifecycleAndFailureTest.php', 'test_a_date_outside_mapping_validity_is_rejected_rather_than_served_by_the_current_mapping'],
                    ['tests/Unit/MarketData/AcquisitionKnowledgeCutoffTest.php', 'test_acquisition_does_not_ask_for_a_listing_recorded_after_the_cutoff'],
                ],
            ],
            'telemetry' => [
                'surfaces' => [
                    'app/Infrastructure/MarketData/Source/PublicApiEodBarsAdapter.php',
                    'app/Application/MarketData/Services/ApiBackfillRangeAcquisitionService.php',
                    'app/Application/MarketData/Services/EodBarsIngestService.php',
                ],
                'methods' => [
                    ['tests/Unit/MarketData/PublicApiEodBarsAdapterTest.php', 'test_yahoo_finance_adapter_deduplicates_single_day_ticker_inputs_and_tracks_aggregate_counts'],
                    ['tests/Unit/MarketData/PublicApiEodBarsAdapterTest.php', 'test_api_adapter_exposes_success_after_retry_telemetry'],
                    ['tests/Unit/MarketData/EodBarsIngestServiceTest.php', 'test_api_ingest_returns_source_acquisition_summary_from_adapter'],
                    ['tests/Unit/MarketData/ApiBackfillRangeAcquisitionServiceTest.php', 'test_acquire_writes_window_ticker_checkpoints_for_resume'],
                    ['tests/Unit/MarketData/SourceProviderResilienceStaticGuardTest.php', 'test_evidence_and_replay_carry_source_provider_context'],
                ],
            ],
            'sector_observation' => [
                'surfaces' => [
                    'app/Infrastructure/MarketData/Source/PublicApiEodBarsAdapter.php',
                    'app/Console/Commands/MarketData/IngestSectorIndexBarsApiCommand.php',
                ],
                'methods' => [
                    ['tests/Unit/MarketData/IngestSectorIndexBarsApiCommandTest.php', 'test_apply_writes_sector_index_bars_from_api_source'],
                    ['tests/Unit/MarketData/IngestSectorIndexBarsApiCommandTest.php', 'test_missing_provider_rows_block_apply_without_writing'],
                    ['tests/Unit/MarketData/BenchmarkProviderSymbolResolverTest.php', 'test_public_api_adapter_fetches_benchmark_symbol_without_suffix'],
                ],
            ],
        ];

        $map = [];
        foreach (self::familyAssignment() as $rule => $family) {
            if (! isset($families[$family])) {
                throw new RuntimeException('Unknown B07 proof family '.$family.' for '.$rule);
            }
            $map[$rule] = $families[$family];
        }
        ksort($map, SORT_STRING);

        return $map;
    }

    /** @return array<string,string> rule id => proof family */
    public static function familyAssignment(): array
    {
        $assignment = [];
        $bind = static function (string $document, array $numbers, string $family) use (&$assignment): void {
            foreach ($numbers as $number) {
                $rule = MarketDataSourceAcquisitionTraceabilitySpec::ruleId($document, $number);
                if (isset($assignment[$rule])) {
                    throw new RuntimeException('Duplicate B07 proof family for '.$rule);
                }
                $assignment[$rule] = $family;
            }
        };

        $bind('MD-S053', [2, 3, 5, 18, 19, 20, 21, 22, 23, 38, 49, 58, 83, 84, 120, 153, 161, 165, 167, 169], 'boundary');
        $bind('MD-S053', [7, 32, 34, 35, 36, 40, 41, 42, 43, 47, 48, 97, 98, 99, 134, 142, 201], 'date_window');
        $bind('MD-S053', [25, 26, 51, 56, 57, 59], 'source_mode');
        $bind('MD-S053', array_merge([4, 8, 15, 61, 82, 129, 133, 141], range(63, 80)), 'observation');
        $bind('MD-S053', array_merge([9, 10, 37, 151, 166], range(86, 95)), 'schema');
        $bind('MD-S053', [105, 106, 107, 108, 111, 112, 168], 'divergence');
        $bind('MD-S053', [132, 144, 145, 146, 147, 150], 'telemetry');

        $bind('MD-S054', [1, 11], 'boundary');
        $bind('MD-S054', [12, 19], 'date_window');
        $bind('MD-S054', [13, 14, 15, 16, 17, 18], 'canonical_handoff');

        foreach (['MD-S020-R0010', 'MD-S058-R0048', 'MD-S066-R0001'] as $rule) {
            [$document, $number] = MarketDataSourceAcquisitionTraceabilitySpec::splitRule($rule);
            $bind($document, [$number], 'observation');
        }
        foreach (['MD-S041-R0029', 'MD-S041-R0056'] as $rule) {
            [$document, $number] = MarketDataSourceAcquisitionTraceabilitySpec::splitRule($rule);
            $bind($document, [$number], 'date_window');
        }
        foreach (['MD-S055-R0024'] as $rule) {
            [$document, $number] = MarketDataSourceAcquisitionTraceabilitySpec::splitRule($rule);
            $bind($document, [$number], 'canonical_handoff');
        }
        foreach (['MD-S059-R0040'] as $rule) {
            [$document, $number] = MarketDataSourceAcquisitionTraceabilitySpec::splitRule($rule);
            $bind($document, [$number], 'telemetry');
        }
        foreach (['MD-S052-R0026'] as $rule) {
            [$document, $number] = MarketDataSourceAcquisitionTraceabilitySpec::splitRule($rule);
            $bind($document, [$number], 'sector_observation');
        }

        return $assignment;
    }

    /** @param array<int,array<string,string>> $rows */
    public static function validate(array $rows, string $root, array $map = null): array
    {
        $map = $map ?? self::proofMap();
        $required = [];
        $errors = [];
        $counts = ['denominator' => 0, 'satisfied' => 0, 'unbound' => 0];
        foreach ($rows as $row) {
            if ($row['primary_stage'] === MarketDataSourceAcquisitionTraceabilitySpec::STAGE
                && $row['coverage_requirement'] === 'REQUIRED') {
                $required[$row['rule_id']] = $row;
            }
        }
        ksort($required, SORT_STRING);
        if (array_keys($required) !== array_keys($map)) {
            $errors[] = 'PROOF_MAP: map must exactly cover the current MD-B07 denominator';
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
            if (isset(self::REMEDIATED_RULES[$rule]) && strpos($row['notes'], 'remediated_at=MD-B07-A001') === false) {
                $errors[] = $rule.': remediated rule does not record MD-B07-A001';
            }
            foreach ($proof['surfaces'] as $surface) {
                if (! file_exists($root.'/'.$surface)) {
                    $errors[] = $rule.': proof surface missing '.$surface;
                }
            }
            foreach ($proof['methods'] as [$file, $method]) {
                $source = @file_get_contents($root.'/'.$file);
                if ($source === false || strpos($source, 'function '.$method.'(') === false) {
                    $errors[] = $rule.': proof method missing '.$file.'::'.$method;
                }
            }
        }

        $evidencePath = $root.'/docs/market_data/records/evidence/'.self::EVIDENCE
            .'_SOURCE_OBSERVATION_AND_ACQUISITION.json';
        $evidence = file_exists($evidencePath) ? json_decode(file_get_contents($evidencePath), true) : null;
        if (! is_array($evidence)
            || ($evidence['evidence_id'] ?? null) !== self::EVIDENCE
            || ($evidence['baseline_id'] ?? null) !== 'MD-B07-A001-BL001'
            || ($evidence['change_impact_declaration'] ?? null) !== 'CI-MD-B07-A001-001') {
            $errors[] = 'EVIDENCE: issued proof record is missing, malformed, or miscorrelated';
        }

        $expected = MarketDataSourceAcquisitionTraceabilitySpec::EXPECTED_B07_DENOMINATOR;
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
    $result = MarketDataSourceAcquisitionProofGate::validate($rows, $root);
    $result['gate'] = 'MarketDataSourceAcquisitionProofGate';
    echo json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES).PHP_EOL;
    exit($result['status'] === 'PASS' ? 0 : 1);
}
