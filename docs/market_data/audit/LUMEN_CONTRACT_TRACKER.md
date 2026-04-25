# LUMEN_CONTRACT_TRACKER

## FINAL SYSTEM STATUS (LATEST)

- Correction Lifecycle → DONE (PROVEN)
- Correction Re-execution Policy → DONE (PROVEN)
- Coverage Gate → DONE
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
