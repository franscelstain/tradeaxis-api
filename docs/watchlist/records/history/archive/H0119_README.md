# Weekly Swing — Canonical Strategy Index

> **Doc Role:** STRATEGY INDEX — NOT A STRATEGY OWNER
> **Default mutability:** STABLE. See `../../governance/DOCUMENT_CHANGE_POLICY.md`.

Folder ini hanya memuat current Weekly Swing behavior dan acceptance strategy. Research, implementation detail, test/result, operator evidence, runtime history, serta superseded strategy berada di layer dokumentasi masing-masing.

## Product Direction

Canonical objective:

`trusted Market Data → PLAN recommendation candidates → qualified RECOMMENDATION → ranked TOP PICKS → D+1 CONFIRM actionability → manual buy decision`

Quality lebih penting daripada jumlah output. Final Top Picks dapat berjumlah nol atau sebanyak candidate yang benar-benar lulus qualification gate.

## Core Strategy

1. `00_WS_SCOPE_LOCK.md`
2. `01_WS_OVERVIEW.md`
3. `02_WS_CANONICAL_RUNTIME_FLOW.md`
4. `03_WS_CANDIDATE_ELIGIBILITY_AND_SETUP.md`
5. `08_WS_PLAN_ALGORITHM.md`
6. `09_WS_CANDIDATE_CLASSIFICATION_DETERMINISTIC.md`
7. `22_WS_RECOMMENDATION_OVERVIEW.md`
8. `24_WS_RECOMMENDATION_ALGORITHM.md`
9. `10_WS_CONFIRM_OVERLAY.md`
10. `12_WS_BACKTEST_AND_CALIBRATION_STRATEGY.md`

## Strategy Validation / Acceptance

1. `validation/16_WS_EVAL_METRICS_SUFFICIENCY_LOCKED.md`
2. `validation/17_WS_WALK_FORWARD_OOS_PROOF_LOCKED.md`

## Parameter Identity Rule

Canonical files own Weekly Swing behavior and parameter semantics. Exact promoted numeric thresholds/weights are frozen in a versioned strategy/paramset identity and may only change through research/evidence/decision + new proof. A numeric change within already-defined semantics does not require rewriting these strategy documents; a semantic behavior change does.

## Layer Boundaries

- technical translation: `../../implementation/`
- experiments / preregistration: `../../research/weekly_swing/`
- actual outcomes / runtime evidence: `../../evidence/weekly_swing/`
- findings: `../../findings/weekly_swing/`
- decisions: `../../decisions/weekly_swing/`
- superseded / migration history: `../../history/weekly_swing/`
