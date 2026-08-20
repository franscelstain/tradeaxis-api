# Legacy Semantic Extract — LX-MD-0038-CTX-02

- Source ID: `LS-MD-0038`
- Original path: `audit/OPS_ENVIRONMENT_BASELINE_INVENTORY.md`
- Original SHA1: `C4C8EC75AF93028E3F6AEFEF6E52E82B376969D5`
- Extract role: `CONTEXT`
- Source range: `L157-L243`
- Extract body SHA1: `78DA0A49FDC74E9571312E6F796BF1A52D82FF10`
- Current authority: `NO`
- Current verification effect: `HISTORICAL_ONLY`
- Preservation policy: `EXACT_SOURCE_RANGE_COPY_WITH_METADATA`

<!-- LEGACY_EXTRACT_BODY_START -->
## 2026-05-21 Production Rollout Runtime Parity Environment Check

Scope: `PRODUCTION_ROLLOUT_VALIDATION_RUNTIME_PARITY_PROOF`.

Status: `[OPS_RUNTIME_PARITY] SUPERSEDED_BY_FINAL_PASSED_RECONCILIATION`. Historical environment-blocked finding is closed for the current source ZIP by final scheduler/provider/full-PHPUnit proof.

Baseline results:

- PHP CLI: 7.4.33, supported by policy `>= 7.3` and `< 8.4`.
- Required extensions: present for `dom`, `mbstring`, `xml`, `xmlwriter`, `json`, `PDO`, `pdo_mysql`, `pdo_pgsql`, `pdo_sqlite`, `openssl`, `curl`, `fileinfo`, and `tokenizer`.
- Composer: 2.8.4, `composer validate` valid.
- Artisan boot: clean Lumen 8.3.4 output, no PHP warning/deprecation/noise.
- PHPUnit: targeted and full MarketData suites passed in this runtime.
- Storage: `storage`, `storage/logs`, `storage/app`, `storage/app/market-data`, `storage/app/market_data`, and the runtime parity evidence root are writable.

Runtime parity blockers:

- `BLOCKED_TESTING_DATABASE_ENV`: `php artisan migrate:fresh --env=testing` did not target `.env.testing` database `tradeaxis_testing`; table checks showed the command affected `.env` database `tradeaxis`. Explicit environment override was required to run the same migration chain against `tradeaxis_testing`.
- `OPS_DEPLOYMENT_TASK_REQUIRED`: scheduler/cron production readiness is represented by the scheduler due-run runtime artifact for this source; no-silent-failure output is recorded.
- `PROVIDER_SMOKE_SAFE_MODE_SURFACE_ADDED`: live provider smoke is passed because the safe dry-run single-ticker command is available and returned valid data.

Decision:

- Runtime baseline itself is PASS.
- Full ops runtime parity is no longer blocked for the current source ZIP; testing DB targeting, scheduler due-run proof, and safe provider smoke have been validated by the later final proof.

Post-doc validation:

- `vendor/bin/phpunit tests/Unit/MarketData/AuditDocsSynchronizationStaticGuardTest.php` -> OK (10 tests, 421 assertions).
- `vendor/bin/phpunit tests/Unit/MarketData/ProductionValidationRuntimeProofStaticGuardTest.php` -> OK (13 tests, 220 assertions).
- `vendor/bin/phpunit tests/Unit/MarketData/OpsEnvironmentBaselineStaticGuardTest.php` -> OK (8 tests, 107 assertions).
- `vendor/bin/phpunit tests/Unit/MarketData --filter "StaticGuard"` -> OK (176 tests, 4141 assertions).
- `vendor/bin/phpunit tests/Unit/MarketData` -> OK (475 tests, 6959 assertions).


## 2026-05-21 Testing DB Isolation Follow-Up

Scope: `TESTING_DATABASE_ISOLATION_SAFE_MIGRATION_CONTRACT`.

Status: `DONE` for the testing DB isolation blocker discovered by runtime parity validation.

Updated baseline result:

- `--env=testing` now selects `.env.testing` before Lumen config boot.
- Config probe resolves `APP_ENV=testing`, `DB_CONNECTION=mysql`, and `DB_DATABASE=tradeaxis_testing`.
- `php artisan migrate:fresh --env=testing --database=nonexistent` exits 3 with `BLOCKED_TESTING_DATABASE_ENV`.
- `php artisan migrate:fresh --env=testing` exits 0 and runs all 29 migrations against `tradeaxis_testing`.
- Required market-data tables are present in `tradeaxis_testing`.
- New command-output evidence is UTF-8 plain text.
- `TestingDatabaseIsolationStaticGuardTest.php` -> OK (4 tests, 41 assertions).
- `vendor/bin/phpunit tests/Unit/MarketData --filter "StaticGuard"` -> OK (180 tests, 4191 assertions).
- `vendor/bin/phpunit tests/Unit/MarketData` -> OK (479 tests, 7009 assertions), Time 00:18.274, Memory 40.00 MB.

Remaining ops rollout blockers:

- `OPS_DEPLOYMENT_TASK_REQUIRED`: superseded by the later Scheduler/Cron Deployment Follow-Up section below.
- `PROVIDER_SMOKE_SAFE_MODE_SURFACE_ADDED`: safe live provider smoke is passed.


## 2026-05-21 Scheduler/Cron Deployment Follow-Up

Scope: `PRODUCTION_SCHEDULER_CRON_DEPLOYMENT_PROOF_CONTRACT`.

Status: `DONE` / `SCHEDULER_RUNTIME_LOG_PRODUCED` because the source ZIP contains the scheduler due-run runtime artifacts required to accept the scheduler proof claim.

Updated baseline result:

- `MARKET_DATA_DAILY_ENABLED=true` registers `market-data:daily --latest`.
- Scheduler event uses configured cutoff and `Asia/Jakarta` timezone.
- Scheduler event appends command output to `MARKET_DATA_SCHEDULER_OUTPUT_PATH`.
- Scheduler event writes `scheduler_status=SUCCESS` or `scheduler_status=FAILURE`.
- `withoutOverlapping` is configured through `MARKET_DATA_SCHEDULER_WITHOUT_OVERLAPPING_MINUTES`.
- `php artisan schedule:run --env=testing` invoked `market-data:daily --latest` when the cutoff was due.
- Scheduler stdout included `Running scheduled command`.
- Safe manual-file proof failed reason-coded with `RUN_SOURCE_MANUAL_FILE_NOT_FOUND`, remained `NOT_READABLE`, recorded `scheduler_status=FAILURE`, and did not switch pointer.
- `ProductionSchedulerCronStaticGuardTest.php` -> rerun required after artifact-reconciliation guard update.
- `vendor/bin/phpunit tests/Unit/MarketData --filter "StaticGuard"` -> OK (184 tests, 4286 assertions).
- `vendor/bin/phpunit tests/Unit/MarketData` -> OK (483 tests, 7104 assertions), Time 00:11.035, Memory 40.00 MB.

Open ops rollout blocker:

- `OPS_DEPLOYMENT_TASK_REQUIRED` is closed for this source ZIP because scheduler runtime artifacts are supplied and archived.

Remaining ops rollout blockers:

- `PROVIDER_SMOKE_SAFE_MODE_SURFACE_ADDED`: safe live provider smoke is passed.


<!-- LEGACY_EXTRACT_BODY_END -->
