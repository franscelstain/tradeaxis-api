# R2 / C171 Paramset Validator Campaign Extensions

> **Doc Role:** HISTORICAL / RESEARCH ADDENDA
> **Authority:** NON-CANONICAL. Preserved verbatim from the previous mixed document during architecture separation.

## J. R2 Entry-Quality Catalog Validation

For catalog `WS_BT_GRID_ENTRY_QUALITY_R2_2026_06`, validation must fail closed unless all rules below pass:

```text
liquidity.min_dv20_idr >= 0
liquidity.dv20_strong_idr >= liquidity.min_dv20_idr
volume.min_vol_ratio >= 0
volume.strong_vol_ratio >= volume.min_vol_ratio
risk.min_atr14_pct <= risk.atr_ideal_low <= risk.atr_ideal_high <= risk.max_atr14_pct
setup.roc_lo < setup.roc_hi
0 <= grouping.secondary_min_score_q <= grouping.top_min_score_q <= 1
sum(scoring.weights.value.*) = 1
```

Every persisted R2 field is required, numeric in its declared unit, and mapped explicitly into the runtime paramset. Missing fields, duplicate canonical parameter combinations, invalid catalog identity/hash, or a catalog row that differs from an already persisted immutable row must fail closed. No silent fallback is permitted for an R2 field.

## K. C171-R1 Optional Upper-Bound Validation

Legacy paramsets remain valid when the three C171 fields are absent. For catalog
`WS_BT_GRID_REAL_IS_REMEDIATION_C171_R1_2026_07`, all three fields are required
by catalog/factory/binding validation and must be hashed as canonical audit
objects.

```text
liquidity.min_dv20_idr <= liquidity.dv20_strong_idr <= liquidity.max_dv20_idr
volume.min_vol_ratio <= volume.strong_vol_ratio <= volume.max_vol_ratio
0 <= grouping.top_max_score_total <= 1
```

Failure conditions:

- present optional field is not a complete audit object;
- numeric value is encoded as a string;
- max DV20 is lower than the minimum or strong threshold;
- max volume ratio is lower than the minimum, or lower than the strong threshold
  for a C171-R1 catalog row;
- TOP maximum score is outside `0..1`;
- persisted grid value and canonical paramset value differ;
- any of the five immutable catalog rows has a duplicate parameter combination,
  wrong row/catalog hash, or changed payload.

Runtime semantics must also be tested: upper liquidity/volume rejects happen
before scoring, and TOP cap is applied before daily TOP quantile calculation.
No validator or adapter may silently reuse `dv20_strong_idr` or
`strong_vol_ratio` as a maximum for legacy payloads.

