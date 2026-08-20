# Legacy Semantic Extract — LX-MD-0032-FND-01

- Source ID: `LS-MD-0032`
- Original path: `audit/MARKET_BENCHMARK_INDICATOR_EXTENSION_INVENTORY.md`
- Original SHA1: `D0BE40DBBA4BFF89D35ED251D64531E8EF56FC39`
- Extract role: `FINDING`
- Source range: `L164-L168`
- Extract body SHA1: `4F80F50768CA7BB53B085F4DF3A0AE63DA40F5C7`
- Current authority: `NO`
- Current verification effect: `HISTORICAL_ONLY`
- Preservation policy: `EXACT_SOURCE_RANGE_COPY_WITH_METADATA`

<!-- LEGACY_EXTRACT_BODY_START -->
## Remaining Risks
- Yahoo/PublicApi availability can still affect future live runs, but current source-state runtime validation passed.
- A single-date benchmark import cannot compute benchmark `roc_20`, `ma20`, `ma50`, or non-null `rs_20_vs_ihsg` until sufficient historical IHSG benchmark bars exist. The current `IND_INSUFFICIENT_HISTORY` state is expected and non-blocking.
- Runtime status is no longer pending for this source state: daily/promote/evidence/replay commands passed for `2026-05-19`, `run_id=3`, `publication_id=2`.


<!-- LEGACY_EXTRACT_BODY_END -->
