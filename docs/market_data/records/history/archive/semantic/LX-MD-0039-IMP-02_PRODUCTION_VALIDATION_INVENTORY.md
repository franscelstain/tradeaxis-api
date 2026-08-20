# Legacy Semantic Extract — LX-MD-0039-IMP-02

- Source ID: `LS-MD-0039`
- Original path: `audit/PRODUCTION_VALIDATION_INVENTORY.md`
- Original SHA1: `A8D8B95268BF27AB2D2EE5D169FEE87AFCAF4EB7`
- Extract role: `IMPLEMENTATION`
- Source range: `L499-L542`
- Extract body SHA1: `756DCC91D161BE39754D803E25D099B0FFCE6AFA`
- Current authority: `NO`
- Current verification effect: `HISTORICAL_ONLY`
- Preservation policy: `EXACT_SOURCE_RANGE_COPY_WITH_METADATA`

<!-- LEGACY_EXTRACT_BODY_START -->
## 2026-05-21 Testing DB Isolation / Safe Migration Guard

Scope: `TESTING_DATABASE_ISOLATION_SAFE_MIGRATION_CONTRACT`.

Status: `DONE` for testing DB isolation. Previous transition wording is superseded: scheduler due-run/non-silent-failure proof and safe live provider smoke PASS are now recorded, so overall rollout status is `OPS_RUNTIME_PARITY_PASSED`.

Evidence root: `storage/app/market-data/testing-database-isolation-safe-migration/**`.

| Validation area | Result | Evidence summary | Status |
|---|---|---|---|
| Environment file loading | `--env=testing` resolves `.env.testing` before config boot | `APP_ENV=testing`, `DB_CONNECTION=mysql`, `DB_DATABASE=tradeaxis_testing` | PASS |
| Negative destructive guard | Unsafe testing DB target blocked before migration handling | `php artisan migrate:fresh --env=testing --database=nonexistent` -> exit 3, `BLOCKED_TESTING_DATABASE_ENV` | PASS |
| Migration status | Testing migration status command boots cleanly | `php artisan migrate:status --env=testing` -> exit 0 | PASS |
| Testing migrate fresh | Destructive migration targets testing DB and succeeds | `php artisan migrate:fresh --env=testing` -> exit 0, 29 migrations | PASS |
| Required table proof | Required market-data tables exist in `tradeaxis_testing` | `tickers`, `market_calendar`, `eod_runs`, `eod_publications`, `eod_current_publication_pointer`, `md_replay_daily_metrics`, `eod_dataset_corrections`, `md_session_snapshots` | PASS |
| Static guard | Regression guard added | `tests/Unit/MarketData/TestingDatabaseIsolationStaticGuardTest.php` -> OK (4 tests, 41 assertions) | PASS |
| Filtered static guard | New guard included in aggregate static sweep | `vendor/bin/phpunit tests/Unit/MarketData --filter "StaticGuard"` -> OK (180 tests, 4191 assertions) | PASS |
| Full MarketData suite | Regression proof remains clean after env/guard patch | `vendor/bin/phpunit tests/Unit/MarketData` -> OK (479 tests, 7009 assertions), Time 00:18.274, Memory 40.00 MB | PASS |
| Evidence encoding | New command outputs are UTF-8 without null-byte/UTF-16 evidence noise | command-output files under evidence root | PASS |

Implementation summary:

- `bootstrap/app.php` now detects CLI `--env testing`, CLI `--env=testing`, or system `APP_ENV` before Lumen environment loading and selects `.env.<environment>` when the file exists.
- `artisan` now guards `migrate:fresh`, `migrate:refresh`, `migrate:reset`, and `db:wipe` in testing before `$kernel->handle(...)`.
- The guard accepts only `tradeaxis_testing` as the destructive testing migration database and emits `BLOCKED_TESTING_DATABASE_ENV` with exit code 3 otherwise.

Final decision for this blocker:

- `BLOCKED_TESTING_DATABASE_ENV` is closed for this patched source state.
- `MARKET_DATA_PRODUCTION_READY_LOCKED` remains valid.  **[SUPERSEDED 2026-08-06 — W22]** Klaim ini tidak berlaku untuk baseline yang dikoreksi; lihat `reports/AUDIT_FINAL_STATE.md`.
- At this DB-isolation closure point, full production rollout parity still had `OPS_DEPLOYMENT_TASK_REQUIRED` and `PROVIDER_SMOKE_SAFE_MODE_SURFACE_ADDED`; scheduler status is superseded by the Production Scheduler / Cron Deployment Proof section below.

Post-patch validation:

- `vendor/bin/phpunit tests/Unit/MarketData/TestingDatabaseIsolationStaticGuardTest.php` -> OK (4 tests, 41 assertions).
- `vendor/bin/phpunit tests/Unit/MarketData/AuditDocsSynchronizationStaticGuardTest.php` -> OK (10 tests, 430 assertions).
- `vendor/bin/phpunit tests/Unit/MarketData/ProductionValidationRuntimeProofStaticGuardTest.php` -> OK (13 tests, 220 assertions).
- `vendor/bin/phpunit tests/Unit/MarketData/OperationalReadinessStaticGuardTest.php` -> OK (10 tests, 204 assertions).
- `vendor/bin/phpunit tests/Unit/MarketData/OpsEnvironmentBaselineStaticGuardTest.php` -> OK (8 tests, 107 assertions).
- `vendor/bin/phpunit tests/Unit/MarketData/ConfigEnvGovernanceCleanupStaticGuardTest.php` -> OK (10 tests, 123 assertions).
- `vendor/bin/phpunit tests/Unit/MarketData --filter "StaticGuard"` -> OK (180 tests, 4191 assertions).
- `vendor/bin/phpunit tests/Unit/MarketData` -> OK (479 tests, 7009 assertions), Time 00:18.274, Memory 40.00 MB.



<!-- LEGACY_EXTRACT_BODY_END -->
