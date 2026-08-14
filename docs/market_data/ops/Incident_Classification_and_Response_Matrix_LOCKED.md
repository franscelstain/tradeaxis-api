# Incident Classification and Response Matrix (STRATEGY LOCKED)

## Activation rule

Before `OPERATIONAL_START_DATE`, unfinished future/daily scope is development frontier, not a production incident. Integrity/security violations are incidents in every phase. After activation, freshness and availability failures are classified below.

| Class | Examples | Severity floor | Consumer protection | Mutation/retry rule |
|---|---|---:|---|---|
| integrity | history mutation attempt, hash/seal/config mismatch, mixed publication, pointer ambiguity | critical | fail closed; preserve last verified publication only if gateway integrity is certain | no ordinary retry or fallback around ambiguity |
| security/provenance | secret leakage, unauthenticated payload, missing observation provenance | critical | block affected data/evidence | revoke/contain; new sanitized evidence/artifacts |
| semantic | wrong identity/date/status, mixed price basis, unverified factor, ATR reseed | high | hold/withdraw affected publication by pointer policy | correction creates revisions, never in-place repair |
| source/schema | outage, stale/wrong-date response, schema drift, rate limit | medium; high when freshness breached | requested date held; prior date explicitly stale if allowed | bounded idempotent retry with retained observations |
| coverage/quality | missing/invalid expected bars, quarantine threshold | medium/high by breadth | hold requested publication | no denominator shrink or synthetic fill |
| scheduler/lock | missed due run, lock conflict, stuck worker | medium; high after SLO breach | keep truthful freshness | fenced recovery/retry |
| consumer freshness | latest expected date unreadable beyond target | medium/high by age | `STALE`, `DEGRADED`, or `NOT_AVAILABLE` | recover root cause; do not relabel prior date |
| proof/monitoring | missing evidence, alert delivery failure, blocked replay | high for release/activation | block readiness/relock claim | restore proof path; `BLOCKED` is not pass |

Severity increases with breadth, duration, consumer exposure, undetected period, irreversibility, and regulatory/security impact. Every incident records all classes/reasons even when one primary class is used for routing.

## Capability boundary (LOCKED)

**What incident classification proves.** That an observed condition maps to a declared severity and response path deterministically, without operator improvisation, and that closure requires evidence matching the claimed outcome.

**What it cannot prove.**

- **That every incident was observed.** Classification acts on conditions that surfaced. A defect that produced no alert, no failing gate, and no complaint is never classified, and the absence of incidents is therefore not evidence of health.
- **That severity reflects consequence.** Severity is assigned from declared signals. A low-severity data defect can outrank a high-severity outage for a consumer whose horizon it corrupts.
- **That a closed incident is a solved one.** Closure requires evidence of the claimed outcome; whether the underlying cause recurs is outside what closure asserts.

Consequently a clean incident record may be cited as evidence that **observed conditions were handled per the matrix**, never as evidence that **nothing went wrong**.
