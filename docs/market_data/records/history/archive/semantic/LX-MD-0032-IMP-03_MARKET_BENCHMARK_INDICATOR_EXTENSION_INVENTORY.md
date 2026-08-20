# Legacy Semantic Extract — LX-MD-0032-IMP-03

- Source ID: `LS-MD-0032`
- Original path: `audit/MARKET_BENCHMARK_INDICATOR_EXTENSION_INVENTORY.md`
- Original SHA1: `D0BE40DBBA4BFF89D35ED251D64531E8EF56FC39`
- Extract role: `IMPLEMENTATION`
- Source range: `L95-L105`
- Extract body SHA1: `FBBBCDA0D20009E4167C3B59005A4AFE75FC457E`
- Current authority: `NO`
- Current verification effect: `HISTORICAL_ONLY`
- Preservation policy: `EXACT_SOURCE_RANGE_COPY_WITH_METADATA`

<!-- LEGACY_EXTRACT_BODY_START -->
## Affected Commands, Services, Repositories
- `market-data:daily` ingest stage can import benchmark bars after equity bars when `source_mode=api`.
- `market-data:promote` computes benchmark indicators before equity indicator extension.
- `PublicApiEodBarsAdapter` now has separate equity and benchmark provider-symbol resolution.
- `BenchmarkBarsIngestService` ingests benchmark EOD bars.
- `BenchmarkIndicatorComputeService` computes benchmark indicators.
- `BenchmarkIndicatorVectorService` owns benchmark formula computation.
- `MarketBenchmarkRepository` owns benchmark master, bars, indicators, and IHSG ROC lookup.
- `IndicatorVectorService` owns equity indicator extension computation.
- `EodArtifactRepository` snapshots/restores/copies the new equity indicator columns.


<!-- LEGACY_EXTRACT_BODY_END -->
