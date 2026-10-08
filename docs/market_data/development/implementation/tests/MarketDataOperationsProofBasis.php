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

        // ---- MD-S053 "API range-window checkpoint/resume addendum" -- family
        // `range_window_checkpoint_resume`, 13 of 13 predicates. `MD-S053-R0210` was held until the project
        // owner decided F-MD-B19-A001-004 (Option A, D-MD-B19-A001-002) and the implementation was corrected.
        // Every scenario mixes a
        // provider-rejected ticker, a retried-timeout ticker and a success in one window, in both
        // iteration orders, because a leak from the previous ticker and a leak from the window's first
        // failure are different defects. The orchestrator-level guards run the real orchestrator,
        // acquisition service and provider adapter over the real calendar with a stubbed HTTP fetcher.
        'MD-S053-R0202' => [
            'positive' => 'B19RangeWindowCheckpointPersistenceTest::test_the_backfill_persists_one_checkpoint_per_window_and_ticker_and_its_diagnostics_agree_with_the_file',
            'negative' => 'B19RangeWindowCheckpointIdentityTest::test_every_window_and_ticker_pair_has_its_own_checkpoint_carrying_that_identity',
            'basis' => 'three windows x two tickers give six rows with a failure confined to one pair, the real orchestrator writes exactly those rows to source_acquisition_checkpoint.json, and a resume merges its row back without dropping the others; keying by ticker, dropping window_end from the key, not writing the file and overwriting on merge each turn a guard red',
        ],
        'MD-S053-R0204' => [
            'positive' => 'B19RangeWindowCheckpointIdentityTest::test_every_window_and_ticker_pair_has_its_own_checkpoint_carrying_that_identity',
            'negative' => 'ApiBackfillRangeAcquisitionServiceTest::test_http_400_checkpoint_keeps_ticker_window_url_and_provider_error_for_same_ticker',
            'basis' => 'window_start of every one of six rows equals the window part of its key, and a failed row asserts it separately; writing the window end into window_start turns both red',
        ],
        'MD-S053-R0205' => [
            'positive' => 'B19RangeWindowCheckpointIdentityTest::test_every_window_and_ticker_pair_has_its_own_checkpoint_carrying_that_identity',
            'negative' => 'ApiBackfillRangeAcquisitionServiceTest::test_http_400_checkpoint_keeps_ticker_window_url_and_provider_error_for_same_ticker',
            'basis' => 'window_end of every one of six rows equals the window part of its key, and a failed row asserts it separately; writing the window start into window_end turns both red',
        ],
        'MD-S053-R0206' => [
            'positive' => 'B19RangeWindowCheckpointIdentityTest::test_every_window_and_ticker_pair_has_its_own_checkpoint_carrying_that_identity',
            'negative' => 'ApiBackfillRangeAcquisitionServiceTest::test_http_400_checkpoint_keeps_ticker_window_url_and_provider_error_for_same_ticker',
            'basis' => 'ticker_code of every one of six rows equals the ticker part of its key, and the persisted file keeps it; changing its case turns the guards red',
        ],
        'MD-S053-R0208' => [
            'positive' => 'B19RangeWindowCheckpointIdentityTest::test_a_failed_checkpoint_takes_every_failure_field_from_its_own_identity',
            'negative' => 'B19RangeWindowCheckpointIdentityTest::test_rows_count_is_the_failed_tickers_own_returned_rows',
            'basis' => 'a rejected ticker (HTTP 400 and its own body, one attempt) and a retried timeout (three attempts, no status) in one window: reason_code, http_status, error_sample, provider_error_sample, sanitized_url, failure_scope, attempt_count and rows_count are each asserted per row, in both orders, and neither row mentions the other ticker; the first-failure context for all rows, a window-level sample, URL, reason code, attempt count or rows count each turn a guard red',
        ],
        'MD-S053-R0209' => [
            'positive' => 'B19RangeWindowCheckpointIdentityTest::test_a_failed_checkpoint_takes_every_failure_field_from_its_own_identity',
            'negative' => 'ApiBackfillRangeAcquisitionServiceTest::test_timeout_checkpoint_does_not_reuse_successful_ticker_http_status_or_error_sample',
            'basis' => 'the timeout row keeps http_status null and provider_error_sample null although a different ticker in the same window failed with HTTP 400 and a body, in both iteration orders; inheriting the window status or provider body turns the positive guard red',
        ],
        'MD-S053-R0210' => [
            'positive' => 'B19RangeWindowCheckpointIdentityTest::test_a_success_checkpoint_carries_the_status_and_attempts_of_its_own_request',
            'negative' => 'B19RangeWindowCheckpointIdentityTest::test_a_neighbours_failure_status_and_retries_do_not_change_a_success_row',
            'basis' => 'under Option A (D-MD-B19-A001-002) a success row carries the status (200) and attempts (2) of its own request while neighbours reject, time out and need retries; three neighbour configurations leave it identical while the window total changes; a success after a failed neighbour keeps its own status; two successes keep their own attempts in both assignments; the sample, scope and reason fields stay null; restoring the window status or attempt total, swapping identities, keeping a failure-derived status, hiding the per-ticker request result or the window totals each turn a guard red',
        ],
        'MD-S053-R0212' => [
            'positive' => 'B19RangeWindowCheckpointIdentityTest::test_each_resume_state_is_reported_only_in_its_own_situation',
            'negative' => 'B19RangeWindowCheckpointPersistenceTest::test_a_resume_retries_only_the_persisted_failure_and_merges_it_back_into_the_file',
            'basis' => 'RETRY_SUCCESS only when every eligible failed checkpoint recovers, in a four-row state table and end to end through the orchestrator; reporting it as PARTIAL_RETRY_SUCCESS, or the reverse, turns the table red',
        ],
        'MD-S053-R0213' => [
            'positive' => 'B19RangeWindowCheckpointIdentityTest::test_each_resume_state_is_reported_only_in_its_own_situation',
            'negative' => 'ApiBackfillRangeAcquisitionServiceTest::test_resume_only_failed_partial_retry_success_state_is_not_systemic',
            'basis' => 'PARTIAL_RETRY_SUCCESS only when some recover and some do not (1 success, 1 failure), and it is not systemic; swapping it with RETRY_SUCCESS turns the table red',
        ],
        'MD-S053-R0214' => [
            'positive' => 'B19RangeWindowCheckpointIdentityTest::test_each_resume_state_is_reported_only_in_its_own_situation',
            'negative' => 'B19RangeWindowCheckpointPersistenceTest::test_a_resume_that_still_fails_reports_its_counts_and_a_failure_sample_that_agrees_with_the_file',
            'basis' => 'FAILED_RETRY_BLOCKED when every retried checkpoint fails again, in the table and end to end with the persisted file; reporting it as SYSTEMIC_FAILED turns the table red',
        ],
        'MD-S053-R0215' => [
            'positive' => 'B19RangeWindowCheckpointIdentityTest::test_each_resume_state_is_reported_only_in_its_own_situation',
            'negative' => 'B19RangeWindowCheckpointPersistenceTest::test_a_resume_over_a_file_with_no_failed_checkpoint_is_a_reported_no_op',
            'basis' => 'NO_FAILED_CHECKPOINT when no checkpoint had failed, with zero provider requests, in the table and through the orchestrator (status NOOP, zero counts); reporting success instead, or fetching every ticker, turns the table red. The orchestrator branch that short-circuits this case is redundant with the service path (two equivalent mutants), so the outcome is held by two independent paths',
        ],
        'MD-S053-R0216' => [
            'positive' => 'B19RangeWindowCheckpointIdentityTest::test_systemic_failed_is_reported_only_for_global_provider_or_config_failures',
            'negative' => 'B19RangeWindowResumeStateTest::test_repeated_ticker_failures_are_not_escalated_to_systemic',
            'basis' => 'authentication refused (401, 403), a provider range rejection (422) and a missing endpoint configuration escalate to SYSTEMIC_FAILED; a one-ticker timeout, bad request or unknown symbol stays FAILED_RETRY_BLOCKED; escalating timeouts, bad requests or any ticker-scoped failure, or not escalating authentication or range rejection, turns the guard red. CONFIG_INVALID in the escalation list is not reachable from this service in a controlled way and is not claimed',
        ],
        'MD-S053-R0217' => [
            'positive' => 'B19RangeWindowCheckpointIdentityTest::test_resume_diagnostics_carry_every_count_and_the_skipped_reasons',
            'negative' => 'B19RangeWindowCheckpointPersistenceTest::test_a_resume_that_still_fails_reports_its_counts_and_a_failure_sample_that_agrees_with_the_file',
            'basis' => 'a mixed checkpoint file yields total 5, eligible 2, retried 2, retry success 1, retry failure 1, skipped 3 with the three skipped reasons, and the quantities are tied (total = eligible + skipped, retried = success + failure, skipped = sum of reasons); the written diagnostics file repeats the counts and its failure sample equals the checkpoint file row field by field; dropping or mislabelling any count or reason, or building the sample from window telemetry, turns a guard red',
        ],
    ];
}
