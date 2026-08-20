# Legacy Semantic Extract — LX-MD-0032-CTX-02

- Source ID: `LS-MD-0032`
- Original path: `audit/MARKET_BENCHMARK_INDICATOR_EXTENSION_INVENTORY.md`
- Original SHA1: `D0BE40DBBA4BFF89D35ED251D64531E8EF56FC39`
- Extract role: `CONTEXT`
- Source range: `L81-L94`
- Extract body SHA1: `28C5EE30A8C5E24B40F95A7684677AF760FBB1A1`
- Current authority: `NO`
- Current verification effect: `HISTORICAL_ONLY`
- Preservation policy: `EXACT_SOURCE_RANGE_COPY_WITH_METADATA`

<!-- LEGACY_EXTRACT_BODY_START -->
## Indicator Formulas
- Benchmark `roc_20 = ((close_today - close_20_trading_days_ago) / close_20_trading_days_ago) * 100`.
- Benchmark `ma20 = average close over 20 trading days`.
- Benchmark `ma50 = average close over 50 trading days`.
- Equity `ma20 = average basis_close over 20 trading days`.
- Equity `ma50 = average basis_close over 50 trading days`.
- Equity `close_to_hh20_pct = ((close_price - hh20) / hh20) * 100`.
- Equity `close_vs_ma20_pct = ((close_price - ma20) / ma20) * 100`.
- Equity `close_vs_ma50_pct = ((close_price - ma50) / ma50) * 100`.
- Equity `ma20_slope_pct = ((ma20_today - ma20_5_trading_days_ago) / ma20_5_trading_days_ago) * 100`.
- Equity `rs_20_vs_ihsg = (roc20_equity * 100) - IHSG.roc_20`.
- All lookbacks use trading-day order, not calendar subtraction.
- Insufficient lookback and null/zero denominators produce `NULL` outputs; fake values are forbidden.


<!-- LEGACY_EXTRACT_BODY_END -->
