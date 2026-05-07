# LUMEN_IMPLEMENTATION_STATUS

## ACTIVE SESSION

ACTIVE SESSION:
- Replay Determinism

[SESSION_STATUS] DONE

[SESSION_SCOPE]
- Enforce replay determinism across fixture metadata, expected context, actual evidence lifecycle context, comparison rules, mismatch reason codes, volatile-field handling, command output, replay artifact persistence, schema, registry, and static guards.
- Container validation completed static trace and `php -l`; operator-local targeted and full PHPUnit validation PASS on 2026-05-07.
- This session reconciles replay behavior with evidence export completeness, source/provider resilience, manual-file policy, coverage gate, finalize/pointer determinism, publishability integrity, fallback, and correction lifecycle safety.

[SESSION_GOAL]
- Replay must prove deterministic publication behavior from a stable fixture/proof package without using volatile current DB state, raw/staging/latest shortcuts, or silent wildcard comparison.

[SESSION_NOTES]
- Gap found: replay fixture schema was still permissive and did not require self-contained fixture metadata or complete expected lifecycle sections.
- Gap found: replay mismatch output was not consistently reason-coded per compared field/context.
- Gap found: non-readable source/failure runs could be blocked before replay proof was emitted, reducing replay to success-path smoke behavior.
- Patch applied: replay now emits expected/actual context, mismatch arrays, reason-code families, deterministic/volatile field metadata, operator-grade command summary, schema persistence, registry/seed sync, fixture v2 metadata, and static guard coverage.

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
