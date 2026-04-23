# LUMEN_CONTRACT_TRACKER

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
