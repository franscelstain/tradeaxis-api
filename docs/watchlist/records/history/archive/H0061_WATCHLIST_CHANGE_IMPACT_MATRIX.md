# Watchlist Change Impact Matrix

> **Status:** CANONICAL GOVERNANCE
> **Active scope:** Weekly Swing only

Gunakan matriks ini untuk menentukan layer yang wajib diperiksa ketika terjadi perubahan. Matriks ini tidak mengizinkan implementation/evidence mengubah strategy secara otomatis.

## Strategy Change — End-to-End Stage / Handoff

Wajib review:
- `../strategy/weekly_swing/WS_END_TO_END_STRATEGY_LIFECYCLE.md`;
- seluruh strategy owner yang memiliki input/output pada stage terdampak;
- runtime flow bila handoff `PLAN → RECOMMENDATION → CONFIRM` terdampak;
- backtest/IS/OOS proof bila perubahan mengubah evaluated runtime behavior atau proof identity.

## Strategy Change — Recommendation

Wajib review:
- `../strategy/weekly_swing/WS_TOP_PICKS_RECOMMENDATION.md`;
- `../strategy/weekly_swing/WS_TOP_PICKS_QUALIFICATION_AND_RANKING.md`;
- canonical flow/PLAN/CONFIRM strategy bila boundary terdampak;
- implementation recommendation contracts/tests/fixtures setelah strategy decision sah;
- backtest/acceptance strategy bila perubahan memengaruhi historical selection/return.


## Strategy Change — Candidate Eligibility / Setup / Scoring

Wajib review:
- `../strategy/weekly_swing/WS_CANDIDATE_ELIGIBILITY_AND_SETUP.md`;
- `../strategy/weekly_swing/WS_PLAN_SCORING_AND_TRADE_PLAN.md`;
- `../strategy/weekly_swing/WS_CANDIDATE_CLASSIFICATION.md`;
- final recommendation strategy;
- backtest/IS/OOS acceptance strategy;
- implementation paramset/validator/PLAN/backtest contracts dan tests.

## Strategy Change — CONFIRM

Wajib review:
- `../strategy/weekly_swing/WS_D1_CONFIRM_ACTIONABILITY.md`;
- `../strategy/weekly_swing/WS_RUNTIME_FLOW.md`;
- `../strategy/weekly_swing/WS_CANDIDATE_ELIGIBILITY_AND_SETUP.md`;
- recommendation strategy untuk memastikan CONFIRM tidak bocor ke ranking/membership;
- implementation CONFIRM persistence/reason codes/tests/examples.

## Strategy Change — PLAN / Group Semantics

Wajib review:
- `../strategy/weekly_swing/WS_PLAN_SCORING_AND_TRADE_PLAN.md`;
- `../strategy/weekly_swing/WS_CANDIDATE_CLASSIFICATION.md`;
- canonical flow;
- recommendation strategy;
- backtest/acceptance strategy bila candidate universe/ranking evaluation berubah;
- related implementation contracts/tests/fixtures.

## Strategy Change — Backtest / Acceptance

Wajib review:
- `../strategy/weekly_swing/WS_HISTORICAL_EVALUATION_STRATEGY.md`;
- `../strategy/weekly_swing/validation/WS_IS_SUFFICIENCY_AND_WINNER_FREEZE.md`;
- `../strategy/weekly_swing/validation/WS_OOS_STRESS_SHADOW_AND_PRODUCTION_PROOF.md`;
- implementation backtest contracts/schema/artifact/tests;
- research/evidence comparability impact.

## Documentation Scope / Owner Change

Wajib review:
- `WATCHLIST_DOCUMENT_AUTHORITY.md`;
- `DOCUMENTATION_ARCHITECTURE.md`;
- `DOCUMENT_CHANGE_POLICY.md`;
- `WATCHLIST_OWNER_MATRIX.md`;
- `../strategy/weekly_swing/README.md`;
- root `../README.md`;
- link/migration validation.

## Implementation-Only Change

Jika behavior strategy tidak berubah, update hanya implementation + tests/evidence yang relevan. Canonical strategy **tidak perlu dan tidak boleh** diubah hanya untuk mencatat refactor, schema implementation, command, test result, atau operator progress.

## Evidence / Finding / Decision Change

- New evidence → append/update evidence record only.
- New finding → findings layer.
- Decision without behavior change → decisions layer.
- Decision with behavior change → wajib memenuhi `DOCUMENT_CHANGE_POLICY.md` sebelum canonical strategy direvisi.

## Rule

Perubahan dianggap lengkap hanya setelah seluruh **affected owner layer** diperiksa. Cross-check tidak berarti semua file harus diubah; file yang tidak terdampak harus tetap stabil.
