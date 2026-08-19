# Weekly Swing Strategy Decision — End-to-End Lifecycle

> **Current-status annotation — partially superseded**
>
> The linear dependency that placed `WS-S05` CONFIRM inside mandatory runtime completion and made CONFIRM-dependent forward shadow a prerequisite for core production-use review is superseded by `WS_STRATEGY_DECISION_OPTIONAL_CONFIRM_NON_BLOCKING.md`. The remaining lifecycle/coherence decisions in this record remain historical rationale for the current stage model. Current authority: core runtime `WS-S00..WS-S04`, optional non-blocking `WS-S05`, core proof `WS-S06..WS-S11`.

## Decision

Weekly Swing memakai authoritative lifecycle stages:

`WS-S00 → WS-S01 → WS-S02 → WS-S03 → WS-S04 → WS-S05 → WS-S06 → WS-S07 → WS-S08 → WS-S09 → WS-S10 → WS-S11`

Stage semantics:

1. `WS-S00` — scope/objective/success lock;
2. `WS-S01` — trusted Market Data binding;
3. `WS-S02` — absolute eligibility + candidate-state classification;
4. `WS-S03` — PLAN scoring/order/trade-plan freeze;
5. `WS-S04` — final qualification + ranked Top Picks;
6. `WS-S05` — D+1 CONFIRM actionability;
7. `WS-S06` — historical evaluation model;
8. `WS-S07` — IS sufficiency + exactly one winner freeze;
9. `WS-S08` — untouched OOS proof;
10. `WS-S09` — adverse-friction robustness;
11. `WS-S10` — forward-shadow full flow including CONFIRM;
12. `WS-S11` — production-use eligibility boundary/review.

## Coherence Corrections

- candidate classification terjadi setelah absolute candidacy requirements lengkap tetapi sebelum PLAN scoring;
- only `RECOMMENDATION_CANDIDATES` enter canonical scoring/recommendation path;
- historical evaluation proves EOD final-Top-Picks edge and must not fabricate unavailable historical CONFIRM observations;
- D+1 CONFIRM is a distinct decision-time input and is proven in forward shadow;
- OOS/stress/shadow consume exactly one winner frozen by IS stage and cannot recalibrate it;
- file-number prefixes are not authoritative build order; lifecycle stage IDs are.

## Rationale

Perubahan ini tidak membuka strategy baru dan tidak mengubah product objective qualified Top Picks. Ia menghilangkan circular dependency, duplicated stage authority, dan time-role ambiguity yang dapat membuat implementation menyimpang walaupun membaca rule individual secara benar.

## Affected Strategy Owners

- `../../authority/strategy/WS_RUNTIME_FLOW.md`;
- `../../authority/strategy/WS_CANDIDATE_ELIGIBILITY_AND_SETUP.md`;
- `../../authority/strategy/WS_END_TO_END_STRATEGY_LIFECYCLE.md` (new orchestration owner);
- `../../authority/strategy/WS_PLAN_SCORING_AND_TRADE_PLAN.md`;
- `../../authority/strategy/WS_CANDIDATE_CLASSIFICATION.md`;
- `../../authority/strategy/WS_D1_CONFIRM_ACTIONABILITY.md`;
- `../../authority/strategy/WS_HISTORICAL_EVALUATION_STRATEGY.md`;
- `../../authority/strategy/WS_IS_SUFFICIENCY_AND_WINNER_FREEZE.md`;
- `../../authority/strategy/WS_OOS_STRESS_SHADOW_AND_PRODUCTION_PROOF.md`.

Prior canonical snapshot is preserved at:

`../history/superseded/2026-08-17_pre-end-to-end-lifecycle-alignment/`

## Implementation Consequence

Implementation remains **STRATEGY_REVISED_IMPLEMENTATION_ALIGNMENT_PENDING**. This decision defines the stage contract that technical translation must satisfy; it does not claim code, schema, tests, or prior evidence already conform.
