# Archived Campaign-specific Implementation Section — R2 Entry-Quality IS-Only Delivery

> **Status:** ARCHIVED / IMMUTABLE
> **Source:** `docs/watchlist/development/implementation/guides/WS_DELIVERY_CHECKLIST.md`
> **Reason:** not part of current generic implementation authority.

---

## R2 Entry-Quality IS-Only Delivery

- [ ] migrate catalog-aware grid/eval identity without deleting R1 evidence;
- [ ] prove R1 count/hash and runtime paramset snapshot remain unchanged;
- [ ] seed `WS_BT_GRID_ENTRY_QUALITY_R2_2026_06` twice and prove idempotency;
- [ ] verify R1 and R2 coexist with no implicit latest/active fallback;
- [ ] run the R2 PHPUnit, Watchlist baseline, and required MarketData read-model suites;
- [ ] execute the exact IS window twice with separate outputs;
- [ ] compare catalog/date/param/evaluation/binding/artifact hashes;
- [ ] prove max requested market-data date is `<= 2025-05-21` and OOS table before/after hashes match;
- [ ] freeze best IS only when every unchanged gate passes;
- [ ] stop after IS; keep OOS, promotion, and production readiness false.
