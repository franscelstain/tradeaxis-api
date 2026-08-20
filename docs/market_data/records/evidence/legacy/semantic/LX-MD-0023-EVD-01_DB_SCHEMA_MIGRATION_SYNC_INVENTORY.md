# Legacy Semantic Extract — LX-MD-0023-EVD-01

- Source ID: `LS-MD-0023`
- Original path: `audit/DB_SCHEMA_MIGRATION_SYNC_INVENTORY.md`
- Original SHA1: `2C21334498F0471DF8EE45D555AC98F3F5279BB4`
- Extract role: `EVIDENCE`
- Source range: `L57-L80`
- Extract body SHA1: `D8735076CF73EAA14786A53D866CCA8651A0296C`
- Current authority: `NO`
- Current verification effect: `HISTORICAL_ONLY`
- Preservation policy: `EXACT_SOURCE_RANGE_COPY_WITH_METADATA`

<!-- LEGACY_EXTRACT_BODY_START -->
## Validation Matrix

| Command | Result | Status |
|---|---|---|
| `php -l database/migrations/2026_05_19_000001_widen_market_data_coverage_decimal_precision.php` | No syntax errors detected | PASS |
| `php -l tests/Unit/MarketData/MarketDataSqliteSchemaSyncTest.php` | No syntax errors detected | PASS |
| `php -l tests/Unit/MarketData/AuditDocsSynchronizationStaticGuardTest.php` | No syntax errors detected | PASS |
| `php -l tests/Unit/MarketData/ConfigEnvGovernanceCleanupStaticGuardTest.php` | No syntax errors detected | PASS |
| `php -l tests/Unit/MarketData/OpsEnvironmentBaselineStaticGuardTest.php` | No syntax errors detected | PASS |
| `vendor/bin/phpunit tests/Unit/MarketData/MarketDataSqliteSchemaSyncTest.php` | OK (5 tests, 139 assertions) | PASS |
| `vendor/bin/phpunit tests/Unit/MarketData/AuditDocsSynchronizationStaticGuardTest.php` | OK (9 tests, 297 assertions) | PASS |
| `vendor/bin/phpunit tests/Unit/MarketData --filter "Schema"` | OK (15 tests, 357 assertions) | PASS |
| `vendor/bin/phpunit tests/Unit/MarketData --filter "DbIntegrity"` | OK (11 tests, 892 assertions) | PASS |
| `vendor/bin/phpunit tests/Unit/MarketData --filter "Publication"` | OK (106 tests, 1269 assertions) | PASS |
| `vendor/bin/phpunit tests/Unit/MarketData --filter "Pointer"` | OK (82 tests, 1164 assertions) | PASS |
| `vendor/bin/phpunit tests/Unit/MarketData --filter "Correction"` | OK (68 tests, 1336 assertions) | PASS |
| `vendor/bin/phpunit tests/Unit/MarketData --filter "Replay"` | OK (55 tests, 850 assertions) | PASS |
| `vendor/bin/phpunit tests/Unit/MarketData --filter "Evidence"` | OK (54 tests, 989 assertions) | PASS |
| `vendor/bin/phpunit tests/Unit/MarketData --filter "AuditDocs"` | OK (9 tests, 297 assertions) | PASS |
| `vendor/bin/phpunit tests/Unit/MarketData --filter "StaticGuard"` | OK (169 tests, 3842 assertions) | PASS |
| `vendor/bin/phpunit tests/Unit/MarketData` | OK (447 tests, 6488 assertions) | PASS |
| `php artisan migrate:fresh --env=testing` | PASS; migration `2026_05_19_000001_widen_market_data_coverage_decimal_precision` applied | PASS |
| Runtime `information_schema.COLUMNS` precision smoke | Six coverage ratio/threshold columns report precision 12 and scale 6 | PASS |


<!-- LEGACY_EXTRACT_BODY_END -->
