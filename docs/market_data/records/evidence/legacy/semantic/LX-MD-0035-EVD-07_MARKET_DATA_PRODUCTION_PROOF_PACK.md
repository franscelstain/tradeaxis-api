# Legacy Semantic Extract — LX-MD-0035-EVD-07

- Source ID: `LS-MD-0035`
- Original path: `audit/MARKET_DATA_PRODUCTION_PROOF_PACK.md`
- Original SHA1: `9D1E95DE0523A6EFF6B7E31DF54056B51CB33F26`
- Extract role: `EVIDENCE`
- Source range: `L490-L564`
- Extract body SHA1: `B82CA24A05D6EA05175C26694B16D42B2A88FAAF`
- Current authority: `NO`
- Current verification effect: `HISTORICAL_ONLY`
- Preservation policy: `EXACT_SOURCE_RANGE_COPY_WITH_METADATA`

<!-- LEGACY_EXTRACT_BODY_START -->
## 2026-05-21 Final Proof Pack / Ops Runtime Parity Reconciliation

[SOURCE_ZIP_IDENTITY]
- Historical source ZIP (`SUPERSEDED_BY_FINAL_PROVIDER_SMOKE_PASS_AND_FULL_MARKETDATA_PHPUNIT_PASS`): `D:\Laravel\tradeaxis-api\tradeaxis-api-provider.zip`.
- Historical source ZIP SHA-256 (`SUPERSEDED_BY_FINAL_PROVIDER_SMOKE_PASS_AND_FULL_MARKETDATA_PHPUNIT_PASS`): `aea589eabb634eca4da6051c3e62dfb732e4b9fa563744d7c54246e215f6c333`.

[FINAL_DECISION]
- Overall decision: `OPS_RUNTIME_PARITY_PASSED`.
- Source-state decision: `MARKET_DATA_PRODUCTION_READY_LOCKED`.  **[SUPERSEDED 2026-08-06 — W22]** Klaim ini tidak berlaku untuk baseline yang dikoreksi; lihat `reports/AUDIT_FINAL_STATE.md`.
- Ops rollout/runtime parity: `OPS_RUNTIME_PARITY_PASSED`.
- Scheduler/cron deployment readiness: `SCHEDULER_DUE_RUN_PROOF_PASSED / SCHEDULER_SUCCESSFUL_DAILY_RUN_PROOF_NOT_CLAIMED`.
- Live provider smoke readiness: `PROVIDER_SMOKE_PASSED`.

[COMMAND_SURFACE]
- Current runtime command count: 21.
- `market-data:provider:smoke` is included in the public ops command surface.
- The 2026-05-20 20-command matrix is now superseded by the final passed provider-smoke proof fixture evidence; current proof context is the 21-command surface.

[PROVIDER_SMOKE_RUNTIME]
- `php artisan market-data:provider:smoke --ticker=BBCA --trade_date=2026-05-21 --dry-run` returned `provider_smoke_status=PASS`, `reason_code=PROVIDER_SMOKE_OK`, `publication_created=false`, `pointer_switched=false`, `readable_publication_created=false`, and `full_universe_fetch=false`.
- Rate-limited provider output is BLOCKED and must not be counted as provider readiness; provider PASS requires `PROVIDER_SMOKE_OK` with returned rows.

[SCHEDULER_RUNTIME]
- Current scheduler artifacts contain due-run output and `runtime/market-data-scheduler-proof.log`; `phase4-scheduler-output-log.txt` records `SCHEDULER_RUNTIME_LOG_PRODUCED`.
- These artifacts are accepted only as `SCHEDULER_DUE_RUN_PROOF_PASSED` and `SCHEDULER_NON_SILENT_FAILURE_PROOF_PASSED`; they do not claim `SCHEDULER_SUCCESSFUL_DAILY_RUN_PROOF_PASSED`.

[VALIDATION_STATUS]
- Runtime baseline: PHP 7.4.33; PHPUnit 9.6.34; Lumen 8.3.4.
- Current command surface: 21 public market-data commands.
- Targeted guards passed: ProductionValidation OK (14 tests, 467 assertions), CommandSurfaceSafety OK (5 tests, 91 assertions), OpsCommandSurfaceRuntimeMatrix OK (6 tests, 120 assertions), ProviderSmokeSafeMode OK (4 tests, 104 assertions), AuditDocs OK (10 tests, 446 assertions), ProductionSchedulerCron OK (5 tests, 104 assertions).
- Filtered validation passed: StaticGuard OK (191 tests, 4688 assertions), AuditDocs OK (10 tests, 446 assertions), Command OK (100 tests, 1290 assertions), Ops OK (74 tests, 624 assertions), RuntimeProof OK (14 tests, 467 assertions), Scheduler OK (5 tests, 104 assertions).
- Full `vendor/bin/phpunit tests/Unit/MarketData` passed: OK (490 tests, 7506 assertions), Time 00:20.344, Memory 40.00 MB.
- Because full MarketData passed, provider smoke passed, and scheduler due-run/non-silent-failure proof is present, source-state lock remains valid and ops parity is `OPS_RUNTIME_PARITY_PASSED`; successful scheduled daily production run proof is not claimed.


## 2026-05-21 — PROVIDER RATE-LIMIT + SCHEDULER DUE-RUN PROOF RECONCILIATION

[SESSION] PROVIDER_RATE_LIMIT_SCHEDULER_DUE_RUN_RECONCILIATION

[SESSION_STATUS] OPS_RUNTIME_PARITY_PASSED

[REVIEW_STATUS] OPS_RUNTIME_PARITY_PASSED_PROVIDER_SMOKE_OK

[INPUT_SOURCE_ZIP]
- Historical source ZIP (`SUPERSEDED_BY_FINAL_PROVIDER_SMOKE_PASS_AND_FULL_MARKETDATA_PHPUNIT_PASS`): `tradeaxis-api-provider.zip`
- Historical source ZIP SHA-256 (`SUPERSEDED_BY_FINAL_PROVIDER_SMOKE_PASS_AND_FULL_MARKETDATA_PHPUNIT_PASS`): `aea589eabb634eca4da6051c3e62dfb732e4b9fa563744d7c54246e215f6c333`

[WHAT_CHANGED_FROM_PREVIOUS_AUDIT]
- Scheduler proof is no longer `SCHEDULER_RUNTIME_LOG_PRODUCED`: the source ZIP now contains `storage/app/market-data/production-scheduler-cron-deployment-proof/runtime/market-data-scheduler-proof.log`.
- `phase4-scheduler-output-log.txt` records `RESULT=SCHEDULER_RUNTIME_LOG_PRODUCED` and `EXIT_CODE:0`.
- `phase3-schedule-run-enabled-due.txt` records that `php artisan schedule:run` executed `market-data:daily --latest` at the configured cutoff minute and exited `0`.
- Scheduler runtime log records `scheduler_status=FAILURE command="market-data:daily --latest"` with visible reason-coded daily failure (`reason_code=RUN_SOURCE_RESPONSE_CHANGED`, `terminal_status=FAILED`, `publishability_state=NOT_READABLE`, `pointer_switched=false`). This is accepted as scheduler due-run proof because the scheduler executed, wrote output, and did not fail silently.
- Provider smoke safe mode remains implemented and non-destructive, but the live BBCA dry-run is passed against Yahoo/PublicApi: `provider_smoke_status=PASS`, `reason_code=PROVIDER_SMOKE_OK`, `source_reason_code=none`, `retry_exhausted=false`.
- Evidence encoding report is current and clean: `ENCODING: UTF-8`, `SCOPE: storage/app/market-data/**/*.txt`, `checked_files=167`, `null_byte_remaining=0`, `status=PASS`.
- Reconciliation summary artifact: `storage/app/market-data/provider-rate-limit-scheduler-due-run-reconciliation/audit-summary.txt`.
- Full MarketData PHPUnit proof after encoding/report correction passed: `OK (490 tests, 7506 assertions)`, Time `00:15.508`, Memory `40.00 MB`.

[FINAL_DECISION]
- Core source-code readiness remains `MARKET_DATA_PRODUCTION_READY_LOCKED`.  **[SUPERSEDED 2026-08-06 — W22]** Klaim ini tidak berlaku untuk baseline yang dikoreksi; lihat `reports/AUDIT_FINAL_STATE.md`.
- Overall ops runtime parity is `OPS_RUNTIME_PARITY_PASSED` because live provider smoke now returns `provider_smoke_status=PASS` / `reason_code=PROVIDER_SMOKE_OK` / `http_status=200`.
- Current rollout status is `OPS_RUNTIME_PARITY_PASSED`.

[CURRENT_BLOCKERS]
- No current provider-smoke rollout blocker for this source ZIP. `LIVE_PROVIDER_SMOKE_PASSED` is backed by `provider_smoke_status=PASS`, `reason_code=PROVIDER_SMOKE_OK`, `http_status=200`, `attempt_count=1`, `retry_exhausted=false`, and all non-destructive safety flags remaining false.

[NON_BLOCKING_EVIDENCE_REFRESH]
- `phase0-migrate-fresh-testing-precondition.txt` and `phase5-schedule-run-disabled-control.txt` still contain old container-blocked output from PHP `8.4.16`; these are stale auxiliary artifacts and should be refreshed in the operator PHP `7.4.33` environment if a fully clean scheduler deployment proof pack is required.
- These stale auxiliary artifacts do not invalidate the newly present scheduler due-run runtime log, the source-state lock, or the full MarketData PHPUnit PASS.

[DO_NOT_CLAIM]
- Claim `OPS_RUNTIME_PARITY_PASSED` for this source because provider smoke returns `provider_smoke_status=PASS` / `reason_code=PROVIDER_SMOKE_OK` / `http_status=200` and all non-destructive safety flags remain false.
- Count the current artifact as provider PASS because it returns `PROVIDER_SMOKE_OK` with HTTP 200.

---


<!-- LEGACY_EXTRACT_BODY_END -->
