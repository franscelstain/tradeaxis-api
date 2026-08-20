# Contract Test Matrix (STRATEGY LOCKED; V2 PROOF OPEN)

Documentation specification status: **`DOCUMENTATION_READY`**. `Current V2 state` below reports implementation/executed-proof availability only; `not implemented`, `partial`, or `superseded` is not a documentation defect and remains mandatory handoff work.

| Contract area | Positive oracle | Required negative proof | Runtime layer | Current V2 state |
|---|---|---|---|---|
| observation/provenance | frozen valid payload→immutable envelope→RAW | stale/schema/wrong-date/secrets/no-lineage | adapter + repository + MariaDB | not implemented |
| temporal identity/mapping | active-at-T listing and mapping | current-state/symbol-text/survivorship leak | repository + replay | not implemented |
| calendar/status/expectation | completed session and verified full-session status | unknown/dormancy/provider absence excluded | service + coverage integration | not implemented |
| canonical bars | valid positive RAW OHLCV | zero/inconsistent/conflicting duplicate/overwrite | ingestion + history repository | partial legacy only |
| actions/factors/products | verified event→revisioned coherent product | synthetic verify, adj-close mix, direct repair | factor builder + correction | superseded tests present |
| coverage/eligibility | independent expectation/delivery/quality/reasons | denominator shrink and reason erasure | finalize + snapshot | superseded/partial |
| actual/proxy metrics | source actual and RAW close-volume proxy | mislabeled/mixed-dimension metric | computation + DTO | not implemented |
| indicators | long-chain independent Wilder oracle | reseed/gap skip/bounded correction impact | computation + publication | superseded/partial |
| config/hash/seal | full immutable snapshot and deterministic replay | null/current-env/annotation omitted | resolver + manifest + seal | schema partial only |
| read model/readiness | one complete publication-bound DTO | max/current/mixed/implicit fallback bypass | gateway + privilege + concurrency | legacy partial |
| publication/as-known replay | frozen exact and cutoff-isolated results | future/current state leakage | replay runner + evidence | legacy exact partial |
| scheduler/incident/SLO | consecutive activated sessions | missed/stale run silently successful | deployed operations | not proven |
| schema/migrations | clean install + upgrade + mirror | repair columns/type and missing binding | MariaDB + SQLite | SQLite mirror partial |

`partial`, `legacy`, `superseded`, `blocked`, or `not implemented` does not satisfy the done criterion. The matrix is updated from executed evidence, never from document completion alone.
