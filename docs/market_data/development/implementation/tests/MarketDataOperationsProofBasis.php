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
            'basis' => 'run_id carries the value of its own run_id column in a run record where every column differs, a persisted NULL stays NULL, and a real sealed run agrees with eod_runs; reading it from the publication id turns the mirror guard red (A01)',
        ],
        'MD-S075-R0026' => [
            'positive' => 'B19RunSummaryValueProvenanceTest::test_a_mirrored_field_carries_the_value_of_its_own_persisted_column',
            'negative' => 'B19RunSummaryRealRunProvenanceTest::test_every_summary_key_named_like_an_eod_runs_column_carries_that_columns_value',
            'basis' => 'trade_date_requested carries the value of its own trade_date_requested column in a run record where every column differs, a persisted NULL stays NULL, and a real sealed run agrees with eod_runs; replacing it by the effective date turns it red (G01)',
        ],
        'MD-S075-R0027' => [
            'positive' => 'B19RunSummaryValueProvenanceTest::test_a_mirrored_field_carries_the_value_of_its_own_persisted_column',
            'negative' => 'B19RunSummaryValueProvenanceTest::test_a_persisted_null_is_exported_as_null_not_as_a_default',
            'basis' => 'trade_date_effective carries the value of its own trade_date_effective column in a run record where every column differs, a persisted NULL stays NULL, and a real sealed run agrees with eod_runs; replacing it by the requested date turns it red (A02)',
        ],
        'MD-S075-R0028' => [
            'positive' => 'B19RunSummaryValueProvenanceTest::test_a_mirrored_field_carries_the_value_of_its_own_persisted_column',
            'negative' => 'B19RunSummaryRealRunProvenanceTest::test_every_summary_key_named_like_an_eod_runs_column_carries_that_columns_value',
            'basis' => 'lifecycle_state carries the value of its own lifecycle_state column in a run record where every column differs, a persisted NULL stays NULL, and a real sealed run agrees with eod_runs; reading it from the stage turns it red (A03)',
        ],
        'MD-S075-R0029' => [
            'positive' => 'B19RunSummaryValueProvenanceTest::test_a_mirrored_field_carries_the_value_of_its_own_persisted_column',
            'negative' => 'B19RunSummaryRealRunProvenanceTest::test_every_summary_key_named_like_an_eod_runs_column_carries_that_columns_value',
            'basis' => 'terminal_status carries the value of its own terminal_status column in a run record where every column differs, a persisted NULL stays NULL, and a real sealed run agrees with eod_runs; reading it from the quality gate turns the sentinel and the real-run guards red (A04)',
        ],
        'MD-S075-R0030' => [
            'positive' => 'B19RunSummaryValueProvenanceTest::test_a_mirrored_field_carries_the_value_of_its_own_persisted_column',
            'negative' => 'B19RunSummaryRealRunProvenanceTest::test_every_summary_key_named_like_an_eod_runs_column_carries_that_columns_value',
            'basis' => 'quality_gate_state carries the value of its own quality_gate_state column in a run record where every column differs, a persisted NULL stays NULL, and a real sealed run agrees with eod_runs; reading it from the terminal status turns it red (G02)',
        ],
        'MD-S075-R0031' => [
            'positive' => 'B19RunSummaryValueProvenanceTest::test_a_mirrored_field_carries_the_value_of_its_own_persisted_column',
            'negative' => 'B19RunSummaryRealRunProvenanceTest::test_every_summary_key_named_like_an_eod_runs_column_carries_that_columns_value',
            'basis' => 'publishability_state carries the value of its own publishability_state column in a run record where every column differs, a persisted NULL stays NULL, and a real sealed run agrees with eod_runs; reading it from the terminal status turns it red (G03)',
        ],
        'MD-S075-R0032' => [
            'positive' => 'B19RunSummaryValueProvenanceTest::test_a_mirrored_field_carries_the_value_of_its_own_persisted_column',
            'negative' => 'B19RunSummaryRealRunProvenanceTest::test_every_summary_key_named_like_an_eod_runs_column_carries_that_columns_value',
            'basis' => 'stage carries the value of its own stage column in a run record where every column differs, a persisted NULL stays NULL, and a real sealed run agrees with eod_runs; swapping it with the lifecycle state turns it red (A05)',
        ],
        'MD-S075-R0033' => [
            'positive' => 'B19RunSummaryValueProvenanceTest::test_a_mirrored_field_carries_the_value_of_its_own_persisted_column',
            'negative' => 'B19RunSummaryRealRunProvenanceTest::test_every_summary_key_named_like_an_eod_runs_column_carries_that_columns_value',
            'basis' => 'source carries the value of its own source column in a run record where every column differs, a persisted NULL stays NULL, and a real sealed run agrees with eod_runs; reading it from the request mode turns it red (A06)',
        ],
        'MD-S075-R0034' => [
            'positive' => 'B19RunSummaryValueProvenanceTest::test_source_context_carries_its_minimum_fields_from_the_persisted_source_columns',
            'negative' => 'B19RunSummaryValueProvenanceTest::test_source_context_invents_no_source_fact_the_run_never_recorded',
            'basis' => 'source_context is an array block carrying the six minimum fields, from persisted columns or recovered from persisted notes; truncating the block turns the guards red (G04b)',
        ],
        'MD-S075-R0035' => [
            'positive' => 'B19RunSummaryValueProvenanceTest::test_source_context_carries_its_minimum_fields_from_the_persisted_source_columns',
            'negative' => 'B19RunSummaryValueProvenanceTest::test_source_context_invents_no_source_fact_the_run_never_recorded',
            'basis' => 'source_name comes from the source_name column, is recovered from notes when the column is thin, and is null when nothing was recorded; reading it from the provider column or dropping the notes recovery turns a guard red (B01, B02)',
        ],
        'MD-S075-R0036' => [
            'positive' => 'B19RunSummaryValueProvenanceTest::test_source_context_carries_its_minimum_fields_from_the_persisted_source_columns',
            'negative' => 'B19RunSummaryValueProvenanceTest::test_source_context_recovers_thin_minimum_fields_from_persisted_notes_only',
            'basis' => 'source_input_file comes from the source_input_file column or the notes; reading it from the file hash turns the guard red (B09)',
        ],
        'MD-S075-R0037' => [
            'positive' => 'B19RunSummaryValueProvenanceTest::test_source_context_carries_its_minimum_fields_from_the_persisted_source_columns',
            'negative' => 'B19RunSummaryValueProvenanceTest::test_source_context_invents_no_source_fact_the_run_never_recorded',
            'basis' => 'attempt_count comes from source_attempt_count (4) or the notes (5) and is null when unrecorded; reading the retry maximum, or defaulting to 1, turns a guard red (B03, B07)',
        ],
        'MD-S075-R0038' => [
            'positive' => 'B19RunSummaryValueProvenanceTest::test_source_context_carries_its_minimum_fields_from_the_persisted_source_columns',
            'negative' => 'B19RunSummaryValueProvenanceTest::test_source_context_invents_no_source_fact_the_run_never_recorded',
            'basis' => 'success_after_retry is yes for a persisted 1, no for a persisted 0 and null when unrecorded; inverting it turns the guard red (B04)',
        ],
        'MD-S075-R0039' => [
            'positive' => 'B19RunSummaryValueProvenanceTest::test_source_context_carries_its_minimum_fields_from_the_persisted_source_columns',
            'negative' => 'B19RunSummaryValueProvenanceTest::test_source_context_invents_no_source_fact_the_run_never_recorded',
            'basis' => 'final_http_status comes from source_final_http_status (503) or the notes (429) and is null when unrecorded; reading the timeout column turns the guard red (B05)',
        ],
        'MD-S075-R0040' => [
            'positive' => 'B19RunSummaryValueProvenanceTest::test_source_context_carries_its_minimum_fields_from_the_persisted_source_columns',
            'negative' => 'B19RunSummaryValueProvenanceTest::test_source_context_invents_no_source_fact_the_run_never_recorded',
            'basis' => 'final_reason_code comes from source_final_reason_code or the notes and is null when unrecorded; exporting an UNKNOWN stand-in turns a guard red (B06)',
        ],
        'MD-S075-R0041' => [
            'positive' => 'B19RunSummaryValueProvenanceTest::test_a_mirrored_field_carries_the_value_of_its_own_persisted_column',
            'negative' => 'B19RunSummaryValueProvenanceTest::test_a_persisted_null_is_exported_as_null_not_as_a_default',
            'basis' => 'coverage_ratio carries the value of its own coverage_ratio column in a run record where every column differs, a persisted NULL stays NULL, and a real sealed run agrees with eod_runs; defaulting an absent ratio to 0 turns the NULL guard red (C01)',
        ],
        'MD-S075-R0042' => [
            'positive' => 'B19RunSummaryValueProvenanceTest::test_a_mirrored_field_carries_the_value_of_its_own_persisted_column',
            'negative' => 'B19RunSummaryValueProvenanceTest::test_a_persisted_null_is_exported_as_null_not_as_a_default',
            'basis' => 'bars_rows_written carries the value of its own bars_rows_written column in a run record where every column differs, a persisted NULL stays NULL, and a real sealed run agrees with eod_runs; reading the indicator rows turns it red (C02)',
        ],
        'MD-S075-R0043' => [
            'positive' => 'B19RunSummaryValueProvenanceTest::test_a_mirrored_field_carries_the_value_of_its_own_persisted_column',
            'negative' => 'B19RunSummaryValueProvenanceTest::test_a_persisted_null_is_exported_as_null_not_as_a_default',
            'basis' => 'indicators_rows_written carries the value of its own indicators_rows_written column in a run record where every column differs, a persisted NULL stays NULL, and a real sealed run agrees with eod_runs; reading the bar rows turns it red (G05)',
        ],
        'MD-S075-R0044' => [
            'positive' => 'B19RunSummaryValueProvenanceTest::test_a_mirrored_field_carries_the_value_of_its_own_persisted_column',
            'negative' => 'B19RunSummaryValueProvenanceTest::test_a_persisted_null_is_exported_as_null_not_as_a_default',
            'basis' => 'eligibility_rows_written carries the value of its own eligibility_rows_written column in a run record where every column differs, a persisted NULL stays NULL, and a real sealed run agrees with eod_runs; reading the indicator rows turns it red (G06)',
        ],
        'MD-S075-R0045' => [
            'positive' => 'B19RunSummaryValueProvenanceTest::test_a_mirrored_field_carries_the_value_of_its_own_persisted_column',
            'negative' => 'B19RunSummaryValueProvenanceTest::test_a_persisted_null_is_exported_as_null_not_as_a_default',
            'basis' => 'invalid_bar_count carries the value of its own invalid_bar_count column in a run record where every column differs, a persisted NULL stays NULL, and a real sealed run agrees with eod_runs; reading the invalid indicator count turns it red (G07)',
        ],
        'MD-S075-R0046' => [
            'positive' => 'B19RunSummaryValueProvenanceTest::test_a_mirrored_field_carries_the_value_of_its_own_persisted_column',
            'negative' => 'B19RunSummaryValueProvenanceTest::test_a_persisted_null_is_exported_as_null_not_as_a_default',
            'basis' => 'invalid_indicator_count carries the value of its own invalid_indicator_count column in a run record where every column differs, a persisted NULL stays NULL, and a real sealed run agrees with eod_runs; reading the invalid bar count turns it red (G08)',
        ],
        'MD-S075-R0047' => [
            'positive' => 'B19RunSummaryDerivedFieldMarkingTest::test_warning_count_is_the_persisted_value_and_a_null_is_not_turned_into_a_count',
            'negative' => 'B19RunSummaryValueProvenanceTest::test_a_persisted_null_is_exported_as_null_not_as_a_default',
            'basis' => 'Option B (D-MD-B19-A001-003): warning_count carries the persisted eod_runs.warning_count under its persisted name, a persisted NULL stays NULL and is never turned into zero or into a count of anything else, and it is never marked as derived; on a real run the persisted value is NULL and the summary says NULL. ACCEPTED LIMITATION, not a conformance claim about warning counting: nothing in app/ writes the counter and a warning is defined nowhere (F-MD-B19-A001-005); a tripwire fails the day a new file starts to handle it, so that the population is decided first. Defaulting to zero, reading another counter, inventing a population from the invalid counts or marking the field as derived turns a guard red (W01..W04); a repository that starts writing the counter or a new file that mentions it turns the tripwire red (W05, W06)',
        ],
        'MD-S075-R0048' => [
            'positive' => 'B19RunSummaryValueProvenanceTest::test_a_mirrored_field_carries_the_value_of_its_own_persisted_column',
            'negative' => 'B19RunSummaryValueProvenanceTest::test_a_persisted_null_is_exported_as_null_not_as_a_default',
            'basis' => 'hard_reject_count carries the value of its own hard_reject_count column in a run record where every column differs, a persisted NULL stays NULL, and a real sealed run agrees with eod_runs; reading the invalid bar count turns it red (C04)',
        ],
        'MD-S075-R0049' => [
            'positive' => 'B19RunSummaryValueProvenanceTest::test_a_mirrored_field_carries_the_value_of_its_own_persisted_column',
            'negative' => 'B19RunSummaryValueProvenanceTest::test_a_persisted_null_is_exported_as_null_not_as_a_default',
            'basis' => 'bars_batch_hash carries the value of its own bars_batch_hash column in a run record where every column differs, a persisted NULL stays NULL, and a real sealed run agrees with eod_runs; reading the eligibility hash turns it red (D01)',
        ],
        'MD-S075-R0050' => [
            'positive' => 'B19RunSummaryValueProvenanceTest::test_a_mirrored_field_carries_the_value_of_its_own_persisted_column',
            'negative' => 'B19RunSummaryValueProvenanceTest::test_a_persisted_null_is_exported_as_null_not_as_a_default',
            'basis' => 'indicators_batch_hash carries the value of its own indicators_batch_hash column in a run record where every column differs, a persisted NULL stays NULL, and a real sealed run agrees with eod_runs; reading the bars hash turns it red (G09)',
        ],
        'MD-S075-R0051' => [
            'positive' => 'B19RunSummaryValueProvenanceTest::test_a_mirrored_field_carries_the_value_of_its_own_persisted_column',
            'negative' => 'B19RunSummaryValueProvenanceTest::test_a_persisted_null_is_exported_as_null_not_as_a_default',
            'basis' => 'eligibility_batch_hash carries the value of its own eligibility_batch_hash column in a run record where every column differs, a persisted NULL stays NULL, and a real sealed run agrees with eod_runs; reading the indicators hash turns it red (G10)',
        ],
        'MD-S075-R0052' => [
            'positive' => 'B19RunSummaryValueProvenanceTest::test_a_mirrored_field_carries_the_value_of_its_own_persisted_column',
            'negative' => 'B19RunSummaryRealRunProvenanceTest::test_publication_facing_fields_agree_with_the_publication_and_snapshot_rows',
            'basis' => 'observation_manifest_hash carries the run column in a run record where every column differs, and on a real run it equals the observation manifest hash of the sealed publication read from eod_publications; reading the bars hash turns the guard red (D03)',
        ],
        'MD-S075-R0053' => [
            'positive' => 'B19RunSummaryValueProvenanceTest::test_a_publication_facing_field_comes_from_the_manifest_of_the_runs_own_publication',
            'negative' => 'B19RunSummaryValueProvenanceTest::test_a_run_without_a_resolved_publication_exports_no_publication_facing_value',
            'basis' => 'publication_manifest_hash is read from the manifest of the run\'s own publication (decoys for every other id and on the run record), equals the eod_publications column on a real run, and is null when no publication resolved; reading it from the run, or from a neighbouring publication id, turns a guard red (D04, F05)',
        ],
        'MD-S075-R0054' => [
            'positive' => 'B19RunSummaryValueProvenanceTest::test_a_mirrored_field_carries_the_value_of_its_own_persisted_column',
            'negative' => 'B19RunSummaryValueProvenanceTest::test_missing_seal_evidence_on_a_readable_claim_is_exported_as_missing',
            'basis' => 'sealed_at carries the sealed_at column, is null for a run that never sealed, and is never replaced by the finish time; a stand-in turns the guards red (D02)',
        ],
        'MD-S075-R0055' => [
            'positive' => 'B19RunSummaryValueProvenanceTest::test_a_mirrored_field_carries_the_value_of_its_own_persisted_column',
            'negative' => 'B19RunSummaryRealRunProvenanceTest::test_every_summary_key_named_like_an_eod_runs_column_carries_that_columns_value',
            'basis' => 'config_version carries the value of its own config_version column in a run record where every column differs, a persisted NULL stays NULL, and a real sealed run agrees with eod_runs; reading the config hash turns it red (E01)',
        ],
        'MD-S075-R0056' => [
            'positive' => 'B19RunSummaryValueProvenanceTest::test_a_mirrored_field_carries_the_value_of_its_own_persisted_column',
            'negative' => 'B19RunSummaryValueProvenanceTest::test_a_persisted_null_is_exported_as_null_not_as_a_default',
            'basis' => 'config_snapshot_id carries the value of its own config_snapshot_id column in a run record where every column differs, a persisted NULL stays NULL, and a real sealed run agrees with eod_runs; reading the publication id as the snapshot id turns it red (E02b)',
        ],
        'MD-S075-R0057' => [
            'positive' => 'B19RunSummaryValueProvenanceTest::test_a_publication_facing_field_comes_from_the_manifest_of_the_runs_own_publication',
            'negative' => 'B19RunSummaryRealRunProvenanceTest::test_publication_facing_fields_agree_with_the_publication_and_snapshot_rows',
            'basis' => 'config_snapshot_hash is read from the manifest of the run\'s own publication (decoys elsewhere) and on a real run equals md_config_snapshots.config_hash of the run\'s snapshot read directly; reading the run record or a neighbouring publication turns a guard red (E03, F05)',
        ],
        'MD-S075-R0058' => [
            'positive' => 'B19RunSummaryValueProvenanceTest::test_a_publication_facing_field_comes_from_the_manifest_of_the_runs_own_publication',
            'negative' => 'B19RunSummaryRealRunProvenanceTest::test_publication_facing_fields_agree_with_the_publication_and_snapshot_rows',
            'basis' => 'temporal_revision_set_hash is read from the manifest of the run\'s own publication, is a SHA-256 value on a real run and equals what the repository resolves for that publication; reading the run record or a neighbouring publication turns a guard red (E04, F05)',
        ],
        'MD-S075-R0059' => [
            'positive' => 'B19RunSummaryValueProvenanceTest::test_a_publication_facing_field_comes_from_the_manifest_of_the_runs_own_publication',
            'negative' => 'B19RunSummaryRealRunProvenanceTest::test_publication_facing_fields_agree_with_the_publication_and_snapshot_rows',
            'basis' => 'factor_set_id is read from the manifest of the run\'s own publication and on a real run equals eod_publications.factor_set_id; reading the run\'s decoy value turns a guard red (E05, F05)',
        ],
        'MD-S075-R0060' => [
            'positive' => 'B19RunSummaryValueProvenanceTest::test_a_mirrored_field_carries_the_value_of_its_own_persisted_column',
            'negative' => 'B19RunSummaryValueProvenanceTest::test_a_persisted_null_is_exported_as_null_not_as_a_default',
            'basis' => 'factor_set_hash carries the value of its own factor_set_hash column in a run record where every column differs, a persisted NULL stays NULL, and a real sealed run agrees with eod_runs; reading the observation hash turns it red (E06)',
        ],
        'MD-S075-R0061' => [
            'positive' => 'B19RunSummaryValueProvenanceTest::test_a_mirrored_field_carries_the_value_of_its_own_persisted_column',
            'negative' => 'B19RunSummaryValueProvenanceTest::test_a_persisted_null_is_exported_as_null_not_as_a_default',
            'basis' => 'price_product_code carries the value of its own price_product_code column in a run record where every column differs, a persisted NULL stays NULL, and a real sealed run agrees with eod_runs; reading the source turns it red (E07)',
        ],
        'MD-S075-R0062' => [
            'positive' => 'B19RunSummaryValueProvenanceTest::test_a_publication_facing_field_comes_from_the_manifest_of_the_runs_own_publication',
            'negative' => 'B19RunSummaryValueProvenanceTest::test_a_run_without_a_resolved_publication_exports_no_publication_facing_value',
            'basis' => 'canonicalization_version is read from the manifest of the run\'s own publication, is non-null on a real run and equals what the repository resolves, and is null when no publication resolved; a fixed-label stand-in turns the guard red (E08)',
        ],
        'MD-S075-R0063' => [
            'positive' => 'B19RunSummaryValueProvenanceTest::test_a_publication_facing_field_comes_from_the_manifest_of_the_runs_own_publication',
            'negative' => 'B19RunSummaryValueProvenanceTest::test_a_run_without_a_resolved_publication_exports_no_publication_facing_value',
            'basis' => 'formula_version is read from the manifest of the run\'s own publication, is non-null on a real run and equals what the repository resolves, and is null when no publication resolved; a fixed-label stand-in turns the guard red (E09)',
        ],
        'MD-S075-R0064' => [
            'positive' => 'B19RunSummaryValueProvenanceTest::test_a_publication_facing_field_comes_from_the_manifest_of_the_runs_own_publication',
            'negative' => 'B19RunSummaryRealRunProvenanceTest::test_publication_facing_fields_agree_with_the_publication_and_snapshot_rows',
            'basis' => 'read_model_version is read from the manifest of the run\'s own publication and on a real run equals eod_publications.read_model_version; reading the run\'s decoy value turns a guard red (E10)',
        ],
        'MD-S075-R0065' => [
            'positive' => 'B19RunSummaryValueProvenanceTest::test_a_mirrored_field_carries_the_value_of_its_own_persisted_column',
            'negative' => 'B19RunSummaryValueProvenanceTest::test_a_persisted_null_is_exported_as_null_not_as_a_default',
            'basis' => 'freshness_state carries the value of its own freshness_state column in a run record where every column differs, a persisted NULL stays NULL, and a real sealed run agrees with eod_runs; reading the stage turns it red (F01)',
        ],
        'MD-S075-R0066' => [
            'positive' => 'B19RunSummaryValueProvenanceTest::test_a_publication_facing_field_comes_from_the_manifest_of_the_runs_own_publication',
            'negative' => 'B19RunSummaryValueProvenanceTest::test_a_run_without_a_resolved_publication_exports_no_publication_facing_value',
            'basis' => 'publication_version is the version of the run\'s own publication (4 in the sentinel, equal to eod_publications.publication_version on a real run) and null when no publication resolved; an off-by-one turns the guard red (G11). Where the run column and the manifest could differ the manifest\'s live value is exported; the pipeline keeps both in step and the real run shows agreement, so no precedence is claimed',
        ],
        'MD-S075-R0067' => [
            'positive' => 'B19RunSummaryValueProvenanceTest::test_a_readable_success_carries_the_seal_and_publication_evidence_it_implies',
            'negative' => 'B19RunSummaryValueProvenanceTest::test_a_held_or_failed_run_is_never_summarised_as_readable',
            'basis' => 'is_current_publication is true for a readable success on its current publication and false for every run that published nothing; marking every run current, or no run current, turns a guard red (F06, G12). Same precedence note as R0066',
        ],
        'MD-S075-R0068' => [
            'positive' => 'B19RunSummaryValueProvenanceTest::test_a_mirrored_field_carries_the_value_of_its_own_persisted_column',
            'negative' => 'B19RunSummaryValueProvenanceTest::test_a_persisted_null_is_exported_as_null_not_as_a_default',
            'basis' => 'supersedes_run_id carries the value of its own supersedes_run_id column in a run record where every column differs, a persisted NULL stays NULL, and a real sealed run agrees with eod_runs; reading the correction id turns it red (F02)',
        ],
        'MD-S075-R0069' => [
            'positive' => 'B19RunSummaryValueProvenanceTest::test_a_mirrored_field_carries_the_value_of_its_own_persisted_column',
            'negative' => 'B19RunSummaryValueProvenanceTest::test_a_persisted_null_is_exported_as_null_not_as_a_default',
            'basis' => 'started_at carries the value of its own started_at column in a run record where every column differs, a persisted NULL stays NULL, and a real sealed run agrees with eod_runs; reading the creation time turns it red (F03)',
        ],
        'MD-S075-R0070' => [
            'positive' => 'B19RunSummaryValueProvenanceTest::test_a_mirrored_field_carries_the_value_of_its_own_persisted_column',
            'negative' => 'B19RunSummaryValueProvenanceTest::test_a_persisted_null_is_exported_as_null_not_as_a_default',
            'basis' => 'finished_at carries the value of its own finished_at column in a run record where every column differs, a persisted NULL stays NULL, and a real sealed run agrees with eod_runs; reading the start time turns it red (F04)',
        ],
        'MD-S075-R0071' => [
            'positive' => 'B19RunSummaryRealRunProvenanceTest::test_a_real_readable_success_carries_compatible_seal_and_publication_evidence',
            'negative' => 'B19RunSummaryValueProvenanceTest::test_a_held_or_failed_run_is_never_summarised_as_readable',
            'basis' => 'the outcome fields (terminal_status, publishability_state, quality_gate_state, ...) equal the persisted row on a real run and the persisted values in every held, failed and not-readable variant; deriving them from a neighbouring column or marking a run current turns a guard red (A04, G02, G03, F06)',
        ],
        'MD-S075-R0072' => [
            'positive' => 'B19RunSummaryValueProvenanceTest::test_a_readable_success_carries_the_seal_and_publication_evidence_it_implies',
            'negative' => 'B19RunSummaryValueProvenanceTest::test_missing_seal_evidence_on_a_readable_claim_is_exported_as_missing',
            'basis' => 'a readable success carries seal time, the three batch hashes, the publication manifest hash, a publication version and the current marking, all equal to the sealed publication on a real run; when a run claims readability without seal evidence the summary exports the absence rather than a stand-in; a seal-time stand-in or a false current marking turns a guard red (D02, G12)',
        ],
        'MD-S075-R0073' => [
            'positive' => 'B19RunSummaryValueProvenanceTest::test_a_held_or_failed_run_is_never_summarised_as_readable',
            'negative' => 'B19RunSummaryValueProvenanceTest::test_a_run_without_a_resolved_publication_exports_no_publication_facing_value',
            'basis' => 'held, failed and not-readable runs are not promoted, switch no pointer, name no current publication, carry no seal and export no publication-facing value, and their outcome note says not readable; a stand-in seal, a current marking or an always-switched pointer turns a guard red (F06, F07, D02)',
        ],
        'MD-S075-R0074' => [
            'positive' => 'B19RunSummaryDerivedFieldMarkingTest::test_final_reason_code_carries_exactly_the_persisted_column',
            'negative' => 'B19RunSummaryRealRunProvenanceTest::test_every_summary_key_named_like_an_eod_runs_column_carries_that_columns_value',
            'basis' => 'final_reason_code mirrors eod_runs.final_reason_code and nothing else (D-MD-B19-A001-003, F-MD-B19-A001-006 Option A): a persisted value is exported although the source and coverage reasons differ, a persisted NULL stays NULL (no source reason, coverage reason or stand-in), the message beside it describes only the persisted code, and on a real run every key shared with an eod_runs column, final_reason_code included, equals the persisted value; the reason resolved for operators is the separate field of R0075. Exporting the effective reason, a stand-in or the source reason under the persisted name, or the message of a fallback reason, turns a guard red (O01..O04); so does a consumer that relabels the effective reason as the persisted one (C01, C03)',
        ],
        'MD-S075-R0075' => [
            'positive' => 'B19RunSummaryDerivedFieldMarkingTest::test_every_derived_field_is_listed_in_the_derived_companion_marker',
            'negative' => 'B19RunSummaryDerivedFieldMarkingTest::test_the_marker_does_not_list_a_pure_persisted_mirror',
            'basis' => 'derived fields appear only as marked derived companion evidence (D-MD-B19-A001-003): the effective final reason with its provenance and message, the seven manifest-derived minimum fields, the bound-input projection, the manifest-preferred publication fields, the current-marking derivations and source_context are listed in derived_companion_fields with a derivation kind and what each is derived from; the effective reason names the exact column or block it was taken from; no persisted mirror is listed and a marker that is absent cannot satisfy that; a manifest-derived field follows the manifest of the run\'s own publication and not the run record, and on a real run the listed fields exist and agree with the tables; the export result, lineage, completeness check and outcome note read the effective field. Omitting a field from the marker, an entry without an origin, a wrong origin, a marked persisted mirror, a dropped or wrong provenance or a consumer that relabels the effective reason turns a guard red (O05..O10, K01..K09, C01..C06)',
        ],
        'MD-S075-R0076' => [
            'positive' => 'B19RunSummaryValueProvenanceTest::test_source_context_recovers_thin_minimum_fields_from_persisted_notes_only',
            'negative' => 'B19RunSummaryValueProvenanceTest::test_source_context_invents_no_source_fact_the_run_never_recorded',
            'basis' => 'source_context recovers minimum fields from persisted notes only, reads only the exported run\'s record, and invents no source fact: a run with no recorded source telemetry has null minimum fields and a null retry_attempt_count (it exported 0 retries before this unit\'s correction); a stand-in reason, a default attempt count, a dropped notes recovery or the old zero default turns a guard red (B02, B06, B07, B08)',
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
            'basis' => 'run_id is the run the file was exported for, read from the run row (never from the publication, which has another id on the real run): the real run, and a run with its own events and no publication, both name themselves; reading the publication id turns both guards red (X01)',
        ],
        'MD-S075-R0115' => [
            'positive' => 'B19RunEventSummaryRealRunProvenanceTest::test_the_requested_date_is_not_the_effective_date',
            'negative' => 'B19RunEventSummaryRealRunProvenanceTest::test_the_file_names_the_run_and_its_requested_date',
            'basis' => 'trade_date_requested is the run\'s REQUESTED date: on a run served on another effective date the file still carries the requested date; reading the effective date turns the guard red (X02)',
        ],
        'MD-S075-R0116' => [
            'positive' => 'B19RunEventSummaryTrailDerivationTest::test_event_count_is_the_number_of_this_runs_events_only',
            'negative' => 'B19RunEventSummaryTrailDerivationTest::test_the_whole_summary_equals_the_independent_derivation',
            'basis' => 'event_count is the number of THIS run\'s events in eod_run_events, on a database where two other runs hold events (one on the same trade date, one on another); counting every run or counting distinct stages turns guards red (M01, M20)',
        ],
        'MD-S075-R0117' => [
            'positive' => 'B19RunEventSummaryTrailDerivationTest::test_first_event_is_the_earliest_by_time_and_not_the_first_inserted_or_lowest_id',
            'negative' => 'B19RunEventSummaryTieOrderAndEmptyTrailTest::test_the_executed_query_orders_ties_by_event_id_after_event_time',
            'basis' => 'first_event_time is the earliest event time of the run, on a trail whose earliest event has a HIGHER event_id than the events after it and was inserted out of time order; ordering by id only, taking the last event or its time turns guards red (M04, M05, M07). Events that share a timestamp are ordered by the lower event_id: the SQL the repository really executes is captured from the connection and must order by event_time and then event_id ascending, and on a table whose only usable index would return tied rows in event_type order the first of two tied events is still the lower id. Removing the explicit event_id tie break (M02), reversing it (M03) or ordering by id only (M04) turns both red. The locked text states no tie rule; the guard protects the determinism the summary needs to be derivable from the append-only trail, event_id being its append sequence',
        ],
        'MD-S075-R0118' => [
            'positive' => 'B19RunEventSummaryTrailDerivationTest::test_last_event_is_the_latest_by_time_and_the_higher_id_wins_a_tie',
            'negative' => 'B19RunEventSummaryTieOrderAndEmptyTrailTest::test_ties_are_resolved_by_event_id_even_when_the_engine_would_return_them_in_another_order',
            'basis' => 'last_event_time is the latest event time of the run, with two events sharing the last timestamp; the first event\'s time or a reversed tie break turns guards red (M03, M06, M08), and a real pipeline trail agrees with an independent derivation. The last of two tied events is the higher event_id even when the engine would return the tie in event_type order (index-order guard); removing the explicit event_id tie break turns it red (M02)',
        ],
        'MD-S075-R0119' => [
            'positive' => 'B19RunEventSummaryTrailDerivationTest::test_first_event_is_the_earliest_by_time_and_not_the_first_inserted_or_lowest_id',
            'negative' => 'B19RunEventSummaryTrailDerivationTest::test_a_tie_for_first_place_is_broken_by_the_lower_event_id',
            'basis' => 'first_event_type is the type of the earliest event (tie: lower event_id), not of the last event or of the lowest id; an empty trail has none; the last event\'s type turns guards red (M05, M09)',
        ],
        'MD-S075-R0120' => [
            'positive' => 'B19RunEventSummaryTrailDerivationTest::test_last_event_is_the_latest_by_time_and_the_higher_id_wins_a_tie',
            'negative' => 'B19RunEventSummaryTrailDerivationTest::test_a_tie_for_first_place_is_broken_by_the_lower_event_id',
            'basis' => 'last_event_type is the type of the latest event, the higher event_id winning a shared timestamp; the first event\'s type turns guards red (M06, M10)',
        ],
        'MD-S075-R0121' => [
            'positive' => 'B19RunEventSummaryTrailDerivationTest::test_highest_severity_over_mixed_trails',
            'negative' => 'B19RunEventSummaryTieOrderAndEmptyTrailTest::test_an_empty_trail_has_a_null_highest_severity',
            'basis' => 'highest_severity is the maximum severity of the run\'s recorded events (INFO < WARN < ERROR) over eight orderings of mixed severities, a trail whose ERROR is neither first nor last, and a single-event trail of each severity (INFO is reported only because an INFO was recorded); never raising it, ranking WARN above ERROR, ignoring ERROR, taking the last event\'s severity or not reporting a recorded INFO turn guards red (M11, M12, M13, M14, S05). For a run with ZERO events it is JSON null, per the Project Owner decision D-MD-B19-A001-004 (F-MD-B19-A001-008 Option A): the repository returns null, the written run_event_summary.json carries `"highest_severity": null` on a real run, and restoring the INFO default, an empty string, a WARN, coalescing the null back to INFO, or reading another run\'s ERROR into an empty run turn guards red (S01, S02, S03, S04, S06, S07)',
        ],
        'MD-S075-R0122' => [
            'positive' => 'B19RunEventSummaryTrailDerivationTest::test_stage_counts_tally_this_runs_events_per_stage',
            'negative' => 'B19RunEventSummaryRealRunProvenanceTest::test_a_run_with_no_events_writes_no_invented_history',
            'basis' => 'stage_counts counts this run\'s events per stage, keyed by the stage name in a deterministic order, adding up to event_count and holding no stage of another run, and is written as a JSON OBJECT even when empty; double counting, keying by event type, leaving the order to the trail or writing an empty map as [] turn guards red (M15, M16, M17, X03)',
        ],
        'MD-S075-R0123' => [
            'positive' => 'B19RunEventSummaryTrailDerivationTest::test_each_stage_of_the_example_is_counted_under_its_own_name',
            'negative' => 'B19RunEventSummaryTrailDerivationTest::test_stage_counts_tally_this_runs_events_per_stage',
            'basis' => 'the INGEST stage of the example is counted under its own key with its own count; keying by event type or counting twice turns guards red (M15, M16)',
        ],
        'MD-S075-R0124' => [
            'positive' => 'B19RunEventSummaryTrailDerivationTest::test_each_stage_of_the_example_is_counted_under_its_own_name',
            'negative' => 'B19RunEventSummaryTrailDerivationTest::test_stage_counts_tally_this_runs_events_per_stage',
            'basis' => 'the CANONICALIZE stage of the example is counted under its own key with its own count; keying by event type or counting twice turns guards red (M15, M16)',
        ],
        'MD-S075-R0125' => [
            'positive' => 'B19RunEventSummaryTrailDerivationTest::test_each_stage_of_the_example_is_counted_under_its_own_name',
            'negative' => 'B19RunEventSummaryTrailDerivationTest::test_stage_counts_tally_this_runs_events_per_stage',
            'basis' => 'the INDICATORS stage of the example is counted under its own key with its own count; keying by event type or counting twice turns guards red (M15, M16)',
        ],
        'MD-S075-R0126' => [
            'positive' => 'B19RunEventSummaryTrailDerivationTest::test_each_stage_of_the_example_is_counted_under_its_own_name',
            'negative' => 'B19RunEventSummaryTrailDerivationTest::test_stage_counts_tally_this_runs_events_per_stage',
            'basis' => 'the FINALIZE stage of the example is counted under its own key with its own count; keying by event type or counting twice turns guards red (M15, M16)',
        ],
        'MD-S075-R0127' => [
            'positive' => 'B19RunEventSummaryTrailDerivationTest::test_reason_code_counts_tally_this_runs_reason_codes_only',
            'negative' => 'B19RunEventSummaryTrailDerivationTest::test_a_trail_without_reason_codes_has_no_reason_code_counts',
            'basis' => 'reason_code_counts counts this run\'s events per reason code, events without one are not a reason, a trail without reason codes reports none and is written as a JSON OBJECT ({}, as in the locked example) and never as []. This was a DEFECT in the exporter: an empty map was written as [], and the real-execution guard was red before the fix and green after; counting null reasons, keying by event type, dropping the counts or removing the object cast turn guards red (M18, M19, M25, X03)',
        ],
        'MD-S075-R0128' => [
            'positive' => 'B19RunEventSummaryTrailDerivationTest::test_an_empty_trail_invents_no_events_times_types_or_counts',
            'negative' => 'B19RunEventSummaryTieOrderAndEmptyTrailTest::test_an_empty_trail_has_a_null_highest_severity',
            'basis' => 'the summary is derivable from eod_run_events and invents nothing: an empty trail yields zero events, null times and types, empty maps and, per D-MD-B19-A001-004, a null highest_severity (no severity is reported where none was observed — the former INFO default is gone); a new event appears in the next summary (no stored or remembered summary); the trail is not written to; another run\'s events and severities never enter; inventing a first type or a last time, reporting a default severity, caching, writing to the trail or reading all runs turn guards red (M01, M21, M22, M23, M24, S01, S03, S04, S06)',
        ],
        'MD-S075-R0129' => [
            'positive' => 'B19RunEventSummaryTrailDerivationTest::test_the_whole_summary_equals_the_independent_derivation',
            'negative' => 'B19RunEventSummaryTrailDerivationTest::test_the_summary_is_re_derived_from_the_trail_and_leaves_the_trail_untouched',
            'basis' => 'first_event_type, last_event_type, highest_severity, stage_counts and reason_code_counts are DERIVED fields computed from the rows of the trail at the time of the call and not read from any stored or remembered value: the whole summary equals an independent derivation for three runs, follows the trail when it grows, and a remembered summary turns the guard red (M24). eod_run_events has no columns of those names',
        ],
        'MD-S075-R0130' => [
            'positive' => 'B19RunEventSummaryTrailDerivationTest::test_highest_severity_is_the_maximum_of_the_trail_and_error_is_never_lowered',
            'negative' => 'B19RunEventSummaryTrailDerivationTest::test_highest_severity_over_mixed_trails',
            'basis' => 'when the run\'s trail holds an ERROR, highest_severity is ERROR wherever the ERROR sits (first, between warnings, last, alone), on a seeded trail and on a real pipeline trail (which holds no ERROR, so it must not claim one); ignoring ERROR, ranking it below WARN, never raising the severity or taking the last event\'s severity turns guards red (M11, M12, M13, M14)',
        ],
        'MD-S075-R0131' => [
            'positive' => 'B19RunEventSummaryRealRunProvenanceTest::test_the_export_leaves_the_event_trail_row_for_row_intact',
            'negative' => 'B19RunEventSummaryTrailDerivationTest::test_the_summary_is_re_derived_from_the_trail_and_leaves_the_trail_untouched',
            'basis' => 'the file summarizes the trail and does not replace it: exporting leaves the run\'s events row for row intact (a real run, before and after), the file holds no event rows or messages, and summarizing never writes to the trail (M23); the row-level events remain the source for diagnosis',
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
    ];
}
