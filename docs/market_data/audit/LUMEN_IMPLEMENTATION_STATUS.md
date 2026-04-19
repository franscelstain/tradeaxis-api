# LUMEN_IMPLEMENTATION_STATUS

## SESSION FINAL — TRACEABILITY / LINKAGE / PUBLISHABILITY / CORRECTION GUARD

Status: DONE

## Scope completed in codebase

1. **source traceability persistence**
   - `eod_runs` now persists first-class source fields:
     - `source`
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
   - pipeline writes those fields from real ingest / hold / failure paths.
   - source context is no longer log-only or notes-only.

2. **run/publication linkage**
   - `eod_runs` now persists:
     - `publication_id`
     - `publication_version`
     - `correction_id`
   - linkage is no longer note-only via `candidate_publication_id=...`.
   - finalize path persists publication linkage directly to the run row.

3. **publishability metadata**
   - `eod_runs.final_reason_code` is now persisted on:
     - finalize
     - hold
     - fail
   - command/evidence summary prefer persisted columns before falling back to notes.
   - publishability state and reason are queryable directly from DB.

4. **correction / reseal guard minimum**
   - correction execution persists `correction_id` on owning run.
   - correction publish/cancel flow keeps direct run-level correction linkage for audit queryability.
   - strict mismatch / lock-conflict paths preserve guard behavior instead of silently overwriting publication state.

## Database/schema changes

Added migration:
- `database/migrations/2026_04_16_000001_add_run_traceability_and_linkage_fields.php`

Updated schema definitions:
- `docs/market_data/db/Database_Schema_MariaDB.sql`
- `tests/Support/UsesMarketDataSqlite.php`
- `docs/market_data/db/DB_FIELDS_AND_METADATA.md`
- `docs/market_data/db/Database_Schema_Contracts_MariaDB.md`

## Code areas changed

- `app/Application/MarketData/Services/MarketDataPipelineService.php`
- `app/Infrastructure/Persistence/MarketData/EodRunRepository.php`
- `app/Application/MarketData/Services/MarketDataBackfillService.php`
- `app/Application/MarketData/Services/MarketDataEvidenceExportService.php`
- `app/Console/Commands/MarketData/AbstractMarketDataCommand.php`
- `app/Console/Commands/MarketData/DailyPipelineCommand.php`
- `app/Models/EodRun.php`
- `tests/Unit/MarketData/MarketDataPipelineIntegrationTest.php`

## Runtime evidence from session

### PHPUnit evidence
- `tests/Unit/MarketData/MarketDataBackfillServiceTest.php` → OK (8 tests, 71 assertions)
- full PHPUnit suite → OK (192 tests, 1941 assertions)

### Artisan runtime evidence
Command:
```bash
php artisan market-data:daily --requested_date=2026-03-24 --source_mode=manual_file --input_file=storage/app/market_data/operator/manual-2026-03-24.csv
```

Observed result:
- `stage=FINALIZE`
- `lifecycle_state=COMPLETED`
- `terminal_status=FAILED`
- `publishability_state=NOT_READABLE`
- `coverage_gate_state=FAIL`
- `coverage_reason_code=COVERAGE_BELOW_THRESHOLD`
- `reason_code=RUN_COVERAGE_LOW`
- `source_name=LOCAL_FILE`
- `source_input_file=storage/app/market_data/operator/manual-2026-03-24.csv`
- `notes=candidate_publication_id=69; source_name=LOCAL_FILE; source_input_file=manual-2026-03-24.csv`

Interpretation:
- pipeline completed successfully through finalize
- source traceability is visible in runtime output
- candidate publication linkage exists
- publishability gate rejected publication correctly because manual file only contained 5/901 symbols
- this is valid runtime proof of contract enforcement, not a pipeline bug

## Final assessment

### source traceability persistence
Status: DONE

### run/publication linkage
Status: DONE

### publishability metadata
Status: DONE

### correction/reseal guard minimum
Status: DONE

## Operational note

Manual CSV with only 5 tickers is valid to prove pipeline behavior, traceability, linkage, and metadata, but not sufficient to satisfy coverage threshold for readable publication.

## Read-side hardening execution

Status: DONE

### Concrete read-path changes

1. **consumer read contract enforcement**
   - `SessionSnapshotService` remains blocked unless a readable current publication exists for the requested trade date.
   - `MarketDataEvidenceExportService` now resolves publication only through `EodPublicationRepository::findReadableCurrentPublicationForRun(...)`.
   - `ReplayVerificationService` now resolves publication only through `EodPublicationRepository::findReadableCurrentPublicationForRun(...)`.

2. **pointer / effective-date enforcement**
   - `EligibilitySnapshotScopeRepository` no longer reads `eod_eligibility` by trade date alone.
   - `EodEvidenceRepository::dominantReasonCodes()` and `exportEligibilityRows()` now require pointer-resolved readable publication joins.
   - Read path is anchored on `eod_current_publication_pointer` + `eod_publications` + `eod_runs`, not implicit trade-date lookup.

3. **anti raw-table bypass**
   - removed effective bypass behavior where publication filter was optional in evidence/snapshot reads.
   - eligibility read helpers now only return rows that belong to the pointer-resolved current sealed readable publication.

4. **anti MAX(date)**
   - no read-side change in this session required DB `MAX(date)` / manual latest selection.
   - read path now explicitly uses pointer + owning run linkage instead of any latest-style inference.

5. **fail-safe read behavior**
   - `MarketDataEvidenceExportService` now throws for non-readable runs instead of silently falling back to another publication via `trade_date_effective`.
   - `ReplayVerificationService` now throws for non-readable runs instead of consuming fallback publication state.
   - empty / stale / foreign publication reads are not auto-accepted.

### Database impact

- **tidak ada perubahan database**
- Existing schema used as-is:
  - `eod_current_publication_pointer`
  - `eod_publications`
  - `eod_runs`
  - `eod_eligibility`

### Test impact

Added/updated tests to prove:
- readable publication for the owning run is mandatory
- non-readable run fails fast
- eligibility reads do not leak rows outside current pointer publication
- raw trade-date-only reads are no longer accepted

## Session conclusion

SESSION STATUS: DONE
WRITE-SIDE PIPELINE: HARDENED
READ-SIDE: ENFORCED
