# Legacy Semantic Extract — LX-MD-0027-CTX-02

- Source ID: `LS-MD-0027`
- Original path: `audit/FULL_MARKET_DATA_PRODUCTION_READY_INVENTORY.md`
- Original SHA1: `4C0357CC7BA4A9338F34EBCF09A671716FC4A857`
- Extract role: `CONTEXT`
- Source range: `L163-L272`
- Extract body SHA1: `8CDDA6D93DF4E50EF11B0B75EAB9C4946D182322`
- Current authority: `NO`
- Current verification effect: `HISTORICAL_ONLY`
- Preservation policy: `EXACT_SOURCE_RANGE_COPY_WITH_METADATA`

<!-- LEGACY_EXTRACT_BODY_START -->
## 2026-06-05 Current Source-State Full Global Lock Update

This update reconciles the aggregate inventory with the active Lumen checkpoints.

Consumed current-source proof:
- `LUMEN_IMPLEMENTATION_STATUS.md` records `FULL_GLOBAL_MARKET_DATA_LOCK_STATUS=LOCKED_UNFILTERED_MISSING_TICKER_PLAN_ZERO_FULL_RANGE_CURRENT_EVIDENCE_REPLAY_PASS`.
- `LUMEN_CONTRACT_TRACKER.md` locks the missing-ticker filtered candidate preservation contract after full global close.
- `MARKET_DATA_PRODUCTION_PROOF_PACK.md` records refreshed provider smoke PASS and full MarketData PHPUnit proof.
- Final missing plan proof reports `missing_bar_count=0`, `missing_trade_date_count=0`, `ticker_count=0`, and `trading_dates=672`.
- Final full-range current evidence/replay proof reports `processed_count=672`, `success_count=672`, `failed_count=0`, and `all_passed=1`.

Final lock decision:
- `MARKET_DATA_PRODUCTION_READY_LOCKED` remains the current aggregate production-ready status.  **[SUPERSEDED 2026-08-06 — W22]** Klaim ini tidak berlaku untuk baseline yang dikoreksi; lihat `reports/AUDIT_FINAL_STATE.md`.
- `FULL_GLOBAL_MARKET_DATA_LOCK_STATUS` is locked for the archived current-readable proof window; production-ready status is source-state and lifecycle readiness, not a terminal date range.
- Older `PARTIAL`, `BLOCKED`, or source-blocker notes in this inventory or in Lumen histories are historical when followed by the 2026-06-05 closure entries above.


## 2026-05-20 Current Source-State Final Lock Update

`MARKET_DATA_PRODUCTION_PROOF_PACK.md` is now the aggregate proof pack for the current uploaded source state.

Consumed current-source proof:

- `OPS_COMMAND_SURFACE_RUNTIME_MATRIX_CONTRACT -> LOCKED` with current 21-command registry/help proof; the 2026-05-20 20-command fixture matrix is now superseded by the final passed provider-smoke proof and is superseded by the provider-smoke overlay.
- Full `vendor/bin/phpunit tests/Unit/MarketData` -> OK (475 tests, 6942 assertions).
- Ops runtime artifacts under `storage/app/market-data/ops-command-surface-runtime-matrix-production-ready/**`.
- Fresh success proof: daily `run_id=30`, stage-chain `run_id=32`, promote `run_id=33`, `publication_id=27`, `current_publication_id=27`.
- Coverage PASS proof: `coverage_gate_state=PASS`, `coverage_ratio=1`, `coverage_min_threshold=0.98`.
- Held/failed proof: `RUN_PARTIAL_DATA`, `COVERAGE_BELOW_THRESHOLD`, `RUN_SOURCE_MANUAL_FILE_EMPTY`.
- Replay proof: `replay_id=15`, `comparison_result=MATCH`, `replay_status=PASS`, `mismatch_count=0`; smoke `all_passed=1`; backfill `replay_id=18` PASS.
- Evidence proof: `run_id=33` evidence admission `ADMITTED_COMPLETE`; correction `3` admission `ADMITTED_COMPLETE`.
- Historical non-current replay proof remains locked for `replay_id=8`.

Final lock decision:

- `MARKET_DATA_PRODUCTION_READY_LOCKED` is allowed for this source state.  **[SUPERSEDED 2026-08-06 — W22]** Klaim ini tidak berlaku untuk baseline yang dikoreksi; lihat `reports/AUDIT_FINAL_STATE.md`.
- `LOCKED` is now used because Final Audit Docs Synchronization consumed the proof pack and no P0/P1 blocker remains.
- Required next remediation session: none for this source-state lock; revalidate only for new code/config/vendor/provider/deployment changes.

---

## 2026-05-21 — OPS_RUNTIME_PARITY_COMPLETION_SCHEDULER_PROVIDER_SMOKE

[SESSION] OPS_RUNTIME_PARITY_COMPLETION_SCHEDULER_PROVIDER_SMOKE

[SESSION_STATUS] OPS_RUNTIME_PARITY_PASSED

[FINAL_DECISION]
- `OPS_RUNTIME_PARITY_PASSED` remains the only valid overall ops runtime parity status for this source ZIP.
- `MARKET_DATA_PRODUCTION_READY_LOCKED` remains valid for core market-data source logic.  **[SUPERSEDED 2026-08-06 — W22]** Klaim ini tidak berlaku untuk baseline yang dikoreksi; lihat `reports/AUDIT_FINAL_STATE.md`.
- Historical provider-smoke surface update is superseded by the later live provider smoke PASS artifact; current docs retain the container runtime limitation only as historical context.

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
- `storage/app/market-data/evidence-encoding-normalization-report.txt` reports `checked_files=165`, `normalized_files=0`, `null_byte_remaining=0`, `status=PASS`.

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
- Provider smoke and scheduler/runtime proof have been reconciled on the documented operator baseline; `OPS_RUNTIME_PARITY_PASSED` is the current source-ZIP decision.
- Previous historical `PROVIDER_SMOKE_SAFE_MODE_SURFACE_ADDED` is superseded at source-surface level by the new command, but runtime provider proof is now passed in the operator-local runtime artifact.

---


<!-- LEGACY_EXTRACT_BODY_END -->
