# Legacy Semantic Extract — LX-MD-0030-CTX-06

- Source ID: `LS-MD-0030`
- Original path: `audit/LUMEN_CONTRACT_TRACKER.md`
- Original SHA1: `88A4CD4C9D12A578A2837DCB275B315C91D1492A`
- Extract role: `CONTEXT`
- Source range: `L4368-L4455`
- Extract body SHA1: `98939944C542DB85AF08BA18EBB1AD7FBA04F115`
- Current authority: `NO`
- Current verification effect: `HISTORICAL_ONLY`
- Preservation policy: `EXACT_SOURCE_RANGE_COPY_WITH_METADATA`

<!-- LEGACY_EXTRACT_BODY_START -->
## MARKET_DATA_DATABASE_DICTIONARY_REQUIRED_CONTRACT

Status: `DONE_DOCS_ONLY`

Last updated: 2026-06-22

Related implementation: `Database Dictionary and Field Usage Governance`

Contract:

- Database-connected Market Data work must read `docs/market_data/db/MARKET_DATA_DICTIONARY.md` and `docs/db/DATABASE_DICTIONARY_USAGE_RULE.md` before coding.
- Each touched table must have table purpose, date key, identifier key, field role, and as-of safety understood before implementation.
- Missing dictionary coverage must be resolved by updating the dictionary or marking the task blocked.
- Column names must not be inferred from memory.
- Current critical mappings are locked in the dictionary: benchmark `roc_20`, benchmark `ma20_slope_pct`, and `market_calendar.cal_date`.

Validation:

- Docs-only contract and dictionary created.

---

## IMPORT_ONLY_COMPLETED_NON_READABLE_RUN_CLOSURE_CONTRACT

Status: `LOCKED_LOCAL_MARKETDATA_PHPUNIT_PASS`

Last updated: 2026-07-05

Related implementation: `2026-07-05 - IMPORT-ONLY RUN CLOSURE + STALE ACTIVE RUN RECOVERY`

[CONTRACT]
- `eod_runs.lifecycle_state=RUNNING` must mean a process is actively executing.
- Successful `request_mode=import_only` ingest must not remain `RUNNING` after command/service return.
- Successful import-only ingest must close as `COMPLETED`, keep `terminal_status=NULL`, keep `publishability_state=NOT_READABLE`, keep `is_current_publication=0`, and write `final_reason_code=IMPORT_ONLY_COMPLETED_NOT_PROMOTED`.
- Import-only closure must not append `RUN_FINALIZED` and must not imply consumer-readable publishability.

[ENFORCEMENT]
- `MarketDataPipelineService::completeIngest()` closes import-only success through `EodRunRepository::completeImportOnly()`.
- `MarketDataPipelineService::completeIngestWithAcquiredRows()` closes import-only success through the same repository method.
- `MarketDataPipelineService::completeRecoveredRowsPartial()` closes recovered-row import-only success through the same repository method.
- `Import_Promote_Separation_Contract.md` and schema semantics now state that `RUNNING` is not a “not promoted yet” marker.

[VALIDATION_STATUS]
- Static/syntax validation passed in sandbox.
- Operator-local targeted validation PASS:
  - `MarketDataPipelineServiceTest.php --filter "complete_ingest"` -> OK (5 tests, 6 assertions).
  - `MarketDataPipelineServiceTest.php --filter "recovered_rows_partial"` -> OK (1 test, 2 assertions).
  - `MarketDataPipelineIntegrationTest.php --filter "import_only"` -> OK (2 tests, 29 assertions).
  - `ImportPromoteSeparationStaticGuardTest.php` -> OK (6 tests, 147 assertions).
  - `LoggingTraceabilityReasonCodesStaticGuardTest.php` -> OK (7 tests, 134 assertions).
  - `AuditDocsSynchronizationStaticGuardTest.php --filter "test_reason_code_registry_and_seed_are_synchronized"` -> OK (1 test, 4 assertions).
- Operator-local full MarketData validation PASS: `vendor\bin\phpunit tests\Unit\MarketData` -> OK (649 tests, 9598 assertions).

[GAP]
- None for this contract scope.

---

## STALE_ACTIVE_RUN_RECOVERY_CONTRACT

Status: `LOCKED_LOCAL_MARKETDATA_PHPUNIT_PASS`

Last updated: 2026-07-05

Related implementation: `2026-07-05 - IMPORT-ONLY RUN CLOSURE + STALE ACTIVE RUN RECOVERY`

[CONTRACT]
- Active owner lookup must not silently reuse an old `PENDING/RUNNING/FINALIZING` run that no longer represents an active process.
- Stale active rows for the same requested date/source/request mode must be closed safely before a new owning run is created or reused.
- Stale recovery must never create readable publication state, switch current pointers, or mark terminal success.

[ENFORCEMENT]
- `EodRunRepository::getOrCreateOwningRun()` calls stale-active cleanup before selecting active owner rows.
- Stale threshold is configured by `MARKET_DATA_ACTIVE_RUN_STALE_MINUTES`, default `1440` minutes.
- Stale rows close as `CANCELLED`, `terminal_status=NULL`, `publishability_state=NOT_READABLE`, `final_reason_code=STALE_ACTIVE_RUN_CANCELLED`, with reason-coded event `STALE_ACTIVE_RUN_CANCELLED`.

[VALIDATION_STATUS]
- Added DB-backed integration coverage for stale `RUNNING` cancellation before new owner creation.
- Static/syntax validation passed in sandbox.
- Operator-local targeted validation PASS:
  - `MarketDataPipelineIntegrationTest.php --filter "stale_running"` -> OK (1 test, 8 assertions).
  - `ImportPromoteSeparationStaticGuardTest.php` -> OK (6 tests, 147 assertions).
  - `LoggingTraceabilityReasonCodesStaticGuardTest.php` -> OK (7 tests, 134 assertions).
  - `AuditDocsSynchronizationStaticGuardTest.php --filter "test_reason_code_registry_and_seed_are_synchronized"` -> OK (1 test, 4 assertions).
- Operator-local full MarketData validation PASS: `vendor\bin\phpunit tests\Unit\MarketData` -> OK (649 tests, 9598 assertions).

[GAP]
- None for this contract scope.

<!-- LEGACY_EXTRACT_BODY_END -->
