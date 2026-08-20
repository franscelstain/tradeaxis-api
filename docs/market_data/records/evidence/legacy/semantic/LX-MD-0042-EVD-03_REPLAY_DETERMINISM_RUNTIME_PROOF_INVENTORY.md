# Legacy Semantic Extract — LX-MD-0042-EVD-03

- Source ID: `LS-MD-0042`
- Original path: `audit/REPLAY_DETERMINISM_RUNTIME_PROOF_INVENTORY.md`
- Original SHA1: `7E5FB7DE9A03E174497EC8911DE7215EE2F3EEEC`
- Extract role: `EVIDENCE`
- Source range: `L70-L86`
- Extract body SHA1: `8461D17644D431DA861D2CDB2B0A92D110295D16`
- Current authority: `NO`
- Current verification effect: `HISTORICAL_ONLY`
- Preservation policy: `EXACT_SOURCE_RANGE_COPY_WITH_METADATA`

<!-- LEGACY_EXTRACT_BODY_START -->
## Validation

- `vendor/bin/phpunit tests/Unit/MarketData/ReplayVerificationServiceTest.php` -> OK (9 tests, 30 assertions)
- `vendor/bin/phpunit tests/Unit/MarketData/ReplayResultRepositoryIntegrationTest.php` -> OK (1 test, 15 assertions)
- `vendor/bin/phpunit tests/Unit/MarketData/ReplayBackfillServiceTest.php` -> OK (2 tests, 11 assertions)
- `vendor/bin/phpunit tests/Unit/MarketData/ReplaySmokeSuiteServiceTest.php` -> OK (1 test, 10 assertions)
- `vendor/bin/phpunit tests/Unit/MarketData/ReplayEvidenceExportServiceTest.php` -> OK (1 test, 51 assertions)
- `vendor/bin/phpunit tests/Unit/MarketData/ReplayDeterminismStaticGuardTest.php` -> OK (5 tests, 163 assertions)
- `vendor/bin/phpunit tests/Unit/MarketData/ReplayHistoricalDeterminismHardeningStaticGuardTest.php` -> OK (6 tests, 70 assertions)
- `vendor/bin/phpunit tests/Unit/MarketData/OpsCommandSurfaceTest.php` -> OK (46 tests, 288 assertions)
- `vendor/bin/phpunit tests/Unit/MarketData --filter "Replay"` -> OK (55 tests, 877 assertions)
- Sequential reruns after a parallel fixture-dir collision: `--filter "Evidence"` OK (55 tests, 1050 assertions), `--filter "Publication"` OK (109 tests, 1297 assertions), `--filter "Pointer"` OK (82 tests, 1164 assertions), `--filter "Coverage"` OK (70 tests, 788 assertions), `--filter "Correction"` OK (69 tests, 1358 assertions)
- `vendor/bin/phpunit tests/Unit/MarketData/AuditDocsSynchronizationStaticGuardTest.php` -> OK (9 tests, 343 assertions)
- `vendor/bin/phpunit tests/Unit/MarketData --filter "AuditDocs"` -> OK (9 tests, 343 assertions)
- `vendor/bin/phpunit tests/Unit/MarketData --filter "StaticGuard"` -> OK (169 tests, 3926 assertions)
- `vendor/bin/phpunit tests/Unit/MarketData` -> OK (451 tests, 6642 assertions)


<!-- LEGACY_EXTRACT_BODY_END -->
