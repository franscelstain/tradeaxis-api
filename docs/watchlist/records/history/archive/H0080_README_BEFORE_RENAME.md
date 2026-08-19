# Weekly Swing — Canonical Strategy Index

> **Doc Role:** STRATEGY INDEX — NOT A STRATEGY OWNER
> **Default mutability:** STABLE. See `../../governance/DOCUMENT_CHANGE_POLICY.md`.

Folder ini hanya memuat current Weekly Swing behavior dan acceptance strategy. Research, implementation detail, test/result, operator evidence, runtime history, serta superseded strategy berada di layer dokumentasi masing-masing.

## Product Direction

Canonical objective:

`trusted Market Data → eligible candidates → immutable PLAN → qualified RECOMMENDATION/TOP PICKS → D+1 CONFIRM actionability → manual buy decision`

Quality lebih penting daripada jumlah output. Final Top Picks dapat berjumlah nol atau sebanyak candidate yang benar-benar lulus qualification gate.

## Authoritative Lifecycle Sequence

**Jangan memakai prefix nomor filename sebagai build order.** Prefix tersebut adalah stable document identifier dari evolusi dokumentasi. Urutan authoritative adalah stage `WS-S00..WS-S11` yang dikunci di:

`04_WS_END_TO_END_STRATEGY_LIFECYCLE.md`

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
| `WS-S00` | `00_WS_SCOPE_LOCK.md`, `01_WS_OVERVIEW.md` |
| `WS-S01` | `02_WS_CANONICAL_RUNTIME_FLOW.md`, `03_WS_CANDIDATE_ELIGIBILITY_AND_SETUP.md` |
| `WS-S02` | `03_WS_CANDIDATE_ELIGIBILITY_AND_SETUP.md`, `09_WS_CANDIDATE_CLASSIFICATION_DETERMINISTIC.md` |
| `WS-S03` | `08_WS_PLAN_ALGORITHM.md` |
| `WS-S04` | `22_WS_RECOMMENDATION_OVERVIEW.md`, `24_WS_RECOMMENDATION_ALGORITHM.md` |
| `WS-S05` | `10_WS_CONFIRM_OVERLAY.md` |
| `WS-S06` | `12_WS_BACKTEST_AND_CALIBRATION_STRATEGY.md` |
| `WS-S07` | `validation/16_WS_EVAL_METRICS_SUFFICIENCY_LOCKED.md` |
| `WS-S08..WS-S11` | `validation/17_WS_WALK_FORWARD_OOS_PROOF_LOCKED.md` |

## Required Reading Order

1. `00_WS_SCOPE_LOCK.md` — product scope and hard boundary.
2. `01_WS_OVERVIEW.md` — product objective, naming, and recommendation meaning.
3. `04_WS_END_TO_END_STRATEGY_LIFECYCLE.md` — authoritative stage/dependency/handoff map.
4. `02_WS_CANONICAL_RUNTIME_FLOW.md` — runtime relationship PLAN → RECOMMENDATION → CONFIRM.
5. `03_WS_CANDIDATE_ELIGIBILITY_AND_SETUP.md` — absolute eligibility/setup rules.
6. `09_WS_CANDIDATE_CLASSIFICATION_DETERMINISTIC.md` — deterministic candidate states before scoring.
7. `08_WS_PLAN_ALGORITHM.md` — scoring, ordering, trade-plan derivation, PLAN freeze.
8. `22_WS_RECOMMENDATION_OVERVIEW.md` — final recommendation semantics.
9. `24_WS_RECOMMENDATION_ALGORITHM.md` — final qualification and Top Picks ranking.
10. `10_WS_CONFIRM_OVERLAY.md` — D+1 current actionability.
11. `12_WS_BACKTEST_AND_CALIBRATION_STRATEGY.md` — causal historical evaluation model.
12. `validation/16_WS_EVAL_METRICS_SUFFICIENCY_LOCKED.md` — IS sufficiency and best-IS freeze.
13. `validation/17_WS_WALK_FORWARD_OOS_PROOF_LOCKED.md` — OOS, friction stress, forward shadow, production-use boundary.

## Runtime Strategy Owners

- `00_WS_SCOPE_LOCK.md`
- `01_WS_OVERVIEW.md`
- `02_WS_CANONICAL_RUNTIME_FLOW.md`
- `03_WS_CANDIDATE_ELIGIBILITY_AND_SETUP.md`
- `09_WS_CANDIDATE_CLASSIFICATION_DETERMINISTIC.md`
- `08_WS_PLAN_ALGORITHM.md`
- `22_WS_RECOMMENDATION_OVERVIEW.md`
- `24_WS_RECOMMENDATION_ALGORITHM.md`
- `10_WS_CONFIRM_OVERLAY.md`

## Proof / Acceptance Strategy Owners

- `12_WS_BACKTEST_AND_CALIBRATION_STRATEGY.md`
- `validation/16_WS_EVAL_METRICS_SUFFICIENCY_LOCKED.md`
- `validation/17_WS_WALK_FORWARD_OOS_PROOF_LOCKED.md`

## Orchestration Owner

- `04_WS_END_TO_END_STRATEGY_LIFECYCLE.md`

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
