# D-WS-20260819-05 — High-Trust Top Picks Strategy Strengthening

> **Role:** DECISION  
> **Status:** ISSUED  
> **Date:** 2026-08-19  
> **Finding:** `../../development/findings/F-WS-20260819-05_HIGH_TRUST_TOP_PICKS_STRATEGY_GAPS.md`  
> **Evidence:** `../evidence/E-WS-20260819-05_HIGH_TRUST_STRATEGY_GAP_VALIDATION.json`

## Decision

The canonical Weekly Swing strategy is strengthened so that real-use Top Picks are judged by causal executability, statistical robustness, economic significance, ranking usefulness, bounded downside, capacity realism, and ongoing health—not merely by positive historical average return.

The following rules are adopted:

1. **Committed exposure rule:** once a valid entry fill exists, the trade remains in evaluation until economically resolved. A later non-executable exit cannot remove it from the denominator. Unresolved exposure at evaluation cutoff receives conservative `ret_net = -100%` for production-qualification metrics.
2. **Operational entry timing:** canonical D+1-open claim requires `recommendation_available_at` at least **30 minutes** before the governed earliest entry opportunity. Earlier prices cannot be backfilled after late publication.
3. **Purged OOS boundary:** IS recommendations whose outcome dependency overlaps the protected OOS suffix are purged from IS selection metrics.
4. **Consumed OOS:** once OOS outcome/diagnostic quality information is read, that OOS identity is consumed. A materially changed strategy needs a fresh untouched later holdout or forward evidence.
5. **Multiple-testing control:** all relevant selection trials are recorded; IS requires selection-bias-adjusted reliability including `DSR_probability > 0.95`, and `PBO <= 0.20` when PBO is validly computable.
6. **Robust plateau:** isolated parameter spikes are not sufficient winners when meaningful neighboring parameter identities materially fail.
7. **Economic edge:** canonical baseline production proof requires `avg_ret_net_top >= +0.25%` after baseline production friction plus a positive 95% lower confidence bound.
8. **Benchmark/selection uplift:** production proof must demonstrate positive matched-horizon IHSG excess return and positive eligible-universe selection uplift when the required governed benchmark input is available; absence of required input is insufficient evidence, not synthetic PASS.
9. **Top-K ranking proof:** exact Top-1/Top-3/Top-5/All subsets are evaluated without changing membership; rank-focused presentation cannot be called proven when its relevant subset evidence is insufficient or persistently inverted.
10. **Tail-risk floors:** canonical proof adds p05, expected shortfall, MAE, loss streak, date-level drawdown, and non-executable-exit extension metrics. Baseline hard floors include `p05 >= -8%`, `ES05 >= -10%`, and date-level maximum drawdown `<= 20%`.
11. **Capacity proof:** strategy identity freezes `reference_order_notional_idr` and `max_adv20_participation_rate`; selection remains user-capital-independent, but production proof must show the reference notional is executable under the frozen participation assumption.
12. **Classification clarity:** `AVOID` is reserved for hard safety/data/executability/disqualifying conditions; `WATCH_ONLY` is non-recommendation monitoring when hard disqualifiers are absent but quality/setup/feature sufficiency is incomplete.
13. **Score semantics:** `score_total` / `recommendation_score` is an ordinal quality score, not probability of profit. Probability/confidence display requires separate calibration proof.
14. **Feature challenger discipline:** new feature families cannot silently enter the baseline; they must be preregistered challengers, counted as selection trials, and prove incremental edge/ranking utility through the full proof chain before replacement.
15. **Post-production health:** active production strategy continues causal 20-day warning and 60-day confirmation monitoring. Deterministic degradation thresholds may move state to `WATCH`, `SUSPEND_NEW_RECOMMENDATIONS_REVIEW_REQUIRED`, or `REVALIDATION_REQUIRED`; production outcome never authorizes automatic retuning.

## Strategy Identity Impact

This is a **material canonical strategy revision**. Prior strategy proof cannot be inherited as current proof for the strengthened clauses. Current rule-level coverage remains/restarts `NOT_ASSESSED` under the active verification epoch.

## Affected Canonical Owners

The revision updates current authority in:

- `../../authority/strategy/WS_SCOPE_AND_SUCCESS_CRITERIA.md`;
- `../../authority/strategy/WS_PRODUCT_OBJECTIVE_AND_LAYERS.md`;
- `../../authority/strategy/WS_END_TO_END_STRATEGY_LIFECYCLE.md`;
- `../../authority/strategy/WS_MARKET_DATA_INPUT_REQUIREMENTS.md`;
- `../../authority/strategy/WS_RUNTIME_FLOW.md`;
- `../../authority/strategy/WS_CANDIDATE_ELIGIBILITY_AND_SETUP.md`;
- `../../authority/strategy/WS_CANDIDATE_CLASSIFICATION.md`;
- `../../authority/strategy/WS_PLAN_SCORING_AND_TRADE_PLAN.md`;
- `../../authority/strategy/WS_TOP_PICKS_RECOMMENDATION.md`;
- `../../authority/strategy/WS_TOP_PICKS_QUALIFICATION_AND_RANKING.md`;
- `../../authority/strategy/WS_HISTORICAL_EVALUATION_STRATEGY.md`;
- `../../authority/strategy/WS_IS_SUFFICIENCY_AND_WINNER_FREEZE.md`;
- `../../authority/strategy/WS_OOS_STRESS_SHADOW_AND_PRODUCTION_PROOF.md`.

`WS_D1_CONFIRM_ACTIONABILITY.md` remains semantically unchanged but its pre-revision authority was archived together with the full 14-owner snapshot for one coherent pre-change baseline.

## Prior Authority Preservation

The exact pre-revision 14-owner snapshot is preserved as `H0358..H0371` in `../history/archive/` and indexed by `../history/ARCHIVE_INDEX.csv`.

## Implementation Consequence

No implementation is declared conformant by this decision. The traceability matrix contains the revised rules as current `NOT_ASSESSED` work. Existing code/evidence is revalidation input only.
