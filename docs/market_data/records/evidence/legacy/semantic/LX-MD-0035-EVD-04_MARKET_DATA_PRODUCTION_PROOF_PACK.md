# Legacy Semantic Extract — LX-MD-0035-EVD-04

- Source ID: `LS-MD-0035`
- Original path: `audit/MARKET_DATA_PRODUCTION_PROOF_PACK.md`
- Original SHA1: `9D1E95DE0523A6EFF6B7E31DF54056B51CB33F26`
- Extract role: `EVIDENCE`
- Source range: `L263-L305`
- Extract body SHA1: `0A87EF878468A8F1B06C67CDFB5E43481DCC1E61`
- Current authority: `NO`
- Current verification effect: `HISTORICAL_ONLY`
- Preservation policy: `EXACT_SOURCE_RANGE_COPY_WITH_METADATA`

<!-- LEGACY_EXTRACT_BODY_START -->
## 17. 2026-05-21 Ops Runtime Parity Revalidation

Scope: `PRODUCTION_ROLLOUT_VALIDATION_RUNTIME_PARITY_PROOF`.

Rollout decision: `OPS_RUNTIME_PARITY_PASSED`.

Source lock impact: `MARKET_DATA_PRODUCTION_READY_LOCKED` remains valid. This revalidation found no P0/P1 market-data source-code blocker.  **[SUPERSEDED 2026-08-06 — W22]** Klaim ini tidak berlaku untuk baseline yang dikoreksi; lihat `reports/AUDIT_FINAL_STATE.md`.

Runtime evidence root: `storage/app/market-data/production-rollout-validation-runtime-parity/**`.

Claimed validation requiring missing artifact support:

- PHP 7.4.33 and required extensions present.
- Composer 2.8.4 and `composer validate` valid.
- Artisan boot clean on Lumen 8.3.4; historical overlay recorded 20 market-data commands before provider-smoke, and current final reconciliation records 21 market-data commands.
- Requested market-data help commands exit 0 with clean output.
- Targeted static guards PASS: AuditDocs OK (10 tests, 419 assertions), ProductionValidation OK (13 tests, 220 assertions), OperationalReadiness OK (10 tests, 204 assertions), OpsEnvironment OK (8 tests, 107 assertions), ConfigEnvGovernance OK (10 tests, 123 assertions).
- Filtered suites PASS: AuditDocs OK (10 tests, 419 assertions), StaticGuard OK (176 tests, 4139 assertions), Production OK (14 tests, 253 assertions), Operational OK (11 tests, 211 assertions), OpsEnvironment OK (8 tests, 107 assertions).
- Full `tests/Unit/MarketData` PASS: OK (475 tests, 6957 assertions), Time 00:10.716, Memory 38.00 MB.
- Manual-file runtime smoke PASS: `run_id=30`, `publication_id=24`, import-only no pointer switch, promote `SUCCESS/READABLE/PASS/SEALED`.
- Evidence export PASS for `run_id=30`: `ADMITTED_COMPLETE`, `COMPLETE`, 10 files.
- Replay verify PASS for current-readable fixture: `replay_id=19`, `MATCH`, `PASS`, `mismatch_count=0`.
- Replay verify PASS for historical non-current publication fixture: `replay_id=20`, `publication_id=2`, `publication_is_current=false`, `NOT_CURRENT_POINTER`, `HISTORICAL_PUBLICATION_AUDIT`, `MATCH`, `PASS`, `mismatch_count=0`.
- Correction lifecycle PASS for `correction_id=5`: request/approve/run/evidence export succeeded; unchanged correction preserved current publication; rerun blocked with `COMMAND_CORRECTION_STATUS_NOT_EXECUTABLE`.
- Storage/log/evidence paths writable.

Environment/deployment blockers:

- `BLOCKED_TESTING_DATABASE_ENV`: plain `php artisan migrate:fresh --env=testing` did not select `.env.testing` database `tradeaxis_testing`; it targeted `.env` database `tradeaxis`. Explicit `APP_ENV=testing DB_DATABASE=tradeaxis_testing` override was required to prove migrations and required tables in the intended testing DB.
- `OPS_DEPLOYMENT_TASK_REQUIRED`: `schedule:list` is unavailable in this Lumen build; `schedule:run` exits cleanly but current env has daily scheduling disabled. Production cron, logging/output routing, timezone, and no-silent-failure proof remain deployment tasks.
- `PROVIDER_SMOKE_SAFE_MODE_SURFACE_ADDED`: live provider/API smoke was not run because no safe dry-run/ticker-limit command surface is available and a broad provider fetch was intentionally avoided.

Final note: this historical ops runtime parity overlay is superseded by the final provider smoke PASS and scheduler due-run/non-silent-failure proof. It does not override the current `OPS_RUNTIME_PARITY_PASSED` decision.

Post-doc validation:

- `vendor/bin/phpunit tests/Unit/MarketData/AuditDocsSynchronizationStaticGuardTest.php` -> OK (10 tests, 421 assertions).
- `vendor/bin/phpunit tests/Unit/MarketData/ProductionValidationRuntimeProofStaticGuardTest.php` -> OK (13 tests, 220 assertions).
- `vendor/bin/phpunit tests/Unit/MarketData/OpsEnvironmentBaselineStaticGuardTest.php` -> OK (8 tests, 107 assertions).
- `vendor/bin/phpunit tests/Unit/MarketData --filter "StaticGuard"` -> OK (176 tests, 4141 assertions).
- `vendor/bin/phpunit tests/Unit/MarketData` -> OK (475 tests, 6959 assertions).



<!-- LEGACY_EXTRACT_BODY_END -->
