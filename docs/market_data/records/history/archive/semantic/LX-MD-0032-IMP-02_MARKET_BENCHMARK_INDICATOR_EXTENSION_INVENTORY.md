# Legacy Semantic Extract — LX-MD-0032-IMP-02

- Source ID: `LS-MD-0032`
- Original path: `audit/MARKET_BENCHMARK_INDICATOR_EXTENSION_INVENTORY.md`
- Original SHA1: `D0BE40DBBA4BFF89D35ED251D64531E8EF56FC39`
- Extract role: `IMPLEMENTATION`
- Source range: `L47-L59`
- Extract body SHA1: `908CBBA2CBFEF7033E28A7C80EFE155095C0CD55`
- Current authority: `NO`
- Current verification effect: `HISTORICAL_ONLY`
- Preservation policy: `EXACT_SOURCE_RANGE_COPY_WITH_METADATA`

<!-- LEGACY_EXTRACT_BODY_START -->
## Schema Changes
- Added `market_benchmarks`.
- Added `market_benchmark_bars`.
- Added `market_benchmark_indicators`.
- Added nullable extension columns to `eod_indicators` and `eod_indicators_history`:
  - `ma20`
  - `ma50`
  - `close_to_hh20_pct`
  - `close_vs_ma20_pct`
  - `close_vs_ma50_pct`
  - `ma20_slope_pct`
  - `rs_20_vs_ihsg`


<!-- LEGACY_EXTRACT_BODY_END -->
