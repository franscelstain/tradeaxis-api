# Archived Campaign-specific Implementation Section — R2 Entry-Quality Regression Focus

> **Status:** ARCHIVED / IMMUTABLE
> **Source:** `docs/watchlist/development/implementation/guides/WS_TEST_IMPLEMENTATION_GUIDANCE.md`
> **Reason:** not part of current generic implementation authority.

---

## R2 Entry-Quality Regression Focus

Tests must additionally prove:

- R1 factory output from an enriched persisted row is byte-for-byte array-equivalent to the historical R1 runtime snapshot;
- R2 catalog identity/count/hash and ordered rows are deterministic;
- every R2 persisted axis is projected and consumed, while grouping item counts and execution axes remain fixed;
- R2 catalog seed is transactional, idempotent, and rejects conflicting/partial persisted sets;
- eval duplicate identity is idempotent and conflicting payload fails closed;
- strict IS replay never requests a calendar or published price after `2025-05-21`;
- post-IS data mutation cannot alter R2 metrics, binding, or canonical artifact hash;
- no best-of-failed, OOS service call, OOS repository call, OOS-table mutation, promotion, or production-ready claim is possible.
