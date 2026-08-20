# Legacy Semantic Extract — LX-MD-0035-CTX-01

- Source ID: `LS-MD-0035`
- Original path: `audit/MARKET_DATA_PRODUCTION_PROOF_PACK.md`
- Original SHA1: `9D1E95DE0523A6EFF6B7E31DF54056B51CB33F26`
- Extract role: `CONTEXT`
- Source range: `L420-L489`
- Extract body SHA1: `28BC2363514C9A17F9D48FF31D53903A2E2186D1`
- Current authority: `NO`
- Current verification effect: `HISTORICAL_ONLY`
- Preservation policy: `EXACT_SOURCE_RANGE_COPY_WITH_METADATA`

<!-- LEGACY_EXTRACT_BODY_START -->
## 2026-05-21 — OPS_RUNTIME_PARITY_COMPLETION_SCHEDULER_PROVIDER_SMOKE

[SESSION] OPS_RUNTIME_PARITY_COMPLETION_SCHEDULER_PROVIDER_SMOKE

[SESSION_STATUS] OPS_RUNTIME_PARITY_PASSED

[FINAL_DECISION]
- `OPS_RUNTIME_PARITY_PASSED` remains the only valid overall ops runtime parity status for this source ZIP.
- `MARKET_DATA_PRODUCTION_READY_LOCKED` remains valid for core market-data source logic.  **[SUPERSEDED 2026-08-06 — W22]** Klaim ini tidak berlaku untuk baseline yang dikoreksi; lihat `reports/AUDIT_FINAL_STATE.md`.
- This update closes the missing safe provider-smoke command surface at source level, but does not claim live provider PASS because local artisan execution is blocked by the documented unsupported PHP 8.4.16 runtime.

[IMPLEMENTATION]
- Added `app/Console/Commands/MarketData/ProviderSmokeCommand.php` with command surface `market-data:provider:smoke --ticker=BBCA --trade_date=YYYY-MM-DD --dry-run`.
- Registered `ProviderSmokeCommand::class` in `app/Console/Kernel.php`.
- The provider smoke command is dry-run only, single-ticker only, and calls `PublicApiEodBarsAdapter::fetchOrLoadEodBars($tradeDate, 'api', [$ticker])` without ingest pipeline writes.
- Provider smoke does not call seal, finalize, publication switching, current pointer updates, candidate publication creation, or artifact replacement.
- Added early `artisan` fail-closed env override guard so `APP_ENV=testing DB_DATABASE=tradeaxis php artisan migrate:fresh --env=testing` exits before the unsupported-PHP guard and proves `BLOCKED_TESTING_DATABASE_ENV` with exit code 3 in this container.

[PROVIDER_SMOKE_SAFE_MODE]
- `PROVIDER_SMOKE_SAFE_MODE_SURFACE_ADDED`.
- Output contract includes `provider_smoke_status=`, `reason_code=`, `publication_created=false`, `seal_executed=false`, `finalize_executed=false`, `pointer_switched=false`, `readable_publication_created=false`, and `full_universe_fetch=false`.
- Supported reason codes include `PROVIDER_SMOKE_OK`, `PROVIDER_RATE_LIMITED`, `PROVIDER_TIMEOUT`, `PROVIDER_NETWORK_ERROR`, `PROVIDER_EMPTY_OR_INVALID_RESPONSE`, `PROVIDER_SMOKE_TICKER_REQUIRED`, `PROVIDER_SMOKE_INVALID_TICKER`, and `PROVIDER_SMOKE_FULL_UNIVERSE_BLOCKED`.
- Runtime attempt artifact records `provider_smoke_status=PASS`, `reason_code=PROVIDER_SMOKE_OK`, `publication_created=false`, and `pointer_switched=false`.

[SCHEDULER_ARTIFACT_STATUS]
- Scheduler config surface artifact was written, but the actual `schedule:run` enabled/disabled commands are `BLOCKED_CONTAINER_RUNTIME_ENV` because PHP 8.4.16 is intentionally rejected before Laravel boot.
- `phase4-scheduler-output-log.txt` records `SCHEDULER_RUNTIME_LOG_NOT_PRODUCED` with `REASON_CODE=BLOCKED_CONTAINER_RUNTIME_ENV`.
- `SUPERSEDED_BY_SCHEDULER_DUE_RUN_AND_NON_SILENT_FAILURE_PROOF`: previous scheduler proof review requirement is closed for due-run/non-silent-failure proof; do not claim `SCHEDULER_SUCCESSFUL_DAILY_RUN_PROOF_PASSED`.

[NEGATIVE_DB_OVERRIDE_PROOF]
- `APP_ENV=testing DB_DATABASE=tradeaxis php artisan migrate:fresh --env=testing` was executed in this container.
- Result: `BLOCKED_TESTING_DATABASE_ENV`, `EXIT_CODE:3`.
- This is the only runtime command in this session that produced the expected safety result inside the container.

[LOCAL_RUNTIME_STATUS]
- Environment baseline: `BLOCKED_CONTAINER_RUNTIME_ENV` because `php artisan --version`, `php artisan list`, `schedule:run`, provider smoke, and PHPUnit are blocked by `ENV_UNSUPPORTED_PHP_VERSION` on PHP 8.4.16.
- Composer is unavailable in the container, so `composer --version` and `composer validate` are also blocked.
- PHPUnit targeted/full suite not executed; status remains `BLOCKED_CONTAINER_RUNTIME_ENV`, PASS.

[EVIDENCE]
- `storage/app/market-data/ops-runtime-parity-completion/command-output/phase1-environment-baseline.txt`.
- `storage/app/market-data/ops-runtime-parity-completion/command-output/phpunit-provider-smoke-static-guard.txt`.
- `storage/app/market-data/production-scheduler-cron-deployment-proof/command-output/phase0-migrate-fresh-testing-precondition.txt`.
- `storage/app/market-data/production-scheduler-cron-deployment-proof/command-output/phase1-testing-db-negative-env-override.txt`.
- `storage/app/market-data/production-scheduler-cron-deployment-proof/command-output/phase2-scheduler-config-enabled.txt`.
- `storage/app/market-data/production-scheduler-cron-deployment-proof/command-output/phase3-schedule-run-enabled-due.txt`.
- `storage/app/market-data/production-scheduler-cron-deployment-proof/command-output/phase4-scheduler-output-log.txt`.
- `storage/app/market-data/production-scheduler-cron-deployment-proof/command-output/phase5-schedule-run-disabled-control.txt`.
- `storage/app/market-data/provider-smoke-safe-mode/command-output/provider-smoke-bbca.txt`.
- `storage/app/market-data/evidence-encoding-normalization-report.txt` reports `checked_files=167`, `normalized_files=0`, `null_byte_remaining=0`, `status=PASS`.

[VALIDATION]
- `php -l artisan` -> PASS.
- `php -l app/Console/Kernel.php` -> PASS.
- `php -l app/Console/Commands/MarketData/ProviderSmokeCommand.php` -> PASS.
- `php -l tests/Unit/MarketData/ProviderSmokeSafeModeStaticGuardTest.php` -> PASS.
- `php vendor/bin/phpunit tests/Unit/MarketData/ProviderSmokeSafeModeStaticGuardTest.php` -> `BLOCKED_CONTAINER_RUNTIME_ENV` because PHPUnit stops on missing `dom`, `mbstring`, `xml`, and `xmlwriter` extensions before project bootstrap.

[BLOCKERS]
- Source-code blocker: none found in scoped patch.
- Environment blocker: PHP 8.4.16 unsupported by project evidence guard; Composer unavailable in container.
- Historical provider blocker is superseded by the later live provider smoke PASS artifact; current status is `FINAL_PROVIDER_SMOKE=PASSED`.

[REMAINING_RISK]
- Provider smoke has been rerun successfully and now supports the `OPS_RUNTIME_PARITY_PASSED` claim for this source.
- Previous historical `PROVIDER_SMOKE_SAFE_MODE_SURFACE_ADDED` is superseded at source-surface level by the new command; runtime provider proof now passes with `PROVIDER_SMOKE_OK` / HTTP 200.
- Final full `vendor\bin\phpunit tests/Unit/MarketData` has passed locally: OK (492 tests, 7590 assertions), Time 00:36.861, Memory 40.00 MB. This supports the final ops parity promotion for this source ZIP.

---


<!-- LEGACY_EXTRACT_BODY_END -->
