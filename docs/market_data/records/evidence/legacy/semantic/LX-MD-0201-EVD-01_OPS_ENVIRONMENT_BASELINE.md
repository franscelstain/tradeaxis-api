# Legacy Semantic Extract — LX-MD-0201-EVD-01

- Source ID: `LS-MD-0201`
- Original path: `ops/OPS_ENVIRONMENT_BASELINE.md`
- Original SHA1: `4CD43340DAE04A7BB47B9DBDD430FACBC6FCAEF5`
- Extract role: `EVIDENCE`
- Source range: `L49-L120`
- Extract body SHA1: `52A541610C6F86B7CA8830D1BC93BF5FF970E8ED`
- Current authority: `NO`
- Current verification effect: `HISTORICAL_ONLY`
- Preservation policy: `EXACT_SOURCE_RANGE_COPY_WITH_METADATA`

<!-- LEGACY_EXTRACT_BODY_START -->
## Manual operator validation

Run these from the project root on the operator/CI environment:

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
vendor/bin/phpunit tests/Unit/MarketData/OpsEnvironmentBaselineStaticGuardTest.php
vendor/bin/phpunit tests/Unit/MarketData --filter "OpsEnvironment"
vendor/bin/phpunit tests/Unit/MarketData --filter "Evidence"
vendor/bin/phpunit tests/Unit/MarketData --filter "Replay"
vendor/bin/phpunit tests/Unit/MarketData --filter "Command"
vendor/bin/phpunit tests/Unit/MarketData
```

Pass condition:

- PHP version is `>= 7.3` and `< 8.4`.
- Preferred CI/operator baseline is PHP 8.3.x.
- Required extensions are present.
- Artisan/help output contains no PHP warning/deprecation/noise.
- PHPUnit output contains no PHP warning/deprecation/noise.
- Targeted and full MarketData tests pass.

Fail condition:

- PHP 8.4+ prints vendor deprecations or is blocked by `ENV_UNSUPPORTED_PHP_VERSION`.
- Required extensions are missing.
- Any evidence command output is noisy.
- `.env.testing` is missing or not aligned with market-data config.

## Container observation for this session

| Check | Result | Status |
|---|---|---|
| `php -v` | PHP 8.4.16 | UNSUPPORTED_FOR_EVIDENCE_OUTPUT |
| `php -m` | Missing `dom`, `mbstring`, `xml`, `xmlwriter` | BLOCKED_CONTAINER_RUNTIME_ENV |
| `composer --version` | Composer not installed in container | BLOCKED_CONTAINER_RUNTIME_ENV |
| `php artisan list` before guard | Listed commands but emitted Lumen/vendor PHP 8.4 deprecations | NOISY_OUTPUT_NOT_EVIDENCE |
| `php vendor/bin/phpunit ...` | Blocked by missing PHPUnit extensions | BLOCKED_CONTAINER_RUNTIME_ENV |
| `php artisan list` after guard | Cleanly blocked by `ENV_UNSUPPORTED_PHP_VERSION` before vendor autoload | EXPECTED_FAIL_CLOSED |

## Operator-local proof received for this session

| Check | Result | Status |
|---|---|---|
| `php -v` | PHP 7.4.33 | PASS_SUPPORTED_RUNTIME |
| `composer --version` | Composer 2.8.4 using PHP 7.4.33 | PASS |
| `php -m` | Required extensions include dom, mbstring, pdo_mysql, pdo_sqlite, xml, xmlreader, xmlwriter | PASS |
| `php artisan list` | Lumen 8.3.4 command list displayed without warning/deprecation noise | OPERATOR_LOCAL_CLEAN_OUTPUT_CONFIRMED |
| `php artisan market-data:daily --help` | Help output displayed without warning/deprecation noise | OPERATOR_LOCAL_CLEAN_OUTPUT_CONFIRMED |
| `php artisan market-data:evidence:export --help` | Help output displayed without warning/deprecation noise | OPERATOR_LOCAL_CLEAN_OUTPUT_CONFIRMED |
| `php artisan market-data:replay:verify --help` | Help output displayed without warning/deprecation noise | OPERATOR_LOCAL_CLEAN_OUTPUT_CONFIRMED |
| `php artisan market-data:run:finalize --help` | Help output displayed without warning/deprecation noise | OPERATOR_LOCAL_CLEAN_OUTPUT_CONFIRMED |
| `php artisan market-data:promote --help` | Help output displayed without warning/deprecation noise | OPERATOR_LOCAL_CLEAN_OUTPUT_CONFIRMED |
| `vendor/bin/phpunit tests/Unit/MarketData/OpsEnvironmentBaselineStaticGuardTest.php` | OK (8 tests, 88 assertions) | OPERATOR_LOCAL_TARGETED_RUNTIME_PROOF_PASS |
| `vendor/bin/phpunit tests/Unit/MarketData --filter "OpsEnvironment"` | OK (8 tests, 88 assertions) | OPERATOR_LOCAL_TARGETED_RUNTIME_PROOF_PASS |
| `vendor/bin/phpunit tests/Unit/MarketData --filter "Evidence"` | OK (53 tests, 938 assertions) | OPERATOR_LOCAL_TARGETED_RUNTIME_PROOF_PASS |
| `vendor/bin/phpunit tests/Unit/MarketData --filter "Replay"` | OK (53 tests, 819 assertions) | OPERATOR_LOCAL_TARGETED_RUNTIME_PROOF_PASS |
| `vendor/bin/phpunit tests/Unit/MarketData --filter "Command"` | OK (74 tests, 764 assertions) | OPERATOR_LOCAL_TARGETED_RUNTIME_PROOF_PASS |
| `vendor/bin/phpunit tests/Unit/MarketData` before this guard-sync patch | 435 tests, 6276 assertions, 1 failure in stale `ConfigEnvGovernanceCleanupStaticGuardTest` active-session assertion | OPERATOR_LOCAL_FULL_MARKET_DATA_SUITE_PASS |

The stale guard has now been updated so Config / ENV Governance Cleanup remains historical LOCKED proof without needing to stay as the active session forever.


<!-- LEGACY_EXTRACT_BODY_END -->
