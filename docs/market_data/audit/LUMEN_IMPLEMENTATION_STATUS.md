- 2026-04-17: Backfill range execution now stops after the first successful `LOCAL_FILE` case when `source_mode=manual_file` and an explicit manual source file is detected, preserving `all_imported=true` while `all_passed=false` for incomplete range coverage.
# LUMEN_IMPLEMENTATION_STATUS

## SESSION UPDATE — TRACEABILITY / LINKAGE / PUBLISHABILITY / CORRECTION GUARD

Status: PARTIAL

### Scope completed in codebase

1. **source traceability persistence**
   - `eod_runs` now persists first-class source fields:
     - `source_name`
     - `source_provider`
     - `source_input_file`
     - `source_timeout_seconds`
     - `source_retry_max`
     - `source_attempt_count`
     - `source_success_after_retry`
     - `source_retry_exhausted`
     - `source_final_http_status`
     - `source_final_reason_code`
   - pipeline now writes those fields during ingest success, recoverable source hold, and stage failure paths.
   - notes/event payload remain as supporting evidence only.

2. **run/publication linkage**
   - `eod_runs` now persists:
     - `publication_id`
     - `publication_version`
     - `correction_id`
   - linkage is no longer note-only via `candidate_publication_id=...`.
   - finalize path now persists publication linkage directly to the run row.

3. **publishability metadata**
   - `eod_runs.final_reason_code` added and now persisted on:
     - finalize
     - hold
     - fail
   - command/evidence summary now prefer persisted columns before falling back to notes.

4. **correction / reseal guard minimum**
   - correction execution now persists `correction_id` on owning run.
   - correction publish/cancel flow keeps direct run-level correction linkage for audit queryability.

### Database/schema changes

Added migration:
- `database/migrations/2026_04_16_000001_add_run_traceability_and_linkage_fields.php`

Updated schema definitions:
- `docs/market_data/db/Database_Schema_MariaDB.sql`
- `tests/Support/UsesMarketDataSqlite.php`
- `docs/market_data/db/DB_FIELDS_AND_METADATA.md`
- `docs/market_data/db/Database_Schema_Contracts_MariaDB.md`

### Code areas changed

- `app/Application/MarketData/Services/MarketDataPipelineService.php`
- `app/Infrastructure/Persistence/MarketData/EodRunRepository.php`
- `app/Application/MarketData/Services/MarketDataBackfillService.php`
- `app/Application/MarketData/Services/MarketDataEvidenceExportService.php`
- `app/Console/Commands/MarketData/AbstractMarketDataCommand.php`
- `app/Models/EodRun.php`
- `tests/Unit/MarketData/MarketDataPipelineIntegrationTest.php`

### What is already proven from static code trace

- source trace fields are written from real pipeline paths, not pseudo placeholders
- run/publication linkage now has real DB columns
- publishability reason is no longer event/notes-only
- correction-aware run linkage now survives beyond log payload
- schema/migration/test sqlite definitions are synchronized

### What is not yet runtime-proven in this session

Because uploaded ZIP does not include `vendor/`, this session cannot claim PHPUnit/artisan runtime execution inside container.

Still pending user-local execution evidence for:
- migration success
- PHPUnit success
- artisan command success
- DB query proof on actual runtime DB

### Next required manual proof

1. run migration
2. run targeted PHPUnit
3. execute one normal manual-file run
4. execute one correction run
5. query `eod_runs`, `eod_publications`, `eod_dataset_corrections`
6. confirm persisted source/linkage/reason columns are populated as expected


## 2026-04-16 test-fix follow-up
- Fixed source_input_file display normalization so relative project paths export as filename-only while absolute operator paths remain intact.
- Fixed numeric source telemetry casting in evidence export (`attempt_count`, `retry_max`, `timeout_seconds`, `final_http_status`).
- Fixed correction post-finalize mismatch path so `RUN_FINALIZED.reason_code` and `eod_runs.final_reason_code` stay aligned on `RUN_LOCK_CONFLICT`.


## 2026-04-16 regression fix follow-up
- Fixed `market-data:daily` command to call `runDaily()` again so ops surface/tests use the full daily pipeline contract.
- Relaxed source-mode immutability guard so legacy/mock runs without persisted `source` do not fail stage startup.
- Fixed backfill summary status semantics: deterministic held/failed runs now remain `FAIL`, while `all_passed` only stays true when every trading date in scope was processed successfully.
- Hardened evidence export against missing optional persisted fields like `final_reason_code` on legacy/stdClass test records.

- 2026-04-17: Fixed final backfill regressions in `MarketDataBackfillService`: deterministic failures now map `source_final_reason_code` -> `final_reason_code`, and summary flags now keep `all_imported=true` when execution stops early after a successfully imported case while `all_passed=false` reflects incomplete coverage.
