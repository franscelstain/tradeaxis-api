# Legacy Semantic Extract — LX-MD-0252-IMP-02

- Source ID: `LS-MD-0252`
- Original path: `tests/PHPUNIT_TEST_MATRIX.md`
- Original SHA1: `FEC9F51F5D950AD3C0DB1B40F3E0D3C4CD966FFA`
- Extract role: `IMPLEMENTATION`
- Source range: `L110-L139`
- Extract body SHA1: `47642C7F432E0B60E54E67128CEA89AC7E8DF711`
- Current authority: `NO`
- Current verification effect: `HISTORICAL_ONLY`
- Preservation policy: `EXACT_SOURCE_RANGE_COPY_WITH_METADATA`

<!-- LEGACY_EXTRACT_BODY_START -->
## OUT-OF-ORDER IMPORT HASH/SEAL/PUBLICATION REPROCESS

- `BackfillLifecyclePublicationReprocessTest`
  - proves lifecycle publication reprocess consumes actual executor output and calls `promoteDaily()` for affected non-readable downstream dates.
  - proves already-readable affected dates are promoted through correction-current mode with explicit correction id lineage.
  - proves mixed readable/non-readable affected dates resolve to `AUTOMATED_MIXED_IMPACT_REPUBLICATION`.
  - proves evidence export and replay verification are triggered for automatically republished downstream dates when requested.
  - proves the primary requested date is not left `PENDING_PROMOTE` after primary promote already handled it.

- `MarketDataPipelineServiceTest::test_run_daily_auto_corrects_readable_and_promotes_non_readable_downstream_impact_dates_after_primary_finalize`
  - proves the full-publish daily pipeline promotes affected downstream non-readable dates and auto-corrects already-readable affected dates after the primary requested date finalizes.

- `OutOfOrderImportImpactStaticGuardTest`
  - guards that lifecycle publication reprocess calls `promoteDaily()`.
  - guards that `promoteDaily()` still includes `completeHash`, `completeSeal`, and `completeFinalize`.

Latest validation:
- `BackfillLifecyclePublicationReprocess` -> OK (4 tests, 19 assertions).
- `MarketDataPipelineService` -> OK (17 tests, 24 assertions).
- `MarketDataImpactReprocessExecutor` -> OK (4 tests, 22 assertions).
- `OutOfOrderImportImpactStaticGuard` -> OK (7 tests, 107 assertions).
- `Daily` -> OK (63 tests, 1236 assertions).
- `Backfill` -> OK (47 tests, 303 assertions).
- `StaticGuard` -> OK (226 tests, 5517 assertions).

Remaining proof boundary:
- Tests prove automatic publication reprocess for affected non-readable dates and correction-current republication for already-readable affected dates.
- Full-suite proof must be refreshed after every patch touching executor, lifecycle orchestrator, pipeline, evidence, or static guards.



<!-- LEGACY_EXTRACT_BODY_END -->
