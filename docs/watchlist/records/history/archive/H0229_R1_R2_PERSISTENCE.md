# Archived Campaign-specific Implementation Section — Catalog-Aware R1/R2 Persistence

> **Status:** ARCHIVED / IMMUTABLE
> **Source:** `docs/watchlist/development/implementation/guides/WS_PERSISTENCE_GUIDANCE.md`
> **Reason:** not part of current generic implementation authority.

---

## Catalog-Aware R1/R2 Persistence

The official identity is now explicit and versioned:

```text
watchlist_bt_param_grid: policy_code + catalog_code + row_code
watchlist_bt_eval: policy_code + catalog_code + catalog_version + param_id + eval_model + paramset_hash + from_date + to_date
```

R1 is backfilled as `WS_BT_GRID_BOOTSTRAP_2026_06 / R1` without changing its historical catalog payload/hash. R2 uses `WS_BT_GRID_ENTRY_QUALITY_R2_2026_06 / R2`. Repository writes are insert-or-idempotent only; the same identity with different content fails closed. Catalog-set verification and cross-catalog immutability checks run inside the seed transaction so a mismatch cannot leave a partial catalog committed.

Rollback must refuse to drop catalog columns while non-R1 catalog/evaluation evidence exists.
