# Weekly Swing — Canonical Strategy Index

> **Doc Role:** STRATEGY INDEX — NOT A STRATEGY OWNER
> **Default mutability:** STABLE. See `../../governance/DOCUMENT_CHANGE_POLICY.md`.

Folder ini hanya memuat current Weekly Swing behavior dan acceptance strategy. Research, implementation detail, test/result, operator evidence, runtime history, serta superseded strategy berada di layer dokumentasi masing-masing.

## Product Direction

Canonical objective:

`trusted Market Data → eligible candidates → immutable PLAN → qualified RECOMMENDATION/TOP PICKS → D+1 CONFIRM actionability → manual buy decision`

Quality lebih penting daripada jumlah output. Final Top Picks dapat berjumlah nol atau sebanyak candidate yang benar-benar lulus qualification gate.

## Authoritative Lifecycle Sequence

Canonical strategy filenames memakai **nama semantik tanpa nomor urut**. Filename menjelaskan tanggung jawab dokumen; **urutan pembangunan authoritative hanya stage `WS-S00..WS-S11`** yang dikunci di:

`WS_END_TO_END_STRATEGY_LIFECYCLE.md`

Ringkasannya:

`WS-S00 scope/objective`
`→ WS-S01 trusted Market Data binding`
`→ WS-S02 eligibility/classification`
`→ WS-S03 PLAN scoring/trade-plan freeze`
`→ WS-S04 final qualification + ranked Top Picks`
`→ WS-S05 D+1 CONFIRM`
`→ WS-S06 historical evaluation model`
`→ WS-S07 IS sufficiency + winner freeze`
`→ WS-S08 untouched OOS`
`→ WS-S09 adverse-friction stress`
`→ WS-S10 forward shadow`
`→ WS-S11 production-use boundary`

## Stage-to-Document Map

| Stage | Strategy owner(s) |
|---|---|
| `WS-S00` | `WS_SCOPE_AND_SUCCESS_CRITERIA.md`, `WS_PRODUCT_OBJECTIVE_AND_LAYERS.md` |
| `WS-S01` | `WS_RUNTIME_FLOW.md`, `WS_CANDIDATE_ELIGIBILITY_AND_SETUP.md` |
| `WS-S02` | `WS_CANDIDATE_ELIGIBILITY_AND_SETUP.md`, `WS_CANDIDATE_CLASSIFICATION.md` |
| `WS-S03` | `WS_PLAN_SCORING_AND_TRADE_PLAN.md` |
| `WS-S04` | `WS_TOP_PICKS_RECOMMENDATION.md`, `WS_TOP_PICKS_QUALIFICATION_AND_RANKING.md` |
| `WS-S05` | `WS_D1_CONFIRM_ACTIONABILITY.md` |
| `WS-S06` | `WS_HISTORICAL_EVALUATION_STRATEGY.md` |
| `WS-S07` | `validation/WS_IS_SUFFICIENCY_AND_WINNER_FREEZE.md` |
| `WS-S08..WS-S11` | `validation/WS_OOS_STRESS_SHADOW_AND_PRODUCTION_PROOF.md` |

## Required Reading Order

1. `WS_SCOPE_AND_SUCCESS_CRITERIA.md` — product scope and hard boundary.
2. `WS_PRODUCT_OBJECTIVE_AND_LAYERS.md` — product objective, naming, and recommendation meaning.
3. `WS_END_TO_END_STRATEGY_LIFECYCLE.md` — authoritative stage/dependency/handoff map.
4. `WS_RUNTIME_FLOW.md` — runtime relationship PLAN → RECOMMENDATION → CONFIRM.
5. `WS_CANDIDATE_ELIGIBILITY_AND_SETUP.md` — absolute eligibility/setup rules.
6. `WS_CANDIDATE_CLASSIFICATION.md` — deterministic candidate states before scoring.
7. `WS_PLAN_SCORING_AND_TRADE_PLAN.md` — scoring, ordering, trade-plan derivation, PLAN freeze.
8. `WS_TOP_PICKS_RECOMMENDATION.md` — final recommendation semantics.
9. `WS_TOP_PICKS_QUALIFICATION_AND_RANKING.md` — final qualification and Top Picks ranking.
10. `WS_D1_CONFIRM_ACTIONABILITY.md` — D+1 current actionability.
11. `WS_HISTORICAL_EVALUATION_STRATEGY.md` — causal historical evaluation model.
12. `validation/WS_IS_SUFFICIENCY_AND_WINNER_FREEZE.md` — IS sufficiency and best-IS freeze.
13. `validation/WS_OOS_STRESS_SHADOW_AND_PRODUCTION_PROOF.md` — OOS, friction stress, forward shadow, production-use boundary.

## Runtime Strategy Owners

- `WS_SCOPE_AND_SUCCESS_CRITERIA.md`
- `WS_PRODUCT_OBJECTIVE_AND_LAYERS.md`
- `WS_RUNTIME_FLOW.md`
- `WS_CANDIDATE_ELIGIBILITY_AND_SETUP.md`
- `WS_CANDIDATE_CLASSIFICATION.md`
- `WS_PLAN_SCORING_AND_TRADE_PLAN.md`
- `WS_TOP_PICKS_RECOMMENDATION.md`
- `WS_TOP_PICKS_QUALIFICATION_AND_RANKING.md`
- `WS_D1_CONFIRM_ACTIONABILITY.md`

## Proof / Acceptance Strategy Owners

- `WS_HISTORICAL_EVALUATION_STRATEGY.md`
- `validation/WS_IS_SUFFICIENCY_AND_WINNER_FREEZE.md`
- `validation/WS_OOS_STRESS_SHADOW_AND_PRODUCTION_PROOF.md`

## Orchestration Owner

- `WS_END_TO_END_STRATEGY_LIFECYCLE.md`

Dokumen lifecycle memiliki authority untuk **stage order, dependency, handoff, dan stop condition**. Dokumen behavioral individual tetap memiliki authority untuk rule detail pada area masing-masing.

## Parameter Identity Rule

Canonical files own Weekly Swing behavior dan parameter semantics. Exact promoted numeric thresholds/weights are frozen in a versioned strategy/paramset identity and may only change through research/evidence/decision + new proof. A numeric change within already-defined semantics does not require rewriting these strategy documents; a semantic behavior change does.

## Layer Boundaries

- technical translation: `../../implementation/`
- experiments / preregistration: `../../research/weekly_swing/`
- actual outcomes / runtime evidence: `../../evidence/weekly_swing/`
- findings: `../../findings/weekly_swing/`
- decisions: `../../decisions/weekly_swing/`
- superseded / migration history: `../../history/weekly_swing/`
