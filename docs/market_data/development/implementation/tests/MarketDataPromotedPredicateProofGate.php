<?php

/**
 * PHP 7.3+; governed `MD-B01-A015` proof-map binder and validator.
 *
 * `MD-B01-A014` promoted 72 rows that section 2 of the traceability standard does not permit to be
 * reference context, and proved two of them. The other 62 carried a normalized predicate and no
 * proof — counted in the denominator, tested by nothing. This gate binds exactly those 62 and
 * refuses to bind a subset: a partial binding would leave the stage claiming a coverage figure that
 * no single execution established.
 *
 * `MD-S020-R0067` is not in this map — that map is the 62 promoted predicates. It was finding-blocked
 * until `D-MD-20260822-04` resolved `MD-DEP-0005`; `MD-B01-A016` proved it separately, and this gate
 * now requires it to stay bound to that proof rather than to stay unassessed.
 *
 * Usage:
 *   (no flags)   validate the current matrix and exit non-zero on any error
 *   --bind       perform the atomic binding, then validate
 */
final class MarketDataPromotedPredicateProofGate
{
    public const EVIDENCE = 'E-MD-B01-A015-001';

    public const ATTEMPT = 'MD-B01-A015';

    public const BLOCKED_RULE = 'MD-S020-R0067';

    /** MD-B01 executable denominator, unchanged by this attempt. */
    public const DENOMINATOR = 207;

    /** Satisfied once the A015 map is bound: 144 carried in, plus the 62 proven here. */
    public const SATISFIED_WHEN_BOUND = 207;

    /**
     * rule id => [test file, test methods, implementation surfaces]
     *
     * A rule appears here only when an executed test establishes its whole normalized predicate.
     * Where a predicate has two halves — an upstream locator and a downstream prohibition — both are
     * in the same method, because a rule proven by half a method is a rule not proven.
     *
     * @return array<string,array{file:string,methods:array<int,string>,surfaces:array<int,string>}>
     */
    public static function proofMap(): array
    {
        $map = [];

        $add = static function (array $ruleIds, $file, array $methods, array $surfaces) use (&$map) {
            foreach ($ruleIds as $ruleId) {
                $map[$ruleId] = ['file' => $file, 'methods' => $methods, 'surfaces' => $surfaces];
            }
        };

        // ---- domain ownership of canonical artifacts (25)
        $ownership = 'tests/Unit/MarketData/DomainOwnershipSurfaceTest.php';
        $ownershipMethods = [
            'test_the_owned_artifact_has_a_canonical_surface_and_a_market_data_owner',
            'test_no_surface_outside_the_market_data_tree_touches_a_market_data_table',
        ];
        $add([
            'MD-S001-R0101', 'MD-S001-R0102', 'MD-S001-R0103', 'MD-S001-R0104', 'MD-S001-R0105',
            'MD-S001-R0106', 'MD-S001-R0107', 'MD-S001-R0108', 'MD-S001-R0109',
            'MD-S020-R0019', 'MD-S020-R0020', 'MD-S020-R0021', 'MD-S020-R0022', 'MD-S020-R0024',
            'MD-S020-R0025', 'MD-S020-R0026', 'MD-S020-R0027', 'MD-S020-R0028',
            'MD-S056-R0055', 'MD-S056-R0056', 'MD-S056-R0057', 'MD-S056-R0058', 'MD-S056-R0059',
            'MD-S056-R0060', 'MD-S056-R0061',
        ], $ownership, $ownershipMethods, [
            'docs/market_data/development/implementation/db/Database_Schema_MariaDB.sql',
            'app/Application/MarketData/Services',
            'app/Infrastructure/Persistence/MarketData',
        ]);

        // ---- phase ownership (1)
        $add(['MD-S056-R0114'], $ownership, ['test_promote_owns_the_five_stages_and_import_owns_none_of_them'], [
            'app/Application/MarketData/Services/MarketDataPipelineService.php',
        ]);

        // ---- declarative boundary invariants (9)
        $invariants = 'tests/Unit/MarketData/BoundaryInvariantSemanticsTest.php';
        $invariantMethods = [
            'test_the_upstream_concept_lives_here_and_carries_no_downstream_sense',
            'test_every_forbidden_pattern_matches_the_sense_it_forbids',
            'test_no_forbidden_pattern_fires_on_a_legitimate_upstream_identifier',
        ];
        $add([
            'MD-S020-R0160', 'MD-S020-R0162', 'MD-S020-R0163', 'MD-S020-R0164', 'MD-S020-R0165',
            'MD-S020-R0166', 'MD-S020-R0167', 'MD-S020-R0168', 'MD-S020-R0169',
        ], $invariants, $invariantMethods, [
            'app/Application/MarketData/Services/MarketDataReadinessService.php',
            'app/Application/MarketData/Services/EligibilityDecisionService.php',
            'app/Application/MarketData/Services/IndicatorVectorService.php',
            'app/Application/MarketData/Services/SessionSnapshotService.php',
            'app/Application/MarketData/Services/ReplayVerificationService.php',
        ]);

        // ---- the invariant that constrains the guards themselves (1)
        $add(['MD-S020-R0172'], 'tests/Unit/MarketData/DownstreamConceptSurfaceBoundaryTest.php', [
            'test_no_market_data_guard_flags_an_overloaded_word_on_the_token_alone',
            'test_no_legitimate_upstream_identifier_is_flagged',
        ], [
            'tests/Unit/MarketData',
            'tests/Unit/MarketData/MarketDataOrdersOneToFourArchitectureTest.php',
        ]);

        // ---- canonical scope, dataset start, frontier, decision-grade conditions (16)
        $scope = 'tests/Unit/MarketData/CanonicalScopeFrontierAndDecisionGradeTest.php';
        $add(['MD-S001-R0006'], $scope, ['test_a_trade_date_resolves_through_the_idx_calendar_in_the_platform_timezone'], [
            'app/Domain/MarketData/MarketDataScope.php',
            'app/Infrastructure/Persistence/MarketData/MarketCalendarRepository.php',
        ]);
        $add(['MD-S001-R0007', 'MD-S056-R0014'], $scope, ['test_a_bar_is_end_of_day_only_after_the_regular_market_session_completed'], [
            'app/Infrastructure/Persistence/MarketData/MarketCalendarRepository.php',
        ]);
        $add(['MD-S001-R0009'], $scope, ['test_no_out_of_phase_market_structure_reaches_the_canonical_surface'], [
            'docs/market_data/development/implementation/db/Database_Schema_MariaDB.sql',
        ]);
        $add(['MD-S001-R0062'], $scope, ['test_completeness_gates_publication_rather_than_speed'], [
            'app/Application/MarketData/Services/MarketDataPipelineService.php',
        ]);
        $add(['MD-S056-R0006'], $scope, ['test_as_known_resolution_applies_an_explicit_knowledge_cutoff'], [
            'app/Infrastructure/Persistence/MarketData/EventRiskSourceRepository.php',
            'app/Infrastructure/Persistence/MarketData/MarketCalendarRepository.php',
        ]);
        $add(['MD-S056-R0007'], $scope, ['test_one_price_basis_and_one_formula_version_identify_an_indicator_row'], [
            'app/Application/MarketData/Services/AnalyticalProductIdentityService.php',
            'app/Infrastructure/Persistence/MarketData/MarketDataReadProductRepository.php',
        ]);
        $add(['MD-S056-R0009'], $scope, ['test_timeliness_is_tracked_against_activation_and_never_claimed_as_achieved'], [
            'app/Infrastructure/Persistence/MarketData/EodRunRepository.php',
            'app/Application/MarketData/Services/MarketDataReadinessService.php',
        ]);
        $add(['MD-S056-R0012'], $scope, ['test_the_target_market_is_idx_listed_equities'], [
            'app/Domain/MarketData/MarketDataScope.php',
        ]);
        $add(['MD-S056-R0031', 'MD-S056-R0032'], $scope, ['test_a_request_before_the_dataset_start_is_refused_by_name_not_reported_as_missing'], [
            'app/Domain/MarketData/MarketDataScope.php',
        ]);
        $add(['MD-S056-R0034'], $scope, ['test_every_windowed_indicator_refuses_to_emit_before_its_own_history_exists'], [
            'app/Application/MarketData/Services/IndicatorVectorService.php',
        ]);
        $add(['MD-S056-R0035'], $scope, ['test_pre_boundary_expansion_is_neither_a_current_requirement_nor_a_blocker'], [
            'docs/market_data/development/implementation/MD_DEPENDENCY_REGISTRY.csv',
            'config/market_data.php',
        ]);
        $add(['MD-S056-R0039', 'MD-S056-R0040', 'MD-S056-R0041'], $scope, ['test_the_development_frontier_gap_is_a_valid_state_and_blocks_no_correction_path'], [
            'config/market_data.php',
            'app/Console/Kernel.php',
            'app/Console/Commands/MarketData',
        ]);

        // ---- anti-assumption claims (4)
        $add(['MD-S001-R0142', 'MD-S001-R0143', 'MD-S001-R0144', 'MD-S001-R0145'],
            'tests/Unit/MarketData/AntiAssumptionClaimBoundaryTest.php', [
                'test_every_pattern_matches_the_statement_it_forbids',
                'test_no_active_document_states_the_forbidden_assumption',
                'test_no_executable_surface_encodes_a_forbidden_assumption',
                'test_a_quotation_of_the_prohibition_is_not_read_as_an_assertion',
            ], ['docs/market_data', 'app', 'config']);

        // ---- provider limitation abstraction (4)
        $provider = 'tests/Unit/MarketData/DateDrivenCapabilityAndProviderAbstractionTest.php';
        $add(['MD-S001-R0075', 'MD-S001-R0076', 'MD-S001-R0078'], $provider,
            ['test_the_three_named_provider_quirks_are_owned_by_the_adapter_and_by_nothing_above_it'],
            ['app/Infrastructure/MarketData/Source/PublicApiEodBarsAdapter.php']);
        $add(['MD-S001-R0082'], $provider,
            ['test_the_import_strategy_may_window_and_retry_while_the_requested_range_still_governs'],
            ['app/Application/MarketData/Services/ApiBackfillRangeAcquisitionService.php']);

        // ---- term ownership register (2)
        $register = 'tests/Unit/MarketData/TermOwnershipAndPriceProductTest.php';
        $add(['MD-S056-R0143'], $register, ['test_the_register_has_exactly_one_home_so_a_new_term_changes_that_document'],
            ['docs/market_data/authority/strategy/book/Terminology_and_Scope.md']);
        $add(['MD-S056-R0144'], $register, ['test_a_second_substantive_definition_is_a_violation_and_not_a_style_difference'],
            ['docs/market_data/authority/strategy/book/Terminology_and_Scope.md', 'docs/market_data']);

        return $map;
    }

    /** @return array{headers:array<int,string>,rows:array<int,array<string,string>>} */
    public static function readMatrix(string $path): array
    {
        $handle = fopen($path, 'r');
        if ($handle === false) {
            throw new RuntimeException('Cannot read matrix: '.$path);
        }
        $headers = fgetcsv($handle);
        $headers[0] = preg_replace('/^\xEF\xBB\xBF/', '', $headers[0]);
        $rows = [];
        while (($values = fgetcsv($handle)) !== false) {
            if (count($values) !== count($headers)) {
                fclose($handle);
                throw new RuntimeException('Malformed matrix row with '.count($values).' fields.');
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

    /**
     * @param  array<int,array<string,string>>  $rows
     * @return array<int,array<string,string>>
     */
    public static function bind(array $rows): array
    {
        $map = self::proofMap();

        foreach ($rows as &$row) {
            $id = $row['rule_id'];
            if (! isset($map[$id])) {
                continue;
            }
            if ($row['coverage_requirement'] !== 'REQUIRED' || strtoupper(trim($row['active'])) !== 'YES') {
                throw new RuntimeException($id.': cannot bind proof to a row that is not an active required rule.');
            }
            $marker = self::ATTEMPT.': proof='.basename($map[$id]['file'], '.php').'::'.implode('&', $map[$id]['methods'])
                .'; implementation_surface='.implode(',', $map[$id]['surfaces']);
            if (strpos($row['notes'], self::ATTEMPT.': proof=') === false) {
                $row['notes'] = trim($row['notes']).' | '.$marker;
            }
            $row['coverage_status'] = 'SATISFIED';
            $row['current_evidence_ids'] = self::EVIDENCE;
        }
        unset($row);

        return $rows;
    }

    /**
     * @param  array<int,array<string,string>>  $rows
     * @return array{errors:array<int,string>,bound:int,counts:array<string,int>}
     */
    public static function validate(array $rows, string $root, array $map = null): array
    {
        $map = $map === null ? self::proofMap() : $map;
        $errors = [];

        if (count($map) !== 62) {
            $errors[] = 'the A015 proof map must contain exactly 62 rules, got '.count($map);
        }
        if (isset($map[self::BLOCKED_RULE])) {
            $errors[] = self::BLOCKED_RULE.': the finding-blocked rule may not appear in a proof map';
        }

        // every named test method must exist on disk
        foreach ($map as $ruleId => $spec) {
            $path = $root.'/'.$spec['file'];
            if (! is_file($path)) {
                $errors[] = $ruleId.': proof file is missing — '.$spec['file'];

                continue;
            }
            $source = (string) file_get_contents($path);
            foreach ($spec['methods'] as $method) {
                if (strpos($source, 'function '.$method.'(') === false) {
                    $errors[] = $ruleId.': proof method '.$method.' does not exist in '.$spec['file'];
                }
            }
            foreach ($spec['surfaces'] as $surface) {
                if (! file_exists($root.'/'.$surface)) {
                    $errors[] = $ruleId.': implementation surface is missing — '.$surface;
                }
            }
        }

        $byId = [];
        foreach ($rows as $row) {
            $byId[$row['rule_id']] = $row;
        }

        $bound = 0;
        foreach ($map as $ruleId => $spec) {
            if (! isset($byId[$ruleId])) {
                $errors[] = $ruleId.': mapped rule is not in the matrix';

                continue;
            }
            $row = $byId[$ruleId];
            if ($row['primary_stage'] !== 'MD-B01' || $row['coverage_requirement'] !== 'REQUIRED') {
                $errors[] = $ruleId.': not an active MD-B01 required rule';

                continue;
            }
            if ($row['coverage_status'] === 'SATISFIED') {
                $bound++;
                if ($row['current_evidence_ids'] !== self::EVIDENCE) {
                    $errors[] = $ruleId.': bound to '.$row['current_evidence_ids'].' rather than '.self::EVIDENCE;
                }
                if (strpos($row['notes'], self::ATTEMPT.': proof=') === false) {
                    $errors[] = $ruleId.': incomplete A015 binding — no proof marker';
                }
            } elseif ($row['coverage_status'] !== 'NOT_ASSESSED' || trim($row['current_evidence_ids']) !== '') {
                $errors[] = $ruleId.': unexpected pre-bind lifecycle/evidence';
            }
        }
        if ($bound !== 0 && $bound !== 62) {
            $errors[] = 'A015 binding must be atomic: '.$bound.' of 62 rows are bound';
        }

        // Resolved at MD-B01-A016 under D-MD-20260822-04. This gate still refuses to carry the rule
        // in its own proof map — that map is the 62 promoted predicates — but the rule must now be
        // proven elsewhere rather than left unassessed.
        $blocked = isset($byId[self::BLOCKED_RULE]) ? $byId[self::BLOCKED_RULE] : null;
        if ($blocked === null || $blocked['coverage_status'] !== 'SATISFIED' || $blocked['current_evidence_ids'] !== 'E-MD-B01-A016-001') {
            $errors[] = self::BLOCKED_RULE.': the resolved rule is not bound to its A016 proof';
        }

        $counts = ['denominator' => 0, 'satisfied' => 0, 'not_assessed' => 0];
        foreach ($rows as $row) {
            if ($row['primary_stage'] !== 'MD-B01' || $row['coverage_requirement'] !== 'REQUIRED' || strtoupper(trim($row['active'])) !== 'YES'
                || ! in_array($row['applicability'], ['MANDATORY', 'CONDITIONAL_APPLICABLE'], true)) {
                continue;
            }
            $counts['denominator']++;
            $counts['satisfied'] += $row['coverage_status'] === 'SATISFIED' ? 1 : 0;
            $counts['not_assessed'] += $row['coverage_status'] === 'NOT_ASSESSED' ? 1 : 0;
        }
        if ($counts['denominator'] !== self::DENOMINATOR) {
            $errors[] = 'MD-B01 denominator drifted from '.self::DENOMINATOR.' to '.$counts['denominator'];
        }
        $expectedSatisfied = $bound === 62 ? self::SATISFIED_WHEN_BOUND : self::SATISFIED_WHEN_BOUND - 62;
        if ($counts['satisfied'] !== $expectedSatisfied) {
            $errors[] = 'MD-B01 satisfied count is '.$counts['satisfied'].', expected '.$expectedSatisfied;
        }

        return ['errors' => $errors, 'bound' => $bound, 'counts' => $counts];
    }
}

if (realpath($_SERVER['SCRIPT_FILENAME'] ?? '') === __FILE__) {
    $marketData = realpath(dirname(__DIR__, 3));
    $root = realpath($marketData.'/../..');
    $matrix = $marketData.'/authority/governance/STRATEGY_TO_IMPLEMENTATION_TRACEABILITY_MATRIX.csv';
    $data = MarketDataPromotedPredicateProofGate::readMatrix($matrix);

    if (in_array('--bind', $argv, true)) {
        $data['rows'] = MarketDataPromotedPredicateProofGate::bind($data['rows']);
        MarketDataPromotedPredicateProofGate::writeMatrix($matrix, $data['headers'], $data['rows']);
    }

    $result = MarketDataPromotedPredicateProofGate::validate($data['rows'], $root);
    $result['gate'] = 'MarketDataPromotedPredicateProofGate';
    $result['stage_id'] = 'MD-B01';
    $result['attempt_id'] = MarketDataPromotedPredicateProofGate::ATTEMPT;
    $result['blocked_rule'] = MarketDataPromotedPredicateProofGate::BLOCKED_RULE;
    $result['status'] = $result['errors'] === [] ? 'PASS' : 'FAIL';
    $result['generated_at'] = date(DATE_ATOM);
    echo json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES).PHP_EOL;
    exit($result['status'] === 'PASS' ? 0 : 1);
}
