# Legacy Semantic Extract — LX-MD-0208-GOV-01

- Source ID: `LS-MD-0208`
- Original path: `ops/RUN_PUBLICATION_POINTER_LINKAGE_INVENTORY.md`
- Original SHA1: `D27A829A793A182A7BEF9796B85546AC7329B73A`
- Extract role: `GOVERNANCE`
- Source range: `L35-L47`
- Extract body SHA1: `6A6BF09FF0EE390F7791A99E89CCAD3FAB61A188`
- Current authority: `NO`
- Current verification effect: `HISTORICAL_ONLY`
- Preservation policy: `EXACT_SOURCE_RANGE_COPY_WITH_METADATA`

<!-- LEGACY_EXTRACT_BODY_START -->
## Final enforced rules

1. Every readable/current publication must be traceable to a valid originating run.
2. Run-publication mirror must agree on `run_id`, `publication_id`, `publication_version`, and trade date when those fields are present.
3. Current pointer must resolve to a publication whose run is `SUCCESS + READABLE + coverage PASS` and whose publication is `SEALED`.
4. Pointer switch must validate candidate state before mutation and verify resolver output after mutation.
5. Correction baseline must be pointer-resolved from an existing current readable publication.
6. Correction replacement must be represented by a valid replacement publication before pointer switch.
7. Failed or unchanged correction must preserve baseline pointer and record baseline lineage.
8. Evidence and replay must carry enough lineage context to prove run/publication/pointer/correction state without ad-hoc database queries.
9. Command output must expose run-publication lineage summary for operator traceability.
10. No current/read-side/fallback resolver may use raw/staging/latest/`MAX(trade_date)` shortcuts.


<!-- LEGACY_EXTRACT_BODY_END -->
