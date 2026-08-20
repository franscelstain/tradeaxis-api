# Legacy Semantic Extract — LX-MD-0027-EVD-03

- Source ID: `LS-MD-0027`
- Original path: `audit/FULL_MARKET_DATA_PRODUCTION_READY_INVENTORY.md`
- Original SHA1: `4C0357CC7BA4A9338F34EBCF09A671716FC4A857`
- Extract role: `EVIDENCE`
- Source range: `L273-L344`
- Extract body SHA1: `896E3619A2A2B37CCA1C8D78653E34159F7FF105`
- Current authority: `NO`
- Current verification effect: `HISTORICAL_ONLY`
- Preservation policy: `EXACT_SOURCE_RANGE_COPY_WITH_METADATA`

<!-- LEGACY_EXTRACT_BODY_START -->
## 2026-05-21 Final Proof Pack / Ops Runtime Parity Reconciliation

[SOURCE_ZIP]
- Historical source ZIP path (`SUPERSEDED_BY_FINAL_PROVIDER_SMOKE_PASS_AND_FULL_MARKETDATA_PHPUNIT_PASS`): `D:\Laravel\tradeaxis-api\tradeaxis-api-provider.zip`.
- Historical source ZIP SHA-256 (`SUPERSEDED_BY_FINAL_PROVIDER_SMOKE_PASS_AND_FULL_MARKETDATA_PHPUNIT_PASS`): `aea589eabb634eca4da6051c3e62dfb732e4b9fa563744d7c54246e215f6c333`.

[DECISION]
- Source-state core readiness remains `MARKET_DATA_PRODUCTION_READY_LOCKED`.  **[SUPERSEDED 2026-08-06 — W22]** Klaim ini tidak berlaku untuk baseline yang dikoreksi; lihat `reports/AUDIT_FINAL_STATE.md`.
- Overall rollout decision is `OPS_RUNTIME_PARITY_PASSED`.
- Ops runtime parity remains `OPS_RUNTIME_PARITY_PASSED` because scheduler proof is PASS and provider smoke is `PROVIDER_SMOKE_OK`.

[COMMAND_SURFACE]
- Current runtime command count: 21.
- New current command: `market-data:provider:smoke`.
- Historical 20-command proof is retained as prior fixture evidence; current docs/tests/static guards use the 21-command surface.

[VALIDATION]
- Full `vendor/bin/phpunit tests/Unit/MarketData` -> OK (490 tests, 7506 assertions).
- StaticGuard filter -> OK (191 tests, 4688 assertions).
- Targeted provider/scheduler/command/audit guards passed.

[PROVIDER_SMOKE]
- Command surface: ENFORCED safe-mode, single-ticker, dry-run only.
- Runtime proof: `provider_smoke_status=PASS`, `reason_code=PROVIDER_SMOKE_OK`, `publication_created=false`, `pointer_switched=false`, `full_universe_fetch=false`.
- Provider smoke runtime is PASS.

[SCHEDULER]
- Scheduler due-run runtime proof is present; stale auxiliary phase0/phase5 artifacts still need refresh if a completely clean scheduler proof pack is required.
- Historical blocked container scheduler artifacts are superseded for due-run/non-silent-failure proof by the committed scheduler runtime log; they still must not be counted as successful scheduled daily production run proof.
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
