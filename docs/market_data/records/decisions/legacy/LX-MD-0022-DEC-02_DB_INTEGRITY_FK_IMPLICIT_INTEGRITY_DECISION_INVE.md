# Legacy Semantic Extract — LX-MD-0022-DEC-02

- Source ID: `LS-MD-0022`
- Original path: `audit/DB_INTEGRITY_FK_IMPLICIT_INTEGRITY_DECISION_INVENTORY.md`
- Original SHA1: `BA5CB0819D76C0ADAEA2600174DA40EF3CFF16A3`
- Extract role: `DECISION`
- Source range: `L27-L39`
- Extract body SHA1: `824F0717B06CC95E3FE15C282C84ECA127067072`
- Current authority: `NO`
- Current verification effect: `HISTORICAL_ONLY`
- Preservation policy: `EXACT_SOURCE_RANGE_COPY_WITH_METADATA`

<!-- LEGACY_EXTRACT_BODY_START -->
## Final policy decision

Final policy: `HYBRID_REQUIRED`.

Rules:

1. Stable DB-owned relation may use FK when it does not create circular finalize/promotion/correction risk.
2. Current live artifact tables must keep mandatory `run_id` and `publication_id` columns plus publication-scoped indexes, but relation validity is enforced by repository/service/static guard, not FK, because the write lifecycle is phase-dependent.
3. Historical artifact tables keep explicit FK to `eod_publications(publication_id)` because they are immutable publication-bound proof surfaces.
4. Current pointer keeps explicit FK to `eod_publications(publication_id)` and uses repository mirror guard for `run_id`, `publication_version`, coverage PASS, `SUCCESS + READABLE`, and SEALED state.
5. Correction, replay, evidence, and publication/run mirror relations stay implicit because nullable/phase-dependent linkage is valid before finalize/publish.
6. No relation may remain `TBD` without blocker. Current source-of-truth ZIP has no `TBD` decision in this inventory.


<!-- LEGACY_EXTRACT_BODY_END -->
