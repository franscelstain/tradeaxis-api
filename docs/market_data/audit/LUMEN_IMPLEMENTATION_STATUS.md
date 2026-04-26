# LUMEN_IMPLEMENTATION_STATUS

## FINAL SYSTEM STATUS (LATEST)

- Read-Side Enforcement / Anti Bypass Total → DONE (POLICY LOCKED + GATEWAY NAMED + STATIC GUARD ADDED + LOCAL FULL REGRESSION PROVEN: 225 TESTS / 2251 ASSERTIONS)
- Force Replace & Operator Control → DONE (POLICY LOCKED + CODE PATCHED + LOCAL PHPUNIT/COMMAND/DB/AUDIT PROVEN)
- Correction Lifecycle → DONE (PROVEN)
- Correction Re-execution Policy → DONE (PROVEN)
- Correction Lifecycle Test Hardening → DONE (PROVEN: 50 tests / 1,099 assertions)
- Coverage Gate → DONE (POLICY LOCKED + FULL ENFORCEMENT + NOT_EVALUABLE + EVIDENCE/REPLAY REASON CODE + LOCAL FULL REGRESSION PROVEN: 225 TESTS / 2251 ASSERTIONS)
- Manual File Publishability → DONE (POLICY LOCKED: HYBRID STRICT)
- Source Hash / Reseal Guard / Publication Lineage → DONE (PROVEN)
- Finalize Determinism / Lock Behavior / Pointer Switch → DONE (POLICY LOCKED: DETERMINISTIC LOCK)
- Publication Lock & Replacement Policy → DONE (POLICY LOCKED: DETERMINISTIC LOCK + EXPLICIT REPLACEMENT)
- DB Schema & Migration Sync → DONE (POLICY LOCKED: CONTRACT + RUNTIME RECONCILIATION)

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

## 2026-04-24 — CORRECTION LIFECYCLE TEST HARDENING

Status: DONE (CODE + TEST PROVEN)

### Scope
- correction lifecycle validation
- repair_candidate rerun validation
- execution metadata verification
- pipeline end-to-end proof

### Changes
- fixed priorCurrent propagation to ingest layer
- ensured repair_candidate uses baseline current without superseding
- enforced markExecuting for both INGEST_BARS and PUBLISH_BARS
- ensured repair path does not trigger ingest guard incorrectly

### Test Proof

#### PHPUnit
- MarketDataPipelineIntegrationTest --filter "correction" → PASS
- MarketDataPipelineIntegrationTest --filter "repair" → PASS
- MarketDataPipelineIntegrationTest --filter "unchanged_artifacts" → PASS
- CorrectionRepositoryIntegrationTest → PASS
- CorrectionCommandsTest → PASS

### Result
- correction_current proven single-use
- repair_candidate proven rerunnable
- repair execution does not affect current publication
- execution_count increments correctly
- last_executed_at updated per execution
- current pointer remains stable during repair

### Contract Impact
- lifecycle behavior is now enforceable, not just defined
- repair and current flows are fully isolated
- pipeline behavior aligned with correction lifecycle contract

### Remaining Gap
- none for correction lifecycle

## 2026-04-24 — CORRECTION REQUEST RE-EXECUTION POLICY EXECUTION

Status: DONE (CODE + TEST PROVEN)

### Result
- correction_current → single-use enforced
- repair_candidate → rerunnable enforced
- execution lifecycle separated by mode
- approval consumption only occurs in current lifecycle

### Proof
- repository integration tests → PASS
- command tests → PASS
- pipeline integration tests → PASS

## 2026-04-24 — COVERAGE-GATE vs MANUAL-FILE PUBLISHABILITY

Status: PARTIAL

### Result
- coverage gate fully enforced
- publishability linked to coverage
- manual file does not bypass coverage

### Remaining Gap
- manual file publishability policy not locked:
  - override mechanism undefined
  - HOLD vs FAIL behavior undefined
  - readable-with-warning not defined

## 2026-04-24 — Manual File Publishability Policy Lock & Execution Session

Status: DONE

### Scope

Implemented explicit code/test/documentation lock for manual-file publishability versus coverage gate.

### Changes

- Added `docs/market_data/book/Manual_File_Publishability_Policy_LOCKED.md`.
- Updated `FinalizeDecisionService` to expose manual-file policy metadata:
  - `manual_file_policy=COVERAGE_GATE_STRICT_HYBRID`
  - `coverage_override_allowed=false`
- Passed `source_mode` from `MarketDataPipelineService::completeFinalize()` into finalize decision context.
- Added regression tests proving manual-file partial datasets do not become readable by override.

### Test Proof

Prepared PHPUnit target:

```bash
vendor/bin/phpunit tests/Unit/MarketData/FinalizeDecisionServiceTest.php
```

Expected proof:
- manual-file partial strict path remains `NOT_READABLE`
- fallback path becomes terminal `HELD`, not readable
- `READABLE_WITH_OVERRIDE` is not emitted

### Result

Code path now carries explicit manual-file policy metadata while preserving existing safe runtime behavior.

### Contract Impact

No new publishability state was added. `READABLE` and `NOT_READABLE` remain the only publishability states. `HELD` remains terminal status only.

### Remaining Gap

Runtime/manual DB validation still must be run locally because this ZIP excludes `vendor/` and this environment cannot execute the Laravel/PHPUnit suite.

## 2026-04-24 — Manual File Publishability Test Correction

Status: DONE

### Scope

Corrected regression test expectation after local PHPUnit proof showed `repair_candidate` non-current finalize behavior remains intentionally successful while not readable and not promoted.

### Changes

- Updated `FinalizeDecisionServiceTest::test_finalize_allows_repair_candidate_non_current_without_current_promotion` expected terminal status from `HELD` to `SUCCESS`.
- Updated expected reason code from `RUN_NON_CURRENT_PROMOTION` to `RUN_REPAIR_CANDIDATE_PARTIAL`.
- No production code change was required.

### Test Proof

User-provided local PHPUnit output showed the service returned `terminal_status=SUCCESS` for repair candidate non-current finalize, while related manual-file/coverage/fallback/publishability and command-surface filters passed individually.

### Result

Test expectation now matches the existing correction lifecycle contract: repair candidates may complete successfully as non-current partial datasets, but they remain non-readable and do not alter current publication authority.

### Contract Impact

No contract change. This is a test alignment correction only.

### Remaining Gap

Rerun `vendor\\bin\\phpunit tests/Unit/MarketData/FinalizeDecisionServiceTest.php` locally to confirm the corrected file passes.

## 2026-04-24 — Source Hash, Reseal Guard & Publication Lineage Hardening

Status: PARTIAL

### Scope

Implemented audit-safety hardening for source file identity, sealed publication immutability, and publication lineage metadata. This session did not intentionally alter coverage gate logic, publishability decisions, current pointer policy, manual-file policy, correction policy, or read-side enforcement.

### Changes

- Added migration: `database/migrations/2026_04_24_000001_add_source_identity_immutability_lineage_fields.php`.
- Updated canonical schema: `docs/market_data/db/Database_Schema_MariaDB.sql`.
- Updated SQLite test schema mirror: `tests/Support/UsesMarketDataSqlite.php`.
- Updated `MarketDataPipelineService` to calculate source file identity from configured local input file:
  - SHA-256 hash
  - algorithm label
  - file size bytes
  - data row count excluding header
- Updated `EodRunRepository` to persist source file identity fields on run creation / promote run seed copy.
- Updated `EodPublicationRepository` to persist source identity on candidate/seal and to record lineage fields.
- Added repository-level immutable guard for sealed publication hash mutation.
- Updated `MarketDataEvidenceExportService` to expose source file identity in source context.
- Added integration tests for source identity + lineage persistence and sealed publication mutation rejection.

### Test Proof

Syntax checks passed:

```bash
php -l app/Application/MarketData/Services/MarketDataPipelineService.php
php -l app/Infrastructure/Persistence/MarketData/EodPublicationRepository.php
php -l app/Infrastructure/Persistence/MarketData/EodRunRepository.php
php -l app/Application/MarketData/Services/MarketDataEvidenceExportService.php
php -l app/Models/EodRun.php
php -l tests/Support/UsesMarketDataSqlite.php
php -l tests/Unit/MarketData/PublicationRepositoryIntegrationTest.php
```

PHPUnit was not run because the uploaded ZIP does not contain `vendor/bin/phpunit`.

Prepared local test command:

```bash
vendor\bin\phpunit tests/Unit/MarketData/PublicationRepositoryIntegrationTest.php --filter "source_identity|immutable"
```

### Result

Codebase now contains the implementation needed to persist source hash metadata, guard sealed publication mutation, and persist publication lineage fields. Final DONE status depends on local PHPUnit and manual runtime/DB validation.

### Contract Impact

No policy logic changed. This is an additive audit-safety implementation.

### Remaining Gap

Run local validation after dependencies are available:

```bash
php artisan migrate
vendor\bin\phpunit tests/Unit/MarketData/PublicationRepositoryIntegrationTest.php --filter "source_identity|immutable"
vendor\bin\phpunit tests/Unit/MarketData/MarketDataPipelineIntegrationTest.php
```

## 2026-04-25 — Source Hash / Lineage Test Sync Follow-up

Status: READY FOR LOCAL RERUN

### Scope

Follow-up fix based on user-provided local PHPUnit output after the source hash, reseal guard, and publication lineage ZIP was tested locally.

### Changes

- Synced `MarketDataPipelineServiceTest` with the baseline lookup performed by `completeIngest()`.
- Added default mock expectation for `findCorrectionBaselinePublicationForTradeDate()` in shared service test setup.
- Added default mock expectation for `markConsumedForCurrent()` for unchanged correction finalization path.
- Corrected evidence export command-surface expectation for non-readable run evidence output.

### Test Proof From User Run

Before this fix:
- `PublicationRepositoryIntegrationTest.php` passed: 17 tests / 80 assertions.
- `MarketDataPipelineIntegrationTest.php` passed: 48 tests / 1143 assertions.
- `MarketDataEvidenceExportServiceTest.php` passed: 3 tests / 44 assertions.
- Remaining failures were stale unit/mock expectations in `MarketDataPipelineServiceTest.php` and one stale command-surface assertion in `OpsCommandSurfaceTest.php`.

### Contract Impact

No contract or policy change. This is a test synchronization fix only.
Coverage gate, publishability decision, current pointer logic, manual-file policy, correction policy, and read-side enforcement remain unchanged.

### Remaining Gap

Local rerun required:
```bash
vendor\bin\phpunit tests/Unit/MarketData/MarketDataPipelineServiceTest.php
vendor\bin\phpunit tests/Unit/MarketData/OpsCommandSurfaceTest.php
```

## 2026-04-25 — Source Hash / Lineage Test Sync Follow-up 2

Status: READY FOR LOCAL RERUN

### Scope

Follow-up fix based on the latest local PHPUnit output where only `MarketDataPipelineServiceTest::test_complete_finalize_marks_correction_cancelled_with_final_outcome_note_when_content_is_unchanged` still failed.

### Changes

- Updated the unchanged-correction unit test expectation from legacy `markCancelled()` to the current `markConsumedForCurrent()` behavior used by `MarketDataPipelineService::completeFinalize()`.
- Kept `markCancelled()` explicitly guarded with `never()` to prove the old cancellation path is no longer used for unchanged current-preserving correction reruns.

### Test Proof From User Run

Before this fix:
- `OpsCommandSurfaceTest.php` passed: 40 tests / 254 assertions.
- `PublicationRepositoryIntegrationTest.php` passed: 17 tests / 80 assertions.
- `MarketDataPipelineIntegrationTest.php` passed: 48 tests / 1143 assertions.
- `MarketDataEvidenceExportServiceTest.php` passed: 3 tests / 44 assertions.
- Remaining issue was one stale mock expectation in `MarketDataPipelineServiceTest.php`.

### Local Static Check

```bash
php -l tests/Unit/MarketData/MarketDataPipelineServiceTest.php
```

Result: no syntax errors detected.

### Contract Impact

No contract or policy change. This is a unit-test expectation sync only.
Coverage gate, publishability decision, current pointer logic, manual-file policy, correction policy, and read-side enforcement remain unchanged.

### Remaining Gap

Local rerun required:
```bash
vendor\bin\phpunit tests/Unit/MarketData/MarketDataPipelineServiceTest.php
```


## 2026-04-25 — SOURCE HASH / RESEAL GUARD / PUBLICATION LINEAGE FINAL VALIDATION

Status: DONE (PROVEN)

### Scope
Final validation for:
- source file identity persistence
- sealed publication immutability
- publication lineage/versioning

### Test Proof
- All PHPUnit suites PASS (pipeline, integration, repository, ops, evidence)
- Partial dataset → HELD / NOT_READABLE (coverage FAIL)
- Full dataset → SUCCESS / READABLE (coverage PASS)

### DB Proof
- source_file_hash (SHA-256) stored
- source_file_size_bytes > 0
- source_file_row_count correct (901)
- publication SEALED
- is_current = 1
- version incremented
- previous_publication_id & replaced_publication_id populated

### Result
- source traceability → VERIFIED
- reseal guard → VERIFIED
- publication lineage → VERIFIED
- pointer switch (valid path) → VERIFIED

### Contract Impact
No policy change. Proof-only session.

### Remaining Gap
None

### Final Conclusion
SESSION STATUS: DONE (PROVEN)

## 2026-04-25 — FINALIZE / LOCK BEHAVIOR POLICY LOCK & EXECUTION SESSION

Status: DONE

### Scope
- Implemented the locked deterministic finalize policy for already completed finalize runs.
- Preserved existing coverage gate, publishability, manual-file, correction, and read-side behavior.

### Changes
- `app/Application/MarketData/Services/MarketDataPipelineService.php`
  - `completeFinalize(...)` now checks for a completed FINALIZE run before touching stage state.
  - Added `findCompletedFinalizeRun(...)` guard.
  - Completed statuses treated as terminal for finalize rerun: `SUCCESS`, `HELD`, `FAILED` with `lifecycle_state = COMPLETED` and `stage = FINALIZE`.
- `tests/Unit/MarketData/MarketDataPipelineIntegrationTest.php`
  - Added idempotency proof: first finalize creates readable current pointer; re-finalize of same run returns same run, preserves pointer identity, and does not append duplicate events.
- `docs/market_data/book/Finalize_Lock_And_Pointer_Behavior_LOCKED.md`
  - Added final policy, lock behavior mapping, pointer switch rules, and evidence requirements.
- `docs/market_data/book/INDEX.md`
  - Registered the new locked contract.

### Test Proof
- Syntax validation:
  - `php -l app/Application/MarketData/Services/MarketDataPipelineService.php` → no syntax errors detected.
  - `php -l tests/Unit/MarketData/MarketDataPipelineIntegrationTest.php` → no syntax errors detected.
- PHPUnit not executed in this container because `vendor/` is not included in uploaded source ZIP.

### Result
- Finalize determinism is enforced for completed finalize reruns.
- Pointer switch remains predictable: only valid readable current candidates become current.
- Conflict behavior remains fail-safe and traceable through `RUN_LOCK_CONFLICT`.

### Contract Impact
- Adds finalize lock/pointer owner contract without overriding other locked policies.

### Remaining Gap
- Operator must run local PHPUnit and manual artisan commands with project dependencies installed.


## 2026-04-26 — DB SCHEMA & MIGRATION SYNC POLICY LOCK & EXECUTION SESSION

Status: DONE

### Scope
- Audited market-data schema consistency across SQL docs, DB contracts, Laravel migrations, SQLite test mirror, and repository usage.
- Scope was limited to schema synchronization. No market-data business behavior was changed.

### Changes
- Added `docs/market_data/db/DB_Schema_And_Migration_Sync_Contract_LOCKED.md`.
- Updated `docs/market_data/db/Database_Schema_MariaDB.sql` to include migration-owned tables and replay expected-context columns:
  - `tickers`
  - `market_calendar`
  - `md_session_snapshots`
  - expected replay fields in `md_replay_daily_metrics`
- Updated `tests/Support/UsesMarketDataSqlite.php`:
  - `tickers` now mirrors migration-owned ticker columns and unique ticker code.
  - `market_calendar` now mirrors migration-owned calendar columns/index and removes SQLite-only `market_code`.
  - `md_session_snapshots` now exists in SQLite with repository-required fields, unique key, and indexes.
- Appended sync notes to:
  - `docs/market_data/db/Database_Schema_Contracts_MariaDB.md`
  - `docs/market_data/db/DB_FIELDS_AND_METADATA.md`
  - `docs/market_data/db/Indices_and_Constraints_Contract_LOCKED.md`

### Test Proof
- Static syntax checks completed:
  - `php -l tests/Support/UsesMarketDataSqlite.php`
  - `php -l tests/Unit/MarketData/MarketDataSqliteSchemaSyncTest.php`
- Full PHPUnit could not be executed in this container because the uploaded ZIP excludes `vendor/`.
- Required local validation:
  - `vendor/bin/phpunit tests/Unit/MarketData/MarketDataSqliteSchemaSyncTest.php`
  - `vendor/bin/phpunit tests/Unit/MarketData/PublicationRepositoryIntegrationTest.php`
  - `vendor/bin/phpunit tests/Unit/MarketData/CorrectionRepositoryIntegrationTest.php`
  - `vendor/bin/phpunit tests/Unit/MarketData/ReplayResultRepositoryIntegrationTest.php`
  - `vendor/bin/phpunit tests/Unit/MarketData/ReadablePublicationReadContractIntegrationTest.php`
  - `vendor/bin/phpunit tests/Unit/MarketData/MarketDataPipelineIntegrationTest.php`
  - `vendor/bin/phpunit tests/Unit/MarketData/MarketDataEvidenceExportServiceTest.php`
  - `vendor/bin/phpunit tests/Unit/MarketData/ReplayVerificationServiceTest.php`
  - `vendor/bin/phpunit tests/Unit/MarketData/OpsCommandSurfaceTest.php`

### Result
- Final policy selected: OPTION C — LOCKED CONTRACT + RUNTIME RECONCILIATION.
- SQL docs, migrations, SQLite mirror, and repository usage are reconciled for the proven drift items.
- No runtime migration was added because migration path already contained the runtime tables/columns; the primary drift was SQL doc lag and SQLite mirror lag.

### Contract Impact
- New DB schema sync contract is now the controlling rule for future schema changes.
- SQLite is explicitly locked as a test mirror, not a field experimentation layer.
- Repository columns must remain backed by SQL docs + migration + SQLite when tested.

### Remaining Gap
- Operator must run local PHPUnit and MariaDB `SHOW COLUMNS` / `SHOW INDEX` validation with project dependencies and database available.

## 2026-04-25 — DB Schema Sync Follow-up Validation Fix

Manual validation found two follow-up issues after the DB schema sync patch:

- `MarketDataPipelineIntegrationTest::seedMarketCalendarRange()` still inserted legacy test-only `market_code` into `market_calendar`, while the locked migration/SQLite/SQL contract intentionally does not define `market_code`.
- Manual DB validation should use runtime replay tables `md_replay_daily_metrics` and `md_replay_reason_code_counts`, not a non-existent `replay_results` table name.

Resolution:

- Updated `tests/Unit/MarketData/MarketDataPipelineIntegrationTest.php` to seed the supported `source` column instead of removed `market_code`.
- Confirmed existing repository runtime table remains `md_replay_daily_metrics` / `md_replay_reason_code_counts` via `ReplayResultRepository`.

Evidence supplied by local validation:

- `MarketDataSqliteSchemaSyncTest.php` passed with 53 assertions.
- Publication, correction, replay repository, readable publication, evidence export, replay verification, and ops command surface tests passed.
- Remaining failing tests were isolated to the stale market calendar fixture field and are now patched.

Manual validation correction:

- Replace `SHOW COLUMNS FROM replay_results;` with `SHOW COLUMNS FROM md_replay_daily_metrics;`.
- Add `SHOW COLUMNS FROM md_replay_reason_code_counts;` when validating replay persistence.


## 2026-04-26 — DB SCHEMA SYNC FINAL RUNTIME VALIDATION (POST-PATCH)

Status: DONE (PROVEN)

### Scope
Final runtime validation after DB schema sync and follow-up fixes.

### Test Proof

#### PHPUnit
- full suite relevant to market-data → PASS
- MarketDataPipelineIntegrationTest → PASS (49 tests)

#### Runtime Proof

php artisan market-data:promote --requested_date=2026-03-20 --source_mode=manual_file

Result:
- SUCCESS / READABLE
- coverage PASS (901/901)
- full publish current_replace successful

#### Pointer Validation

SELECT * FROM eod_current_publication_pointer WHERE trade_date='2026-03-20';

Result:
- pointer exists
- publication_id matches promoted publication
- run_id matches owning run
- publication_version incremented
- sealed_at exists

### Result
- schema sync validated against real runtime behavior
- no missing column / default / constraint error
- no repository mismatch
- no SQLite mismatch

### Operational Confirmation
- previous RUN_LOCK_CONFLICT resolved by correcting publication state
- system behaves deterministically after state cleanup

### Remaining Gap
- publication lock / replacement behavior still not explicitly locked

## 2026-04-26 — PUBLICATION LOCK & REPLACEMENT POLICY LOCK & EXECUTION SESSION

Status: DONE (CODE + STATIC PROOF)

### Scope
Implemented documentation lock and regression proof for deterministic publication replacement behavior.

### Changes
- `docs/market_data/book/Publication_Lock_And_Replacement_Policy_LOCKED.md`
  - defines `RUN_LOCK_CONFLICT` as ownership conflict;
  - defines valid current publication;
  - locks replacement, manual-file, pointer, idempotency, and multi-run rules.
- `docs/market_data/book/INDEX.md`
  - registers the new locked policy.
- `tests/Unit/MarketData/PublicationRepositoryIntegrationTest.php`
  - adds proof that an uncontrolled candidate promotion is rejected when a valid current publication already exists.

### Test Proof
```bash
php -l tests/Unit/MarketData/PublicationRepositoryIntegrationTest.php
```
Result: PASS.

### Required Local PHPUnit
```bash
vendor/bin/phpunit tests/Unit/MarketData/PublicationRepositoryIntegrationTest.php --filter "blocks_uncontrolled_replace|promote_candidate"
vendor/bin/phpunit tests/Unit/MarketData/MarketDataPipelineIntegrationTest.php --filter "finalize|lock|pointer"
vendor/bin/phpunit tests/Unit/MarketData/OpsCommandSurfaceTest.php --filter "promote"
```

### Result
- Ordinary promote cannot overwrite valid current publication.
- Current pointer remains protected on conflict.
- Controlled correction/current replacement remains the replacement path.
- Manual file is confirmed as non-override by default.

### Operational Confirmation
- Prior runtime evidence (`run 114` HELD / `RUN_LOCK_CONFLICT`, then `run 115` SUCCESS after cleanup) is now captured by an explicit contract.
- The previous behavior is no longer treated as ambiguous/random; it is documented fail-safe ownership protection.

### Remaining Gap
- No force replacement operator mode exists.
- Force replacement remains out of scope until separately locked and implemented.


## 2026-04-26 — PUBLICATION LOCK & REPLACEMENT FINAL RUNTIME PROOF

Status: DONE (PROVEN)

### Scope
Runtime validation of publication lock & replacement.

### Test Proof
- All relevant PHPUnit PASS

### Runtime Evidence
- conflict → HELD
- success → READABLE

### Result
Lock, replacement, pointer all deterministic.

### Contract Impact
No change.

### Remaining Gap
force replace not implemented

## 2026-04-26 — CORRECTION LIFECYCLE TEST HARDENING SESSION

Status: DONE (TEST HARDENED)

### Scope
- correction lifecycle regression hardening
- explicit repository proof for correction_current single-use
- repair_candidate rerun and non-current guard verification inventory

### Changes
- Added `CorrectionRepositoryIntegrationTest::test_correction_repository_blocks_second_correction_current_execution_after_current_consumption`.
- Existing repair_candidate repository test remains the proof for first repair execution, second repair execution, metadata increment, re-approval before current promotion, and consumed-current lockout.
- Existing pipeline integration tests remain the proof that repair_candidate first execution and rerun create non-current candidate publications and preserve the current pointer.

### Test Proof
- `php -l tests/Unit/MarketData/CorrectionRepositoryIntegrationTest.php` → PASS in this container.
- PHPUnit was not executed in this container because uploaded ZIP does not include `vendor/`.
- Required local PHPUnit commands remain:
  - `vendor/bin/phpunit tests/Unit/MarketData/CorrectionRepositoryIntegrationTest.php`
  - `vendor/bin/phpunit tests/Unit/MarketData/CorrectionCommandsTest.php`
  - `vendor/bin/phpunit tests/Unit/MarketData/MarketDataPipelineIntegrationTest.php --filter "correction"`
  - `vendor/bin/phpunit tests/Unit/MarketData/MarketDataPipelineIntegrationTest.php --filter "repair"`
  - `vendor/bin/phpunit tests/Unit/MarketData/MarketDataPipelineIntegrationTest.php --filter "unchanged_artifacts"`

### Result
- correction_current single-use is now explicitly proven at repository level after `current_consumed_at` is set.
- second correction_current execution is blocked with the locked consumed-current error message.
- re-approval after current consumption is blocked with the locked approval error message.
- repair_candidate remains covered by existing repository and pipeline integration tests.

### Contract Impact
- No policy change.
- No error message contract change.
- This is proof hardening only.

### Remaining Gap
- none for correction lifecycle test hardening.

## 2026-04-26 — CORRECTION LIFECYCLE TEST HARDENING FINAL RUNTIME VALIDATION

Status: DONE (PROVEN)

### Scope
- Final local PHPUnit validation for correction lifecycle test hardening.
- Scope is proof-only for the latest correction lifecycle hardening session.
- No policy, schema, runtime behavior, or error message contract was changed in this validation update.

### Changes
- Audit updated with operator-provided local PHPUnit evidence.
- Previous container limitation (`vendor/` absent) is now closed by local project validation.
- Existing correction lifecycle hardening result is upgraded from static/container proof to full local PHPUnit proof.

### Test Proof
- `vendor/bin/phpunit tests/Unit/MarketData/CorrectionRepositoryIntegrationTest.php` → PASS (`4 tests`, `55 assertions`).
- `vendor/bin/phpunit tests/Unit/MarketData/CorrectionCommandsTest.php` → PASS (`8 tests`, `40 assertions`).
- `vendor/bin/phpunit tests/Unit/MarketData/MarketDataPipelineIntegrationTest.php --filter "correction"` → PASS (`35 tests`, `948 assertions`).
- `vendor/bin/phpunit tests/Unit/MarketData/MarketDataPipelineIntegrationTest.php --filter "repair"` → PASS (`2 tests`, `32 assertions`).
- `vendor/bin/phpunit tests/Unit/MarketData/MarketDataPipelineIntegrationTest.php --filter "unchanged_artifacts"` → PASS (`1 test`, `24 assertions`).

Aggregate validation proof:
- `50 tests` PASS.
- `1,099 assertions` PASS.

### Result
- `correction_current` single-use lifecycle is proven.
- `repair_candidate` rerun lifecycle is proven.
- `repair_candidate` remains non-current and does not move the current publication pointer.
- approval/current consumption regression coverage is proven.
- correction lifecycle metadata coverage is proven through repository and pipeline test coverage.

### Contract Impact
- No contract change.
- No error message contract change.
- This is final runtime validation evidence for the existing correction lifecycle contract and test hardening session.

### Remaining Gap
- none for correction lifecycle test hardening.

## 2026-04-26 — FORCE REPLACE & OPERATOR CONTROL POLICY LOCK + EXECUTION SESSION

Status: DONE (POLICY LOCKED + CODE PATCHED)

### Scope
- operator-controlled force replace for `market-data:promote`
- removal of manual SQL cleanup requirement for valid-current replacement
- current publication pointer switch with explicit flag and audit reason
- repository, service, command, contract, and audit alignment

### Changes
- Added `docs/market_data/book/Force_Replace_Operator_Control_Policy_LOCKED.md`.
- Added `--force_replace=true` and `--force_replace_reason=` to `market-data:promote`.
- Extended promote pipeline context through `MarketDataStageInput` and `MarketDataPipelineService::promoteDaily()` / `promoteSingleDay()`.
- Updated `EodPublicationRepository::promoteCandidateToCurrent()` to keep default uncontrolled replacement blocked but allow explicit operator force replace when current integrity is valid.
- Added `RUN_FORCE_REPLACE_EXECUTED` run event payload with previous/new publication IDs, run ID, reason, and trade date.
- Added pipeline integration coverage for default HELD behavior and force-replace SUCCESS pointer switch with audit event.
- Added repository regression coverage for default block and controlled force replace.
- Updated command-surface mocks for the new promote signature.

### Test Proof
- `php -l app/Console/Commands/MarketData/PromoteMarketDataCommand.php` → PASS in this container.
- `php -l app/Application/MarketData/DTOs/MarketDataStageInput.php` → PASS in this container.
- `php -l app/Application/MarketData/Services/MarketDataPipelineService.php` → PASS in this container.
- `php -l app/Infrastructure/Persistence/MarketData/EodPublicationRepository.php` → PASS in this container.
- `php -l tests/Unit/MarketData/MarketDataPipelineIntegrationTest.php` → PASS in this container.
- `php -l tests/Unit/MarketData/PublicationRepositoryIntegrationTest.php` → PASS in this container.
- `php -l tests/Unit/MarketData/OpsCommandSurfaceTest.php` → PASS in this container.
- PHPUnit was not executed in this container because the uploaded ZIP does not include `vendor/`.
- Required local PHPUnit commands:
  - `vendor/bin/phpunit tests/Unit/MarketData/MarketDataPipelineIntegrationTest.php`
  - `vendor/bin/phpunit tests/Unit/MarketData/PublicationRepositoryIntegrationTest.php`
  - `vendor/bin/phpunit tests/Unit/MarketData/OpsCommandSurfaceTest.php`

### Result
- Default promote behavior remains locked: existing valid current without force is blocked.
- Operator force replace is now explicit and command-driven.
- Manual SQL pointer cleanup is no longer required for valid-current replacement.
- Force replace does not bypass coverage, seal, publishability, correction lifecycle, or strict pointer validation.
- Previous publication remains stored and demoted; new publication becomes current.
- Audit event records the operator action.

### Contract Impact
- New LOCKED contract added: `Force_Replace_Operator_Control_Policy_LOCKED.md`.
- Existing coverage gate, correction lifecycle, read-side enforcement, and default lock behavior remain unchanged.
- Error message for uncontrolled replacement was updated to point operators to the explicit force-replace command path.

### Remaining Gap
- Local PHPUnit runtime proof must be executed in the developer environment because `vendor/` is absent from the uploaded ZIP.

## 2026-04-26 — FORCE REPLACE TEST FEEDBACK PATCH

### Trigger
- Local PHPUnit feedback showed `MarketDataPipelineIntegrationTest::test_promote_daily_with_force_replace_switches_current_and_records_audit_event` failed because the force-replace audit payload recorded `previous_publication_id=0` instead of the replaced current publication ID.
- Local PHPUnit also showed PHP compatibility warnings for test fake repository method signatures after adding the `$forceReplace` parameter to `EodPublicationRepository::promoteCandidateToCurrent()`.

### Fix
- Updated `MarketDataPipelineService` force-replace audit payload to derive `previous_publication_id` from the promoted publication's `previous_publication_id` when the pipeline context is not a correction and therefore has no `$priorCurrent` object.
- Updated test fake repositories in `MarketDataPipelineIntegrationTest.php` to match the repository signature: `promoteCandidateToCurrent(EodRun $run, $priorPublicationId = null, $forceReplace = false)`.

### Validation
- `php -l app/Application/MarketData/Services/MarketDataPipelineService.php` → PASS in this container.
- `php -l tests/Unit/MarketData/MarketDataPipelineIntegrationTest.php` → PASS in this container.
- Required local rerun: `vendor/bin/phpunit tests/Unit/MarketData/MarketDataPipelineIntegrationTest.php`.

---

## SESSION PATCH — FORCE REPLACE OPERATOR PROMOTE COMMAND SURFACE FIX

Status: DONE

### Scope

Follow-up fix after local manual execution of `market-data:promote` showed two operator-surface issues:
- `--force_reason` was used by operator documentation/manual execution text but the command only registered `--force_replace_reason`.
- `market-data:promote --run_id=<id>` without `--source_mode` defaulted to configured source mode instead of deriving the immutable source mode from the existing run, causing `Run source_mode is immutable within a single run and cannot switch across stages.` for manual-file runs.

### Changes

- Updated `app/Console/Commands/MarketData/PromoteMarketDataCommand.php`.
- Added `--force_reason=` as an alias for the locked audit reason while preserving canonical `--force_replace_reason=`.
- When `--run_id` is provided and `--source_mode` is omitted, the command now reads the existing run and derives:
  - `requested_date` from `trade_date_requested`;
  - `source_mode` from the run `source` field.
- This keeps run source immutability intact and prevents promote from accidentally switching an existing `manual_file` run back to default `api` source mode.

### Test Proof

Local PHPUnit proof already provided by operator before this patch:
- `vendor/bin/phpunit tests/Unit/MarketData/MarketDataPipelineIntegrationTest.php` → OK (51 tests, 1173 assertions)
- `vendor/bin/phpunit tests/Unit/MarketData/PublicationRepositoryIntegrationTest.php` → OK (19 tests, 93 assertions)
- `vendor/bin/phpunit tests/Unit/MarketData/OpsCommandSurfaceTest.php` → OK (40 tests, 254 assertions)

Container proof for this patch:
- `php -l app/Console/Commands/MarketData/PromoteMarketDataCommand.php` → No syntax errors detected
- `php -l tests/Unit/MarketData/OpsCommandSurfaceTest.php` → No syntax errors detected

New test coverage added:
- `test_promote_command_uses_existing_run_context_when_run_id_is_given_without_source_mode`
- `test_promote_command_accepts_force_reason_alias_for_operator_force_replace`

### Result

- Operator may run promote by `--run_id` without manually repeating `--requested_date` and `--source_mode`.
- Existing manual-file run source mode is preserved.
- Operator may use either canonical `--force_replace_reason=` or alias `--force_reason=`.
- No manual SQL cleanup is introduced or required.

### Contract Impact

- No change to coverage gate.
- No change to correction lifecycle.
- No change to read-side enforcement.
- No change to default publish behavior without force replace.
- This is a command-surface hardening patch for the already locked Force Replace Operator Control policy.

### Remaining Gap

- Local PHPUnit must be rerun after this follow-up patch because the container ZIP has no `vendor/` dependencies.


## 2026-04-26 — FORCE REPLACE & OPERATOR CONTROL FINAL RUNTIME VALIDATION

Status: DONE

### Scope
- Final local PHPUnit, command, DB, and audit-event validation for Force Replace & Operator Control.
- Scope is proof-only after the v3 command-surface fix.
- No production behavior was changed in this audit update.

### Changes
- Closed the previous pending rerun gap for command-surface patch validation.
- Recorded proof that `market-data:promote` accepts both `--force_reason=` and `--force_replace_reason=`.
- Recorded proof that promote by `--run_id` correctly preserves existing manual-file source context.
- Recorded proof that repeated force replace does not create duplicate current publication state.
- Recorded proof that force replace writes audit event telemetry.

### Test Proof
- `vendor/bin/phpunit tests/Unit/MarketData/MarketDataPipelineIntegrationTest.php` → OK (`51 tests`, `1173 assertions`).
- `vendor/bin/phpunit tests/Unit/MarketData/PublicationRepositoryIntegrationTest.php` → OK (`19 tests`, `93 assertions`).
- `vendor/bin/phpunit tests/Unit/MarketData/OpsCommandSurfaceTest.php` → OK (`42 tests`, `260 assertions`).

Manual execution proof:
- `php artisan market-data:promote --run_id=117 --force_replace=true --force_reason="operator approved replace current publication"` returned `SUCCESS` / `READABLE`, `force_replace=true`, output run `119`.
- `php artisan market-data:promote --run_id=117 --force_replace=true --force_replace_reason="operator approved replace current publication"` returned `SUCCESS` / `READABLE`, `force_replace=true`, output run `120`.

DB/audit proof:
- `select trade_date, count(*) as total_current from eod_publications where trade_date = '2026-03-20' and is_current = 1 group by trade_date;` returned `total_current = 1`.
- Uploaded `eod_publications` proof shows `publication_id=94/run_id=120` as the only current row and prior publications demoted.
- Uploaded `eod_run_events` proof shows `RUN_FORCE_REPLACE_EXECUTED` events for force replace runs.

### Result
- Force Replace & Operator Control is DONE and proven end-to-end.
- Existing valid current without force remains protected.
- Explicit force replace can switch current safely without manual SQL cleanup.
- Pointer/current state remains single-current and deterministic.
- Audit trail exists for force replace execution.

### Contract Impact
- No coverage gate change.
- No correction lifecycle change.
- No read-side enforcement change.
- No schema change.
- No default publish behavior change without force replace.
- The locked Force Replace Operator Control policy is now fully runtime-proven.

### Remaining Gap
- None for Force Replace & Operator Control.

## 2026-04-26 — DB SCHEMA & MIGRATION SYNC POLICY LOCK & EXECUTION SESSION

Status: DONE

### Scope

Executed the DB schema and migration sync pass for market-data, covering schema ownership, migration alignment, SQLite mirror alignment, repository/query usage, schema sync testing, and append-only audit update.

### Changes

- Updated `docs/market_data/db/DB_Schema_And_Migration_Sync_Contract_LOCKED.md` with 2026-04-26 evidence refresh and final drift resolution.
- Updated `tests/Support/UsesMarketDataSqlite.php`:
  - added `eod_reason_codes` table mirror;
  - removed SQLite-only replay source-file fields from `md_replay_daily_metrics`.
- Updated `tests/Unit/MarketData/MarketDataSqliteSchemaSyncTest.php`:
  - added `eod_reason_codes` column assertions;
  - added guard that `md_replay_daily_metrics.source_file_*` fields must not exist as SQLite-only experiment fields.
- No new migration was added because the only runtime-relevant drift was in the SQLite mirror; MariaDB SQL docs/migrations already own source identity on `eod_runs` and `eod_publications`, and replay repository does not write source-file fields to `md_replay_daily_metrics`.

### Test Proof

Static validation executed in this environment:

- `php -l tests/Support/UsesMarketDataSqlite.php` → PASS
- `php -l tests/Unit/MarketData/MarketDataSqliteSchemaSyncTest.php` → PASS

PHPUnit was not executed because this uploaded ZIP does not include `vendor/`.

### Result

DONE. Schema owner policy is explicit, SQLite mirror drift was corrected, schema sync test coverage was hardened, and audit files were updated append-only.

### Contract Impact

DB Schema & Migration Sync remains locked under `OPTION C — LOCKED CONTRACT + RUNTIME RECONCILIATION`. No business behavior was changed.

### Remaining Gap

Local validation still required with full dependencies:

- `vendor/bin/phpunit tests/Unit/MarketData/MarketDataSqliteSchemaSyncTest.php`
- `vendor/bin/phpunit tests/Unit/MarketData/PublicationRepositoryIntegrationTest.php`
- `vendor/bin/phpunit tests/Unit/MarketData/CorrectionRepositoryIntegrationTest.php`
- `vendor/bin/phpunit tests/Unit/MarketData/ReplayResultRepositoryIntegrationTest.php`
- `vendor/bin/phpunit tests/Unit/MarketData/ReadablePublicationReadContractIntegrationTest.php`
- `vendor/bin/phpunit tests/Unit/MarketData/MarketDataPipelineIntegrationTest.php`
- `vendor/bin/phpunit tests/Unit/MarketData/MarketDataEvidenceExportServiceTest.php`
- `vendor/bin/phpunit tests/Unit/MarketData/ReplayVerificationServiceTest.php`
- `vendor/bin/phpunit tests/Unit/MarketData/OpsCommandSurfaceTest.php`

## 2026-04-26 — DB SCHEMA & MIGRATION SYNC RUNTIME DB VALIDATION FOLLOW-UP

Status: PARTIAL

### Scope

Follow-up validation after local PHPUnit passed and operator supplied `Column_and_Index.xlsx` containing runtime MariaDB `SHOW COLUMNS` / `SHOW INDEX` evidence for key market-data tables.

### Changes

- Added `database/migrations/2026_04_26_000001_sync_runtime_db_to_locked_schema_contract.php`.
- Updated `docs/market_data/db/DB_Schema_And_Migration_Sync_Contract_LOCKED.md` with runtime DB validation follow-up evidence and remediation rule.
- Kept SQL schema snapshot unchanged because `Database_Schema_MariaDB.sql` already represents the locked target schema for fresh databases.
- Kept SQLite mirror unchanged because local PHPUnit already proved the SQLite mirror contract after the prior patch.

### Test Proof

Operator local validation passed before this follow-up migration:

- `php -l tests/Support/UsesMarketDataSqlite.php` → PASS.
- `php -l tests/Unit/MarketData/MarketDataSqliteSchemaSyncTest.php` → PASS.
- `vendor/bin/phpunit tests/Unit/MarketData/MarketDataSqliteSchemaSyncTest.php` → OK (`2 tests`, `64 assertions`).
- `vendor/bin/phpunit tests/Unit/MarketData/PublicationRepositoryIntegrationTest.php` → OK (`19 tests`, `93 assertions`).
- `vendor/bin/phpunit tests/Unit/MarketData/CorrectionRepositoryIntegrationTest.php` → OK (`4 tests`, `55 assertions`).
- `vendor/bin/phpunit tests/Unit/MarketData/ReplayResultRepositoryIntegrationTest.php` → OK (`1 test`, `5 assertions`).
- `vendor/bin/phpunit tests/Unit/MarketData/ReadablePublicationReadContractIntegrationTest.php` → OK (`4 tests`, `9 assertions`).
- `vendor/bin/phpunit tests/Unit/MarketData/MarketDataPipelineIntegrationTest.php` → OK (`51 tests`, `1173 assertions`).
- `vendor/bin/phpunit tests/Unit/MarketData/MarketDataEvidenceExportServiceTest.php` → OK (`3 tests`, `44 assertions`).
- `vendor/bin/phpunit tests/Unit/MarketData/ReplayVerificationServiceTest.php` → OK (`5 tests`, `15 assertions`).
- `vendor/bin/phpunit tests/Unit/MarketData/OpsCommandSurfaceTest.php` → OK (`42 tests`, `260 assertions`).

Container static validation after adding the follow-up migration:

- `php -l database/migrations/2026_04_26_000001_sync_runtime_db_to_locked_schema_contract.php` → PASS.

Runtime DB evidence from `Column_and_Index.xlsx` found deployed DB drift:

- `eod_publications.promote_mode` and `eod_publications.publish_target` existed in live DB but are not owned by the locked publication schema/repository.
- `md_replay_daily_metrics` was missing actual replay coverage fields required by locked schema/replay persistence.
- `md_replay_daily_metrics.coverage_ratio` existed as `DECIMAL(6,4)` while locked schema expects `DECIMAL(12,6)`.
- replay/session secondary indexes were not proven present by the workbook and are recreated idempotently by the remediation migration.

### Result

PARTIAL until the new runtime remediation migration is executed locally and DB `SHOW COLUMNS` / `SHOW INDEX` proof is rerun.

### Contract Impact

- DB Schema & Migration Sync remains locked under `OPTION C — LOCKED CONTRACT + RUNTIME RECONCILIATION`.
- Fresh DB path remains governed by `Database_Schema_MariaDB.sql`.
- Existing DB drift must be fixed by forward-only remediation migrations, not by relying on edited SQL snapshots.
- No coverage gate behavior changed.
- No force replace behavior changed.
- No correction lifecycle changed.
- No manual-file publishability changed.
- No read-side enforcement changed.
- No finalize lock behavior changed.
- No publication replacement policy changed.

### Remaining Gap

Run locally:

- `php artisan migrate`
- rerun `SHOW COLUMNS` / `SHOW INDEX` checks for `eod_publications`, `md_replay_daily_metrics`, `md_replay_reason_code_counts`, and `md_session_snapshots`.
- rerun targeted PHPUnit if migration succeeds.

## 2026-04-26 — DB SCHEMA & MIGRATION SYNC RUNTIME DB VALIDATION FINAL

Status: DONE

### Scope

Finalized the DB schema and migration sync session after the operator executed the runtime remediation migration, reran targeted PHPUnit, and supplied fresh MariaDB `SHOW COLUMNS` / `SHOW INDEX` proof in `Column_Index.xlsx`.

### Changes

- Confirmed `database/migrations/2026_04_26_000001_sync_runtime_db_to_locked_schema_contract.php` migrated successfully in the operator environment.
- Confirmed the seven supplied runtime DB evidence sheets now align with the locked schema columns for `eod_runs`, `eod_publications`, `eod_current_publication_pointer`, `eod_dataset_corrections`, `md_replay_daily_metrics`, `md_replay_reason_code_counts`, and `md_session_snapshots`.
- Confirmed runtime indexes shown in the workbook align with locked index intent for the checked tables. Foreign key constraints are not expected to appear in `SHOW INDEX` output and remain validated by schema/migration contract review.
- No additional schema patch was required after the runtime follow-up migration.

### Test Proof

- `php artisan migrate` → migrated `2026_04_26_000001_sync_runtime_db_to_locked_schema_contract` successfully.
- `php -l database/migrations/2026_04_26_000001_sync_runtime_db_to_locked_schema_contract.php` → PASS.
- `vendor/bin/phpunit tests/Unit/MarketData/MarketDataSqliteSchemaSyncTest.php` → OK (`2 tests`, `64 assertions`).
- `vendor/bin/phpunit tests/Unit/MarketData/ReplayResultRepositoryIntegrationTest.php` → OK (`1 test`, `5 assertions`).
- `vendor/bin/phpunit tests/Unit/MarketData/ReplayVerificationServiceTest.php` → OK (`5 tests`, `15 assertions`).
- Prior full local validation in this session also passed: `PublicationRepositoryIntegrationTest`, `CorrectionRepositoryIntegrationTest`, `ReadablePublicationReadContractIntegrationTest`, `MarketDataPipelineIntegrationTest`, `MarketDataEvidenceExportServiceTest`, and `OpsCommandSurfaceTest`.

### Result

DONE. The DB schema sync policy is locked, the SQLite mirror test is hardened, the deployed MariaDB runtime drift has been remediated by forward migration, and the supplied post-migration DB evidence confirms the checked runtime tables now match the locked schema shape.

### Contract Impact

- `DB_Schema_And_Migration_Sync_Contract_LOCKED.md` remains active.
- Final policy remains **OPTION C — LOCKED CONTRACT + RUNTIME RECONCILIATION**.
- Runtime schema drift must be fixed through forward-only migrations.
- SQL snapshot remains the canonical fresh-install full schema.
- SQLite remains a mirror for tested runtime tables, not a field experimentation layer.
- No coverage gate behavior changed.
- No force replace behavior changed.
- No correction lifecycle changed.
- No manual-file publishability changed.
- No read-side enforcement changed.
- No finalize lock behavior changed.
- No publication replacement policy changed.

### Remaining Gap

None for the checked DB schema and migration sync scope.


## 2026-04-26 — READ-SIDE ENFORCEMENT / ANTI BYPASS TOTAL POLICY LOCK & EXECUTION SESSION

Status: DONE

### Scope

Locked the read-side consumer contract for market-data and hardened the repository/test surface so API/service/repository/command/evidence/replay consumer paths must resolve data only through current readable publication pointer semantics.

### Changes

- Added `docs/market_data/book/Read_Side_Enforcement_Anti_Bypass_Contract_LOCKED.md`.
- Added official gateway `EodPublicationRepository::resolveCurrentReadablePublicationForTradeDate($tradeDate)`.
- Rewired existing compatibility methods `findCurrentPublicationForTradeDate()` and `findPointerResolvedPublicationForTradeDate()` to call the official gateway instead of duplicating pointer logic.
- Added `tests/Unit/MarketData/ReadSideAntiBypassStaticContractTest.php` to guard against latest/current shortcut patterns in read-side consumer files.
- Confirmed existing pointer-resolved consumer repositories remain aligned:
  - `EligibilitySnapshotScopeRepository`
  - `EodEvidenceRepository`

### Test Proof

Container syntax proof:

- `php -l app/Infrastructure/Persistence/MarketData/EodPublicationRepository.php` → PASS.
- `php -l tests/Unit/MarketData/ReadSideAntiBypassStaticContractTest.php` → PASS.
- `php -l app/Infrastructure/Persistence/MarketData/EodEvidenceRepository.php` → PASS.
- `php -l app/Infrastructure/Persistence/MarketData/EligibilitySnapshotScopeRepository.php` → PASS.

Static grep classification from this session:

- `MAX(trade_date)` / `MAX(publication_id)` findings in app code → none as executable shortcut. Mentions are contract/test text or gateway comment.
- `EodArtifactRepository` direct artifact table access → `ALLOWED_WRITE_PATH`.
- `EodEvidenceRepository::readableEligibilityQuery()` direct `eod_eligibility` access → allowed because it joins `eod_current_publication_pointer`, validates sealed/current/SUCCESS/READABLE, and therefore is pointer-resolved consumer read.
- `EligibilitySnapshotScopeRepository::getScopeForTradeDate()` direct `eod_eligibility` access → allowed because it joins `eod_current_publication_pointer`, validates sealed/current/SUCCESS/READABLE, and therefore is pointer-resolved consumer read.

PHPUnit was not executed in this container because the uploaded ZIP does not contain `vendor/`.

### Result

DONE. The read-side enforcement policy is locked, the repository gateway is explicit, existing consumer read paths remain pointer/readable-current guarded, and a static anti-bypass test now protects against reintroducing MAX/latest/current shortcuts in consumer files.

### Contract Impact

- New active contract: `Read_Side_Enforcement_Anti_Bypass_Contract_LOCKED.md`.
- Final policy: **OPTION C — Read-Side Contract + Pointer-Only Repository Gateway + Static/Runtime Tests**.
- No coverage gate behavior changed.
- No manual-file publishability behavior changed.
- No correction lifecycle changed.
- No force replace behavior changed.
- No finalize lock behavior changed.
- No publication replacement policy changed.
- No DB schema change was required.

### Remaining Gap

Run local PHPUnit with dependencies installed:

- `vendor/bin/phpunit tests/Unit/MarketData/ReadSideAntiBypassStaticContractTest.php`
- `vendor/bin/phpunit tests/Unit/MarketData/ReadablePublicationReadContractIntegrationTest.php`
- `vendor/bin/phpunit tests/Unit/MarketData/PublicationRepositoryIntegrationTest.php`
- `vendor/bin/phpunit tests/Unit/MarketData/MarketDataEvidenceExportServiceTest.php`
- `vendor/bin/phpunit tests/Unit/MarketData/ReplayVerificationServiceTest.php`
- `vendor/bin/phpunit tests/Unit/MarketData/OpsCommandSurfaceTest.php`
- `vendor/bin/phpunit tests/Unit/MarketData`

## 2026-04-26 — READ-SIDE ENFORCEMENT / LOCAL REGRESSION FOLLOW-UP

Status: DONE

### Scope

Adjusted local unit-test mock expectations after user-provided full market-data PHPUnit evidence showed four `MarketDataPipelineServiceTest` errors caused by stale mocks, not by read-side bypass behavior.

### Changes

- Updated `tests/Unit/MarketData/MarketDataPipelineServiceTest.php` expectations for `promoteCandidateToCurrent($run, $priorRunId, false)` to match the current force-replace-aware service signature.
- Updated two correction/finalize test expectations where `findByRunId(55)` is legitimately called twice through the finalize path.

### Test Proof

User-provided local proof before this follow-up:

- `ReadSideAntiBypassStaticContractTest.php` → PASS, 3 tests / 58 assertions.
- `PublicationRepositoryIntegrationTest.php` → PASS, 19 tests / 93 assertions.
- `ReadablePublicationReadContractIntegrationTest.php` → PASS, 4 tests / 9 assertions.
- `MarketDataEvidenceExportServiceTest.php` → PASS, 3 tests / 44 assertions.
- `ReplayVerificationServiceTest.php` → PASS, 5 tests.
- `OpsCommandSurfaceTest.php` → PASS, 42 tests / 260 assertions.
- Manual scan for `MAX(trade_date)` / `MAX(publication_id)` → no findings.
- Manual scan for raw/current terms only found SQLite test schema definitions.

Container syntax proof after this follow-up:

- `php -l tests/Unit/MarketData/MarketDataPipelineServiceTest.php` → PASS.

### Result

DONE. The remaining regression errors were stale mock expectations in finalize tests. No production read-side enforcement code was weakened.

### Contract Impact

No contract change. Read-side enforcement remains locked under **OPTION C — Read-Side Contract + Pointer-Only Repository Gateway + Static/Runtime Tests**.

### Remaining Gap

Run local full regression again:

- `vendor/bin/phpunit tests/Unit/MarketData/MarketDataPipelineServiceTest.php`
- `vendor/bin/phpunit tests/Unit/MarketData`

## 2026-04-26 — READ-SIDE ENFORCEMENT / ANTI BYPASS TOTAL FINAL RUNTIME VALIDATION

Status: DONE

### Scope

Final local runtime validation for the Read-Side Enforcement / Anti Bypass Total policy lock and execution session after stale `MarketDataPipelineServiceTest` mock expectations were corrected. Scope is proof-only after test synchronization; no read-side policy, repository gateway rule, schema, coverage gate, correction lifecycle, force replace behavior, finalize lock behavior, or publication replacement behavior was changed.

### Changes

- Closed the previous local regression rerun gap recorded in the read-side enforcement follow-up.
- Confirmed `tests/Unit/MarketData/MarketDataPipelineServiceTest.php` was updated so Mockery expectations match the current finalize/promotion flow.
- Confirmed `getOrCreateCandidatePublication(...)`, `promoteCandidateToCurrent(..., false)`, `syncCurrentPublicationMirror(...)`, and repeated `findByRunId(...)` calls are handled by the test expectations.
- No production code change was introduced by this final validation update.

### Test Proof

Operator-provided local validation after applying the revised `MarketDataPipelineServiceTest.php`:

- `php -l tests/Unit/MarketData/MarketDataPipelineServiceTest.php` → PASS.
- `vendor\bin\phpunit tests/Unit/MarketData/MarketDataPipelineServiceTest.php` → PASS (`14 tests`, `17 assertions`).
- `vendor\bin\phpunit tests/Unit/MarketData` → PASS (`225 tests`, `2251 assertions`).

Prior read-side proof from the same session remains valid:

- `ReadSideAntiBypassStaticContractTest.php` → PASS (`3 tests`, `58 assertions`).
- `PublicationRepositoryIntegrationTest.php` → PASS (`19 tests`, `93 assertions`).
- `ReadablePublicationReadContractIntegrationTest.php` → PASS (`4 tests`, `9 assertions`).
- `MarketDataEvidenceExportServiceTest.php` → PASS (`3 tests`, `44 assertions`).
- `ReplayVerificationServiceTest.php` → PASS (`5 tests`).
- `OpsCommandSurfaceTest.php` → PASS (`42 tests`, `260 assertions`).
- Manual scan for `MAX(trade_date)` / `MAX(publication_id)` returned no executable app shortcut findings.
- Manual scan for raw/current terms only found SQLite test schema definitions, classified as `ALLOWED_TEST_SETUP`.

### Result

DONE. Read-Side Enforcement / Anti Bypass Total is now policy-locked, implementation-aligned, static-guarded, focused-test-proven, and full market-data regression-proven locally. The earlier four full-suite errors were stale unit-test mock expectations, not read-side bypass defects.

### Contract Impact

No contract change. `Read_Side_Enforcement_Anti_Bypass_Contract_LOCKED.md` remains active under **OPTION C — Read-Side Contract + Pointer-Only Repository Gateway + Static/Runtime Tests**. Existing coverage gate, manual-file publishability, correction lifecycle, force replace behavior, finalize lock behavior, publication replacement policy, and DB schema sync policy remain unchanged.

### Remaining Gap

None for Read-Side Enforcement / Anti Bypass Total.


## 2026-04-27 — COVERAGE GATE ENFORCEMENT POLICY LOCK & EXECUTION SESSION

Status: DONE

### Scope
Coverage gate enforcement was locked as the single source of truth for finalize, publishability, current pointer eligibility, evidence export, replay verification, and operator command visibility.

### Changes
- Added `docs/market_data/book/Coverage_Gate_Enforcement_Contract_LOCKED.md` and registered it in `docs/market_data/book/INDEX.md`.
- Updated `CoverageGateEvaluator` so empty universe emits coverage gate state `NOT_EVALUABLE` instead of new evaluations using generic `BLOCKED`.
- Updated `FinalizeDecisionService` so `NOT_EVALUABLE` is explicitly non-readable, non-promotable, and maps to `RUN_COVERAGE_NOT_EVALUABLE`.
- Updated pipeline and command reason-code resolution so `NOT_EVALUABLE` and legacy `BLOCKED` both remain fail-safe.
- Updated evidence export to expose `coverage_summary` and `coverage_reason_code` as first-class run evidence fields.
- Updated replay verification to compare `coverage_reason_code` when present in fixture expectations.
- Updated locked MariaDB schema and added a MariaDB enum sync migration for `eod_runs.coverage_gate_state` to allow `NOT_EVALUABLE`.
- Updated unit/integration test expectations from coverage `BLOCKED` to `NOT_EVALUABLE` where coverage itself cannot be evaluated.

### Test Proof
Static validation only in this session because uploaded ZIP does not contain `vendor/`.

Executed locally in this environment:
- PHP syntax scan with `php -l` for all PHP files under `app`, `database`, and `tests`.
- Static grep trace for coverage enforcement paths.

Manual test required on user machine:
- `vendor/bin/phpunit tests/Unit/MarketData`
- `php artisan migrate`
- relevant market-data daily/promote/finalize commands for PASS, FAIL, and NOT_EVALUABLE coverage cases.

### Result
Coverage gate is enforced as full policy: only `PASS` can become readable/current; `FAIL` and `NOT_EVALUABLE` stay non-readable and cannot own current pointer. Evidence and replay now expose/verify coverage reason code explicitly.

### Contract Impact
New locked contract added: `Coverage_Gate_Enforcement_Contract_LOCKED.md`. Existing coverage/finalize/read-side behavior is extended, not relaxed.

### Remaining Gap
No runtime PHPUnit/artisan proof was claimed because `vendor/` is absent from the uploaded ZIP. User must run manual commands locally and provide output if runtime proof is needed.


## 2026-04-27 — COVERAGE GATE ENFORCEMENT FINAL RUNTIME VALIDATION

Status: DONE

### Scope
Final local runtime validation for the Coverage Gate Enforcement implementation after the policy lock and execution patch. Scope is proof-only; no new production behavior, schema policy, threshold rule, read-side rule, correction lifecycle behavior, force replace behavior, or finalize lock behavior was changed by this audit update.

### Changes
- Closed the previous manual-runtime validation gap for the coverage enforcement session.
- Recorded operator-provided local syntax and PHPUnit evidence.
- Confirmed `NOT_EVALUABLE` coverage handling is test-proven.
- Confirmed coverage reason-code propagation is test-proven for evidence export and replay verification.
- Confirmed full market-data unit regression passes after the coverage enforcement changes.

### Test Proof
- `php -l app/Application/MarketData/Services/CoverageGateEvaluator.php` → PASS.
- `php -l app/Application/MarketData/Services/FinalizeDecisionService.php` → PASS.
- `php -l app/Application/MarketData/Services/MarketDataPipelineService.php` → PASS.
- `php -l app/Application/MarketData/Services/MarketDataEvidenceExportService.php` → PASS.
- `php -l app/Application/MarketData/Services/ReplayVerificationService.php` → PASS.
- `vendor/bin/phpunit tests/Unit/MarketData/CoverageGateEvaluatorTest.php` → OK (`4 tests`, `38 assertions`).
- `vendor/bin/phpunit tests/Unit/MarketData/FinalizeDecisionServiceTest.php` → OK (`10 tests`, `54 assertions`).
- `vendor/bin/phpunit tests/Unit/MarketData/MarketDataPipelineIntegrationTest.php` → OK (`51 tests`, `1173 assertions`).
- `vendor/bin/phpunit tests/Unit/MarketData/MarketDataEvidenceExportServiceTest.php` → OK (`3 tests`, `44 assertions`).
- `vendor/bin/phpunit tests/Unit/MarketData/ReplayVerificationServiceTest.php` → OK (`5 tests`, `15 assertions`).
- `vendor/bin/phpunit tests/Unit/MarketData/OpsCommandSurfaceTest.php` → OK (`42 tests`, `260 assertions`).
- `vendor/bin/phpunit tests/Unit/MarketData` → OK (`225 tests`, `2251 assertions`).

### Result
Coverage Gate Enforcement is DONE and locally proven. Coverage `PASS` is required for readable/current publication eligibility. Coverage `FAIL` and `NOT_EVALUABLE` remain fail-safe: non-readable, non-promotable, and not allowed to own the current pointer. Evidence export and replay verification now remain consistent with coverage reason-code enforcement.

### Contract Impact
No contract change. Existing locked coverage enforcement policy remains active. Existing read-side enforcement, manual-file publishability, correction lifecycle, force replace, finalize lock, publication replacement, and DB schema sync policies remain unchanged.

### Remaining Gap
None for Coverage Gate Enforcement.
