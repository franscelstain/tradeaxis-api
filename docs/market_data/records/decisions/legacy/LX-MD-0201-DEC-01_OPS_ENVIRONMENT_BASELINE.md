# Legacy Semantic Extract — LX-MD-0201-DEC-01

- Source ID: `LS-MD-0201`
- Original path: `ops/OPS_ENVIRONMENT_BASELINE.md`
- Original SHA1: `4CD43340DAE04A7BB47B9DBDD430FACBC6FCAEF5`
- Extract role: `DECISION`
- Source range: `L7-L21`
- Extract body SHA1: `F84C26793733017D9F6AEA93CDC52A482E1F4E51`
- Current authority: `NO`
- Current verification effect: `HISTORICAL_ONLY`
- Preservation policy: `EXACT_SOURCE_RANGE_COPY_WITH_METADATA`

<!-- LEGACY_EXTRACT_BODY_START -->
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


<!-- LEGACY_EXTRACT_BODY_END -->
