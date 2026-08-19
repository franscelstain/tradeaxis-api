# Finding — Market Data ↔ Weekly Swing Cross-Domain Binding Gaps

## Finding

Current documentation had the correct producer/consumer direction but still allowed several incompatible interpretations at the `market_data -> watchlist` boundary.

## Material gaps found

1. Watchlist governance still required producer-internal facts such as `SUCCESS` run, current-pointer mirror, and direct table/dictionary mappings even though Market Data exposes a publication-aware consumer read contract.
2. Current-plan behavior for `FRESH` versus explicit stale/prior-effective-date fallback was not locked.
3. Market Data `data_usable` / compatibility `eligible` could still be confused with Weekly Swing strategy eligibility.
4. Liquidity semantics were ambiguous between actual traded value, close×volume proxy, and legacy `dv20_idr`.
5. Active indicator requirements were described generically, leaving field identity/null handling open to implementation guesses.
6. Historical replay and current production intake did not have a single explicit fallback rule.
7. D+1 CONFIRM could be misread as if the EOD Market Data contract guaranteed a decision-time/intraday source.
8. Active implementation/audit prompts still instructed Watchlist work to inspect Market Data physical tables as the normal source contract.
9. Market Data strategy docs had a field-name mismatch between registry `vol_ratio_20` and older `vol_ratio` wording.
10. System audit documents disagreed: the readiness tracker marked the cross-domain gap `PASS/CLOSED` while the bridge baseline still said `PARTIAL`.
11. Trading calendar/session completion and temporal universe/listing membership were assumed by Watchlist but were not mapped explicitly through the consumer boundary.
12. Historical executable-price logic depended on IDX tick/fraction/price-band facts without an explicit cross-domain owner/application split; this risked current-tier hardcoding into historical proof.
13. Benchmark/regime-dependent Weekly Swing identities could still tempt direct `market_benchmark_*` reads when the selected consumer read-model version did not expose the exact required benchmark facts.
14. Legacy Watchlist technical contracts still used `dv20_idr` and `vol_ratio` tokens without a consistent current semantic override, allowing old physical names to be misread as current producer meanings.

## Impact

Without correction, two implementations could both appear compliant while using different data meaning, freshness policy, liquidity basis, or producer read paths. That is unacceptable for point-in-time Weekly Swing proof.

## Required resolution

Adopt one explicit Weekly Swing Market Data input-requirements owner, one technical intake contract, one current/stale decision table, one no-direct-producer-internals rule, and an explicit owner/application split for calendar, universe, benchmark, and exchange market-structure facts. Preserve Market Data ownership of facts and Watchlist ownership of strategy thresholds/behavior. Legacy technical tokens must carry an explicit compatibility mapping until physical migration is complete.
