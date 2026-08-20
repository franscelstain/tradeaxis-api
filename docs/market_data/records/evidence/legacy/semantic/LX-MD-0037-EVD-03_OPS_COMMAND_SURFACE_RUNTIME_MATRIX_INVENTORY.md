# Legacy Semantic Extract — LX-MD-0037-EVD-03

- Source ID: `LS-MD-0037`
- Original path: `audit/OPS_COMMAND_SURFACE_RUNTIME_MATRIX_INVENTORY.md`
- Original SHA1: `D6E40A3FC4141C4D0798627BD21A5F34418206F8`
- Extract role: `EVIDENCE`
- Source range: `L254-L276`
- Extract body SHA1: `5A94964E38DDC45514BE62E721540CC9403152F7`
- Current authority: `NO`
- Current verification effect: `HISTORICAL_ONLY`
- Preservation policy: `EXACT_SOURCE_RANGE_COPY_WITH_METADATA`

<!-- LEGACY_EXTRACT_BODY_START -->
## Validation Matrix

| Validation | Result |
|---|---|
| `php -l` changed command/test PHP files | PASS |
| Final registry/help/provider-smoke/full-range-current/sector-import/event-import/missing-ticker loop | PASS for current surface: `php artisan list market-data` exit 0; 30 public market-data commands registered; backfill lifecycle help exits 0; missing-ticker lifecycle help exits 0; provider-smoke help exits 0; full-range current evidence/replay help exits 0; sector membership import help exits 0; sector index CSV bar import help exits 0; sector index API import help exits 0; corporate action import help exits 0; trading status import help exits 0; current indicator recompute help/runtime/full-range replay all pass; final provider smoke artifact records `provider_smoke_status=PASS`, `reason_code=PROVIDER_SMOKE_OK`, `http_status=200`, and all non-destructive safety flags false. Historical 2026-05-20 fixture loop remains 20-command proof before lifecycle backfill, provider-smoke, full-range current proof orchestration, sector imports, event imports, and missing-ticker lifecycle were included in the current command surface count. |
| Final invalid-input loop | PASS: daily/promote/backfill/evidence/replay/correction/snapshot/repair/purge/force-guard invalid cases exit 1 with `status=BLOCKED` and reason codes |
| `vendor/bin/phpunit tests/Unit/MarketData/OpsCommandSurfaceTest.php` | PASS: OK (57 tests, 341 assertions) |
| `vendor/bin/phpunit tests/Unit/MarketData/CorrectionCommandsTest.php` | PASS: OK (11 tests, 60 assertions) |
| `vendor/bin/phpunit tests/Unit/MarketData/CommandSurfaceSafetyStaticGuardTest.php` | PASS: OK (5 tests, 89 assertions) |
| `vendor/bin/phpunit tests/Unit/MarketData/OperationalReadinessStaticGuardTest.php` | PASS: OK (10 tests, 204 assertions) |
| `vendor/bin/phpunit tests/Unit/MarketData/OpsEnvironmentBaselineStaticGuardTest.php` | PASS: OK (8 tests, 107 assertions) |
| `vendor/bin/phpunit tests/Unit/MarketData/ProductionValidationRuntimeProofStaticGuardTest.php` | PASS: OK (13 tests, 220 assertions) |
| `vendor/bin/phpunit tests/Unit/MarketData/OpsCommandSurfaceRuntimeMatrixStaticGuardTest.php` | PASS: OK (6 tests, 114 assertions) |
| `vendor/bin/phpunit tests/Unit/MarketData --filter "Command"` | PASS: OK (97 tests, 1009 assertions) |
| `vendor/bin/phpunit tests/Unit/MarketData --filter "Ops"` | PASS: OK (74 tests, 616 assertions) |
| `vendor/bin/phpunit tests/Unit/MarketData --filter "Operational"` | PASS: OK (11 tests, 211 assertions) |
| `vendor/bin/phpunit tests/Unit/MarketData --filter "RuntimeProof"` | PASS: OK (13 tests, 220 assertions) |
| `vendor/bin/phpunit tests/Unit/MarketData/AuditDocsSynchronizationStaticGuardTest.php` | PASS: OK (10 tests, 404 assertions) |
| `vendor/bin/phpunit tests/Unit/MarketData --filter "AuditDocs"` | PASS: OK (10 tests, 404 assertions) |
| `vendor/bin/phpunit tests/Unit/MarketData --filter "StaticGuard"` | PASS: OK (176 tests, 4124 assertions) |
| `vendor/bin/phpunit tests/Unit/MarketData` | PASS: OK (475 tests, 6942 assertions) |


<!-- LEGACY_EXTRACT_BODY_END -->
