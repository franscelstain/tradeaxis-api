# Weekly Swing Documentation Role Separation Cleanup — 2026-08-16

> **Doc Role:** DECISION
> **Decision:** APPROVED — DOCUMENTATION-ONLY ROLE CLEANUP
> **Trading behavior change:** NONE INTENDED

## Context

Finding `../../findings/weekly_swing/WS_STRATEGY_LAYER_ROLE_LEAKAGE_2026-08-16.md` menunjukkan canonical strategy masih mengandung history/migration wording dan physical implementation details.

## Decision

1. Remove history/migration notes from canonical strategy.
2. Move reason-code, DDL/schema/table, SQL/query, serialization, artifact-layout, and implementation verification details to `implementation/weekly_swing/`.
3. Preserve behavioral evaluation assumptions and acceptance gates in strategy.
4. Rename mixed `12_WS_BACKTEST_SCHEMA_AND_CALIBRATION.md` to `12_WS_BACKTEST_AND_CALIBRATION_STRATEGY.md` because physical schema is no longer owned by strategy.
5. Preserve pre-cleanup snapshots under `history/weekly_swing/documentation_migrations/2026-08-16_strategy_layer_cleanup/`.
6. Update indexes/references so implementation docs are not presented as canonical strategy owners.

## Guard

This decision does not authorize changes to Weekly Swing scoring, ranking, Top Picks/recommendation semantics, thresholds, entry/exit behavior, horizon, fee/slippage assumptions, IS/OOS acceptance, or any trading behavior. Those require separate strategy review and material evidence.
