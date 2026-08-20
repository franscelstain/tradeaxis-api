# Legacy Semantic Extract — LX-MD-0053-IMP-02

- Source ID: `LS-MD-0053`
- Original path: `audit/WEEKLY_SWING_PRIORITY1_INDICATOR_EXTENSION_INVENTORY.md`
- Original SHA1: `7DE98BB33121A3E580DB11E5BEE81D00CEC53353`
- Extract role: `IMPLEMENTATION`
- Source range: `L29-L53`
- Extract body SHA1: `EFC490C25295FAA98CEBBB620450308018A5302F`
- Current authority: `NO`
- Current verification effect: `HISTORICAL_ONLY`
- Preservation policy: `EXACT_SOURCE_RANGE_COPY_WITH_METADATA`

<!-- LEGACY_EXTRACT_BODY_START -->
## Schema / Storage

- Migration: `database/migrations/2026_06_02_000001_add_weekly_swing_priority1_indicators.php`.
- Sector migration: `database/migrations/2026_06_03_000001_add_sector_code_to_market_data_indicators.php`.
- Sector rotation migration: `database/migrations/2026_06_03_000002_add_sector_rotation_indicators.php`.
- Sector taxonomy/membership tables:
  - `market_data_sectors`
  - `ticker_sector_memberships`
- Equity tables updated:
  - `eod_indicators`
  - `eod_indicators_history`
- Benchmark table updated:
  - `market_benchmark_indicators`

## Runtime Owners

- Equity formula computation: `app/Application/MarketData/Services/IndicatorVectorService.php`.
- Sector membership resolution/import: `app/Infrastructure/Persistence/MarketData/SectorClassificationRepository.php` and `market-data:sectors:import-memberships`.
- Sector index bar import: `market-data:sector-indexes:import-bars` for CSV/audited input and `market-data:sector-indexes:ingest-api` for provider API input.
- Benchmark/IHSG formula computation: `app/Application/MarketData/Services/BenchmarkIndicatorVectorService.php`.
- Indicator history copy and promote copy: `app/Infrastructure/Persistence/MarketData/EodArtifactRepository.php`.
- Hash/seal indicator column list: `app/Application/MarketData/Services/MarketDataPipelineService.php`.
- Watchlist read output: `app/Infrastructure/Persistence/MarketData/MarketDataWatchlistReadRepository.php`.
- Benchmark read output: `app/Infrastructure/Persistence/MarketData/MarketBenchmarkReadRepository.php`.


<!-- LEGACY_EXTRACT_BODY_END -->
