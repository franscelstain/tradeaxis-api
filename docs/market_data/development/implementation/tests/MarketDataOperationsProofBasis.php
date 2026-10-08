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

        // ---- MD-S075 section 1 "run_summary.json" -- family `artifact_run_summary`, 49 of 52 predicates. HELD, with no entry:
        // MD-S075-R0047 (F-MD-B19-A001-005: warning_count is never written by the pipeline and 'warning' is undefined),
        // MD-S075-R0074 and MD-S075-R0075 (F-MD-B19-A001-006: final_reason_code is a derived value under a persisted column
        // name, and the contract defines no marker for derived fields). The earlier guard proved every minimum field is a KEY
        // of the file; these entries are value proofs: a run record in which every column differs, the manifest of the run's own
        // publication against decoys, and a real sealed run read directly from the tables.
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
        'MD-S075-R0076' => [
            'positive' => 'B19RunSummaryValueProvenanceTest::test_source_context_recovers_thin_minimum_fields_from_persisted_notes_only',
            'negative' => 'B19RunSummaryValueProvenanceTest::test_source_context_invents_no_source_fact_the_run_never_recorded',
            'basis' => 'source_context recovers minimum fields from persisted notes only, reads only the exported run\'s record, and invents no source fact: a run with no recorded source telemetry has null minimum fields and a null retry_attempt_count (it exported 0 retries before this unit\'s correction); a stand-in reason, a default attempt count, a dropped notes recovery or the old zero default turns a guard red (B02, B06, B07, B08)',
        ],
    ];
}
