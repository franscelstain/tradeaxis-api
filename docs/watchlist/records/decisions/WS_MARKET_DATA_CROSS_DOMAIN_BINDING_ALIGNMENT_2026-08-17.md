# Decision — Market Data ↔ Weekly Swing Cross-Domain Binding Alignment

## Decision

Adopt a single explicit cross-domain binding for the active Weekly Swing strategy.

## Canonical rules adopted

- Market Data remains the sole owner of market facts, field semantics, publication/readiness, point-in-time identity, indicators, status, sector, and event context.
- Weekly Swing consumes only the producer-facing publication-aware read product; direct Market Data table reads are not a normal Watchlist intake path.
- A current EOD Weekly Swing PLAN requires `READABLE`, `FRESH`, and `effective_trade_date == requested_trade_date`. Explicit Market Data fallback remains visible for audit/read-only use but does not become a new current-date PLAN input.
- Row-level Market Data `data_usable=true` is an upstream integrity prerequisite only; Weekly Swing eligibility is a separate downstream decision.
- Required strategy fields must be valid; missing optional fields do not fail the whole publication.
- Weekly Swing bootstrap liquidity semantics bind to `adv20_close_volume_proxy_idr`; legacy `dv20_idr` is only a compatibility alias of that proxy, never actual turnover. Moving to `adv20_traded_value_idr_actual` is a new strategy/proof identity.
- Weekly Swing uses Market Data indicator semantic identities from the producer registry; physical/legacy aliases are handled only by the adapter.
- Trading date/session completion and temporal universe/listing membership are producer-owned point-in-time facts; Watchlist does not infer weekdays, holidays, current listing state, or current board identity.
- Effective-dated IDX market-structure facts (minimum Regular-Market price, tick/fraction ladder, upper/lower auto-rejection bands) remain Market Data-owned facts; Watchlist may apply them for causal executable-price evaluation but must not hardcode current tiers into history or treat them as alpha by default.
- Benchmark/regime facts required by an active Weekly Swing identity must be exposed by the selected producer-facing read-model version. If not exposed, the dependency is unavailable; Watchlist must not reconstruct it from `market_benchmark_*` internals.
- Legacy Watchlist technical tokens `dv20_idr` and `vol_ratio` may remain physically for compatibility, but their current semantic mappings are respectively `adv20_close_volume_proxy_idr` and `vol_ratio_20`; these aliases cannot silently change basis/formula.
- Historical proof requests exact/as-known publication identity and never silently substitutes a prior date.
- D+1 CONFIRM is not guaranteed by the EOD Market Data contract; without a governed decision-time source it stays unavailable/retryable and never blocks core Weekly Swing.
- System cross-domain documentation readiness may be `PASS` after this binding is present; implementation conformance remains separately pending.

## New owners

- Strategy binding: `docs/watchlist/authority/strategy/WS_MARKET_DATA_INPUT_REQUIREMENTS.md`
- Technical intake translation: `docs/watchlist/development/implementation/MARKET_DATA_INTAKE_IMPLEMENTATION_CONTRACT.md`
- System bridge: `docs/system_audit/SYSTEM_CROSS_DOMAIN_INPUT_BASELINE.md`

## Supersession

Older Watchlist guidance that authorizes direct Market Data table/dictionary mappings as normal runtime intake, or that requires producer-internal run/pointer state beyond the consumer read contract, is superseded for current implementation. Historical tracker/evidence lines remain historical only.
