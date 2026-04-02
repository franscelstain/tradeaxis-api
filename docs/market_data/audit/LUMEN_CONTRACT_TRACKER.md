# LUMEN_CONTRACT_TRACKER

## CONTRACT ITEM 8 — Ops backfill / replay / evidence export command semantics
- STATUS: PARTIAL
- OWNER AREA: ops backfill, replay, and evidence export command/service semantics
- LAST UPDATED SESSION: session11_proof_complete
- EVIDENCE:
  - owner-doc target remains `docs/market_data/ops/Commands_and_Runbook_LOCKED.md`, `docs/market_data/ops/Bootstrap_and_Backfill_Runbook_LOCKED.md`
  - Backfill & replay command surface now expose required operator-level fields:
    - summary-level `source_mode`
    - per-date `publishability_state`
    - per-date `trade_date_effective`
    - explicit `error` text
    - replay identifiers (`run_id`, `replay_id`)
  - `tests/Unit/MarketData/OpsCommandSurfaceTest.php` covers:
    - option propagation (`--fixture_root`, `--output_dir`, `--continue_on_error`)
    - success / held / error rendering
    - operator-visible identifiers
- PROOF:
  - php -l checks -> PASS
  - targeted PHPUnit -> PASS
  - full PHPUnit suite -> PASS (115 tests, 1256 assertions)

- OPEN GAP:
  - broader ops family beyond command-surface still PARTIAL

- NEXT REQUIRED ACTION:
  - continue next narrow ops/runbook parity gap (SESSION 12)
