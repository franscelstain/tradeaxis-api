# EOD Coverage Delivery Gate Contract (LOCKED)

## Purpose

Define whether expected IDX Regular-Market observations were delivered for requested trade date D, separately from quality, liquidity, event risk, and eligibility.

Coverage is evaluated during promote from immutable acquisition/canonicalization evidence. Import success is not coverage pass, and coverage pass alone is not readability.

## Separate dimensions (LOCKED)

- **Expectation:** was a bar expected for listing/date?
- **Delivery coverage:** was an identifiable requested-date market observation delivered?
- **Quality:** can the delivered observation be trusted/used canonically?
- **Liquidity:** what are the actual trading facts/proxies?
- **Event risk/status:** is output contaminated or blocked?
- **Eligibility:** do declared upstream data-use gates pass?

These dimensions must have separate fields/reasons. Coverage must not absorb or hide the others.

## Denominator (LOCKED)

`coverage_expected_count = temporal_universe_count - verified_not_expected_count`

Both `EXPECTED` and unresolved `UNKNOWN` listings remain in the fail-safe denominator. Only point-in-time verified calendar/listing/status evidence may produce `NOT_EXPECTED` exclusion.

Dormancy, zero volume, illiquidity, provider absence, current activity, and downstream preference never reduce this denominator.

## Numerator and delivery classification (LOCKED)

`coverage_delivered_count` counts unique expected listing/date observations that are traceably delivered for requested D and linked to immutable source observation evidence.

It does not count:

- HTTP/request success without an identifiable observation
- a prior/stale date row
- duplicate rows more than once
- fabricated/filled rows
- observations outside temporal universe

A delivered observation that fails quality/canonical validation remains visible as delivered plus quality-blocked/invalid; it must not become eligible/readable. Schema-unknown or unidentifiable payload is not delivered.

Required separate counts include delivered, canonical-valid, invalid/quality-blocked, missing, stale, and quarantined.

## Formula and threshold

When denominator is positive and trustworthy:

`coverage_ratio = coverage_delivered_count / coverage_expected_count`

Locked minimum delivery threshold remains `0.98`.

- ratio `>= 0.98`: coverage may be `PASS`
- ratio `< 0.98`: coverage is `FAIL`
- denominator/basis unprovable: `NOT_EVALUABLE`

Coverage `PASS` never overrides hard quality, schema, provenance, status/event, config, seal, or eligibility blockers.

A ratio between `0.98` and `1.00` is a bounded delivery gap, not “complete delivery.” It may continue only when every missing expected listing remains represented by explicit delivery/eligibility state and reasons, missingness is not caused by untrustworthy denominator/global source-state ambiguity, and every independent hard gate passes. The publication artifact families and full-universe eligibility accounting must still be complete.

## Gate states

- `PASS`
- `FAIL`
- `NOT_EVALUABLE`

Legacy `BLOCKED` is normalized to `NOT_EVALUABLE` for coverage and may remain a separate quality/readiness state.

## Date-driven and source behavior

Historical/recent requested dates use the same semantics. Provider window limits, retry exhaustion, manual source, correction, or recovery cannot change threshold or denominator rules.

An import process that is incomplete, not coverage-evaluated, missing required artifact/reason rows, or below threshold may preserve evidence but cannot become readable. A bounded gap that explicitly satisfies the `0.98` rule above is not permission to hide missing listings. Stale/prior rows never count for requested D.

## Required audit-visible fields

- requested trade date
- temporal universe identity/version/hash and count
- expected/not-expected/unknown counts
- delivered/canonical-valid/invalid/missing/stale/quarantined counts
- expectation and missing samples/references
- coverage ratio/threshold/state/reason
- separate quality, liquidity, event-risk, and eligibility states

## Promote/readability rule

Promote may continue only when coverage `PASS` and all independent hard gates pass. `FAIL` or `NOT_EVALUABLE` remains `NOT_READABLE`; prior readable publication may remain as explicitly stale/prior fallback but never masquerades as D.

## Acceptance criterion (LOCKED)

Provider failure cannot disappear through dormant/liquidity exclusion; a 100% delivery ratio cannot conceal invalid/quarantined observations because independent quality counts/gates remain visible and enforcing.

## Cross-contract alignment

- `Coverage_Universe_Definition_LOCKED.md`
- `Coverage_Gate_Enforcement_Contract_LOCKED.md`
- `Trading_Status_Source_Contract_LOCKED.md`
- `EOD_Eligibility_Snapshot_Contract_LOCKED.md`
- `Run_Status_and_Quality_Gates_LOCKED.md`
