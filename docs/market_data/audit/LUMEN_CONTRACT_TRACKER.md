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
- STATUS: PARTIAL (SESSION 16 PATCH APPLIED; LOCAL RUNTIME PROOF PENDING USER EXECUTION)
- OWNER AREA: repository + correction/runtime integration for readable publication resolution
- LAST UPDATED SESSION: session16_patch_only_pending_local_proof

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
  - session 16 patch now hardens:
    - `findCurrentPublicationForTradeDate(...)`
    - `findPointerResolvedPublicationForTradeDate(...)`
    - `findCorrectionBaselinePublicationForTradeDate(...)`
    - `findLatestReadablePublicationBefore(...)`
    by requiring non-null `ptr.sealed_at`, `pub.sealed_at`, and `run.sealed_at`
  - tests were extended for:
    - repository fail-safe resolution when pointed publication `sealed_at` is missing
    - repository fail-safe resolution when owning run `sealed_at` is missing
    - approved correction baseline rejection when current publication `sealed_at` is missing
    - post-switch mismatch fallback protection when fallback publication `sealed_at` is missing

- PROOF:
  - patch applied
  - local syntax/runtime proof in this environment is still pending because `vendor/` is absent from the uploaded source ZIP
  - required user-run commands are listed in the session output and must be executed before this item can be upgraded beyond `PARTIAL`

- OPEN GAP:
  - session 16 seal-timestamp integrity patch still needs local PHPUnit confirmation
  - broader DB-backed malformed fallback/current-pointer matrix may still contain later narrow cases, but the selected session 16 batch is limited to the now-patched sealed-at integrity rule

- NEXT REQUIRED ACTION:
  - run the listed local PHPUnit commands and send the outputs back
  - if all targeted proofs pass, upgrade this item to `DONE` for the session 16 batch and continue to the next grounded DB-backed integrity gap or final readiness gate

## CONTRACT ITEM 9 — Final readiness gate
- STATUS: MISSING
- OWNER AREA: project-level readiness
- LAST UPDATED SESSION: session16_not_reached

- EVIDENCE:
  - session 15 closed session snapshot runtime proof
  - session 16 reopened the still-unfinished DB-backed integration family in the active tracker and applied the next seal-timestamp integrity patch
  - final readiness remains unavailable until the active unfinished market-data parent family is fully proven

- OPEN GAP:
  - contract item 7 is still `PARTIAL` pending local proof
  - final project-level readiness therefore cannot be claimed yet

- NEXT REQUIRED ACTION:
  - close the active unfinished parent family with proof, then reassess whether only the final readiness gate remains
