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

## Manual-file promote intent classification execution

Status: DONE

### What changed

- Added explicit promote intent persistence on `eod_runs`:
  - `promote_mode`
  - `publish_target`
- Added command surface support:
  - `market-data:promote --mode=full_publish|correction|incremental`
- Added intent resolution in promote path:
  - default without `correction_id` → `full_publish`
  - default with `correction_id` → `correction`
  - explicit incremental manual promote → `incremental_candidate`

### Coverage policy after patch

- `full_publish` + `current_replace`:
  - coverage gate remains blocking
  - only `PASS` may continue to readable current promotion
- `correction` + `current_replace`:
  - existing guarded correction/current replacement semantics remain active
  - no global threshold weakening
- `incremental` + `incremental_candidate`:
  - coverage is still recorded as evidence
  - promote no longer misclassifies partial manual file as full current-replacement attempt
  - finalize seals a **non-current** candidate and keeps current readable publication authoritative

### Current publication protection

- incremental/manual non-current promote never calls current replacement path
- `publishability_state` remains `NOT_READABLE`
- final reason is explicit: `RUN_NON_CURRENT_PROMOTION`
- readable current publication is preserved via existing fallback/current pointer contract

### Files changed in this session

- `app/Console/Commands/MarketData/PromoteMarketDataCommand.php`
- `app/Console/Commands/MarketData/AbstractMarketDataCommand.php`
- `app/Application/MarketData/Services/MarketDataPipelineService.php`
- `app/Application/MarketData/Services/FinalizeDecisionService.php`
- `app/Infrastructure/Persistence/MarketData/EodRunRepository.php`
- `app/Models/EodRun.php`
- `database/migrations/2026_04_22_000001_add_promote_intent_fields_to_eod_runs.php`
- `docs/market_data/db/Database_Schema_MariaDB.sql`
- `tests/Unit/MarketData/FinalizeDecisionServiceTest.php`
- `tests/Unit/MarketData/MarketDataPipelineServiceTest.php`
- `tests/Unit/MarketData/OpsCommandSurfaceTest.php`

### Proof status in this container

- `php -l` on changed PHP files → PASS
- PHPUnit runtime → not runnable here because uploaded ZIP does not contain `vendor/`

### Remaining gap

- local operator/runtime proof still needs to be executed in user environment for:
  - `--mode=incremental`
  - `--mode=full_publish`
  - `--mode=correction --correction_id=...`

- 2026-04-22 follow-up hotfix: promote retry/reclassification now forks a fresh promote run from the persisted import seed instead of reusing a previously finalized promote run. This prevents stale terminal_status/promote_mode from contaminating incremental/correction attempts and keeps the import-only run immutable.

- 2026-04-22 hotfix: promote command no longer pre-binds to latest run before correction validation; correction promote now validates approval before run selection/forking so failed correction requests do not render stale incremental/full-promote run summaries.

## 2026-04-22 — CURRENT PUBLICATION INTEGRITY HARDENING EXECUTION

### Problem proven from runtime evidence

Runtime and DB evidence proved an invalid current-pointer state for `2026-03-20`:

- `eod_current_publication_pointer` existed and pointed to publication/run `55`
- `eod_publications.is_current = 1`
- `eod_publications.seal_state = SEALED`
- but the pointed run had:
  - `terminal_status = FAILED`
  - `publishability_state = NOT_READABLE`
  - `is_current_publication = 1`

This is a contract violation. A failed / non-readable run must never remain authoritative as current publication.

### Hardening delivered

1. **raw current integrity inspection added**
   - repository now exposes raw current-pointer inspection without silently filtering invalid rows away
   - invalid current-pointer states can now be detected explicitly instead of only disappearing from strict readable resolvers

2. **promotion guard strengthened**
   - `promoteCandidateToCurrent()` now detects invalid existing current ownership and refuses replacement with a precise integrity-repair error instead of generic current-exists semantics

3. **post-finalize integrity repair guard added**
   - if a non-readable/non-success run somehow remains current after finalize logic, the pipeline now restores prior readable current publication when available, otherwise clears current ownership for that trade date
   - this prevents future recurrence of `FAILED + NOT_READABLE + is_current = 1`

4. **operator repair command added**
   - new command: `market-data:current-publication:repair`
   - supports dry-run detection and `--apply` clearing of invalid current ownership for specific trade date or all detected invalid rows

### Files changed in this session

- `app/Application/MarketData/Services/MarketDataPipelineService.php`
- `app/Infrastructure/Persistence/MarketData/EodPublicationRepository.php`
- `app/Console/Commands/MarketData/RepairCurrentPublicationIntegrityCommand.php`
- `app/Console/Kernel.php`
- `tests/Support/UsesMarketDataSqlite.php`
- `tests/Unit/MarketData/PublicationRepositoryIntegrationTest.php`

### Local manual proof still required

Run in user environment:

- `php artisan market-data:current-publication:repair --trade_date=2026-03-20`
- `php artisan market-data:current-publication:repair --trade_date=2026-03-20 --apply`
- verify pointer/current mirrors are cleared for invalid current state
- verify subsequent correction still requires valid readable baseline unless a valid current publication is restored/published

## 2026-04-23 — CORRECTION REQUEST RE-EXECUTION POLICY EXECUTION

Status: DONE IN CODE / PENDING LOCAL DB MIGRATION + PHPUnit PROOF

### Policy implemented

Adopted split lifecycle with mode-aware execution enforcement:

- `correction_current`
  - single-use for current replacement
  - once a current-style execution reaches terminal consumption, the request becomes consumed and cannot be executed or approved again
- `repair_candidate`
  - reusable for iterative non-current repair execution
  - same `correction_id` may rerun while status stays inside repair-capable lifecycle states
  - repair execution never promotes to current automatically

### Concrete schema changes

Added to `eod_dataset_corrections`:
- `execution_count`
- `last_executed_at`
- `current_consumed_at`

Expanded status enum to include:
- `REPAIR_ACTIVE`
- `REPAIR_EXECUTED`
- `CONSUMED_CURRENT`
- `CLOSED`

### Concrete code changes

- `EodCorrectionRepository`
  - added `canExecuteCorrection($correctionId, $tradeDate, $mode)`
  - `requireApprovedForTradeDate()` now delegates to `correction_current` mode enforcement
  - `markExecuting()` is now mode-aware
  - added `markRepairExecuted()`
  - added `markConsumedForCurrent()`
  - `markRepairCandidate()` kept as compatibility alias to `markRepairExecuted()`
  - `approve()` now blocks re-approval of already consumed current corrections

- `MarketDataPipelineService`
  - repair-candidate promote path now validates correction eligibility with mode-aware execution policy
  - correction finalize now persists mode-specific terminal lifecycle:
    - unchanged current correction → `CONSUMED_CURRENT`
    - repair candidate finalize → `REPAIR_EXECUTED`
    - successful current replacement → `PUBLISHED`

### Files changed in this session

- `app/Infrastructure/Persistence/MarketData/EodCorrectionRepository.php`
- `app/Application/MarketData/Services/MarketDataPipelineService.php`
- `database/migrations/2026_04_23_000004_add_correction_reexecution_policy_fields.php`
- `docs/market_data/db/Database_Schema_MariaDB.sql`
- `tests/Support/UsesMarketDataSqlite.php`
- `tests/Unit/MarketData/CorrectionRepositoryIntegrationTest.php`
- `tests/Unit/MarketData/CorrectionCommandsTest.php`
- `tests/Unit/MarketData/MarketDataPipelineIntegrationTest.php`

### Proof status in this container

- `php -l` on changed PHP files → PASS
- PHPUnit runtime → not runnable here because uploaded ZIP does not contain `vendor/`

### Local proof still required

Run after `php artisan migrate` in user environment:

- `vendor/bin/phpunit tests/Unit/MarketData/CorrectionRepositoryIntegrationTest.php`
- `vendor/bin/phpunit tests/Unit/MarketData/CorrectionCommandsTest.php`
- `vendor/bin/phpunit tests/Unit/MarketData/MarketDataPipelineIntegrationTest.php --filter "correction|repair_candidate|unchanged_artifacts"`

Suggested manual runtime proof:

1. `repair_candidate` first execution with `market-data:promote --mode=incremental --correction_id=...`
2. same `repair_candidate` second execution with same `correction_id` → must remain allowed
3. `correction_current` execution with same `correction_id` after it becomes consumed → must fail
4. verify current publication pointer remains unchanged for repair candidate executions
