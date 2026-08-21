<?php

/** PHP 7.3+; governed A013 traceability/proof binder and validator. */
final class MarketDataScopeBoundaryCompletionGate
{
    public const EVIDENCE = 'E-MD-B01-A013-001';
    public const BLOCKED_RULE = 'MD-S020-R0067';

    /**
     * MD-B01 executable denominator. A013 locked this at 143 and the Stage Register called it FINAL.
     * `MD-B01-A014` proved the figure understated the stage: 17 enumerated runs carried mixed
     * classification, so 72 predicate rows sat outside the denominator because they state their
     * obligation without a deontic modal. This constant must move only through a governed
     * re-derivation, never to make a count agree with the matrix.
     */
    public const DENOMINATOR = 207;

    /**
     * Stage-wide satisfied count once A013's 31 rows are bound: A012's 111 carry-forwards, A013's 31,
     * and the two alias-cluster rules A014 promoted and then proved
     * (`MD-S020-R0068`, `MD-S020-R0071`).
     */
    public const SATISFIED_WHEN_BOUND = 144;

    /** @return array<string,array{proofs:array<int,array{0:string,1:string}>,surfaces:array<int,string>}> */
    public static function proofMap(): array
    {
        $map = [];
        $add = static function (array $ids, array $proofs, array $surfaces) use (&$map): void {
            foreach ($ids as $id) {
                $map[$id] = ['proofs' => $proofs, 'surfaces' => $surfaces];
            }
        };

        $completion = 'tests/Unit/MarketData/ScopeBoundaryAndOrchestrationCompletionTest.php';
        $scope = 'tests/Unit/MarketData/ScopeProductAndTimeBoundaryTest.php';
        $provider = 'tests/Unit/MarketData/DateDrivenCapabilityAndProviderAbstractionTest.php';
        $downstream = 'tests/Unit/MarketData/DownstreamConceptSurfaceBoundaryTest.php';
        $dual = 'tests/Unit/MarketData/DualUseFactAndContractAlignmentTest.php';
        $activation = 'tests/Unit/MarketData/ActivationClaimAndTerminologyBoundaryTest.php';
        $eligibility = 'tests/Unit/MarketData/EligibilityExplainabilityBoundaryTest.php';
        $metrics = 'tests/Unit/MarketData/ActualVersusProxyMetricBoundaryTest.php';
        $dormancy = 'tests/Unit/MarketData/CoverageDormantUniverseTest.php';
        $coverage = 'tests/Unit/MarketData/CoverageSilentImprovementBoundaryTest.php';
        $replay = 'tests/Unit/MarketData/AsKnownReplayBoundaryTest.php';
        $observations = 'tests/Unit/MarketData/SourceObservationImmutabilityTest.php';
        $consumer = 'tests/Unit/MarketData/ConsumerReadProductAntiBypassTest.php';

        $add(['MD-S001-R0003'], [
            [$completion, 'test_scope_summary_matches_the_executable_boundary_and_keeps_weekly_swing_downstream'],
            [$scope, 'test_readiness_depends_only_on_scope_and_publication_state'],
        ], [
            'docs/market_data/development/implementation/guides/system/SYSTEM_CONTEXT_AND_DEPENDENCIES.md',
            'app/Application/MarketData/Services/MarketDataReadinessService.php',
        ]);
        $add(['MD-S001-R0069'], [
            [$completion, 'test_yahoo_bootstrap_decision_matches_the_provider_neutral_port_and_explicit_adapter_limits'],
            [$provider, 'test_the_acquisition_port_is_provider_neutral_and_date_addressed'],
            [$provider, 'test_the_provider_query_shape_lives_in_the_source_adapter'],
        ], [
            'docs/market_data/development/implementation/guides/system/SYSTEM_CONTEXT_AND_DEPENDENCIES.md',
            'app/Application/MarketData/Ports/ApiEodBarsSource.php',
            'app/Infrastructure/MarketData/Source/PublicApiEodBarsAdapter.php',
        ]);
        $add(['MD-S001-R0124', 'MD-S001-R0125', 'MD-S001-R0151'], [
            [$completion, 'test_governed_execution_is_sequential_with_one_resume_and_distinct_authoritative_roles'],
        ], [
            'docs/market_data/development/implementation/MD_IMPLEMENTATION_BUILD_SEQUENCE.md',
            'docs/market_data/development/implementation/MD_IMPLEMENTATION_STAGE_REGISTER.md',
            'docs/market_data/authority/governance/STRATEGY_TO_IMPLEMENTATION_TRACEABILITY_MATRIX.csv',
            'docs/market_data/development/implementation/CURRENT_STATE.md',
        ]);

        $add(['MD-S020-R0001'], [
            [$completion, 'test_scope_summary_matches_the_executable_boundary_and_keeps_weekly_swing_downstream'],
            [$scope, 'test_no_market_data_class_depends_on_a_policy_namespace'],
            [$downstream, 'test_no_named_surface_embeds_consumer_policy'],
        ], ['app/Domain/MarketData', 'app/Application/MarketData', 'docs/market_data/development/implementation/guides/system/SYSTEM_CONTEXT_AND_DEPENDENCIES.md']);
        $add(['MD-S020-R0003'], [
            [$dual, 'test_each_alignment_target_carries_the_shared_boundary_predicate'],
            [$dual, 'test_no_active_document_overrides_the_owner_boundary_definition'],
            [$downstream, 'test_no_schema_table_or_column_names_a_downstream_concept'],
        ], ['docs/market_data/authority/strategy/book/Domain_Boundary_Invariants_LOCKED.md', 'docs/market_data/development/implementation/db/Database_Schema_MariaDB.sql', 'app']);
        $add(['MD-S020-R0006', 'MD-S020-R0161'], [
            [$scope, 'test_no_market_data_class_depends_on_a_policy_namespace'],
            [$completion, 'test_scope_summary_matches_the_executable_boundary_and_keeps_weekly_swing_downstream'],
        ], ['app/Domain/MarketData', 'app/Application/MarketData', 'app/Infrastructure/MarketData']);
        $add(['MD-S020-R0042'], [
            [$scope, 'test_readiness_depends_only_on_scope_and_publication_state'],
            [$eligibility, 'test_the_decision_consults_no_preference_input'],
        ], ['app/Application/MarketData/Services/MarketDataReadinessService.php', 'app/Application/MarketData/Services/EligibilityDecisionService.php']);
        $add(['MD-S020-R0055'], [
            [$eligibility, 'test_eligible_means_usable_data_and_carries_no_selection_verdict'],
            [$scope, 'test_the_eligible_alias_is_derived_from_data_usable_and_never_independent'],
        ], ['app/Application/MarketData/Services/EligibilityDecisionService.php', 'app/Infrastructure/Persistence/MarketData/MarketDataReadProductRepository.php']);
        $add(['MD-S020-R0065'], [
            [$eligibility, 'test_a_missing_bar_produces_a_blocked_decision_with_a_reason'],
            [$eligibility, 'test_an_invalid_indicator_carries_its_specific_cause'],
            [$dormancy, 'test_a_dormant_ticker_stays_in_the_denominator'],
        ], ['app/Application/MarketData/Services/EligibilityDecisionService.php', 'app/Application/MarketData/Services/CoverageGateEvaluator.php']);
        $add(['MD-S020-R0083'], [
            [$metrics, 'test_the_actual_traded_value_is_null_rather_than_filled_with_the_proxy'],
            [$metrics, 'test_actual_and_proxy_are_separate_fields_with_distinct_names'],
        ], ['app/Application/MarketData/Services/IndicatorVectorService.php', 'app/Infrastructure/Persistence/MarketData/MarketDataReadProductRepository.php']);
        $add(['MD-S020-R0084'], [
            [$eligibility, 'test_the_decision_consults_no_preference_input'],
            [$dormancy, 'test_a_thinly_traded_ticker_inside_the_horizon_stays_expected'],
        ], ['app/Application/MarketData/Services/EligibilityDecisionService.php', 'app/Application/MarketData/Services/CoverageGateEvaluator.php']);
        $add(['MD-S020-R0085'], [
            [$dormancy, 'test_a_dormant_ticker_stays_in_the_denominator'],
            [$dormancy, 'test_a_universe_of_dormant_tickers_reports_low_coverage_not_perfect_coverage'],
            [$coverage, 'test_every_excluded_instrument_is_counted_somewhere'],
        ], ['app/Application/MarketData/Services/CoverageGateEvaluator.php']);
        $add(['MD-S020-R0086'], [
            [$downstream, 'test_no_indicator_identifier_becomes_a_signal_or_ranking'],
            [$downstream, 'test_no_eligibility_identifier_becomes_screening_or_timing'],
            [$eligibility, 'test_the_decision_consults_no_preference_input'],
        ], ['app/Application/MarketData/Services/IndicatorVectorService.php', 'app/Application/MarketData/Services/EligibilityDecisionService.php']);
        $add(['MD-S020-R0087'], [
            [$eligibility, 'test_low_liquidity_does_not_block_a_row_whose_data_is_usable'],
            [$eligibility, 'test_zero_volume_alone_does_not_block_a_row'],
            [$metrics, 'test_actual_and_proxy_are_separate_fields_with_distinct_names'],
        ], ['app/Application/MarketData/Services/EligibilityDecisionService.php', 'app/Application/MarketData/Services/IndicatorVectorService.php']);
        $add(['MD-S020-R0107'], [
            [$completion, 'test_market_data_replay_proves_reproducibility_and_emits_no_strategy_outcome'],
            [$replay, 'test_every_revision_kind_named_by_the_exit_gate_has_a_cutoff_bearing_root'],
        ], ['app/Application/MarketData/Services/ReplayVerificationService.php', 'app/Application/MarketData/Services/ReplayBackfillService.php']);
        $add(['MD-S020-R0124'], [
            [$scope, 'test_no_market_data_class_depends_on_a_policy_namespace'],
            [$observations, 'test_a_rerun_appends_a_new_observation_and_never_overwrites'],
            [$consumer, 'test_the_payload_declares_its_product_and_read_model_version'],
        ], ['app/Domain/MarketData', 'app/Application/MarketData', 'app/Infrastructure/Persistence/MarketData/MarketDataReadProductRepository.php']);
        $add(['MD-S020-R0139'], [
            [$downstream, 'test_no_schema_table_or_column_names_a_downstream_concept'],
            [$downstream, 'test_no_http_route_exposes_a_downstream_concept'],
            [$consumer, 'test_the_payload_declares_its_product_and_read_model_version'],
        ], ['docs/market_data/development/implementation/db/Database_Schema_MariaDB.sql', 'app/Application/MarketData/Services/MarketDataReadProductService.php']);
        $add(['MD-S020-R0152'], [
            [$dual, 'test_a_comment_about_the_disownment_is_not_read_as_a_surface'],
            [$downstream, 'test_no_named_surface_embeds_consumer_policy'],
        ], ['app', 'docs/market_data/development/implementation/guides/system/SYSTEM_CONTEXT_AND_DEPENDENCIES.md']);
        $add(['MD-S020-R0159'], [
            [$downstream, 'test_every_forbidden_concept_matches_the_identifier_it_forbids'],
            [$downstream, 'test_no_legitimate_upstream_identifier_is_flagged'],
        ], ['app', 'config/market_data.php', 'docs/market_data/development/implementation/db/Database_Schema_MariaDB.sql']);
        $add(['MD-S020-R0170'], [
            [$scope, 'test_no_market_data_class_depends_on_a_policy_namespace'],
            [$observations, 'test_a_rerun_appends_a_new_observation_and_never_overwrites'],
            [$completion, 'test_market_data_replay_proves_reproducibility_and_emits_no_strategy_outcome'],
        ], ['app/Domain/MarketData', 'app/Application/MarketData', 'app/Infrastructure/Persistence/MarketData/SourceObservationRepository.php']);

        $add(['MD-S056-R0001', 'MD-S056-R0015'], [
            [$completion, 'test_scope_summary_matches_the_executable_boundary_and_keeps_weekly_swing_downstream'],
            [$scope, 'test_timezone_fails_closed_when_either_key_drifts'],
        ], ['app/Domain/MarketData/MarketDataScope.php', 'config/market_data.php']);
        $add(['MD-S056-R0004'], [
            [$completion, 'test_headline_quality_term_has_one_owner_and_unusable_data_is_explicitly_reason_blocked'],
            [$activation, 'test_the_headline_quality_property_is_never_asserted_by_an_executable_surface'],
        ], ['docs/market_data/authority/strategy/book/Terminology_and_Scope.md', 'docs/market_data/development/implementation/guides/system/SYSTEM_DATA_PRODUCT_MAP.md']);
        $add(['MD-S056-R0008', 'MD-S056-R0010'], [
            [$completion, 'test_headline_quality_term_has_one_owner_and_unusable_data_is_explicitly_reason_blocked'],
            [$eligibility, 'test_a_missing_bar_produces_a_blocked_decision_with_a_reason'],
            [$eligibility, 'test_an_invalid_indicator_carries_its_specific_cause'],
        ], ['app/Application/MarketData/Services/EligibilityDecisionService.php', 'docs/market_data/development/implementation/db/registry/Reason_Codes_Seed.sql']);
        $add(['MD-S056-R0016', 'MD-S056-R0023'], [
            [$completion, 'test_horizon_generates_concrete_trading_day_requirements_without_becoming_a_readiness_gate'],
        ], ['docs/market_data/development/implementation/guides/system/SYSTEM_CONTEXT_AND_DEPENDENCIES.md', 'config/market_data.php']);
        $add(['MD-S056-R0047'], [
            [$completion, 'test_operational_activation_meaning_and_gate_requirements_have_distinct_owners'],
            [$activation, 'test_the_reported_activation_state_follows_the_governed_marker'],
        ], ['docs/market_data/authority/strategy/book/Terminology_and_Scope.md', 'docs/market_data/authority/strategy/book/EOD_SOURCE_OPERATIONAL_RESILIENCE_CONTRACT_LOCKED.md', 'app/Domain/MarketData/MarketDataScope.php']);

        ksort($map);

        return $map;
    }

    /** @return array{headers:array<int,string>,rows:array<int,array<string,string>>} */
    public static function readMatrix(string $path): array
    {
        $handle = fopen($path, 'r');
        if ($handle === false) {
            throw new RuntimeException('Cannot open matrix.');
        }
        $headers = fgetcsv($handle);
        $headers[0] = preg_replace('/^\xEF\xBB\xBF/', '', $headers[0]);
        $rows = [];
        while (($values = fgetcsv($handle)) !== false) {
            if (count($values) !== count($headers)) {
                fclose($handle);
                throw new RuntimeException('Malformed matrix row.');
            }
            $rows[] = array_combine($headers, $values);
        }
        fclose($handle);

        return ['headers' => $headers, 'rows' => $rows];
    }

    /** @param array<int,string> $headers @param array<int,array<string,string>> $rows */
    public static function writeMatrix(string $path, array $headers, array $rows): void
    {
        $temp = $path.'.tmp-'.getmypid();
        $handle = fopen($temp, 'w');
        if ($handle === false) {
            throw new RuntimeException('Cannot create temporary matrix.');
        }
        fputcsv($handle, $headers);
        foreach ($rows as $row) {
            $values = [];
            foreach ($headers as $header) {
                $values[] = $row[$header];
            }
            fputcsv($handle, $values);
        }
        fclose($handle);
        if (! rename($temp, $path)) {
            @unlink($temp);
            throw new RuntimeException('Cannot replace canonical matrix.');
        }
    }

    /** @param array<int,array<string,string>> $rows @param array<string,mixed>|null $map */
    public static function validate(array $rows, string $root, array $map = null): array
    {
        $map = $map ?? self::proofMap();
        $errors = [];
        if (count($map) !== 31) {
            $errors[] = 'A013 proof map must contain exactly 31 rules, got '.count($map);
        }
        $byId = [];
        foreach ($rows as $row) {
            $byId[$row['rule_id']] = $row;
        }
        $bound = 0;
        foreach ($map as $id => $spec) {
            if (! isset($byId[$id])) {
                $errors[] = $id.': missing matrix row';
                continue;
            }
            $row = $byId[$id];
            if ($row['primary_stage'] !== 'MD-B01' || $row['coverage_requirement'] !== 'REQUIRED' || $row['active'] !== 'YES') {
                $errors[] = $id.': not an active MD-B01 required rule';
            }
            if (! in_array($row['applicability'], ['MANDATORY', 'CONDITIONAL_APPLICABLE'], true)) {
                $errors[] = $id.': not in the executable denominator';
            }
            if (strpos($row['notes'], 'normalized_predicate=') === false) {
                $errors[] = $id.': normalized predicate missing';
            }
            foreach ($spec['proofs'] as $proof) {
                $path = $root.'/'.$proof[0];
                if (! is_file($path)) {
                    $errors[] = $id.': proof file missing '.$proof[0];
                    continue;
                }
                if (strpos((string) file_get_contents($path), 'function '.$proof[1].'(') === false) {
                    $errors[] = $id.': proof method missing '.$proof[1];
                }
            }
            foreach ($spec['surfaces'] as $surface) {
                if (! file_exists($root.'/'.$surface)) {
                    $errors[] = $id.': implementation surface missing '.$surface;
                }
            }
            if ($row['current_evidence_ids'] === self::EVIDENCE) {
                $bound++;
                if ($row['coverage_status'] !== 'SATISFIED' || strpos($row['notes'], 'MD-B01-A013: proof=') === false) {
                    $errors[] = $id.': incomplete A013 binding';
                }
            } elseif ($row['coverage_status'] !== 'NOT_ASSESSED' || trim($row['current_evidence_ids']) !== '') {
                $errors[] = $id.': unexpected pre-bind lifecycle/evidence';
            }
        }
        if ($bound !== 0 && $bound !== 31) {
            $errors[] = 'A013 binding must be atomic: '.$bound.' of 31 rows are bound';
        }

        $blocked = $byId[self::BLOCKED_RULE] ?? null;
        if ($blocked === null
            || $blocked['primary_stage'] !== 'MD-B01'
            || $blocked['applicability'] !== 'CONDITIONAL_APPLICABLE'
            || $blocked['coverage_status'] !== 'NOT_ASSESSED'
            || trim($blocked['current_evidence_ids']) !== '') {
            $errors[] = self::BLOCKED_RULE.': finding-blocked lifecycle was advanced or altered';
        }

        $satisfied = 0;
        $notAssessed = 0;
        $denominator = 0;
        foreach ($rows as $row) {
            if ($row['primary_stage'] !== 'MD-B01' || $row['coverage_requirement'] !== 'REQUIRED' || $row['active'] !== 'YES'
                || ! in_array($row['applicability'], ['MANDATORY', 'CONDITIONAL_APPLICABLE'], true)) {
                continue;
            }
            $denominator++;
            $satisfied += $row['coverage_status'] === 'SATISFIED' ? 1 : 0;
            $notAssessed += $row['coverage_status'] === 'NOT_ASSESSED' ? 1 : 0;
        }
        if ($denominator !== self::DENOMINATOR) {
            $errors[] = 'MD-B01 denominator drifted from '.self::DENOMINATOR.' to '.$denominator;
        }
        $expectedSatisfied = $bound === 31 ? self::SATISFIED_WHEN_BOUND : self::SATISFIED_WHEN_BOUND - 31;
        // Every denominator row is either SATISFIED or NOT_ASSESSED, so the remainder is derived.
        // The satisfied figure and the denominator are the independently locked numbers.
        $expectedNotAssessed = self::DENOMINATOR - $expectedSatisfied;
        if ($satisfied !== $expectedSatisfied || $notAssessed !== $expectedNotAssessed) {
            $errors[] = 'MD-B01 lifecycle counts are '.$satisfied.'/'.$notAssessed.', expected '.$expectedSatisfied.'/'.$expectedNotAssessed;
        }

        return [
            'errors' => $errors,
            'phase' => $bound === 31 ? 'BIND_COMPLETE' : 'PRE_BIND',
            'proof_map_rules' => count($map),
            'bound_rules' => $bound,
            'blocked_rule' => self::BLOCKED_RULE,
            'counts' => ['denominator' => $denominator, 'satisfied' => $satisfied, 'not_assessed' => $notAssessed],
        ];
    }

    /** @param array<int,array<string,string>> $rows @return array<int,array<string,string>> */
    public static function bind(array $rows): array
    {
        $map = self::proofMap();
        foreach ($rows as &$row) {
            $id = $row['rule_id'];
            if (! isset($map[$id])) {
                continue;
            }
            $proofs = [];
            foreach ($map[$id]['proofs'] as $proof) {
                $proofs[] = basename($proof[0], '.php').'::'.$proof[1];
            }
            $marker = 'MD-B01-A013: proof='.implode('&', $proofs)
                .'; implementation_surface='.implode('&', $map[$id]['surfaces'])
                .'; evidence='.self::EVIDENCE;
            $row['coverage_status'] = 'SATISFIED';
            $row['current_evidence_ids'] = self::EVIDENCE;
            if (strpos($row['notes'], 'MD-B01-A013: proof=') === false) {
                $row['notes'] .= ' | '.$marker;
            }
        }
        unset($row);

        return $rows;
    }
}

if (PHP_SAPI === 'cli' && realpath($_SERVER['SCRIPT_FILENAME']) === __FILE__) {
    $md = realpath(dirname(__DIR__, 3));
    $root = realpath($md.'/../..');
    $matrix = $md.'/authority/governance/STRATEGY_TO_IMPLEMENTATION_TRACEABILITY_MATRIX.csv';
    $data = MarketDataScopeBoundaryCompletionGate::readMatrix($matrix);
    if (in_array('--bind-a013-evidence', $argv, true)) {
        $data['rows'] = MarketDataScopeBoundaryCompletionGate::bind($data['rows']);
        MarketDataScopeBoundaryCompletionGate::writeMatrix($matrix, $data['headers'], $data['rows']);
    }
    $result = MarketDataScopeBoundaryCompletionGate::validate($data['rows'], $root);
    $result['gate'] = 'MarketDataScopeBoundaryCompletionGate';
    $result['stage_id'] = 'MD-B01';
    $result['attempt_id'] = 'MD-B01-A013';
    $result['status'] = $result['errors'] === [] ? 'PASS' : 'FAIL';
    echo json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES).PHP_EOL;
    exit($result['errors'] === [] ? 0 : 1);
}
