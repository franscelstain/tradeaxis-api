# Legacy Semantic Extract — LX-MD-0032-CTX-04

- Source ID: `LS-MD-0032`
- Original path: `audit/MARKET_BENCHMARK_INDICATOR_EXTENSION_INVENTORY.md`
- Original SHA1: `D0BE40DBBA4BFF89D35ED251D64531E8EF56FC39`
- Extract role: `CONTEXT`
- Source range: `L132-L150`
- Extract body SHA1: `F41175D9B151DC3A9F0CC8BBEBB11D179053F10D`
- Current authority: `NO`
- Current verification effect: `HISTORICAL_ONLY`
- Preservation policy: `EXACT_SOURCE_RANGE_COPY_WITH_METADATA`

<!-- LEGACY_EXTRACT_BODY_START -->
## Manual Database Checks
```sql
SELECT *
FROM market_benchmarks
WHERE benchmark_code = 'IHSG';

SELECT *
FROM market_benchmark_bars
WHERE benchmark_code = 'IHSG'
ORDER BY trade_date DESC
LIMIT 10;

SELECT *
FROM market_benchmark_indicators
WHERE benchmark_code = 'IHSG'
ORDER BY trade_date DESC
LIMIT 10;
```


<!-- LEGACY_EXTRACT_BODY_END -->
