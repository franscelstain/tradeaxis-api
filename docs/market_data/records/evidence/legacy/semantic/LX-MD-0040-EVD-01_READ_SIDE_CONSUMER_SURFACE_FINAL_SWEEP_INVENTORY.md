# Legacy Semantic Extract — LX-MD-0040-EVD-01

- Source ID: `LS-MD-0040`
- Original path: `audit/READ_SIDE_CONSUMER_SURFACE_FINAL_SWEEP_INVENTORY.md`
- Original SHA1: `89C4B1A3A221C2A0F9B52AA735566CF5C0F8B107`
- Extract role: `EVIDENCE`
- Source range: `L106-L171`
- Extract body SHA1: `7707656431AA2FF871D3D29FCF1C56C82C12EC08`
- Current authority: `NO`
- Current verification effect: `HISTORICAL_ONLY`
- Preservation policy: `EXACT_SOURCE_RANGE_COPY_WITH_METADATA`

<!-- LEGACY_EXTRACT_BODY_START -->
## Static proof / validation matrix

| Command | Result | Tests | Assertions | Status |
|---|---:|---:|---:|---|
| `php -v` | PHP 7.4.33 | N/A | N/A | `PASS_LOCAL_ENV` |
| `php vendor/bin/phpunit --version` | PHPUnit 9.6.34 | N/A | N/A | `PASS_LOCAL_ENV` |
| `php -m` required extension check | dom, mbstring, pdo_mysql, pdo_sqlite, xml, xmlreader, xmlwriter available | N/A | N/A | `PASS_LOCAL_ENV` |
| `php -l tests/Unit/MarketData/ReadSideConsumerSurfaceFinalSweepStaticGuardTest.php` | No syntax errors detected | N/A | N/A | `PASS_STATIC` |
| `php -l tests/Unit/MarketData/AuditDocsSynchronizationStaticGuardTest.php` | No syntax errors detected | N/A | N/A | `PASS_STATIC` |
| `php -l tests/Unit/MarketData/ProductionValidationRuntimeProofStaticGuardTest.php` | No syntax errors detected | N/A | N/A | `PASS_STATIC` |
| `php vendor/bin/phpunit --version` | Blocked: missing `dom`, `mbstring`, `xml`, `xmlwriter` | N/A | N/A | `BLOCKED_CONTAINER_RUNTIME_ENV` |
| `vendor/bin/phpunit tests/Unit/MarketData/ReadSideConsumerSurfaceFinalSweepStaticGuardTest.php` | OK | 8 | 157 | `PASS_LOCAL` |
| `vendor/bin/phpunit tests/Unit/MarketData --filter "ReadSide"` | OK | 12 | 226 | `PASS_LOCAL` |
| `vendor/bin/phpunit tests/Unit/MarketData --filter "Readable"` | OK | 57 | 426 | `PASS_LOCAL` |
| `vendor/bin/phpunit tests/Unit/MarketData --filter "Pointer"` | OK | 76 | 1117 | `PASS_LOCAL` |
| `vendor/bin/phpunit tests/Unit/MarketData --filter "Publication"` | OK | 98 | 1193 | `PASS_LOCAL` |
| `vendor/bin/phpunit tests/Unit/MarketData --filter "Consumer"` | OK | 13 | 222 | `PASS_LOCAL` |
| `vendor/bin/phpunit tests/Unit/MarketData --filter "CommandSurface"` | OK | 49 | 359 | `PASS_LOCAL` |
| `vendor/bin/phpunit tests/Unit/MarketData --filter "Replay"` | OK | 43 | 717 | `PASS_LOCAL` |
| `vendor/bin/phpunit tests/Unit/MarketData --filter "Evidence"` | OK | 45 | 812 | `PASS_LOCAL` |
| `vendor/bin/phpunit tests/Unit/MarketData --filter "StaticGuard"` | OK | 124 | 2785 | `PASS_LOCAL` |
| `vendor/bin/phpunit tests/Unit/MarketData` | OK | 391 | 5345 | `PASS_LOCAL` |

## Operator-local final validation received

Latest operator-local proof supplied for this sweep:

- Operator-local runtime environment baseline: PHP 7.4.33, PHPUnit 9.6.34, required extensions available (`dom`, `mbstring`, `pdo_mysql`, `pdo_sqlite`, `xml`, `xmlreader`, `xmlwriter`).
- Container PHPUnit baseline: blocked by missing `dom`, `mbstring`, `xml`, and `xmlwriter`; container proof is static-only and not used as runtime authority.
- `ReadSideConsumerSurfaceFinalSweepStaticGuardTest.php` -> PASS: `OK (8 tests, 157 assertions)`.
- `ReadSide` filter -> PASS: `OK (12 tests, 226 assertions)`.
- `Readable` filter -> PASS: `OK (57 tests, 426 assertions)`.
- `Pointer` filter -> PASS: `OK (76 tests, 1117 assertions)`.
- `Publication` filter -> PASS: `OK (98 tests, 1193 assertions)`.
- `Consumer` filter -> PASS: `OK (13 tests, 222 assertions)`.
- `CommandSurface` filter -> PASS: `OK (49 tests, 359 assertions)`.
- `Replay` filter -> PASS: `OK (43 tests, 717 assertions)`.
- `Evidence` filter -> PASS: `OK (45 tests, 812 assertions)`.
- `StaticGuard` filter -> PASS: `OK (124 tests, 2785 assertions)`.
- Full `tests/Unit/MarketData` -> PASS: `OK (391 tests, 5345 assertions)`.
- `Evidence` filter -> PASS after audit-phrase patch: `OK (45 tests, 812 assertions)`.
- `StaticGuard` filter -> PASS after audit-phrase patch: `OK (124 tests, 2785 assertions)`.

Historical failure classification: `STATIC_GUARD_COMPATIBILITY_GAP`, not a replay/read-side consumer bypass. The Production Validation runtime proof was already present, but the audit text did not include the exact marker `20-command command list/full help` required by the guard. The marker was patched, and Evidence/StaticGuard reruns passed locally.

## Manual validation command block

Run these locally from the project root after applying the ZIP:

```text
vendor/bin/phpunit tests/Unit/MarketData/ReadSideConsumerSurfaceFinalSweepStaticGuardTest.php
vendor/bin/phpunit tests/Unit/MarketData --filter "ReadSide"
vendor/bin/phpunit tests/Unit/MarketData --filter "Readable"
vendor/bin/phpunit tests/Unit/MarketData --filter "Pointer"
vendor/bin/phpunit tests/Unit/MarketData --filter "Publication"
vendor/bin/phpunit tests/Unit/MarketData --filter "Consumer"
vendor/bin/phpunit tests/Unit/MarketData --filter "CommandSurface"
vendor/bin/phpunit tests/Unit/MarketData --filter "Evidence"
vendor/bin/phpunit tests/Unit/MarketData --filter "Replay"
vendor/bin/phpunit tests/Unit/MarketData --filter "StaticGuard"
vendor/bin/phpunit tests/Unit/MarketData
```

Expected output: each PHPUnit command returns `OK (... tests, ... assertions)`.  
Pass/fail criteria: this sweep is `DONE` for this ZIP because targeted read-side/consumer/static-guard filters and full `tests/Unit/MarketData` passed in the operator-local environment. Container static proof remains historical/support context only.


<!-- LEGACY_EXTRACT_BODY_END -->
