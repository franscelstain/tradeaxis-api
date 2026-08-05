# Downstream Data Readiness Guarantee (STRATEGY LOCKED)

## Guarantee

For a requested Regular-Market trade date, the platform makes one explicit decision: return the active sealed publication for that date, return an explicitly labeled prior-date result, or return a reason-coded non-readable state. Silent fallback and mixed-publication reads are prohibited.

## Dates tracked independently

- `latest_expected_trade_date`: latest completed Regular-Market session for which a bar is expected under temporal calendar/status evidence;
- `latest_acquired_trade_date`: latest expected date with a traceable source observation outcome;
- `latest_canonicalized_trade_date`: latest date with valid canonical facts;
- `latest_readable_trade_date`: latest date with an active sealed publication satisfying the minimum read product;
- `requested_trade_date` and `effective_trade_date`.

These dates may differ. No one of them may be inferred from `MAX(trade_date)` in a fact table.

## Readiness states

- `READABLE`: one active, sealed, internally consistent immutable publication satisfies all required product gates.
- `HELD`: processing completed far enough to make a deterministic non-publication decision, with reasons and evidence.
- `FAILED`: the run failed and cannot expose a candidate.
- `BUILDING`: a candidate exists but is not sealed/active.
- `SUPERSEDED`: a formerly readable publication remains auditable but is not the active consumer target.
- `NOT_AVAILABLE`: no qualifying publication or allowed fallback exists.

Job success, row counts, eligibility, or a seal record alone do not imply `READABLE`.

## Freshness states

- `FRESH`: effective date equals the requested/latest expected date and all activated operational freshness gates pass.
- `STALE`: a readable prior publication is deliberately returned and its older effective date is visible.
- `DEGRADED`: the effective date may be current or prior, but one or more declared non-silent degraded conditions apply without violating data integrity.
- `NOT_AVAILABLE`: there is no consumer-safe result.

Before `OPERATIONAL_START_DATE`, missing future/daily data is development frontier, not an incident; the response still may not claim `FRESH`. After activation, lag against the latest expected completed session is measured and alerted.

## `READABLE` conditions

All must hold atomically for the same publication:

1. temporal universe, identity, calendar/session, and status revisions are resolved;
2. source observations and canonical rows have complete provenance and accepted schema/date/value validation;
3. coverage expectation/delivery and quality gates pass without denominator manipulation;
4. the selected analytical price product is coherent and factor/event revisions are verified or correctly contaminated;
5. required indicators, daily metrics, and eligibility facts are present or validly null with reasons;
6. non-null full config snapshot/hash, formula/registry/build lineage, content hashes, manifest, and seal verify;
7. the publication is activated through the pointer/state machine and is not superseded; and
8. the read surface can materialize the minimum versioned market-data DTO without cross-publication joins.

These conditions are entirely data-facing. No candidate count, ranking stability, signal outcome, P&L, or other watchlist result can make a failed condition pass or a passed condition fail.

## Fallback policy

Fallback is policy-controlled and explicit. When allowed, it returns the newest prior active sealed publication as `effective_trade_date`, sets `STALE` or `DEGRADED`, and includes the requested date, age in expected trading sessions, and fallback reason.

A prior result is never renamed to the requested date, never counted as delivered for that date, and never combined with newer facts. If the maximum allowed age or required context fails, return `NOT_AVAILABLE`.

## Correction behavior

A correction builds and validates a distinct immutable publication, then atomically switches the active pointer. In-flight reads resolve exactly one pointer/publication. Rollback is another audited pointer event, not content mutation.

## Acceptance proof

Executed integration tests must prove normal current reads, held and failed dates, explicit prior-date fallback, no-fallback behavior, atomic correction switch, concurrent reads, stale activation behavior, and rejection of mixed/configless/unsealed candidates. Until then this is a strategy guarantee, not a production-readiness claim.

## Capability boundary (LOCKED)

**What the readiness guarantee proves.** That a readiness state is derived from publication and freshness facts rather than guessed, that stale is labelled stale, and that the absence of a readable publication is stated rather than filled with the nearest available date.

**What it cannot prove.**

- **That ready means right.** Readiness is a statement about publication resolution and freshness. It carries no claim about the correctness of what was published, and every upstream boundary still applies.
- **That fresh means current in market terms.** Freshness measures the platform's knowledge against the expected session. If the calendar's expectation is wrong, the platform reports fresh for a session that never occurred, or stale for one that never existed.
- **That readiness reflects consumer need.** The horizon a consumer serves determines how much lateness is tolerable. This contract states what the platform knows; it does not know what that costs downstream.

Consequently `READABLE` may be cited as evidence that **a sealed publication resolved for the stated effective date**, never as evidence that **the data is correct or timely enough for a given decision**.
