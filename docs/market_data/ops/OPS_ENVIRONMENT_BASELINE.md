# Ops Environment Baseline

Status: DONE / LOCKED_LOCAL_RUNTIME_PROOF / CONTAINER_RUNTIME_BLOCKED_BY_UNSUPPORTED_PHP / OPERATOR_LOCAL_TARGETED_RUNTIME_PROOF_PASS / OPERATOR_LOCAL_FULL_MARKET_DATA_SUITE_PASS

This document locks the operator and CI baseline required before any `market-data:*` command output is used as runtime evidence.

## Baseline decision

| Item | Decision | Evidence / Reason | Validation command | Failure meaning |
|---|---|---|---|---|
| Preferred operator/CI PHP version | PHP 8.3.x | Lumen 8.3.4 and PHPUnit 9.6.34 run without PHP 8.4 implicit-nullable vendor deprecation noise. | `php -v` | Wrong runtime for clean evidence if PHP is `>= 8.4`. |
| Supported clean-output PHP range for current dependency set | `>= 7.3` and `< 8.4` | `composer.json` allows `^7.3|^8.0`; container proof shows PHP 8.4.16 emits vendor deprecations before patch. | `php -v` | PHP below 7.3 or PHP 8.4+ must not run evidence commands. |
| Lumen version observed in ZIP | Lumen 8.3.4 | `composer.lock` locks `laravel/lumen-framework` to `v8.3.4`. | `php artisan --version` on supported PHP | Framework mismatch or unsupported vendor state. |
| PHPUnit version expected | PHPUnit 9.6.x | Prior operator-local proof used PHPUnit 9.6.34; `composer.json` requires `^9.5.10`. | `vendor/bin/phpunit --version` | Test proof cannot be used without version context. |
| Required PHP extensions for PHPUnit/runtime proof | `dom`, `json`, `libxml`, `mbstring`, `pdo_mysql`, `pdo_sqlite`, `tokenizer`, `xml`, `xmlreader`, `xmlwriter` | Container PHP lacks `dom`, `mbstring`, `xml`, and `xmlwriter`; PHPUnit is blocked there. | `php -m` | Runtime/test proof is blocked until extensions are enabled. |
| Timezone | `Asia/Jakarta` | `.env.example`, `.env.testing`, `config/app.php`, and `config/market_data.php` use this timezone. | `php artisan tinker` or config dump on supported PHP | Evidence timestamp/cutoff semantics may drift. |
| `.env.testing` | Required and present in source ZIP | Testing DB/env must be explicit before migration/PHPUnit proof. | `test -f .env.testing` or PowerShell equivalent | Do not claim test runtime proof. |
| Composer platform config | `DEFER_WITH_REASON` | `composer.json` / `composer.lock` are kept in sync in this patch; no `config.platform.php` is added without Composer lock regeneration. Runtime blocking is done before Lumen vendor autoload. | `composer validate` on operator machine | Future Composer change must update lock intentionally. |
| Artisan evidence command output | Must be clean or blocked with explicit environment reason before vendor autoload | PHP 8.4.16 previously emitted vendor deprecation during `php artisan list`; `artisan` now blocks unsupported PHP before `vendor/autoload.php`. | `php artisan list` | Any PHP warning/deprecation/noise means output is not valid evidence. |
| PHPUnit proof output | Must be clean or blocked with explicit environment reason | `phpunit.xml` bootstraps `tests/bootstrap.php`; unsupported PHP is rejected before project autoload. | `vendor/bin/phpunit tests/Unit/MarketData/OpsEnvironmentBaselineStaticGuardTest.php` | Any PHP warning/deprecation/noise means proof is invalid. |

## Clean-output policy

Market-data command output is runtime evidence. The following must not appear in command output used for audit, evidence export, replay verification, production validation, or operator runbooks:

- `PHP Warning`
- `PHP Deprecated`
- `Deprecated:`
- `PHP Notice`
- vendor/framework deprecation text
- stack trace caused by environment mismatch
- Composer platform mismatch warning
- missing extension warning
- timezone warning
- xdebug/debug noise

If the environment is wrong, the command must fail closed with a clear reason such as `ENV_UNSUPPORTED_PHP_VERSION`. The fix is to run the supported operator/CI baseline, not to suppress warnings or redirect stderr away from evidence.

## Runtime guard

`artisan` performs the PHP-version check before `vendor/autoload.php`:

- PHP `< 7.3` is blocked.
- PHP `>= 8.4` is blocked.
- The expected operator/CI baseline is PHP 8.3.x unless the project explicitly locks another supported version in a future environment session.

`phpunit.xml` now uses `tests/bootstrap.php` so PHPUnit proof gets the same unsupported-version guard before project autoload.

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

## Final rule

No future market-data session may use command output as evidence when the output contains PHP warnings, PHP deprecations, vendor/framework deprecations, missing-extension warnings, timezone warnings, debug noise, or stack traces caused by environment mismatch.

A clean unsupported-environment block is acceptable as `BLOCKED_CONTAINER_RUNTIME_ENV`; it is not a runtime PASS and must not be used to mark a market-data implementation DONE/LOCKED. Operator-local targeted proof and final full `tests/Unit/MarketData` proof have been supplied after the Config / ENV guard synchronization patch.


## Final closure proof

| Check | Result | Status |
|---|---|---|
| `vendor/bin/phpunit tests/Unit/MarketData/ConfigEnvGovernanceCleanupStaticGuardTest.php` | OK (10 tests, 119 assertions) | PASS |
| `vendor/bin/phpunit tests/Unit/MarketData --filter "StaticGuard"` | OK (164 tests, 3702 assertions) | PASS |
| `vendor/bin/phpunit tests/Unit/MarketData` | OK (435 tests, 6299 assertions) | PASS |

Ops Environment Baseline is DONE/LOCKED for this source-of-truth ZIP. Reopen only if PHP/runtime/CI/output-noise behavior changes.
