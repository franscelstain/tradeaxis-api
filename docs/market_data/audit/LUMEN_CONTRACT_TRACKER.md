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

