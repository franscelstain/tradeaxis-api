# F-WS-20260819-05 — High-Trust Top Picks Strategy Gaps

> **Role:** FINDING  
> **Status:** RESOLVED_BY_STRATEGY_REVISION  
> **Date:** 2026-08-19  
> **Resolution decision:** `../../records/decisions/D-WS-20260819-05_HIGH_TRUST_TOP_PICKS_STRATEGY_STRENGTHENING.md`

## Finding

The 14 canonical Weekly Swing strategy owners already defined qualified Top Picks, causal next-day entry, realistic friction, untouched OOS, ranking proof, and forward shadow, but the strategy still had material proof gaps that could allow an apparently profitable result to be less trustworthy than real use requires.

The gaps were:

1. a trade with valid entry could disappear from the return distribution when later exit became non-executable;
2. `open(D+1)` was not bound to a concrete recommendation-availability/decision-lead-time contract;
3. IS outcomes could overlap the protected OOS boundary through entry/holding/exit dependency;
4. OOS did not have an explicit consumed/burned rule after its outcome was observed;
5. parameter/feature search did not explicitly penalize multiple testing or selection bias;
6. positive mean/median alone did not establish statistical confidence or economically meaningful edge;
7. benchmark-relative edge and selection uplift were not mandatory production-proof dimensions;
8. proof did not fully match likely user focus on Top-1/Top-3/Top-5;
9. tail/path risk was under-specified beyond p25/min/monthly floors;
10. standardized execution capacity/reference-notional proof was not explicit;
11. `WATCH_ONLY` versus `AVOID` severity semantics could still be interpreted too broadly;
12. canonical score could be misread as probability/confidence by downstream consumers;
13. production approval lacked deterministic ongoing health/suspension/revalidation behavior;
14. feature-family expansion was not explicitly required to compete as a preregistered challenger against the canonical baseline.

## Impact

**CRITICAL for production trust.** None of these gaps proves that historical Top Picks were bad, but together they could produce optimistic evaluation, overfit winner selection, unproven execution assumptions, weak ranking claims, or continued use after edge deterioration.

## Required Resolution

A controlled strategy revision must make these items current authority before implementation verification begins. Historical PASS/FAIL remains historical-only and cannot satisfy the revised rules.

## Resolution

Resolved by `D-WS-20260819-05`. Current traceability rows for the strengthened strategy remain `NOT_ASSESSED`; this finding resolves the **strategy-definition gap**, not implementation/proof completion.
