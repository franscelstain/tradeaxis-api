# Market Data Failure Playbook (STRATEGY LOCKED)

## First response

1. Preserve evidence and the prior active publication; do not mutate facts to make a run green.
2. Record activation context and requested/latest expected/acquired/canonicalized/readable dates.
3. Freeze run/attempt/lock/config/observation/candidate/publication identifiers and all reasons.
4. Determine whether the issue is retryable acquisition/runtime failure, deterministic data hold, integrity incident, or missing proof/config.
5. Protect consumers through the readiness gateway and alert according to activation/SLO state.

## Response matrix

| Failure | Immediate state | Retry? | Required action |
|---|---|---|---|
| provider timeout/rate limit/transient transport | requested date remains non-readable | bounded/idempotent | retain failed observation; retry/refetch as a new linked observation |
| stale/wrong-date/schema-invalid response | quarantine/hold | only after source condition changes | preserve payload/provenance; adapter/schema investigation |
| missing provider rows/outage | delivery gap/hold | bounded | never shrink expectation denominator or call listings dormant |
| invalid/zero OHLC or conflicting duplicate | quarantine/hold | not as automatic repair | obtain new source evidence or explicit correction |
| unknown calendar/status/mapping | blocked/hold | after authoritative revision | do not infer from current state/provider absence |
| synthetic price break/unverified action | contaminated/hold dependent products | no auto-adjust | authoritative verification and new event/factor revision |
| coverage/quality/indicator gate fail | hold | after input/config correction | preserve candidate; build a distinct corrected candidate |
| missing/mismatched config/hash/manifest/seal | integrity failure | no ordinary retry | fail closed, investigate all affected artifacts |
| lock conflict | skipped/blocked | retry after verified owner/expiry | respect fencing; never run concurrent promotion |
| pointer ambiguity or mixed-publication read | critical integrity incident | no fallback | disable affected read, preserve state, correct via audited pointer/publication workflow |
| stale latest expected date after activation | freshness incident | cause-dependent | alert and recover; prior result remains explicitly stale |

## Correction rule

A factual correction always starts from retained evidence and creates new observation/event/config/factor/artifact/publication revisions. It cannot update sealed content in place. Rollback is an audited pointer to a valid immutable publication.

## Closure

Close only when root cause, affected dates/listings/publications, evidence, remediation, regression fixture, and consumer-gateway state are recorded. For activated incidents, confirm alert recovery and SLO impact. A later successful process without proving the originally affected state is not closure.
