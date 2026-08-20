# Legacy Semantic Extract — LX-MD-0179-EVD-02

- Source ID: `LS-MD-0179`
- Original path: `ops/commands/05_BACKFILL.md`
- Original SHA1: `7D024D1A49999C8FD30899BF32AC78581D6AE221`
- Extract role: `EVIDENCE`
- Source range: `L272-L288`
- Extract body SHA1: `088A24548AEC62311CADEB734557381E0A6346D7`
- Current authority: `NO`
- Current verification effect: `HISTORICAL_ONLY`
- Preservation policy: `EXACT_SOURCE_RANGE_COPY_WITH_METADATA`

<!-- LEGACY_EXTRACT_BODY_START -->
## Amendment 2026-05-27 - Final validation lock for import-only output and readable auto-correction

Final local validation confirms both backfill-related cleanup targets are locked:

- `BackfillLifecyclePublicationReprocess` -> OK (4 tests, 19 assertions).
- `OutOfOrderImportImpact` -> OK (7 tests, 107 assertions).
- `Backfill` -> OK (49 tests, 339 assertions).
- Full MarketData suite -> OK (585 tests, 8713 assertions).

Operational rule after this lock:

- Plain `market-data:backfill` remains import-only, but it must expose execution-layer reprocess fields in command output and summary when run notes include them.
- Lifecycle/full-publish publication reprocess may auto-correct an already-readable affected downstream date only through the correction-current lifecycle.
- The correction-current path must preserve baseline lineage and must not bypass coverage, hash, seal, finalize, pointer, evidence, or replay guards.
- Normal full-publish must not replace an already-readable affected date directly.



<!-- LEGACY_EXTRACT_BODY_END -->
