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
- STATUS: PARTIAL (SESSIONS 1-2 DOC/CONFIG/SCHEMA SYNC COMPLETE; RUNTIME IMPLEMENTATION STILL OPEN)
- OWNER AREA: coverage-gate semantics for requested-date readability and finalization
- LAST UPDATED SESSION: session2_coverage_gate_config_env_db_schema

- OWNER DOCS:
  - `docs/market_data/book/EOD_COVERAGE_GATE_CONTRACT_LOCKED.md`
  - `docs/market_data/book/Coverage_Universe_Definition_LOCKED.md`
  - `docs/market_data/book/Run_Status_and_Quality_Gates_LOCKED.md`
  - `docs/market_data/book/EOD_Cutoff_and_Finalization_Contract_LOCKED.md`
  - `docs/market_data/ops/Daily_Pipeline_Execution_and_Sealing_Runbook_LOCKED.md`
  - `docs/market_data/tests/Contract_Test_Matrix_LOCKED.md`

- EVIDENCE:
  - source-of-truth ZIP already contained an early `EOD_COVERAGE_GATE_CONTRACT_LOCKED.md`, but it was still too thin to safely act as the definitive owner contract
  - session 1 hardened that contract so it now explicitly locks:
    - resolved universe denominator semantics
    - canonical-valid-bar numerator semantics
    - official coverage formula
    - explicit threshold dependency (`COVERAGE_MIN`)
    - final allowed gate states: `PASS`, `FAIL`, `BLOCKED`
    - outcome mapping from coverage result to requested-date readability, terminal status, and fallback behavior
  - related owner docs were synchronized so coverage fail/block paths no longer conflict with finalization/readability wording
  - test matrix was extended with explicit coverage-gate contract cases

- PROOF:
  - owner-doc reread and sync in current source-of-truth ZIP -> PASS
  - cross-doc conflict check for coverage vs finalization/readability wording -> PASS
  - code/runtime conformance proof in this container -> NOT RUN (`vendor/` absent from uploaded ZIP)
  - config owner block added in `config/market_data.php` and synced to `.env.example` -> PASS
  - MariaDB owner schema for `eod_runs` and replay metrics expanded with coverage evidence fields -> PASS
  - SQLite test schema mirror expanded with the same coverage evidence fields -> PASS
  - implementation alignment against hardened contract -> OPEN

- OPEN GAP:
  - code/service/finalize path may still not fully enforce the hardened denominator/numerator/state rules
  - runtime write-path still needs to populate the new coverage config/schema fields consistently
  - explicit DB/runtime evidence fields for coverage denominator/numerator/threshold may still need implementation or strengthening
  - PHPUnit/integration proof for the new coverage-gate cases is still open

- NEXT REQUIRED ACTION:
  - implement coverage-gate contract in code paths that compute/finalize publishability
  - add or harden tests for the new coverage-gate matrix
  - only close this item after code + proof align with the owner contract
