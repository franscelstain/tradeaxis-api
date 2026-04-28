# LUMEN_CONTRACT_TRACKER

## FINAL SYSTEM STATUS (LATEST)
- System Invariant Lock / Anti Future Regression -> DONE (GUARD PATCHED + POSITIONING FIX APPLIED + LOCAL PIPELINE REGRESSION PROVEN: 236 TESTS / 2277 ASSERTIONS)
- Publishability vs Coverage vs Fallback Cross-Consistency -> DONE (POLICY LOCKED + FULL ENFORCEMENT + TEST FIXTURES REALIGNED + LOCAL FULL REGRESSION PROVEN: 231 TESTS / 2269 ASSERTIONS)
- Publishability State Integrity -> DONE (POLICY LOCKED + STATE MATRIX ENFORCED + FALLBACK RESTORE VALIDATED + LOCAL FULL REGRESSION PROVEN: 228 TESTS / 2261 ASSERTIONS)

- Coverage Edge Cases -> DONE (POLICY LOCKED + CONTROLLED HOLD/FAILED EDGE HANDLING + LOCAL FULL REGRESSION PROVEN: 227 TESTS / 2259 ASSERTIONS + RUNTIME SOURCE FAILURE REASON PRESERVED)
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

## Traceability / Linkage / Publishability / Correction Guard Session

Status: DONE

## Contract status

1. **source traceability persistence** → DONE
   - source context is persisted in `eod_runs`
   - runtime output and DB fields both prove traceability exists beyond notes/events

2. **run/publication linkage** → DONE
   - `eod_runs.publication_id`
   - `eod_runs.publication_version`
   - `eod_runs.correction_id`
   - `eod_publications.run_id`
   - linkage is explicit and queryable

3. **publishability metadata** → DONE
   - `eod_runs.final_reason_code` persisted
   - terminal/publishability outcome visible in command output and DB
   - coverage gate rejection proven at runtime (`RUN_COVERAGE_LOW` / `NOT_READABLE`)

4. **correction/reseal guard minimum** → DONE
   - correction linkage persists on owning run
   - finalize / correction mismatch paths stay guarded
   - test suite coverage confirms guard behavior remains intact

## Concrete DB contract additions

Added to `eod_runs`:
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
- `publication_id`
- `publication_version`
- `correction_id`
- `final_reason_code`

## Verification completed in this session

### PHPUnit
- `MarketDataBackfillServiceTest` → PASS
- full suite → PASS (`192/192`)

### Runtime command proof
- manual-file daily run completes through FINALIZE
- source trace fields render in command output
- candidate publication linkage appears in notes/output
- coverage gate rejects insufficient manual file correctly

### DB/runtime interpretation
- write-side persistence contract is working
- publication is not silently promoted when publishability fails
- command output, DB persistence, and tests are aligned

## Non-blocking observation

- manual-file range/backfill with a single-date input file is operationally unsuitable for cross-date range ingestion
- this does not invalidate the write-side contract completed in this session

## Final conclusion

SESSION STATUS: DONE
WRITE-SIDE PIPELINE: HARDENED
READ-SIDE: NOT YET ENFORCED

## Next contract target

### READ-SIDE ENFORCEMENT

1. consumer read contract enforcement
2. pointer / effective-date enforcement
3. anti raw-table bypass
4. anti MAX(date)
5. fail-safe read behavior


## Read-side enforcement session

Status: DONE

### Contract status

1. **read contract** → DONE
   - read-side consumers no longer resolve publication through trade-date fallback.
   - owning run must resolve to current sealed readable publication.

2. **pointer enforcement** → DONE
   - eligibility scope/evidence reads are anchored to `eod_current_publication_pointer`.
   - `eod_publications` and `eod_runs` readability constraints are enforced in-query.

3. **anti bypass** → DONE
   - raw `eod_eligibility` trade-date reads without publication contract are removed from consumer read helpers.
   - foreign/non-current publication rows do not leak into evidence/snapshot consumption.

4. **anti MAX(date)** → DONE
   - no consumer read path in this session uses `MAX(date)`, manual latest lookup, or `ORDER BY ... DESC LIMIT 1` to resolve readable data.

5. **fail-safe consumption** → DONE
   - `NOT_READABLE` / non-`SUCCESS` runs now fail fast in read-side evidence/replay consumers.
   - silent fallback to prior/effective-date publication is blocked.

### Concrete files changed

- `app/Application/MarketData/Services/MarketDataEvidenceExportService.php`
- `app/Application/MarketData/Services/ReplayVerificationService.php`
- `app/Infrastructure/Persistence/MarketData/EodPublicationRepository.php`
- `app/Infrastructure/Persistence/MarketData/EodEvidenceRepository.php`
- `app/Infrastructure/Persistence/MarketData/EligibilitySnapshotScopeRepository.php`
- `tests/Unit/MarketData/MarketDataEvidenceExportServiceTest.php`
- `tests/Unit/MarketData/ReplayVerificationServiceTest.php`
- `tests/Unit/MarketData/ReadablePublicationReadContractIntegrationTest.php`

### Final conclusion

SESSION STATUS: DONE
WRITE-SIDE PIPELINE: HARDENED
READ-SIDE: ENFORCED


## Consumer-surface-sweep execution

Status: PARTIAL

### Contract status update from this session

1. **pointer enforcement** → DONE for run/replay evidence surfaces touched here
   - replay evidence export no longer accepts ambiguous selector resolution; exact `trade_date` is mandatory.

2. **anti raw bypass** → DONE for invalid-bars evidence export
   - `eod_invalid_bars` evidence export is now scoped to the owning `run_id`, not just same-day raw rows.

3. **anti MAX(date)** → DONE for replay evidence export path
   - latest-row fallback via `orderByDesc('trade_date')->first()` is no longer reachable from consumer replay evidence export.

4. **fail-safe consumption** → DONE for replay evidence selector
   - command-level fail-fast guard now rejects replay evidence export without explicit `--trade_date`.

### Concrete proof points
- `app/Console/Commands/MarketData/ExportEvidenceCommand.php`
- `app/Application/MarketData/Services/MarketDataEvidenceExportService.php`
- `app/Infrastructure/Persistence/MarketData/EodEvidenceRepository.php`
- `tests/Unit/MarketData/MarketDataEvidenceExportServiceTest.php`
- `tests/Unit/MarketData/OpsCommandSurfaceTest.php`

### Remaining work for full sweep completion
- complete inventory/classification for any remaining downstream consumer surfaces outside evidence/replay/snapshot batch
- run local PHPUnit/regression in vendor-backed environment and record final evidence


## Publication-current-pointer readiness execution

Status: PARTIAL

### Contract status

1. **finalize enforcement** → PARTIAL
   - success-path finalize now must pass strict pointer-resolved current-publication validation before its readable claim is trusted;
   - full runtime proof still pending because vendor-backed local test run was not available inside uploaded ZIP.

2. **publication sync** → DONE in code patch
   - publication switch now rejects sealed-state without `sealed_at`;
   - publication current demotion/restore paths are explicit instead of relying on later service steps.

3. **pointer enforcement** → DONE in code patch
   - success-path mismatch now fails explicit and clears invalid pointer/current state when no safe baseline exists.

4. **run/publication/pointer consistency** → DONE in code patch
   - promotion and restore now sync `eod_runs.publication_id`, `publication_version`, and `is_current_publication` in the same transaction as publication/pointer writes.

5. **fail-safe behavior** → DONE in code patch
   - no-prior-baseline mismatch no longer leaves ambiguous current pointer/current publication behind.

### Files touched

- `app/Application/MarketData/Services/MarketDataPipelineService.php`
- `app/Infrastructure/Persistence/MarketData/EodPublicationRepository.php`
- `tests/Unit/MarketData/MarketDataPipelineIntegrationTest.php`
- `tests/Unit/MarketData/PublicationRepositoryIntegrationTest.php`

### Evidence status

- syntax validation: DONE
- targeted PHPUnit/runtime proof: PARTIAL (missing `vendor/bin/phpunit` inside uploaded ZIP)


### Follow-up regression fix

- Fixed success-path strict pointer validation so it validates against the **resolved current publication contract**, not always the newly created candidate publication.
- This preserves the documented correction behavior for **unchanged artifacts**: correction request is cancelled, prior current publication remains current, and run stays `SUCCESS` / `READABLE`.


## Contract Tracker Update — 2026-04-19 Coverage Boundary Hardening

- **Coverage gate enforcement boundary:** preserved as blocking on promote/finalize path only.
- **Manual file intent classification:** `market-data:daily` is treated as `import_only`; `market-data:promote` is treated as `promote`.
- **Import vs promote separation:** command surface is now aligned with existing pipeline split (`importDaily` vs `promoteDaily`).
- **Publishability safety:** import-only runs remain `NOT_READABLE` and do not switch current publication. Coverage still governs publishability when promote is invoked.
- **Schema impact:** none. Existing `eod_runs` coverage telemetry columns continue to store coverage only when coverage evaluation is actually executed.

## Promote intent classification update — 2026-04-22

Status: DONE IN CODE / PENDING LOCAL RUNTIME PROOF

### Contract result

1. **promote intent classification** → DONE
   - explicit `promote_mode` now exists on run context
   - explicit `publish_target` now exists on run context

2. **coverage policy per intent** → DONE
   - `full_publish/current_replace` remains coverage-blocked
   - `incremental/incremental_candidate` records coverage but does not pretend to be current replacement

3. **manual correction / incremental handling** → PARTIAL/DONE
   - incremental manual promote path is explicit and fail-safe
   - correction path remains current-replacement oriented when a real approved `correction_id` is supplied

4. **current publication protection** → DONE
   - non-current incremental promote never becomes current automatically
   - current readable publication remains authoritative

5. **ops/output clarity** → DONE
   - command output + summary artifact now expose:
     - `promote_mode`
     - `publish_target`

### Concrete DB/schema additions

Added to `eod_runs`:
- `promote_mode`
- `publish_target`

### Operational interpretation

- previous bug: partial `manual_file` promote was implicitly treated as full publish/current replacement
- patched behavior: operator can classify promote intent explicitly, and incremental manual promote now lands as a sealed non-current candidate with `RUN_NON_CURRENT_PROMOTION`
- coverage gate for readable current publication is still intact and unchanged for full publish

- 2026-04-22 follow-up hotfix: promote retry/reclassification now forks a fresh promote run from the persisted import seed instead of reusing a previously finalized promote run. This prevents stale terminal_status/promote_mode from contaminating incremental/correction attempts and keeps the import-only run immutable.

- 2026-04-22 hotfix: promote command no longer pre-binds to latest run before correction validation; correction promote now validates approval before run selection/forking so failed correction requests do not render stale incremental/full-promote run summaries.

## 2026-04-22 — CURRENT PUBLICATION INTEGRITY HARDENING

### Contract decision

**Current publication integrity is now explicitly hardened as a blocking invariant.**

A publication may only remain or become authoritative current state when all of the following are true:

- pointer row exists for trade date
- pointer `publication_id/run_id/publication_version` match publication/run rows
- publication is `is_current = 1`
- publication is `SEALED`
- pointer/publication/run all have sealed timestamps
- run `terminal_status = SUCCESS`
- run `publishability_state = READABLE`
- run `is_current_publication = 1`

### Enforcement added

- raw invalid-current detection is now available in repository layer
- current replacement now refuses to proceed when existing current ownership is internally inconsistent
- finalize now repairs stray current ownership if a non-readable/non-success run is detected as current after finalize flow
- operator command added for explicit remediation of legacy broken current-pointer rows:
  - `market-data:current-publication:repair`

### Notes

- `request_mode` is **not** a persisted `eod_runs` column and remains command/runtime output only
- persisted current-publication integrity relies on:
  - `eod_current_publication_pointer`
  - `eod_publications.is_current`
  - `eod_publications.seal_state`
  - `eod_runs.terminal_status`
  - `eod_runs.publishability_state`
  - `eod_runs.is_current_publication`

## 2026-04-23 — CORRECTION REQUEST RE-EXECUTION POLICY HARDENING

### Contract decision

The correction lifecycle is now **mode-specific** and no longer relies on one shared approval interpretation.

#### correction_current
- purpose: replace current publication safely
- approval requirement: required
- execution rule: single-use current lifecycle
- terminal consumed states:
  - `CONSUMED_CURRENT`
  - `PUBLISHED`
- after terminal current consumption:
  - same `correction_id` cannot be executed again
  - same `correction_id` cannot be approved again

#### repair_candidate
- purpose: iterative non-current repair / reseal attempts
- approval requirement: required initially
- reusable states for rerun:
  - `APPROVED`
  - `EXECUTING`
  - `RESEALED`
  - `REPAIR_ACTIVE`
  - `REPAIR_EXECUTED`
  - `REPAIR_CANDIDATE` (legacy compatibility)
- terminal repair execution state:
  - `REPAIR_EXECUTED`
- repair execution must never auto-promote to current

### Persisted lifecycle telemetry

`eod_dataset_corrections` now carries explicit execution telemetry:
- `execution_count`
- `last_executed_at`
- `current_consumed_at`

### Allowed transition summary

- `REQUESTED -> APPROVED`
- `APPROVED -> EXECUTING` for `correction_current`
- `APPROVED -> REPAIR_ACTIVE` for `repair_candidate`
- `EXECUTING -> RESEALED`
- `REPAIR_ACTIVE -> RESEALED`
- `RESEALED -> PUBLISHED` when current replacement succeeds
- `RESEALED -> CONSUMED_CURRENT` when current-style execution is unchanged/cancelled
- `RESEALED -> REPAIR_EXECUTED` when repair-candidate finalize completes non-current
- `REPAIR_EXECUTED -> APPROVED` only when operator explicitly re-approves for a new controlled execution attempt

### Guardrail result

This session closes the prior ambiguity where repair-candidate flow existed at promote/finalize level but correction approval remained effectively single-use without mode differentiation.

The enforced invariant is now:
- iterative repair is allowed
- current replacement remains single-use and approval-consuming
- repair execution cannot silently overwrite authoritative current publication

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

Locked manual-file publishability behavior against coverage gate and current publication safety.

### Changes

- Added owner contract: `docs/market_data/book/Manual_File_Publishability_Policy_LOCKED.md`.
- Final policy selected: HYBRID STRICT.
- `manual_file` remains coverage-gated and does not bypass coverage.
- `HELD` is confirmed as `terminal_status`, not `publishability_state`.
- `READABLE_WITH_OVERRIDE` is explicitly rejected for this contract version.

### Test Proof

Added `FinalizeDecisionServiceTest` coverage for:
- manual file partial without fallback → `FAILED` + `NOT_READABLE`
- manual file partial with fallback → `HELD` + `NOT_READABLE`
- `allow_partial` context does not create `READABLE_WITH_OVERRIDE`

### Result

Manual-file partial publishability is no longer ambiguous. Import success is separate from publishability success.

### Contract Impact

Coverage gate remains authoritative for readable current publication replacement. Manual file cannot create a reader-authoritative partial current publication.

### Remaining Gap

No override mode exists. Any future override must define a separate consumer/read-side partial-readable contract before implementation.

## 2026-04-24 — Manual File Publishability Test Correction

Status: DONE

### Scope

Aligned test expectation with the locked correction lifecycle behavior for non-current repair candidates.

### Changes

- Repair candidate non-current finalize remains `SUCCESS` because execution completed.
- Repair candidate remains `NOT_READABLE` and `promotion_allowed=false` because it is not current-reader authoritative.
- Manual-file strict hybrid policy remains unchanged.

### Test Proof

User-provided local test run identified only the stale expectation in `FinalizeDecisionServiceTest`; related integration and command-surface filters passed individually.

### Result

Correction lifecycle and manual-file publishability policy are now consistent in unit test expectations.

### Contract Impact

No new publishability state and no coverage override introduced.

### Remaining Gap

Full local PHPUnit rerun pending after this corrected ZIP.

## 2026-04-24 — Source Hash, Reseal Guard & Publication Lineage Hardening

Status: PARTIAL

### Scope

Audit-safety hardening only. No coverage gate, publishability decision, current pointer policy, manual-file policy, correction policy, or read-side enforcement logic was intentionally changed.

### Changes

- Added locked contract: `docs/market_data/book/Publication_Traceability_Immutability_Lineage_LOCKED.md`.
- Added source file identity fields to `eod_runs` and `eod_publications`:
  - `source_file_hash`
  - `source_file_hash_algorithm`
  - `source_file_size_bytes`
  - `source_file_row_count`
- Added publication lineage fields to `eod_publications`:
  - `previous_publication_id`
  - `replaced_publication_id`
- Kept existing compatibility lineage field:
  - `supersedes_publication_id`
- Added repository-level immutable guard for sealed publication hash mutation with reason/message:
  - `SEALED_PUBLICATION_IMMUTABLE`

### Test Proof

Static PHP syntax checks passed for modified PHP files. PHPUnit could not be executed in this environment because `vendor/bin/phpunit` is not present in the uploaded ZIP.

Prepared local PHPUnit target:

```bash
vendor\bin\phpunit tests/Unit/MarketData/PublicationRepositoryIntegrationTest.php --filter "source_identity|immutable"
```

### Result

Contract and code paths for source identity persistence, reseal guard, and lineage metadata are implemented in the ZIP. Runtime proof remains pending until local PHPUnit and artisan/manual DB validation are run with project dependencies installed.

### Contract Impact

This is a contract extension for audit-safety metadata and immutability only. Existing coverage, publishability, current pointer, manual-file, correction, and read-side contracts remain unchanged.

### Remaining Gap

Local runtime validation is still required:
- full publish stores source file identity and version lineage
- partial publish remains non-readable and does not move pointer
- re-publish increments version and records previous/replaced publication
- sealed hash mutation fails with `SEALED_PUBLICATION_IMMUTABLE`

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

Follow-up unit-test synchronization after the latest local run showed only one remaining stale expectation in the unchanged-correction finalize scenario.

### Changes

- Updated `MarketDataPipelineServiceTest` to expect `markConsumedForCurrent()` for unchanged correction reruns that preserve the current publication.
- Explicitly asserted `markCancelled()` is not called in that path.

### Contract Impact

No policy or contract behavior was changed. This records alignment between test expectations and the already-current correction outcome behavior.

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
- Locked finalize determinism, lock-conflict semantics, and current pointer switch consistency.
- Scope intentionally did not redefine coverage gate, manual-file publishability, correction policy, or read-side consumer behavior.

### Changes
- Added `docs/market_data/book/Finalize_Lock_And_Pointer_Behavior_LOCKED.md` as the owner contract for finalize idempotency, `RUN_LOCK_CONFLICT` meaning, and pointer switch safety.
- Updated `docs/market_data/book/INDEX.md` to include the new locked contract under run readiness, effective date, and consumer safety.
- Added explicit finalize idempotency guard in `MarketDataPipelineService::completeFinalize(...)` for already completed FINALIZE runs.
- Added DB-backed integration proof for re-finalize behavior preserving pointer and event count.

### Test Proof
- Static syntax check performed with `php -l` on:
  - `app/Application/MarketData/Services/MarketDataPipelineService.php`
  - `tests/Unit/MarketData/MarketDataPipelineIntegrationTest.php`
- Full PHPUnit could not be executed in this container because `vendor/` is not present in the uploaded ZIP.
- Required local validation command:
  - `vendor/bin/phpunit tests/Unit/MarketData/MarketDataPipelineIntegrationTest.php --filter "finalize|lock|pointer|correction|repair_candidate"`

### Result
- Final policy selected: OPTION C — DETERMINISTIC LOCK.
- `RUN_LOCK_CONFLICT` is locked as a traceable unsafe ownership transition / pointer integrity conflict, not a generic database lock error.
- Re-finalize of a completed run is terminal/idempotent and returns persisted state without duplicate finalize mutation.

### Contract Impact
- New contract is additive and does not change coverage/manual-file/correction/read-side contracts.
- Pointer switch contract now requires promotion eligibility plus post-switch pointer identity match on publication, version, run, and trade date.

### Remaining Gap
- Local PHPUnit and artisan runtime proof must be run by the operator because uploaded ZIP excludes `vendor/`.


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
Final runtime validation after DB schema & migration sync patch and follow-up fixes.

### Changes
- No new schema or contract change.
- This session is pure runtime validation proof.

### Test Proof

#### PHPUnit (FULL PROVEN)
- MarketDataSqliteSchemaSyncTest → PASS
- PublicationRepositoryIntegrationTest → PASS
- CorrectionRepositoryIntegrationTest → PASS
- ReplayResultRepositoryIntegrationTest → PASS
- ReadablePublicationReadContractIntegrationTest → PASS
- MarketDataPipelineIntegrationTest → PASS (49 tests, 1149 assertions)
- MarketDataEvidenceExportServiceTest → PASS
- ReplayVerificationServiceTest → PASS
- OpsCommandSurfaceTest → PASS

#### Runtime (ARTISAN PROOF)

Command:
php artisan market-data:promote --requested_date=2026-03-20 --source_mode=manual_file

Result:
- terminal_status = SUCCESS
- publishability_state = READABLE
- coverage_gate_state = PASS (901/901)
- promote_mode = full_publish
- publish_target = current_replace

Command:
php artisan market-data:run:finalize --requested_date=2026-03-20 --source_mode=manual_file --run_id=115

Result:
- lifecycle_state = COMPLETED
- pointer switch consistent

#### DB Proof

SELECT * FROM eod_current_publication_pointer WHERE trade_date = '2026-03-20';

Result:
- publication_id = 90
- run_id = 115
- publication_version = 22
- sealed_at present
- pointer consistent

### Result
- schema consistency → VERIFIED
- repository compatibility → VERIFIED
- SQLite mirror correctness → VERIFIED
- runtime pipeline → VERIFIED
- publication creation → VERIFIED
- pointer switch → VERIFIED

### Contract Impact
- no contract change
- this is proof that DB Schema Sync contract is correct and enforceable

### Remaining Gap
- publication lock / replacement policy still not explicitly locked
- RUN_LOCK_CONFLICT behavior still implicit (needs next session)

## 2026-04-26 — PUBLICATION LOCK & REPLACEMENT POLICY LOCK & EXECUTION SESSION

Status: DONE (CODE + STATIC PROOF)

### Scope
- Locked publication replacement ownership behavior for EOD market-data current publication and pointer switch.
- Scope intentionally did not change coverage gate, manual-file publishability threshold, correction lifecycle, read-side enforcement, or schema.

### Changes
- Added owner contract: `docs/market_data/book/Publication_Lock_And_Replacement_Policy_LOCKED.md`.
- Registered the new contract in `docs/market_data/book/INDEX.md`.
- Added repository integration proof that ordinary promote cannot replace an existing valid current publication without a controlled prior baseline.

### Test Proof
- Static syntax check:
  - `php -l tests/Unit/MarketData/PublicationRepositoryIntegrationTest.php` → PASS
- PHPUnit was not executed in this container because uploaded ZIP excludes `vendor/`.
- Required local validation:
  - `vendor/bin/phpunit tests/Unit/MarketData/PublicationRepositoryIntegrationTest.php --filter "blocks_uncontrolled_replace|promote_candidate"`
  - `vendor/bin/phpunit tests/Unit/MarketData/MarketDataPipelineIntegrationTest.php --filter "finalize|lock|pointer"`
  - `vendor/bin/phpunit tests/Unit/MarketData/OpsCommandSurfaceTest.php --filter "promote"`

### Result
- Final policy selected: OPTION C — DETERMINISTIC LOCK + EXPLICIT REPLACEMENT.
- `RUN_LOCK_CONFLICT` is explicitly locked as publication ownership conflict, not a generic DB lock.
- Valid current publication cannot be overwritten by ordinary promote.
- Manual-file promote does not bypass current-publication lock.
- Pointer may change only for eligible `SUCCESS` + `READABLE` publication switch.

### Contract Impact
- Adds a dedicated publication replacement owner contract.
- Existing finalize lock contract remains valid and is now narrowed by explicit replacement rules.
- Existing correction/current replacement path remains the controlled replacement mechanism.

### Remaining Gap
- Optional `force_replace` behavior is not implemented and is intentionally not part of this locked policy.
- Any future force replacement needs separate operator contract, audit reason, and runtime telemetry.


## 2026-04-26 — PUBLICATION LOCK & REPLACEMENT POLICY FINAL RUNTIME VALIDATION

Status: DONE (PROVEN)

### Scope
Final runtime validation for deterministic publication lock & replacement behavior.

### Test Proof
- PublicationRepositoryIntegrationTest → PASS
- MarketDataPipelineIntegrationTest → PASS
- OpsCommandSurfaceTest → PASS

### Runtime Proof
- existing current → HELD (RUN_LOCK_CONFLICT)
- after cleanup → SUCCESS (READABLE)

### Result
Deterministic lock & replacement proven in runtime.

### Contract Impact
No change.

### Remaining Gap
force_replace not implemented

## 2026-04-26 — CORRECTION LIFECYCLE TEST HARDENING SESSION

Status: DONE (TEST HARDENED)

### Scope
- correction_current consumption enforcement
- repair_candidate rerun proof inventory
- lifecycle metadata guard validation

### Changes
- Added explicit repository-level single-use regression test for correction_current after `markConsumedForCurrent()`.
- Preserved existing repair_candidate rerun contract tests and pipeline non-current candidate tests.
- No contract definition was changed.

### Test Proof
- `php -l tests/Unit/MarketData/CorrectionRepositoryIntegrationTest.php` → PASS in this container.
- PHPUnit must be run locally because `vendor/` is absent from uploaded ZIP.

### Result
- correction_current stays single-use.
- consumed correction_current cannot be executed again.
- consumed correction_current cannot be approved again.
- repair_candidate remains isolated from current publication promotion.

### Contract Impact
- Contract remains immutable.
- Added enforcement proof only.

### Remaining Gap
- none for correction lifecycle contract proof.

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

## 2026-04-26 — FORCE REPLACE AUDIT PAYLOAD PATCH

### Contract Status
- Force Replace Operator Control policy remains LOCKED.
- No policy behavior changed.

### Clarification
- For full-publish force replace, previous current publication may be resolved inside `EodPublicationRepository::promoteCandidateToCurrent()` rather than from correction baseline context.
- Audit payload must still record the replaced publication ID, using the promoted candidate's persisted `previous_publication_id` when needed.

### Validation Impact
- This is a test-feedback implementation fix, not a contract change.
- Test doubles must preserve the full repository method signature including `$forceReplace`.

---

## SESSION PATCH — FORCE REPLACE OPERATOR PROMOTE COMMAND SURFACE FIX

Status: DONE

### Scope

Follow-up operator execution proved the Force Replace policy implementation needed command-surface hardening:
- `--force_reason` alias was absent even though operator execution text used it.
- `--run_id` promote without explicit `--source_mode` could violate source immutability by falling back to default source mode.

### Changes

- `market-data:promote` now accepts both:
  - `--force_replace_reason=` as canonical option;
  - `--force_reason=` as operator-friendly alias.
- `market-data:promote --run_id=<id>` now derives the existing run context when `--source_mode` is omitted:
  - requested date comes from the existing run;
  - source mode comes from the existing run.

### Test Proof

Local PHPUnit proof from operator before this follow-up patch:
- `MarketDataPipelineIntegrationTest.php` → OK (51 tests, 1173 assertions)
- `PublicationRepositoryIntegrationTest.php` → OK (19 tests, 93 assertions)
- `OpsCommandSurfaceTest.php` → OK (40 tests, 254 assertions)

Container syntax proof:
- `php -l app/Console/Commands/MarketData/PromoteMarketDataCommand.php` → PASS
- `php -l tests/Unit/MarketData/OpsCommandSurfaceTest.php` → PASS

### Result

- Force Replace remains explicit and audit-reasoned.
- Command execution no longer forces operator to use manual SQL or repeat source/date context for a known run.
- Source immutability remains enforced; the command now passes the correct immutable source for existing run IDs.

### Contract Impact

- Policy behavior unchanged.
- This is an execution-surface alignment with the locked policy.
- `--force_replace_reason=` remains canonical; `--force_reason=` is accepted as compatible alias.

### Remaining Gap

- Operator must rerun targeted PHPUnit locally after applying this patch.


## 2026-04-26 — FORCE REPLACE & OPERATOR CONTROL FINAL RUNTIME VALIDATION

Status: DONE

### Scope
- Final local runtime validation for the locked Force Replace & Operator Control policy.
- Scope is proof-only after the command-surface fix.
- No coverage gate, correction lifecycle, read-side enforcement, schema, or default non-force publish behavior was changed.

### Changes
- Updated audit status to close the previous command-surface local-rerun gap.
- Recorded local PHPUnit proof for pipeline, repository, and ops command surfaces.
- Recorded manual operator execution proof for both canonical and alias force-replace reason options.
- Recorded DB proof that current publication remains single-current after repeated force replace execution.
- Recorded audit-event proof that `RUN_FORCE_REPLACE_EXECUTED` is persisted for force replace runs.

### Test Proof
- `vendor/bin/phpunit tests/Unit/MarketData/MarketDataPipelineIntegrationTest.php` → PASS (`51 tests`, `1173 assertions`).
- `vendor/bin/phpunit tests/Unit/MarketData/PublicationRepositoryIntegrationTest.php` → PASS (`19 tests`, `93 assertions`).
- `vendor/bin/phpunit tests/Unit/MarketData/OpsCommandSurfaceTest.php` → PASS (`42 tests`, `260 assertions`).
- Manual command proof:
  - `php artisan market-data:promote --run_id=117 --force_replace=true --force_reason=...` → `SUCCESS` / `READABLE`, `force_replace=true`, output run `119`.
  - `php artisan market-data:promote --run_id=117 --force_replace=true --force_replace_reason=...` → `SUCCESS` / `READABLE`, `force_replace=true`, output run `120`.
- DB proof supplied by operator:
  - `eod_publications` for `2026-03-20` has exactly one `is_current = 1`.
  - `publication_id=94`, `run_id=120`, `is_current=1`.
  - `publication_id=93`, `run_id=119`, `is_current=0`.
  - `publication_id=92`, `run_id=117`, `is_current=0`.
  - `eod_run_events` contains `RUN_FORCE_REPLACE_EXECUTED` for force replace runs `119` and `120`.

### Result
- Force Replace & Operator Control is fully proven.
- Manual SQL cleanup is no longer required for operator-approved current replacement.
- Pointer/current state remains deterministic and single-current after repeated force replace.
- Previous publications remain stored and are demoted, not deleted.
- Audit event records the operator action.

### Contract Impact
- No new contract change.
- Existing `Force_Replace_Operator_Control_Policy_LOCKED.md` is now validated by local PHPUnit, command execution, DB state, and audit events.
- `--force_replace_reason=` remains canonical.
- `--force_reason=` remains accepted alias for operator execution compatibility.

### Remaining Gap
- None for Force Replace & Operator Control.

## 2026-04-26 — DB SCHEMA & MIGRATION SYNC POLICY LOCK & EXECUTION SESSION

Status: DONE

### Scope

Locked and refreshed market-data DB schema synchronization governance across MariaDB schema docs, Laravel/Lumen migrations, SQLite test mirror, repository query usage, schema sync PHPUnit coverage, and audit records.

### Changes

- Reconfirmed `docs/market_data/db/DB_Schema_And_Migration_Sync_Contract_LOCKED.md` policy as `OPTION C — LOCKED CONTRACT + RUNTIME RECONCILIATION`.
- Confirmed `docs/market_data/db/Database_Schema_MariaDB.sql` is the canonical full MariaDB schema snapshot and is executed directly by the core market-data migration.
- Added `eod_reason_codes` to `tests/Support/UsesMarketDataSqlite.php` because it existed in MariaDB schema but was missing from SQLite mirror.
- Removed SQLite-only `md_replay_daily_metrics.source_file_hash`, `source_file_hash_algorithm`, `source_file_size_bytes`, and `source_file_row_count` from the SQLite mirror because replay repository persistence does not write these fields and MariaDB schema/migrations do not own them for replay metrics.
- Extended `tests/Unit/MarketData/MarketDataSqliteSchemaSyncTest.php` to prove `eod_reason_codes` mirror coverage and reject SQLite-only replay source-file fields.

### Test Proof

Static validation executed in this environment:

- `php -l tests/Support/UsesMarketDataSqlite.php` → PASS
- `php -l tests/Unit/MarketData/MarketDataSqliteSchemaSyncTest.php` → PASS

PHPUnit was not executed because this uploaded ZIP does not include `vendor/`.

### Result

DB schema sync contract remains DONE. Current resolved drift is limited to SQLite mirror correction and test hardening; no new runtime migration was required because no MariaDB runtime field was missing for current repository usage.

### Contract Impact

- No change to coverage gate.
- No change to force replace behavior.
- No change to correction lifecycle.
- No change to manual-file publishability.
- No change to read-side enforcement.
- No change to finalize lock behavior.
- No change to publication replacement policy.
- SQLite is reaffirmed as a mirror, not a field experimentation layer.

### Remaining Gap

Run local PHPUnit and manual DB validation against the developer environment:

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

Recorded operator-provided runtime DB column/index evidence after the DB schema sync PHPUnit suite passed locally.

### Changes

- Added a forward-only runtime remediation migration for existing MariaDB databases that drifted from the locked schema snapshot.
- Added runtime follow-up evidence to `DB_Schema_And_Migration_Sync_Contract_LOCKED.md`.
- Preserved the locked authority model: contract docs + SQL full schema snapshot + migrations + SQLite mirror + repository usage.

### Test Proof

Local operator PHPUnit proof before the follow-up migration:

- `MarketDataSqliteSchemaSyncTest` → PASS (`2 tests`, `64 assertions`).
- `PublicationRepositoryIntegrationTest` → PASS (`19 tests`, `93 assertions`).
- `CorrectionRepositoryIntegrationTest` → PASS (`4 tests`, `55 assertions`).
- `ReplayResultRepositoryIntegrationTest` → PASS (`1 test`, `5 assertions`).
- `ReadablePublicationReadContractIntegrationTest` → PASS (`4 tests`, `9 assertions`).
- `MarketDataPipelineIntegrationTest` → PASS (`51 tests`, `1173 assertions`).
- `MarketDataEvidenceExportServiceTest` → PASS (`3 tests`, `44 assertions`).
- `ReplayVerificationServiceTest` → PASS (`5 tests`, `15 assertions`).
- `OpsCommandSurfaceTest` → PASS (`42 tests`, `260 assertions`).

Container syntax proof after remediation migration:

- `php -l database/migrations/2026_04_26_000001_sync_runtime_db_to_locked_schema_contract.php` → PASS.

### Result

Runtime DB validation changed the DB sync session from closed validation to follow-up remediation. The codebase now contains the migration needed to reconcile existing developer/runtime DBs to the locked schema.

### Contract Impact

- `DB_Schema_And_Migration_Sync_Contract_LOCKED.md` remains the active DB schema governance contract.
- Existing DB schema drift must be reconciled by idempotent forward migrations.
- DB-only orphan fields are not allowed to become contract fields without repository/runtime need and a locked policy decision.

### Remaining Gap

Execute the new migration locally and provide fresh DB column/index proof.

## 2026-04-26 — DB SCHEMA & MIGRATION SYNC RUNTIME DB VALIDATION FINAL

Status: DONE

### Scope

Finalized the DB schema and migration sync session after the operator executed the runtime remediation migration, reran targeted PHPUnit, and supplied fresh MariaDB `SHOW COLUMNS` / `SHOW INDEX` proof in `Column_Index.xlsx`.

### Changes

- Confirmed the runtime remediation migration executed successfully.
- Confirmed the supplied runtime DB evidence aligns with locked schema columns and checked index intent for the seven reviewed tables.
- No additional schema patch was required after the remediation migration.

### Test Proof

- `php artisan migrate` → migrated `2026_04_26_000001_sync_runtime_db_to_locked_schema_contract` successfully.
- `php -l database/migrations/2026_04_26_000001_sync_runtime_db_to_locked_schema_contract.php` → PASS.
- `MarketDataSqliteSchemaSyncTest` → OK (`2 tests`, `64 assertions`).
- `ReplayResultRepositoryIntegrationTest` → OK (`1 test`, `5 assertions`).
- `ReplayVerificationServiceTest` → OK (`5 tests`, `15 assertions`).

### Result

DONE. DB schema and migration sync contract is locked, implemented, migrated locally, and validated against the supplied post-migration DB evidence.

### Contract Impact

- Final policy remains **OPTION C — LOCKED CONTRACT + RUNTIME RECONCILIATION**.
- Runtime schema drift must be fixed through forward-only migrations.
- SQL snapshot remains canonical for fresh install.
- SQLite remains a test mirror, not a field experimentation layer.

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

Captured local validation results and fixed stale unit-test expectations related to finalize mock signatures and run hydration calls.

### Changes

- No contract rule changed.
- Test expectations now align with existing force-replace-aware finalize behavior.

### Test Proof

User local validation proved the read-side anti-bypass contract tests and focused integration tests pass. The only full regression failures were stale mocks in `MarketDataPipelineServiceTest`, now updated syntactically.

### Result

DONE. Contract remains active and unchanged.

### Contract Impact

No change to coverage gate, correction lifecycle, force replace behavior, finalize lock behavior, schema sync, or read-side enforcement policy.

### Remaining Gap

Re-run full market-data PHPUnit locally after applying the updated ZIP.

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
Locked coverage gate as the controlling contract for finalize outcome, publishability, current pointer ownership, evidence export, replay verification, and command visibility.

### Changes
- New contract: `docs/market_data/book/Coverage_Gate_Enforcement_Contract_LOCKED.md`.
- Coverage statuses locked to `PASS`, `FAIL`, and `NOT_EVALUABLE` for new evaluations.
- `BLOCKED` remains backward-compatible as a quality/readiness state only.
- Finalize decision contract now treats `FAIL` and `NOT_EVALUABLE` as non-readable and non-promotable.
- Evidence contract now requires `coverage_summary` and `coverage_reason_code`.
- Replay contract now compares `coverage_reason_code` when fixtures declare it.
- Schema contract updated to support `NOT_EVALUABLE` in `eod_runs.coverage_gate_state`.

### Test Proof
Static validation only; no PHPUnit/artisan runtime claim because `vendor/` is not in the ZIP.

Manual proof required:
- `php artisan migrate`
- `vendor/bin/phpunit tests/Unit/MarketData`
- replay/evidence command checks using fixtures with coverage PASS, FAIL, and NOT_EVALUABLE.

### Result
Coverage gate is no longer metadata-only. It is the authoritative enforcement gate for readable/current publication eligibility.

### Contract Impact
Coverage enforcement is now locked as a full enforcement policy. Any future override, partial coverage acceptance, or manual-file exception requires a new explicit locked contract.

### Remaining Gap
Runtime command output must be supplied by the user after running the manual validation commands locally.


## 2026-04-27 — COVERAGE GATE ENFORCEMENT FINAL RUNTIME VALIDATION

Status: DONE

### Scope
Final local runtime validation for the Coverage Gate Enforcement Policy Lock & Execution Session. Scope is proof-only after the coverage enforcement patch; no additional production behavior, schema rule, threshold rule, read-side rule, correction lifecycle rule, force replace behavior, or finalize lock behavior was changed in this audit update.

### Changes
- Closed the previous runtime validation gap for Coverage Gate Enforcement.
- Recorded operator-provided local syntax and PHPUnit evidence.
- Confirmed coverage gate enforcement remains the controlling contract for finalize, publishability, current pointer eligibility, evidence export, replay verification, and command visibility.
- Confirmed full market-data unit regression passes after adding `NOT_EVALUABLE` handling and coverage reason-code evidence/replay comparison.

### Test Proof
- `php -l app/Application/MarketData/Services/CoverageGateEvaluator.php` → PASS.
- `php -l app/Application/MarketData/Services/FinalizeDecisionService.php` → PASS.
- `php -l app/Application/MarketData/Services/MarketDataPipelineService.php` → PASS.
- `php -l app/Application/MarketData/Services/MarketDataEvidenceExportService.php` → PASS.
- `php -l app/Application/MarketData/Services/ReplayVerificationService.php` → PASS.
- `vendor/bin/phpunit tests/Unit/MarketData/CoverageGateEvaluatorTest.php` → PASS (`4 tests`, `38 assertions`).
- `vendor/bin/phpunit tests/Unit/MarketData/FinalizeDecisionServiceTest.php` → PASS (`10 tests`, `54 assertions`).
- `vendor/bin/phpunit tests/Unit/MarketData/MarketDataPipelineIntegrationTest.php` → PASS (`51 tests`, `1173 assertions`).
- `vendor/bin/phpunit tests/Unit/MarketData/MarketDataEvidenceExportServiceTest.php` → PASS (`3 tests`, `44 assertions`).
- `vendor/bin/phpunit tests/Unit/MarketData/ReplayVerificationServiceTest.php` → PASS (`5 tests`, `15 assertions`).
- `vendor/bin/phpunit tests/Unit/MarketData/OpsCommandSurfaceTest.php` → PASS (`42 tests`, `260 assertions`).
- `vendor/bin/phpunit tests/Unit/MarketData` → PASS (`225 tests`, `2251 assertions`).

### Result
Coverage Gate Enforcement is fully proven by local PHPUnit regression. Coverage `PASS` remains the only readable/current-eligible state; `FAIL` and `NOT_EVALUABLE` remain non-readable and non-promotable. Evidence export and replay verification are aligned with coverage reason-code enforcement.

### Contract Impact
No contract change. `Coverage_Gate_Enforcement_Contract_LOCKED.md` remains active as the single source of truth for coverage-controlled publishability and current publication eligibility. Any future override, partial coverage acceptance, or manual-file exception still requires a separate explicit locked contract.

### Remaining Gap
None for Coverage Gate Enforcement.

## 2026-04-27 - Coverage Edge Cases Policy Lock & Execution Session

Status: DONE

### Scope
Locked and implemented controlled handling for coverage edge cases: multi-source boundary, partial dataset, delayed data window, retry exhaustion, stale data, specific reason-code mapping, and fallback safety. Coverage Gate threshold behavior was not weakened.

### Changes
- Added `docs/market_data/book/Coverage_Edge_Cases_Contract_LOCKED.md`.
- Added coverage edge-case config block in `config/market_data.php`:
  - `MARKET_DATA_COVERAGE_DELAY_WINDOW_MINUTES`
  - `MARKET_DATA_MULTI_SOURCE_MODE`
  - `MARKET_DATA_ALLOW_MIXED_SOURCES`
- Updated `FinalizeDecisionService` so coverage `FAIL` can produce specific locked reason codes:
  - `RUN_PARTIAL_DATA`
  - `RUN_DATA_DELAYED`
  - `RUN_STALE_DATA`
  - fallback to `RUN_COVERAGE_LOW` when no specific edge applies.
- Updated `MarketDataPipelineService` to classify coverage-fail edge context before finalize decision:
  - delay-window fail -> `RUN_DATA_DELAYED`
  - partial available count below expected universe -> `RUN_PARTIAL_DATA`
  - otherwise -> `RUN_COVERAGE_LOW`.
- Updated `EodBarsIngestService` to reject rows outside requested single-day trade-date boundary as `RUN_STALE_DATA` instead of treating stale rows as available.
- Updated reason-code registry seed with `RUN_PARTIAL_DATA`, `RUN_DATA_DELAYED`, and `RUN_STALE_DATA`.
- Added unit coverage in `FinalizeDecisionServiceTest` for partial and delayed edge decisions.

### Test Proof
Static validation performed in this environment only because uploaded ZIP does not contain `vendor/`:
- `php -l app/Application/MarketData/Services/FinalizeDecisionService.php` -> PASS.
- `php -l app/Application/MarketData/Services/MarketDataPipelineService.php` -> PASS.
- `php -l app/Application/MarketData/Services/EodBarsIngestService.php` -> PASS.
- `php -l config/market_data.php` -> PASS.
- `php -l tests/Unit/MarketData/FinalizeDecisionServiceTest.php` -> PASS.

Manual validation still required locally:
- `vendor/bin/phpunit tests/Unit/MarketData/FinalizeDecisionServiceTest.php`
- `vendor/bin/phpunit tests/Unit/MarketData/PublicApiEodBarsAdapterTest.php`
- `vendor/bin/phpunit tests/Unit/MarketData/MarketDataPipelineIntegrationTest.php`
- `vendor/bin/phpunit tests/Unit/MarketData/OpsCommandSurfaceTest.php`
- `vendor/bin/phpunit tests/Unit/MarketData`

### Result
Coverage Edge Cases are now contract-locked and implemented as controlled non-readable outcomes. Coverage `PASS` remains the only READABLE path. Partial, delayed, stale, timeout, and rate-limit conditions do not bypass coverage and do not promote partial/stale candidates to current readable publication.

### Contract Impact
New locked contract added. Existing Coverage Gate Enforcement remains authoritative and unchanged: coverage `PASS` is required for `SUCCESS + READABLE`. Edge cases only refine HOLD/FAILED reason-code behavior and do not create a coverage override.

### Remaining Gap
Local PHPUnit/artisan runtime proof must be executed by operator because `vendor/` is not present in the uploaded ZIP.


## 2026-04-27 — COVERAGE EDGE CASES FINAL RUNTIME VALIDATION

Status: DONE

### Scope
Final local runtime validation and follow-up hardening for the Coverage Edge Cases Policy Lock & Execution Session. Scope covers controlled handling for partial dataset, delayed/retry/source failure, stale data rejection, fallback safety, reason-code specificity, and finalize reason-code precedence when source acquisition fails.

### Changes
- Closed the previous local PHPUnit/runtime proof gap for Coverage Edge Cases.
- Updated low-coverage integration expectations so partial dataset cases assert `RUN_PARTIAL_DATA`, not generic `RUN_COVERAGE_LOW`.
- Hardened finalize decision context so source failure reason code is preserved when coverage is `NOT_EVALUABLE` or cutoff would otherwise return `RUN_FINALIZE_BEFORE_CUTOFF`.
- Updated `MarketDataPipelineService::completeFinalize(...)` to pass source failure context from persisted run notes (`source_final_reason_code=...`) into `FinalizeDecisionService`.
- Added note parsing helper for persisted run notes so import-only source failure context survives later finalize-by-run execution.
- Confirmed evidence export remains blocked for `HELD` / `NOT_READABLE` runs, as required by read-side enforcement.

### Test Proof
Operator-provided local validation after applying the final runtime patch:

- `vendor/bin/phpunit tests/Unit/MarketData/CoverageGateEvaluatorTest.php` → OK (`4 tests`, `38 assertions`).
- `vendor/bin/phpunit tests/Unit/MarketData/FinalizeDecisionServiceTest.php` → OK (`12 tests`, `62 assertions`).
- `vendor/bin/phpunit tests/Unit/MarketData/MarketDataPipelineServiceTest.php` → OK (`14 tests`, `17 assertions`).
- `vendor/bin/phpunit tests/Unit/MarketData/MarketDataPipelineIntegrationTest.php` → OK (`51 tests`, `1173 assertions`).
- `vendor/bin/phpunit tests/Unit/MarketData/EodBarsIngestServiceTest.php` → OK (`4 tests`, `31 assertions`).
- `vendor/bin/phpunit tests/Unit/MarketData/OpsCommandSurfaceTest.php` → OK (`42 tests`, `260 assertions`).
- `vendor/bin/phpunit tests/Unit/MarketData/MarketDataEvidenceExportServiceTest.php` → OK (`3 tests`, `44 assertions`).
- `vendor/bin/phpunit tests/Unit/MarketData` → OK (`227 tests`, `2259 assertions`).
- `Get-ChildItem -Recurse app,tests -Filter *.php | ForEach-Object { php -l $_.FullName }` → PASS for all listed PHP files.

Runtime command proof:

- `php artisan market-data:daily --requested_date=2026-04-21 --source_mode=api` → `HELD` / `NOT_READABLE`, `reason_code=RUN_SOURCE_RATE_LIMIT`, `source_final_reason_code=RUN_SOURCE_RATE_LIMIT`, `fallback_trade_date=2026-03-20`.
- `php artisan market-data:run:finalize --run_id=124` → `HELD` / `NOT_READABLE`, `reason_code=RUN_SOURCE_RATE_LIMIT`.
- `php artisan market-data:evidence:export --run_id=121` rejected non-readable evidence export with: `Run evidence export requires a SUCCESS + READABLE run; non-readable runs cannot be consumed through publication read path.`

### Result
Coverage Edge Cases are fully proven. Coverage `PASS` remains the only `SUCCESS + READABLE` path. Partial dataset maps to `RUN_PARTIAL_DATA`. API rate-limit/source failure remains `HELD + NOT_READABLE` and now preserves `RUN_SOURCE_RATE_LIMIT` through finalize instead of being masked by cutoff or coverage-not-evaluable logic. Fallback remains deterministic to previous readable publication only.

### Contract Impact
No relaxation to Coverage Gate Enforcement. The Coverage Edge Cases contract is now locally proven and runtime-hardened. Edge cases refine non-readable HOLD/FAILED reason-code behavior only; they do not create a partial-readable override and do not allow stale or partial data to become current readable publication.

### Remaining Gap
None for Coverage Edge Cases.

## 2026-04-27 — PUBLISHABILITY STATE INTEGRITY POLICY LOCK & EXECUTION SESSION

Status: DONE

### Scope
Locked publishability state integrity across `eod_runs`, `eod_publications`, and `eod_current_publication_pointer`, with deterministic fallback limited to prior readable publications.

### Changes
- Added `docs/market_data/book/Publishability_State_Integrity_Contract_LOCKED.md`.
- Registered the contract in `docs/market_data/book/INDEX.md`.
- Enforced state matrix return guards in `FinalizeDecisionService`.
- Enforced state matrix return guards in `PublicationFinalizeOutcomeService`.
- Hardened `EodPublicationRepository::restorePriorCurrentPublication()` so fallback restore rejects prior publication/run combinations that are not sealed `SUCCESS + READABLE`.
- Added integration test coverage for rejected fallback restore to a NOT_READABLE/HELD prior run.

### Test Proof
- Static lint performed in this workspace only:
  - `php -l app/Application/MarketData/Services/FinalizeDecisionService.php`
  - `php -l app/Application/MarketData/Services/PublicationFinalizeOutcomeService.php`
  - `php -l app/Infrastructure/Persistence/MarketData/EodPublicationRepository.php`
  - `php -l tests/Unit/MarketData/PublicationRepositoryIntegrationTest.php`
- PHPUnit/artisan were not executed because uploaded ZIP has no `vendor/`.

### Result
Publishability state now has an explicit locked matrix. `READABLE` requires `SUCCESS`; `FAILED` and `HELD` are forced to remain `NOT_READABLE`; fallback restore cannot point current pointer to a prior publication whose owning run is not `SUCCESS + READABLE`.

### Contract Impact
New locked contract becomes the owner rule for publishability state integrity and supplements existing pointer/read-side/finalize contracts without replacing them.

### Remaining Gap
Manual local validation still required with `vendor/bin/phpunit tests/Unit/MarketData` and relevant artisan finalize/evidence/replay commands because dependencies are not present in the uploaded ZIP.

## 2026-04-27 — PUBLISHABILITY STATE INTEGRITY FINAL RUNTIME VALIDATION

Status: DONE

### Scope
Final local runtime validation for Publishability State Integrity after policy lock and code guard enforcement. Scope is proof-only after prior static validation and covers consistency across `eod_runs`, `eod_publications`, and `eod_current_publication_pointer`.

### Changes
- Closed the previous static-only validation gap.
- Recorded operator-provided full local PHPUnit regression proof.
- Confirmed state matrix enforcement across:
  - `FinalizeDecisionService`
  - `PublicationFinalizeOutcomeService`
  - `EodPublicationRepository`
- Confirmed fallback restore rejects prior publication/run combinations that are not `SUCCESS + READABLE`.
- Confirmed no production behavior, schema rule, coverage rule, read-side rule, correction lifecycle rule, force replace behavior, or finalize lock behavior was changed by this proof update.

### Test Proof
Operator-provided local validation:

- `php artisan migrate` → Nothing to migrate.
- `vendor/bin/phpunit tests/Unit/MarketData/PublicationRepositoryIntegrationTest.php` → OK (`20 tests`, `95 assertions`).
- `vendor/bin/phpunit tests/Unit/MarketData/FinalizeDecisionServiceTest.php` → OK (`12 tests`, `62 assertions`).
- `vendor/bin/phpunit tests/Unit/MarketData/PublicationFinalizeOutcomeServiceTest.php` → OK (`8 tests`, `39 assertions`).
- `vendor/bin/phpunit tests/Unit/MarketData/MarketDataPipelineIntegrationTest.php --filter finalize` → OK (`3 tests`, `35 assertions`).
- `vendor/bin/phpunit tests/Unit/MarketData/OpsCommandSurfaceTest.php` → OK (`42 tests`, `260 assertions`).
- `vendor/bin/phpunit tests/Unit/MarketData` → OK (`228 tests`, `2261 assertions`).

Manual scan proof:

- `Select-String -Path app/**/*.php,tests/**/*.php -Pattern "READABLE","NOT_READABLE","publishability_state","terminal_status","is_current" -CaseSensitive:$false`
- Result only surfaced SQLite/test-support schema definitions for `terminal_status`, `publishability_state`, `is_current_publication`, and `is_current`.

### Result
Publishability State Integrity is fully runtime-proven.

Validated outcomes:
- `SUCCESS + READABLE` may become current.
- `SUCCESS + NOT_READABLE` cannot become current.
- `FAILED + NOT_READABLE` cannot become current.
- `HELD + NOT_READABLE` cannot become current.
- Pointer cannot reference non-readable publication.
- Fallback restore is limited to prior `SUCCESS + READABLE` publication.

### Contract Impact
No contract definition changed. `Publishability_State_Integrity_Contract_LOCKED.md` remains the owner contract for the publishability state matrix, pointer eligibility, current publication eligibility, and fallback eligibility.

### Remaining Gap
None for Publishability State Integrity.

## 2026-04-27 — PUBLISHABILITY vs COVERAGE vs FALLBACK CROSS-CONSISTENCY POLICY LOCK & EXECUTION SESSION

Status: DONE

### Scope
Locked and fully enforced cross-consistency between coverage gate, finalize decision, publishability state, fallback restore, and current publication pointer eligibility.

### Changes
- Created `docs/market_data/book/Publishability_Coverage_Fallback_Cross_Consistency_Contract_LOCKED.md`.
- Registered the new locked contract in `docs/market_data/book/INDEX.md`.
- Hardened `FinalizeDecisionService` so `READABLE` requires `coverage_gate_status=PASS` and `terminal_status=SUCCESS`.
- Hardened `PublicationFinalizeOutcomeService` so final readable outcomes cannot be produced unless coverage is PASS.
- Hardened `EodPublicationRepository` read-side pointer resolution so current/readable/baseline/fallback queries require `run.coverage_gate_state=PASS` in addition to `SUCCESS + READABLE`.
- Hardened current promotion and fallback restore so pointer writes require:
  - `terminal_status=SUCCESS`
  - `publishability_state=READABLE`
  - `coverage_gate_state=PASS`
- Added integration/unit test coverage for pointer rejection when prior/current coverage is not PASS and outcome rejection when readable is attempted with coverage FAIL.

### Test Proof
Operator local validation:

- `vendor/bin/phpunit tests/Unit/MarketData/FinalizeDecisionServiceTest.php` → OK
- `vendor/bin/phpunit tests/Unit/MarketData/PublicationFinalizeOutcomeServiceTest.php` → OK
- `vendor/bin/phpunit tests/Unit/MarketData/PublicationRepositoryIntegrationTest.php` → OK
- `vendor/bin/phpunit tests/Unit/MarketData/MarketDataPipelineIntegrationTest.php --filter finalize` → OK
- `vendor/bin/phpunit tests/Unit/MarketData/OpsCommandSurfaceTest.php` → OK
- `vendor/bin/phpunit tests/Unit/MarketData` → OK (231 tests, 2269 assertions)

### Result
Cross-consistency is now fully enforced and runtime-proven:

- `READABLE` cannot exist without coverage PASS  
- fallback cannot restore non-PASS baseline  
- pointer cannot reference non-PASS publication  
- invalid state combinations are rejected  

### Contract Impact
Cross-consistency is now a strict invariant:

coverage → finalize → publishability → fallback → pointer

No bypass exists.

### Remaining Gap
None


## 2026-04-27 — PUBLISHABILITY vs COVERAGE vs FALLBACK TEST CONTRACT REALIGNMENT

Status: DONE

### Scope
Realigned market-data PHPUnit fixtures to comply with strict cross-consistency contract requiring coverage PASS for any readable baseline, fallback, or current publication.

### Changes
- Updated `tests/Unit/MarketData/MarketDataPipelineIntegrationTest.php` baseline/current/fallback fixture seeders so readable baseline runs explicitly persist:
  - `coverage_gate_state=PASS`
  - `coverage_min_threshold`
  - `coverage_threshold_mode`
  - `coverage_contract_version`
- Updated `tests/Unit/MarketData/MarketDataPipelineServiceTest.php::makeRun()` so default mocked runs that represent readable/success finalize paths also carry coverage PASS telemetry.
- Removed all implicit/legacy assumptions allowing readable state without coverage metadata.

### Test Proof
Operator local validation after patch:

- `vendor/bin/phpunit tests/Unit/MarketData/MarketDataPipelineIntegrationTest.php` → OK
- `vendor/bin/phpunit tests/Unit/MarketData/MarketDataPipelineServiceTest.php` → OK
- `vendor/bin/phpunit tests/Unit/MarketData` → OK (231 tests, 2269 assertions)

### Result
- All test fixtures are now aligned with locked contract  
- No readable baseline exists without coverage PASS  
- No fallback/correction path bypasses coverage enforcement  

### Contract Impact
No contract change. This is strict alignment of test layer to invariant:

coverage → finalize → publishability → fallback → pointer

### Remaining Gap
None

## 2026-04-28 - SYSTEM INVARIANT LOCK (ANTI FUTURE REGRESSION)

Status: PARTIAL

### Scope
- Locked cross-layer invariant guard for READABLE, coverage PASS, fallback target, pointer target, and promotion bypass state.
- Scope limited to market-data finalize/outcome/pipeline/repository/command guardrails.
- No new feature behavior added.

### Changes
- Added `app/Application/MarketData/Services/MarketDataInvariantGuard.php`.
- `FinalizeDecisionService` now runs centralized no-bypass invariant validation before returning final decision state.
- `PublicationFinalizeOutcomeService` now runs centralized no-bypass invariant validation before returning publication outcome state.
- `MarketDataPipelineService` now validates resolved finalize outcome before persisting final run state.
- `EodPublicationRepository::switchCurrentPublication()` now validates pointer target before pointer/current switch.
- `EodPublicationRepository::assertPublicationEligibleForCurrent()` now validates fallback target and pointer target through centralized invariant guard before prior-current restore can write pointer/current mirrors.
- `PromoteMarketDataCommand` command success/exit status now requires both `publishability_state=READABLE` and `coverage_gate_state=PASS`.
- Added `tests/Unit/MarketData/MarketDataInvariantGuardTest.php` to lock direct guard behavior.

### Test Proof
- Static/lint proof completed inside uploaded ZIP environment:
  - `php -l app/Application/MarketData/Services/MarketDataInvariantGuard.php` -> PASS
  - `php -l app/Application/MarketData/Services/FinalizeDecisionService.php` -> PASS
  - `php -l app/Application/MarketData/Services/PublicationFinalizeOutcomeService.php` -> PASS
  - `php -l app/Application/MarketData/Services/MarketDataPipelineService.php` -> PASS
  - `php -l app/Infrastructure/Persistence/MarketData/EodPublicationRepository.php` -> PASS
  - `php -l app/Console/Commands/MarketData/PromoteMarketDataCommand.php` -> PASS
  - `php -l tests/Unit/MarketData/MarketDataInvariantGuardTest.php` -> PASS
- Static scan proof:
  - READABLE references were scanned across `app/Application/MarketData`, `app/Infrastructure/Persistence/MarketData`, and `app/Console/Commands/MarketData`.
  - Pointer/read queries already require `terminal_status=SUCCESS`, `publishability_state=READABLE`, and `coverage_gate_state=PASS` in repository query paths.
  - New write-side guard now protects promotion, pointer switch, and fallback restore against bypass state.
- PHPUnit/artisan proof not executed because uploaded ZIP does not contain `vendor/`.

### Result
- Central fail-fast invariant guard is now present and wired into finalize, outcome, pipeline, repository pointer write, fallback restore, and promote command surface.
- READABLE cannot be returned from finalize/outcome without coverage PASS.
- Promotion cannot be allowed unless state is `SUCCESS + READABLE + coverage PASS`.
- Pointer/current target cannot be written unless target publication is sealed and owning run is `SUCCESS + READABLE + coverage PASS`.
- Fallback restore cannot target a prior publication unless the prior run is `SUCCESS + READABLE + coverage PASS`.

### Contract Impact
- Existing locked contracts are not changed.
- This session adds enforcement hardening only: centralized guardrails prevent future regression against the already locked flow `coverage -> finalize -> publishability -> fallback -> pointer`.

### Remaining Gap
- Manual local regression proof is still required:
  - `vendor/bin/phpunit tests/Unit/MarketData/MarketDataInvariantGuardTest.php`
  - `vendor/bin/phpunit tests/Unit/MarketData/FinalizeDecisionServiceTest.php`
  - `vendor/bin/phpunit tests/Unit/MarketData/PublicationFinalizeOutcomeServiceTest.php`
  - `vendor/bin/phpunit tests/Unit/MarketData/PublicationRepositoryIntegrationTest.php`
  - `vendor/bin/phpunit tests/Unit/MarketData/MarketDataPipelineIntegrationTest.php`
  - `vendor/bin/phpunit tests/Unit/MarketData/OpsCommandSurfaceTest.php`
  - `vendor/bin/phpunit tests/Unit/MarketData`
- Status remains PARTIAL until local vendor-backed PHPUnit/artisan evidence is provided.

## 2026-04-28 - SYSTEM INVARIANT LOCK POSITIONING FIX

Status: PARTIAL

### Scope
- Corrected premature guard placement that caused valid pipeline integration paths to downgrade from SUCCESS to HELD.
- Scope limited to enforcement positioning; locked invariant contract remains unchanged.

### Changes
- Removed the early `assertValidPointerTarget()` call from `EodPublicationRepository::promoteCandidateToCurrent()`.
- Kept final pointer/current eligibility guard in `EodPublicationRepository::assertPublicationEligibleForCurrent()`.
- Preserved existing explicit promotion preconditions and domain-specific pointer mismatch behavior.

### Test Proof
- Operator-provided runtime proof before correction:
  - Guard/direct decision tests passed.
  - `MarketDataPipelineIntegrationTest.php` failed with 21 failures and 2 errors due to premature pointer target guard.
  - Full `tests/Unit/MarketData` failed with the same 21 failures and 2 errors.
- Static/lint proof after correction:
  - `php -l app/Infrastructure/Persistence/MarketData/EodPublicationRepository.php` -> PASS
  - `php -l app/Application/MarketData/Services/MarketDataInvariantGuard.php` -> PASS
  - `php -l app/Application/MarketData/Services/MarketDataPipelineService.php` -> PASS
  - `php -l app/Application/MarketData/Services/FinalizeDecisionService.php` -> PASS
  - `php -l app/Application/MarketData/Services/PublicationFinalizeOutcomeService.php` -> PASS
- PHPUnit/artisan proof not executed because uploaded ZIP does not contain `vendor/`.

### Result
- Pointer invariant remains enforced only when a publication is evaluated as eligible/current-readable, not before the owning run has been finalized.
- The pipeline should preserve expected SUCCESS outcomes for valid PASS coverage runs.
- Domain mismatch tests should receive the original pointer resolution mismatch behavior instead of guard-level replacement messages.

### Contract Impact
- No contract change.
- The locked flow remains: coverage -> finalize -> publishability -> fallback -> pointer.
- Enforcement boundary clarified: fail-fast guard applies at final state/write eligibility boundaries, not at transient pre-finalize lifecycle boundaries.

### Remaining Gap
- Corrected ZIP still requires local PHPUnit regression proof.
- Status remains PARTIAL until the corrected runtime suite passes locally.

## 2026-04-28 — SYSTEM INVARIANT LOCK FINAL RUNTIME VALIDATION

Status: DONE

### Scope
Final local runtime validation for System Invariant Lock / Anti Future Regression after the invariant guard positioning fix. Scope is proof-only after the positioning correction; no locked contract definition, schema rule, coverage rule, publishability rule, fallback rule, pointer rule, read-side rule, correction lifecycle rule, force replace behavior, or finalize lock behavior was changed by this audit update.

### Changes
- Closed the previous local regression proof gap for System Invariant Lock.
- Upgraded the System Invariant Lock / Anti Future Regression status from PARTIAL to DONE based on operator-provided local PHPUnit evidence.
- Confirmed the premature pointer-target guard regression was resolved by enforcing the invariant at final eligibility/final state boundaries instead of transient pre-finalize lifecycle boundaries.
- Confirmed the centralized invariant guard remains active for final decision/outcome validation, pipeline final outcome validation, final pointer/current eligibility, fallback restore eligibility, and promote command success/exit status.

### Test Proof
Operator-provided local validation after applying the positioning fix:

- `vendor/bin/phpunit tests/Unit/MarketData/MarketDataPipelineIntegrationTest.php` → OK (`51 tests`, `1173 assertions`).
- `vendor/bin/phpunit tests/Unit/MarketData` → OK (`236 tests`, `2277 assertions`).

Prior focused proof from the same System Invariant Lock session remains valid:

- `vendor/bin/phpunit tests/Unit/MarketData/MarketDataInvariantGuardTest.php` → OK (`5 tests`, `8 assertions`).
- `vendor/bin/phpunit tests/Unit/MarketData/FinalizeDecisionServiceTest.php` → OK (`12 tests`, `62 assertions`).
- `vendor/bin/phpunit tests/Unit/MarketData/PublicationFinalizeOutcomeServiceTest.php` → OK (`9 tests`, `41 assertions`).
- `vendor/bin/phpunit tests/Unit/MarketData/PublicationRepositoryIntegrationTest.php` → OK (`22 tests`, `101 assertions`).
- `vendor/bin/phpunit tests/Unit/MarketData/OpsCommandSurfaceTest.php` → OK (`42 tests`, `260 assertions`).

### Result
System Invariant Lock is DONE and locally regression-proven. The invariant remains enforced without the previous integration regression:

- `READABLE` requires coverage `PASS`.
- fallback targets require prior `SUCCESS + READABLE + coverage PASS`.
- pointer/current targets require `SUCCESS + READABLE + coverage PASS`.
- valid PASS coverage pipeline runs remain `SUCCESS` and are no longer downgraded to `HELD` by premature guard execution.
- domain-specific pointer mismatch behavior remains preserved and is not replaced by premature guard errors.

### Contract Impact
No contract definition changed. This validation confirms the already locked invariant flow remains active:

`coverage -> finalize -> publishability -> fallback -> pointer`

The enforcement boundary is now proven correct: fail-fast guard applies at final state/write eligibility boundaries, not before the owning run has reached final publishability state.

### Remaining Gap
None for System Invariant Lock / Anti Future Regression.

