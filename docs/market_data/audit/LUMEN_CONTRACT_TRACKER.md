# LUMEN_CONTRACT_TRACKER

## CONTRACT ITEM 5 — Session snapshot runtime / retention / purge semantics
- STATUS: PARTIAL (SESSION 14 PATCH IMPLEMENTED, LOCAL RUNTIME PROOF PENDING)
- OWNER AREA: session snapshot runtime and retention evidence semantics
- LAST UPDATED SESSION: session14_patch_cutoff_source_contract

- OWNER DOCS:
  - `docs/market_data/ops/Commands_and_Runbook_LOCKED.md`
  - `docs/market_data/session_snapshot/Session_Snapshot_Contract_LOCKED.md`
  - `docs/market_data/session_snapshot/Session_Snapshot_Retention_Defaults_LOCKED.md`

- EVIDENCE:
  - capture path remains in place for readable-publication-gated manual-file snapshot runtime
  - purge path already wrote summary artifacts with cutoff timestamp and deleted rows
  - session 14 hardens the remaining retention-summary ambiguity by making cutoff provenance explicit in both artifact and operator surface:
    - `cutoff_source=explicit_before_date` when `--before_date` is supplied
    - `cutoff_source=default_retention_days` when purge uses retention defaults
    - `before_date` is present only for explicit cutoff mode
    - `retention_days` is present only for default-retention mode
  - tests updated for service-level artifact semantics and command-surface rendering in both purge modes

- PROOF:
  - php -l checks -> pending session 14 local syntax validation
  - targeted PHPUnit -> pending local execution
  - full PHPUnit suite -> pending local execution

- OPEN GAP:
  - full local/runtime proof for the session 14 patch is still missing because the source ZIP does not ship `vendor/`
  - broader session-snapshot parent area is not yet closed at final-readiness level beyond this narrowed purge cutoff-source batch

- NEXT REQUIRED ACTION:
  - execute the session 14 local proof commands and return the outputs
  - if proof passes, keep session snapshot family open only for any remaining grounded runtime/ops gaps; otherwise repair from failing output first

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
