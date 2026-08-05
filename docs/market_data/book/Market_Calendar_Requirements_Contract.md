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

## Calendar provenance tiers (LOCKED)

A calendar row may be a **verified** fact or a **projection**, and the two must never be stored or read as the same thing.

| Tier | Meaning | May produce `EXPECTED` |
|---|---|---|
| `VERIFIED` | Reconciled against the exchange-published schedule for that period, with source reference and reconciliation date | yes |
| `PROJECTED` | Derived by rule — typically weekdays minus known recurring closures — for a period the exchange has not yet published | **no** |

Exchanges publish holiday schedules a limited distance ahead. Rows beyond that horizon are necessarily projections, and a projected weekday is an assumption about a date, not a governed trading session.

Rules:

- Every calendar row carries its tier, the source it was reconciled against, and the date of that reconciliation.
- A `PROJECTED` row resolves bar expectation to `UNKNOWN`, never `EXPECTED`. It stays in the fail-safe denominator under the coverage contract and never silently produces a missing-bar finding against a date that was never a session.
- A tier transition from `PROJECTED` to `VERIFIED` is a calendar revision with effective evidence, not an in-place edit.
- A calendar generated wholly by weekday rule, with no holiday reconciliation for its period, must be recorded as `PROJECTED` for that period even when it visually resembles a real schedule.
- Range extent is not evidence of authority. A calendar that reaches far into the future says only that rows were generated, not that sessions were confirmed.

Without this tiering, the first trading year beyond the published horizon produces an expected bar on every public holiday, and the resulting coverage failures look like provider faults rather than calendar assumptions.

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

## Shortened-session semantics (LOCKED)

`is_half_day` is required among the fields above, but a marker without stated meaning changes nothing downstream. IDX runs several shortened Regular-Market sessions each year, typically around major holidays.

What a shortened session **does not** change:

- it is a completed Regular-Market session and produces a normal expected bar;
- the bar is canonical, not partial, degraded, or quality-blocked;
- coverage treats it exactly like any other trading day.

What it **does** change, and must therefore be visible:

- **Volume and every measure derived from it.** A shorter session mechanically produces lower traded volume for reasons that have nothing to do with the instrument. Rolling liquidity measures spanning a shortened session are depressed by the calendar, not by the market.
- **Range-based measures.** A shorter session narrows the opportunity for the day's high and low to separate, which affects true range and any measure built on it.

Requirements:

- The session-length context of trade date T must be retrievable alongside its bar, so a consumer can tell a low-volume day caused by the schedule from one caused by the instrument.
- Liquidity and volatility measures must not silently normalise, exclude, or reweight shortened sessions. Any such treatment is a versioned decision owned by the measure's contract and must be declared there.
- A shortened session must never be inferred from low volume. It is a calendar fact and comes only from calendar evidence.

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

## Capability boundary (LOCKED)

The calendar is the root of expectation. Everything downstream measures itself against it, which means **no downstream check can detect that the calendar is wrong**.

**What the calendar proves.** That a given date was, according to the recorded revision, a completed Regular-Market session; that expectation for a listing/date is traceable to a version, an evidence source, and a reason.

**What the calendar cannot prove.**

- **Its own completeness.** A session the calendar never recorded produces no missing bars, no coverage shortfall, and no reason code. Numerator and denominator omit it together and every gate reports clean.
- **Its own correctness from platform data.** Bars, provider absence, and dormancy are explicitly barred from establishing expectation, and rightly so. That prohibition removes the only internal signal, so verification must come from outside.

### Consequence — completeness is verified externally (LOCKED)

The calendar is the root of expectation, so it falls under the shared external-reconciliation rules owned by global gate 13 in `Market_Data_Implementation_Conformance_Matrix_LOCKED.md`. Those rules are not repeated here.

Domain parameters owned by this contract:

- **Authority:** the exchange-published Regular-Market schedule for the period.
- **Scope:** every period from the intentional dataset start through the furthest `VERIFIED` row. Rows beyond the published horizon remain `PROJECTED` under the provenance tiers above and are not reconcilable until the exchange publishes them.
- **Qualification:** a reconciled period upgrades its rows from `PROJECTED` to `VERIFIED` as a calendar revision.

This is the burden the coverage gate contract explicitly assigns here: because coverage is self-consistent under a wrong calendar, the calendar carries its own proof.

## Acceptance criterion (LOCKED)

For every listing/date coverage decision, the system must be able to show the calendar version, completed Regular-Market session evidence, temporal listing state, temporal trading-status evidence, expectation result, and reason. If it cannot, expected-bar semantics for T are not proven.

## Cross-contract alignment

- `Tickers_and_Identity_Dependency_Contract_LOCKED.md`
- `Trading_Status_Source_Contract_LOCKED.md`
- `Coverage_Universe_Definition_LOCKED.md`
- `EOD_Cutoff_and_Finalization_Contract_LOCKED.md`
- `Replay_Verification_Contract_LOCKED.md`
