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


## Consumer-surface-sweep execution

Status: PARTIAL

### Consumer inventory completed in this session

Consumer read surfaces traced to real query/table level:
- `app/Console/Commands/MarketData/ExportEvidenceCommand.php` → replay evidence export selector
- `app/Application/MarketData/Services/MarketDataEvidenceExportService.php` → run / replay evidence read service
- `app/Infrastructure/Persistence/MarketData/EodEvidenceRepository.php` → replay metric lookup + invalid bars export query
- `app/Console/Commands/MarketData/CaptureSessionSnapshotCommand.php` → session snapshot consumer entry
- `app/Application/MarketData/Services/SessionSnapshotService.php` → readable publication + scope resolution
- `app/Infrastructure/Persistence/MarketData/EligibilitySnapshotScopeRepository.php` → pointer-anchored eligibility scope read
- `app/Console/Commands/MarketData/ReplayBackfillCommand.php` → replay consumer batch entry
- `app/Application/MarketData/Services/ReplayBackfillService.php` → current readable publication per trade date
- `app/Console/Commands/MarketData/VerifyReplayCommand.php` → replay verify entry
- `app/Application/MarketData/Services/ReplayVerificationService.php` → owning-run readable publication enforcement

### Concrete violations fixed in this session

1. **anti MAX(date) / latest manual fix on replay evidence export**
   - Before this session, replay evidence export still allowed `--replay_id` without `--trade_date`.
   - `MarketDataEvidenceExportService::exportReplayEvidence()` delegated to `EodEvidenceRepository::findReplayMetric($replayId, null)`.
   - `EodEvidenceRepository::findReplayMetric()` resolved the row with `orderByDesc('trade_date')->first()` when `trade_date` was omitted.
   - This was a real consumer violation because replay evidence could read the latest row instead of an explicit trade-date contract.

   Fixed by:
   - `app/Console/Commands/MarketData/ExportEvidenceCommand.php`
   - `app/Application/MarketData/Services/MarketDataEvidenceExportService.php`
   - replay evidence export now **requires explicit `--trade_date`** and fails fast otherwise.

2. **anti raw-table leakage fix on invalid bars evidence export**
   - Before this session, `EodEvidenceRepository::exportInvalidBarsRows()` read `eod_invalid_bars` by `trade_date` only.
   - Schema proves `eod_invalid_bars` also stores `run_id`, so same-date rows from another run could leak into run evidence export.
   - This was a real consumer violation because the evidence bundle for one run could include foreign raw invalid-bar rows from another run.

   Fixed by:
   - `app/Infrastructure/Persistence/MarketData/EodEvidenceRepository.php`
   - `app/Application/MarketData/Services/MarketDataEvidenceExportService.php`
   - invalid bars export is now scoped by **`trade_date` + `run_id`**.

### Files changed in this session
- `app/Console/Commands/MarketData/ExportEvidenceCommand.php`
- `app/Application/MarketData/Services/MarketDataEvidenceExportService.php`
- `app/Infrastructure/Persistence/MarketData/EodEvidenceRepository.php`
- `tests/Unit/MarketData/MarketDataEvidenceExportServiceTest.php`
- `tests/Unit/MarketData/OpsCommandSurfaceTest.php`

### Test proof status
- `php -l` syntax validation on all changed PHP files → PASS
- PHPUnit in this container → **not runnable** because uploaded ZIP does not contain `vendor/` / `vendor/bin/phpunit`

### Required local validation
Run locally in the real project root:
```bash
vendor/bin/phpunit tests/Unit/MarketData/MarketDataEvidenceExportServiceTest.php
vendor/bin/phpunit tests/Unit/MarketData/OpsCommandSurfaceTest.php --filter evidence_export
```
Expected result:
- replay evidence export without `--trade_date` must fail fast
- replay evidence export with `--trade_date` must still pass
- run evidence export must request invalid bars rows with the owning `run_id`

### Session assessment
- consumer inventory sweep → PARTIAL
- read-path trace per consumer → PARTIAL but grounded for evidence / replay / snapshot surfaces touched here
- violation hardening batch → DONE for the violations found above
- regression proof → PARTIAL pending local PHPUnit with vendor present


## Publication-current-pointer readiness execution

Status: PARTIAL

### Root cause readiness issue proved in code

- success-path finalize previously promoted candidate publication and current pointer before run-final state was revalidated through the same strict pointer-resolved consumer contract;
- strict post-finalize pointer validation was only applied on changed correction publish path, not on ordinary readable success path;
- publication/pointer switch and `eod_runs.is_current_publication` mirror sync were split across repository/service steps, so write-path could leave partial current-state evidence unless every later step completed perfectly;
- when post-switch mismatch happened without a prior readable baseline, code had no explicit cleanup path to clear invalid current pointer/current flag state.

### Files changed

- `app/Application/MarketData/Services/MarketDataPipelineService.php`
- `app/Infrastructure/Persistence/MarketData/EodPublicationRepository.php`
- `tests/Unit/MarketData/MarketDataPipelineIntegrationTest.php`
- `tests/Unit/MarketData/PublicationRepositoryIntegrationTest.php`

### Patch performed

1. **success-path strict validation expanded**
   - finalize now re-checks `findPointerResolvedPublicationForTradeDate(...)` for every `SUCCESS + READABLE` outcome that claims a current publication, not only correction publish flow.

2. **explicit invalid-current cleanup added**
   - if post-finalize resolver mismatch is detected with no prior readable current baseline, repository now clears `eod_publications.is_current`, removes `eod_current_publication_pointer`, and zeroes `eod_runs.is_current_publication` for that trade date.

3. **promotion/restore transaction sync hardened**
   - `promoteCandidateToCurrent(...)` now also syncs `eod_runs.publication_id`, `publication_version`, and `is_current_publication` inside the same DB transaction as publication/pointer switch.
   - `restorePriorCurrentPublication(...)` now restores run mirror/linkage in the same DB transaction.

4. **sealed candidate enforcement tightened**
   - promotion now rejects candidate publication that claims `SEALED` but has missing `sealed_at`.

### Test proof added in codebase

- success-path integration proof now asserts repository consumer resolver can read the newly promoted current publication for a normal readable run;
- new integration proof covers post-switch mismatch on ordinary success path and asserts finalize downgrades to `HELD` / `NOT_READABLE` plus clears invalid current pointer when there is no safe baseline;
- repository integration proof now asserts promotion transaction also syncs `eod_runs` current mirror/linkage.

### Validation status

- local syntax validation completed:
  - `php -l app/Application/MarketData/Services/MarketDataPipelineService.php`
  - `php -l app/Infrastructure/Persistence/MarketData/EodPublicationRepository.php`
  - `php -l tests/Unit/MarketData/MarketDataPipelineIntegrationTest.php`
  - `php -l tests/Unit/MarketData/PublicationRepositoryIntegrationTest.php`
- targeted PHPUnit could not be executed in this ZIP because `vendor/bin/phpunit` is absent from the uploaded source bundle.

### Final status

- write-path hardening for current publication/pointer readiness: PARTIAL
- code patch: DONE
- runtime/phpunit proof from this session: BLOCKED by missing vendor

### Remaining work

- run targeted PHPUnit in local full environment;
- run artisan/manual DB proof against a real finalize path to confirm at least one readable current publication resolves through repository consumer query.


### Follow-up regression fix

- Fixed success-path strict pointer validation so it validates against the **resolved current publication contract**, not always the newly created candidate publication.
- This preserves the documented correction behavior for **unchanged artifacts**: correction request is cancelled, prior current publication remains current, and run stays `SUCCESS` / `READABLE`.


## Session Update — 2026-04-19 Coverage Gate vs Manual File Publishability

- `market-data:daily` now routes to `MarketDataPipelineService::importDaily(...)` instead of `runDaily(...)`.
- `market-data:promote` remains the only CLI write-surface that executes coverage evaluation and finalize/publishability promotion.
- Coverage gate contract remains enforced on promote/finalize path; no threshold relaxation or coverage bypass was introduced.
- Ops summaries now emit explicit `request_mode` values (`import_only` for daily, `promote` for promote) so operator intent is visible in console output and summary artifacts.
- Result: manual file partial ingestion is no longer forced through coverage gate merely by using `market-data:daily`.


## Promote intent classification execution

Status: DONE

### Design selected
- `market-data:promote --mode=full_publish|correction`
- default behavior:
  - no `correction_id` => `full_publish`
  - with `correction_id` and no explicit mode => `correction`

### Contract result
- `full_publish` keeps existing full-universe coverage blocking semantics.
- `correction` persists:
  - `eod_runs.promote_mode`
  - `eod_runs.publish_target`
  - `eod_publications.promote_mode`
  - `eod_publications.publish_target`
- `correction` path no longer auto-assumes `current_replace`.
- non-current correction promote finishes as sealed non-current publication and preserves existing readable current publication.

### Files changed
- `app/Console/Commands/MarketData/PromoteMarketDataCommand.php`
- `app/Console/Commands/MarketData/AbstractMarketDataCommand.php`
- `app/Application/MarketData/Services/MarketDataPipelineService.php`
- `app/Application/MarketData/Services/FinalizeDecisionService.php`
- `app/Application/MarketData/Services/PublicationFinalizeOutcomeService.php`
- `app/Infrastructure/Persistence/MarketData/EodRunRepository.php`
- `app/Infrastructure/Persistence/MarketData/EodPublicationRepository.php`
- `database/migrations/2026_04_19_000001_add_promote_intent_fields.php`
- `docs/market_data/db/Database_Schema_MariaDB.sql`
- `tests/Support/UsesMarketDataSqlite.php`
- `tests/Unit/MarketData/MarketDataPipelineServiceTest.php`
- `tests/Unit/MarketData/OpsCommandSurfaceTest.php`

### Proof recorded
- full publish path still blocks on coverage failure.
- correction mode now requires `correction_id` so non-current publication stays isolated from current tables/history contract.
- promote command now surfaces `promote_mode` and `publish_target`.

## 2026-04-19 — Correction lifecycle guard & fast-fail ops surface hardening

### Summary
- Promote intent classification from the prior session remains in place: `full_publish` is coverage-blocked and `correction` is isolated as `non_current_correction`.
- This session hardened correction lifecycle validation so `PUBLISHED` corrections are no longer reported as generic approval failures.
- Fast-fail promote output now keeps operator-facing context even when correction validation fails before a new run is created.

### Contract / behavior
- correction status `APPROVED`, `EXECUTING`, or `RESEALED` remains executable for correction promote flow.
- correction status `PUBLISHED` is explicitly rejected with: `Correction request is already PUBLISHED and cannot be executed again.`
- correction status `REQUESTED` is explicitly rejected with: `Correction request is still REQUESTED and must be APPROVED before execution.`
- other non-executable statuses are rejected with status-aware messaging instead of generic approval failure text.
- fast-fail promote output now preserves: `requested_date`, `stage=PROMOTE_VALIDATION`, `lifecycle_state=FAILED`, `terminal_status=FAILED`, `publishability_state=NOT_READABLE`, `promote_mode`, `publish_target`, and a specific `reason_code`.

### Files changed
- `app/Infrastructure/Persistence/MarketData/EodCorrectionRepository.php`
- `app/Console/Commands/MarketData/PromoteMarketDataCommand.php`
- `tests/Unit/MarketData/MarketDataPipelineIntegrationTest.php`
- `tests/Unit/MarketData/OpsCommandSurfaceTest.php`

### Proof / regression target
- correction `PUBLISHED` no longer emits misleading `must be APPROVED` error text.
- correction fast-fail ops surface remains informative even when no new run is created.
- full publish coverage blocking contract is unchanged.

