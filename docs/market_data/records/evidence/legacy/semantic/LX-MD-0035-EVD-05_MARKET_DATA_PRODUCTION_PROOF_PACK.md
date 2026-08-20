# Legacy Semantic Extract — LX-MD-0035-EVD-05

- Source ID: `LS-MD-0035`
- Original path: `audit/MARKET_DATA_PRODUCTION_PROOF_PACK.md`
- Original SHA1: `9D1E95DE0523A6EFF6B7E31DF54056B51CB33F26`
- Extract role: `EVIDENCE`
- Source range: `L341-L373`
- Extract body SHA1: `90C02B2C94E4E763BD80B5CE25927F5120D470A7`
- Current authority: `NO`
- Current verification effect: `HISTORICAL_ONLY`
- Preservation policy: `EXACT_SOURCE_RANGE_COPY_WITH_METADATA`

<!-- LEGACY_EXTRACT_BODY_START -->
## 19. 2026-05-21 Production Scheduler / Cron Deployment Proof

Scope: `PRODUCTION_SCHEDULER_CRON_DEPLOYMENT_PROOF_CONTRACT`.

Decision for this blocker: `SCHEDULER_RUNTIME_LOG_PRODUCED / PASS`.

Source lock impact: `MARKET_DATA_PRODUCTION_READY_LOCKED` remains valid. This patch does not change market-data service/repository/provider/replay/correction/finalize/pointer behavior or migration schema.  **[SUPERSEDED 2026-08-06 — W22]** Klaim ini tidak berlaku untuk baseline yang dikoreksi; lihat `reports/AUDIT_FINAL_STATE.md`.

Runtime evidence root: `storage/app/market-data/production-scheduler-cron-deployment-proof/**`.

Claimed validation requiring missing artifact support:

- Scheduler registration remains conditional on `MARKET_DATA_DAILY_ENABLED=true` and still invokes only `market-data:daily --latest`.
- Scheduler event uses configured cutoff, `Asia/Jakarta` timezone, `withoutOverlapping`, append-only output log, and `scheduler_status=SUCCESS|FAILURE` markers.
- Config proof: `APP_ENV=testing`, `DB_DATABASE=tradeaxis_testing`, `daily_enabled=true`, `default_source_mode=manual_file`, `timezone=Asia/Jakarta`, `cutoff_time=11:52:00`, scheduler output path under the proof runtime root, and overlap TTL `120`.
- Runtime invocation proof: `php artisan schedule:run --env=testing` exits 0 and prints `Running scheduled command: ... market-data:daily --latest`.
- Scheduler log proof: scheduled daily creates `run_id=1`, `terminal_status=FAILED`, `publishability_state=NOT_READABLE`, `source_mode=manual_file`, `final_reason_code=RUN_SOURCE_MANUAL_FILE_NOT_FOUND`, `pointer_switched=false`, and `scheduler_status=FAILURE`.
- Disabled control proof: `MARKET_DATA_DAILY_ENABLED=false php artisan schedule:run --env=testing` exits 0 with `No scheduled commands are ready to run.`
- DB isolation negative proof strengthened: `APP_ENV=testing DB_DATABASE=tradeaxis php artisan migrate:fresh --env=testing` exits 3 with `BLOCKED_TESTING_DATABASE_ENV`.
- Static guard proof: `vendor/bin/phpunit tests/Unit/MarketData/ProductionSchedulerCronStaticGuardTest.php` -> rerun required after artifact-reconciliation guard update.
- StaticGuard filter: `vendor/bin/phpunit tests/Unit/MarketData --filter "StaticGuard"` -> OK (184 tests, 4286 assertions).
- Full MarketData suite: `vendor/bin/phpunit tests/Unit/MarketData` -> OK (483 tests, 7104 assertions), Time 00:11.035, Memory 40.00 MB.

Open blocker:

- None for current scheduler due-run proof; scheduler runtime command-output/log artifacts are present and accepted for this source state.

Remaining rollout blockers:

- `PROVIDER_SMOKE_SAFE_MODE_SURFACE_ADDED`: safe live provider smoke is passed.

Final note: this historical scheduler evidence packaging gap is superseded by the later scheduler due-run/non-silent-failure proof and live provider smoke PASS. It still must not be read as successful scheduled daily production run proof.


<!-- LEGACY_EXTRACT_BODY_END -->
