# D-WS-20260819-07 — Market Data Fact Ownership and No Local Substitution

## Role

`DECISION`

## Decision

1. Market Data is the sole semantic owner of every market fact used by Weekly Swing, including future factual features not yet enumerated by current strategy.
2. Watchlist must never reconstruct, recompute, repair, normalize, enrich, impute, infer, reinterpret, independently source, or otherwise create a substitute market fact because the authoritative producer fact is missing.
3. Watchlist may calculate only strategy-dependent decisions/outcomes whose meaning depends on the frozen Weekly Swing identity, using authoritative market facts as inputs.
4. A value that retains the same market meaning independent of Weekly Swing remains a Market Data fact even when the arithmetic is simple or its raw ingredients are already available.
5. Missing required market facts create `UPSTREAM_MARKET_DATA_DEPENDENCY_GAP`; run/ticker/proof/optional behavior must fail closed or become unavailable/insufficient according to strategy scope, never fall back to a local fact.
6. Downstream stages that discover a missing market fact route the dependency back through `WS-S01`; internal Market Data tables or external-provider reads do not resolve the dependency unless the producer-facing contract exposes the governed fact.
7. Historical replay, IS, OOS, stress, shadow, production monitoring, and optional CONFIRM follow the same ownership rule.
8. Research may declare a hypothesis that needs a new market fact, but official strategy proof cannot use a Watchlist-built substitute feature.
9. A newly available Market Data fact does not automatically become an active Weekly Swing input; adoption still requires controlled strategy identity and applicable re-proof.
10. This decision does not add Market Data work to Watchlist and does not change the EOD-only product boundary.

## Authority Impact

Twelve of fourteen strategy owners are strengthened. Top Picks recommendation/ranking owners are unchanged because they already consume upstream PLAN/recommendation semantics and do not define market-fact acquisition/derivation. The complete fourteen-owner pre-change strategy is preserved as `H0393..H0406`.

## Verification Impact

All newly introduced mandatory/conditional rules start `NOT_ASSESSED`; optional CONFIRM rules remain `OPTIONAL_NOT_REQUESTED`. Existing implementation/evidence does not inherit PASS.

## Related Records

- Finding: `../../development/findings/F-WS-20260819-07_MARKET_DATA_FACT_OWNERSHIP_BOUNDARY_GAP.md`
- Evidence: `../evidence/E-WS-20260819-07_MARKET_DATA_OWNERSHIP_BOUNDARY_VALIDATION.json`
- Change log: `../../authority/governance/DOCUMENT_CHANGE_LOG.md`
