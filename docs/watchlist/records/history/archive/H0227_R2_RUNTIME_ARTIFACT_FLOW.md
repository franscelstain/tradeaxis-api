# Archived Campaign-specific Implementation Section — R2 IS-Only Runtime Artifact Flow

> **Status:** ARCHIVED / IMMUTABLE
> **Source:** `docs/watchlist/development/implementation/guides/WS_RUNTIME_ARTIFACT_FLOW.md`
> **Reason:** not part of current generic implementation authority.

---

## R2 IS-Only Runtime Artifact Flow

R2 entry-quality calibration uses a separate explicit flow:

```text
explicit R2 catalog
→ official IS calendar 2023-01-02..2025-05-21
→ catalog row to canonical paramset projection
→ strategy replay with final HOLD=5 dates censored from entry generation
→ exact published-price date/ticker reads bounded by 2025-05-21
→ canonical metrics and unchanged gates
→ catalog-aware watchlist_bt_eval persistence
→ deterministic IS-only JSON evidence
```

The R2 path does not construct an OOS split, call an OOS service/repository, or write `watchlist_bt_oos_eval_ws`. The R1 factory path remains compatibility-locked: catalog-enrichment columns are excluded from its runtime `bt_grid` snapshot so historical R1 paramset and binding hashes do not drift.
