# Legacy Semantic Extract — LX-MD-0033-CTX-01

- Source ID: `LS-MD-0033`
- Original path: `audit/MARKET_DATA_CONSUMER_READ_MODEL_INVENTORY.md`
- Original SHA1: `A63ADB11787063B5198FC2AB1A3E1DA244D95EC8`
- Extract role: `CONTEXT`
- Source range: `L136-L179`
- Extract body SHA1: `29D65461471A10CD00D37E01E11DCC79F52FF004`
- Current authority: `NO`
- Current verification effect: `HISTORICAL_ONLY`
- Preservation policy: `EXACT_SOURCE_RANGE_COPY_WITH_METADATA`

<!-- LEGACY_EXTRACT_BODY_START -->
## NO RAW / STAGING / LATEST / MAX(DATE) ENFORCEMENT

READ_SIDE_CONTRACT_STATUS: current readable publication only; no raw/staging/latest/MAX(date)

Consumer read surfaces must not use:
- `MAX(trade_date)`
- `max('trade_date')`
- `latest('trade_date')`
- `orderByDesc('trade_date')`
- `orderBy('trade_date', 'desc')`
- raw or staging table fallback
- candidate publication fallback
- unsealed publication fallback
- evidence historical publication resolver
- silent fallback to another requested date

Static guard owner:
- `MarketDataConsumerReadModelStaticGuardTest`

## TESTS ADDED / UPDATED

Added:
- `tests/Unit/MarketData/MarketDataWatchlistReadModelTest.php`
- `tests/Unit/MarketData/MarketDataPortfolioPriceReadModelTest.php`
- `tests/Unit/MarketData/MarketBenchmarkReadModelTest.php`
- `tests/Unit/MarketData/MarketDataReadinessServiceTest.php`
- `tests/Unit/MarketData/MarketDataConsumerReadModelStaticGuardTest.php`
- `tests/Support/MarketData/SeedsConsumerReadModelFixture.php`

Updated:
- `tests/Unit/MarketData/AuditDocsSynchronizationStaticGuardTest.php`

Required validation commands:
- `vendor/bin/phpunit tests/Unit/MarketData --filter "WatchlistRead"` -> OK (3 tests, 22 assertions)
- `vendor/bin/phpunit tests/Unit/MarketData --filter "PortfolioPrice"` -> OK (4 tests, 21 assertions)
- `vendor/bin/phpunit tests/Unit/MarketData --filter "BenchmarkRead"` -> OK (3 tests, 17 assertions)
- `vendor/bin/phpunit tests/Unit/MarketData --filter "Readiness"` -> OK (22 tests, 289 assertions)
- `vendor/bin/phpunit tests/Unit/MarketData --filter "ConsumerReadModel"` -> OK (5 tests, 110 assertions)
- `vendor/bin/phpunit tests/Unit/MarketData --filter "StaticGuard"` -> OK (206 tests, 5262 assertions)
- `vendor/bin/phpunit tests/Unit/MarketData` -> OK (534 tests, 8287 assertions)

Raw operator proof artifact:
- `storage/app/market_data/evidence/consumer-read-model/operator_command_proof.txt`


<!-- LEGACY_EXTRACT_BODY_END -->
