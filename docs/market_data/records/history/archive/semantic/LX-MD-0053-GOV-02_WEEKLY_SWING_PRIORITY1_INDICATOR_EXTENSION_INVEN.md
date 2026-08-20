# Legacy Semantic Extract — LX-MD-0053-GOV-02

- Source ID: `LS-MD-0053`
- Original path: `audit/WEEKLY_SWING_PRIORITY1_INDICATOR_EXTENSION_INVENTORY.md`
- Original SHA1: `7DE98BB33121A3E580DB11E5BEE81D00CEC53353`
- Extract role: `GOVERNANCE`
- Source range: `L54-L78`
- Extract body SHA1: `12E6B839C759DF4AFF7CC7BB014139D539AB15E2`
- Current authority: `NO`
- Current verification effect: `HISTORICAL_ONLY`
- Preservation policy: `EXACT_SOURCE_RANGE_COPY_WITH_METADATA`

<!-- LEGACY_EXTRACT_BODY_START -->
## Formula Definitions

- `roc5 = (P(D) / P(D[-5])) - 1`.
- `roc10 = (P(D) / P(D[-10])) - 1`.
- `ll20 = min(low)` over `D[-19] ... D`.
- `close_to_ll20_pct = ((P(D) - ll20) / ll20) * 100`.
- `range_20_pct = ((hh20 - ll20) / ll20) * 100`.
- `range_position_20_pct = ((P(D) - ll20) / (hh20 - ll20)) * 100`.
- IHSG `ma20_slope_pct = ((ma20_today - ma20_5_trading_days_ago) / ma20_5_trading_days_ago) * 100`.
- IHSG `close_to_ma20_pct = ((close - ma20) / ma20) * 100`.
- IHSG `close_to_ma50_pct = ((close - ma50) / ma50) * 100`.
- `sector_code` is resolved from the effective ticker-sector membership on D and remains NULL when no source-backed membership exists.
- `sector_roc20` is resolved from `market_benchmark_indicators.roc_20` for the active sector index on D.
- `rs_20_vs_sector = (roc20 * 100) - sector_roc20`.
- `sector_rs_20_vs_ihsg = sector_roc20 - IHSG_roc_20`.

## Fail-Safe Behavior

- Insufficient required history returns NULL indicator values and reason-coded invalid rows according to the existing indicator validity contract.
- Non-positive denominator returns NULL.
- Flat 20-day range (`hh20 - ll20 <= 0`) returns `range_position_20_pct = NULL`.
- Missing sector membership returns `sector_code = NULL` and does not invalidate the indicator row.
- Missing sector index history returns `sector_roc20`, `rs_20_vs_sector`, and `sector_rs_20_vs_ihsg` as NULL and does not invalidate the indicator row.
- No zero-fill, forward-fill, calendar interpolation, or fake benchmark values are allowed.


<!-- LEGACY_EXTRACT_BODY_END -->
