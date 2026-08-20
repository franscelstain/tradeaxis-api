# Legacy Semantic Extract — LX-MD-0053-GOV-01

- Source ID: `LS-MD-0053`
- Original path: `audit/WEEKLY_SWING_PRIORITY1_INDICATOR_EXTENSION_INVENTORY.md`
- Original SHA1: `7DE98BB33121A3E580DB11E5BEE81D00CEC53353`
- Extract role: `GOVERNANCE`
- Source range: `L14-L28`
- Extract body SHA1: `47C3396E7A3798477A287C7DA2B5095FD32717C2`
- Current authority: `NO`
- Current verification effect: `HISTORICAL_ONLY`
- Preservation policy: `EXACT_SOURCE_RANGE_COPY_WITH_METADATA`

<!-- LEGACY_EXTRACT_BODY_START -->
## Scope

- Add short-term equity momentum: `roc5`, `roc10`.
- Add 20-day range/support context: `ll20`, `close_to_ll20_pct`, `range_20_pct`, `range_position_20_pct`.
- Add richer IHSG regime context: `ma20_slope_pct`, `close_to_ma20_pct`, `close_to_ma50_pct`.
- Add source-backed nullable `sector_code` membership context for future watchlist grouping/filtering.
- Add nullable sector-rotation context: `sector_roc20`, `rs_20_vs_sector`, and `sector_rs_20_vs_ihsg`.
- Preserve market-data as an upstream data provider only.

## Explicit Non-Scope

- No watchlist scoring, ranking, recommendation, buy/sell decision, entry/exit rule, risk/reward calculation, or backtest logic.
- No fake sector rotation strength values without sector index history; sector-rotation fields are source-backed and nullable.
- No event-risk placeholder fields without UMA/suspend/corporate-action source data.


<!-- LEGACY_EXTRACT_BODY_END -->
