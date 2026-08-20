# Legacy Semantic Extract — LX-MD-0036-EVD-01

- Source ID: `LS-MD-0036`
- Original path: `audit/OPERATIONAL_READINESS_INVENTORY.md`
- Original SHA1: `5052578CFB667B3B0F992126B669430E12EFBA12`
- Extract role: `EVIDENCE`
- Source range: `L40-L67`
- Extract body SHA1: `C1FAC19C3B7058B68077A5E325FB00F5FEB3321C`
- Current authority: `NO`
- Current verification effect: `HISTORICAL_ONLY`
- Preservation policy: `EXACT_SOURCE_RANGE_COPY_WITH_METADATA`

<!-- LEGACY_EXTRACT_BODY_START -->
## Manual validation completed

Completed local commands:

- `vendor/bin/phpunit tests/Unit/MarketData/OperationalReadinessStaticGuardTest.php` -> OK (10 tests, 196 assertions)
- `vendor/bin/phpunit tests/Unit/MarketData --filter "OperationalReadiness"` -> OK (10 tests, 196 assertions)
- `vendor/bin/phpunit tests/Unit/MarketData --filter "CommandSurface"` -> OK (47 tests, 348 assertions)
- `vendor/bin/phpunit tests/Unit/MarketData --filter "Evidence"` -> OK (41 tests, 718 assertions)
- `vendor/bin/phpunit tests/Unit/MarketData --filter "Replay"` -> OK (38 tests, 643 assertions)
- `vendor/bin/phpunit tests/Unit/MarketData --filter "Correction"` -> OK (65 tests, 1287 assertions)
- `vendor/bin/phpunit tests/Unit/MarketData --filter "FailSafe"` -> OK (5 tests, 108 assertions)
- `vendor/bin/phpunit tests/Unit/MarketData` -> OK (368 tests, 4927 assertions)
- `php artisan list | findstr market-data` -> PASS, 19 market-data commands listed


## Final local validation evidence

- `vendor/bin/phpunit tests/Unit/MarketData/OperationalReadinessStaticGuardTest.php` -> OK (10 tests, 196 assertions)
- `vendor/bin/phpunit tests/Unit/MarketData --filter "OperationalReadiness"` -> OK (10 tests, 196 assertions)
- `vendor/bin/phpunit tests/Unit/MarketData --filter "CommandSurface"` -> OK (47 tests, 348 assertions)
- `vendor/bin/phpunit tests/Unit/MarketData --filter "Evidence"` -> OK (41 tests, 718 assertions)
- `vendor/bin/phpunit tests/Unit/MarketData --filter "Replay"` -> OK (38 tests, 643 assertions)
- `vendor/bin/phpunit tests/Unit/MarketData --filter "Correction"` -> OK (65 tests, 1287 assertions)
- `vendor/bin/phpunit tests/Unit/MarketData --filter "FailSafe"` -> OK (5 tests, 108 assertions)
- `vendor/bin/phpunit tests/Unit/MarketData` -> OK (368 tests, 4927 assertions)
- `php artisan list | findstr market-data` -> PASS, 19 market-data commands listed
- Command help spot checks -> PASS for `market-data:daily`, `market-data:promote`, `market-data:evidence:export`, `market-data:replay:verify`, `market-data:correction:request`, `market-data:correction:approve`, and `market-data:correction:run`


<!-- LEGACY_EXTRACT_BODY_END -->
