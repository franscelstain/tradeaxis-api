# Legacy Semantic Extract — LX-MD-0032-GOV-04

- Source ID: `LS-MD-0032`
- Original path: `audit/MARKET_BENCHMARK_INDICATOR_EXTENSION_INVENTORY.md`
- Original SHA1: `D0BE40DBBA4BFF89D35ED251D64531E8EF56FC39`
- Extract role: `GOVERNANCE`
- Source range: `L169-L180`
- Extract body SHA1: `0C32B27B6EC5A52500DF0ACA0DAD7D613EFFBA76`
- Current authority: `NO`
- Current verification effect: `HISTORICAL_ONLY`
- Preservation policy: `EXACT_SOURCE_RANGE_COPY_WITH_METADATA`

<!-- LEGACY_EXTRACT_BODY_START -->
## Done Criteria
- IHSG is not processed as an equity ticker.
- `^JKSE` is fetched without `.JK` suffix.
- Benchmark bars are written deterministically by `(benchmark_code, trade_date)`.
- Benchmark `roc_20`, `ma20`, and `ma50` are tested.
- Equity indicator extension formulas are tested.
- `rs_20_vs_ihsg` uses IHSG benchmark ROC, not a hardcoded value.
- Insufficient lookback and null/zero denominators produce deterministic `NULL`.
- Read-side contract remains current-readable-publication only.
- Evidence export and replay verify pass after runtime validation.
- Audit docs and static guards are synchronized.


<!-- LEGACY_EXTRACT_BODY_END -->
