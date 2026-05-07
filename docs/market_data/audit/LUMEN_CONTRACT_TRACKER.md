# LUMEN_CONTRACT_TRACKER

## ACTIVE SESSION

ACTIVE SESSION:
- Test Coverage Behavioral

[SESSION_STATUS] LOCKED

[SESSION_SCOPE]
- Define and enforce `TEST_COVERAGE_BEHAVIORAL_CONTRACT` so critical market-data test coverage is evaluated by runtime-like behavior proof rather than mock-heavy unit confidence.
- New proof added for manual-file import-only and manual-file promote coverage-gate behavior.
- Container validation was static-only because uploaded ZIP has no `vendor/`; operator-local targeted and full PHPUnit validation later passed, so the contract is LOCKED.

[SESSION_GOAL]
- Behavioral test coverage must prove lifecycle state, reason codes, pointer safety, coverage impact, evidence/replay proof, and read-side fail-safe behavior through real DB-backed execution wherever feasible.

---
## OPERATIONAL STATUS

[CURRENT_AUDIT_MODE]
- CLEAN_START_RETEST

[HISTORICAL_STATUS_POLICY]
- Previous DONE/LOCKED contract claims are not copied as current status without fresh scoped evidence.
- Contract status is rebuilt one concern at a time and mapped to implementation evidence.
- Revalidated contracts must be represented as canonical entries, not repeated hotfix/session fragments.

[DEFAULT_RULE]
- No contract may be marked DONE without current implementation evidence.
- No contract may be marked LOCKED without FINAL_RULE and VALIDATED evidence.
- One contract concern must have one canonical tracker entry.

---

## CURRENT WORKING CONTRACT

- TEST_COVERAGE_BEHAVIORAL_CONTRACT -> LOCKED

  [LAST_UPDATED] 2026-05-07

  [RELATED_IMPLEMENTATION] Test Coverage Behavioral

  [REVIEW_STATUS] REVIEWED_OK

  [HISTORY]
  - 2026-05-07 -> Contract opened as canonical test coverage behavioral contract under audit governance.
  - 2026-05-07 -> Static trace found that core lifecycle areas already have DB-backed integration proof, but command surface tests are internal mock-heavy and must not be counted as lifecycle proof.
  - 2026-05-07 -> Gap found and patched: manual-file import-only behavior now has explicit DB-backed proof that it writes candidate bars without finalize, seal, coverage gate, current publication, or pointer switch.
  - 2026-05-07 -> Gap found and patched: manual-file promote from an imported partial dataset now has explicit DB-backed proof that coverage gate blocks readable publication and pointer switch with reason-coded finalization.
  - 2026-05-07 -> Behavioral coverage inventory and static guard were added to keep critical proof classification stable.
  - 2026-05-07 -> Contract held at ENFORCED because uploaded ZIP has no `vendor/`; operator-local targeted/full PHPUnit was required before LOCKED.
  - 2026-05-07 -> Operator-local targeted validation PASS: manual-file import-only 1 test / 19 assertions; manual-file promote partial 1 test / 17 assertions; `TestCoverageBehavioralStaticGuardTest.php` 5 tests / 108 assertions.
  - 2026-05-07 -> Operator-local filtered validation PASS: Behavior, Integration, Pipeline, Finalize, Coverage, Pointer, Correction, Replay, Evidence, Readable, Command, Manual, and Source filters all passed.
  - 2026-05-07 -> Operator-local focused file validation PASS: pipeline integration, readable publication contract, replay verification, replay determinism static guard, market-data evidence export, and ops command surface tests all passed.
  - 2026-05-07 -> Operator-local full validation PASS: `vendor/bin/phpunit tests/Unit/MarketData` -> 298 tests / 3327 assertions.
  - 2026-05-07 -> Contract promoted from ENFORCED to LOCKED after targeted, filtered, focused file, static guard, integration, and full MarketData unit validation passed.

  [DEFINED]
  - Lifecycle-critical coverage must be proven by runtime-like DB-backed tests whenever the behavior mutates run/publication/pointer/evidence/replay state.
  - Unit tests, command surface tests, static guards, and mock-heavy orchestration tests may support proof but must not be treated as primary lifecycle proof.
  - Internal repository/service mocks cannot be used to claim finalize, coverage, pointer, fallback, correction, replay, evidence, or read-side behavior is fully proven.
  - Boundary mocks are allowed only for external provider API, file input isolation, clock/time, command IO, or documented orchestration shells.
  - PASS/DONE/LOCKED requires local targeted and full MarketData PHPUnit validation.

  [IMPLEMENTED]
  - `docs/market_data/tests/Behavioral_Test_Coverage_Inventory.md` records area-level coverage, mock level, runtime proof status, gaps, and action.
  - `MarketDataPipelineIntegrationTest` includes explicit manual-file import-only and manual-file promote coverage-gate DB-backed tests.
  - `TestCoverageBehavioralStaticGuardTest` enforces inventory presence, DB-backed proof files, pipeline proof names, command-support classification, and static-guard-as-support rule.
  - Existing DB-backed proof files remain canonical for pipeline, repository, pointer/read-side, correction, replay result persistence, and SQLite schema.

  [ENFORCED]
  - Import-only cannot be accepted as publishable proof: test asserts unsealed non-current candidate, no pointer, no finalize event, and no coverage/seal/hash state.
  - Manual-file promote cannot bypass coverage: test asserts coverage FAIL, NOT_READABLE, no current pointer/publication, coverage counts, promote context, and reason-coded finalize event.
  - Static guard prevents lifecycle proof files from becoming internal Mockery/`shouldReceive` based.
  - Static guard requires command surface mock-heavy status to stay explicit and support-only.

  [VALIDATED]
  - Container static trace completed.
  - `php -l tests/Unit/MarketData/MarketDataPipelineIntegrationTest.php` -> no syntax errors detected.
  - `php -l tests/Unit/MarketData/TestCoverageBehavioralStaticGuardTest.php` -> no syntax errors detected.
  - Operator-local targeted validation PASS: manual-file import-only 1 test / 19 assertions; manual-file promote partial 1 test / 17 assertions; `TestCoverageBehavioralStaticGuardTest.php` 5 tests / 108 assertions.
  - Operator-local filtered validation PASS: Behavior 5 tests / 108 assertions; Integration 91 tests / 1443 assertions; Pipeline 91 tests / 1432 assertions; Finalize 44 tests / 311 assertions; Coverage 48 tests / 527 assertions; Pointer 65 tests / 837 assertions; Correction 61 tests / 1208 assertions; Replay 34 tests / 550 assertions; Evidence 34 tests / 520 assertions; Readable 54 tests / 375 assertions; Command 58 tests / 475 assertions; Manual 21 tests / 227 assertions; Source 35 tests / 386 assertions.
  - Operator-local focused file validation PASS: `MarketDataPipelineIntegrationTest.php` 55 tests / 1227 assertions; `ReadablePublicationReadContractIntegrationTest.php` 8 tests / 15 assertions; `ReplayVerificationServiceTest.php` 6 tests / 17 assertions; `ReplayDeterminismStaticGuardTest.php` 5 tests / 155 assertions; `MarketDataEvidenceExportServiceTest.php` 3 tests / 87 assertions; `OpsCommandSurfaceTest.php` 42 tests / 260 assertions.
  - Operator-local full validation PASS: `vendor/bin/phpunit tests/Unit/MarketData` -> 298 tests / 3327 assertions.

  [FINAL_RULE]
  - LOCKED. Behavioral coverage may be claimed only when lifecycle-critical behavior is backed by runtime-like DB/state proof, negative/fail-safe assertions, reason-code assertions, and regression static guards. Mock-heavy command/service/repository tests and static guards remain support evidence only and must not be used as primary lifecycle proof. Manual-file import-only must remain non-publishable, while manual-file promote must enforce coverage before any readable/current pointer switch.

  [NEXT_ACTION]
  - None for this contract. Keep future test additions aligned with the locked mock policy and behavioral inventory.

- REPLAY_DETERMINISM_CONTRACT -> LOCKED

  [LAST_UPDATED] 2026-05-07

  [RELATED_IMPLEMENTATION] Replay Determinism

  [REVIEW_STATUS] REVIEWED_OK

  [HISTORY]
  - 2026-05-07 -> Contract opened as canonical replay determinism contract under audit governance.
  - 2026-05-07 -> Static trace found fixture metadata/completeness, reason-coded mismatch, non-readable actual proof, replay artifact schema, command output, and registry gaps.
  - 2026-05-07 -> Enforcement patch implemented fixture schema v2 validation, complete expected/actual lifecycle context comparison, reason-coded mismatch families, fail-safe proof-incomplete behavior, deterministic-vs-volatile field separation, replay artifact persistence, command proof summary, fixture updates, and static guards.
  - 2026-05-07 -> Contract held at ENFORCED because container cannot run PHPUnit/artisan without `vendor/`; operator-local targeted and full validation still required before LOCKED.
  - 2026-05-07 -> Operator-local targeted validation PASS: replay verifier, replay static guard, replay evidence export, market-data evidence export, and ops command surface tests passed.
  - 2026-05-07 -> Operator-local filtered validation PASS: Replay/replay, Evidence, Command, Coverage, Pointer, Finalize, Correction, Manual, and Source filters passed.
  - 2026-05-07 -> Operator-local integration validation PASS: MarketData pipeline integration and readable publication read contract integration passed.
  - 2026-05-07 -> Operator-local full validation PASS: `vendor/bin/phpunit tests/Unit/MarketData` -> 291 tests / 3183 assertions.
  - 2026-05-07 -> Contract promoted from ENFORCED to LOCKED after targeted, filtered, integration, static guard, and full MarketData unit validation passed.

  [DEFINED]
  - Replay fixture is the expected proof source and must be stable, versioned, schema-checked, and self-contained.
  - Replay actual proof must come from evidence lifecycle context, not raw/staging/latest/MAX-date shortcut or volatile current DB state as expected source.
  - Replay must compare run, requested/effective date, request/source/provider/manual-file, coverage, artifact/hash/seal, publication, pointer, fallback, correction, final reason, and lineage context.
  - Every mismatch must have an explicit replay reason code and be persisted/exported in replay artifact/evidence.
  - Replay must ignore documented volatile runtime fields only; deterministic fields remain compared.
  - Incomplete fixture/actual proof must fail safe and cannot become wildcard PASS.

  [IMPLEMENTED]
  - `ReplayVerificationService` fixture loading, expected proof validation, actual evidence context building, comparison, mismatch reason coding, volatile-field tracking, and non-readable run handling.
  - `ReplayResultRepository`, migration, SQL schema, and SQLite test schema replay metric columns for fixture metadata, expected/actual contexts, mismatches, mismatch reason codes, deterministic fields checked, ignored volatile fields, and final reason code.
  - `MarketDataEvidenceExportService` replay export context extensions.
  - `VerifyReplayCommand` operator-grade output.
  - `ReplayDeterminismStaticGuardTest`, updated `ReplayVerificationServiceTest`, replay fixture v2 packages, and reason-code registry/seed entries.

  [ENFORCED]
  - `REPLAY_FIXTURE_SCHEMA_MISMATCH` for missing/incompatible fixture schema.
  - `REPLAY_EXPECTED_PROOF_INCOMPLETE` for missing expected fixture sections/files.
  - `REPLAY_ACTUAL_PROOF_INCOMPLETE` for missing actual run proof.
  - Specific replay reason-code families for source, provider, coverage, artifact/seal, publication, pointer, fallback, correction, final status/reason, lineage, unexpected success/failure, and non-deterministic output.
  - Static guard prevents latest/MAX/raw/staging shortcut usage in replay verifier/commands/repository and requires command/artifact/schema/registry surfaces.

  [VALIDATED]
  - Container static trace completed.
  - Container `php -l` passed for changed PHP files.
  - Operator-local targeted replay/evidence/command/static guard validation PASS.
  - Operator-local filtered replay/evidence/command/coverage/pointer/finalize/correction/manual/source validation PASS.
  - Operator-local integration validation PASS.
  - Operator-local full `tests/Unit/MarketData` validation PASS: 291 tests / 3183 assertions.

  [FINAL_RULE]
  - LOCKED. Replay may only produce deterministic MATCH when stable expected fixture proof and actual lifecycle proof match under explicit comparison. Any missing proof or divergent deterministic field must produce a failed/mismatched replay result with clear reason code. Replay verification must not mutate fixtures, derive expected from actual, or use latest/MAX/raw/staging shortcuts.

  [NEXT_ACTION]
  - None for replay determinism. Future replay changes must preserve this contract and re-run targeted plus full MarketData validation before any tracker change.

---

## VERIFIED CONTRACT ENTRIES

- SOURCE_PROVIDER_RESILIENCE_CONTRACT -> LOCKED

  [LAST_UPDATED] 2026-05-06

  [RELATED_IMPLEMENTATION] Source / Provider Resilience

  [REVIEW_STATUS] REVIEWED_OK

  [HISTORY]
  - 2026-05-03 -> Canonical source/provider resilience contract opened under audit governance.
  - 2026-05-03 -> Static trace reconciled existing source identity, ingest, degraded source, fallback preservation, coverage gate, finalize/publishability, evidence export, replay verification, command output, reason-code registry, repository persistence, and DB schema contracts.
  - 2026-05-03 -> Gap found: manual-file failure reason codes were not explicit because missing/unreadable/malformed input paths used generic runtime exceptions.
  - 2026-05-03 -> Gap found: Yahoo provider request telemetry was last-request based and did not aggregate per-ticker attempts/failures/missing tickers.
  - 2026-05-03 -> Gap found: partial provider output lacked a distinct source lifecycle reason code separate from coverage failure reason.
  - 2026-05-03 -> Gap found: evidence/replay did not fully persist and compare source/provider lifecycle context.
  - 2026-05-03 -> Enforcement patch added explicit manual-file source exceptions, aggregate Yahoo attempt/failure telemetry, source partial response reason code, source context evidence/replay persistence/comparison, command source-mode output, schema/registry sync, and static guards.
  - 2026-05-03 -> Operator-local validation found recovery gaps: `md_replay_daily_metrics` must not persist actual `source_file_*` columns, and static guard must assert the pipeline snake-case `source_final_reason_code` field instead of unrelated camel-case naming.
  - 2026-05-03 -> Recovery patch reconciled replay schema with existing SQLite contract, added a cleanup migration for prior-ZIP actual source file columns, and corrected source/provider static guard assertion.
  - 2026-05-06 -> Recovery-2 validation confirmed targeted source/provider recovery suites PASS: `PublicApiEodBarsAdapterTest.php` 12 tests / 70 assertions; `MarketDataEvidenceExportServiceTest.php` 3 tests / 52 assertions; `ReadablePublicationReadContractIntegrationTest.php` 8 tests / 15 assertions; `ReplayVerificationServiceTest.php` 5 tests / 15 assertions.
  - 2026-05-06 -> Full operator-local validation PASS: `vendor/bin/phpunit tests/Unit/MarketData` -> 276 tests / 2722 assertions.
  - 2026-05-06 -> Contract promoted from ENFORCED to LOCKED after targeted and full MarketData unit validation passed.

  [DEFINED]
  - Source mode must be explicit and immutable for the run lifecycle.
  - API and manual-file source identities must not be mixed.
  - Timeout, rate-limit, retry exhaustion, manual-file missing/unreadable/malformed, and partial source response must have explicit reason codes.
  - Source failure must not create `SUCCESS + READABLE`, switch pointer, make candidate current, hide reason code, or bypass coverage/finalize.
  - Partial source output must remain under coverage gate and finalize/publishability decision.
  - Valid source fallback must use internal previous readable publication resolver only, never raw/staging/latest/MAX-date shortcut.
  - Evidence, replay, and command surfaces must expose source/provider lifecycle context.

  [IMPLEMENTED]
  - `LocalFileEodBarsAdapter` maps manual-file input failures to explicit `SourceAcquisitionException` reason codes.
  - `PublicApiEodBarsAdapter` aggregates Yahoo per-ticker telemetry and marks partial source output with `RUN_SOURCE_PARTIAL_RESPONSE`.
  - `MarketDataEvidenceExportService` preserves source-failure evidence through explicit source telemetry paths, while `EodEvidenceRepository::dominantReasonCodes()` remains gated by valid readable pointer/publication/run context to prevent non-readable reason-code leakage.
  - `ReplayVerificationService` and `ReplayResultRepository` persist and compare source/provider expected/actual context.
  - Runtime migration, SQL schema, and SQLite mirror include replay source/provider lifecycle columns.
  - Replay actual source file hash columns are intentionally not persisted in `md_replay_daily_metrics`; only expected source file fields remain there, while run/publication/evidence context keeps source file identity where the schema already permits it.
  - `AbstractMarketDataCommand` exposes source mode and source lifecycle context for operator output/artifacts.
  - Reason-code registry/seed includes partial/manual-file source codes.
  - `SourceProviderResilienceStaticGuardTest` protects source/provider resilience invariants.

  [ENFORCED]
  - Manual file is `LOCAL_FILE` with provider `null`; API remains provider-backed and does not read manual file.
  - Source timeout/rate-limit/retry attempt context is carried to run/evidence/command and replay.
  - Partial provider output is not silently full success; it is traceable and still coverage-gated.
  - Non-readable source-failure run evidence/replay does not require a fake readable publication path.
  - Replay can detect source mode/provider/reason/retry/file context mismatch when fixture expectations provide those fields.
  - Static guard blocks identity mixing, silent source failure, missing source lifecycle context, and latest-date shortcut patterns.

  [VALIDATED]
  - Container static trace completed.
  - Container `php -l` passed for all changed PHP files.
  - `vendor/` and `vendor/bin/phpunit` are absent from uploaded ZIP; PHPUnit/artisan validation was performed operator-local.
  - Container runtime shortcut scan found no forbidden latest trade-date fallback patterns in app runtime paths; forbidden literals exist only in static guard/test docs by design.
  - Operator-local first validation failed for Source/Provider filters due schema/static-guard recovery issues; recovery patch was applied.
  - Operator-local targeted source/provider recovery validation PASS: `PublicApiEodBarsAdapterTest.php` -> 12 tests / 70 assertions; `MarketDataEvidenceExportServiceTest.php` -> 3 tests / 52 assertions; `ReadablePublicationReadContractIntegrationTest.php` -> 8 tests / 15 assertions; `ReplayVerificationServiceTest.php` -> 5 tests / 15 assertions.
  - Operator-local full validation PASS: `vendor/bin/phpunit tests/Unit/MarketData` -> 276 tests / 2722 assertions.

  [FINAL_RULE]
  - LOCKED. Source/provider failure must remain explicit, traceable, and non-readable unless coverage/finalize produce a valid readable publication or internal fallback preserves a previous readable publication. API/manual-file identity, timeout/retry/rate-limit telemetry, partial response handling, evidence/replay source context, command output, and pointer preservation are protected by code/static guards and validated by targeted plus full MarketData unit PASS evidence.

  [LOCK_CONDITION]
  - Satisfied by operator-local targeted source/provider recovery validation and full `tests/Unit/MarketData` PASS.

---

- CORRECTION_LIFECYCLE_SAFETY_CONTRACT -> LOCKED

  [LAST_UPDATED] 2026-05-03

  [RELATED_IMPLEMENTATION] Correction Lifecycle Safety

  [REVIEW_STATUS] REVIEWED_OK

  [HISTORY]
  - 2026-05-03 -> Canonical correction lifecycle safety contract opened under audit governance.
  - 2026-05-03 -> Static trace reconciled existing finalize/lock/pointer determinism, coverage gate enforcement, read-side pointer enforcement, publishability state integrity, fallback preservation, artifact seal, evidence export, replay verification, command output, repository persistence, and DB schema contracts.
  - 2026-05-03 -> Gap found: correction evidence did not reliably derive baseline/candidate publication ids from prior/new run linkage.
  - 2026-05-03 -> Gap found: artifact diff was boolean-only and lacked explicit invalid/incomplete hash state, reason code, changed scope, and hash context.
  - 2026-05-03 -> Gap found: replay did not persist/compare correction lifecycle context, allowing correction expected/actual drift to remain hidden.
  - 2026-05-03 -> Gap found: correction command did not display unchanged/reseal/baseline/candidate pointer state.
  - 2026-05-03 -> Enforcement patch added deterministic artifact comparison, invalid diff fail-fast, unchanged no-reseal/no-switch context, correction evidence linkage derivation, replay correction expected/actual fields, command lifecycle output, DB/schema sync, and static guards.
  - 2026-05-03 -> Operator-local validation returned migration PASS but targeted/full PHPUnit FAIL due evidence `seal_state` access, stale `PublicationDiffService` mock expectations, and static guard string interpolation.
  - 2026-05-03 -> Recovery patch fixed those regressions without changing the final correction lifecycle contract rule.
  - 2026-05-03 -> Operator-local recovery validation PASS: targeted Correction, Unchanged, Reseal, Hash, Evidence, Replay, Finalize, Publication suites and full `tests/Unit/MarketData` all passed; contract promoted to LOCKED.

  [DEFINED]
  - Correction baseline must be current-readable pointer-resolved and must satisfy `SUCCESS + READABLE + SEALED + coverage PASS + run/publication mirror`.
  - Correction baseline must not use `MAX(trade_date)`, `latest('trade_date')`, `orderByDesc('trade_date')`, latest successful run, sealed-only fallback, raw/staging shortcut, or arbitrary latest date shortcut.
  - Unchanged artifacts must produce unchanged/no-reseal/no-pointer-switch/no-new-current behavior.
  - Changed artifacts must produce reseal/pointer switch only after complete deterministic hash comparison and valid linkage.
  - Evidence/replay/command surfaces must expose correction lifecycle context.

  [IMPLEMENTED]
  - `PublicationDiffService::compare()` defines `INVALID`, `UNCHANGED`, and `CHANGED` decisions with reason code and hash context.
  - `MarketDataPipelineService::completeFinalize()` blocks invalid correction artifact comparison before pointer switch and requires `CHANGED` before correction history promotion/reseal path.
  - `EodEvidenceRepository` derives correction publication context from prior/new run linkage.
  - `MarketDataEvidenceExportService` writes `correction_lifecycle` with baseline/candidate/run/seal/current/reseal/changed/final-outcome context.
  - `ReplayVerificationService` and `ReplayResultRepository` carry and compare correction lifecycle context when fixture expectations provide it.
  - Runtime migration, SQL schema, and SQLite mirror include correction lifecycle replay columns.
  - `RunCorrectionCommand` outputs correction outcome, reseal status, baseline publication id, candidate publication id, candidate switch state, and final outcome note.
  - `CorrectionLifecycleSafetyStaticGuardTest` guards the critical static invariants.
  - Recovery patch aligns tests and evidence access with the enforced `PublicationDiffService::compare()` contract.

  [ENFORCED]
  - Invalid/incomplete correction hashes cannot proceed to pointer switch.
  - Unchanged correction keeps previous current readable publication and records `NOT_RESEALED_UNCHANGED` context.
  - Changed correction requires explicit changed artifact comparison before reseal/promotion.
  - Replay can compare correction expected/actual lifecycle fields and fail on mismatch when expected fields are present.
  - Evidence derives correction publication context from durable run/publication linkage rather than assuming non-schema correction columns.
  - Command output no longer hides correction lifecycle state.

  [VALIDATED]
  - Container static trace completed.
  - Container `php -l` passed for all changed PHP files.
  - `vendor/` and `vendor/bin/phpunit` are absent from uploaded ZIP; no PHPUnit/artisan command was run in container.
  - Operator-local migration PASS.
  - Operator-local first PHPUnit validation FAIL: Correction filter 3 errors + 1 failure; full `tests/Unit/MarketData` 5 errors + 1 failure; focused PipelineIntegration, PublicationFinalizeOutcome, and ReadablePublicationReadContractIntegration PASS.
  - Recovery ZIP container `php -l` passed for changed recovery files.
  - Operator-local recovery validation PASS: `Correction` 59 tests / 1146 assertions; `Unchanged` 9 / 63; `Reseal` 5 / 46; `Hash` 8 / 24; `Evidence` 27 / 241; `Replay` 25 / 257; `Finalize` 42 / 261; `Publication` 88 / 906.
  - Operator-local full validation PASS: `vendor/bin/phpunit tests/Unit/MarketData` -> 271 tests / 2613 assertions.

  [FINAL_RULE]
  - LOCKED. Correction may publish a new readable current publication only when baseline is pointer-resolved, artifacts are complete and changed, reseal/linkage is valid, and post-switch pointer resolution matches the candidate. Unchanged or invalid corrections must preserve the previous current readable publication and expose the lifecycle outcome in evidence/replay/command surfaces.

  [LOCK_CONDITION]
  - Satisfied by operator-local targeted correction lifecycle validation and full `tests/Unit/MarketData` PASS.

---

- FINALIZE_LOCK_POINTER_DETERMINISM_CONTRACT -> LOCKED

  [LAST_UPDATED] 2026-05-03

  [RELATED_IMPLEMENTATION] Finalize / Lock / Pointer Determinism

  [REVIEW_STATUS] REVIEWED_OK

  [HISTORY]
  - 2026-05-02 -> Canonical finalize/lock/pointer determinism contract opened under audit governance.
  - 2026-05-02 -> Static trace reconciled existing publishability state integrity, coverage gate enforcement, read-side pointer enforcement, fallback preservation, correction safety, evidence export, replay verification, command output, repository persistence, and static guards.
  - 2026-05-02 -> Existing contract coverage confirmed: pointer promotion is transaction-protected, post-switch pointer resolver mismatch throws, readable resolver enforces SUCCESS/READABLE/PASS/SEALED/current/mirror state, and fallback does not use raw/staging/latest shortcut.
  - 2026-05-02 -> Gap found and patched: completed `SUCCESS + READABLE + current` finalize rerun could return idempotently from run state without re-validating current-readable pointer identity.
  - 2026-05-02 -> Enforcement added: completed-readable rerun must resolve through the current-readable pointer contract to the same run/publication/version; malformed pointer fails safe as `HELD + NOT_READABLE + RUN_LOCK_CONFLICT` without duplicate publication or pointer switch.
  - 2026-05-02 -> Static guard and integration test were added for the idempotency pointer corruption edge.
  - 2026-05-03 -> Operator local validation passed migration, targeted finalize/idempotent/lock/pointer/publication/readable/correction/fallback/evidence/replay/command suites, full `tests/Unit/MarketData`, and required focused test files. Contract promoted to LOCKED.

  [DEFINED]
  - Finalize idempotency boundary is `run_id`, constrained by requested trade date and the persisted final state.
  - Completed `SUCCESS + READABLE + current` finalize rerun is valid only when current-readable pointer resolution returns the same run id, publication id, publication version, and trade date.
  - Pointer validity requires sealed current publication, run/publication mirror consistency, run terminal status SUCCESS, publishability state READABLE, coverage PASS, and pointer-resolved identity.
  - Lock/promotion mutation must remain atomic: invalid post-switch state rolls back or fails safe without leaving candidate current.
  - Fallback/correction must preserve previous readable pointer context and must not invent effective date or switch to latest/MAX date shortcut.

  [IMPLEMENTED]
  - `MarketDataPipelineService` validates completed-readable idempotency through `findReadableCurrentPublicationForRun()` before short-circuiting.
  - `MarketDataPipelineService` fails safe when a completed-readable rerun no longer resolves to the same current-readable pointer identity.
  - Existing `EodPublicationRepository` current-readable resolver remains the authoritative pointer gate and enforces SUCCESS/READABLE/PASS/SEALED/current/mirror predicates.
  - Existing promotion path remains transaction-wrapped and post-switch resolver-asserted.
  - Integration and static guard tests cover the idempotency pointer corruption edge.

  [ENFORCED]
  - Completed readable rerun cannot return from run state alone.
  - Malformed current pointer cannot keep a run exposed as readable through finalize idempotency.
  - Duplicate publication/current pointer creation is blocked on completed-run rerun.
  - Static guard checks the presence of pointer validation, identity comparison, fail-safe clearing, explicit event, and `RUN_LOCK_CONFLICT` reason code.
  - Runtime tests confirm finalize/idempotent/lock/pointer/publication/readable/correction/fallback/evidence/replay/command paths remain compatible with the contract.

  [VALIDATED]
  - Container static scan completed.
  - Container `php -l` passed for changed PHP files.
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

  [FINAL_RULE]
  - LOCKED. Finalize rerun may return an existing final outcome only if the final run state and current-readable pointer identity still agree. A completed `SUCCESS + READABLE` run with malformed/mismatched pointer must fail safe and must not create another publication, switch pointer blindly, or expose an invalid readable/current state.
  - Pointer-valid readable state requires `SUCCESS + READABLE + PASS + SEALED + current + pointer-resolved + run/publication mirror` consistency.
  - Fallback/correction paths must preserve deterministic previous-readable pointer context and must not use latest/MAX/raw/staging shortcuts.

  [LOCK_CONDITION]
  - LOCKED after operator local validation confirmed targeted finalize/idempotent/lock/pointer/publication/readable/correction/fallback/evidence/replay/command suites, full `tests/Unit/MarketData`, and focused pipeline/finalize/outcome/readable test files all PASS.
  - Reopen only if a future finalize/pointer/lock/fallback/correction/evidence/replay/command/repository path changes this idempotency or pointer-determinism contract.
---

- PUBLISHABILITY_STATE_INTEGRITY_CONTRACT -> LOCKED

  [LAST_UPDATED] 2026-05-02

  [RELATED_IMPLEMENTATION] Publishability State Integrity / No Invalid State Combination

  [REVIEW_STATUS] REVIEWED_OK

  [HISTORY]
  - 2026-05-02 -> Canonical publishability state integrity contract opened under audit governance.
  - 2026-05-02 -> Static trace reconciled existing coverage gate, read-side pointer, finalize, fallback, correction, evidence, replay, command, DB schema, and static guard contracts.
  - 2026-05-02 -> Gap found and patched: missing publication identity could be treated as a readable pointer match through null-to-empty-string comparison.
  - 2026-05-02 -> Gap found and patched: post-switch pointer mismatch returned false instead of failing promotion/restore transaction.
  - 2026-05-02 -> Gap found and patched: evidence/replay did not fully carry and compare state context for publishability/publication/current-pointer identity.
  - 2026-05-02 -> Operator local validation exposed a false post-switch integrity failure: valid promotions were rejected with `RUN_PUBLICATION_ID_MISMATCH` because pointer-resolved rows did not expose `pointer_publication_id`.
  - 2026-05-02 -> Recovery patch requires pointer publication identity aliases on resolver rows and validates raw pointer/publication/run mirrors before resolving the current readable publication.
  - 2026-05-02 -> Recovery-1 local validation proved pointer switching now PASS but finalize still downgraded valid paths to HELD.
  - 2026-05-02 -> Recovery-2 requires Lumen-safe Carbon timestamp DB priming before pointer switch and requires pipeline finalize to re-resolve the current readable publication through the pointer resolver before accepting READABLE outcome.
  - 2026-05-02 -> Operator local validation after Recovery-2 confirmed the repository/integration/evidence contract path is healthy; remaining failures were unit-test mock expectations that omitted the enforced post-promotion readable resolver proof.
  - 2026-05-02 -> Recovery-3 updates the unit proof surface to require `resolveCurrentReadablePublicationForTradeDate()` in correction publish/conflict tests, preserving the stricter contract while unblocking final local validation.
  - 2026-05-02 -> Final operator local validation passed targeted `Finalize`, targeted `Correction`, and full `tests/Unit/MarketData`; contract promoted to LOCKED.

  [DEFINED]
  - `READABLE` is valid only when run terminal status is SUCCESS, run publishability state is READABLE, coverage gate is PASS with complete telemetry, publication is SEALED, publication is current, pointer resolves to the same publication/run/version, and run-publication mirror fields match.
  - `NOT_READABLE`, `HELD`, or controlled failure is required when coverage, seal, pointer, mirror, fallback, correction baseline, or state context is invalid.
  - `HELD` may preserve only a previous readable publication resolved through the fallback/pointer contract.
  - Candidate publications must not become consumer-readable unless they pass the complete state matrix.

  [IMPLEMENTED]
  - Publication outcome now requires explicit candidate/current identity before READABLE and rejects unchanged correction if current pointer identity is unproven.
  - Pointer promotion/restore now fails transaction on unresolved or mismatched post-switch current-readable pointer state.
  - Pointer-resolved repository rows now carry `pointer_publication_id`, and post-switch assertion uses raw pointer state to distinguish real mirror violations from missing selected aliases.
  - Candidate promotion requires a pre-approved `SUCCESS + READABLE` run before pointer switch, validates intended final READABLE identity in memory, and persists run publication/current mirrors only after pointer/publication switch state is written.
  - Pipeline pre-approval uses `Carbon::now(config('market_data.platform.timezone'))` to avoid silently failing DB priming in Lumen contexts where the `now()` helper is unavailable.
  - Pipeline outcome uses `resolveCurrentReadablePublicationForTradeDate()` after promotion as the authoritative proof of current-readable publication identity.
  - Unit-level correction finalize tests now model the same resolver proof instead of implicitly treating `promoteCandidateToCurrent()` return value as sufficient proof.
  - Evidence export now includes run terminal status, publishability state, coverage state, publication identity/version/seal/current state, and pointer validation context.
  - Replay verification and replay result persistence now include expected/actual terminal, publishability, publication id, publication run id, and current-publication state context.
  - Command output now surfaces effective date, publication id/version, and current-publication flag when available.

  [ENFORCED]
  - Static guards assert no readable outcome from missing publication identity.
  - Static guards assert post-switch pointer checks throw instead of returning false.
  - Static guards assert pointer-resolved current rows select `ptr.publication_id as pointer_publication_id` and post-switch checks inspect raw pointer integrity.
  - Static guards assert pipeline finalize uses Lumen-safe Carbon timestamp priming and authoritative pointer resolver proof before readable outcome.
  - Static guards assert evidence/replay contain publishability and publication/current-pointer state fields.
  - Schema sync tests assert SQL, migration, and SQLite replay metric state-context columns.

  [VALIDATED]
  - Container static scan completed.
  - Container `php -l` completed for changed PHP files.
  - Local PHPUnit/artisan validation was supplied by operator because `vendor/` is absent from the uploaded ZIP.
  - Operator local validation after first patch showed migration and several targeted suites PASS, but full MarketData suite failed with valid promotion/correction paths becoming HELD due to `RUN_PUBLICATION_ID_MISMATCH`; Recovery-1 patch applied.
  - Operator local validation after Recovery-1: pointer filter PASS, but Publication/Finalize/Correction/Evidence/Pipeline/full suite still failed because valid finalize paths remained HELD; Recovery-2 patch applied.
  - Operator local validation after Recovery-2: Publication, Evidence, and PipelineIntegration all PASS; full suite had only two remaining Mockery expectation errors in `MarketDataPipelineServiceTest`; Recovery-3 patch applied.
  - Operator local validation after Recovery-3: `vendor/bin/phpunit tests/Unit/MarketData --filter "Finalize"` -> PASS; `OK (39 tests, 225 assertions)`.
  - Operator local validation after Recovery-3: `vendor/bin/phpunit tests/Unit/MarketData --filter "Correction"` -> PASS; `OK (54 tests, 1081 assertions)`.
  - Operator local validation after Recovery-3: `vendor/bin/phpunit tests/Unit/MarketData` -> PASS; `OK (262 tests, 2519 assertions)`.

  [FINAL_RULE]
  - LOCKED. No market-data path may expose a publication as READABLE/current unless run, publication, pointer, coverage, fallback/correction, evidence, replay, and command state context agree on the same valid publication identity; pointer publication identity must be present in resolver rows before mirror checks are evaluated, and pipeline finalize must use the current-readable pointer resolver as authoritative post-promotion proof.
  - Invalid state combinations must fail safe as NOT_READABLE, HELD, controlled exception, or preserved previous readable pointer context according to the locked contract.

  [LOCK_CONDITION]
  - LOCKED after operator local validation confirmed targeted `Finalize`, targeted `Correction`, and full `tests/Unit/MarketData` all PASS without weakening assertions or schema constraints.
  - Reopen only if a future finalize/publication/pointer/fallback/correction/evidence/replay/command/repository path changes this no-invalid-state-combination contract.

---

- COVERAGE_GATE_ENFORCEMENT_CONTRACT -> LOCKED

  [LAST_UPDATED] 2026-05-02

  [RELATED_IMPLEMENTATION] Coverage Gate Enforcement / No Coverage Bypass

  [REVIEW_STATUS] REVIEWED_OK

  [HISTORY]
  - 2026-05-01 -> Contract enforcement session opened under audit governance.
  - 2026-05-01 -> Static trace found readable/current paths that relied on PASS state without complete coverage telemetry proof.
  - 2026-05-01 -> Enforcement added to guard, finalize decision, publication outcome, pipeline finalize guard states, pointer repository predicates, and static tests.
  - 2026-05-01 -> Operator local validation exposed recovery gaps: static guard Lumen path resolution, coverage alias conflict handling, incomplete mocked coverage summaries, and readable baseline/fallback fixtures missing complete telemetry.
  - 2026-05-01 -> Recovery patch applied to keep contract strict while restoring valid correction/fallback behavior through complete coverage telemetry and post-query guard validation.
  - 2026-05-01 -> Recovery validation exposed and resolved correction/fallback regressions without weakening coverage no-bypass enforcement.

  - 2026-05-02 -> Final operator local validation passed: pipeline integration, pointer, coverage, finalize, publication, readable, evidence, replay, command, evaluator, finalize decision, publication outcome, static guard, and full MarketData suite. Contract promoted to LOCKED.

  [DEFINED]
  - Coverage gate is valid only when expected universe count, available EOD count, missing EOD count, coverage ratio, threshold value, threshold mode, gate state, reason code, universe basis, and contract version are deterministic and traceable.
  - READABLE/current publication requires coverage PASS plus complete persisted coverage telemetry.
  - FAIL or NOT_EVALUABLE coverage must not publish a new readable publication or switch current pointer.
  - Empty universe or incomplete PASS context is NOT_EVALUABLE/fail-safe unless a future locked contract explicitly says otherwise.

  [IMPLEMENTED]
  - `MarketDataInvariantGuard` enforces complete coverage telemetry for readable/current/promotion/fallback states.
  - `FinalizeDecisionService` downgrades incomplete PASS coverage to NOT_EVALUABLE.
  - `PublicationFinalizeOutcomeService` preserves coverage summary for outcome guard validation.
  - `CoverageGateEvaluator` dedupes universe/available ticker counts and emits basis/contract/reason aliases.
  - `EodPublicationRepository` requires complete run coverage telemetry on readable pointer resolution and re-validates resolved rows through `MarketDataInvariantGuard`.
  - `EligibilitySnapshotScopeRepository` and `EodEvidenceRepository` require complete coverage telemetry before returning pointer-scoped consumer/evidence rows.
  - `CoverageGateNoBypassStaticGuardTest` added and made independent from Lumen `base_path()`.

  [ENFORCED]
  - Static guard coverage exists for complete telemetry requirements and no latest trade-date shortcut in runtime coverage/finalize/evidence/replay paths.
  - Runtime guard treats conflicting `coverage_gate_state` / `coverage_gate_status` aliases as NOT_EVALUABLE instead of allowing one alias to hide failure.
  - Syntax validation completed for changed PHP files.
  - Local PHPUnit validation passed after recovery patches, including targeted and full MarketData suites.

  [VALIDATED]
  - Container static scan completed.
  - Container `php -l` completed for changed PHP files.
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

  [FINAL_RULE]
  - LOCKED. No market-data path may mark a run/publication READABLE/current based only on `coverage_gate_state = PASS`. Complete coverage telemetry and internally consistent count/ratio/threshold math are required.
  - Coverage FAIL, NOT_EVALUABLE, empty universe, incomplete PASS context, conflicting coverage aliases, or invalid pointer/fallback telemetry must fail-safe and must not switch pointer to a new readable publication.
  - Evidence/replay/command surfaces must carry and validate coverage context, including threshold mode, universe basis, contract version, reason code, and expected/available/missing/ratio fields.

  [LOCK_CONDITION]
  - LOCKED for the current source-of-truth ZIP after local validation confirmed targeted coverage/finalize/publication/pointer/evidence/replay/command tests and full `tests/Unit/MarketData` all PASS.
  - Reopen only if a future coverage/finalize/publication/pointer/evidence/replay/command/repository path changes this no-bypass contract.

---

- READ_SIDE_POINTER_ENFORCEMENT_CONTRACT → LOCKED

  [LAST_UPDATED] 2026-05-01

  [RELATED_IMPLEMENTATION] Read-Side Enforcement / Anti Bypass Total

  [REVIEW_STATUS] REVIEWED_OK

  [HISTORY]
  - 2026-05-01 → Canonical read-side pointer enforcement contract opened under audit governance.
  - 2026-05-01 → Static trace confirmed the official consumer gateway is `EodPublicationRepository::resolveCurrentReadablePublicationForTradeDate($tradeDate)`.
  - 2026-05-01 → Gap found: pointer-scoped eligibility/evidence reads did not uniformly require `coverage_gate_state = PASS` and run mirror fields matching pointer publication metadata.
  - 2026-05-01 → Gap fixed in repository predicates and guarded through integration/static tests.
  - 2026-05-01 → Contract document synchronized to explicitly include coverage PASS and run mirror validation.
  - 2026-05-01 → Operator local PHPUnit evidence found correction/fallback regressions when consumer-only run mirror predicates were added to the internal prior-readable fallback lookup.
  - 2026-05-01 → Contract clarified that internal fallback lookup is not a consumer read gateway; consumer gateway/evidence/eligibility scope remain mirror-enforced.
  - 2026-05-01 → Operator retest confirmed targeted readable/pointer tests, full MarketData suite, readable-publication integration test, and pointer static guard all PASS after the regression patch.

  [DEFINED]
  - Consumer read paths must resolve through `eod_current_publication_pointer`.
  - Valid readable context requires sealed current publication, pointer/publication/run identity match, `terminal_status = SUCCESS`, `publishability_state = READABLE`, `coverage_gate_state = PASS`, `run.is_current_publication = 1`, and run `publication_id/publication_version` mirror match to the pointer.
  - Artifact rows returned to consumers must be scoped by `publication_id` and pointer-resolved `trade_date_effective`/trade date context.
  - No readable pointer context means fail-safe: empty controlled output, not-readable response, controlled exception, or explicit command/evidence/replay failure.
  - Internal prior-readable fallback lookup is allowed only for pipeline hold/degraded-mode/correction preservation and must not be used as an API/evidence/replay/consumer latest shortcut.

  [IMPLEMENTED]
  - `EligibilitySnapshotScopeRepository` enforces coverage PASS and run mirror match.
  - `EodEvidenceRepository::findPublicationForRun` enforces pointer/current/sealed/SUCCESS/READABLE/PASS/current/mirror validation.
  - `EodEvidenceRepository::exportEligibilityRows` enforces pointer-scoped readable eligibility context.
  - `EodEvidenceRepository::dominantReasonCodes` no longer returns reason-code output when the publication/run context is not current-readable/PASS/mirror-valid.
  - `EodPublicationRepository::findLatestReadablePublicationBefore` remains an internal fallback lookup only; it preserves pipeline correction/fallback behavior and must not be used as a consumer gateway.
  - Static guards and integration tests were extended for coverage PASS and run mirror requirements.

  [ENFORCED]
  - Static guard coverage exists for forbidden latest/MAX shortcuts in consumer files.
  - Static guard coverage exists for pointer gateway predicates.
  - Static guard coverage exists for pointer-scoped eligibility/evidence coverage PASS and run mirror checks.
  - Integration coverage exists for no-leak behavior when coverage is not PASS or run mirror mismatches pointer metadata.
  - Regression reconciliation exists for internal fallback lookup so consumer enforcement does not break prior-readable preservation behavior.

  [VALIDATED]
  - Container static grep/query scan completed.
  - Container `php -l` completed for changed PHP files.
  - Local command: `php artisan migrate:fresh --env=testing` → PASS.
  - Local command: `vendor/bin/phpunit tests/Unit/MarketData --filter "readable"` → PASS; `OK (45 tests, 256 assertions)`.
  - Local command: `vendor/bin/phpunit tests/Unit/MarketData --filter "pointer"` → PASS; `OK (51 tests, 551 assertions)`.
  - Local command: `vendor/bin/phpunit tests/Unit/MarketData` → PASS; `OK (250 tests, 2355 assertions)`.
  - Local command: `vendor/bin/phpunit tests/Unit/MarketData/ReadablePublicationReadContractIntegrationTest.php` → PASS; `OK (8 tests, 15 assertions)`.
  - Local command: `vendor/bin/phpunit tests/Unit/MarketData/PublicationCurrentPointerReadinessStaticGuardTest.php` → PASS; `OK (3 tests, 23 assertions)`.

  [FINAL_RULE]
  - LOCKED. No market-data consumer may read raw/staging/latest/current artifact data unless it is resolved through the current readable publication pointer and validated against sealed publication, SUCCESS/READABLE/PASS run, current state, run mirror metadata, and publication scope.
  - No consumer may fallback to MAX/latest/raw/staging data when pointer resolution fails.
  - Internal prior-readable fallback remains allowed only for pipeline hold/degraded-mode/correction preservation and must not be exposed as consumer latest/read gateway.

  [LOCK_CONDITION]
  - This contract is locked for the current source-of-truth ZIP after targeted and full MarketData PHPUnit validation.
  - Reopen only if a future market-data read path, evidence/replay flow, repository method, command output, or fallback rule changes the pointer/readability enforcement contract.


---

- AUDIT_REBUILD_BASELINE_CONTRACT → LOCKED

  [LAST_UPDATED] 2026-05-01

  [RELATED_IMPLEMENTATION] Audit Rebuild Baseline / One-by-One Regression Review

  [REVIEW_STATUS] REVIEWED_OK

  [HISTORY]
  - 2026-05-01 → Clean contract tracker rebuild started; previous broad LOCKED/DONE list intentionally removed from active tracker until one-by-one retest evidence is supplied.
  - 2026-05-01 → First reviewed contract scope completed through `DB_SCHEMA_AND_MIGRATION_SYNC_CONTRACT`; clean rebuild workflow is validated for continued use.

  [DEFINED]
  - This contract controls the clean audit rebuild mode after historical status uncertainty.
  - It requires future contract restoration to happen one scope at a time using current evidence.

  [IMPLEMENTED]
  - Implemented as a clean tracker structure with active session tracking, canonical contract entries, and no unverified historical LOCKED claims.
  - First restored locked contract is `DB_SCHEMA_AND_MIGRATION_SYNC_CONTRACT`.

  [ENFORCED]
  - Any restored contract must have a matching implementation entry in `LUMEN_IMPLEMENTATION_STATUS.md`.
  - Any restored LOCKED contract must include current validation evidence and a final rule.
  - Duplicate contract fragments must be merged into the canonical contract entry.

  [VALIDATED]
  - First one-by-one retest scope completed: `DB_SCHEMA_AND_MIGRATION_SYNC_CONTRACT` is validated and locked with local migration/PHPUnit evidence.

  [FINAL_RULE]
  - LOCKED. The audit rebuild model must restore contract status one concern at a time, backed by current evidence, with no duplicate contract entries and no unverified historical LOCKED carry-forward.

  [LOCK_CONDITION]
  - This governance baseline remains locked unless the audit strategy itself changes through an explicit audit-governance session.

---

- DB_SCHEMA_AND_MIGRATION_SYNC_CONTRACT → LOCKED

  [LAST_UPDATED] 2026-05-01

  [RELATED_IMPLEMENTATION] DB Schema & Migration Sync / Runtime Schema Four-Way Synchronization

  [REVIEW_STATUS] REVIEWED_OK

  [HISTORY]
  - 2026-05-01 → Contract enforcement started for DB schema synchronization across SQL docs, migrations, SQLite test schema, repository/query usage, and fixtures.
  - 2026-05-01 → Runtime-orphan SQLite surrogate keys were removed and artifact/history identity rules were aligned with runtime composite keys.
  - 2026-05-01 → Replay index naming and ticker timestamp behavior were synchronized between SQL schema and migrations.
  - 2026-05-01 → Migration-chain idempotency gaps were resolved for `md_session_snapshots` and correction reexecution policy fields.
  - 2026-05-01 → Strict SQLite/runtime constraints exposed fixture drift; fixtures were corrected rather than weakening the schema mirror.
  - 2026-05-01 → Repository restore-prior validation and pipeline promotion-failure fallback effective-date handling were aligned with pointer/publication/run integrity rules.
  - 2026-05-01 → Final local evidence confirmed migration fresh, schema guard, repository tests, pipeline integration tests, and full MarketData PHPUnit suite all PASS.
  - 2026-05-01 → Audit recovery applied: prior DB schema contract hotfix fragments were merged into this canonical locked contract entry.

  [DEFINED]
  - Runtime schema reference: `docs/market_data/db/Database_Schema_MariaDB.sql`.
  - Migration/runtime generation reference: market-data migrations under `database/migrations/`.
  - Test mirror reference: `tests/Support/UsesMarketDataSqlite.php`.
  - Query validation scope: market-data repository layer under `app/Infrastructure/Persistence/MarketData/` plus market-data services that persist artifacts, publications, runs, evidence, and correction outcomes.
  - Fixture/test validation scope: MarketData unit/integration tests that seed or read market-data runtime tables.

  [IMPLEMENTED]
  - SQLite-only surrogate keys were removed from current/history artifact tables.
  - SQL schema and migration replay index names were aligned.
  - Ticker timestamp behavior was aligned between migration and SQL schema.
  - Additive migrations were hardened against duplicate table/column creation when the canonical SQL schema already represents final state.
  - SQLite mirror defaults and constraints were aligned with MariaDB behavior where appropriate.
  - Repository/read-contract/pipeline fixtures now seed runtime-required fields explicitly.
  - Restore-prior validation rejects invalid fallback runs before restoring current pointer state.
  - Pipeline correction promotion failure handling preserves valid fallback effective date without publishing failed candidate state.

  [ENFORCED]
  - Market-data schema changes must be represented consistently across SQL docs, migration final state, SQLite test mirror, repository/query usage, and fixtures.
  - SQLite test schema must not contain runtime-orphan fields or looser behavior that creates false-positive tests.
  - Tests must obey runtime-required fields and composite unique keys.
  - Current pointer replacement and fallback restoration require aligned pointer/publication/run mirror metadata.
  - Migration history may use idempotent guards when the canonical SQL schema bootstrap already creates the final-state table or column.

  [VALIDATED]
  - `php artisan migrate:fresh --env=testing` → PASS.
  - `vendor/bin/phpunit tests/Unit/MarketData --filter "schema"` → PASS; `OK (3 tests, 70 assertions)`.
  - `vendor/bin/phpunit tests/Unit/MarketData --filter "Repository"` → PASS; `OK (33 tests, 180 assertions)`.
  - `vendor/bin/phpunit tests/Unit/MarketData/MarketDataPipelineIntegrationTest.php` → PASS; `OK (52 tests, 1182 assertions)`.
  - `vendor/bin/phpunit tests/Unit/MarketData` → PASS; `OK (244 tests, 2327 assertions)`.
  - Static validation during patch sequence: changed PHP files passed `php -l` before local reruns.

  [FINAL_RULE]
  - LOCKED. Market-data DB schema changes must stay in four-way sync across `Database_Schema_MariaDB.sql`, Laravel/Lumen migrations, SQLite test schema, and repository/test usage.
  - No market-data field, identity key, nullable/default behavior, index, unique constraint, enum/status value, or repository-used column may exist only in one layer.
  - Fixture/test failures caused by runtime-aligned constraints must be fixed in fixtures or implementation, not hidden by loosening SQLite schema.
  - Any future drift must be fixed directly or recorded as an explicit policy gap before related implementation work is marked DONE.

  [LOCK_CONDITION]
  - This contract remains locked for the current source-of-truth ZIP.
  - Reopen only through a schema/contract session if future migration, SQL schema, SQLite mirror, repository query, or fixture change introduces new drift or requires a deliberate breaking change.

## Recovery-3 malformed fallback pointer fix — Coverage Gate Enforcement / No Coverage Bypass

- Status: SUPERSEDED_BY_FINAL_LOCK.
- Local evidence received: static guard, Coverage, Publication, readable, Evidence, Replay, and Command suites passed; one integration/pointer failure remained for malformed fallback pointer effective-date handling.
- Recovery-3 fix: when correction pointer mismatch occurs and no contract-valid readable fallback exists, `trade_date_effective` is explicitly cleared to null instead of retaining the requested candidate date.
- Final result: superseded by Recovery-5 final local validation; `MarketDataPipelineIntegrationTest`, pointer filter, and full `tests/Unit/MarketData` all PASS.

## Recovery-4 fallback mirror fixture alignment — COVERAGE_GATE_ENFORCEMENT_CONTRACT

- Status: SUPERSEDED_BY_FINAL_LOCK.
- Local evidence received after Recovery-3: all targeted suites except pipeline integration/pointer fallback cases passed; full MarketData suite had four remaining fallback/effective-date failures.
- Enforcement recovery: fallback publication fixtures now satisfy strict pointer/publication/run mirror identity, and correction baseline pointer mismatch is treated as a pointer-integrity failure instead of a generic promotion error.
- Final result: superseded by Recovery-5 final local validation; `MarketDataPipelineIntegrationTest`, pointer filter, and full `tests/Unit/MarketData` all PASS.

## Recovery-5 baseline pointer mismatch message preservation — COVERAGE_GATE_ENFORCEMENT_CONTRACT

- Status: LOCKED by final local validation.
- Local evidence after Recovery-5: `MarketDataPipelineIntegrationTest`, pointer filter, targeted coverage/finalize/publication/readable/evidence/replay/command suites, core service tests, static guard, and full `tests/Unit/MarketData` all PASS.
- Enforcement recovery: pointer-integrity failures keep specific operator/audit messages for correction baseline mismatch while generic post-switch mismatch cases continue using the generic current publication pointer resolution message.
- Final lock completed for `COVERAGE_GATE_ENFORCEMENT_CONTRACT`.
