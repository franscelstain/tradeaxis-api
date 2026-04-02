# LUMEN_CONTRACT_TRACKER

## CONTRACT ITEM 8 — Ops backfill / replay / evidence export command semantics
- STATUS: PARTIAL
- OWNER AREA: ops backfill, replay, and evidence export command/service semantics
- LAST UPDATED SESSION: session8_evidence_export_command_surface_hardening_proof_synced
- EVIDENCE:
  - owner-doc target remains `docs/market_data/ops/Commands_and_Runbook_LOCKED.md`, `docs/market_data/ops/Bootstrap_and_Backfill_Runbook_LOCKED.md`, and related replay/evidence contracts under `docs/market_data/ops/`
  - `ExportEvidenceCommand` now requires exactly one selector among `--run_id`, `--correction_id`, or `--replay_id`
  - ambiguous selector input is now rejected explicitly instead of silently preferring run export path
  - `tests/Unit/MarketData/OpsCommandSurfaceTest.php` now covers:
    - missing-selector failure path for `market-data:evidence:export`
    - ambiguous multi-selector failure path for `market-data:evidence:export`
    - run evidence export success path
    - correction evidence export success path
    - replay evidence export success path
  - proof synced from local user environment:
    - `php -l app/Console/Commands/MarketData/ExportEvidenceCommand.php` -> passed
    - `php -l tests/Unit/MarketData/OpsCommandSurfaceTest.php` -> passed
    - `vendor\\bin\\phpunit tests\\Unit\\MarketData\\OpsCommandSurfaceTest.php` -> passed (`OK (10 tests, 39 assertions)`)
    - `vendor\\bin\\phpunit` -> passed (`OK (111 tests, 1231 assertions)`)
- OPEN GAP:
  - wider ops family remains partial beyond this narrowed evidence-export command surface gap
  - broader parity across replay/backfill/evidence operational matrix can still be tightened later
- NEXT REQUIRED ACTION:
  - keep the session-8 evidence-export sub-gap closed and move to the next narrow ops/replay/evidence gap under CONTRACT ITEM 8
  - continue tightening broader command/runbook parity without reopening already closed finalize/correction batches
