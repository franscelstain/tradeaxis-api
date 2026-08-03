# Market Calendar Requirements Contract (LOCKED)

## Purpose

Define the authoritative temporal calendar and session-completion facts required to decide whether an IDX Regular-Market EOD observation is expected for trade date T.

## Ownership note

Market Data Platform depends on a shared exchange-calendar foundation. This contract defines required semantics without transferring ownership of the shared calendar or of market-data publications.

## Market scope (LOCKED)

The canonical product uses the IDX **Regular Market** calendar in platform timezone `Asia/Jakarta`. Cash and negotiated market schedules must not be silently combined with this calendar.

`trade_date` is the exchange-local Regular-Market session date, not the UTC date of a provider timestamp and not the platform ingestion date.

## Required calendar fields

For each calendar date, the governed dependency must provide:

- `trade_date`
- exchange/market-segment identity (`IDX`, Regular Market or stable equivalent)
- `is_trading_day`
- session state such as scheduled, open, completed, cancelled, or unknown
- effective session open and close times
- `is_half_day` or other special-session marker
- `prev_trading_day` and `next_trading_day`
- source/provenance, calendar version/revision, and `recorded_at`/`known_at`

Ad-hoc holidays, emergency closures, shortened sessions, and late corrections must create effective revision evidence rather than silently rewriting the calendar used by sealed publications.

## Session-completion rule (LOCKED)

A date may become a Regular-Market EOD target only when:

1. the calendar identifies T as a Regular-Market trading day
2. the governed session state proves the session completed or the operational cutoff contract proves completion from an authoritative state
3. provider timestamps are normalized to `Asia/Jakarta` and map to T
4. the requested run binds the calendar/session version used

Wall-clock time passing a configured cutoff is not sufficient when session state is cancelled, revised, unknown, or inconsistent. Missing/unknown completion evidence holds the latest-date publication; it must not guess success.

Historical processing uses the calendar revision/evidence governed for the replay mode. As-known replay must not use a future calendar correction that was unknown at its cutoff.

## Expected-bar decision (LOCKED)

For listing L and trade date T, bar expectation must be explainable from separate facts:

- T is a governed completed Regular-Market trading session
- L is valid in the IDX Regular-Market universe as-of T
- point-in-time trading status does not provide verified evidence that a Regular-Market bar was not expected for the applicable full session

The decision must produce an explicit state such as `EXPECTED`, `NOT_EXPECTED`, or `UNKNOWN` plus reason and source/version references. `UNKNOWN` must not be silently excluded from the coverage denominator.

Current `is_active`, dormancy, historical zero volume, missing provider response, or present-day suspension state cannot prove `NOT_EXPECTED` for T. Only point-in-time authoritative calendar/listing/status evidence may do so.

## Trading-window rules (LOCKED)

- Trading-day windows follow `prev_trading_day`/`next_trading_day`, never calendar-day subtraction.
- `D[-N]` walks the governed calendar N trading sessions.
- Indicator, benchmark, mutation-impact, replay, and API warm-up windows use the same calendar identity/version.
- A requested non-trading date must not finalize `SUCCESS` as a requested trading-date publication.
- Near intentional dataset start `2023-01-02`, fewer prior sessions are not a calendar failure; indicators emit deterministic `NULL` until their individual warm-up requirements are satisfied.
- Calendar-day overfetch is permitted only as transport convenience after the authoritative trading window is resolved.

## Latest trade-date resolution

Before operational activation, latest ingested date is a development frontier and need not equal the latest calendar session.

After operational activation:

- if the current Regular-Market session is authoritatively completed, latest expected date may resolve to today
- otherwise it resolves to the latest prior completed Regular-Market trading date
- missing, cancelled, partial, or unknown session state must remain explicit
- latest expected, latest acquired, and latest readable dates must be reported separately

## Calendar/status dependency failure

- Missing T: block/hold requested-date processing.
- Conflicting calendar revisions: quarantine/hold until one governed revision is selected.
- Unknown session completion: do not publish latest T as EOD.
- Missing status evidence: do not infer suspension or non-expectation from provider absence.

## Acceptance criterion (LOCKED)

For every listing/date coverage decision, the system must be able to show the calendar version, completed Regular-Market session evidence, temporal listing state, temporal trading-status evidence, expectation result, and reason. If it cannot, expected-bar semantics for T are not proven.

## Cross-contract alignment

- `Tickers_and_Identity_Dependency_Contract_LOCKED.md`
- `Trading_Status_Source_Contract_LOCKED.md`
- `Coverage_Universe_Definition_LOCKED.md`
- `EOD_Cutoff_and_Finalization_Contract_LOCKED.md`
- `Replay_Verification_Contract_LOCKED.md`
