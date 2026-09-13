<?php

use PHPUnit\Framework\TestCase;

/**
 * `MD-B18-A002` -- `MD-S004-R0003` and `MD-S004-R0005`, from
 * `Point_In_Time_Backtest_Input_Contract_LOCKED.md`.
 *
 * Both are lists, and both were `PARTIAL` for the same reason recorded against them: one member of
 * each had an executing guard and the binding was filed as though that settled the rest.
 *
 * > Today's universe, symbol, sector, action verification, or current publication may not be
 * > backfilled into an earlier decision.
 *
 * > Inactive/delisted securities remain present when they were in the temporal universe. Symbol
 * > changes/reuse use listing IDs. Late corrections/actions produce a distinct later-known dataset
 * > and do not rewrite the earlier-known dataset.
 *
 * Every member now names a guard that executes it, and both maps are checked against the sentences
 * parsed from the contract rather than transcribed, so a member added there fails here instead of
 * leaving the list quietly short. This class holds no fixtures of its own: each member is exercised
 * somewhere it belongs, and what was missing was the accounting that says so and breaks when it
 * stops being true.
 */
class B18PointInTimeInputContractTest extends TestCase
{
    private const CONTRACT = 'docs/market_data/authority/strategy/backtest/Point_In_Time_Backtest_Input_Contract_LOCKED.md';

    /**
     * `MD-S004-R0003` -- the five things today may not backfill into an earlier decision.
     *
     * @return array<string,string>
     */
    private function noBackfillMap(): array
    {
        return [
            "Today's universe" =>
                'B18AntiSurvivorshipFixtureCorpusTest::test_a_listing_active_at_T_but_delisted_today_stays_in_the_historical_universe',
            'symbol' =>
                'B18AntiSurvivorshipFixtureCorpusTest::test_a_symbol_change_resolves_to_the_symbol_and_mapping_effective_on_the_trade_date',
            'sector' =>
                'B18AntiFutureResolutionTest::test_a_sector_reclassification_recorded_later_is_invisible_at_the_earlier_cutoff',
            'action verification' =>
                'AsKnownReplayBoundaryTest::test_a_corporate_action_recorded_after_the_cutoff_is_invisible',
            'current publication' =>
                'B18ReplayComparisonExhaustivenessTest::test_a_blocked_replay_does_not_fall_back_to_the_current_publication',
        ];
    }

    /**
     * `MD-S004-R0005` -- the three survivorship-and-revision claims.
     *
     * @return array<string,string>
     */
    private function survivorshipMap(): array
    {
        return [
            'Inactive/delisted securities remain present when they were in the temporal universe.' =>
                'B18AntiSurvivorshipFixtureCorpusTest::test_a_listing_active_at_T_but_delisted_today_stays_in_the_historical_universe',
            'Symbol changes/reuse use listing IDs.' =>
                'B18AntiSurvivorshipFixtureCorpusTest::test_reused_symbol_text_resolves_to_the_listing_that_held_it_on_the_trade_date',
            'Late corrections/actions produce a distinct later-known dataset and do not rewrite the earlier-known dataset.' =>
                'B18AsKnownSnapshotIsolationTest::test_a_later_cutoff_exposes_later_revisions_without_rewriting_the_earlier_snapshot',
        ];
    }

    public function test_the_no_backfill_map_names_exactly_what_the_contract_names(): void
    {
        $this->assertSame(1, preg_match(
            '/^(.+?) may not be backfilled into an earlier decision\.$/m',
            $this->contract(),
            $match
        ), 'the MD-S004 no-backfill sentence moved; re-read it rather than weakening this map');

        $items = preg_split('/,\s*or\s+|,\s*/', trim($match[1]));
        $items = array_values(array_filter(array_map('trim', $items)));
        $mapped = array_keys($this->noBackfillMap());
        sort($items);
        sort($mapped);

        $this->assertSame($items, $mapped,
            'MD-S004 and the reviewed map disagree about what today may not backfill into an '
                .'earlier decision');
    }

    public function test_the_survivorship_map_names_exactly_what_the_contract_names(): void
    {
        $this->assertSame(1, preg_match(
            '/^Inactive\/delisted securities remain present.+$/m',
            $this->contract(),
            $match
        ), 'the MD-S004 survivorship paragraph moved; re-read it rather than weakening this map');

        // Three sentences rather than a comma list, so the split is on sentence boundaries.
        preg_match_all('/[^.]+\./', trim($match[0]), $sentences);
        $claims = array_values(array_filter(array_map('trim', $sentences[0])));
        $mapped = array_keys($this->survivorshipMap());
        sort($claims);
        sort($mapped);

        $this->assertSame($claims, $mapped,
            'MD-S004 and the reviewed map disagree about the survivorship and revision claims');
    }

    /**
     * Every guard both maps name must exist. Without this each map is a list of intentions that a
     * rename elsewhere would silently empty.
     */
    public function test_every_guard_both_maps_name_exists_and_is_executable(): void
    {
        $missing = [];

        foreach (array_merge($this->noBackfillMap(), $this->survivorshipMap()) as $item => $ref) {
            [$class, $method] = explode('::', $ref);
            $file = __DIR__.'/'.$class.'.php';
            if (! is_file($file)) {
                $missing[] = $item.' -> '.$class.' (file not found)';

                continue;
            }
            if (strpos((string) file_get_contents($file), 'function '.$method.'(') === false) {
                $missing[] = $item.' -> '.$ref;
            }
        }

        $this->assertSame([], $missing, 'these contract members name a guard that no longer exists');
    }

    /**
     * The two maps must not collapse onto one guard. `MD-S004` names eight members between them and
     * a binding that pointed them all at the same test would satisfy both maps above while proving
     * one thing -- which is the shape `F-MD-B19-A001-002` records.
     */
    public function test_the_members_are_not_all_bound_to_a_single_guard(): void
    {
        $guards = array_unique(array_merge(
            array_values($this->noBackfillMap()),
            array_values($this->survivorshipMap())
        ));

        $this->assertGreaterThanOrEqual(5, count($guards),
            'eight contract members resolve to fewer than five distinct guards; a family is being '
                .'treated as a predicate');
    }

    /**
     * `MD-S004-R0002` -- the input kinds a decision may contain, each bounded by the declared
     * cutoff.
     *
     * > Inputs contain only observations and identity, calendar, status, event, factor, config, and
     * > formula revisions recorded/known by that cutoff and effective for the evaluated context.
     *
     * @return array<string,string>
     */
    private function cutoffBoundedInputMap(): array
    {
        return [
            'observations' =>
                'SourceObservationAsKnownBoundaryTest::test_as_known_rows_require_both_observation_and_identity_binding_to_be_known_by_cutoff',
            'identity' =>
                'AsKnownReplayBoundaryTest::test_identity_recorded_after_the_cutoff_is_invisible',
            'calendar' =>
                'B18AsKnownTemporalSequenceTest::test_the_same_calendar_revision_recorded_after_the_cutoff_is_refused',
            'status' =>
                'B18AsKnownTemporalSequenceTest::test_a_suspension_lifted_later_is_still_suspended_as_known_before_the_lift_was_recorded',
            'event' =>
                'AsKnownReplayBoundaryTest::test_a_corporate_action_recorded_after_the_cutoff_is_invisible',
            'factor' =>
                'B18AsKnownSnapshotIsolationTest::test_a_later_cutoff_exposes_later_revisions_without_rewriting_the_earlier_snapshot',
            'config' =>
                'AsKnownReplayBoundaryTest::test_a_configuration_recorded_after_the_cutoff_is_invisible_and_none_is_created',
            'formula' =>
                'B18AsKnownSnapshotIsolationTest::test_a_later_cutoff_exposes_later_revisions_without_rewriting_the_earlier_snapshot',
        ];
    }

    /**
     * `MD-S004-R0002` -- the map and the contract must name the same input kinds.
     */
    public function test_the_cutoff_bounded_input_map_names_exactly_what_the_contract_names(): void
    {
        $this->assertSame(1, preg_match(
            '/Inputs contain only (.+?) revisions recorded\/known by that cutoff and effective for the evaluated context\./s',
            $this->contract(),
            $match
        ), 'the MD-S004 knowledge-time sentence moved; re-read it rather than weakening this map');

        // "observations and identity, calendar, status, event, factor, config, and formula"
        $items = preg_split('/,\s*and\s+|,\s*|\s+and\s+/', trim($match[1]));
        $items = array_values(array_filter(array_map('trim', $items)));
        $mapped = array_keys($this->cutoffBoundedInputMap());
        sort($items);
        sort($mapped);

        $this->assertSame($items, $mapped,
            'MD-S004 and the reviewed map disagree about which inputs a decision may contain');
    }

    /**
     * Each declared cutoff has to be honoured by every input kind, so every guard named above must
     * exist. A cutoff honoured by seven of eight leaks.
     */
    public function test_every_cutoff_bounded_input_guard_exists(): void
    {
        $missing = [];

        foreach ($this->cutoffBoundedInputMap() as $item => $ref) {
            [$class, $method] = explode('::', $ref);
            $file = __DIR__.'/'.$class.'.php';
            if (! is_file($file) || strpos((string) file_get_contents($file), 'function '.$method.'(') === false) {
                $missing[] = $item.' -> '.$ref;
            }
        }

        $this->assertSame([], $missing, 'these input kinds name a guard that no longer exists');
    }
    /**
     * `MD-S004-R0008` -- the seven acceptance fixtures the contract requires at minimum.
     *
     * @return array<string,string>
     */
    private function acceptanceFixtureMap(): array
    {
        return [
            'inactive-now/active-then membership' =>
                'B18AntiSurvivorshipFixtureCorpusTest::test_a_listing_active_at_T_but_delisted_today_stays_in_the_historical_universe',
            'symbol transition/reuse' =>
                'B18AntiSurvivorshipFixtureCorpusTest::test_reused_symbol_text_resolves_to_the_listing_that_held_it_on_the_trade_date',
            'late action verification' =>
                'AsKnownReplayBoundaryTest::test_a_corporate_action_recorded_after_the_cutoff_is_invisible',
            'late config/calendar/status correction' =>
                'B18AsKnownTemporalSequenceTest::test_a_suspension_lifted_later_is_still_suspended_as_known_before_the_lift_was_recorded',
            'unavailable same-day data' =>
                'EmptyDatasetFailSafeTest::test_no_fallback_fails_the_run_outright',
            'explicit stale fallback' =>
                'EmptyDatasetFailSafeTest::test_an_available_fallback_holds_the_run_rather_than_failing_it',
            'original-versus-corrected as-known datasets' =>
                'B18CorrectionReadPathScenarioTest::test_a_consumer_reads_the_publication_the_pointer_names',
        ];
    }

    /**
     * `MD-S004-R0008` -- the map and the contract must name the same fixtures.
     */
    public function test_the_acceptance_fixture_map_names_exactly_what_the_contract_names(): void
    {
        $this->assertSame(1, preg_match(
            '/^At minimum prove (.+?)\.$/m',
            $this->contract(),
            $match
        ), 'the MD-S004 acceptance-fixtures sentence moved; re-read it rather than weakening this map');

        $items = preg_split('/,\s*and\s+|,\s*/', trim($match[1]));
        $items = array_values(array_filter(array_map('trim', $items)));
        $mapped = array_keys($this->acceptanceFixtureMap());
        sort($items);
        sort($mapped);

        $this->assertSame($items, $mapped,
            'MD-S004 and the reviewed map disagree about which acceptance fixtures must be proven');
    }

    /**
     * Every acceptance fixture must name a guard that exists, and the seven must not collapse onto
     * one: "at minimum prove" is a floor over seven distinct scenarios, and a binding that pointed
     * them all at one test would satisfy the map above while proving one of them.
     */
    public function test_every_acceptance_fixture_guard_exists_and_they_are_distinct(): void
    {
        $missing = [];
        foreach ($this->acceptanceFixtureMap() as $item => $ref) {
            [$class, $method] = explode('::', $ref);
            $file = __DIR__.'/'.$class.'.php';
            if (! is_file($file) || strpos((string) file_get_contents($file), 'function '.$method.'(') === false) {
                $missing[] = $item.' -> '.$ref;
            }
        }
        $this->assertSame([], $missing, 'these acceptance fixtures name a guard that no longer exists');

        $this->assertGreaterThanOrEqual(6, count(array_unique(array_values($this->acceptanceFixtureMap()))),
            'seven acceptance fixtures resolve to fewer than six distinct guards');
    }
    private function contract(): string
    {
        $path = dirname(__DIR__, 3).'/'.self::CONTRACT;
        $this->assertFileExists($path);

        return (string) file_get_contents($path);
    }
}
