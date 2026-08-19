# Archived Campaign-specific Implementation Section — C171 Optional Upper-Bound Context

> **Status:** ARCHIVED / IMMUTABLE
> **Source:** `docs/watchlist/development/implementation/db/PLAN_UNIVERSE_SNAPSHOT_SCHEMA.md`
> **Reason:** not part of current generic implementation authority.

---

## C171 Optional Upper-Bound Context

The snapshot metric fields remain `dv20_idr` and `vol_ratio`; no outcome-derived field is added. For an immutable C171 remediation paramset, the proof context must also preserve the active paramset identity/hash so the optional `max_dv20_idr` and `max_vol_ratio` bounds can be reproduced. A legacy paramset without those bounds must not emit `WS_LIQ_HIGH` or `WS_VOLR_HIGH`.
