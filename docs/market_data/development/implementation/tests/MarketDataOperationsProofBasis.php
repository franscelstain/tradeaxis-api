<?php

/**
 * `MD-B19-A001` accumulating proof basis -- one entry per predicate this attempt has established.
 *
 * This is the artifact `F-MD-B19-A001-002` requires. A predicate is listed here only once a guard
 * has been written or verified **for that predicate**, executed, and shown able to fail under an
 * injected defect. Membership of a proof family is not a reason to appear here:
 * `MarketDataOperationsProofSpec::RULE_FAMILIES` records the family-level grouping, which is a
 * review aid and not proof.
 *
 * The file is expected to be incomplete while the attempt is open. `MarketDataOperationsProofGate`
 * reports the shortfall by name rather than passing on the rows that are present.
 *
 * `positive` is the guard that asserts the conforming behaviour. `negative` is the guard that
 * asserts the refusal or discrimination side of the same predicate -- the half an implementation
 * that satisfied only the positive guard would get wrong. Both are `Class::method` references into
 * `tests/Unit/MarketData`, and the gate fails if either does not exist.
 *
 * `basis` states how the guards establish the predicate, in the sentence a reviewer needs in order
 * to disagree. A predicate whose guard establishes only part of it does not appear here; it stays
 * without a basis and is counted as such (`F-MD-B18-A002-008`).
 */
final class MarketDataOperationsProofBasis
{
    public const ATTEMPT = 'MD-B19-A001';

    /** @var array<string,array<string,string>> */
    public const PROVEN = [
        // ---- MD-S053 "API range-window market-calendar warmup addendum" -- family
        // `range_window_warmup_calendar`. The addendum has two halves that pull in opposite
        // directions (fail fast for a non-trading requested date; do not fail fast at the dataset
        // start), so no predicate below is claimed from a guard that exercises only one of them.
        // Two guard files cooperate: `B19RangeWindowWarmupCalendarTest` drives the resolver
        // itself, and `B19RangeWindowWarmupWiringTest` drives the real orchestrator against the
        // real calendar repository and observes what acquisition is handed and what the run
        // records. A resolver that is right but unused would satisfy the first and fail the second.
        'MD-S053-R0218' => [
            'positive' => 'B19RangeWindowWarmupWiringTest::test_the_acquisition_service_is_handed_the_calendar_boundary',
            'negative' => 'B19RangeWindowWarmupWiringTest::test_an_unprovable_calendar_blocks_before_acquisition_and_publication',
            'basis' => 'the real orchestrator hands the acquisition service the calendar-resolved boundary and the calendar-resolved trading dates for two different horizons, and hands it nothing when the calendar cannot resolve one; handing requested_start or a calendar-day date turns the positive guard red',
        ],
        'MD-S053-R0219' => [
            'positive' => 'B19RangeWindowWarmupCalendarTest::test_warmup_start_is_the_governed_trading_day_boundary_not_calendar_day_arithmetic',
            'negative' => 'B19RangeWindowWarmupWiringTest::test_the_acquisition_service_is_handed_the_calendar_boundary',
            'basis' => 'the seeded closure makes N trading days back and N calendar days back different dates; the resolver result and the date acquisition is handed are both asserted to differ from requested_start minus N calendar days',
        ],
        'MD-S053-R0220' => [
            'positive' => 'B19RangeWindowWarmupCalendarTest::test_warmup_start_is_the_governed_trading_day_boundary_not_calendar_day_arithmetic',
            'negative' => 'B19RangeWindowWarmupWiringTest::test_the_acquisition_service_is_handed_the_calendar_boundary',
            'basis' => 'the window must span exactly the configured number of trading days and the boundary must move by exactly one trading date when the count moves by one, so a fixed buffer added to the count fails both guards',
        ],
        'MD-S053-R0221' => [
            'positive' => 'B19RangeWindowWarmupWiringTest::test_an_unprovable_calendar_blocks_before_acquisition_and_publication',
            'negative' => 'B19RangeWindowWarmupCalendarTest::test_a_non_trading_requested_date_fails_fast',
            'basis' => 'four unprovable-calendar conditions (no trading date in range, unverified provenance, missing calendar foundation, conflicting live revisions) each stop the real orchestrator before the acquisition service is called, with a pipeline mock that has no expectations and every publication table still empty; the resolver refuses a non-trading anchor instead of approximating. Scope read as: the calendar cannot resolve the boundary. Whether a calendar loaded later than dataset_start must also block is not asserted (see the note on MD-S053-R0224 and the contract line 314/355 tension)',
        ],
        'MD-S053-R0222' => [
            'positive' => 'B19RangeWindowWarmupCalendarTest::test_warmup_start_is_the_governed_trading_day_boundary_not_calendar_day_arithmetic',
            'negative' => 'B19RangeWindowWarmupCalendarTest::test_the_dataset_start_boundary_caps_the_warmup_instead_of_failing',
            'basis' => 'the resolver equals tradingDateWindowStart over a closure sequence, is itself a verified trading date and spans N trading days; a horizon longer than the dataset caps at the first available trading date',
        ],
        'MD-S053-R0223' => [
            'positive' => 'B19RangeWindowWarmupCalendarTest::test_a_non_trading_requested_date_fails_fast',
            'negative' => 'B19RangeWindowWarmupWiringTest::test_an_unprovable_calendar_blocks_before_acquisition_and_publication',
            'basis' => 'a closure used as the anchor raises MARKET_CALENDAR_REQUIRES_REQUESTED_TRADING_DATE at the resolver, and a lifecycle request whose every date is a closure stops with no acquisition and no publication',
        ],
        'MD-S053-R0224' => [
            'positive' => 'B19RangeWindowWarmupCalendarTest::test_the_dataset_start_boundary_caps_the_warmup_instead_of_failing',
            'negative' => 'IndicatorVectorServiceTest::test_short_history_calculates_each_field_as_soon_as_its_own_warmup_is_met',
            'basis' => 'the cap half: a 500-day horizon resolves to the first available trading date instead of failing; the NULL-per-field half: a 20-bar history yields roc5/roc10/dv20/atr14/hh20/ll20/ma20 and NULL roc20/ma20_slope/ma50 on the real IndicatorVectorService, so a zero-filled or fail-fast implementation turns it red',
        ],
        'MD-S053-R0225' => [
            'positive' => 'B19RangeWindowWarmupWiringTest::test_the_recorded_plan_carries_the_calendar_boundary_and_the_four_telemetry_fields',
            'negative' => 'B19RangeWindowWarmupWiringTest::test_a_blocked_acquisition_still_records_the_boundary_in_its_diagnostic',
            'basis' => 'warmup_start, requested_start, requested_end and source_acquisition_mode=range_window are asserted in the returned plan, in the written run summary, and in the diagnostic a blocked acquisition writes; dropping any one from the summary, or recording the wrong mode, turns the guards red',
        ],
    ];
}
