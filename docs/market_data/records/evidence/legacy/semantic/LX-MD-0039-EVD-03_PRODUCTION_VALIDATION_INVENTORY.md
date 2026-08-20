# Legacy Semantic Extract — LX-MD-0039-EVD-03

- Source ID: `LS-MD-0039`
- Original path: `audit/PRODUCTION_VALIDATION_INVENTORY.md`
- Original SHA1: `A8D8B95268BF27AB2D2EE5D169FEE87AFCAF4EB7`
- Extract role: `EVIDENCE`
- Source range: `L269-L304`
- Extract body SHA1: `F2D8C362E42C7D377BF040C6BBFDFD4379A6239E`
- Current authority: `NO`
- Current verification effect: `HISTORICAL_ONLY`
- Preservation policy: `EXACT_SOURCE_RANGE_COPY_WITH_METADATA`

<!-- LEGACY_EXTRACT_BODY_START -->
## Manual validation command block

Run locally from the project root after extracting this ZIP:

```text
vendor/bin/phpunit tests/Unit/MarketData/ProductionValidationRuntimeProofStaticGuardTest.php
vendor/bin/phpunit tests/Unit/MarketData --filter "ProductionValidation"
vendor/bin/phpunit tests/Unit/MarketData --filter "OperationalReadiness"
vendor/bin/phpunit tests/Unit/MarketData --filter "CommandSurface"
vendor/bin/phpunit tests/Unit/MarketData --filter "Evidence"
vendor/bin/phpunit tests/Unit/MarketData --filter "Replay"
vendor/bin/phpunit tests/Unit/MarketData --filter "Correction"
vendor/bin/phpunit tests/Unit/MarketData --filter "FailSafe"
vendor/bin/phpunit tests/Unit/MarketData
php artisan list | findstr market-data
php artisan market-data:daily --help
php artisan market-data:promote --help
php artisan market-data:evidence:export --help
php artisan market-data:replay:verify --help
php artisan market-data:correction:request --help
php artisan market-data:correction:approve --help
php artisan market-data:correction:run --help
```

Expected output:

- PHPUnit commands return `OK (... tests, ... assertions)`.
- Artisan list shows registered market-data commands.
- Help commands show usage/options and no fatal error.
- Evidence/replay/flow commands create artifacts when run with valid local fixture IDs/paths.

Pass/fail criteria:

- PASS when all required targeted PHPUnit, full suite, command list/help, evidence output, replay verification, and flow/failure validation are either proven by actual output or explicitly marked pending where fixture/data is unavailable.
- FAIL when any DONE/LOCKED claim exists without runtime proof, any command/test fails, evidence/replay output is claimed without artifact path, command help/list drifts from docs, or pending runtime gaps are hidden.


<!-- LEGACY_EXTRACT_BODY_END -->
