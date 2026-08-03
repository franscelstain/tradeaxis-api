# Contract Tests Specification (STRATEGY LOCKED)

## Proof rule

Tests derive expected meaning from independently reviewed owner contracts and oracles, not from copying current implementation output. A green test for a superseded rule is regression evidence against the new strategy, not proof of correctness.

Each test identifies contract/version, fixture/manifest hash, runtime/database/build, frozen input revisions/config, assertion layers, and evidence admission state.

## Required test groups

1. **Acquisition/provenance:** immutable success/failure/stale/schema-invalid observations, secret sanitization, refetch linkage, provider-neutral mapping, wrong-date rejection.
2. **Temporal identity:** inactive-now/active-then, listing/symbol changes, symbol reuse, provider mapping intervals, as-known cutoff.
3. **Calendar/status/expectation:** completed IDX Regular-Market sessions, verified full-session no-bar state, unknown expectation, no dormancy/provider-absence exclusion.
4. **Canonical bars:** exact source mapping, positive/internal OHLC consistency, zero/null rejection, duplicate conflict quarantine, actual EOD field provenance.
5. **History/correction:** observation and snapshots immutable, corrected publication distinct, pointer switch atomic, rollback by pointer, direct repair impossible.
6. **Corporate actions/products:** synthetic detector never verifies, ex-date and versioned event/factor lifecycle, coherent structural OHLC/volume, RAW unchanged, total return separate.
7. **Coverage/eligibility:** expectation/delivery/quality/liquidity/status/event facts independent; unknown in denominator; complete reason sets.
8. **Metrics/indicators:** actual versus proxy units, stable Wilder ATR long chain, warm-up/gap/correction propagation, fixed windows, null reasons, formula/config/hash determinism.
9. **Readiness/consumer:** full minimum DTO, same publication/config/factor/formula, explicit fresh/stale/degraded/unavailable, no current/latest/internal bypass.
10. **Replay/backtest:** exact publication and as-known modes, anti-future/anti-survivorship fixtures, original/corrected contexts.
11. **Operations/schema:** scheduler target/lock fencing/idempotent retry, activation-aware stale alert, MariaDB clean install/upgrade, SQLite mirror, non-null enforcement and privileges.

## Assertion layers

Required scenarios assert row values/null reasons, observation and temporal lineage, run/gate/reason states, artifact/manifest/config hashes, publication/seal/pointer state, gateway response/freshness, and replay result. Row count or exit code alone is insufficient.

## Database requirement

Portable unit tests may use SQLite, but uniqueness/check/FK/transaction/isolation/locking and migration behavior are proven on MariaDB. Any unsupported SQLite behavior is an explicit non-pass until MariaDB proof exists.

## Current closure state

The V2 schema mirror test proves only table/column presence and removal of the draft repair schema. Most behavior groups above remain unimplemented or unexecuted; therefore order 21 and production relock remain open.
