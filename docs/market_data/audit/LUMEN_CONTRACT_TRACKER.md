# LUMEN_CONTRACT_TRACKER

## CONTRACT ITEM 8 — Ops backfill / replay / evidence export command semantics
- STATUS: PARTIAL
- OWNER AREA: ops backfill, replay, and evidence export command/service semantics
- LAST UPDATED SESSION: session10_patch_ready_local_proof_pending
- EVIDENCE:
  - owner-doc target remains `docs/market_data/ops/Commands_and_Runbook_LOCKED.md`, `docs/market_data/ops/Bootstrap_and_Backfill_Runbook_LOCKED.md`, and related replay/evidence contracts under `docs/market_data/ops/`
  - `app/Console/Commands/MarketData/ReplayBackfillCommand.php` now emits richer per-date case lines with:
    - `status`
    - `run_id` when present
    - `replay_id` when present
    - explicit `error` text on failing dates
  - `tests/Unit/MarketData/OpsCommandSurfaceTest.php` now covers replay-backfill command surface for:
    - option propagation of `--fixture_root`, `--output_dir`, and `--continue_on_error`
    - success-path operator output including `status`, `run_id`, and `replay_id`
    - failure-path operator output including explicit per-date error text
  - previously closed command-surface coverage remains in place for:
    - `market-data:evidence:export` exact-one-selector guardrail
    - run / correction / replay evidence export success paths
    - `market-data:replay:verify` success path with optional evidence export and mismatch non-zero semantics
    - `market-data:replay:smoke` suite summary behavior
    - `market-data:replay:backfill` non-zero summary behavior when any case fails
    - `market-data:backfill` summary behavior
    - session snapshot capture / purge summary behavior
  - proof executed in this environment:
    - `php -l app/Console/Commands/MarketData/ReplayBackfillCommand.php` -> passed
    - `php -l tests/Unit/MarketData/OpsCommandSurfaceTest.php` -> passed
  - proof still pending in user environment because `vendor/` is intentionally absent from the uploaded ZIP:
    - targeted `vendor\bin\phpunit tests\Unit\MarketData\OpsCommandSurfaceTest.php`
    - full `vendor\bin\phpunit`
- OPEN GAP:
  - wider ops family remains partial beyond the command-surface slices already covered
  - this session's replay-backfill sub-gap is patch-complete but not yet locally proof-synced with PHPUnit in the current environment
- NEXT REQUIRED ACTION:
  - keep session-8 evidence-export, session-9 replay-verify, and session-10 replay-backfill operator-summary sub-gaps closed
  - run targeted and full PHPUnit proof in the user environment, then continue tightening the next narrow ops/runbook parity gap under CONTRACT ITEM 8 without reopening already-covered command surfaces


### SESSION 10 PATCH-READY NOTE
- Contract family: ops / replay backfill / evidence-oriented operator semantics.
- Patch intent: align command-level replay-backfill output with the runbook requirement that per-date replay outcomes stay distinguishable instead of collapsing away the resolved run/replay identifiers.
- Status impact: ITEM 8 remains `PARTIAL` overall because local runtime proof still depends on user-side PHPUnit execution, but the SESSION 10 sub-gap is implemented and syntax-clean.
