# LUMEN_CONTRACT_TRACKER

## CONTRACT ITEM 8 — Ops backfill / replay / evidence export command semantics
- STATUS: PARTIAL
- OWNER AREA: ops backfill, replay, and evidence export command/service semantics
- LAST UPDATED SESSION: session9_final_proof_synced
- EVIDENCE:
  - owner-doc target remains `docs/market_data/ops/Commands_and_Runbook_LOCKED.md`, `docs/market_data/ops/Bootstrap_and_Backfill_Runbook_LOCKED.md`, and related replay/evidence contracts under `docs/market_data/ops/`
  - `tests/Unit/MarketData/OpsCommandSurfaceTest.php` now covers `market-data:replay:verify` for:
    - success path with deterministic `replay_id`
    - fixture path propagation into `ReplayVerificationService`
    - optional replay evidence export invocation when `--output_dir` is provided
    - mismatch path returning non-zero without implicit evidence export
  - previously closed command-surface coverage remains in place for:
    - `market-data:evidence:export` exact-one-selector guardrail
    - run / correction / replay evidence export success paths
    - `market-data:replay:smoke` suite summary behavior
    - `market-data:replay:backfill` failure summary behavior
    - `market-data:backfill` summary behavior
    - session snapshot capture / purge summary behavior
  - proof executed and confirmed:
    - `php -l tests/Unit/MarketData/OpsCommandSurfaceTest.php` -> passed
    - `vendor\\bin\\phpunit tests\\Unit\\MarketData\\OpsCommandSurfaceTest.php` -> passed (`OK (12 tests, 51 assertions)`)
    - `vendor\\bin\\phpunit` -> passed (`OK (113 tests, 1243 assertions)`)
- OPEN GAP:
  - wider ops family remains partial beyond the command-surface slices already covered
  - broader parity across replay/backfill/evidence operational matrix can still be tightened later
- NEXT REQUIRED ACTION:
  - keep session-8 evidence-export and session-9 replay-verify sub-gaps closed
  - continue tightening the next narrow ops/runbook parity gap under CONTRACT ITEM 8 without reopening already-covered command surfaces


### SESSION 9 FINAL PROOF-SYNC NOTE
- Contract family: ops / replay / evidence command semantics.
- Hotfix confirmed: `VerifyReplayCommand` no longer calls `exportReplayEvidence()` when `--output_dir` is omitted.
- Reason: local user proof exposed unintended export side effect on mismatch path; the hotfix aligned runtime behavior with the command-surface contract.
- Status impact: ITEM 8 remains `PARTIAL` overall, but the SESSION 9 replay-verify sub-gap is now closed with local targeted proof and full-suite confirmation.
