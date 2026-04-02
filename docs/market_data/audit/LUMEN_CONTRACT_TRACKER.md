# LUMEN_CONTRACT_TRACKER

## CONTRACT ITEM 8 — Ops backfill / replay / evidence export command semantics
- STATUS: PARTIAL
- OWNER AREA: ops backfill, replay, and evidence export command/service semantics
- LAST UPDATED SESSION: session12_patch_pending_local_proof
- EVIDENCE:
  - owner-doc target remains `docs/market_data/ops/Commands_and_Runbook_LOCKED.md`, `docs/market_data/ops/Bootstrap_and_Backfill_Runbook_LOCKED.md`
  - Backfill & replay command surface now expose required operator-level fields:
    - summary-level `source_mode`
    - per-date `publishability_state`
    - per-date `trade_date_effective`
    - explicit `error` text
    - replay identifiers (`run_id`, `replay_id`)
  - Replay smoke command surface now also exposes operator-visible replay proof context per case:
    - `fixture_root`
    - `trade_date`
    - `replay_id`
    - `evidence_output_dir`
    - explicit per-case `error`
  - `tests/Unit/MarketData/OpsCommandSurfaceTest.php` now covers:
    - option propagation (`--fixture_root`, `--output_dir`, `--continue_on_error`)
    - success / held / error rendering
    - operator-visible identifiers
    - replay smoke failure exit discipline when any case deviates from expected outcome
- PROOF:
  - php -l checks -> PASS
  - targeted PHPUnit -> NOT RUN IN THIS ENVIRONMENT (`vendor/` missing from uploaded ZIP)
  - full PHPUnit suite -> NOT RUN IN THIS ENVIRONMENT (`vendor/` missing from uploaded ZIP)

- OPEN GAP:
  - broader ops family beyond command-surface parity still PARTIAL until local PHPUnit reconfirms the new smoke-surface patch and the next ops/runbook gap is closed.

- NEXT REQUIRED ACTION:
  - run local targeted/full PHPUnit for the updated ops command surface
  - if green, continue next narrow ops/runbook parity gap in SESSION 13
