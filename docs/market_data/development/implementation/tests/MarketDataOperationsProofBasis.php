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

        // ---- MD-S075 section 1 "run_summary.json" -- family `artifact_run_summary`, 52 of 52 predicates. Three were held for owner
        // decisions and are entered after D-MD-B19-A001-003: MD-S075-R0047 (F-MD-B19-A001-005 Option B: warning_count mirrors the
        // persisted value incl. NULL, an ACCEPTED LIMITATION -- nothing writes the counter), MD-S075-R0074 and MD-S075-R0075
        // (F-MD-B19-A001-006 Option A: final_reason_code strictly mirrors the persisted column; the effective reason is a separate
        // derived field and derived fields are listed in derived_companion_fields). The earlier guard proved every minimum field is
        // a KEY of the file; these entries are value proofs: a run record in which every column differs, the manifest of the run's
        // own publication against decoys, and a real sealed run read directly from the tables.
        'MD-S075-R0025' => [
            'positive' => 'B19RunSummaryValueProvenanceTest::test_a_mirrored_field_carries_the_value_of_its_own_persisted_column',
            'negative' => 'B19RunSummaryRealRunProvenanceTest::test_every_summary_key_named_like_an_eod_runs_column_carries_that_columns_value',
            'basis' => 'E023 impact revalidation: real source/run/event/selected-and-current publication exports plus focused current run-summary/event/companion regressions retain exact scalar meanings and source guards; changed narrative bytes rehashed, absent/contradictory source evidence incomplete. No replay semantics invalidation or historical mutation replay. Prior basis: run_id carries the value of its own run_id column in a run record where every column differs, a persisted NULL stays NULL, and a real sealed run agrees with eod_runs; reading it from the publication id turns the mirror guard red (A01)',
        ],
        'MD-S075-R0026' => [
            'positive' => 'B19RunSummaryValueProvenanceTest::test_a_mirrored_field_carries_the_value_of_its_own_persisted_column',
            'negative' => 'B19RunSummaryRealRunProvenanceTest::test_every_summary_key_named_like_an_eod_runs_column_carries_that_columns_value',
            'basis' => 'E023 impact revalidation: real source/run/event/selected-and-current publication exports plus focused current run-summary/event/companion regressions retain exact scalar meanings and source guards; changed narrative bytes rehashed, absent/contradictory source evidence incomplete. No replay semantics invalidation or historical mutation replay. Prior basis: trade_date_requested carries the value of its own trade_date_requested column in a run record where every column differs, a persisted NULL stays NULL, and a real sealed run agrees with eod_runs; replacing it by the effective date turns it red (G01)',
        ],
        'MD-S075-R0027' => [
            'positive' => 'B19RunSummaryValueProvenanceTest::test_a_mirrored_field_carries_the_value_of_its_own_persisted_column',
            'negative' => 'B19RunSummaryValueProvenanceTest::test_a_persisted_null_is_exported_as_null_not_as_a_default',
            'basis' => 'E023 impact revalidation: real source/run/event/selected-and-current publication exports plus focused current run-summary/event/companion regressions retain exact scalar meanings and source guards; changed narrative bytes rehashed, absent/contradictory source evidence incomplete. No replay semantics invalidation or historical mutation replay. Prior basis: trade_date_effective carries the value of its own trade_date_effective column in a run record where every column differs, a persisted NULL stays NULL, and a real sealed run agrees with eod_runs; replacing it by the requested date turns it red (A02)',
        ],
        'MD-S075-R0028' => [
            'positive' => 'B19RunSummaryValueProvenanceTest::test_a_mirrored_field_carries_the_value_of_its_own_persisted_column',
            'negative' => 'B19RunSummaryRealRunProvenanceTest::test_every_summary_key_named_like_an_eod_runs_column_carries_that_columns_value',
            'basis' => 'E023 impact revalidation: real source/run/event/selected-and-current publication exports plus focused current run-summary/event/companion regressions retain exact scalar meanings and source guards; changed narrative bytes rehashed, absent/contradictory source evidence incomplete. No replay semantics invalidation or historical mutation replay. Prior basis: lifecycle_state carries the value of its own lifecycle_state column in a run record where every column differs, a persisted NULL stays NULL, and a real sealed run agrees with eod_runs; reading it from the stage turns it red (A03)',
        ],
        'MD-S075-R0029' => [
            'positive' => 'B19RunSummaryValueProvenanceTest::test_a_mirrored_field_carries_the_value_of_its_own_persisted_column',
            'negative' => 'B19RunSummaryRealRunProvenanceTest::test_every_summary_key_named_like_an_eod_runs_column_carries_that_columns_value',
            'basis' => 'E023 impact revalidation: real source/run/event/selected-and-current publication exports plus focused current run-summary/event/companion regressions retain exact scalar meanings and source guards; changed narrative bytes rehashed, absent/contradictory source evidence incomplete. No replay semantics invalidation or historical mutation replay. Prior basis: terminal_status carries the value of its own terminal_status column in a run record where every column differs, a persisted NULL stays NULL, and a real sealed run agrees with eod_runs; reading it from the quality gate turns the sentinel and the real-run guards red (A04)',
        ],
        'MD-S075-R0030' => [
            'positive' => 'B19RunSummaryValueProvenanceTest::test_a_mirrored_field_carries_the_value_of_its_own_persisted_column',
            'negative' => 'B19RunSummaryRealRunProvenanceTest::test_every_summary_key_named_like_an_eod_runs_column_carries_that_columns_value',
            'basis' => 'E023 impact revalidation: real source/run/event/selected-and-current publication exports plus focused current run-summary/event/companion regressions retain exact scalar meanings and source guards; changed narrative bytes rehashed, absent/contradictory source evidence incomplete. No replay semantics invalidation or historical mutation replay. Prior basis: quality_gate_state carries the value of its own quality_gate_state column in a run record where every column differs, a persisted NULL stays NULL, and a real sealed run agrees with eod_runs; reading it from the terminal status turns it red (G02)',
        ],
        'MD-S075-R0031' => [
            'positive' => 'B19RunSummaryValueProvenanceTest::test_a_mirrored_field_carries_the_value_of_its_own_persisted_column',
            'negative' => 'B19RunSummaryRealRunProvenanceTest::test_every_summary_key_named_like_an_eod_runs_column_carries_that_columns_value',
            'basis' => 'E023 impact revalidation: real source/run/event/selected-and-current publication exports plus focused current run-summary/event/companion regressions retain exact scalar meanings and source guards; changed narrative bytes rehashed, absent/contradictory source evidence incomplete. No replay semantics invalidation or historical mutation replay. Prior basis: publishability_state carries the value of its own publishability_state column in a run record where every column differs, a persisted NULL stays NULL, and a real sealed run agrees with eod_runs; reading it from the terminal status turns it red (G03)',
        ],
        'MD-S075-R0032' => [
            'positive' => 'B19RunSummaryValueProvenanceTest::test_a_mirrored_field_carries_the_value_of_its_own_persisted_column',
            'negative' => 'B19RunSummaryRealRunProvenanceTest::test_every_summary_key_named_like_an_eod_runs_column_carries_that_columns_value',
            'basis' => 'E023 impact revalidation: real source/run/event/selected-and-current publication exports plus focused current run-summary/event/companion regressions retain exact scalar meanings and source guards; changed narrative bytes rehashed, absent/contradictory source evidence incomplete. No replay semantics invalidation or historical mutation replay. Prior basis: stage carries the value of its own stage column in a run record where every column differs, a persisted NULL stays NULL, and a real sealed run agrees with eod_runs; swapping it with the lifecycle state turns it red (A05)',
        ],
        'MD-S075-R0033' => [
            'positive' => 'B19RunSummaryValueProvenanceTest::test_a_mirrored_field_carries_the_value_of_its_own_persisted_column',
            'negative' => 'B19RunSummaryRealRunProvenanceTest::test_every_summary_key_named_like_an_eod_runs_column_carries_that_columns_value',
            'basis' => 'E023 impact revalidation: real source/run/event/selected-and-current publication exports plus focused current run-summary/event/companion regressions retain exact scalar meanings and source guards; changed narrative bytes rehashed, absent/contradictory source evidence incomplete. No replay semantics invalidation or historical mutation replay. Prior basis: source carries the value of its own source column in a run record where every column differs, a persisted NULL stays NULL, and a real sealed run agrees with eod_runs; reading it from the request mode turns it red (A06)',
        ],
        'MD-S075-R0034' => [
            'positive' => 'B19RunSummaryValueProvenanceTest::test_source_context_carries_its_minimum_fields_from_the_persisted_source_columns',
            'negative' => 'B19RunSummaryValueProvenanceTest::test_source_context_invents_no_source_fact_the_run_never_recorded',
            'basis' => 'E023 impact revalidation: real source/run/event/selected-and-current publication exports plus focused current run-summary/event/companion regressions retain exact scalar meanings and source guards; changed narrative bytes rehashed, absent/contradictory source evidence incomplete. No replay semantics invalidation or historical mutation replay. Prior basis: source_context is an array block carrying the six minimum fields, from persisted columns or recovered from persisted notes; truncating the block turns the guards red (G04b)',
        ],
        'MD-S075-R0035' => [
            'positive' => 'B19RunSummaryValueProvenanceTest::test_source_context_carries_its_minimum_fields_from_the_persisted_source_columns',
            'negative' => 'B19RunSummaryValueProvenanceTest::test_source_context_invents_no_source_fact_the_run_never_recorded',
            'basis' => 'E023 impact revalidation: real source/run/event/selected-and-current publication exports plus focused current run-summary/event/companion regressions retain exact scalar meanings and source guards; changed narrative bytes rehashed, absent/contradictory source evidence incomplete. No replay semantics invalidation or historical mutation replay. Prior basis: source_name comes from the source_name column, is recovered from notes when the column is thin, and is null when nothing was recorded; reading it from the provider column or dropping the notes recovery turns a guard red (B01, B02)',
        ],
        'MD-S075-R0036' => [
            'positive' => 'B19RunSummaryValueProvenanceTest::test_source_context_carries_its_minimum_fields_from_the_persisted_source_columns',
            'negative' => 'B19RunSummaryValueProvenanceTest::test_source_context_recovers_thin_minimum_fields_from_persisted_notes_only',
            'basis' => 'E023 impact revalidation: real source/run/event/selected-and-current publication exports plus focused current run-summary/event/companion regressions retain exact scalar meanings and source guards; changed narrative bytes rehashed, absent/contradictory source evidence incomplete. No replay semantics invalidation or historical mutation replay. Prior basis: source_input_file comes from the source_input_file column or the notes; reading it from the file hash turns the guard red (B09)',
        ],
        'MD-S075-R0037' => [
            'positive' => 'B19RunSummaryValueProvenanceTest::test_source_context_carries_its_minimum_fields_from_the_persisted_source_columns',
            'negative' => 'B19RunSummaryValueProvenanceTest::test_source_context_invents_no_source_fact_the_run_never_recorded',
            'basis' => 'E023 impact revalidation: real source/run/event/selected-and-current publication exports plus focused current run-summary/event/companion regressions retain exact scalar meanings and source guards; changed narrative bytes rehashed, absent/contradictory source evidence incomplete. No replay semantics invalidation or historical mutation replay. Prior basis: attempt_count comes from source_attempt_count (4) or the notes (5) and is null when unrecorded; reading the retry maximum, or defaulting to 1, turns a guard red (B03, B07)',
        ],
        'MD-S075-R0038' => [
            'positive' => 'B19RunSummaryValueProvenanceTest::test_source_context_carries_its_minimum_fields_from_the_persisted_source_columns',
            'negative' => 'B19RunSummaryValueProvenanceTest::test_source_context_invents_no_source_fact_the_run_never_recorded',
            'basis' => 'E023 impact revalidation: real source/run/event/selected-and-current publication exports plus focused current run-summary/event/companion regressions retain exact scalar meanings and source guards; changed narrative bytes rehashed, absent/contradictory source evidence incomplete. No replay semantics invalidation or historical mutation replay. Prior basis: success_after_retry is yes for a persisted 1, no for a persisted 0 and null when unrecorded; inverting it turns the guard red (B04)',
        ],
        'MD-S075-R0039' => [
            'positive' => 'B19RunSummaryValueProvenanceTest::test_source_context_carries_its_minimum_fields_from_the_persisted_source_columns',
            'negative' => 'B19RunSummaryValueProvenanceTest::test_source_context_invents_no_source_fact_the_run_never_recorded',
            'basis' => 'E023 impact revalidation: real source/run/event/selected-and-current publication exports plus focused current run-summary/event/companion regressions retain exact scalar meanings and source guards; changed narrative bytes rehashed, absent/contradictory source evidence incomplete. No replay semantics invalidation or historical mutation replay. Prior basis: final_http_status comes from source_final_http_status (503) or the notes (429) and is null when unrecorded; reading the timeout column turns the guard red (B05)',
        ],
        'MD-S075-R0040' => [
            'positive' => 'B19RunSummaryValueProvenanceTest::test_source_context_carries_its_minimum_fields_from_the_persisted_source_columns',
            'negative' => 'B19RunSummaryValueProvenanceTest::test_source_context_invents_no_source_fact_the_run_never_recorded',
            'basis' => 'E023 impact revalidation: real source/run/event/selected-and-current publication exports plus focused current run-summary/event/companion regressions retain exact scalar meanings and source guards; changed narrative bytes rehashed, absent/contradictory source evidence incomplete. No replay semantics invalidation or historical mutation replay. Prior basis: final_reason_code comes from source_final_reason_code or the notes and is null when unrecorded; exporting an UNKNOWN stand-in turns a guard red (B06)',
        ],
        'MD-S075-R0041' => [
            'positive' => 'B19RunSummaryValueProvenanceTest::test_a_mirrored_field_carries_the_value_of_its_own_persisted_column',
            'negative' => 'B19RunSummaryValueProvenanceTest::test_a_persisted_null_is_exported_as_null_not_as_a_default',
            'basis' => 'E023 impact revalidation: real source/run/event/selected-and-current publication exports plus focused current run-summary/event/companion regressions retain exact scalar meanings and source guards; changed narrative bytes rehashed, absent/contradictory source evidence incomplete. No replay semantics invalidation or historical mutation replay. Prior basis: coverage_ratio carries the value of its own coverage_ratio column in a run record where every column differs, a persisted NULL stays NULL, and a real sealed run agrees with eod_runs; defaulting an absent ratio to 0 turns the NULL guard red (C01)',
        ],
        'MD-S075-R0042' => [
            'positive' => 'B19RunSummaryValueProvenanceTest::test_a_mirrored_field_carries_the_value_of_its_own_persisted_column',
            'negative' => 'B19RunSummaryValueProvenanceTest::test_a_persisted_null_is_exported_as_null_not_as_a_default',
            'basis' => 'E023 impact revalidation: real source/run/event/selected-and-current publication exports plus focused current run-summary/event/companion regressions retain exact scalar meanings and source guards; changed narrative bytes rehashed, absent/contradictory source evidence incomplete. No replay semantics invalidation or historical mutation replay. Prior basis: bars_rows_written carries the value of its own bars_rows_written column in a run record where every column differs, a persisted NULL stays NULL, and a real sealed run agrees with eod_runs; reading the indicator rows turns it red (C02)',
        ],
        'MD-S075-R0043' => [
            'positive' => 'B19RunSummaryValueProvenanceTest::test_a_mirrored_field_carries_the_value_of_its_own_persisted_column',
            'negative' => 'B19RunSummaryValueProvenanceTest::test_a_persisted_null_is_exported_as_null_not_as_a_default',
            'basis' => 'E023 impact revalidation: real source/run/event/selected-and-current publication exports plus focused current run-summary/event/companion regressions retain exact scalar meanings and source guards; changed narrative bytes rehashed, absent/contradictory source evidence incomplete. No replay semantics invalidation or historical mutation replay. Prior basis: indicators_rows_written carries the value of its own indicators_rows_written column in a run record where every column differs, a persisted NULL stays NULL, and a real sealed run agrees with eod_runs; reading the bar rows turns it red (G05)',
        ],
        'MD-S075-R0044' => [
            'positive' => 'B19RunSummaryValueProvenanceTest::test_a_mirrored_field_carries_the_value_of_its_own_persisted_column',
            'negative' => 'B19RunSummaryValueProvenanceTest::test_a_persisted_null_is_exported_as_null_not_as_a_default',
            'basis' => 'E023 impact revalidation: real source/run/event/selected-and-current publication exports plus focused current run-summary/event/companion regressions retain exact scalar meanings and source guards; changed narrative bytes rehashed, absent/contradictory source evidence incomplete. No replay semantics invalidation or historical mutation replay. Prior basis: eligibility_rows_written carries the value of its own eligibility_rows_written column in a run record where every column differs, a persisted NULL stays NULL, and a real sealed run agrees with eod_runs; reading the indicator rows turns it red (G06)',
        ],
        'MD-S075-R0045' => [
            'positive' => 'B19RunSummaryValueProvenanceTest::test_a_mirrored_field_carries_the_value_of_its_own_persisted_column',
            'negative' => 'B19RunSummaryValueProvenanceTest::test_a_persisted_null_is_exported_as_null_not_as_a_default',
            'basis' => 'E023 impact revalidation: real source/run/event/selected-and-current publication exports plus focused current run-summary/event/companion regressions retain exact scalar meanings and source guards; changed narrative bytes rehashed, absent/contradictory source evidence incomplete. No replay semantics invalidation or historical mutation replay. Prior basis: invalid_bar_count carries the value of its own invalid_bar_count column in a run record where every column differs, a persisted NULL stays NULL, and a real sealed run agrees with eod_runs; reading the invalid indicator count turns it red (G07)',
        ],
        'MD-S075-R0046' => [
            'positive' => 'B19RunSummaryValueProvenanceTest::test_a_mirrored_field_carries_the_value_of_its_own_persisted_column',
            'negative' => 'B19RunSummaryValueProvenanceTest::test_a_persisted_null_is_exported_as_null_not_as_a_default',
            'basis' => 'E023 impact revalidation: real source/run/event/selected-and-current publication exports plus focused current run-summary/event/companion regressions retain exact scalar meanings and source guards; changed narrative bytes rehashed, absent/contradictory source evidence incomplete. No replay semantics invalidation or historical mutation replay. Prior basis: invalid_indicator_count carries the value of its own invalid_indicator_count column in a run record where every column differs, a persisted NULL stays NULL, and a real sealed run agrees with eod_runs; reading the invalid bar count turns it red (G08)',
        ],
        'MD-S075-R0047' => [
            'positive' => 'B19RunSummaryDerivedFieldMarkingTest::test_warning_count_is_the_persisted_value_and_a_null_is_not_turned_into_a_count',
            'negative' => 'B19RunSummaryValueProvenanceTest::test_a_persisted_null_is_exported_as_null_not_as_a_default',
            'basis' => 'E023 impact revalidation: real source/run/event/selected-and-current publication exports plus focused current run-summary/event/companion regressions retain exact scalar meanings and source guards; changed narrative bytes rehashed, absent/contradictory source evidence incomplete. No replay semantics invalidation or historical mutation replay. Prior basis: Option B (D-MD-B19-A001-003): warning_count carries the persisted eod_runs.warning_count under its persisted name, a persisted NULL stays NULL and is never turned into zero or into a count of anything else, and it is never marked as derived; on a real run the persisted value is NULL and the summary says NULL. ACCEPTED LIMITATION, not a conformance claim about warning counting: nothing in app/ writes the counter and a warning is defined nowhere (F-MD-B19-A001-005); a tripwire fails the day a new file starts to handle it, so that the population is decided first. Defaulting to zero, reading another counter, inventing a population from the invalid counts or marking the field as derived turns a guard red (W01..W04); a repository that starts writing the counter or a new file that mentions it turns the tripwire red (W05, W06)',
        ],
        'MD-S075-R0048' => [
            'positive' => 'B19RunSummaryValueProvenanceTest::test_a_mirrored_field_carries_the_value_of_its_own_persisted_column',
            'negative' => 'B19RunSummaryValueProvenanceTest::test_a_persisted_null_is_exported_as_null_not_as_a_default',
            'basis' => 'E023 impact revalidation: real source/run/event/selected-and-current publication exports plus focused current run-summary/event/companion regressions retain exact scalar meanings and source guards; changed narrative bytes rehashed, absent/contradictory source evidence incomplete. No replay semantics invalidation or historical mutation replay. Prior basis: hard_reject_count carries the value of its own hard_reject_count column in a run record where every column differs, a persisted NULL stays NULL, and a real sealed run agrees with eod_runs; reading the invalid bar count turns it red (C04)',
        ],
        'MD-S075-R0049' => [
            'positive' => 'B19RunSummaryValueProvenanceTest::test_a_mirrored_field_carries_the_value_of_its_own_persisted_column',
            'negative' => 'B19RunSummaryValueProvenanceTest::test_a_persisted_null_is_exported_as_null_not_as_a_default',
            'basis' => 'E023 impact revalidation: real source/run/event/selected-and-current publication exports plus focused current run-summary/event/companion regressions retain exact scalar meanings and source guards; changed narrative bytes rehashed, absent/contradictory source evidence incomplete. No replay semantics invalidation or historical mutation replay. Prior basis: bars_batch_hash carries the value of its own bars_batch_hash column in a run record where every column differs, a persisted NULL stays NULL, and a real sealed run agrees with eod_runs; reading the eligibility hash turns it red (D01)',
        ],
        'MD-S075-R0050' => [
            'positive' => 'B19RunSummaryValueProvenanceTest::test_a_mirrored_field_carries_the_value_of_its_own_persisted_column',
            'negative' => 'B19RunSummaryValueProvenanceTest::test_a_persisted_null_is_exported_as_null_not_as_a_default',
            'basis' => 'E023 impact revalidation: real source/run/event/selected-and-current publication exports plus focused current run-summary/event/companion regressions retain exact scalar meanings and source guards; changed narrative bytes rehashed, absent/contradictory source evidence incomplete. No replay semantics invalidation or historical mutation replay. Prior basis: indicators_batch_hash carries the value of its own indicators_batch_hash column in a run record where every column differs, a persisted NULL stays NULL, and a real sealed run agrees with eod_runs; reading the bars hash turns it red (G09)',
        ],
        'MD-S075-R0051' => [
            'positive' => 'B19RunSummaryValueProvenanceTest::test_a_mirrored_field_carries_the_value_of_its_own_persisted_column',
            'negative' => 'B19RunSummaryValueProvenanceTest::test_a_persisted_null_is_exported_as_null_not_as_a_default',
            'basis' => 'E023 impact revalidation: real source/run/event/selected-and-current publication exports plus focused current run-summary/event/companion regressions retain exact scalar meanings and source guards; changed narrative bytes rehashed, absent/contradictory source evidence incomplete. No replay semantics invalidation or historical mutation replay. Prior basis: eligibility_batch_hash carries the value of its own eligibility_batch_hash column in a run record where every column differs, a persisted NULL stays NULL, and a real sealed run agrees with eod_runs; reading the indicators hash turns it red (G10)',
        ],
        'MD-S075-R0052' => [
            'positive' => 'B19RunSummaryValueProvenanceTest::test_a_mirrored_field_carries_the_value_of_its_own_persisted_column',
            'negative' => 'B19RunSummaryRealRunProvenanceTest::test_publication_facing_fields_agree_with_the_publication_and_snapshot_rows',
            'basis' => 'E023 impact revalidation: real source/run/event/selected-and-current publication exports plus focused current run-summary/event/companion regressions retain exact scalar meanings and source guards; changed narrative bytes rehashed, absent/contradictory source evidence incomplete. No replay semantics invalidation or historical mutation replay. Prior basis: observation_manifest_hash carries the run column in a run record where every column differs, and on a real run it equals the observation manifest hash of the sealed publication read from eod_publications; reading the bars hash turns the guard red (D03)',
        ],
        'MD-S075-R0053' => [
            'positive' => 'B19RunSummaryValueProvenanceTest::test_a_publication_facing_field_comes_from_the_manifest_of_the_runs_own_publication',
            'negative' => 'B19RunSummaryValueProvenanceTest::test_a_run_without_a_resolved_publication_exports_no_publication_facing_value',
            'basis' => 'E023 impact revalidation: real source/run/event/selected-and-current publication exports plus focused current run-summary/event/companion regressions retain exact scalar meanings and source guards; changed narrative bytes rehashed, absent/contradictory source evidence incomplete. No replay semantics invalidation or historical mutation replay. Prior basis: publication_manifest_hash is read from the manifest of the run\'s own publication (decoys for every other id and on the run record), equals the eod_publications column on a real run, and is null when no publication resolved; reading it from the run, or from a neighbouring publication id, turns a guard red (D04, F05)',
        ],
        'MD-S075-R0054' => [
            'positive' => 'B19RunSummaryValueProvenanceTest::test_a_mirrored_field_carries_the_value_of_its_own_persisted_column',
            'negative' => 'B19RunSummaryValueProvenanceTest::test_missing_seal_evidence_on_a_readable_claim_is_exported_as_missing',
            'basis' => 'E023 impact revalidation: real source/run/event/selected-and-current publication exports plus focused current run-summary/event/companion regressions retain exact scalar meanings and source guards; changed narrative bytes rehashed, absent/contradictory source evidence incomplete. No replay semantics invalidation or historical mutation replay. Prior basis: sealed_at carries the sealed_at column, is null for a run that never sealed, and is never replaced by the finish time; a stand-in turns the guards red (D02)',
        ],
        'MD-S075-R0055' => [
            'positive' => 'B19RunSummaryValueProvenanceTest::test_a_mirrored_field_carries_the_value_of_its_own_persisted_column',
            'negative' => 'B19RunSummaryRealRunProvenanceTest::test_every_summary_key_named_like_an_eod_runs_column_carries_that_columns_value',
            'basis' => 'E023 impact revalidation: real source/run/event/selected-and-current publication exports plus focused current run-summary/event/companion regressions retain exact scalar meanings and source guards; changed narrative bytes rehashed, absent/contradictory source evidence incomplete. No replay semantics invalidation or historical mutation replay. Prior basis: config_version carries the value of its own config_version column in a run record where every column differs, a persisted NULL stays NULL, and a real sealed run agrees with eod_runs; reading the config hash turns it red (E01)',
        ],
        'MD-S075-R0056' => [
            'positive' => 'B19RunSummaryValueProvenanceTest::test_a_mirrored_field_carries_the_value_of_its_own_persisted_column',
            'negative' => 'B19RunSummaryValueProvenanceTest::test_a_persisted_null_is_exported_as_null_not_as_a_default',
            'basis' => 'E023 impact revalidation: real source/run/event/selected-and-current publication exports plus focused current run-summary/event/companion regressions retain exact scalar meanings and source guards; changed narrative bytes rehashed, absent/contradictory source evidence incomplete. No replay semantics invalidation or historical mutation replay. Prior basis: config_snapshot_id carries the value of its own config_snapshot_id column in a run record where every column differs, a persisted NULL stays NULL, and a real sealed run agrees with eod_runs; reading the publication id as the snapshot id turns it red (E02b)',
        ],
        'MD-S075-R0057' => [
            'positive' => 'B19RunSummaryValueProvenanceTest::test_a_publication_facing_field_comes_from_the_manifest_of_the_runs_own_publication',
            'negative' => 'B19RunSummaryRealRunProvenanceTest::test_publication_facing_fields_agree_with_the_publication_and_snapshot_rows',
            'basis' => 'E023 impact revalidation: real source/run/event/selected-and-current publication exports plus focused current run-summary/event/companion regressions retain exact scalar meanings and source guards; changed narrative bytes rehashed, absent/contradictory source evidence incomplete. No replay semantics invalidation or historical mutation replay. Prior basis: config_snapshot_hash is read from the manifest of the run\'s own publication (decoys elsewhere) and on a real run equals md_config_snapshots.config_hash of the run\'s snapshot read directly; reading the run record or a neighbouring publication turns a guard red (E03, F05)',
        ],
        'MD-S075-R0058' => [
            'positive' => 'B19RunSummaryValueProvenanceTest::test_a_publication_facing_field_comes_from_the_manifest_of_the_runs_own_publication',
            'negative' => 'B19RunSummaryRealRunProvenanceTest::test_publication_facing_fields_agree_with_the_publication_and_snapshot_rows',
            'basis' => 'E023 impact revalidation: real source/run/event/selected-and-current publication exports plus focused current run-summary/event/companion regressions retain exact scalar meanings and source guards; changed narrative bytes rehashed, absent/contradictory source evidence incomplete. No replay semantics invalidation or historical mutation replay. Prior basis: temporal_revision_set_hash is read from the manifest of the run\'s own publication, is a SHA-256 value on a real run and equals what the repository resolves for that publication; reading the run record or a neighbouring publication turns a guard red (E04, F05)',
        ],
        'MD-S075-R0059' => [
            'positive' => 'B19RunSummaryValueProvenanceTest::test_a_publication_facing_field_comes_from_the_manifest_of_the_runs_own_publication',
            'negative' => 'B19RunSummaryRealRunProvenanceTest::test_publication_facing_fields_agree_with_the_publication_and_snapshot_rows',
            'basis' => 'E023 impact revalidation: real source/run/event/selected-and-current publication exports plus focused current run-summary/event/companion regressions retain exact scalar meanings and source guards; changed narrative bytes rehashed, absent/contradictory source evidence incomplete. No replay semantics invalidation or historical mutation replay. Prior basis: factor_set_id is read from the manifest of the run\'s own publication and on a real run equals eod_publications.factor_set_id; reading the run\'s decoy value turns a guard red (E05, F05)',
        ],
        'MD-S075-R0060' => [
            'positive' => 'B19RunSummaryValueProvenanceTest::test_a_mirrored_field_carries_the_value_of_its_own_persisted_column',
            'negative' => 'B19RunSummaryValueProvenanceTest::test_a_persisted_null_is_exported_as_null_not_as_a_default',
            'basis' => 'E023 impact revalidation: real source/run/event/selected-and-current publication exports plus focused current run-summary/event/companion regressions retain exact scalar meanings and source guards; changed narrative bytes rehashed, absent/contradictory source evidence incomplete. No replay semantics invalidation or historical mutation replay. Prior basis: factor_set_hash carries the value of its own factor_set_hash column in a run record where every column differs, a persisted NULL stays NULL, and a real sealed run agrees with eod_runs; reading the observation hash turns it red (E06)',
        ],
        'MD-S075-R0061' => [
            'positive' => 'B19RunSummaryValueProvenanceTest::test_a_mirrored_field_carries_the_value_of_its_own_persisted_column',
            'negative' => 'B19RunSummaryValueProvenanceTest::test_a_persisted_null_is_exported_as_null_not_as_a_default',
            'basis' => 'E023 impact revalidation: real source/run/event/selected-and-current publication exports plus focused current run-summary/event/companion regressions retain exact scalar meanings and source guards; changed narrative bytes rehashed, absent/contradictory source evidence incomplete. No replay semantics invalidation or historical mutation replay. Prior basis: price_product_code carries the value of its own price_product_code column in a run record where every column differs, a persisted NULL stays NULL, and a real sealed run agrees with eod_runs; reading the source turns it red (E07)',
        ],
        'MD-S075-R0062' => [
            'positive' => 'B19RunSummaryValueProvenanceTest::test_a_publication_facing_field_comes_from_the_manifest_of_the_runs_own_publication',
            'negative' => 'B19RunSummaryValueProvenanceTest::test_a_run_without_a_resolved_publication_exports_no_publication_facing_value',
            'basis' => 'E023 impact revalidation: real source/run/event/selected-and-current publication exports plus focused current run-summary/event/companion regressions retain exact scalar meanings and source guards; changed narrative bytes rehashed, absent/contradictory source evidence incomplete. No replay semantics invalidation or historical mutation replay. Prior basis: canonicalization_version is read from the manifest of the run\'s own publication, is non-null on a real run and equals what the repository resolves, and is null when no publication resolved; a fixed-label stand-in turns the guard red (E08)',
        ],
        'MD-S075-R0063' => [
            'positive' => 'B19RunSummaryValueProvenanceTest::test_a_publication_facing_field_comes_from_the_manifest_of_the_runs_own_publication',
            'negative' => 'B19RunSummaryValueProvenanceTest::test_a_run_without_a_resolved_publication_exports_no_publication_facing_value',
            'basis' => 'E023 impact revalidation: real source/run/event/selected-and-current publication exports plus focused current run-summary/event/companion regressions retain exact scalar meanings and source guards; changed narrative bytes rehashed, absent/contradictory source evidence incomplete. No replay semantics invalidation or historical mutation replay. Prior basis: formula_version is read from the manifest of the run\'s own publication, is non-null on a real run and equals what the repository resolves, and is null when no publication resolved; a fixed-label stand-in turns the guard red (E09)',
        ],
        'MD-S075-R0064' => [
            'positive' => 'B19RunSummaryValueProvenanceTest::test_a_publication_facing_field_comes_from_the_manifest_of_the_runs_own_publication',
            'negative' => 'B19RunSummaryRealRunProvenanceTest::test_publication_facing_fields_agree_with_the_publication_and_snapshot_rows',
            'basis' => 'E023 impact revalidation: real source/run/event/selected-and-current publication exports plus focused current run-summary/event/companion regressions retain exact scalar meanings and source guards; changed narrative bytes rehashed, absent/contradictory source evidence incomplete. No replay semantics invalidation or historical mutation replay. Prior basis: read_model_version is read from the manifest of the run\'s own publication and on a real run equals eod_publications.read_model_version; reading the run\'s decoy value turns a guard red (E10)',
        ],
        'MD-S075-R0065' => [
            'positive' => 'B19RunSummaryValueProvenanceTest::test_a_mirrored_field_carries_the_value_of_its_own_persisted_column',
            'negative' => 'B19RunSummaryValueProvenanceTest::test_a_persisted_null_is_exported_as_null_not_as_a_default',
            'basis' => 'E023 impact revalidation: real source/run/event/selected-and-current publication exports plus focused current run-summary/event/companion regressions retain exact scalar meanings and source guards; changed narrative bytes rehashed, absent/contradictory source evidence incomplete. No replay semantics invalidation or historical mutation replay. Prior basis: freshness_state carries the value of its own freshness_state column in a run record where every column differs, a persisted NULL stays NULL, and a real sealed run agrees with eod_runs; reading the stage turns it red (F01)',
        ],
        'MD-S075-R0066' => [
            'positive' => 'B19RunSummaryValueProvenanceTest::test_a_publication_facing_field_comes_from_the_manifest_of_the_runs_own_publication',
            'negative' => 'B19RunSummaryValueProvenanceTest::test_a_run_without_a_resolved_publication_exports_no_publication_facing_value',
            'basis' => 'E023 impact revalidation: real source/run/event/selected-and-current publication exports plus focused current run-summary/event/companion regressions retain exact scalar meanings and source guards; changed narrative bytes rehashed, absent/contradictory source evidence incomplete. No replay semantics invalidation or historical mutation replay. Prior basis: publication_version is the version of the run\'s own publication (4 in the sentinel, equal to eod_publications.publication_version on a real run) and null when no publication resolved; an off-by-one turns the guard red (G11). Where the run column and the manifest could differ the manifest\'s live value is exported; the pipeline keeps both in step and the real run shows agreement, so no precedence is claimed',
        ],
        'MD-S075-R0067' => [
            'positive' => 'B19RunSummaryValueProvenanceTest::test_a_readable_success_carries_the_seal_and_publication_evidence_it_implies',
            'negative' => 'B19RunSummaryValueProvenanceTest::test_a_held_or_failed_run_is_never_summarised_as_readable',
            'basis' => 'E023 impact revalidation: real source/run/event/selected-and-current publication exports plus focused current run-summary/event/companion regressions retain exact scalar meanings and source guards; changed narrative bytes rehashed, absent/contradictory source evidence incomplete. No replay semantics invalidation or historical mutation replay. Prior basis: is_current_publication is true for a readable success on its current publication and false for every run that published nothing; marking every run current, or no run current, turns a guard red (F06, G12). Same precedence note as R0066',
        ],
        'MD-S075-R0068' => [
            'positive' => 'B19RunSummaryValueProvenanceTest::test_a_mirrored_field_carries_the_value_of_its_own_persisted_column',
            'negative' => 'B19RunSummaryValueProvenanceTest::test_a_persisted_null_is_exported_as_null_not_as_a_default',
            'basis' => 'E023 impact revalidation: real source/run/event/selected-and-current publication exports plus focused current run-summary/event/companion regressions retain exact scalar meanings and source guards; changed narrative bytes rehashed, absent/contradictory source evidence incomplete. No replay semantics invalidation or historical mutation replay. Prior basis: supersedes_run_id carries the value of its own supersedes_run_id column in a run record where every column differs, a persisted NULL stays NULL, and a real sealed run agrees with eod_runs; reading the correction id turns it red (F02)',
        ],
        'MD-S075-R0069' => [
            'positive' => 'B19RunSummaryValueProvenanceTest::test_a_mirrored_field_carries_the_value_of_its_own_persisted_column',
            'negative' => 'B19RunSummaryValueProvenanceTest::test_a_persisted_null_is_exported_as_null_not_as_a_default',
            'basis' => 'E023 impact revalidation: real source/run/event/selected-and-current publication exports plus focused current run-summary/event/companion regressions retain exact scalar meanings and source guards; changed narrative bytes rehashed, absent/contradictory source evidence incomplete. No replay semantics invalidation or historical mutation replay. Prior basis: started_at carries the value of its own started_at column in a run record where every column differs, a persisted NULL stays NULL, and a real sealed run agrees with eod_runs; reading the creation time turns it red (F03)',
        ],
        'MD-S075-R0070' => [
            'positive' => 'B19RunSummaryValueProvenanceTest::test_a_mirrored_field_carries_the_value_of_its_own_persisted_column',
            'negative' => 'B19RunSummaryValueProvenanceTest::test_a_persisted_null_is_exported_as_null_not_as_a_default',
            'basis' => 'E023 impact revalidation: real source/run/event/selected-and-current publication exports plus focused current run-summary/event/companion regressions retain exact scalar meanings and source guards; changed narrative bytes rehashed, absent/contradictory source evidence incomplete. No replay semantics invalidation or historical mutation replay. Prior basis: finished_at carries the value of its own finished_at column in a run record where every column differs, a persisted NULL stays NULL, and a real sealed run agrees with eod_runs; reading the start time turns it red (F04)',
        ],
        'MD-S075-R0071' => [
            'positive' => 'B19RunSummaryRealRunProvenanceTest::test_a_real_readable_success_carries_compatible_seal_and_publication_evidence',
            'negative' => 'B19RunSummaryValueProvenanceTest::test_a_held_or_failed_run_is_never_summarised_as_readable',
            'basis' => 'E023 impact revalidation: real source/run/event/selected-and-current publication exports plus focused current run-summary/event/companion regressions retain exact scalar meanings and source guards; changed narrative bytes rehashed, absent/contradictory source evidence incomplete. No replay semantics invalidation or historical mutation replay. Prior basis: the outcome fields (terminal_status, publishability_state, quality_gate_state, ...) equal the persisted row on a real run and the persisted values in every held, failed and not-readable variant; deriving them from a neighbouring column or marking a run current turns a guard red (A04, G02, G03, F06)',
        ],
        'MD-S075-R0072' => [
            'positive' => 'B19RunSummaryValueProvenanceTest::test_a_readable_success_carries_the_seal_and_publication_evidence_it_implies',
            'negative' => 'B19RunSummaryValueProvenanceTest::test_missing_seal_evidence_on_a_readable_claim_is_exported_as_missing',
            'basis' => 'E023 impact revalidation: real source/run/event/selected-and-current publication exports plus focused current run-summary/event/companion regressions retain exact scalar meanings and source guards; changed narrative bytes rehashed, absent/contradictory source evidence incomplete. No replay semantics invalidation or historical mutation replay. Prior basis: a readable success carries seal time, the three batch hashes, the publication manifest hash, a publication version and the current marking, all equal to the sealed publication on a real run; when a run claims readability without seal evidence the summary exports the absence rather than a stand-in; a seal-time stand-in or a false current marking turns a guard red (D02, G12)',
        ],
        'MD-S075-R0073' => [
            'positive' => 'B19RunSummaryValueProvenanceTest::test_a_held_or_failed_run_is_never_summarised_as_readable',
            'negative' => 'B19RunSummaryValueProvenanceTest::test_a_run_without_a_resolved_publication_exports_no_publication_facing_value',
            'basis' => 'E023 impact revalidation: real source/run/event/selected-and-current publication exports plus focused current run-summary/event/companion regressions retain exact scalar meanings and source guards; changed narrative bytes rehashed, absent/contradictory source evidence incomplete. No replay semantics invalidation or historical mutation replay. Prior basis: held, failed and not-readable runs are not promoted, switch no pointer, name no current publication, carry no seal and export no publication-facing value, and their outcome note says not readable; a stand-in seal, a current marking or an always-switched pointer turns a guard red (F06, F07, D02)',
        ],
        'MD-S075-R0074' => [
            'positive' => 'B19RunSummaryDerivedFieldMarkingTest::test_final_reason_code_carries_exactly_the_persisted_column',
            'negative' => 'B19RunSummaryRealRunProvenanceTest::test_every_summary_key_named_like_an_eod_runs_column_carries_that_columns_value',
            'basis' => 'E023 impact revalidation: real source/run/event/selected-and-current publication exports plus focused current run-summary/event/companion regressions retain exact scalar meanings and source guards; changed narrative bytes rehashed, absent/contradictory source evidence incomplete. No replay semantics invalidation or historical mutation replay. Prior basis: final_reason_code mirrors eod_runs.final_reason_code and nothing else (D-MD-B19-A001-003, F-MD-B19-A001-006 Option A): a persisted value is exported although the source and coverage reasons differ, a persisted NULL stays NULL (no source reason, coverage reason or stand-in), the message beside it describes only the persisted code, and on a real run every key shared with an eod_runs column, final_reason_code included, equals the persisted value; the reason resolved for operators is the separate field of R0075. Exporting the effective reason, a stand-in or the source reason under the persisted name, or the message of a fallback reason, turns a guard red (O01..O04); so does a consumer that relabels the effective reason as the persisted one (C01, C03)',
        ],
        'MD-S075-R0075' => [
            'positive' => 'B19RunSummaryDerivedFieldMarkingTest::test_every_derived_field_is_listed_in_the_derived_companion_marker',
            'negative' => 'B19RunSummaryDerivedFieldMarkingTest::test_the_marker_does_not_list_a_pure_persisted_mirror',
            'basis' => 'E023 impact revalidation: real source/run/event/selected-and-current publication exports plus focused current run-summary/event/companion regressions retain exact scalar meanings and source guards; changed narrative bytes rehashed, absent/contradictory source evidence incomplete. No replay semantics invalidation or historical mutation replay. Prior basis: derived fields appear only as marked derived companion evidence (D-MD-B19-A001-003): the effective final reason with its provenance and message, the seven manifest-derived minimum fields, the bound-input projection, the manifest-preferred publication fields, the current-marking derivations and source_context are listed in derived_companion_fields with a derivation kind and what each is derived from; the effective reason names the exact column or block it was taken from; no persisted mirror is listed and a marker that is absent cannot satisfy that; a manifest-derived field follows the manifest of the run\'s own publication and not the run record, and on a real run the listed fields exist and agree with the tables; the export result, lineage, completeness check and outcome note read the effective field. Omitting a field from the marker, an entry without an origin, a wrong origin, a marked persisted mirror, a dropped or wrong provenance or a consumer that relabels the effective reason turns a guard red (O05..O10, K01..K09, C01..C06)',
        ],
        'MD-S075-R0076' => [
            'positive' => 'B19RunSummaryValueProvenanceTest::test_source_context_recovers_thin_minimum_fields_from_persisted_notes_only',
            'negative' => 'B19RunSummaryValueProvenanceTest::test_source_context_invents_no_source_fact_the_run_never_recorded',
            'basis' => 'E023 impact revalidation: real source/run/event/selected-and-current publication exports plus focused current run-summary/event/companion regressions retain exact scalar meanings and source guards; changed narrative bytes rehashed, absent/contradictory source evidence incomplete. No replay semantics invalidation or historical mutation replay. Prior basis: source_context recovers minimum fields from persisted notes only, reads only the exported run\'s record, and invents no source fact: a run with no recorded source telemetry has null minimum fields and a null retry_attempt_count (it exported 0 retries before this unit\'s correction); a stand-in reason, a default attempt count, a dropped notes recovery or the old zero default turns a guard red (B02, B06, B07, B08)',
        ],

        // ---- MD-S075 section 2 "publication_manifest.json" -- family `artifact_publication_manifest`, 33 of 33 predicates (R0079..R0111).
        // No production defect was found: the manifest builder already maps every field to the source that owns it. What was missing is proof.
        // Three guards cooperate: `B19PublicationManifestValueProvenanceTest` drives the real builder on a history where every source differs and
        // neighbours and run mirrors are decoys; `B19PublicationManifestSupersessionTest` drives the repository's own seal and promotion twice on
        // one trade date and reads the superseded manifest before and after; `B19PublicationManifestRealRunProvenanceTest` compares the file of a
        // real sealed publication with an independent read of the tables and verifies the hash under its governed profile.
        'MD-S075-R0079' => [
            'positive' => 'B19PublicationManifestValueProvenanceTest::test_a_minimum_field_carries_the_value_of_its_own_persisted_source',
            'negative' => 'B19PublicationManifestRealRunProvenanceTest::test_every_minimum_field_of_a_real_publication_equals_its_source_row',
            'basis' => 'publication_id carries the publication\'s own id in a history where the publication, its run, its config snapshot, its lineage binding and its bars history all hold different values, the run mirrors the same names with other values and a predecessor and a publication of another date have their own rows, and a real sealed publication agrees with an independent read of the tables; reading the run id turns the guard red (P01)',
        ],
        'MD-S075-R0080' => [
            'positive' => 'B19PublicationManifestValueProvenanceTest::test_a_minimum_field_carries_the_value_of_its_own_persisted_source',
            'negative' => 'B19PublicationManifestValueProvenanceTest::test_the_publication_date_and_the_runs_requested_date_are_kept_apart',
            'basis' => 'trade_date is the publication\'s own date, kept apart from the run\'s requested date, and the real publication agrees; taking the run\'s requested date turns the guard red (P02)',
        ],
        'MD-S075-R0081' => [
            'positive' => 'B19PublicationManifestValueProvenanceTest::test_a_minimum_field_carries_the_value_of_its_own_persisted_source',
            'negative' => 'B19PublicationManifestRealRunProvenanceTest::test_every_minimum_field_of_a_real_publication_equals_its_source_row',
            'basis' => 'run_id is the run that produced the publication in a history where the publication, its run, its config snapshot, its lineage binding and its bars history all hold different values, the run mirrors the same names with other values and a predecessor and a publication of another date have their own rows, and a real sealed publication agrees with an independent read of the tables; reading the publication id turns the guard red (P03)',
        ],
        'MD-S075-R0082' => [
            'positive' => 'B19PublicationManifestValueProvenanceTest::test_a_minimum_field_carries_the_value_of_its_own_persisted_source',
            'negative' => 'B19PublicationManifestRealRunProvenanceTest::test_every_minimum_field_of_a_real_publication_equals_its_source_row',
            'basis' => 'publication_version is the publication\'s own version, not the run mirror of the same name in a history where the publication, its run, its config snapshot, its lineage binding and its bars history all hold different values, the run mirrors the same names with other values and a predecessor and a publication of another date have their own rows, and a real sealed publication agrees with an independent read of the tables; reading the publication id turns the guard red (P04)',
        ],
        'MD-S075-R0083' => [
            'positive' => 'B19PublicationManifestValueProvenanceTest::test_current_and_supersession_use_the_publication_names_and_not_the_run_mirror_names',
            'negative' => 'B19PublicationManifestSupersessionTest::test_superseding_a_publication_changes_its_current_marking_and_nothing_else',
            'basis' => 'is_current is the publication\'s stored marking: true for the current publication, false for a superseded one on a history produced by the repository\'s own seal and promotion, never the run\'s is_current_publication mirror, and the real publication agrees with the pointer table; always-current, the run mirror, a renamed key and a promotion that leaves the predecessor current turn guards red (P05, P06, P09, P42)',
        ],
        'MD-S075-R0084' => [
            'positive' => 'B19PublicationManifestValueProvenanceTest::test_current_and_supersession_use_the_publication_names_and_not_the_run_mirror_names',
            'negative' => 'B19PublicationManifestSupersessionTest::test_the_successor_names_its_predecessor_as_a_publication_and_is_current',
            'basis' => 'supersedes_publication_id is the superseded PUBLICATION id: the successor of a real correction names its predecessor, the first publication is NULL, the previous and replaced links are distinct columns, and a run id is never exported as a publication id; reading the previous link, the run mirror supersedes_run_id, a renamed key, or a promotion that forgets what it supersedes turns guards red (P07, P08, P10, P43)',
        ],
        'MD-S075-R0085' => [
            'positive' => 'B19PublicationManifestValueProvenanceTest::test_a_minimum_field_carries_the_value_of_its_own_persisted_source',
            'negative' => 'B19PublicationManifestValueProvenanceTest::test_a_value_the_publication_cannot_supply_is_null_not_borrowed',
            'basis' => 'seal_state is the publication\'s stored state: SEALED for a sealed publication, UNSEALED for one that is not, and the real publication agrees; a constant SEALED turns the guard red (P11)',
        ],
        'MD-S075-R0086' => [
            'positive' => 'B19PublicationManifestValueProvenanceTest::test_a_minimum_field_carries_the_value_of_its_own_persisted_source',
            'negative' => 'B19PublicationManifestValueProvenanceTest::test_a_value_the_publication_cannot_supply_is_null_not_borrowed',
            'basis' => 'sealed_at is the publication\'s stored seal time, NULL for an unsealed publication and never filled in from another timestamp; the update time of the row or a created_at stand-in turns guards red (P12, P13). On the real run the stored seal time and the row update time coincide under the frozen test clock, so the real-run guard alone cannot tell them apart (a recorded equivalent mutant)',
        ],
        'MD-S075-R0087' => [
            'positive' => 'B19PublicationManifestValueProvenanceTest::test_a_minimum_field_carries_the_value_of_its_own_persisted_source',
            'negative' => 'B19PublicationManifestRealRunProvenanceTest::test_the_artifact_exports_the_persisted_observation_identity_and_not_the_semantic_one_under_its_name',
            'basis' => 'observation_manifest_hash is the publication\'s persisted observation manifest hash; on a V2 publication the lineage\'s semantic observation identity, which the manifest hash binds, is a different value and is NOT exported under this name. Another hash or the semantic identity under the persisted name turns guards red (P14, Q04). The artifact does not expose the semantic identities and nothing here claims it should',
        ],
        'MD-S075-R0088' => [
            'positive' => 'B19PublicationManifestValueProvenanceTest::test_a_minimum_field_carries_the_value_of_its_own_persisted_source',
            'negative' => 'B19PublicationManifestRealRunProvenanceTest::test_every_minimum_field_of_a_real_publication_equals_its_source_row',
            'basis' => 'config_snapshot_id is the publication\'s own snapshot id in a history where the publication, its run, its config snapshot, its lineage binding and its bars history all hold different values, the run mirrors the same names with other values and a predecessor and a publication of another date have their own rows, and a real sealed publication agrees with an independent read of the tables; reading the factor set id turns the guard red (P15)',
        ],
        'MD-S075-R0089' => [
            'positive' => 'B19PublicationManifestValueProvenanceTest::test_a_minimum_field_carries_the_value_of_its_own_persisted_source',
            'negative' => 'B19PublicationManifestRealRunProvenanceTest::test_every_minimum_field_of_a_real_publication_equals_its_source_row',
            'basis' => 'config_snapshot_hash is the config_hash of the publication\'s own snapshot (the predecessor\'s snapshot is a decoy), read from md_config_snapshots on the real run; the registry revision in its place turns guards red (P16, Q02)',
        ],
        'MD-S075-R0090' => [
            'positive' => 'B19PublicationManifestValueProvenanceTest::test_the_temporal_revision_set_hash_is_composed_from_the_publications_own_lineage',
            'negative' => 'B19PublicationManifestValueProvenanceTest::test_a_value_the_publication_cannot_supply_is_null_not_borrowed',
            'basis' => 'temporal_revision_set_hash is the canonical-document hash of the publication\'s trade date and its own identity, calendar and status revision hashes (recomputed independently from the lineage row on the real run), NULL when a lineage hash is not a SHA-256 value; dropping the trade date, repeating a member or hashing a partial lineage turns guards red (P17, P18, P19, Q01)',
        ],
        'MD-S075-R0091' => [
            'positive' => 'B19PublicationManifestValueProvenanceTest::test_a_minimum_field_carries_the_value_of_its_own_persisted_source',
            'negative' => 'B19PublicationManifestRealRunProvenanceTest::test_every_minimum_field_of_a_real_publication_equals_its_source_row',
            'basis' => 'factor_set_id is the publication\'s own factor set id in a history where the publication, its run, its config snapshot, its lineage binding and its bars history all hold different values, the run mirrors the same names with other values and a predecessor and a publication of another date have their own rows, and a real sealed publication agrees with an independent read of the tables; the config snapshot id in its place turns the guard red (P20)',
        ],
        'MD-S075-R0092' => [
            'positive' => 'B19PublicationManifestValueProvenanceTest::test_a_minimum_field_carries_the_value_of_its_own_persisted_source',
            'negative' => 'B19PublicationManifestRealRunProvenanceTest::test_every_minimum_field_of_a_real_publication_equals_its_source_row',
            'basis' => 'factor_set_hash is the publication\'s own factor set hash in a history where the publication, its run, its config snapshot, its lineage binding and its bars history all hold different values, the run mirrors the same names with other values and a predecessor and a publication of another date have their own rows, and a real sealed publication agrees with an independent read of the tables; the factor decision hash in its place turns the guard red (P21)',
        ],
        'MD-S075-R0093' => [
            'positive' => 'B19PublicationManifestValueProvenanceTest::test_a_minimum_field_carries_the_value_of_its_own_persisted_source',
            'negative' => 'B19PublicationManifestRealRunProvenanceTest::test_every_minimum_field_of_a_real_publication_equals_its_source_row',
            'basis' => 'price_product_code is the publication\'s own product code, not the run mirror and not the product version in a history where the publication, its run, its config snapshot, its lineage binding and its bars history all hold different values, the run mirrors the same names with other values and a predecessor and a publication of another date have their own rows, and a real sealed publication agrees with an independent read of the tables; the version in its place turns the guard red (P22)',
        ],
        'MD-S075-R0094' => [
            'positive' => 'B19PublicationManifestValueProvenanceTest::test_a_minimum_field_carries_the_value_of_its_own_persisted_source',
            'negative' => 'B19PublicationManifestValueProvenanceTest::test_a_value_the_publication_cannot_supply_is_null_not_borrowed',
            'basis' => 'canonicalization_version is the one recorded in the bars history of THIS publication (other publications of the date carry other values), NULL when the history records none and never defaulted; reading across the date or a default label turns guards red (P23, P24)',
        ],
        'MD-S075-R0095' => [
            'positive' => 'B19PublicationManifestValueProvenanceTest::test_a_minimum_field_carries_the_value_of_its_own_persisted_source',
            'negative' => 'B19PublicationManifestRealRunProvenanceTest::test_every_minimum_field_of_a_real_publication_equals_its_source_row',
            'basis' => 'formula_version is the lineage binding\'s formula version for the publication in a history where the publication, its run, its config snapshot, its lineage binding and its bars history all hold different values, the run mirrors the same names with other values and a predecessor and a publication of another date have their own rows, and a real sealed publication agrees with an independent read of the tables; the read model version in its place turns the guard red (P25)',
        ],
        'MD-S075-R0096' => [
            'positive' => 'B19PublicationManifestValueProvenanceTest::test_a_minimum_field_carries_the_value_of_its_own_persisted_source',
            'negative' => 'B19PublicationManifestRealRunProvenanceTest::test_every_minimum_field_of_a_real_publication_equals_its_source_row',
            'basis' => 'read_model_version is the lineage binding\'s read model version (the publication column carries another value and does not win); preferring the publication column turns the guard red (P26)',
        ],
        'MD-S075-R0097' => [
            'positive' => 'B19PublicationManifestValueProvenanceTest::test_a_minimum_field_carries_the_value_of_its_own_persisted_source',
            'negative' => 'B19PublicationManifestRealRunProvenanceTest::test_every_minimum_field_of_a_real_publication_equals_its_source_row',
            'basis' => 'bars_batch_hash is the publication\'s own bars hash, not the run mirror and not a sibling artifact hash in a history where the publication, its run, its config snapshot, its lineage binding and its bars history all hold different values, the run mirrors the same names with other values and a predecessor and a publication of another date have their own rows, and a real sealed publication agrees with an independent read of the tables; the indicators hash in its place turns the guard red (P27)',
        ],
        'MD-S075-R0098' => [
            'positive' => 'B19PublicationManifestValueProvenanceTest::test_a_minimum_field_carries_the_value_of_its_own_persisted_source',
            'negative' => 'B19PublicationManifestRealRunProvenanceTest::test_every_minimum_field_of_a_real_publication_equals_its_source_row',
            'basis' => 'indicators_batch_hash is the publication\'s own indicators hash in a history where the publication, its run, its config snapshot, its lineage binding and its bars history all hold different values, the run mirrors the same names with other values and a predecessor and a publication of another date have their own rows, and a real sealed publication agrees with an independent read of the tables; the eligibility hash in its place turns the guard red (P28)',
        ],
        'MD-S075-R0099' => [
            'positive' => 'B19PublicationManifestValueProvenanceTest::test_a_minimum_field_carries_the_value_of_its_own_persisted_source',
            'negative' => 'B19PublicationManifestRealRunProvenanceTest::test_every_minimum_field_of_a_real_publication_equals_its_source_row',
            'basis' => 'eligibility_batch_hash is the publication\'s own eligibility hash in a history where the publication, its run, its config snapshot, its lineage binding and its bars history all hold different values, the run mirrors the same names with other values and a predecessor and a publication of another date have their own rows, and a real sealed publication agrees with an independent read of the tables; the bars hash in its place turns the guard red (P29)',
        ],
        'MD-S075-R0100' => [
            'positive' => 'B19PublicationManifestValueProvenanceTest::test_a_minimum_field_carries_the_value_of_its_own_persisted_source',
            'negative' => 'B19PublicationManifestSupersessionTest::test_a_damaged_stored_manifest_hash_is_refused_by_the_governed_verifier',
            'basis' => 'publication_manifest_hash is the publication\'s stored manifest hash, a SHA-256 value that VERIFIES under its governed profile (V2) on a real sealed publication, on a superseded one after supersession, and whose damage the governed verifier refuses (a damaged predecessor hash is refused for its successor too, because the successor\'s identity binds it); the seal fingerprint in its place, or a promotion that erases the predecessor\'s hash, turns guards red (P30, P44, Q03)',
        ],
        'MD-S075-R0101' => [
            'positive' => 'B19PublicationManifestValueProvenanceTest::test_a_minimum_field_carries_the_value_of_its_own_persisted_source',
            'negative' => 'B19PublicationManifestRealRunProvenanceTest::test_the_row_counts_of_a_real_publication_equal_the_rows_of_its_history',
            'basis' => 'bars_rows_written is the run\'s bars row count for the publication (three different counts in the mapping history) and equals the rows its history holds on the real run, NULL when unrecorded and never zero; the indicators count in its place, or a zero default, turns guards red (P31, P34)',
        ],
        'MD-S075-R0102' => [
            'positive' => 'B19PublicationManifestValueProvenanceTest::test_a_minimum_field_carries_the_value_of_its_own_persisted_source',
            'negative' => 'B19PublicationManifestRealRunProvenanceTest::test_the_row_counts_of_a_real_publication_equal_the_rows_of_its_history',
            'basis' => 'indicators_rows_written is the run\'s indicators row count and equals the rows its history holds on the real run; the eligibility count in its place turns the guard red (P32)',
        ],
        'MD-S075-R0103' => [
            'positive' => 'B19PublicationManifestValueProvenanceTest::test_a_minimum_field_carries_the_value_of_its_own_persisted_source',
            'negative' => 'B19PublicationManifestRealRunProvenanceTest::test_the_row_counts_of_a_real_publication_equal_the_rows_of_its_history',
            'basis' => 'eligibility_rows_written is the run\'s eligibility row count and equals the rows its history holds on the real run; the bars count in its place turns the guard red (P33)',
        ],
        'MD-S075-R0104' => [
            'positive' => 'B19PublicationManifestValueProvenanceTest::test_a_minimum_field_carries_the_value_of_its_own_persisted_source',
            'negative' => 'B19PublicationManifestValueProvenanceTest::test_the_publication_date_and_the_runs_requested_date_are_kept_apart',
            'basis' => 'trade_date_requested is the run\'s requested date, kept apart from the publication\'s trade date and from the effective date; the effective date in its place turns guards red (P35)',
        ],
        'MD-S075-R0105' => [
            'positive' => 'B19PublicationManifestValueProvenanceTest::test_a_minimum_field_carries_the_value_of_its_own_persisted_source',
            'negative' => 'B19PublicationManifestValueProvenanceTest::test_requested_and_effective_dates_are_the_runs_two_dates',
            'basis' => 'trade_date_effective is the run\'s recorded effective date, different from the requested date, and the real run agrees; the requested date in its place turns guards red (P36). When the run recorded no effective date the manifest falls back to the publication\'s own trade date, exactly as the governed V2 hash payload does; that fallback is not asserted either way (the pipeline records the effective date before a run can reach READABLE)',
        ],
        'MD-S075-R0106' => [
            'positive' => 'B19PublicationManifestValueProvenanceTest::test_readiness_is_the_publications_state_and_not_the_runs_publishability',
            'negative' => 'B19PublicationManifestRealRunProvenanceTest::test_current_readiness_and_freshness_of_a_real_publication_are_the_stored_truth',
            'basis' => 'readiness_state is the publication\'s stored readiness (HELD is reported as HELD although the run is not READABLE, and READABLE for a real sealed publication), never derived and never defaulted; a constant READABLE turns the guard red (P37)',
        ],
        'MD-S075-R0107' => [
            'positive' => 'B19PublicationManifestValueProvenanceTest::test_freshness_is_the_recorded_label_inside_the_vocabulary_and_never_an_optimistic_default',
            'negative' => 'B19PublicationManifestRealRunProvenanceTest::test_current_readiness_and_freshness_of_a_real_publication_are_the_stored_truth',
            'basis' => 'freshness_state is the run\'s recorded label inside the governed vocabulary (FRESH, STALE, DEGRADED, NOT_AVAILABLE, NOT_APPLICABLE pass through; an unknown, empty or NULL label is NOT_AVAILABLE and can never be exported as FRESH), and the predecessor keeps its own label; an unnormalized label or an unknown label defaulting to FRESH turns guards red (P38, P39)',
        ],
        'MD-S075-R0108' => [
            'positive' => 'B19PublicationManifestValueProvenanceTest::test_the_exported_file_is_the_manifest_the_repository_builds',
            'negative' => 'B19PublicationManifestValueProvenanceTest::test_current_and_supersession_use_the_publication_names_and_not_the_run_mirror_names',
            'basis' => 'the written publication_manifest.json is the repository\'s manifest field for field (nothing renamed, dropped or added by the writer) and carries the publication-contract names; a dropped field or an added run-mirror name turns guards red (P40, P41, P09, P10)',
        ],
        'MD-S075-R0109' => [
            'positive' => 'B19PublicationManifestValueProvenanceTest::test_a_minimum_field_carries_the_value_of_its_own_persisted_source',
            'negative' => 'B19PublicationManifestRealRunProvenanceTest::test_the_artifact_exports_the_persisted_observation_identity_and_not_the_semantic_one_under_its_name',
            'basis' => 'publication_manifest.json is publication-shaped evidence assembled from the publication row, the run, the config snapshot, the lineage binding and the bars history: every field is taken from the source that owns it (proven for all 29 fields against decoys in every other source) and not all of them live in eod_publications; the file is not a copy of the publication row',
        ],
        'MD-S075-R0110' => [
            'positive' => 'B19PublicationManifestValueProvenanceTest::test_current_and_supersession_use_the_publication_names_and_not_the_run_mirror_names',
            'negative' => 'B19PublicationManifestSupersessionTest::test_the_successor_names_its_predecessor_as_a_publication_and_is_current',
            'basis' => 'is_current and supersedes_publication_id are the publication-shaped names and the run-mirror names is_current_publication and supersedes_run_id never appear in a built, exported, current or superseded manifest; their values come from the publication and not from the run mirror that disagrees with it; renaming a key or reading the run mirror turns guards red (P06, P08, P09, P10)',
        ],
        'MD-S075-R0111' => [
            'positive' => 'B19PublicationManifestSupersessionTest::test_superseding_a_publication_changes_its_current_marking_and_nothing_else',
            'negative' => 'B19PublicationManifestValueProvenanceTest::test_a_superseded_publication_manifest_is_not_rewritten_to_look_current',
            'basis' => 'a superseded publication\'s manifest stays audit-valid: on a history produced by the repository\'s own seal and promotion, promoting a correction changes the predecessor\'s is_current and nothing else, its manifest hash still verifies under its governed profile, the evidence export of its own run made after the correction writes that historical manifest with is_current false, and a stale run mirror saying current does not make it look current; always-current, a predecessor left current, or a demotion that erases its hash turns guards red (P05, P42, P44)',
        ],

        // ---- MD-S075 section 3 "run_event_summary.json" -- family `artifact_run_event_summary`, 18 of 18 predicates (R0114..R0131).
        // R0121 and R0128 were HELD while F-MD-B19-A001-008 was open and are re-admitted under the Project Owner decision D-MD-B19-A001-004 (Option A): highest_severity is null for a run with no events.
        // One production DEFECT was found and corrected: an empty stage_counts / reason_code_counts was written as a JSON array ([]) instead of an object ({}).
        // Three guards cooperate (the third is `B19RunEventSummaryTieOrderAndEmptyTrailTest`: executed-SQL and index-order proof of the event_id tie break): `B19RunEventSummaryTrailDerivationTest` derives the summary from a seeded eod_run_events trail built against the usual shortcuts and
        // compares it with an independent derivation; `B19RunEventSummaryRealRunProvenanceTest` exports a real run and a clone with no or reason-free events and
        // compares the written file with the raw trail and with the run row.
        'MD-S075-R0114' => [
            'positive' => 'B19RunEventSummaryRealRunProvenanceTest::test_the_file_names_the_run_and_its_requested_date',
            'negative' => 'B19RunEventSummaryRealRunProvenanceTest::test_a_trail_without_reason_codes_writes_an_empty_json_object',
            'basis' => 'E023 impact revalidation: real source/run/event/selected-and-current publication exports plus focused current run-summary/event/companion regressions retain exact scalar meanings and source guards; changed narrative bytes rehashed, absent/contradictory source evidence incomplete. No replay semantics invalidation or historical mutation replay. Prior basis: run_id is the run the file was exported for, read from the run row (never from the publication, which has another id on the real run): the real run, and a run with its own events and no publication, both name themselves; reading the publication id turns both guards red (X01)',
        ],
        'MD-S075-R0115' => [
            'positive' => 'B19RunEventSummaryRealRunProvenanceTest::test_the_requested_date_is_not_the_effective_date',
            'negative' => 'B19RunEventSummaryRealRunProvenanceTest::test_the_file_names_the_run_and_its_requested_date',
            'basis' => 'E023 impact revalidation: real source/run/event/selected-and-current publication exports plus focused current run-summary/event/companion regressions retain exact scalar meanings and source guards; changed narrative bytes rehashed, absent/contradictory source evidence incomplete. No replay semantics invalidation or historical mutation replay. Prior basis: trade_date_requested is the run\'s REQUESTED date: on a run served on another effective date the file still carries the requested date; reading the effective date turns the guard red (X02)',
        ],
        'MD-S075-R0116' => [
            'positive' => 'B19RunEventSummaryTrailDerivationTest::test_event_count_is_the_number_of_this_runs_events_only',
            'negative' => 'B19RunEventSummaryTrailDerivationTest::test_the_whole_summary_equals_the_independent_derivation',
            'basis' => 'E023 impact revalidation: real source/run/event/selected-and-current publication exports plus focused current run-summary/event/companion regressions retain exact scalar meanings and source guards; changed narrative bytes rehashed, absent/contradictory source evidence incomplete. No replay semantics invalidation or historical mutation replay. Prior basis: event_count is the number of THIS run\'s events in eod_run_events, on a database where two other runs hold events (one on the same trade date, one on another); counting every run or counting distinct stages turns guards red (M01, M20)',
        ],
        'MD-S075-R0117' => [
            'positive' => 'B19RunEventSummaryTrailDerivationTest::test_first_event_is_the_earliest_by_time_and_not_the_first_inserted_or_lowest_id',
            'negative' => 'B19RunEventSummaryTieOrderAndEmptyTrailTest::test_the_executed_query_orders_ties_by_event_id_after_event_time',
            'basis' => 'E023 impact revalidation: real source/run/event/selected-and-current publication exports plus focused current run-summary/event/companion regressions retain exact scalar meanings and source guards; changed narrative bytes rehashed, absent/contradictory source evidence incomplete. No replay semantics invalidation or historical mutation replay. Prior basis: first_event_time is the earliest event time of the run, on a trail whose earliest event has a HIGHER event_id than the events after it and was inserted out of time order; ordering by id only, taking the last event or its time turns guards red (M04, M05, M07). Events that share a timestamp are ordered by the lower event_id: the SQL the repository really executes is captured from the connection and must order by event_time and then event_id ascending, and on a table whose only usable index would return tied rows in event_type order the first of two tied events is still the lower id. Removing the explicit event_id tie break (M02), reversing it (M03) or ordering by id only (M04) turns both red. The locked text states no tie rule; the guard protects the determinism the summary needs to be derivable from the append-only trail, event_id being its append sequence',
        ],
        'MD-S075-R0118' => [
            'positive' => 'B19RunEventSummaryTrailDerivationTest::test_last_event_is_the_latest_by_time_and_the_higher_id_wins_a_tie',
            'negative' => 'B19RunEventSummaryTieOrderAndEmptyTrailTest::test_ties_are_resolved_by_event_id_even_when_the_engine_would_return_them_in_another_order',
            'basis' => 'E023 impact revalidation: real source/run/event/selected-and-current publication exports plus focused current run-summary/event/companion regressions retain exact scalar meanings and source guards; changed narrative bytes rehashed, absent/contradictory source evidence incomplete. No replay semantics invalidation or historical mutation replay. Prior basis: last_event_time is the latest event time of the run, with two events sharing the last timestamp; the first event\'s time or a reversed tie break turns guards red (M03, M06, M08), and a real pipeline trail agrees with an independent derivation. The last of two tied events is the higher event_id even when the engine would return the tie in event_type order (index-order guard); removing the explicit event_id tie break turns it red (M02)',
        ],
        'MD-S075-R0119' => [
            'positive' => 'B19RunEventSummaryTrailDerivationTest::test_first_event_is_the_earliest_by_time_and_not_the_first_inserted_or_lowest_id',
            'negative' => 'B19RunEventSummaryTrailDerivationTest::test_a_tie_for_first_place_is_broken_by_the_lower_event_id',
            'basis' => 'E023 impact revalidation: real source/run/event/selected-and-current publication exports plus focused current run-summary/event/companion regressions retain exact scalar meanings and source guards; changed narrative bytes rehashed, absent/contradictory source evidence incomplete. No replay semantics invalidation or historical mutation replay. Prior basis: first_event_type is the type of the earliest event (tie: lower event_id), not of the last event or of the lowest id; an empty trail has none; the last event\'s type turns guards red (M05, M09)',
        ],
        'MD-S075-R0120' => [
            'positive' => 'B19RunEventSummaryTrailDerivationTest::test_last_event_is_the_latest_by_time_and_the_higher_id_wins_a_tie',
            'negative' => 'B19RunEventSummaryTrailDerivationTest::test_a_tie_for_first_place_is_broken_by_the_lower_event_id',
            'basis' => 'E023 impact revalidation: real source/run/event/selected-and-current publication exports plus focused current run-summary/event/companion regressions retain exact scalar meanings and source guards; changed narrative bytes rehashed, absent/contradictory source evidence incomplete. No replay semantics invalidation or historical mutation replay. Prior basis: last_event_type is the type of the latest event, the higher event_id winning a shared timestamp; the first event\'s type turns guards red (M06, M10)',
        ],
        'MD-S075-R0121' => [
            'positive' => 'B19RunEventSummaryTrailDerivationTest::test_highest_severity_over_mixed_trails',
            'negative' => 'B19RunEventSummaryTieOrderAndEmptyTrailTest::test_an_empty_trail_has_a_null_highest_severity',
            'basis' => 'E023 impact revalidation: real source/run/event/selected-and-current publication exports plus focused current run-summary/event/companion regressions retain exact scalar meanings and source guards; changed narrative bytes rehashed, absent/contradictory source evidence incomplete. No replay semantics invalidation or historical mutation replay. Prior basis: highest_severity is the maximum severity of the run\'s recorded events (INFO < WARN < ERROR) over eight orderings of mixed severities, a trail whose ERROR is neither first nor last, and a single-event trail of each severity (INFO is reported only because an INFO was recorded); never raising it, ranking WARN above ERROR, ignoring ERROR, taking the last event\'s severity or not reporting a recorded INFO turn guards red (M11, M12, M13, M14, S05). For a run with ZERO events it is JSON null, per the Project Owner decision D-MD-B19-A001-004 (F-MD-B19-A001-008 Option A): the repository returns null, the written run_event_summary.json carries `"highest_severity": null` on a real run, and restoring the INFO default, an empty string, a WARN, coalescing the null back to INFO, or reading another run\'s ERROR into an empty run turn guards red (S01, S02, S03, S04, S06, S07)',
        ],
        'MD-S075-R0122' => [
            'positive' => 'B19RunEventSummaryTrailDerivationTest::test_stage_counts_tally_this_runs_events_per_stage',
            'negative' => 'B19RunEventSummaryRealRunProvenanceTest::test_a_run_with_no_events_writes_no_invented_history',
            'basis' => 'E023 impact revalidation: real source/run/event/selected-and-current publication exports plus focused current run-summary/event/companion regressions retain exact scalar meanings and source guards; changed narrative bytes rehashed, absent/contradictory source evidence incomplete. No replay semantics invalidation or historical mutation replay. Prior basis: stage_counts counts this run\'s events per stage, keyed by the stage name in a deterministic order, adding up to event_count and holding no stage of another run, and is written as a JSON OBJECT even when empty; double counting, keying by event type, leaving the order to the trail or writing an empty map as [] turn guards red (M15, M16, M17, X03)',
        ],
        'MD-S075-R0123' => [
            'positive' => 'B19RunEventSummaryTrailDerivationTest::test_each_stage_of_the_example_is_counted_under_its_own_name',
            'negative' => 'B19RunEventSummaryTrailDerivationTest::test_stage_counts_tally_this_runs_events_per_stage',
            'basis' => 'E023 impact revalidation: real source/run/event/selected-and-current publication exports plus focused current run-summary/event/companion regressions retain exact scalar meanings and source guards; changed narrative bytes rehashed, absent/contradictory source evidence incomplete. No replay semantics invalidation or historical mutation replay. Prior basis: the INGEST stage of the example is counted under its own key with its own count; keying by event type or counting twice turns guards red (M15, M16)',
        ],
        'MD-S075-R0124' => [
            'positive' => 'B19RunEventSummaryTrailDerivationTest::test_each_stage_of_the_example_is_counted_under_its_own_name',
            'negative' => 'B19RunEventSummaryTrailDerivationTest::test_stage_counts_tally_this_runs_events_per_stage',
            'basis' => 'E023 impact revalidation: real source/run/event/selected-and-current publication exports plus focused current run-summary/event/companion regressions retain exact scalar meanings and source guards; changed narrative bytes rehashed, absent/contradictory source evidence incomplete. No replay semantics invalidation or historical mutation replay. Prior basis: the CANONICALIZE stage of the example is counted under its own key with its own count; keying by event type or counting twice turns guards red (M15, M16)',
        ],
        'MD-S075-R0125' => [
            'positive' => 'B19RunEventSummaryTrailDerivationTest::test_each_stage_of_the_example_is_counted_under_its_own_name',
            'negative' => 'B19RunEventSummaryTrailDerivationTest::test_stage_counts_tally_this_runs_events_per_stage',
            'basis' => 'E023 impact revalidation: real source/run/event/selected-and-current publication exports plus focused current run-summary/event/companion regressions retain exact scalar meanings and source guards; changed narrative bytes rehashed, absent/contradictory source evidence incomplete. No replay semantics invalidation or historical mutation replay. Prior basis: the INDICATORS stage of the example is counted under its own key with its own count; keying by event type or counting twice turns guards red (M15, M16)',
        ],
        'MD-S075-R0126' => [
            'positive' => 'B19RunEventSummaryTrailDerivationTest::test_each_stage_of_the_example_is_counted_under_its_own_name',
            'negative' => 'B19RunEventSummaryTrailDerivationTest::test_stage_counts_tally_this_runs_events_per_stage',
            'basis' => 'E023 impact revalidation: real source/run/event/selected-and-current publication exports plus focused current run-summary/event/companion regressions retain exact scalar meanings and source guards; changed narrative bytes rehashed, absent/contradictory source evidence incomplete. No replay semantics invalidation or historical mutation replay. Prior basis: the FINALIZE stage of the example is counted under its own key with its own count; keying by event type or counting twice turns guards red (M15, M16)',
        ],
        'MD-S075-R0127' => [
            'positive' => 'B19RunEventSummaryTrailDerivationTest::test_reason_code_counts_tally_this_runs_reason_codes_only',
            'negative' => 'B19RunEventSummaryTrailDerivationTest::test_a_trail_without_reason_codes_has_no_reason_code_counts',
            'basis' => 'E023 impact revalidation: real source/run/event/selected-and-current publication exports plus focused current run-summary/event/companion regressions retain exact scalar meanings and source guards; changed narrative bytes rehashed, absent/contradictory source evidence incomplete. No replay semantics invalidation or historical mutation replay. Prior basis: reason_code_counts counts this run\'s events per reason code, events without one are not a reason, a trail without reason codes reports none and is written as a JSON OBJECT ({}, as in the locked example) and never as []. This was a DEFECT in the exporter: an empty map was written as [], and the real-execution guard was red before the fix and green after; counting null reasons, keying by event type, dropping the counts or removing the object cast turn guards red (M18, M19, M25, X03)',
        ],
        'MD-S075-R0128' => [
            'positive' => 'B19RunEventSummaryTrailDerivationTest::test_an_empty_trail_invents_no_events_times_types_or_counts',
            'negative' => 'B19RunEventSummaryTieOrderAndEmptyTrailTest::test_an_empty_trail_has_a_null_highest_severity',
            'basis' => 'E023 impact revalidation: real source/run/event/selected-and-current publication exports plus focused current run-summary/event/companion regressions retain exact scalar meanings and source guards; changed narrative bytes rehashed, absent/contradictory source evidence incomplete. No replay semantics invalidation or historical mutation replay. Prior basis: the summary is derivable from eod_run_events and invents nothing: an empty trail yields zero events, null times and types, empty maps and, per D-MD-B19-A001-004, a null highest_severity (no severity is reported where none was observed â€” the former INFO default is gone); a new event appears in the next summary (no stored or remembered summary); the trail is not written to; another run\'s events and severities never enter; inventing a first type or a last time, reporting a default severity, caching, writing to the trail or reading all runs turn guards red (M01, M21, M22, M23, M24, S01, S03, S04, S06)',
        ],
        'MD-S075-R0129' => [
            'positive' => 'B19RunEventSummaryTrailDerivationTest::test_the_whole_summary_equals_the_independent_derivation',
            'negative' => 'B19RunEventSummaryTrailDerivationTest::test_the_summary_is_re_derived_from_the_trail_and_leaves_the_trail_untouched',
            'basis' => 'E023 impact revalidation: real source/run/event/selected-and-current publication exports plus focused current run-summary/event/companion regressions retain exact scalar meanings and source guards; changed narrative bytes rehashed, absent/contradictory source evidence incomplete. No replay semantics invalidation or historical mutation replay. Prior basis: first_event_type, last_event_type, highest_severity, stage_counts and reason_code_counts are DERIVED fields computed from the rows of the trail at the time of the call and not read from any stored or remembered value: the whole summary equals an independent derivation for three runs, follows the trail when it grows, and a remembered summary turns the guard red (M24). eod_run_events has no columns of those names',
        ],
        'MD-S075-R0130' => [
            'positive' => 'B19RunEventSummaryTrailDerivationTest::test_highest_severity_is_the_maximum_of_the_trail_and_error_is_never_lowered',
            'negative' => 'B19RunEventSummaryTrailDerivationTest::test_highest_severity_over_mixed_trails',
            'basis' => 'E023 impact revalidation: real source/run/event/selected-and-current publication exports plus focused current run-summary/event/companion regressions retain exact scalar meanings and source guards; changed narrative bytes rehashed, absent/contradictory source evidence incomplete. No replay semantics invalidation or historical mutation replay. Prior basis: when the run\'s trail holds an ERROR, highest_severity is ERROR wherever the ERROR sits (first, between warnings, last, alone), on a seeded trail and on a real pipeline trail (which holds no ERROR, so it must not claim one); ignoring ERROR, ranking it below WARN, never raising the severity or taking the last event\'s severity turns guards red (M11, M12, M13, M14)',
        ],
        'MD-S075-R0131' => [
            'positive' => 'B19RunEventSummaryRealRunProvenanceTest::test_the_export_leaves_the_event_trail_row_for_row_intact',
            'negative' => 'B19RunEventSummaryTrailDerivationTest::test_the_summary_is_re_derived_from_the_trail_and_leaves_the_trail_untouched',
            'basis' => 'E023 impact revalidation: real source/run/event/selected-and-current publication exports plus focused current run-summary/event/companion regressions retain exact scalar meanings and source guards; changed narrative bytes rehashed, absent/contradictory source evidence incomplete. No replay semantics invalidation or historical mutation replay. Prior basis: the file summarizes the trail and does not replace it: exporting leaves the run\'s events row for row intact (a real run, before and after), the file holds no event rows or messages, and summarizing never writes to the trail (M23); the row-level events remain the source for diagnosis',
        ],

        // ---- MD-S075 section 4 "eligibility_export.csv" -- family `artifact_eligibility_export`, 9 of 9 predicates (R0133..R0138, R0143..R0145).
        // Production DEFECT corrected (F-MD-B01-A014-001, owned by MD-B19): the file shipped only the optional legacy half (trade_date, ticker_id, eligible, reason_code).
        // It now ships listing_id, publication_id, data_usable and the complete persisted reason set, then the legacy projection last.
        // Three guards: `B19EligibilityExportRowProvenanceTest` (a seeded history of superseded, current, unsealed and neighbouring publications, literal expectations),
        // `B19EligibilityExportRealRunProvenanceTest` (the file of a real run against an independent read of the tables, with a superseded neighbour added) and
        // `B19EligibilityReasonRegistrationTest` (blocking reasons against the reason-code registry seed).
        'MD-S075-R0133' => [
            'positive' => 'B19EligibilityExportRowProvenanceTest::test_the_trade_date_is_the_publication_trade_date',
            'negative' => 'B19EligibilityExportRealRunProvenanceTest::test_the_file_of_a_real_run_equals_an_independent_read_of_the_tables',
            'basis' => 'trade_date is the publication\'s trade date: every row of a seeded superseded/current/neighbouring history carries the date of its publication (a publication is exported only for its own date), and on a real run every row carries the publication\'s trade_date, which is the run\'s resolved date; dropping the date filter turns the guard red (E18)',
        ],
        'MD-S075-R0134' => [
            'positive' => 'B19EligibilityExportRowProvenanceTest::test_listing_identity_is_the_persisted_listing_and_ticker_is_only_a_compatibility_column',
            'negative' => 'B19EligibilityExportRealRunProvenanceTest::test_the_file_of_a_real_run_equals_an_independent_read_of_the_tables',
            'basis' => 'listing_id is the persisted stable listing identity of the row, distinct from the ticker id and the publication id, NULL stays NULL (never 0, never the ticker), and on a real run it equals eod_eligibility.listing_id for every row; not exporting it, substituting the ticker id or turning NULL into 0 turn guards red (E01, E02, E03, X02). Boundary case: a row without a listing identity cannot belong to a V2 semantic artifact - the identity key the semantic hash navigates by refuses a NULL, zero or absent listing id (ARTIFACT_LOCAL_NAVIGATION_KEY_MISSING; W07) - so a NULL listing_id in the export is a legacy row reported as stored, not a V2 sealed publication. This is the part of F-MD-B01-A014-001 the pre-fix export dropped',
        ],
        'MD-S075-R0135' => [
            'positive' => 'B19EligibilityExportRowProvenanceTest::test_listing_identity_is_the_persisted_listing_and_ticker_is_only_a_compatibility_column',
            'negative' => 'B19EligibilityExportRowProvenanceTest::test_a_superseded_publication_exports_exactly_its_own_rows',
            'basis' => 'ticker_id is kept as the optional compatibility/display column and equals the persisted ticker id of the row, always next to (never instead of) the listing identity; the carried-over value from another column turns guards red (E20). The optional display column ticker_code is not exported and the contract does not require it',
        ],
        'MD-S075-R0136' => [
            'positive' => 'B19EligibilityExportRowProvenanceTest::test_a_superseded_publication_exports_exactly_its_own_rows',
            'negative' => 'B19EligibilityExportRealRunProvenanceTest::test_the_file_of_a_real_run_equals_an_independent_read_of_the_tables',
            'basis' => 'publication_id is the persisted publication of the row (10 for the superseded export, 11 for the current one, the real publication on a real run), not the ticker, the run or a constant; not exporting it, reading another column or a constant turn guards red (E04, E05, X01, X02). This is the part of F-MD-B01-A014-001 the pre-fix export dropped',
        ],
        'MD-S075-R0137' => [
            'positive' => 'B19EligibilityExportRowProvenanceTest::test_data_usable_is_the_persisted_usability_independent_of_the_reasons',
            'negative' => 'B19EligibilityExportRealRunProvenanceTest::test_the_file_of_a_real_run_equals_an_independent_read_of_the_tables',
            'basis' => 'data_usable is the persisted usability (eligible = 1) and nothing else: blocked although the legacy reason is NULL, usable although the set is non-empty, blocked although the set is empty, usable although no set was recorded; deriving it from the legacy reason or from the set, or a constant, turns guards red (E06, E07, E08, X05), and on a real run the blocked row is written 0',
        ],
        'MD-S075-R0138' => [
            'positive' => 'B19EligibilityExportRowProvenanceTest::test_the_complete_reason_set_is_exported_and_the_legacy_reason_is_only_a_projection',
            'negative' => 'B19EligibilityExportRowProvenanceTest::test_an_unrecorded_reason_set_is_unknown_and_is_never_rebuilt',
            'basis' => 'reason_codes is the complete persisted reason set in its persisted order (a two-member set whose first member is not the legacy reason), a recorded empty set is [], an unrecorded set is an empty cell and is never rebuilt from the legacy reason_code; rebuilding from the legacy reason, re-sorting, truncating, turning unrecorded into [], or not reading the set turn guards red (E09, E10, E11, E12, E13, X02, X03). Boundary cases: a NULL set cannot be written (the existing write completeness guard refuses it, B19EligibilityBlockedRowAdmissionTest and StageThreeWriteCompletenessGuardTest), so an unrecorded set is legacy-only; a blocked row whose set is not a non-empty JSON list of non-blank strings - including an object with numeric keys - is refused at the write (N01-N07, W01-W07). Scope note: the contract prescribes no cell encoding, so the persisted set is rendered verbatim as a JSON array (no delimiter and no re-sorting are introduced); a different encoding would need an owner decision',
        ],
        'MD-S075-R0143' => [
            'positive' => 'B19EligibilityExportRowProvenanceTest::test_no_row_of_another_publication_or_date_appears',
            'negative' => 'B19EligibilityExportRealRunProvenanceTest::test_the_file_of_a_real_run_equals_an_independent_read_of_the_tables',
            'basis' => 'one coherent publication context: every row of an export names the one requested publication and its date, and the file of a real run carries exactly one publication id equal to the run\'s publication and to the manifest written next to it; the neighbours (a second superseded sealed publication, an unsealed candidate with a sealed_at but UNSEALED state, another date) never appear and a superseded neighbour added to a real history leaves the file byte-identical; removing the publication filter, the seal-state filter or exporting the wrong id turn guards red (E04, E05, E14, E17, X01, X04)',
        ],
        'MD-S075-R0144' => [
            'positive' => 'B19EligibilityReasonRegistrationTest::test_every_blocking_reason_the_decision_returns_is_registered_and_active',
            'negative' => 'B19EligibilityBlockedRowAdmissionTest::test_a_blocked_row_without_a_usable_reason_set_is_refused_before_anything_is_replaced',
            'basis' => 'blocked rows carry registered reason codes. WHERE THE BOUNDARY IS: nothing downstream of the producer consults the reason-code registry - an unregistered member written through the public replaceEligibility() is accepted and is then exported unchanged for a SEALED publication (executed in B19EligibilityReasonReachabilityTest; the semantic hash covers eligibility_reasons_json as an opaque value) - so the boundary is the PRODUCER and it is closed: (1) producer content: every blocking reason the eligibility decision returns on every branch, and every ELIG_ code literal in the two producers (including the suspension reason that leads a set), is a registered active code of the reason-code registry seed; an unregistered code, an unregistered suspension reason or a blocked decision without a reason turn guards red (G01, G02, G03). (2) producer reachability, by census of app/: replaceEligibility() has exactly one production caller (EodEligibilityBuildService), only that service builds a reason set, its members come from exactly two expressions (the suspension literal and the reason EligibilityDecisionService::decide() returns), the files that write the eligibility tables are a closed set, and the copy paths (snapshot, promote) and the market-structure bind neither build nor change a reason set; a second caller, a second builder, a third member source, a new table writer or a market-structure payload that carries a reason set turn guards red (C01-C05), so a change that could introduce an unregistered code must be reviewed against this rule before it is accepted. (3) persistence shape: a blocked row whose reason set is not a non-empty JSON list of non-blank strings is refused before any stored row is replaced; the JSON root type is read from a decode without the associative flag (numeric-key objects refused); empty lists, objects, lists of lists, blank and non-string members refused, mixed batches atomic (N01-N07, W01-W07, F-MD-B19-A001-009). (4) the real run: every member of every blocked row\'s set and its legacy reason is in eod_reason_codes and the set is non-empty. Stated limits: the export reports persisted rows as stored and neither invents nor repairs a reason (a defective legacy or bypassed row is a faithful report of non-conformant data); the write does not consult the registry (B19EligibilityWriteRegistrationBoundaryTest; C06 turns the demonstration red the day persistence starts to refuse, and the basis must then be updated). Whether persistence or sealing should additionally consult the registry, and which registry (the live eod_reason_codes table or the snapshot bound to the publication\'s config), is an authority choice recorded as an optional hardening question; it is not needed for the current system to satisfy R0144 because no production path can pass an unregistered code',
        ],
        'MD-S075-R0145' => [
            'positive' => 'B19EligibilityExportRowProvenanceTest::test_no_row_of_another_publication_or_date_appears',
            'negative' => 'B19EligibilityExportRowProvenanceTest::test_a_current_publication_with_history_rows_is_not_doubled',
            'basis' => 'the export never mixes current and superseded publication states: a superseded publication is read from the history table and exports only its own rows although the current publication has rows for the same tickers with the opposite usability, the current publication is read from one table and never unioned with its history, and the pointer-resolved readable export of a superseded publication is empty; reading the superseded publication from the current table, the current one from history only, or removing the publication filter turn guards red (E14, E15, E16, X04)',
        ],

        // ---- MD-S075 section 5 "invalid_bars_export.csv" -- partial: eight reviewed bases.
        // R0152 is deliberately absent: the real conflicting-source producer emits an unregistered
        // BAR_CONFLICTING_SOURCE_ROWS code (F-MD-B19-A001-010); its owner must resolve the vocabulary.
        'MD-S075-R0147' => [
            'positive' => 'B19InvalidBarsExportRowProvenanceTest::test_the_export_rows_equal_their_own_persisted_rejections_with_null_identity_preserved',
            'negative' => 'B19InvalidBarsProducerProvenanceTest::test_each_rejected_row_carries_the_provenance_of_the_input_row_it_came_from',
            'basis' => 'the export reads only persisted rejections of the requested run/date and carries each row\'s trade_date; independently hand-written ingest rows bind their own requested trade date. Removing the selected date turns the row guard red (M01)',
        ],
        'MD-S075-R0148' => [
            'positive' => 'B19InvalidBarsExportRowProvenanceTest::test_the_export_rows_equal_their_own_persisted_rejections_with_null_identity_preserved',
            'negative' => 'B19InvalidBarsProducerProvenanceTest::test_each_rejected_row_carries_the_provenance_of_the_input_row_it_came_from',
            'basis' => 'a resolved listing exports its persisted listing_id; an unresolved listing exports NULL, never the ticker or a stand-in. The producer derives each from the temporal mapping of hand-written inputs. Omitting listing_id turns the export guard red (M02)',
        ],
        'MD-S075-R0149' => [
            'positive' => 'B19InvalidBarsExportRowProvenanceTest::test_the_export_rows_equal_their_own_persisted_rejections_with_null_identity_preserved',
            'negative' => 'B19InvalidBarsProducerProvenanceTest::test_each_rejected_row_carries_the_provenance_of_the_input_row_it_came_from',
            'basis' => 'the optional ticker_id compatibility field is the rejected row\'s persisted ticker, including NULL for an unknown ticker, distinct from listing identity. Omitting it turns the export guard red (M03)',
        ],
        'MD-S075-R0150' => [
            'positive' => 'B19InvalidBarsExportRowProvenanceTest::test_the_export_rows_equal_their_own_persisted_rejections_with_null_identity_preserved',
            'negative' => 'B19InvalidBarsProducerProvenanceTest::test_each_rejected_row_carries_the_provenance_of_the_input_row_it_came_from',
            'basis' => 'the exported immutable source_observation_id is read from each persisted rejected row and traced to its hand-written source input; an input with no observation stays NULL rather than gaining one. Omitting the column turns the export guard red (M04)',
        ],
        'MD-S075-R0151' => [
            'positive' => 'B19InvalidBarsExportRowProvenanceTest::test_the_export_rows_equal_their_own_persisted_rejections_with_null_identity_preserved',
            'negative' => 'B19InvalidBarsProducerProvenanceTest::test_each_rejected_row_carries_the_provenance_of_the_input_row_it_came_from',
            'basis' => 'the source and source_row_ref of each rejected row equal the literal source and distinct row reference fed to ingest, then persisted for that run/date. Dropping source or source_row_ref turns the export guard red (M05, M06)',
        ],
        'MD-S075-R0156' => [
            'positive' => 'B19InvalidBarsExportRowProvenanceTest::test_the_export_rows_equal_their_own_persisted_rejections_with_null_identity_preserved',
            'negative' => 'B19InvalidBarsProducerProvenanceTest::test_rejected_rows_are_never_canonical_and_the_clean_row_is_not_rejected',
            'basis' => 'the audit export reads rejected rows without changing either the rejection or canonical tables; the producer supplies invalid rows to the invalid store and only the clean row to canonical. Querying canonical bars instead of rejected rows turns the export guard red (M08)',
        ],
        'MD-S075-R0157' => [
            'positive' => 'B19InvalidBarsProducerProvenanceTest::test_rejected_rows_are_never_canonical_and_the_clean_row_is_not_rejected',
            'negative' => 'B19InvalidBarsExportRowProvenanceTest::test_the_export_rows_equal_their_own_persisted_rejections_with_null_identity_preserved',
            'basis' => 'seven defective input rows are rejected and none appears in the canonical output; the clean ticker is canonical and absent from the rejected set and audit export. A validation inversion turns the producer guard red and reading canonical bars turns the export guard red (M08, M13)',
        ],
        'MD-S075-R0158' => [
            'positive' => 'B19InvalidBarsRealRunExportTest::test_the_real_file_and_summary_match_rejected_rows_and_disclose_sampling',
            'negative' => 'B19InvalidBarsExportRowProvenanceTest::test_a_bounded_export_reports_the_full_count_of_its_run_and_date',
            'basis' => 'for a real sealed run with two independently seeded rejected rows, the real repository and exporter write one CSV row under a limit of one and run_summary.json explicitly reports sampled=true, exported 1, total 2 and limit 1; a second export with limit three writes both and reports sampled=false. The count uses the same run/date filters independently of the limited row read. Widening the limit, suppressing disclosure or dropping either count filter turns guards red (M09, M10, M14, M15); the writer guard independently checks the one-of-two disclosure',
        ],

        // ---- MD-S075 section 8 replay_result.json pilot: three identity fields only.
        // The other 29 replay-result predicates remain without a reviewed basis.
        'MD-S075-R0231' => [
            'positive' => 'B19ReplayResultIdentityProvenanceTest::test_replay_result_identity_fields_follow_the_exact_persisted_pair',
            'negative' => 'B19ReplayResultIdentityProvenanceTest::test_replay_result_identity_never_falls_back_to_another_pair',
            'basis' => 'E022 impact revalidation: identity verification and evidence admission now explicitly independent from historical comparison PASS; current real MariaDB and targeted producer/persistence/export guards revalidate own source values and both state namespaces. Supplemental binding absence leaves legacy evidence incomplete, never repairs or changes historical comparison. Original scalar/semantic meaning and retained mutations unchanged. Prior basis: three separately seeded replay metric rows distinguish replay id and date; the real repository lookup and JSON writer export the exact persisted replay_id for each selected pair, with no lookup of an absent id. Replacing the exported id with zero or selecting a different date makes the guard red (pilot M01, M04)',
        ],
        'MD-S075-R0232' => [
            'positive' => 'B19ReplayResultIdentityProvenanceTest::test_replay_result_identity_fields_follow_the_exact_persisted_pair',
            'negative' => 'B19ReplayResultIdentityProvenanceTest::test_replay_result_identity_never_falls_back_to_another_pair',
            'basis' => 'E022 impact revalidation: identity verification and evidence admission now explicitly independent from historical comparison PASS; current real MariaDB and targeted producer/persistence/export guards revalidate own source values and both state namespaces. Supplemental binding absence leaves legacy evidence incomplete, never repairs or changes historical comparison. Original scalar/semantic meaning and retained mutations unchanged. Prior basis: two dates for one replay id plus a same-date different-id decoy make the requested persisted trade_date independently distinguishable; an absent date and an omitted selector are refused. Substituting a fixed export date or selecting a different persisted date makes the guard red (pilot M02, M04)',
        ],
        'MD-S075-R0233' => [
            'positive' => 'B19ReplayResultIdentityProvenanceTest::test_replay_result_identity_fields_follow_the_exact_persisted_pair',
            'negative' => 'B19ReplayResultIdentityProvenanceTest::test_replay_result_identity_never_falls_back_to_another_pair',
            'basis' => 'E022 impact revalidation: identity verification and evidence admission now explicitly independent from historical comparison PASS; current real MariaDB and targeted producer/persistence/export guards revalidate own source values and both state namespaces. Supplemental binding absence leaves legacy evidence incomplete, never repairs or changes historical comparison. Original scalar/semantic meaning and retained mutations unchanged. Prior basis: each of three persisted rows has a different effective date, distinct from its trade date; the actual replay_result.json value equals its independently seeded metric field, and a fixed substitute turns the guard red (pilot M03). Missing replay/date pairs cannot borrow a neighbouring effective date',
        ],

        // ---- MD-S075 section 8: persisted replay-result field projections, each with a distinct red mutation.
        'MD-S075-R0234' => [
            'positive' => 'B19ReplayResultPersistedFieldProvenanceTest::test_replay_result_fields_equal_their_own_independently_seeded_metric_values',
            'negative' => 'B19ReplayResultPersistedFieldProvenanceTest::test_expected_fields_do_not_inherit_actual_values_or_a_neighbour',
            'basis' => 'E022 impact revalidation: identity verification and evidence admission now explicitly independent from historical comparison PASS; current real MariaDB and targeted producer/persistence/export guards revalidate own source values and both state namespaces. Supplemental binding absence leaves legacy evidence incomplete, never repairs or changes historical comparison. Original scalar/semantic meaning and retained mutations unchanged. Prior basis: The selected persisted replay metric has distinct actual status values COMPLETED, HELD and BLOCKED; the real exporter writes each unchanged to replay_result.json, independently of expected_status and neighbouring rows. Replacing actual status with expected_status turns the guard red (E-MD-B19-A001-017 R0234). This proves the stored terminal status projection, not the separate PASS/FAIL/BLOCKED replay_status classification.',
        ],
        'MD-S075-R0236' => [
            'positive' => 'B19ReplayResultPersistedFieldProvenanceTest::test_replay_result_fields_equal_their_own_independently_seeded_metric_values',
            'negative' => 'B19ReplayResultPersistedFieldProvenanceTest::test_expected_fields_do_not_inherit_actual_values_or_a_neighbour',
            'basis' => 'E022 impact revalidation: identity verification and evidence admission now explicitly independent from historical comparison PASS; current real MariaDB and targeted producer/persistence/export guards revalidate own source values and both state namespaces. Supplemental binding absence leaves legacy evidence incomplete, never repairs or changes historical comparison. Original scalar/semantic meaning and retained mutations unchanged. Prior basis: The selected persisted replay metrics carry FAIL, PASS and BLOCKED as replay_status, distinct from terminal status; the real exporter writes each unchanged, and a neighbour date differs. Substituting terminal status turns the guard red (E-MD-B19-A001-017 R0236). The separate rule for semantic classification of these values, R0260, remains unreviewed.',
        ],
        'MD-S075-R0235' => [
            'positive' => 'B19ReplayResultPersistedFieldProvenanceTest::test_replay_result_fields_equal_their_own_independently_seeded_metric_values',
            'negative' => 'B19ReplayResultPersistedFieldProvenanceTest::test_expected_fields_do_not_inherit_actual_values_or_a_neighbour',
            'basis' => 'E022 impact revalidation: identity verification and evidence admission now explicitly independent from historical comparison PASS; current real MariaDB and targeted producer/persistence/export guards revalidate own source values and both state namespaces. Supplemental binding absence leaves legacy evidence incomplete, never repairs or changes historical comparison. Original scalar/semantic meaning and retained mutations unchanged. Prior basis: The real replay evidence exporter reads the exact persisted replay metric row for the requested id/date and writes its comparison_result to replay_result.json. Three independently seeded rows distinguish MISMATCH versus EXPECTED_DEGRADE, with a BLOCKED decoy; no value is reconstructed from the exported file, an actual-state counterpart or a neighbouring row. Replacing this projection with a plausible wrong source turns the guard red (E-MD-B19-A001-017 R0235). This basis proves this stored-field export, not the semantic production of the metric.',
        ],
        'MD-S075-R0237' => [
            'positive' => 'B19ReplayResultPersistedFieldProvenanceTest::test_replay_result_fields_equal_their_own_independently_seeded_metric_values',
            'negative' => 'B19ReplayResultPersistedFieldProvenanceTest::test_expected_fields_do_not_inherit_actual_values_or_a_neighbour',
            'basis' => 'E022 impact revalidation: identity verification and evidence admission now explicitly independent from historical comparison PASS; current real MariaDB and targeted producer/persistence/export guards revalidate own source values and both state namespaces. Supplemental binding absence leaves legacy evidence incomplete, never repairs or changes historical comparison. Original scalar/semantic meaning and retained mutations unchanged. Prior basis: The real replay evidence exporter reads the exact persisted replay metric row for the requested id/date and writes its comparison_note to replay_result.json. Three independently seeded rows distinguish two distinct notes and a NULL decoy; no value is reconstructed from the exported file, an actual-state counterpart or a neighbouring row. Replacing this projection with a plausible wrong source turns the guard red (E-MD-B19-A001-017 R0237). This basis proves this stored-field export, not the semantic production of the metric.',
        ],
        'MD-S075-R0238' => [
            'positive' => 'B19ReplayResultPersistedFieldProvenanceTest::test_replay_result_fields_equal_their_own_independently_seeded_metric_values',
            'negative' => 'B19ReplayResultPersistedFieldProvenanceTest::test_expected_fields_do_not_inherit_actual_values_or_a_neighbour',
            'basis' => 'E022 impact revalidation: identity verification and evidence admission now explicitly independent from historical comparison PASS; current real MariaDB and targeted producer/persistence/export guards revalidate own source values and both state namespaces. Supplemental binding absence leaves legacy evidence incomplete, never repairs or changes historical comparison. Original scalar/semantic meaning and retained mutations unchanged. Prior basis: The real replay evidence exporter reads the exact persisted replay metric row for the requested id/date and writes its artifact_changed_scope to replay_result.json. Three independently seeded rows distinguish eligibility_only versus bars_only and a NULL decoy; no value is reconstructed from the exported file, an actual-state counterpart or a neighbouring row. Replacing this projection with a plausible wrong source turns the guard red (E-MD-B19-A001-017 R0238). This basis proves this stored-field export, not the semantic production of the metric.',
        ],
        'MD-S075-R0239' => [
            'positive' => 'B19ReplayResultPersistedFieldProvenanceTest::test_replay_result_fields_equal_their_own_independently_seeded_metric_values',
            'negative' => 'B19ReplayResultPersistedFieldProvenanceTest::test_expected_fields_do_not_inherit_actual_values_or_a_neighbour',
            'basis' => 'E022 impact revalidation: identity verification and evidence admission now explicitly independent from historical comparison PASS; current real MariaDB and targeted producer/persistence/export guards revalidate own source values and both state namespaces. Supplemental binding absence leaves legacy evidence incomplete, never repairs or changes historical comparison. Original scalar/semantic meaning and retained mutations unchanged. Prior basis: The real replay evidence exporter reads the exact persisted replay metric row for the requested id/date and writes its replay_mode to replay_result.json. Three independently seeded rows distinguish PUBLICATION_EXACT versus AS_KNOWN; no value is reconstructed from the exported file, an actual-state counterpart or a neighbouring row. Replacing this projection with a plausible wrong source turns the guard red (E-MD-B19-A001-017 R0239). This basis proves this stored-field export, not the semantic production of the metric.',
        ],
        'MD-S075-R0250' => [
            'positive' => 'B19ReplayResultPersistedFieldProvenanceTest::test_replay_result_fields_equal_their_own_independently_seeded_metric_values',
            'negative' => 'B19ReplayResultPersistedFieldProvenanceTest::test_expected_fields_do_not_inherit_actual_values_or_a_neighbour',
            'basis' => 'E022 impact revalidation: identity verification and evidence admission now explicitly independent from historical comparison PASS; current real MariaDB and targeted producer/persistence/export guards revalidate own source values and both state namespaces. Supplemental binding absence leaves legacy evidence incomplete, never repairs or changes historical comparison. Original scalar/semantic meaning and retained mutations unchanged. Prior basis: The real replay evidence exporter reads the exact persisted replay metric row for the requested id/date and writes its publication_version to replay_result.json. Three independently seeded rows distinguish 7 versus 8 and a NULL decoy; no value is reconstructed from the exported file, an actual-state counterpart or a neighbouring row. Replacing this projection with a plausible wrong source turns the guard red (E-MD-B19-A001-017 R0250). This basis proves this stored-field export, not the semantic production of the metric.',
        ],
        'MD-S075-R0251' => [
            'positive' => 'B19ReplayResultPersistedFieldProvenanceTest::test_replay_result_fields_equal_their_own_independently_seeded_metric_values',
            'negative' => 'B19ReplayResultPersistedFieldProvenanceTest::test_expected_fields_do_not_inherit_actual_values_or_a_neighbour',
            'basis' => 'E022 impact revalidation: identity verification and evidence admission now explicitly independent from historical comparison PASS; current real MariaDB and targeted producer/persistence/export guards revalidate own source values and both state namespaces. Supplemental binding absence leaves legacy evidence incomplete, never repairs or changes historical comparison. Original scalar/semantic meaning and retained mutations unchanged. Prior basis: The real replay evidence exporter reads the exact persisted replay metric row for the requested id/date and writes its bars_batch_hash to replay_result.json. Three independently seeded rows distinguish bars-A versus bars-B and a NULL decoy, distinct from indicator and eligibility hashes; no value is reconstructed from the exported file, an actual-state counterpart or a neighbouring row. Replacing this projection with a plausible wrong source turns the guard red (E-MD-B19-A001-017 R0251). This basis proves this stored-field export, not the semantic production of the metric.',
        ],
        'MD-S075-R0252' => [
            'positive' => 'B19ReplayResultPersistedFieldProvenanceTest::test_replay_result_fields_equal_their_own_independently_seeded_metric_values',
            'negative' => 'B19ReplayResultPersistedFieldProvenanceTest::test_expected_fields_do_not_inherit_actual_values_or_a_neighbour',
            'basis' => 'E022 impact revalidation: identity verification and evidence admission now explicitly independent from historical comparison PASS; current real MariaDB and targeted producer/persistence/export guards revalidate own source values and both state namespaces. Supplemental binding absence leaves legacy evidence incomplete, never repairs or changes historical comparison. Original scalar/semantic meaning and retained mutations unchanged. Prior basis: The real replay evidence exporter reads the exact persisted replay metric row for the requested id/date and writes its indicators_batch_hash to replay_result.json. Three independently seeded rows distinguish indicators-A versus indicators-B and a NULL decoy, distinct from bars and eligibility hashes; no value is reconstructed from the exported file, an actual-state counterpart or a neighbouring row. Replacing this projection with a plausible wrong source turns the guard red (E-MD-B19-A001-017 R0252). This basis proves this stored-field export, not the semantic production of the metric.',
        ],
        'MD-S075-R0253' => [
            'positive' => 'B19ReplayResultPersistedFieldProvenanceTest::test_replay_result_fields_equal_their_own_independently_seeded_metric_values',
            'negative' => 'B19ReplayResultPersistedFieldProvenanceTest::test_expected_fields_do_not_inherit_actual_values_or_a_neighbour',
            'basis' => 'E022 impact revalidation: identity verification and evidence admission now explicitly independent from historical comparison PASS; current real MariaDB and targeted producer/persistence/export guards revalidate own source values and both state namespaces. Supplemental binding absence leaves legacy evidence incomplete, never repairs or changes historical comparison. Original scalar/semantic meaning and retained mutations unchanged. Prior basis: The real replay evidence exporter reads the exact persisted replay metric row for the requested id/date and writes its eligibility_batch_hash to replay_result.json. Three independently seeded rows distinguish eligibility-A versus eligibility-B and a NULL decoy, distinct from bars and indicator hashes; no value is reconstructed from the exported file, an actual-state counterpart or a neighbouring row. Replacing this projection with a plausible wrong source turns the guard red (E-MD-B19-A001-017 R0253). This basis proves this stored-field export, not the semantic production of the metric.',
        ],
        'MD-S075-R0254' => [
            'positive' => 'B19ReplayResultPersistedFieldProvenanceTest::test_replay_result_fields_equal_their_own_independently_seeded_metric_values',
            'negative' => 'B19ReplayResultPersistedFieldProvenanceTest::test_expected_fields_do_not_inherit_actual_values_or_a_neighbour',
            'basis' => 'E022 impact revalidation: identity verification and evidence admission now explicitly independent from historical comparison PASS; current real MariaDB and targeted producer/persistence/export guards revalidate own source values and both state namespaces. Supplemental binding absence leaves legacy evidence incomplete, never repairs or changes historical comparison. Original scalar/semantic meaning and retained mutations unchanged. Prior basis: The real replay evidence exporter reads the exact persisted replay metric row for the requested id/date and writes its seal_state to replay_result.json. Three independently seeded rows distinguish SEALED versus UNSEALED, with expected seal state deliberately opposite; no value is reconstructed from the exported file, an actual-state counterpart or a neighbouring row. Replacing this projection with a plausible wrong source turns the guard red (E-MD-B19-A001-017 R0254). This basis proves this stored-field export, not the semantic production of the metric.',
        ],
        'MD-S075-R0255' => [
            'positive' => 'B19ReplayResultPersistedFieldProvenanceTest::test_replay_result_fields_equal_their_own_independently_seeded_metric_values',
            'negative' => 'B19ReplayResultPersistedFieldProvenanceTest::test_expected_fields_do_not_inherit_actual_values_or_a_neighbour',
            'basis' => 'E022 impact revalidation: identity verification and evidence admission now explicitly independent from historical comparison PASS; current real MariaDB and targeted producer/persistence/export guards revalidate own source values and both state namespaces. Supplemental binding absence leaves legacy evidence incomplete, never repairs or changes historical comparison. Original scalar/semantic meaning and retained mutations unchanged. Prior basis: The real replay evidence exporter reads the exact persisted replay metric row for the requested id/date and writes its expected_status to replay_result.json. Three independently seeded rows distinguish SUCCESS versus HELD and a NULL decoy, deliberately distinct from actual status; no value is reconstructed from the exported file, an actual-state counterpart or a neighbouring row. Replacing this projection with a plausible wrong source turns the guard red (E-MD-B19-A001-017 R0255). This basis proves this stored-field export, not the semantic production of the metric.',
        ],
        'MD-S075-R0256' => [
            'positive' => 'B19ReplayResultPersistedFieldProvenanceTest::test_replay_result_fields_equal_their_own_independently_seeded_metric_values',
            'negative' => 'B19ReplayResultPersistedFieldProvenanceTest::test_expected_fields_do_not_inherit_actual_values_or_a_neighbour',
            'basis' => 'E022 impact revalidation: identity verification and evidence admission now explicitly independent from historical comparison PASS; current real MariaDB and targeted producer/persistence/export guards revalidate own source values and both state namespaces. Supplemental binding absence leaves legacy evidence incomplete, never repairs or changes historical comparison. Original scalar/semantic meaning and retained mutations unchanged. Prior basis: The real replay evidence exporter reads the exact persisted replay metric row for the requested id/date and writes its expected_trade_date_effective to replay_result.json. Three independently seeded rows distinguish 2026-04-04 versus 2026-04-03 and a NULL decoy, distinct from actual effective dates; no value is reconstructed from the exported file, an actual-state counterpart or a neighbouring row. Replacing this projection with a plausible wrong source turns the guard red (E-MD-B19-A001-017 R0256). This basis proves this stored-field export, not the semantic production of the metric.',
        ],
        'MD-S075-R0257' => [
            'positive' => 'B19ReplayResultPersistedFieldProvenanceTest::test_replay_result_fields_equal_their_own_independently_seeded_metric_values',
            'negative' => 'B19ReplayResultPersistedFieldProvenanceTest::test_expected_fields_do_not_inherit_actual_values_or_a_neighbour',
            'basis' => 'E022 impact revalidation: identity verification and evidence admission now explicitly independent from historical comparison PASS; current real MariaDB and targeted producer/persistence/export guards revalidate own source values and both state namespaces. Supplemental binding absence leaves legacy evidence incomplete, never repairs or changes historical comparison. Original scalar/semantic meaning and retained mutations unchanged. Prior basis: The real replay evidence exporter reads the exact persisted replay metric row for the requested id/date and writes its expected_seal_state to replay_result.json. Three independently seeded rows distinguish UNSEALED versus SEALED and a NULL decoy, deliberately opposite to actual seal state; no value is reconstructed from the exported file, an actual-state counterpart or a neighbouring row. Replacing this projection with a plausible wrong source turns the guard red (E-MD-B19-A001-017 R0257). This basis proves this stored-field export, not the semantic production of the metric.',
        ],
        'MD-S075-R0258' => [
            'positive' => 'B19ReplayResultPersistedFieldProvenanceTest::test_replay_result_fields_equal_their_own_independently_seeded_metric_values',
            'negative' => 'B19ReplayResultPersistedFieldProvenanceTest::test_expected_fields_do_not_inherit_actual_values_or_a_neighbour',
            'basis' => 'E022 impact revalidation: identity verification and evidence admission now explicitly independent from historical comparison PASS; current real MariaDB and targeted producer/persistence/export guards revalidate own source values and both state namespaces. Supplemental binding absence leaves legacy evidence incomplete, never repairs or changes historical comparison. Original scalar/semantic meaning and retained mutations unchanged. Prior basis: The real replay evidence exporter reads the exact persisted replay metric row for the requested id/date and writes its mismatch_summary to replay_result.json. Three independently seeded rows distinguish summary A versus NULL on two non-mismatch rows, distinct from comparison notes; no value is reconstructed from the exported file, an actual-state counterpart or a neighbouring row. Replacing this projection with a plausible wrong source turns the guard red (E-MD-B19-A001-017 R0258). This basis proves this stored-field export, not the semantic production of the metric.',
        ],
        'MD-S075-R0262' => [
            'positive' => 'B19ReplayResultMismatchDetailTest::test_summary_and_both_persisted_mismatches_survive_the_real_export',
            'negative' => 'ReplayVerificationServiceTest::test_verify_replay_marks_mismatch_with_reason_code_when_reason_code_counts_diverge',
            'basis' => 'E022 impact revalidation: identity verification and evidence admission now explicitly independent from historical comparison PASS; current real MariaDB and targeted producer/persistence/export guards revalidate own source values and both state namespaces. Supplemental binding absence leaves legacy evidence incomplete, never repairs or changes historical comparison. Original scalar/semantic meaning and retained mutations unchanged. Prior basis: A real repository/exporter reads a persisted MISMATCH with a short summary and two independently seeded, field-specific mismatches, preserving both detail entries in replay_result.json and replay_evidence_pack.json. The actual replay comparator is separately exercised on a reason-code divergence and the producer guard requires the persisted mismatches_json to contain the reason_code_counts field, not merely a nonempty summary. Dropping exported detail or replacing produced detail with an empty list turns the two guards red (E-MD-B19-A001-017 M16/M17). The producer guard mocks repository calls, but the paired export guard uses real SQLite persistence and a real writer.',
        ],
        'MD-S075-R0260' => [
            'positive' => 'B19ReplayIdentityMariaDbPipelineTest::test_all_identity_paths_follow_independent_inputs_and_persisted_publication_binding',
            'negative' => 'B19ReplayResultSemanticPipelineTest::test_unavailable_prerequisites_block_the_real_pipeline',
            'basis' => 'E022 impact revalidation: identity verification and evidence admission now explicitly independent from historical comparison PASS; current real MariaDB and targeted producer/persistence/export guards revalidate own source values and both state namespaces. Supplemental binding absence leaves legacy evidence incomplete, never repairs or changes historical comparison. Original scalar/semantic meaning and retained mutations unchanged. Prior basis: E021 revalidation under D006/freeze 20261018-001: real verified MariaDB publication gives MATCH/PASS, EXPECTED_DEGRADE/PASS, MISMATCH/FAIL and UNEXPECTED/FAIL. Independently authored counterfactual fixture keeps status/date/seal/version/three batch hashes/reason counts separate through producer/persistence/export; malformed/missing expectation is BLOCKED. Legacy incomplete worlds now correctly BLOCKED; original independent fixture bytes, scalar assertions and applicable E018/E019 mutations retained. Historical evidence text below describes its original freeze, not current legacy admission: Real comparator/admission -> real metric writer -> exact persisted read -> JSON export: literal independent fixtures yield MATCH/PASS, EXPECTED_DEGRADE/PASS (seven usable listings, three expected history holds), MISMATCH/FAIL and UNEXPECTED/FAIL; actual terminal status remains SUCCESS. Missing expected context, unavailable runtime observation identity and self-generated expectation yield NOT_ADMISSIBLE/BLOCKED. Separate executed guards refuse contradictory or invalid classification at both write and export, retain valid historical NULL-status derivation without rewriting old rows, and distinguish run status from replay_status. Ten defect cases red before the boundary correction (F-MD-B19-A001-012), then green; new classification, admission, pair and unknown-value mutations red and byte-restored (E-MD-B19-A001-018 M01-M08).',
        ],
        'MD-S075-R0261' => [
            'positive' => 'B19ReplayIdentityMariaDbPipelineTest::test_all_identity_paths_follow_independent_inputs_and_persisted_publication_binding',
            'negative' => 'B19ReplayResultSemanticPipelineTest::test_unavailable_prerequisites_block_the_real_pipeline',
            'basis' => 'E022 impact revalidation: identity verification and evidence admission now explicitly independent from historical comparison PASS; current real MariaDB and targeted producer/persistence/export guards revalidate own source values and both state namespaces. Supplemental binding absence leaves legacy evidence incomplete, never repairs or changes historical comparison. Original scalar/semantic meaning and retained mutations unchanged. Prior basis: E021 revalidation under D006/freeze 20261018-001: real verified MariaDB publication gives MATCH/PASS, EXPECTED_DEGRADE/PASS, MISMATCH/FAIL and UNEXPECTED/FAIL. Independently authored counterfactual fixture keeps status/date/seal/version/three batch hashes/reason counts separate through producer/persistence/export; malformed/missing expectation is BLOCKED. Legacy incomplete worlds now correctly BLOCKED; original independent fixture bytes, scalar assertions and applicable E018/E019 mutations retained. Historical evidence text below describes its original freeze, not current legacy admission: Hand-authored b19-v1/b19-v2 fixture definitions and retained literal context template are loaded before actual world creation. Version two explicitly expects HELD, prior effective date and UNSEALED while actual is SUCCESS/current date/SEALED: those expected literals and publication/lineage identities survive the real comparator, expected_context_json and expected metric columns into replay_result.json and replay_expected_state.json with fixture id/version/schema and manifest hash. Expected file hashes remain unchanged; no actual outputs generate the oracle. Missing expected status/date/seal stays NULL and BLOCKED, and a self-generated oracle is refused. Actual/expected swaps at producer and exporter, dropped context, removed version and missing-context admission mutations each turn an executed guard red (E-MD-B19-A001-018 M09-M17). No R0025 golden admission is claimed.',
        ],
        'MD-S075-R0264' => [
            'positive' => 'B19ReplayIdentityMariaDbPipelineTest::test_all_identity_paths_follow_independent_inputs_and_persisted_publication_binding',
            'negative' => 'B19ReplayStatePairStableSemanticsTest::test_counterfactual_expected_members_cannot_replace_actual',
            'basis' => 'E022 impact revalidation: identity verification and evidence admission now explicitly independent from historical comparison PASS; current real MariaDB and targeted producer/persistence/export guards revalidate own source values and both state namespaces. Supplemental binding absence leaves legacy evidence incomplete, never repairs or changes historical comparison. Original scalar/semantic meaning and retained mutations unchanged. Prior basis: E021 revalidation under D006/freeze 20261018-001: real verified MariaDB publication gives MATCH/PASS, EXPECTED_DEGRADE/PASS, MISMATCH/FAIL and UNEXPECTED/FAIL. Independently authored counterfactual fixture keeps status/date/seal/version/three batch hashes/reason counts separate through producer/persistence/export; malformed/missing expectation is BLOCKED. Legacy incomplete worlds now correctly BLOCKED; original independent fixture bytes, scalar assertions and applicable E018/E019 mutations retained. Historical evidence text below describes its original freeze, not current legacy admission: E-MD-B19-A001-019: expected status follows its independent source through the real replay comparator, persisted metrics/reason rows and replay_expected_state.json. The accepted hand-authored literal fixture is loaded before actual source rows exist; counterfactual status expectations remain different from real actual values and yield MISMATCH/FAIL. Actual run/publication/eligibility sources and metric values are asserted separately. JSON values are behavioral assertions, not key checks. New exporter swaps/drops fail by assertion and byte-restore; retained E018 M14/M16/M17 cover the unchanged expected status/date/seal projections without rerunning them. Persisted version and output hashes survive later publication supersession/live-run changes. Exact meaning/path unchanged by the unissued F011 draft. No golden fixture admission or authority-dependent identity proof is claimed.',
        ],
        'MD-S075-R0281' => [
            'positive' => 'B19ReplayIdentityMariaDbPipelineTest::test_all_identity_paths_follow_independent_inputs_and_persisted_publication_binding',
            'negative' => 'B19ReplayStatePairStableSemanticsTest::test_counterfactual_expected_members_cannot_replace_actual',
            'basis' => 'E022 impact revalidation: identity verification and evidence admission now explicitly independent from historical comparison PASS; current real MariaDB and targeted producer/persistence/export guards revalidate own source values and both state namespaces. Supplemental binding absence leaves legacy evidence incomplete, never repairs or changes historical comparison. Original scalar/semantic meaning and retained mutations unchanged. Prior basis: E021 revalidation under D006/freeze 20261018-001: real verified MariaDB publication gives MATCH/PASS, EXPECTED_DEGRADE/PASS, MISMATCH/FAIL and UNEXPECTED/FAIL. Independently authored counterfactual fixture keeps status/date/seal/version/three batch hashes/reason counts separate through producer/persistence/export; malformed/missing expectation is BLOCKED. Legacy incomplete worlds now correctly BLOCKED; original independent fixture bytes, scalar assertions and applicable E018/E019 mutations retained. Historical evidence text below describes its original freeze, not current legacy admission: E-MD-B19-A001-019: actual status follows its independent source through the real replay comparator, persisted metrics/reason rows and replay_actual_state.json. The accepted hand-authored literal fixture is loaded before actual source rows exist; counterfactual status expectations remain different from real actual values and yield MISMATCH/FAIL. Actual run/publication/eligibility sources and metric values are asserted separately. JSON values are behavioral assertions, not key checks. New exporter swaps/drops fail by assertion and byte-restore; retained E018 M14/M16/M17 cover the unchanged expected status/date/seal projections without rerunning them. Persisted version and output hashes survive later publication supersession/live-run changes. Exact meaning/path unchanged by the unissued F011 draft. No golden fixture admission or authority-dependent identity proof is claimed.',
        ],
        'MD-S075-R0265' => [
            'positive' => 'B19ReplayIdentityMariaDbPipelineTest::test_all_identity_paths_follow_independent_inputs_and_persisted_publication_binding',
            'negative' => 'B19ReplayStatePairStableSemanticsTest::test_counterfactual_expected_members_cannot_replace_actual',
            'basis' => 'E022 impact revalidation: identity verification and evidence admission now explicitly independent from historical comparison PASS; current real MariaDB and targeted producer/persistence/export guards revalidate own source values and both state namespaces. Supplemental binding absence leaves legacy evidence incomplete, never repairs or changes historical comparison. Original scalar/semantic meaning and retained mutations unchanged. Prior basis: E021 revalidation under D006/freeze 20261018-001: real verified MariaDB publication gives MATCH/PASS, EXPECTED_DEGRADE/PASS, MISMATCH/FAIL and UNEXPECTED/FAIL. Independently authored counterfactual fixture keeps status/date/seal/version/three batch hashes/reason counts separate through producer/persistence/export; malformed/missing expectation is BLOCKED. Legacy incomplete worlds now correctly BLOCKED; original independent fixture bytes, scalar assertions and applicable E018/E019 mutations retained. Historical evidence text below describes its original freeze, not current legacy admission: E-MD-B19-A001-019: expected trade_date_effective follows its independent source through the real replay comparator, persisted metrics/reason rows and replay_expected_state.json. The accepted hand-authored literal fixture is loaded before actual source rows exist; counterfactual trade_date_effective expectations remain different from real actual values and yield MISMATCH/FAIL. Actual run/publication/eligibility sources and metric values are asserted separately. JSON values are behavioral assertions, not key checks. New exporter swaps/drops fail by assertion and byte-restore; retained E018 M14/M16/M17 cover the unchanged expected status/date/seal projections without rerunning them. Persisted version and output hashes survive later publication supersession/live-run changes. Exact meaning/path unchanged by the unissued F011 draft. No golden fixture admission or authority-dependent identity proof is claimed.',
        ],
        'MD-S075-R0282' => [
            'positive' => 'B19ReplayIdentityMariaDbPipelineTest::test_all_identity_paths_follow_independent_inputs_and_persisted_publication_binding',
            'negative' => 'B19ReplayStatePairStableSemanticsTest::test_counterfactual_expected_members_cannot_replace_actual',
            'basis' => 'E022 impact revalidation: identity verification and evidence admission now explicitly independent from historical comparison PASS; current real MariaDB and targeted producer/persistence/export guards revalidate own source values and both state namespaces. Supplemental binding absence leaves legacy evidence incomplete, never repairs or changes historical comparison. Original scalar/semantic meaning and retained mutations unchanged. Prior basis: E021 revalidation under D006/freeze 20261018-001: real verified MariaDB publication gives MATCH/PASS, EXPECTED_DEGRADE/PASS, MISMATCH/FAIL and UNEXPECTED/FAIL. Independently authored counterfactual fixture keeps status/date/seal/version/three batch hashes/reason counts separate through producer/persistence/export; malformed/missing expectation is BLOCKED. Legacy incomplete worlds now correctly BLOCKED; original independent fixture bytes, scalar assertions and applicable E018/E019 mutations retained. Historical evidence text below describes its original freeze, not current legacy admission: E-MD-B19-A001-019: actual trade_date_effective follows its independent source through the real replay comparator, persisted metrics/reason rows and replay_actual_state.json. The accepted hand-authored literal fixture is loaded before actual source rows exist; counterfactual trade_date_effective expectations remain different from real actual values and yield MISMATCH/FAIL. Actual run/publication/eligibility sources and metric values are asserted separately. JSON values are behavioral assertions, not key checks. New exporter swaps/drops fail by assertion and byte-restore; retained E018 M14/M16/M17 cover the unchanged expected status/date/seal projections without rerunning them. Persisted version and output hashes survive later publication supersession/live-run changes. Exact meaning/path unchanged by the unissued F011 draft. No golden fixture admission or authority-dependent identity proof is claimed.',
        ],
        'MD-S075-R0266' => [
            'positive' => 'B19ReplayIdentityMariaDbPipelineTest::test_all_identity_paths_follow_independent_inputs_and_persisted_publication_binding',
            'negative' => 'B19ReplayStatePairStableSemanticsTest::test_counterfactual_expected_members_cannot_replace_actual',
            'basis' => 'E022 impact revalidation: identity verification and evidence admission now explicitly independent from historical comparison PASS; current real MariaDB and targeted producer/persistence/export guards revalidate own source values and both state namespaces. Supplemental binding absence leaves legacy evidence incomplete, never repairs or changes historical comparison. Original scalar/semantic meaning and retained mutations unchanged. Prior basis: E021 revalidation under D006/freeze 20261018-001: real verified MariaDB publication gives MATCH/PASS, EXPECTED_DEGRADE/PASS, MISMATCH/FAIL and UNEXPECTED/FAIL. Independently authored counterfactual fixture keeps status/date/seal/version/three batch hashes/reason counts separate through producer/persistence/export; malformed/missing expectation is BLOCKED. Legacy incomplete worlds now correctly BLOCKED; original independent fixture bytes, scalar assertions and applicable E018/E019 mutations retained. Historical evidence text below describes its original freeze, not current legacy admission: E-MD-B19-A001-019: expected seal_state follows its independent source through the real replay comparator, persisted metrics/reason rows and replay_expected_state.json. The accepted hand-authored literal fixture is loaded before actual source rows exist; counterfactual seal_state expectations remain different from real actual values and yield MISMATCH/FAIL. Actual run/publication/eligibility sources and metric values are asserted separately. JSON values are behavioral assertions, not key checks. New exporter swaps/drops fail by assertion and byte-restore; retained E018 M14/M16/M17 cover the unchanged expected status/date/seal projections without rerunning them. Persisted version and output hashes survive later publication supersession/live-run changes. Exact meaning/path unchanged by the unissued F011 draft. No golden fixture admission or authority-dependent identity proof is claimed.',
        ],
        'MD-S075-R0283' => [
            'positive' => 'B19ReplayIdentityMariaDbPipelineTest::test_all_identity_paths_follow_independent_inputs_and_persisted_publication_binding',
            'negative' => 'B19ReplayStatePairStableSemanticsTest::test_counterfactual_expected_members_cannot_replace_actual',
            'basis' => 'E022 impact revalidation: identity verification and evidence admission now explicitly independent from historical comparison PASS; current real MariaDB and targeted producer/persistence/export guards revalidate own source values and both state namespaces. Supplemental binding absence leaves legacy evidence incomplete, never repairs or changes historical comparison. Original scalar/semantic meaning and retained mutations unchanged. Prior basis: E021 revalidation under D006/freeze 20261018-001: real verified MariaDB publication gives MATCH/PASS, EXPECTED_DEGRADE/PASS, MISMATCH/FAIL and UNEXPECTED/FAIL. Independently authored counterfactual fixture keeps status/date/seal/version/three batch hashes/reason counts separate through producer/persistence/export; malformed/missing expectation is BLOCKED. Legacy incomplete worlds now correctly BLOCKED; original independent fixture bytes, scalar assertions and applicable E018/E019 mutations retained. Historical evidence text below describes its original freeze, not current legacy admission: E-MD-B19-A001-019: actual seal_state follows its independent source through the real replay comparator, persisted metrics/reason rows and replay_actual_state.json. The accepted hand-authored literal fixture is loaded before actual source rows exist; counterfactual seal_state expectations remain different from real actual values and yield MISMATCH/FAIL. Actual run/publication/eligibility sources and metric values are asserted separately. JSON values are behavioral assertions, not key checks. New exporter swaps/drops fail by assertion and byte-restore; retained E018 M14/M16/M17 cover the unchanged expected status/date/seal projections without rerunning them. Persisted version and output hashes survive later publication supersession/live-run changes. Exact meaning/path unchanged by the unissued F011 draft. No golden fixture admission or authority-dependent identity proof is claimed.',
        ],
        'MD-S075-R0275' => [
            'positive' => 'B19ReplayIdentityMariaDbPipelineTest::test_all_identity_paths_follow_independent_inputs_and_persisted_publication_binding',
            'negative' => 'B19ReplayStatePairStableSemanticsTest::test_counterfactual_expected_members_cannot_replace_actual',
            'basis' => 'E022 impact revalidation: identity verification and evidence admission now explicitly independent from historical comparison PASS; current real MariaDB and targeted producer/persistence/export guards revalidate own source values and both state namespaces. Supplemental binding absence leaves legacy evidence incomplete, never repairs or changes historical comparison. Original scalar/semantic meaning and retained mutations unchanged. Prior basis: E021 revalidation under D006/freeze 20261018-001: real verified MariaDB publication gives MATCH/PASS, EXPECTED_DEGRADE/PASS, MISMATCH/FAIL and UNEXPECTED/FAIL. Independently authored counterfactual fixture keeps status/date/seal/version/three batch hashes/reason counts separate through producer/persistence/export; malformed/missing expectation is BLOCKED. Legacy incomplete worlds now correctly BLOCKED; original independent fixture bytes, scalar assertions and applicable E018/E019 mutations retained. Historical evidence text below describes its original freeze, not current legacy admission: E-MD-B19-A001-019: expected publication_version follows its independent source through the real replay comparator, persisted metrics/reason rows and replay_expected_state.json. The accepted hand-authored literal fixture is loaded before actual source rows exist; counterfactual publication_version expectations remain different from real actual values and yield MISMATCH/FAIL. Actual run/publication/eligibility sources and metric values are asserted separately. JSON values are behavioral assertions, not key checks. New exporter swaps/drops fail by assertion and byte-restore; retained E018 M14/M16/M17 cover the unchanged expected status/date/seal projections without rerunning them. Persisted version and output hashes survive later publication supersession/live-run changes. Exact meaning/path unchanged by the unissued F011 draft. No golden fixture admission or authority-dependent identity proof is claimed.',
        ],
        'MD-S075-R0292' => [
            'positive' => 'B19ReplayIdentityMariaDbPipelineTest::test_all_identity_paths_follow_independent_inputs_and_persisted_publication_binding',
            'negative' => 'B19ReplayStatePairStableSemanticsTest::test_counterfactual_expected_members_cannot_replace_actual',
            'basis' => 'E022 impact revalidation: identity verification and evidence admission now explicitly independent from historical comparison PASS; current real MariaDB and targeted producer/persistence/export guards revalidate own source values and both state namespaces. Supplemental binding absence leaves legacy evidence incomplete, never repairs or changes historical comparison. Original scalar/semantic meaning and retained mutations unchanged. Prior basis: E021 revalidation under D006/freeze 20261018-001: real verified MariaDB publication gives MATCH/PASS, EXPECTED_DEGRADE/PASS, MISMATCH/FAIL and UNEXPECTED/FAIL. Independently authored counterfactual fixture keeps status/date/seal/version/three batch hashes/reason counts separate through producer/persistence/export; malformed/missing expectation is BLOCKED. Legacy incomplete worlds now correctly BLOCKED; original independent fixture bytes, scalar assertions and applicable E018/E019 mutations retained. Historical evidence text below describes its original freeze, not current legacy admission: E-MD-B19-A001-019: actual publication_version follows its independent source through the real replay comparator, persisted metrics/reason rows and replay_actual_state.json. The accepted hand-authored literal fixture is loaded before actual source rows exist; counterfactual publication_version expectations remain different from real actual values and yield MISMATCH/FAIL. Actual run/publication/eligibility sources and metric values are asserted separately. JSON values are behavioral assertions, not key checks. New exporter swaps/drops fail by assertion and byte-restore; retained E018 M14/M16/M17 cover the unchanged expected status/date/seal projections without rerunning them. Persisted version and output hashes survive later publication supersession/live-run changes. Exact meaning/path unchanged by the unissued F011 draft. No golden fixture admission or authority-dependent identity proof is claimed.',
        ],
        'MD-S075-R0276' => [
            'positive' => 'B19ReplayIdentityMariaDbPipelineTest::test_all_identity_paths_follow_independent_inputs_and_persisted_publication_binding',
            'negative' => 'B19ReplayStatePairStableSemanticsTest::test_counterfactual_expected_members_cannot_replace_actual',
            'basis' => 'E022 impact revalidation: identity verification and evidence admission now explicitly independent from historical comparison PASS; current real MariaDB and targeted producer/persistence/export guards revalidate own source values and both state namespaces. Supplemental binding absence leaves legacy evidence incomplete, never repairs or changes historical comparison. Original scalar/semantic meaning and retained mutations unchanged. Prior basis: E021 revalidation under D006/freeze 20261018-001: real verified MariaDB publication gives MATCH/PASS, EXPECTED_DEGRADE/PASS, MISMATCH/FAIL and UNEXPECTED/FAIL. Independently authored counterfactual fixture keeps status/date/seal/version/three batch hashes/reason counts separate through producer/persistence/export; malformed/missing expectation is BLOCKED. Legacy incomplete worlds now correctly BLOCKED; original independent fixture bytes, scalar assertions and applicable E018/E019 mutations retained. Historical evidence text below describes its original freeze, not current legacy admission: E-MD-B19-A001-019: expected bars_batch_hash follows its independent source through the real replay comparator, persisted metrics/reason rows and replay_expected_state.json. The accepted hand-authored literal fixture is loaded before actual source rows exist; counterfactual bars_batch_hash expectations remain different from real actual values and yield MISMATCH/FAIL. Actual run/publication/eligibility sources and metric values are asserted separately. JSON values are behavioral assertions, not key checks. New exporter swaps/drops fail by assertion and byte-restore; retained E018 M14/M16/M17 cover the unchanged expected status/date/seal projections without rerunning them. Persisted version and output hashes survive later publication supersession/live-run changes. Exact meaning/path unchanged by the unissued F011 draft. No golden fixture admission or authority-dependent identity proof is claimed.',
        ],
        'MD-S075-R0293' => [
            'positive' => 'B19ReplayIdentityMariaDbPipelineTest::test_all_identity_paths_follow_independent_inputs_and_persisted_publication_binding',
            'negative' => 'B19ReplayStatePairStableSemanticsTest::test_counterfactual_expected_members_cannot_replace_actual',
            'basis' => 'E022 impact revalidation: identity verification and evidence admission now explicitly independent from historical comparison PASS; current real MariaDB and targeted producer/persistence/export guards revalidate own source values and both state namespaces. Supplemental binding absence leaves legacy evidence incomplete, never repairs or changes historical comparison. Original scalar/semantic meaning and retained mutations unchanged. Prior basis: E021 revalidation under D006/freeze 20261018-001: real verified MariaDB publication gives MATCH/PASS, EXPECTED_DEGRADE/PASS, MISMATCH/FAIL and UNEXPECTED/FAIL. Independently authored counterfactual fixture keeps status/date/seal/version/three batch hashes/reason counts separate through producer/persistence/export; malformed/missing expectation is BLOCKED. Legacy incomplete worlds now correctly BLOCKED; original independent fixture bytes, scalar assertions and applicable E018/E019 mutations retained. Historical evidence text below describes its original freeze, not current legacy admission: E-MD-B19-A001-019: actual bars_batch_hash follows its independent source through the real replay comparator, persisted metrics/reason rows and replay_actual_state.json. The accepted hand-authored literal fixture is loaded before actual source rows exist; counterfactual bars_batch_hash expectations remain different from real actual values and yield MISMATCH/FAIL. Actual run/publication/eligibility sources and metric values are asserted separately. JSON values are behavioral assertions, not key checks. New exporter swaps/drops fail by assertion and byte-restore; retained E018 M14/M16/M17 cover the unchanged expected status/date/seal projections without rerunning them. Persisted version and output hashes survive later publication supersession/live-run changes. Exact meaning/path unchanged by the unissued F011 draft. No golden fixture admission or authority-dependent identity proof is claimed.',
        ],
        'MD-S075-R0277' => [
            'positive' => 'B19ReplayIdentityMariaDbPipelineTest::test_all_identity_paths_follow_independent_inputs_and_persisted_publication_binding',
            'negative' => 'B19ReplayStatePairStableSemanticsTest::test_counterfactual_expected_members_cannot_replace_actual',
            'basis' => 'E022 impact revalidation: identity verification and evidence admission now explicitly independent from historical comparison PASS; current real MariaDB and targeted producer/persistence/export guards revalidate own source values and both state namespaces. Supplemental binding absence leaves legacy evidence incomplete, never repairs or changes historical comparison. Original scalar/semantic meaning and retained mutations unchanged. Prior basis: E021 revalidation under D006/freeze 20261018-001: real verified MariaDB publication gives MATCH/PASS, EXPECTED_DEGRADE/PASS, MISMATCH/FAIL and UNEXPECTED/FAIL. Independently authored counterfactual fixture keeps status/date/seal/version/three batch hashes/reason counts separate through producer/persistence/export; malformed/missing expectation is BLOCKED. Legacy incomplete worlds now correctly BLOCKED; original independent fixture bytes, scalar assertions and applicable E018/E019 mutations retained. Historical evidence text below describes its original freeze, not current legacy admission: E-MD-B19-A001-019: expected indicators_batch_hash follows its independent source through the real replay comparator, persisted metrics/reason rows and replay_expected_state.json. The accepted hand-authored literal fixture is loaded before actual source rows exist; counterfactual indicators_batch_hash expectations remain different from real actual values and yield MISMATCH/FAIL. Actual run/publication/eligibility sources and metric values are asserted separately. JSON values are behavioral assertions, not key checks. New exporter swaps/drops fail by assertion and byte-restore; retained E018 M14/M16/M17 cover the unchanged expected status/date/seal projections without rerunning them. Persisted version and output hashes survive later publication supersession/live-run changes. Exact meaning/path unchanged by the unissued F011 draft. No golden fixture admission or authority-dependent identity proof is claimed.',
        ],
        'MD-S075-R0294' => [
            'positive' => 'B19ReplayIdentityMariaDbPipelineTest::test_all_identity_paths_follow_independent_inputs_and_persisted_publication_binding',
            'negative' => 'B19ReplayStatePairStableSemanticsTest::test_counterfactual_expected_members_cannot_replace_actual',
            'basis' => 'E022 impact revalidation: identity verification and evidence admission now explicitly independent from historical comparison PASS; current real MariaDB and targeted producer/persistence/export guards revalidate own source values and both state namespaces. Supplemental binding absence leaves legacy evidence incomplete, never repairs or changes historical comparison. Original scalar/semantic meaning and retained mutations unchanged. Prior basis: E021 revalidation under D006/freeze 20261018-001: real verified MariaDB publication gives MATCH/PASS, EXPECTED_DEGRADE/PASS, MISMATCH/FAIL and UNEXPECTED/FAIL. Independently authored counterfactual fixture keeps status/date/seal/version/three batch hashes/reason counts separate through producer/persistence/export; malformed/missing expectation is BLOCKED. Legacy incomplete worlds now correctly BLOCKED; original independent fixture bytes, scalar assertions and applicable E018/E019 mutations retained. Historical evidence text below describes its original freeze, not current legacy admission: E-MD-B19-A001-019: actual indicators_batch_hash follows its independent source through the real replay comparator, persisted metrics/reason rows and replay_actual_state.json. The accepted hand-authored literal fixture is loaded before actual source rows exist; counterfactual indicators_batch_hash expectations remain different from real actual values and yield MISMATCH/FAIL. Actual run/publication/eligibility sources and metric values are asserted separately. JSON values are behavioral assertions, not key checks. New exporter swaps/drops fail by assertion and byte-restore; retained E018 M14/M16/M17 cover the unchanged expected status/date/seal projections without rerunning them. Persisted version and output hashes survive later publication supersession/live-run changes. Exact meaning/path unchanged by the unissued F011 draft. No golden fixture admission or authority-dependent identity proof is claimed.',
        ],
        'MD-S075-R0278' => [
            'positive' => 'B19ReplayIdentityMariaDbPipelineTest::test_all_identity_paths_follow_independent_inputs_and_persisted_publication_binding',
            'negative' => 'B19ReplayStatePairStableSemanticsTest::test_counterfactual_expected_members_cannot_replace_actual',
            'basis' => 'E022 impact revalidation: identity verification and evidence admission now explicitly independent from historical comparison PASS; current real MariaDB and targeted producer/persistence/export guards revalidate own source values and both state namespaces. Supplemental binding absence leaves legacy evidence incomplete, never repairs or changes historical comparison. Original scalar/semantic meaning and retained mutations unchanged. Prior basis: E021 revalidation under D006/freeze 20261018-001: real verified MariaDB publication gives MATCH/PASS, EXPECTED_DEGRADE/PASS, MISMATCH/FAIL and UNEXPECTED/FAIL. Independently authored counterfactual fixture keeps status/date/seal/version/three batch hashes/reason counts separate through producer/persistence/export; malformed/missing expectation is BLOCKED. Legacy incomplete worlds now correctly BLOCKED; original independent fixture bytes, scalar assertions and applicable E018/E019 mutations retained. Historical evidence text below describes its original freeze, not current legacy admission: E-MD-B19-A001-019: expected eligibility_batch_hash follows its independent source through the real replay comparator, persisted metrics/reason rows and replay_expected_state.json. The accepted hand-authored literal fixture is loaded before actual source rows exist; counterfactual eligibility_batch_hash expectations remain different from real actual values and yield MISMATCH/FAIL. Actual run/publication/eligibility sources and metric values are asserted separately. JSON values are behavioral assertions, not key checks. New exporter swaps/drops fail by assertion and byte-restore; retained E018 M14/M16/M17 cover the unchanged expected status/date/seal projections without rerunning them. Persisted version and output hashes survive later publication supersession/live-run changes. Exact meaning/path unchanged by the unissued F011 draft. No golden fixture admission or authority-dependent identity proof is claimed.',
        ],
        'MD-S075-R0295' => [
            'positive' => 'B19ReplayIdentityMariaDbPipelineTest::test_all_identity_paths_follow_independent_inputs_and_persisted_publication_binding',
            'negative' => 'B19ReplayStatePairStableSemanticsTest::test_counterfactual_expected_members_cannot_replace_actual',
            'basis' => 'E022 impact revalidation: identity verification and evidence admission now explicitly independent from historical comparison PASS; current real MariaDB and targeted producer/persistence/export guards revalidate own source values and both state namespaces. Supplemental binding absence leaves legacy evidence incomplete, never repairs or changes historical comparison. Original scalar/semantic meaning and retained mutations unchanged. Prior basis: E021 revalidation under D006/freeze 20261018-001: real verified MariaDB publication gives MATCH/PASS, EXPECTED_DEGRADE/PASS, MISMATCH/FAIL and UNEXPECTED/FAIL. Independently authored counterfactual fixture keeps status/date/seal/version/three batch hashes/reason counts separate through producer/persistence/export; malformed/missing expectation is BLOCKED. Legacy incomplete worlds now correctly BLOCKED; original independent fixture bytes, scalar assertions and applicable E018/E019 mutations retained. Historical evidence text below describes its original freeze, not current legacy admission: E-MD-B19-A001-019: actual eligibility_batch_hash follows its independent source through the real replay comparator, persisted metrics/reason rows and replay_actual_state.json. The accepted hand-authored literal fixture is loaded before actual source rows exist; counterfactual eligibility_batch_hash expectations remain different from real actual values and yield MISMATCH/FAIL. Actual run/publication/eligibility sources and metric values are asserted separately. JSON values are behavioral assertions, not key checks. New exporter swaps/drops fail by assertion and byte-restore; retained E018 M14/M16/M17 cover the unchanged expected status/date/seal projections without rerunning them. Persisted version and output hashes survive later publication supersession/live-run changes. Exact meaning/path unchanged by the unissued F011 draft. No golden fixture admission or authority-dependent identity proof is claimed.',
        ],
        'MD-S075-R0279' => [
            'positive' => 'B19ReplayIdentityMariaDbPipelineTest::test_all_identity_paths_follow_independent_inputs_and_persisted_publication_binding',
            'negative' => 'B19ReplayStatePairStableSemanticsTest::test_counterfactual_expected_members_cannot_replace_actual',
            'basis' => 'E022 impact revalidation: identity verification and evidence admission now explicitly independent from historical comparison PASS; current real MariaDB and targeted producer/persistence/export guards revalidate own source values and both state namespaces. Supplemental binding absence leaves legacy evidence incomplete, never repairs or changes historical comparison. Original scalar/semantic meaning and retained mutations unchanged. Prior basis: E021 revalidation under D006/freeze 20261018-001: real verified MariaDB publication gives MATCH/PASS, EXPECTED_DEGRADE/PASS, MISMATCH/FAIL and UNEXPECTED/FAIL. Independently authored counterfactual fixture keeps status/date/seal/version/three batch hashes/reason counts separate through producer/persistence/export; malformed/missing expectation is BLOCKED. Legacy incomplete worlds now correctly BLOCKED; original independent fixture bytes, scalar assertions and applicable E018/E019 mutations retained. Historical evidence text below describes its original freeze, not current legacy admission: E-MD-B19-A001-019: expected reason_code_counts follows its independent source through the real replay comparator, persisted metrics/reason rows and replay_expected_state.json. The accepted hand-authored literal fixture is loaded before actual source rows exist; counterfactual reason_code_counts expectations remain different from real actual values and yield MISMATCH/FAIL. Actual run/publication/eligibility sources and metric values are asserted separately. JSON values are behavioral assertions, not key checks. New exporter swaps/drops fail by assertion and byte-restore; retained E018 M14/M16/M17 cover the unchanged expected status/date/seal projections without rerunning them. Persisted version and output hashes survive later publication supersession/live-run changes. Exact meaning/path unchanged by the unissued F011 draft. No golden fixture admission or authority-dependent identity proof is claimed.',
        ],
        'MD-S075-R0296' => [
            'positive' => 'B19ReplayIdentityMariaDbPipelineTest::test_all_identity_paths_follow_independent_inputs_and_persisted_publication_binding',
            'negative' => 'B19ReplayStatePairStableSemanticsTest::test_counterfactual_expected_members_cannot_replace_actual',
            'basis' => 'E022 impact revalidation: identity verification and evidence admission now explicitly independent from historical comparison PASS; current real MariaDB and targeted producer/persistence/export guards revalidate own source values and both state namespaces. Supplemental binding absence leaves legacy evidence incomplete, never repairs or changes historical comparison. Original scalar/semantic meaning and retained mutations unchanged. Prior basis: E021 revalidation under D006/freeze 20261018-001: real verified MariaDB publication gives MATCH/PASS, EXPECTED_DEGRADE/PASS, MISMATCH/FAIL and UNEXPECTED/FAIL. Independently authored counterfactual fixture keeps status/date/seal/version/three batch hashes/reason counts separate through producer/persistence/export; malformed/missing expectation is BLOCKED. Legacy incomplete worlds now correctly BLOCKED; original independent fixture bytes, scalar assertions and applicable E018/E019 mutations retained. Historical evidence text below describes its original freeze, not current legacy admission: E-MD-B19-A001-019: actual reason_code_counts follows its independent source through the real replay comparator, persisted metrics/reason rows and replay_actual_state.json. The accepted hand-authored literal fixture is loaded before actual source rows exist; counterfactual reason_code_counts expectations remain different from real actual values and yield MISMATCH/FAIL. Actual run/publication/eligibility sources and metric values are asserted separately. JSON values are behavioral assertions, not key checks. New exporter swaps/drops fail by assertion and byte-restore; retained E018 M14/M16/M17 cover the unchanged expected status/date/seal projections without rerunning them. Persisted version and output hashes survive later publication supersession/live-run changes. Exact meaning/path unchanged by the unissued F011 draft. No golden fixture admission or authority-dependent identity proof is claimed.',
        ],

        // E021: 28 new bases; real MariaDB producer + independent fixture/source inputs + paired negative projections.
        'MD-S075-R0152' => [
            'positive' => 'B19ConflictReasonPipelineTest::test_stable_listing_conflicts_and_duplicate_winners_reach_real_storage_and_export',
            'negative' => 'B19ConflictReasonPipelineTest::test_conflict_is_registered_active_bar_hard_and_duplicate_is_still_warn',
            'basis' => 'E-MD-B19-A001-021: invalid_bars.csv.invalid_reason_code; source: independently authored stable-listing/date conflicts, exact source refs/observation IDs, normal and recovered ingest; full canonical SQL seed on SQLite/MariaDB. Real producer -> persisted metric/audit rows -> exact JSON/CSV export, paired with independent field-specific projection expectations and negative guards. Mutations: normal_ticker_key; recovered_ticker_key; conflict_as_duplicate; severity_wrong; assertion-red, exact byte restoration. Applicable identities are non-NULL; AS_KNOWN publication-only members explicitly inapplicable; unsupported PUBLICATION_EXACT RAW unavailable/BLOCKED. No live repair, no actual-to-expected oracle copying, no successor golden candidate admission.',
        ],
        'MD-S075-R0240' => [
            'positive' => 'B19ReplayIdentityMariaDbPipelineTest::test_all_identity_paths_follow_independent_inputs_and_persisted_publication_binding',
            'negative' => 'B19ReplayIdentityProjectionTest::test_expected_and_actual_contexts_are_preserved_separately',
            'basis' => 'E022 impact revalidation: identity verification and evidence admission now explicitly independent from historical comparison PASS; current real MariaDB and targeted producer/persistence/export guards revalidate own source values and both state namespaces. Supplemental binding absence leaves legacy evidence incomplete, never repairs or changes historical comparison. Original scalar/semantic meaning and retained mutations unchanged. Prior basis: E-MD-B19-A001-021: knowledge_cutoff_at; source: explicit caller cutoff for AS_KNOWN, NULL publication coordinate for PUBLICATION_EXACT. Real producer -> persisted metric/audit rows -> exact JSON/CSV export, paired with independent field-specific projection expectations and negative guards. Mutations: cutoff_name; expected_mode_substitution; actual_mode_substitution; assertion-red, exact byte restoration. Applicable identities are non-NULL; AS_KNOWN publication-only members explicitly inapplicable; unsupported PUBLICATION_EXACT RAW unavailable/BLOCKED. No live repair, no actual-to-expected oracle copying, no successor golden candidate admission.',
        ],
        'MD-S075-R0241' => [
            'positive' => 'B19ReplayIdentityMariaDbPipelineTest::test_all_identity_paths_follow_independent_inputs_and_persisted_publication_binding',
            'negative' => 'B19ReplayIdentityProjectionTest::test_expected_and_actual_contexts_are_preserved_separately',
            'basis' => 'E022 impact revalidation: identity verification and evidence admission now explicitly independent from historical comparison PASS; current real MariaDB and targeted producer/persistence/export guards revalidate own source values and both state namespaces. Supplemental binding absence leaves legacy evidence incomplete, never repairs or changes historical comparison. Original scalar/semantic meaning and retained mutations unchanged. Prior basis: E-MD-B19-A001-021: bound_inputs.source_observation_manifest_hash; source: immutable selected publication lineage / AS_KNOWN observation snapshot. Real producer -> persisted metric/audit rows -> exact JSON/CSV export, paired with independent field-specific projection expectations and negative guards. Mutations: observation_substitution; assertion-red, exact byte restoration. Applicable identities are non-NULL; AS_KNOWN publication-only members explicitly inapplicable; unsupported PUBLICATION_EXACT RAW unavailable/BLOCKED. No live repair, no actual-to-expected oracle copying, no successor golden candidate admission.',
        ],
        'MD-S075-R0242' => [
            'positive' => 'B19ReplayIdentityMariaDbPipelineTest::test_all_identity_paths_follow_independent_inputs_and_persisted_publication_binding',
            'negative' => 'B19ReplayIdentityProjectionTest::test_expected_and_actual_contexts_are_preserved_separately',
            'basis' => 'E022 impact revalidation: identity verification and evidence admission now explicitly independent from historical comparison PASS; current real MariaDB and targeted producer/persistence/export guards revalidate own source values and both state namespaces. Supplemental binding absence leaves legacy evidence incomplete, never repairs or changes historical comparison. Original scalar/semantic meaning and retained mutations unchanged. Prior basis: E-MD-B19-A001-021: bound_inputs.config_snapshot_id; source: selected publication primary config snapshot ID, typed integer. Real producer -> persisted metric/audit rows -> exact JSON/CSV export, paired with independent field-specific projection expectations and negative guards. Mutations: config_id_type; assertion-red, exact byte restoration. Applicable identities are non-NULL; AS_KNOWN publication-only members explicitly inapplicable; unsupported PUBLICATION_EXACT RAW unavailable/BLOCKED. No live repair, no actual-to-expected oracle copying, no successor golden candidate admission.',
        ],
        'MD-S075-R0243' => [
            'positive' => 'B19ReplayIdentityMariaDbPipelineTest::test_all_identity_paths_follow_independent_inputs_and_persisted_publication_binding',
            'negative' => 'B19ReplayIdentityProjectionTest::test_expected_and_actual_contexts_are_preserved_separately',
            'basis' => 'E022 impact revalidation: identity verification and evidence admission now explicitly independent from historical comparison PASS; current real MariaDB and targeted producer/persistence/export guards revalidate own source values and both state namespaces. Supplemental binding absence leaves legacy evidence incomplete, never repairs or changes historical comparison. Original scalar/semantic meaning and retained mutations unchanged. Prior basis: E-MD-B19-A001-021: bound_inputs.config_snapshot_hash; source: SHA256 of independently retained resolved_config_json; selected ID/hash pair. Real producer -> persisted metric/audit rows -> exact JSON/CSV export, paired with independent field-specific projection expectations and negative guards. Mutations: config_hash_substitution; config_ref_fallback; assertion-red, exact byte restoration. Applicable identities are non-NULL; AS_KNOWN publication-only members explicitly inapplicable; unsupported PUBLICATION_EXACT RAW unavailable/BLOCKED. No live repair, no actual-to-expected oracle copying, no successor golden candidate admission.',
        ],
        'MD-S075-R0244' => [
            'positive' => 'B19ReplayIdentityMariaDbPipelineTest::test_all_identity_paths_follow_independent_inputs_and_persisted_publication_binding',
            'negative' => 'B19ReplayIdentityProjectionTest::test_expected_and_actual_contexts_are_preserved_separately',
            'basis' => 'E022 impact revalidation: identity verification and evidence admission now explicitly independent from historical comparison PASS; current real MariaDB and targeted producer/persistence/export guards revalidate own source values and both state namespaces. Supplemental binding absence leaves legacy evidence incomplete, never repairs or changes historical comparison. Original scalar/semantic meaning and retained mutations unchanged. Prior basis: E-MD-B19-A001-021: actual_context.actual_publication_manifest_identity.temporal_revision_set_hash; source: manual hash over selected publication trade_date and authoritative identity/calendar/status revision hashes. Real producer -> persisted metric/audit rows -> exact JSON/CSV export, paired with independent field-specific projection expectations and negative guards. Mutations: temporal_alias; persisted_envelope_validation_bypass; assertion-red, exact byte restoration. Applicable identities are non-NULL; AS_KNOWN publication-only members explicitly inapplicable; unsupported PUBLICATION_EXACT RAW unavailable/BLOCKED. No live repair, no actual-to-expected oracle copying, no successor golden candidate admission.',
        ],
        'MD-S075-R0245' => [
            'positive' => 'B19ReplayIdentityMariaDbPipelineTest::test_all_identity_paths_follow_independent_inputs_and_persisted_publication_binding',
            'negative' => 'B19ReplayIdentityProjectionTest::test_expected_and_actual_contexts_are_preserved_separately',
            'basis' => 'E022 impact revalidation: identity verification and evidence admission now explicitly independent from historical comparison PASS; current real MariaDB and targeted producer/persistence/export guards revalidate own source values and both state namespaces. Supplemental binding absence leaves legacy evidence incomplete, never repairs or changes historical comparison. Original scalar/semantic meaning and retained mutations unchanged. Prior basis: E-MD-B19-A001-021: actual_context.actual_publication_context.factor_set_id; source: selected publication factor_set_id from primary input row. Real producer -> persisted metric/audit rows -> exact JSON/CSV export, paired with independent field-specific projection expectations and negative guards. Mutations: result_context_swap; assertion-red, exact byte restoration. Applicable identities are non-NULL; AS_KNOWN publication-only members explicitly inapplicable; unsupported PUBLICATION_EXACT RAW unavailable/BLOCKED. No live repair, no actual-to-expected oracle copying, no successor golden candidate admission.',
        ],
        'MD-S075-R0246' => [
            'positive' => 'B19ReplayIdentityMariaDbPipelineTest::test_all_identity_paths_follow_independent_inputs_and_persisted_publication_binding',
            'negative' => 'B19ReplayIdentityProjectionTest::test_expected_and_actual_contexts_are_preserved_separately',
            'basis' => 'E022 impact revalidation: identity verification and evidence admission now explicitly independent from historical comparison PASS; current real MariaDB and targeted producer/persistence/export guards revalidate own source values and both state namespaces. Supplemental binding absence leaves legacy evidence incomplete, never repairs or changes historical comparison. Original scalar/semantic meaning and retained mutations unchanged. Prior basis: E-MD-B19-A001-021: actual_context.actual_publication_context.factor_set_hash; source: independent accepted fixture semantic factor-set hash; not event_factor_hash. Real producer -> persisted metric/audit rows -> exact JSON/CSV export, paired with independent field-specific projection expectations and negative guards. Mutations: result_context_swap; assertion-red, exact byte restoration. Applicable identities are non-NULL; AS_KNOWN publication-only members explicitly inapplicable; unsupported PUBLICATION_EXACT RAW unavailable/BLOCKED. No live repair, no actual-to-expected oracle copying, no successor golden candidate admission.',
        ],
        'MD-S075-R0247' => [
            'positive' => 'B19ReplayIdentityMariaDbPipelineTest::test_all_identity_paths_follow_independent_inputs_and_persisted_publication_binding',
            'negative' => 'B19ReplayIdentityProjectionTest::test_expected_and_actual_contexts_are_preserved_separately',
            'basis' => 'E022 impact revalidation: identity verification and evidence admission now explicitly independent from historical comparison PASS; current real MariaDB and targeted producer/persistence/export guards revalidate own source values and both state namespaces. Supplemental binding absence leaves legacy evidence incomplete, never repairs or changes historical comparison. Original scalar/semantic meaning and retained mutations unchanged. Prior basis: E-MD-B19-A001-021: actual_context.actual_publication_context.price_product_code; source: independent accepted fixture product literal and frozen selected publication version; live RAW default cannot substitute. Real producer -> persisted metric/audit rows -> exact JSON/CSV export, paired with independent field-specific projection expectations and negative guards. Mutations: result_context_swap; raw_capability_bypass; assertion-red, exact byte restoration. Applicable identities are non-NULL; AS_KNOWN publication-only members explicitly inapplicable; unsupported PUBLICATION_EXACT RAW unavailable/BLOCKED. No live repair, no actual-to-expected oracle copying, no successor golden candidate admission.',
        ],
        'MD-S075-R0248' => [
            'positive' => 'B19ReplayIdentityMariaDbPipelineTest::test_all_identity_paths_follow_independent_inputs_and_persisted_publication_binding',
            'negative' => 'B19ReplayIdentityProjectionTest::test_expected_and_actual_contexts_are_preserved_separately',
            'basis' => 'E022 impact revalidation: identity verification and evidence admission now explicitly independent from historical comparison PASS; current real MariaDB and targeted producer/persistence/export guards revalidate own source values and both state namespaces. Supplemental binding absence leaves legacy evidence incomplete, never repairs or changes historical comparison. Original scalar/semantic meaning and retained mutations unchanged. Prior basis: E-MD-B19-A001-021: actual_context.actual_publication_manifest_identity.formula_version; source: independently versioned formula literal; not formula_registry_hash. Real producer -> persisted metric/audit rows -> exact JSON/CSV export, paired with independent field-specific projection expectations and negative guards. Mutations: formula_alias; expected_schema_bypass; expected_applicability_bypass; assertion-red, exact byte restoration. Applicable identities are non-NULL; AS_KNOWN publication-only members explicitly inapplicable; unsupported PUBLICATION_EXACT RAW unavailable/BLOCKED. No live repair, no actual-to-expected oracle copying, no successor golden candidate admission.',
        ],
        'MD-S075-R0249' => [
            'positive' => 'B19ReplayIdentityMariaDbPipelineTest::test_all_identity_paths_follow_independent_inputs_and_persisted_publication_binding',
            'negative' => 'B19ReplayIdentityProjectionTest::test_expected_and_actual_contexts_are_preserved_separately',
            'basis' => 'E022 impact revalidation: identity verification and evidence admission now explicitly independent from historical comparison PASS; current real MariaDB and targeted producer/persistence/export guards revalidate own source values and both state namespaces. Supplemental binding absence leaves legacy evidence incomplete, never repairs or changes historical comparison. Original scalar/semantic meaning and retained mutations unchanged. Prior basis: E-MD-B19-A001-021: bound_inputs.read_model_version; source: independent fixture read-model version bound to immutable publication. Real producer -> persisted metric/audit rows -> exact JSON/CSV export, paired with independent field-specific projection expectations and negative guards. Mutations: read_model_substitution; assertion-red, exact byte restoration. Applicable identities are non-NULL; AS_KNOWN publication-only members explicitly inapplicable; unsupported PUBLICATION_EXACT RAW unavailable/BLOCKED. No live repair, no actual-to-expected oracle copying, no successor golden candidate admission.',
        ],
        'MD-S075-R0259' => [
            'positive' => 'B19ReplayIdentityMariaDbPipelineTest::test_all_identity_paths_follow_independent_inputs_and_persisted_publication_binding',
            'negative' => 'B19ReplayIdentityProjectionTest::test_exact_persisted_names_and_versioned_paths_are_not_aliases',
            'basis' => 'E022 impact revalidation: identity verification and evidence admission now explicitly independent from historical comparison PASS; current real MariaDB and targeted producer/persistence/export guards revalidate own source values and both state namespaces. Supplemental binding absence leaves legacy evidence incomplete, never repairs or changes historical comparison. Original scalar/semantic meaning and retained mutations unchanged. Prior basis: E-MD-B19-A001-021: sections 8-10 explicit persisted names and versioned namespaces; source: independent contract literals; distinct temporal, factor and formula identities; no top-level aliases. Real producer -> persisted metric/audit rows -> exact JSON/CSV export, paired with independent field-specific projection expectations and negative guards. Mutations: cutoff_name; result_context_swap; temporal_alias; formula_alias; assertion-red, exact byte restoration. Applicable identities are non-NULL; AS_KNOWN publication-only members explicitly inapplicable; unsupported PUBLICATION_EXACT RAW unavailable/BLOCKED. No live repair, no actual-to-expected oracle copying, no successor golden candidate admission.',
        ],
        'MD-S075-R0267' => [
            'positive' => 'B19ReplayIdentityMariaDbPipelineTest::test_all_identity_paths_follow_independent_inputs_and_persisted_publication_binding',
            'negative' => 'B19ReplayIdentityProjectionTest::test_as_known_export_does_not_substitute_publication_mode',
            'basis' => 'E022 impact revalidation: identity verification and evidence admission now explicitly independent from historical comparison PASS; current real MariaDB and targeted producer/persistence/export guards revalidate own source values and both state namespaces. Supplemental binding absence leaves legacy evidence incomplete, never repairs or changes historical comparison. Original scalar/semantic meaning and retained mutations unchanged. Prior basis: E-MD-B19-A001-021: replay_expected_state.json.replay_mode (versioned expected_context where required); source: independent fixture declared mode / actual executed mode. Real producer -> persisted metric/audit rows -> exact JSON/CSV export, paired with independent field-specific projection expectations and negative guards. Mutations: expected_mode_substitution; actual_mode_substitution; missing_expected_mode_default; assertion-red, exact byte restoration. Applicable identities are non-NULL; AS_KNOWN publication-only members explicitly inapplicable; unsupported PUBLICATION_EXACT RAW unavailable/BLOCKED. No live repair, no actual-to-expected oracle copying, no successor golden candidate admission.',
        ],
        'MD-S075-R0268' => [
            'positive' => 'B19ReplayIdentityMariaDbPipelineTest::test_all_identity_paths_follow_independent_inputs_and_persisted_publication_binding',
            'negative' => 'B19ReplayIdentityProjectionTest::test_expected_and_actual_contexts_are_preserved_separately',
            'basis' => 'E022 impact revalidation: identity verification and evidence admission now explicitly independent from historical comparison PASS; current real MariaDB and targeted producer/persistence/export guards revalidate own source values and both state namespaces. Supplemental binding absence leaves legacy evidence incomplete, never repairs or changes historical comparison. Original scalar/semantic meaning and retained mutations unchanged. Prior basis: E-MD-B19-A001-021: replay_expected_state.json.source_observation_manifest_hash (versioned expected_context where required); source: immutable selected publication lineage / AS_KNOWN observation snapshot. Real producer -> persisted metric/audit rows -> exact JSON/CSV export, paired with independent field-specific projection expectations and negative guards. Mutations: expected_context_swap; actual_context_swap; assertion-red, exact byte restoration. Applicable identities are non-NULL; AS_KNOWN publication-only members explicitly inapplicable; unsupported PUBLICATION_EXACT RAW unavailable/BLOCKED. No live repair, no actual-to-expected oracle copying, no successor golden candidate admission.',
        ],
        'MD-S075-R0269' => [
            'positive' => 'B19ReplayIdentityMariaDbPipelineTest::test_all_identity_paths_follow_independent_inputs_and_persisted_publication_binding',
            'negative' => 'B19ReplayIdentityProjectionTest::test_expected_and_actual_contexts_are_preserved_separately',
            'basis' => 'E022 impact revalidation: identity verification and evidence admission now explicitly independent from historical comparison PASS; current real MariaDB and targeted producer/persistence/export guards revalidate own source values and both state namespaces. Supplemental binding absence leaves legacy evidence incomplete, never repairs or changes historical comparison. Original scalar/semantic meaning and retained mutations unchanged. Prior basis: E-MD-B19-A001-021: replay_expected_state.json.config_snapshot_hash (versioned expected_context where required); source: SHA256 of independently retained resolved_config_json; selected ID/hash pair. Real producer -> persisted metric/audit rows -> exact JSON/CSV export, paired with independent field-specific projection expectations and negative guards. Mutations: expected_context_swap; actual_context_swap; assertion-red, exact byte restoration. Applicable identities are non-NULL; AS_KNOWN publication-only members explicitly inapplicable; unsupported PUBLICATION_EXACT RAW unavailable/BLOCKED. No live repair, no actual-to-expected oracle copying, no successor golden candidate admission.',
        ],
        'MD-S075-R0270' => [
            'positive' => 'B19ReplayIdentityMariaDbPipelineTest::test_all_identity_paths_follow_independent_inputs_and_persisted_publication_binding',
            'negative' => 'B19ReplayIdentityProjectionTest::test_expected_and_actual_contexts_are_preserved_separately',
            'basis' => 'E022 impact revalidation: identity verification and evidence admission now explicitly independent from historical comparison PASS; current real MariaDB and targeted producer/persistence/export guards revalidate own source values and both state namespaces. Supplemental binding absence leaves legacy evidence incomplete, never repairs or changes historical comparison. Original scalar/semantic meaning and retained mutations unchanged. Prior basis: E-MD-B19-A001-021: replay_expected_state.json.temporal_revision_set_hash (versioned expected_context where required); source: manual hash over selected publication trade_date and authoritative identity/calendar/status revision hashes. Real producer -> persisted metric/audit rows -> exact JSON/CSV export, paired with independent field-specific projection expectations and negative guards. Mutations: temporal_alias; expected_context_swap; actual_context_swap; assertion-red, exact byte restoration. Applicable identities are non-NULL; AS_KNOWN publication-only members explicitly inapplicable; unsupported PUBLICATION_EXACT RAW unavailable/BLOCKED. No live repair, no actual-to-expected oracle copying, no successor golden candidate admission.',
        ],
        'MD-S075-R0271' => [
            'positive' => 'B19ReplayIdentityMariaDbPipelineTest::test_all_identity_paths_follow_independent_inputs_and_persisted_publication_binding',
            'negative' => 'B19ReplayIdentityProjectionTest::test_expected_and_actual_contexts_are_preserved_separately',
            'basis' => 'E022 impact revalidation: identity verification and evidence admission now explicitly independent from historical comparison PASS; current real MariaDB and targeted producer/persistence/export guards revalidate own source values and both state namespaces. Supplemental binding absence leaves legacy evidence incomplete, never repairs or changes historical comparison. Original scalar/semantic meaning and retained mutations unchanged. Prior basis: E-MD-B19-A001-021: replay_expected_state.json.factor_set_hash (versioned expected_context where required); source: independent accepted fixture semantic factor-set hash; not event_factor_hash. Real producer -> persisted metric/audit rows -> exact JSON/CSV export, paired with independent field-specific projection expectations and negative guards. Mutations: expected_context_swap; actual_context_swap; assertion-red, exact byte restoration. Applicable identities are non-NULL; AS_KNOWN publication-only members explicitly inapplicable; unsupported PUBLICATION_EXACT RAW unavailable/BLOCKED. No live repair, no actual-to-expected oracle copying, no successor golden candidate admission.',
        ],
        'MD-S075-R0272' => [
            'positive' => 'B19ReplayIdentityMariaDbPipelineTest::test_all_identity_paths_follow_independent_inputs_and_persisted_publication_binding',
            'negative' => 'B19ReplayIdentityProjectionTest::test_expected_and_actual_contexts_are_preserved_separately',
            'basis' => 'E022 impact revalidation: identity verification and evidence admission now explicitly independent from historical comparison PASS; current real MariaDB and targeted producer/persistence/export guards revalidate own source values and both state namespaces. Supplemental binding absence leaves legacy evidence incomplete, never repairs or changes historical comparison. Original scalar/semantic meaning and retained mutations unchanged. Prior basis: E-MD-B19-A001-021: replay_expected_state.json.price_product_code (versioned expected_context where required); source: independent accepted fixture product literal and frozen selected publication version; live RAW default cannot substitute. Real producer -> persisted metric/audit rows -> exact JSON/CSV export, paired with independent field-specific projection expectations and negative guards. Mutations: expected_context_swap; actual_context_swap; raw_capability_bypass; assertion-red, exact byte restoration. Applicable identities are non-NULL; AS_KNOWN publication-only members explicitly inapplicable; unsupported PUBLICATION_EXACT RAW unavailable/BLOCKED. No live repair, no actual-to-expected oracle copying, no successor golden candidate admission.',
        ],
        'MD-S075-R0273' => [
            'positive' => 'B19ReplayIdentityMariaDbPipelineTest::test_all_identity_paths_follow_independent_inputs_and_persisted_publication_binding',
            'negative' => 'B19ReplayIdentityProjectionTest::test_expected_and_actual_contexts_are_preserved_separately',
            'basis' => 'E022 impact revalidation: identity verification and evidence admission now explicitly independent from historical comparison PASS; current real MariaDB and targeted producer/persistence/export guards revalidate own source values and both state namespaces. Supplemental binding absence leaves legacy evidence incomplete, never repairs or changes historical comparison. Original scalar/semantic meaning and retained mutations unchanged. Prior basis: E-MD-B19-A001-021: replay_expected_state.json.formula_version (versioned expected_context where required); source: independently versioned formula literal; not formula_registry_hash. Real producer -> persisted metric/audit rows -> exact JSON/CSV export, paired with independent field-specific projection expectations and negative guards. Mutations: formula_alias; expected_context_swap; actual_context_swap; assertion-red, exact byte restoration. Applicable identities are non-NULL; AS_KNOWN publication-only members explicitly inapplicable; unsupported PUBLICATION_EXACT RAW unavailable/BLOCKED. No live repair, no actual-to-expected oracle copying, no successor golden candidate admission.',
        ],
        'MD-S075-R0274' => [
            'positive' => 'B19ReplayIdentityMariaDbPipelineTest::test_all_identity_paths_follow_independent_inputs_and_persisted_publication_binding',
            'negative' => 'B19ReplayIdentityProjectionTest::test_expected_and_actual_contexts_are_preserved_separately',
            'basis' => 'E022 impact revalidation: identity verification and evidence admission now explicitly independent from historical comparison PASS; current real MariaDB and targeted producer/persistence/export guards revalidate own source values and both state namespaces. Supplemental binding absence leaves legacy evidence incomplete, never repairs or changes historical comparison. Original scalar/semantic meaning and retained mutations unchanged. Prior basis: E-MD-B19-A001-021: replay_expected_state.json.read_model_version (versioned expected_context where required); source: independent fixture read-model version bound to immutable publication. Real producer -> persisted metric/audit rows -> exact JSON/CSV export, paired with independent field-specific projection expectations and negative guards. Mutations: expected_context_swap; actual_context_swap; assertion-red, exact byte restoration. Applicable identities are non-NULL; AS_KNOWN publication-only members explicitly inapplicable; unsupported PUBLICATION_EXACT RAW unavailable/BLOCKED. No live repair, no actual-to-expected oracle copying, no successor golden candidate admission.',
        ],
        'MD-S075-R0284' => [
            'positive' => 'B19ReplayIdentityMariaDbPipelineTest::test_all_identity_paths_follow_independent_inputs_and_persisted_publication_binding',
            'negative' => 'B19ReplayIdentityProjectionTest::test_as_known_export_does_not_substitute_publication_mode',
            'basis' => 'E022 impact revalidation: identity verification and evidence admission now explicitly independent from historical comparison PASS; current real MariaDB and targeted producer/persistence/export guards revalidate own source values and both state namespaces. Supplemental binding absence leaves legacy evidence incomplete, never repairs or changes historical comparison. Original scalar/semantic meaning and retained mutations unchanged. Prior basis: E-MD-B19-A001-021: replay_actual_state.json.replay_mode (versioned actual_context where required); source: independent fixture declared mode / actual executed mode. Real producer -> persisted metric/audit rows -> exact JSON/CSV export, paired with independent field-specific projection expectations and negative guards. Mutations: expected_mode_substitution; actual_mode_substitution; missing_expected_mode_default; assertion-red, exact byte restoration. Applicable identities are non-NULL; AS_KNOWN publication-only members explicitly inapplicable; unsupported PUBLICATION_EXACT RAW unavailable/BLOCKED. No live repair, no actual-to-expected oracle copying, no successor golden candidate admission.',
        ],
        'MD-S075-R0285' => [
            'positive' => 'B19ReplayIdentityMariaDbPipelineTest::test_all_identity_paths_follow_independent_inputs_and_persisted_publication_binding',
            'negative' => 'B19ReplayIdentityProjectionTest::test_expected_and_actual_contexts_are_preserved_separately',
            'basis' => 'E022 impact revalidation: identity verification and evidence admission now explicitly independent from historical comparison PASS; current real MariaDB and targeted producer/persistence/export guards revalidate own source values and both state namespaces. Supplemental binding absence leaves legacy evidence incomplete, never repairs or changes historical comparison. Original scalar/semantic meaning and retained mutations unchanged. Prior basis: E-MD-B19-A001-021: replay_actual_state.json.source_observation_manifest_hash (versioned actual_context where required); source: immutable selected publication lineage / AS_KNOWN observation snapshot. Real producer -> persisted metric/audit rows -> exact JSON/CSV export, paired with independent field-specific projection expectations and negative guards. Mutations: expected_context_swap; actual_context_swap; assertion-red, exact byte restoration. Applicable identities are non-NULL; AS_KNOWN publication-only members explicitly inapplicable; unsupported PUBLICATION_EXACT RAW unavailable/BLOCKED. No live repair, no actual-to-expected oracle copying, no successor golden candidate admission.',
        ],
        'MD-S075-R0286' => [
            'positive' => 'B19ReplayIdentityMariaDbPipelineTest::test_all_identity_paths_follow_independent_inputs_and_persisted_publication_binding',
            'negative' => 'B19ReplayIdentityProjectionTest::test_expected_and_actual_contexts_are_preserved_separately',
            'basis' => 'E022 impact revalidation: identity verification and evidence admission now explicitly independent from historical comparison PASS; current real MariaDB and targeted producer/persistence/export guards revalidate own source values and both state namespaces. Supplemental binding absence leaves legacy evidence incomplete, never repairs or changes historical comparison. Original scalar/semantic meaning and retained mutations unchanged. Prior basis: E-MD-B19-A001-021: replay_actual_state.json.config_snapshot_hash (versioned actual_context where required); source: SHA256 of independently retained resolved_config_json; selected ID/hash pair. Real producer -> persisted metric/audit rows -> exact JSON/CSV export, paired with independent field-specific projection expectations and negative guards. Mutations: expected_context_swap; actual_context_swap; assertion-red, exact byte restoration. Applicable identities are non-NULL; AS_KNOWN publication-only members explicitly inapplicable; unsupported PUBLICATION_EXACT RAW unavailable/BLOCKED. No live repair, no actual-to-expected oracle copying, no successor golden candidate admission.',
        ],
        'MD-S075-R0287' => [
            'positive' => 'B19ReplayIdentityMariaDbPipelineTest::test_all_identity_paths_follow_independent_inputs_and_persisted_publication_binding',
            'negative' => 'B19ReplayIdentityProjectionTest::test_expected_and_actual_contexts_are_preserved_separately',
            'basis' => 'E022 impact revalidation: identity verification and evidence admission now explicitly independent from historical comparison PASS; current real MariaDB and targeted producer/persistence/export guards revalidate own source values and both state namespaces. Supplemental binding absence leaves legacy evidence incomplete, never repairs or changes historical comparison. Original scalar/semantic meaning and retained mutations unchanged. Prior basis: E-MD-B19-A001-021: replay_actual_state.json.temporal_revision_set_hash (versioned actual_context where required); source: manual hash over selected publication trade_date and authoritative identity/calendar/status revision hashes. Real producer -> persisted metric/audit rows -> exact JSON/CSV export, paired with independent field-specific projection expectations and negative guards. Mutations: temporal_alias; expected_context_swap; actual_context_swap; assertion-red, exact byte restoration. Applicable identities are non-NULL; AS_KNOWN publication-only members explicitly inapplicable; unsupported PUBLICATION_EXACT RAW unavailable/BLOCKED. No live repair, no actual-to-expected oracle copying, no successor golden candidate admission.',
        ],
        'MD-S075-R0288' => [
            'positive' => 'B19ReplayIdentityMariaDbPipelineTest::test_all_identity_paths_follow_independent_inputs_and_persisted_publication_binding',
            'negative' => 'B19ReplayIdentityProjectionTest::test_expected_and_actual_contexts_are_preserved_separately',
            'basis' => 'E022 impact revalidation: identity verification and evidence admission now explicitly independent from historical comparison PASS; current real MariaDB and targeted producer/persistence/export guards revalidate own source values and both state namespaces. Supplemental binding absence leaves legacy evidence incomplete, never repairs or changes historical comparison. Original scalar/semantic meaning and retained mutations unchanged. Prior basis: E-MD-B19-A001-021: replay_actual_state.json.factor_set_hash (versioned actual_context where required); source: independent accepted fixture semantic factor-set hash; not event_factor_hash. Real producer -> persisted metric/audit rows -> exact JSON/CSV export, paired with independent field-specific projection expectations and negative guards. Mutations: expected_context_swap; actual_context_swap; assertion-red, exact byte restoration. Applicable identities are non-NULL; AS_KNOWN publication-only members explicitly inapplicable; unsupported PUBLICATION_EXACT RAW unavailable/BLOCKED. No live repair, no actual-to-expected oracle copying, no successor golden candidate admission.',
        ],
        'MD-S075-R0289' => [
            'positive' => 'B19ReplayIdentityMariaDbPipelineTest::test_all_identity_paths_follow_independent_inputs_and_persisted_publication_binding',
            'negative' => 'B19ReplayIdentityProjectionTest::test_expected_and_actual_contexts_are_preserved_separately',
            'basis' => 'E022 impact revalidation: identity verification and evidence admission now explicitly independent from historical comparison PASS; current real MariaDB and targeted producer/persistence/export guards revalidate own source values and both state namespaces. Supplemental binding absence leaves legacy evidence incomplete, never repairs or changes historical comparison. Original scalar/semantic meaning and retained mutations unchanged. Prior basis: E-MD-B19-A001-021: replay_actual_state.json.price_product_code (versioned actual_context where required); source: independent accepted fixture product literal and frozen selected publication version; live RAW default cannot substitute. Real producer -> persisted metric/audit rows -> exact JSON/CSV export, paired with independent field-specific projection expectations and negative guards. Mutations: expected_context_swap; actual_context_swap; raw_capability_bypass; assertion-red, exact byte restoration. Applicable identities are non-NULL; AS_KNOWN publication-only members explicitly inapplicable; unsupported PUBLICATION_EXACT RAW unavailable/BLOCKED. No live repair, no actual-to-expected oracle copying, no successor golden candidate admission.',
        ],
        'MD-S075-R0290' => [
            'positive' => 'B19ReplayIdentityMariaDbPipelineTest::test_all_identity_paths_follow_independent_inputs_and_persisted_publication_binding',
            'negative' => 'B19ReplayIdentityProjectionTest::test_expected_and_actual_contexts_are_preserved_separately',
            'basis' => 'E022 impact revalidation: identity verification and evidence admission now explicitly independent from historical comparison PASS; current real MariaDB and targeted producer/persistence/export guards revalidate own source values and both state namespaces. Supplemental binding absence leaves legacy evidence incomplete, never repairs or changes historical comparison. Original scalar/semantic meaning and retained mutations unchanged. Prior basis: E-MD-B19-A001-021: replay_actual_state.json.formula_version (versioned actual_context where required); source: independently versioned formula literal; not formula_registry_hash. Real producer -> persisted metric/audit rows -> exact JSON/CSV export, paired with independent field-specific projection expectations and negative guards. Mutations: formula_alias; expected_context_swap; actual_context_swap; assertion-red, exact byte restoration. Applicable identities are non-NULL; AS_KNOWN publication-only members explicitly inapplicable; unsupported PUBLICATION_EXACT RAW unavailable/BLOCKED. No live repair, no actual-to-expected oracle copying, no successor golden candidate admission.',
        ],
        'MD-S075-R0291' => [
            'positive' => 'B19ReplayIdentityMariaDbPipelineTest::test_all_identity_paths_follow_independent_inputs_and_persisted_publication_binding',
            'negative' => 'B19ReplayIdentityProjectionTest::test_expected_and_actual_contexts_are_preserved_separately',
            'basis' => 'E022 impact revalidation: identity verification and evidence admission now explicitly independent from historical comparison PASS; current real MariaDB and targeted producer/persistence/export guards revalidate own source values and both state namespaces. Supplemental binding absence leaves legacy evidence incomplete, never repairs or changes historical comparison. Original scalar/semantic meaning and retained mutations unchanged. Prior basis: E-MD-B19-A001-021: replay_actual_state.json.read_model_version (versioned actual_context where required); source: independent fixture read-model version bound to immutable publication. Real producer -> persisted metric/audit rows -> exact JSON/CSV export, paired with independent field-specific projection expectations and negative guards. Mutations: expected_context_swap; actual_context_swap; assertion-red, exact byte restoration. Applicable identities are non-NULL; AS_KNOWN publication-only members explicitly inapplicable; unsupported PUBLICATION_EXACT RAW unavailable/BLOCKED. No live repair, no actual-to-expected oracle copying, no successor golden candidate admission.',
        ],

        // E022: 50 independently mapped bases; shared real worlds plus scoped negative guards.
        'MD-S063-R0102' => [
            'positive' => 'B19ReplayAuditPackProofTest::test_each_replay_pack_requirement_keeps_its_independent_expected_and_actual_meaning',
            'negative' => 'B19ArtifactBindingCompanionProofTest::test_failed_comparison_without_diagnostics_is_not_complete',
            'basis' => 'E-MD-B19-A001-022: independent counterfactual status/date/version and three hashes produce named actionable mismatch fields/reasons. Positive/negative assertions are individually mapped in per_predicate_proof_map.json and executed shared MariaDB source -> producer -> persisted records -> consumer plus scoped SQLite negatives. Sensitivity: pack_expected_actual_swap; pack_actual_expected_swap; failed_diagnostics_admission_bypass. Retained E015-E021 proof reused only where unchanged; no aggregate-guard-only admission, no new golden candidate, no formal SATISFIED.',
        ],
        'MD-S063-R0104' => [
            'positive' => 'B19ReplayAuditPackProofTest::test_each_replay_pack_requirement_keeps_its_independent_expected_and_actual_meaning',
            'negative' => 'B19ReplayAuditPackProofTest::test_each_replay_pack_requirement_keeps_its_independent_expected_and_actual_meaning',
            'basis' => 'E-MD-B19-A001-022: exact persisted replay/case and independently versioned fixture identity. Positive/negative assertions are individually mapped in per_predicate_proof_map.json and executed shared MariaDB source -> producer -> persisted records -> consumer plus scoped SQLite negatives. Sensitivity: pack_expected_actual_swap; pack_actual_expected_swap. Retained E015-E021 proof reused only where unchanged; no aggregate-guard-only admission, no new golden candidate, no formal SATISFIED.',
        ],
        'MD-S063-R0105' => [
            'positive' => 'B19ReplayAuditPackProofTest::test_each_replay_pack_requirement_keeps_its_independent_expected_and_actual_meaning',
            'negative' => 'B19ReplayAuditPackProofTest::test_each_replay_pack_requirement_keeps_its_independent_expected_and_actual_meaning',
            'basis' => 'E-MD-B19-A001-022: independent HELD/prior-date/hash fixture versus actual SUCCESS/current-date/hash. Positive/negative assertions are individually mapped in per_predicate_proof_map.json and executed shared MariaDB source -> producer -> persisted records -> consumer plus scoped SQLite negatives. Sensitivity: pack_expected_actual_swap; pack_actual_expected_swap. Retained E015-E021 proof reused only where unchanged; no aggregate-guard-only admission, no new golden candidate, no formal SATISFIED.',
        ],
        'MD-S063-R0106' => [
            'positive' => 'B19ReplayAuditPackProofTest::test_each_replay_pack_requirement_keeps_its_independent_expected_and_actual_meaning',
            'negative' => 'B19ReplayAuditPackProofTest::test_each_replay_pack_requirement_keeps_its_independent_expected_and_actual_meaning',
            'basis' => 'E-MD-B19-A001-022: real SUCCESS/selected date and immutable v5 hash; never use expected as actual. Positive/negative assertions are individually mapped in per_predicate_proof_map.json and executed shared MariaDB source -> producer -> persisted records -> consumer plus scoped SQLite negatives. Sensitivity: pack_expected_actual_swap; pack_actual_expected_swap. Retained E015-E021 proof reused only where unchanged; no aggregate-guard-only admission, no new golden candidate, no formal SATISFIED.',
        ],
        'MD-S063-R0107' => [
            'positive' => 'B19ReplayAuditPackProofTest::test_each_replay_pack_requirement_keeps_its_independent_expected_and_actual_meaning',
            'negative' => 'B19ArtifactBindingCompanionProofTest::test_failed_comparison_without_diagnostics_is_not_complete',
            'basis' => 'E-MD-B19-A001-022: MISMATCH -> FAIL while actual run status remains SUCCESS; MATCH and EXPECTED_DEGRADE -> PASS. Positive/negative assertions are individually mapped in per_predicate_proof_map.json and executed shared MariaDB source -> producer -> persisted records -> consumer plus scoped SQLite negatives. Sensitivity: pack_expected_actual_swap; pack_actual_expected_swap; failed_diagnostics_admission_bypass. Retained E015-E021 proof reused only where unchanged; no aggregate-guard-only admission, no new golden candidate, no formal SATISFIED.',
        ],
        'MD-S063-R0108' => [
            'positive' => 'B19ReplayAuditPackProofTest::test_each_replay_pack_requirement_keeps_its_independent_expected_and_actual_meaning',
            'negative' => 'B19ArtifactBindingCompanionProofTest::test_failed_comparison_without_diagnostics_is_not_complete',
            'basis' => 'E-MD-B19-A001-022: classification reasons and changed fields retain distinct cause. Positive/negative assertions are individually mapped in per_predicate_proof_map.json and executed shared MariaDB source -> producer -> persisted records -> consumer plus scoped SQLite negatives. Sensitivity: pack_expected_actual_swap; pack_actual_expected_swap; failed_diagnostics_admission_bypass. Retained E015-E021 proof reused only where unchanged; no aggregate-guard-only admission, no new golden candidate, no formal SATISFIED.',
        ],
        'MD-S063-R0109' => [
            'positive' => 'B19ReplayAuditPackProofTest::test_each_replay_pack_requirement_keeps_its_independent_expected_and_actual_meaning',
            'negative' => 'B19ArtifactBindingCompanionProofTest::test_failed_comparison_without_diagnostics_is_not_complete',
            'basis' => 'E-MD-B19-A001-022: mismatch summary names count and reasons; FAIL without diagnostics incomplete. Positive/negative assertions are individually mapped in per_predicate_proof_map.json and executed shared MariaDB source -> producer -> persisted records -> consumer plus scoped SQLite negatives. Sensitivity: pack_expected_actual_swap; pack_actual_expected_swap; failed_diagnostics_admission_bypass. Retained E015-E021 proof reused only where unchanged; no aggregate-guard-only admission, no new golden candidate, no formal SATISFIED.',
        ],
        'MD-S063-R0110' => [
            'positive' => 'B19ReplayAuditPackProofTest::test_each_replay_pack_requirement_keeps_its_independent_expected_and_actual_meaning',
            'negative' => 'B19ReplayAuditPackProofTest::test_missing_expected_count_is_not_reconstructed_from_actual_and_invalid_count_is_refused',
            'basis' => 'E-MD-B19-A001-022: all seven coverage values on both own contexts; counterfactual 4/2/2/0.5/0.9 versus actual1/1/0/1/0.98. Positive/negative assertions are individually mapped in per_predicate_proof_map.json and executed shared MariaDB source -> producer -> persisted records -> consumer plus scoped SQLite negatives. Sensitivity: pack_expected_actual_swap; pack_actual_expected_swap; persisted_coverage_count_drop; coverage_actual_expected_swap; legacy_coverage_normalization_bypass. Retained E015-E021 proof reused only where unchanged; no aggregate-guard-only admission, no new golden candidate, no formal SATISFIED.',
        ],
        'MD-S063-R0111' => [
            'positive' => 'B19ReplayAuditPackProofTest::test_each_replay_pack_requirement_keeps_its_independent_expected_and_actual_meaning',
            'negative' => 'B19ReplayAuditPackProofTest::test_missing_expected_count_is_not_reconstructed_from_actual_and_invalid_count_is_refused',
            'basis' => 'E-MD-B19-A001-022: legacy BLOCKED -> NOT_EVALUABLE with explicit raw metadata; current writer canonicalizes new rows. Positive/negative assertions are individually mapped in per_predicate_proof_map.json and executed shared MariaDB source -> producer -> persisted records -> consumer plus scoped SQLite negatives. Sensitivity: pack_expected_actual_swap; pack_actual_expected_swap; persisted_coverage_count_drop; coverage_actual_expected_swap; legacy_coverage_normalization_bypass. Retained E015-E021 proof reused only where unchanged; no aggregate-guard-only admission, no new golden candidate, no formal SATISFIED.',
        ],
        'MD-S063-R0112' => [
            'positive' => 'B19ReplayAuditPackProofTest::test_each_replay_pack_requirement_keeps_its_independent_expected_and_actual_meaning',
            'negative' => 'B19ReplayAuditPackProofTest::test_each_replay_pack_requirement_keeps_its_independent_expected_and_actual_meaning',
            'basis' => 'E-MD-B19-A001-022: config snapshot ID/hash sourced from retained snapshot bytes, not label/reference. Positive/negative assertions are individually mapped in per_predicate_proof_map.json and executed shared MariaDB source -> producer -> persisted records -> consumer plus scoped SQLite negatives. Sensitivity: pack_expected_actual_swap; pack_actual_expected_swap. Retained E015-E021 proof reused only where unchanged; no aggregate-guard-only admission, no new golden candidate, no formal SATISFIED.',
        ],
        'MD-S063-R0113' => [
            'positive' => 'B19ReplayAuditPackProofTest::test_each_replay_pack_requirement_keeps_its_independent_expected_and_actual_meaning',
            'negative' => 'B19ReplayAuditPackProofTest::test_each_replay_pack_requirement_keeps_its_independent_expected_and_actual_meaning',
            'basis' => 'E-MD-B19-A001-022: single bars/indicators/eligibility drift and multi_artifact drift remain distinct scopes. Positive/negative assertions are individually mapped in per_predicate_proof_map.json and executed shared MariaDB source -> producer -> persisted records -> consumer plus scoped SQLite negatives. Sensitivity: pack_expected_actual_swap; pack_actual_expected_swap. Retained E015-E021 proof reused only where unchanged; no aggregate-guard-only admission, no new golden candidate, no formal SATISFIED.',
        ],
        'MD-S063-R0114' => [
            'positive' => 'B19ReplayAuditPackProofTest::test_each_replay_pack_requirement_keeps_its_independent_expected_and_actual_meaning',
            'negative' => 'B19ReplayAuditPackProofTest::test_each_replay_pack_requirement_keeps_its_independent_expected_and_actual_meaning',
            'basis' => 'E-MD-B19-A001-022: real requested/approved unchanged correction -> NOT_RESEALED_UNCHANGED, no switch/new publication; independent version/seal mismatch. Positive/negative assertions are individually mapped in per_predicate_proof_map.json and executed shared MariaDB source -> producer -> persisted records -> consumer plus scoped SQLite negatives. Sensitivity: pack_expected_actual_swap; pack_actual_expected_swap. Retained E015-E021 proof reused only where unchanged; no aggregate-guard-only admission, no new golden candidate, no formal SATISFIED.',
        ],
        'MD-S063-R0115' => [
            'positive' => 'B19ReplayAuditPackProofTest::test_each_replay_pack_requirement_keeps_its_independent_expected_and_actual_meaning',
            'negative' => 'B19ReplayAuditPackProofTest::test_each_replay_pack_requirement_keeps_its_independent_expected_and_actual_meaning',
            'basis' => 'E-MD-B19-A001-022: case/fixture version identify actual executed case. Positive/negative assertions are individually mapped in per_predicate_proof_map.json and executed shared MariaDB source -> producer -> persisted records -> consumer plus scoped SQLite negatives. Sensitivity: pack_expected_actual_swap; pack_actual_expected_swap. Retained E015-E021 proof reused only where unchanged; no aggregate-guard-only admission, no new golden candidate, no formal SATISFIED.',
        ],
        'MD-S063-R0116' => [
            'positive' => 'B19ReplayAuditPackProofTest::test_each_replay_pack_requirement_keeps_its_independent_expected_and_actual_meaning',
            'negative' => 'B19ReplayAuditPackProofTest::test_each_replay_pack_requirement_keeps_its_independent_expected_and_actual_meaning',
            'basis' => 'E-MD-B19-A001-022: fixture independently authored before replay; expected context not regenerated from output. Positive/negative assertions are individually mapped in per_predicate_proof_map.json and executed shared MariaDB source -> producer -> persisted records -> consumer plus scoped SQLite negatives. Sensitivity: pack_expected_actual_swap; pack_actual_expected_swap. Retained E015-E021 proof reused only where unchanged; no aggregate-guard-only admission, no new golden candidate, no formal SATISFIED.',
        ],
        'MD-S063-R0117' => [
            'positive' => 'B19ReplayAuditPackProofTest::test_each_replay_pack_requirement_keeps_its_independent_expected_and_actual_meaning',
            'negative' => 'B19ReplayAuditPackProofTest::test_each_replay_pack_requirement_keeps_its_independent_expected_and_actual_meaning',
            'basis' => 'E-MD-B19-A001-022: actual source state persisted and exported without expected overwrite. Positive/negative assertions are individually mapped in per_predicate_proof_map.json and executed shared MariaDB source -> producer -> persisted records -> consumer plus scoped SQLite negatives. Sensitivity: pack_expected_actual_swap; pack_actual_expected_swap. Retained E015-E021 proof reused only where unchanged; no aggregate-guard-only admission, no new golden candidate, no formal SATISFIED.',
        ],
        'MD-S063-R0118' => [
            'positive' => 'B19ReplayAuditPackProofTest::test_each_replay_pack_requirement_keeps_its_independent_expected_and_actual_meaning',
            'negative' => 'B19ReplayAuditPackProofTest::test_each_replay_pack_requirement_keeps_its_independent_expected_and_actual_meaning',
            'basis' => 'E-MD-B19-A001-022: independent single-layer and multi-layer counterfactuals identify changed artifact layer. Positive/negative assertions are individually mapped in per_predicate_proof_map.json and executed shared MariaDB source -> producer -> persisted records -> consumer plus scoped SQLite negatives. Sensitivity: pack_expected_actual_swap; pack_actual_expected_swap. Retained E015-E021 proof reused only where unchanged; no aggregate-guard-only admission, no new golden candidate, no formal SATISFIED.',
        ],
        'MD-S063-R0119' => [
            'positive' => 'B19ReplayAuditPackProofTest::test_each_replay_pack_requirement_keeps_its_independent_expected_and_actual_meaning',
            'negative' => 'B19ReplayAuditPackProofTest::test_each_replay_pack_requirement_keeps_its_independent_expected_and_actual_meaning',
            'basis' => 'E-MD-B19-A001-022: all three own expected hashes differ from actual counterfactual without alias. Positive/negative assertions are individually mapped in per_predicate_proof_map.json and executed shared MariaDB source -> producer -> persisted records -> consumer plus scoped SQLite negatives. Sensitivity: pack_expected_actual_swap; pack_actual_expected_swap. Retained E015-E021 proof reused only where unchanged; no aggregate-guard-only admission, no new golden candidate, no formal SATISFIED.',
        ],
        'MD-S063-R0120' => [
            'positive' => 'B19ReplayAuditPackProofTest::test_each_replay_pack_requirement_keeps_its_independent_expected_and_actual_meaning',
            'negative' => 'B19ReplayAuditPackProofTest::test_each_replay_pack_requirement_keeps_its_independent_expected_and_actual_meaning',
            'basis' => 'E-MD-B19-A001-022: independent expected HELD/prior date versus actual SUCCESS/current date. Positive/negative assertions are individually mapped in per_predicate_proof_map.json and executed shared MariaDB source -> producer -> persisted records -> consumer plus scoped SQLite negatives. Sensitivity: pack_expected_actual_swap; pack_actual_expected_swap. Retained E015-E021 proof reused only where unchanged; no aggregate-guard-only admission, no new golden candidate, no formal SATISFIED.',
        ],
        'MD-S063-R0121' => [
            'positive' => 'B19ReplayAuditPackProofTest::test_each_replay_pack_requirement_keeps_its_independent_expected_and_actual_meaning',
            'negative' => 'B19ReplayAdmissionBoundaryTest::test_missing_or_unbound_identity_members_cannot_be_complete',
            'basis' => 'E-MD-B19-A001-022: distinct observation/config/temporal/factor/product/formula/read-model identities, own expected and actual namespaces. Positive/negative assertions are individually mapped in per_predicate_proof_map.json and executed shared MariaDB source -> producer -> persisted records -> consumer plus scoped SQLite negatives. Sensitivity: pack_expected_actual_swap; pack_actual_expected_swap; identity_missing_marker_admission; retained E021 field/mode probes. Retained E015-E021 proof reused only where unchanged; no aggregate-guard-only admission, no new golden candidate, no formal SATISFIED.',
        ],
        'MD-S063-R0122' => [
            'positive' => 'B19ReplayAuditPackProofTest::test_each_replay_pack_requirement_keeps_its_independent_expected_and_actual_meaning',
            'negative' => 'B19ReplayAdmissionBoundaryTest::test_missing_or_unbound_identity_members_cannot_be_complete',
            'basis' => 'E-MD-B19-A001-022: PUBLICATION_EXACT NULL cutoff, AS_KNOWN normalized declared cutoff and isolated immutable inputs. Positive/negative assertions are individually mapped in per_predicate_proof_map.json and executed shared MariaDB source -> producer -> persisted records -> consumer plus scoped SQLite negatives. Sensitivity: pack_expected_actual_swap; pack_actual_expected_swap; identity_missing_marker_admission; retained E021 field/mode probes. Retained E015-E021 proof reused only where unchanged; no aggregate-guard-only admission, no new golden candidate, no formal SATISFIED.',
        ],
        'MD-S063-R0123' => [
            'positive' => 'B19ReplayAuditPackProofTest::test_each_replay_pack_requirement_keeps_its_independent_expected_and_actual_meaning',
            'negative' => 'B19ReplayAuditPackProofTest::test_each_replay_pack_requirement_keeps_its_independent_expected_and_actual_meaning',
            'basis' => 'E-MD-B19-A001-022: canonical,indicator,eligibility,formatting drift executed here; retained real lineage/publication mismatch paths remain distinct. Positive/negative assertions are individually mapped in per_predicate_proof_map.json and executed shared MariaDB source -> producer -> persisted records -> consumer plus scoped SQLite negatives. Sensitivity: pack_expected_actual_swap; pack_actual_expected_swap. Retained E015-E021 proof reused only where unchanged; no aggregate-guard-only admission, no new golden candidate, no formal SATISFIED.',
        ],
        'MD-S075-R0004' => [
            'positive' => 'B19ArtifactBindingCompanionProofTest::test_each_applicable_binding_has_an_independent_value_and_missing_value_is_not_complete',
            'negative' => 'B19ArtifactBindingCompanionProofTest::test_missing_or_contradictory_context_stays_unavailable_without_backfill',
            'basis' => 'E025 scoped revalidation: changed admission/final completeness serialization; actual run/correction/replay/historical/failed companions bind exact bytes and independent contexts. Timestamp/category/cross-artifact controls pass; selected historical publication never implies current pointer. Semantic producers and immutable captures unchanged. Prior basis: E024 impact revalidation: correction-only scalar/lifecycle/narrative changes preserve exact serialized companion hashes, own baseline/replacement/current identities, timezone/secrecy guards and immutable prior records. Actual changed/no-op/failed/superseded correction and focused companion controls verify this affected slice. Prior basis: E023 impact revalidation: real source/run/event/selected-and-current publication exports plus focused current run-summary/event/companion regressions retain exact scalar meanings and source guards; changed narrative bytes rehashed, absent/contradictory source evidence incomplete. No replay semantics invalidation or historical mutation replay. Prior basis: E-MD-B19-A001-022: D007 companion: every exact serialized member resolves own run/publication binding; 19 independently derived values and sorted complete own reason distribution. Missing/malformed/contradictory values UNAVAILABLE; immutable bytes/co-delivery; no historical live repair. Positive/negative assertions are individually mapped in per_predicate_proof_map.json and executed shared MariaDB source -> producer -> persisted records -> consumer plus scoped SQLite negatives. Sensitivity: binding_admission_bypass; binding_availability_bypass; expected_file_context_swap; actual_context_anchor_bypass; producer_semantic_compatibility_substitution; unverified_publication_binding_admission. Retained E015-E021 proof reused only where unchanged; no aggregate-guard-only admission, no new golden candidate, no formal SATISFIED.',
        ],
        'MD-S075-R0006' => [
            'positive' => 'B19MultiFamilyMariaDbPipelineTest::test_all_identity_paths_follow_independent_inputs_and_persisted_publication_binding',
            'negative' => 'B19ArtifactCrossCuttingProofTest::test_stale_readable_publication_cannot_survive_a_held_run_export_in_the_same_directory',
            'basis' => 'E023 impact revalidation: real source/run/event/selected-and-current publication exports plus focused current run-summary/event/companion regressions retain exact scalar meanings and source guards; changed narrative bytes rehashed, absent/contradictory source evidence incomplete. No replay semantics invalidation or historical mutation replay. Prior basis: E-MD-B19-A001-022: actual provider/pipeline readable SUCCESS -> selected sealed publication and artifacts. Positive/negative assertions are individually mapped in per_predicate_proof_map.json and executed shared MariaDB source -> producer -> persisted records -> consumer plus scoped SQLite negatives. Sensitivity: stale_publication_allow; immutable_bytes_bypass; missing_binding_hash_projection; per-file expected/actual swaps. Retained E015-E021 proof reused only where unchanged; no aggregate-guard-only admission, no new golden candidate, no formal SATISFIED.',
        ],
        'MD-S075-R0007' => [
            'positive' => 'B19MultiFamilyMariaDbPipelineTest::test_all_identity_paths_follow_independent_inputs_and_persisted_publication_binding',
            'negative' => 'B19ArtifactCrossCuttingProofTest::test_stale_readable_publication_cannot_survive_a_held_run_export_in_the_same_directory',
            'basis' => 'E023 impact revalidation: real source/run/event/selected-and-current publication exports plus focused current run-summary/event/companion regressions retain exact scalar meanings and source guards; changed narrative bytes rehashed, absent/contradictory source evidence incomplete. No replay semantics invalidation or historical mutation replay. Prior basis: E-MD-B19-A001-022: authoritative HELD and FAILED primary rows -> actual export NOT_READABLE without publication; no new failed-producer semantics claimed. Positive/negative assertions are individually mapped in per_predicate_proof_map.json and executed shared MariaDB source -> producer -> persisted records -> consumer plus scoped SQLite negatives. Sensitivity: stale_publication_allow; immutable_bytes_bypass; missing_binding_hash_projection; per-file expected/actual swaps. Retained E015-E021 proof reused only where unchanged; no aggregate-guard-only admission, no new golden candidate, no formal SATISFIED.',
        ],
        'MD-S075-R0008' => [
            'positive' => 'B19MultiFamilyMariaDbPipelineTest::test_all_identity_paths_follow_independent_inputs_and_persisted_publication_binding',
            'negative' => 'B19ArtifactCrossCuttingProofTest::test_stale_readable_publication_cannot_survive_a_held_run_export_in_the_same_directory',
            'basis' => 'E025 scoped revalidation: changed admission/final completeness serialization; actual run/correction/replay/historical/failed companions bind exact bytes and independent contexts. Timestamp/category/cross-artifact controls pass; selected historical publication never implies current pointer. Semantic producers and immutable captures unchanged. Prior basis: E024 impact revalidation: correction-only scalar/lifecycle/narrative changes preserve exact serialized companion hashes, own baseline/replacement/current identities, timezone/secrecy guards and immutable prior records. Actual changed/no-op/failed/superseded correction and focused companion controls verify this affected slice. Prior basis: E023 impact revalidation: real source/run/event/selected-and-current publication exports plus focused current run-summary/event/companion regressions retain exact scalar meanings and source guards; changed narrative bytes rehashed, absent/contradictory source evidence incomplete. No replay semantics invalidation or historical mutation replay. Prior basis: E-MD-B19-A001-022: actual correction request/approve/pipeline/export unchanged lifecycle keeps prior publication immutable. Positive/negative assertions are individually mapped in per_predicate_proof_map.json and executed shared MariaDB source -> producer -> persisted records -> consumer plus scoped SQLite negatives. Sensitivity: stale_publication_allow; immutable_bytes_bypass; missing_binding_hash_projection; per-file expected/actual swaps. Retained E015-E021 proof reused only where unchanged; no aggregate-guard-only admission, no new golden candidate, no formal SATISFIED.',
        ],
        'MD-S075-R0009' => [
            'positive' => 'B19MultiFamilyMariaDbPipelineTest::test_all_identity_paths_follow_independent_inputs_and_persisted_publication_binding',
            'negative' => 'B19ReplayAuditPackProofTest::test_each_replay_pack_requirement_keeps_its_independent_expected_and_actual_meaning',
            'basis' => 'E-MD-B19-A001-022: actual replay MATCH and independent mismatch runs export all three states and diagnostic pack. Positive/negative assertions are individually mapped in per_predicate_proof_map.json and executed shared MariaDB source -> producer -> persisted records -> consumer plus scoped SQLite negatives. Sensitivity: stale_publication_allow; immutable_bytes_bypass; missing_binding_hash_projection; per-file expected/actual swaps. Retained E015-E021 proof reused only where unchanged; no aggregate-guard-only admission, no new golden candidate, no formal SATISFIED.',
        ],
        'MD-S075-R0011' => [
            'positive' => 'B19MultiFamilyMariaDbPipelineTest::test_all_identity_paths_follow_independent_inputs_and_persisted_publication_binding',
            'negative' => 'B19ArtifactCrossCuttingProofTest::test_stale_readable_publication_cannot_survive_a_held_run_export_in_the_same_directory',
            'basis' => 'E023 impact revalidation: real source/run/event/selected-and-current publication exports plus focused current run-summary/event/companion regressions retain exact scalar meanings and source guards; changed narrative bytes rehashed, absent/contradictory source evidence incomplete. No replay semantics invalidation or historical mutation replay. Prior basis: E-MD-B19-A001-022: own run identity/status/date rather than mere summary presence. Positive/negative assertions are individually mapped in per_predicate_proof_map.json and executed shared MariaDB source -> producer -> persisted records -> consumer plus scoped SQLite negatives. Sensitivity: stale_publication_allow; immutable_bytes_bypass; missing_binding_hash_projection; per-file expected/actual swaps. Retained E015-E021 proof reused only where unchanged; no aggregate-guard-only admission, no new golden candidate, no formal SATISFIED.',
        ],
        'MD-S075-R0012' => [
            'positive' => 'B19MultiFamilyMariaDbPipelineTest::test_all_identity_paths_follow_independent_inputs_and_persisted_publication_binding',
            'negative' => 'B19ArtifactCrossCuttingProofTest::test_stale_readable_publication_cannot_survive_a_held_run_export_in_the_same_directory',
            'basis' => 'E023 impact revalidation: real source/run/event/selected-and-current publication exports plus focused current run-summary/event/companion regressions retain exact scalar meanings and source guards; changed narrative bytes rehashed, absent/contradictory source evidence incomplete. No replay semantics invalidation or historical mutation replay. Prior basis: E-MD-B19-A001-022: selected sealed publication exact immutable oracle digest; noncurrent selection retained. Positive/negative assertions are individually mapped in per_predicate_proof_map.json and executed shared MariaDB source -> producer -> persisted records -> consumer plus scoped SQLite negatives. Sensitivity: stale_publication_allow; immutable_bytes_bypass; missing_binding_hash_projection; per-file expected/actual swaps. Retained E015-E021 proof reused only where unchanged; no aggregate-guard-only admission, no new golden candidate, no formal SATISFIED.',
        ],
        'MD-S075-R0013' => [
            'positive' => 'B19MultiFamilyMariaDbPipelineTest::test_all_identity_paths_follow_independent_inputs_and_persisted_publication_binding',
            'negative' => 'B19ArtifactCrossCuttingProofTest::test_stale_readable_publication_cannot_survive_a_held_run_export_in_the_same_directory',
            'basis' => 'E023 impact revalidation: real source/run/event/selected-and-current publication exports plus focused current run-summary/event/companion regressions retain exact scalar meanings and source guards; changed narrative bytes rehashed, absent/contradictory source evidence incomplete. No replay semantics invalidation or historical mutation replay. Prior basis: E-MD-B19-A001-022: actual event trail stage counts independently equal primary events for own run. Positive/negative assertions are individually mapped in per_predicate_proof_map.json and executed shared MariaDB source -> producer -> persisted records -> consumer plus scoped SQLite negatives. Sensitivity: stale_publication_allow; immutable_bytes_bypass; missing_binding_hash_projection; per-file expected/actual swaps. Retained E015-E021 proof reused only where unchanged; no aggregate-guard-only admission, no new golden candidate, no formal SATISFIED.',
        ],
        'MD-S075-R0014' => [
            'positive' => 'B19MultiFamilyMariaDbPipelineTest::test_all_identity_paths_follow_independent_inputs_and_persisted_publication_binding',
            'negative' => 'B19ArtifactCrossCuttingProofTest::test_stale_readable_publication_cannot_survive_a_held_run_export_in_the_same_directory',
            'basis' => 'E-MD-B19-A001-022: real insufficient-history eligibility CSV row and exact reason, retained E015 row-provenance proof. Positive/negative assertions are individually mapped in per_predicate_proof_map.json and executed shared MariaDB source -> producer -> persisted records -> consumer plus scoped SQLite negatives. Sensitivity: stale_publication_allow; immutable_bytes_bypass; missing_binding_hash_projection; per-file expected/actual swaps. Retained E015-E021 proof reused only where unchanged; no aggregate-guard-only admission, no new golden candidate, no formal SATISFIED.',
        ],
        'MD-S075-R0015' => [
            'positive' => 'B19MultiFamilyMariaDbPipelineTest::test_all_identity_paths_follow_independent_inputs_and_persisted_publication_binding',
            'negative' => 'B19InvalidBarsExportRowProvenanceTest::test_the_export_rows_equal_their_own_persisted_rejections_with_null_identity_preserved',
            'basis' => 'E-MD-B19-A001-022: actual empty invalid CSV for clean world plus retained executed nonempty conflict/rejection source provenance and current regression. Positive/negative assertions are individually mapped in per_predicate_proof_map.json and executed shared MariaDB source -> producer -> persisted records -> consumer plus scoped SQLite negatives. Sensitivity: stale_publication_allow; immutable_bytes_bypass; missing_binding_hash_projection; per-file expected/actual swaps; retained E015/E021 rejection/reason/source-row mutations. Retained E015-E021 proof reused only where unchanged; no aggregate-guard-only admission, no new golden candidate, no formal SATISFIED.',
        ],
        'MD-S075-R0016' => [
            'positive' => 'B19MultiFamilyMariaDbPipelineTest::test_all_identity_paths_follow_independent_inputs_and_persisted_publication_binding',
            'negative' => 'B19ArtifactCrossCuttingProofTest::test_stale_readable_publication_cannot_survive_a_held_run_export_in_the_same_directory',
            'basis' => 'E023 impact revalidation: real source/run/event/selected-and-current publication exports plus focused current run-summary/event/companion regressions retain exact scalar meanings and source guards; changed narrative bytes rehashed, absent/contradictory source evidence incomplete. No replay semantics invalidation or historical mutation replay. Prior basis: E-MD-B19-A001-022: anomaly summary contains own SUCCESS/HELD/FAILED status, no stale readable story. Positive/negative assertions are individually mapped in per_predicate_proof_map.json and executed shared MariaDB source -> producer -> persisted records -> consumer plus scoped SQLite negatives. Sensitivity: stale_publication_allow; immutable_bytes_bypass; missing_binding_hash_projection; per-file expected/actual swaps. Retained E015-E021 proof reused only where unchanged; no aggregate-guard-only admission, no new golden candidate, no formal SATISFIED.',
        ],
        'MD-S075-R0018' => [
            'positive' => 'B19MultiFamilyMariaDbPipelineTest::test_all_identity_paths_follow_independent_inputs_and_persisted_publication_binding',
            'negative' => 'B19ArtifactCrossCuttingProofTest::test_stale_readable_publication_cannot_survive_a_held_run_export_in_the_same_directory',
            'basis' => 'E023 impact revalidation: real source/run/event/selected-and-current publication exports plus focused current run-summary/event/companion regressions retain exact scalar meanings and source guards; changed narrative bytes rehashed, absent/contradictory source evidence incomplete. No replay semantics invalidation or historical mutation replay. Prior basis: E-MD-B19-A001-022: real telemetry event resolves own run, exactly one successful HTTP200 provider attempt. Positive/negative assertions are individually mapped in per_predicate_proof_map.json and executed shared MariaDB source -> producer -> persisted records -> consumer plus scoped SQLite negatives. Sensitivity: stale_publication_allow; immutable_bytes_bypass; missing_binding_hash_projection; per-file expected/actual swaps. Retained E015-E021 proof reused only where unchanged; no aggregate-guard-only admission, no new golden candidate, no formal SATISFIED.',
        ],
        'MD-S075-R0019' => [
            'positive' => 'B19MultiFamilyMariaDbPipelineTest::test_all_identity_paths_follow_independent_inputs_and_persisted_publication_binding',
            'negative' => 'B19ArtifactCrossCuttingProofTest::test_stale_readable_publication_cannot_survive_a_held_run_export_in_the_same_directory',
            'basis' => 'E025 scoped revalidation: changed admission/final completeness serialization; actual run/correction/replay/historical/failed companions bind exact bytes and independent contexts. Timestamp/category/cross-artifact controls pass; selected historical publication never implies current pointer. Semantic producers and immutable captures unchanged. Prior basis: E024 impact revalidation: correction-only scalar/lifecycle/narrative changes preserve exact serialized companion hashes, own baseline/replacement/current identities, timezone/secrecy guards and immutable prior records. Actual changed/no-op/failed/superseded correction and focused companion controls verify this affected slice. Prior basis: E-MD-B19-A001-022: real correction identity, UNCHANGED/no switch/no new publication/NOT_RESEALED_UNCHANGED. Positive/negative assertions are individually mapped in per_predicate_proof_map.json and executed shared MariaDB source -> producer -> persisted records -> consumer plus scoped SQLite negatives. Sensitivity: stale_publication_allow; immutable_bytes_bypass; missing_binding_hash_projection; per-file expected/actual swaps. Retained E015-E021 proof reused only where unchanged; no aggregate-guard-only admission, no new golden candidate, no formal SATISFIED.',
        ],
        'MD-S075-R0020' => [
            'positive' => 'B19MultiFamilyMariaDbPipelineTest::test_all_identity_paths_follow_independent_inputs_and_persisted_publication_binding',
            'negative' => 'B19ReplayAuditPackProofTest::test_each_replay_pack_requirement_keeps_its_independent_expected_and_actual_meaning',
            'basis' => 'E-MD-B19-A001-022: actual comparator/persistence result classification, own namespaces and serialized hash. Positive/negative assertions are individually mapped in per_predicate_proof_map.json and executed shared MariaDB source -> producer -> persisted records -> consumer plus scoped SQLite negatives. Sensitivity: stale_publication_allow; immutable_bytes_bypass; missing_binding_hash_projection; per-file expected/actual swaps. Retained E015-E021 proof reused only where unchanged; no aggregate-guard-only admission, no new golden candidate, no formal SATISFIED.',
        ],
        'MD-S075-R0021' => [
            'positive' => 'B19MultiFamilyMariaDbPipelineTest::test_all_identity_paths_follow_independent_inputs_and_persisted_publication_binding',
            'negative' => 'B19ReplayAuditPackProofTest::test_each_replay_pack_requirement_keeps_its_independent_expected_and_actual_meaning',
            'basis' => 'E-MD-B19-A001-022: independent fixture expected state/status/date/hash and binding reference. Positive/negative assertions are individually mapped in per_predicate_proof_map.json and executed shared MariaDB source -> producer -> persisted records -> consumer plus scoped SQLite negatives. Sensitivity: stale_publication_allow; immutable_bytes_bypass; missing_binding_hash_projection; per-file expected/actual swaps. Retained E015-E021 proof reused only where unchanged; no aggregate-guard-only admission, no new golden candidate, no formal SATISFIED.',
        ],
        'MD-S075-R0022' => [
            'positive' => 'B19MultiFamilyMariaDbPipelineTest::test_all_identity_paths_follow_independent_inputs_and_persisted_publication_binding',
            'negative' => 'B19ReplayAuditPackProofTest::test_each_replay_pack_requirement_keeps_its_independent_expected_and_actual_meaning',
            'basis' => 'E-MD-B19-A001-022: real actual state/status/date/hash and own actual context reference. Positive/negative assertions are individually mapped in per_predicate_proof_map.json and executed shared MariaDB source -> producer -> persisted records -> consumer plus scoped SQLite negatives. Sensitivity: stale_publication_allow; immutable_bytes_bypass; missing_binding_hash_projection; per-file expected/actual swaps. Retained E015-E021 proof reused only where unchanged; no aggregate-guard-only admission, no new golden candidate, no formal SATISFIED.',
        ],
        'MD-S075-R0297' => [
            'positive' => 'B19ArtifactCrossCuttingProofTest::test_exact_serialized_file_hashes_timestamp_context_and_historical_inputs_are_preserved',
            'negative' => 'B19ArtifactCrossCuttingProofTest::test_exact_serialized_file_hashes_timestamp_context_and_historical_inputs_are_preserved',
            'basis' => 'E025 scoped revalidation: changed admission/final completeness serialization; actual run/correction/replay/historical/failed companions bind exact bytes and independent contexts. Timestamp/category/cross-artifact controls pass; selected historical publication never implies current pointer. Semantic producers and immutable captures unchanged. Prior basis: E024 impact revalidation: correction-only scalar/lifecycle/narrative changes preserve exact serialized companion hashes, own baseline/replacement/current identities, timezone/secrecy guards and immutable prior records. Actual changed/no-op/failed/superseded correction and focused companion controls verify this affected slice. Prior basis: E-MD-B19-A001-022: co-delivered explicit Asia/Jakarta timestamp representation; original explicit offsets retained as stated alternative. Positive/negative assertions are individually mapped in per_predicate_proof_map.json and executed shared MariaDB source -> producer -> persisted records -> consumer plus scoped SQLite negatives. Sensitivity: timestamp_context_wrong. Retained E015-E021 proof reused only where unchanged; no aggregate-guard-only admission, no new golden candidate, no formal SATISFIED.',
        ],
        'MD-S075-R0298' => [
            'positive' => 'B19ArtifactCrossCuttingProofTest::test_exact_serialized_file_hashes_timestamp_context_and_historical_inputs_are_preserved',
            'negative' => 'B19ArtifactCrossCuttingProofTest::test_exact_serialized_file_hashes_timestamp_context_and_historical_inputs_are_preserved',
            'basis' => 'E025 scoped revalidation: changed admission/final completeness serialization; actual run/correction/replay/historical/failed companions bind exact bytes and independent contexts. Timestamp/category/cross-artifact controls pass; selected historical publication never implies current pointer. Semantic producers and immutable captures unchanged. Prior basis: E024 impact revalidation: correction-only scalar/lifecycle/narrative changes preserve exact serialized companion hashes, own baseline/replacement/current identities, timezone/secrecy guards and immutable prior records. Actual changed/no-op/failed/superseded correction and focused companion controls verify this affected slice. Prior basis: E-MD-B19-A001-022: same timestamp context across run, correction and replay; UTC substitution fails. Positive/negative assertions are individually mapped in per_predicate_proof_map.json and executed shared MariaDB source -> producer -> persisted records -> consumer plus scoped SQLite negatives. Sensitivity: timestamp_context_wrong. Retained E015-E021 proof reused only where unchanged; no aggregate-guard-only admission, no new golden candidate, no formal SATISFIED.',
        ],
        'MD-S075-R0300' => [
            'positive' => 'B19ArtifactCrossCuttingProofTest::test_sensitive_fixture_metadata_is_refused_before_it_can_leak',
            'negative' => 'B19ArtifactCrossCuttingProofTest::test_csv_and_markdown_sinks_refuse_embedded_credentials',
            'basis' => 'E-MD-B19-A001-022: structured credentials and embedded URI/password strings refused at all JSON/CSV/Markdown sinks. Positive/negative assertions are individually mapped in per_predicate_proof_map.json and executed shared MariaDB source -> producer -> persisted records -> consumer plus scoped SQLite negatives. Sensitivity: credential_field_allow; csv_sensitive_sink_bypass. Retained E015-E021 proof reused only where unchanged; no aggregate-guard-only admission, no new golden candidate, no formal SATISFIED.',
        ],
        'MD-S075-R0301' => [
            'positive' => 'B19ArtifactCrossCuttingProofTest::test_sensitive_fixture_metadata_is_refused_before_it_can_leak',
            'negative' => 'B19ArtifactCrossCuttingProofTest::test_csv_and_markdown_sinks_refuse_embedded_credentials',
            'basis' => 'E-MD-B19-A001-022: access/refresh/auth token fields and bearer strings refused without leaking values in errors. Positive/negative assertions are individually mapped in per_predicate_proof_map.json and executed shared MariaDB source -> producer -> persisted records -> consumer plus scoped SQLite negatives. Sensitivity: access_token_allow. Retained E015-E021 proof reused only where unchanged; no aggregate-guard-only admission, no new golden candidate, no formal SATISFIED.',
        ],
        'MD-S075-R0302' => [
            'positive' => 'B19ArtifactCrossCuttingProofTest::test_sensitive_fixture_metadata_is_refused_before_it_can_leak',
            'negative' => 'B19ArtifactCrossCuttingProofTest::test_csv_and_markdown_sinks_refuse_embedded_credentials',
            'basis' => 'E-MD-B19-A001-022: internal-only sensitive keys refused; reason/provenance audit fields preserved. Positive/negative assertions are individually mapped in per_predicate_proof_map.json and executed shared MariaDB source -> producer -> persisted records -> consumer plus scoped SQLite negatives. Sensitivity: internal_token_allow. Retained E015-E021 proof reused only where unchanged; no aggregate-guard-only admission, no new golden candidate, no formal SATISFIED.',
        ],
        'MD-S075-R0309' => [
            'positive' => 'B19MultiFamilyMariaDbPipelineTest::test_all_identity_paths_follow_independent_inputs_and_persisted_publication_binding',
            'negative' => 'B19ArtifactCrossCuttingProofTest::test_stale_readable_publication_cannot_survive_a_held_run_export_in_the_same_directory',
            'basis' => 'E025 scoped revalidation: changed admission/final completeness serialization; actual run/correction/replay/historical/failed companions bind exact bytes and independent contexts. Timestamp/category/cross-artifact controls pass; selected historical publication never implies current pointer. Semantic producers and immutable captures unchanged. Prior basis: E024 impact revalidation: correction-only scalar/lifecycle/narrative changes preserve exact serialized companion hashes, own baseline/replacement/current identities, timezone/secrecy guards and immutable prior records. Actual changed/no-op/failed/superseded correction and focused companion controls verify this affected slice. Prior basis: E023 impact revalidation: real source/run/event/selected-and-current publication exports plus focused current run-summary/event/companion regressions retain exact scalar meanings and source guards; changed narrative bytes rehashed, absent/contradictory source evidence incomplete. No replay semantics invalidation or historical mutation replay. Prior basis: E-MD-B19-A001-022: held/failed has no publication manifest; reuse readable directory refused; replay result/expected/actual maintain own consistent identities. Positive/negative assertions are individually mapped in per_predicate_proof_map.json and executed shared MariaDB source -> producer -> persisted records -> consumer plus scoped SQLite negatives. Sensitivity: stale_publication_allow; pack_expected_actual_swap; pack_actual_expected_swap. Retained E015-E021 proof reused only where unchanged; no aggregate-guard-only admission, no new golden candidate, no formal SATISFIED.',
        ],
        'MD-S075-R0325' => [
            'positive' => 'B19MultiFamilyMariaDbPipelineTest::test_all_identity_paths_follow_independent_inputs_and_persisted_publication_binding',
            'negative' => 'B19ArtifactBindingCompanionProofTest::test_missing_or_contradictory_context_stays_unavailable_without_backfill',
            'basis' => 'E-MD-B19-A001-022: real replay production from selected immutable semantic observation/config; AS_KNOWN cutoff inputs isolated; no config label/hash alias. Positive/negative assertions are individually mapped in per_predicate_proof_map.json and executed shared MariaDB source -> producer -> persisted records -> consumer plus scoped SQLite negatives. Sensitivity: producer_semantic_compatibility_substitution; retained E021 config_id_type/config_hash_substitution/read_model_substitution. Retained E015-E021 proof reused only where unchanged; no aggregate-guard-only admission, no new golden candidate, no formal SATISFIED.',
        ],
        'MD-S075-R0326' => [
            'positive' => 'B19MultiFamilyMariaDbPipelineTest::test_all_identity_paths_follow_independent_inputs_and_persisted_publication_binding',
            'negative' => 'B19ArtifactBindingCompanionProofTest::test_missing_or_contradictory_context_stays_unavailable_without_backfill',
            'basis' => 'E-MD-B19-A001-022: versioned capture before persistence from verified selected noncurrent publication; current pointer/live default changes cannot repair; legacy missing stays unavailable. Positive/negative assertions are individually mapped in per_predicate_proof_map.json and executed shared MariaDB source -> producer -> persisted records -> consumer plus scoped SQLite negatives. Sensitivity: unverified_publication_binding_admission; identity_missing_marker_admission; retained E021 persisted envelope refusal. Retained E015-E021 proof reused only where unchanged; no aggregate-guard-only admission, no new golden candidate, no formal SATISFIED.',
        ],
        'MD-S075-R0327' => [
            'positive' => 'B19MultiFamilyMariaDbPipelineTest::test_all_identity_paths_follow_independent_inputs_and_persisted_publication_binding',
            'negative' => 'B19ArtifactBindingCompanionProofTest::test_missing_or_contradictory_context_stays_unavailable_without_backfill',
            'basis' => 'E-MD-B19-A001-022: analytical non-NULL required; AS_KNOWN canonical RAW explicit NA; unsupported publication RAW BLOCKED; missing applicable envelope/member cannot complete. Positive/negative assertions are individually mapped in per_predicate_proof_map.json and executed shared MariaDB source -> producer -> persisted records -> consumer plus scoped SQLite negatives. Sensitivity: identity_status_promotion; actual_context_anchor_bypass; retained E021 raw_capability_bypass. Retained E015-E021 proof reused only where unchanged; no aggregate-guard-only admission, no new golden candidate, no formal SATISFIED.',
        ],
        'MD-S075-R0328' => [
            'positive' => 'B19MultiFamilyMariaDbPipelineTest::test_all_identity_paths_follow_independent_inputs_and_persisted_publication_binding',
            'negative' => 'B19ArtifactBindingCompanionProofTest::test_missing_or_contradictory_context_stays_unavailable_without_backfill',
            'basis' => 'E-MD-B19-A001-022: independent temporal preimage versus temporal_identity, semantic factor versus event_factor, formula label versus formula_registry; frozen code/version. Positive/negative assertions are individually mapped in per_predicate_proof_map.json and executed shared MariaDB source -> producer -> persisted records -> consumer plus scoped SQLite negatives. Sensitivity: producer_semantic_compatibility_substitution; retained E021 temporal_alias/formula_alias. Retained E015-E021 proof reused only where unchanged; no aggregate-guard-only admission, no new golden candidate, no formal SATISFIED.',
        ],
        'MD-S075-R0329' => [
            'positive' => 'B19MultiFamilyMariaDbPipelineTest::test_all_identity_paths_follow_independent_inputs_and_persisted_publication_binding',
            'negative' => 'B19ArtifactBindingCompanionProofTest::test_each_file_resolves_its_own_immutable_context_and_sorted_complete_reason_set',
            'basis' => 'E-MD-B19-A001-022: independent versioned fixture expected mode/context/reasons; substitutions and missing members detected; actual expected namespaces remain separate. Positive/negative assertions are individually mapped in per_predicate_proof_map.json and executed shared MariaDB source -> producer -> persisted records -> consumer plus scoped SQLite negatives. Sensitivity: expected_file_context_swap; pack_expected_actual_swap; retained E021 mode/context substitutions. Retained E015-E021 proof reused only where unchanged; no aggregate-guard-only admission, no new golden candidate, no formal SATISFIED.',
        ],
        'MD-S075-R0330' => [
            'positive' => 'B19ArtifactCrossCuttingProofTest::test_exact_serialized_file_hashes_timestamp_context_and_historical_inputs_are_preserved',
            'negative' => 'B19ArtifactCrossCuttingProofTest::test_delivered_companion_and_member_bytes_are_immutable',
            'basis' => 'E025 scoped revalidation: changed admission/final completeness serialization; actual run/correction/replay/historical/failed companions bind exact bytes and independent contexts. Timestamp/category/cross-artifact controls pass; selected historical publication never implies current pointer. Semantic producers and immutable captures unchanged. Prior basis: E024 impact revalidation: correction-only scalar/lifecycle/narrative changes preserve exact serialized companion hashes, own baseline/replacement/current identities, timezone/secrecy guards and immutable prior records. Actual changed/no-op/failed/superseded correction and focused companion controls verify this affected slice. Prior basis: E023 impact revalidation: real source/run/event/selected-and-current publication exports plus focused current run-summary/event/companion regressions retain exact scalar meanings and source guards; changed narrative bytes rehashed, absent/contradictory source evidence incomplete. No replay semantics invalidation or historical mutation replay. Prior basis: E-MD-B19-A001-022: exact member byte SHA256/size; changed fixture gets new version/output directory; old artifact bytes and immutable fixtures retained. Positive/negative assertions are individually mapped in per_predicate_proof_map.json and executed shared MariaDB source -> producer -> persisted records -> consumer plus scoped SQLite negatives. Sensitivity: missing_binding_hash_projection; immutable_bytes_bypass. Retained E015-E021 proof reused only where unchanged; no aggregate-guard-only admission, no new golden candidate, no formal SATISFIED.',
        ],
        'MD-S063-R0169' => [
            'positive' => 'B19ReplayAuditPackProofTest::test_each_replay_pack_requirement_keeps_its_independent_expected_and_actual_meaning',
            'negative' => 'B19ReplayAdmissionBoundaryTest::test_missing_or_unbound_identity_members_cannot_be_complete',
            'basis' => 'E-MD-B19-A001-022: exact aligned persisted names/versioned envelopes and both contexts; missing marker/envelope cannot complete identity; no live repair or matching NULL admission. Positive/negative assertions are individually mapped in per_predicate_proof_map.json and executed shared MariaDB source -> producer -> persisted records -> consumer plus scoped SQLite negatives. Sensitivity: pack_expected_actual_swap; pack_actual_expected_swap; identity_missing_marker_admission; retained E021 field/mode probes. Retained E015-E021 proof reused only where unchanged; no aggregate-guard-only admission, no new golden candidate, no formal SATISFIED.',
        ],

        // E023: 66 per-predicate bases; real producer worlds and independent persisted/source expectations.
        'MD-S063-R0017' => [
            'positive' => 'B19RunEvidenceMariaDbProofTest::test_real_sources_attempts_run_pack_anomaly_and_historical_resolution',
            'negative' => 'B19RunAuditPackProofTest::test_each_run_pack_question_preserves_authoritative_facts_and_unavailability',
            'basis' => 'E-MD-B19-A001-023: requested date/result explained through real SUCCESS, retry, corrected-current, superseded, HELD and persisted FAILED paths. Source: real source/pipeline -> primary run/event/config/lineage/artifact/publication rows, immutable v5 expected literals, verified selected and actual current publication; direct independently authored negative source rows. Export: evidence_pack.json and co-delivered per-artifact binding manifest. Positive and contradictory/unavailable assertions individually mapped in per_predicate_proof_map.json; real MariaDB plus SQLite source/projection guards, independently derived values; no field-presence/mock-only/NULL-match admission. Sensitivity: new source-selection/admission/narrative probes as applicable; retained unchanged run-summary/event/semantic-field mutations from E015-E022, not repeated. Formal SATISFIED remains zero.',
        ],
        'MD-S063-R0019' => [
            'positive' => 'B19RunEvidenceMariaDbProofTest::test_real_sources_attempts_run_pack_anomaly_and_historical_resolution',
            'negative' => 'B19RunAuditPackProofTest::test_each_run_pack_question_preserves_authoritative_facts_and_unavailability',
            'basis' => 'E-MD-B19-A001-023: own real run summary ID/status/date and primary input records. Source: real source/pipeline -> primary run/event/config/lineage/artifact/publication rows, immutable v5 expected literals, verified selected and actual current publication; direct independently authored negative source rows. Export: evidence_pack.json and co-delivered per-artifact binding manifest. Positive and contradictory/unavailable assertions individually mapped in per_predicate_proof_map.json; real MariaDB plus SQLite source/projection guards, independently derived values; no field-presence/mock-only/NULL-match admission. Sensitivity: new source-selection/admission/narrative probes as applicable; retained unchanged run-summary/event/semantic-field mutations from E015-E022, not repeated. Formal SATISFIED remains zero.',
        ],
        'MD-S063-R0020' => [
            'positive' => 'B19RunEvidenceMariaDbProofTest::test_real_sources_attempts_run_pack_anomaly_and_historical_resolution',
            'negative' => 'B19RunAuditPackProofTest::test_each_run_pack_question_preserves_authoritative_facts_and_unavailability',
            'basis' => 'E-MD-B19-A001-023: verified selected manifest or explicit unavailable publication-resolution object; historical selected old manifest differs from live current pointer. Source: real source/pipeline -> primary run/event/config/lineage/artifact/publication rows, immutable v5 expected literals, verified selected and actual current publication; direct independently authored negative source rows. Export: evidence_pack.json and co-delivered per-artifact binding manifest. Positive and contradictory/unavailable assertions individually mapped in per_predicate_proof_map.json; real MariaDB plus SQLite source/projection guards, independently derived values; no field-presence/mock-only/NULL-match admission. Sensitivity: real_current_pointer_hidden_by_history; real_historical_readability_invented; real_superseded_publication_safety_invented. Formal SATISFIED remains zero.',
        ],
        'MD-S063-R0021' => [
            'positive' => 'B19RunEvidenceMariaDbProofTest::test_real_sources_attempts_run_pack_anomaly_and_historical_resolution',
            'negative' => 'B19RunAuditPackProofTest::test_each_run_pack_question_preserves_authoritative_facts_and_unavailability',
            'basis' => 'E-MD-B19-A001-023: event count/stage/reason summary independently derived from primary trail, not mere keys. Source: real source/pipeline -> primary run/event/config/lineage/artifact/publication rows, immutable v5 expected literals, verified selected and actual current publication; direct independently authored negative source rows. Export: evidence_pack.json and co-delivered per-artifact binding manifest. Positive and contradictory/unavailable assertions individually mapped in per_predicate_proof_map.json; real MariaDB plus SQLite source/projection guards, independently derived values; no field-presence/mock-only/NULL-match admission. Sensitivity: real_event_trail_admission_bypass; old_event_replaces_latest. Formal SATISFIED remains zero.',
        ],
        'MD-S063-R0022' => [
            'positive' => 'B19RunEvidenceMariaDbProofTest::test_real_sources_attempts_run_pack_anomaly_and_historical_resolution',
            'negative' => 'B19RunAuditPackProofTest::test_each_run_pack_question_preserves_authoritative_facts_and_unavailability',
            'basis' => 'E-MD-B19-A001-023: requested date retained independently of fallback effective date. Source: real source/pipeline -> primary run/event/config/lineage/artifact/publication rows, immutable v5 expected literals, verified selected and actual current publication; direct independently authored negative source rows. Export: evidence_pack.json and co-delivered per-artifact binding manifest. Positive and contradictory/unavailable assertions individually mapped in per_predicate_proof_map.json; real MariaDB plus SQLite source/projection guards, independently derived values; no field-presence/mock-only/NULL-match admission. Sensitivity: new source-selection/admission/narrative probes as applicable; retained unchanged run-summary/event/semantic-field mutations from E015-E022, not repeated. Formal SATISFIED remains zero.',
        ],
        'MD-S063-R0023' => [
            'positive' => 'B19RunEvidenceMariaDbProofTest::test_real_sources_attempts_run_pack_anomaly_and_historical_resolution',
            'negative' => 'B19RunAuditPackProofTest::test_each_run_pack_question_preserves_authoritative_facts_and_unavailability',
            'basis' => 'E-MD-B19-A001-023: own effective date versus requested date and verified/no-claimed fallback. Source: real source/pipeline -> primary run/event/config/lineage/artifact/publication rows, immutable v5 expected literals, verified selected and actual current publication; direct independently authored negative source rows. Export: evidence_pack.json and co-delivered per-artifact binding manifest. Positive and contradictory/unavailable assertions individually mapped in per_predicate_proof_map.json; real MariaDB plus SQLite source/projection guards, independently derived values; no field-presence/mock-only/NULL-match admission. Sensitivity: new source-selection/admission/narrative probes as applicable; retained unchanged run-summary/event/semantic-field mutations from E015-E022, not repeated. Formal SATISFIED remains zero.',
        ],
        'MD-S063-R0024' => [
            'positive' => 'B19RunEvidenceMariaDbProofTest::test_real_sources_attempts_run_pack_anomaly_and_historical_resolution',
            'negative' => 'B19RunAuditPackProofTest::test_each_run_pack_question_preserves_authoritative_facts_and_unavailability',
            'basis' => 'E-MD-B19-A001-023: SUCCESS/HELD/FAILED terminal state persisted by real producer, FAILED auth exception propagates after recording evidence. Source: real source/pipeline -> primary run/event/config/lineage/artifact/publication rows, immutable v5 expected literals, verified selected and actual current publication; direct independently authored negative source rows. Export: evidence_pack.json and co-delivered per-artifact binding manifest. Positive and contradictory/unavailable assertions individually mapped in per_predicate_proof_map.json; real MariaDB plus SQLite source/projection guards, independently derived values; no field-presence/mock-only/NULL-match admission. Sensitivity: new source-selection/admission/narrative probes as applicable; retained unchanged run-summary/event/semantic-field mutations from E015-E022, not repeated. Formal SATISFIED remains zero.',
        ],
        'MD-S063-R0025' => [
            'positive' => 'B19RunEvidenceMariaDbProofTest::test_real_sources_attempts_run_pack_anomaly_and_historical_resolution',
            'negative' => 'B19RunAuditPackProofTest::test_each_run_pack_question_preserves_authoritative_facts_and_unavailability',
            'basis' => 'E-MD-B19-A001-023: reason counts independently derived from own events plus exact publication eligibility history. Source: real source/pipeline -> primary run/event/config/lineage/artifact/publication rows, immutable v5 expected literals, verified selected and actual current publication; direct independently authored negative source rows. Export: evidence_pack.json and co-delivered per-artifact binding manifest. Positive and contradictory/unavailable assertions individually mapped in per_predicate_proof_map.json; real MariaDB plus SQLite source/projection guards, independently derived values; no field-presence/mock-only/NULL-match admission. Sensitivity: new source-selection/admission/narrative probes as applicable; retained unchanged run-summary/event/semantic-field mutations from E015-E022, not repeated. Formal SATISFIED remains zero.',
        ],
        'MD-S063-R0026' => [
            'positive' => 'B19RunEvidenceMariaDbProofTest::test_real_sources_attempts_run_pack_anomaly_and_historical_resolution',
            'negative' => 'B19RunAuditPackProofTest::test_each_run_pack_question_preserves_authoritative_facts_and_unavailability',
            'basis' => 'E-MD-B19-A001-023: three batch hashes equal immutable independently authored v5 semantic literals; absent applicable evidence stays incomplete. Source: real source/pipeline -> primary run/event/config/lineage/artifact/publication rows, immutable v5 expected literals, verified selected and actual current publication; direct independently authored negative source rows. Export: evidence_pack.json and co-delivered per-artifact binding manifest. Positive and contradictory/unavailable assertions individually mapped in per_predicate_proof_map.json; real MariaDB plus SQLite source/projection guards, independently derived values; no field-presence/mock-only/NULL-match admission. Sensitivity: new source-selection/admission/narrative probes as applicable; retained unchanged run-summary/event/semantic-field mutations from E015-E022, not repeated. Formal SATISFIED remains zero.',
        ],
        'MD-S063-R0027' => [
            'positive' => 'B19RunEvidenceMariaDbProofTest::test_real_sources_attempts_run_pack_anomaly_and_historical_resolution',
            'negative' => 'B19RunAuditPackProofTest::test_each_run_pack_question_preserves_authoritative_facts_and_unavailability',
            'basis' => 'E-MD-B19-A001-023: actual sealed selected publication; held/failed have no safe publication claim. Source: real source/pipeline -> primary run/event/config/lineage/artifact/publication rows, immutable v5 expected literals, verified selected and actual current publication; direct independently authored negative source rows. Export: evidence_pack.json and co-delivered per-artifact binding manifest. Positive and contradictory/unavailable assertions individually mapped in per_predicate_proof_map.json; real MariaDB plus SQLite source/projection guards, independently derived values; no field-presence/mock-only/NULL-match admission. Sensitivity: new source-selection/admission/narrative probes as applicable; retained unchanged run-summary/event/semantic-field mutations from E015-E022, not repeated. Formal SATISFIED remains zero.',
        ],
        'MD-S063-R0028' => [
            'positive' => 'B19RunEvidenceMariaDbProofTest::test_real_sources_attempts_run_pack_anomaly_and_historical_resolution',
            'negative' => 'B19RunAuditPackProofTest::test_each_run_pack_question_preserves_authoritative_facts_and_unavailability',
            'basis' => 'E-MD-B19-A001-023: primary config snapshot ID plus manual SHA256 over retained resolved_config_json; legacy labels alone incomplete. Source: real source/pipeline -> primary run/event/config/lineage/artifact/publication rows, immutable v5 expected literals, verified selected and actual current publication; direct independently authored negative source rows. Export: evidence_pack.json and co-delivered per-artifact binding manifest. Positive and contradictory/unavailable assertions individually mapped in per_predicate_proof_map.json; real MariaDB plus SQLite source/projection guards, independently derived values; no field-presence/mock-only/NULL-match admission. Sensitivity: new source-selection/admission/narrative probes as applicable; retained unchanged run-summary/event/semantic-field mutations from E015-E022, not repeated. Formal SATISFIED remains zero.',
        ],
        'MD-S063-R0029' => [
            'positive' => 'B19RunEvidenceMariaDbProofTest::test_real_sources_attempts_run_pack_anomaly_and_historical_resolution',
            'negative' => 'B19RunAuditPackProofTest::test_each_run_pack_question_preserves_authoritative_facts_and_unavailability',
            'basis' => 'E-MD-B19-A001-023: V2 observation semantic binding equals preexisting independent expected literal, not allocation-bound compatibility identity. Source: real source/pipeline -> primary run/event/config/lineage/artifact/publication rows, immutable v5 expected literals, verified selected and actual current publication; direct independently authored negative source rows. Export: evidence_pack.json and co-delivered per-artifact binding manifest. Positive and contradictory/unavailable assertions individually mapped in per_predicate_proof_map.json; real MariaDB plus SQLite source/projection guards, independently derived values; no field-presence/mock-only/NULL-match admission. Sensitivity: new source-selection/admission/narrative probes as applicable; retained unchanged run-summary/event/semantic-field mutations from E015-E022, not repeated. Formal SATISFIED remains zero.',
        ],
        'MD-S063-R0030' => [
            'positive' => 'B19RunEvidenceMariaDbProofTest::test_real_sources_attempts_run_pack_anomaly_and_historical_resolution',
            'negative' => 'B19RunAuditPackProofTest::test_each_run_pack_question_preserves_authoritative_facts_and_unavailability',
            'basis' => 'E-MD-B19-A001-023: manual date+identity/calendar/status revision preimage hash; primary factor-set ID and independently expected semantic hash. Source: real source/pipeline -> primary run/event/config/lineage/artifact/publication rows, immutable v5 expected literals, verified selected and actual current publication; direct independently authored negative source rows. Export: evidence_pack.json and co-delivered per-artifact binding manifest. Positive and contradictory/unavailable assertions individually mapped in per_predicate_proof_map.json; real MariaDB plus SQLite source/projection guards, independently derived values; no field-presence/mock-only/NULL-match admission. Sensitivity: new source-selection/admission/narrative probes as applicable; retained unchanged run-summary/event/semantic-field mutations from E015-E022, not repeated. Formal SATISFIED remains zero.',
        ],
        'MD-S063-R0031' => [
            'positive' => 'B19RunEvidenceMariaDbProofTest::test_real_sources_attempts_run_pack_anomaly_and_historical_resolution',
            'negative' => 'B19RunAuditPackProofTest::test_each_run_pack_question_preserves_authoritative_facts_and_unavailability',
            'basis' => 'E-MD-B19-A001-023: frozen product/version/canonicalization/formula/config-registry/read-model versions equal accepted independent literal inputs. Source: real source/pipeline -> primary run/event/config/lineage/artifact/publication rows, immutable v5 expected literals, verified selected and actual current publication; direct independently authored negative source rows. Export: evidence_pack.json and co-delivered per-artifact binding manifest. Positive and contradictory/unavailable assertions individually mapped in per_predicate_proof_map.json; real MariaDB plus SQLite source/projection guards, independently derived values; no field-presence/mock-only/NULL-match admission. Sensitivity: new source-selection/admission/narrative probes as applicable; retained unchanged run-summary/event/semantic-field mutations from E015-E022, not repeated. Formal SATISFIED remains zero.',
        ],
        'MD-S063-R0032' => [
            'positive' => 'B19RunEvidenceMariaDbProofTest::test_real_sources_attempts_run_pack_anomaly_and_historical_resolution',
            'negative' => 'B19RunAuditPackProofTest::test_each_run_pack_question_preserves_authoritative_facts_and_unavailability',
            'basis' => 'E-MD-B19-A001-023: actual current pointer distinct from selected superseded publication; no stale current-readability claim. Source: real source/pipeline -> primary run/event/config/lineage/artifact/publication rows, immutable v5 expected literals, verified selected and actual current publication; direct independently authored negative source rows. Export: evidence_pack.json and co-delivered per-artifact binding manifest. Positive and contradictory/unavailable assertions individually mapped in per_predicate_proof_map.json; real MariaDB plus SQLite source/projection guards, independently derived values; no field-presence/mock-only/NULL-match admission. Sensitivity: real_current_pointer_hidden_by_history; real_historical_readability_invented; real_superseded_publication_safety_invented. Formal SATISFIED remains zero.',
        ],
        'MD-S063-R0033' => [
            'positive' => 'B19RunEvidenceMariaDbProofTest::test_real_sources_attempts_run_pack_anomaly_and_historical_resolution',
            'negative' => 'B19RunAuditPackProofTest::test_each_run_pack_question_preserves_authoritative_facts_and_unavailability',
            'basis' => 'E-MD-B19-A001-023: all seven coverage members have independent 1/1/0/1.0/0.98/PASS/MET positive and 4/3/1/0.75/0.98/legacy fixture negatives. Source: real source/pipeline -> primary run/event/config/lineage/artifact/publication rows, immutable v5 expected literals, verified selected and actual current publication; direct independently authored negative source rows. Export: evidence_pack.json and co-delivered per-artifact binding manifest. Positive and contradictory/unavailable assertions individually mapped in per_predicate_proof_map.json; real MariaDB plus SQLite source/projection guards, independently derived values; no field-presence/mock-only/NULL-match admission. Sensitivity: new source-selection/admission/narrative probes as applicable; retained unchanged run-summary/event/semantic-field mutations from E015-E022, not repeated. Formal SATISFIED remains zero.',
        ],
        'MD-S063-R0034' => [
            'positive' => 'B19RunEvidenceMariaDbProofTest::test_real_sources_attempts_run_pack_anomaly_and_historical_resolution',
            'negative' => 'B19RunAuditPackProofTest::test_each_run_pack_question_preserves_authoritative_facts_and_unavailability',
            'basis' => 'E-MD-B19-A001-023: legacy BLOCKED -> NOT_EVALUABLE plus explicit raw metadata, stored legacy row unchanged. Source: real source/pipeline -> primary run/event/config/lineage/artifact/publication rows, immutable v5 expected literals, verified selected and actual current publication; direct independently authored negative source rows. Export: evidence_pack.json and co-delivered per-artifact binding manifest. Positive and contradictory/unavailable assertions individually mapped in per_predicate_proof_map.json; real MariaDB plus SQLite source/projection guards, independently derived values; no field-presence/mock-only/NULL-match admission. Sensitivity: new source-selection/admission/narrative probes as applicable; retained unchanged run-summary/event/semantic-field mutations from E015-E022, not repeated. Formal SATISFIED remains zero.',
        ],
        'MD-S063-R0035' => [
            'positive' => 'B19RunEvidenceMariaDbProofTest::test_real_sources_attempts_run_pack_anomaly_and_historical_resolution',
            'negative' => 'B19RunAuditPackProofTest::test_missing_source_notes_are_enriched_only_from_own_event_and_manual_path_keeps_provenance',
            'basis' => 'E-MD-B19-A001-023: persisted columns/notes minimum source fields, own event enrichment when thin, explicit manual input path; logical route/source label not equated with provider. Source: real source/pipeline -> primary run/event/config/lineage/artifact/publication rows, immutable v5 expected literals, verified selected and actual current publication; direct independently authored negative source rows. Export: evidence_pack.json and co-delivered per-artifact binding manifest. Positive and contradictory/unavailable assertions individually mapped in per_predicate_proof_map.json; real MariaDB plus SQLite source/projection guards, independently derived values; no field-presence/mock-only/NULL-match admission. Sensitivity: real_conflicting_provider_admitted; real_invalid_source_admission_bypass; summary_materializes_false_attempt_artifact. Formal SATISFIED remains zero.',
        ],
        'MD-S063-R0036' => [
            'positive' => 'B19RunEvidenceMariaDbProofTest::test_real_sources_attempts_run_pack_anomaly_and_historical_resolution',
            'negative' => 'B19RunAuditPackProofTest::test_each_run_pack_question_preserves_authoritative_facts_and_unavailability',
            'basis' => 'E-MD-B19-A001-023: actual request/retry event -> optional telemetry file, never summary-only/invented attempts. Source: real source/pipeline -> primary run/event/config/lineage/artifact/publication rows, immutable v5 expected literals, verified selected and actual current publication; direct independently authored negative source rows. Export: evidence_pack.json and co-delivered per-artifact binding manifest. Positive and contradictory/unavailable assertions individually mapped in per_predicate_proof_map.json; real MariaDB plus SQLite source/projection guards, independently derived values; no field-presence/mock-only/NULL-match admission. Sensitivity: real_conflicting_provider_admitted; real_invalid_source_admission_bypass; summary_materializes_false_attempt_artifact. Formal SATISFIED remains zero.',
        ],
        'MD-S063-R0037' => [
            'positive' => 'B19RunEvidenceMariaDbProofTest::test_real_sources_attempts_run_pack_anomaly_and_historical_resolution',
            'negative' => 'B19RunAuditPackProofTest::test_each_run_pack_question_preserves_authoritative_facts_and_unavailability',
            'basis' => 'E-MD-B19-A001-023: held/failed and degraded summary own identity/date/reason/status/fallback/safety, no contradictory publication story. Source: real source/pipeline -> primary run/event/config/lineage/artifact/publication rows, immutable v5 expected literals, verified selected and actual current publication; direct independently authored negative source rows. Export: evidence_pack.json and co-delivered per-artifact binding manifest. Positive and contradictory/unavailable assertions individually mapped in per_predicate_proof_map.json; real MariaDB plus SQLite source/projection guards, independently derived values; no field-presence/mock-only/NULL-match admission. Sensitivity: anomaly_status_explanation_omitted; anomaly_reason_omitted; anomaly_fallback_readable_invention. Formal SATISFIED remains zero.',
        ],
        'MD-S063-R0038' => [
            'positive' => 'B19RunEvidenceMariaDbProofTest::test_real_sources_attempts_run_pack_anomaly_and_historical_resolution',
            'negative' => 'B19RunAuditPackProofTest::test_each_run_pack_question_preserves_authoritative_facts_and_unavailability',
            'basis' => 'E-MD-B19-A001-023: explicit requested date. Source: real source/pipeline -> primary run/event/config/lineage/artifact/publication rows, immutable v5 expected literals, verified selected and actual current publication; direct independently authored negative source rows. Export: evidence_pack.json and co-delivered per-artifact binding manifest. Positive and contradictory/unavailable assertions individually mapped in per_predicate_proof_map.json; real MariaDB plus SQLite source/projection guards, independently derived values; no field-presence/mock-only/NULL-match admission. Sensitivity: new source-selection/admission/narrative probes as applicable; retained unchanged run-summary/event/semantic-field mutations from E015-E022, not repeated. Formal SATISFIED remains zero.',
        ],
        'MD-S063-R0039' => [
            'positive' => 'B19RunEvidenceMariaDbProofTest::test_real_sources_attempts_run_pack_anomaly_and_historical_resolution',
            'negative' => 'B19RunAuditPackProofTest::test_each_run_pack_question_preserves_authoritative_facts_and_unavailability',
            'basis' => 'E-MD-B19-A001-023: own run ID and lineage to actual finalize execution. Source: real source/pipeline -> primary run/event/config/lineage/artifact/publication rows, immutable v5 expected literals, verified selected and actual current publication; direct independently authored negative source rows. Export: evidence_pack.json and co-delivered per-artifact binding manifest. Positive and contradictory/unavailable assertions individually mapped in per_predicate_proof_map.json; real MariaDB plus SQLite source/projection guards, independently derived values; no field-presence/mock-only/NULL-match admission. Sensitivity: new source-selection/admission/narrative probes as applicable; retained unchanged run-summary/event/semantic-field mutations from E015-E022, not repeated. Formal SATISFIED remains zero.',
        ],
        'MD-S063-R0040' => [
            'positive' => 'B19RunEvidenceMariaDbProofTest::test_real_sources_attempts_run_pack_anomaly_and_historical_resolution',
            'negative' => 'B19RunAuditPackProofTest::test_each_run_pack_question_preserves_authoritative_facts_and_unavailability',
            'basis' => 'E-MD-B19-A001-023: actual persisted terminal status. Source: real source/pipeline -> primary run/event/config/lineage/artifact/publication rows, immutable v5 expected literals, verified selected and actual current publication; direct independently authored negative source rows. Export: evidence_pack.json and co-delivered per-artifact binding manifest. Positive and contradictory/unavailable assertions individually mapped in per_predicate_proof_map.json; real MariaDB plus SQLite source/projection guards, independently derived values; no field-presence/mock-only/NULL-match admission. Sensitivity: new source-selection/admission/narrative probes as applicable; retained unchanged run-summary/event/semantic-field mutations from E015-E022, not repeated. Formal SATISFIED remains zero.',
        ],
        'MD-S063-R0041' => [
            'positive' => 'B19RunEvidenceMariaDbProofTest::test_real_sources_attempts_run_pack_anomaly_and_historical_resolution',
            'negative' => 'B19RunAuditPackProofTest::test_each_run_pack_question_preserves_authoritative_facts_and_unavailability',
            'basis' => 'E-MD-B19-A001-023: effective readable/fallback date or explicit unavailable result. Source: real source/pipeline -> primary run/event/config/lineage/artifact/publication rows, immutable v5 expected literals, verified selected and actual current publication; direct independently authored negative source rows. Export: evidence_pack.json and co-delivered per-artifact binding manifest. Positive and contradictory/unavailable assertions individually mapped in per_predicate_proof_map.json; real MariaDB plus SQLite source/projection guards, independently derived values; no field-presence/mock-only/NULL-match admission. Sensitivity: new source-selection/admission/narrative probes as applicable; retained unchanged run-summary/event/semantic-field mutations from E015-E022, not repeated. Formal SATISFIED remains zero.',
        ],
        'MD-S063-R0042' => [
            'positive' => 'B19RunEvidenceMariaDbProofTest::test_real_sources_attempts_run_pack_anomaly_and_historical_resolution',
            'negative' => 'B19RunAuditPackProofTest::test_each_run_pack_question_preserves_authoritative_facts_and_unavailability',
            'basis' => 'E-MD-B19-A001-023: requested date seal/readability checked against selected and current publication facts. Source: real source/pipeline -> primary run/event/config/lineage/artifact/publication rows, immutable v5 expected literals, verified selected and actual current publication; direct independently authored negative source rows. Export: evidence_pack.json and co-delivered per-artifact binding manifest. Positive and contradictory/unavailable assertions individually mapped in per_predicate_proof_map.json; real MariaDB plus SQLite source/projection guards, independently derived values; no field-presence/mock-only/NULL-match admission. Sensitivity: real_current_pointer_hidden_by_history; real_historical_readability_invented; real_superseded_publication_safety_invented. Formal SATISFIED remains zero.',
        ],
        'MD-S063-R0043' => [
            'positive' => 'B19RunEvidenceMariaDbProofTest::test_real_sources_attempts_run_pack_anomaly_and_historical_resolution',
            'negative' => 'B19RunAuditPackProofTest::test_each_run_pack_question_preserves_authoritative_facts_and_unavailability',
            'basis' => 'E-MD-B19-A001-023: actual current publication and selected historical publication are separately visible. Source: real source/pipeline -> primary run/event/config/lineage/artifact/publication rows, immutable v5 expected literals, verified selected and actual current publication; direct independently authored negative source rows. Export: evidence_pack.json and co-delivered per-artifact binding manifest. Positive and contradictory/unavailable assertions individually mapped in per_predicate_proof_map.json; real MariaDB plus SQLite source/projection guards, independently derived values; no field-presence/mock-only/NULL-match admission. Sensitivity: real_current_pointer_hidden_by_history; real_historical_readability_invented; real_superseded_publication_safety_invented. Formal SATISFIED remains zero.',
        ],
        'MD-S063-R0044' => [
            'positive' => 'B19RunEvidenceMariaDbProofTest::test_real_sources_attempts_run_pack_anomaly_and_historical_resolution',
            'negative' => 'B19RunAuditPackProofTest::test_each_run_pack_question_preserves_authoritative_facts_and_unavailability',
            'basis' => 'E-MD-B19-A001-023: real event trail exists and independently derived summary; missing trail cannot admit complete proof. Source: real source/pipeline -> primary run/event/config/lineage/artifact/publication rows, immutable v5 expected literals, verified selected and actual current publication; direct independently authored negative source rows. Export: evidence_pack.json and co-delivered per-artifact binding manifest. Positive and contradictory/unavailable assertions individually mapped in per_predicate_proof_map.json; real MariaDB plus SQLite source/projection guards, independently derived values; no field-presence/mock-only/NULL-match admission. Sensitivity: real_event_trail_admission_bypass; old_event_replaces_latest. Formal SATISFIED remains zero.',
        ],
        'MD-S063-R0045' => [
            'positive' => 'B19RunEvidenceMariaDbProofTest::test_real_sources_attempts_run_pack_anomaly_and_historical_resolution',
            'negative' => 'B19RunAuditPackProofTest::test_each_run_pack_question_preserves_authoritative_facts_and_unavailability',
            'basis' => 'E-MD-B19-A001-023: independent primary reason counts and own anomaly narrative. Source: real source/pipeline -> primary run/event/config/lineage/artifact/publication rows, immutable v5 expected literals, verified selected and actual current publication; direct independently authored negative source rows. Export: evidence_pack.json and co-delivered per-artifact binding manifest. Positive and contradictory/unavailable assertions individually mapped in per_predicate_proof_map.json; real MariaDB plus SQLite source/projection guards, independently derived values; no field-presence/mock-only/NULL-match admission. Sensitivity: new source-selection/admission/narrative probes as applicable; retained unchanged run-summary/event/semantic-field mutations from E015-E022, not repeated. Formal SATISFIED remains zero.',
        ],
        'MD-S063-R0046' => [
            'positive' => 'B19RunEvidenceMariaDbProofTest::test_real_sources_attempts_run_pack_anomaly_and_historical_resolution',
            'negative' => 'B19RunAuditPackProofTest::test_each_run_pack_question_preserves_authoritative_facts_and_unavailability',
            'basis' => 'E-MD-B19-A001-023: exact applicable full config identity, no reference-string substitution. Source: real source/pipeline -> primary run/event/config/lineage/artifact/publication rows, immutable v5 expected literals, verified selected and actual current publication; direct independently authored negative source rows. Export: evidence_pack.json and co-delivered per-artifact binding manifest. Positive and contradictory/unavailable assertions individually mapped in per_predicate_proof_map.json; real MariaDB plus SQLite source/projection guards, independently derived values; no field-presence/mock-only/NULL-match admission. Sensitivity: new source-selection/admission/narrative probes as applicable; retained unchanged run-summary/event/semantic-field mutations from E015-E022, not repeated. Formal SATISFIED remains zero.',
        ],
        'MD-S075-R0160' => [
            'positive' => 'B19RunEvidenceMariaDbProofTest::test_real_sources_attempts_run_pack_anomaly_and_historical_resolution',
            'negative' => 'B19RunEvidenceTelemetryProofTest::test_anomaly_narrative_keeps_run_facts_and_does_not_claim_unverified_safety',
            'basis' => 'E-MD-B19-A001-023: own run identity emitted, never omitted or replaced by another run. Source: own eod_runs + primary event/publication eligibility reason counts + verified selected publication/current pointer/fallback facts. Export: anomaly_report.md. Positive and contradictory/unavailable assertions individually mapped in per_predicate_proof_map.json; real MariaDB plus SQLite source/projection guards, independently derived values; no field-presence/mock-only/NULL-match admission. Sensitivity: anomaly_run_id_omitted. Formal SATISFIED remains zero.',
        ],
        'MD-S075-R0161' => [
            'positive' => 'B19RunEvidenceMariaDbProofTest::test_real_sources_attempts_run_pack_anomaly_and_historical_resolution',
            'negative' => 'B19RunEvidenceTelemetryProofTest::test_anomaly_narrative_keeps_run_facts_and_does_not_claim_unverified_safety',
            'basis' => 'E-MD-B19-A001-023: requested and effective dates remain distinct. Source: own eod_runs + primary event/publication eligibility reason counts + verified selected publication/current pointer/fallback facts. Export: anomaly_report.md. Positive and contradictory/unavailable assertions individually mapped in per_predicate_proof_map.json; real MariaDB plus SQLite source/projection guards, independently derived values; no field-presence/mock-only/NULL-match admission. Sensitivity: anomaly_requested_date_swap. Formal SATISFIED remains zero.',
        ],
        'MD-S075-R0162' => [
            'positive' => 'B19RunEvidenceMariaDbProofTest::test_real_sources_attempts_run_pack_anomaly_and_historical_resolution',
            'negative' => 'B19RunEvidenceTelemetryProofTest::test_anomaly_narrative_keeps_run_facts_and_does_not_claim_unverified_safety',
            'basis' => 'E-MD-B19-A001-023: dominant reason is derived from own scoped event/eligibility counts. Source: own eod_runs + primary event/publication eligibility reason counts + verified selected publication/current pointer/fallback facts. Export: anomaly_report.md. Positive and contradictory/unavailable assertions individually mapped in per_predicate_proof_map.json; real MariaDB plus SQLite source/projection guards, independently derived values; no field-presence/mock-only/NULL-match admission. Sensitivity: anomaly_reason_omitted. Formal SATISFIED remains zero.',
        ],
        'MD-S075-R0163' => [
            'positive' => 'B19RunEvidenceMariaDbProofTest::test_real_sources_attempts_run_pack_anomaly_and_historical_resolution',
            'negative' => 'B19RunEvidenceTelemetryProofTest::test_anomaly_narrative_keeps_run_facts_and_does_not_claim_unverified_safety',
            'basis' => 'E-MD-B19-A001-023: terminal status explanation retains own status and authoritative reason code/description or explicit unavailable description. Source: own eod_runs + primary event/publication eligibility reason counts + verified selected publication/current pointer/fallback facts. Export: anomaly_report.md. Positive and contradictory/unavailable assertions individually mapped in per_predicate_proof_map.json; real MariaDB plus SQLite source/projection guards, independently derived values; no field-presence/mock-only/NULL-match admission. Sensitivity: anomaly_status_promoted; anomaly_status_explanation_omitted. Formal SATISFIED remains zero.',
        ],
        'MD-S075-R0164' => [
            'positive' => 'B19RunEvidenceMariaDbProofTest::test_real_sources_attempts_run_pack_anomaly_and_historical_resolution',
            'negative' => 'B19RunEvidenceTelemetryProofTest::test_anomaly_narrative_keeps_run_facts_and_does_not_claim_unverified_safety',
            'basis' => 'E-MD-B19-A001-023: fallback effective date and verification availability stated explicitly, no invented sealed/readable fallback. Source: own eod_runs + primary event/publication eligibility reason counts + verified selected publication/current pointer/fallback facts. Export: anomaly_report.md. Positive and contradictory/unavailable assertions individually mapped in per_predicate_proof_map.json; real MariaDB plus SQLite source/projection guards, independently derived values; no field-presence/mock-only/NULL-match admission. Sensitivity: anomaly_fallback_readable_invention. Formal SATISFIED remains zero.',
        ],
        'MD-S075-R0165' => [
            'positive' => 'B19RunEvidenceMariaDbProofTest::test_real_sources_attempts_run_pack_anomaly_and_historical_resolution',
            'negative' => 'B19RunEvidenceTelemetryProofTest::test_anomaly_narrative_keeps_run_facts_and_does_not_claim_unverified_safety',
            'basis' => 'E-MD-B19-A001-023: current sealed publication safety requires selected current seal; no safety claim for held/failed or superseded publication. Source: own eod_runs + primary event/publication eligibility reason counts + verified selected publication/current pointer/fallback facts. Export: anomaly_report.md. Positive and contradictory/unavailable assertions individually mapped in per_predicate_proof_map.json; real MariaDB plus SQLite source/projection guards, independently derived values; no field-presence/mock-only/NULL-match admission. Sensitivity: anomaly_unsealed_safe_invention; real_superseded_publication_safety_invented. Formal SATISFIED remains zero.',
        ],
        'MD-S075-R0172' => [
            'positive' => 'B19RunEvidenceMariaDbProofTest::test_real_sources_attempts_run_pack_anomaly_and_historical_resolution',
            'negative' => 'B19RunEvidenceTelemetryProofTest::test_anomaly_narrative_keeps_run_facts_and_does_not_claim_unverified_safety',
            'basis' => 'E-MD-B19-A001-023: narrative facts agree with own run summary. Source: own eod_runs + primary event/publication eligibility reason counts + verified selected publication/current pointer/fallback facts. Export: anomaly_report.md. Positive and contradictory/unavailable assertions individually mapped in per_predicate_proof_map.json; real MariaDB plus SQLite source/projection guards, independently derived values; no field-presence/mock-only/NULL-match admission. Sensitivity: anomaly_status_promoted; anomaly_requested_date_swap. Formal SATISFIED remains zero.',
        ],
        'MD-S075-R0173' => [
            'positive' => 'B19RunEvidenceMariaDbProofTest::test_real_sources_attempts_run_pack_anomaly_and_historical_resolution',
            'negative' => 'B19RunEvidenceTelemetryProofTest::test_anomaly_narrative_keeps_run_facts_and_does_not_claim_unverified_safety',
            'basis' => 'E-MD-B19-A001-023: historical/current status, seal and publication resolution never tell conflicting stories. Source: own eod_runs + primary event/publication eligibility reason counts + verified selected publication/current pointer/fallback facts. Export: anomaly_report.md. Positive and contradictory/unavailable assertions individually mapped in per_predicate_proof_map.json; real MariaDB plus SQLite source/projection guards, independently derived values; no field-presence/mock-only/NULL-match admission. Sensitivity: real_historical_readability_invented; real_superseded_publication_safety_invented. Formal SATISFIED remains zero.',
        ],
        'MD-S075-R0176' => [
            'positive' => 'B19RunEvidenceTelemetryProofTest::test_each_telemetry_member_has_its_own_persisted_value',
            'negative' => 'B19RunEvidenceTelemetryProofTest::test_malformed_or_foreign_context_cannot_admit_complete_telemetry',
            'basis' => 'E-MD-B19-A001-023: independent source literal for event_id; exact primary event identity/time/type and own persisted acquisition field, never other run/stage or live defaults. Source: PublicApiEodBarsAdapter request/aggregate -> MarketDataPipelineService -> EodRunRepository appendEvent -> eod_run_events.event_payload_json and permitted own retained run context -> EodEvidenceRepository -> MarketDataEvidenceExportService. Export: source_attempt_telemetry.json.event_id. Positive and contradictory/unavailable assertions individually mapped in per_predicate_proof_map.json; real MariaDB plus SQLite source/projection guards, independently derived values; no field-presence/mock-only/NULL-match admission. Sensitivity: project_event_id. Formal SATISFIED remains zero.',
        ],
        'MD-S075-R0177' => [
            'positive' => 'B19RunEvidenceTelemetryProofTest::test_each_telemetry_member_has_its_own_persisted_value',
            'negative' => 'B19RunEvidenceTelemetryProofTest::test_malformed_or_foreign_context_cannot_admit_complete_telemetry',
            'basis' => 'E-MD-B19-A001-023: independent source literal for event_time; exact primary event identity/time/type and own persisted acquisition field, never other run/stage or live defaults. Source: PublicApiEodBarsAdapter request/aggregate -> MarketDataPipelineService -> EodRunRepository appendEvent -> eod_run_events.event_payload_json and permitted own retained run context -> EodEvidenceRepository -> MarketDataEvidenceExportService. Export: source_attempt_telemetry.json.event_time. Positive and contradictory/unavailable assertions individually mapped in per_predicate_proof_map.json; real MariaDB plus SQLite source/projection guards, independently derived values; no field-presence/mock-only/NULL-match admission. Sensitivity: project_event_time. Formal SATISFIED remains zero.',
        ],
        'MD-S075-R0178' => [
            'positive' => 'B19RunEvidenceTelemetryProofTest::test_each_telemetry_member_has_its_own_persisted_value',
            'negative' => 'B19RunEvidenceTelemetryProofTest::test_malformed_or_foreign_context_cannot_admit_complete_telemetry',
            'basis' => 'E-MD-B19-A001-023: independent source literal for event_type; exact primary event identity/time/type and own persisted acquisition field, never other run/stage or live defaults. Source: PublicApiEodBarsAdapter request/aggregate -> MarketDataPipelineService -> EodRunRepository appendEvent -> eod_run_events.event_payload_json and permitted own retained run context -> EodEvidenceRepository -> MarketDataEvidenceExportService. Export: source_attempt_telemetry.json.event_type. Positive and contradictory/unavailable assertions individually mapped in per_predicate_proof_map.json; real MariaDB plus SQLite source/projection guards, independently derived values; no field-presence/mock-only/NULL-match admission. Sensitivity: project_event_type. Formal SATISFIED remains zero.',
        ],
        'MD-S075-R0179' => [
            'positive' => 'B19RunEvidenceTelemetryProofTest::test_each_telemetry_member_has_its_own_persisted_value',
            'negative' => 'B19RunEvidenceTelemetryProofTest::test_malformed_or_foreign_context_cannot_admit_complete_telemetry',
            'basis' => 'E-MD-B19-A001-023: independent source literal for source_name; exact primary event identity/time/type and own persisted acquisition field, never other run/stage or live defaults. Source: PublicApiEodBarsAdapter request/aggregate -> MarketDataPipelineService -> EodRunRepository appendEvent -> eod_run_events.event_payload_json and permitted own retained run context -> EodEvidenceRepository -> MarketDataEvidenceExportService. Export: source_attempt_telemetry.json.source_name. Positive and contradictory/unavailable assertions individually mapped in per_predicate_proof_map.json; real MariaDB plus SQLite source/projection guards, independently derived values; no field-presence/mock-only/NULL-match admission. Sensitivity: project_source_name. Formal SATISFIED remains zero.',
        ],
        'MD-S075-R0180' => [
            'positive' => 'B19RunEvidenceTelemetryProofTest::test_each_telemetry_member_has_its_own_persisted_value',
            'negative' => 'B19RunEvidenceTelemetryProofTest::test_malformed_or_foreign_context_cannot_admit_complete_telemetry',
            'basis' => 'E-MD-B19-A001-023: independent source literal for provider; exact primary event identity/time/type and own persisted acquisition field, never other run/stage or live defaults. Source: PublicApiEodBarsAdapter request/aggregate -> MarketDataPipelineService -> EodRunRepository appendEvent -> eod_run_events.event_payload_json and permitted own retained run context -> EodEvidenceRepository -> MarketDataEvidenceExportService. Export: source_attempt_telemetry.json.provider. Positive and contradictory/unavailable assertions individually mapped in per_predicate_proof_map.json; real MariaDB plus SQLite source/projection guards, independently derived values; no field-presence/mock-only/NULL-match admission. Sensitivity: project_provider. Formal SATISFIED remains zero.',
        ],
        'MD-S075-R0181' => [
            'positive' => 'B19RunEvidenceTelemetryProofTest::test_each_telemetry_member_has_its_own_persisted_value',
            'negative' => 'B19RunEvidenceTelemetryProofTest::test_malformed_or_foreign_context_cannot_admit_complete_telemetry',
            'basis' => 'E-MD-B19-A001-023: independent source literal for timeout_seconds; exact primary event identity/time/type and own persisted acquisition field, never other run/stage or live defaults. Source: PublicApiEodBarsAdapter request/aggregate -> MarketDataPipelineService -> EodRunRepository appendEvent -> eod_run_events.event_payload_json and permitted own retained run context -> EodEvidenceRepository -> MarketDataEvidenceExportService. Export: source_attempt_telemetry.json.timeout_seconds. Positive and contradictory/unavailable assertions individually mapped in per_predicate_proof_map.json; real MariaDB plus SQLite source/projection guards, independently derived values; no field-presence/mock-only/NULL-match admission. Sensitivity: project_timeout_seconds. Formal SATISFIED remains zero.',
        ],
        'MD-S075-R0182' => [
            'positive' => 'B19RunEvidenceTelemetryProofTest::test_each_telemetry_member_has_its_own_persisted_value',
            'negative' => 'B19RunEvidenceTelemetryProofTest::test_malformed_or_foreign_context_cannot_admit_complete_telemetry',
            'basis' => 'E-MD-B19-A001-023: independent source literal for retry_max; exact primary event identity/time/type and own persisted acquisition field, never other run/stage or live defaults. Source: PublicApiEodBarsAdapter request/aggregate -> MarketDataPipelineService -> EodRunRepository appendEvent -> eod_run_events.event_payload_json and permitted own retained run context -> EodEvidenceRepository -> MarketDataEvidenceExportService. Export: source_attempt_telemetry.json.retry_max. Positive and contradictory/unavailable assertions individually mapped in per_predicate_proof_map.json; real MariaDB plus SQLite source/projection guards, independently derived values; no field-presence/mock-only/NULL-match admission. Sensitivity: project_retry_max. Formal SATISFIED remains zero.',
        ],
        'MD-S075-R0183' => [
            'positive' => 'B19RunEvidenceTelemetryProofTest::test_each_telemetry_member_has_its_own_persisted_value',
            'negative' => 'B19RunEvidenceTelemetryProofTest::test_missing_total_attempt_count_is_not_invented_from_bounded_rows',
            'basis' => 'E-MD-B19-A001-023: independent source literal for attempt_count; exact primary event identity/time/type and own persisted acquisition field, never other run/stage or live defaults. Source: PublicApiEodBarsAdapter request/aggregate -> MarketDataPipelineService -> EodRunRepository appendEvent -> eod_run_events.event_payload_json and permitted own retained run context -> EodEvidenceRepository -> MarketDataEvidenceExportService. Export: source_attempt_telemetry.json.attempt_count. Positive and contradictory/unavailable assertions individually mapped in per_predicate_proof_map.json; real MariaDB plus SQLite source/projection guards, independently derived values; no field-presence/mock-only/NULL-match admission. Sensitivity: project_attempt_count; bounded_rows_invent_total_count. Formal SATISFIED remains zero.',
        ],
        'MD-S075-R0184' => [
            'positive' => 'B19RunEvidenceTelemetryProofTest::test_each_telemetry_member_has_its_own_persisted_value',
            'negative' => 'B19RunEvidenceTelemetryProofTest::test_explicit_no_is_not_truthy_yes_and_explicit_null_is_not_note_repair',
            'basis' => 'E-MD-B19-A001-023: independent source literal for success_after_retry; exact primary event identity/time/type and own persisted acquisition field, never other run/stage or live defaults. Source: PublicApiEodBarsAdapter request/aggregate -> MarketDataPipelineService -> EodRunRepository appendEvent -> eod_run_events.event_payload_json and permitted own retained run context -> EodEvidenceRepository -> MarketDataEvidenceExportService. Export: source_attempt_telemetry.json.success_after_retry. Positive and contradictory/unavailable assertions individually mapped in per_predicate_proof_map.json; real MariaDB plus SQLite source/projection guards, independently derived values; no field-presence/mock-only/NULL-match admission. Sensitivity: project_success_after_retry; explicit_no_promoted_to_yes. Formal SATISFIED remains zero.',
        ],
        'MD-S075-R0185' => [
            'positive' => 'B19RunEvidenceTelemetryProofTest::test_each_telemetry_member_has_its_own_persisted_value',
            'negative' => 'B19RunEvidenceTelemetryProofTest::test_missing_final_http_status_cannot_hide_a_contradictory_known_last_response',
            'basis' => 'E-MD-B19-A001-023: independent source literal for final_http_status; exact primary event identity/time/type and own persisted acquisition field, never other run/stage or live defaults. Source: PublicApiEodBarsAdapter request/aggregate -> MarketDataPipelineService -> EodRunRepository appendEvent -> eod_run_events.event_payload_json and permitted own retained run context -> EodEvidenceRepository -> MarketDataEvidenceExportService. Export: source_attempt_telemetry.json.final_http_status. Positive and contradictory/unavailable assertions individually mapped in per_predicate_proof_map.json; real MariaDB plus SQLite source/projection guards, independently derived values; no field-presence/mock-only/NULL-match admission. Sensitivity: project_final_http_status; contradictory_final_http_status_admitted. Formal SATISFIED remains zero.',
        ],
        'MD-S075-R0186' => [
            'positive' => 'B19RunEvidenceTelemetryProofTest::test_each_telemetry_member_has_its_own_persisted_value',
            'negative' => 'B19RunEvidenceTelemetryProofTest::test_malformed_or_foreign_context_cannot_admit_complete_telemetry',
            'basis' => 'E-MD-B19-A001-023: independent source literal for final_reason_code; exact primary event identity/time/type and own persisted acquisition field, never other run/stage or live defaults. Source: PublicApiEodBarsAdapter request/aggregate -> MarketDataPipelineService -> EodRunRepository appendEvent -> eod_run_events.event_payload_json and permitted own retained run context -> EodEvidenceRepository -> MarketDataEvidenceExportService. Export: source_attempt_telemetry.json.final_reason_code. Positive and contradictory/unavailable assertions individually mapped in per_predicate_proof_map.json; real MariaDB plus SQLite source/projection guards, independently derived values; no field-presence/mock-only/NULL-match admission. Sensitivity: project_final_reason_code. Formal SATISFIED remains zero.',
        ],
        'MD-S075-R0187' => [
            'positive' => 'B19RunEvidenceTelemetryProofTest::test_each_telemetry_member_has_its_own_persisted_value',
            'negative' => 'B19RunEvidenceTelemetryProofTest::test_missing_capture_is_unavailable_not_replaced_with_event_time',
            'basis' => 'E-MD-B19-A001-023: independent source literal for captured_at; exact primary event identity/time/type and own persisted acquisition field, never other run/stage or live defaults. Source: PublicApiEodBarsAdapter request/aggregate -> MarketDataPipelineService -> EodRunRepository appendEvent -> eod_run_events.event_payload_json and permitted own retained run context -> EodEvidenceRepository -> MarketDataEvidenceExportService. Export: source_attempt_telemetry.json.captured_at. Positive and contradictory/unavailable assertions individually mapped in per_predicate_proof_map.json; real MariaDB plus SQLite source/projection guards, independently derived values; no field-presence/mock-only/NULL-match admission. Sensitivity: project_captured_at; missing_capture_event_time_guess; real_source_capture_dropped; invalid_calendar_timestamp_admitted. Formal SATISFIED remains zero.',
        ],
        'MD-S075-R0188' => [
            'positive' => 'B19RunEvidenceTelemetryProofTest::test_each_telemetry_member_has_its_own_persisted_value',
            'negative' => 'B19RunEvidenceTelemetryProofTest::test_malformed_attempts_never_become_invented_rows_or_complete_evidence',
            'basis' => 'E-MD-B19-A001-023: exact independently authored two attempt rows; real504->200 request/retry path separately proves producer persistence. Source: PublicApiEodBarsAdapter request/aggregate -> MarketDataPipelineService -> EodRunRepository appendEvent -> eod_run_events.event_payload_json and permitted own retained run context -> EodEvidenceRepository -> MarketDataEvidenceExportService. Export: source_attempt_telemetry.json.attempts. Positive and contradictory/unavailable assertions individually mapped in per_predicate_proof_map.json; real MariaDB plus SQLite source/projection guards, independently derived values; no field-presence/mock-only/NULL-match admission. Sensitivity: attempt_0_attempt_number; attempt_1_attempt_number; malformed_attempt_row_invention. Formal SATISFIED remains zero.',
        ],
        'MD-S075-R0189' => [
            'positive' => 'B19RunEvidenceTelemetryProofTest::test_each_telemetry_member_has_its_own_persisted_value',
            'negative' => 'B19RunEvidenceTelemetryProofTest::test_malformed_or_foreign_context_cannot_admit_complete_telemetry',
            'basis' => 'E-MD-B19-A001-023: exact independently authored own attempt value; real timeout/retry recovery or nonretry auth failure; NULL reason only independently successful response, never matching unavailable identities. Source: PublicApiEodBarsAdapter request/aggregate -> MarketDataPipelineService -> EodRunRepository appendEvent -> eod_run_events.event_payload_json and permitted own retained run context -> EodEvidenceRepository -> MarketDataEvidenceExportService. Export: source_attempt_telemetry.json.attempts[0].attempt_number. Positive and contradictory/unavailable assertions individually mapped in per_predicate_proof_map.json; real MariaDB plus SQLite source/projection guards, independently derived values; no field-presence/mock-only/NULL-match admission. Sensitivity: attempt_0_attempt_number. Formal SATISFIED remains zero.',
        ],
        'MD-S075-R0190' => [
            'positive' => 'B19RunEvidenceTelemetryProofTest::test_each_telemetry_member_has_its_own_persisted_value',
            'negative' => 'B19RunEvidenceTelemetryProofTest::test_malformed_or_foreign_context_cannot_admit_complete_telemetry',
            'basis' => 'E-MD-B19-A001-023: exact independently authored own attempt value; real timeout/retry recovery or nonretry auth failure; NULL reason only independently successful response, never matching unavailable identities. Source: PublicApiEodBarsAdapter request/aggregate -> MarketDataPipelineService -> EodRunRepository appendEvent -> eod_run_events.event_payload_json and permitted own retained run context -> EodEvidenceRepository -> MarketDataEvidenceExportService. Export: source_attempt_telemetry.json.attempts[0].reason_code. Positive and contradictory/unavailable assertions individually mapped in per_predicate_proof_map.json; real MariaDB plus SQLite source/projection guards, independently derived values; no field-presence/mock-only/NULL-match admission. Sensitivity: attempt_0_reason_code. Formal SATISFIED remains zero.',
        ],
        'MD-S075-R0191' => [
            'positive' => 'B19RunEvidenceTelemetryProofTest::test_each_telemetry_member_has_its_own_persisted_value',
            'negative' => 'B19RunEvidenceTelemetryProofTest::test_malformed_or_foreign_context_cannot_admit_complete_telemetry',
            'basis' => 'E-MD-B19-A001-023: exact independently authored own attempt value; real timeout/retry recovery or nonretry auth failure; NULL reason only independently successful response, never matching unavailable identities. Source: PublicApiEodBarsAdapter request/aggregate -> MarketDataPipelineService -> EodRunRepository appendEvent -> eod_run_events.event_payload_json and permitted own retained run context -> EodEvidenceRepository -> MarketDataEvidenceExportService. Export: source_attempt_telemetry.json.attempts[0].http_status. Positive and contradictory/unavailable assertions individually mapped in per_predicate_proof_map.json; real MariaDB plus SQLite source/projection guards, independently derived values; no field-presence/mock-only/NULL-match admission. Sensitivity: attempt_0_http_status. Formal SATISFIED remains zero.',
        ],
        'MD-S075-R0192' => [
            'positive' => 'B19RunEvidenceTelemetryProofTest::test_each_telemetry_member_has_its_own_persisted_value',
            'negative' => 'B19RunEvidenceTelemetryProofTest::test_malformed_or_foreign_context_cannot_admit_complete_telemetry',
            'basis' => 'E-MD-B19-A001-023: exact independently authored own attempt value; real timeout/retry recovery or nonretry auth failure; NULL reason only independently successful response, never matching unavailable identities. Source: PublicApiEodBarsAdapter request/aggregate -> MarketDataPipelineService -> EodRunRepository appendEvent -> eod_run_events.event_payload_json and permitted own retained run context -> EodEvidenceRepository -> MarketDataEvidenceExportService. Export: source_attempt_telemetry.json.attempts[0].throttle_delay_ms. Positive and contradictory/unavailable assertions individually mapped in per_predicate_proof_map.json; real MariaDB plus SQLite source/projection guards, independently derived values; no field-presence/mock-only/NULL-match admission. Sensitivity: attempt_0_throttle_delay_ms. Formal SATISFIED remains zero.',
        ],
        'MD-S075-R0193' => [
            'positive' => 'B19RunEvidenceTelemetryProofTest::test_each_telemetry_member_has_its_own_persisted_value',
            'negative' => 'B19RunEvidenceTelemetryProofTest::test_malformed_or_foreign_context_cannot_admit_complete_telemetry',
            'basis' => 'E-MD-B19-A001-023: exact independently authored own attempt value; real timeout/retry recovery or nonretry auth failure; NULL reason only independently successful response, never matching unavailable identities. Source: PublicApiEodBarsAdapter request/aggregate -> MarketDataPipelineService -> EodRunRepository appendEvent -> eod_run_events.event_payload_json and permitted own retained run context -> EodEvidenceRepository -> MarketDataEvidenceExportService. Export: source_attempt_telemetry.json.attempts[0].backoff_delay_ms. Positive and contradictory/unavailable assertions individually mapped in per_predicate_proof_map.json; real MariaDB plus SQLite source/projection guards, independently derived values; no field-presence/mock-only/NULL-match admission. Sensitivity: attempt_0_backoff_delay_ms. Formal SATISFIED remains zero.',
        ],
        'MD-S075-R0194' => [
            'positive' => 'B19RunEvidenceTelemetryProofTest::test_each_telemetry_member_has_its_own_persisted_value',
            'negative' => 'B19RunEvidenceTelemetryProofTest::test_malformed_or_foreign_context_cannot_admit_complete_telemetry',
            'basis' => 'E-MD-B19-A001-023: exact independently authored own attempt value; real timeout/retry recovery or nonretry auth failure; NULL reason only independently successful response, never matching unavailable identities. Source: PublicApiEodBarsAdapter request/aggregate -> MarketDataPipelineService -> EodRunRepository appendEvent -> eod_run_events.event_payload_json and permitted own retained run context -> EodEvidenceRepository -> MarketDataEvidenceExportService. Export: source_attempt_telemetry.json.attempts[0].will_retry. Positive and contradictory/unavailable assertions individually mapped in per_predicate_proof_map.json; real MariaDB plus SQLite source/projection guards, independently derived values; no field-presence/mock-only/NULL-match admission. Sensitivity: attempt_0_will_retry. Formal SATISFIED remains zero.',
        ],
        'MD-S075-R0195' => [
            'positive' => 'B19RunEvidenceTelemetryProofTest::test_each_telemetry_member_has_its_own_persisted_value',
            'negative' => 'B19RunEvidenceTelemetryProofTest::test_malformed_or_foreign_context_cannot_admit_complete_telemetry',
            'basis' => 'E-MD-B19-A001-023: exact independently authored own attempt value; real timeout/retry recovery or nonretry auth failure; NULL reason only independently successful response, never matching unavailable identities. Source: PublicApiEodBarsAdapter request/aggregate -> MarketDataPipelineService -> EodRunRepository appendEvent -> eod_run_events.event_payload_json and permitted own retained run context -> EodEvidenceRepository -> MarketDataEvidenceExportService. Export: source_attempt_telemetry.json.attempts[1].attempt_number. Positive and contradictory/unavailable assertions individually mapped in per_predicate_proof_map.json; real MariaDB plus SQLite source/projection guards, independently derived values; no field-presence/mock-only/NULL-match admission. Sensitivity: attempt_1_attempt_number. Formal SATISFIED remains zero.',
        ],
        'MD-S075-R0196' => [
            'positive' => 'B19RunEvidenceTelemetryProofTest::test_each_telemetry_member_has_its_own_persisted_value',
            'negative' => 'B19RunEvidenceTelemetryProofTest::test_malformed_or_foreign_context_cannot_admit_complete_telemetry',
            'basis' => 'E-MD-B19-A001-023: exact independently authored own attempt value; real timeout/retry recovery or nonretry auth failure; NULL reason only independently successful response, never matching unavailable identities. Source: PublicApiEodBarsAdapter request/aggregate -> MarketDataPipelineService -> EodRunRepository appendEvent -> eod_run_events.event_payload_json and permitted own retained run context -> EodEvidenceRepository -> MarketDataEvidenceExportService. Export: source_attempt_telemetry.json.attempts[1].reason_code. Positive and contradictory/unavailable assertions individually mapped in per_predicate_proof_map.json; real MariaDB plus SQLite source/projection guards, independently derived values; no field-presence/mock-only/NULL-match admission. Sensitivity: attempt_1_reason_code. Formal SATISFIED remains zero.',
        ],
        'MD-S075-R0197' => [
            'positive' => 'B19RunEvidenceTelemetryProofTest::test_each_telemetry_member_has_its_own_persisted_value',
            'negative' => 'B19RunEvidenceTelemetryProofTest::test_malformed_or_foreign_context_cannot_admit_complete_telemetry',
            'basis' => 'E-MD-B19-A001-023: exact independently authored own attempt value; real timeout/retry recovery or nonretry auth failure; NULL reason only independently successful response, never matching unavailable identities. Source: PublicApiEodBarsAdapter request/aggregate -> MarketDataPipelineService -> EodRunRepository appendEvent -> eod_run_events.event_payload_json and permitted own retained run context -> EodEvidenceRepository -> MarketDataEvidenceExportService. Export: source_attempt_telemetry.json.attempts[1].http_status. Positive and contradictory/unavailable assertions individually mapped in per_predicate_proof_map.json; real MariaDB plus SQLite source/projection guards, independently derived values; no field-presence/mock-only/NULL-match admission. Sensitivity: attempt_1_http_status. Formal SATISFIED remains zero.',
        ],
        'MD-S075-R0198' => [
            'positive' => 'B19RunEvidenceTelemetryProofTest::test_each_telemetry_member_has_its_own_persisted_value',
            'negative' => 'B19RunEvidenceTelemetryProofTest::test_malformed_or_foreign_context_cannot_admit_complete_telemetry',
            'basis' => 'E-MD-B19-A001-023: exact independently authored own attempt value; real timeout/retry recovery or nonretry auth failure; NULL reason only independently successful response, never matching unavailable identities. Source: PublicApiEodBarsAdapter request/aggregate -> MarketDataPipelineService -> EodRunRepository appendEvent -> eod_run_events.event_payload_json and permitted own retained run context -> EodEvidenceRepository -> MarketDataEvidenceExportService. Export: source_attempt_telemetry.json.attempts[1].throttle_delay_ms. Positive and contradictory/unavailable assertions individually mapped in per_predicate_proof_map.json; real MariaDB plus SQLite source/projection guards, independently derived values; no field-presence/mock-only/NULL-match admission. Sensitivity: attempt_1_throttle_delay_ms. Formal SATISFIED remains zero.',
        ],
        'MD-S075-R0199' => [
            'positive' => 'B19RunEvidenceTelemetryProofTest::test_each_telemetry_member_has_its_own_persisted_value',
            'negative' => 'B19RunEvidenceTelemetryProofTest::test_malformed_or_foreign_context_cannot_admit_complete_telemetry',
            'basis' => 'E-MD-B19-A001-023: exact independently authored own attempt value; real timeout/retry recovery or nonretry auth failure; NULL reason only independently successful response, never matching unavailable identities. Source: PublicApiEodBarsAdapter request/aggregate -> MarketDataPipelineService -> EodRunRepository appendEvent -> eod_run_events.event_payload_json and permitted own retained run context -> EodEvidenceRepository -> MarketDataEvidenceExportService. Export: source_attempt_telemetry.json.attempts[1].backoff_delay_ms. Positive and contradictory/unavailable assertions individually mapped in per_predicate_proof_map.json; real MariaDB plus SQLite source/projection guards, independently derived values; no field-presence/mock-only/NULL-match admission. Sensitivity: attempt_1_backoff_delay_ms. Formal SATISFIED remains zero.',
        ],
        'MD-S075-R0200' => [
            'positive' => 'B19RunEvidenceTelemetryProofTest::test_each_telemetry_member_has_its_own_persisted_value',
            'negative' => 'B19RunEvidenceTelemetryProofTest::test_malformed_or_foreign_context_cannot_admit_complete_telemetry',
            'basis' => 'E-MD-B19-A001-023: exact independently authored own attempt value; real timeout/retry recovery or nonretry auth failure; NULL reason only independently successful response, never matching unavailable identities. Source: PublicApiEodBarsAdapter request/aggregate -> MarketDataPipelineService -> EodRunRepository appendEvent -> eod_run_events.event_payload_json and permitted own retained run context -> EodEvidenceRepository -> MarketDataEvidenceExportService. Export: source_attempt_telemetry.json.attempts[1].will_retry. Positive and contradictory/unavailable assertions individually mapped in per_predicate_proof_map.json; real MariaDB plus SQLite source/projection guards, independently derived values; no field-presence/mock-only/NULL-match admission. Sensitivity: attempt_1_will_retry. Formal SATISFIED remains zero.',
        ],
        'MD-S075-R0201' => [
            'positive' => 'B19RunEvidenceTelemetryProofTest::test_each_telemetry_member_has_its_own_persisted_value',
            'negative' => 'B19RunEvidenceTelemetryProofTest::test_own_import_event_wins_over_later_unrelated_stage_and_other_run',
            'basis' => 'E-MD-B19-A001-023: only own primary source event INGEST_BARS/current or retained IMPORT legacy stage, exact payload provenance; foreign run/later unrelated context cannot substitute. Source: PublicApiEodBarsAdapter request/aggregate -> MarketDataPipelineService -> EodRunRepository appendEvent -> eod_run_events.event_payload_json and permitted own retained run context -> EodEvidenceRepository -> MarketDataEvidenceExportService. Export: source_attempt_telemetry.json / primary append-only eod_run_events. Positive and contradictory/unavailable assertions individually mapped in per_predicate_proof_map.json; real MariaDB plus SQLite source/projection guards, independently derived values; no field-presence/mock-only/NULL-match admission. Sensitivity: foreign_run_replaces_source; unrelated_stage_replaces_source. Formal SATISFIED remains zero.',
        ],
        'MD-S075-R0202' => [
            'positive' => 'B19RunEvidenceTelemetryProofTest::test_each_telemetry_member_has_its_own_persisted_value',
            'negative' => 'B19RunEvidenceTelemetryProofTest::test_summary_without_attempt_rows_does_not_materialize_attempt_companion',
            'basis' => 'E-MD-B19-A001-023: only actual attempt-level event materializes artifact; absent/summary-only payload remains absent, unavailable context cannot complete admission. Source: PublicApiEodBarsAdapter request/aggregate -> MarketDataPipelineService -> EodRunRepository appendEvent -> eod_run_events.event_payload_json and permitted own retained run context -> EodEvidenceRepository -> MarketDataEvidenceExportService. Export: source_attempt_telemetry.json / primary append-only eod_run_events. Positive and contradictory/unavailable assertions individually mapped in per_predicate_proof_map.json; real MariaDB plus SQLite source/projection guards, independently derived values; no field-presence/mock-only/NULL-match admission. Sensitivity: summary_materializes_false_attempt_artifact. Formal SATISFIED remains zero.',
        ],
        'MD-S075-R0203' => [
            'positive' => 'B19RunEvidenceTelemetryProofTest::test_each_telemetry_member_has_its_own_persisted_value',
            'negative' => 'B19RunEvidenceTelemetryProofTest::test_explicit_no_is_not_truthy_yes_and_explicit_null_is_not_note_repair',
            'basis' => 'E-MD-B19-A001-023: explicit own retained run-context identity enrichment, no invented attempt rows or NULL result repair; enrichment provenance exposed. Source: PublicApiEodBarsAdapter request/aggregate -> MarketDataPipelineService -> EodRunRepository appendEvent -> eod_run_events.event_payload_json and permitted own retained run context -> EodEvidenceRepository -> MarketDataEvidenceExportService. Export: source_attempt_telemetry.json / primary append-only eod_run_events. Positive and contradictory/unavailable assertions individually mapped in per_predicate_proof_map.json; real MariaDB plus SQLite source/projection guards, independently derived values; no field-presence/mock-only/NULL-match admission. Sensitivity: null_result_repaired_from_notes; malformed_attempt_row_invention. Formal SATISFIED remains zero.',
        ],
        'MD-S075-R0204' => [
            'positive' => 'B19RunEvidenceTelemetryProofTest::test_each_telemetry_member_has_its_own_persisted_value',
            'negative' => 'B19RunEvidenceTelemetryProofTest::test_latest_own_attempt_event_is_bounded_and_complete_trail_is_preserved',
            'basis' => 'E-MD-B19-A001-023: one latest own event forms bounded companion; full authoritative trail byte-for-byte preserved and independently resolvable. Source: PublicApiEodBarsAdapter request/aggregate -> MarketDataPipelineService -> EodRunRepository appendEvent -> eod_run_events.event_payload_json and permitted own retained run context -> EodEvidenceRepository -> MarketDataEvidenceExportService. Export: source_attempt_telemetry.json / primary append-only eod_run_events. Positive and contradictory/unavailable assertions individually mapped in per_predicate_proof_map.json; real MariaDB plus SQLite source/projection guards, independently derived values; no field-presence/mock-only/NULL-match admission. Sensitivity: old_event_replaces_latest; foreign_run_replaces_source. Formal SATISFIED remains zero.',
        ],

        // E024: correction/impact and actual archive proof; R0324 explicitly HELD under D008.
        'MD-S062-R0006' => [
            'positive' => 'B19ArchivedExecutedEvidenceProofTest::test_each_archived_obligation_has_actual_independent_source_and_execution',
            'negative' => 'B19ArchivedExecutedEvidenceProofTest::test_missing_illustrative_or_contradictory_archives_are_not_admitted',
            'basis' => 'E-MD-B19-A001-024: Exact per-ID switch/dataset assertion over real actual archive, independent primary source rows/config preimage and immutable v5 expected semantic hashes; no illustrative example can substitute. 1. one normal readable success run. Source/consumer: accepted E022 replay and E023 normal/HELD actual artifacts with issued inventory hashes; new E024 actual correction and contract-test stdout -> independently checked five-class archive; final issue binds new files in official existing raw root. Sensitivity: seven archive negatives: illustrative label, missing class, foreign run ID, old/new hash swap, replay config swap, fabricated test stdout and unofficial path, plus seven clean sensitivity mutations. Per-ID dataset and exact raw map in E024_per_predicate_proof_map.json. Formal SATISFIED remains zero.',
        ],
        'MD-S062-R0007' => [
            'positive' => 'B19ArchivedExecutedEvidenceProofTest::test_each_archived_obligation_has_actual_independent_source_and_execution',
            'negative' => 'B19ArchivedExecutedEvidenceProofTest::test_missing_illustrative_or_contradictory_archives_are_not_admitted',
            'basis' => 'E-MD-B19-A001-024: Exact per-ID switch/dataset assertion over real actual archive, independent primary source rows/config preimage and immutable v5 expected semantic hashes; no illustrative example can substitute. 2. one held or failed requested-date run. Source/consumer: accepted E022 replay and E023 normal/HELD actual artifacts with issued inventory hashes; new E024 actual correction and contract-test stdout -> independently checked five-class archive; final issue binds new files in official existing raw root. Sensitivity: seven archive negatives: illustrative label, missing class, foreign run ID, old/new hash swap, replay config swap, fabricated test stdout and unofficial path, plus seven clean sensitivity mutations. Per-ID dataset and exact raw map in E024_per_predicate_proof_map.json. Formal SATISFIED remains zero.',
        ],
        'MD-S062-R0008' => [
            'positive' => 'B19ArchivedExecutedEvidenceProofTest::test_each_archived_obligation_has_actual_independent_source_and_execution',
            'negative' => 'B19ArchivedExecutedEvidenceProofTest::test_missing_illustrative_or_contradictory_archives_are_not_admitted',
            'basis' => 'E-MD-B19-A001-024: Exact per-ID switch/dataset assertion over real actual archive, independent primary source rows/config preimage and immutable v5 expected semantic hashes; no illustrative example can substitute. 3. one historical correction publication. Source/consumer: accepted E022 replay and E023 normal/HELD actual artifacts with issued inventory hashes; new E024 actual correction and contract-test stdout -> independently checked five-class archive; final issue binds new files in official existing raw root. Sensitivity: seven archive negatives: illustrative label, missing class, foreign run ID, old/new hash swap, replay config swap, fabricated test stdout and unofficial path, plus seven clean sensitivity mutations. Per-ID dataset and exact raw map in E024_per_predicate_proof_map.json. Formal SATISFIED remains zero.',
        ],
        'MD-S062-R0009' => [
            'positive' => 'B19ArchivedExecutedEvidenceProofTest::test_each_archived_obligation_has_actual_independent_source_and_execution',
            'negative' => 'B19ArchivedExecutedEvidenceProofTest::test_missing_illustrative_or_contradictory_archives_are_not_admitted',
            'basis' => 'E-MD-B19-A001-024: Exact per-ID switch/dataset assertion over real actual archive, independent primary source rows/config preimage and immutable v5 expected semantic hashes; no illustrative example can substitute. 4. one replay execution. Source/consumer: accepted E022 replay and E023 normal/HELD actual artifacts with issued inventory hashes; new E024 actual correction and contract-test stdout -> independently checked five-class archive; final issue binds new files in official existing raw root. Sensitivity: seven archive negatives: illustrative label, missing class, foreign run ID, old/new hash swap, replay config swap, fabricated test stdout and unofficial path, plus seven clean sensitivity mutations. Per-ID dataset and exact raw map in E024_per_predicate_proof_map.json. Formal SATISFIED remains zero.',
        ],
        'MD-S062-R0010' => [
            'positive' => 'B19ArchivedExecutedEvidenceProofTest::test_each_archived_obligation_has_actual_independent_source_and_execution',
            'negative' => 'B19ArchivedExecutedEvidenceProofTest::test_missing_illustrative_or_contradictory_archives_are_not_admitted',
            'basis' => 'E-MD-B19-A001-024: Exact per-ID switch/dataset assertion over real actual archive, independent primary source rows/config preimage and immutable v5 expected semantic hashes; no illustrative example can substitute. 5. one executed contract-test result. Source/consumer: accepted E022 replay and E023 normal/HELD actual artifacts with issued inventory hashes; new E024 actual correction and contract-test stdout -> independently checked five-class archive; final issue binds new files in official existing raw root. Sensitivity: seven archive negatives: illustrative label, missing class, foreign run ID, old/new hash swap, replay config swap, fabricated test stdout and unofficial path, plus seven clean sensitivity mutations. Per-ID dataset and exact raw map in E024_per_predicate_proof_map.json. Formal SATISFIED remains zero.',
        ],
        'MD-S062-R0011' => [
            'positive' => 'B19ArchivedExecutedEvidenceProofTest::test_each_archived_obligation_has_actual_independent_source_and_execution',
            'negative' => 'B19ArchivedExecutedEvidenceProofTest::test_missing_illustrative_or_contradictory_archives_are_not_admitted',
            'basis' => 'E-MD-B19-A001-024: Exact per-ID switch/dataset assertion over real actual archive, independent primary source rows/config preimage and immutable v5 expected semantic hashes; no illustrative example can substitute. Illustrative examples do not satisfy this requirement by themselves.. Source/consumer: accepted E022 replay and E023 normal/HELD actual artifacts with issued inventory hashes; new E024 actual correction and contract-test stdout -> independently checked five-class archive; final issue binds new files in official existing raw root. Sensitivity: seven archive negatives: illustrative label, missing class, foreign run ID, old/new hash swap, replay config swap, fabricated test stdout and unofficial path, plus seven clean sensitivity mutations. Per-ID dataset and exact raw map in E024_per_predicate_proof_map.json. Formal SATISFIED remains zero.',
        ],
        'MD-S062-R0013' => [
            'positive' => 'B19ArchivedExecutedEvidenceProofTest::test_each_archived_obligation_has_actual_independent_source_and_execution',
            'negative' => 'B19ArchivedExecutedEvidenceProofTest::test_missing_illustrative_or_contradictory_archives_are_not_admitted',
            'basis' => 'E-MD-B19-A001-024: Exact per-ID switch/dataset assertion over real actual archive, independent primary source rows/config preimage and immutable v5 expected semantic hashes; no illustrative example can substitute. - actual `run_summary.json`. Source/consumer: accepted E022 replay and E023 normal/HELD actual artifacts with issued inventory hashes; new E024 actual correction and contract-test stdout -> independently checked five-class archive; final issue binds new files in official existing raw root. Sensitivity: seven archive negatives: illustrative label, missing class, foreign run ID, old/new hash swap, replay config swap, fabricated test stdout and unofficial path, plus seven clean sensitivity mutations. Per-ID dataset and exact raw map in E024_per_predicate_proof_map.json. Formal SATISFIED remains zero.',
        ],
        'MD-S062-R0014' => [
            'positive' => 'B19ArchivedExecutedEvidenceProofTest::test_each_archived_obligation_has_actual_independent_source_and_execution',
            'negative' => 'B19ArchivedExecutedEvidenceProofTest::test_missing_illustrative_or_contradictory_archives_are_not_admitted',
            'basis' => 'E-MD-B19-A001-024: Exact per-ID switch/dataset assertion over real actual archive, independent primary source rows/config preimage and immutable v5 expected semantic hashes; no illustrative example can substitute. - actual `publication_manifest.json` or publication resolution artifact. Source/consumer: accepted E022 replay and E023 normal/HELD actual artifacts with issued inventory hashes; new E024 actual correction and contract-test stdout -> independently checked five-class archive; final issue binds new files in official existing raw root. Sensitivity: seven archive negatives: illustrative label, missing class, foreign run ID, old/new hash swap, replay config swap, fabricated test stdout and unofficial path, plus seven clean sensitivity mutations. Per-ID dataset and exact raw map in E024_per_predicate_proof_map.json. Formal SATISFIED remains zero.',
        ],
        'MD-S062-R0015' => [
            'positive' => 'B19ArchivedExecutedEvidenceProofTest::test_each_archived_obligation_has_actual_independent_source_and_execution',
            'negative' => 'B19ArchivedExecutedEvidenceProofTest::test_missing_illustrative_or_contradictory_archives_are_not_admitted',
            'basis' => 'E-MD-B19-A001-024: Exact per-ID switch/dataset assertion over real actual archive, independent primary source rows/config preimage and immutable v5 expected semantic hashes; no illustrative example can substitute. - actual hash values. Source/consumer: accepted E022 replay and E023 normal/HELD actual artifacts with issued inventory hashes; new E024 actual correction and contract-test stdout -> independently checked five-class archive; final issue binds new files in official existing raw root. Sensitivity: seven archive negatives: illustrative label, missing class, foreign run ID, old/new hash swap, replay config swap, fabricated test stdout and unofficial path, plus seven clean sensitivity mutations. Per-ID dataset and exact raw map in E024_per_predicate_proof_map.json. Formal SATISFIED remains zero.',
        ],
        'MD-S062-R0016' => [
            'positive' => 'B19ArchivedExecutedEvidenceProofTest::test_each_archived_obligation_has_actual_independent_source_and_execution',
            'negative' => 'B19ArchivedExecutedEvidenceProofTest::test_missing_illustrative_or_contradictory_archives_are_not_admitted',
            'basis' => 'E-MD-B19-A001-024: Exact per-ID switch/dataset assertion over real actual archive, independent primary source rows/config preimage and immutable v5 expected semantic hashes; no illustrative example can substitute. - actual `run_event_summary.json` or reference. Source/consumer: accepted E022 replay and E023 normal/HELD actual artifacts with issued inventory hashes; new E024 actual correction and contract-test stdout -> independently checked five-class archive; final issue binds new files in official existing raw root. Sensitivity: seven archive negatives: illustrative label, missing class, foreign run ID, old/new hash swap, replay config swap, fabricated test stdout and unofficial path, plus seven clean sensitivity mutations. Per-ID dataset and exact raw map in E024_per_predicate_proof_map.json. Formal SATISFIED remains zero.',
        ],
        'MD-S062-R0017' => [
            'positive' => 'B19ArchivedExecutedEvidenceProofTest::test_each_archived_obligation_has_actual_independent_source_and_execution',
            'negative' => 'B19ArchivedExecutedEvidenceProofTest::test_missing_illustrative_or_contradictory_archives_are_not_admitted',
            'basis' => 'E-MD-B19-A001-024: Exact per-ID switch/dataset assertion over real actual archive, independent primary source rows/config preimage and immutable v5 expected semantic hashes; no illustrative example can substitute. - actual config identity. Source/consumer: accepted E022 replay and E023 normal/HELD actual artifacts with issued inventory hashes; new E024 actual correction and contract-test stdout -> independently checked five-class archive; final issue binds new files in official existing raw root. Sensitivity: seven archive negatives: illustrative label, missing class, foreign run ID, old/new hash swap, replay config swap, fabricated test stdout and unofficial path, plus seven clean sensitivity mutations. Per-ID dataset and exact raw map in E024_per_predicate_proof_map.json. Formal SATISFIED remains zero.',
        ],
        'MD-S062-R0018' => [
            'positive' => 'B19ArchivedExecutedEvidenceProofTest::test_each_archived_obligation_has_actual_independent_source_and_execution',
            'negative' => 'B19ArchivedExecutedEvidenceProofTest::test_missing_illustrative_or_contradictory_archives_are_not_admitted',
            'basis' => 'E-MD-B19-A001-024: Exact per-ID switch/dataset assertion over real actual archive, independent primary source rows/config preimage and immutable v5 expected semantic hashes; no illustrative example can substitute. - actual timestamps. Source/consumer: accepted E022 replay and E023 normal/HELD actual artifacts with issued inventory hashes; new E024 actual correction and contract-test stdout -> independently checked five-class archive; final issue binds new files in official existing raw root. Sensitivity: seven archive negatives: illustrative label, missing class, foreign run ID, old/new hash swap, replay config swap, fabricated test stdout and unofficial path, plus seven clean sensitivity mutations. Per-ID dataset and exact raw map in E024_per_predicate_proof_map.json. Formal SATISFIED remains zero.',
        ],
        'MD-S062-R0020' => [
            'positive' => 'B19ArchivedExecutedEvidenceProofTest::test_each_archived_obligation_has_actual_independent_source_and_execution',
            'negative' => 'B19ArchivedExecutedEvidenceProofTest::test_missing_illustrative_or_contradictory_archives_are_not_admitted',
            'basis' => 'E-MD-B19-A001-024: Exact per-ID switch/dataset assertion over real actual archive, independent primary source rows/config preimage and immutable v5 expected semantic hashes; no illustrative example can substitute. - actual correction request reference. Source/consumer: accepted E022 replay and E023 normal/HELD actual artifacts with issued inventory hashes; new E024 actual correction and contract-test stdout -> independently checked five-class archive; final issue binds new files in official existing raw root. Sensitivity: seven archive negatives: illustrative label, missing class, foreign run ID, old/new hash swap, replay config swap, fabricated test stdout and unofficial path, plus seven clean sensitivity mutations. Per-ID dataset and exact raw map in E024_per_predicate_proof_map.json. Formal SATISFIED remains zero.',
        ],
        'MD-S062-R0021' => [
            'positive' => 'B19ArchivedExecutedEvidenceProofTest::test_each_archived_obligation_has_actual_independent_source_and_execution',
            'negative' => 'B19ArchivedExecutedEvidenceProofTest::test_missing_illustrative_or_contradictory_archives_are_not_admitted',
            'basis' => 'E-MD-B19-A001-024: Exact per-ID switch/dataset assertion over real actual archive, independent primary source rows/config preimage and immutable v5 expected semantic hashes; no illustrative example can substitute. - actual prior publication reference. Source/consumer: accepted E022 replay and E023 normal/HELD actual artifacts with issued inventory hashes; new E024 actual correction and contract-test stdout -> independently checked five-class archive; final issue binds new files in official existing raw root. Sensitivity: seven archive negatives: illustrative label, missing class, foreign run ID, old/new hash swap, replay config swap, fabricated test stdout and unofficial path, plus seven clean sensitivity mutations. Per-ID dataset and exact raw map in E024_per_predicate_proof_map.json. Formal SATISFIED remains zero.',
        ],
        'MD-S062-R0022' => [
            'positive' => 'B19ArchivedExecutedEvidenceProofTest::test_each_archived_obligation_has_actual_independent_source_and_execution',
            'negative' => 'B19ArchivedExecutedEvidenceProofTest::test_missing_illustrative_or_contradictory_archives_are_not_admitted',
            'basis' => 'E-MD-B19-A001-024: Exact per-ID switch/dataset assertion over real actual archive, independent primary source rows/config preimage and immutable v5 expected semantic hashes; no illustrative example can substitute. - actual new publication reference. Source/consumer: accepted E022 replay and E023 normal/HELD actual artifacts with issued inventory hashes; new E024 actual correction and contract-test stdout -> independently checked five-class archive; final issue binds new files in official existing raw root. Sensitivity: seven archive negatives: illustrative label, missing class, foreign run ID, old/new hash swap, replay config swap, fabricated test stdout and unofficial path, plus seven clean sensitivity mutations. Per-ID dataset and exact raw map in E024_per_predicate_proof_map.json. Formal SATISFIED remains zero.',
        ],
        'MD-S062-R0023' => [
            'positive' => 'B19ArchivedExecutedEvidenceProofTest::test_each_archived_obligation_has_actual_independent_source_and_execution',
            'negative' => 'B19ArchivedExecutedEvidenceProofTest::test_missing_illustrative_or_contradictory_archives_are_not_admitted',
            'basis' => 'E-MD-B19-A001-024: Exact per-ID switch/dataset assertion over real actual archive, independent primary source rows/config preimage and immutable v5 expected semantic hashes; no illustrative example can substitute. - actual correction diff artifact. Source/consumer: accepted E022 replay and E023 normal/HELD actual artifacts with issued inventory hashes; new E024 actual correction and contract-test stdout -> independently checked five-class archive; final issue binds new files in official existing raw root. Sensitivity: seven archive negatives: illustrative label, missing class, foreign run ID, old/new hash swap, replay config swap, fabricated test stdout and unofficial path, plus seven clean sensitivity mutations. Per-ID dataset and exact raw map in E024_per_predicate_proof_map.json. Formal SATISFIED remains zero.',
        ],
        'MD-S062-R0024' => [
            'positive' => 'B19ArchivedExecutedEvidenceProofTest::test_each_archived_obligation_has_actual_independent_source_and_execution',
            'negative' => 'B19ArchivedExecutedEvidenceProofTest::test_missing_illustrative_or_contradictory_archives_are_not_admitted',
            'basis' => 'E-MD-B19-A001-024: Exact per-ID switch/dataset assertion over real actual archive, independent primary source rows/config preimage and immutable v5 expected semantic hashes; no illustrative example can substitute. - actual old/new hash values. Source/consumer: accepted E022 replay and E023 normal/HELD actual artifacts with issued inventory hashes; new E024 actual correction and contract-test stdout -> independently checked five-class archive; final issue binds new files in official existing raw root. Sensitivity: seven archive negatives: illustrative label, missing class, foreign run ID, old/new hash swap, replay config swap, fabricated test stdout and unofficial path, plus seven clean sensitivity mutations. Per-ID dataset and exact raw map in E024_per_predicate_proof_map.json. Formal SATISFIED remains zero.',
        ],
        'MD-S062-R0025' => [
            'positive' => 'B19ArchivedExecutedEvidenceProofTest::test_each_archived_obligation_has_actual_independent_source_and_execution',
            'negative' => 'B19ArchivedExecutedEvidenceProofTest::test_missing_illustrative_or_contradictory_archives_are_not_admitted',
            'basis' => 'E-MD-B19-A001-024: Exact per-ID switch/dataset assertion over real actual archive, independent primary source rows/config preimage and immutable v5 expected semantic hashes; no illustrative example can substitute. - actual publication switch result. Source/consumer: accepted E022 replay and E023 normal/HELD actual artifacts with issued inventory hashes; new E024 actual correction and contract-test stdout -> independently checked five-class archive; final issue binds new files in official existing raw root. Sensitivity: seven archive negatives: illustrative label, missing class, foreign run ID, old/new hash swap, replay config swap, fabricated test stdout and unofficial path, plus seven clean sensitivity mutations. Per-ID dataset and exact raw map in E024_per_predicate_proof_map.json. Formal SATISFIED remains zero.',
        ],
        'MD-S062-R0027' => [
            'positive' => 'B19ArchivedExecutedEvidenceProofTest::test_each_archived_obligation_has_actual_independent_source_and_execution',
            'negative' => 'B19ArchivedExecutedEvidenceProofTest::test_missing_illustrative_or_contradictory_archives_are_not_admitted',
            'basis' => 'E-MD-B19-A001-024: Exact per-ID switch/dataset assertion over real actual archive, independent primary source rows/config preimage and immutable v5 expected semantic hashes; no illustrative example can substitute. - actual `replay_result.json` artifact. Source/consumer: accepted E022 replay and E023 normal/HELD actual artifacts with issued inventory hashes; new E024 actual correction and contract-test stdout -> independently checked five-class archive; final issue binds new files in official existing raw root. Sensitivity: seven archive negatives: illustrative label, missing class, foreign run ID, old/new hash swap, replay config swap, fabricated test stdout and unofficial path, plus seven clean sensitivity mutations. Per-ID dataset and exact raw map in E024_per_predicate_proof_map.json. Formal SATISFIED remains zero.',
        ],
        'MD-S062-R0028' => [
            'positive' => 'B19ArchivedExecutedEvidenceProofTest::test_each_archived_obligation_has_actual_independent_source_and_execution',
            'negative' => 'B19ArchivedExecutedEvidenceProofTest::test_missing_illustrative_or_contradictory_archives_are_not_admitted',
            'basis' => 'E-MD-B19-A001-024: Exact per-ID switch/dataset assertion over real actual archive, independent primary source rows/config preimage and immutable v5 expected semantic hashes; no illustrative example can substitute. - actual comparison result. Source/consumer: accepted E022 replay and E023 normal/HELD actual artifacts with issued inventory hashes; new E024 actual correction and contract-test stdout -> independently checked five-class archive; final issue binds new files in official existing raw root. Sensitivity: seven archive negatives: illustrative label, missing class, foreign run ID, old/new hash swap, replay config swap, fabricated test stdout and unofficial path, plus seven clean sensitivity mutations. Per-ID dataset and exact raw map in E024_per_predicate_proof_map.json. Formal SATISFIED remains zero.',
        ],
        'MD-S062-R0029' => [
            'positive' => 'B19ArchivedExecutedEvidenceProofTest::test_each_archived_obligation_has_actual_independent_source_and_execution',
            'negative' => 'B19ArchivedExecutedEvidenceProofTest::test_missing_illustrative_or_contradictory_archives_are_not_admitted',
            'basis' => 'E-MD-B19-A001-024: Exact per-ID switch/dataset assertion over real actual archive, independent primary source rows/config preimage and immutable v5 expected semantic hashes; no illustrative example can substitute. - actual config identity. Source/consumer: accepted E022 replay and E023 normal/HELD actual artifacts with issued inventory hashes; new E024 actual correction and contract-test stdout -> independently checked five-class archive; final issue binds new files in official existing raw root. Sensitivity: seven archive negatives: illustrative label, missing class, foreign run ID, old/new hash swap, replay config swap, fabricated test stdout and unofficial path, plus seven clean sensitivity mutations. Per-ID dataset and exact raw map in E024_per_predicate_proof_map.json. Formal SATISFIED remains zero.',
        ],
        'MD-S062-R0030' => [
            'positive' => 'B19ArchivedExecutedEvidenceProofTest::test_each_archived_obligation_has_actual_independent_source_and_execution',
            'negative' => 'B19ArchivedExecutedEvidenceProofTest::test_missing_illustrative_or_contradictory_archives_are_not_admitted',
            'basis' => 'E-MD-B19-A001-024: Exact per-ID switch/dataset assertion over real actual archive, independent primary source rows/config preimage and immutable v5 expected semantic hashes; no illustrative example can substitute. - actual hash outputs. Source/consumer: accepted E022 replay and E023 normal/HELD actual artifacts with issued inventory hashes; new E024 actual correction and contract-test stdout -> independently checked five-class archive; final issue binds new files in official existing raw root. Sensitivity: seven archive negatives: illustrative label, missing class, foreign run ID, old/new hash swap, replay config swap, fabricated test stdout and unofficial path, plus seven clean sensitivity mutations. Per-ID dataset and exact raw map in E024_per_predicate_proof_map.json. Formal SATISFIED remains zero.',
        ],
        'MD-S062-R0031' => [
            'positive' => 'B19ArchivedExecutedEvidenceProofTest::test_each_archived_obligation_has_actual_independent_source_and_execution',
            'negative' => 'B19ArchivedExecutedEvidenceProofTest::test_missing_illustrative_or_contradictory_archives_are_not_admitted',
            'basis' => 'E-MD-B19-A001-024: Exact per-ID switch/dataset assertion over real actual archive, independent primary source rows/config preimage and immutable v5 expected semantic hashes; no illustrative example can substitute. - actual mismatch or match summary. Source/consumer: accepted E022 replay and E023 normal/HELD actual artifacts with issued inventory hashes; new E024 actual correction and contract-test stdout -> independently checked five-class archive; final issue binds new files in official existing raw root. Sensitivity: seven archive negatives: illustrative label, missing class, foreign run ID, old/new hash swap, replay config swap, fabricated test stdout and unofficial path, plus seven clean sensitivity mutations. Per-ID dataset and exact raw map in E024_per_predicate_proof_map.json. Formal SATISFIED remains zero.',
        ],
        'MD-S062-R0033' => [
            'positive' => 'B19ArchivedExecutedEvidenceProofTest::test_each_archived_obligation_has_actual_independent_source_and_execution',
            'negative' => 'B19ArchivedExecutedEvidenceProofTest::test_missing_illustrative_or_contradictory_archives_are_not_admitted',
            'basis' => 'E-MD-B19-A001-024: Exact per-ID switch/dataset assertion over real actual archive, independent primary source rows/config preimage and immutable v5 expected semantic hashes; no illustrative example can substitute. - actual test ID. Source/consumer: accepted E022 replay and E023 normal/HELD actual artifacts with issued inventory hashes; new E024 actual correction and contract-test stdout -> independently checked five-class archive; final issue binds new files in official existing raw root. Sensitivity: seven archive negatives: illustrative label, missing class, foreign run ID, old/new hash swap, replay config swap, fabricated test stdout and unofficial path, plus seven clean sensitivity mutations. Per-ID dataset and exact raw map in E024_per_predicate_proof_map.json. Formal SATISFIED remains zero.',
        ],
        'MD-S062-R0034' => [
            'positive' => 'B19ArchivedExecutedEvidenceProofTest::test_each_archived_obligation_has_actual_independent_source_and_execution',
            'negative' => 'B19ArchivedExecutedEvidenceProofTest::test_missing_illustrative_or_contradictory_archives_are_not_admitted',
            'basis' => 'E-MD-B19-A001-024: Exact per-ID switch/dataset assertion over real actual archive, independent primary source rows/config preimage and immutable v5 expected semantic hashes; no illustrative example can substitute. - actual fixture family. Source/consumer: accepted E022 replay and E023 normal/HELD actual artifacts with issued inventory hashes; new E024 actual correction and contract-test stdout -> independently checked five-class archive; final issue binds new files in official existing raw root. Sensitivity: seven archive negatives: illustrative label, missing class, foreign run ID, old/new hash swap, replay config swap, fabricated test stdout and unofficial path, plus seven clean sensitivity mutations. Per-ID dataset and exact raw map in E024_per_predicate_proof_map.json. Formal SATISFIED remains zero.',
        ],
        'MD-S062-R0035' => [
            'positive' => 'B19ArchivedExecutedEvidenceProofTest::test_each_archived_obligation_has_actual_independent_source_and_execution',
            'negative' => 'B19ArchivedExecutedEvidenceProofTest::test_missing_illustrative_or_contradictory_archives_are_not_admitted',
            'basis' => 'E-MD-B19-A001-024: Exact per-ID switch/dataset assertion over real actual archive, independent primary source rows/config preimage and immutable v5 expected semantic hashes; no illustrative example can substitute. - actual pass/fail result. Source/consumer: accepted E022 replay and E023 normal/HELD actual artifacts with issued inventory hashes; new E024 actual correction and contract-test stdout -> independently checked five-class archive; final issue binds new files in official existing raw root. Sensitivity: seven archive negatives: illustrative label, missing class, foreign run ID, old/new hash swap, replay config swap, fabricated test stdout and unofficial path, plus seven clean sensitivity mutations. Per-ID dataset and exact raw map in E024_per_predicate_proof_map.json. Formal SATISFIED remains zero.',
        ],
        'MD-S062-R0036' => [
            'positive' => 'B19ArchivedExecutedEvidenceProofTest::test_each_archived_obligation_has_actual_independent_source_and_execution',
            'negative' => 'B19ArchivedExecutedEvidenceProofTest::test_missing_illustrative_or_contradictory_archives_are_not_admitted',
            'basis' => 'E-MD-B19-A001-024: Exact per-ID switch/dataset assertion over real actual archive, independent primary source rows/config preimage and immutable v5 expected semantic hashes; no illustrative example can substitute. - actual assertion evidence for one or more applicable layers. Source/consumer: accepted E022 replay and E023 normal/HELD actual artifacts with issued inventory hashes; new E024 actual correction and contract-test stdout -> independently checked five-class archive; final issue binds new files in official existing raw root. Sensitivity: seven archive negatives: illustrative label, missing class, foreign run ID, old/new hash swap, replay config swap, fabricated test stdout and unofficial path, plus seven clean sensitivity mutations. Per-ID dataset and exact raw map in E024_per_predicate_proof_map.json. Formal SATISFIED remains zero.',
        ],
        'MD-S062-R0038' => [
            'positive' => 'B19ArchivedExecutedEvidenceProofTest::test_each_archived_obligation_has_actual_independent_source_and_execution',
            'negative' => 'B19ArchivedExecutedEvidenceProofTest::test_missing_illustrative_or_contradictory_archives_are_not_admitted',
            'basis' => 'E-MD-B19-A001-024: Exact per-ID switch/dataset assertion over real actual archive, independent primary source rows/config preimage and immutable v5 expected semantic hashes; no illustrative example can substitute. 1. it contains real execution identifiers. Source/consumer: accepted E022 replay and E023 normal/HELD actual artifacts with issued inventory hashes; new E024 actual correction and contract-test stdout -> independently checked five-class archive; final issue binds new files in official existing raw root. Sensitivity: seven archive negatives: illustrative label, missing class, foreign run ID, old/new hash swap, replay config swap, fabricated test stdout and unofficial path, plus seven clean sensitivity mutations. Per-ID dataset and exact raw map in E024_per_predicate_proof_map.json. Formal SATISFIED remains zero.',
        ],
        'MD-S062-R0039' => [
            'positive' => 'B19ArchivedExecutedEvidenceProofTest::test_each_archived_obligation_has_actual_independent_source_and_execution',
            'negative' => 'B19ArchivedExecutedEvidenceProofTest::test_missing_illustrative_or_contradictory_archives_are_not_admitted',
            'basis' => 'E-MD-B19-A001-024: Exact per-ID switch/dataset assertion over real actual archive, independent primary source rows/config preimage and immutable v5 expected semantic hashes; no illustrative example can substitute. 2. it contains real produced values. Source/consumer: accepted E022 replay and E023 normal/HELD actual artifacts with issued inventory hashes; new E024 actual correction and contract-test stdout -> independently checked five-class archive; final issue binds new files in official existing raw root. Sensitivity: seven archive negatives: illustrative label, missing class, foreign run ID, old/new hash swap, replay config swap, fabricated test stdout and unofficial path, plus seven clean sensitivity mutations. Per-ID dataset and exact raw map in E024_per_predicate_proof_map.json. Formal SATISFIED remains zero.',
        ],
        'MD-S062-R0040' => [
            'positive' => 'B19ArchivedExecutedEvidenceProofTest::test_each_archived_obligation_has_actual_independent_source_and_execution',
            'negative' => 'B19ArchivedExecutedEvidenceProofTest::test_missing_illustrative_or_contradictory_archives_are_not_admitted',
            'basis' => 'E-MD-B19-A001-024: Exact per-ID switch/dataset assertion over real actual archive, independent primary source rows/config preimage and immutable v5 expected semantic hashes; no illustrative example can substitute. 3. it is traceable to an actual execution context. Source/consumer: accepted E022 replay and E023 normal/HELD actual artifacts with issued inventory hashes; new E024 actual correction and contract-test stdout -> independently checked five-class archive; final issue binds new files in official existing raw root. Sensitivity: seven archive negatives: illustrative label, missing class, foreign run ID, old/new hash swap, replay config swap, fabricated test stdout and unofficial path, plus seven clean sensitivity mutations. Per-ID dataset and exact raw map in E024_per_predicate_proof_map.json. Formal SATISFIED remains zero.',
        ],
        'MD-S062-R0041' => [
            'positive' => 'B19ArchivedExecutedEvidenceProofTest::test_each_archived_obligation_has_actual_independent_source_and_execution',
            'negative' => 'B19ArchivedExecutedEvidenceProofTest::test_missing_illustrative_or_contradictory_archives_are_not_admitted',
            'basis' => 'E-MD-B19-A001-024: Exact per-ID switch/dataset assertion over real actual archive, independent primary source rows/config preimage and immutable v5 expected semantic hashes; no illustrative example can substitute. 4. it is stored in the designated archived evidence area or equivalent official evidence repository. Source/consumer: accepted E022 replay and E023 normal/HELD actual artifacts with issued inventory hashes; new E024 actual correction and contract-test stdout -> independently checked five-class archive; final issue binds new files in official existing raw root. Sensitivity: seven archive negatives: illustrative label, missing class, foreign run ID, old/new hash swap, replay config swap, fabricated test stdout and unofficial path, plus seven clean sensitivity mutations. Per-ID dataset and exact raw map in E024_per_predicate_proof_map.json. Formal SATISFIED remains zero.',
        ],
        'MD-S062-R0042' => [
            'positive' => 'B19ArchivedExecutedEvidenceProofTest::test_each_archived_obligation_has_actual_independent_source_and_execution',
            'negative' => 'B19ArchivedExecutedEvidenceProofTest::test_missing_illustrative_or_contradictory_archives_are_not_admitted',
            'basis' => 'E-MD-B19-A001-024: Exact per-ID switch/dataset assertion over real actual archive, independent primary source rows/config preimage and immutable v5 expected semantic hashes; no illustrative example can substitute. At least one archived actual evidence bundle should be preserved for each major class above.. Source/consumer: accepted E022 replay and E023 normal/HELD actual artifacts with issued inventory hashes; new E024 actual correction and contract-test stdout -> independently checked five-class archive; final issue binds new files in official existing raw root. Sensitivity: seven archive negatives: illustrative label, missing class, foreign run ID, old/new hash swap, replay config swap, fabricated test stdout and unofficial path, plus seven clean sensitivity mutations. Per-ID dataset and exact raw map in E024_per_predicate_proof_map.json. Formal SATISFIED remains zero.',
        ],
        'MD-S062-R0043' => [
            'positive' => 'B19ArchivedExecutedEvidenceProofTest::test_each_archived_obligation_has_actual_independent_source_and_execution',
            'negative' => 'B19ArchivedExecutedEvidenceProofTest::test_missing_illustrative_or_contradictory_archives_are_not_admitted',
            'basis' => 'E-MD-B19-A001-024: Exact per-ID switch/dataset assertion over real actual archive, independent primary source rows/config preimage and immutable v5 expected semantic hashes; no illustrative example can substitute. If the platform evolves, additional archived bundles are encouraged, but these minimum classes should never drop to zero.. Source/consumer: accepted E022 replay and E023 normal/HELD actual artifacts with issued inventory hashes; new E024 actual correction and contract-test stdout -> independently checked five-class archive; final issue binds new files in official existing raw root. Sensitivity: seven archive negatives: illustrative label, missing class, foreign run ID, old/new hash swap, replay config swap, fabricated test stdout and unofficial path, plus seven clean sensitivity mutations. Per-ID dataset and exact raw map in E024_per_predicate_proof_map.json. Formal SATISFIED remains zero.',
        ],
        'MD-S062-R0061' => [
            'positive' => 'B19ArchivedExecutedEvidenceProofTest::test_each_archived_obligation_has_actual_independent_source_and_execution',
            'negative' => 'B19ArchivedExecutedEvidenceProofTest::test_missing_illustrative_or_contradictory_archives_are_not_admitted',
            'basis' => 'E-MD-B19-A001-024: Exact per-ID switch/dataset assertion over real actual archive, independent primary source rows/config preimage and immutable v5 expected semantic hashes; no illustrative example can substitute. If a platform claims proof-by-execution but stores only examples and no archived actual evidence bundles, the claim is overstated.. Source/consumer: accepted E022 replay and E023 normal/HELD actual artifacts with issued inventory hashes; new E024 actual correction and contract-test stdout -> independently checked five-class archive; final issue binds new files in official existing raw root. Sensitivity: seven archive negatives: illustrative label, missing class, foreign run ID, old/new hash swap, replay config swap, fabricated test stdout and unofficial path, plus seven clean sensitivity mutations. Per-ID dataset and exact raw map in E024_per_predicate_proof_map.json. Formal SATISFIED remains zero.',
        ],
        'MD-S063-R0057' => [
            'positive' => 'B19CorrectionEvidenceProofTest::test_individual_correction_obligation_from_primary_rows',
            'negative' => 'B19CorrectionMariaDbProofTest::test_real_changed_unchanged_failed_and_historical_correction',
            'basis' => 'E-MD-B19-A001-024: Exact independent primary source literals and three distinct hash preimages, actual MariaDB source105->106 approved correction plus no-op, failed and later107 supersession. Expected old semantic hashes are immutable preexisting v5 literals; new export hashes independently equal retained primary replacement columns before export, not values copied from exported JSON. controlled changed publication, old immutable rows preserved. Source/consumer: actual createRequest/approve -> real pipeline/reseal/current switch -> eod_dataset_corrections + explicit baseline/replacement eod_publications + own eod_runs/immutable manifests -> correction_evidence.json + artifact_manifest.json. Sensitivity: exact scalar/hash/approval/reason/supersession/current/historical-switch probes and complete missing-approval/lifecycle negatives, individually retained in E024_mutation_results.json. Per-ID dataset and exact raw map in E024_per_predicate_proof_map.json. Formal SATISFIED remains zero.',
        ],
        'MD-S063-R0059' => [
            'positive' => 'B19CorrectionEvidenceProofTest::test_individual_correction_obligation_from_primary_rows',
            'negative' => 'B19CorrectionMariaDbProofTest::test_real_changed_unchanged_failed_and_historical_correction',
            'basis' => 'E-MD-B19-A001-024: Exact independent primary source literals and three distinct hash preimages, actual MariaDB source105->106 approved correction plus no-op, failed and later107 supersession. Expected old semantic hashes are immutable preexisting v5 literals; new export hashes independently equal retained primary replacement columns before export, not values copied from exported JSON. correction_id. Source/consumer: actual createRequest/approve -> real pipeline/reseal/current switch -> eod_dataset_corrections + explicit baseline/replacement eod_publications + own eod_runs/immutable manifests -> correction_evidence.json + artifact_manifest.json. Sensitivity: exact scalar/hash/approval/reason/supersession/current/historical-switch probes and complete missing-approval/lifecycle negatives, individually retained in E024_mutation_results.json. Per-ID dataset and exact raw map in E024_per_predicate_proof_map.json. Formal SATISFIED remains zero.',
        ],
        'MD-S063-R0060' => [
            'positive' => 'B19CorrectionEvidenceProofTest::test_individual_correction_obligation_from_primary_rows',
            'negative' => 'B19CorrectionMariaDbProofTest::test_real_changed_unchanged_failed_and_historical_correction',
            'basis' => 'E-MD-B19-A001-024: Exact independent primary source literals and three distinct hash preimages, actual MariaDB source105->106 approved correction plus no-op, failed and later107 supersession. Expected old semantic hashes are immutable preexisting v5 literals; new export hashes independently equal retained primary replacement columns before export, not values copied from exported JSON. approval actor/time. Source/consumer: actual createRequest/approve -> real pipeline/reseal/current switch -> eod_dataset_corrections + explicit baseline/replacement eod_publications + own eod_runs/immutable manifests -> correction_evidence.json + artifact_manifest.json. Sensitivity: exact scalar/hash/approval/reason/supersession/current/historical-switch probes and complete missing-approval/lifecycle negatives, individually retained in E024_mutation_results.json. Per-ID dataset and exact raw map in E024_per_predicate_proof_map.json. Formal SATISFIED remains zero.',
        ],
        'MD-S063-R0061' => [
            'positive' => 'B19CorrectionEvidenceProofTest::test_individual_correction_obligation_from_primary_rows',
            'negative' => 'B19CorrectionMariaDbProofTest::test_real_changed_unchanged_failed_and_historical_correction',
            'basis' => 'E-MD-B19-A001-024: Exact independent primary source literals and three distinct hash preimages, actual MariaDB source105->106 approved correction plus no-op, failed and later107 supersession. Expected old semantic hashes are immutable preexisting v5 literals; new export hashes independently equal retained primary replacement columns before export, not values copied from exported JSON. target trade_date. Source/consumer: actual createRequest/approve -> real pipeline/reseal/current switch -> eod_dataset_corrections + explicit baseline/replacement eod_publications + own eod_runs/immutable manifests -> correction_evidence.json + artifact_manifest.json. Sensitivity: exact scalar/hash/approval/reason/supersession/current/historical-switch probes and complete missing-approval/lifecycle negatives, individually retained in E024_mutation_results.json. Per-ID dataset and exact raw map in E024_per_predicate_proof_map.json. Formal SATISFIED remains zero.',
        ],
        'MD-S063-R0062' => [
            'positive' => 'B19CorrectionEvidenceProofTest::test_individual_correction_obligation_from_primary_rows',
            'negative' => 'B19CorrectionMariaDbProofTest::test_real_changed_unchanged_failed_and_historical_correction',
            'basis' => 'E-MD-B19-A001-024: Exact independent primary source literals and three distinct hash preimages, actual MariaDB source105->106 approved correction plus no-op, failed and later107 supersession. Expected old semantic hashes are immutable preexisting v5 literals; new export hashes independently equal retained primary replacement columns before export, not values copied from exported JSON. prior_publication. Source/consumer: actual createRequest/approve -> real pipeline/reseal/current switch -> eod_dataset_corrections + explicit baseline/replacement eod_publications + own eod_runs/immutable manifests -> correction_evidence.json + artifact_manifest.json. Sensitivity: exact scalar/hash/approval/reason/supersession/current/historical-switch probes and complete missing-approval/lifecycle negatives, individually retained in E024_mutation_results.json. Per-ID dataset and exact raw map in E024_per_predicate_proof_map.json. Formal SATISFIED remains zero.',
        ],
        'MD-S063-R0063' => [
            'positive' => 'B19CorrectionEvidenceProofTest::test_individual_correction_obligation_from_primary_rows',
            'negative' => 'B19CorrectionMariaDbProofTest::test_real_changed_unchanged_failed_and_historical_correction',
            'basis' => 'E-MD-B19-A001-024: Exact independent primary source literals and three distinct hash preimages, actual MariaDB source105->106 approved correction plus no-op, failed and later107 supersession. Expected old semantic hashes are immutable preexisting v5 literals; new export hashes independently equal retained primary replacement columns before export, not values copied from exported JSON. new_run_id/candidate_run_id. Source/consumer: actual createRequest/approve -> real pipeline/reseal/current switch -> eod_dataset_corrections + explicit baseline/replacement eod_publications + own eod_runs/immutable manifests -> correction_evidence.json + artifact_manifest.json. Sensitivity: exact scalar/hash/approval/reason/supersession/current/historical-switch probes and complete missing-approval/lifecycle negatives, individually retained in E024_mutation_results.json. Per-ID dataset and exact raw map in E024_per_predicate_proof_map.json. Formal SATISFIED remains zero.',
        ],
        'MD-S063-R0064' => [
            'positive' => 'B19CorrectionEvidenceProofTest::test_individual_correction_obligation_from_primary_rows',
            'negative' => 'B19CorrectionMariaDbProofTest::test_real_changed_unchanged_failed_and_historical_correction',
            'basis' => 'E-MD-B19-A001-024: Exact independent primary source literals and three distinct hash preimages, actual MariaDB source105->106 approved correction plus no-op, failed and later107 supersession. Expected old semantic hashes are immutable preexisting v5 literals; new export hashes independently equal retained primary replacement columns before export, not values copied from exported JSON. old_hashes. Source/consumer: actual createRequest/approve -> real pipeline/reseal/current switch -> eod_dataset_corrections + explicit baseline/replacement eod_publications + own eod_runs/immutable manifests -> correction_evidence.json + artifact_manifest.json. Sensitivity: exact scalar/hash/approval/reason/supersession/current/historical-switch probes and complete missing-approval/lifecycle negatives, individually retained in E024_mutation_results.json. Per-ID dataset and exact raw map in E024_per_predicate_proof_map.json. Formal SATISFIED remains zero.',
        ],
        'MD-S063-R0065' => [
            'positive' => 'B19CorrectionEvidenceProofTest::test_individual_correction_obligation_from_primary_rows',
            'negative' => 'B19CorrectionMariaDbProofTest::test_real_changed_unchanged_failed_and_historical_correction',
            'basis' => 'E-MD-B19-A001-024: Exact independent primary source literals and three distinct hash preimages, actual MariaDB source105->106 approved correction plus no-op, failed and later107 supersession. Expected old semantic hashes are immutable preexisting v5 literals; new export hashes independently equal retained primary replacement columns before export, not values copied from exported JSON. new_hashes. Source/consumer: actual createRequest/approve -> real pipeline/reseal/current switch -> eod_dataset_corrections + explicit baseline/replacement eod_publications + own eod_runs/immutable manifests -> correction_evidence.json + artifact_manifest.json. Sensitivity: exact scalar/hash/approval/reason/supersession/current/historical-switch probes and complete missing-approval/lifecycle negatives, individually retained in E024_mutation_results.json. Per-ID dataset and exact raw map in E024_per_predicate_proof_map.json. Formal SATISFIED remains zero.',
        ],
        'MD-S063-R0066' => [
            'positive' => 'B19CorrectionEvidenceProofTest::test_individual_correction_obligation_from_primary_rows',
            'negative' => 'B19CorrectionMariaDbProofTest::test_real_changed_unchanged_failed_and_historical_correction',
            'basis' => 'E-MD-B19-A001-024: Exact independent primary source literals and three distinct hash preimages, actual MariaDB source105->106 approved correction plus no-op, failed and later107 supersession. Expected old semantic hashes are immutable preexisting v5 literals; new export hashes independently equal retained primary replacement columns before export, not values copied from exported JSON. baseline/candidate historical publication proof. Source/consumer: actual createRequest/approve -> real pipeline/reseal/current switch -> eod_dataset_corrections + explicit baseline/replacement eod_publications + own eod_runs/immutable manifests -> correction_evidence.json + artifact_manifest.json. Sensitivity: exact scalar/hash/approval/reason/supersession/current/historical-switch probes and complete missing-approval/lifecycle negatives, individually retained in E024_mutation_results.json. Per-ID dataset and exact raw map in E024_per_predicate_proof_map.json. Formal SATISFIED remains zero.',
        ],
        'MD-S063-R0067' => [
            'positive' => 'B19CorrectionEvidenceProofTest::test_individual_correction_obligation_from_primary_rows',
            'negative' => 'B19CorrectionMariaDbProofTest::test_real_changed_unchanged_failed_and_historical_correction',
            'basis' => 'E-MD-B19-A001-024: Exact independent primary source literals and three distinct hash preimages, actual MariaDB source105->106 approved correction plus no-op, failed and later107 supersession. Expected old semantic hashes are immutable preexisting v5 literals; new export hashes independently equal retained primary replacement columns before export, not values copied from exported JSON. supersession.supersedes_publication_id. Source/consumer: actual createRequest/approve -> real pipeline/reseal/current switch -> eod_dataset_corrections + explicit baseline/replacement eod_publications + own eod_runs/immutable manifests -> correction_evidence.json + artifact_manifest.json. Sensitivity: exact scalar/hash/approval/reason/supersession/current/historical-switch probes and complete missing-approval/lifecycle negatives, individually retained in E024_mutation_results.json. Per-ID dataset and exact raw map in E024_per_predicate_proof_map.json. Formal SATISFIED remains zero.',
        ],
        'MD-S063-R0068' => [
            'positive' => 'B19CorrectionEvidenceProofTest::test_individual_correction_obligation_from_primary_rows',
            'negative' => 'B19CorrectionMariaDbProofTest::test_real_changed_unchanged_failed_and_historical_correction',
            'basis' => 'E-MD-B19-A001-024: Exact independent primary source literals and three distinct hash preimages, actual MariaDB source105->106 approved correction plus no-op, failed and later107 supersession. Expected old semantic hashes are immutable preexisting v5 literals; new export hashes independently equal retained primary replacement columns before export, not values copied from exported JSON. comparison_summary. Source/consumer: actual createRequest/approve -> real pipeline/reseal/current switch -> eod_dataset_corrections + explicit baseline/replacement eod_publications + own eod_runs/immutable manifests -> correction_evidence.json + artifact_manifest.json. Sensitivity: exact scalar/hash/approval/reason/supersession/current/historical-switch probes and complete missing-approval/lifecycle negatives, individually retained in E024_mutation_results.json. Per-ID dataset and exact raw map in E024_per_predicate_proof_map.json. Formal SATISFIED remains zero.',
        ],
        'MD-S063-R0069' => [
            'positive' => 'B19CorrectionEvidenceProofTest::test_individual_correction_obligation_from_primary_rows',
            'negative' => 'B19CorrectionMariaDbProofTest::test_real_changed_unchanged_failed_and_historical_correction',
            'basis' => 'E-MD-B19-A001-024: Exact independent primary source literals and three distinct hash preimages, actual MariaDB source105->106 approved correction plus no-op, failed and later107 supersession. Expected old semantic hashes are immutable preexisting v5 literals; new export hashes independently equal retained primary replacement columns before export, not values copied from exported JSON. historical publication_switch. Source/consumer: actual createRequest/approve -> real pipeline/reseal/current switch -> eod_dataset_corrections + explicit baseline/replacement eod_publications + own eod_runs/immutable manifests -> correction_evidence.json + artifact_manifest.json. Sensitivity: exact scalar/hash/approval/reason/supersession/current/historical-switch probes and complete missing-approval/lifecycle negatives, individually retained in E024_mutation_results.json. Per-ID dataset and exact raw map in E024_per_predicate_proof_map.json. Formal SATISFIED remains zero.',
        ],
        'MD-S063-R0070' => [
            'positive' => 'B19CorrectionEvidenceProofTest::test_individual_correction_obligation_from_primary_rows',
            'negative' => 'B19CorrectionMariaDbProofTest::test_real_changed_unchanged_failed_and_historical_correction',
            'basis' => 'E-MD-B19-A001-024: Exact independent primary source literals and three distinct hash preimages, actual MariaDB source105->106 approved correction plus no-op, failed and later107 supersession. Expected old semantic hashes are immutable preexisting v5 literals; new export hashes independently equal retained primary replacement columns before export, not values copied from exported JSON. correction_lifecycle.reason_code/reason_note. Source/consumer: actual createRequest/approve -> real pipeline/reseal/current switch -> eod_dataset_corrections + explicit baseline/replacement eod_publications + own eod_runs/immutable manifests -> correction_evidence.json + artifact_manifest.json. Sensitivity: exact scalar/hash/approval/reason/supersession/current/historical-switch probes and complete missing-approval/lifecycle negatives, individually retained in E024_mutation_results.json. Per-ID dataset and exact raw map in E024_per_predicate_proof_map.json. Formal SATISFIED remains zero.',
        ],
        'MD-S063-R0071' => [
            'positive' => 'B19CorrectionEvidenceProofTest::test_individual_correction_obligation_from_primary_rows',
            'negative' => 'B19CorrectionMariaDbProofTest::test_real_changed_unchanged_failed_and_historical_correction',
            'basis' => 'E-MD-B19-A001-024: Exact independent primary source literals and three distinct hash preimages, actual MariaDB source105->106 approved correction plus no-op, failed and later107 supersession. Expected old semantic hashes are immutable preexisting v5 literals; new export hashes independently equal retained primary replacement columns before export, not values copied from exported JSON. approval.approved_by. Source/consumer: actual createRequest/approve -> real pipeline/reseal/current switch -> eod_dataset_corrections + explicit baseline/replacement eod_publications + own eod_runs/immutable manifests -> correction_evidence.json + artifact_manifest.json. Sensitivity: exact scalar/hash/approval/reason/supersession/current/historical-switch probes and complete missing-approval/lifecycle negatives, individually retained in E024_mutation_results.json. Per-ID dataset and exact raw map in E024_per_predicate_proof_map.json. Formal SATISFIED remains zero.',
        ],
        'MD-S063-R0072' => [
            'positive' => 'B19CorrectionEvidenceProofTest::test_individual_correction_obligation_from_primary_rows',
            'negative' => 'B19CorrectionMariaDbProofTest::test_real_changed_unchanged_failed_and_historical_correction',
            'basis' => 'E-MD-B19-A001-024: Exact independent primary source literals and three distinct hash preimages, actual MariaDB source105->106 approved correction plus no-op, failed and later107 supersession. Expected old semantic hashes are immutable preexisting v5 literals; new export hashes independently equal retained primary replacement columns before export, not values copied from exported JSON. baseline_publication_id. Source/consumer: actual createRequest/approve -> real pipeline/reseal/current switch -> eod_dataset_corrections + explicit baseline/replacement eod_publications + own eod_runs/immutable manifests -> correction_evidence.json + artifact_manifest.json. Sensitivity: exact scalar/hash/approval/reason/supersession/current/historical-switch probes and complete missing-approval/lifecycle negatives, individually retained in E024_mutation_results.json. Per-ID dataset and exact raw map in E024_per_predicate_proof_map.json. Formal SATISFIED remains zero.',
        ],
        'MD-S063-R0073' => [
            'positive' => 'B19CorrectionEvidenceProofTest::test_individual_correction_obligation_from_primary_rows',
            'negative' => 'B19CorrectionMariaDbProofTest::test_real_changed_unchanged_failed_and_historical_correction',
            'basis' => 'E-MD-B19-A001-024: Exact independent primary source literals and three distinct hash preimages, actual MariaDB source105->106 approved correction plus no-op, failed and later107 supersession. Expected old semantic hashes are immutable preexisting v5 literals; new export hashes independently equal retained primary replacement columns before export, not values copied from exported JSON. candidate_run_id. Source/consumer: actual createRequest/approve -> real pipeline/reseal/current switch -> eod_dataset_corrections + explicit baseline/replacement eod_publications + own eod_runs/immutable manifests -> correction_evidence.json + artifact_manifest.json. Sensitivity: exact scalar/hash/approval/reason/supersession/current/historical-switch probes and complete missing-approval/lifecycle negatives, individually retained in E024_mutation_results.json. Per-ID dataset and exact raw map in E024_per_predicate_proof_map.json. Formal SATISFIED remains zero.',
        ],
        'MD-S063-R0074' => [
            'positive' => 'B19CorrectionEvidenceProofTest::test_individual_correction_obligation_from_primary_rows',
            'negative' => 'B19CorrectionMariaDbProofTest::test_real_changed_unchanged_failed_and_historical_correction',
            'basis' => 'E-MD-B19-A001-024: Exact independent primary source literals and three distinct hash preimages, actual MariaDB source105->106 approved correction plus no-op, failed and later107 supersession. Expected old semantic hashes are immutable preexisting v5 literals; new export hashes independently equal retained primary replacement columns before export, not values copied from exported JSON. independent old/new three-member hash diff. Source/consumer: actual createRequest/approve -> real pipeline/reseal/current switch -> eod_dataset_corrections + explicit baseline/replacement eod_publications + own eod_runs/immutable manifests -> correction_evidence.json + artifact_manifest.json. Sensitivity: exact scalar/hash/approval/reason/supersession/current/historical-switch probes and complete missing-approval/lifecycle negatives, individually retained in E024_mutation_results.json. Per-ID dataset and exact raw map in E024_per_predicate_proof_map.json. Formal SATISFIED remains zero.',
        ],
        'MD-S063-R0075' => [
            'positive' => 'B19CorrectionEvidenceProofTest::test_individual_correction_obligation_from_primary_rows',
            'negative' => 'B19CorrectionMariaDbProofTest::test_real_changed_unchanged_failed_and_historical_correction',
            'basis' => 'E-MD-B19-A001-024: Exact independent primary source literals and three distinct hash preimages, actual MariaDB source105->106 approved correction plus no-op, failed and later107 supersession. Expected old semantic hashes are immutable preexisting v5 literals; new export hashes independently equal retained primary replacement columns before export, not values copied from exported JSON. old_hashes. Source/consumer: actual createRequest/approve -> real pipeline/reseal/current switch -> eod_dataset_corrections + explicit baseline/replacement eod_publications + own eod_runs/immutable manifests -> correction_evidence.json + artifact_manifest.json. Sensitivity: exact scalar/hash/approval/reason/supersession/current/historical-switch probes and complete missing-approval/lifecycle negatives, individually retained in E024_mutation_results.json. Per-ID dataset and exact raw map in E024_per_predicate_proof_map.json. Formal SATISFIED remains zero.',
        ],
        'MD-S063-R0076' => [
            'positive' => 'B19CorrectionEvidenceProofTest::test_individual_correction_obligation_from_primary_rows',
            'negative' => 'B19CorrectionMariaDbProofTest::test_real_changed_unchanged_failed_and_historical_correction',
            'basis' => 'E-MD-B19-A001-024: Exact independent primary source literals and three distinct hash preimages, actual MariaDB source105->106 approved correction plus no-op, failed and later107 supersession. Expected old semantic hashes are immutable preexisting v5 literals; new export hashes independently equal retained primary replacement columns before export, not values copied from exported JSON. new_hashes. Source/consumer: actual createRequest/approve -> real pipeline/reseal/current switch -> eod_dataset_corrections + explicit baseline/replacement eod_publications + own eod_runs/immutable manifests -> correction_evidence.json + artifact_manifest.json. Sensitivity: exact scalar/hash/approval/reason/supersession/current/historical-switch probes and complete missing-approval/lifecycle negatives, individually retained in E024_mutation_results.json. Per-ID dataset and exact raw map in E024_per_predicate_proof_map.json. Formal SATISFIED remains zero.',
        ],
        'MD-S063-R0077' => [
            'positive' => 'B19CorrectionEvidenceProofTest::test_individual_correction_obligation_from_primary_rows',
            'negative' => 'B19CorrectionMariaDbProofTest::test_real_changed_unchanged_failed_and_historical_correction',
            'basis' => 'E-MD-B19-A001-024: Exact independent primary source literals and three distinct hash preimages, actual MariaDB source105->106 approved correction plus no-op, failed and later107 supersession. Expected old semantic hashes are immutable preexisting v5 literals; new export hashes independently equal retained primary replacement columns before export, not values copied from exported JSON. historical switch distinct from current flags. Source/consumer: actual createRequest/approve -> real pipeline/reseal/current switch -> eod_dataset_corrections + explicit baseline/replacement eod_publications + own eod_runs/immutable manifests -> correction_evidence.json + artifact_manifest.json. Sensitivity: exact scalar/hash/approval/reason/supersession/current/historical-switch probes and complete missing-approval/lifecycle negatives, individually retained in E024_mutation_results.json. Per-ID dataset and exact raw map in E024_per_predicate_proof_map.json. Formal SATISFIED remains zero.',
        ],
        'MD-S063-R0078' => [
            'positive' => 'B19CorrectionEvidenceProofTest::test_individual_correction_obligation_from_primary_rows',
            'negative' => 'B19CorrectionMariaDbProofTest::test_real_changed_unchanged_failed_and_historical_correction',
            'basis' => 'E-MD-B19-A001-024: Exact independent primary source literals and three distinct hash preimages, actual MariaDB source105->106 approved correction plus no-op, failed and later107 supersession. Expected old semantic hashes are immutable preexisting v5 literals; new export hashes independently equal retained primary replacement columns before export, not values copied from exported JSON. actual current pointer, distinct from correction replacement after later supersession. Source/consumer: actual createRequest/approve -> real pipeline/reseal/current switch -> eod_dataset_corrections + explicit baseline/replacement eod_publications + own eod_runs/immutable manifests -> correction_evidence.json + artifact_manifest.json. Sensitivity: exact scalar/hash/approval/reason/supersession/current/historical-switch probes and complete missing-approval/lifecycle negatives, individually retained in E024_mutation_results.json. Per-ID dataset and exact raw map in E024_per_predicate_proof_map.json. Formal SATISFIED remains zero.',
        ],
        'MD-S063-R0079' => [
            'positive' => 'B19CorrectionEvidenceProofTest::test_individual_correction_obligation_from_primary_rows',
            'negative' => 'B19CorrectionMariaDbProofTest::test_real_changed_unchanged_failed_and_historical_correction',
            'basis' => 'E-MD-B19-A001-024: Exact independent primary source literals and three distinct hash preimages, actual MariaDB source105->106 approved correction plus no-op, failed and later107 supersession. Expected old semantic hashes are immutable preexisting v5 literals; new export hashes independently equal retained primary replacement columns before export, not values copied from exported JSON. old immutable publication still resolvable. Source/consumer: actual createRequest/approve -> real pipeline/reseal/current switch -> eod_dataset_corrections + explicit baseline/replacement eod_publications + own eod_runs/immutable manifests -> correction_evidence.json + artifact_manifest.json. Sensitivity: exact scalar/hash/approval/reason/supersession/current/historical-switch probes and complete missing-approval/lifecycle negatives, individually retained in E024_mutation_results.json. Per-ID dataset and exact raw map in E024_per_predicate_proof_map.json. Formal SATISFIED remains zero.',
        ],
        'MD-S075-R0208' => [
            'positive' => 'B19CorrectionEvidenceProofTest::test_individual_correction_obligation_from_primary_rows',
            'negative' => 'B19CorrectionMariaDbProofTest::test_real_changed_unchanged_failed_and_historical_correction',
            'basis' => 'E-MD-B19-A001-024: Exact independent primary source literals and three distinct hash preimages, actual MariaDB source105->106 approved correction plus no-op, failed and later107 supersession. Expected old semantic hashes are immutable preexisting v5 literals; new export hashes independently equal retained primary replacement columns before export, not values copied from exported JSON. correction_id. Source/consumer: actual createRequest/approve -> real pipeline/reseal/current switch -> eod_dataset_corrections + explicit baseline/replacement eod_publications + own eod_runs/immutable manifests -> correction_evidence.json + artifact_manifest.json. Sensitivity: exact scalar/hash/approval/reason/supersession/current/historical-switch probes and complete missing-approval/lifecycle negatives, individually retained in E024_mutation_results.json. Per-ID dataset and exact raw map in E024_per_predicate_proof_map.json. Formal SATISFIED remains zero.',
        ],
        'MD-S075-R0209' => [
            'positive' => 'B19CorrectionEvidenceProofTest::test_individual_correction_obligation_from_primary_rows',
            'negative' => 'B19CorrectionMariaDbProofTest::test_real_changed_unchanged_failed_and_historical_correction',
            'basis' => 'E-MD-B19-A001-024: Exact independent primary source literals and three distinct hash preimages, actual MariaDB source105->106 approved correction plus no-op, failed and later107 supersession. Expected old semantic hashes are immutable preexisting v5 literals; new export hashes independently equal retained primary replacement columns before export, not values copied from exported JSON. trade_date. Source/consumer: actual createRequest/approve -> real pipeline/reseal/current switch -> eod_dataset_corrections + explicit baseline/replacement eod_publications + own eod_runs/immutable manifests -> correction_evidence.json + artifact_manifest.json. Sensitivity: exact scalar/hash/approval/reason/supersession/current/historical-switch probes and complete missing-approval/lifecycle negatives, individually retained in E024_mutation_results.json. Per-ID dataset and exact raw map in E024_per_predicate_proof_map.json. Formal SATISFIED remains zero.',
        ],
        'MD-S075-R0210' => [
            'positive' => 'B19CorrectionEvidenceProofTest::test_individual_correction_obligation_from_primary_rows',
            'negative' => 'B19CorrectionMariaDbProofTest::test_real_changed_unchanged_failed_and_historical_correction',
            'basis' => 'E-MD-B19-A001-024: Exact independent primary source literals and three distinct hash preimages, actual MariaDB source105->106 approved correction plus no-op, failed and later107 supersession. Expected old semantic hashes are immutable preexisting v5 literals; new export hashes independently equal retained primary replacement columns before export, not values copied from exported JSON. prior_run_id. Source/consumer: actual createRequest/approve -> real pipeline/reseal/current switch -> eod_dataset_corrections + explicit baseline/replacement eod_publications + own eod_runs/immutable manifests -> correction_evidence.json + artifact_manifest.json. Sensitivity: exact scalar/hash/approval/reason/supersession/current/historical-switch probes and complete missing-approval/lifecycle negatives, individually retained in E024_mutation_results.json. Per-ID dataset and exact raw map in E024_per_predicate_proof_map.json. Formal SATISFIED remains zero.',
        ],
        'MD-S075-R0211' => [
            'positive' => 'B19CorrectionEvidenceProofTest::test_individual_correction_obligation_from_primary_rows',
            'negative' => 'B19CorrectionMariaDbProofTest::test_real_changed_unchanged_failed_and_historical_correction',
            'basis' => 'E-MD-B19-A001-024: Exact independent primary source literals and three distinct hash preimages, actual MariaDB source105->106 approved correction plus no-op, failed and later107 supersession. Expected old semantic hashes are immutable preexisting v5 literals; new export hashes independently equal retained primary replacement columns before export, not values copied from exported JSON. new_run_id. Source/consumer: actual createRequest/approve -> real pipeline/reseal/current switch -> eod_dataset_corrections + explicit baseline/replacement eod_publications + own eod_runs/immutable manifests -> correction_evidence.json + artifact_manifest.json. Sensitivity: exact scalar/hash/approval/reason/supersession/current/historical-switch probes and complete missing-approval/lifecycle negatives, individually retained in E024_mutation_results.json. Per-ID dataset and exact raw map in E024_per_predicate_proof_map.json. Formal SATISFIED remains zero.',
        ],
        'MD-S075-R0212' => [
            'positive' => 'B19CorrectionEvidenceProofTest::test_individual_correction_obligation_from_primary_rows',
            'negative' => 'B19CorrectionMariaDbProofTest::test_real_changed_unchanged_failed_and_historical_correction',
            'basis' => 'E-MD-B19-A001-024: Exact independent primary source literals and three distinct hash preimages, actual MariaDB source105->106 approved correction plus no-op, failed and later107 supersession. Expected old semantic hashes are immutable preexisting v5 literals; new export hashes independently equal retained primary replacement columns before export, not values copied from exported JSON. prior_publication_version. Source/consumer: actual createRequest/approve -> real pipeline/reseal/current switch -> eod_dataset_corrections + explicit baseline/replacement eod_publications + own eod_runs/immutable manifests -> correction_evidence.json + artifact_manifest.json. Sensitivity: exact scalar/hash/approval/reason/supersession/current/historical-switch probes and complete missing-approval/lifecycle negatives, individually retained in E024_mutation_results.json. Per-ID dataset and exact raw map in E024_per_predicate_proof_map.json. Formal SATISFIED remains zero.',
        ],
        'MD-S075-R0213' => [
            'positive' => 'B19CorrectionEvidenceProofTest::test_individual_correction_obligation_from_primary_rows',
            'negative' => 'B19CorrectionMariaDbProofTest::test_real_changed_unchanged_failed_and_historical_correction',
            'basis' => 'E-MD-B19-A001-024: Exact independent primary source literals and three distinct hash preimages, actual MariaDB source105->106 approved correction plus no-op, failed and later107 supersession. Expected old semantic hashes are immutable preexisting v5 literals; new export hashes independently equal retained primary replacement columns before export, not values copied from exported JSON. new_publication_version. Source/consumer: actual createRequest/approve -> real pipeline/reseal/current switch -> eod_dataset_corrections + explicit baseline/replacement eod_publications + own eod_runs/immutable manifests -> correction_evidence.json + artifact_manifest.json. Sensitivity: exact scalar/hash/approval/reason/supersession/current/historical-switch probes and complete missing-approval/lifecycle negatives, individually retained in E024_mutation_results.json. Per-ID dataset and exact raw map in E024_per_predicate_proof_map.json. Formal SATISFIED remains zero.',
        ],
        'MD-S075-R0214' => [
            'positive' => 'B19CorrectionEvidenceProofTest::test_individual_correction_obligation_from_primary_rows',
            'negative' => 'B19CorrectionMariaDbProofTest::test_real_changed_unchanged_failed_and_historical_correction',
            'basis' => 'E-MD-B19-A001-024: Exact independent primary source literals and three distinct hash preimages, actual MariaDB source105->106 approved correction plus no-op, failed and later107 supersession. Expected old semantic hashes are immutable preexisting v5 literals; new export hashes independently equal retained primary replacement columns before export, not values copied from exported JSON. old_hashes. Source/consumer: actual createRequest/approve -> real pipeline/reseal/current switch -> eod_dataset_corrections + explicit baseline/replacement eod_publications + own eod_runs/immutable manifests -> correction_evidence.json + artifact_manifest.json. Sensitivity: exact scalar/hash/approval/reason/supersession/current/historical-switch probes and complete missing-approval/lifecycle negatives, individually retained in E024_mutation_results.json. Per-ID dataset and exact raw map in E024_per_predicate_proof_map.json. Formal SATISFIED remains zero.',
        ],
        'MD-S075-R0215' => [
            'positive' => 'B19CorrectionEvidenceProofTest::test_individual_correction_obligation_from_primary_rows',
            'negative' => 'B19CorrectionMariaDbProofTest::test_real_changed_unchanged_failed_and_historical_correction',
            'basis' => 'E-MD-B19-A001-024: Exact independent primary source literals and three distinct hash preimages, actual MariaDB source105->106 approved correction plus no-op, failed and later107 supersession. Expected old semantic hashes are immutable preexisting v5 literals; new export hashes independently equal retained primary replacement columns before export, not values copied from exported JSON. old_hashes.bars_batch_hash. Source/consumer: actual createRequest/approve -> real pipeline/reseal/current switch -> eod_dataset_corrections + explicit baseline/replacement eod_publications + own eod_runs/immutable manifests -> correction_evidence.json + artifact_manifest.json. Sensitivity: exact scalar/hash/approval/reason/supersession/current/historical-switch probes and complete missing-approval/lifecycle negatives, individually retained in E024_mutation_results.json. Per-ID dataset and exact raw map in E024_per_predicate_proof_map.json. Formal SATISFIED remains zero.',
        ],
        'MD-S075-R0216' => [
            'positive' => 'B19CorrectionEvidenceProofTest::test_individual_correction_obligation_from_primary_rows',
            'negative' => 'B19CorrectionMariaDbProofTest::test_real_changed_unchanged_failed_and_historical_correction',
            'basis' => 'E-MD-B19-A001-024: Exact independent primary source literals and three distinct hash preimages, actual MariaDB source105->106 approved correction plus no-op, failed and later107 supersession. Expected old semantic hashes are immutable preexisting v5 literals; new export hashes independently equal retained primary replacement columns before export, not values copied from exported JSON. old_hashes.indicators_batch_hash. Source/consumer: actual createRequest/approve -> real pipeline/reseal/current switch -> eod_dataset_corrections + explicit baseline/replacement eod_publications + own eod_runs/immutable manifests -> correction_evidence.json + artifact_manifest.json. Sensitivity: exact scalar/hash/approval/reason/supersession/current/historical-switch probes and complete missing-approval/lifecycle negatives, individually retained in E024_mutation_results.json. Per-ID dataset and exact raw map in E024_per_predicate_proof_map.json. Formal SATISFIED remains zero.',
        ],
        'MD-S075-R0217' => [
            'positive' => 'B19CorrectionEvidenceProofTest::test_individual_correction_obligation_from_primary_rows',
            'negative' => 'B19CorrectionMariaDbProofTest::test_real_changed_unchanged_failed_and_historical_correction',
            'basis' => 'E-MD-B19-A001-024: Exact independent primary source literals and three distinct hash preimages, actual MariaDB source105->106 approved correction plus no-op, failed and later107 supersession. Expected old semantic hashes are immutable preexisting v5 literals; new export hashes independently equal retained primary replacement columns before export, not values copied from exported JSON. old_hashes.eligibility_batch_hash. Source/consumer: actual createRequest/approve -> real pipeline/reseal/current switch -> eod_dataset_corrections + explicit baseline/replacement eod_publications + own eod_runs/immutable manifests -> correction_evidence.json + artifact_manifest.json. Sensitivity: exact scalar/hash/approval/reason/supersession/current/historical-switch probes and complete missing-approval/lifecycle negatives, individually retained in E024_mutation_results.json. Per-ID dataset and exact raw map in E024_per_predicate_proof_map.json. Formal SATISFIED remains zero.',
        ],
        'MD-S075-R0218' => [
            'positive' => 'B19CorrectionEvidenceProofTest::test_individual_correction_obligation_from_primary_rows',
            'negative' => 'B19CorrectionMariaDbProofTest::test_real_changed_unchanged_failed_and_historical_correction',
            'basis' => 'E-MD-B19-A001-024: Exact independent primary source literals and three distinct hash preimages, actual MariaDB source105->106 approved correction plus no-op, failed and later107 supersession. Expected old semantic hashes are immutable preexisting v5 literals; new export hashes independently equal retained primary replacement columns before export, not values copied from exported JSON. new_hashes. Source/consumer: actual createRequest/approve -> real pipeline/reseal/current switch -> eod_dataset_corrections + explicit baseline/replacement eod_publications + own eod_runs/immutable manifests -> correction_evidence.json + artifact_manifest.json. Sensitivity: exact scalar/hash/approval/reason/supersession/current/historical-switch probes and complete missing-approval/lifecycle negatives, individually retained in E024_mutation_results.json. Per-ID dataset and exact raw map in E024_per_predicate_proof_map.json. Formal SATISFIED remains zero.',
        ],
        'MD-S075-R0219' => [
            'positive' => 'B19CorrectionEvidenceProofTest::test_individual_correction_obligation_from_primary_rows',
            'negative' => 'B19CorrectionMariaDbProofTest::test_real_changed_unchanged_failed_and_historical_correction',
            'basis' => 'E-MD-B19-A001-024: Exact independent primary source literals and three distinct hash preimages, actual MariaDB source105->106 approved correction plus no-op, failed and later107 supersession. Expected old semantic hashes are immutable preexisting v5 literals; new export hashes independently equal retained primary replacement columns before export, not values copied from exported JSON. new_hashes.bars_batch_hash. Source/consumer: actual createRequest/approve -> real pipeline/reseal/current switch -> eod_dataset_corrections + explicit baseline/replacement eod_publications + own eod_runs/immutable manifests -> correction_evidence.json + artifact_manifest.json. Sensitivity: exact scalar/hash/approval/reason/supersession/current/historical-switch probes and complete missing-approval/lifecycle negatives, individually retained in E024_mutation_results.json. Per-ID dataset and exact raw map in E024_per_predicate_proof_map.json. Formal SATISFIED remains zero.',
        ],
        'MD-S075-R0220' => [
            'positive' => 'B19CorrectionEvidenceProofTest::test_individual_correction_obligation_from_primary_rows',
            'negative' => 'B19CorrectionMariaDbProofTest::test_real_changed_unchanged_failed_and_historical_correction',
            'basis' => 'E-MD-B19-A001-024: Exact independent primary source literals and three distinct hash preimages, actual MariaDB source105->106 approved correction plus no-op, failed and later107 supersession. Expected old semantic hashes are immutable preexisting v5 literals; new export hashes independently equal retained primary replacement columns before export, not values copied from exported JSON. new_hashes.indicators_batch_hash. Source/consumer: actual createRequest/approve -> real pipeline/reseal/current switch -> eod_dataset_corrections + explicit baseline/replacement eod_publications + own eod_runs/immutable manifests -> correction_evidence.json + artifact_manifest.json. Sensitivity: exact scalar/hash/approval/reason/supersession/current/historical-switch probes and complete missing-approval/lifecycle negatives, individually retained in E024_mutation_results.json. Per-ID dataset and exact raw map in E024_per_predicate_proof_map.json. Formal SATISFIED remains zero.',
        ],
        'MD-S075-R0221' => [
            'positive' => 'B19CorrectionEvidenceProofTest::test_individual_correction_obligation_from_primary_rows',
            'negative' => 'B19CorrectionMariaDbProofTest::test_real_changed_unchanged_failed_and_historical_correction',
            'basis' => 'E-MD-B19-A001-024: Exact independent primary source literals and three distinct hash preimages, actual MariaDB source105->106 approved correction plus no-op, failed and later107 supersession. Expected old semantic hashes are immutable preexisting v5 literals; new export hashes independently equal retained primary replacement columns before export, not values copied from exported JSON. new_hashes.eligibility_batch_hash. Source/consumer: actual createRequest/approve -> real pipeline/reseal/current switch -> eod_dataset_corrections + explicit baseline/replacement eod_publications + own eod_runs/immutable manifests -> correction_evidence.json + artifact_manifest.json. Sensitivity: exact scalar/hash/approval/reason/supersession/current/historical-switch probes and complete missing-approval/lifecycle negatives, individually retained in E024_mutation_results.json. Per-ID dataset and exact raw map in E024_per_predicate_proof_map.json. Formal SATISFIED remains zero.',
        ],
        'MD-S075-R0222' => [
            'positive' => 'B19CorrectionEvidenceProofTest::test_individual_correction_obligation_from_primary_rows',
            'negative' => 'B19CorrectionMariaDbProofTest::test_real_changed_unchanged_failed_and_historical_correction',
            'basis' => 'E-MD-B19-A001-024: Exact independent primary source literals and three distinct hash preimages, actual MariaDB source105->106 approved correction plus no-op, failed and later107 supersession. Expected old semantic hashes are immutable preexisting v5 literals; new export hashes independently equal retained primary replacement columns before export, not values copied from exported JSON. publication_switch. Source/consumer: actual createRequest/approve -> real pipeline/reseal/current switch -> eod_dataset_corrections + explicit baseline/replacement eod_publications + own eod_runs/immutable manifests -> correction_evidence.json + artifact_manifest.json. Sensitivity: exact scalar/hash/approval/reason/supersession/current/historical-switch probes and complete missing-approval/lifecycle negatives, individually retained in E024_mutation_results.json. Per-ID dataset and exact raw map in E024_per_predicate_proof_map.json. Formal SATISFIED remains zero.',
        ],
        'MD-S075-R0223' => [
            'positive' => 'B19CorrectionEvidenceProofTest::test_individual_correction_obligation_from_primary_rows',
            'negative' => 'B19CorrectionMariaDbProofTest::test_real_changed_unchanged_failed_and_historical_correction',
            'basis' => 'E-MD-B19-A001-024: Exact independent primary source literals and three distinct hash preimages, actual MariaDB source105->106 approved correction plus no-op, failed and later107 supersession. Expected old semantic hashes are immutable preexisting v5 literals; new export hashes independently equal retained primary replacement columns before export, not values copied from exported JSON. prior_publication_is_current. Source/consumer: actual createRequest/approve -> real pipeline/reseal/current switch -> eod_dataset_corrections + explicit baseline/replacement eod_publications + own eod_runs/immutable manifests -> correction_evidence.json + artifact_manifest.json. Sensitivity: exact scalar/hash/approval/reason/supersession/current/historical-switch probes and complete missing-approval/lifecycle negatives, individually retained in E024_mutation_results.json. Per-ID dataset and exact raw map in E024_per_predicate_proof_map.json. Formal SATISFIED remains zero.',
        ],
        'MD-S075-R0224' => [
            'positive' => 'B19CorrectionEvidenceProofTest::test_individual_correction_obligation_from_primary_rows',
            'negative' => 'B19CorrectionMariaDbProofTest::test_real_changed_unchanged_failed_and_historical_correction',
            'basis' => 'E-MD-B19-A001-024: Exact independent primary source literals and three distinct hash preimages, actual MariaDB source105->106 approved correction plus no-op, failed and later107 supersession. Expected old semantic hashes are immutable preexisting v5 literals; new export hashes independently equal retained primary replacement columns before export, not values copied from exported JSON. new_publication_is_current. Source/consumer: actual createRequest/approve -> real pipeline/reseal/current switch -> eod_dataset_corrections + explicit baseline/replacement eod_publications + own eod_runs/immutable manifests -> correction_evidence.json + artifact_manifest.json. Sensitivity: exact scalar/hash/approval/reason/supersession/current/historical-switch probes and complete missing-approval/lifecycle negatives, individually retained in E024_mutation_results.json. Per-ID dataset and exact raw map in E024_per_predicate_proof_map.json. Formal SATISFIED remains zero.',
        ],
        'MD-S075-R0225' => [
            'positive' => 'B19CorrectionEvidenceProofTest::test_individual_correction_obligation_from_primary_rows',
            'negative' => 'B19CorrectionMariaDbProofTest::test_real_changed_unchanged_failed_and_historical_correction',
            'basis' => 'E-MD-B19-A001-024: Exact independent primary source literals and three distinct hash preimages, actual MariaDB source105->106 approved correction plus no-op, failed and later107 supersession. Expected old semantic hashes are immutable preexisting v5 literals; new export hashes independently equal retained primary replacement columns before export, not values copied from exported JSON. old/new coexistent immutable history. Source/consumer: actual createRequest/approve -> real pipeline/reseal/current switch -> eod_dataset_corrections + explicit baseline/replacement eod_publications + own eod_runs/immutable manifests -> correction_evidence.json + artifact_manifest.json. Sensitivity: exact scalar/hash/approval/reason/supersession/current/historical-switch probes and complete missing-approval/lifecycle negatives, individually retained in E024_mutation_results.json. Per-ID dataset and exact raw map in E024_per_predicate_proof_map.json. Formal SATISFIED remains zero.',
        ],
        'MD-S075-R0226' => [
            'positive' => 'B19CorrectionEvidenceProofTest::test_individual_correction_obligation_from_primary_rows',
            'negative' => 'B19CorrectionMariaDbProofTest::test_real_changed_unchanged_failed_and_historical_correction',
            'basis' => 'E-MD-B19-A001-024: Exact independent primary source literals and three distinct hash preimages, actual MariaDB source105->106 approved correction plus no-op, failed and later107 supersession. Expected old semantic hashes are immutable preexisting v5 literals; new export hashes independently equal retained primary replacement columns before export, not values copied from exported JSON. unchanged no switch. Source/consumer: actual createRequest/approve -> real pipeline/reseal/current switch -> eod_dataset_corrections + explicit baseline/replacement eod_publications + own eod_runs/immutable manifests -> correction_evidence.json + artifact_manifest.json. Sensitivity: exact scalar/hash/approval/reason/supersession/current/historical-switch probes and complete missing-approval/lifecycle negatives, individually retained in E024_mutation_results.json. Per-ID dataset and exact raw map in E024_per_predicate_proof_map.json. Formal SATISFIED remains zero.',
        ],
        'MD-S075-R0227' => [
            'positive' => 'B19CorrectionEvidenceProofTest::test_individual_correction_obligation_from_primary_rows',
            'negative' => 'B19CorrectionMariaDbProofTest::test_real_changed_unchanged_failed_and_historical_correction',
            'basis' => 'E-MD-B19-A001-024: Exact independent primary source literals and three distinct hash preimages, actual MariaDB source105->106 approved correction plus no-op, failed and later107 supersession. Expected old semantic hashes are immutable preexisting v5 literals; new export hashes independently equal retained primary replacement columns before export, not values copied from exported JSON. prior publication preservation. Source/consumer: actual createRequest/approve -> real pipeline/reseal/current switch -> eod_dataset_corrections + explicit baseline/replacement eod_publications + own eod_runs/immutable manifests -> correction_evidence.json + artifact_manifest.json. Sensitivity: exact scalar/hash/approval/reason/supersession/current/historical-switch probes and complete missing-approval/lifecycle negatives, individually retained in E024_mutation_results.json. Per-ID dataset and exact raw map in E024_per_predicate_proof_map.json. Formal SATISFIED remains zero.',
        ],
        'MD-S075-R0315' => [
            'positive' => 'B19CorrectionMariaDbProofTest::test_real_impact_modes_require_safe_publication_lifecycle',
            'negative' => 'BackfillLifecyclePublicationReprocessTest::test_lifecycle_publication_reprocess_auto_corrects_readable_affected_dates',
            'basis' => 'E-MD-B19-A001-024: real sealed readable replacement before valid replay; no raw provider body; independent fixture required. Source/consumer: actual import and private per-case lifecycle entry (explicit bounded seam; no mock producer), real promote/correction/reseal -> syncPublicationReprocessNotes -> primary run notes -> run evidence bounded impact projection. Sensitivity: actual correction unavailable and explicitly blocked date retain pointer; independent fixture root missing refused; existing unchanged lifecycle/executor guards and new source/persisted/export checks. Per-ID dataset and exact raw map in E024_per_predicate_proof_map.json. Formal SATISFIED remains zero.',
        ],
        'MD-S075-R0317' => [
            'positive' => 'B19CorrectionMariaDbProofTest::test_real_impact_modes_require_safe_publication_lifecycle',
            'negative' => 'BackfillLifecyclePublicationReprocessTest::test_lifecycle_publication_reprocess_auto_corrects_readable_affected_dates',
            'basis' => 'E-MD-B19-A001-024: bounded persisted impact summaries, no provider body/checkpoint/credential disclosure. Source/consumer: actual import and private per-case lifecycle entry (explicit bounded seam; no mock producer), real promote/correction/reseal -> syncPublicationReprocessNotes -> primary run notes -> run evidence bounded impact projection. Sensitivity: actual correction unavailable and explicitly blocked date retain pointer; independent fixture root missing refused; existing unchanged lifecycle/executor guards and new source/persisted/export checks. Per-ID dataset and exact raw map in E024_per_predicate_proof_map.json. Formal SATISFIED remains zero.',
        ],
        'MD-S075-R0319' => [
            'positive' => 'B19CorrectionMariaDbProofTest::test_real_impact_modes_require_safe_publication_lifecycle',
            'negative' => 'BackfillLifecyclePublicationReprocessTest::test_lifecycle_publication_reprocess_auto_corrects_readable_affected_dates',
            'basis' => 'E-MD-B19-A001-024: real non-readable promote and real approved readable correction; no pointer mutation on unavailable correction path. Source/consumer: actual import and private per-case lifecycle entry (explicit bounded seam; no mock producer), real promote/correction/reseal -> syncPublicationReprocessNotes -> primary run notes -> run evidence bounded impact projection. Sensitivity: actual correction unavailable and explicitly blocked date retain pointer; independent fixture root missing refused; existing unchanged lifecycle/executor guards and new source/persisted/export checks. Per-ID dataset and exact raw map in E024_per_predicate_proof_map.json. Formal SATISFIED remains zero.',
        ],
        'MD-S075-R0321' => [
            'positive' => 'B19CorrectionMariaDbProofTest::test_real_impact_modes_require_safe_publication_lifecycle',
            'negative' => 'BackfillLifecyclePublicationReprocessTest::test_lifecycle_publication_reprocess_auto_corrects_readable_affected_dates',
            'basis' => 'E-MD-B19-A001-024: AUTOMATED_NON_READABLE_DATES after actual sealed/readable promote. Source/consumer: actual import and private per-case lifecycle entry (explicit bounded seam; no mock producer), real promote/correction/reseal -> syncPublicationReprocessNotes -> primary run notes -> run evidence bounded impact projection. Sensitivity: actual correction unavailable and explicitly blocked date retain pointer; independent fixture root missing refused; existing unchanged lifecycle/executor guards and new source/persisted/export checks. Per-ID dataset and exact raw map in E024_per_predicate_proof_map.json. Formal SATISFIED remains zero.',
        ],
        'MD-S075-R0322' => [
            'positive' => 'B19CorrectionMariaDbProofTest::test_real_impact_modes_require_safe_publication_lifecycle',
            'negative' => 'BackfillLifecyclePublicationReprocessTest::test_lifecycle_publication_reprocess_auto_corrects_readable_affected_dates',
            'basis' => 'E-MD-B19-A001-024: AUTOMATED_READABLE_CORRECTION with actual approved correction ID and sealed current replacement. Source/consumer: actual import and private per-case lifecycle entry (explicit bounded seam; no mock producer), real promote/correction/reseal -> syncPublicationReprocessNotes -> primary run notes -> run evidence bounded impact projection. Sensitivity: actual correction unavailable and explicitly blocked date retain pointer; independent fixture root missing refused; existing unchanged lifecycle/executor guards and new source/persisted/export checks. Per-ID dataset and exact raw map in E024_per_predicate_proof_map.json. Formal SATISFIED remains zero.',
        ],
        'MD-S075-R0323' => [
            'positive' => 'B19CorrectionMariaDbProofTest::test_real_impact_modes_require_safe_publication_lifecycle',
            'negative' => 'BackfillLifecyclePublicationReprocessTest::test_lifecycle_publication_reprocess_auto_corrects_readable_affected_dates',
            'basis' => 'E-MD-B19-A001-024: BLOCKED_REQUIRES_CORRECTION explicit blocked date, plus FAILED unsafe correction path; pointer unchanged. Source/consumer: actual import and private per-case lifecycle entry (explicit bounded seam; no mock producer), real promote/correction/reseal -> syncPublicationReprocessNotes -> primary run notes -> run evidence bounded impact projection. Sensitivity: actual correction unavailable and explicitly blocked date retain pointer; independent fixture root missing refused; existing unchanged lifecycle/executor guards and new source/persisted/export checks. Per-ID dataset and exact raw map in E024_per_predicate_proof_map.json. Formal SATISFIED remains zero.',
        ],

        // E025: actual execution packs/admission/audit core,72 individually checked.
        'MD-S063-R0002' => [
            'positive' => 'B19ExecutedEvidencePredicateProofTest::test_each_predicate_uses_independent_execution_values',
            'negative' => 'B19ExecutedEvidencePredicateProofTest::test_each_predicate_detects_missing_or_substituted_evidence',
            'basis' => 'E-MD-B19-A001-025 dataset MD-S063-R0002: - one requested-date run Independent sources: primary eod_runs before export; actual one controlled provider observation succeeds; retained E023 eod_runs. Current real persistence/export and immutable E022-E024 primary captures. Exact observations and per-value negatives in E025_per_predicate_proof_map.json; runtime mutations and proof-refusal bypass restored exactly. Admission never proves output correctness/current governed verification. Formal SATISFIED zero.',
        ],
        'MD-S063-R0003' => [
            'positive' => 'B19ExecutedEvidencePredicateProofTest::test_each_predicate_uses_independent_execution_values',
            'negative' => 'B19ExecutedEvidencePredicateProofTest::test_each_predicate_detects_missing_or_substituted_evidence',
            'basis' => 'E-MD-B19-A001-025 dataset MD-S063-R0003: - one historical correction case Independent sources: E024 primary approved correction; E024 immutable primary baseline; E024 primary replacement. Current real persistence/export and immutable E022-E024 primary captures. Exact observations and per-value negatives in E025_per_predicate_proof_map.json; runtime mutations and proof-refusal bypass restored exactly. Admission never proves output correctness/current governed verification. Formal SATISFIED zero.',
        ],
        'MD-S063-R0004' => [
            'positive' => 'B19ExecutedEvidencePredicateProofTest::test_each_predicate_uses_independent_execution_values',
            'negative' => 'B19ExecutedEvidencePredicateProofTest::test_each_predicate_detects_missing_or_substituted_evidence',
            'basis' => 'E-MD-B19-A001-025 dataset MD-S063-R0004: - one replay mismatch case Independent sources: separately authored wrong eligibility hash before comparator; MISMATCH classification; independent expected fixture, not actual output; primary sealed publication. Current real persistence/export and immutable E022-E024 primary captures. Exact observations and per-value negatives in E025_per_predicate_proof_map.json; runtime mutations and proof-refusal bypass restored exactly. Admission never proves output correctness/current governed verification. Formal SATISFIED zero.',
        ],
        'MD-S063-R0005' => [
            'positive' => 'B19ExecutedEvidencePredicateProofTest::test_each_predicate_uses_independent_execution_values',
            'negative' => 'B19ExecutedEvidencePredicateProofTest::test_each_predicate_detects_missing_or_substituted_evidence',
            'basis' => 'E-MD-B19-A001-025 dataset MD-S063-R0005: This contract ensures that publication safety, determinism, correction behavior, and replay discrepancies can be explained without guesswork. Independent sources: primary eod_runs before export; actual one controlled provider observation succeeds; retained E023 eod_runs; E024 primary approved correction; E024 immutable primary baseline; E024 primary replacement; separately authored wrong eligibility hash before comparator; MISMATCH classification; independent expected fixture, not actual output; primary sealed publication; primary prior publication bars_batch_hash; primary replacement publication bars_batch_hash; primary prior publication indicators_batch_hash; primary replacement publication indicators_batch_hash; primary prior publication eligibility_batch_hash; primary replacement publication eligibility_batch_hash. Current real persistence/export and immutable E022-E024 primary captures. Exact observations and per-value negatives in E025_per_predicate_proof_map.json; runtime mutations and proof-refusal bypass restored exactly. Admission never proves output correctness/current governed verification. Formal SATISFIED zero.',
        ],
        'MD-S063-R0007' => [
            'positive' => 'B19ExecutedEvidencePredicateProofTest::test_each_predicate_uses_independent_execution_values',
            'negative' => 'B19ExecutedEvidencePredicateProofTest::test_each_predicate_detects_missing_or_substituted_evidence',
            'basis' => 'E-MD-B19-A001-025 dataset MD-S063-R0007: - what happened Independent sources: primary eod_runs before export; actual one controlled provider observation succeeds; retained E023 eod_runs. Current real persistence/export and immutable E022-E024 primary captures. Exact observations and per-value negatives in E025_per_predicate_proof_map.json; runtime mutations and proof-refusal bypass restored exactly. Admission never proves output correctness/current governed verification. Formal SATISFIED zero.',
        ],
        'MD-S063-R0008' => [
            'positive' => 'B19ExecutedEvidencePredicateProofTest::test_each_predicate_uses_independent_execution_values',
            'negative' => 'B19ExecutedEvidencePredicateProofTest::test_each_predicate_detects_missing_or_substituted_evidence',
            'basis' => 'E-MD-B19-A001-025 dataset MD-S063-R0008: - what was readable or not readable Independent sources: actual sealed successful run; real held world, not falsely readable success. Current real persistence/export and immutable E022-E024 primary captures. Exact observations and per-value negatives in E025_per_predicate_proof_map.json; runtime mutations and proof-refusal bypass restored exactly. Admission never proves output correctness/current governed verification. Formal SATISFIED zero.',
        ],
        'MD-S063-R0009' => [
            'positive' => 'B19ExecutedEvidencePredicateProofTest::test_each_predicate_uses_independent_execution_values',
            'negative' => 'B19ExecutedEvidencePredicateProofTest::test_each_predicate_detects_missing_or_substituted_evidence',
            'basis' => 'E-MD-B19-A001-025 dataset MD-S063-R0009: - which publication state was current Independent sources: actual current pointer before correction; after real supersession selected baseline remains historical; historical selected publication cannot imply current readable pointer. Current real persistence/export and immutable E022-E024 primary captures. Exact observations and per-value negatives in E025_per_predicate_proof_map.json; runtime mutations and proof-refusal bypass restored exactly. Admission never proves output correctness/current governed verification. Formal SATISFIED zero.',
        ],
        'MD-S063-R0010' => [
            'positive' => 'B19ExecutedEvidencePredicateProofTest::test_each_predicate_uses_independent_execution_values',
            'negative' => 'B19ExecutedEvidencePredicateProofTest::test_each_predicate_detects_missing_or_substituted_evidence',
            'basis' => 'E-MD-B19-A001-025 dataset MD-S063-R0010: - which hashes were involved Independent sources: immutable independently versioned v5 expected publication manifest; primary prior publication bars_batch_hash; primary replacement publication bars_batch_hash; primary prior publication indicators_batch_hash; primary replacement publication indicators_batch_hash; primary prior publication eligibility_batch_hash; primary replacement publication eligibility_batch_hash. Current real persistence/export and immutable E022-E024 primary captures. Exact observations and per-value negatives in E025_per_predicate_proof_map.json; runtime mutations and proof-refusal bypass restored exactly. Admission never proves output correctness/current governed verification. Formal SATISFIED zero.',
        ],
        'MD-S063-R0011' => [
            'positive' => 'B19ExecutedEvidencePredicateProofTest::test_each_predicate_uses_independent_execution_values',
            'negative' => 'B19ExecutedEvidencePredicateProofTest::test_each_predicate_detects_missing_or_substituted_evidence',
            'basis' => 'E-MD-B19-A001-025 dataset MD-S063-R0011: - whether prior safe state was preserved Independent sources: immutable prior historical proof; old publication preserved independently of current pointer. Current real persistence/export and immutable E022-E024 primary captures. Exact observations and per-value negatives in E025_per_predicate_proof_map.json; runtime mutations and proof-refusal bypass restored exactly. Admission never proves output correctness/current governed verification. Formal SATISFIED zero.',
        ],
        'MD-S063-R0012' => [
            'positive' => 'B19ExecutedEvidencePredicateProofTest::test_each_predicate_uses_independent_execution_values',
            'negative' => 'B19ExecutedEvidencePredicateProofTest::test_each_predicate_detects_missing_or_substituted_evidence',
            'basis' => 'E-MD-B19-A001-025 dataset MD-S063-R0012: Every pack also binds the immutable source-observation manifest, temporal revision set, full config snapshot ID/hash, factor-set ID/hash, coherent price product, formula/registry/read-model versions, and requested/effective/readiness/freshness context. Legacy version labels or `config_identity` strings are compatibility metadata only and cannot satisfy reproducibility alone. Independent sources: retained primary canonical config preimage; immutable independently versioned v5 expected publication manifest; independently authored versioned fixture binding trade_date_requested; all applicable fixture bindings required and available, not merely matching NULL; independently authored versioned fixture binding trade_date_effective; independently authored versioned fixture binding observation_manifest_hash; independently authored versioned fixture binding config_registry_revision; independently authored versioned fixture binding factor_set_hash; independently authored versioned fixture binding price_product_code; independently authored versioned fixture binding price_product_version; independently authored versioned fixture binding canonicalization_version; independently authored versioned fixture binding formula_version; independently authored versioned fixture binding read_model_version; independently authored versioned fixture binding readiness_state; independently authored versioned fixture binding freshness_state; independently authored versioned fixture binding run_id; independently authored versioned fixture binding publication_id; independently authored versioned fixture binding publication_manifest_hash; independently authored versioned fixture binding config_snapshot_id; independently authored versioned fixture binding config_snapshot_hash; independently authored versioned fixture binding temporal_revision_set_hash; independently authored versioned fixture binding factor_set_id. Current real persistence/export and immutable E022-E024 primary captures. Exact observations and per-value negatives in E025_per_predicate_proof_map.json; runtime mutations and proof-refusal bypass restored exactly. Admission never proves output correctness/current governed verification. Formal SATISFIED zero.',
        ],
        'MD-S063-R0014' => [
            'positive' => 'B19ExecutedEvidencePredicateProofTest::test_each_predicate_uses_independent_execution_values',
            'negative' => 'B19ExecutedEvidencePredicateProofTest::test_each_predicate_detects_missing_or_substituted_evidence',
            'basis' => 'E-MD-B19-A001-025 dataset MD-S063-R0014: 1. requested-date run evidence pack Independent sources: primary eod_runs before export; actual one controlled provider observation succeeds; retained E023 eod_runs. Current real persistence/export and immutable E022-E024 primary captures. Exact observations and per-value negatives in E025_per_predicate_proof_map.json; runtime mutations and proof-refusal bypass restored exactly. Admission never proves output correctness/current governed verification. Formal SATISFIED zero.',
        ],
        'MD-S063-R0015' => [
            'positive' => 'B19ExecutedEvidencePredicateProofTest::test_each_predicate_uses_independent_execution_values',
            'negative' => 'B19ExecutedEvidencePredicateProofTest::test_each_predicate_detects_missing_or_substituted_evidence',
            'basis' => 'E-MD-B19-A001-025 dataset MD-S063-R0015: 2. historical correction evidence pack Independent sources: E024 primary approved correction; E024 immutable primary baseline; E024 primary replacement. Current real persistence/export and immutable E022-E024 primary captures. Exact observations and per-value negatives in E025_per_predicate_proof_map.json; runtime mutations and proof-refusal bypass restored exactly. Admission never proves output correctness/current governed verification. Formal SATISFIED zero.',
        ],
        'MD-S063-R0016' => [
            'positive' => 'B19ExecutedEvidencePredicateProofTest::test_each_predicate_uses_independent_execution_values',
            'negative' => 'B19ExecutedEvidencePredicateProofTest::test_each_predicate_detects_missing_or_substituted_evidence',
            'basis' => 'E-MD-B19-A001-025 dataset MD-S063-R0016: 3. replay result evidence pack Independent sources: separately authored wrong eligibility hash before comparator; MISMATCH classification; independent expected fixture, not actual output; primary sealed publication. Current real persistence/export and immutable E022-E024 primary captures. Exact observations and per-value negatives in E025_per_predicate_proof_map.json; runtime mutations and proof-refusal bypass restored exactly. Admission never proves output correctness/current governed verification. Formal SATISFIED zero.',
        ],
        'MD-S063-R0151' => [
            'positive' => 'B19ExecutedEvidencePredicateProofTest::test_each_predicate_uses_independent_execution_values',
            'negative' => 'B19ExecutedEvidencePredicateProofTest::test_each_predicate_detects_missing_or_substituted_evidence',
            'basis' => 'E-MD-B19-A001-025 dataset MD-S063-R0151: 1. Evidence packs must preserve enough detail to support diagnosis, not just human narrative. Independent sources: diagnosis from exact persisted events; primary prior publication bars_batch_hash; primary replacement publication bars_batch_hash; primary prior publication indicators_batch_hash; primary replacement publication indicators_batch_hash; primary prior publication eligibility_batch_hash; primary replacement publication eligibility_batch_hash. Current real persistence/export and immutable E022-E024 primary captures. Exact observations and per-value negatives in E025_per_predicate_proof_map.json; runtime mutations and proof-refusal bypass restored exactly. Admission never proves output correctness/current governed verification. Formal SATISFIED zero.',
        ],
        'MD-S063-R0152' => [
            'positive' => 'B19ExecutedEvidencePredicateProofTest::test_each_predicate_uses_independent_execution_values',
            'negative' => 'B19ExecutedEvidencePredicateProofTest::test_each_predicate_detects_missing_or_substituted_evidence',
            'basis' => 'E-MD-B19-A001-025 dataset MD-S063-R0152: 2. Evidence packs must be internally consistent with authoritative persisted run/publication data. Independent sources: primary eod_runs before export; actual one controlled provider observation succeeds; retained E023 eod_runs; primary prior publication bars_batch_hash; primary replacement publication bars_batch_hash; primary prior publication indicators_batch_hash; primary replacement publication indicators_batch_hash; primary prior publication eligibility_batch_hash; primary replacement publication eligibility_batch_hash; retained primary canonical config preimage. Current real persistence/export and immutable E022-E024 primary captures. Exact observations and per-value negatives in E025_per_predicate_proof_map.json; runtime mutations and proof-refusal bypass restored exactly. Admission never proves output correctness/current governed verification. Formal SATISFIED zero.',
        ],
        'MD-S063-R0153' => [
            'positive' => 'B19ExecutedEvidencePredicateProofTest::test_each_predicate_uses_independent_execution_values',
            'negative' => 'B19ExecutedEvidencePredicateProofTest::test_each_predicate_detects_missing_or_substituted_evidence',
            'basis' => 'E-MD-B19-A001-025 dataset MD-S063-R0153: 3. A correction evidence pack must always preserve both old and new state. Independent sources: E024 primary approved correction; E024 immutable primary baseline; E024 primary replacement; primary prior publication bars_batch_hash; primary replacement publication bars_batch_hash; primary prior publication indicators_batch_hash; primary replacement publication indicators_batch_hash; primary prior publication eligibility_batch_hash; primary replacement publication eligibility_batch_hash. Current real persistence/export and immutable E022-E024 primary captures. Exact observations and per-value negatives in E025_per_predicate_proof_map.json; runtime mutations and proof-refusal bypass restored exactly. Admission never proves output correctness/current governed verification. Formal SATISFIED zero.',
        ],
        'MD-S063-R0154' => [
            'positive' => 'B19ExecutedEvidencePredicateProofTest::test_each_predicate_uses_independent_execution_values',
            'negative' => 'B19ExecutedEvidencePredicateProofTest::test_each_predicate_detects_missing_or_substituted_evidence',
            'basis' => 'E-MD-B19-A001-025 dataset MD-S063-R0154: 4. A replay result evidence pack must always preserve both expected and actual state plus the explicit replay result status (`PASS`, `FAIL`, or `BLOCKED`). Independent sources: separately authored wrong eligibility hash before comparator; MISMATCH classification; independent expected fixture, not actual output; primary sealed publication. Current real persistence/export and immutable E022-E024 primary captures. Exact observations and per-value negatives in E025_per_predicate_proof_map.json; runtime mutations and proof-refusal bypass restored exactly. Admission never proves output correctness/current governed verification. Formal SATISFIED zero.',
        ],
        'MD-S063-R0155' => [
            'positive' => 'B19ExecutedEvidencePredicateProofTest::test_each_predicate_uses_independent_execution_values',
            'negative' => 'B19ExecutedEvidencePredicateProofTest::test_each_predicate_detects_missing_or_substituted_evidence',
            'basis' => 'E-MD-B19-A001-025 dataset MD-S063-R0155: 5. Evidence packs must not imply readability for an unsealed or non-current publication. Independent sources: selected historical publication distinct from actual pointer; current readability not granted to historical publication; real unsealed failed run; missing publication not invented. Current real persistence/export and immutable E022-E024 primary captures. Exact observations and per-value negatives in E025_per_predicate_proof_map.json; runtime mutations and proof-refusal bypass restored exactly. Admission never proves output correctness/current governed verification. Formal SATISFIED zero.',
        ],
        'MD-S063-R0168' => [
            'positive' => 'B19ExecutedEvidencePredicateProofTest::test_each_predicate_uses_independent_execution_values',
            'negative' => 'B19ExecutedEvidencePredicateProofTest::test_each_predicate_detects_missing_or_substituted_evidence',
            'basis' => 'E-MD-B19-A001-025 dataset MD-S063-R0168: If an incident, correction, or replay mismatch cannot be explained by a coherent evidence pack, then the operational proof layer is incomplete and the case is not considered fully auditable. Independent sources: incoherent eventless case not fully auditable; single consistent completeness result; separately authored wrong eligibility hash before comparator; MISMATCH classification; independent expected fixture, not actual output; primary sealed publication. Current real persistence/export and immutable E022-E024 primary captures. Exact observations and per-value negatives in E025_per_predicate_proof_map.json; runtime mutations and proof-refusal bypass restored exactly. Admission never proves output correctness/current governed verification. Formal SATISFIED zero.',
        ],
        'MD-S068-R0002' => [
            'positive' => 'B19ExecutedEvidencePredicateProofTest::test_each_predicate_uses_independent_execution_values',
            'negative' => 'B19ExecutedEvidencePredicateProofTest::test_each_predicate_detects_missing_or_substituted_evidence',
            'basis' => 'E-MD-B19-A001-025 dataset MD-S068-R0002: An artifact may be labeled as executed evidence only if it is sourced from an actual run, replay, correction, or test execution context. Independent sources: primary eod_runs before export; actual one controlled provider observation succeeds; retained E023 eod_runs; E024 primary approved correction; E024 immutable primary baseline; E024 primary replacement; separately authored wrong eligibility hash before comparator; MISMATCH classification; independent expected fixture, not actual output; primary sealed publication; actual process exit code; correlated immutable stdout digest; actual test source ID and exact command. Current real persistence/export and immutable E022-E024 primary captures. Exact observations and per-value negatives in E025_per_predicate_proof_map.json; runtime mutations and proof-refusal bypass restored exactly. Admission never proves output correctness/current governed verification. Formal SATISFIED zero.',
        ],
        'MD-S068-R0005' => [
            'positive' => 'B19ExecutedEvidencePredicateProofTest::test_each_predicate_uses_independent_execution_values',
            'negative' => 'B19ExecutedEvidencePredicateProofTest::test_each_predicate_detects_missing_or_substituted_evidence',
            'basis' => 'E-MD-B19-A001-025 dataset MD-S068-R0005: - correction_id Independent sources: E024 primary approved correction; E024 immutable primary baseline; E024 primary replacement. Current real persistence/export and immutable E022-E024 primary captures. Exact observations and per-value negatives in E025_per_predicate_proof_map.json; runtime mutations and proof-refusal bypass restored exactly. Admission never proves output correctness/current governed verification. Formal SATISFIED zero.',
        ],
        'MD-S068-R0006' => [
            'positive' => 'B19ExecutedEvidencePredicateProofTest::test_each_predicate_uses_independent_execution_values',
            'negative' => 'B19ExecutedEvidencePredicateProofTest::test_each_predicate_detects_missing_or_substituted_evidence',
            'basis' => 'E-MD-B19-A001-025 dataset MD-S068-R0006: - test_id plus actual execution metadata Independent sources: actual process exit code; correlated immutable stdout digest; actual test source ID and exact command. Current real persistence/export and immutable E022-E024 primary captures. Exact observations and per-value negatives in E025_per_predicate_proof_map.json; runtime mutations and proof-refusal bypass restored exactly. Admission never proves output correctness/current governed verification. Formal SATISFIED zero.',
        ],
        'MD-S068-R0008' => [
            'positive' => 'B19ExecutedEvidencePredicateProofTest::test_each_predicate_uses_independent_execution_values',
            'negative' => 'B19ExecutedEvidencePredicateProofTest::test_each_predicate_detects_missing_or_substituted_evidence',
            'basis' => 'E-MD-B19-A001-025 dataset MD-S068-R0008: - real hashes Independent sources: immutable independently versioned v5 expected publication manifest. Current real persistence/export and immutable E022-E024 primary captures. Exact observations and per-value negatives in E025_per_predicate_proof_map.json; runtime mutations and proof-refusal bypass restored exactly. Admission never proves output correctness/current governed verification. Formal SATISFIED zero.',
        ],
        'MD-S068-R0009' => [
            'positive' => 'B19ExecutedEvidencePredicateProofTest::test_each_predicate_uses_independent_execution_values',
            'negative' => 'B19ExecutedEvidencePredicateProofTest::test_each_predicate_detects_missing_or_substituted_evidence',
            'basis' => 'E-MD-B19-A001-025 dataset MD-S068-R0009: - real counts Independent sources: primary events, not count copied from export. Current real persistence/export and immutable E022-E024 primary captures. Exact observations and per-value negatives in E025_per_predicate_proof_map.json; runtime mutations and proof-refusal bypass restored exactly. Admission never proves output correctness/current governed verification. Formal SATISFIED zero.',
        ],
        'MD-S068-R0010' => [
            'positive' => 'B19ExecutedEvidencePredicateProofTest::test_each_predicate_uses_independent_execution_values',
            'negative' => 'B19ExecutedEvidencePredicateProofTest::test_each_predicate_detects_missing_or_substituted_evidence',
            'basis' => 'E-MD-B19-A001-025 dataset MD-S068-R0010: - real timestamps Independent sources: persisted own finished_at. Current real persistence/export and immutable E022-E024 primary captures. Exact observations and per-value negatives in E025_per_predicate_proof_map.json; runtime mutations and proof-refusal bypass restored exactly. Admission never proves output correctness/current governed verification. Formal SATISFIED zero.',
        ],
        'MD-S068-R0011' => [
            'positive' => 'B19ExecutedEvidencePredicateProofTest::test_each_predicate_uses_independent_execution_values',
            'negative' => 'B19ExecutedEvidencePredicateProofTest::test_each_predicate_detects_missing_or_substituted_evidence',
            'basis' => 'E-MD-B19-A001-025 dataset MD-S068-R0011: - real terminal states Independent sources: primary eod_runs before export; actual one controlled provider observation succeeds; retained E023 eod_runs; real401 persisted failed outcome. Current real persistence/export and immutable E022-E024 primary captures. Exact observations and per-value negatives in E025_per_predicate_proof_map.json; runtime mutations and proof-refusal bypass restored exactly. Admission never proves output correctness/current governed verification. Formal SATISFIED zero.',
        ],
        'MD-S068-R0012' => [
            'positive' => 'B19ExecutedEvidencePredicateProofTest::test_each_predicate_uses_independent_execution_values',
            'negative' => 'B19ExecutedEvidencePredicateProofTest::test_each_predicate_detects_missing_or_substituted_evidence',
            'basis' => 'E-MD-B19-A001-025 dataset MD-S068-R0012: - real publication IDs Independent sources: primary actual sealed publication. Current real persistence/export and immutable E022-E024 primary captures. Exact observations and per-value negatives in E025_per_predicate_proof_map.json; runtime mutations and proof-refusal bypass restored exactly. Admission never proves output correctness/current governed verification. Formal SATISFIED zero.',
        ],
        'MD-S068-R0014' => [
            'positive' => 'B19ExecutedEvidencePredicateProofTest::test_each_predicate_uses_independent_execution_values',
            'negative' => 'B19ExecutedEvidencePredicateProofTest::test_each_predicate_detects_missing_or_substituted_evidence',
            'basis' => 'E-MD-B19-A001-025 dataset MD-S068-R0014: - `example_run_id` Independent sources: primary eod_runs before export; actual one controlled provider observation succeeds; retained E023 eod_runs; positive real ID, symbolic/zero separately rejected through exporter helper. Current real persistence/export and immutable E022-E024 primary captures. Exact observations and per-value negatives in E025_per_predicate_proof_map.json; runtime mutations and proof-refusal bypass restored exactly. Admission never proves output correctness/current governed verification. Formal SATISFIED zero.',
        ],
        'MD-S068-R0015' => [
            'positive' => 'B19ExecutedEvidencePredicateProofTest::test_each_predicate_uses_independent_execution_values',
            'negative' => 'B19ExecutedEvidencePredicateProofTest::test_each_predicate_detects_missing_or_substituted_evidence',
            'basis' => 'E-MD-B19-A001-025 dataset MD-S068-R0015: - fabricated timestamps Independent sources: actual own timestamp; missing/symbolic/invalid calendar production negatives in BoundaryTest. Current real persistence/export and immutable E022-E024 primary captures. Exact observations and per-value negatives in E025_per_predicate_proof_map.json; runtime mutations and proof-refusal bypass restored exactly. Admission never proves output correctness/current governed verification. Formal SATISFIED zero.',
        ],
        'MD-S068-R0016' => [
            'positive' => 'B19ExecutedEvidencePredicateProofTest::test_each_predicate_uses_independent_execution_values',
            'negative' => 'B19ExecutedEvidencePredicateProofTest::test_each_predicate_detects_missing_or_substituted_evidence',
            'basis' => 'E-MD-B19-A001-025 dataset MD-S068-R0016: 4. it can be traced back to a real execution context or archived execution bundle Independent sources: primary eod_runs before export; actual one controlled provider observation succeeds; retained E023 eod_runs; E024 primary approved correction; E024 immutable primary baseline; E024 primary replacement; separately authored wrong eligibility hash before comparator; MISMATCH classification; independent expected fixture, not actual output; primary sealed publication; actual process exit code; correlated immutable stdout digest; actual test source ID and exact command. Current real persistence/export and immutable E022-E024 primary captures. Exact observations and per-value negatives in E025_per_predicate_proof_map.json; runtime mutations and proof-refusal bypass restored exactly. Admission never proves output correctness/current governed verification. Formal SATISFIED zero.',
        ],
        'MD-S068-R0023' => [
            'positive' => 'B19ExecutedEvidencePredicateProofTest::test_each_predicate_uses_independent_execution_values',
            'negative' => 'B19ExecutedEvidencePredicateProofTest::test_each_predicate_detects_missing_or_substituted_evidence',
            'basis' => 'E-MD-B19-A001-025 dataset MD-S068-R0023: - illustrative JSON shapes with placeholder hashes Independent sources: immutable independently versioned v5 expected publication manifest. Current real persistence/export and immutable E022-E024 primary captures. Exact observations and per-value negatives in E025_per_predicate_proof_map.json; runtime mutations and proof-refusal bypass restored exactly. Admission never proves output correctness/current governed verification. Formal SATISFIED zero.',
        ],
        'MD-S068-R0024' => [
            'positive' => 'B19ExecutedEvidencePredicateProofTest::test_each_predicate_uses_independent_execution_values',
            'negative' => 'B19ExecutedEvidencePredicateProofTest::test_each_predicate_detects_missing_or_substituted_evidence',
            'basis' => 'E-MD-B19-A001-025 dataset MD-S068-R0024: - manually fabricated example outputs with no execution trace Independent sources: primary eod_runs before export; actual one controlled provider observation succeeds; retained E023 eod_runs; E024 primary approved correction; E024 immutable primary baseline; E024 primary replacement; separately authored wrong eligibility hash before comparator; MISMATCH classification; independent expected fixture, not actual output; primary sealed publication; actual process exit code; correlated immutable stdout digest; actual test source ID and exact command. Current real persistence/export and immutable E022-E024 primary captures. Exact observations and per-value negatives in E025_per_predicate_proof_map.json; runtime mutations and proof-refusal bypass restored exactly. Admission never proves output correctness/current governed verification. Formal SATISFIED zero.',
        ],
        'MD-S068-R0025' => [
            'positive' => 'B19ExecutedEvidencePredicateProofTest::test_each_predicate_uses_independent_execution_values',
            'negative' => 'B19ExecutedEvidencePredicateProofTest::test_each_predicate_detects_missing_or_substituted_evidence',
            'basis' => 'E-MD-B19-A001-025 dataset MD-S068-R0025: - examples whose values are obviously symbolic only Independent sources: immutable independently versioned v5 expected publication manifest. Current real persistence/export and immutable E022-E024 primary captures. Exact observations and per-value negatives in E025_per_predicate_proof_map.json; runtime mutations and proof-refusal bypass restored exactly. Admission never proves output correctness/current governed verification. Formal SATISFIED zero.',
        ],
        'MD-S068-R0030' => [
            'positive' => 'B19ExecutedEvidencePredicateProofTest::test_each_predicate_uses_independent_execution_values',
            'negative' => 'B19ExecutedEvidencePredicateProofTest::test_each_predicate_detects_missing_or_substituted_evidence',
            'basis' => 'E-MD-B19-A001-025 dataset MD-S068-R0030: If a reader cannot tell whether an artifact is illustrative or actually executed, the evidence labeling is too weak. Independent sources: primary eod_runs before export; actual one controlled provider observation succeeds; retained E023 eod_runs; E024 primary approved correction; E024 immutable primary baseline; E024 primary replacement; separately authored wrong eligibility hash before comparator; MISMATCH classification; independent expected fixture, not actual output; primary sealed publication; actual process exit code; correlated immutable stdout digest; actual test source ID and exact command. Current real persistence/export and immutable E022-E024 primary captures. Exact observations and per-value negatives in E025_per_predicate_proof_map.json; runtime mutations and proof-refusal bypass restored exactly. Admission never proves output correctness/current governed verification. Formal SATISFIED zero.',
        ],
        'MD-S068-R0031' => [
            'positive' => 'B19ExecutedEvidencePredicateProofTest::test_each_predicate_uses_independent_execution_values',
            'negative' => 'B19ExecutedEvidencePredicateProofTest::test_each_predicate_detects_missing_or_substituted_evidence',
            'basis' => 'E-MD-B19-A001-025 dataset MD-S068-R0031: **What run admission proves.** That an executed run carried the identity, evidence, and completeness required before its results may be cited, and that a run failing those criteria is excluded rather than partially counted. Independent sources: real otherwise complete world with own mandatory trail deleted; final mandatory evidence excluded, not partially complete; same unavailable trail must not claim completeness. Current real persistence/export and immutable E022-E024 primary captures. Exact observations and per-value negatives in E025_per_predicate_proof_map.json; runtime mutations and proof-refusal bypass restored exactly. Admission never proves output correctness/current governed verification. Formal SATISFIED zero.',
        ],
        'MD-S068-R0032' => [
            'positive' => 'B19ExecutedEvidencePredicateProofTest::test_each_predicate_uses_independent_execution_values',
            'negative' => 'B19ExecutedEvidencePredicateProofTest::test_each_predicate_detects_missing_or_substituted_evidence',
            'basis' => 'E-MD-B19-A001-025 dataset MD-S068-R0032: **What it cannot prove.** Independent sources: explicit implementation criteria version; independent current source bytes; declared criteria list content binding, not completeness of undeclared requirements; frozen admission capability boundary; frozen admission version boundary; runtime output alone cannot create issued proof. Current real persistence/export and immutable E022-E024 primary captures. Exact observations and per-value negatives in E025_per_predicate_proof_map.json; runtime mutations and proof-refusal bypass restored exactly. Admission never proves output correctness/current governed verification. Formal SATISFIED zero.',
        ],
        'MD-S068-R0033' => [
            'positive' => 'B19ExecutedEvidencePredicateProofTest::test_each_predicate_uses_independent_execution_values',
            'negative' => 'B19ExecutedEvidencePredicateProofTest::test_each_predicate_detects_missing_or_substituted_evidence',
            'basis' => 'E-MD-B19-A001-025 dataset MD-S068-R0033: - **That an admitted run produced correct data.** Admission checks the run\'s evidence, not its output. A well-evidenced run over wrong inputs is fully admissible. Independent sources: separately authored wrong eligibility hash before comparator; MISMATCH classification; independent expected fixture, not actual output; primary sealed publication; complete independently mismatching diagnostic evidence; admitted FAIL must not imply data correctness. Current real persistence/export and immutable E022-E024 primary captures. Exact observations and per-value negatives in E025_per_predicate_proof_map.json; runtime mutations and proof-refusal bypass restored exactly. Admission never proves output correctness/current governed verification. Formal SATISFIED zero.',
        ],
        'MD-S068-R0034' => [
            'positive' => 'B19ExecutedEvidencePredicateProofTest::test_each_predicate_uses_independent_execution_values',
            'negative' => 'B19ExecutedEvidencePredicateProofTest::test_each_predicate_detects_missing_or_substituted_evidence',
            'basis' => 'E-MD-B19-A001-025 dataset MD-S068-R0034: - **That the criteria cover what matters.** A run is admitted against declared requirements. A missing binding nobody required — as configuration snapshots were until recently — does not block admission. Independent sources: explicit implementation criteria version; independent current source bytes; declared criteria list content binding, not completeness of undeclared requirements; frozen admission capability boundary; frozen admission version boundary; runtime output alone cannot create issued proof. Current real persistence/export and immutable E022-E024 primary captures. Exact observations and per-value negatives in E025_per_predicate_proof_map.json; runtime mutations and proof-refusal bypass restored exactly. Admission never proves output correctness/current governed verification. Formal SATISFIED zero.',
        ],
        'MD-S068-R0035' => [
            'positive' => 'B19ExecutedEvidencePredicateProofTest::test_each_predicate_uses_independent_execution_values',
            'negative' => 'B19ExecutedEvidencePredicateProofTest::test_each_predicate_detects_missing_or_substituted_evidence',
            'basis' => 'E-MD-B19-A001-025 dataset MD-S068-R0035: - **That admission history is comparable across versions.** Runs admitted under earlier criteria were held to a different bar, and the record does not distinguish them unless the criteria version is bound. Independent sources: explicit implementation criteria version; independent current source bytes; declared criteria list content binding, not completeness of undeclared requirements; frozen admission capability boundary; frozen admission version boundary; runtime output alone cannot create issued proof. Current real persistence/export and immutable E022-E024 primary captures. Exact observations and per-value negatives in E025_per_predicate_proof_map.json; runtime mutations and proof-refusal bypass restored exactly. Admission never proves output correctness/current governed verification. Formal SATISFIED zero.',
        ],
        'MD-S068-R0036' => [
            'positive' => 'B19ExecutedEvidencePredicateProofTest::test_each_predicate_uses_independent_execution_values',
            'negative' => 'B19ExecutedEvidencePredicateProofTest::test_each_predicate_detects_missing_or_substituted_evidence',
            'basis' => 'E-MD-B19-A001-025 dataset MD-S068-R0036: Consequently an admitted run may be cited as evidence that **its evidence met the declared bar**, never as evidence that **its results are sound**. Independent sources: separately authored wrong eligibility hash before comparator; MISMATCH classification; independent expected fixture, not actual output; primary sealed publication; complete independently mismatching diagnostic evidence; admitted FAIL must not imply data correctness. Current real persistence/export and immutable E022-E024 primary captures. Exact observations and per-value negatives in E025_per_predicate_proof_map.json; runtime mutations and proof-refusal bypass restored exactly. Admission never proves output correctness/current governed verification. Formal SATISFIED zero.',
        ],
        'MD-S076-R0006' => [
            'positive' => 'B19ExecutedEvidencePredicateProofTest::test_each_predicate_uses_independent_execution_values',
            'negative' => 'B19ExecutedEvidencePredicateProofTest::test_each_predicate_detects_missing_or_substituted_evidence',
            'basis' => 'E-MD-B19-A001-025 dataset MD-S076-R0006: 1. normal readable success Independent sources: primary eod_runs before export; actual one controlled provider observation succeeds; retained E023 eod_runs. Current real persistence/export and immutable E022-E024 primary captures. Exact observations and per-value negatives in E025_per_predicate_proof_map.json; runtime mutations and proof-refusal bypass restored exactly. Admission never proves output correctness/current governed verification. Formal SATISFIED zero.',
        ],
        'MD-S076-R0007' => [
            'positive' => 'B19ExecutedEvidencePredicateProofTest::test_each_predicate_uses_independent_execution_values',
            'negative' => 'B19ExecutedEvidencePredicateProofTest::test_each_predicate_detects_missing_or_substituted_evidence',
            'basis' => 'E-MD-B19-A001-025 dataset MD-S076-R0007: 2. held or failed requested-date outcome Independent sources: retained E023 deliberately held real run; primary persisted held status; actual401 failure. Current real persistence/export and immutable E022-E024 primary captures. Exact observations and per-value negatives in E025_per_predicate_proof_map.json; runtime mutations and proof-refusal bypass restored exactly. Admission never proves output correctness/current governed verification. Formal SATISFIED zero.',
        ],
        'MD-S076-R0008' => [
            'positive' => 'B19ExecutedEvidencePredicateProofTest::test_each_predicate_uses_independent_execution_values',
            'negative' => 'B19ExecutedEvidencePredicateProofTest::test_each_predicate_detects_missing_or_substituted_evidence',
            'basis' => 'E-MD-B19-A001-025 dataset MD-S076-R0008: 3. historical correction publication Independent sources: E024 primary approved correction; E024 immutable primary baseline; E024 primary replacement. Current real persistence/export and immutable E022-E024 primary captures. Exact observations and per-value negatives in E025_per_predicate_proof_map.json; runtime mutations and proof-refusal bypass restored exactly. Admission never proves output correctness/current governed verification. Formal SATISFIED zero.',
        ],
        'MD-S076-R0009' => [
            'positive' => 'B19ExecutedEvidencePredicateProofTest::test_each_predicate_uses_independent_execution_values',
            'negative' => 'B19ExecutedEvidencePredicateProofTest::test_each_predicate_detects_missing_or_substituted_evidence',
            'basis' => 'E-MD-B19-A001-025 dataset MD-S076-R0009: 4. replay match or replay mismatch Independent sources: separately authored wrong eligibility hash before comparator; MISMATCH classification; independent expected fixture, not actual output; primary sealed publication. Current real persistence/export and immutable E022-E024 primary captures. Exact observations and per-value negatives in E025_per_predicate_proof_map.json; runtime mutations and proof-refusal bypass restored exactly. Admission never proves output correctness/current governed verification. Formal SATISFIED zero.',
        ],
        'MD-S076-R0010' => [
            'positive' => 'B19ExecutedEvidencePredicateProofTest::test_each_predicate_uses_independent_execution_values',
            'negative' => 'B19ExecutedEvidencePredicateProofTest::test_each_predicate_detects_missing_or_substituted_evidence',
            'basis' => 'E-MD-B19-A001-025 dataset MD-S076-R0010: 5. executed contract-test run summary Independent sources: actual process exit code; correlated immutable stdout digest; actual test source ID and exact command. Current real persistence/export and immutable E022-E024 primary captures. Exact observations and per-value negatives in E025_per_predicate_proof_map.json; runtime mutations and proof-refusal bypass restored exactly. Admission never proves output correctness/current governed verification. Formal SATISFIED zero.',
        ],
        'MD-S076-R0012' => [
            'positive' => 'B19ExecutedEvidencePredicateProofTest::test_each_predicate_uses_independent_execution_values',
            'negative' => 'B19ExecutedEvidencePredicateProofTest::test_each_predicate_detects_missing_or_substituted_evidence',
            'basis' => 'E-MD-B19-A001-025 dataset MD-S076-R0012: - real `run_summary.json` Independent sources: primary eod_runs before export; actual one controlled provider observation succeeds; retained E023 eod_runs. Current real persistence/export and immutable E022-E024 primary captures. Exact observations and per-value negatives in E025_per_predicate_proof_map.json; runtime mutations and proof-refusal bypass restored exactly. Admission never proves output correctness/current governed verification. Formal SATISFIED zero.',
        ],
        'MD-S076-R0013' => [
            'positive' => 'B19ExecutedEvidencePredicateProofTest::test_each_predicate_uses_independent_execution_values',
            'negative' => 'B19ExecutedEvidencePredicateProofTest::test_each_predicate_detects_missing_or_substituted_evidence',
            'basis' => 'E-MD-B19-A001-025 dataset MD-S076-R0013: - real `publication_manifest.json` or publication resolution output Independent sources: immutable independent manifest literal. Current real persistence/export and immutable E022-E024 primary captures. Exact observations and per-value negatives in E025_per_predicate_proof_map.json; runtime mutations and proof-refusal bypass restored exactly. Admission never proves output correctness/current governed verification. Formal SATISFIED zero.',
        ],
        'MD-S076-R0014' => [
            'positive' => 'B19ExecutedEvidencePredicateProofTest::test_each_predicate_uses_independent_execution_values',
            'negative' => 'B19ExecutedEvidencePredicateProofTest::test_each_predicate_detects_missing_or_substituted_evidence',
            'basis' => 'E-MD-B19-A001-025 dataset MD-S076-R0014: - real hash values Independent sources: immutable independently versioned v5 expected publication manifest. Current real persistence/export and immutable E022-E024 primary captures. Exact observations and per-value negatives in E025_per_predicate_proof_map.json; runtime mutations and proof-refusal bypass restored exactly. Admission never proves output correctness/current governed verification. Formal SATISFIED zero.',
        ],
        'MD-S076-R0015' => [
            'positive' => 'B19ExecutedEvidencePredicateProofTest::test_each_predicate_uses_independent_execution_values',
            'negative' => 'B19ExecutedEvidencePredicateProofTest::test_each_predicate_detects_missing_or_substituted_evidence',
            'basis' => 'E-MD-B19-A001-025 dataset MD-S076-R0015: - real `run_event_summary.json` or run-event trail reference Independent sources: primary individual event records before export. Current real persistence/export and immutable E022-E024 primary captures. Exact observations and per-value negatives in E025_per_predicate_proof_map.json; runtime mutations and proof-refusal bypass restored exactly. Admission never proves output correctness/current governed verification. Formal SATISFIED zero.',
        ],
        'MD-S076-R0016' => [
            'positive' => 'B19ExecutedEvidencePredicateProofTest::test_each_predicate_uses_independent_execution_values',
            'negative' => 'B19ExecutedEvidencePredicateProofTest::test_each_predicate_detects_missing_or_substituted_evidence',
            'basis' => 'E-MD-B19-A001-025 dataset MD-S076-R0016: - real eligibility export or reason-code distribution Independent sources: independent reason distribution from primary events. Current real persistence/export and immutable E022-E024 primary captures. Exact observations and per-value negatives in E025_per_predicate_proof_map.json; runtime mutations and proof-refusal bypass restored exactly. Admission never proves output correctness/current governed verification. Formal SATISFIED zero.',
        ],
        'MD-S076-R0017' => [
            'positive' => 'B19ExecutedEvidencePredicateProofTest::test_each_predicate_uses_independent_execution_values',
            'negative' => 'B19ExecutedEvidencePredicateProofTest::test_each_predicate_detects_missing_or_substituted_evidence',
            'basis' => 'E-MD-B19-A001-025 dataset MD-S076-R0017: - real config identity Independent sources: retained primary canonical config preimage. Current real persistence/export and immutable E022-E024 primary captures. Exact observations and per-value negatives in E025_per_predicate_proof_map.json; runtime mutations and proof-refusal bypass restored exactly. Admission never proves output correctness/current governed verification. Formal SATISFIED zero.',
        ],
        'MD-S076-R0018' => [
            'positive' => 'B19ExecutedEvidencePredicateProofTest::test_each_predicate_uses_independent_execution_values',
            'negative' => 'B19ExecutedEvidencePredicateProofTest::test_each_predicate_detects_missing_or_substituted_evidence',
            'basis' => 'E-MD-B19-A001-025 dataset MD-S076-R0018: - real timestamps Independent sources: primary persisted creation timestamp. Current real persistence/export and immutable E022-E024 primary captures. Exact observations and per-value negatives in E025_per_predicate_proof_map.json; runtime mutations and proof-refusal bypass restored exactly. Admission never proves output correctness/current governed verification. Formal SATISFIED zero.',
        ],
        'MD-S076-R0019' => [
            'positive' => 'B19ExecutedEvidencePredicateProofTest::test_each_predicate_uses_independent_execution_values',
            'negative' => 'B19ExecutedEvidencePredicateProofTest::test_each_predicate_detects_missing_or_substituted_evidence',
            'basis' => 'E-MD-B19-A001-025 dataset MD-S076-R0019: - real terminal outcome Independent sources: primary eod_runs before export; actual one controlled provider observation succeeds; retained E023 eod_runs. Current real persistence/export and immutable E022-E024 primary captures. Exact observations and per-value negatives in E025_per_predicate_proof_map.json; runtime mutations and proof-refusal bypass restored exactly. Admission never proves output correctness/current governed verification. Formal SATISFIED zero.',
        ],
        'MD-S076-R0021' => [
            'positive' => 'B19ExecutedEvidencePredicateProofTest::test_each_predicate_uses_independent_execution_values',
            'negative' => 'B19ExecutedEvidencePredicateProofTest::test_each_predicate_detects_missing_or_substituted_evidence',
            'basis' => 'E-MD-B19-A001-025 dataset MD-S076-R0021: - real correction request reference Independent sources: E024 primary approved correction; E024 immutable primary baseline; E024 primary replacement. Current real persistence/export and immutable E022-E024 primary captures. Exact observations and per-value negatives in E025_per_predicate_proof_map.json; runtime mutations and proof-refusal bypass restored exactly. Admission never proves output correctness/current governed verification. Formal SATISFIED zero.',
        ],
        'MD-S076-R0022' => [
            'positive' => 'B19ExecutedEvidencePredicateProofTest::test_each_predicate_uses_independent_execution_values',
            'negative' => 'B19ExecutedEvidencePredicateProofTest::test_each_predicate_detects_missing_or_substituted_evidence',
            'basis' => 'E-MD-B19-A001-025 dataset MD-S076-R0022: - real prior publication reference Independent sources: E024 primary approved correction; E024 immutable primary baseline; E024 primary replacement. Current real persistence/export and immutable E022-E024 primary captures. Exact observations and per-value negatives in E025_per_predicate_proof_map.json; runtime mutations and proof-refusal bypass restored exactly. Admission never proves output correctness/current governed verification. Formal SATISFIED zero.',
        ],
        'MD-S076-R0023' => [
            'positive' => 'B19ExecutedEvidencePredicateProofTest::test_each_predicate_uses_independent_execution_values',
            'negative' => 'B19ExecutedEvidencePredicateProofTest::test_each_predicate_detects_missing_or_substituted_evidence',
            'basis' => 'E-MD-B19-A001-025 dataset MD-S076-R0023: - real new publication reference Independent sources: E024 primary approved correction; E024 immutable primary baseline; E024 primary replacement. Current real persistence/export and immutable E022-E024 primary captures. Exact observations and per-value negatives in E025_per_predicate_proof_map.json; runtime mutations and proof-refusal bypass restored exactly. Admission never proves output correctness/current governed verification. Formal SATISFIED zero.',
        ],
        'MD-S076-R0024' => [
            'positive' => 'B19ExecutedEvidencePredicateProofTest::test_each_predicate_uses_independent_execution_values',
            'negative' => 'B19ExecutedEvidencePredicateProofTest::test_each_predicate_detects_missing_or_substituted_evidence',
            'basis' => 'E-MD-B19-A001-025 dataset MD-S076-R0024: - real old/new hash values Independent sources: primary prior publication bars_batch_hash; primary replacement publication bars_batch_hash; primary prior publication indicators_batch_hash; primary replacement publication indicators_batch_hash; primary prior publication eligibility_batch_hash; primary replacement publication eligibility_batch_hash. Current real persistence/export and immutable E022-E024 primary captures. Exact observations and per-value negatives in E025_per_predicate_proof_map.json; runtime mutations and proof-refusal bypass restored exactly. Admission never proves output correctness/current governed verification. Formal SATISFIED zero.',
        ],
        'MD-S076-R0025' => [
            'positive' => 'B19ExecutedEvidencePredicateProofTest::test_each_predicate_uses_independent_execution_values',
            'negative' => 'B19ExecutedEvidencePredicateProofTest::test_each_predicate_detects_missing_or_substituted_evidence',
            'basis' => 'E-MD-B19-A001-025 dataset MD-S076-R0025: - real publication switch result Independent sources: actual approved changed correction replaces prior; input105->106; primary supersession edge. Current real persistence/export and immutable E022-E024 primary captures. Exact observations and per-value negatives in E025_per_predicate_proof_map.json; runtime mutations and proof-refusal bypass restored exactly. Admission never proves output correctness/current governed verification. Formal SATISFIED zero.',
        ],
        'MD-S076-R0026' => [
            'positive' => 'B19ExecutedEvidencePredicateProofTest::test_each_predicate_uses_independent_execution_values',
            'negative' => 'B19ExecutedEvidencePredicateProofTest::test_each_predicate_detects_missing_or_substituted_evidence',
            'basis' => 'E-MD-B19-A001-025 dataset MD-S076-R0026: - real correction diff artifact Independent sources: primary prior publication bars_batch_hash; primary replacement publication bars_batch_hash; primary prior publication indicators_batch_hash; primary replacement publication indicators_batch_hash; primary prior publication eligibility_batch_hash; primary replacement publication eligibility_batch_hash; controlled105->106 correction changes canonical values; actual correction diff. Current real persistence/export and immutable E022-E024 primary captures. Exact observations and per-value negatives in E025_per_predicate_proof_map.json; runtime mutations and proof-refusal bypass restored exactly. Admission never proves output correctness/current governed verification. Formal SATISFIED zero.',
        ],
        'MD-S076-R0028' => [
            'positive' => 'B19ExecutedEvidencePredicateProofTest::test_each_predicate_uses_independent_execution_values',
            'negative' => 'B19ExecutedEvidencePredicateProofTest::test_each_predicate_detects_missing_or_substituted_evidence',
            'basis' => 'E-MD-B19-A001-025 dataset MD-S076-R0028: - real `replay_result.json` object Independent sources: separately authored wrong eligibility hash before comparator; MISMATCH classification; independent expected fixture, not actual output; primary sealed publication. Current real persistence/export and immutable E022-E024 primary captures. Exact observations and per-value negatives in E025_per_predicate_proof_map.json; runtime mutations and proof-refusal bypass restored exactly. Admission never proves output correctness/current governed verification. Formal SATISFIED zero.',
        ],
        'MD-S076-R0029' => [
            'positive' => 'B19ExecutedEvidencePredicateProofTest::test_each_predicate_uses_independent_execution_values',
            'negative' => 'B19ExecutedEvidencePredicateProofTest::test_each_predicate_detects_missing_or_substituted_evidence',
            'basis' => 'E-MD-B19-A001-025 dataset MD-S076-R0029: - real comparison result Independent sources: separately authored wrong eligibility hash before comparator; MISMATCH classification; independent expected fixture, not actual output; primary sealed publication. Current real persistence/export and immutable E022-E024 primary captures. Exact observations and per-value negatives in E025_per_predicate_proof_map.json; runtime mutations and proof-refusal bypass restored exactly. Admission never proves output correctness/current governed verification. Formal SATISFIED zero.',
        ],
        'MD-S076-R0030' => [
            'positive' => 'B19ExecutedEvidencePredicateProofTest::test_each_predicate_uses_independent_execution_values',
            'negative' => 'B19ExecutedEvidencePredicateProofTest::test_each_predicate_detects_missing_or_substituted_evidence',
            'basis' => 'E-MD-B19-A001-025 dataset MD-S076-R0030: - real config identity Independent sources: primary replay config snapshot preimage. Current real persistence/export and immutable E022-E024 primary captures. Exact observations and per-value negatives in E025_per_predicate_proof_map.json; runtime mutations and proof-refusal bypass restored exactly. Admission never proves output correctness/current governed verification. Formal SATISFIED zero.',
        ],
        'MD-S076-R0031' => [
            'positive' => 'B19ExecutedEvidencePredicateProofTest::test_each_predicate_uses_independent_execution_values',
            'negative' => 'B19ExecutedEvidencePredicateProofTest::test_each_predicate_detects_missing_or_substituted_evidence',
            'basis' => 'E-MD-B19-A001-025 dataset MD-S076-R0031: - real actual hashes Independent sources: immutable independent v5 canonical hash. Current real persistence/export and immutable E022-E024 primary captures. Exact observations and per-value negatives in E025_per_predicate_proof_map.json; runtime mutations and proof-refusal bypass restored exactly. Admission never proves output correctness/current governed verification. Formal SATISFIED zero.',
        ],
        'MD-S076-R0032' => [
            'positive' => 'B19ExecutedEvidencePredicateProofTest::test_each_predicate_uses_independent_execution_values',
            'negative' => 'B19ExecutedEvidencePredicateProofTest::test_each_predicate_detects_missing_or_substituted_evidence',
            'basis' => 'E-MD-B19-A001-025 dataset MD-S076-R0032: - real mismatch or match summary Independent sources: separately authored wrong eligibility hash before comparator; MISMATCH classification; independent expected fixture, not actual output; primary sealed publication. Current real persistence/export and immutable E022-E024 primary captures. Exact observations and per-value negatives in E025_per_predicate_proof_map.json; runtime mutations and proof-refusal bypass restored exactly. Admission never proves output correctness/current governed verification. Formal SATISFIED zero.',
        ],
        'MD-S076-R0034' => [
            'positive' => 'B19ExecutedEvidencePredicateProofTest::test_each_predicate_uses_independent_execution_values',
            'negative' => 'B19ExecutedEvidencePredicateProofTest::test_each_predicate_detects_missing_or_substituted_evidence',
            'basis' => 'E-MD-B19-A001-025 dataset MD-S076-R0034: - real test ID Independent sources: actual process exit code; correlated immutable stdout digest; actual test source ID and exact command. Current real persistence/export and immutable E022-E024 primary captures. Exact observations and per-value negatives in E025_per_predicate_proof_map.json; runtime mutations and proof-refusal bypass restored exactly. Admission never proves output correctness/current governed verification. Formal SATISFIED zero.',
        ],
        'MD-S076-R0035' => [
            'positive' => 'B19ExecutedEvidencePredicateProofTest::test_each_predicate_uses_independent_execution_values',
            'negative' => 'B19ExecutedEvidencePredicateProofTest::test_each_predicate_detects_missing_or_substituted_evidence',
            'basis' => 'E-MD-B19-A001-025 dataset MD-S076-R0035: - real fixture family Independent sources: actual command/test fixture source independently versioned. Current real persistence/export and immutable E022-E024 primary captures. Exact observations and per-value negatives in E025_per_predicate_proof_map.json; runtime mutations and proof-refusal bypass restored exactly. Admission never proves output correctness/current governed verification. Formal SATISFIED zero.',
        ],
        'MD-S076-R0036' => [
            'positive' => 'B19ExecutedEvidencePredicateProofTest::test_each_predicate_uses_independent_execution_values',
            'negative' => 'B19ExecutedEvidencePredicateProofTest::test_each_predicate_detects_missing_or_substituted_evidence',
            'basis' => 'E-MD-B19-A001-025 dataset MD-S076-R0036: - real pass/fail result Independent sources: actual process exit code; correlated immutable stdout digest; actual test source ID and exact command. Current real persistence/export and immutable E022-E024 primary captures. Exact observations and per-value negatives in E025_per_predicate_proof_map.json; runtime mutations and proof-refusal bypass restored exactly. Admission never proves output correctness/current governed verification. Formal SATISFIED zero.',
        ],
        'MD-S076-R0038' => [
            'positive' => 'B19ExecutedEvidencePredicateProofTest::test_each_predicate_uses_independent_execution_values',
            'negative' => 'B19ExecutedEvidencePredicateProofTest::test_each_predicate_detects_missing_or_substituted_evidence',
            'basis' => 'E-MD-B19-A001-025 dataset MD-S076-R0038: - publication Independent sources: actual process exit code; correlated immutable stdout digest; actual test source ID and exact command; executed publication assertions, not a method-name claim. Current real persistence/export and immutable E022-E024 primary captures. Exact observations and per-value negatives in E025_per_predicate_proof_map.json; runtime mutations and proof-refusal bypass restored exactly. Admission never proves output correctness/current governed verification. Formal SATISFIED zero.',
        ],
        'MD-S076-R0040' => [
            'positive' => 'B19ExecutedEvidencePredicateProofTest::test_each_predicate_uses_independent_execution_values',
            'negative' => 'B19ExecutedEvidencePredicateProofTest::test_each_predicate_detects_missing_or_substituted_evidence',
            'basis' => 'E-MD-B19-A001-025 dataset MD-S076-R0040: - sourced from an actual run, replay, correction, or test execution Independent sources: primary eod_runs before export; actual one controlled provider observation succeeds; retained E023 eod_runs; actual process exit code; correlated immutable stdout digest; actual test source ID and exact command. Current real persistence/export and immutable E022-E024 primary captures. Exact observations and per-value negatives in E025_per_predicate_proof_map.json; runtime mutations and proof-refusal bypass restored exactly. Admission never proves output correctness/current governed verification. Formal SATISFIED zero.',
        ],
        'MD-S076-R0041' => [
            'positive' => 'B19ExecutedEvidencePredicateProofTest::test_each_predicate_uses_independent_execution_values',
            'negative' => 'B19ExecutedEvidencePredicateProofTest::test_each_predicate_detects_missing_or_substituted_evidence',
            'basis' => 'E-MD-B19-A001-025 dataset MD-S076-R0041: - carrying actual produced values Independent sources: immutable independently versioned v5 expected publication manifest; primary prior publication bars_batch_hash; primary replacement publication bars_batch_hash; primary prior publication indicators_batch_hash; primary replacement publication indicators_batch_hash; primary prior publication eligibility_batch_hash; primary replacement publication eligibility_batch_hash. Current real persistence/export and immutable E022-E024 primary captures. Exact observations and per-value negatives in E025_per_predicate_proof_map.json; runtime mutations and proof-refusal bypass restored exactly. Admission never proves output correctness/current governed verification. Formal SATISFIED zero.',
        ],
        'MD-S076-R0042' => [
            'positive' => 'B19ExecutedEvidencePredicateProofTest::test_each_predicate_uses_independent_execution_values',
            'negative' => 'B19ExecutedEvidencePredicateProofTest::test_each_predicate_detects_missing_or_substituted_evidence',
            'basis' => 'E-MD-B19-A001-025 dataset MD-S076-R0042: - not manually fabricated placeholders Independent sources: immutable independently versioned v5 expected publication manifest; actual process exit code; correlated immutable stdout digest; actual test source ID and exact command. Current real persistence/export and immutable E022-E024 primary captures. Exact observations and per-value negatives in E025_per_predicate_proof_map.json; runtime mutations and proof-refusal bypass restored exactly. Admission never proves output correctness/current governed verification. Formal SATISFIED zero.',
        ],
        'MD-S076-R0053' => [
            'positive' => 'B19ExecutedEvidencePredicateProofTest::test_each_predicate_uses_independent_execution_values',
            'negative' => 'B19ExecutedEvidencePredicateProofTest::test_each_predicate_detects_missing_or_substituted_evidence',
            'basis' => 'E-MD-B19-A001-025 dataset MD-S076-R0053: If a platform claims strong proof-by-execution but cannot show at least one real evidence bundle for the major operational classes above, then the proof layer is not fully saturated. Independent sources: primary eod_runs before export; actual one controlled provider observation succeeds; retained E023 eod_runs; E024 primary approved correction; E024 immutable primary baseline; E024 primary replacement; separately authored wrong eligibility hash before comparator; MISMATCH classification; independent expected fixture, not actual output; primary sealed publication; actual process exit code; correlated immutable stdout digest; actual test source ID and exact command; fifth major actual class, retained primary held producer. Current real persistence/export and immutable E022-E024 primary captures. Exact observations and per-value negatives in E025_per_predicate_proof_map.json; runtime mutations and proof-refusal bypass restored exactly. Admission never proves output correctness/current governed verification. Formal SATISFIED zero.',
        ],

        // E026: policy alignment only; actual runtime behavior is separately proven or held.
        'MD-S062-R0056' => [
            'positive' => 'B19CrossContractSemanticAlignmentTest::test_each_pair_preserves_its_independently_named_semantic_fact',
            'negative' => 'B19CrossContractSemanticAlignmentTest::test_each_pair_rejects_removed_retargeted_or_unregistered_reference',
            'basis' => 'E026 MD-S062-R0056: independently selected \'one normal readable success run\' on the owner side and \'normal readable success\' on the registered target side; multiple independently selected shared dimensions, current role registration, and missing/inverted clause probes are asserted per row. E027 successor: exact registered governing source line resolves to this target; removed/retargeted/unregistered reference is rejected while E026 semantic clause probes remain. Policy alignment only; no operator execution is inferred.',
        ],
        'MD-S062-R0057' => [
            'positive' => 'B19CrossContractSemanticAlignmentTest::test_each_pair_preserves_its_independently_named_semantic_fact',
            'negative' => 'B19CrossContractSemanticAlignmentTest::test_each_pair_rejects_removed_retargeted_or_unregistered_reference',
            'basis' => 'E026 MD-S062-R0057: independently selected \'real execution identifiers\' on the owner side and \'real execution identity\' on the registered target side; multiple independently selected shared dimensions, current role registration, and missing/inverted clause probes are asserted per row. E027 successor: exact registered governing source line resolves to this target; removed/retargeted/unregistered reference is rejected while E026 semantic clause probes remain. Policy alignment only; no operator execution is inferred.',
        ],
        'MD-S062-R0058' => [
            'positive' => 'B19CrossContractSemanticAlignmentTest::test_each_pair_preserves_its_independently_named_semantic_fact',
            'negative' => 'B19CrossContractSemanticAlignmentTest::test_each_pair_rejects_removed_retargeted_or_unregistered_reference',
            'basis' => 'E026 MD-S062-R0058: independently selected \'real execution identifiers\' on the owner side and \'real execution identity\' on the registered target side; multiple independently selected shared dimensions, current role registration, and missing/inverted clause probes are asserted per row. E027 successor: exact registered governing source line resolves to this target; removed/retargeted/unregistered reference is rejected while E026 semantic clause probes remain. Policy alignment only; no operator execution is inferred.',
        ],
        'MD-S062-R0059' => [
            'positive' => 'B19CrossContractSemanticAlignmentTest::test_each_pair_preserves_its_independently_named_semantic_fact',
            'negative' => 'B19CrossContractSemanticAlignmentTest::test_each_pair_rejects_removed_retargeted_or_unregistered_reference',
            'basis' => 'E026 MD-S062-R0059: independently selected \'Illustrative examples do not satisfy\' on the owner side and \'non-authoritative illustrative/example artifacts\' on the registered target side; multiple independently selected shared dimensions, current role registration, and missing/inverted clause probes are asserted per row. E027 successor: exact registered governing source line resolves to this target; removed/retargeted/unregistered reference is rejected while E026 semantic clause probes remain. Policy alignment only; no operator execution is inferred.',
        ],
        'MD-S062-R0060' => [
            'positive' => 'B19CrossContractSemanticAlignmentTest::test_each_pair_preserves_its_independently_named_semantic_fact',
            'negative' => 'B19CrossContractSemanticAlignmentTest::test_each_pair_rejects_removed_retargeted_or_unregistered_reference',
            'basis' => 'E026 MD-S062-R0060: independently selected \'actual correction request reference\' on the owner side and \'correction request identity\' on the registered target side; multiple independently selected shared dimensions, current role registration, and missing/inverted clause probes are asserted per row. E027 successor: exact registered governing source line resolves to this target; removed/retargeted/unregistered reference is rejected while E026 semantic clause probes remain. Policy alignment only; no operator execution is inferred.',
        ],
        'MD-S063-R0163' => [
            'positive' => 'B19CrossContractSemanticAlignmentTest::test_each_pair_preserves_its_independently_named_semantic_fact',
            'negative' => 'B19CrossContractSemanticAlignmentTest::test_each_pair_rejects_removed_retargeted_or_unregistered_reference',
            'basis' => 'E026 MD-S063-R0163: independently selected \'Evidence packs must not imply readability for an unsealed or non-current publication\' on the owner side and \'summary must not imply requested date is readable\' on the registered target side; multiple independently selected shared dimensions, current role registration, and missing/inverted clause probes are asserted per row. E027 successor: exact registered governing source line resolves to this target; removed/retargeted/unregistered reference is rejected while E026 semantic clause probes remain. Policy alignment only; no operator execution is inferred.',
        ],
        'MD-S063-R0164' => [
            'positive' => 'B19CrossContractSemanticAlignmentTest::test_each_pair_preserves_its_independently_named_semantic_fact',
            'negative' => 'B19CrossContractSemanticAlignmentTest::test_each_pair_rejects_removed_retargeted_or_unregistered_reference',
            'basis' => 'E026 MD-S063-R0164: independently selected \'whether prior safe state was preserved\' on the owner side and \'Preserve evidence and the prior active publication\' on the registered target side; multiple independently selected shared dimensions, current role registration, and missing/inverted clause probes are asserted per row. E027 successor: exact registered governing source line resolves to this target; removed/retargeted/unregistered reference is rejected while E026 semantic clause probes remain. Policy alignment only; no operator execution is inferred.',
        ],
        'MD-S063-R0165' => [
            'positive' => 'B19CrossContractSemanticAlignmentTest::test_each_pair_preserves_its_independently_named_semantic_fact',
            'negative' => 'B19CrossContractSemanticAlignmentTest::test_each_pair_rejects_removed_retargeted_or_unregistered_reference',
            'basis' => 'E026 MD-S063-R0165: independently selected \'prior current publication reference\' on the owner side and \'prior sealed publication\' on the registered target side; multiple independently selected shared dimensions, current role registration, and missing/inverted clause probes are asserted per row. E027 successor: exact registered governing source line resolves to this target; removed/retargeted/unregistered reference is rejected while E026 semantic clause probes remain. Policy alignment only; no operator execution is inferred.',
        ],
        'MD-S063-R0166' => [
            'positive' => 'B19CrossContractSemanticAlignmentTest::test_each_pair_preserves_its_independently_named_semantic_fact',
            'negative' => 'B19CrossContractSemanticAlignmentTest::test_each_pair_rejects_removed_retargeted_or_unregistered_reference',
            'basis' => 'E026 MD-S063-R0166: independently selected \'both expected and actual state\' on the owner side and \'actual and expected contexts\' on the registered target side; multiple independently selected shared dimensions, current role registration, and missing/inverted clause probes are asserted per row. E027 successor: exact registered governing source line resolves to this target; removed/retargeted/unregistered reference is rejected while E026 semantic clause probes remain. Policy alignment only; no operator execution is inferred.',
        ],
        'MD-S063-R0167' => [
            'positive' => 'B19CrossContractSemanticAlignmentTest::test_each_pair_preserves_its_independently_named_semantic_fact',
            'negative' => 'B19CrossContractSemanticAlignmentTest::test_each_pair_rejects_removed_retargeted_or_unregistered_reference',
            'basis' => 'E026 MD-S063-R0167: independently selected \'Evidence packs must not imply readability for an unsealed or non-current publication\' on the owner side and \'one active, sealed\' on the registered target side; multiple independently selected shared dimensions, current role registration, and missing/inverted clause probes are asserted per row. E027 successor: exact registered governing source line resolves to this target; removed/retargeted/unregistered reference is rejected while E026 semantic clause probes remain. Policy alignment only; no operator execution is inferred.',
        ],
        'MD-S068-R0027' => [
            'positive' => 'B19CrossContractSemanticAlignmentTest::test_each_pair_preserves_its_independently_named_semantic_fact',
            'negative' => 'B19CrossContractSemanticAlignmentTest::test_each_pair_rejects_removed_retargeted_or_unregistered_reference',
            'basis' => 'E026 MD-S068-R0027: independently selected \'sourced from an actual run\' on the owner side and \'real executed run\' on the registered target side; multiple independently selected shared dimensions, current role registration, and missing/inverted clause probes are asserted per row. E027 successor: exact registered governing source line resolves to this target; removed/retargeted/unregistered reference is rejected while E026 semantic clause probes remain. Policy alignment only; no operator execution is inferred.',
        ],
        'MD-S068-R0028' => [
            'positive' => 'B19CrossContractSemanticAlignmentTest::test_each_pair_preserves_its_independently_named_semantic_fact',
            'negative' => 'B19CrossContractSemanticAlignmentTest::test_each_pair_rejects_removed_retargeted_or_unregistered_reference',
            'basis' => 'E026 MD-S068-R0028: independently selected \'illustrative JSON shapes with placeholder hashes\' on the owner side and \'non-authoritative illustrative/example artifacts\' on the registered target side; multiple independently selected shared dimensions, current role registration, and missing/inverted clause probes are asserted per row. E027 successor: exact registered governing source line resolves to this target; removed/retargeted/unregistered reference is rejected while E026 semantic clause probes remain. Policy alignment only; no operator execution is inferred.',
        ],
        'MD-S068-R0029' => [
            'positive' => 'B19CrossContractSemanticAlignmentTest::test_each_pair_preserves_its_independently_named_semantic_fact',
            'negative' => 'B19CrossContractSemanticAlignmentTest::test_each_pair_rejects_removed_retargeted_or_unregistered_reference',
            'basis' => 'E026 MD-S068-R0029: independently selected \'real execution identity\' on the owner side and \'real execution identity\' on the registered target side; multiple independently selected shared dimensions, current role registration, and missing/inverted clause probes are asserted per row. E027 successor: exact registered governing source line resolves to this target; removed/retargeted/unregistered reference is rejected while E026 semantic clause probes remain. Policy alignment only; no operator execution is inferred.',
        ],
        'MD-S071-R0045' => [
            'positive' => 'B19CrossContractSemanticAlignmentTest::test_each_pair_preserves_its_independently_named_semantic_fact',
            'negative' => 'B19CrossContractSemanticAlignmentTest::test_each_pair_rejects_removed_retargeted_or_unregistered_reference',
            'basis' => 'E026 MD-S071-R0045: independently selected \'preserve prior publication and hold/fail requested date\' on the owner side and \'Preserve evidence and the prior active publication\' on the registered target side; multiple independently selected shared dimensions, current role registration, and missing/inverted clause probes are asserted per row. E027 successor: exact registered governing source line resolves to this target; removed/retargeted/unregistered reference is rejected while E026 semantic clause probes remain. Policy alignment only; no operator execution is inferred.',
        ],
        'MD-S071-R0047' => [
            'positive' => 'B19CrossContractSemanticAlignmentTest::test_each_pair_preserves_its_independently_named_semantic_fact',
            'negative' => 'B19CrossContractSemanticAlignmentTest::test_each_pair_rejects_removed_retargeted_or_unregistered_reference',
            'basis' => 'E026 MD-S071-R0047: independently selected \'Preserve prior current publication\' on the owner side and \'prior current publication remains preserved\' on the registered target side; multiple independently selected shared dimensions, current role registration, and missing/inverted clause probes are asserted per row. E027 successor: exact registered governing source line resolves to this target; removed/retargeted/unregistered reference is rejected while E026 semantic clause probes remain. Policy alignment only; no operator execution is inferred.',
        ],
        'MD-S075-R0304' => [
            'positive' => 'B19CrossContractSemanticAlignmentTest::test_each_pair_preserves_its_independently_named_semantic_fact',
            'negative' => 'B19CrossContractSemanticAlignmentTest::test_each_pair_rejects_removed_retargeted_or_unregistered_reference',
            'basis' => 'E026 MD-S075-R0304: independently selected \'summary must not imply requested date is readable\' on the owner side and \'Evidence packs must not imply readability for an unsealed or non-current publication\' on the registered target side; multiple independently selected shared dimensions, current role registration, and missing/inverted clause probes are asserted per row. E027 successor: exact registered governing source line resolves to this target; removed/retargeted/unregistered reference is rejected while E026 semantic clause probes remain. Policy alignment only; no operator execution is inferred.',
        ],
        'MD-S075-R0305' => [
            'positive' => 'B19CrossContractSemanticAlignmentTest::test_each_pair_preserves_its_independently_named_semantic_fact',
            'negative' => 'B19CrossContractSemanticAlignmentTest::test_each_pair_rejects_removed_retargeted_or_unregistered_reference',
            'basis' => 'E026 MD-S075-R0305: independently selected \'Publication safety: requested date not readable\' on the owner side and \'requested date remains non-readable\' on the registered target side; multiple independently selected shared dimensions, current role registration, and missing/inverted clause probes are asserted per row. E027 successor: exact registered governing source line resolves to this target; removed/retargeted/unregistered reference is rejected while E026 semantic clause probes remain. Policy alignment only; no operator execution is inferred.',
        ],
        'MD-S075-R0306' => [
            'positive' => 'B19CrossContractSemanticAlignmentTest::test_each_pair_preserves_its_independently_named_semantic_fact',
            'negative' => 'B19CrossContractSemanticAlignmentTest::test_each_pair_rejects_removed_retargeted_or_unregistered_reference',
            'basis' => 'E026 MD-S075-R0306: independently selected \'unchanged rerun must not claim a publication switch\' on the owner side and \'material correction\' on the registered target side; multiple independently selected shared dimensions, current role registration, and missing/inverted clause probes are asserted per row. E027 successor: exact registered governing source line resolves to this target; removed/retargeted/unregistered reference is rejected while E026 semantic clause probes remain. Policy alignment only; no operator execution is inferred.',
        ],
        'MD-S075-R0307' => [
            'positive' => 'B19CrossContractSemanticAlignmentTest::test_each_pair_preserves_its_independently_named_semantic_fact',
            'negative' => 'B19CrossContractSemanticAlignmentTest::test_each_pair_rejects_removed_retargeted_or_unregistered_reference',
            'basis' => 'E026 MD-S075-R0307: independently selected \'replay-result status must be explicit\' on the owner side and \'PASS`: all expected values\' on the registered target side; multiple independently selected shared dimensions, current role registration, and missing/inverted clause probes are asserted per row. E027 successor: exact registered governing source line resolves to this target; removed/retargeted/unregistered reference is rejected while E026 semantic clause probes remain. Policy alignment only; no operator execution is inferred.',
        ],
        'MD-S075-R0308' => [
            'positive' => 'B19CrossContractSemanticAlignmentTest::test_each_pair_preserves_its_independently_named_semantic_fact',
            'negative' => 'B19CrossContractSemanticAlignmentTest::test_each_pair_rejects_removed_retargeted_or_unregistered_reference',
            'basis' => 'E026 MD-S075-R0308: independently selected \'readiness/freshness states\' on the owner side and \'They are independent facts\' on the registered target side; multiple independently selected shared dimensions, current role registration, and missing/inverted clause probes are asserted per row. E027 successor: exact registered governing source line resolves to this target; removed/retargeted/unregistered reference is rejected while E026 semantic clause probes remain. Policy alignment only; no operator execution is inferred.',
        ],
        'MD-S076-R0049' => [
            'positive' => 'B19CrossContractSemanticAlignmentTest::test_each_pair_preserves_its_independently_named_semantic_fact',
            'negative' => 'B19CrossContractSemanticAlignmentTest::test_each_pair_rejects_removed_retargeted_or_unregistered_reference',
            'basis' => 'E026 MD-S076-R0049: independently selected \'real `run_summary.json`\' on the owner side and \'run summary\' on the registered target side; multiple independently selected shared dimensions, current role registration, and missing/inverted clause probes are asserted per row. E027 successor: exact registered governing source line resolves to this target; removed/retargeted/unregistered reference is rejected while E026 semantic clause probes remain. Policy alignment only; no operator execution is inferred.',
        ],
        'MD-S076-R0050' => [
            'positive' => 'B19CrossContractSemanticAlignmentTest::test_each_pair_preserves_its_independently_named_semantic_fact',
            'negative' => 'B19CrossContractSemanticAlignmentTest::test_each_pair_rejects_removed_retargeted_or_unregistered_reference',
            'basis' => 'E026 MD-S076-R0050: independently selected \'real `run_summary.json`\' on the owner side and \'run_summary.json\' on the registered target side; multiple independently selected shared dimensions, current role registration, and missing/inverted clause probes are asserted per row. E027 successor: exact registered governing source line resolves to this target; removed/retargeted/unregistered reference is rejected while E026 semantic clause probes remain. Policy alignment only; no operator execution is inferred.',
        ],
        'MD-S076-R0051' => [
            'positive' => 'B19CrossContractSemanticAlignmentTest::test_each_pair_preserves_its_independently_named_semantic_fact',
            'negative' => 'B19CrossContractSemanticAlignmentTest::test_each_pair_rejects_removed_retargeted_or_unregistered_reference',
            'basis' => 'E026 MD-S076-R0051: independently selected \'real test ID\' on the owner side and \'actual `test_id`\' on the registered target side; multiple independently selected shared dimensions, current role registration, and missing/inverted clause probes are asserted per row. E027 successor: exact registered governing source line resolves to this target; removed/retargeted/unregistered reference is rejected while E026 semantic clause probes remain. Policy alignment only; no operator execution is inferred.',
        ],
        'MD-S076-R0052' => [
            'positive' => 'B19CrossContractSemanticAlignmentTest::test_each_pair_preserves_its_independently_named_semantic_fact',
            'negative' => 'B19CrossContractSemanticAlignmentTest::test_each_pair_rejects_removed_retargeted_or_unregistered_reference',
            'basis' => 'E026 MD-S076-R0052: independently selected \'Illustrative examples are useful\' on the owner side and \'non-authoritative illustrative/example artifacts\' on the registered target side; multiple independently selected shared dimensions, current role registration, and missing/inverted clause probes are asserted per row. E027 successor: exact registered governing source line resolves to this target; removed/retargeted/unregistered reference is rejected while E026 semantic clause probes remain. Policy alignment only; no operator execution is inferred.',
        ],
        'MD-S071-R0046' => [
            'positive' => 'B19CrossContractSemanticAlignmentTest::test_each_pair_preserves_its_independently_named_semantic_fact',
            'negative' => 'B19CrossContractSemanticAlignmentTest::test_each_pair_rejects_removed_retargeted_or_unregistered_reference',
            'basis' => 'E026 MD-S071-R0046: operator prior-publication protection agrees with the incident matrix integrity fail-closed and verified-gateway condition; missing or inverted clauses fail the per-row guard. E027 successor: governing reference identity is checked and removal/retargeting rejected; E026 semantic clause probes remain. Policy alignment only; runtime incident classification is held.',
        ],

        // E026: bounded runtime ownership and publication-safety scenarios; takeover remains unproved.
        'MD-S077-R0019' => [
            'positive' => 'MarketDataPipelineIntegrationTest::test_run_daily_correction_with_changed_artifacts_and_promotion_failure_holds_and_preserves_prior_current_publication',
            'negative' => 'MarketDataPipelineIntegrationTest::test_promote_daily_with_force_replace_switches_current_and_records_audit_event',
            'basis' => 'E026 A real approved correction whose pointer promotion throws after sealing remains HELD/non-readable; prior publication and pointer keep IDs 1/90, candidate remains sealed/non-current, and no CORRECTION_PUBLISHED event is fabricated. Authorized successful replacement switches only after safety checks. Focused 6/6 runtime tests and restored force-interlock mutation; no universal concurrent ownership claim.',
        ],
        'MD-S077-R0021' => [
            'positive' => 'MarketDataPipelineIntegrationTest::test_promote_daily_without_force_replace_holds_when_valid_current_exists',
            'negative' => 'MarketDataPipelineIntegrationTest::test_promote_daily_with_force_replace_switches_current_and_records_audit_event',
            'basis' => 'E026 A competing unforced run finalizes HELD/NOT_READABLE with RUN_LOCK_CONFLICT while the prior owner pointer remains ID 1/run 90. A separately reasoned authorized replacement reaches SUCCESS/READABLE; removing the force interlock makes the first test red. Focused 6/6 runtime tests and restored force-interlock mutation; no universal concurrent ownership claim.',
        ],
        'MD-S077-R0022' => [
            'positive' => 'MarketDataPipelineIntegrationTest::test_promote_daily_without_force_replace_holds_when_valid_current_exists',
            'negative' => 'MarketDataPipelineIntegrationTest::test_promote_daily_with_force_replace_switches_current_and_records_audit_event',
            'basis' => 'E026 A competing unforced run cannot switch the existing current pointer, while the authorized replacement updates pointer, old/new current flags and supersession links atomically. The bypass mutant wrongly succeeds and is detected. Focused 6/6 runtime tests and restored force-interlock mutation; no universal concurrent ownership claim.',
        ],
        'MD-S077-R0029' => [
            'positive' => 'MarketDataPipelineIntegrationTest::test_run_daily_correction_with_changed_artifacts_and_promotion_failure_holds_and_preserves_prior_current_publication',
            'negative' => 'MarketDataPipelineIntegrationTest::test_promote_daily_with_force_replace_switches_current_and_records_audit_event',
            'basis' => 'E026 An injected loss of promotion ownership leaves the prior sealed current pointer and publication active, with a sealed but non-current candidate and a WARN RUN_LOCK_CONFLICT event; the controlled successful replacement distinguishes a safe decision from uncertainty. This is limited to the executed promotion failure, not stale-owner takeover. Focused 6/6 runtime tests and restored force-interlock mutation; no universal concurrent ownership claim.',
        ],
        'MD-S077-R0032' => [
            'positive' => 'MarketDataPipelineIntegrationTest::test_run_daily_persists_full_db_backed_pipeline_and_current_publication',
            'negative' => 'MarketDataPipelineIntegrationTest::test_binding_rejects_ownership_mismatch',
            'basis' => 'E026 The real pipeline hashes, binds and seals its own candidate; an attempted bind of a different run to that publication is rejected with INPUT_CAPTURE_BINDING_OWNERSHIP_MISMATCH before publication. The separate direct seal verification rejects ownership mismatch too. No stale-owner takeover is inferred. Focused 6/6 runtime tests and restored force-interlock mutation; no universal concurrent ownership claim.',
        ],
        'MD-S077-R0037' => [
            'positive' => 'MarketDataPipelineIntegrationTest::test_promote_daily_with_force_replace_switches_current_and_records_audit_event',
            'negative' => 'MarketDataPipelineIntegrationTest::test_promote_daily_without_force_replace_holds_when_valid_current_exists',
            'basis' => 'E026 On controlled replacement the current pointer run_id equals the successful owner run and the audit event belongs to that run; on an unforced conflict the pointer continues to identify the prior owner run 90. This proves owner identity for these observed paths only. Focused 6/6 runtime tests and restored force-interlock mutation; no universal concurrent ownership claim.',
        ],
        'MD-S077-R0040' => [
            'positive' => 'MarketDataPipelineIntegrationTest::test_promote_daily_with_force_replace_switches_current_and_records_audit_event',
            'negative' => 'MarketDataPipelineIntegrationTest::test_run_daily_correction_with_changed_artifacts_and_promotion_failure_holds_and_preserves_prior_current_publication',
            'basis' => 'E026 The successful replacement leaves exactly the new publication current, old publication non-current and explicit supersession; the injected promotion failure leaves the prior pointer and publication current and the candidate non-current. Resulting resolution is asserted from persisted rows, not a workflow document. Focused 6/6 runtime tests and restored force-interlock mutation; no universal concurrent ownership claim.',
        ],

        // E026: additional bounded owner-run producer and correction-switch assertions.
        'MD-S077-R0006' => [
            'positive' => 'MarketDataPipelineIntegrationTest::test_run_daily_correction_replaces_current_publication_and_marks_correction_published',
            'negative' => 'MarketDataPipelineIntegrationTest::test_run_daily_correction_with_changed_artifacts_and_promotion_failure_holds_and_preserves_prior_current_publication',
            'basis' => 'E026 Approved changed correction execution produces a new current publication with an explicit CORRECTION_PUBLISHED outcome; a promotion failure leaves the prior pointer current and candidate non-current. This covers the correction switch as a publish-critical stage, not lease takeover. Raw focused SQLite execution retained; no stale-owner or concurrent takeover claim.',
        ],
        'MD-S077-R0015' => [
            'positive' => 'MarketDataPipelineIntegrationTest::test_run_daily_persists_full_db_backed_pipeline_and_current_publication',
            'negative' => 'MarketDataPipelineIntegrationTest::test_run_daily_low_coverage_with_fallback_holds_requested_date_and_preserves_old_readable_publication',
            'basis' => 'E026 The real owning run retains source, calendar, mapping, raw lineage, indicator, eligibility, config and publication captures for one coherent candidate; incomplete coverage holds the requested date rather than publishing a partial candidate. Raw focused SQLite execution retained; no stale-owner or concurrent takeover claim.',
        ],
        'MD-S077-R0016' => [
            'positive' => 'MarketDataPipelineIntegrationTest::test_run_daily_persists_full_db_backed_pipeline_and_current_publication',
            'negative' => 'MarketDataPipelineIntegrationTest::test_run_daily_low_coverage_with_fallback_holds_requested_date_and_preserves_old_readable_publication',
            'basis' => 'E026 The same real owner run records non-null bars, indicators, eligibility and observation-manifest hashes before a readable pointer exists; low coverage cannot substitute a readable hash claim. Raw focused SQLite execution retained; no stale-owner or concurrent takeover claim.',
        ],
        'MD-S077-R0017' => [
            'positive' => 'MarketDataPipelineIntegrationTest::test_run_daily_persists_full_db_backed_pipeline_and_current_publication',
            'negative' => 'MarketDataPipelineIntegrationTest::test_binding_rejects_ownership_mismatch',
            'basis' => 'E026 The real run seals a publication linked to its run ID and persisted input bindings; binding a different run to its candidate is refused with INPUT_CAPTURE_BINDING_OWNERSHIP_MISMATCH. The direct seal ownership guard is separately executed in the focused six-test batch. Raw focused SQLite execution retained; no stale-owner or concurrent takeover claim.',
        ],
        'MD-S077-R0018' => [
            'positive' => 'MarketDataPipelineIntegrationTest::test_run_daily_persists_full_db_backed_pipeline_and_current_publication',
            'negative' => 'MarketDataPipelineIntegrationTest::test_run_daily_low_coverage_with_fallback_holds_requested_date_and_preserves_old_readable_publication',
            'basis' => 'E026 The successful owner records terminal SUCCESS, READABLE and COMPLETED; a low-coverage run finishes HELD and keeps the requested publication non-readable. This is the observed terminal decision, not universal incident classification. Raw focused SQLite execution retained; no stale-owner or concurrent takeover claim.',
        ],

        // E026: separate evidence-preservation and competing-identity assertions.
        'MD-S077-R0023' => [
            'positive' => 'MarketDataPipelineIntegrationTest::test_promote_daily_without_force_replace_holds_when_valid_current_exists',
            'negative' => 'MarketDataPipelineIntegrationTest::test_promote_daily_with_force_replace_switches_current_and_records_audit_event',
            'basis' => 'E026 The unforced competing run is HELD and the entire persisted prior owner publication row is byte-for-byte equal before and after the attempt, while the pointer stays on publication1/run90. The separately authorized replacement changes only through the audited switch. The force-interlock bypass mutation turns the blocked scenario red. Focused 6/65 runtime tests and restored force-interlock mutation; no general concurrent or stale-takeover claim.',
        ],
        'MD-S077-R0038' => [
            'positive' => 'MarketDataPipelineIntegrationTest::test_promote_daily_without_force_replace_holds_when_valid_current_exists',
            'negative' => 'MarketDataPipelineIntegrationTest::test_promote_daily_with_force_replace_switches_current_and_records_audit_event',
            'basis' => 'E026 The blocked competitor has a distinct run ID from owner run90; its own RUN_FINALIZED event carries RUN_LOCK_CONFLICT, while the pointer continues to identify owner run90. The authorized replacement path records the new owner run ID and audit event. Both identities are persisted observations, not inferred from a document. Focused 6/65 runtime tests and restored force-interlock mutation; no general concurrent or stale-takeover claim.',
        ],

        // E028: exact duplicate and fenced-worker terminal outcomes, plus runtime help/catalog identity.
        'MD-S067-R0016' => [
            'positive' => 'MarketDataPipelineIntegrationTest::test_verified_identical_target_persists_skipped_without_switching_current',
            'negative' => 'MarketDataPipelineIntegrationTest::test_promote_daily_without_force_replace_holds_when_valid_current_exists',
            'basis' => 'E028: independently completed current publication and new sealed candidate have matching mandatory artifact/binding comparison plus config/product identity. The producer persists SKIPPED/NOT_READABLE, records the exact preserved current publication ID, and leaves pointer/current mirrors unchanged. A changed target remains HELD/RUN_LOCK_CONFLICT; SKIPPED-to-HELD mutant red and restored. No unverified current is treated as duplicate.',
        ],
        'MD-S067-R0017' => [
            'positive' => 'MarketDataPipelineIntegrationTest::test_fenced_competing_worker_persists_blocked_run_without_touching_owner',
            'negative' => 'B19OwnerFenceMariaDbTest::test_mariadb_migration_and_two_process_date_owner_fence',
            'basis' => 'E028: cross-mode fenced request persists a separate COMPLETED/BLOCKED/NOT_READABLE run with RUN_LOCK_CONFLICT event and owner/blocked IDs while original owner fence and publication stay unchanged. MariaDB two-process contender confirms persisted BLOCKED status on the production enum. BLOCKED-to-HELD mutant red and restored.',
        ],
        'MD-S064-R0010' => [
            'positive' => 'B19RuntimeCommandCatalogTest::test_registered_market_data_commands_and_options_match_the_operator_catalog',
            'negative' => 'B19RuntimeCommandCatalogTest::test_registered_market_data_commands_and_options_match_the_operator_catalog',
            'basis' => 'E028: actual artisan list and help --format=json for all 41 registered market-data commands are compared with every exact command and command-specific option in commands/README.md. Three missing registered names were corrected. An invented option in the catalog turns the guard red and is byte-restored. The catalog explicitly does not confer safety merely from a force-named option.',
        ],

        // E027: date-scoped runtime owner fence, adversarial two-process acquisition and conservative stale takeover.
        'MD-S077-R0003' => [
            'positive' => 'B19OwnerFenceMariaDbTest::test_mariadb_migration_and_two_process_date_owner_fence',
            'negative' => 'MarketDataPipelineIntegrationTest::test_publish_critical_owner_fences_cross_source_and_request_mode_competitors',
            'basis' => 'E027 SQLite proof retained. E028: migrated InnoDB primary-key date fence serializes two independent PHP processes across source/request modes; one active run and matching fence owner survive, contender persists BLOCKED. Transaction retry handles a real InnoDB deadlock; removing retry turns the MariaDB test red. No worker-thread exclusivity within one run is claimed.',
        ],
        'MD-S077-R0004' => [
            'positive' => 'B19OwnerFenceMariaDbTest::test_mariadb_migration_and_two_process_date_owner_fence',
            'negative' => 'MarketDataPipelineIntegrationTest::test_direct_publication_writes_refuse_an_applicable_run_with_no_date_fence',
            'basis' => 'E027 SQLite proof retained. E028 MariaDB injects a distinct run after real two-process acquisition and rejects its readable finalize, direct seal and promotion while prior pointer stays unchanged. Direct entry with no fence is rejected before hash/candidate work; missing-fence mutant red and restored. The separately rejected worker has its own BLOCKED audit run, not a readable finalize.',
        ],
        'MD-S077-R0013' => [
            'positive' => 'B19OwnerFenceMariaDbTest::test_mariadb_migration_and_two_process_date_owner_fence',
            'negative' => 'MarketDataPipelineIntegrationTest::test_publish_critical_owner_fences_cross_source_and_request_mode_competitors',
            'basis' => 'E027 injected dual-active rejection and source-scope mutant retained. E028 migrated MariaDB InnoDB unique date key plus two-process cross-mode contention admit exactly one active owner; rejected contender persists completed BLOCKED rather than a second active owner. Removed transaction retry mutant exposes the actual deadlock path and turns red.',
        ],
        'MD-S077-R0025' => [
            'positive' => 'MarketDataPipelineIntegrationTest::test_promote_seed_cannot_create_second_active_publish_owner',
            'negative' => 'MarketDataPipelineIntegrationTest::test_publish_critical_owner_fences_cross_source_and_request_mode_competitors',
            'basis' => 'E027: blocked promote seed and cross-mode requests record diagnostic owner/competitor context and BLOCKED decision; they create no executable competing publish owner, seal or current pointer. A directly injected non-owner remains non-readable and cannot finalize/seal/publish.',
        ],
        'MD-S077-R0027' => [
            'positive' => 'MarketDataPipelineIntegrationTest::test_stale_publish_owner_takeover_requires_empty_candidate_and_records_decision',
            'negative' => 'MarketDataPipelineIntegrationTest::test_stale_owner_with_ambiguous_partial_publication_blocks_takeover',
            'basis' => 'E027: elapsed stale threshold alone does not transfer ownership. The recovery producer inspects candidate publication, hashes, seal and current binding; an empty/untrustworthy candidate is explicitly abandoned, while a real unsealed partial publication blocks takeover. Trust-bypass mutant makes the negative red.',
        ],
        'MD-S077-R0028' => [
            'positive' => 'MarketDataPipelineIntegrationTest::test_stale_takeover_rejection_preserves_prior_readable_publication',
            'negative' => 'MarketDataPipelineIntegrationTest::test_stale_owner_with_unverified_prior_pointer_blocks_takeover',
            'basis' => 'E027: partial candidate leaves the exact prior pointer/publication rows unchanged; an orphan prior pointer blocks even an otherwise empty stale candidate. The recovery decision records unverified context rather than guessing readability or restoring from a malformed pointer.',
        ],
        'MD-S077-R0031' => [
            'positive' => 'MarketDataPipelineIntegrationTest::test_stale_publish_owner_takeover_requires_empty_candidate_and_records_decision',
            'negative' => 'MarketDataPipelineIntegrationTest::test_publish_critical_owner_fences_cross_source_and_request_mode_competitors',
            'basis' => 'E027: configured active_run_stale_minutes=60 and a two-hour-old owner allow takeover only after the trust check; a live owner blocks cross-mode competition. Prior owner becomes CANCELLED, new owner receives the fence, and old owner cannot resume a publish-critical stage.',
        ],
        'MD-S077-R0033' => [
            'positive' => 'MarketDataPipelineIntegrationTest::test_stale_publish_owner_takeover_requires_empty_candidate_and_records_decision',
            'negative' => 'MarketDataPipelineIntegrationTest::test_stale_owner_with_ambiguous_partial_publication_blocks_takeover',
            'basis' => 'E027: persisted old-owner cancellation and new-owner takeover events carry both run IDs, candidate trust, partial-publication exclusion and decision; blocked partial candidate carries a distinct recorded BLOCKED reason. No unobserved operator action is inferred.',
        ],
        'MD-S077-R0034' => [
            'positive' => 'B19OwnerFenceMariaDbTest::test_mariadb_migration_and_two_process_date_owner_fence',
            'negative' => 'MarketDataPipelineIntegrationTest::test_publish_critical_owner_fences_cross_source_and_request_mode_competitors',
            'basis' => 'E027 real owner success retained. E028 after MariaDB process contention injects a distinct competitor; readable finalize is refused before state mutation and its terminal status remains null. The prior seeded publication/pointer remains the sole current outcome. Three affected publication regressions pass; no second readable finalization is inferred from a sequential-only test.',
        ],
        'MD-S077-R0035' => [
            'positive' => 'B19OwnerFenceMariaDbTest::test_mariadb_migration_and_two_process_date_owner_fence',
            'negative' => 'MarketDataPipelineIntegrationTest::test_publish_critical_owner_fences_cross_source_and_request_mode_competitors',
            'basis' => 'E027 controlled replacement and seal-fence mutant retained. E028 MariaDB direct non-owner seal and promotion are refused, including missing-fence direct entry; prior current pointer/publication and historical raw read remain available, with one current row. No competing readable publication outcome is produced.',
        ],
        'MD-S077-R0039' => [
            'positive' => 'MarketDataPipelineIntegrationTest::test_stale_publish_owner_takeover_requires_empty_candidate_and_records_decision',
            'negative' => 'MarketDataPipelineIntegrationTest::test_stale_owner_with_ambiguous_partial_publication_blocks_takeover',
            'basis' => 'E027: persisted RUN_OWNER_TAKEOVER event binds previous owner run ID and new owner run ID to trust assessment and ALLOWED decision; rejected partial state persists STALE_OWNER_TAKEOVER_BLOCKED with UNVERIFIED trust and exact reason. Resulting fence owner is asserted from storage.',
        ],

        // E029 operational slice: executable status, scheduled outcomes and real MariaDB evidence.
        'MD-S064-R0002' => [
            'positive' => 'B19OperationalCommandsMariaDbTest::test_registered_scheduled_command_executes_real_chain_then_idempotently_skips_verified_current',
            'negative' => 'B19OperationalCommandsMariaDbTest::test_due_run_source_failure_retains_attempts_and_prior_readable_date_without_claiming_success',
            'basis' => 'E029: registered status command reports requested/expected/acquired/canonicalized/readable frontiers and is bracketed by byte-identical requested-date runs/events/pointer and unchanged observation count. Conflicting date/latest selectors and invalid selectors refuse; MariaDB outage status remains explicitly unhealthy.',
        ],
        'MD-S064-R0003' => [
            'positive' => 'B19OperationalCommandsMariaDbTest::test_registered_scheduled_command_executes_real_chain_then_idempotently_skips_verified_current',
            'negative' => 'B19OperationalCommandsMariaDbTest::test_due_run_source_failure_retains_attempts_and_prior_readable_date_without_claiming_success',
            'basis' => 'E029: real registered scheduled command executes import, promotion, verified-current status, and appends SCHEDULER_OUTCOME; a repeated invocation skips without new runs/observations/pointer writes. A provider outage on the separately resolved due date yields HELD/NOT_READABLE, nonzero exit, and unchanged prior readable publication.',
        ],
        'MD-S070-R0002' => [
            'positive' => 'B19OperationalObservabilityMariaDbTest::test_real_run_status_events_counts_hash_seal_config_and_export_have_primary_provenance',
            'negative' => 'B19OperationalObservabilityMariaDbTest::test_missing_contradictory_and_unavailable_primary_facts_never_report_operational_health',
            'basis' => 'E029 MariaDB: persisted eod_runs terminal status is primary; an appended contradictory WARN message cannot overwrite SUCCESS. Missing terminal authority is mutated and restored and makes status unhealthy.',
        ],
        'MD-S070-R0003' => [
            'positive' => 'B19OperationalObservabilityMariaDbTest::test_real_run_status_events_counts_hash_seal_config_and_export_have_primary_provenance',
            'negative' => 'B19OperationalObservabilityMariaDbTest::test_missing_contradictory_and_unavailable_primary_facts_never_report_operational_health',
            'basis' => 'E029 MariaDB: real stage/run events and exported reason summary retain producer provenance; absent trail mutation is detected as RUN_EVENT_TRAIL_MISSING. The mutation is transactionally restored.',
        ],
        'MD-S070-R0004' => [
            'positive' => 'B19OperationalObservabilityMariaDbTest::test_real_run_status_events_counts_hash_seal_config_and_export_have_primary_provenance',
            'negative' => 'B19OperationalObservabilityMariaDbTest::test_missing_contradictory_and_unavailable_primary_facts_never_report_operational_health',
            'basis' => 'E029 MariaDB: bars, indicators and eligibility persisted row counts match the independent frozen candidate-v5 expected publication. Each missing row-count field is mutated/restored and prevents healthy status.',
        ],
        'MD-S070-R0006' => [
            'positive' => 'B19OperationalObservabilityMariaDbTest::test_real_run_status_events_counts_hash_seal_config_and_export_have_primary_provenance',
            'negative' => 'B19OperationalObservabilityMariaDbTest::test_missing_contradictory_and_unavailable_primary_facts_never_report_operational_health',
            'basis' => 'E029 MariaDB: three persisted final hashes match the independent expected publication manifest; missing or malformed hashes are independently mutated/restored and prevent health.',
        ],
        'MD-S070-R0007' => [
            'positive' => 'B19OperationalObservabilityMariaDbTest::test_real_run_status_events_counts_hash_seal_config_and_export_have_primary_provenance',
            'negative' => 'B19OperationalObservabilityMariaDbTest::test_missing_contradictory_and_unavailable_primary_facts_never_report_operational_health',
            'basis' => 'E029 MariaDB: sealed timestamp and exported SEALED manifest are present on a verified current consumer publication; removing seal metadata is a restored negative that makes status unhealthy.',
        ],
        'MD-S070-R0008' => [
            'positive' => 'B19OperationalObservabilityMariaDbTest::test_real_run_status_events_counts_hash_seal_config_and_export_have_primary_provenance',
            'negative' => 'B19OperationalObservabilityMariaDbTest::test_missing_contradictory_and_unavailable_primary_facts_never_report_operational_health',
            'basis' => 'E029 MariaDB: snapshot ID, UID and resolved-config hash verify against the immutable run config; absent ID and contradictory hash mutations restore exactly and make status unhealthy.',
        ],
        'MD-S070-R0009' => [
            'positive' => 'B19OperationalObservabilityMariaDbTest::test_real_run_status_events_counts_hash_seal_config_and_export_have_primary_provenance',
            'negative' => 'B19OperationalCommandsMariaDbTest::test_due_run_source_failure_retains_attempts_and_prior_readable_date_without_claiming_success',
            'basis' => 'E029 MariaDB: the requested date resolves to its persisted SUCCESS run; a distinct future requested date resolves HELD and never inherits the prior run outcome as success.',
        ],
        'MD-S070-R0010' => [
            'positive' => 'B19OperationalCommandsMariaDbTest::test_registered_scheduled_command_executes_real_chain_then_idempotently_skips_verified_current',
            'negative' => 'B19OperationalCommandsMariaDbTest::test_due_run_source_failure_retains_attempts_and_prior_readable_date_without_claiming_success',
            'basis' => 'E029 MariaDB: a normal run exposes the effective requested date; source outage exposes the persisted older fallback effective date explicitly, while requested date remains due and publication is unreadable.',
        ],
        'MD-S070-R0011' => [
            'positive' => 'B19OperationalCommandsMariaDbTest::test_due_run_source_failure_retains_attempts_and_prior_readable_date_without_claiming_success',
            'negative' => 'B19OperationalObservabilityMariaDbTest::test_real_run_status_events_counts_hash_seal_config_and_export_have_primary_provenance',
            'basis' => 'E029 MariaDB: coverage failure maps to the persisted PUBLISH_BARS event stage on the held requested date; a successful run reports no failed/held stage. No stage is inferred from log text.',
        ],
        'MD-S070-R0012' => [
            'positive' => 'B19OperationalObservabilityMariaDbTest::test_real_run_status_events_counts_hash_seal_config_and_export_have_primary_provenance',
            'negative' => 'B19OperationalCommandsMariaDbTest::test_due_run_source_failure_retains_attempts_and_prior_readable_date_without_claiming_success',
            'basis' => 'E029 MariaDB: reason codes are traceable to event and persisted source/final reason fields. A contradictory WARN reason is included as supporting evidence without replacing terminal authority; timeout and coverage failure are both retained for outage diagnosis.',
        ],
        'MD-S070-R0013' => [
            'positive' => 'B19OperationalObservabilityMariaDbTest::test_real_run_status_events_counts_hash_seal_config_and_export_have_primary_provenance',
            'negative' => 'B19OperationalCommandsMariaDbTest::test_due_run_source_failure_retains_attempts_and_prior_readable_date_without_claiming_success',
            'basis' => 'E029 MariaDB: independent normal execution has coverage PASS and provider outage execution retains coverage FAIL with missing count and zero ratio; provider failure does not shrink the requested universe.',
        ],
        'MD-S070-R0014' => [
            'positive' => 'B19OperationalObservabilityMariaDbTest::test_real_run_status_events_counts_hash_seal_config_and_export_have_primary_provenance',
            'negative' => 'B19OperationalObservabilityMariaDbTest::test_missing_contradictory_and_unavailable_primary_facts_never_report_operational_health',
            'basis' => 'E029 MariaDB: hashes match the independent expected manifest; missing/malformed hash mutations cause explicit unavailable defects and unhealthy status.',
        ],
        'MD-S070-R0015' => [
            'positive' => 'B19OperationalObservabilityMariaDbTest::test_real_run_status_events_counts_hash_seal_config_and_export_have_primary_provenance',
            'negative' => 'B19OperationalObservabilityMariaDbTest::test_missing_contradictory_and_unavailable_primary_facts_never_report_operational_health',
            'basis' => 'E029 MariaDB: seal metadata exists with the materializable current publication; missing seal mutation is restored and produces SEAL_PRESENCE_UNPROVEN.',
        ],
        'MD-S070-R0016' => [
            'positive' => 'B19OperationalObservabilityMariaDbTest::test_real_run_status_events_counts_hash_seal_config_and_export_have_primary_provenance',
            'negative' => 'B19OperationalObservabilityMariaDbTest::test_missing_contradictory_and_unavailable_primary_facts_never_report_operational_health',
            'basis' => 'E029 MariaDB: effective snapshot ID, UID and hash are cross-checked against eod_runs; missing and contradictory config mutations restore and make status unhealthy.',
        ],
        'MD-S070-R0017' => [
            'positive' => 'B19OperationalObservabilityMariaDbTest::test_real_run_status_events_counts_hash_seal_config_and_export_have_primary_provenance',
            'negative' => 'B19OperationalObservabilityMariaDbTest::test_missing_contradictory_and_unavailable_primary_facts_never_report_operational_health',
            'basis' => 'E029 MariaDB: eod_runs terminal status remains SUCCESS when a contradictory warning message says FAILED; event data is supporting and export primary fields agree with persisted authority.',
        ],
        'MD-S070-R0018' => [
            'positive' => 'B19OperationalObservabilityMariaDbTest::test_event_model_and_actual_mariadb_reject_update_and_delete_but_accept_append',
            'negative' => 'B19OperationalObservabilityMariaDbTest::test_event_model_and_actual_mariadb_reject_update_and_delete_but_accept_append',
            'basis' => 'E029 MariaDB: model and direct SQL update/delete are refused by runtime hooks and database triggers; original event bytes remain equal, while append succeeds. Four attempted mutations are observed and restored by rollback.',
        ],
        'MD-S070-R0019' => [
            'positive' => 'B19OperationalObservabilityMariaDbTest::test_missing_contradictory_and_unavailable_primary_facts_never_report_operational_health',
            'negative' => 'B19OperationalObservabilityMariaDbTest::test_real_run_status_events_counts_hash_seal_config_and_export_have_primary_provenance',
            'basis' => 'E029 MariaDB: a terminal run with no events is classified RUN_EVENT_TRAIL_MISSING and unhealthy; the normal real execution has a persisted event trail and exported event count.',
        ],
        'MD-S070-R0020' => [
            'positive' => 'B19OperationalObservabilityMariaDbTest::test_real_run_status_events_counts_hash_seal_config_and_export_have_primary_provenance',
            'negative' => 'B19OperationalObservabilityMariaDbTest::test_missing_contradictory_and_unavailable_primary_facts_never_report_operational_health',
            'basis' => 'E029 MariaDB: healthy status requires terminal/effective/hash/seal/config and consumer-readable evidence; each required source field is independently removed or contradicted and restored, and none reports health.',
        ],
        'MD-S041-R0057' => [
            'positive' => 'B19OperationalCommandsMariaDbTest::test_due_run_source_failure_retains_attempts_and_prior_readable_date_without_claiming_success',
            'negative' => 'B19OperationalCalendarTest::test_latest_target_requires_cutoff_and_verified_completed_session_and_exposes_unknown_states',
            'basis' => 'E029: 2030 requested target is independently calendar-resolved while acquired/canonicalized/readable frontiers remain in 2026; development frontier need not equal calendar expected date.',
        ],
        'MD-S041-R0059' => [
            'positive' => 'B19OperationalCalendarTest::test_latest_target_requires_cutoff_and_verified_completed_session_and_exposes_unknown_states',
            'negative' => 'B19OperationalCalendarTest::test_conflicting_or_unverified_calendar_cannot_authorize_a_latest_target',
            'basis' => 'E029: today 2026-03-25 after configured cutoff and verified completion resolves to today; unverified completion is refused.',
        ],
        'MD-S041-R0060' => [
            'positive' => 'B19OperationalCalendarTest::test_latest_target_requires_cutoff_and_verified_completed_session_and_exposes_unknown_states',
            'negative' => 'B19OperationalCalendarTest::test_latest_target_requires_cutoff_and_verified_completed_session_and_exposes_unknown_states',
            'basis' => 'E029: before cutoff, latest expected resolves to prior completed 2026-03-24; after cutoff it moves to the verified current session.',
        ],
        'MD-S041-R0061' => [
            'positive' => 'B19OperationalCalendarTest::test_latest_target_requires_cutoff_and_verified_completed_session_and_exposes_unknown_states',
            'negative' => 'B19OperationalCalendarTest::test_conflicting_or_unverified_calendar_cannot_authorize_a_latest_target',
            'basis' => 'E029: UNKNOWN, PARTIAL, CANCELLED and MISSING are returned explicitly while target remains prior completed session; unverified and conflicting revisions refuse authorization.',
        ],
        'MD-S078-R0001' => [
            'positive' => 'B19OperationalCommandsMariaDbTest::test_due_run_source_failure_retains_attempts_and_prior_readable_date_without_claiming_success',
            'negative' => 'B19OperationalCommandsMariaDbTest::test_missing_verified_calendar_produces_explicit_failed_scheduler_outcome',
            'basis' => 'E029 MariaDB: scheduler with machine time in 2026 targets verified completed 2030-01-02 from versioned calendar although all persisted data frontiers remain in 2026; deleting calendar evidence produces explicit FAILED with no requested date.',
        ],
        'MD-S078-R0005' => [
            'positive' => 'B19OperationalCommandsMariaDbTest::test_registered_scheduled_command_executes_real_chain_then_idempotently_skips_verified_current',
            'negative' => 'B19OperationalCommandsMariaDbTest::test_missing_verified_calendar_produces_explicit_failed_scheduler_outcome',
            'basis' => 'E029 MariaDB actual command executions cover SUCCESS_READABLE, SUCCESS_HELD, FAILED, SKIPPED_ALREADY_READABLE and SKIPPED_LOCKED. Each emits the fixed machine result shape, IDs/dates where established, reasons and next action; no zero exit is used to claim readability.',
        ],
        'MD-S078-R0008' => [
            'positive' => 'B19OwnerFenceMariaDbTest::test_mariadb_migration_and_two_process_date_owner_fence',
            'negative' => 'MarketDataPipelineIntegrationTest::test_publish_critical_owner_fences_cross_source_and_request_mode_competitors',
            'basis' => 'E029 revalidates E028 real InnoDB two-process cross-mode acquisition: exactly one active owner survives, the competing worker cannot publish, and prior pointer is retained. A source-mode fence bypass is caught by the paired adversarial test.',
        ],
        'MD-S078-R0010' => [
            'positive' => 'B19OperationalCommandsMariaDbTest::test_due_run_source_failure_retains_attempts_and_prior_readable_date_without_claiming_success',
            'negative' => 'B19OperationalCommandsMariaDbTest::test_registered_scheduled_command_executes_real_chain_then_idempotently_skips_verified_current',
            'basis' => 'E029 MariaDB: prior current publication on 2026-03-23 is preserved during requested 2030-01-02 provider failure; due outcome is HELD/NOT_READABLE and has no requested-date publication ID.',
        ],
        'MD-S078-R0011' => [
            'positive' => 'B19OperationalCommandsMariaDbTest::test_due_run_source_failure_retains_attempts_and_prior_readable_date_without_claiming_success',
            'negative' => 'B19OperationalObservabilityMariaDbTest::test_real_run_status_events_counts_hash_seal_config_and_export_have_primary_provenance',
            'basis' => 'E029 MariaDB: provider outage retains coverage universe and FAIL with one missing row and zero ratio; normal independent run records coverage PASS. No provider failure is made to look complete by shrinking its scope.',
        ],
        'MD-S078-R0012' => [
            'positive' => 'B19OperationalCommandsMariaDbTest::test_due_run_source_failure_retains_attempts_and_prior_readable_date_without_claiming_success',
            'negative' => 'B19OperationalCommandsMariaDbTest::test_registered_scheduled_command_executes_real_chain_then_idempotently_skips_verified_current',
            'basis' => 'E029 MariaDB: outage executes initial request plus two bounded retries; persisted source_attempt_count and notes record three attempts and exact preexisting observation IDs remain byte-identical. Successful idempotent repeat adds no source observation.',
        ],
        'MD-S078-R0013' => [
            'positive' => 'B19OperationalCommandsMariaDbTest::test_registered_scheduled_command_executes_real_chain_then_idempotently_skips_verified_current',
            'negative' => 'B19OperationalCommandsMariaDbTest::test_due_run_source_failure_retains_attempts_and_prior_readable_date_without_claiming_success',
            'basis' => 'E029 MariaDB: materializable sealed current is required for readable success; outage has no sealed requested-date publication, no pointer switch, and returns HELD/NOT_READABLE with status unhealthy.',
        ],
        'MD-S078-R0014' => [
            'positive' => 'B19OwnerFenceMariaDbTest::test_mariadb_migration_and_two_process_date_owner_fence',
            'negative' => 'B19OperationalCommandsMariaDbTest::test_scheduled_competitor_reports_locked_without_starting_a_second_owner',
            'basis' => 'E029 real MariaDB process contention and scheduled contender prove exactly one owner; the second scheduled request returns SKIPPED_LOCKED/RUN_LOCK_CONFLICT while the original owner token remains and no readable pointer is created.',
        ],
        'MD-S078-R0016' => [
            'positive' => 'B19OperationalCommandsMariaDbTest::test_registered_scheduled_command_executes_real_chain_then_idempotently_skips_verified_current',
            'negative' => 'B19OperationalCommandsMariaDbTest::test_scheduled_competitor_reports_locked_without_starting_a_second_owner',
            'basis' => 'E029 Laravel schedule registration records intent; separate direct registered-command execution records SCHEDULER_OUTCOME with run ID, requested date and verified publication. A blocked invocation persists SKIPPED_LOCKED rather than inferring execution from registration.',
        ],
        'MD-S078-R0018' => [
            'positive' => 'B19OwnerFenceMariaDbTest::test_mariadb_migration_and_two_process_date_owner_fence',
            'negative' => 'MarketDataPipelineIntegrationTest::test_publish_critical_owner_fences_cross_source_and_request_mode_competitors',
            'basis' => 'E029 two independent MariaDB processes are released into same-date owner acquisition and persisted results show one winner/one blocked contender; separate SQLite cross-mode adversary ensures absent contention is not substituted for exclusion proof.',
        ],
        // E031: range acquisition is exercised by the registered lifecycle command on MariaDB.
        // The two requested dates, independently seeded calendar, fixed HTTP rows, run IDs and
        // current pointers are checked together; the provider-outage run supplies the refusal side.
        'MD-S053-R0179' => [
            'positive' => 'B19RangeBackfillMariaDbProofTest::test_range_window_runs_each_requested_date_in_order_without_publishing_warmup',
            'negative' => 'B19RangeBackfillMariaDbProofTest::test_failed_provider_checkpoint_resumes_without_faking_readability_then_publishes_recovered_rows',
            'basis' => 'E031: one HTTP range supplies two rows, but the real command records distinct requested-date runs and pointers; an unavailable range creates neither requested-date pointer nor fake success.',
        ],
        'MD-S053-R0180' => [
            'positive' => 'B19RangeBackfillMariaDbProofTest::test_range_window_runs_each_requested_date_in_order_without_publishing_warmup',
            'negative' => 'B19RangeWindowWarmupCalendarTest::test_a_non_trading_requested_date_fails_fast',
            'basis' => 'E031: cases and persisted runs are exactly 2026-03-24 then 2026-03-25 despite one window response; a nontrading requested date is refused.',
        ],
        'MD-S053-R0181' => [
            'positive' => 'B19RangeBackfillMariaDbProofTest::test_range_window_runs_each_requested_date_in_order_without_publishing_warmup',
            'negative' => 'B19RangeBackfillMariaDbProofTest::test_failed_provider_checkpoint_resumes_without_faking_readability_then_publishes_recovered_rows',
            'basis' => 'E031: independently identified MariaDB run IDs differ for the two requested dates; a failed acquisition has no readable requested-date run.',
        ],
        'MD-S053-R0183' => [
            'positive' => 'B19RangeBackfillMariaDbProofTest::test_range_window_runs_each_requested_date_in_order_without_publishing_warmup',
            'negative' => 'B19RangeBackfillMariaDbProofTest::test_range_window_runs_each_requested_date_in_order_without_publishing_warmup',
            'basis' => 'E032 revalidates the E031 proposal on actual MariaDB rows for 2026-03-20, 2026-03-24 and 2026-03-25: the pre-request row is retained as support history with its exact source-observation identity, both requested dates publish, and direct promotion of the warmup-only run refuses RANGE_WINDOW_WARMUP_DATE_PROMOTION_REFUSED. Removing the source guard makes the exact refusal assertion fail; byte-restored rerun passes.',
        ],
        'MD-S053-R0185' => [
            'positive' => 'B19RangeBackfillMariaDbProofTest::test_range_window_runs_each_requested_date_in_order_without_publishing_warmup',
            'negative' => 'B19RangeBackfillMariaDbProofTest::test_manual_file_historical_fill_executes_the_registered_lifecycle_and_rejects_missing_input',
            'basis' => 'E031: each API requested-date run persists source_acquisition_mode=range_window; executed manual_file mode reports its distinct single-file mode.',
        ],
        'MD-S053-R0186' => [
            'positive' => 'B19RangeBackfillMariaDbProofTest::test_range_window_runs_each_requested_date_in_order_without_publishing_warmup',
            'negative' => 'B19RangeBackfillMariaDbProofTest::test_failed_provider_checkpoint_resumes_without_faking_readability_then_publishes_recovered_rows',
            'basis' => 'E031: both persisted date runs carry the same independently specified batch ID API_20260324_20260325_001; failed acquisition retains a nonempty batch ID rather than inventing a run.',
        ],
        'MD-S053-R0187' => [
            'positive' => 'B19RangeBackfillMariaDbProofTest::test_range_window_runs_each_requested_date_in_order_without_publishing_warmup',
            'negative' => 'B19RangeWindowWarmupWiringTest::test_a_blocked_acquisition_still_records_the_boundary_in_its_diagnostic',
            'basis' => 'E031: source_window_start is the independently seeded 2026-02-20 calendar boundary on both date runs; blocked acquisition retains its boundary in diagnostics.',
        ],
        'MD-S053-R0188' => [
            'positive' => 'B19RangeBackfillMariaDbProofTest::test_range_window_runs_each_requested_date_in_order_without_publishing_warmup',
            'negative' => 'B19RangeBackfillMariaDbProofTest::test_failed_provider_checkpoint_resumes_without_faking_readability_then_publishes_recovered_rows',
            'basis' => 'E031: source_window_end is 2026-03-25 on both runs, distinct from first requested date; the outage checkpoint identifies its failed window.',
        ],
        'MD-S053-R0189' => [
            'positive' => 'B19RangeBackfillMariaDbProofTest::test_range_window_runs_each_requested_date_in_order_without_publishing_warmup',
            'negative' => 'B19RangeWindowWarmupCalendarTest::test_warmup_start_is_the_governed_trading_day_boundary_not_calendar_day_arithmetic',
            'basis' => 'E031: both runs persist warmup_start=2026-02-20, matching the independently seeded trading calendar and differing from calendar-day subtraction.',
        ],
        'MD-S053-R0190' => [
            'positive' => 'B19RangeBackfillMariaDbProofTest::test_range_window_runs_each_requested_date_in_order_without_publishing_warmup',
            'negative' => 'B19RangeBackfillMariaDbProofTest::test_failed_provider_checkpoint_resumes_without_faking_readability_then_publishes_recovered_rows',
            'basis' => 'E031: both persisted date runs record requested_start=2026-03-24; failed acquisition summary retains the same start without promotion.',
        ],
        'MD-S053-R0191' => [
            'positive' => 'B19RangeBackfillMariaDbProofTest::test_range_window_runs_each_requested_date_in_order_without_publishing_warmup',
            'negative' => 'B19RangeBackfillMariaDbProofTest::test_failed_provider_checkpoint_resumes_without_faking_readability_then_publishes_recovered_rows',
            'basis' => 'E031: both persisted date runs record requested_end=2026-03-25, not the first run date; failed acquisition records its requested end without a pointer.',
        ],
        'MD-S053-R0192' => [
            'positive' => 'B19RangeBackfillMariaDbProofTest::test_range_window_runs_each_requested_date_in_order_without_publishing_warmup',
            'negative' => 'B19RangeBackfillMariaDbProofTest::test_failed_provider_checkpoint_resumes_without_faking_readability_then_publishes_recovered_rows',
            'basis' => 'E031: both successful runs persist SUCCESS; real provider 503 persists SYSTEMIC_FAILED and no readable requested-date pointer.',
        ],
        'MD-S053-R0193' => [
            'positive' => 'B19RangeBackfillMariaDbProofTest::test_range_window_runs_each_requested_date_in_order_without_publishing_warmup',
            'negative' => 'B19RangeWindowCheckpointIdentityTest::test_window_totals_remain_available_on_the_window_telemetry',
            'basis' => 'E031: each date run records expected_ticker_count=1 from the independently seeded SYNV2 universe; checkpoint window totals are separately checked.',
        ],
        'MD-S053-R0194' => [
            'positive' => 'B19RangeBackfillMariaDbProofTest::test_range_window_runs_each_requested_date_in_order_without_publishing_warmup',
            'negative' => 'B19RangeBackfillMariaDbProofTest::test_failed_provider_checkpoint_resumes_without_faking_readability_then_publishes_recovered_rows',
            'basis' => 'E031: successful per-date run notes record success_ticker_count=1; provider 503 cannot produce a readable requested-date outcome.',
        ],
        'MD-S053-R0195' => [
            'positive' => 'B19RangeBackfillMariaDbProofTest::test_range_window_runs_each_requested_date_in_order_without_publishing_warmup',
            'negative' => 'B19RangeBackfillMariaDbProofTest::test_failed_provider_checkpoint_resumes_without_faking_readability_then_publishes_recovered_rows',
            'basis' => 'E031: both successful date runs record failed_ticker_count=0 while real 503 summary records one failed ticker.',
        ],
        'MD-S053-R0197' => [
            'positive' => 'B19RangeWindowWarmupCalendarTest::test_warmup_start_is_the_governed_trading_day_boundary_not_calendar_day_arithmetic',
            'negative' => 'B19RangeBackfillMariaDbProofTest::test_range_window_runs_each_requested_date_in_order_without_publishing_warmup',
            'basis' => 'E031 reuses the independent calendar-day counterexample and confirms the chosen trading boundary persists through the MariaDB lifecycle.',
        ],
        'MD-S053-R0198' => [
            'positive' => 'B19RangeWindowWarmupCalendarTest::test_warmup_start_is_the_governed_trading_day_boundary_not_calendar_day_arithmetic',
            'negative' => 'B19RangeWindowWarmupWiringTest::test_the_acquisition_service_is_handed_the_calendar_boundary',
            'basis' => 'E031: moving the configured trading-day count moves the boundary from the first requested date; the real orchestrator passes that resolved boundary to acquisition.',
        ],
        'MD-S053-R0199' => [
            'positive' => 'B19RangeWindowWarmupWiringTest::test_an_unprovable_calendar_blocks_before_acquisition_and_publication',
            'negative' => 'B19RangeWindowWarmupCalendarTest::test_the_dataset_start_boundary_caps_the_warmup_instead_of_failing',
            'basis' => 'E031: missing or contradictory requested-date calendar blocks before acquisition, while the later binding addendum caps a short prior window at intentional dataset start; no fail-fast rule is inferred for that case.',
        ],
        'MD-S053-R0200' => [
            'positive' => 'B19RangeBackfillMariaDbProofTest::test_range_window_runs_each_requested_date_in_order_without_publishing_warmup',
            'negative' => 'B19RangeWindowWarmupWiringTest::test_the_acquisition_service_is_handed_the_calendar_boundary',
            'basis' => 'E032 revalidates the E031 proposal on actual pre-request source rows: the 2026-03-20 warmup row is retained and source-attributed, but no warmup current pointer is created or changed; both explicitly requested dates remain separately readable. A direct non-requested warmup promotion counterfactual is refused by the runtime guard.',
        ],
        'MD-S074-R0001' => [
            'positive' => 'B19RangeBackfillMariaDbProofTest::test_range_window_runs_each_requested_date_in_order_without_publishing_warmup',
            'negative' => 'B19RangeBackfillMariaDbProofTest::test_range_window_runs_each_requested_date_in_order_without_publishing_warmup',
            'basis' => 'E032 revalidates the E031 proposal with a genuine interrupted two-date MariaDB lifecycle: 2026-03-24 completes, a one-shot failure before 2026-03-25 import interrupts the range, then --resume skips the completed date and reacquires the provider range to publish 2026-03-25. The prior pointer, run count, publications, bars and history remain identical; resumed date has one run/pointer and distinct run identity. A counterfactual restoring stale source-window checkpoint skipping leaves the date HELD and makes the resume success assertion fail; exact source restoration and green rerun verified.',
        ],
        'MD-S074-R0002' => [
            'positive' => 'B19RangeBackfillMariaDbProofTest::test_failed_provider_checkpoint_resumes_without_faking_readability_then_publishes_recovered_rows',
            'negative' => 'SourceObservationImmutabilityTest::test_a_rerun_appends_a_new_observation_and_never_overwrites',
            'basis' => 'E031: failed and successful real retries append observation attempts and bytes of prior rows remain identical; recovered canonical bar binds accepted observation and stable listing ID. Immutable-source guard rejects updates.',
        ],
        'MD-S074-R0004' => [
            'positive' => 'B19RangeBackfillMariaDbProofTest::test_failed_provider_checkpoint_resumes_without_faking_readability_then_publishes_recovered_rows',
            'negative' => 'B19RangeBackfillMariaDbProofTest::test_invalid_source_mode_refuses_before_creating_a_run',
            'basis' => 'E031: once recovery seals a date, repeated only-failed invocation reports NO_FAILED_CHECKPOINT with identical run count and pointer; invalid mode cannot create a date run.',
        ],
        'MD-S074-R0005' => [
            'positive' => 'B19RangeBackfillMariaDbProofTest::test_failed_provider_checkpoint_resumes_without_faking_readability_then_publishes_recovered_rows',
            'negative' => 'B19RangeBackfillMariaDbProofTest::test_range_window_runs_each_requested_date_in_order_without_publishing_warmup',
            'basis' => 'E031: retry with no failed checkpoint creates no run or pointer change; a new explicit two-date request creates one run for each requested date.',
        ],
        'MD-S074-R0006' => [
            'positive' => 'B19CorrectionMariaDbProofTest::test_real_changed_unchanged_failed_and_historical_correction',
            'negative' => 'B19CorrectionMariaDbProofTest::test_real_impact_modes_require_safe_publication_lifecycle',
            'basis' => 'E031 revalidates E024 real correction: approved historical change creates a distinct run and version-two sealed publication with exported prior/new run IDs, supersession, approval identity and changed hash set; unapproved and unavailable-correction paths refuse.',
        ],
        'MD-S074-R0009' => [
            'positive' => 'B19RangeBackfillMariaDbProofTest::test_failed_provider_checkpoint_resumes_without_faking_readability_then_publishes_recovered_rows',
            'negative' => 'B19RangeWindowCheckpointPersistenceTest::test_a_resume_that_still_fails_reports_its_counts_and_a_failure_sample_that_agrees_with_the_file',
            'basis' => 'E031: a second real 503 retry leaves cases empty, indicators and eligibility absent, and requested pointer missing, while checkpoint diagnostics retain the failure.',
        ],
        'MD-S074-R0010' => [
            'positive' => 'B19RangeBackfillMariaDbProofTest::test_failed_provider_checkpoint_resumes_without_faking_readability_then_publishes_recovered_rows',
            'negative' => 'B19RangeBackfillMariaDbProofTest::test_recovered_rows_cannot_substitute_an_unrelated_accepted_observation',
            'basis' => 'E031: resumed 503 becomes one accepted observation bound to actual captured and normalized rows; the candidate and canonical bar bind that ID and independently computed V1/V2 hashes before readable publication. Six substituted or incomplete observation identities refuse.',
        ],
        'MD-S074-R0012' => [
            'positive' => 'B19RangeBackfillMariaDbProofTest::test_failed_provider_checkpoint_resumes_without_faking_readability_then_publishes_recovered_rows',
            'negative' => 'B19RangeBackfillMariaDbProofTest::test_recovered_rows_cannot_substitute_an_unrelated_accepted_observation',
            'basis' => 'E031: recovered bar changes from absent to observed, affected nonreadable date executes both indicator and eligibility reprocess before guarded promotion; altered source values or identity cannot masquerade as that row.',
        ],
        'MD-S074-R0014' => [
            'positive' => 'B19RangeBackfillMariaDbProofTest::test_failed_provider_checkpoint_resumes_without_faking_readability_then_publishes_recovered_rows',
            'negative' => 'B19RangeBackfillMariaDbProofTest::test_recovered_rows_cannot_substitute_an_unrelated_accepted_observation',
            'basis' => 'E031: nonreadable recovered date executes normal reprocess and promote, producing a sealed publication and current pointer only after exact manifest identity verifies; six identity counterfactuals never reach readability.',
        ],
        'MD-S074-R0015' => [
            'positive' => 'B19CorrectionMariaDbProofTest::test_real_impact_modes_require_safe_publication_lifecycle',
            'negative' => 'B19CorrectionMariaDbProofTest::test_real_changed_unchanged_failed_and_historical_correction',
            'basis' => 'E031 revalidates E024 MariaDB: already readable affected date cannot silently update; explicit blocked AFFECTED_PUBLICATION_REQUIRES_CORRECTION preserves pointer, whereas approved correction creates auditable replacement and prior publication remains readable historically.',
        ],
        'MD-S053-R0030' => [
            'positive' => 'B19RangeBackfillMariaDbProofTest::test_manual_file_historical_fill_executes_the_registered_lifecycle_and_rejects_missing_input',
            'negative' => 'B19RangeBackfillMariaDbProofTest::test_invalid_source_mode_refuses_before_creating_a_run',
            'basis' => 'E031 MariaDB: explicit single-date operator historical CSV is consumed by the registered lifecycle command and yields a readable publication under the supported legacy row-hash profile; missing file and unregistered mode refuse. The default V2 profile fails ARTIFACT_PROVIDER_SOURCE_REQUIRED for local-only rows, a separate held default-profile limitation; this basis does not claim V2 manual correction or replay.',
        ],
        'MD-S001-R0085' => [
            'positive' => 'B19RangeBackfillMariaDbProofTest::test_snapshot_retention_dry_run_and_apply_only_remove_expired_snapshot_rows',
            'negative' => 'B19RangeBackfillMariaDbProofTest::test_snapshot_retention_dry_run_and_apply_only_remove_expired_snapshot_rows',
            'basis' => 'E032 revalidates the E031 proposal with independent MariaDB snapshots before dry-run and apply. An expired session snapshot is the sole deletion candidate; retained snapshot remains. Bars, indicators, eligibility, runs, publications, correction-trail sentinel, current pointer and histories are seeded/present and byte-identical across both invocations. This proves ordinary snapshot TTL safety only; audit-evidence retention and date-partition maintenance remain held.',
        ],
        'MD-S074-R0007' => [
            'positive' => 'B19RangeBackfillMariaDbProofTest::test_range_window_runs_each_requested_date_in_order_without_publishing_warmup',
            'negative' => 'B19RangeBackfillMariaDbProofTest::test_range_window_runs_each_requested_date_in_order_without_publishing_warmup',
            'basis' => 'E032: the interrupted multi-date execution persists the completed first date and leaves the second incomplete; resume starts at the first incomplete date, verifies the first date is checkpoint-skipped, and preserves its publication bytes and run count. Reverting the required reacquisition behavior leaves the second date HELD with zero rows and the success assertion fails; source restored byte-for-byte and test passes.',
        ],
    ];
}
