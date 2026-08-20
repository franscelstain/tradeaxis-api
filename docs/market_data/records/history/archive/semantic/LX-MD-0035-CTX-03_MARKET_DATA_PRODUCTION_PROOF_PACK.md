# Legacy Semantic Extract — LX-MD-0035-CTX-03

- Source ID: `LS-MD-0035`
- Original path: `audit/MARKET_DATA_PRODUCTION_PROOF_PACK.md`
- Original SHA1: `9D1E95DE0523A6EFF6B7E31DF54056B51CB33F26`
- Extract role: `CONTEXT`
- Source range: `L633-L689`
- Extract body SHA1: `461BE8ECB468CA730BDE97EAFF81AA0AC4CAD557`
- Current authority: `NO`
- Current verification effect: `HISTORICAL_ONLY`
- Preservation policy: `EXACT_SOURCE_RANGE_COPY_WITH_METADATA`

<!-- LEGACY_EXTRACT_BODY_START -->
## 2026-05-22 - Final Provider Smoke Passed / Ops Runtime Parity Lock

[SESSION] FINAL_PROVIDER_SMOKE_PASSED_OPS_RUNTIME_PARITY_LOCK

[SESSION_STATUS] OPS_RUNTIME_PARITY_PASSED

[FINAL_DECISION]
- Ops rollout/runtime parity: `OPS_RUNTIME_PARITY_PASSED`.
- Source-state decision: `MARKET_DATA_PRODUCTION_READY_LOCKED`.  **[SUPERSEDED 2026-08-06 — W22]** Klaim ini tidak berlaku untuk baseline yang dikoreksi; lihat `reports/AUDIT_FINAL_STATE.md`.
- Final source-state lock status: `LOCKED`.
- Final provider smoke: `FINAL_PROVIDER_SMOKE=PASSED`.
- Live provider smoke: `LIVE_PROVIDER_SMOKE_PASSED`.
- Provider smoke safe mode remains non-destructive and single-ticker only.

[AUTHORITATIVE_PROVIDER_SMOKE_ARTIFACT]
- Artifact: `storage/app/market-data/provider-smoke-safe-mode/command-output/provider-smoke-bbca.txt`.
- Command: `php artisan market-data:provider:smoke --ticker=BBCA --trade_date=2026-05-20 --dry-run --retry-max=0`.
- `provider_smoke_status=PASS`.
- `reason_code=PROVIDER_SMOKE_OK`.
- `source_reason_code=none`.
- `provider=Yahoo/PublicApi`.
- `ticker=BBCA`.
- `trade_date=2026-05-20`.
- `dry_run=true`.
- `write_mode=none`.
- `publication_created=false`.
- `seal_executed=false`.
- `finalize_executed=false`.
- `pointer_switched=false`.
- `readable_publication_created=false`.
- `full_universe_fetch=false`.
- `returned_row_count=1`.
- `request_url=https://query1.finance.yahoo.com/v8/finance/chart/BBCA.JK?interval=1d&range=10d&includePrePost=false&events=div%2Csplits&corsDomain=finance.yahoo.com`.
- `http_status=200`.
- `adapter_reason_code=PROVIDER_SMOKE_OK`.
- `attempt_count=1`.
- `retry_max=0`.
- `retry_exhausted=false`.
- `timeout_seconds=10`.

[SCHEDULER_PROOF]
- Scheduler due-run proof remains present through `storage/app/market-data/production-scheduler-cron-deployment-proof/**`.
- `phase3-schedule-run-enabled-due.txt` records `php artisan schedule:run` executing `market-data:daily --latest`.
- `phase4-scheduler-output-log.txt` records `SCHEDULER_RUNTIME_LOG_PRODUCED`.
- `runtime/market-data-scheduler-proof.log` records visible scheduler output with `scheduler_status=FAILURE`; this proves cron execution and non-silent failure handling. It is not treated as provider failure.

[VALIDATION]
- `vendor\bin\phpunit tests/Unit/MarketData --filter "ProviderSmokeSafeModeStaticGuardTest"` -> OK (5 tests, 162 assertions).
- `vendor\bin\phpunit tests/Unit/MarketData --filter "ProductionValidationRuntimeProofStaticGuardTest"` -> OK (15 tests, 477 assertions).
- `vendor\bin\phpunit tests/Unit/MarketData --filter "AuditDocsSynchronizationStaticGuardTest"` -> OK (10 tests, 456 assertions).
- `vendor\bin\phpunit tests/Unit/MarketData` -> OK (492 tests, 7584 assertions), Time 00:09.118, Memory 38.00 MB.

[SUPERSEDES]
- Supersedes previous partial/rate-limited rollout overlays for the current source ZIP; current status remains `OPS_RUNTIME_PARITY_PASSED` and `LIVE_PROVIDER_SMOKE_PASSED`.
- Previous provider-rate-limit records are historical only and must not be used as current rollout status after this proof.
- Current release decision is `OPS_RUNTIME_PARITY_PASSED` because scheduler due-run proof exists, provider smoke returned PASS/HTTP 200, all provider smoke safety flags remained false, and full MarketData PHPUnit passed.


<!-- LEGACY_EXTRACT_BODY_END -->
