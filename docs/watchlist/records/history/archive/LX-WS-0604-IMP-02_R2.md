# Legacy Role Extract — R2 — IMPLEMENTATION

> **Document Type:** IMPLEMENTATION
> **Status:** HISTORICAL_EXTRACT / IMMUTABLE
> **Legacy Extract ID:** `LX-WS-0604-IMP-02`
> **Legacy Source ID:** `LS-WS-0604`
> **Legacy Work Key:** `R2`
> **Original Path:** `docs/watchlist/system/policies/weekly_swing/_refs/WS_R2_ENTRY_QUALITY_CALIBRATION_NOTE.md`
> **Original SHA1:** `74783FDEA5FEA8C3F39255D52386E24E31018678`
> **Source Sections:** L86-L94 Persistence and Identity Closure
> **Extract Body SHA1:** `1CE4BC684686680605ED90C9EF7825AB3465C6D7`
> **Current Authority:** NO

The body below is an exact copy of the identified original sections. No semantic rewriting was applied.

---

## Persistence and Identity Closure

- `watchlist_bt_param_grid` now carries explicit catalog and row identity plus only runtime-consumed R2 columns.
- R1 and R2 are queried by explicit `catalog_code`; no latest/active fallback exists.
- Seed behavior is INSERT-or-idempotent only; immutable conflicts fail closed.
- `watchlist_bt_eval` identity includes policy, catalog code/version, param id, eval model, paramset hash, and exact window. The R2 paramset hash excludes generated `param_id` and is stable across databases for the same immutable catalog row.
- Exact duplicate eval persistence is idempotent; conflicting payloads fail with `WS_BT_EVAL_IDENTITY_CONFLICT`.
- Migration backfills existing R1 rows deterministically and preserves R1 eval rows under the explicit R1 identity.
