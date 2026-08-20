# Legacy Semantic Extract — LX-MD-0029-RES-01

- Source ID: `LS-MD-0029`
- Original path: `audit/IMPORT_PROMOTE_SEPARATION_INVENTORY.md`
- Original SHA1: `F6D6E4A0D59F66EB903E4C0FA59BB30AC736CBA6`
- Extract role: `RESEARCH`
- Source range: `L39-L52`
- Extract body SHA1: `E6F187ACC90A04C1265DD9ACB8D92893EAB3C0DE`
- Current authority: `NO`
- Current verification effect: `HISTORICAL_ONLY`
- Preservation policy: `EXACT_SOURCE_RANGE_COPY_WITH_METADATA`

<!-- LEGACY_EXTRACT_BODY_START -->
## Production-Ready Reconciliation Addendum

Current canonical status for this scope is LOCKED in `LUMEN_CONTRACT_TRACKER.md`. Historical pending/local-validation wording above has been reconciled for the import-only run closure and stale active run recovery patch.

Latest operator-local validation for this patch scope:

- `MarketDataPipelineServiceTest.php --filter "complete_ingest"` -> OK (5 tests, 6 assertions).
- `MarketDataPipelineServiceTest.php --filter "recovered_rows_partial"` -> OK (1 test, 2 assertions).
- `MarketDataPipelineIntegrationTest.php --filter "import_only"` -> OK (2 tests, 29 assertions).
- `MarketDataPipelineIntegrationTest.php --filter "stale_running"` -> OK (1 test, 8 assertions).
- `LoggingTraceabilityReasonCodesStaticGuardTest.php` -> OK (7 tests, 134 assertions).
- `ImportPromoteSeparationStaticGuardTest.php` -> OK (6 tests, 147 assertions).
- `AuditDocsSynchronizationStaticGuardTest.php --filter "test_reason_code_registry_and_seed_are_synchronized"` -> OK (1 test, 4 assertions).
- Full `tests/Unit/MarketData` -> OK (649 tests, 9598 assertions).

<!-- LEGACY_EXTRACT_BODY_END -->
