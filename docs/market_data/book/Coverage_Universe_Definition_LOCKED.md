# Coverage Universe and Bar Expectation Definition (LOCKED)

## Purpose

Define temporal universe membership and the separate point-in-time decision of whether a Regular-Market EOD observation is expected for each listing/date.

## Temporal universe (LOCKED)

The universe for trade date D contains every equity listing whose effective listing interval and IDX Regular-Market scope cover D, including instruments inactive today but active on D.

Universe resolution uses stable issuer/instrument/listing identity and temporal records. Current `is_active`, current ticker lists, current symbol, or present-day board/status cannot resolve historical membership.

## Bar expectation states

For each universe listing/date, resolve:

- `EXPECTED` — a Regular-Market observation is expected
- `NOT_EXPECTED` — verified point-in-time calendar/status evidence proves no applicable full-session bar was expected
- `UNKNOWN` — expectation evidence is incomplete/conflicting

Only verified `NOT_EXPECTED` may be excluded from the denominator. `UNKNOWN` remains included/fail-safe and visible; it must never be silently treated as `NOT_EXPECTED`.

## Valid `NOT_EXPECTED` evidence

Examples require source-backed, effective-dated proof:

- non-trading/cancelled Regular-Market session
- listing not yet effective or already terminated on D
- verified full-session suspension/halt/status whose dictionary semantics explicitly state no bar expected

Partial-session status requires its own semantics and does not automatically exclude the date.

## Forbidden expectation exclusions (LOCKED)

The following cannot prove `NOT_EXPECTED`:

- dormancy or no recent bars
- historical zero volume or illiquidity
- provider missing/empty response
- current `is_active` or current suspension state
- price anomaly or corporate-action candidate
- downstream watchlist/liquidity preference
- manual removal without temporal source evidence

Dormancy, zero-volume history, and liquidity are separate factual dimensions. Excluding them would hide provider outages and make coverage look healthier as missing data accumulates. Any tradability interpretation belongs downstream and must not alter coverage or upstream data usability.

## Counts and evidence

Each run/publication must expose:

- temporal universe count/version/hash
- `EXPECTED`, `NOT_EXPECTED`, and `UNKNOWN` counts
- per-listing expectation reason/source/version
- delivered, missing, invalid/quality-blocked counts separately
- excluded listing sample and complete evidence reference

The coverage denominator is `EXPECTED + UNKNOWN`; equivalently, temporal universe minus verified `NOT_EXPECTED`. A global dependency failure that makes the basis untrustworthy may make coverage `NOT_EVALUABLE`, but it never reduces the denominator silently.

## Re-entry behavior

A listing excluded by a verified temporary status returns when a governed clear/end event makes observations expected again. This is temporal status resolution, not dormant auto-reentry.

## Acceptance criterion (LOCKED)

A provider outage for an otherwise valid listing must increase missing delivery and cannot disappear from coverage after N days. An inactive-now-but-active-on-D listing must remain in the historical universe.

## Ownership boundary

This document owns universe and expectation semantics. Coverage delivery formula/gate is owned by `EOD_COVERAGE_GATE_CONTRACT_LOCKED.md`; quality, liquidity, event risk, and eligibility remain separate owner concerns.

## Cross-contract alignment

- `Tickers_and_Identity_Dependency_Contract_LOCKED.md`
- `Market_Calendar_Requirements_Contract.md`
- `Trading_Status_Source_Contract_LOCKED.md`
- `EOD_COVERAGE_GATE_CONTRACT_LOCKED.md`
