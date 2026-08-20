# Legacy Semantic Extract — LX-MD-0031-IMP-06

- Source ID: `LS-MD-0031`
- Original path: `audit/LUMEN_IMPLEMENTATION_STATUS.md`
- Original SHA1: `3FDDFFF0B53D431DAA71A8545F87173445A616F7`
- Extract role: `IMPLEMENTATION`
- Source range: `L4662-L4722`
- Extract body SHA1: `3AC69C9F3B49F971A1452C93DFA3A1937FB1EB0D`
- Current authority: `NO`
- Current verification effect: `HISTORICAL_ONLY`
- Preservation policy: `EXACT_SOURCE_RANGE_COPY_WITH_METADATA`

<!-- LEGACY_EXTRACT_BODY_START -->
## 2026-07-05 - IMPORT-ONLY RUN CLOSURE + STALE ACTIVE RUN RECOVERY

[STATUS]
- `LOCKED_LOCAL_MARKETDATA_PHPUNIT_PASS` for removing misleading post-import `RUNNING` state from successful import-only runs.
- `LOCKED_LOCAL_MARKETDATA_PHPUNIT_PASS` for cancelling stale active run ownership before creating/reusing an owning run.

[ROOT_CAUSE]
- `RUNNING` was previously overloaded to mean both “process is actively executing” and “import-only has not been promoted/finalized yet”.
- A successful `request_mode=import_only` run appended `STAGE_COMPLETED` and `IMPORT_ONLY_NOT_PROMOTED`, but returned without closing `eod_runs.lifecycle_state`, leaving `RUNNING` visible in the database after the PHP process had already exited.
- If a process died before `failStage()` executed, stale `PENDING/RUNNING/FINALIZING` rows could also be reused as active owners because ownership lookup only filtered by lifecycle state.

[IMPLEMENTED_CHANGE]
- Added `EodRunRepository::completeImportOnly()`.
- `MarketDataPipelineService::completeIngest()` and `completeIngestWithAcquiredRows()` now close successful import-only ingest as:
  - `lifecycle_state=COMPLETED`
  - `terminal_status=NULL`
  - `publishability_state=NOT_READABLE`
  - `is_current_publication=0`
  - `final_reason_code=IMPORT_ONLY_COMPLETED_NOT_PROMOTED`
  - `finished_at` populated
- `MarketDataPipelineService::completeRecoveredRowsPartial()` now applies the same import-only closure after recovered-row import apply succeeds.
- `EodRunRepository::getOrCreateOwningRun()` now cancels stale active rows for the same requested date/source/request mode before selecting an active owner.
- Added config key `market_data.pipeline.active_run_stale_minutes`, backed by `MARKET_DATA_ACTIVE_RUN_STALE_MINUTES`, default `1440` minutes.
- Stale active rows are closed as:
  - `lifecycle_state=CANCELLED`
  - `terminal_status=NULL`
  - `publishability_state=NOT_READABLE`
  - `final_reason_code=STALE_ACTIVE_RUN_CANCELLED`
  - `finished_at` populated
- Added reason-code registry entries for `IMPORT_ONLY_COMPLETED_NOT_PROMOTED` and `STALE_ACTIVE_RUN_CANCELLED`.

[FINAL_BEHAVIOR]
- `RUNNING` now means an active process is currently executing.
- A successful import-only run is completed but non-readable; it is not promoted, not sealed, not current, and not consumer-readable.
- “Not promoted yet” is no longer represented by `RUNNING`; it is represented by `COMPLETED + terminal_status=NULL + publishability_state=NOT_READABLE`.
- Stale active ownership cannot silently block or reuse old `RUNNING` rows after the configured stale threshold.

[VALIDATION_THIS_SESSION]
- `php -l app/Infrastructure/Persistence/MarketData/EodRunRepository.php` -> PASS.
- `php -l app/Application/MarketData/Services/MarketDataPipelineService.php` -> PASS.
- `php -l tests/Unit/MarketData/MarketDataPipelineServiceTest.php` -> PASS.
- `php -l tests/Unit/MarketData/MarketDataPipelineIntegrationTest.php` -> PASS.
- Operator-local targeted proof in `D:\Laravel\tradeaxis-api`:
  - `vendor\bin\phpunit tests\Unit\MarketData\MarketDataPipelineServiceTest.php --filter "complete_ingest"` -> PASS: OK (5 tests, 6 assertions).
  - `vendor\bin\phpunit tests\Unit\MarketData\MarketDataPipelineServiceTest.php --filter "recovered_rows_partial"` -> PASS: OK (1 test, 2 assertions).
  - `vendor\bin\phpunit tests\Unit\MarketData\MarketDataPipelineIntegrationTest.php --filter "import_only"` -> PASS: OK (2 tests, 29 assertions).
  - `vendor\bin\phpunit tests\Unit\MarketData\MarketDataPipelineIntegrationTest.php --filter "stale_running"` -> PASS: OK (1 test, 8 assertions).
  - `vendor\bin\phpunit tests\Unit\MarketData\LoggingTraceabilityReasonCodesStaticGuardTest.php` -> PASS: OK (7 tests, 134 assertions).
  - `vendor\bin\phpunit tests\Unit\MarketData\ImportPromoteSeparationStaticGuardTest.php` -> PASS: OK (6 tests, 147 assertions).
  - `vendor\bin\phpunit tests\Unit\MarketData\AuditDocsSynchronizationStaticGuardTest.php --filter "test_reason_code_registry_and_seed_are_synchronized"` -> PASS: OK (1 test, 4 assertions).
- Operator-local full MarketData suite proof:
  - `vendor\bin\phpunit tests\Unit\MarketData` -> PASS: OK (649 tests, 9598 assertions).

[MANUAL_VALIDATION_REQUIRED]
- None for this patch scope after the operator-local full MarketData suite PASS above.

[CLAIM_BOUNDARY]
- This patch does not make import-only data readable.
- This patch does not switch current publication pointers.
- This patch does not bypass promote, coverage, hash, seal, finalize, evidence, or replay gates.
- Local PHPUnit passed and this patch scope is locked for import-only closure and stale active run recovery.

<!-- LEGACY_EXTRACT_BODY_END -->
