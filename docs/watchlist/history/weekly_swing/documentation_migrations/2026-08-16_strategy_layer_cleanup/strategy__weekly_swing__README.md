# Weekly Swing — Canonical Strategy Index

> **Doc Role:** CANONICAL STRATEGY INDEX
> **Default mutability:** STABLE. See `../../governance/DOCUMENT_CHANGE_POLICY.md`.

Folder ini adalah owner current Weekly Swing behavior. Campaign-specific research, operator evidence, hashes, runtime results, dan implementation history tidak boleh ditambahkan ke canonical strategy.

## Core Strategy

1. `00_WS_SCOPE_LOCK.md`
2. `01_WS_OVERVIEW.md`
3. `02_WS_CANONICAL_RUNTIME_FLOW.md`
4. `05_WS_PARAMETER_REGISTRY_COMPLETE.md`
5. `08_WS_PLAN_ALGORITHM.md`
6. `09_WS_DYNAMIC_SELECTION_DETERMINISTIC.md`
7. `22_WS_RECOMMENDATION_OVERVIEW.md`
8. `24_WS_RECOMMENDATION_ALGORITHM.md`
9. `10_WS_CONFIRM_OVERLAY.md`
10. `12_WS_BACKTEST_SCHEMA_AND_CALIBRATION.md`

## Strategy Validation / Acceptance

- `validation/14_WS_BT_COVERAGE_MATRIX_LOCKED.md`
- `validation/15_WS_UNIVERSE_EQUIVALENCE_CONTRACT_LOCKED.md`
- `validation/16_WS_EVAL_METRICS_SUFFICIENCY_LOCKED.md`
- `validation/17_WS_WALK_FORWARD_OOS_PROOF_LOCKED.md`

## Technical Translation

Parameter registry, schema, JSON contracts, validators, reason codes, BT coverage/equivalence verification, persistence, tests, procedures, fixtures, examples, and API/module mapping live under `../../implementation/`. They must follow this strategy and are not strategy owners.

## Research / Evidence / History

- experiments and preregistration: `../../research/weekly_swing/`
- actual outcomes: `../../evidence/weekly_swing/`
- findings: `../../findings/weekly_swing/`
- decisions: `../../decisions/weekly_swing/`
- superseded/campaign history: `../../history/weekly_swing/`
