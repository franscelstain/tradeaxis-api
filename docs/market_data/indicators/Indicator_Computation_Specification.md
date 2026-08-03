# Indicator Computation Specification (LOCKED IMPLEMENTATION ORDER)

## Role

`EOD_Indicators_Formula_Spec.md` owns formulas; `../book/EOD_Indicators_Contract.md` owns artifact semantics. This document locks computation orchestration and may not redefine them.

## Bound inputs

Before compute, resolve and freeze:

- requested trade date and publication candidate
- stable point-in-time universe/listing/symbol mapping
- Regular-Market calendar/session version
- immutable raw-bar publication/observation lineage
- coherent `STRUCTURAL_ADJUSTED` product and verified factor-set version
- sector/benchmark/status/event revisions
- formula/indicator-set version
- full output-affecting config snapshot/hash

Missing or conflicting mandatory binding holds computation; it must not read current/future defaults.

## Computation order (LOCKED)

1. Resolve temporal identity/calendar and required dependency dates.
2. Load immutable `RAW` bars for the exact source publication.
3. Resolve verified event/factor revisions and construct one coherent structural-adjusted OHLC/volume vector.
4. Resolve actual traded value and separately named raw close-volume proxy.
5. Resolve or reconstruct stable recursive ATR state from the governed seed.
6. Compute formulas with no intermediate rounding.
7. Resolve source-backed sector/benchmark/status/event facts as-of the governed cutoff.
8. Emit per-field value/nullability/validity/reasons.
9. Bind row to product/factor/formula/config/source identities and hash it.
10. Persist only to candidate/immutable publication-bound snapshot flow.

## Window loading

Fixed-window indicators load exact calendar dependencies. ATR cannot be calculated from an arbitrary fixed sliding window; the loader must obtain prior versioned recursive state or the full stable chain from dataset/listing seed.

Dataset start `2023-01-02` caps initial history. Early outputs remain `NULL`; no pre-boundary data is invented. Newly listed instruments seed from their listing history.

## Gaps and invalid inputs

- Missing expected bar: dependent field `NULL` with missing-dependency reason.
- Invalid/zero OHLC: never present canonically; rejection/quality state blocks dependent fields.
- Zero volume with valid price: valid observation, handled by formula denominator/liquidity rules.
- Missing actual traded value: actual-value metric `NULL`; proxy may compute separately without fallback/coalescing.
- Unverified factor/break: affected fields `NULL`/contaminated.
- Missing optional benchmark: only dependent benchmark fields `NULL`.

## Rounding and serialization

Use locked decimal/numeric semantics and deterministic ordering. Round only when persisting the final field according to registry precision. Hash serialization uses the versioned manifest/number-format contract.

## Recompute safety

Recompute never fetches or mutates source/master/bar/factor records. Any output-affecting source revision is selected before computation. Published changes create new correction/publication lineage.

Mutation impact follows the complete dependency graph. ATR impact may continue through every later recursive state; a maximum fixed window is not a safe bound.

## Runtime proof boundary

Historical proof under prior `adj_close` fallback, zero-placeholder, or sliding ATR behavior is not proof of this corrected strategy. Relock requires golden long-chain and negative semantic fixtures for the new version.

## Acceptance criterion

Two computations with the same complete bindings yield identical value/null/reason/hash output regardless of query load-window size or current master/config state.
