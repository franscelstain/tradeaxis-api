# Legacy Semantic Extract — LX-MD-0032-GOV-02

- Source ID: `LS-MD-0032`
- Original path: `audit/MARKET_BENCHMARK_INDICATOR_EXTENSION_INVENTORY.md`
- Original SHA1: `D0BE40DBBA4BFF89D35ED251D64531E8EF56FC39`
- Extract role: `GOVERNANCE`
- Source range: `L60-L80`
- Extract body SHA1: `7A86C5C89D6DFAD3B87364C59FC3C45E42D91BE3`
- Current authority: `NO`
- Current verification effect: `HISTORICAL_ONLY`
- Preservation policy: `EXACT_SOURCE_RANGE_COPY_WITH_METADATA`

<!-- LEGACY_EXTRACT_BODY_START -->
## Benchmark Contract
- `tickers` remains the equity universe.
- `market_benchmarks` owns benchmark/index instruments.
- Required seed:
  - `benchmark_code=IHSG`
  - `benchmark_name=Jakarta Composite Index`
  - `provider=yahoo_finance`
  - `provider_symbol=^JKSE`
  - `instrument_type=INDEX`
  - `is_active=1`
- Benchmark bars are uniquely keyed by `(benchmark_code, trade_date)`.
- Benchmark indicators are uniquely keyed by `(benchmark_code, trade_date, indicator_set_version)`.

## Provider Symbol Contract
- Equity provider symbols use equity resolution: `BBCA -> BBCA.JK`.
- Benchmark/index provider symbols use master-data provider symbol as-is: `IHSG -> ^JKSE`.
- Forbidden:
  - `IHSG.JK`
  - `^JKSE.JK`
  - inserting IHSG into equity ticker universe as a normal ticker.


<!-- LEGACY_EXTRACT_BODY_END -->
