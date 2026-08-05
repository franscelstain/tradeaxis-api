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

## Evidence validity boundary (LOCKED)

Coverage evidence records `coverage_contract_version`, which states which coverage rules applied. It does not state which **universe resolver** produced the denominator, and the denominator is the part of coverage that depends most on something outside this contract.

Consequently a stored coverage result cannot, by itself, be distinguished from one produced by a resolver since found defective.

Required bindings on every coverage evidence record:

- **identity/universe resolver version** that produced the temporal universe, alongside the coverage contract version;
- **calendar and trading-status revision identities** used to resolve expectation;
- the resulting `coverage_universe_count`, `coverage_bar_not_expected_count`, and delivered counts, each already required above.

Admissibility rules:

- Coverage evidence produced under a **superseded resolver, calendar revision, or status revision is not admissible** as proof of temporal correctness. It remains valid evidence of what the platform decided at the time, which is a different claim.
- Such evidence is either re-derived under current components or explicitly qualified wherever it is cited. It is never silently carried forward as current proof.
- A conformance claim that depends on coverage correctness must name the resolver version its evidence was produced under.

This generalises: **evidence binds the version of every component its correctness depends on, not only the version of the contract that produced it.** A coverage record binding only its own contract version records less than it appears to.

## Denominator exclusion path (LOCKED)

`bar_not_expected` is the only mechanism permitted to reduce the denominator, which makes it the only mechanism through which a provider failure could be concealed. Its rarity in operation is a property of the market, not evidence that it is safe.

- The exclusion path requires positive proof of correct behaviour — a governed test that exercises it — independently of how seldom production triggers it.
- Low usage is never cited as assurance. A path used in a fraction of a percent of runs is effectively untested by production traffic, and its first material use may also be its first real execution.
- Every exclusion records the verified evidence that produced it, so that a spike in exclusions is inspectable rather than merely plausible.
- A sustained rise in exclusion rate is itself a finding for the quality gates, since the honest reading is either a market event affecting many listings or a defect in expectation resolution.

## Capability boundary (LOCKED)

**What the gate proves.** That the expected set of listings for a date was delivered, traceably and without fabrication, at or above the configured ratio; that provider absence and dormancy did not shrink the denominator.

**What the gate cannot prove.**

- **That the delivered values are correct.** Coverage counts observations, not prices. A `PASS` at 100 percent is fully compatible with every delivered bar being wrong. Correctness is the quality dimension's concern, and quality validation has its own boundary.
- **That the expectation itself is right.** Coverage is measured against the calendar and status evidence. When those are wrong in the direction of `NOT_EXPECTED`, numerator and denominator shrink together and the ratio stays clean while a real Regular-Market session is missing entirely. Coverage is self-consistent under a wrong calendar; it cannot detect that condition from the inside.
- **That a session is complete in market terms.** The gate answers "did we get a row for each listing we expected", not "did we get the whole session".

### Consequences (LOCKED)

- Coverage `PASS` may never be cited as evidence of data correctness, calendar correctness, or session completeness.
- A missing expected trading date is a calendar/status finding, not a coverage finding, and must be detected by an independent check that does not derive its expectation from the same calendar revision.
- Because the gate cannot see its own expectation being wrong, calendar and trading-status revisions carry the burden of proof independently under their owner contracts.

For a five-trading-day analytical horizon, a silently absent session is not a small loss. It shortens the window while every field continues to label it as complete.

## Acceptance criterion (LOCKED)

Provider failure cannot disappear through dormant/liquidity exclusion; a 100% delivery ratio cannot conceal invalid/quarantined observations because independent quality counts/gates remain visible and enforcing.

## Cross-contract alignment

- `Coverage_Universe_Definition_LOCKED.md`
- `Coverage_Gate_Enforcement_Contract_LOCKED.md`
- `Trading_Status_Source_Contract_LOCKED.md`
- `EOD_Eligibility_Snapshot_Contract_LOCKED.md`
- `Run_Status_and_Quality_Gates_LOCKED.md`
