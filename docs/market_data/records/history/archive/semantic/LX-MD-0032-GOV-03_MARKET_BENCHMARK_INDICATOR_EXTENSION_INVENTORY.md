# Legacy Semantic Extract — LX-MD-0032-GOV-03

- Source ID: `LS-MD-0032`
- Original path: `audit/MARKET_BENCHMARK_INDICATOR_EXTENSION_INVENTORY.md`
- Original SHA1: `D0BE40DBBA4BFF89D35ED251D64531E8EF56FC39`
- Extract role: `GOVERNANCE`
- Source range: `L106-L112`
- Extract body SHA1: `623AAAE46709C77F344869A9FDB9B73B32A2A8C3`
- Current authority: `NO`
- Current verification effect: `HISTORICAL_ONLY`
- Preservation policy: `EXACT_SOURCE_RANGE_COPY_WITH_METADATA`

<!-- LEGACY_EXTRACT_BODY_START -->
## Read-Side / Publication Contract Impact
- Existing current-readable publication contract remains the only consumer-safe path.
- New equity indicator fields are part of the readable indicator artifact after publication.
- Consumers must not read raw/staging/latest shortcuts or `MAX(trade_date)` to infer market-data state.
- Benchmark tables are upstream support artifacts and do not alter equity coverage-gate universe membership.
- `rs_20_vs_ihsg` is nullable when IHSG benchmark ROC is unavailable; it is never hardcoded.


<!-- LEGACY_EXTRACT_BODY_END -->
