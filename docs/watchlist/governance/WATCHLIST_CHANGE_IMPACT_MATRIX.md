# Watchlist Change Impact Matrix

> **Status:** CANONICAL GOVERNANCE
> **Active scope:** Weekly Swing only

Gunakan matriks ini untuk menentukan layer yang wajib diperiksa ketika terjadi perubahan. Matriks ini tidak mengizinkan implementation/evidence mengubah strategy secara otomatis.

## Strategy Change — Recommendation

Wajib review:
- `../strategy/weekly_swing/22_WS_RECOMMENDATION_OVERVIEW.md`;
- `../strategy/weekly_swing/24_WS_RECOMMENDATION_ALGORITHM.md`;
- canonical flow/PLAN/CONFIRM strategy bila boundary terdampak;
- implementation recommendation contracts/tests/fixtures setelah strategy decision sah;
- backtest/acceptance strategy bila perubahan memengaruhi historical selection/return.

## Strategy Change — CONFIRM

Wajib review:
- `../strategy/weekly_swing/10_WS_CONFIRM_OVERLAY.md`;
- `../strategy/weekly_swing/02_WS_CANONICAL_RUNTIME_FLOW.md`;
- recommendation strategy untuk memastikan CONFIRM tidak bocor ke ranking/membership;
- implementation CONFIRM persistence/reason codes/tests/examples.

## Strategy Change — PLAN / Group Semantics

Wajib review:
- `../strategy/weekly_swing/08_WS_PLAN_ALGORITHM.md`;
- `../strategy/weekly_swing/09_WS_DYNAMIC_SELECTION_DETERMINISTIC.md`;
- canonical flow;
- recommendation strategy;
- backtest/acceptance strategy bila candidate universe/ranking evaluation berubah;
- related implementation contracts/tests/fixtures.

## Strategy Change — Backtest / Acceptance

Wajib review:
- `../strategy/weekly_swing/12_WS_BACKTEST_AND_CALIBRATION_STRATEGY.md`;
- `../strategy/weekly_swing/validation/16_WS_EVAL_METRICS_SUFFICIENCY_LOCKED.md`;
- `../strategy/weekly_swing/validation/17_WS_WALK_FORWARD_OOS_PROOF_LOCKED.md`;
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
