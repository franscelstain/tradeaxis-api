# D-WS-20260818-07 — Adopt Work Correlation, Registries, Closure Manifest, and Relationship Controls

- **Status:** ISSUED
- **Finding:** `../../development/findings/WS_WORK_CORRELATION_CLOSURE_VISIBILITY_GAP_2026-08-18.md`
- **Decision:** ADOPT

## Decision

1. Attempt ID becomes canonical Work/Correlation ID for current/future `WS-Bxx` work.
2. Current/future work records are indexed in `records/WORK_RECORD_REGISTRY.csv`.
3. Verified blockers/dependencies are indexed in `development/implementation/WS_DEPENDENCY_REGISTRY.csv`.
4. Material changes require pre/post Change Impact Declaration.
5. Terminal stages require immutable Stage Closure Manifest.
6. Relationship integrity is executable and closure-relevant.
7. `CURRENT_STATE.md` is generated from canonical indexes; it is not a new authority.
8. Historical records are not mass-renamed/backfilled; current attempts may reference them explicitly.
