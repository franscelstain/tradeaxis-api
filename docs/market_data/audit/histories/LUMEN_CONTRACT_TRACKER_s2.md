# LUMEN_CONTRACT_TRACKER

## CONTRACT ITEM 5 — Session snapshot runtime / retention / purge semantics
- STATUS: DONE (SESSION 15 PATCH + HOTFIX + FULL RUNTIME PROOF PASSED)
- OWNER AREA: session snapshot runtime and retention evidence semantics
- LAST UPDATED SESSION: session15_proven_complete

- OWNER DOCS:
  - `docs/market_data/ops/Commands_and_Runbook_LOCKED.md`
  - `docs/market_data/session_snapshot/Session_Snapshot_Contract_LOCKED.md`
  - `docs/market_data/session_snapshot/Session_Snapshot_Retention_Defaults_LOCKED.md`
  - `docs/market_data/session_snapshot/Snapshot_Slot_Tolerances_and_Session_Rules_LOCKED.md`

- EVIDENCE:
  - capture path remains in place for readable-publication-gated manual-file session snapshot runtime
  - purge path already writes explicit cutoff provenance and supporting cutoff context from session 14
  - session 15 closed the next runtime gap inside the same family by aligning capture behavior with locked slot-tolerance rules:
    - default slot anchors are enforced for `OPEN_CHECK`, `MIDDAY_CHECK`, and `PRE_CLOSE_CHECK`
    - configured slot tolerance now decides whether a row is captured or counted as skipped partial-state evidence
    - capture event payloads and summary artifacts now expose `slot_tolerance_minutes`, `slot_anchor_time` when applicable, and `slot_miss_count`
    - command surface now renders `trade_date_effective`, `publication_id`, and slot-miss evidence so operators can validate alignment without opening the artifact file first
  - targeted test isolation issue was also closed by making the session snapshot test fixture timezone-explicit, preventing runtime-dependent slot drift in single-test execution
  - tests now cover both normal capture and slot-miss partial-state capture, and the targeted execution path is stable again

- PROOF:
  - php -l checks -> PASS
  - targeted PHPUnit (`test_capture_writes_summary_for_partial_scope`) -> PASS
  - `tests/Unit/MarketData/SessionSnapshotServiceTest.php` -> PASS
  - `tests/Unit/MarketData/OpsCommandSurfaceTest.php` -> PASS
  - full PHPUnit suite -> PASS (`119 tests, 1323 assertions`)

- OPEN GAP:
  - none for the session 15 batch
  - half-day pre-close override behavior may still be reassessed later only if a grounded owner-doc/code gap is identified, but it is not an open blocker from session 15

- NEXT REQUIRED ACTION:
  - none for this contract item unless a later regression is observed or a new doc-grounded half-day/pre-close batch is explicitly selected

## CONTRACT ITEM 8 — Ops backfill / replay / evidence export command semantics
- STATUS: DONE (SESSION 13 PATCH + FULL RUNTIME PROOF PASSED)
- OWNER AREA: ops backfill, replay, and evidence export command/service semantics
- LAST UPDATED SESSION: session13_proven_complete

- EVIDENCE:
  - owner-doc target remains `docs/market_data/ops/Commands_and_Runbook_LOCKED.md`, `docs/market_data/ops/Bootstrap_and_Backfill_Runbook_LOCKED.md`, and `docs/market_data/ops/Audit_Evidence_Pack_Contract_LOCKED.md`
  - backfill and replay command surface exposes required operator-level fields for range and replay minimums
  - evidence export command surface exposes normalized selector metadata and deterministic file summary fields for run / correction / replay exports
  - session 13 proof already passed locally, including full PHPUnit

- PROOF:
  - php -l checks -> PASS
  - targeted PHPUnit -> PASS
  - full PHPUnit suite -> PASS

- OPEN GAP:
  - none for this contract family in current scope

- NEXT REQUIRED ACTION:
  - none unless a later regression is observed


## CONTRACT ITEM 7 — DB-backed integration proof / readable seal-timestamp integrity
- STATUS: DONE (SESSION 16 PATCH + HOTFIX + FULL RUNTIME PROOF PASSED)
- OWNER AREA: repository + correction/runtime integration for readable publication resolution
- LAST UPDATED SESSION: session16_proven_complete

- OWNER DOCS:
  - `docs/market_data/book/Downstream_Data_Readiness_Guarantee_LOCKED.md`
  - `docs/market_data/book/Downstream_Consumer_Read_Model_Contract_LOCKED.md`
  - `docs/market_data/book/Publication_Current_Pointer_Integrity_Contract_LOCKED.md`
  - `docs/market_data/book/Dataset_Seal_and_Freeze_Contract_LOCKED.md`
  - `docs/market_data/ops/Daily_Pipeline_Execution_and_Sealing_Runbook_LOCKED.md`

- EVIDENCE:
  - checkpoint validation against repo showed the active tracker no longer exposed the still-open DB-backed integration family even though repository/runtime integrity remained the next load-bearing unfinished area
  - repo audit for the next grounded gap found a seal-timestamp integrity hole inside current/baseline/fallback resolution:
    - readable publication resolver queries required `seal_state = SEALED`
    - but they did not yet require `sealed_at` to be non-null on the pointed publication and owning run
    - this was weaker than owner docs that define seal completion and operator consumability using non-null `sealed_at`
  - session 16 patch hardened:
    - `findCurrentPublicationForTradeDate(...)`
    - `findPointerResolvedPublicationForTradeDate(...)`
    - `findCorrectionBaselinePublicationForTradeDate(...)`
    by requiring non-null `ptr.sealed_at`, `pub.sealed_at`, and `run.sealed_at`
  - user local targeted proof exposed one residual hole still inside the same batch:
    - `findLatestReadablePublicationBefore(...)` still allowed fallback resolution from rows with missing seal timestamps
  - session 16 hotfix closed that residual hole by requiring non-null `ptr.sealed_at`, `pub.sealed_at`, and `run.sealed_at` in `findLatestReadablePublicationBefore(...)`
  - tests in scope for this batch cover:
    - repository fail-safe resolution when pointed publication `sealed_at` is missing
    - repository fail-safe resolution when owning run `sealed_at` is missing
    - approved correction baseline rejection when current publication `sealed_at` is missing
    - post-switch mismatch fallback protection when fallback publication `sealed_at` is missing

- PROOF:
  - user local `php -l` checks -> PASS
  - user local targeted PHPUnit against the first session 16 patch -> FAIL, which honestly exposed the residual fallback-readable gap
  - post-hotfix targeted PHPUnit -> PASS
  - `tests/Unit/MarketData/PublicationRepositoryIntegrationTest.php` -> PASS (`13 tests, 60 assertions`)
  - `tests/Unit/MarketData/MarketDataPipelineIntegrationTest.php` -> PASS (`36 tests, 969 assertions`)
  - full PHPUnit suite -> PASS (`123 tests, 1391 assertions`)

- OPEN GAP:
  - none for the session 16 batch
  - broader DB-backed malformed fallback/current-pointer matrix may still contain later narrow cases, but the selected session 16 sealed-at integrity batch is closed

- NEXT REQUIRED ACTION:
  - choose the next grounded DB-backed integrity gap only if it is separately evidenced in owner-doc scope
  - otherwise reassess whether only the final readiness gate remains

## CONTRACT ITEM 9 — Final readiness gate
- STATUS: DONE (SESSION 19 FINAL AUDIT CLOSURE CLOSED)
- OWNER AREA: project-level readiness
- LAST UPDATED SESSION: session19_final_audit_closure

- EVIDENCE:
  - session 15 closed session snapshot runtime proof
  - session 16 closed the still-unfinished DB-backed integration family batch for readable seal-timestamp integrity
  - session 17 reassessment against the live repo and active tracker found no remaining higher-priority market-data parent contract family still open in current scope
  - session 18 revalidated the uploaded source-of-truth ZIP and confirmed the same closure state still holds in the current artifact
  - session 19 repeated that closure check against the newest source-of-truth ZIP, re-read the relevant readiness/build-order anchors, confirmed packaged proof artifacts are still present, and added current-scope PHP syntax validation for the provider-default path
  - active tracker state after reassessment is now:
    - contract item 5 = `DONE`
    - contract item 7 = `DONE`
    - contract item 8 = `DONE`
    - contract item 9 = readiness closure item itself
  - the previous `PARTIAL` state on contract item 9 had become a stale planning placeholder rather than evidence of an unresolved implementation family
  - session 17 therefore closed the final readiness gate as a checkpoint-state action without inventing new runtime-proof claims beyond the already recorded proof-backed sessions
  - session 18 keeps that closure intact and only hardens the checkpoint so the current artifact is the explicit final-audit source of truth
  - session 19 advances that checkpoint one step further so the final-audit handoff ZIP, not the prior session ZIP, is the explicit source of truth for closure
  - after that closure, the active codebase received one sanctioned config/provider-default update: EOD acquisition default now uses `source_mode=api` with provider `yahoo_finance`, and owner docs were synchronized immediately after proof passed

- PROOF:
  - checkpoint-vs-repo reassessment -> PASS
  - active tracker closure consistency -> PASS
  - source-of-truth ZIP revalidation in current environment -> PASS
  - readiness/build-order anchor re-read in current environment -> PASS
  - Yahoo default-provider code/doc sync spot-check -> PASS
  - targeted PHP syntax checks in current scope -> PASS
  - new runtime execution in this container -> NOT RUN (`vendor/` intentionally absent from uploaded ZIP)
  - last full runtime proof already recorded in active checkpoint before this closure step -> PASS (`123 tests, 1391 assertions` from session 16)
  - post-closure provider-default proof -> PASS (`PublicApiEodBarsAdapterTest` 4/4, `EodBarsIngestServiceTest` 2/2, full suite `125 tests, 1405 assertions`)

- OPEN GAP:
  - none in active market-data scope

- NEXT REQUIRED ACTION:
  - none unless a later regression, owner-doc change, or newly evidenced contract gap reopens market-data checkpoint work


## CONTRACT ITEM 10 — Coverage gate owner contract + doc sync
- REGRESSION FIX NOTE: post-closure user proof surfaced one remaining parity bug where successful full-coverage finalize emitted `RUN_FINALIZED.reason_code=COVERAGE_THRESHOLD_MET` instead of keeping the dominant reason code `null`; code patch applied in `MarketDataPipelineService::resolveFinalizeReasonCode()`, and final `SELESAI` status must now be re-confirmed by a fresh local PHPUnit rerun.
- STATUS: DONE (FINAL SESSION CLOSURE CONFIRMED)
- OWNER AREA: coverage-gate semantics for requested-date readability, finalization, operator surface, and existing-db schema parity
- LAST UPDATED SESSION: final_session_coverage_gate_closure

- OWNER DOCS:
  - `docs/market_data/book/EOD_COVERAGE_GATE_CONTRACT_LOCKED.md`
  - `docs/market_data/book/Coverage_Universe_Definition_LOCKED.md`
  - `docs/market_data/book/Run_Status_and_Quality_Gates_LOCKED.md`
  - `docs/market_data/book/EOD_Cutoff_and_Finalization_Contract_LOCKED.md`
  - `docs/market_data/ops/Daily_Pipeline_Execution_and_Sealing_Runbook_LOCKED.md`
  - `docs/market_data/tests/Contract_Test_Matrix_LOCKED.md`

- EVIDENCE:
  - session 1 hardened the coverage-gate owner contract and synchronized the related owner docs
  - session 2 added config/env/schema/sqlite contract support for dedicated coverage telemetry
  - session 3 implemented the standalone `CoverageGateEvaluator`
  - session 4 wired evaluator output into pipeline telemetry and removed the old eligibility-based ambiguity around `coverage_ratio`
  - session 5 completed finalize/outcome alignment so requested-date readability now depends on the official coverage gate status rather than on a raw ratio comparison shortcut
  - session 6 completed evidence/export/replay alignment so the same coverage contract is now visible in exported evidence packs, persisted replay metrics, and replay actual-vs-expected comparison
  - session 7 synchronized coverage reason-code literals into registry/seed, expanded operator command surface expectations, and added ops-surface tests for coverage-aware command output
  - session 7 patch closed the live existing-db schema drift by adding an `eod_runs` migration for the runtime coverage telemetry columns:
    - `coverage_universe_count`
    - `coverage_available_count`
    - `coverage_missing_count`
    - `coverage_ratio`
    - `coverage_min_threshold`
    - `coverage_gate_state`
    - `coverage_threshold_mode`
    - `coverage_universe_basis`
    - `coverage_contract_version`
    - `coverage_missing_sample_json`
  - local runtime/manual proof after the migration now shows:
    - `php -l app/Console/Commands/MarketData/AbstractMarketDataCommand.php` -> PASS
    - `tests/Unit/MarketData/OpsCommandSurfaceTest.php` -> PASS (`19 tests, 116 assertions`)
    - full PHPUnit suite -> PASS (`138 tests, 1504 assertions`)
    - `market-data:daily --requested_date=2026-03-24 --source_mode=manual_file -vvv` no longer fails on missing coverage columns; it now reaches source loading and fails only because the configured local JSON/CSV bars file for that requested date is absent
    - live `market-data:run:finalize --run_id=55/54/53/52 -vvv` shows `coverage_summary`, but the observed runtime output still does not surface `coverage_gate_state` / `coverage_reason_code` on those real runs even though the unit-test fixture path now does
  - coverage finalize semantics currently evidenced in code/tests remain:
    - coverage `PASS` + finalize preconditions satisfied -> candidate may be promoted toward readable success
    - coverage `FAIL` + fallback exists -> requested date remains `NOT_READABLE`, terminal resolves `HELD`
    - coverage `FAIL` + no fallback -> requested date remains `NOT_READABLE`, terminal resolves `FAILED`
    - evaluator `NOT_EVALUABLE` is treated as blocked/non-readable in finalize and never becomes readable success
  - session 8 extends the proof from unit/fixture scope into DB-backed integration coverage tests:
    - full daily pipeline with full coverage -> `SUCCESS + READABLE`
    - low coverage with fallback -> `HELD + NOT_READABLE` while prior readable publication remains untouched
    - low coverage without fallback -> `FAILED + NOT_READABLE`
    - not evaluable finalize path -> `FAILED + NOT_READABLE + BLOCKED`
  - session 8 also closes an integration-path reason-code bug in `RUN_FINALIZED` events:
    - non-success finalize events are no longer blindly tagged `RUN_LOCK_CONFLICT`
    - real coverage failures now emit `RUN_COVERAGE_LOW`
    - real not-evaluable finalize outcomes now emit `RUN_COVERAGE_NOT_EVALUABLE`
  - session 8 expands `RUN_FINALIZED` event payload parity so downstream operator/evidence layers receive:
    - `coverage_gate_state`
    - `coverage_reason_code`
    - `coverage_available_count`
    - `coverage_universe_count`
    - `coverage_missing_count`
    - `coverage_min_threshold`
    - `coverage_contract_version`

- PROOF:
  - owner-doc reread and sync in current source-of-truth ZIP -> PASS
  - cross-doc conflict check for coverage vs finalization/readability wording -> PASS
  - config owner block added in `config/market_data.php` and synced to `.env.example` -> PASS
  - MariaDB owner schema for `eod_runs` and replay metrics expanded with coverage evidence fields -> PASS
  - SQLite test schema mirror expanded with the same coverage evidence fields -> PASS
  - standalone `CoverageGateEvaluator` implemented -> PASS
  - pipeline eligibility stage computes true EOD coverage and persists dedicated coverage telemetry fields on `eod_runs` -> PASS
  - finalize now consumes the official coverage summary instead of the old raw coverage-ratio shortcut -> PASS
  - publication outcome preserves coverage-aware non-readable / fallback-safe outcomes -> PASS
  - evidence export / replay comparison / replay persistence are coverage-aware -> PASS
  - reason-code registry + seed now include coverage pass/fail/not-evaluable literals used by runtime -> PASS
  - existing-db backfill migration for `eod_runs` coverage telemetry columns added -> PASS
  - PHP lint local check on changed runtime/tests files -> PASS
  - `tests/Unit/MarketData/OpsCommandSurfaceTest.php` local runtime proof -> PASS (`19 tests, 116 assertions`)
  - full PHPUnit local runtime proof -> PASS (`138 tests, 1504 assertions`)
  - session 8 changed files pass `php -l` in this container -> PASS
  - session 8 targeted integration proof on user environment -> PASS (`tests/Unit/MarketData/MarketDataPipelineIntegrationTest.php` 36 tests)
  - session 8 full PHPUnit proof on user environment -> PASS (`138 tests, 1504 assertions`)
  - current-container targeted / full PHPUnit rerun -> NOT RUN (`vendor/` absent from uploaded ZIP)

- OPEN GAP:
  - none in active coverage-gate scope

- NEXT REQUIRED ACTION:
  - none unless a later regression, owner-doc change, or new contradictory evidence reopens this contract family
