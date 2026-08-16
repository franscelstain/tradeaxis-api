# Weekly Swing Campaign Reason-Code Extensions

> **Doc Role:** HISTORICAL COMPATIBILITY RECORD
> **Authority:** NON-CANONICAL for current strategy. Preserved from campaign-specific additions.

### C171 decision-time upper-bound reason codes

- `WS_LIQ_HIGH` means `dv20_idr` exceeds the explicit immutable `liquidity.max_dv20_idr` bound.
- `WS_VOLR_HIGH` means `vol_ratio` exceeds the explicit immutable `volume.max_vol_ratio` bound.
- Both codes are PLAN universe guard diagnostics evaluated from data available on `asof_eod_date`.
- Neither code may be inferred from return outcomes, OOS evidence, ticker identity, sector identity, or a later trade path.
- Legacy paramsets that omit the optional upper-bound fields do not emit these codes.
- `WS_TICK_RISK_HIGH` means the signal-date close/ATR stop, after conservative IDX price-fraction normalization, expands risk above `risk.max_signal_tick_risk_expansion_pct`.
- The tick-risk guard uses only signal-date `close`, `atr14_pct`, and immutable `stop_atr_mult`; next-open entry, realized return, and future path are forbidden.

## E. R2/C01 IS-Only Calibration Reason Codes

The following BT-scope reason codes are active for immutable catalog identity, IS-only execution, and persistence conflict handling:

- `WS_BT_R2_CATALOG_MISSING` — explicit R2 catalog identity is absent or the requested catalog has no rows.
- `WS_BT_R2_CATALOG_INVALID` — catalog shape, unit, invariant, range, or deterministic identity is invalid.
- `WS_BT_R2_CATALOG_IDENTITY_CONFLICT` — an existing immutable catalog row has the same identity but a different payload.
- `WS_BT_R2_CATALOG_PERSISTED_SET_MISMATCH` — persisted catalog count/set/hash differs from the code-owned immutable catalog.
- `WS_BT_R2_NO_VALID_IS_CANDIDATE` — no R2 row passes every canonical IS gate; no best-of-failed binding may be created.
- `WS_BT_C01_NO_VALID_IS_CANDIDATE` — no C01 row passes every canonical IS gate; no best-of-failed binding may be created.
- `WS_BT_R2_IS_BOUNDARY_VIOLATION` — an IS-only request attempts to cross `2025-05-21`.
- `WS_BT_R2_IS_WINDOW_MISMATCH` — an immutable IS catalog is requested with a window other than `2023-01-02` through `2025-05-21`.
- `WS_BT_R2_CALIBRATION_FAILED` — an unclassified IS orchestration failure blocks evidence creation.
- `WS_BT_R1_MUTATION_REJECTED` — an IS catalog operation detects or attempts a change to immutable R1 state.
- `WS_BT_R2_OOS_PERSISTENCE_MUTATION` — `watchlist_bt_oos_eval_ws` changes during IS-only execution.
- `WS_BT_EVAL_IDENTITY_CONFLICT` — the same canonical eval identity is presented with a different payload.

The `WS_BT_R2_*` catalog/persistence names remain compatibility reason codes for the immutable R2 implementation path and shared explicit-catalog guardrail. They do not authorize R-series catalog naming beyond the historical R1/R2 identities.

These codes do not imply OOS execution, promotion, ACTIVE status, or production readiness.
