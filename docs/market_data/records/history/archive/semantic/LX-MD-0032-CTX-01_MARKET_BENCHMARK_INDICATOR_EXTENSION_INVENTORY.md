# Legacy Semantic Extract — LX-MD-0032-CTX-01

- Source ID: `LS-MD-0032`
- Original path: `audit/MARKET_BENCHMARK_INDICATOR_EXTENSION_INVENTORY.md`
- Original SHA1: `D0BE40DBBA4BFF89D35ED251D64531E8EF56FC39`
- Extract role: `CONTEXT`
- Source range: `L6-L33`
- Extract body SHA1: `48BFAF40628D06CA6BC6B289C1AF348D94422147`
- Current authority: `NO`
- Current verification effect: `HISTORICAL_ONLY`
- Preservation policy: `EXACT_SOURCE_RANGE_COPY_WITH_METADATA`

<!-- LEGACY_EXTRACT_BODY_START -->
## 2026-06-02 Addendum - Weekly Swing Priority 1 Extension

Status: `ENFORCED_FULL_MARKETDATA_PHPUNIT_PASS_RUNTIME_PENDING`, not production-ready relocked.

Additional nullable equity indicator fields:
- `roc5`
- `roc10`
- `ll20`
- `close_to_ll20_pct`
- `range_20_pct`
- `range_position_20_pct`

Additional nullable IHSG benchmark indicator fields:
- `ma20_slope_pct`
- `close_to_ma20_pct`
- `close_to_ma50_pct`

Targeted validation:
- `IndicatorVectorServiceTest` -> OK (10 tests, 76 assertions).
- `BenchmarkIndicatorVectorServiceTest` -> OK (3 tests, 21 assertions).
- `MarketBenchmarkReadModel` -> OK (3 tests, 23 assertions).
- `MarketDataWatchlistReadModel` -> OK (3 tests, 28 assertions).
- `MarketDataSqliteSchemaSync` -> OK (5 tests, 214 assertions).
- Full `tests/Unit/MarketData` -> OK (586 tests, 8771 assertions).

Remaining proof required before LOCKED/full production-ready relock:
- Daily/promote/evidence/replay runtime proof after the new columns are in place.


<!-- LEGACY_EXTRACT_BODY_END -->
