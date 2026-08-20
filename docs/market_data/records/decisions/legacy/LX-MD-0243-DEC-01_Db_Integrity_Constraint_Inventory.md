# Legacy Semantic Extract — LX-MD-0243-DEC-01

- Source ID: `LS-MD-0243`
- Original path: `tests/Db_Integrity_Constraint_Inventory.md`
- Original SHA1: `F8EC0B923A05E4141D9FEF6A1E71E132AA698D5B`
- Extract role: `DECISION`
- Source range: `L74-L82`
- Extract body SHA1: `6443815C656DF2603508BDF08AC38EDCC372F79E`
- Current authority: `NO`
- Current verification effect: `HISTORICAL_ONLY`
- Preservation policy: `EXACT_SOURCE_RANGE_COPY_WITH_METADATA`

<!-- LEGACY_EXTRACT_BODY_START -->
## DB Integrity FK / Implicit Integrity Decision hardening — 2026-05-17

Related inventory: `docs/market_data/audit/DB_INTEGRITY_FK_IMPLICIT_INTEGRITY_DECISION_INVENTORY.md`.

Decision: `HYBRID_REQUIRED`.

This hardening does not reopen the whole schema sync contract and does not claim the entire schema sync failed. It classifies the remaining live artifact relation risk: current live artifact tables keep mandatory `run_id` and `publication_id` context plus publication-scoped indexes, while phase-dependent relation validity remains protected by repository/service/evidence/replay/static guards. Stable proof relations keep explicit FK enforcement: `eod_current_publication_pointer.publication_id` and immutable history artifact `publication_id` references to `eod_publications(publication_id)`.

Historical transition status was `READY_FOR_LOCAL_RUNTIME_VALIDATION`; container PHPUnit was `BLOCKED_CONTAINER_RUNTIME_ENV` because `dom`, `mbstring`, `xml`, and `xmlwriter` were unavailable. Later operator-local `DbIntegrityFkImplicitIntegrityDecisionStaticGuardTest.php`, `DbIntegrity`, `StaticGuard`, and full `tests/Unit/MarketData` proof promoted this scope to DONE/LOCKED as recorded in Lumen.

<!-- LEGACY_EXTRACT_BODY_END -->
