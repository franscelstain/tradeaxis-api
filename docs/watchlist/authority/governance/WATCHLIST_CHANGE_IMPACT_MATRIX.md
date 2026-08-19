# Watchlist Change Impact Matrix

> **Status:** CANONICAL GOVERNANCE
> **Active scope:** Weekly Swing only

Gunakan matriks ini untuk menentukan layer yang wajib diperiksa ketika terjadi perubahan. Matriks ini tidak mengizinkan implementation/evidence mengubah strategy secara otomatis.

## Strategy Change — End-to-End Stage / Handoff

Wajib review:
- `../strategy/WS_END_TO_END_STRATEGY_LIFECYCLE.md`;
- seluruh strategy owner yang memiliki input/output pada stage terdampak;
- runtime flow bila handoff `PLAN → RECOMMENDATION → CONFIRM` terdampak;
- backtest/IS/OOS proof bila perubahan mengubah evaluated runtime behavior atau proof identity.

## Strategy / Producer Contract Change — Market Data Intake

Wajib review:
- `../strategy/WS_MARKET_DATA_INPUT_REQUIREMENTS.md`;
- `../../market_data/book/CONSUMER_READ_CONTRACT_LOCKED.md`;
- `../../market_data/book/Downstream_Consumer_Read_Model_Contract_LOCKED.md`;
- `../../market_data/book/Downstream_Data_Readiness_Guarantee_LOCKED.md`;
- `../../development/implementation/MARKET_DATA_INTAKE_IMPLEMENTATION_CONTRACT.md`;
- candidate eligibility, PLAN, historical evaluation, and CONFIRM strategy bila required field/timing meaning terdampak;
- `../../system_audit/SYSTEM_CROSS_DOMAIN_INPUT_BASELINE.md`.

Perubahan physical table/schema producer saja tidak mengubah Watchlist selama producer-facing semantic contract tetap compatible. Perubahan field meaning, readiness/fallback semantics, liquidity basis, or required identity creates an integration/proof impact and must not be absorbed silently by the adapter.

## Strategy Change — Recommendation

Wajib review:
- `../strategy/WS_TOP_PICKS_RECOMMENDATION.md`;
- `../strategy/WS_TOP_PICKS_QUALIFICATION_AND_RANKING.md`;
- canonical flow/PLAN/CONFIRM strategy bila boundary terdampak;
- implementation recommendation contracts/tests/fixtures setelah strategy decision sah;
- backtest/acceptance strategy bila perubahan memengaruhi historical selection/return.


## Strategy Change — Candidate Eligibility / Setup / Scoring

Wajib review:
- `../strategy/WS_CANDIDATE_ELIGIBILITY_AND_SETUP.md`;
- `../strategy/WS_PLAN_SCORING_AND_TRADE_PLAN.md`;
- `../strategy/WS_CANDIDATE_CLASSIFICATION.md`;
- final recommendation strategy;
- backtest/IS/OOS acceptance strategy;
- implementation paramset/validator/PLAN/backtest contracts dan tests.

## Strategy Change — CONFIRM

Wajib review:
- `../strategy/WS_D1_CONFIRM_ACTIONABILITY.md`;
- `../strategy/WS_RUNTIME_FLOW.md`;
- `../strategy/WS_CANDIDATE_ELIGIBILITY_AND_SETUP.md`;
- recommendation strategy untuk memastikan CONFIRM tidak bocor ke ranking/membership;
- implementation CONFIRM persistence/reason codes/tests/examples.

## Strategy Change — PLAN / Group Semantics

Wajib review:
- `../strategy/WS_PLAN_SCORING_AND_TRADE_PLAN.md`;
- `../strategy/WS_CANDIDATE_CLASSIFICATION.md`;
- canonical flow;
- recommendation strategy;
- backtest/acceptance strategy bila candidate universe/ranking evaluation berubah;
- related implementation contracts/tests/fixtures.

## Strategy Change — Backtest / Acceptance

Wajib review:
- `../strategy/WS_HISTORICAL_EVALUATION_STRATEGY.md`;
- `../strategy/WS_IS_SUFFICIENCY_AND_WINNER_FREEZE.md`;
- `../strategy/WS_OOS_STRESS_SHADOW_AND_PRODUCTION_PROOF.md`;
- implementation backtest contracts/schema/artifact/tests;
- research/evidence comparability impact.

## Documentation Scope / Owner Change

Wajib review:
- `WATCHLIST_DOCUMENT_AUTHORITY.md`;
- `DOCUMENTATION_ARCHITECTURE.md`;
- `DOCUMENT_CHANGE_POLICY.md`;
- `WATCHLIST_OWNER_MATRIX.md`;
- `../strategy/README.md`;
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
