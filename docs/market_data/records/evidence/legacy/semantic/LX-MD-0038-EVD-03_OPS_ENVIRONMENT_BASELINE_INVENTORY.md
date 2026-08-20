# Legacy Semantic Extract — LX-MD-0038-EVD-03

- Source ID: `LS-MD-0038`
- Original path: `audit/OPS_ENVIRONMENT_BASELINE_INVENTORY.md`
- Original SHA1: `C4C8EC75AF93028E3F6AEFEF6E52E82B376969D5`
- Extract role: `EVIDENCE`
- Source range: `L345-L405`
- Extract body SHA1: `2975054073632D500C8F86C770F1BA6D88880AAF`
- Current authority: `NO`
- Current verification effect: `HISTORICAL_ONLY`
- Preservation policy: `EXACT_SOURCE_RANGE_COPY_WITH_METADATA`

<!-- LEGACY_EXTRACT_BODY_START -->
## 2026-05-21 Final Proof Pack / Ops Runtime Parity Reconciliation

[CURRENT_RUNTIME_BASELINE]
- PHP: PHP 7.4.33.
- PHPUnit: PHPUnit 9.6.34.
- Artisan: Lumen 8.3.4.
- Historical source ZIP (`SUPERSEDED_BY_FINAL_PROVIDER_SMOKE_PASS_AND_FULL_MARKETDATA_PHPUNIT_PASS`): `D:\Laravel\tradeaxis-api\tradeaxis-api-provider.zip`.
- Current source ZIP: `tradeaxis-api.zip`.
- Historical source ZIP SHA-256 (`SUPERSEDED_BY_FINAL_PROVIDER_SMOKE_PASS_AND_FULL_MARKETDATA_PHPUNIT_PASS`): `aea589eabb634eca4da6051c3e62dfb732e4b9fa563744d7c54246e215f6c333`.
- Current source ZIP SHA-256: `86b29452bf563b1f52d9c423072049b0babb6640be5e2ede0dcb1551fa1be325`.

[OPS_RUNTIME_PARITY]
- Current command surface count: 21 public market-data commands.
- `market-data:provider:smoke` is now public and safe-mode enforced.
- Provider smoke runtime result: `provider_smoke_status=PASS`, `reason_code=PROVIDER_SMOKE_OK`, `publication_created=false`, `pointer_switched=false`, `full_universe_fetch=false`.
- Provider smoke status: `LIVE_PROVIDER_SMOKE_PASSED`; this is provider PASS.
- Scheduler status: `SCHEDULER_RUNTIME_LOG_PRODUCED / PASS`; scheduler due-run artifacts are present.
- Overall ops parity remains `OPS_RUNTIME_PARITY_PASSED`.
- Final full `vendor\bin\phpunit tests/Unit/MarketData` has passed locally: OK (492 tests, 7590 assertions), Time 00:36.861, Memory 40.00 MB. This supports the final ops parity promotion for this source ZIP.


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
- Evidence encoding report is current and clean: `ENCODING: UTF-8`, `SCOPE: storage/app/market-data/**/*.txt`, `checked_files=165`, `null_byte_remaining=0`, `status=PASS`.
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
