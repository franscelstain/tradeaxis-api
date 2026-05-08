# LUMEN_IMPLEMENTATION_STATUS

## ACTIVE SESSION

ACTIVE SESSION:
- Operational Readiness

[SESSION_STATUS] DONE

[SESSION_SCOPE]
- Establish operator-ready runbook coverage for daily/import/promote/finalize/evidence/replay/correction/backfill/session-snapshot/manual DB policy flows.
- Source-of-truth ZIP has no `vendor/`; container validation remains static/`php -l` only. Operator-local targeted and full MarketData PHPUnit evidence has now been supplied and recorded as promotion evidence.

[SESSION_GOAL]
- Market-data must be runnable by an operator from documented commands and runbook proof, with actionable terminal states, reason-code handling, evidence export, replay verification, and no hidden manual steps.

[SESSION_NOTES]
- Previous Audit Docs Synchronization remains recorded below as LOCKED historical/current proof. Operational Readiness is now DONE after operator-local targeted/full suite validation and artisan command discovery/help spot checks passed.

---
## OPERATIONAL STATUS

[CURRENT_AUDIT_MODE]
- CLEAN_START_RETEST

[HISTORICAL_STATUS_POLICY]
- Previous DONE/LOCKED claims are not copied as current status without fresh evidence.
- Current audit status is rebuilt from scoped test output, static trace, runtime proof, or explicit operator evidence.
- Revalidated scopes must be represented as canonical entries, not repeated hotfix/session fragments.

[DEFAULT_RULE]
- No implementation entry may be marked DONE without current evidence.
- No implementation entry may be split into duplicate entries when the work belongs to one implementation concern.
- Every implementation entry must map to a contract entry in `LUMEN_CONTRACT_TRACKER.md`.

---

## CURRENT WORKING ENTRY

- Operational Readiness -> DONE

  [LAST_UPDATED] 2026-05-08

  [RELATED_CONTRACT] OPERATIONAL_READINESS_CONTRACT

  [REVIEW_STATUS] LOCKED_LOCAL_PHPUNIT_PASS

  [HISTORY]
  - 2026-05-08 -> Session opened from uploaded Operational Readiness prompt and latest source-of-truth ZIP. Static trace found command-specific ops docs and command safety inventory existed, but no single canonical operational runbook covered the full operator flow from import/ingest through promote/finalize/evidence/replay/correction/backfill/session snapshot/manual DB policy.
  - 2026-05-08 -> Patch added `docs/market_data/ops/OPERATIONAL_RUNBOOK.md`, `docs/market_data/audit/OPERATIONAL_READINESS_INVENTORY.md`, updated command docs index, added `OperationalReadinessStaticGuardTest.php`, and reconciled audit-docs guard behavior so future active sessions can be recorded without deleting Audit Docs Synchronization history.
  - 2026-05-08 -> Container validation was static only because uploaded ZIP has no `vendor/`; implementation stayed IN_PROGRESS until operator-local targeted and full MarketData PHPUnit validation was supplied.
  - 2026-05-08 -> Operator-local validation PASS: `OperationalReadinessStaticGuardTest.php` OK (10 tests, 196 assertions); `OperationalReadiness` filter OK (10 tests, 196 assertions); `CommandSurface` filter OK (47 tests, 348 assertions); `Evidence` filter OK (41 tests, 718 assertions); `Replay` filter OK (38 tests, 643 assertions); `Correction` filter OK (65 tests, 1287 assertions); `FailSafe` filter OK (5 tests, 108 assertions); full `vendor/bin/phpunit tests/Unit/MarketData` OK (368 tests, 4927 assertions).
  - 2026-05-08 -> Operator-local artisan validation PASS: `php artisan list | findstr market-data` listed 19 market-data commands, and help spot checks passed for `market-data:daily`, `market-data:promote`, `market-data:evidence:export`, `market-data:replay:verify`, `market-data:correction:request`, `market-data:correction:approve`, and `market-data:correction:run`.
  - 2026-05-08 -> Implementation promoted from IN_PROGRESS to DONE after local PHPUnit/artisan evidence confirmed the runbook, command coverage, evidence/replay/correction/fail-safe surfaces, and full MarketData regression suite.

  [IMPLEMENTATION]
  - `OPERATIONAL_RUNBOOK.md` is now the operator source of truth for daily, manual file import-only, manual file promote, provider/API, stage sequence, terminal state handling, reason-code handling, evidence export, replay verification, correction lifecycle, backfill, session snapshot, manual DB action policy, forbidden shortcuts, operator checklists, troubleshooting, and manual validation commands.
  - `OPERATIONAL_READINESS_INVENTORY.md` records current state, required state, gap, patch, evidence, and status for operational readiness areas.
  - `OperationalReadinessStaticGuardTest.php` guards runbook existence, command coverage, terminal states, next actions, evidence/replay docs, import-vs-promote manual file safety, correction lifecycle, forbidden shortcuts, manual DB policy, audit docs references, and command-index synchronization.
  - `docs/market_data/ops/commands/README.md` now points to the operational runbook as the canonical operator source of truth and lists the registered command surface.

  [ENFORCEMENT]
  - Static guard fails if any registered market-data command is missing from the operational runbook.
  - Static guard fails if HELD / FAILED / NOT_READABLE / READABLE handling, reason code, next action, evidence export, replay verification, manual file import/promote, correction lifecycle, manual DB action policy, or raw/staging/latest/MAX(date) forbidden shortcut language disappears.
  - Audit docs record this implementation as DONE with LOCKED_LOCAL_PHPUNIT_PASS evidence.

  [CURRENT_BEHAVIOR]
  - DONE. Operational Readiness is operator-ready and locally validated across targeted static guard, related functional filters, command discovery/help spot checks, and full MarketData PHPUnit suite.

  [EVIDENCE]
  - Static trace completed across docs/market_data/ops, docs/market_data/audit, command classes, Console Kernel registration, and existing command/evidence/replay/correction/fail-safe guard files.
  - Container `php -l tests/Unit/MarketData/OperationalReadinessStaticGuardTest.php` passed.
  - Container `php -l tests/Unit/MarketData/AuditDocsSynchronizationStaticGuardTest.php` passed after active-session guard reconciliation.
  - Container grep/static scan confirms `OPERATIONAL_RUNBOOK.md`, `OPERATIONAL_READINESS_CONTRACT`, all registered `market-data:*` commands, HELD, FAILED, NOT_READABLE, READABLE, reason code, next action, manual file, import-only, promote, coverage gate, seal, finalize, pointer, evidence, replay, manual DB action, and raw/staging/latest/MAX(date) are present.
  - Operator-local validation PASS: `vendor/bin/phpunit tests/Unit/MarketData/OperationalReadinessStaticGuardTest.php` OK (10 tests, 196 assertions).
  - Operator-local validation PASS: `vendor/bin/phpunit tests/Unit/MarketData --filter "OperationalReadiness"` OK (10 tests, 196 assertions).
  - Operator-local validation PASS: `vendor/bin/phpunit tests/Unit/MarketData --filter "CommandSurface"` OK (47 tests, 348 assertions).
  - Operator-local validation PASS: `vendor/bin/phpunit tests/Unit/MarketData --filter "Evidence"` OK (41 tests, 718 assertions).
  - Operator-local validation PASS: `vendor/bin/phpunit tests/Unit/MarketData --filter "Replay"` OK (38 tests, 643 assertions).
  - Operator-local validation PASS: `vendor/bin/phpunit tests/Unit/MarketData --filter "Correction"` OK (65 tests, 1287 assertions).
  - Operator-local validation PASS: `vendor/bin/phpunit tests/Unit/MarketData --filter "FailSafe"` OK (5 tests, 108 assertions).
  - Operator-local validation PASS: full `vendor/bin/phpunit tests/Unit/MarketData` OK (368 tests, 4927 assertions).
  - Operator-local artisan discovery PASS: `php artisan list | findstr market-data` listed 19 market-data commands including daily, promote, evidence export, replay verify/smoke/backfill, correction request/approve/run, current-publication repair, session snapshot, and session snapshot purge.
  - Operator-local artisan help spot checks PASS for `market-data:daily`, `market-data:promote`, `market-data:evidence:export`, `market-data:replay:verify`, `market-data:correction:request`, `market-data:correction:approve`, and `market-data:correction:run`.

  [MANUAL_VALIDATION_COMPLETED]
  - `vendor/bin/phpunit tests/Unit/MarketData/OperationalReadinessStaticGuardTest.php` -> OK (10 tests, 196 assertions)
  - `vendor/bin/phpunit tests/Unit/MarketData --filter "OperationalReadiness"` -> OK (10 tests, 196 assertions)
  - `vendor/bin/phpunit tests/Unit/MarketData --filter "CommandSurface"` -> OK (47 tests, 348 assertions)
  - `vendor/bin/phpunit tests/Unit/MarketData --filter "Evidence"` -> OK (41 tests, 718 assertions)
  - `vendor/bin/phpunit tests/Unit/MarketData --filter "Replay"` -> OK (38 tests, 643 assertions)
  - `vendor/bin/phpunit tests/Unit/MarketData --filter "Correction"` -> OK (65 tests, 1287 assertions)
  - `vendor/bin/phpunit tests/Unit/MarketData --filter "FailSafe"` -> OK (5 tests, 108 assertions)
  - `vendor/bin/phpunit tests/Unit/MarketData` -> OK (368 tests, 4927 assertions)
  - `php artisan list | findstr market-data` -> PASS, 19 market-data commands listed
  - Command help spot checks -> PASS for daily, promote, evidence export, replay verify, correction request/approve/run

  [NEXT_ACTION]
  - Continue with the next market-data hardening contract from a fresh source-of-truth ZIP. Preserve Operational Readiness as DONE unless a future scoped regression provides contrary evidence.

- Audit Docs Synchronization -> DONE

  [LAST_UPDATED] 2026-05-08

  [RELATED_CONTRACT] AUDIT_DOCS_SYNCHRONIZATION_CONTRACT

  [REVIEW_STATUS] LOCKED_LOCAL_PHPUNIT_PASS

  [HISTORY]
  - 2026-05-08 -> Session opened from uploaded Audit Docs Synchronization prompt and latest source-of-truth ZIP. Static trace found the active audit docs still pointed to Fail-Safe Behavior / No Silent Failure, no canonical audit-docs synchronization contract existed, no dedicated audit-docs inventory existed, and no static guard specifically protected audit docs synchronization.
  - 2026-05-08 -> Patch updated ACTIVE SESSION, inserted the canonical implementation entry, created `AUDIT_DOCS_SYNCHRONIZATION_INVENTORY.md`, strengthened audit governance, and added `AuditDocsSynchronizationStaticGuardTest.php` to prevent audit-docs drift.
  - 2026-05-08 -> Container validation was limited to static trace and `php -l` because uploaded ZIP has no `vendor/`; implementation stayed IN_PROGRESS until operator-local AuditDocs/static/full MarketData PHPUnit evidence was supplied.
  - 2026-05-08 -> Operator-local first retest reported two AuditDocs/static/full-suite failures: `READ_SIDE_POINTER_ENFORCEMENT_CONTRACT` was missed because the static guard only parsed ASCII `->` contract headings while existing historical tracker entries use unicode `→`, and `AUDIT_DOCS_SYNCHRONIZATION_INVENTORY.md` did not contain the exact phrase `not a new container PHPUnit run`.
  - 2026-05-08 -> Follow-up patch made the audit-docs static guard tolerant of both `->` and `→` canonical contract headings and updated the inventory with the required exact evidence phrase.
  - 2026-05-08 -> Operator-local validation after the follow-up patch passed: `AuditDocsSynchronizationStaticGuardTest.php` OK (9 tests, 153 assertions); `AuditDocs` filter OK (9 tests, 153 assertions); `StaticGuard` filter OK (93 tests, 2160 assertions); `Evidence` filter OK (39 tests, 678 assertions); full `vendor/bin/phpunit tests/Unit/MarketData` OK (358 tests, 4711 assertions). Implementation promoted from IN_PROGRESS to DONE.

  [IMPLEMENTATION]
  - `LUMEN_IMPLEMENTATION_STATUS.md` records Audit Docs Synchronization as the active implementation concern and keeps previous DONE entries as history below the current working entry.
  - `LUMEN_CONTRACT_TRACKER.md` records `AUDIT_DOCS_SYNCHRONIZATION_CONTRACT` as the active LOCKED contract and links it to this implementation entry.
  - `AUDIT_UPDATE_GOVERNANCE.md` explicitly requires audit docs synchronization updates, locked evidence markers, latest full-suite evidence references, inventory upkeep, and static guard coverage.
  - `AUDIT_DOCS_SYNCHRONIZATION_INVENTORY.md` records the audited doc/code/test sync inventory, stale/duplicate/evidence findings, patches, local pass evidence, and future sync rule.
  - `AuditDocsSynchronizationStaticGuardTest.php` guards active-session alignment, current-working positioning, canonical contract presence, locked evidence, duplicate contract prevention, implementation/tracker synchronization, governance rules, latest full-suite evidence recording, registry/seed sync, and locked audit-docs proof.

  [ENFORCEMENT]
  - Static guard fails if implementation status and contract tracker active sessions drift, while preserving the locked Audit Docs Synchronization contract and evidence history.
  - Static guard fails if the current working entry/contract is not first under the current working sections.
  - Static guard fails if `AUDIT_DOCS_SYNCHRONIZATION_CONTRACT` is missing, duplicated, or disconnected from the implementation entry.
  - Static guard fails if locked contracts lack concrete validation markers or if reason-code registry and seed drift.
  - Governance makes audit docs synchronization mandatory for future code/test/contract/reason-code/command/evidence changes.

  [CURRENT_BEHAVIOR]
  - DONE. Audit docs synchronization is statically enforced, locally validated, and recorded as the current audit-docs source-of-truth state.

  [EVIDENCE]
  - Static trace completed across audit governance, implementation status, contract tracker, audit inventory, registry/seed, and existing static guards.
  - Reason-code registry/seed sync scan found 315 registry codes and 315 seed codes with no mismatch.
  - Latest carried operator-local full-suite baseline remains recorded: full `vendor/bin/phpunit tests/Unit/MarketData` OK (349 tests, 4558 assertions) from the Fail-Safe Behavior / No Silent Failure session. This is not a new container PHPUnit run.
  - Container `php -l tests/Unit/MarketData/AuditDocsSynchronizationStaticGuardTest.php` passed. PHPUnit/artisan were not run in container because uploaded ZIP has no `vendor/`.
  - Operator-local first retest before the follow-up patch failed: `AuditDocsSynchronizationStaticGuardTest.php` had 2 failures; `AuditDocs` filter had 2 failures; `StaticGuard` filter had 2 failures; `Evidence` filter had 1 AuditDocs evidence-phrase failure; `Reason` and `Replay` filters passed; full `tests/Unit/MarketData` reached 358 tests / 4707 assertions with 2 AuditDocs failures.
  - Operator-local final validation PASS: `vendor/bin/phpunit tests/Unit/MarketData/AuditDocsSynchronizationStaticGuardTest.php` OK (9 tests, 153 assertions).
  - Operator-local final validation PASS: `vendor/bin/phpunit tests/Unit/MarketData --filter "AuditDocs"` OK (9 tests, 153 assertions).
  - Operator-local final validation PASS: `vendor/bin/phpunit tests/Unit/MarketData --filter "StaticGuard"` OK (93 tests, 2160 assertions).
  - Operator-local final validation PASS: `vendor/bin/phpunit tests/Unit/MarketData --filter "Evidence"` OK (39 tests, 678 assertions).
  - Operator-local final validation PASS: full `vendor/bin/phpunit tests/Unit/MarketData` OK (358 tests, 4711 assertions).

  [GAPS]
  - No open audit-docs synchronization gap after the final operator-local validation pass.

  [NEXT_ACTION]
  - Keep audit docs synchronized append-only for future code/test/contract/reason-code/command/evidence changes. Do not create duplicate canonical contracts; extend/reconcile the existing owner entry.






- Fail-Safe Behavior / No Silent Failure -> DONE

  [LAST_UPDATED] 2026-05-08

  [RELATED_CONTRACT] FAIL_SAFE_NO_SILENT_FAILURE_CONTRACT

  [REVIEW_STATUS] LOCKED_LOCAL_PHPUNIT_PASS

  [HISTORY]
  - 2026-05-08 -> Session opened from uploaded Fail-Safe Behavior / No Silent Failure prompt and latest source-of-truth ZIP.
  - 2026-05-08 -> Static trace found no-data gaps: manual JSON/CSV with zero rows could previously be accepted as `source_final_status=SUCCESS` with zero accepted rows; generic API response with empty rows and Yahoo successful responses without target-date bars could return empty arrays; ingest could create/replace an empty bars artifact; finalize did not explicitly block a supplied zero valid data count before readable promotion.
  - 2026-05-08 -> Patch added manual-file empty blocking, API empty/no-valid-data blocking, ingest zero-valid-canonical-bars blocking, finalize explicit zero-valid-data blocking, source failure telemetry context, reason-code registry/seed sync, fail-safe inventory, and static guard coverage.
  - 2026-05-08 -> Container `php -l` passed for changed PHP files. PHPUnit/artisan were not run in container because uploaded ZIP has no `vendor/`.
  - 2026-05-08 -> Operator-local PHPUnit reported remaining failures: static guard looked for array-literal `empty_artifact_blocked` while implementation used assignment syntax; generic API success-after-retry telemetry was lost from source context, causing missing `attempt_count`, `success_after_retry`, and `final_http_status` in evidence/backfill summaries and full-suite `Undefined index: attempt_count`.
  - 2026-05-08 -> Follow-up patch aligned the static guard with the actual finalize assignment syntax and preserved generic API request/retry telemetry into terminal source acquisition telemetry for success, empty-response, and malformed-response paths. Container `php -l` passed for the patched files.
  - 2026-05-08 -> Final operator-local validation PASS after follow-up patch: `FailSafeNoSilentFailureStaticGuardTest.php` OK (5 tests, 108 assertions); `Source` filter OK (37 tests, 420 assertions); `Evidence` filter OK (37 tests, 594 assertions); `Integration` filter OK (91 tests, 1450 assertions); full `vendor/bin/phpunit tests/Unit/MarketData` OK (349 tests, 4558 assertions). Implementation promoted from IN_PROGRESS to DONE.

  [IMPLEMENTATION]
  - `LocalFileEodBarsAdapter` now blocks empty manual CSV/JSON with `RUN_SOURCE_MANUAL_FILE_EMPTY`, failed telemetry, file identity, row counts, and `manual_file_empty_blocked=true`.
  - `PublicApiEodBarsAdapter` now blocks generic empty API responses and Yahoo no-target-date/no-valid-data responses with `RUN_SOURCE_NO_VALID_DATA`, failed telemetry, row counts, and `empty_response_blocked=true`; generic API success/empty/malformed paths preserve retry telemetry (`attempt_count`, `success_after_retry`, `final_http_status`, attempt log).
  - `EodBarsIngestService` now rejects empty source rows and zero valid canonical bars before writing a candidate bars artifact.
  - `MarketDataPipelineService` treats API `RUN_SOURCE_NO_VALID_DATA` as recoverable source failure for HELD/NOT_READABLE fallback preservation and passes explicit bars row count into finalize decision context.
  - `FinalizeDecisionService` now blocks explicit zero valid data proof even if coverage input incorrectly claims PASS.
  - `Reason_Codes_Registry.md` and `Reason_Codes_Seed.sql` include fail-safe no-data/manual-empty/artifact/evidence/replay/pointer codes.
  - `FAIL_SAFE_NO_SILENT_FAILURE_INVENTORY.md` records fail-safe behavior inventory.
  - `FailSafeNoSilentFailureStaticGuardTest` guards no-empty-success behavior, finalize no-fake-success behavior, registry/seed sync, inventory/audit presence, and forbidden latest-date shortcuts.

  [ENFORCEMENT]
  - Manual file empty input cannot become successful source telemetry.
  - API source no-data output cannot become successful empty source rows.
  - Ingest cannot write an empty valid bars artifact.
  - Finalize cannot promote explicit zero valid data proof to `SUCCESS + READABLE`.
  - API no-valid-data failure preserves current pointer through recoverable source failure handling when a prior readable fallback exists.
  - Reason codes used by new fail-safe paths are registered and seeded.

  [FINAL_BEHAVIOR]
  - DONE. Fail-safe/no-silent-failure behavior is enforced by source/manual/API no-data blocking, zero-valid canonical-bars blocking, finalize no-fake-success blocking, pointer-preserving recoverable source failure handling, registry/seed sync, audit inventory, static guard coverage, targeted local validation, and full MarketData suite PASS evidence.

  [EVIDENCE]
  - Static trace completed across source adapters, ingest service, finalize decision service, pipeline source failure handling, evidence/replay surfaces, registry/seed, audit inventory, and static tests.
  - Container `php -l` passed for changed PHP files.
  - Operator-local PHPUnit failure output was reviewed and mapped to the follow-up patch for static guard syntax and generic API retry telemetry preservation.
  - Operator-local validation PASS: `vendor/bin/phpunit tests/Unit/MarketData/FailSafeNoSilentFailureStaticGuardTest.php` OK (5 tests, 108 assertions).
  - Operator-local validation PASS: `vendor/bin/phpunit tests/Unit/MarketData --filter "Source"` OK (37 tests, 420 assertions).
  - Operator-local validation PASS: `vendor/bin/phpunit tests/Unit/MarketData --filter "Evidence"` OK (37 tests, 594 assertions).
  - Operator-local validation PASS: `vendor/bin/phpunit tests/Unit/MarketData --filter "Integration"` OK (91 tests, 1450 assertions).
  - Operator-local validation PASS: full `vendor/bin/phpunit tests/Unit/MarketData` OK (349 tests, 4558 assertions).
  - PHPUnit/artisan were not run in container because uploaded ZIP has no `vendor/`; local operator evidence is the promotion evidence.

  [GAPS]
  - None for this scoped fail-safe/no-silent-failure session after local full-suite PASS.

  [NEXT_ACTION]
  - Continue with the next market-data hardening contract only from a fresh source-of-truth ZIP. Preserve this implementation as DONE unless a future scoped regression provides contrary evidence.

- Import vs Promote Separation -> DONE

  [LAST_UPDATED] 2026-05-08

  [RELATED_CONTRACT] IMPORT_PROMOTE_SEPARATION_CONTRACT

  [REVIEW_STATUS] LOCKED_LOCAL_PHPUNIT_PASS

  [HISTORY]
  - 2026-05-08 -> Session opened from uploaded Import vs Promote Separation prompt and latest source-of-truth ZIP.
  - 2026-05-08 -> Static trace confirmed `market-data:daily` already routes to `importDaily()` and `market-data:promote` routes to `promoteDaily()`, but `request_mode` was not yet a first-class persisted run contract and import/promote proof was still partially inferred from output/notes.
  - 2026-05-08 -> Patch added `eod_runs.request_mode`, DTO normalization, repository persistence, promote-run derivation, request-mode immutability guard, import-only side-effect assertion, reason-coded import-only completion, enriched command output, evidence/replay import-promote context, schema/docs sync, registry/seed sync, inventory, and static guard coverage.
  - 2026-05-08 -> Container `php -l` passed for changed PHP files. PHPUnit/artisan were not run because uploaded ZIP has no `vendor/`.
  - 2026-05-08 -> Operator-local PHPUnit reported targeted failures after the first ZIP: static guard over-asserted `promote`/`current_publication_id`, strict Mockery expectations did not include new `request_mode` arguments/reason codes, and import-only validation attempted a candidate publication repository call during guard inspection.
  - 2026-05-08 -> Follow-up patch narrowed import-only guard to non-mutating inspection, exposed `current_publication_id` in replay context, made DTO allowed request modes explicit, and reconciled affected unit-test expectations for request mode, import/promote reason codes, and enriched notes. Container `php -l` passed again.
  - 2026-05-08 -> Operator-local rerun passed ImportPromote static guard, ImportPromote filter, Manual, Finalize, Publication, Evidence, and StaticGuard filters; Source filter had one remaining Mockery expectation error for `touchStage()` attributes after request-mode notes enrichment. Patch updated that expectation to require `request_mode=import_only`, `notes=request_mode=import_only`, null supersession, and null correction context.
  - 2026-05-08 -> Operator-local rerun after the Source expectation patch passed Source, Provider, Coverage, Pointer, Correction, CommandSurface, and Integration filters. Replay filter and full suite had two remaining errors in `ReplayVerificationServiceTest` because expected replay lineage fixtures did not include the newly exported `current_publication_id`. Patch updated replay fixture expectations to include `current_publication_id` in publication and lineage context.
  - 2026-05-08 -> Final operator-local validation PASS: `Replay` filter OK (37 tests, 624 assertions); full `vendor/bin/phpunit tests/Unit/MarketData` OK (341 tests, 4436 assertions). Implementation promoted to DONE.

  [IMPLEMENTATION]
  - `MarketDataStageInput` now carries normalized `requestMode`.
  - `eod_runs.request_mode` is added through migration, SQLite test bootstrap, and MariaDB schema docs.
  - `EodRunRepository` persists request mode on new runs and derived promote runs.
  - `MarketDataPipelineService` validates allowed request modes, blocks `import_only` from non-ingest stages, prevents request/source mode mutation inside a run, and asserts import-only never becomes readable/current/pointer-switched.
  - `market-data:daily` remains import-only; `market-data:promote` remains explicit promote.
  - `AbstractMarketDataCommand` renders `import_status`, `promote_status`, `promoted`, `pointer_switched`, and `current_publication_id` when applicable.
  - `MarketDataEvidenceExportService` exports `import_promote_boundary` context.
  - `ReplayVerificationService` compares request/import/promote/pointer context when expected proof provides it.
  - `Reason_Codes_Registry.md` and `Reason_Codes_Seed.sql` include import/promote separation reason-code families.
  - `IMPORT_PROMOTE_SEPARATION_INVENTORY.md` records the boundary inventory.
  - `ImportPromoteSeparationStaticGuardTest` guards runtime/schema/command/evidence/replay/reason-code/no-shortcut expectations.

  [ENFORCEMENT]
  - `request_mode=import_only` may ingest and record candidate/import context, but may not write READABLE, current publication, current pointer, or correction published state.
  - `request_mode=promote` is the explicit path for coverage/hash/seal/finalize/pointer validation.
  - Evidence and replay must expose import/promote distinction without requiring raw DB inspection.
  - Command output must show import/promote/pointer state to operators.

  [FINAL_BEHAVIOR]
  - DONE. Import vs Promote Separation is enforced by first-class `request_mode` persistence, import-only side-effect blocking, explicit promote gate context, command/evidence/replay import-promote proof, reason-code registry/seed sync, static guard coverage, targeted local validation, and full MarketData suite PASS evidence.

  [EVIDENCE]
  - Static trace completed across command, DTO, pipeline, repository, schema, evidence, replay, registry/seed, docs, and tests.
  - Container `php -l` passed for changed PHP files after each patch. PHPUnit/artisan were not run in container because uploaded ZIP has no `vendor/`; final runtime proof is operator-local PHPUnit evidence.
  - Operator-local PASS: `ImportPromoteSeparationStaticGuardTest.php` OK (6 tests, 136 assertions); `ImportPromote` filter OK (6 tests, 136 assertions); `Manual` OK (21 tests, 227 assertions); `Source` OK (36 tests, 400 assertions); `Provider` OK (7 tests, 135 assertions); `Coverage` OK (50 tests, 577 assertions); `Finalize` OK (46 tests, 355 assertions); `Publication` OK (97 tests, 1182 assertions); `Pointer` OK (73 tests, 1054 assertions); `Correction` OK (64 tests, 1276 assertions); `Evidence` OK (37 tests, 594 assertions); `Replay` OK (37 tests, 624 assertions); `CommandSurface` OK (47 tests, 348 assertions); `StaticGuard` OK (79 tests, 1899 assertions); `Integration` OK (91 tests, 1450 assertions); full `tests/Unit/MarketData` OK (341 tests, 4436 assertions).

  [GAPS]
  - No open gap for this scope after operator-local targeted and full MarketData validation passed.

  [NEXT_ACTION]
  - Keep this implementation locked. Any future change touching request mode, source mode, import-only ingest, promote/finalize, current pointer switch, correction publish flow, command output, evidence, replay, schema, or reason-code registry/seed must rerun targeted ImportPromote/Source/Replay/CommandSurface/Integration filters plus full `tests/Unit/MarketData`.

- Run / Publication / Pointer Linkage -> DONE

  [LAST_UPDATED] 2026-05-08

  [RELATED_CONTRACT] RUN_PUBLICATION_POINTER_LINKAGE_CONTRACT

  [REVIEW_STATUS] LOCKED_LOCAL_PHPUNIT_PASS

  [HISTORY]
  - 2026-05-08 -> Session opened from uploaded Run / Publication / Pointer Linkage prompt and latest source-of-truth ZIP.
  - 2026-05-08 -> Static trace found correction lineage gap: `eod_dataset_corrections` persisted `prior_run_id` and `new_run_id` but did not persist baseline/replacement publication ids explicitly.
  - 2026-05-08 -> Patch added correction baseline/replacement publication linkage fields, schema/index/test bootstrap sync, repository persistence, pipeline propagation, evidence/replay lineage fallback, command summary linkage output, invariant mirror guard, reason-code registry/seed sync, and static guard coverage.
  - 2026-05-08 -> Container `php -l` passed for changed PHP files. PHPUnit/artisan were not run because uploaded ZIP has no `vendor/`.
  - 2026-05-08 -> Operator-local retest reported failures in `RunPublicationPointerLinkageStaticGuardTest`, `Publication`, `Finalize`, `StaticGuard`, and `Integration`: pipeline lacked explicit lineage-field strings, hash/seal static guard expected finalize seal reason codes, correction service tests still expected old 4-argument calls, and non-correction lock-conflict handling cleared the existing current pointer.
  - 2026-05-08 -> Recovery patch added explicit `baseline_publication_id`/`replacement_publication_id` payload keys in pipeline lineage events, restored `FINALIZE_SEAL_MISSING`/`FINALIZE_SEAL_INVALID` reason-code literals, updated correction service test expectations for explicit publication lineage arguments, and changed non-correction `CURRENT_PUBLICATION_REPLACE_BLOCKED` handling to preserve the pre-switch current pointer instead of clearing it.
  - 2026-05-08 -> Operator-local validation PASS: `RunPublicationPointerLinkageStaticGuardTest.php` OK (5 tests, 169 assertions); `Publication` filter OK (97 tests, 1182 assertions); `Pointer` filter OK (73 tests, 1054 assertions); `Finalize` filter OK (46 tests, 355 assertions); `StaticGuard` filter OK (73 tests, 1763 assertions); `Integration` filter OK (91 tests, 1450 assertions); full `vendor/bin/phpunit tests/Unit/MarketData` OK (335 tests, 4300 assertions). Implementation promoted to DONE.

  [IMPLEMENTATION]
  - `eod_dataset_corrections` now carries `baseline_publication_id` and `replacement_publication_id` as explicit correction lineage fields.
  - `EodCorrectionRepository` persists baseline/replacement publication lineage across executing, resealed, published, repair, consumed, and cancelled correction states.
  - `MarketDataPipelineService` propagates pointer-resolved baseline publication id and replacement publication id through correction execution/finalize outcomes while preserving baseline pointer on unchanged/cancelled/failed outcomes.
  - `MarketDataInvariantGuard` now exposes `assertRunPublicationMirror()` and pointer target validation calls it before accepting a pointer candidate.
  - `EodPublicationRepository` now surfaces linkage-specific reason-code prefixes for missing candidate publication, missing run, invalid state, unsealed target, current replace block, correction baseline mismatch, restore mismatch, and pointer orphan cases.
  - `MarketDataEvidenceExportService` and `ReplayVerificationService` now prefer explicit correction publication lineage fields and retain fallback aliases for compatibility.
  - `AbstractMarketDataCommand` now includes run/publication/current-pointer linkage summary fields in run command output payloads.
  - `Reason_Codes_Registry.md` and `Reason_Codes_Seed.sql` include run-publication, pointer-publication, correction-lineage, replay-lineage, and evidence-lineage reason-code families.
  - `RUN_PUBLICATION_POINTER_LINKAGE_INVENTORY.md` records the linkage inventory and final local validation state.
  - `RunPublicationPointerLinkageStaticGuardTest` guards schema/index/linkage/replay/evidence/command/reason-code/no-shortcut expectations.

  [ENFORCEMENT]
  - Publication/current pointer promotion must validate run-publication mirror and pointer target state.
  - Current pointer targets must remain `SUCCESS + READABLE + SEALED + coverage PASS` and trade-date aligned.
  - Correction execution must persist pointer-resolved baseline publication linkage and replacement publication linkage when a replacement is produced.
  - Failed/unchanged/cancelled correction paths must preserve baseline pointer lineage.
  - Replay/evidence/command output must expose enough linkage context for audit without manual raw database probing.
  - Reason-code registry and seed must stay synchronized for lineage failures and proof events.

  [FINAL_BEHAVIOR]
  - DONE. Run / Publication / Pointer Linkage is enforced by explicit correction baseline/replacement publication lineage, run-publication mirror validation, pointer target validation, pointer switch post-verification, reason-coded force/blocked replacement behavior, replay/evidence lineage proof, command output linkage context, targeted local validation, and full MarketData suite PASS evidence.

  [EVIDENCE]
  - Static trace completed across run, publication, pointer, correction, replay, evidence, command output, schema, SQLite bootstrap, reason-code registry/seed, and inventory.
  - Container `php -l` passed for changed PHP files.
  - PHPUnit/artisan not run in container because uploaded ZIP has no `vendor/`; final runtime proof is operator-local PHPUnit evidence.
  - Operator-local PASS: `RunPublicationPointerLinkageStaticGuardTest.php` OK (5 tests, 169 assertions); `Publication` OK (97 tests, 1182 assertions); `Pointer` OK (73 tests, 1054 assertions); `Finalize` OK (46 tests, 355 assertions); `StaticGuard` OK (73 tests, 1763 assertions); `Integration` OK (91 tests, 1450 assertions); full `tests/Unit/MarketData` OK (335 tests, 4300 assertions).

  [GAPS]
  - No open gap for this scope after operator-local targeted and full MarketData validation passed.

  [NEXT_ACTION]
  - Keep this implementation locked. Any future change touching run-publication mirror, pointer target validation, pointer switch, correction baseline/replacement publication lineage, replay/evidence lineage proof, command output, schema/indexes, or reason-code registry/seed must rerun the targeted linkage filters plus full `tests/Unit/MarketData`.

- Hash / Seal / Dataset Integrity -> DONE

  [LAST_UPDATED] 2026-05-07

  [RELATED_CONTRACT] HASH_SEAL_DATASET_INTEGRITY_CONTRACT

  [REVIEW_STATUS] LOCKED_LOCAL_PHPUNIT_PASS

  [HISTORY]
  - 2026-05-07 -> Session opened from uploaded Hash / Seal / Dataset Integrity prompt and latest source-of-truth ZIP.
  - 2026-05-07 -> Static trace found deterministic-hash gap: `DeterministicHashService` was hardcoded to sha256, did not use configured delimiter/line/null token, and could preserve input order in hash output.
  - 2026-05-07 -> Static trace found manifest gap: publication manifest existed as a DB join but did not expose full hash contract, component column contract, source context, coverage context, canonical ordering rule, or seal verification status.
  - 2026-05-07 -> Static trace found immutability gap: live artifact replacement paths could delete/reinsert current tables without checking whether a different sealed/current/readable publication already existed for the trade date.
  - 2026-05-07 -> Patch hardened deterministic serialization, manifest context, seal/finalize hash guards, command output integrity summary, sealed live artifact mutation guard, reason-code registry/seed sync, hash/seal inventory, and static guard coverage.
  - 2026-05-07 -> Container `php -l` passed for changed PHP files. PHPUnit/artisan were not run because uploaded ZIP has no `vendor/`.
  - 2026-05-07 -> Recovery applied from operator-local PHPUnit failures: timeout contract restored to 20 seconds, candidate hash mirroring now updates `eod_runs`, promote validation now preserves current-pointer/operator errors before hash checks, and replacement candidates route derived artifacts/hash through history to avoid mutating sealed/current baseline datasets.
  - 2026-05-07 -> Recovery round 2 applied from operator-local PHPUnit retest: source/API timeout baseline is enforced in SQLite test bootstrap, and publication-version replacement candidates route indicators, eligibility, and hash through history from compute/build/hash stages so sealed live baselines are not touched before finalize.
  - 2026-05-07 -> Recovery round 3 applied from operator-local PHPUnit retest: replacement candidates create candidate-bound bars history from current live bars when no candidate bars history exists, so mandatory hash/seal preconditions are complete without mutating sealed baseline rows.
  - 2026-05-07 -> Operator-local final validation PASS: `vendor/bin/phpunit tests/Unit/MarketData --filter "Finalize"` OK (46 tests, 355 assertions); `vendor/bin/phpunit tests/Unit/MarketData --filter "Integration"` OK (91 tests, 1443 assertions); full `vendor/bin/phpunit tests/Unit/MarketData` OK (329 tests, 4110 assertions). Implementation promoted to DONE.

  [IMPLEMENTATION]
  - `DeterministicHashService` now reads hash algorithm, delimiter, line separator, and null token from `market_data.hash.*`, normalizes null/empty/numeric/date/bool values deterministically, encodes canonical scalar values safely, sorts canonical row lines, and hashes with the configured algorithm.
  - `MarketDataPipelineService::completeHash()` now emits `DATASET_HASH_CREATED` and persists hash contract context in the run event payload; replacement candidates with superseded publication lineage hash history artifacts instead of baseline live artifacts.
  - `MarketDataPipelineService::hashForTable()` now explicitly orders by trade date and ticker id before canonical hash service sorting.
  - `EodPublicationRepository` now blocks seal when mandatory hash/manifest context is missing, blocks finalize promotion when run/publication hashes are missing or mismatched, and enriches `buildManifestByPublicationId()` with dataset scope, hash config, component hashes, row counts, column contract, source context, coverage context, canonical ordering rule, and seal verification status.
  - `EodArtifactRepository` now blocks normal live artifact replacement when a different sealed/current/readable publication exists for that trade date; correction and operator replacement candidates use the history/candidate flow.
  - `EodArtifactRepository::ensureBarsHistoryFromCurrentTradeDate()` materializes candidate-bound bars history from current live rows only when a replacement candidate lacks bars history, preserving the sealed baseline while making candidate seal/hash complete.
  - `AbstractMarketDataCommand` now renders hash/seal/integrity context in command summaries and run summary artifacts.
  - `Reason_Codes_Registry.md` and `Reason_Codes_Seed.sql` include dataset integrity and finalize hash/seal reason codes.
  - `HASH_SEAL_DATASET_INTEGRITY_INVENTORY.md` records the inventory and final local validation state.
  - `HashSealDatasetIntegrityStaticGuardTest` and expanded `DeterministicHashServiceTest` guard the new behavior.
  - `UsesMarketDataSqlite` pins the source/API timeout baseline to `20` for market-data SQLite tests so local environment drift cannot break source/provider contract assertions.
  - `EodIndicatorsComputeService`, `EodEligibilityBuildService`, and `MarketDataPipelineService::completeHash()` route replacement candidate publication versions through history artifacts before any finalize decision.

  [ENFORCEMENT]
  - Hash serialization is canonical and input-order independent.
  - Normal seal requires mandatory hashes and row-count manifest context.
  - Finalize promotion requires candidate publication hash context to match run hash context.
  - Sealed/current/readable live datasets cannot be replaced through normal artifact paths; mutation is reason-coded as `SEALED_DATASET_MUTATION_BLOCKED`.
  - Replacement promote/finalize flows build candidate artifacts in publication-bound history and only switch live/current state after valid finalize authorization.
  - Manifest export must include hash/seal/source/coverage/column/order context.
  - Command output must show hash algorithm and component hash/seal summary.
  - Reason code registry and seed must stay synchronized for dataset integrity codes.

  [FINAL_BEHAVIOR]
  - DONE. Hash / Seal / Dataset Integrity is enforced by deterministic hash serialization, complete candidate manifest/hash/seal context, immutable sealed/current/readable baseline protection, history-backed replacement candidate artifacts, reason-coded integrity failures, targeted local validation, and full MarketData suite PASS evidence.
  - Same logical artifact rows produce the same hash even when input order changes.
  - Null and empty string are no longer ambiguous because null uses the configured null token.
  - Changed artifact values produce changed hashes.
  - A normal publication cannot be sealed without mandatory integrity context.
  - A readable current promotion cannot proceed when run/publication hashes are missing or mismatched.
  - Baseline sealed/current/readable live artifacts cannot be silently mutated; correction and replacement promote flows must preserve baseline and publish through a new sealed candidate.

  [EVIDENCE]
  - Static trace completed across hash, seal, artifact mutation, finalize, manifest, command output, reason-code registry/seed, and inventory.
  - Container `php -l` passed for changed PHP files after the follow-up patch.
  - Operator-local rerun passed ImportPromote static guard, ImportPromote filter, Manual, Finalize, Publication, Evidence, and StaticGuard filters.
  - Operator-local Source filter exposed one remaining strict Mockery expectation mismatch for enriched `touchStage()` attributes; patch applied in `MarketDataPipelineServiceTest`.
  - PHPUnit/artisan not run in container because uploaded ZIP has no `vendor/`.
  - Operator-local `vendor/bin/phpunit tests/Unit/MarketData --filter "Finalize"` -> PASS: OK (46 tests, 355 assertions).
  - Operator-local `vendor/bin/phpunit tests/Unit/MarketData --filter "Integration"` -> PASS: OK (91 tests, 1443 assertions).
  - Operator-local full `vendor/bin/phpunit tests/Unit/MarketData` -> PASS: OK (329 tests, 4110 assertions).

  [FINAL_RULE]
  - LOCKED. Hash/seal/dataset integrity must remain deterministic, config-driven, reason-coded, and auditable. No publication may become readable/current through missing or mismatched hash/seal/manifest context.
  - Sealed/current/readable live datasets must not be mutated through normal artifact replacement; correction and replacement promote flows must use publication-bound candidate history until finalize authorizes pointer/current promotion.
  - Any future change touching hash serialization, seal lifecycle, artifact mutation, finalize promotion, replacement candidates, correction, replay/evidence integrity proof, command output, or reason-code registry/seed must rerun targeted integrity/finalize/integration tests plus full `tests/Unit/MarketData`.

  [NEXT_ACTION]
  - Keep this implementation as the canonical Hash / Seal / Dataset Integrity entry. Reopen only if a future change touches hash/seal/dataset mutation/finalize/replay/evidence behavior or introduces a new integrity policy gap.

---

- Logging / Traceability / Reason Codes -> DONE

  [LAST_UPDATED] 2026-05-07

  [RELATED_CONTRACT] LOGGING_TRACEABILITY_REASON_CODES_CONTRACT

  [REVIEW_STATUS] LOCKED_LOCAL_PHPUNIT_PASS

  [HISTORY]
  - 2026-05-07 -> Logging / Traceability / Reason Codes session opened against latest source-of-truth ZIP and uploaded execution prompt.
  - 2026-05-07 -> Static trace confirmed uploaded ZIP has no `vendor/`, so container validation is limited to source scan and `php -l`.
  - 2026-05-07 -> Gap found: reason-code registry/seed were not synchronized for several runtime-used reason-code families including partial/delayed/stale coverage, compute/eligibility/finalize failures, pointer/publication integrity, correction artifact outcomes, evidence completeness, and replay match.
  - 2026-05-07 -> Gap found: run creation existed as row state but not as an explicit persisted `RUN_CREATED` lifecycle event.
  - 2026-05-07 -> Gap found: several pointer/correction recovery catch paths used comments or state fallback without a persisted trace event.
  - 2026-05-07 -> Patch added persisted `RUN_CREATED` events in `EodRunRepository`, richer `STAGE_STARTED` payload context, reason-coded correction unchanged/published events, reason-coded pointer recovery events, reason-code registry/seed reconciliation, logging inventory, and `LoggingTraceabilityReasonCodesStaticGuardTest`.
  - 2026-05-07 -> Container `php -l` passed for changed PHP files. PHPUnit/artisan were not run because uploaded ZIP has no `vendor/`.
  - 2026-05-07 -> Operator-local validation PASS: `LoggingTraceabilityReasonCodesStaticGuardTest.php` OK (7 tests, 134 assertions); targeted filters `Reason`, `Trace`, `Log`, `Event`, `Lifecycle`, `CommandSurface`, `Coverage`, `Finalize`, `Pointer`, `Publication`, `Correction`, `Replay`, `Evidence`, `Source`, `Provider`, `ManualFile`, and `Integration` all PASS; full `vendor/bin/phpunit tests/Unit/MarketData` PASS with `OK (319 tests, 4033 assertions)`. Implementation promoted to DONE.

  [IMPLEMENTATION]
  - `EodRunRepository::getOrCreateOwningRun()` now persists `RUN_CREATED` immediately after creating a new owning run, including run id, requested/effective trade date, source mode, supersedes run id, lifecycle state, and publishability state.
  - `EodRunRepository::createPromoteRunFromSeed()` now persists `RUN_CREATED` for seed-derived promote runs, including seed run id, promote mode, publish target, source mode, and run lifecycle context.
  - `MarketDataPipelineService::startStage()` now includes run id, requested/effective trade date, source mode, stage, and correction id in `STAGE_STARTED` payloads.
  - Correction unchanged/skipped/cancelled and correction published events are now reason-coded with `CORRECTION_ARTIFACT_UNCHANGED` or `CORRECTION_PUBLISHED` instead of relying on event type alone.
  - Pointer restore/resolution/mirror-repair/cleanup recovery branches now append reason-coded trace events instead of relying only on comments or silent state fallback.
  - `Reason_Codes_Registry.md` and `Reason_Codes_Seed.sql` now contain the same canonical code set for the touched run, coverage, publication, pointer, correction, evidence, and replay reason-code families.
  - `docs/market_data/ops/LOGGING_TRACEABILITY_REASON_CODES_INVENTORY.md` records the scoped logging inventory and current static/PHPUnit status.
  - `LoggingTraceabilityReasonCodesStaticGuardTest` guards registry/seed sync, lifecycle trace presence, critical reason-code registration, traceability inventory coverage, pointer/correction recovery trace, and no latest-date shortcut regression in the logging scope.

  [ENFORCEMENT]
  - New runs and promote runs must have explicit persisted lifecycle start evidence through `RUN_CREATED`.
  - Stage start events must carry enough context to identify run, requested/effective date, source mode, stage, and correction linkage.
  - Failure/held/not-readable/pointer recovery/correction unchanged/correction published paths must be reason-coded and traceable.
  - Registry and seed must stay synchronized; the static guard fails on drift.
  - Logging inventory must remain present and cover pipeline, source, manual file, coverage, finalize, publication, pointer, correction, replay, evidence, session snapshot, repair, and command failure scopes.

  [FINAL_BEHAVIOR]
  - DONE. Logging / Traceability / Reason Codes is enforced by persisted run lifecycle events, registered reason codes, registry/seed sync guards, pointer/correction recovery trace, logging inventory, targeted local validation, and full MarketData suite PASS evidence.
  - Final behavior: run lifecycle has explicit creation/start/final trace, correction outcome events are reason-coded, pointer recovery catch paths are no longer comment-only, touched reason-code families are registry/seed synchronized, and regression is protected by static guards.

  [EVIDENCE]
  - Static scan completed across market-data run repository, pipeline service, registry/seed, ops inventory, audit files, and tests.
  - `php -l app/Infrastructure/Persistence/MarketData/EodRunRepository.php` -> PASS.
  - `php -l app/Application/MarketData/Services/MarketDataPipelineService.php` -> PASS.
  - `php -l tests/Unit/MarketData/LoggingTraceabilityReasonCodesStaticGuardTest.php` -> PASS.
  - PHPUnit/artisan not run in container because uploaded ZIP has no `vendor/`.
  - Operator-local `vendor/bin/phpunit tests/Unit/MarketData/LoggingTraceabilityReasonCodesStaticGuardTest.php` -> PASS: OK (7 tests, 134 assertions).
  - Operator-local targeted filters `Reason`, `Trace`, `Log`, `Event`, `Lifecycle`, `CommandSurface`, `Coverage`, `Finalize`, `Pointer`, `Publication`, `Correction`, `Replay`, `Evidence`, `Source`, `Provider`, `ManualFile`, and `Integration` -> PASS.
  - Operator-local full `vendor/bin/phpunit tests/Unit/MarketData` -> PASS: OK (319 tests, 4033 assertions).

  [NEXT_ACTION]
  - Keep this implementation as the canonical logging/traceability/reason-code entry.
  - Future changes touching lifecycle logs, reason codes, registry/seed, commands, provider/manual file, correction, replay, evidence, or pointer/finalize behavior must rerun the targeted filters plus full `tests/Unit/MarketData`.


- Command Surface Safety / Ops Layer -> DONE

  [LAST_UPDATED] 2026-05-07

  [RELATED_CONTRACT] COMMAND_SURFACE_SAFETY_OPS_LAYER_CONTRACT

  [REVIEW_STATUS] REVIEWED_OK

  [HISTORY]
  - 2026-05-07 -> Command Surface Safety / Ops Layer session opened against latest source-of-truth ZIP.
  - 2026-05-07 -> Static trace inventoried registered market-data commands in `app/Console/Kernel.php` and command implementations under `app/Console/Commands/MarketData`.
  - 2026-05-07 -> Gap found: `market-data:session-snapshot:purge` was destructive and deleted rows without an explicit `--apply` guard or dry-run default.
  - 2026-05-07 -> Patch added dry-run/apply behavior to session snapshot purge, candidate-row counting before deletion, reason-coded `COMMAND_DRY_RUN_ONLY` / `COMMAND_APPLY_CONFIRMED` output, and operator next action.
  - 2026-05-07 -> Patch added common command blocked-output helpers and date/source validation for core market-data commands.
  - 2026-05-07 -> Patch added reason-coded guard output for promote force-replace validation, evidence selector validation, replay verify execution failure, correction request/approve/run validation, and current-publication repair dry-run/apply output.
  - 2026-05-07 -> Patch added `COMMAND_*` reason codes to registry/seed, `COMMAND_SURFACE_SAFETY_INVENTORY.md`, session-snapshot purge runbook update, service/command tests, and `CommandSurfaceSafetyStaticGuardTest`.
  - 2026-05-07 -> Container `php -l` passed for changed PHP files; PHPUnit/artisan were not run because uploaded ZIP has no `vendor/`.
  - 2026-05-07 -> Operator-local validation showed command behavior PASS for purge dry-run/apply and related OpsCommand/SessionSnapshot tests, but full MarketData suite failed on one static guard false negative that expected `COMMAND_DRY_RUN_ONLY` literal in the command file instead of service-owned reason-code summary output.
  - 2026-05-07 -> Fix2 corrected the static guard to assert command summary reason-code rendering plus service-owned `COMMAND_DRY_RUN_ONLY` / `COMMAND_APPLY_CONFIRMED`, and hardened `SessionSnapshotService::purge()` to default to non-mutating dry-run unless `$apply=true` is explicit.
  - 2026-05-07 -> Operator-local Fix2 validation PASS: `CommandSurfaceSafetyStaticGuardTest.php` OK (5 tests, 81 assertions); `SessionSnapshotServiceTest.php` OK (6 tests, 38 assertions); `--filter "CommandSurface"` OK (47 tests, 348 assertions); `--filter "DryRun"` OK (2 tests, 15 assertions); `--filter "Apply"` OK (4 tests, 26 assertions); full `vendor/bin/phpunit tests/Unit/MarketData` OK (312 tests, 3899 assertions).

  [IMPLEMENTATION]
  - `SessionSnapshotService::purge()` now defaults to non-mutating dry-run, accepts an explicit `$apply` flag, counts candidate rows, writes operation mode and reason code, and does not delete rows unless `$apply=true`.
  - `SessionSnapshotRepository` now exposes `countBefore()` so purge dry-run can preview the mutation without executing delete.
  - `PurgeSessionSnapshotCommand` now defaults to dry-run and requires `--apply` for deletion.
  - `RepairCurrentPublicationIntegrityCommand` now renders dry-run/apply reason-code context while preserving the existing `--apply` guard.
  - `AbstractMarketDataCommand` now centralizes `status=BLOCKED`, registered reason code output, date validation, and source-mode validation.
  - Core stage, daily, backfill, promote, replay-backfill, session-snapshot, evidence, replay-verify, and correction commands now use stronger input/operator-failure guard paths.
  - Command surface inventory and session snapshot purge runbook define the final dry-run/apply and destructive action policies.

  [ENFORCEMENT]
  - Destructive purge cannot delete snapshot rows unless `--apply` is supplied.
  - Dry-run purge must render `COMMAND_DRY_RUN_ONLY`, candidate rows, deleted rows `0`, cutoff context, and next action.
  - Applied purge must render `COMMAND_APPLY_CONFIRMED`, candidate rows, actual deleted rows, cutoff context, and artifact path.
  - Invalid date/source/mode/selector/correction command inputs return `status=BLOCKED` with registered `COMMAND_*` reason codes.
  - Static guard now checks command inventory registration, purge dry-run/apply protection without false coupling to service-owned reason-code literals in command files, command reason-code registry/seed sync, promote force guard, and repair apply guard.

  [FINAL_BEHAVIOR]
  - DONE. Command surface safety / ops layer is enforced and locally validated. Destructive purge is dry-run by default, apply is explicit, reason-code output is registered, command inventory is complete for registered market-data commands, and targeted plus full MarketData PHPUnit validation passed locally.

  [EVIDENCE]
  - Container static trace completed across command files, session snapshot service/repository, reason-code registry/seed, ops docs, and command tests.
  - Container `php -l` passed for all changed PHP files.
  - PHPUnit/artisan not run in container because uploaded ZIP has no `vendor/`.
  - Operator-local pre-Fix2 evidence: purge dry-run/apply command output behaved correctly; OpsCommand, SessionSnapshot, Reason, Correction, Replay, Evidence, and Integration filters passed; one static guard false negative blocked full suite.
  - Operator-local Fix2 PASS: `CommandSurfaceSafetyStaticGuardTest.php` -> OK (5 tests, 81 assertions).
  - Operator-local Fix2 PASS: `SessionSnapshotServiceTest.php` -> OK (6 tests, 38 assertions).
  - Operator-local Fix2 PASS: `vendor/bin/phpunit tests/Unit/MarketData --filter "CommandSurface"` -> OK (47 tests, 348 assertions).
  - Operator-local Fix2 PASS: `vendor/bin/phpunit tests/Unit/MarketData --filter "DryRun"` -> OK (2 tests, 15 assertions).
  - Operator-local Fix2 PASS: `vendor/bin/phpunit tests/Unit/MarketData --filter "Apply"` -> OK (4 tests, 26 assertions).
  - Operator-local Fix2 PASS: full `vendor/bin/phpunit tests/Unit/MarketData` -> OK (312 tests, 3899 assertions).

  [NEXT_ACTION]
  - None for this session. Future market-data command changes must preserve command inventory, destructive dry-run/apply guard, registered reason-code output, and full MarketData PHPUnit validation.

---

- DB Integrity & Constraint Enforcement -> DONE

  [LAST_UPDATED] 2026-05-07

  [RELATED_CONTRACT] DB_INTEGRITY_CONSTRAINT_ENFORCEMENT_CONTRACT

  [REVIEW_STATUS] REVIEWED_OK

  [HISTORY]
  - 2026-05-07 -> DB Integrity & Constraint Enforcement session opened against latest source-of-truth ZIP.
  - 2026-05-07 -> Static trace reviewed locked SQL schema, runtime migrations, SQLite mirror, publication/pointer repositories, evidence/correction/replay repositories, registry/seed files, and existing schema/read-side/static guards.
  - 2026-05-07 -> Gap found: SQLite mirror did not fully represent runtime integrity indexes and composite replay reason-code identity, allowing tests to run against a weaker schema than the locked MariaDB contract.
  - 2026-05-07 -> Gap found: run readable lookup, publication readable lookup, artifact publication-scoped reads, pointer run/version lookup, source identity lookup, and correction prior/new linkage needed explicit index enforcement across SQL schema, additive migration, and SQLite mirror.
  - 2026-05-07 -> Gap found: `RUN_FINALIZE_IDEMPOTENCY_POINTER_INVALID` was used by runtime/tests but was not registered in reason-code registry/seed.
  - 2026-05-07 -> Patch added DB-integrity indexes to `Database_Schema_MariaDB.sql`, additive idempotent migration `2026_05_07_000002_enforce_market_data_db_integrity_indexes.php`, SQLite mirror indexes/primary keys, `MarketDataSqliteSchemaSyncTest` integrity assertions, and `DbIntegrityConstraintEnforcementStaticGuardTest`.
  - 2026-05-07 -> Container `php -l` passed for changed PHP files; PHPUnit/artisan were not run because uploaded ZIP has no `vendor/`.
  - 2026-05-07 -> Operator-local validation reported 2 regressions after DB integrity enforcement: `PublicationRepositoryIntegrationTest::test_pointer_resolution_returns_null_when_pointer_publication_version_mismatches_pointed_publication` violated new `(trade_date, publication_version)` uniqueness in its negative fixture, and `TestCoverageBehavioralStaticGuardTest` still required the historical `ENFORCED_PENDING_LOCAL_PHPUNIT` inventory marker.
  - 2026-05-07 -> Follow-up patch adjusted the pointer-version mismatch fixture to use publication version `2` before corrupting pointer version to `999`, preserving the new uniqueness contract while still proving fail-safe pointer resolution; behavioral inventory now retains the historical enforcement marker without downgrading the locked behavioral coverage status.
  - 2026-05-07 -> Operator-local final validation PASS: targeted `Repository`, `Pointer`, `Publication`, `Coverage`, `Integration`, and full `vendor/bin/phpunit tests/Unit/MarketData` all passed; full suite result `OK (305 tests, 3795 assertions)`. DB Integrity & Constraint Enforcement promoted to DONE.

  [IMPLEMENTATION]
  - Runtime SQL schema now explicitly declares supporting indexes for readable run lookup, publication readable lookup, source identity lookup, pointer run/version lookup, publication-scoped artifact reads, correction status/execution lookup, correction prior/new linkage, replay publication identity, and replay reason-code lookup.
  - Additive migration `2026_05_07_000002_enforce_market_data_db_integrity_indexes.php` creates the same integrity indexes idempotently for databases bootstrapped from an older schema state.
  - SQLite mirror now carries the same critical primary keys, unique keys, and runtime indexes used by repository/query paths.
  - `md_replay_reason_code_counts` SQLite mirror now uses composite primary key `(replay_id, trade_date, reason_code)` to match the locked SQL schema.
  - `MarketDataSqliteSchemaSyncTest` now verifies critical primary key columns and index names instead of only checking column presence.
  - `DbIntegrityConstraintEnforcementStaticGuardTest` guards SQL primary keys, business keys, runtime indexes, implicit integrity policy, repository pointer guards, enum-like values, reason-code registry/seed sync, and forbidden latest-date shortcuts.
  - Reason-code registry and seed now include `RUN_FINALIZE_IDEMPOTENCY_POINTER_INVALID`.

  [ENFORCEMENT]
  - Test schema can no longer silently omit runtime indexes and replay reason-code identity.
  - Pointer/current resolution stays guarded by pointer primary key, pointer publication uniqueness, publication trade-date/version uniqueness, run/publication mirror checks, coverage PASS, `SUCCESS + READABLE`, and sealed publication state.
  - Pointer-version mismatch negative fixtures must respect `(trade_date, publication_version)` uniqueness and corrupt only the pointer mirror fields being tested.
  - Non-FK lifecycle relations remain governed by explicit implicit integrity policy and repository/static guards until a physical FK is proven feasible for that lifecycle path.
  - Reason code usage must remain backed by registry and seed entries.

  [FINAL_BEHAVIOR]
  - DONE. DB integrity enforcement is now backed by SQL schema, additive migration, SQLite mirror, static/schema guards, reason-code registry/seed sync, fixed schema-valid negative fixture behavior, targeted local validation, and full MarketData suite PASS evidence.

  [EVIDENCE]
  - Container static trace completed across SQL schema, migrations, SQLite mirror, repositories, registry/seed, and tests.
  - Container `php -l` passed for: `database/migrations/2026_05_07_000002_enforce_market_data_db_integrity_indexes.php`, `tests/Support/UsesMarketDataSqlite.php`, `tests/Unit/MarketData/MarketDataSqliteSchemaSyncTest.php`, `tests/Unit/MarketData/DbIntegrityConstraintEnforcementStaticGuardTest.php`, and `tests/Unit/MarketData/PublicationRepositoryIntegrationTest.php`.
  - `vendor/` and `vendor/bin/phpunit` are absent from uploaded ZIP, so container PHPUnit/artisan validation was not possible; operator-local validation evidence supplied by the operator is now recorded below.
  - Operator-local PASS: `vendor/bin/phpunit tests/Unit/MarketData --filter "Repository"` -> OK (38 tests, 220 assertions).
  - Operator-local PASS: `vendor/bin/phpunit tests/Unit/MarketData --filter "Pointer"` -> OK (65 tests, 837 assertions).
  - Operator-local PASS: `vendor/bin/phpunit tests/Unit/MarketData --filter "Publication"` -> OK (90 tests, 1007 assertions).
  - Operator-local PASS: `vendor/bin/phpunit tests/Unit/MarketData --filter "Coverage"` -> OK (48 tests, 527 assertions).
  - Operator-local PASS: `vendor/bin/phpunit tests/Unit/MarketData --filter "Integration"` -> OK (91 tests, 1443 assertions).
  - Operator-local PASS: `vendor/bin/phpunit tests/Unit/MarketData` -> OK (305 tests, 3795 assertions).

  [NEXT_ACTION]
  - None for this session. Continue using DB integrity static/schema guards and full `tests/Unit/MarketData` as regression validation for future market-data schema or repository changes.

---

## RECENT LOCKED ENTRY

- Test Coverage Behavioral -> DONE

  [LAST_UPDATED] 2026-05-07

  [RELATED_CONTRACT] TEST_COVERAGE_BEHAVIORAL_CONTRACT

  [REVIEW_STATUS] REVIEWED_OK

  [HISTORY]
  - 2026-05-07 -> Test Coverage Behavioral session opened against latest source-of-truth ZIP.
  - 2026-05-07 -> Static trace reviewed market-data tests, test support, command tests, integration tests, replay/evidence tests, read-side tests, static guards, audit governance, and test docs.
  - 2026-05-07 -> Inventory created in `docs/market_data/tests/Behavioral_Test_Coverage_Inventory.md` mapping critical areas to existing coverage, mock level, runtime proof state, gaps, and action.
  - 2026-05-07 -> Gap found: manual-file import-only behavior was guarded mostly by policy/static/command support, but did not have explicit DB-backed proof that import-only stays unfinalized, unsealed, non-current, and pointerless.
  - 2026-05-07 -> Gap found: manual-file promote from imported partial data did not have explicit DB-backed proof that coverage gate blocks readable publication and pointer switch.
  - 2026-05-07 -> Gap found: command surface tests are internal mock-heavy and must stay support-only until real command runtime tests assert DB/evidence/replay state locally.
  - 2026-05-07 -> Patch added two `MarketDataPipelineIntegrationTest` cases for manual-file import-only and manual-file promote coverage enforcement.
  - 2026-05-07 -> Patch added `TestCoverageBehavioralStaticGuardTest` to guard the inventory, DB-backed proof files, import/promote/finalize/coverage/pointer/correction proof names, mock policy, and static-guard-as-support rule.
  - 2026-05-07 -> Container `php -l` passed for changed PHP files; PHPUnit/artisan were not run because uploaded ZIP has no `vendor/`.
  - 2026-05-07 -> Operator-local targeted validation PASS: manual-file import-only 1 test / 19 assertions; manual-file promote partial 1 test / 17 assertions; `TestCoverageBehavioralStaticGuardTest.php` 5 tests / 108 assertions.
  - 2026-05-07 -> Operator-local filtered validation PASS: Behavior 5 tests / 108 assertions; Integration 91 tests / 1443 assertions; Pipeline 91 tests / 1432 assertions; Finalize 44 tests / 311 assertions; Coverage 48 tests / 527 assertions; Pointer 65 tests / 837 assertions; Correction 61 tests / 1208 assertions; Replay 34 tests / 550 assertions; Evidence 34 tests / 520 assertions; Readable 54 tests / 375 assertions; Command 58 tests / 475 assertions; Manual 21 tests / 227 assertions; Source 35 tests / 386 assertions.
  - 2026-05-07 -> Operator-local focused file validation PASS: `MarketDataPipelineIntegrationTest.php` 55 tests / 1227 assertions; `ReadablePublicationReadContractIntegrationTest.php` 8 tests / 15 assertions; `ReplayVerificationServiceTest.php` 6 tests / 17 assertions; `ReplayDeterminismStaticGuardTest.php` 5 tests / 155 assertions; `MarketDataEvidenceExportServiceTest.php` 3 tests / 87 assertions; `OpsCommandSurfaceTest.php` 42 tests / 260 assertions.
  - 2026-05-07 -> Operator-local full validation PASS: `vendor/bin/phpunit tests/Unit/MarketData` -> 298 tests / 3327 assertions.
  - 2026-05-07 -> Test Coverage Behavioral promoted to DONE after targeted, filtered, focused file, static guard, integration, and full MarketData unit validation passed.

  [IMPLEMENTATION]
  - Added DB-backed integration proof in `tests/Unit/MarketData/MarketDataPipelineIntegrationTest.php` for manual-file import-only and manual-file promote coverage-gate behavior.
  - Added `tests/Unit/MarketData/TestCoverageBehavioralStaticGuardTest.php` to prevent critical lifecycle proof from drifting into mock-heavy false confidence.
  - Added `docs/market_data/tests/Behavioral_Test_Coverage_Inventory.md` with area-by-area behavioral coverage inventory, mock policy, support-only test classification, and local validation boundary.

  [ENFORCEMENT]
  - Import-only proof asserts stage/state, no terminal status, no coverage gate, no hashes/seal, unsealed non-current publication, no pointer, no finalize event, and persisted bars only.
  - Promote proof asserts coverage FAIL, NOT_READABLE, no pointer/current publication, reason-coded finalize event, coverage expected/available/missing counts, and promote context.
  - Static guard requires lifecycle proof files to remain `UsesMarketDataSqlite` + `DB::table` backed and free from internal Mockery/`shouldReceive` proof.
  - Static guard requires command surface mock-heavy status to remain explicitly documented as support-only.

  [FINAL_BEHAVIOR]
  - Behavioral coverage proof is LOCKED for this source-of-truth ZIP after local targeted and full validation passed.
  - Manual-file import-only cannot silently become publishable; it persists candidate bars without finalize, seal, coverage gate, current publication, or pointer switch.
  - Manual-file promote from a partial imported dataset cannot bypass coverage; it remains NOT_READABLE and pointer-safe with reason-coded finalization.
  - Existing DB-backed integration proof remains the primary source for finalize, coverage, pointer, fallback, publishability, correction, read-side, and repository behavior.
  - Command tests remain operator-surface support only and are not treated as primary lifecycle proof, even though command filter validation passed locally.

  [EVIDENCE]
  - Container static trace completed.
  - `php -l tests/Unit/MarketData/MarketDataPipelineIntegrationTest.php` -> no syntax errors detected.
  - `php -l tests/Unit/MarketData/TestCoverageBehavioralStaticGuardTest.php` -> no syntax errors detected.
  - Operator-local targeted, filtered, focused-file, static guard, integration, command-surface, replay/evidence/read-side, and full MarketData PHPUnit validation passed.
  - Full suite evidence: `vendor/bin/phpunit tests/Unit/MarketData` -> 298 tests / 3327 assertions.

  [NEXT_ACTION]
  - None for this session. Use this ZIP as the next source of truth.

- Replay Determinism -> DONE

  [LAST_UPDATED] 2026-05-07

  [RELATED_CONTRACT] REPLAY_DETERMINISM_CONTRACT

  [REVIEW_STATUS] REVIEWED_OK

  [HISTORY]
  - 2026-05-07 -> Replay Determinism session opened against latest source-of-truth ZIP.
  - 2026-05-07 -> Static trace reviewed replay verifier, replay result repository, evidence export service, replay fixtures, command output, schema, reason-code registry/seed, and related tests/static guards.
  - 2026-05-07 -> Gap found: replay fixture packages did not require stable `fixture_id`, `fixture_version`, `fixture_schema_version`, `fixture_created_at`, and `fixture_source` metadata.
  - 2026-05-07 -> Gap found: expected proof could be incomplete while comparison still skipped nullable/missing fields like publication, pointer, fallback, correction, source, coverage, artifact, and lineage context.
  - 2026-05-07 -> Gap found: mismatch output did not expose a structured `reason_code` per field, so replay divergence was not operator-grade.
  - 2026-05-07 -> Gap found: non-readable/source-failure actual runs could be rejected before replay proof, preventing deterministic HELD/FAILED/NOT_READABLE replay evidence.
  - 2026-05-07 -> Enforcement patch added fixture v2 metadata validation, expected-proof completeness checks, evidence-derived actual context, explicit context comparators, mismatch reason-code families, deterministic/volatile field metadata, replay artifact persistence columns, operator-grade replay command output, replay evidence export fields, fixture updates, and `ReplayDeterminismStaticGuardTest`.
  - 2026-05-07 -> Container `php -l` passed for changed PHP files; `vendor/` is absent in uploaded ZIP, so PHPUnit/artisan validation was not run in container.
  - 2026-05-07 -> Operator-local targeted validation PASS: `ReplayVerificationServiceTest.php` 6 tests / 17 assertions; `ReplayDeterminismStaticGuardTest.php` 5 tests / 155 assertions; `ReplayEvidenceExportServiceTest.php` 1 test / 42 assertions; `MarketDataEvidenceExportServiceTest.php` 3 tests / 87 assertions; `OpsCommandSurfaceTest.php` 42 tests / 260 assertions.
  - 2026-05-07 -> Operator-local filtered validation PASS: Replay 34 tests / 550 assertions; replay 34 tests / 550 assertions; Evidence 34 tests / 520 assertions; Command 57 tests / 467 assertions; Coverage 42 tests / 402 assertions; Pointer 62 tests / 770 assertions; Finalize 42 tests / 261 assertions; Correction 60 tests / 1177 assertions; Manual 19 tests / 191 assertions; Source 35 tests / 386 assertions.
  - 2026-05-07 -> Operator-local integration validation PASS: `MarketDataPipelineIntegrationTest.php` 53 tests / 1191 assertions; `ReadablePublicationReadContractIntegrationTest.php` 8 tests / 15 assertions.
  - 2026-05-07 -> Operator-local full validation PASS: `vendor/bin/phpunit tests/Unit/MarketData` -> 291 tests / 3183 assertions.
  - 2026-05-07 -> Replay Determinism promoted to DONE after targeted, filtered, integration, static guard, and full MarketData unit validation passed.

  [IMPLEMENTATION]
  - `ReplayVerificationService` now loads fixture v2 metadata, validates expected proof sections, builds actual proof from run/source/coverage/artifact/seal/publication/pointer/fallback/correction/lineage evidence context, compares expected vs actual explicitly, and persists mismatch arrays with reason-code families.
  - `ReplayResultRepository`, MariaDB schema docs, SQLite test schema, and migration now include fixture identity, expected/actual context JSON, mismatch count, mismatch reason codes, mismatches, ignored volatile fields, deterministic fields checked, and final replay reason code.
  - `MarketDataEvidenceExportService` now exports replay fixture metadata, expected context, actual context, mismatch details, volatile-field exclusions, and deterministic fields checked.
  - `VerifyReplayCommand` now prints operator-grade proof summary: suite/case/schema, expected/actual final state, mismatch count/reason codes, source/coverage/publication/pointer/fallback/correction summaries, evidence path, and replay artifact path.
  - Replay fixtures under `storage/app/market_data/replay-fixtures` were upgraded to fixture schema v2 where appropriate; broken and missing-file cases remain intentional error cases.
  - Reason-code registry and seed now include replay mismatch/fail-safe reason-code families.

  [ENFORCEMENT]
  - Missing or incompatible fixture metadata fails with `REPLAY_FIXTURE_SCHEMA_MISMATCH`.
  - Missing expected proof sections fail-safe with `REPLAY_EXPECTED_PROOF_INCOMPLETE`; missing actual run proof fails with `REPLAY_ACTUAL_PROOF_INCOMPLETE`.
  - Source mode/file/API provider, coverage, artifact/hash/seal, publication, pointer, fallback, correction, final reason, and lineage differences are reason-coded.
  - Volatile runtime fields are explicitly listed and excluded from deterministic comparison, while deterministic fields checked are persisted.
  - Static guard blocks replay regression: missing context comparison, missing reason codes, forbidden latest/MAX/raw/staging shortcuts, missing artifact schema fields, command output drift, and unregistered replay reason codes.

  [FINAL_BEHAVIOR]
  - Replay no longer proves success by command execution alone. Replay produces MATCH only when fixture expected proof and actual lifecycle proof align under deterministic comparison. Mismatches are visible as structured reason-coded proof. Incomplete fixture/actual proof fails safe rather than wildcard-passing.

  [EVIDENCE]
  - Container static trace completed.
  - Container `php -l` passed for changed PHP files.
  - Operator-local targeted replay/evidence/command/static guard validation PASS.
  - Operator-local filtered replay/evidence/command/coverage/pointer/finalize/correction/manual/source validation PASS.
  - Operator-local integration validation PASS.
  - Operator-local full `tests/Unit/MarketData` validation PASS: 291 tests / 3183 assertions.

  [NEXT_ACTION]
  - None for replay determinism. Keep future changes under regression guard and append-only governance.

---

## VERIFIED IMPLEMENTATION ENTRIES

- Source / Provider Resilience -> DONE

  [LAST_UPDATED] 2026-05-06

  [RELATED_CONTRACT] SOURCE_PROVIDER_RESILIENCE_CONTRACT

  [REVIEW_STATUS] REVIEWED_OK

  [HISTORY]
  - 2026-05-03 -> Source / Provider Resilience session opened against latest source-of-truth ZIP.
  - 2026-05-03 -> Static trace reviewed source adapters, ingest service, pipeline source failure/fallback path, coverage/finalize interaction, evidence export, replay verification, command output, repository persistence, DB schema, reason-code registry, and static guards.
  - 2026-05-03 -> Gap found: manual-file missing/unreadable/malformed failures used generic runtime exceptions and did not emit explicit source reason codes.
  - 2026-05-03 -> Gap found: Yahoo per-ticker acquisition did not aggregate attempt telemetry across ticker requests and did not expose `RUN_SOURCE_PARTIAL_RESPONSE` for partial provider results.
  - 2026-05-03 -> Gap found: evidence/replay did not carry enough source/provider lifecycle context, and replay could not verify non-readable source-failure runs without requiring a readable publication path.
  - 2026-05-03 -> Enforcement patch added explicit manual-file source exceptions, aggregate Yahoo source telemetry, partial response reason code, replay source expected/actual fields, evidence source context, command `source_mode` output, runtime/schema sync, registry sync, and `SourceProviderResilienceStaticGuardTest`.
  - 2026-05-03 -> Container `php -l` passed for changed PHP files; vendor/PHPUnit unavailable in uploaded ZIP, so local validation remains pending.
  - 2026-05-03 -> Operator-local validation returned FAIL for `tests/Unit/MarketData --filter Source` and `--filter Provider`: replay metrics incorrectly added actual `source_file_*` columns, and source/provider static guard expected camel-case `sourceFinalReasonCode` in `MarketDataPipelineService`.
  - 2026-05-03 -> Recovery patch removed actual replay `source_file_*` columns from runtime/schema/SQLite/repository, added cleanup migration for already-applied prior ZIP migration, and corrected the static guard to assert `source_final_reason_code`.
  - 2026-05-06 -> Operator-local targeted recovery validation PASS: `PublicApiEodBarsAdapterTest.php` 12 tests / 70 assertions; `MarketDataEvidenceExportServiceTest.php` 3 tests / 52 assertions; `ReadablePublicationReadContractIntegrationTest.php` 8 tests / 15 assertions; `ReplayVerificationServiceTest.php` 5 tests / 15 assertions.
  - 2026-05-06 -> Operator-local full validation PASS: `vendor/bin/phpunit tests/Unit/MarketData` -> 276 tests / 2722 assertions.
  - 2026-05-06 -> Source / Provider Resilience promoted to DONE after recovery validation confirmed no regression against full MarketData unit suite.

  [IMPLEMENTATION]
  - `LocalFileEodBarsAdapter` now throws `SourceAcquisitionException` with explicit manual-file reason codes for unsupported mode, missing file, unreadable file, malformed JSON/CSV, missing header, missing columns, and row/header mismatch.
  - `PublicApiEodBarsAdapter` now aggregates Yahoo per-ticker request telemetry, failed ticker codes, missing ticker codes, failure reason summary, attempt count, retry exhausted state, and final source status. Partial Yahoo source output uses `RUN_SOURCE_PARTIAL_RESPONSE` and remains subject to coverage gate.
  - `EodEvidenceRepository::dominantReasonCodes()` remains gated by valid readable pointer/publication/run context for readable-publication evidence, while source-failure evidence is exported through explicit source telemetry paths without leaking non-readable run reason codes into readable-only evidence queries.
  - `MarketDataEvidenceExportService` now includes `source_mode` and source final status in run evidence, allows non-readable run evidence without forcing publication read path, and exports replay actual/expected source context.
  - `ReplayVerificationService` now supports non-readable source-failure runs, persists source/provider lifecycle fields, and compares expected source context when fixtures provide it.
  - `ReplayResultRepository`, `Database_Schema_MariaDB.sql`, SQLite test schema, and migration `2026_05_03_000002_add_source_provider_context_to_replay_metrics.php` now carry replay source/provider context fields.
  - Recovery migration `2026_05_03_000003_drop_actual_source_file_columns_from_replay_metrics.php` removes actual replay `source_file_*` columns if the prior ZIP migration was already applied; replay keeps expected source file fields but does not persist actual source file hash columns in `md_replay_daily_metrics`.
  - `AbstractMarketDataCommand` now renders `source_mode` and merges source lifecycle telemetry into command/operator context while preserving existing source summary shape.
  - `Reason_Codes_Seed.sql` and `Reason_Codes_Registry.md` now include source partial/manual-file reason codes.
  - `SourceProviderResilienceStaticGuardTest` guards API retry/rate-limit/timeout/partial telemetry, manual-file/API identity separation, controlled source failure state, evidence/replay context, and forbidden latest trade-date shortcuts.

  [ENFORCEMENT]
  - Manual-file and API source identity remain separated: manual file reports `LOCAL_FILE` and provider `null`; API reports provider/source identity from API config.
  - Timeout and rate-limit keep explicit reason codes and attempt telemetry.
  - Partial Yahoo response is traceable as source partial context and still relies on coverage/finalize for publishability.
  - Non-readable source-failure runs can be evidenced/replayed for source context without pretending a readable publication exists.
  - Replay can fail on source mode/provider/retry/reason/file context mismatch when expected fields are supplied.
  - Static guard blocks regressions for silent source failure, identity mixing, missing source context, and latest trade-date shortcut patterns.

  [FINAL_BEHAVIOR]
  - DONE. Source/provider resilience is enforced by code/static guards and validated by operator-local targeted recovery suites plus full `tests/Unit/MarketData` PASS.

  [EVIDENCE]
  - Container confirmed uploaded ZIP has no `vendor/`; `vendor/bin/phpunit` unavailable.
  - Container static trace completed across source adapters, ingest, pipeline source failure/fallback, evidence, replay, repository, command, DB schema, registry, and static guard paths.
  - Container `php -l` passed for: `MarketDataEvidenceExportService.php`, `ReplayVerificationService.php`, `AbstractMarketDataCommand.php`, `LocalFileEodBarsAdapter.php`, `PublicApiEodBarsAdapter.php`, `EodEvidenceRepository.php`, `ReplayResultRepository.php`, new replay source migration, SQLite schema support, and `SourceProviderResilienceStaticGuardTest.php`.
  - Container runtime shortcut scan found no forbidden latest trade-date fallback patterns in app runtime paths; only static guard/test strings contain forbidden literals by design.
  - Operator-local validation evidence received: Source filter initially failed 2 tests and Provider filter initially failed 1 test due recovery issues in schema/static guard, not source/provider runtime behavior.
  - Recovery patch static validation completed in container.
  - Operator-local targeted recovery validation PASS: `vendor/bin/phpunit tests/Unit/MarketData/PublicApiEodBarsAdapterTest.php` -> 12 tests / 70 assertions; `MarketDataEvidenceExportServiceTest.php` -> 3 tests / 52 assertions; `ReadablePublicationReadContractIntegrationTest.php` -> 8 tests / 15 assertions; `ReplayVerificationServiceTest.php` -> 5 tests / 15 assertions.
  - Operator-local full validation PASS: `vendor/bin/phpunit tests/Unit/MarketData` -> 276 tests / 2722 assertions.

  [LOCK_CONDITION]
  - Satisfied for implementation DONE by operator-local targeted source/provider recovery validation and full `tests/Unit/MarketData` PASS.

---

- Correction Lifecycle Safety -> DONE

  [LAST_UPDATED] 2026-05-03

  [RELATED_CONTRACT] CORRECTION_LIFECYCLE_SAFETY_CONTRACT

  [REVIEW_STATUS] REVIEWED_OK

  [HISTORY]
  - 2026-05-03 -> Correction Lifecycle Safety session opened against latest source-of-truth ZIP.
  - 2026-05-03 -> Static trace reviewed correction baseline resolver, artifact diff, reseal, finalize pointer switch, fallback preservation, evidence export, replay verification, command surface, repository persistence, DB schema, and static guard coverage.
  - 2026-05-03 -> Gap found: correction evidence repository returned raw correction row only, while evidence export expected derived `prior_publication_id` and `new_publication_id`; runtime evidence could miss baseline/candidate publication context.
  - 2026-05-03 -> Gap found: artifact comparison was boolean-only; invalid/incomplete correction hashes were not represented as a deterministic guarded state.
  - 2026-05-03 -> Gap found: replay expected/actual state did not persist or compare correction lifecycle fields.
  - 2026-05-03 -> Gap found: correction command output exposed only correction id/status and hid unchanged/reseal/pointer/linkage context.
  - 2026-05-03 -> Enforcement patch added deterministic artifact comparison, invalid hash fail-fast before pointer switch, unchanged no-reseal/no-switch event context, correction evidence linkage derivation, replay correction lifecycle fields, command lifecycle output, DB/schema sync, and static guard coverage.
  - 2026-05-03 -> Operator-local validation returned migration PASS but targeted/full PHPUnit FAIL due recovery issues in evidence property access, stale `PublicationDiffService` mock expectations, and replay static guard string interpolation.
  - 2026-05-03 -> Recovery patch fixed guarded evidence field access, updated pipeline unit tests from `isUnchanged()` to `compare()` expectations, and corrected replay static guard assertion.
  - 2026-05-03 -> Operator-local recovery validation PASS: targeted Correction, Unchanged, Reseal, Hash, Evidence, Replay, Finalize, Publication suites and full `tests/Unit/MarketData` all passed; implementation promoted to DONE.

  [IMPLEMENTATION]
  - `PublicationDiffService` now returns explicit `INVALID`, `UNCHANGED`, or `CHANGED` decisions with reason code, changed scope, changed fields, and baseline/candidate hash context.
  - `MarketDataPipelineService::completeFinalize()` now blocks correction pointer switch when artifact comparison is invalid, treats unchanged artifacts as no-reseal/no-switch/no-new-current outcome, and requires changed artifact comparison before history promotion/reseal/pointer switch.
  - `EodEvidenceRepository::findCorrectionById()` now derives baseline/candidate publication ids, versions, run states, coverage states, seal states, and current flags from prior/new run linkage.
  - `MarketDataEvidenceExportService::exportCorrectionEvidence()` now writes `correction_lifecycle` context including changed decision, reseal status, baseline/candidate publication ids, run state, seal state, pointer/current state, and final outcome note.
  - `ReplayVerificationService`, `ReplayResultRepository`, SQL schema, migration, and SQLite mirror now carry correction lifecycle actual/expected fields and compare them when fixture expectations provide them.
  - `RunCorrectionCommand` now prints correction outcome, reseal status, baseline publication id, candidate publication id, candidate pointer switch state, and final outcome note.
  - `CorrectionLifecycleSafetyStaticGuardTest` guards baseline pointer resolution, no latest/MAX-date baseline shortcut, invalid diff blocking, unchanged candidate discard/no switch, changed reseal requirement, evidence linkage derivation, replay correction context, and command output.
  - Recovery patch keeps existing contract behavior unchanged and only fixes local regression causes exposed by the first operator PHPUnit run.

  [ENFORCEMENT]
  - Correction baseline remains pointer-resolved through current readable publication contract; no latest/MAX date path was introduced.
  - Correction pointer switch is blocked when artifact hashes are incomplete or baseline/candidate comparison is invalid.
  - Unchanged correction preserves previous current pointer and records `NOT_RESEALED_UNCHANGED` context.
  - Changed correction requires explicit changed artifact comparison before reseal/current promotion.
  - Evidence and replay now expose correction lifecycle context rather than hiding linkage/state mismatch.
  - Static guard prevents regression of baseline shortcut, invalid diff bypass, unchanged publication creation/switch, missing evidence/replay context, and weak command output.

  [FINAL_BEHAVIOR]
  - DONE. Correction baseline is pointer-resolved, unchanged correction preserves previous current readable publication without reseal/switch, changed correction requires deterministic artifact comparison and reseal/linkage validity, and evidence/replay/command surfaces expose correction lifecycle state.

  [EVIDENCE]
  - Container confirmed uploaded ZIP has no `vendor/` and no executable `vendor/bin/phpunit`.
  - Container static trace completed across correction baseline, artifact diff, reseal, pointer switch, fallback, evidence, replay, command, repository, DB schema, and static guard paths.
  - Container `php -l` passed for all changed PHP files.
  - Operator-local `php artisan migrate:fresh --env=testing` PASS through `2026_05_03_000001_add_correction_lifecycle_context_to_replay_metrics`.
  - Operator-local first PHPUnit pass exposed failures: Correction filter had 3 errors + 1 failure; full `tests/Unit/MarketData` had 5 errors + 1 failure; recovery patch addressed evidence property access, stale diff-service mocks, and static guard assertion drift.
  - Recovery ZIP container `php -l` passed for `MarketDataEvidenceExportService.php`, `ReplayVerificationService.php`, `MarketDataPipelineServiceTest.php`, and `CorrectionLifecycleSafetyStaticGuardTest.php`.
  - Operator-local recovery validation PASS: `Correction` 59 tests / 1146 assertions; `Unchanged` 9 / 63; `Reseal` 5 / 46; `Hash` 8 / 24; `Evidence` 27 / 241; `Replay` 25 / 257; `Finalize` 42 / 261; `Publication` 88 / 906.
  - Operator-local full validation PASS: `vendor/bin/phpunit tests/Unit/MarketData` -> 271 tests / 2613 assertions.

  [LOCK_CONDITION]
  - Satisfied by operator-local targeted correction lifecycle validation and full `tests/Unit/MarketData` PASS.
  - Implementation status promoted to DONE.

---

- Finalize / Lock / Pointer Determinism -> DONE

  [LAST_UPDATED] 2026-05-03

  [RELATED_CONTRACT] FINALIZE_LOCK_POINTER_DETERMINISM_CONTRACT

  [REVIEW_STATUS] REVIEWED_OK

  [HISTORY]
  - 2026-05-02 -> Finalize/lock/pointer determinism session opened against latest source-of-truth ZIP.
  - 2026-05-02 -> Static trace reviewed finalize decision, run lifecycle, publication promotion/current mutation, pointer resolver, fallback preservation, correction path, evidence export, replay verification, command surface, repository predicates, and static guards.
  - 2026-05-02 -> Existing enforcement confirmed: finalize promotion runs inside transaction, pointer promotion requires sealed/readable/coverage-valid target, post-switch pointer assertion throws on mismatch, fallback uses previous readable pointer context, and evidence/replay already carry pointer/publication state context.
  - 2026-05-02 -> Gap found: completed `SUCCESS + READABLE + current` finalize rerun could short-circuit on run state alone without proving that the current-readable pointer still resolved to the same run/publication/version.
  - 2026-05-02 -> Enforcement patch added pointer-resolved idempotency validation before completed-readable short-circuit and fail-safe handling for malformed/mismatched current pointer state.
  - 2026-05-02 -> Static guard and integration coverage were added for completed-success rerun with invalid pointer.
  - 2026-05-03 -> Operator local validation completed: migration, targeted finalize/idempotent/lock/pointer/publication/readable/correction/fallback/evidence/replay/command suites, full `tests/Unit/MarketData`, and required focused test files all PASS. Implementation promoted to DONE.

  [IMPLEMENTATION]
  - `MarketDataPipelineService::findCompletedFinalizeRun()` now treats `run_id` as the primary idempotency boundary but requires completed `SUCCESS + READABLE + is_current_publication = 1` runs to re-resolve through `EodPublicationRepository::findReadableCurrentPublicationForRun()` before returning the existing final outcome.
  - `MarketDataPipelineService::completedCurrentReadableRunStillPointerResolved()` compares resolved publication id, publication version, run id, and requested trade date against the completed run before allowing the idempotent short-circuit.
  - `MarketDataPipelineService::failSafeCompletedReadableRunPointerMismatch()` repairs stale run-current mirror when the authoritative pointer resolves to another valid current-readable run, or clears unsafe current pointer/publication state and holds the rerun as `HELD + NOT_READABLE` with `RUN_LOCK_CONFLICT` when the pointer is malformed.
  - `MarketDataPipelineIntegrationTest` covers rerun of a completed success run after pointer corruption and asserts no duplicate publication, no blind pointer switch, no current publication leak, and explicit `RUN_FINALIZE_IDEMPOTENCY_POINTER_INVALID` event.
  - `PublicationCurrentPointerReadinessStaticGuardTest` guards that completed-success idempotency calls pointer validation/fail-safe logic and that pointer validation compares publication/run/version identity.

  [ENFORCEMENT]
  - Completed `SUCCESS + READABLE + current` finalize rerun is not accepted from run state alone; it must be pointer-resolved to the same publication identity.
  - Malformed current pointer on rerun fails safe by preventing duplicate publication creation and preventing candidate/current leakage.
  - A valid pointer to another readable run is treated as authoritative and repairs stale run mirror instead of corrupting the existing current pointer.
  - Existing transaction-wrapped promotion, post-switch resolver assertion, coverage PASS requirement, and fallback/correction preservation remain in force.
  - Static guard prevents regression where idempotency bypasses current-readable pointer validation.

  [FINAL_BEHAVIOR]
  - DONE. Finalize rerun for the same run/date/state is idempotent only when the completed run's pointer/publication identity is still valid. If a previously completed readable run no longer resolves through the current-readable pointer contract, the system fails safe instead of returning a false readable outcome or creating a new publication/current pointer.

  [EVIDENCE]
  - Container static trace completed across finalize, pointer, repository, fallback, correction, evidence, replay, command, and static guard paths.
  - Container syntax validation passed for changed PHP files with `php -l`.
  - Operator local command: `php artisan migrate:fresh --env=testing` -> PASS.
  - Operator local command: `vendor/bin/phpunit tests/Unit/MarketData --filter "Finalize"` -> PASS; `OK (41 tests, 248 assertions)`.
  - Operator local command: `vendor/bin/phpunit tests/Unit/MarketData --filter "finalize"` -> PASS; `OK (41 tests, 248 assertions)`.
  - Operator local command: `vendor/bin/phpunit tests/Unit/MarketData --filter "Idempotent"` -> PASS; `OK (2 tests, 15 assertions)`.
  - Operator local command: `vendor/bin/phpunit tests/Unit/MarketData --filter "idempotent"` -> PASS; `OK (2 tests, 15 assertions)`.
  - Operator local command: `vendor/bin/phpunit tests/Unit/MarketData --filter "Lock"` -> PASS; `OK (16 tests, 87 assertions)`.
  - Operator local command: `vendor/bin/phpunit tests/Unit/MarketData --filter "lock"` -> PASS; `OK (16 tests, 87 assertions)`.
  - Operator local command: `vendor/bin/phpunit tests/Unit/MarketData --filter "Pointer"` -> PASS; `OK (57 tests, 633 assertions)`.
  - Operator local command: `vendor/bin/phpunit tests/Unit/MarketData --filter "pointer"` -> PASS; `OK (57 tests, 633 assertions)`.
  - Operator local command: `vendor/bin/phpunit tests/Unit/MarketData --filter "Publication"` -> PASS; `OK (85 tests, 887 assertions)`.
  - Operator local command: `vendor/bin/phpunit tests/Unit/MarketData --filter "readable"` -> PASS; `OK (51 tests, 309 assertions)`.
  - Operator local command: `vendor/bin/phpunit tests/Unit/MarketData --filter "Correction"` -> PASS; `OK (54 tests, 1081 assertions)`.
  - Operator local command: `vendor/bin/phpunit tests/Unit/MarketData --filter "Fallback"` -> PASS; `OK (29 tests, 609 assertions)`.
  - Operator local command: `vendor/bin/phpunit tests/Unit/MarketData --filter "Evidence"` -> PASS; `OK (26 tests, 228 assertions)`.
  - Operator local command: `vendor/bin/phpunit tests/Unit/MarketData --filter "Replay"` -> PASS; `OK (24 tests, 237 assertions)`.
  - Operator local command: `vendor/bin/phpunit tests/Unit/MarketData --filter "Command"` -> PASS; `OK (52 tests, 331 assertions)`.
  - Operator local command: `vendor/bin/phpunit tests/Unit/MarketData` -> PASS; `OK (264 tests, 2542 assertions)`.
  - Operator local command: `vendor/bin/phpunit tests/Unit/MarketData/MarketDataPipelineIntegrationTest.php` -> PASS; `OK (53 tests, 1191 assertions)`.
  - Operator local command: `vendor/bin/phpunit tests/Unit/MarketData/FinalizeDecisionServiceTest.php` -> PASS; `OK (13 tests, 66 assertions)`.
  - Operator local command: `vendor/bin/phpunit tests/Unit/MarketData/PublicationFinalizeOutcomeServiceTest.php` -> PASS; `OK (12 tests, 52 assertions)`.
  - Operator local command: `vendor/bin/phpunit tests/Unit/MarketData/ReadablePublicationReadContractIntegrationTest.php` -> PASS; `OK (8 tests, 15 assertions)`.

  [LOCK_CONDITION]
  - DONE after operator local validation confirmed targeted finalize/idempotent/lock/pointer/publication/readable/correction/fallback/evidence/replay/command suites, full `tests/Unit/MarketData`, and focused pipeline/finalize/outcome/readable test files all PASS.
  - Reopen only if a future finalize/pointer/lock/fallback/correction/evidence/replay/command/repository path changes this idempotency or pointer-determinism behavior.
---

- Publishability State Integrity / No Invalid State Combination -> DONE

  [LAST_UPDATED] 2026-05-02

  [RELATED_CONTRACT] PUBLISHABILITY_STATE_INTEGRITY_CONTRACT

  [REVIEW_STATUS] REVIEWED_OK

  [HISTORY]
  - 2026-05-02 -> Publishability state integrity session opened against latest source-of-truth ZIP.
  - 2026-05-02 -> Static trace reviewed finalize decision, publication outcome, invariant guard, pointer repository, fallback/correction preservation, evidence export, replay verification, command surface, schema mirror, and static guards.
  - 2026-05-02 -> Gap found: publication outcome could treat missing candidate/resolved pointer identity as a match because null identifiers were string-cast into empty strings.
  - 2026-05-02 -> Gap found: post-switch pointer resolver assertion in `EodPublicationRepository` returned false on mismatch but promotion/restore callers did not fail the transaction.
  - 2026-05-02 -> Gap found: evidence/replay context did not fully persist and compare publishability/publication/current-pointer state fields.
  - 2026-05-02 -> Static enforcement patch added explicit publication identity checks before READABLE outcome, throwing post-switch pointer assertions, replay state-context persistence/comparison, command surface fields, schema/migration sync, and static/test coverage.
  - 2026-05-02 -> Operator local validation exposed regression: valid publication promotion/correction paths were downgraded to HELD because post-switch integrity detection reported `RUN_PUBLICATION_ID_MISMATCH`.
  - 2026-05-02 -> Recovery patch added missing `ptr.publication_id as pointer_publication_id` aliases to pointer-resolved publication queries, switched post-switch assertion to inspect raw pointer state before readable resolution, and removed repository-level persisted READABLE priming from the promotion method itself.
  - 2026-05-02 -> Operator local validation after Recovery-1 confirmed pointer suite PASS but valid finalize/publication/correction/evidence paths still downgraded to HELD, proving the remaining regression sits in pipeline finalize priming/outcome flow rather than repository pointer switching.
  - 2026-05-02 -> Recovery-2 replaced Lumen-unsafe `now()` usage in `prepareRunForPointerSwitch()` with `Carbon::now(config('market_data.platform.timezone'))` so the persisted run is actually pre-approved as SUCCESS + READABLE before repository pointer validation.
  - 2026-05-02 -> Recovery-2 changed pipeline finalize to re-resolve the current readable publication through the pointer resolver after promotion before passing publication identity to outcome resolution.
  - 2026-05-02 -> Operator local validation after Recovery-2 confirmed `Publication`, `Evidence`, and `MarketDataPipelineIntegrationTest` PASS; remaining full-suite errors were isolated to two `MarketDataPipelineServiceTest` Mockery expectations that did not model the new authoritative `resolveCurrentReadablePublicationForTradeDate()` proof.
  - 2026-05-02 -> Recovery-3 aligned the two correction finalize unit tests with the enforced post-promotion resolver proof without weakening assertions or changing runtime contract.
  - 2026-05-02 -> Final operator local validation after Recovery-3 passed targeted `Finalize`, targeted `Correction`, and full `tests/Unit/MarketData`; implementation promoted to DONE.

  [IMPLEMENTATION]
  - `PublicationFinalizeOutcomeService` now requires explicit candidate/resolved current publication identity before READABLE outcome and rejects unchanged correction when current readable pointer identity is not proven.
  - `EodPublicationRepository` now throws on unresolved/mismatched post-switch pointer state so invalid promotion/restore rolls back instead of silently continuing.
  - `EodPublicationRepository` recovery patch now carries `pointer_publication_id` aliases through pointer-resolved rows and validates post-switch state from raw pointer/publication/run mirrors before returning a readable-resolved row.
  - `MarketDataPipelineService` Recovery-2 now persists pre-approved run state with `Carbon::now(config('market_data.platform.timezone'))` instead of the unavailable `now()` helper and uses `resolveCurrentReadablePublicationForTradeDate()` as the authoritative post-promotion identity proof.
  - `MarketDataPipelineServiceTest` Recovery-3 now mocks that same authoritative resolver proof in correction publish/conflict tests so unit-level expectations match the runtime contract already proven by integration tests.
  - Candidate promotion now requires the run to already be pre-approved as `SUCCESS + READABLE` before pointer switch and validates the intended final run identity in memory before persisting publication/current mirrors.
  - `MarketDataEvidenceExportService` now exports run/publication/pointer/fallback state context including terminal status, publishability state, coverage state, publication identity, seal/current state, and pointer validation result.
  - `ReplayVerificationService` and `ReplayResultRepository` now carry and compare expected/actual publishability/publication/current-pointer context.
  - Replay schema, migration, SQLite mirror, schema sync test, command summary output, and static guard tests were updated for the new state context.

  [ENFORCEMENT]
  - READABLE publication outcome requires non-empty current publication id/version and candidate/resolved identity match.
  - Post-switch current pointer resolution is mandatory and throws on no pointer, publication mismatch, run mismatch, or integrity reason.
  - Pointer-resolved queries must expose pointer publication identity (`pointer_publication_id`) so mirror validation cannot compare run publication id against an absent alias.
  - Pipeline finalize must not trust the object returned by `promoteCandidateToCurrent()` alone; it must re-read through the current-readable pointer resolver before a READABLE outcome is accepted.
  - Run pre-approval before pointer switch must use Lumen-safe timestamp handling so DB priming is not swallowed and converted into false HELD outcomes.
  - Replay verification compares terminal status, publishability state, publication id, publication run id, and current-publication flag when expected context exists.
  - Static guard now prevents reintroducing the null-string publication identity match and pointer post-switch `return false` behavior.

  [FINAL_BEHAVIOR]
  - DONE. Invalid run/publication/pointer state fails safe as NOT_READABLE/HELD/controlled exception, while valid sealed/current/pointer-mirrored promotion has two required proofs: persisted pre-approved run state and authoritative current-readable pointer resolver identity.
  - Fallback preservation remains limited to previous readable pointer context; malformed fallback pointer cannot invent a readable effective date through the patched outcome path.

  [EVIDENCE]
  - Container syntax validation: changed PHP files passed `php -l` with no syntax errors.
  - Container static scan: runtime fallback/pointer read paths do not introduce `MAX(trade_date)`, `max('trade_date')`, `latest('trade_date')`, or direct `orderByDesc('trade_date')` shortcut for consumer read resolution.
  - PHPUnit/artisan not run in container because the uploaded ZIP does not contain `vendor/`.
  - Operator local validation after the first patch: `migrate:fresh` PASS; Publishability, Fallback, Replay, Command, FinalizeDecisionService, PublicationFinalizeOutcomeService, and ReadablePublicationReadContractIntegrationTest PASS; full `tests/Unit/MarketData` failed with 4 errors and 6 failures driven by `RUN_PUBLICATION_ID_MISMATCH`/valid runs becoming HELD.
  - Operator local validation after Recovery-1: pointer filter PASS (`OK (54 tests, 602 assertions)`), while Publication/Finalize/Correction/Evidence/Pipeline/full suite still failed because valid runs remained HELD and evidence export correctly rejected the resulting non-readable runs.
  - Operator local validation after Recovery-2: `Publication` PASS (`OK (83 tests, 864 assertions)`), `Evidence` PASS (`OK (26 tests, 228 assertions)`), and `MarketDataPipelineIntegrationTest` PASS (`OK (52 tests, 1182 assertions)`); full `tests/Unit/MarketData` had only two remaining Mockery expectation errors in `MarketDataPipelineServiceTest`.
  - Container Recovery-3 validation: changed PHP test file passed `php -l`; static trace confirms unit tests now model the post-promotion resolver proof required by runtime code.
  - Operator local validation after Recovery-3: `vendor/bin/phpunit tests/Unit/MarketData --filter "Finalize"` -> PASS; `OK (39 tests, 225 assertions)`.
  - Operator local validation after Recovery-3: `vendor/bin/phpunit tests/Unit/MarketData --filter "Correction"` -> PASS; `OK (54 tests, 1081 assertions)`.
  - Operator local validation after Recovery-3: `vendor/bin/phpunit tests/Unit/MarketData` -> PASS; `OK (262 tests, 2519 assertions)`.

  [GAP]
  - None for this scoped session after final local validation.

  [NEXT_ACTION]
  - Continue one-by-one audit governance for the next market-data contract scope only when a new scoped session is opened.

  [FINAL_RULE]
  - DONE. No market-data path may expose a publication as READABLE/current unless terminal status, publishability state, sealed/current publication state, coverage PASS, run-publication mirror, pointer resolver, fallback/correction safety, evidence/replay context, and command surface agree on the same valid publication identity.

  [FINAL_CONSTRAINT]
  - Reopen this implementation only if a future finalize/publication/pointer/fallback/correction/evidence/replay/command/repository path changes publishability state behavior or introduces a new readable/current state combination.

---

- Coverage Gate Enforcement / No Coverage Bypass -> DONE

  [LAST_UPDATED] 2026-05-02

  [RELATED_CONTRACT] COVERAGE_GATE_ENFORCEMENT_CONTRACT

  [REVIEW_STATUS] REVIEWED_OK

  [HISTORY]
  - 2026-05-01 -> Coverage gate enforcement session opened against latest source-of-truth ZIP.
  - 2026-05-01 -> Static trace reviewed coverage evaluator, finalize decision, publication outcome, pointer repository, pipeline finalize, evidence/replay/command coverage surfaces, and related tests.
  - 2026-05-01 -> Gap found: PASS coverage state could be used as the primary readable/current gate without proving expected/available/missing/ratio/threshold/mode/basis/contract completeness.
  - 2026-05-01 -> Static enforcement patch added complete coverage telemetry validation before READABLE, promotion_allowed, pointer target, and fallback target states.
  - 2026-05-01 -> Coverage evaluator now counts unique universe tickers, emits canonical coverage basis/contract/reason fields, and returns NOT_EVALUABLE for empty universe instead of any implicit PASS path.
  - 2026-05-01 -> Pointer/readable repository predicates now require persisted coverage telemetry fields, not only coverage_gate_state = PASS.
  - 2026-05-01 -> Static guard and service tests were added/updated to prevent coverage bypass regression.
  - 2026-05-01 -> Operator local validation showed failures: static guard used `base_path()` in a plain Container test, `coverage_gate_status`/`coverage_gate_state` alias conflict could hide FAIL, mocked finalize decisions lacked full coverage summary, and valid baseline/fallback fixtures lacked required coverage telemetry.
  - 2026-05-01 -> Recovery patch replaced static guard path resolution, made conflicting coverage gate aliases fail-safe, completed coverage summaries in service mocks, aligned readable/correction/fallback fixtures with strict telemetry, extended evidence/eligibility read predicates to require complete coverage telemetry, and exposed `coverage_threshold_mode` in command output payload/summary.

  - 2026-05-02 -> Operator final local validation passed: pipeline integration, pointer, coverage, finalize, publication, readable, evidence, replay, command, core service tests, static guard, and full `tests/Unit/MarketData` all PASS. Entry promoted to DONE.

  [IMPLEMENTATION]
  - `MarketDataInvariantGuard` rejects READABLE/promotion/current/fallback targets unless coverage PASS has complete expected/available/missing/ratio/threshold/mode/basis/contract telemetry and consistent count/ratio math.
  - `FinalizeDecisionService` normalizes coverage aliases and downgrades incomplete PASS coverage to NOT_EVALUABLE with `RUN_COVERAGE_NOT_EVALUABLE`.
  - `PublicationFinalizeOutcomeService` carries coverage summary into final outcome guard validation.
  - `CoverageGateEvaluator` uses unique universe ticker count, deduped available ticker count, deterministic missing count, ratio, threshold, basis, contract version, and reason code aliases.
  - `EodPublicationRepository` requires complete run coverage telemetry on readable pointer resolution and pointer/fallback integrity checks.
  - `EodPublicationRepository` now re-validates pointer/fallback rows with `MarketDataInvariantGuard` after query resolution so non-null telemetry alone cannot bypass count/ratio/threshold consistency.
  - `EligibilitySnapshotScopeRepository` and `EodEvidenceRepository` now require full persisted coverage telemetry, not only `coverage_gate_state = PASS`, before returning readable consumer/evidence data.
  - Pipeline finalize guard states and RUN_FINALIZED payloads now carry coverage mode/basis/contract context.

  [ENFORCEMENT]
  - Static guard added: `CoverageGateNoBypassStaticGuardTest`.
  - Guard tests now assert incomplete PASS coverage fails fast.
  - Finalize/outcome tests now include complete coverage context and explicit downgrade behavior for incomplete PASS.
  - Repository integration fixtures were aligned with strict coverage telemetry requirements.
  - Recovery patch updated pipeline/readable fixtures and mocked finalize decisions so tests prove the stricter contract instead of passing through incomplete PASS context.

  [FINAL_BEHAVIOR]
  - DONE. Coverage FAIL or NOT_EVALUABLE cannot produce READABLE/current publication through patched guard, finalize, outcome, or pointer repository paths.
  - Incomplete PASS coverage is treated as NOT_EVALUABLE and fail-safe, not readable.
  - Pointer resolution requires SUCCESS + READABLE + PASS plus complete coverage telemetry fields.

  [EVIDENCE]
  - Container static scan: no forbidden MAX/trade-date shortcut found in runtime coverage/finalize/evidence/replay paths.
  - Container syntax validation: changed PHP files passed `php -l` with no syntax errors.
  - Operator local command: `vendor/bin/phpunit tests/Unit/MarketData/MarketDataPipelineIntegrationTest.php` -> PASS; `OK (52 tests, 1182 assertions)`.
  - Operator local command: `vendor/bin/phpunit tests/Unit/MarketData --filter "pointer"` -> PASS; `OK (52 tests, 586 assertions)`.
  - Operator local command: `vendor/bin/phpunit tests/Unit/MarketData` -> PASS; `OK (258 tests, 2461 assertions)`.
  - Operator local command: `vendor/bin/phpunit tests/Unit/MarketData --filter "coverage"` -> PASS; `OK (38 tests, 283 assertions)`.
  - Operator local command: `vendor/bin/phpunit tests/Unit/MarketData --filter "finalize"` -> PASS; `OK (37 tests, 216 assertions)`.
  - Operator local command: `vendor/bin/phpunit tests/Unit/MarketData --filter "Publication"` -> PASS; `OK (79 tests, 836 assertions)`.
  - Operator local command: `vendor/bin/phpunit tests/Unit/MarketData --filter "readable"` -> PASS; `OK (49 tests, 297 assertions)`.
  - Operator local command: `vendor/bin/phpunit tests/Unit/MarketData --filter "Evidence"` -> PASS; `OK (26 tests, 216 assertions)`.
  - Operator local command: `vendor/bin/phpunit tests/Unit/MarketData --filter "Replay"` -> PASS; `OK (24 tests, 215 assertions)`.
  - Operator local command: `vendor/bin/phpunit tests/Unit/MarketData --filter "Command"` -> PASS; `OK (52 tests, 327 assertions)`.
  - Operator local command: `vendor/bin/phpunit tests/Unit/MarketData/CoverageGateEvaluatorTest.php` -> PASS; `OK (4 tests, 38 assertions)`.
  - Operator local command: `vendor/bin/phpunit tests/Unit/MarketData/FinalizeDecisionServiceTest.php` -> PASS; `OK (13 tests, 66 assertions)`.
  - Operator local command: `vendor/bin/phpunit tests/Unit/MarketData/PublicationFinalizeOutcomeServiceTest.php` -> PASS; `OK (10 tests, 43 assertions)`.
  - Operator local command: `vendor/bin/phpunit tests/Unit/MarketData/CoverageGateNoBypassStaticGuardTest.php` -> PASS; `OK (4 tests, 96 assertions)`.

  [GAP]
  - None for this scope after final local validation.

  [NEXT_ACTION]
  - No immediate action. Reopen only if a future coverage/finalize/publication/pointer/evidence/replay/command path changes the contract.

  [FINAL_CONSTRAINT]
  - DONE for the current source-of-truth ZIP. Future changes must preserve no-coverage-bypass enforcement and rerun targeted/full MarketData tests.

---

- Read-Side Enforcement / Anti Bypass Total → DONE

  [LAST_UPDATED] 2026-05-01

  [RELATED_CONTRACT] READ_SIDE_POINTER_ENFORCEMENT_CONTRACT

  [REVIEW_STATUS] REVIEWED_OK

  [HISTORY]
  - 2026-05-01 → Read-side anti-bypass session opened against the latest source-of-truth ZIP.
  - 2026-05-01 → Static trace reviewed repository, service, command, evidence, replay, test, DB schema, and locked book-contract surfaces for market-data read paths.
  - 2026-05-01 → `EligibilitySnapshotScopeRepository` was hardened to require `coverage_gate_state = PASS` and run mirror match before returning pointer-scoped eligibility rows.
  - 2026-05-01 → `EodEvidenceRepository` was hardened so publication lookup, eligibility export, and reason-code export require pointer/current/readable/PASS/mirror-valid context.
  - 2026-05-01 → Static guard and integration tests were extended to prevent regression of coverage-gate and run-mirror enforcement.
  - 2026-05-01 → Operator local PHPUnit evidence showed 4 MarketData integration regressions in correction/fallback behavior after run-mirror enforcement was applied too broadly to the internal prior-readable fallback lookup.
  - 2026-05-01 → Regression patch restored `EodPublicationRepository::findLatestReadablePublicationBefore` as an internal pipeline fallback resolver while keeping consumer gateway, evidence, and eligibility scope mirror-enforced.
  - 2026-05-01 → Operator retest confirmed targeted readable/pointer tests, full MarketData suite, readable-publication integration test, and pointer static guard all PASS after the regression patch.

  [IMPLEMENTATION]
  - Consumer eligibility scope reads are pointer-scoped through `eod_current_publication_pointer`, `eod_publications`, and `eod_runs`.
  - Evidence eligibility export returns rows only when the requested publication is the current pointer target and the run is `SUCCESS`, `READABLE`, `coverage_gate_state = PASS`, current, sealed, and mirror-aligned.
  - Evidence dominant reason-code export stops with an empty result when the publication/run context is not current-readable/PASS/mirror-valid, preventing event reason leakage from invalid read contexts.
  - Prior-readable fallback lookup remains a pipeline/internal fallback path, not a public consumer resolver; it preserves fallback/correction behavior without weakening consumer read enforcement.
  - The locked read-side contract document explicitly requires coverage PASS and run mirror validation for consumer read gateways.

  [ENFORCEMENT]
  - Static guards assert official pointer gateway predicates, consumer no-latest/no-MAX rules, pointer-scoped eligibility predicates, coverage PASS, and run publication mirror checks.
  - Integration tests cover no-leak behavior for non-PASS coverage and run/publication mirror mismatch.
  - Raw/current artifact table access remains allowed only for ingestion, build, seal/finalize, admin/repair, evidence invalid-row sampling, and test fixtures.
  - Internal fallback lookup is explicitly classified as `ALLOWED_INTERNAL_PIPELINE_FALLBACK`, not a consumer read gateway.

  [FINAL_BEHAVIOR]
  - DONE. Market-data consumer read paths are pointer-resolved, current-readable, publication-scoped, coverage-PASS, and fail-safe.
  - No patched read-side consumer may return eligibility rows or evidence reason codes unless current pointer, sealed publication, SUCCESS/READABLE/PASS run, current mirror, run mirror, and publication scope all match.
  - If the readable pointer context is absent or invalid, patched read paths return an empty controlled result or controlled failure; they do not fallback to raw/staging/latest/current artifact shortcuts.
  - Correction/fallback pipeline behavior remains valid after the regression patch: internal prior-readable lookup can preserve prior current readable publication without becoming a consumer latest shortcut.

  [EVIDENCE]
  - Static scan: no consumer app path uses `MAX(trade_date)`, `max('trade_date')`, `latest('trade_date')`, or `orderByDesc('trade_date')` as a consumer readable-data resolver.
  - Static scan: direct `eod_bars`, `eod_indicators`, and `eod_eligibility` app access is isolated to artifact build/write/finalize repositories or pointer-scoped evidence/scope reads.
  - Static scan: no market-data HTTP/controller read path exists in the current source tree.
  - Container syntax validation: changed PHP files passed `php -l`.
  - Local command: `php artisan migrate:fresh --env=testing` → PASS; migrations completed successfully through `2026_04_27_000001_expand_coverage_gate_state_not_evaluable`.
  - Local command: `vendor/bin/phpunit tests/Unit/MarketData --filter "readable"` → PASS; `OK (45 tests, 256 assertions)`.
  - Local command: `vendor/bin/phpunit tests/Unit/MarketData --filter "pointer"` → PASS; `OK (51 tests, 551 assertions)`.
  - Local command: `vendor/bin/phpunit tests/Unit/MarketData` → PASS; `OK (250 tests, 2355 assertions)`.
  - Local command: `vendor/bin/phpunit tests/Unit/MarketData/ReadablePublicationReadContractIntegrationTest.php` → PASS; `OK (8 tests, 15 assertions)`.
  - Local command: `vendor/bin/phpunit tests/Unit/MarketData/PublicationCurrentPointerReadinessStaticGuardTest.php` → PASS; `OK (3 tests, 23 assertions)`.

  [FINAL_CONSTRAINT]
  - This implementation is DONE for the current source-of-truth ZIP.
  - Future read-side changes must not create duplicate audit entries for this scope; append reconciliation notes under this canonical implementation concern.
  - Any future consumer read path must resolve current readable publication via pointer, enforce SUCCESS/READABLE/PASS and run mirror checks, and fail-safe without raw/staging/latest fallback.


---

- Audit Rebuild Baseline / One-by-One Regression Review → DONE

  [LAST_UPDATED] 2026-05-01

  [RELATED_CONTRACT] AUDIT_REBUILD_BASELINE_CONTRACT

  [REVIEW_STATUS] REVIEWED_OK

  [HISTORY]
  - 2026-05-01 → Clean audit rebuild started; previous broad DONE list intentionally removed from active implementation status until one-by-one retest evidence is supplied.
  - 2026-05-01 → First retested scope completed through DB Schema & Migration Sync final validation; clean rebuild workflow is now proven usable.

  [IMPLEMENTATION]
  - Operational audit remains in clean-start retest mode.
  - DB Schema & Migration Sync is the first restored DONE implementation scope under the cleaned governance model.
  - Duplicate DB schema hotfix entries were merged into a single canonical implementation entry.

  [ENFORCEMENT]
  - New DONE entries require current validation evidence.
  - Duplicate entries for the same implementation concern must be merged into a canonical entry with HISTORY, FINAL_BEHAVIOR, and EVIDENCE preserved.
  - Contract mapping remains mandatory through `LUMEN_CONTRACT_TRACKER.md`.

  [FINAL_BEHAVIOR]
  - The clean audit rebuild process is active as the operating audit model, and the first validated scope has been recorded without carrying forward unverified historical DONE claims.

  [EVIDENCE]
  - DB Schema & Migration Sync implementation entry below records the first completed validation scope with local migration and PHPUnit evidence.

  [FINAL_CONSTRAINT]
  - Future audit restoration must continue one scope at a time and must not reintroduce broad DONE/LOCKED claims without fresh evidence.

---

- DB Schema & Migration Sync / Runtime Schema Four-Way Synchronization → DONE

  [LAST_UPDATED] 2026-05-01

  [RELATED_CONTRACT] DB_SCHEMA_AND_MIGRATION_SYNC_CONTRACT

  [REVIEW_STATUS] REVIEWED_OK

  [HISTORY]
  - 2026-05-01 → Static schema inventory compared `Database_Schema_MariaDB.sql`, migration output expectations, SQLite market-data mirror, and market-data repository/query usage.
  - 2026-05-01 → SQLite-only orphan surrogate keys were removed from current/history artifact tables and runtime composite keys were enforced in the test mirror.
  - 2026-05-01 → Replay metric index names and ticker timestamp update behavior were synchronized between SQL schema, migration, and test expectations.
  - 2026-05-01 → `md_session_snapshots` migration idempotency was fixed after local `migrate:fresh` exposed duplicate-table failure.
  - 2026-05-01 → Correction reexecution policy migration idempotency was fixed after local `migrate:fresh` exposed duplicate-column failure on `execution_count`.
  - 2026-05-01 → Local migration evidence confirmed `php artisan migrate:fresh --env=testing` completed successfully through `2026_04_27_000001_expand_coverage_gate_state_not_evaluable`.
  - 2026-05-01 → Stricter SQLite schema exposed fixture drift on `tickers.created_at` and `eod_runs.source`; test fixtures/default mirrors were corrected without weakening runtime constraints.
  - 2026-05-01 → Repository/current-pointer fixtures and restore-prior validation were aligned with pointer/publication/run mirror integrity requirements.
  - 2026-05-01 → Pipeline correction promotion failure handling was aligned to preserve a valid prior readable fallback effective date while keeping failed candidate publication non-current and non-readable.
  - 2026-05-01 → Final local validation passed for schema guard, repository-targeted tests, pipeline integration tests, and the full MarketData PHPUnit suite.
  - 2026-05-01 → Audit recovery applied: prior DB schema cleanup/hotfix/final-closure entries were merged into this canonical implementation entry.

  [IMPLEMENTATION]
  - `tests/Support/UsesMarketDataSqlite.php` no longer creates SQLite-only surrogate keys on `eod_bars`, `eod_indicators`, `eod_eligibility`, `eod_bars_history`, `eod_indicators_history`, and `eod_eligibility_history`.
  - SQLite mirror uses runtime composite identities for canonical artifact tables: `(trade_date, ticker_id)`.
  - SQLite mirror uses runtime composite identities for publication-bound history tables: `(publication_id, trade_date, ticker_id)`.
  - SQLite mirror includes runtime-aligned indexes/default behavior required by repository/test usage.
  - `docs/market_data/db/Database_Schema_MariaDB.sql` uses replay index names aligned with runtime migration sync: `idx_replay_daily_comparison`, `idx_replay_daily_coverage_gate`, and `idx_replay_daily_artifact_scope`.
  - `database/migrations/2026_03_22_000001_create_tickers_table.php` aligns `tickers.updated_at` update behavior with the SQL schema timestamp contract.
  - `database/migrations/2026_03_24_000002_create_md_session_snapshots_table.php` is idempotent when the locked schema path already created `md_session_snapshots`.
  - `database/migrations/2026_04_23_000004_add_correction_reexecution_policy_fields.php` adds correction reexecution policy fields only when missing.
  - `tests/Unit/MarketData/MarketDataSqliteSchemaSyncTest.php` guards against reintroducing runtime-orphan surrogate keys.
  - Repository and read-contract tests seed runtime-required `eod_runs` fields instead of relying on looser SQLite behavior.
  - `EodPublicationRepository` validates prior fallback run readability and publication mirror integrity before restoring a prior current publication.
  - `MarketDataPipelineService` keeps fail-safe correction behavior while retaining a valid fallback effective date when promotion fails after a valid prior readable publication is resolved.
  - Pipeline fixtures use idempotent seeding where runtime composite uniqueness would otherwise reject duplicate current artifact rows.

  [ENFORCEMENT]
  - SQLite tests can no longer pass with artifact/history identity columns that do not exist in MariaDB.
  - Runtime schema constraints remain authoritative; tests and fixtures must satisfy them instead of weakening the schema mirror.
  - Migration chain is safe for the project’s canonical SQL-schema bootstrap path and later additive migrations.
  - Repository restore-prior behavior rejects invalid fallback targets before pointer restoration.
  - Current pointer replacement and fallback restoration require aligned pointer/publication/run mirror state.
  - Composite artifact uniqueness remains enforced in SQLite tests.

  [FINAL_BEHAVIOR]
  - DONE. Market-data DB schema, migration chain, SQLite test schema, repository/query usage, fixtures, and correction fallback behavior are synchronized for the current source-of-truth ZIP.
  - A clean `migrate:fresh` path is valid.
  - The schema guard, repository-targeted tests, pipeline integration tests, and full MarketData PHPUnit suite are green.
  - Failed correction promotion remains fail-safe: the candidate is not published, prior current publication is preserved, the run stays HELD/NOT_READABLE, and a valid prior readable fallback date is retained only when resolved from the fallback publication lookup.

  [FINAL_CONSTRAINT]
  - Future market-data schema changes must update and validate all affected layers together: `Database_Schema_MariaDB.sql`, Laravel/Lumen migrations, SQLite test schema, repository/query usage, test fixtures, and audit records.
  - Field drift, nullable/default drift, index/unique drift, orphan test-only columns, and repository usage of non-schema fields must be fixed directly or recorded as an explicit policy gap before any new DONE claim.
  - Test failures caused by stricter runtime-aligned SQLite constraints must be resolved by fixing fixtures or implementation, not by relaxing the SQLite mirror.

  [EVIDENCE]
  - Local command: `php artisan migrate:fresh --env=testing` → PASS; all listed market-data migrations completed successfully through `2026_04_27_000001_expand_coverage_gate_state_not_evaluable`.
  - Local command: `vendor/bin/phpunit tests/Unit/MarketData --filter "schema"` → PASS; `OK (3 tests, 70 assertions)`.
  - Local command: `vendor/bin/phpunit tests/Unit/MarketData --filter "Repository"` → PASS; `OK (33 tests, 180 assertions)`.
  - Local command: `vendor/bin/phpunit tests/Unit/MarketData/MarketDataPipelineIntegrationTest.php` → PASS; `OK (52 tests, 1182 assertions)`.
  - Local command: `vendor/bin/phpunit tests/Unit/MarketData` → PASS; `OK (244 tests, 2327 assertions)`.
  - Container static validation during the session: changed PHP files passed `php -l` before local PHPUnit reruns.

## Recovery-3 malformed fallback pointer fix — Coverage Gate Enforcement / No Coverage Bypass

- Status: SUPERSEDED_BY_FINAL_LOCK.
- Local evidence received: static guard, Coverage, Publication, readable, Evidence, Replay, and Command suites passed; one integration/pointer failure remained for malformed fallback pointer effective-date handling.
- Recovery-3 fix: when correction pointer mismatch occurs and no contract-valid readable fallback exists, `trade_date_effective` is explicitly cleared to null instead of retaining the requested candidate date.
- Final result: superseded by Recovery-5 final local validation; `MarketDataPipelineIntegrationTest`, pointer filter, and full `tests/Unit/MarketData` all PASS.

## Recovery-4 fallback mirror fixture alignment — Coverage Gate Enforcement / No Coverage Bypass

- Status: SUPERSEDED_BY_FINAL_LOCK.
- Local evidence received after Recovery-3: static guard, Coverage, Publication, readable, Evidence, Replay, and Command suites passed; remaining failures were isolated to correction fallback/effective-date and low-coverage fallback preservation.
- Recovery-4 fix: `seedReadableFallbackPublication()` now mirrors `eod_runs.publication_id` to the seeded fallback publication id instead of hard-coding publication `1`, so strict pointer/publication/run mirror validation can resolve valid fallback baselines while still rejecting malformed fallback pointers.
- Recovery-4 fix: correction baseline pointer mismatch messages are classified as pointer-integrity failures, so failed correction promotion preserves prior current state and uses the contract-valid fallback date when one resolves.
- Final result: superseded by Recovery-5 final local validation; `MarketDataPipelineIntegrationTest`, pointer filter, and full `tests/Unit/MarketData` all PASS.

## Recovery-5 baseline pointer mismatch message preservation — Coverage Gate Enforcement / No Coverage Bypass

- Status: DONE / LOCKED by final local validation.
- Local evidence after Recovery-5: `MarketDataPipelineIntegrationTest`, pointer filter, targeted coverage/finalize/publication/readable/evidence/replay/command suites, core service tests, static guard, and full `tests/Unit/MarketData` all PASS.
- Recovery-5 fix: pointer-integrity handling now preserves the explicit `Correction baseline no longer matches current publication pointer` note instead of collapsing it to the generic post-finalize pointer mismatch message.
- Final lock completed for Coverage Gate Enforcement / No Coverage Bypass.

## Hash / Seal / Dataset Integrity — Recovery round 3

- Status: DONE / LOCKED by final local validation.
- Local evidence received before final recovery: `Artifact` and `Evidence` filters PASS; remaining failures isolated to replacement promote/finalize seal precondition because mandatory candidate hashes were incomplete.
- Recovery: replacement candidates now create candidate-bound bars history from current live bars when no candidate bars history exists, then compute/hash/seal against history scope without mutating sealed baseline live rows.
- Final validation: `vendor/bin/phpunit tests/Unit/MarketData --filter "Finalize"` PASS with `OK (46 tests, 355 assertions)`; `vendor/bin/phpunit tests/Unit/MarketData --filter "Integration"` PASS with `OK (91 tests, 1443 assertions)`; full `vendor/bin/phpunit tests/Unit/MarketData` PASS with `OK (329 tests, 4110 assertions)`.
- Final lock completed for Hash / Seal / Dataset Integrity.
