# Legacy Role Extract — WS — IMPLEMENTATION

> **Document Type:** IMPLEMENTATION
> **Status:** HISTORICAL_EXTRACT / IMMUTABLE
> **Legacy Extract ID:** `LX-WS-0545-IMP-01`
> **Legacy Source ID:** `LS-WS-0545`
> **Legacy Work Key:** `WS`
> **Original Path:** `docs/watchlist/system/policies/weekly_swing/07_WS_REASON_CODES_AND_HASH.md`
> **Original SHA1:** `BD70FD8283A295D8C51C47A316B3A5DB404C39EB`
> **Source Sections:** L216-L221 Recommendation Reason Codes Sync Note; L222-L241 E. R2/C01 IS-Only Calibration Reason Codes
> **Extract Body SHA1:** `D0095EFCB40C98D1F3C3407D763996552408B83D`
> **Current Authority:** NO

The body below is an exact copy of the identified original sections. No semantic rewriting was applied.

---

## Recommendation Reason Codes Sync Note

Recommendation reason codes aktif untuk foundation ini adalah: `WS_REC_SELECTED`, `WS_REC_NOT_SELECTED`, `WS_REC_BORDERLINE`, `WS_REC_EMPTY_SET`, `WS_REC_RANK_OUTSIDE_DYNAMIC_TARGET`, `WS_REC_CAPITAL_AWARE`, `WS_REC_CAPITAL_INSUFFICIENT`, dan `WS_REC_MIN_LOT_NOT_AFFORDABLE`.

Catatan sinkronisasi: `WS_REC_RANK_OUTSIDE_DYNAMIC_TARGET` adalah nama aktif untuk kandidat yang tidak terpilih karena berada di luar target dinamis recommendation. Jangan memakai alias lama atau istilah support lain untuk output runtime baru.

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
