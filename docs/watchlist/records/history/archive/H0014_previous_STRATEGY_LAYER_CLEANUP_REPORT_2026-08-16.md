# Watchlist Weekly Swing Strategy Layer Cleanup Report — 2026-08-16

> **Scope:** documentation-role separation only
> **Strategy behavior modification:** NONE intended

## Completed

- Removed historical campaign notes from canonical PLAN/dynamic-selection strategy.
- Removed compatibility-history note from canonical runtime-flow strategy.
- Removed CONFIRM reason-code catalogue from strategy; implementation copies remain authoritative for identifiers.
- Renamed mixed backtest document to `12_WS_BACKTEST_AND_CALIBRATION_STRATEGY.md`.
- Rewrote backtest strategy so it owns behavioral evaluation/calibration semantics only.
- Created `implementation/weekly_swing/contracts/WS_BACKTEST_EVALUATION_TECHNICAL_CONTRACT.md` for table/schema/SQL/reason-code/serialization details.
- Rewrote metric sufficiency and OOS proof docs to remove physical storage/DDL/artifact-layout ownership while preserving acceptance semantics.
- Corrected strategy README and overview so technical documents are no longer presented as canonical strategy owners.
- Preserved pre-cleanup snapshots in documentation migration history.
- Added finding + decision records governing the cleanup.

## Explicitly Not Changed

No intended change to PLAN/RECOMMENDATION/CONFIRM behavior, evaluation horizon, NEXT_OPEN entry baseline, stop/target precedence, canonical notional/fee/slippage assumptions, acceptance thresholds, or IS/OOS split/gates.
## Governance / Reference Consistency Cleanup

- Rebuilt `WATCHLIST_OWNER_MATRIX.md`: implementation files are no longer listed as normative policy owners.
- Rebuilt `WATCHLIST_CHANGE_IMPACT_MATRIX.md` using the new architecture.
- Replaced multi-strategy-oriented `STRATEGY_POLICY_FRAMEWORK.md` with `WEEKLY_SWING_POLICY_FRAMEWORK.md`; shared technical contracts do not imply another active strategy.
- Updated `implementation/shared/README.md` to clarify it is technical support, not strategy authority.
- Updated active artifact-reference fixture paths to strategy/implementation split (`V5`).
- Moved prior architecture report/validation into documentation history so prior-state reports are not mistaken for current governance.
- Added path-authority guard to the evidence ledger; historical path text remains preserved as evidence.

## Final Validation

Status: **PASS**.

- canonical strategy campaign tokens: `0`;
- physical `watchlist_bt_*` table names in canonical strategy: `0`;
- SQL blocks in canonical strategy: `0`;
- SHA1/PHPUnit historical-result tokens in canonical strategy: `0`;
- historical/migration leakage markers in canonical strategy: `0`;
- stale active references to legacy `docs/watchlist/system/` or `docs/watchlist/audit/`: `0`;
- stale active strategy support paths (`strategy/.../_refs|fixtures|examples|db`): `0`;
- broken Markdown links in active documentation: `0`;
- JSON parse errors: `0`;
- CSV parse errors: `0`;
- key strategy behavior preservation markers missing: `0`;
- pre-cleanup reason/rule tokens missing after relocation: `0`;
- pre-cleanup physical backtest table tokens missing after relocation: `0`;
- pre-cleanup evaluation marker classes missing after relocation: `0`.

Raw files under `history/.../documentation_migrations/` intentionally preserve their pre-move content and original relative links; they are historical snapshots, not active navigation surfaces.

