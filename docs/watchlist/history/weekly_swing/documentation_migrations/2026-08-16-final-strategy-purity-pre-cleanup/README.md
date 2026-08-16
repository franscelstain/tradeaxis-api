# Weekly Swing — Canonical Strategy Index

> **Doc Role:** CANONICAL STRATEGY INDEX
> **Default mutability:** STABLE. See `../../governance/DOCUMENT_CHANGE_POLICY.md`.

Folder ini hanya memuat owner current Weekly Swing behavior dan acceptance strategy. Campaign research, implementation detail, test/result, operator evidence, hashes, runtime history, serta superseded material tidak boleh menjadi owner rule di sini.

## Core Strategy

1. `00_WS_SCOPE_LOCK.md`
2. `01_WS_OVERVIEW.md`
3. `02_WS_CANONICAL_RUNTIME_FLOW.md`
4. `08_WS_PLAN_ALGORITHM.md`
5. `09_WS_DYNAMIC_SELECTION_DETERMINISTIC.md`
6. `22_WS_RECOMMENDATION_OVERVIEW.md`
7. `24_WS_RECOMMENDATION_ALGORITHM.md`
8. `10_WS_CONFIRM_OVERLAY.md`
9. `12_WS_BACKTEST_AND_CALIBRATION_STRATEGY.md`

## Strategy Validation / Acceptance

1. `validation/16_WS_EVAL_METRICS_SUFFICIENCY_LOCKED.md`
2. `validation/17_WS_WALK_FORWARD_OOS_PROOF_LOCKED.md`

## Technical Translation

Parameter registry, data/schema contracts, JSON contracts, validators, reason codes, persistence, BT coverage/universe-equivalence verification, DDL/SQL, tests, procedures, fixtures, examples, artifact serialization, and API/module mapping live under `../../implementation/`. They must implement this strategy and are not strategy owners.

## Supporting Records

- experiments / preregistration: `../../research/weekly_swing/`
- actual outcomes / runtime evidence: `../../evidence/weekly_swing/`
- findings: `../../findings/weekly_swing/`
- decisions: `../../decisions/weekly_swing/`
- documentation/campaign/superseded history: `../../history/weekly_swing/`
