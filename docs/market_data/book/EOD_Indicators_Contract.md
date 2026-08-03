# EOD Indicators Artifact Contract (LOCKED)

## Purpose

Define immutable, publication-bound, versioned indicator facts for one stable instrument/listing and trade date.

Indicators are upstream measurements, not signals, rankings, watchlist groups, or portfolio/execution actions.

## Identity and lineage (LOCKED)

Logical row identity within a publication is `(trade_date, stable listing/instrument identity)`. Physical identity additionally binds publication, run, indicator row revision, price-product/factor-set, formula set, config snapshot, temporal universe/calendar, and source-bar publication.

Optional live tables are rebuildable projections only. Immutable publication-bound history is authoritative.

## Required metadata

- trade date and stable identity
- publication/run/row revision
- `indicator_set_version` and formula version
- price-basis/product identity (`STRUCTURAL_ADJUSTED` for technical default)
- factor-set hash/reference and adjustment/contamination state
- config hash/snapshot reference
- field-level validity/nullability/reasons plus compatibility primary reason
- source bar publication/hash

## Baseline facts

- actual/proxy liquidity fields with explicit labels
- `atr14_pct`, `vol_ratio`
- `roc5`, `roc10`, `roc20`
- `ma20`, `ma50`, distance metrics
- `hh20`, `ll20`, and range-position metrics
- source-backed sector/benchmark context
- separate corporate-action, trading-status, suspension/UMA, and event-risk facts

Exact identities, formulas, dependencies, and required/optional classification are owned by the versioned indicator registry and formula specification.

## Price basis (LOCKED)

One run uses one coherent `STRUCTURAL_ADJUSTED` OHLC/volume vector. Provider `adj_close`, raw close fallback, and per-date basis mixing are forbidden. Missing verified factors cause explicit null/contamination state.

## Nullability and row validity

- Nullability is per field; one missing optional dependency does not null unrelated facts.
- Intentional dataset-start and post-listing warm-up produce deterministic `NULL` per formula.
- Missing expected bars, invalid inputs, unresolved factors, or recursive-chain gaps use distinct reasons.
- Overall `is_valid` reflects the versioned required-field set, but complete field-level states/reasons must remain available.
- No zero OHLCV placeholder row is permitted.

## Temporal source context

Sector membership, benchmark, corporate action, factor, and trading status resolve as-of trade date and, for as-known mode, only from revisions known at the run cutoff. Absence remains unknown/NULL, not fabricated safe state.

## ATR state and mutation impact

ATR follows stable-seed Wilder recursion. The runtime must persist versioned recursive state or compute from stable chain. A changed historical TR/factor can affect every later ATR, so a fixed sliding horizon is insufficient; affected publications require recompute/correction until chain equivalence is restored.

## Recompute and immutability

Recompute reads immutable source/master revisions and writes new candidate/publication-bound rows. It cannot mutate bars, source/master events, factors, sealed indicator rows, or history. Changed published output requires correction/republication; identical output is an evidenced no-op.

## Eligibility interaction

Indicator validity/warm-up/contamination facts feed eligibility under explicit versioned gates. Indicators do not decide alpha or selection.

## Determinism

Identical source publication, temporal source revisions, product/factor version, stable ATR state/seed, formula/config version, and precision rules produce identical rows, field states, reasons, and hashes.

## Acceptance criterion (LOCKED)

Every value and `NULL` is explainable from exact dependencies/version identity, and long-chain reruns never drift as the loaded window moves.

## Cross-contract alignment

- `../indicators/EOD_Indicators_Formula_Spec.md`
- `../indicators/Indicator_Computation_Specification.md`
- `../registry/Indicator_Registry_Baseline_LOCKED.md`
- `Indicator_Nullability_And_OHLCV_Gap_Contract.md`
- `Corporate_Action_and_Adjustment_Policy_Selected_Defaults_LOCKED.md`
