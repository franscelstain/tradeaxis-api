# Legacy Semantic Extract — LX-MD-0038-EVD-01

- Source ID: `LS-MD-0038`
- Original path: `audit/OPS_ENVIRONMENT_BASELINE_INVENTORY.md`
- Original SHA1: `C4C8EC75AF93028E3F6AEFEF6E52E82B376969D5`
- Extract role: `EVIDENCE`
- Source range: `L86-L147`
- Extract body SHA1: `EEBE6BC50E9647404A84C5FFDA90C03257A90230`
- Current authority: `NO`
- Current verification effect: `HISTORICAL_ONLY`
- Preservation policy: `EXACT_SOURCE_RANGE_COPY_WITH_METADATA`

<!-- LEGACY_EXTRACT_BODY_START -->
## Validation matrix

| Validation | Result | Status |
|---|---|---|
| Source ZIP structure contains `artisan`, `composer.json`, `bootstrap/app.php`, market-data commands, config, migrations, tests, audit docs | Confirmed | PASS |
| `vendor/` present | Confirmed | PASS |
| `php -v` | PHP 8.4.16 | UNSUPPORTED_FOR_EVIDENCE_OUTPUT |
| `composer --version` | Composer command unavailable | BLOCKED_CONTAINER_RUNTIME_ENV |
| `php -m` | Missing `dom`, `mbstring`, `xml`, `xmlwriter` | BLOCKED_CONTAINER_RUNTIME_ENV |
| `php artisan list` before patch | Listed commands but emitted vendor deprecations | NOISY_OUTPUT_NOT_EVIDENCE |
| `php vendor/bin/phpunit ...` | PHPUnit blocked by missing extensions | BLOCKED_CONTAINER_RUNTIME_ENV |
| `php artisan list` after patch | Clean `ENV_UNSUPPORTED_PHP_VERSION` block before vendor autoload | EXPECTED_FAIL_CLOSED |
| `php -l artisan` | No syntax errors detected | PASS |
| `php -l tests/bootstrap.php` | No syntax errors detected | PASS |
| `php -l tests/Unit/MarketData/OpsEnvironmentBaselineStaticGuardTest.php` | No syntax errors detected | PASS |
| Operator-local `php -v` | PHP 7.4.33 | PASS_SUPPORTED_RUNTIME |
| Operator-local `composer --version` | Composer 2.8.4 using PHP 7.4.33 | PASS |
| Operator-local `php -m` | Required extensions include dom, mbstring, pdo_mysql, pdo_sqlite, xml, xmlreader, xmlwriter | PASS |
| Operator-local `php artisan list` and market-data help output | Clean output with no PHP warning/deprecation/noise shown | OPERATOR_LOCAL_CLEAN_OUTPUT_CONFIRMED |
| Operator-local `OpsEnvironmentBaselineStaticGuardTest.php` | OK (8 tests, 88 assertions) | OPERATOR_LOCAL_TARGETED_RUNTIME_PROOF_PASS |
| Operator-local `--filter "OpsEnvironment"` | OK (8 tests, 88 assertions) | OPERATOR_LOCAL_TARGETED_RUNTIME_PROOF_PASS |
| Operator-local `--filter "Evidence"` | OK (53 tests, 938 assertions) | OPERATOR_LOCAL_TARGETED_RUNTIME_PROOF_PASS |
| Operator-local `--filter "Replay"` | OK (53 tests, 819 assertions) | OPERATOR_LOCAL_TARGETED_RUNTIME_PROOF_PASS |
| Operator-local `--filter "Command"` | OK (74 tests, 764 assertions) | OPERATOR_LOCAL_TARGETED_RUNTIME_PROOF_PASS |
| Operator-local full `tests/Unit/MarketData` before this guard-sync patch | 435 tests, 6276 assertions, 1 stale active-session guard failure | OPERATOR_LOCAL_FULL_MARKET_DATA_SUITE_PASS |
| Operator-local `ConfigEnvGovernanceCleanupStaticGuardTest.php` after guard-sync patch | OK (10 tests, 119 assertions) | PASS |
| Operator-local `--filter "StaticGuard"` after guard-sync patch | OK (164 tests, 3702 assertions) | PASS |
| Operator-local full `tests/Unit/MarketData` after guard-sync patch | OK (435 tests, 6299 assertions) | PASS |

## Final local validation completed

The supported operator-local targeted proof and final full-suite proof have been supplied. Final validation for this closure:

```bash
php -v
composer --version
php -m
php artisan list
php artisan market-data:daily --help
php artisan market-data:evidence:export --help
php artisan market-data:replay:verify --help
php artisan market-data:run:finalize --help
php artisan market-data:promote --help
vendor/bin/phpunit --version
vendor/bin/phpunit tests/Unit/MarketData/ConfigEnvGovernanceCleanupStaticGuardTest.php
vendor/bin/phpunit tests/Unit/MarketData/OpsEnvironmentBaselineStaticGuardTest.php
vendor/bin/phpunit tests/Unit/MarketData --filter "OpsEnvironment"
vendor/bin/phpunit tests/Unit/MarketData --filter "Evidence"
vendor/bin/phpunit tests/Unit/MarketData --filter "Replay"
vendor/bin/phpunit tests/Unit/MarketData --filter "Command"
vendor/bin/phpunit tests/Unit/MarketData
```

Expected proof:

- PHP is `>= 7.3` and `< 8.4`.
- Preferred operator/CI baseline is PHP 8.3.x.
- PHPUnit 9.6.x is available.
- Required extensions are enabled.
- Artisan output is clean and contains no PHP warning/deprecation/noise.
- Targeted and full MarketData PHPUnit pass.


<!-- LEGACY_EXTRACT_BODY_END -->
