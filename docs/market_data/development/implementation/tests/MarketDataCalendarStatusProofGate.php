<?php

require_once __DIR__.'/MarketDataCalendarStatusTraceabilityGate.php';

/** Exact MD-B06-A001 proof binding and implementation-surface gate. */
final class MarketDataCalendarStatusProofGate
{
    public const EVIDENCE = 'E-MD-B06-A001-001';

    public const REMEDIATED_RULES = [
        'MD-S041-R0022' => 'calendar reads now preserve projected-to-verified successor history',
        'MD-S041-R0031' => 'wall clock can no longer manufacture session completion',
        'MD-S041-R0064' => 'conflicting terminal calendar revisions now fail closed',
        'MD-S058-R0006' => 'status resolution now binds the temporal board valid on the trade date',
        'MD-S058-R0023' => 'same-priority authoritative conflict now holds instead of selecting recency',
        'MD-S058-R0026' => 'manual import is explicitly transport-only',
        'MD-S058-R0029' => 'operator authority now requires the governed authority triple',
        'MD-S058-R0034' => 'exact-date event types cannot carry indefinitely',
        'MD-S058-R0035' => 'current status is resolved as-known and cannot leak backward',
        'MD-S058-R0040' => 'BAR_NOT_EXPECTED now requires verified full-session evidence',
    ];

    /** @return array<string,array{surfaces:array<int,string>,methods:array<int,array{0:string,1:string}>}> */
    public static function proofMap(): array
    {
        $families = [
            'calendar_revision' => [
                'surfaces' => [
                    'app/Infrastructure/Persistence/MarketData/MarketCalendarRepository.php',
                    'database/migrations/2026_08_22_000001_harden_calendar_and_trading_status_expectation.php',
                ],
                'methods' => [
                    ['tests/Unit/MarketData/CalendarProvenanceAndStatusTest.php', 'test_a_projected_calendar_date_is_never_expected'],
                    ['tests/Unit/MarketData/CalendarProvenanceAndStatusTest.php', 'test_an_unclassified_calendar_date_is_never_expected'],
                    ['tests/Unit/MarketData/CalendarProvenanceAndStatusTest.php', 'test_a_verified_completed_session_resolves_and_carries_its_tier'],
                    ['tests/Unit/MarketData/CalendarProvenanceAndStatusTest.php', 'test_wall_clock_cannot_manufacture_session_completion'],
                    ['tests/Unit/MarketData/CalendarProvenanceAndStatusTest.php', 'test_calendar_revision_conflict_and_incomplete_verification_both_fail_closed'],
                    ['tests/Unit/MarketData/CalendarProvenanceAndStatusTest.php', 'test_projected_calendar_revision_becomes_verified_only_after_known_successor'],
                    ['tests/Unit/MarketData/CalendarProvenanceAndStatusTest.php', 'test_trading_window_excludes_a_verified_label_without_reconciliation_evidence'],
                    ['tests/Unit/MarketData/AsKnownReplayBoundaryTest.php', 'test_a_calendar_revision_recorded_after_the_cutoff_is_invisible'],
                    ['tests/Unit/MarketData/MarketDataOrdersOneToFourFoundationTest.php', 'test_calendar_completion_requires_and_reads_an_explicit_successor_revision'],
                ],
            ],
            'calendar_expectation' => [
                'surfaces' => [
                    'app/Application/MarketData/Services/ExpectedBarDecisionService.php',
                    'app/Infrastructure/Persistence/MarketData/MarketCalendarRepository.php',
                    'app/Infrastructure/Persistence/MarketData/TemporalTradingStatusRepository.php',
                ],
                'methods' => [
                    ['tests/Unit/MarketData/CalendarProvenanceAndStatusTest.php', 'test_a_verified_holiday_is_refused_as_a_non_trading_day'],
                    ['tests/Unit/MarketData/CalendarProvenanceAndStatusTest.php', 'test_a_date_with_no_calendar_evidence_fails_closed'],
                    ['tests/Unit/MarketData/CalendarProvenanceAndStatusTest.php', 'test_expected_bar_decision_is_explainable_and_half_day_is_normal'],
                    ['tests/Unit/MarketData/TradingWindowWarmupTest.php', 'test_the_window_counts_trading_days_not_calendar_days'],
                    ['tests/Unit/MarketData/TradingWindowWarmupTest.php', 'test_closed_days_are_not_counted_toward_the_window'],
                    ['tests/Unit/MarketData/TradingWindowWarmupTest.php', 'test_a_closed_date_cannot_anchor_a_window'],
                ],
            ],
            'status_foundation' => [
                'surfaces' => [
                    'app/Infrastructure/Persistence/MarketData/TemporalTradingStatusRepository.php',
                    'database/migrations/2026_08_22_000001_harden_calendar_and_trading_status_expectation.php',
                    'database/migrations/2026_08_22_000001_add_temporal_listing_board_intervals.php',
                ],
                'methods' => [
                    ['tests/Unit/MarketData/AuthoritativeTradingStatusSnapshotServiceTest.php', 'test_apply_binds_authoritative_status_to_immutable_observation_and_stable_ids'],
                    ['tests/Unit/MarketData/CalendarProvenanceAndStatusTest.php', 'test_status_board_mismatch_fails_closed'],
                    ['tests/Unit/MarketData/CalendarProvenanceAndStatusTest.php', 'test_exact_date_status_cannot_carry_forward_without_an_end'],
                    ['tests/Unit/MarketData/CalendarProvenanceAndStatusTest.php', 'test_successor_status_revision_replaces_its_predecessor_without_conflict'],
                ],
            ],
            'status_authority' => [
                'surfaces' => [
                    'app/Infrastructure/Persistence/MarketData/TemporalTradingStatusRepository.php',
                    'app/Console/Commands/MarketData/ImportTradingStatusEventsCommand.php',
                    'app/Application/MarketData/Services/AuthoritativeTradingStatusSnapshotService.php',
                ],
                'methods' => [
                    ['tests/Unit/MarketData/CalendarProvenanceAndStatusTest.php', 'test_derived_or_partial_status_cannot_remove_a_bar_from_expectation'],
                    ['tests/Unit/MarketData/CalendarProvenanceAndStatusTest.php', 'test_governed_operator_entry_requires_and_carries_explicit_authority_context'],
                    ['tests/Unit/MarketData/CalendarProvenanceAndStatusTest.php', 'test_exchange_authority_requires_an_accepted_exchange_observation'],
                    ['tests/Unit/MarketData/CalendarProvenanceAndStatusTest.php', 'test_same_priority_authoritative_conflict_holds_instead_of_using_recency'],
                    ['tests/Unit/MarketData/ImportTradingStatusEventsCommandTest.php', 'test_missing_origin_metadata_is_rejected_before_transport_write'],
                    ['tests/Unit/MarketData/ImportTradingStatusEventsCommandTest.php', 'test_operator_entered_transport_requires_named_governed_authority_context'],
                    ['tests/Unit/MarketData/AuthoritativeTradingStatusSnapshotServiceTest.php', 'test_invalid_verifier_result_fails_before_any_status_or_observation_write'],
                ],
            ],
            'status_temporal' => [
                'surfaces' => [
                    'app/Infrastructure/Persistence/MarketData/TemporalTradingStatusRepository.php',
                    'app/Infrastructure/Persistence/MarketData/EventRiskSourceRepository.php',
                ],
                'methods' => [
                    ['tests/Unit/MarketData/CalendarProvenanceAndStatusTest.php', 'test_current_status_does_not_leak_into_an_earlier_date'],
                    ['tests/Unit/MarketData/CalendarProvenanceAndStatusTest.php', 'test_a_long_suspension_is_not_reclassified_as_dormancy'],
                    ['tests/Unit/MarketData/CalendarProvenanceAndStatusTest.php', 'test_exact_date_status_cannot_carry_forward_without_an_end'],
                    ['tests/Unit/MarketData/AsKnownReplayBoundaryTest.php', 'test_a_status_revision_recorded_after_the_cutoff_is_invisible'],
                    ['tests/Unit/MarketData/CoverageDenominatorKnowledgeCutoffTest.php', 'test_denominator_at_a_fixed_cutoff_is_unmoved_by_a_later_recorded_suspension'],
                ],
            ],
            'status_expectation' => [
                'surfaces' => [
                    'app/Application/MarketData/Services/ExpectedBarDecisionService.php',
                    'app/Application/MarketData/Services/EodEligibilityBuildService.php',
                    'app/Infrastructure/Persistence/MarketData/TemporalTradingStatusRepository.php',
                ],
                'methods' => [
                    ['tests/Unit/MarketData/CalendarProvenanceAndStatusTest.php', 'test_absent_status_evidence_resolves_to_unknown_not_normal'],
                    ['tests/Unit/MarketData/CalendarProvenanceAndStatusTest.php', 'test_derived_or_partial_status_cannot_remove_a_bar_from_expectation'],
                    ['tests/Unit/MarketData/CalendarProvenanceAndStatusTest.php', 'test_same_priority_authoritative_conflict_holds_instead_of_using_recency'],
                    ['tests/Unit/MarketData/CalendarProvenanceAndStatusTest.php', 'test_expected_bar_decision_is_explainable_and_half_day_is_normal'],
                    ['tests/Unit/MarketData/MarketDataOrdersOneToFourFoundationTest.php', 'test_calendar_and_trading_status_are_point_in_time_and_fail_safe_on_missing_evidence'],
                ],
            ],
        ];

        $map = [];
        foreach (self::familyAssignment() as $rule => $family) {
            if (! isset($families[$family])) {
                throw new RuntimeException('Unknown B06 proof family '.$family.' for '.$rule);
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
                $rule = MarketDataCalendarStatusTraceabilitySpec::ruleId($document, $number);
                if (isset($assignment[$rule])) {
                    throw new RuntimeException('Duplicate B06 proof family for '.$rule);
                }
                $assignment[$rule] = $family;
            }
        };

        $bind('MD-S041', [1, 2, 3, 4, 6, 7, 8, 9, 10, 11, 12, 13, 15, 17, 18, 19, 20, 22, 23, 24, 27, 28, 31, 63, 64, 68], 'calendar_revision');
        $bind('MD-S041', [34, 35, 36, 38, 39, 41, 50, 51, 52, 66], 'calendar_expectation');

        $bind('MD-S058', [1, 2, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14], 'status_foundation');
        $bind('MD-S058', [15, 18, 19, 20, 21, 22, 23, 24, 25, 26, 27, 28, 29, 31], 'status_authority');
        $bind('MD-S058', [32, 33, 34, 35, 37], 'status_temporal');
        $bind('MD-S058', [39, 40, 41, 43, 49, 50, 53, 54, 56, 62], 'status_expectation');

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
            if ($row['primary_stage'] === MarketDataCalendarStatusTraceabilitySpec::STAGE
                && $row['coverage_requirement'] === 'REQUIRED') {
                $required[$row['rule_id']] = $row;
            }
        }
        ksort($required, SORT_STRING);
        if (array_keys($required) !== array_keys($map)) {
            $errors[] = 'PROOF_MAP: map must exactly cover the current MD-B06 denominator';
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
            if (isset(self::REMEDIATED_RULES[$rule]) && strpos($row['notes'], 'remediated_at=MD-B06-A001') === false) {
                $errors[] = $rule.': remediated rule does not record MD-B06-A001';
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
            .'_CALENDAR_SESSION_AND_TRADING_STATUS.json';
        $evidence = file_exists($evidencePath) ? json_decode(file_get_contents($evidencePath), true) : null;
        if (! is_array($evidence)
            || ($evidence['evidence_id'] ?? null) !== self::EVIDENCE
            || ($evidence['baseline_id'] ?? null) !== 'MD-B06-A001-BL001'
            || ($evidence['change_impact_declaration'] ?? null) !== 'CI-MD-B06-A001-001') {
            $errors[] = 'EVIDENCE: issued proof record is missing, malformed, or miscorrelated';
        }

        $expected = MarketDataCalendarStatusTraceabilitySpec::EXPECTED_B06_DENOMINATOR;
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
    $result = MarketDataCalendarStatusProofGate::validate($rows, $root);
    $result['gate'] = 'MarketDataCalendarStatusProofGate';
    echo json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES).PHP_EOL;
    exit($result['status'] === 'PASS' ? 0 : 1);
}
