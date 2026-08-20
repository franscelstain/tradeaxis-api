# Effective Trade Date Contract (STRATEGY LOCKED)

## Date meanings

- `requested_trade_date`: the Regular-Market date the caller asked to evaluate.
- `latest_expected_trade_date`: latest completed session expected under temporal calendar/status evidence.
- `effective_trade_date`: the true date of the immutable publication returned.

These fields are never aliases unless their values actually match.

## Rules

1. A fresh requested-date result has `effective_trade_date = requested_trade_date`.
2. An allowed prior-publication fallback retains its older `effective_trade_date`, includes the requested date, age in expected sessions, and reason, and is labeled `STALE` or `DEGRADED`.
3. No service, DTO, cache, export, or UI may relabel prior-date market facts as requested-date facts.
4. All rows, identity/status/event revisions, price products, indicators, eligibility, and config in one response bind to the same effective date/publication.
5. Latest date is resolved from the versioned Regular-Market calendar/session state and active publication pointers, never from fact-table `MAX(trade_date)`.
6. A non-trading or verified no-bar-expected requested date does not authorize synthetic bars. Any prior result remains explicitly prior.
7. Unknown expectation fails closed and cannot be treated as holiday, suspension, dormancy, or provider absence.

## Cache and audit invariant

Cache keys include product/read-model version, requested date, effective date, and publication ID. Logs and audit records preserve all four plus freshness/readiness and evaluated-at time.

## No-good-fallback rule

If the prior publication is outside the permitted age, lacks the current request's required product contract, or has integrity ambiguity, the result is `NOT_AVAILABLE`; selecting an even older convenient row is prohibited.
