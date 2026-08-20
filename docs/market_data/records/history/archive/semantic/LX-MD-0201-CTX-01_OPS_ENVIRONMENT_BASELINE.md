# Legacy Semantic Extract — LX-MD-0201-CTX-01

- Source ID: `LS-MD-0201`
- Original path: `ops/OPS_ENVIRONMENT_BASELINE.md`
- Original SHA1: `4CD43340DAE04A7BB47B9DBDD430FACBC6FCAEF5`
- Extract role: `CONTEXT`
- Source range: `L22-L48`
- Extract body SHA1: `7EE28960AB7DA9C6BDA421E063A451CBD5BED69B`
- Current authority: `NO`
- Current verification effect: `HISTORICAL_ONLY`
- Preservation policy: `EXACT_SOURCE_RANGE_COPY_WITH_METADATA`

<!-- LEGACY_EXTRACT_BODY_START -->
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


<!-- LEGACY_EXTRACT_BODY_END -->
