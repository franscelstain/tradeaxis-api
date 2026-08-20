# Legacy Semantic Extract — LX-MD-0033-GOV-03

- Source ID: `LS-MD-0033`
- Original path: `audit/MARKET_DATA_CONSUMER_READ_MODEL_INVENTORY.md`
- Original SHA1: `A63ADB11787063B5198FC2AB1A3E1DA244D95EC8`
- Extract role: `GOVERNANCE`
- Source range: `L307-L319`
- Extract body SHA1: `F6C23E0962409CEBB5D67BD19FDE03B652A5979B`
- Current authority: `NO`
- Current verification effect: `HISTORICAL_ONLY`
- Preservation policy: `EXACT_SOURCE_RANGE_COPY_WITH_METADATA`

<!-- LEGACY_EXTRACT_BODY_START -->
## DONE CRITERIA

Done criteria for this inventory:
- Watchlist read surface returns indicator rows from current readable publication.
- Watchlist read surface blocks when no readable publication exists.
- Portfolio price read surface returns official close/adjusted close from current readable publication.
- Portfolio price read surface returns missing tickers and does not fallback to another date.
- Benchmark read surface returns IHSG from benchmark tables and preserves `IND_INSUFFICIENT_HISTORY`.
- Readiness service returns ready only for `SEALED / SUCCESS / READABLE / PASS / current pointer`.
- Static guard forbids raw/staging/latest/MAX(date) bypass in consumer read model classes.
- Audit docs include `MARKET_DATA_CONSUMER_READ_MODEL_CONTRACT`.
- Full `vendor/bin/phpunit tests/Unit/MarketData` passes.


<!-- LEGACY_EXTRACT_BODY_END -->
