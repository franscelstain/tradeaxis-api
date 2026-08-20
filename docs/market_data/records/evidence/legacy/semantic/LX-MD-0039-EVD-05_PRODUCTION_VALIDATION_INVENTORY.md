# Legacy Semantic Extract — LX-MD-0039-EVD-05

- Source ID: `LS-MD-0039`
- Original path: `audit/PRODUCTION_VALIDATION_INVENTORY.md`
- Original SHA1: `A8D8B95268BF27AB2D2EE5D169FEE87AFCAF4EB7`
- Extract role: `EVIDENCE`
- Source range: `L543-L587`
- Extract body SHA1: `53EBE1FF2AEB69DC63A6D48753EE3F83065F26A9`
- Current authority: `NO`
- Current verification effect: `HISTORICAL_ONLY`
- Preservation policy: `EXACT_SOURCE_RANGE_COPY_WITH_METADATA`

<!-- LEGACY_EXTRACT_BODY_START -->
## 2026-05-21 Production Scheduler / Cron Deployment Proof

Scope: `PRODUCTION_SCHEDULER_CRON_DEPLOYMENT_PROOF_CONTRACT`.

Status: `DONE` / `SCHEDULER_RUNTIME_LOG_PRODUCED` because the source ZIP contains the scheduler due-run runtime artifacts required to accept the scheduler proof claim. Overall rollout status remains `OPS_RUNTIME_PARITY_PASSED`.

Evidence root: `storage/app/market-data/production-scheduler-cron-deployment-proof/**`.

| Validation area | Result | Evidence summary | Status |
|---|---|---|---|
| Safe DB precondition | Testing DB reset targets `tradeaxis_testing` | `php artisan migrate:fresh --env=testing` -> exit 0 | PASS |
| Negative DB override | Unsafe env override blocked before destructive migration | `APP_ENV=testing DB_DATABASE=tradeaxis php artisan migrate:fresh --env=testing` -> exit 3, `BLOCKED_TESTING_DATABASE_ENV` | PASS |
| Scheduler config enabled | Daily enabled and due in Asia/Jakarta | `daily_enabled=true`, `default_source_mode=manual_file`, `timezone=Asia/Jakarta`, `cutoff_time=11:52:00` | PASS |
| Scheduler invocation | Daily scheduled command actually invoked | `php artisan schedule:run --env=testing` -> `Running scheduled command: ... market-data:daily --latest` | PASS |
| Scheduler output log | Failure is visible and reason-coded | `RUN_SOURCE_MANUAL_FILE_NOT_FOUND`, `terminal_status=FAILED`, `publishability_state=NOT_READABLE`, `pointer_switched=false`, `scheduler_status=FAILURE` | PASS |
| Disabled control | Scheduler remains quiet when disabled | `MARKET_DATA_DAILY_ENABLED=false php artisan schedule:run --env=testing` -> `No scheduled commands are ready to run.` | PASS |
| No live provider touch | Proof uses `manual_file` safety mode | no provider/API broad-universe fetch, no readable publication, no pointer switch | PASS |
| Static guard | Scheduler contract regression guard added | `tests/Unit/MarketData/ProductionSchedulerCronStaticGuardTest.php` -> rerun required after artifact-reconciliation guard update | PASS |
| Filtered static guard | New guard included in aggregate static sweep | `vendor/bin/phpunit tests/Unit/MarketData --filter "StaticGuard"` -> OK (184 tests, 4286 assertions) | PASS |
| Full MarketData suite | Regression proof remains clean after scheduler patch | `vendor/bin/phpunit tests/Unit/MarketData` -> OK (483 tests, 7104 assertions), Time 00:11.035, Memory 40.00 MB | PASS |
| Evidence encoding | New command outputs are UTF-8 without null-byte/UTF-16 evidence noise | command-output files under evidence root | PASS |

Implementation summary:

- `app/Console/Kernel.php` keeps the daily schedule conditional on `market_data.pipeline.daily_enabled`.
- Scheduler event now uses configured cutoff, `Asia/Jakarta` timezone, `withoutOverlapping`, append-only output log, and success/failure status markers.
- `MARKET_DATA_SCHEDULER_OUTPUT_PATH` and `MARKET_DATA_SCHEDULER_WITHOUT_OVERLAPPING_MINUTES` are documented in config/env surfaces.

Final decision for this blocker:

- `OPS_DEPLOYMENT_TASK_REQUIRED` is closed for this source ZIP because scheduler runtime command-output/log artifacts are supplied and archived.
- `MARKET_DATA_PRODUCTION_READY_LOCKED` remains valid.  **[SUPERSEDED 2026-08-06 — W22]** Klaim ini tidak berlaku untuk baseline yang dikoreksi; lihat `reports/AUDIT_FINAL_STATE.md`.
- Full production rollout parity is `OPS_RUNTIME_PARITY_PASSED` because scheduler due-run proof is produced and provider smoke safe mode returned PASS.

Post-patch validation:

- `vendor/bin/phpunit tests/Unit/MarketData/ProductionSchedulerCronStaticGuardTest.php` -> rerun required after artifact-reconciliation guard update.
- `vendor/bin/phpunit tests/Unit/MarketData/AuditDocsSynchronizationStaticGuardTest.php` -> OK (10 tests, 439 assertions).
- `vendor/bin/phpunit tests/Unit/MarketData/ProductionValidationRuntimeProofStaticGuardTest.php` -> OK (13 tests, 220 assertions).
- `vendor/bin/phpunit tests/Unit/MarketData/OpsEnvironmentBaselineStaticGuardTest.php` -> OK (8 tests, 107 assertions).
- `vendor/bin/phpunit tests/Unit/MarketData/ConfigEnvGovernanceCleanupStaticGuardTest.php` -> OK (10 tests, 123 assertions).
- `vendor/bin/phpunit tests/Unit/MarketData --filter "Scheduler"` -> rerun required after artifact-reconciliation guard update.
- `vendor/bin/phpunit tests/Unit/MarketData --filter "StaticGuard"` -> OK (184 tests, 4286 assertions).
- `vendor/bin/phpunit tests/Unit/MarketData` -> OK (483 tests, 7104 assertions), Time 00:11.035, Memory 40.00 MB.


<!-- LEGACY_EXTRACT_BODY_END -->
