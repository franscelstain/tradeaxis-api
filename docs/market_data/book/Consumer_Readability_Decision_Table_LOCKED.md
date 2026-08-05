# Consumer Readability Decision Table (STRATEGY LOCKED)

## Decision table

| Requested-date condition | Prior active sealed publication | Result | Effective date | Freshness |
|---|---|---|---|---|
| Active sealed publication passes every minimum-product gate | irrelevant | return requested publication | requested | `FRESH` unless an explicit activated degraded condition applies |
| Candidate is building or unsealed | allowed and within age policy | return prior publication only through explicit fallback policy | prior | `STALE` or `DEGRADED` |
| Candidate is building or unsealed | none/disallowed/too old | no data product | none | `NOT_AVAILABLE` |
| Requested date is held for coverage, quality, provenance, config, adjustment, or indicator failure | allowed and within age policy | return prior publication with hold reasons | prior | `STALE` or `DEGRADED` |
| Requested date failed | allowed and within age policy | return prior publication with failure reasons | prior | `STALE` or `DEGRADED` |
| Requested date failed/held | none/disallowed/too old | no data product | none | `NOT_AVAILABLE` |
| Requested publication is superseded and replacement active | replacement passes | return replacement | requested | evaluate normally |
| Pointer, seal, manifest, or artifact binding is ambiguous/inconsistent | any | fail closed; do not fallback around integrity ambiguity | none | `NOT_AVAILABLE` |
| No bar expected under verified temporal status | product policy explicitly defines a prior-date request | return only with true prior effective date | prior | never silently `FRESH` for requested date |
| Expectation is unknown | any | requested publication cannot pass coverage/readability by exclusion | policy-dependent | never use unknown to shrink denominator |

## Required interpretation

`READABLE` requires one active immutable publication whose identity, observations, canonical facts, analytical products, indicators, eligibility, full config, hashes, manifest, and seal agree. Eligibility or row presence alone is insufficient.

Fallback is not recovery of the requested date; it is a distinct response for a prior effective date. Its age is counted in expected Regular-Market sessions and its reason is visible.

Before operational activation, an absent future/daily publication can be normal development frontier, but it is still not fresh readable data. After activation, the same lag is subject to freshness SLO and alerting.

## Correction concurrency

If the active pointer changes during a read, the implementation either completes using the initially resolved immutable publication or restarts against the new pointer. It may not combine both.

## Fail-safe default

Any state not represented above resolves to no consumer data product with an explicit reason. New success-like states require a contract version and tests before use.

## Capability boundary (LOCKED)

**What the decision table proves.** That every enumerated combination of coverage, seal, pointer, and freshness state maps to exactly one readability outcome, deterministically and without operator judgement.

**What it cannot prove.**

- **That the enumeration is complete.** A table decides the combinations someone listed. A state combination that was never anticipated has no row, and the safety of that moment depends on the default path rather than on this table.
- **That the inputs it consumed were right.** The table is a pure function of states supplied to it. Wrong coverage or a wrong seal state produces a confident, deterministic, wrong readability outcome.
- **That a rarely reached row works.** Determinism is a property of the mapping, not evidence that every branch has been executed.

Consequently a readability outcome may be cited as evidence that **the declared decision rules were applied**, never as evidence that **the date deserves to be read**.
