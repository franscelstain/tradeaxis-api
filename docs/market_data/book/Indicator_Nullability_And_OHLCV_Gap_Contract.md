# Indicator Nullability and OHLCV Gap Contract (LOCKED)

## Core rules

1. Canonical zero/null OHLC placeholders are forbidden; missing/invalid observations remain separate evidence.
2. Indicator nullability is per field and reason-coded.
3. Intentional dataset start `2023-01-02` and later listing dates create normal deterministic warm-up `NULL` states.
4. Fixed windows require exact expected trading-date dependencies; missing sessions are not skipped or forward-filled.
5. ATR requires stable recursive seed/state; a gap cannot be hidden by reseeding a sliding window.
6. Missing optional sector/benchmark/source facts null only their dependent fields.
7. Unresolved structural action/price break nulls or contaminates every affected field until verified factor/correction lineage exists.
8. Zero volume with valid positive OHLC is distinct from missing data; price formulas may remain valid while liquidity/volume denominators follow their rules.

## Reason separation

At minimum distinguish insufficient history, missing expected dependency, invalid input, zero denominator, missing optional benchmark, unresolved adjustment/contamination, and provenance/config mismatch.

A compatibility primary reason must not erase field-level reason sets.

## Publication behavior

Early or partially-null rows may exist in a publication when run-level gates allow it, but eligibility facts must expose required-field blocks. A missing row or `NULL` is never silently turned into zero.

Published nullability/reasons are immutable and versioned. Corrections create new indicator/publication snapshots.

## Acceptance criterion

Every `NULL` has a deterministic, inspectable cause; every non-null value has complete valid dependencies on one coherent basis.

## Capability boundary (LOCKED)

**What nullability rules prove.** That a value is emitted only when its declared preconditions are met, that an unmet precondition yields a deterministic `NULL` with a reason rather than a substitute, and that gaps are never forward-filled or interpolated.

**What they cannot prove.**

- **That a non-null value met its preconditions meaningfully.** The rules check that the declared conditions were formally satisfied — enough sessions present, dependencies loaded. Formal satisfaction over a window whose content is wrong still produces a non-null value with no reason attached.
- **That the precondition set is sufficient.** Preconditions cover the failure modes someone anticipated. A window that is complete, contiguous, and contaminated satisfies every count-based precondition there is.
- **That `NULL` means the market had nothing to say.** `NULL` means the platform declined to assert a value. Those are different statements, and only the second is being made.

Consequently a fully populated indicator row may be cited as evidence that **declared preconditions were satisfied**, never as evidence that **the window was suitable**.
