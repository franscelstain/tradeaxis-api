# Legacy Semantic Extract — LX-MD-0040-EVD-02

- Source ID: `LS-MD-0040`
- Original path: `audit/READ_SIDE_CONSUMER_SURFACE_FINAL_SWEEP_INVENTORY.md`
- Original SHA1: `89C4B1A3A221C2A0F9B52AA735566CF5C0F8B107`
- Extract role: `EVIDENCE`
- Source range: `L246-L269`
- Extract body SHA1: `B3A59B0173D21623DEBF9F357F49FAD00AECC880`
- Current authority: `NO`
- Current verification effect: `HISTORICAL_ONLY`
- Preservation policy: `EXACT_SOURCE_RANGE_COPY_WITH_METADATA`

<!-- LEGACY_EXTRACT_BODY_START -->
## Completion Validation Matrix

| Command | Result | Status |
|---|---|---|
| `php -l` changed PHP files | No syntax errors detected | `PASS_LOCAL` |
| `vendor/bin/phpunit tests/Unit/MarketData/ReadablePublicationReadContractIntegrationTest.php` | OK (8 tests, 15 assertions) | `PASS_LOCAL` |
| `vendor/bin/phpunit tests/Unit/MarketData/ReadSideAntiBypassStaticContractTest.php` | OK (4 tests, 69 assertions) | `PASS_LOCAL` |
| `vendor/bin/phpunit tests/Unit/MarketData/ReadSideConsumerSurfaceFinalSweepStaticGuardTest.php` | OK (9 tests, 193 assertions) | `PASS_LOCAL` |
| `vendor/bin/phpunit tests/Unit/MarketData --filter "ReadSide"` | OK (13 tests, 262 assertions) | `PASS_LOCAL` |
| `vendor/bin/phpunit tests/Unit/MarketData --filter "ReadablePublication"` | OK (8 tests, 15 assertions) | `PASS_LOCAL` |
| `vendor/bin/phpunit tests/Unit/MarketData --filter "Publication"` | OK (108 tests, 1279 assertions) | `PASS_LOCAL` |
| `vendor/bin/phpunit tests/Unit/MarketData --filter "Pointer"` | OK (82 tests, 1164 assertions) | `PASS_LOCAL` |
| `vendor/bin/phpunit tests/Unit/MarketData --filter "Correction"` | OK (68 tests, 1336 assertions) | `PASS_LOCAL` |
| `vendor/bin/phpunit tests/Unit/MarketData --filter "Evidence"` | OK (54 tests, 994 assertions) | `PASS_LOCAL` |
| `vendor/bin/phpunit tests/Unit/MarketData --filter "Replay"` | OK (55 tests, 852 assertions) | `PASS_LOCAL` |
| `vendor/bin/phpunit tests/Unit/MarketData/AuditDocsSynchronizationStaticGuardTest.php` | OK (9 tests, 303 assertions) | `PASS_LOCAL` |
| `vendor/bin/phpunit tests/Unit/MarketData --filter "AuditDocs"` | OK (9 tests, 303 assertions) | `PASS_LOCAL` |
| `vendor/bin/phpunit tests/Unit/MarketData --filter "StaticGuard"` | OK (169 tests, 3866 assertions) | `PASS_LOCAL` |
| `vendor/bin/phpunit tests/Unit/MarketData` | OK (449 tests, 6522 assertions) | `PASS_LOCAL` |
| `php artisan list market-data` | PASS; 20 market-data commands listed | `PASS_LOCAL` |
| `php artisan market-data:promote --help` | PASS | `PASS_LOCAL` |
| `php artisan market-data:evidence:export --help` | PASS | `PASS_LOCAL` |
| `php artisan market-data:replay:verify --help` | PASS | `PASS_LOCAL` |


<!-- LEGACY_EXTRACT_BODY_END -->
