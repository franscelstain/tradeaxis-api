# LUMEN_CONTRACT_TRACKER

## CONTRACT ITEM 5 — Session snapshot runtime / retention / purge semantics
- STATUS: PARTIAL (SESSION 15 PATCH IMPLEMENTED, LOCAL RUNTIME PROOF PENDING)
- OWNER AREA: session snapshot runtime and retention evidence semantics
- LAST UPDATED SESSION: session15_patch_slot_tolerance_contract

- OWNER DOCS:
  - `docs/market_data/ops/Commands_and_Runbook_LOCKED.md`
  - `docs/market_data/session_snapshot/Session_Snapshot_Contract_LOCKED.md`
  - `docs/market_data/session_snapshot/Session_Snapshot_Retention_Defaults_LOCKED.md`
  - `docs/market_data/session_snapshot/Snapshot_Slot_Tolerances_and_Session_Rules_LOCKED.md`

- EVIDENCE:
  - capture path remains in place for readable-publication-gated manual-file session snapshot runtime
  - purge path already writes explicit cutoff provenance and supporting cutoff context from session 14
  - session 15 closes the next runtime gap inside the same family by aligning capture behavior with locked slot-tolerance rules:
    - default slot anchors are enforced for `OPEN_CHECK`, `MIDDAY_CHECK`, and `PRE_CLOSE_CHECK`
    - configured slot tolerance now decides whether a row is captured or counted as skipped partial-state evidence
    - capture event payloads and summary artifacts now expose `slot_tolerance_minutes`, `slot_anchor_time` when applicable, and `slot_miss_count`
    - command surface now renders `trade_date_effective`, `publication_id`, and slot-miss evidence so operators can validate alignment without opening the artifact file first
  - tests updated for both normal capture and slot-miss partial-state capture

- PROOF:
  - php -l checks -> PASS
  - targeted PHPUnit -> pending local execution
  - full PHPUnit suite -> pending local execution

- OPEN GAP:
  - full local/runtime proof for the combined session 14 + session 15 session-snapshot patch set is still missing because the source ZIP does not ship `vendor/`
  - half-day pre-close override behavior remains document-owned but is not proven in code within this narrowed batch; keep this visible for the next family reassessment after proof returns

- NEXT REQUIRED ACTION:
  - execute the session 15 local proof commands and return the outputs
  - if proof passes, reassess whether item 5 can be closed directly or whether the remaining half-day/pre-close contract needs one final narrow batch

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
